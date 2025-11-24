<?php

class KasaController extends Zend_Controller_Action
{
    protected $_system;
    
    public function init()
    {
      $ajaxContext = $this->_helper->getHelper('AjaxContext');
      $ajaxContext->addActionContext('klient', 'html')
              ->addActionContext('klient-validate', 'json')
              ->addActionContext('dostawa', 'html')
              ->addActionContext('platnosc', 'html')
              ->addActionContext('podsumowanie', 'html')
              ->initContext();

      $this->_system = Zend_Registry::get('system');
      $koszyk = new Application_Model_Koszyk();
      if ($koszyk->iloscTowarow() == 0) {
          $this->redirect('/koszyk');
      }
    }

    public function platnoscAction()
    {
      $koszyk = new Application_Model_Koszyk();
      $koszyk->przelicz();

      $dostawa = new Application_Model_Dostawa();
      $where = Zend_Registry::get('enovaDb')->quoteInto('t.ID = ?', $dostawa->getId());
      $dostawa = $dostawa->fetchOne($where);
      //  Zend_Debug::dump($dostawa);exit;

      $platnosc = new Application_Model_Platnosc();
      if ($this->_request->isPost()) {
        if ($id = $this->getRequest()->getParam('platnosc')) {
          $platnosc->setId($id);
        }
      }
        
        if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna && 
            $koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna) {
        
            $this->view->assign('bezplatnaDostawa', true);
            
        }
        
        if (stripos($dostawa['Nazwa'], Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->paczkomat->nazwa) !== false) {
          
          if (empty($_COOKIE['paczkomat'])) {
            // $this->_redirect('/koszyk');
            
          } else {
//            Zend_Debug::dump($_COOKIE['paczkomat']);exit;
            $dostawa['Paczkomat'] = json_decode($_COOKIE['paczkomat']);
            
          }
        }
        $this->view->assign('dostawa', $dostawa);


        if ($dostawa['SposobZaplatyID'] == Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata) {
          
          $payU = new Application_Model_PayU();
          $opcje = $payU->getOptions();

        } else {
          $opcje[] = [
            'value' => 'gotowka',
            'name' => 'Gotówka'
          ];
          
        }

        $this->view->assign('opcje', $opcje);
        $this->view->assign('platnoscCheck', ['ID' => $platnosc->getId()]);

