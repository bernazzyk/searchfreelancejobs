<?php

class IndexController extends Zend_Controller_Action
{

    public function init()
    {
		$this->view->headScript()->appendFile( '/media/js/global/db_sliders.js' );
		$messages = $this->_helper->flashMessenger->getMessages();
		Zend_Session::start();
		//unset($_SESSION['returnUrl']);
		if(isset($_SESSION['returnUrl']) && $_SESSION['returnUrl']!='')
		{
			
			
			$this->_redirect($_SESSION['returnUrl']);
		}
		
		//print_r($messages); 
    }

	public function step3Action()
	{
		Zend_Session::start();
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();

        $modelFromGeneral = new Application_Model_General();

        $cftl =  $modelFromGeneral->checkIfFirstTimeLogin($user_id);

        /*if(!$cftl){
            $this->_helper->redirector->gotoUrl('/');
            //$this->_redirect('/');
        }*/

		if($user_id != 0){
			$modelFromGeneral->setFirstTimeLogin($user_id);
		}
		else {
			$this->view->message_not_logged = '<span class="f_error_msg">You should Sign In in order to view this page</span>';
		}
		
		$this->_forward('index', null, null, array('step3' => true));
	}
	
    public function indexAction()
    {
        $model = new Application_Model_Index();
        $modelFromGeneral = new Application_Model_General(); 
		$projectsTable = new Application_Model_DbTable_Projects();
		$projectFromEachPlatforms =  $projectsTable->getLatestProjectFromEachPlatforms();
		foreach($projectFromEachPlatforms as $projectId)
		{
			$projectIdArray[] = $projectId['maxId'];
		}
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
		 $userId = (int)$authStorage->read();
		 $this->view->userId = $userId;
        // $this->view->isNewTab = null !== strpos($_SERVER['HTTP_USER_AGENT'], 'Safari');
		$paymentpagesetting = new Application_Model_DbTable_Paymentpagesetting();
		$platformHeaderShow  = $paymentpagesetting->getDetails(array('active'),'2');
		if($platformHeaderShow['active'])
		{
			$this->view->isNewTab = 0; 
		}
		else 
		{
			$this->view->isNewTab = 1; 
		}
         $request = $this->getRequest();
		 $uid = (int)$request->getParam('u');
		if($userId!=0 && $uid!='')
		 {
		 	
		 	 
			 $userRow = $modelFromGeneral->getUserById($uid);
			 if($userRow['fname']!='' || $userRow['lname']!='')
			 {
			 	$uname = $userRow['fname'].' '.$userRow['lname'];
			 }
			 if($uname=='')
			 {
			 	$uname =$userRow['fname'];
			 }
			 $this->view->uid = $uid;
			 $this->view->uname = $uname;
		 }
        $modelFromPagination = new Application_Model_Pagination();
        
        $From = $modelFromPagination->getFrom(1);
        $PerPage = $modelFromPagination->getPerPage();
        
        $this->view->assign('From', $From); 
        $this->view->assign('PerPage', $PerPage);
        
        $categories = $request->getParam('c');
        $categories_get = $categories ? '/c/' . $categories : '';
        
        $search = $request->getParam('q');
        
        $categories_array = $model->parseGetParamInArray($categories, 'x');
        $platforms = $request->getParam('platform');
        $platforms_array = $model->parseGetParamInArray($platforms, 'x');        
        $job_type = $request->getParam('jtype');
        
        $price_limits_hourly = $request->getParam('pricehourly');
        $price_limits_hourly_array = $model->parseGetParamInArray($price_limits_hourly, 'x');
        
        $price_limits_fixed = $request->getParam('pricefixed');
        $price_limits_fixed_array = $model->parseGetParamInArray($price_limits_fixed, 'x');
        
        $time_left = $request->getParam('timeleft');
        $posted_date_days = $request->getParam('dapd');
        $tag = $request->getParam('tag');
        
        $this->view->categories = $categories;
        $this->view->categories_array = $categories_array;
        $this->view->search = $search;
        $this->view->platforms = $platforms;
        $this->view->platforms_array = $platforms_array;
        $this->view->price_limits_hourly = $price_limits_hourly;
        $this->view->price_limits_fixed = $price_limits_fixed;
        $this->view->timeleft = $time_left;
        $this->view->posted_date_days = $posted_date_days;
        $this->view->step3 = $this->_getParam('step3');
        $this->view->is_main_page = true;
        $search = $request->getParam('q');
        $sql_limit = ' LIMIT 25';
        $ProjectsList = $model->projects($categories_array,$tag,$price_limits_hourly_array,$price_limits_fixed_array,$time_left,$posted_date_days,$platforms_array,$job_type,$search, $sql_limit, false,NULL,$projectIdArray);
        $this->view->ProjectsList = $ProjectsList;
        
        $platformModel = new Application_Model_DbTable_Platforms();
        $platformsAssoc = $platformModel->getAssoc();
        
        $projectModel = new Application_Model_DbTable_Projects();
        $projectRows = array();
        foreach ($ProjectsList as $project) {
            $projectRows[$project['id']] = $projectModel->createRow($project);
            $projectRows[$project['id']]->setBidUrlTemplate($platformsAssoc[$project['platform_id']]['bid_url']);
        }
        $this->view->projectRows = $projectRows;
        
        $CurrenciesArray = array(1=>'$',2=>'€',3=>'£');
        
        $this->view->CurrenciesArray = $CurrenciesArray;
        
        $NrOfProjects = $model->projects($categories_array,$tag,$price_limits_hourly_array,$price_limits_fixed_array,$time_left,$posted_date_days,$platforms_array,$job_type,$search, $sql_limit, true,NULL,NULL);
        $this->view->NrOfProjects = $NrOfProjects;
        
        $page = $request->getParam('p');
        $CurrentPage = $modelFromPagination->getCurrentPage($page);
        
        $url_part_param = $modelFromGeneral->getGetParamsNoP();
        
        $total_pages = $modelFromPagination->countPages($NrOfProjects, $PerPage);
        
        $Pager = $modelFromPagination->showPagination($total_pages, $CurrentPage, '/projects/index',$url_part_param) ;
        $this->view->Pager = $Pager;
        
        $ProjectsListTable = $this->view->render('index/projectslist.phtml');
        $this->view->ProjectsListTable = $ProjectsListTable;
        
        $ActiveCategories = $model->getActiveCategories();
        $this->view->ActiveCategories = $ActiveCategories;
        
        $Platforms = $model->getPlatforms();
        $this->view->Platforms = $Platforms;
        
		$subCatTable = new Application_Model_DbTable_Freelancerscategories();
		$subsCat = $subCatTable->countUserSubscribeCategory($userId);
		$this->view->subsCat = $subsCat;
        $BlockOfAdvancedSearch = $this->view->render('index/filtersearch.phtml');
        $this->view->BlockOfAdvancedSearch = $BlockOfAdvancedSearch;
        
        $projectIds = array();
        foreach ($ProjectsList as $project) {
            $projectIds[] = $project['id'];
        }
        $projectFiles = $modelFromGeneral->extractFiles($projectIds);
        $this->view->projectFiles = $projectFiles;
        
		$db = $this->_getParam('db');

		if(isset($_SERVER['HTTP_REFERER']))
		{
			$referer = $_SERVER['HTTP_REFERER'];
			$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			if(substr($referer,-strlen($curent_url))!==$curent_url)
			{
				$session = new Zend_Session_Namespace('redirection');
				$session->redirection = $referer;
			}
		}
	 
        $loginForm = new Application_Model_LoginForm();

     
        if ($loginForm->isValid($_POST)) {
            $adapter = new Zend_Auth_Adapter_DbTable(
                $db,
                'accounts',
                'name',
                'password'
            );
     
            $adapter->setIdentity($loginForm->getValue('username'));
			//$password = md5(md5($UserInfo['password']) . 'dfd67fbcf54d99ef2dc2f900610255e4');
            $adapter->setCredential(md5(md5($loginForm->getValue('password')). 'dfd67fbcf54d99ef2dc2f900610255e4') );
               
            $auth   = Zend_Auth::getInstance();
            $result = $auth->authenticate($adapter);
 
            if ($result->isValid()) {
                $this->_helper->FlashMessenger('<span class="f_success_msg">Successful Login</span>');

				$redirection = '/';
				
				$session = new Zend_Session_Namespace('redirection');
				if (isset($session->redirection)) {
					$redirection = $session->redirection;
					unset($session->redirection);
				}
				
				$this->_redirect($redirection);
                return;
            } else {
				$this->view->message = '<span class="f_error_msg">Username or password is wrong</span>';
			}
        }
		
		$this->view->assign('loginForm', $loginForm);
    }
    
