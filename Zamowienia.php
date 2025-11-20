<?php

class Application_Model_Zamowienia
{
    protected $klient;
    protected $faktura;
    protected $towary;
    protected $koszyk;
    protected $rabat;
    protected $dostawa;
    protected $bezplatnaDostawa;
    protected $zamowienie;
    protected $platnosc;

    public function __construct($guid = NULL) {
        $this->klient = new Application_Model_Klient();
        $this->platnosc = new Application_Model_Platnosc($guid);
        $this->koszyk = Zend_Registry::get('Koszyk');
        if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna && 
                $this->koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna) {
          $this->bezplatnaDostawa = true;
        }
//        Zend_Debug::dump($this->platnosc); exit;
        
        if (!empty($guid)) {
//            $table = new Application_Model_DbTable_Zamowienia();
            $zamowienie = $this->fetchRow("Guid = '$guid'");
            
            if (!empty($zamowienie)) {
              $zamowienie['Guid'] = $guid;
              $plik = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/' . $guid . '.zam';
//              $this->fromFile($plik);
//            Zend_Debug::dump($zamowienie);exit;
              
                $this->zamowienie = $zamowienie;
                
                $data = new Zend_Date($zamowienie['Data']);
                $data->setHour(0);
                $data->addMinute((int) $zamowienie['Czas']);
                $this->zamowienie['Data'] = $data;
                $this->zamowienie['Status'] = 'Zarejestrowane w systemie';
                
                $table = new Application_Model_PozycjeZamowien();
                $towary = $table->fetchAll('Dokument = ' . $zamowienie['ID']);
//                Zend_Debug::dump($towary);exit;
            
//                $towary = $pozycje->toArray();
                foreach ($towary as $k=>$towar) {
                    $towary[$k]['BruttoValue'] = $towar['CenaValue'];
                }
                $this->towary = $towary;
                
                $table = new Application_Model_DbTable_Kontrahenci();
                $kontrahent = $table->fetchAll(
                    $table->select()
                        ->where('Host = ?', $zamowienie['ID'])
                        ->where('HostType = ?', 'DokHandlowe')
                        ->order('ID DESC'));
                
                foreach ($kontrahent->toArray() as $value) {
                    if ($value['Typ'] <> 0) {
                        $value['ImieNazwisko'] = $value['Nazwa'];
                        $value['Email'] = $zamowienie['Email'];
                        $this->klient->setOdbiorca($value);
                    } else if (!empty($value['NIP'])) {
                        $this->klient->setKontrahent($value);
                    }
                }
                
//                Zend_Debug::dump($this->klient);exit;
                $this->zrodlo = 'enova';
                
            } else if ($this->isZlozone ($guid)) {
                $this->zrodlo = 'plik';
                
                $plik = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/' . $guid . '.zam';
                $this->fromFile($plik);
//                Zend_Debug::dump($this->zamowienie);exit;
            }
            
        } else {
            $this->dostawa = new Application_Model_Dostawa();
            $kody = new Application_Model_KodyRabatowe();
            if ($kod = $kody->read()) {
                $rabat = $kody->fetchOne($kod);
            }

            $towary = new Application_Model_Towary();
            $this->towary = $towary->fetchKoszyk($rabat);
            
            if ($this->dostawa->getId()) {
              $this->towary['dostawa'] = $this->dostawa->fetchTowar();
              $this->towary['dostawa']['IloscValue'] = 1;
              $this->towary['dostawa']['IloscSymbol'] = $this->towary['dostawa']['Jednostka'];
            }
            if ($this->bezplatnaDostawa) {
              $this->towary['dostawa']['NettoValue']
                      = $this->towary['dostawa']['BruttoValue']
                      = 0;
              
              $this->towary['dostawa']['bezplatna'] = true;
            }
//            Zend_Debug::dump($this->towary);exit;

            foreach ($this->towary as $v => $t) {
                $ilosc = $this->koszyk->ilosc($t['ID']);
                if (empty($ilosc)) {
                    $ilosc = 1;
                }
                $this->towary[$v]['IloscValue'] = $ilosc;
                $this->towary[$v]['IloscSymbol'] = $t['Jednostka'];

                if (!empty($t['PrzecenaNettoValue'])) {
                    $this->towary[$v]['SumaNetto'] = (float) $t['PrzecenaNettoValue'] * $ilosc;
                } else {
                    $this->towary[$v]['SumaNetto'] = 
                            $this->towary[$v]['PrzecenaNettoValue'] = (float) $t['NettoValue'] * $ilosc;
                }
                $netto  += (float) $this->towary[$v]['SumaNetto'];

                if (!empty($t['PrzecenaBruttoValue'])) {
                    $this->towary[$v]['CenaValue'] = (float) $t['PrzecenaBruttoValue'];
                    $this->towary[$v]['SumaBrutto'] = (float) $t['PrzecenaBruttoValue'] * $ilosc;
                } else {
                    $this->towary[$v]['CenaValue'] = 
                            $this->towary[$v]['PrzecenaBruttoValue'] = (float) $t['BruttoValue'];
                    $this->towary[$v]['SumaBrutto'] = (float) $t['BruttoValue'] * $ilosc;
                }
                $brutto += (float) $this->towary[$v]['SumaBrutto'];
            }

            $this->zamowienie['Guid'] = $this->guid();
            $this->zamowienie['SumaNetto'] = $netto;
            $this->zamowienie['SumaBrutto'] = $brutto;
            $this->zamowienie['SumaVat'] = $brutto - $netto;
    //        $this->zamowienie['bruttoPoRabacie'] = $bruttoPoRabacie;


        }
        
//        Zend_Debug::dump($this->getArray());
        $this->platnosc->setData($this->getArray());
//        Zend_Debug::dump($this->platnosc);exit;
        