        $this->view->assign('sposobZaplaty', array(
            'SposobZaplatyID' => $this->view->dostawa['SposobZaplatyID'],
            'SposobZaplatyNazwa' => $this->view->dostawa['SposobZaplatyNazwa']
        ));
    }

    public function klientValidateAction()
    {
      $form = new Application_Form_Zamowienie();

      if ($this->_request->isPost()) {
        if ($this->getRequest()->getParam('czy_faktura') == 0) {
            $toValid = $form->getSubForm('klient');
        } else {
            $toValid = $form;
        }
        
        if($toValid->isValid($this->getRequest()->getParams())) {
          $klient = new Application_Model_Klient();
          $values = $form->getValues();
          $values['klient']['ImieNazwisko'] = $values['klient']['Imie'] . ' ' . $values['klient']['Nazwisko'];
          $klient->save($values);
          // Zend_Debug::dump($klient->getAll());exit;

          echo json_encode(['status'=>'valid']);exit;
            
        } else {
          echo json_encode(['status'=>'invalid']);exit;

        }
      }
    }

    public function klientAction()
    {
      $form = new Application_Form_Zamowienie();
      $klient = new Application_Model_Klient();

      if ($klient->hasKlient()) {
        $form->populate($klient->getAll());
      }

      if ($this->_request->isPost()) {
        if ($this->getRequest()->getParam('czy_faktura') == 0) {
            $toValid = $form->getSubForm('klient');
        } else {
            $toValid = $form;
        }
        
        if($toValid->isValid($this->getRequest()->getParams())) {
            $values = $form->getValues();
            $values['klient']['ImieNazwisko'] = $values['klient']['Imie'] . ' ' . $values['klient']['Nazwisko'];
            $klient->save($values);
            
        } else {
          $form->populate($this->getRequest()->getParams());

        }
      }

      $this->view->assign('form', $form);

    }

    public function dostawaAction()
    {
      $dostawa = new Application_Model_Dostawa();
      

      if ($this->_request->isPost()) {
        $dostawa->setId($this->getRequest()->getParam('dostawa'));
      }

      $where = Zend_Registry::get('enovaDb')->quoteInto('t.ID = ?', $dostawa->getId());
      $dostawa_data = $dostawa->fetchOne($where);
      //  Zend_Debug::dump($dostawa);exit;
      // if (stripos($dostawa_data['Nazwa'], Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->paczkomat->nazwa) !== false && empty($_COOKIE['paczkomat'])) {
       
      //   $this->_redirect('/koszyk');
      // }

      $koszyk = Zend_Registry::get('Koszyk');
      
      if ($koszyk->iloscTowarow()) {
        $towary = new Application_Model_Towary();
        $towary = $towary->fetchKoszyk($rabat);
        $this->view->towary = $towary;

        $waga = 0;
        $wielkogabarytowe = false;
        foreach ($towary as $towar) {
            $waga += $towar['MasaBruttoValue'] * $koszyk->ilosc($towar['ID']);
            if ($towar['Wielkogabarytowy']) {
                $wielkogabarytowe = true;
            }
        }
        // Zend_Debug::dump($dostawa);exit;
        
        $dostawaArr = $dostawa->fetchOpcje($waga, $wielkogabarytowe);
        
        $this->view->dostawaCheck = null;
        $this->view->dostawa = array();
        
        foreach ($dostawaArr as $row) {
          if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna && 
                $koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna) {

            if ((Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata == $row['SposobZaplatyID']) 
                || (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->gotowka == $row['SposobZaplatyID'])) 
              $this->view->dostawa[$row['ID']] = $row;
            
          } else {
            $this->view->dostawa[$row['ID']] = $row;
            
          }
          
          if ($dostawa->getId() == $row['ID'])
            $this->view->dostawaCheck = $row;
        }
        
        if ($paczkomat = $_COOKIE['paczkomat'])
          $this->view->assign('paczkomat', json_decode($paczkomat));
        
        
  //            Zend_Debug::dump($dostawaArr);exit;
        
        if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna &&
                $koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna)
          $this->view->assign('bezplatnaDostawa', true);
        
  //            if (!array_key_exists($dostawa->getId(), $this->view->dostawa)) {
  //                $this->view->dostawaCheck = null;
  //                $dostawa->setId($this->view->dostawa[0]['ID']);
  //            }
  //            
  //            if ($this->view->dostawaCheck['SposobZaplatyID'] == Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata) {
  //              $this->view->assign('platnoscOpcje', array(
  //                  'payu' => 'płatność elektroniczna PayU', 
  //                  'przelew' => 'przelew tradycyjny'
  //              ));
  //            }
        $this->view->towaryWaga = $waga;

      } else {
        $this->view->assign('jsRedirectUrl', '/koszyk');

      }

    }

    public function indexAction()
    {
      $form = new Application_Form_Zamowienie();
      $klient = new Application_Model_Klient();
      
      $dostawa = new Application_Model_Dostawa();
      $where = Zend_Registry::get('enovaDb')->quoteInto('t.ID = ?', $dostawa->getId());
      $dostawa_data = $dostawa->fetchOne($where);
      //  Zend_Debug::dump($dostawa);exit;
      // if (stripos($dostawa_data['Nazwa'], Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->paczkomat->nazwa) !== false && empty($_COOKIE['paczkomat'])) {
       
      //   $this->_redirect('/koszyk');
      // }
      
      if ($this->_request->isPost()) {
          if ($this->getRequest()->getParam('czy_faktura') == 0) {
              $toValid = $form->getSubForm('klient');
          } else {
              $toValid = $form;
          }
          
          if($toValid->isValid($this->getRequest()->getParams())) {
              $values = $form->getValues();
              $values['klient']['ImieNazwisko'] = $values['klient']['Imie'] . ' ' . $values['klient']['Nazwisko'];
              $klient->save($values);
              $this->redirect('/kasa/podsumowanie');
              
          } else {
//                $form = new Application_Form_Zamowienie();
              
          }
          
          $form->populate($this->_request->getPost());
          
      } else if ($klient->hasKlient()) {
          $form->populate($klient->getAll());
          
      }
      
      $koszyk = Zend_Registry::get('Koszyk');
      
      if ($koszyk->iloscTowarow()) {
        $towary = new Application_Model_Towary();
        $towary = $towary->fetchKoszyk($rabat);
        $this->view->towary = $towary;

        $waga = 0;
        $wielkogabarytowe = false;
        foreach ($towary as $towar) {
            $waga += $towar['MasaBruttoValue'] * $koszyk->ilosc($towar['ID']);
            if ($towar['Wielkogabarytowy']) {
                $wielkogabarytowe = true;
            }
        }
        // Zend_Debug::dump($dostawa);exit;
        
        $dostawaArr = $dostawa->fetchOpcje($waga, $wielkogabarytowe);
        
        $this->view->dostawaCheck = null;
        $this->view->dostawa = array();
        
        foreach ($dostawaArr as $row) {
          if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna && 
                $koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna) {
            if ((Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata == $row['SposobZaplatyID']) || (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->gotowka == $row['SposobZaplatyID'])) 
              $this->view->dostawa[$row['ID']] = $row;
            
          } else {
            $this->view->dostawa[$row['ID']] = $row;
            
          }
          
          if ($dostawa->getId() == $row['ID'])
            $this->view->dostawaCheck = $row;
        }
        
        if ($paczkomat = $_COOKIE['paczkomat'])
          $this->view->assign('paczkomat', json_decode($paczkomat));
        
        
  //            Zend_Debug::dump($dostawaArr);exit;
        
        if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna && 
                $koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna)
          $this->view->assign('bezplatnaDostawa', true);
        
  //            if (!array_key_exists($dostawa->getId(), $this->view->dostawa)) {
  //                $this->view->dostawaCheck = null;
  //                $dostawa->setId($this->view->dostawa[0]['ID']);
  //            }
  //            
  //            if ($this->view->dostawaCheck['SposobZaplatyID'] == Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata) {
  //              $this->view->assign('platnoscOpcje', array(
  //                  'payu' => 'płatność elektroniczna PayU', 
  //                  'przelew' => 'przelew tradycyjny'
  //              ));
  //            }
        $this->view->towaryWaga = $waga;

      }
      
      $this->view->form = $form;
    }

    public function _klientAction()
    {
              
        $form = new Application_Form_Zamowienie();
        $klient = new Application_Model_Klient();
        
        $dostawa = new Application_Model_Dostawa();
        $where = Zend_Registry::get('enovaDb')->quoteInto('t.ID = ?', $dostawa->getId());
        $dostawa = $dostawa->fetchOne($where);
//        Zend_Debug::dump($dostawa);exit;
        if (stripos($dostawa['Nazwa'], Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->paczkomat->nazwa) !== false && empty($_COOKIE['paczkomat'])) {
         
          $this->_redirect('/koszyk');
        }
        
        if ($this->_request->isPost()) {
            if ($this->getRequest()->getParam('czy_faktura') == 0) {
                $toValid = $form->getSubForm('klient');
            } else {
                $toValid = $form;
            }
            
            if($toValid->isValid($this->getRequest()->getParams())) {
                $values = $form->getValues();
                $values['klient']['ImieNazwisko'] = $values['klient']['Imie'] . ' ' . $values['klient']['Nazwisko'];
                $klient->save($values);
                $this->redirect('/kasa/podsumowanie');
                
            } else {
//                $form = new Application_Form_Zamowienie();
                
            }
            
            $form->populate($this->_request->getPost());
            
        } else if ($klient->hasKlient()) {
            $form->populate($klient->getAll());
            
        }
        
        $this->view->form = $form;
    }

    public function podsumowanieAction()
    {
        $klient = new Application_Model_Klient();
        $this->view->assign('klient', $klient->getAll());
        
        $kody = new Application_Model_KodyRabatowe();
//        Zend_Debug::dump($kody->read());exit;
        if ($kod = $kody->read()) {
            $rabat = $kody->fetchOne($kod);
            $this->view->kodRabatowy = $kod;
            $this->view->kodRabat = $rabat;
        }
        
        $towary = new Application_Model_Towary();
        $towary = $towary->fetchKoszyk($rabat);
        $this->view->towary = $towary;
        
        $koszyk = new Application_Model_Koszyk();
        $koszyk->przelicz();
//        Zend_Debug::dump($koszyk->bruttoPoRabacie);exit;
        
        $dostawa = new Application_Model_Dostawa();
        $where = Zend_Registry::get('enovaDb')->quoteInto('t.ID = ?', $dostawa->getId());
        $dostawa = $dostawa->fetchOne($where);
//        Zend_Debug::dump($dostawa);exit;
        
        if (Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna && 
            $koszyk->bruttoPoRabacie > Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->bezplatna) {
        
            $this->view->assign('bezplatnaDostawa', true);
            
        }
        $this->view->assign('dostawa', $dostawa);

    }

    public function finalizacjaAction()
    {
      if (!$this->_request->isPost()) $this->redirect('/kasa');

        $koszyk = $koszyk = Zend_Registry::get('Koszyk');
        
        $zamowienie = new Application_Model_Zamowienia();
//        Zend_Debug::dump($zamowienie->getTowary());exit;
        $daneZamowienia = $zamowienie->getZamowienie();
        
        $kody = new Application_Model_KodyRabatowe();
        if ($kod = $kody->read()) {
            $rabat = $kody->fetchOne($kod);
        }
        
        $uwagi = $this->getRequest()->getParam('uwagi') . "\n\n";
        if ($kod) {
            $uwagi .= 'użyty kod rabatowy: ' . $kod . ' (-' . $rabat . '%)'
                    . "\n\n";
        }
        
        if ($zamowienie->bezplatnaDostawa())
          $this->view->assign('bezplatnaDostawa', $bezplatnaDostawa);
        
//        Zend_Debug::dump($zamowienie);exit;
        
        if (stripos($zamowienie->getDostawa('Nazwa'), Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->paczkomat->nazwa) !== false) {
        
          if (empty($_COOKIE['paczkomat'])) {
            $this->redirect('/kasa');
            
          } else {
            $paczkomat = json_decode($_COOKIE['paczkomat']);
//          Zend_Debug::dump($paczkomat); exit;
            $uwagi .= 'Paczkomat: '
                    . $paczkomat->name . ', ' 
                    . $paczkomat->address->line1 . ', ' 
                    . $paczkomat->address->line2
                    . "\n\n";
            
          }
        }
        
        $zamowienie->setUwagi($uwagi);
        
        $platnosc = $zamowienie->getPlatnosc();
        $dostawa = $zamowienie->getDostawa();

        switch ($dostawa['SposobZaplatyID']) {
          case Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->przedplata:
            $platnosc->setTyp('payu');
            break;

          case Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->pobranie:
            $platnosc->setTyp('pobranie');
            break;

          case Zend_Registry::get('Zend_Config')->enova->zamowienia->dostawa->gotowka:
            $platnosc->setTyp('gotowka');
            break;
      
        }
        // Zend_Debug::dump($platnosc);exit;
        
        $ga_towary = $ga4_towary = array();
        foreach ($zamowienie->getTowary() as $key => $value) {
          if ($key !== 'dostawa') {
            $ga_towary[] = array(
                'id'        => $value['ID'],
                'name'      => $value['Nazwa'],
                'price'     => $value['CenaValue'],
                'quantity'  => $value['IloscValue']
            );
            $ga4_towary[] = array(
                'item_id'       => $value['ID'],
                'item_name'     => $value['Nazwa'],
                'price'         => $value['CenaValue'],
                'quantity'      => $value['IloscValue']
            );
          } else {
            $dostawa = $value;
          }
        }
        
        $this->view
              ->assign('ga_towary', $ga_towary)
              ->assign('ga4_towary', $ga4_towary)
              ->assign('dostawa', $dostawa);
//        Zend_Debug::dump($ga_towary);exit;
//        exit;

        $bodyHtml = clone $this->view;
        $bodyHtml->addScriptPath(APPLICATION_PATH . '/views/scripts/email/')
                ->addHelperPath(APPLICATION_PATH . '/views/helpers/')
                ->assign('zamowienie', $zamowienie->getZamowienie())
                ->assign('towary', $zamowienie->getTowary())
                ->assign('zamawiajacy', $zamowienie->getZamawiajacy())
                ->assign('faktura', $zamowienie->getFaktura())
                ->assign('platnosc', $zamowienie->getPlatnosc())
                ->assign('czynnosc', 'skladanie');
//        echo($bodyHtml->render('zamowienie.phtml'));exit;

        $mail = new Zend_Mail('UTF-8');
        $mail->setBodyHtml($bodyHtml->render('zamowienie.phtml'));
        $mail->addTo(
                $zamowienie->getZamawiajacy('Email'),
                $zamowienie->getZamawiajacy('ImieNazwisko'));
        $mail->addTo(
                Zend_Registry::get('Zend_Config')->enova->zamowienia->email->adres,
                Zend_Registry::get('Zend_Config')->enova->zamowienia->email->Nazwa);
        $mail->setSubject('Potwierdzenie zamówienia.')
          ->setReplyTo('sklep@bifix.pl', 'Sklep Bifix');
        // Zend_Debug::dump($mail);exit;
        
        if ($mail->send()) {
            $zamowienie->toFile();
            $zamowienie->toXml();
            $zamowienie->sendXml();
            
            $koszyk->delete();
            $this->view->koszyk = $koszyk;
            
            $this->view
              ->assign('zamowienie', $zamowienie->getZamowienie())
              ->assign('towary', $zamowienie->getTowary())
              ->assign('zamawiajacy', $zamowienie->getOdbiorca());
        
            if ($platnosc->getTyp() == 'payu') {
              $payu = new Application_Model_PayU();

              if ($platnosc->getId() <> 'przelew') {
                $order['payMethods']['payMethod']['type'] = 'PBL';
                $order['payMethods']['payMethod']['value'] = $platnosc->getId();
                $platnosc->addData($order);
              }

              $order = $platnosc->getData();
              // Zend_Debug::dump($order);exit;
              $response = $payu->orderCreate($order);
              // Zend_Debug::dump($response);exit;
              // $this->redirect($response->getResponse()->redirectUri);
              $this->view->assign('redirectUrl', $response->getResponse()->redirectUri);
            }
            
            // setcookie("paczkomat", "", time()-3600);
        }
    }


}







