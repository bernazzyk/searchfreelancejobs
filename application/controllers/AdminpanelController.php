<?php

class AdminpanelController extends Zend_Controller_Action
{
    
    const USERS_PAGE_LIMIT = 20;
	const PLATFORMS_PAGE_LIMIT = 20;
    
    public function init()
    {
	
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
		$accountModel = new Application_Model_DbTable_Accounts();
        $account = $accountModel->find($userId)->current();
        if (!$userId  || 'admin' != $account->type) {
			$this->_redirect('/');
            //throw new Zend_Controller_Action_Exception('not logged in');
            
        }
        
        
        if (null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        if ('admin' != $account->type) {
            throw new Zend_Controller_Action_Exception('Wrong user');
        }
        
        $this->_helper->layout->setLayout('adminpanel');
        
        $this->view->headTitle()->exchangeArray(array());
        $this->view->headTitle()->setSeparator(' — ');
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
		$form->paymentPageSettingFrm();
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
	public function manageplatformsheaderAction()
    {
        $paymentpagesettingsModel = new Application_Model_DbTable_Paymentpagesetting();
		$form = new Application_Model_PaymentpagesettingForm();
		$form->platformsHeaderSettingFrm();
        $pageSetting = $paymentpagesettingsModel->find('2')->current();
        if (null === $pageSetting) {
            throw new Zend_Controller_Aciton_Exception("Platforms header setting was not found", 404);
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
		$this->view->activeTab = 'platforms_header';
    }
	public function sendmailAction()
	{
		$form = new Application_Model_SendMailAdminForm();
		$form->sendMailFrm();
		$request = $this->getRequest();
        if ($request->isPost() && $form->isValid($request->getPost())) {
			$formData = $this->_request->getPost();
			$accountModel = new Application_Model_DbTable_Accounts();
			$emailRows = $accountModel->getAll(array('email'));
			//echo "<pre>";
			//print_r($emailRows);die;
			$config = array('auth' => 'login',
			'username' => 'support@searchfreelancejobs.com',
			'password' => '123qwe');
			$transport = new Zend_Mail_Transport_Smtp('mail.searchfreelancejobs.com', $config);	
			foreach($emailRows as $email){
			
			if(!empty($email['email']))
			{	
			//echo $email['email'];die;				
			$mail = new Zend_Mail();
            $mail->setFrom('no-reply@SearchFreelanceJobs.com', 'SearchFreelanceJobs.com');
            $mail->setSubject($formData['subject']);
            $mail->setBodyText($formData['body']);
    		$mail->addTo($email['email']);
			$mail->send($transport);
		}
	}				
		//	echo "<pre>";
		//print_r($mail);die;
            	
			$this->view->send = true;
			$form->reset();
			//$this->_redirect('/adminpanel/');
        } 
        $this->view->form = $form;
		$this->view->activeTab = 'mail';
	}
}