<?php

class RegistrationController extends Zend_Controller_Action
{
    public function init()
    {
        Zend_Session::start(); 
        $messages = $this->_helper->flashMessenger->getMessages();
        if (isset($messages[0])) {
            $this->view->message = $messages[0];
        }
    }
    
    public function indexAction()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if ($userId) {
            $this->_redirect('/');
        }
        
        $db = Zend_Registry::get('db');
        
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $this->view->assign('CurrController', $controller);
        $this->view->assign('CurrAction', $action);
        
        $model = new Application_Model_Registration();
		
        $form = new Application_Model_RegisterUser();
        
        $modelFromIndex = new Application_Model_Index();
        $modelFromGeneral = new Application_Model_General();
        $Platforms = $modelFromIndex->getPlatforms();
        $this->view->assign('Platforms', $Platforms);
        
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)) {
                $result_of_insertion = $model->insertUser($formData);
                if ($result_of_insertion['insertResult'] == 2) {
                    $this->view->message = '<span class="f_error_msg">Such email was already registered</span>';
                    $form->populate($formData);
                } elseif ($result_of_insertion['insertResult'] == 1) {
                    // authorize a user
                    $adapter = new Zend_Auth_Adapter_DbTable($db, 'accounts', 'id', 'password');
                    $user_id = $result_of_insertion['user_id'];
                    
                    if ($user_id) {
					
					
						$paymentpagesetting = new Application_Model_DbTable_Paymentpagesetting();
						$paymentPageSetting = $paymentpagesetting->getDetails(array('active'),'1');
						
						
                        $adapter->setIdentity($user_id);
                        $adapter->setCredential(md5(md5($form->password->getValue()). 'dfd67fbcf54d99ef2dc2f900610255e4') );
                        
                        $auth = Zend_Auth::getInstance();
                        $result = $auth->authenticate($adapter);
                        
                        $link_to_confirm = 'http://searchfreelancejobs.com/registration/confirm/u/' . $result_of_insertion['user_id'].'/hs/'.$result_of_insertion['insertHashSum'];
                        												
						
						$config = array('auth' => 'login',
										'username' => 'support@searchfreelancejobs.com',
										'password' => '123qwe');
						$transport = new Zend_Mail_Transport_Smtp('mail.searchfreelancejobs.com', $config);		
 								
                        $mail = new Zend_Mail();
                        $mail->setFrom('support@SearchFreelanceJobs.com', 'SearchFreelanceJobs.com');
                        $mail->setSubject('Please confirm your SearchFreelanceJobs.com Account');
                        $mail->setBodyText("Welcome to the SearchFreelanceJobs.com family! Please click the link below or copy/paste into your browser, to confirm your new SearchFreelanceJobs.com account:\n\n$link_to_confirm\n\nWith love,\nThe SearchFreelanceJobs.com Team");
                        $mail->addTo($formData['email']);						
                        $mail->send($transport);						
                        if($paymentPageSetting['active']==1)
						{
                       		$_SESSION['returnUrl'] = '/registration/step2';
							$this->_helper->redirector('step2');
						}
						else
						{
						 	$this->_redirect('/');
						}
                    }
                }
            } else {
                $form->populate($formData);
            }
        }
        
        $this->view->form = $form;
    }
    
    public function step2Action()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
        
        $accountModel = new Application_Model_DbTable_Accounts();
        $transactionModel = new Application_Model_DbTable_Transactions();
        $payment = new Application_Model_Payment();
        
        $account = $accountModel->find($userId)->current();
        if (null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        $request = $this->getRequest();
        $form = new Application_Model_PaymentForm();
        
        if ($request->isPost()) {
            switch ($request->getPost('paytype')) {
                case 'paypal':
                    foreach ($form->getElements() as $elementName => $element) {
                        $element->setRequired(false);
                    }
                    break;
                case 'cc':
                    break;
            }
            if ($form->isValid($request->getPost())) {
                $account->setFromArray($form->getValues());
                if ($form->paytype->getValue() == 'cc') {
                    $cc = array(
                        'cctype' => $form->cctype->getValue(),
                        'cc' => $form->cc->getValue(),
                        'ccexpmonth' => $form->ccexpmonth->getValue(),
                        'ccexpyear' => $form->ccexpyear->getValue(),
                        'ccv' => $form->ccv->getValue()
                    );
                    $ccToStore = $cc;
                    $ccToStore['cc'] = substr_replace($ccToStore['cc'], '', 0, -4);
                    unset($ccToStore['ccv']);
                    $account->setCC($ccToStore);
                }
                $account->save();
                
                switch ($account->paytype) {
                    case 'paypal':
                        $transaction = $transactionModel->createRow();
                        $transaction->account_id = $userId;
                        $transaction->amount = SUBSCRIPTION_COST;
                        $transaction->added = new Zend_Db_Expr('NOW()');
                        $transaction->paytype = 'paypal';
                        $transaction->save();
                        
                        $account->agreed = 1;
                        $account->agreed_at = new Zend_Db_Expr('NOW()');
                        $account->save();
                        
                        $this->view->sendForm = $payment->getPayPalForm($transaction);
                        break;
                    case 'cc':
                        $transaction = $transactionModel->createRow();
                        $transaction->account_id = $userId;
                        $transaction->amount = SUBSCRIPTION_COST;
                        $transaction->added = new Zend_Db_Expr('NOW()');
                        $transaction->paytype = 'cc';
                        $transaction->save();
                        
                        $subscriptionId = $payment->ccQuery($transaction, $account, $cc);
                        if ($subscriptionId) {
                            $account->subscription_id = $subscriptionId;
                            $account->subscription_check = new Zend_Db_Expr('NOW()');
                            $account->agreed = 1;
                            $account->agreed_at = new Zend_Db_Expr('NOW()');
                            $account->save();
                            
							if(isset($_SESSION['returnUrl']))
							unset($_SESSION['returnUrl']); 
                            $this->_redirect('/');
                        } else {
                            $this->view->ccError = true;
                        }
                        
                        break;
                }
            }
        }
        
        $this->view->form = $form;
        $this->view->account = $account;
    }
    
	public function confirmAction() {
		$request = $this->getRequest();
		$user_id = $request->getParam('u');
		$hash_sum = $request->getParam('hs');
		
		$error_message = 'Could not confirm sign up proces';
		
		if($user_id!='' && $hash_sum!='')
		{
			$model = new Application_Model_Registration();
			if($model->setConfirmSignUp((int)$user_id, $hash_sum))
			{
				$auth = Zend_Auth::getInstance();
				$auth->getStorage()->write($user_id);
				$this->_redirect('/profile/step2/'); 
				//$this->_redirect('/');
			}
			else {
				$this->view->assign('error_message',$error_message);
			}
		}
		else {
			$this->view->assign('error_message',$error_message) ;
		}
	}
	public function registerAction()
    {
        //var_dump($_SESSION);
		Zend_Session::start();
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if ($userId=='') {
            $this->_redirect('/');
        }
		
		//30.01.2014 If login with social platforms and password has been already introduced previously, don't show Password update form
        $modelFromGeneral = new Application_Model_General();
        $account_info = $modelFromGeneral->getUserById($userId);
        if(!empty($account_info["password"]) && strlen($account_info["password"]) > 0){
            if(isset($_SESSION['returnUrl']))
                unset($_SESSION['returnUrl']);
            $this->_redirect('/');
        }
        $db = Zend_Registry::get('db');
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $this->view->assign('CurrController', $controller);
        $this->view->assign('CurrAction', $action);
        $model = new Application_Model_Registration();
        $form = new Application_Model_RegisterForm();
		$form->RegisterFrom('');
        $modelFromIndex = new Application_Model_Index();
        $modelFromGeneral = new Application_Model_General();
        $Platforms = $modelFromIndex->getPlatforms();
        $this->view->assign('Platforms', $Platforms);

        //var_dump($_SESSION);

        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
			$formData['uid'] = $userId;
            if ($form->isValid($formData)) {
                $result_of_insertion = $model->updateEmail($formData);

                if ($result_of_insertion['insertResult'] == 2) {
                    $this->view->message = '<span class="f_error_msg">Such email was already registered</span>';
                    $form->populate($formData);
                } elseif ($result_of_insertion['insertResult'] == 1) {
                   
				  		$paymentpagesetting = new Application_Model_DbTable_Paymentpagesetting();
						$paymentPageSetting = $paymentpagesetting->getDetails(array('active'),'1'); 
				    // authorize a user
                        $link_to_confirm = 'http://searchfreelancejobs.com/registration/confirm/u/' . $result_of_insertion['user_id'].'/hs/'.$result_of_insertion['insertHashSum'];
                        												
						
						$config = array('auth' => 'login',
										'username' => 'support@searchfreelancejobs.com',
										'password' => '123qwe');
						$transport = new Zend_Mail_Transport_Smtp('mail.searchfreelancejobs.com', $config);		
 								
                        $mail = new Zend_Mail();
                        $mail->setFrom('support@SearchFreelanceJobs.com', 'SearchFreelanceJobs.com');
                        $mail->setSubject('Please confirm your SearchFreelanceJobs.com Account');
                        $mail->setBodyText("Welcome to the SearchFreelanceJobs.com family! Please click the link below or copy/paste into your browser, to confirm your new SearchFreelanceJobs.com account:\n\n$link_to_confirm\n\nWith love,\nThe SearchFreelanceJobs.com Team");
                        $mail->addTo($formData['email']);						
                        $mail->send($transport);						
                        if($paymentPageSetting['active']==1)
						{
							$_SESSION['returnUrl'] = '/registration/step2';
                       		$this->_helper->redirector('step2');
						}
						else
						{
						
							if(isset($_SESSION['returnUrl']))
							unset($_SESSION['returnUrl']);
							$this->_redirect('/');
						}
                    }
            } else {
                $form->populate($formData);
            }
        }
        
        $this->view->form = $form;
    }
	public function updatepassAction()
    {
		Zend_Session::start();
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();

        if ($userId=='') {
            $this->_redirect('/');
        }

        //30.01.2014 If login with social platforms and password has been already introduced previously, don't show Password update form
        $modelFromGeneral = new Application_Model_General();
        $account_info = $modelFromGeneral->getUserById($userId);
        if(!empty($account_info["password"]) && strlen($account_info["password"]) > 0){
            if(isset($_SESSION['returnUrl']))
                unset($_SESSION['returnUrl']);
            $this->_redirect('/');
        }

        $db = Zend_Registry::get('db');
        $controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $this->view->assign('CurrController', $controller);
        $this->view->assign('CurrAction', $action);
        $model = new Application_Model_Registration();
        $form = new Application_Model_RegisterForm();
		$form->passwordForm('');
        $modelFromIndex = new Application_Model_Index();

        $Platforms = $modelFromIndex->getPlatforms();
        $this->view->assign('Platforms', $Platforms);
        if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
			$formData['uid'] = $userId;

            if ($form->isValid($formData)) {
                $result_of_insertion = $model->updatePassword($formData);
                $paymentpagesetting = new Application_Model_DbTable_Paymentpagesetting();
                $paymentPageSetting = $paymentpagesetting->getDetails(array('active'),'1');
                if($paymentPageSetting['active']==1)
                {
                    $_SESSION['returnUrl'] = '/registration/step2';
                    $this->_helper->redirector('step2');
                }
                else
                {

                    if(isset($_SESSION['returnUrl']))
                    unset($_SESSION['returnUrl']);
                    $this->_redirect('/');
                }
                    
            } else {
                $form->populate($formData);
            }
        }
        
        $this->view->form = $form;
    }
	
}
?>