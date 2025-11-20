<?php

class ZamowieniaController extends Zend_Controller_Action
{
    protected $_system;
    
    public function init()
    {
        $this->_system = Zend_Registry::get('system');
    }

    public function indexAction()
    {
        $adapter = Zend_Db_Table::getDefaultAdapter();
        $adapter->getConnection()->exec('SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED');
    }

    public function verifyAction()
    {
        if ($guid = $this->getRequest()->getParam('guid', null)) {
            $zamowienie = new Application_Model_Zamowienia($guid);
//            Zend_Debug::dump($zamowienie->getTowary());exit;
            
            if (!$zamowienie->isZlozone()) {
                $this->view->stan = 'Nie znaleziono zamowienia.';
                
            } else if ($zamowienie->isPotwierdzone()) {
                $this->view->stan = 'Zamówienie zostało już wcześniej potwierdzone.';
                
            } else {
//                                Zend_Debug::dump($zamowienie);exit;
                $zamowienie->toXml();
                $zamowienie->toFtp();

                $bodyHtml = new Zend_View();
                $bodyHtml->addScriptPath(APPLICATION_PATH . '/views/scripts/email/')
                        ->addHelperPath(APPLICATION_PATH . '/views/helpers/')
                        ->assign('zamowienie', $zamowienie->getZamowienie())
                        ->assign('towary', $zamowienie->getTowary())
                        ->assign('zamawiajacy', $zamowienie->getOdbiorca())
                        ->assign('faktura', $zamowienie->getKontrahent())
                        ->assign('komunikat', $komunikat);

                $mail = new Zend_Mail('UTF-8');
                $mail->setBodyHtml($bodyHtml->render('zamowienie.phtml'));
                $mail->setReplyTo($zamowienie->getZamawiajacy('Email'), 
                    $this->view->win2utf($zamowienie->getZamawiajacy('Nazwa')));
                $mail->addTo($this->_system['zamowienia']['email']['adres'], 
                        $this->_system['zamowienia']['email']['nazwa']);
                $mail->setSubject('Nowe zamówienie');
                $mail->send();
                
                $this->view->stan = '<p>Dziękujemy za potwierdzenie zamowienia.</p>'
                    . '<p>Szczegóły zamówienia dostępne pod <a href="' . $this->view->url(array('controller' => 'zamowienia', 'action' => 'szczegoly', 'guid' => $guid)) . '">tym adresem</a>.</p>';
                
                $this->view
                        ->assign('zamowienie', $zamowienie->getZamowienie())
                        ->assign('pozycje', $zamowienie->getTowary())
                        ->assign('zamawiajacy', $zamowienie->getOdbiorca());
            }
            
        } else {
            $this->view->stan = 'Nie znaleziono zamówienia';
        }
    }

    public function szczegolyAction()
    {
        if ($guid = $this->getRequest()->getParam('guid')) {
            $zamowienie = new Application_Model_Zamowienia($guid);
//            Zend_Debug::dump($zamowienie->getZamowienie());exit;
            $platnosc = $zamowienie->getPlatnosc();
            
            $this->view->assign('zamowienie', $zamowienie->getZamowienie())
                ->assign('platnosc', $zamowienie->getPlatnosc())
                ->assign('towary', $zamowienie->getTowary())
                ->assign('klient', $zamowienie->getZamawiajacy())
                ->assign('faktura', $zamowienie->getFaktura());
            
            if (!$platnosc->isZaplacone() && $platnosc->getTyp() == 'payu') {
                $this->view->assign('payu', $platnosc->getPayuForm());
            }
//            Zend_Debug::dump($zamowienie->getPlatnosc()); exit;
        }
        
        if ($this->getRequest()->getParam('analytics', null)) {
            $zamowienie = $zam->getZamowienie();
            $pozycje = $zam->getTowary();
            $zamawiajacy = $zam->getOdbiorca();
//            Zend_Debug::dump($pozycje); exit;
            $dostawa = 0;
            $dostawa = $pozycje['dostawa'];
            unset($pozycje['dostawa']);

            $trans = array(
                $zamowienie['Guid'],                 // order ID - required
                '',                                        // affiliation or store name
                $zamowienie['SumaBrutto']-$dostawa['CenaValue'],  // total - required
                $zamowienie['SumaVat']-($dostawa['CenaValue']-$dostawa['SumaNetto']),                                        // tax
                $dostawa['CenaValue'],                                  // shipping
                $zamawiajacy['AdresMiejscowosc'],    // city
                '',                                        // state or province
                'Polska'
            );
            $this->view->GoogleAnalytics()->addTrans('UA-21409254-6', $trans);
            
            foreach ($pozycje as $k => $poz) {
                
                $grupy = array_diff(explode('\\', $poz['Grupa']), array('', 'Kategoria'));
                $category = implode(' / ', $grupy);
                $item = array(
                    $zamowienie['Guid'],                  // order ID - required
                    $this->view->win2utf($poz['Kod']),                   // SKU/code - required
                    $this->view->win2utf($poz['Nazwa']), // product name
                    $this->view->win2utf($category),                     // category or variation
                    $poz['CenaValue'],             // unit price - required
                    $poz['IloscValue']             // quantity - required
                );
                $this->view->GoogleAnalytics()->addItem('UA-21409254-6', $item);
            }

            $this->view->GoogleAnalytics()->trackTrans('UA-21409254-6');
        }
    }


}