        if ($this->isZaplacone()) {
            $this->zamowienie['Status'] = 'Opłacone';
            
        }
    }
    
    

    public function select() {
//        $select = parent::select()->setIntegrityCheck(false);
        $select = Zend_Registry::get('enovaDb')->select();
        $select->from(array('dk' => 'dbo.DokHandlowe'))
                ->joinLeft(array('f' => 'dbo.Features'),
                        '(f.Parent = dk.ID) AND (f.Name = \'E-mail_zamowienia\')',
                        array('Email' => 'Data'))
                ->joinLeft(array('f2' => 'dbo.Features'),
                        '(f2.Parent = dk.ID) AND (f2.Name = \'Uwagi\')',
                        array('Uwagi' => 'Data'))
                ->joinLeft(array('f3' => 'dbo.Features'),
                        '(f.Parent = dk.ID) AND (f.Name = \'Telefon_zamowienia\')',
                        array('Telefon' => 'Data'));
//        Zend_Debug::dump($select->__toString());exit;
        return $select;
    }

    public function fetchAll($where = null) {
        $select = $this->select();
        if ($where) {
            $select->where($where);
        }
//        Zend_Debug::dump($select->__toString());exit;
        $stmt = Zend_Registry::get('enovaDb')->query($select);
        return $stmt->fetchObject();
    }

    public function fetchRow($where = null) {
        $select = $this->select();
        $select->limit(1);
        if ($where) {
            $select->where($where);
        }
//        Zend_Debug::dump($select->__toString());exit;
        $stmt = Zend_Registry::get('enovaDb')->query($select);
        $arr = $stmt->fetchAll();
        return $arr[0];
    }
    
    public function getArray() {
        $data = array(
            'towary' => $this->towary,
            'zamowienie' => $this->zamowienie,
            'odbiorca' => $this->getZamawiajacy(),
            'kontrahent' => $this->getKontrahent(),
            'platnosc' => $this->getPlatnosc()
        );
        
        return $data;
    }


    public function getZamowienie($info = NULL) {
        if (empty($info)) return $this->zamowienie;
        else return $this->zamowienie[$info];
    }

    public function getTowary() {
        return $this->towary;
    }

    public function getDostawa($param = null) {
      if (!is_null($param)) {
        return $this->towary['dostawa'][$param];
      }
      return $this->towary['dostawa'];
    }

    public function bezplatnaDostawa() {
      return $this->bezplatnaDostawa;
    }
    
    public function setRabat($rabat) {
        $this->rabat = $rabat;
    }
    
    public function setOdbiorca($data = NULL) {
        $this->klient->setOdbiorca($data);
    }
    
    public function getOdbiorca($info = NULL) {
        if (!empty($info)) {
            return $this->klient->getOdbiorca($info);
        } else {
            return $this->klient->getOdbiorca();
        }
    }

    public function getZamawiajacy($info = NULL) {
        return $this->klient->getKlient($info);
    }
    
    public function setKontrahent($data = NULL) {
        $this->klient->setKontrahent($data);
    }
    
    public function getKontrahent($info = NULL) {
        if (!empty($info)) {
            return $this->klient->getKontrahent($info);
        } else {
            return $this->klient->getKontrahent();
        }
    }
    
    public function getPlatnosc() {
        return $this->platnosc;
    }
    
    public function getFaktura() {
        return $this->klient->getFaktura();
    }

    private function setKoszyk($array) {
        $this->koszyk = $array;
    }
    
    private function setDostawa($id) {
        $this->dostawa = $id;
    }
    
    public function setUwagi($text) {
        $this->zamowienie['Uwagi'] = $text;
    }
    
    public function setPlatnosc($data) {
        $this->platnosc = $data;
    }
    
    public function isZlozone($guid = null) {
        if ($guid === null) $guid = $this->getZamowienie('Guid');
        $path = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/';
        return file_exists($path . $guid . '.zam');
    }
    
    public function isPotwierdzone($guid = null) {
        if ($guid === null) $guid = $this->getZamowienie('Guid');
        $path = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/';
        return file_exists($path . $guid . '.xml');
    }
    
    public function isZaplacone() {
        return $this->platnosc->isZaplacone();
    }

    public function toFile() {
        $this->zamowienie['Data'] = Zend_Date::now();
        
        $data = array(
            'towary'     => $this->towary,
            'zamowienie' => $this->zamowienie,
            'klient'     => $this->klient->getKlient(),
            'faktura'    => $this->klient->getFaktura(),
            'platnosc'   => $this->platnosc->getArray(),
            'paczkomat'  => $_COOKIE['paczkomat'],
            'paczkomat_csv'  => $_COOKIE['paczkomat_csv'],
            'paczkomat_name'  => $_COOKIE['paczkomat_name']
        );
        
        $plik = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/' 
                . $this->getZamowienie('Guid') . '.zam';
        $handle = fopen($plik, 'w');
        fwrite($handle, serialize($data));
        fclose($handle);
    }
    
    public function fromFile($plik) {
        $handle = fopen($plik, 'r');
        $tresc = fread($handle, filesize($plik));
        $data = unserialize($tresc);
        fclose($handle);
        
        if ($data['faktura']) {
          $faktura = array(
              'Nazwa' => $data['faktura']['FNazwa'],
              'NIP' => $data['faktura']['FNIP'],
              'AdresUlica' => $data['faktura']['FAdresUlica'],
              'AdresNrDomu' => $data['faktura']['FAdresNrDomu'],
              'AdresNrLokalu' => $data['faktura']['FAdresNrLokalu'],
              'AdresMiejscowosc' => $data['faktura']['FAdresMiejscowosc'],
              'AdresKodPocztowy' => $data['faktura']['FAdresKodPocztowy'],
              'AdresPoczta' => $data['faktura']['FAdresPoczta']
          );
          $data['faktura'] = $faktura;
        }
//        Zend_Debug::dump($data);exit;
        
        $this->towary = $data['towary'];
        $this->zamowienie = $data['zamowienie'];
        
        if ($this->isPotwierdzone()) {
            $this->zamowienie['Status'] = 'Zapisane';
        } else {
            $this->zamowienie['Status'] = 'Oczekuje na potwierdzenie przez Klienta';
        }
        
        $this->platnosc->setTyp($data['platnosc']['typ'])
                ->setData($data['platnosc']['data']);
        
        $this->klient = new Application_Model_Klient();
        $this->klient->setKlient($data['klient']);
        $this->klient->setFaktura($data['faktura']);
    }
    
    public function toXml() {
        $data = new Zend_Date();
        $odbiorca = $this->klient->getKlient();
//        Zend_Debug::dump($odbiorca);exit;
        
        if ($this->klient->czyFaktura()) {
            $kontrahent = $this->klient->getFaktura();
            
        } else {
            $kontrahent = array();
            $kontrahent['FNazwa'] = $odbiorca['ImieNazwisko'];
            $kontrahent['FNIP'] = '';
            $kontrahent['FAdresUlica'] = $odbiorca['AdresUlica'];
            $kontrahent['FAdresNrDomu'] = $odbiorca['AdresNrDomu'];
            $kontrahent['FAdresNrLokalu'] = $odbiorca['AdresNrLokalu'];
            $kontrahent['FAdresMiejscowosc'] = $odbiorca['AdresMiejscowosc'];
            $kontrahent['FAdresKodPocztowy'] = $odbiorca['AdresKodPocztowy'];
            $kontrahent['FAdresPoczta'] = $odbiorca['AdresPoczta'];
        }
//        Zend_Debug::dump($kontrahent);exit;
        
//        if ($this->platnosc->getTyp() == 'payu') {
            $sposobZaplatyGuid = Zend_Registry::get('Zend_Config')->enova->zamowienia->sposobZaplaty->{$this->platnosc->getTyp()}->guid;
//        } else {
//            $sposobZaplatyGuid = Zend_Registry::get('Zend_Config')->enova->zamowienia->sposobZaplaty->domyslna->guid;
//        }
        
        $str = array();
        $str[] = '<?xml version="1.0" encoding="utf-8" ?>';
        $str[] = '<session xmlns="http://www.soneta.pl/schema/business" business="true">';
        $str[] = '  <Kontrahent business="false" id="id2" key="Kod=' 
                    . Zend_Registry::get('Zend_Config')->enova->zamowienia->kontrahent . '"></Kontrahent>';
        $str[] = '  <DokumentHandlowy guid="' . $this->zamowienie['Guid'] . '">';
        $str[] = '    <Definicja where="Symbol=' . Zend_Registry::get('Zend_Config')->enova->zamowienia->symbol . '" />';
        $str[] = '    <Magazyn where="Symbol=' . Zend_Registry::get('Zend_Config')->enova->zamowienia->magazyn . '" />';
        $str[] = '    <Data>' . $data->toString('YYYY-MM-dd') . '</Data>';
        $str[] = '    <Czas>' . $data->toString(Zend_Date::TIME_SHORT) . '</Czas>';
        $str[] = '    <DataOperacji>' . $data->toString('YYYY-MM-dd') . '</DataOperacji>';
        $str[] = '    <Kontrahent where="Kod=' . Zend_Registry::get('Zend_Config')->enova->zamowienia->kontrahent . '" />';
        $str[] = '    <Odbiorca where="Kod=' . Zend_Registry::get('Zend_Config')->enova->zamowienia->kontrahent . '" />';
        $str[] = '    <Platnosci>';
        $str[] = '      <Platnosc class="Soneta.Kasa.Naleznosc,Soneta.Kasa">';
        $str[] = '        <SposobZaplaty>' . $sposobZaplatyGuid . '</SposobZaplaty>';
        $str[] = '        <Kwota>' . $this->zamowienie['SumaBrutto'] . ' PLN</Kwota>';
        $str[] = '        <TerminDni>0</TerminDni>';
        $str[] = '      </Platnosc>';
        $str[] = '    </Platnosci>';
        $str[] = '    <Pozycje>';
        
        foreach ($this->towary as $t) {
            if ($t['PrzecenaBruttoValue']) {
                $t['BruttoValue'] = $t['PrzecenaBruttoValue'];
            }
            
            $str[] = '      <Pozycja>';
            $str[] = '        <Towar where="Kod=' . $t['Kod'] . '" />';
            $str[] = '        <Ilosc>' . $t['IloscValue'] . ' ' 
                                . $t['IloscSymbol'] . '</Ilosc>';
            $str[] = '        <Cena>' . $t['BruttoValue'] . ' PLN</Cena>';
            $str[] = '        <CenaPoRabacie>' . $t['PrzecenaBruttoValue'] . ' PLN</CenaPoRabacie>';
            $str[] = '        <Wspolczynnik>1/1</Wspolczynnik>';
            $str[] = '      </Pozycja>';
        
        }
        
        $str[] = '    </Pozycje>';
        $str[] = '    <DaneKontrahenta>';
        $str[] = '      <Nazwa>' . $kontrahent['FNazwa'] . '</Nazwa>';
        $str[] = '      <NIP>' . $kontrahent['FNIP'] . '</NIP>';
        $str[] = '      <Adres>';
        $str[] = '        <Ulica>' . $kontrahent['FAdresUlica'] . '</Ulica>';
        $str[] = '        <NrDomu>' . $kontrahent['FAdresNrDomu'] . '</NrDomu>';
        $str[] = '        <NrLokalu>' . $kontrahent['FAdresNrLokalu'] . '</NrLokalu>';
        $str[] = '        <Miejscowosc>' . $kontrahent['FAdresMiejscowosc'] . '</Miejscowosc>';
        $str[] = '        <Poczta>' . $kontrahent['FAdresPoczta'] . '</Poczta>';
        
        $digitFilter = new Zend_Filter_Digits();
        $str[] = '        <KodPocztowy>' 
                            . $digitFilter->filter($kontrahent['FAdresKodPocztowy']) 
                            . '</KodPocztowy>';
        $str[] = '      </Adres>';
        $str[] = '    </DaneKontrahenta>';
        $str[] = '    <DaneOdbiorcy>';
        $str[] = '      <Nazwa>' . $odbiorca['ImieNazwisko'] . '</Nazwa>';
        $str[] = '      <NIP />';
        $str[] = '      <Adres>';
        $str[] = '        <Ulica>' . $odbiorca['AdresUlica'] . '</Ulica>';
        $str[] = '        <NrDomu>' . $odbiorca['AdresNrDomu'] . '</NrDomu>';
        $str[] = '        <NrLokalu>' . $odbiorca['AdresNrLokalu'] . '</NrLokalu>';
        $str[] = '        <Telefon>' . $odbiorca['AdresTelefon'] . '</Telefon>';
        $str[] = '        <Miejscowosc>' . $odbiorca['AdresMiejscowosc'] . '</Miejscowosc>';
        $str[] = '        <Poczta>' . $odbiorca['AdresPoczta'] . '</Poczta>';
        $str[] = '        <KodPocztowy>' . $digitFilter->filter($odbiorca['AdresKodPocztowy']) 
                            . '</KodPocztowy>';
        $str[] = '      </Adres>';
        $str[] = '    </DaneOdbiorcy>';
        $str[] = '    <features>';
        $str[] = '       <feature name="E-mail_zamowienia">' . $odbiorca['Email'] . '</feature>';
        $str[] = '       <feature name="Telefon_zamowienia">' . $odbiorca['AdresTelefon'] . '</feature>';
        $str[] = '       <feature name="Uwagi">' . $this->zamowienie['Uwagi'] . '</feature>';
        $str[] = '    </features>';
        $str[] = '  </DokumentHandlowy>';
        $str[] = '</session>';
//        Zend_Debug::dump($str);exit;
        
        $plik = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/' 
                . $this->getZamowienie('Guid') . '.xml';
        $handle = fopen($plik, 'w');
        fwrite($handle, implode("\n", $str));
        fclose($handle);
    }
    
    public function getXmlContent() {
        $plik = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/' 
                . $this->getZamowienie('Guid') . '.xml';
        
        if (!file_exists($plik)) return FALSE;
        
        return file_get_contents($plik);
    }
    
    public function toFtp() {
        $conn_id = ftp_connect(Zend_Registry::get('Zend_Config')->enova->zamowienia->ftp->host);
        ftp_login($conn_id, 
            Zend_Registry::get('Zend_Config')->enova->zamowienia->ftp->user, 
            Zend_Registry::get('Zend_Config')->enova->zamowienia->ftp->pass);
        $plik_zdalny = $this->getZamowienie('Guid') . '.xml';
        $plik = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/' 
                . $plik_zdalny;
        ftp_put($conn_id, $plik_zdalny, $plik, FTP_ASCII);
        ftp_close($conn_id);
    }
    
    public function sendXml() {
        if (!empty(Zend_Registry::get('Zend_Config')->enova->zamowienia->ftp->host)) {
            $this->toFtp();
            
        } else {
            $source = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->zamowienie . '/'
                    . $this->getZamowienie('Guid') . '.xml';
            $dest = Zend_Registry::get('Zend_Config')->enova->zamowienia->pliki->xml . '/'
                    . $this->getZamowienie('Guid') . '.xml';
            copy($source, $dest);
        }
    }

    public function guid(){
        mt_srand((double)microtime()*10000);
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $uuid = substr($charid, 0, 8).'-'
                .substr($charid, 8, 4).'-'
                .substr($charid,12, 4).'-'
                .substr($charid,16, 4).'-'
                .substr($charid,20,12);
        return $uuid;
    }
    
    public function iconv($in, $out, $obj) {
        if (is_string($obj)) {
            return iconv($in, $out, $obj);
        } else if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $r[$key] = $this->iconv($in, $out, $value);
            }
            return $r;
        } else {
            return $obj;
        }
    }
}

