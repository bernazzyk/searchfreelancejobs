<?php
class FreelancersController extends Zend_Controller_Action
{

	 public function init()
    {
		Zend_Session::start();
		if(isset($_SESSION['returnUrl']) && $_SESSION['returnUrl']!='')
		{
			
			$this->_redirect($_SESSION['returnUrl']);
		}
		
		//print_r($messages); 
    }
    
    public function indexAction()
    {
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		$this->view->user_id = $user_id;
		$controller = $this->getRequest()->getControllerName();
        $action = $this->getRequest()->getActionName();
        $this->view->assign('CurrController', $controller);
        $this->view->assign('CurrAction', $action);
		
		$freelancerTable = new  Application_Model_DbTable_Freelancers();
		$transationTable = new  Application_Model_DbTable_Transactions();
		$searchArray = array();
		if ($this->_request->isPost()) {
         $formData = $this->_request->getPost();
		 $searchArray = $formData;
		 $this->view->sortBy = $searchArray['sortBy'];
		 }
		$page = $this->_request->getParam('page',1); 
        $freelancerRow = $freelancerTable->getFeaturedFreelance(array('*'),$searchArray,'','');
		$adapter = new Zend_Paginator_Adapter_DbSelect($freelancerRow); //adapter
   		$paginator = new Zend_Paginator($adapter); // setup Pagination
    	$paginator->setItemCountPerPage(12); // Items perpage, in this example is 10
    	$paginator->setCurrentPageNumber($page); // current page
    	$this->view->freelancerRow = $paginator;
		 if ($user_id != 0) {
		$this->view->isFeaturedUser = $transationTable->checkIsFeatured($user_id);
		}
		//$Pager = $modelFromPagination->showPagination($total_pages, $CurrentPage, '/projects/index',$url_part_param) ;
        //$this->view->Pager = $Pager;
		
		//$this->view->freelancerRow = $freelancerRow;
    }
	public function detailsAction()
    {
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		if ($user_id != 0) {
		$request = $this->getRequest();
        $freelancerId = (int) $request->getParam('id');
	    if($freelancerId!='') {
		$transationTable = new  Application_Model_DbTable_Transactions();
		if($transationTable->checkIsFeatured($freelancerId)!=0)
		{
				//$freelancerTable = new  Application_Model_DbTable_Freelancers();
				$photoTable = new Application_Model_DbTable_Freelancersphotos();
				$ccountTable = new Application_Model_DbTable_Accounts();
				$freelancerRow = $ccountTable->getFreelanceDetails(array('email','name','fname','lname'),$freelancerId);
				
				$photoRows = $photoTable->getUserPhotos($freelancerId,'8');
				$this->view->photoRows = $photoRows;
				$this->view->freelancerRow = $freelancerRow;
				$this->view->isFeaturedUser = $transationTable->checkIsFeatured($user_id);
				//echo "<pre>";
				//print_r($freelancerRow);die;
			}
			else
			{
				$this->_redirect('/freelancers/');
			}
	   
	   	}
		} 
		else 
		{
			$this->_redirect('/');
		}
	   
    }
	public function sendmessageAction()
	{
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
			$generalTable  = new Application_Model_General();
			$transationTable = new  Application_Model_DbTable_Transactions();
			$fullBaseUrl = $this->view->serverUrl() . $this->view->baseUrl();
			if($transationTable->checkIsFeatured($user_id)!=0)
			{
				$replyUrl = $fullBaseUrl.'/freelancers/replyfrommail/type/1/rid/'.$formData['account_id'].'/returnurl/'.base64_encode('/freelancers/details/id/'.$user_id);
			}
			else
			{
				$replyUrl = $fullBaseUrl.'/freelancers/replyfrommail/type/2/rid/'.$formData['account_id'].'/returnurl/'.base64_encode('/index/index/u/'.$user_id);
			}
			$userRow = $generalTable->getUserById($formData['account_id']);
			$pattern = "/[^@\s]*@[^@\s]*\.[^@\s]*/";
			$replacement = " ";
			$message = preg_replace($pattern, $replacement, $formData['message']);
			$email = $userRow['email'];
			$config = array('auth' => 'login',
			'username' => 'support@searchfreelancejobs.com',
			'password' => '123qwe');
			$transport = new Zend_Mail_Transport_Smtp('mail.searchfreelancejobs.com', $config);							
			$mail = new Zend_Mail();
			$mail->setFrom('no-reply@SearchFreelanceJobs.com', 'SearchFreelanceJobs.com');
			$mail->setSubject('From SearchFreelanceJobs');
		    $button = '<br><p><a href="'.$replyUrl.'">Reply now &gt;&gt;</a></p>';
			$mail->setBodyHtml($message.$button);
            $mail->addTo($userRow['email']);						
            $mail->send($transport);	
			echo 1;die;
		}	
			else
			{
				$this->_redirect('/freelancers/');
			}
	}
	public function replyfrommailAction()
	{
		Zend_Session::start();
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		$request = $this->getRequest();
        $returnurl = $request->getParam('returnurl');
		$type = $request->getParam('type');
		$rid = $request->getParam('rid');
		if ($user_id != 0 && $rid==$user_id) {
			if($type==1)
			{
				$this->_redirect(base64_decode($returnurl));
			}
			else
			{
				$this->_redirect(base64_decode($returnurl));
			}
		}
		else
		{
			if($type==1)
			{
				$_SESSION['rid'] = $rid;
				$_SESSION['msgReturnUrl'] = base64_decode($returnurl);
				$this->_redirect('/');
				
			}
			else 
			{
				$_SESSION['rid'] = $rid;
				$_SESSION['msgReturnUrl'] = base64_decode($returnurl);
				$this->_redirect('/');
			}
		}
		//echo base64_decode($returnurl);die; 
	}
    
}