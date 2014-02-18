<?php

class ProfilController extends Zend_Controller_Action
{

    const USERS_PAGE_LIMIT = 20;
    const PLATFORMS_PAGE_LIMIT = 20;

    public function init()
    {
        if (!isset($_COOKIE['profil']) || $_COOKIE['profil']!='get_access') {
            $this->_redirect('/');
        }
        else {
            $this->_helper->layout->setLayout('adminpanel');
            $this->view->headTitle()->exchangeArray(array());
            $this->view->headTitle()->setSeparator(' — ');
        }
    }

    public function indexAction()
    {
        $this->view->activeTab = 'home';
    }

    public function usersAction()
    {
        $accountModel = new Application_Model_DbTable_Accounts();

        $paginator = Zend_Paginator::factory($accountModel->select());
        $paginator->setCurrentPageNumber((int)$this->_getParam('page'))
            ->setItemCountPerPage(self::USERS_PAGE_LIMIT);

        $this->view->paginator = $paginator;
        $this->view->activeTab = 'users';
    }

    public function usesrAction()
    {
        $realtionModel = new Application_Model_DbTable_ProjectsFreelancersRealtions();

        $paginator = Zend_Paginator::factory($realtionModel->select());
        //var_dump($accountModel->select());
        //echo '<br>#############################<br>';
        //var_dump($paginator);
        $paginator->setCurrentPageNumber((int)$this->_getParam('page'))
            ->setItemCountPerPage(self::USERS_PAGE_LIMIT);

        $this->view->paginator = $paginator;
        $this->view->activeTab = 'usesr';
    }

    public function userEditAction()
    {
        $id = (int)$this->_getParam('id');
        if (!$id) {
            throw new Zend_Controller_Action_Exception('Empty user ID', 404);
        }

        $accountModel = new Application_Model_DbTable_Accounts();
        $account = $accountModel->find($id)->current();
        if (null === $account) {
            throw new Zend_Controller_Aciton_Exception("User '{$id}' was not found", 404);
        }

        $request = $this->getRequest();
        $form = new Application_Model_AccountForm();

        if ($request->isPost() && $form->isValid($request->getPost())) {
            $account->setFromArray($form->getValues());
            $account->save();

            $this->view->saved = true;
        } else {
            $form->populate($account->toArray());
        }

        $this->view->form = $form;
        $this->view->activeTab = 'users';
    }

    public function userDeleteAction()
    {
        $id = (int)$this->_getParam('id');
        if (!$id) {
            throw new Zend_Controller_Action_Exception('Empty user ID', 404);
        }

        $accountModel = new Application_Model_DbTable_Accounts();
        $account = $accountModel->find($id)->current();
        if (null === $account) {
            throw new Zend_Controller_Aciton_Exception("User '{$id}' was not found", 404);
        }

        $account->delete();

        $this->_redirect('/adminpanel/users');
    }

    public function contentsAction()
    {
        $contentModel = Application_Model_DbTable_Contents::getInstance();

        $this->view->contents = $contentModel->fetchAll(null, 'page');
        $this->view->activeTab = 'contents';
    }

    public function contentEditAction()
    {
        $id = (int)$this->_getParam('id');
        if (!$id) {
            throw new Zend_Controller_Action_Exception('Empty user ID', 404);
        }

        if ($content = trim($this->_getParam('content'))) {
            Application_Model_DbTable_Contents::set($id, $content);

            $this->view->saved = true;
        }

        $this->view->id = $id;
    }

    public function platformsAction()
    {
        $platformsModel = new Application_Model_DbTable_Platforms();

        $paginator = Zend_Paginator::factory($platformsModel->select());
        $paginator->setCurrentPageNumber((int)$this->_getParam('page'))
            ->setItemCountPerPage(self::PLATFORMS_PAGE_LIMIT);

        $this->view->paginator = $paginator;
        $this->view->activeTab = 'platforms';
    }
    public function managepaymentpageAction()
    {
        $paymentpagesettingsModel = new Application_Model_DbTable_Paymentpagesetting();
        $form = new Application_Model_PaymentpagesettingForm();
        $pageSetting = $paymentpagesettingsModel->find('1')->current();
        if (null === $pageSetting) {
            throw new Zend_Controller_Aciton_Exception("Page setting was not found", 404);
        }

        $request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $pageSetting->setFromArray($form->getValues());
            $pageSetting->save();

            $this->view->saved = true;
        } else {
            $form->populate($pageSetting->toArray());
        }
        //print_r($pageSetting->toArray());die;
        $this->view->form = $form;
        $this->view->activeTab = 'payment_page';
    }
}