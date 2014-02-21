<?php
/**
 * the payment operations controller
 * @author Sergey Mitroshin <sergeymitr@gmail.com>
 */
class PaymentController extends Zend_Controller_Action
{
    
    public function checkSubscriptionAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $accountModel = new Application_Model_DbTable_Accounts();
        $paymentModel = new Application_Model_Payment();
        
        for ($i = 0; $i < 10; $i++) {
            $account = $accountModel->getNext();
            if (null === $account) {
                break;
            }
            
            $result = $paymentModel->checkSubscription($account->subscription_id);
            $account->subscription_check = new Zend_Db_Expr('NOW()');
            if (!$result) {
                $account->agreed = 0;
            }
            $account->save();
        }
    }
    
    public function freeTrialAction()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        
        $accountModel = new Application_Model_DbTable_Accounts();
        
        for ($i = 0; $i < 50; $i++) {
            $account = $accountModel->getNotBilled();
            if (null === $account) {
                break;
            }
            
            $account->free_trial = 1;
            $account->free_trial_started = new Zend_Db_Expr('NOW()');
            $account->save();
            
            $account->sendTrialMail();
        }
    }
    
    public function subCancelAction()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
        
        $accountModel = new Application_Model_DbTable_Accounts();
        $paymentModel = new Application_Model_Payment();
        
        $account = $accountModel->find($userId)->current();
        if (null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        if ($this->_getParam('confirm')) {
            switch ($account->paytype) {
                case 'paypal':
                    $result = $paymentModel->cancelPayPal($account->paypal_subscription_id);
                    break;
                case 'cc':
                    $result = $paymentModel->cancelSubscription($account->subscription_id);
                    break;
            }
            if ($result) {
                $account->suspend();
                
                Zend_Session::forgetMe();
                Zend_Session::destroy();
                $this->_helper->redirector('canceled');
            } else {
                $this->view->error = true;
            }
        }
    }
    
    public function canceledAction()
    {
        
    }
    
    /**
     * the action will show the "Limit is exceeded" message
     */
    public function limitAction()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
        
        $accountModel = new Application_Model_DbTable_Accounts();
        
        $account = $accountModel->find($userId)->current();
        if (null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        if ($account->isTrial() || $account->isPaid()) {
            $this->_redirect('/');
        }
        
        $request = $this->getRequest();
        $form = new Application_Model_PaymentForm();
        
        $payment = new Application_Model_Payment();
        $transactionModel = new Application_Model_DbTable_Transactions();
        
        if ($request->isPost()) {
            switch ($account->paytype) {
                case 'paypal':
                    $transaction = $transactionModel->createRow();
                    $transaction->account_id = $userId;
                    $transaction->amount = SUBSCRIPTION_COST;
                    $transaction->added = new Zend_Db_Expr('NOW()');
                    $transaction->paytype = 'paypal';
                    $transaction->save();
                    
                    $this->view->sendForm = $payment->getPayPalForm($transaction);
                    break;
                case 'cc':
                    $transaction = $transactionModel->createRow();
                    $transaction->account_id = $userId;
                    $transaction->amount = SUBSCRIPTION_COST;
                    $transaction->added = new Zend_Db_Expr('NOW()');
                    $transaction->paytype = 'cc';
                    $transaction->save();
                    
                    $this->view->sendForm = $payment->getPayPalForm($transaction);
                    break;
            }
            
            /*if ($form->isValid($request->getPost())) {
                $account->setFromArray($form->getValues());
                $account->setCC(substr($form->cc->getValue, -4));
                $account->save();
                
                $soap = new Zend_Soap_Client('https://sandbox.fidelipay.com/soap/gate/3213EA2A/fidelipay.wsdl');
            }*/
        } else {
            //$form->populate($account->toArray());
        }
        
        $this->view->form = $form;
        $this->view->account = $account;
    }
    
    public function cancelAction()
    {
         
		 
    }
    
    public function successAction()
    {
		Zend_Session::start();
		if(isset($_SESSION['returnUrl']))
		unset($_SESSION['returnUrl']); 	
		//$this->_redirect('/');
    }
	public function successupgradeAction()
	{
	
		$auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
		/*
		$request = $this->getRequest();
		$item_no            =$request->getParam('item_number');
		$item_transaction   = $request->getParam('tx'); // Paypal transaction ID
		$item_price         = $request->getParam('amt');// Paypal received amount
		$item_currency      = $request->getParam('cc'); // Paypal received currency type
		$price = UPGRADE_COST;
		$currency='USD';
		//Rechecking the product price and currency details
		if($item_price==$price && $item_currency==$currency)
		{ */
			$payment = new Application_Model_Payment();
			$transactionModel = new Application_Model_DbTable_Transactions();
			$transaction = $transactionModel->createRow();
			$transaction->account_id = $userId;
			$transaction->amount = UPGRADE_COST;
			$transaction->added = new Zend_Db_Expr('NOW()');
			$transaction->paytype = 'paypal';
			$transaction->payment_status = 'Y';
			$transaction->expiry_date = date('Y-m-d H:i:s', strtotime("+30 days"));
			$transaction->save();
			$this->_redirect('/payment/success');
		
	}
    
    public function resultPaypalAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $payment = new Application_Model_Payment();
        $transactionModel = new Application_Model_DbTable_Transactions();
        $accountModel = new Application_Model_DbTable_Accounts();
        
        $request = $this->getRequest();
        
        if ($payment->validatePayPal()) {
            switch ($request->getPost('txn_type')) {
                case 'subscr_signup':
                    if (($id = (int)$request->getPost('item_number'))
                        && ($subscriptionId = trim($request->getPost('subscr_id')))
                    ) {
                        $transaction = $transactionModel->find($id)->current();
                        $account = $accountModel->find($transaction->account_id)->current();
                        $account->paypal_subscription_id = $subscriptionId;
                        $account->agreed = 1;
                        $account->save();
                    }
                    break;
                case 'subscr_payment':
                    if ($subscriptionId = (int)$request->getPost('subscr_id')) {
                        $account = $accountModel->fetchRow(array('paypal_subscription_id = ?' => $subscriptionId));
                        if (null !== $account) {
                            $account->paid_date = date('Y-m-d H:i:s');
                            $account->save();
                        }
                    }
                    break;
                case 'subscr_cancel':
                    if ($subscriptionId = (int)$request->getPost('subscr_id')) {
                        $account = $accountModel->fetchRow(array('paypal_subscription_id = ?' => $subscriptionId));
                        if (null !== $account) {
                            $account->suspend();
                        }
                    }
                    break;
            }
        }
        
        $data = print_r($_GET, true) . "\n" . print_r($_POST, true);
        file_put_contents(APPLICATION_PATH . '/../logs/paypal.log', $data, FILE_APPEND);
        die('result');
    }
	/*
    public function testAction()
	{
		$payment = new Application_Model_Payment();
			$transactionModel = new Application_Model_DbTable_Transactions();
			$transaction = $transactionModel->createRow();
			$transaction->account_id = 1;
			$transaction->amount = 100;
			$transaction->added = new Zend_Db_Expr('NOW()');
			$transaction->paytype = 'paypal';
			$transaction->payment_status = 'N';
			$transaction->expiry_date = date('Y-m-d H:i:s', strtotime("+30 days"));
			$transaction->save();
	}
	*/
}