    public function ajaxdetailedAction()
    {
        $this->_helper->layout->disableLayout();
        
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $this->view->userId = (int)$authStorage->read();
        
        $this->view->isNewTab = null !== strpos($_SERVER['HTTP_USER_AGENT'], 'Safari');
        
		$request = $this->getRequest();
		$project_id = $request->getParam('projectid');
		
		$projectModel = new Application_Model_DbTable_Projects();
		
		$model = new Application_Model_Index(); 
		$Project = $model->ajaxDetailedProject($project_id);
		$this->view->assign('Project', $Project);
		
		$this->view->projectRow = $projectModel->createRow($Project);
		
		$modelTimeOutput = new Application_Model_TimeOutput();

		$date_posted = $modelTimeOutput->elapsed_time(strtotime($Project['posted']),2);
		$this->view->assign('date_posted', $date_posted);
		
		if($Project['ends']!='0000-00-00 00:00:00')
		{
			$time_left = $modelTimeOutput->time_left(strtotime($Project['ends']), 2);
		} else {
			$time_left = 'N/A'; 
		}
		$this->view->assign('time_left', $time_left);
		
		$modelFromGeneral = new Application_Model_General();
		$ProjectTags = $modelFromGeneral->getProjectTags($project_id);
		$this->view->assign('ProjectTags', $ProjectTags);
		$ProjectFiles = $modelFromGeneral->extractProjectsFiles($project_id);
		if($ProjectFiles)
		{		
			$this->view->assign('ProjectFiles',$ProjectFiles);
		}
		//$CurrenciesArray = $modelFromGeneral->getCurrenciesArray();
		$CurrenciesArray = array(1=>'$',2=>'€',3=>'£');
		$this->view->assign('CurrenciesArray', $CurrenciesArray);
		
		$this->view->backUrl = urlencode($_SERVER['HTTP_REFERER']);
	}
	
	public function proposalsAction()
	{
		$this->_helper->layout->setLayout('ajaxlayout');	
	}
	
	public function sendProposalAction(){
		print_r($_POST);
		
	}
	
	public function registrationAction() {
	
		/*$messages = $this->_helper->flashMessenger->getMessages();
		if(isset($messages[0]))
		{
			$this->view->message = $messages[0];
		}*/
	
		$model = new Application_Model_Index();

		$form = new Application_Model_RegisterUser();
        
		if ($this->_request->isPost()) {

            $formData = $this->_request->getPost();

            if ($form->isValid($formData)) {
				$result_of_insertion = $model->insertUser($formData);
				if($result_of_insertion==2)
				{
					//echo 'success';
					$flashMessenger = $this->_helper->getHelper('FlashMessenger');
					$flashMessenger->addMessage('Such email was already registered');
					
					$mess_arr = $flashMessenger->getMessages();
					$this->view->message = $mess_arr[0];
					
					$form->populate($formData);
				}
				else if($result_of_insertion == 1){
					$flashMessenger = $this->_helper->getHelper('FlashMessenger');
					$flashMessenger->addMessage('You was registered with succcess');
					
					$mess_arr = $flashMessenger->getMessages();
					$this->view->message = $mess_arr[0];
					
					//this->_redirector->gotoUrl('/my-controller/my-action/param1/test/param2');
					
					$this->_helper->redirector->gotoUrl('/index/registration/');
				}
              
            } else {

                $form->populate($formData);
            }
        }
		
		$this->view->form = $form;
	}
	
	/*public function insertaAction()
	{
		//$model = new Application_Model_Index(); 
		//$model->inserta();
		
		$sql_stmt = 'INSERT INTO `project_categories`(project_id, category_id) VALUES';
		
		srand();
		
		
		for($i=1;$i<1780;$i++)
		{
			$category_id = (rand()%10) + 1;
			$sql_stmt .= "($i, $category_id), ";
		}
		$sql_stmt = rtrim ($sql_stmt, ',');
		
		
		print $sql_stmt;
		
	}*/
	public function subscribecategoryAction()
	{
		$auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
		if ($this->_request->isPost()) {
        	$formData = $this->_request->getPost();
			if(!empty($formData['category']) && $userId!='')
			{
				$subCatTable = new Application_Model_DbTable_Freelancerscategories();
				if($subCatTable->saveSubscribeCategory($formData['category'],$userId)) {
				$flashMessenger = $this->_helper->getHelper('FlashMessenger');
				$flashMessenger->addMessage('You Successfully subscribe the jobs category ');	
				$mess_arr = $flashMessenger->getMessages();
				$this->view->message = $mess_arr[0];
				if($formData['returnback']!='')
				{
					$this->_redirect($formData['returnback']);
				}
			 }

			}	
		}
		//this->_redirector->gotoUrl('/my-controller/my-action/param1/test/param2');					
		$this->_helper->redirector->gotoUrl('/');
	
	}
	
	
}

