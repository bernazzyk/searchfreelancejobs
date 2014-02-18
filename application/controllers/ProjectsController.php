<?php

class ProjectsController extends Zend_Controller_Action
{

    public function init()
    {
		$this->view->headScript()->appendFile( '/media/js/global/db_sliders.js' );
        /* Initialize action controller here */
		$messages = $this->_helper->flashMessenger->getMessages();
		if(isset($messages[0]))
		{
			$this->view->message = $messages[0];
		}
    }
    
    public function detailAction()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $this->view->userId = (int)$authStorage->read();
        
        $projectUrl = trim($this->_getParam('project'));
        if (!$projectUrl) {
            throw new Zend_Controller_Action_Exception('Project url is not defined');
        }
        
        $projectModel = new Application_Model_DbTable_Projects();
        $project = $projectModel->fetchRow(array('url = ?' => $projectUrl));
        if (null === $project) {
            throw new Zend_Controller_Action_Exception('Project is not found');
        }
        $Project = $project->toArray();
        
        $CurrenciesArray = array(1=>'$',2=>'€',3=>'£');	
        $this->view->assign('CurrenciesArray', $CurrenciesArray);
        
        $model = new Application_Model_Index(); 
        $Project = $model->ajaxDetailedProject($Project['id']);
        
        $this->view->assign('Project', $Project);
        
        $modelTimeOutput = new Application_Model_TimeOutput();
        
        $date_posted = $modelTimeOutput->elapsed_time(strtotime($Project['posted']),2);
        $this->view->assign('date_posted', $date_posted);
        
        $time_left = $modelTimeOutput->time_left(strtotime($Project['ends']), 2);
        $this->view->assign('time_left', $time_left);
        
        $this->view->assign('no_apply_button', true);
        
        $ProjectDetailBlock = $this->view->render('index/ajaxdetailed.phtml');
        $this->view->assign('ProjectDetailBlock', $ProjectDetailBlock);
        
        $this->view->disableLoginForm = true;
    }
    
	public function parseGetParamInArray($get_param, $delimiter)
	{
		$tokenize_category = strtok($get_param, $delimiter);
		$parsed_array = array();
	
		$parsed_array = array();
		$i = 0;
		while ($tokenize_category !== false) 
		{
			if(is_numeric($tokenize_category))
			{
				$parsed_array[$i] = $tokenize_category;
			}
			
			$tokenize_category = strtok($delimiter);
			$i++;
		}
		return $parsed_array;
	}
    public function indexAction()
    {
		$modelFromIndex = new Application_Model_Index();
		$auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
		 $userId = (int)$authStorage->read();
		 $this->view->userId = $userId;

		 

	//	echo $form->render();
		
		
		/*******************************************************************************/
		$modelFromPagination = new Application_Model_Pagination();		

		/*$url_params = $this->getRequest()->getUserParams();
		print_r($url_params);*/
		
		$request = $this->getRequest();
		/*$diffArray = array(
			$request->getActionKey(),
			$request->getControllerKey(),
			$request->getModuleKey()
		);
		$params = array_diff_key(
			$request->getUserParams(),
			array_flip($diffArray)
		);
		
		//print_r($params);
		
		$url_part_param = '';
		foreach($params as $key=>$value)
		{	
			if($key!='p')
			{
				$value = str_replace(' ','+',$value);
				$url_part_param .= '/'.$key.'/'.$value;
				
			}
		}*/
		
		$modelFromGeneral = new Application_Model_General();
		$url_part_param = $modelFromGeneral->getGetParamsNoP();
		$this->view->assign('url_part_param', $url_part_param);
		
	//	print $url_part_param;
		
		$search = $request->getParam('q');
		//print $search; die;
		/*if(str_replace('+','',$search)=='')
		{
			$search = '';
		}*/
		
		$request = $this->getRequest();
		$page = $request->getParam('p');
		$CurrentPage = $modelFromPagination->getCurrentPage($page);
		
		$From = $modelFromPagination->getFrom($CurrentPage);
		$PerPage = $modelFromPagination->getPerPage();
		
		$this->view->assign('From', $From); 
		$this->view->assign('PerPage', $PerPage);
		
		$categories = $request->getParam('c');
		$categories_get = ($categories)? '/c/'.$categories : '';
		
		
		
		//print $categories_get;

		$categories_array = $modelFromIndex->parseGetParamInArray($categories, 'x');
		
		
		$platforms = $request->getParam('platform');
		$platforms_array = $modelFromIndex->parseGetParamInArray($platforms, 'x');
		
		$job_type = $request->getParam('jtype');
		
		$price_limits_hourly = $request->getParam('pricehourly');
		$price_limits_hourly_array = $modelFromIndex->parseGetParamInArray($price_limits_hourly, 'x');
		
		$price_limits_fixed = $request->getParam('pricefixed');
		$price_limits_fixed_array = $modelFromIndex->parseGetParamInArray($price_limits_fixed, 'x');
		
		//$price_limits_hourly = array();
		//$price_limits_fixed = array();
		
		$time_left = $request->getParam('timeleft');
		//$time_left = $request->getParam('timeleft');
		$posted_date_days = $request->getParam('dapd');
		
		$tag = $request->getParam('tag');
		if($tag!='')
		{
			$this->view->assign('ProjectBlockTitle', 'Projects for tag "'.$tag.'"');
		}
		
		//user to show project on top when came from the mail.
		$projectId = $request->getParam('pid');
		$this->view->assign('projectId', $projectId);
		
		$this->view->assign('categories', $categories);
		$this->view->assign('categories_array', $categories_array);
		$this->view->assign('platforms_array', $platforms_array);
		$this->view->assign('search', $search);
		$this->view->assign('platforms', $platforms);
		$this->view->assign('price_limits_hourly', $price_limits_hourly);
		$this->view->assign('price_limits_fixed', $price_limits_fixed);
		$this->view->assign('timeleft', $time_left);
		$this->view->assign('posted_date_days', $posted_date_days);
		$this->view->assign('show_view_all', false);
		
		//print_r($price_limits_hourly_array);
	
		/*******************************************************************************/
		
		$sql_limit = ' LIMIT ' . $From . ', ' . $PerPage;
		
		$modelFromIndex = new Application_Model_Index();
		//$NrOfProjects = $modelFromIndex->NrOfProjects($categories_array, $price_limits_hourly_array, $time_left, $posted_date_days);
		$NrOfProjects = $modelFromIndex->projects($categories_array,$tag,$price_limits_hourly_array,$price_limits_fixed_array,$time_left,$posted_date_days,$platforms_array,$job_type,$search, $sql_limit, true,$projectId);
		$this->view->assign('NrOfProjects', $NrOfProjects); 
		
		$ProjectsList = $modelFromIndex->projects($categories_array,$tag,$price_limits_hourly_array,$price_limits_fixed_array,$time_left,$posted_date_days,$platforms_array,$job_type,$search, $sql_limit, false,$projectId);
		$this->view->assign('ProjectsList', $ProjectsList);
		
		
		
		  $platformModel = new Application_Model_DbTable_Platforms();
        $platformsAssoc = $platformModel->getAssoc();
        
        $projectModel = new Application_Model_DbTable_Projects();
        $projectRows = array();
        foreach ($ProjectsList as $project) {
            $projectRows[$project['id']] = $projectModel->createRow($project);
            $projectRows[$project['id']]->setBidUrlTemplate($platformsAssoc[$project['platform_id']]['bid_url']);
        }
        $this->view->projectRows = $projectRows;
		//$modelFromIndex->insertTags($ProjectsList);
		
		//print_r($ProjectsList);
		
		//$this->view->assign('categories_array', $categories_array);
		
		//$CurrenciesArray = $modelFromGeneral->getCurrenciesArray();
		$CurrenciesArray = array(1=>'$',2=>'€',3=>'£');	
		
		$this->view->assign('CurrenciesArray', $CurrenciesArray);
		
		$ActiveCategories = $modelFromIndex->getActiveCategories();
		$this->view->assign('ActiveCategories', $ActiveCategories);
		
		
		$BlockOfCategories = $this->view->render('index/activecategories.phtml');
		$this->view->assign('BlockOfCategories', $BlockOfCategories);
		
		$Platforms = $modelFromIndex->getPlatforms();
		$this->view->assign('Platforms', $Platforms);
		
		/*$FormFilter = $this->view->render('index/filter.phtml');
		print $FormFilter;*/
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
		 
		
		$total_pages = $modelFromPagination->countPages($NrOfProjects, $PerPage);
	
		$model = new Application_Model_Projects();

		$Pager = $modelFromPagination->showPagination($total_pages, $CurrentPage, '/projects/index',$url_part_param) ;
		$this->view->assign('Pager', $Pager);
		
		$ProjectsListTable = $this->view->render('index/projectslist.phtml');
		$this->view->assign('ProjectsListTable', $ProjectsListTable);
		
		if ($this->getRequest()->isXmlHttpRequest()) {
		    $this->_helper->layout->disableLayout();
		} else {
		
			$subCatTable = new Application_Model_DbTable_Freelancerscategories();
		$subsCat = $subCatTable->countUserSubscribeCategory($userId);
		$this->view->subsCat = $subsCat;
		    $BlockOfAdvancedSearch = $this->view->render('index/filtersearch.phtml');
		    $this->view->assign('BlockOfAdvancedSearch', $BlockOfAdvancedSearch);
		}
    }

    public function recentAction()
    {
        $model = new Application_Model_Projects();
        
        $this->view->projects = $model->recent();
        
        // action body
    }

    public function categoriesAction()
    {
        // action body
    }

	public function filterAction()
	{
		
		if ($this->_request->isPost()) {

            $formData = $this->_request->getPost();
			
			$url_to_redirect='';
			
			$url_parameters='';
			
			if(isset($formData['category']))
			{
				$url_parameters .='/c/';
				foreach($formData['category'] as $key=>$value)
				{
					$url_parameters .= $key.'x';
				}
				$url_parameters = rtrim($url_parameters,'x');
			}
			
			if(isset($formData['platform']))
			{
				$url_parameters .='/platform/';
				foreach($formData['platform'] as $key=>$value)
				{
					$url_parameters .= $key.'x';
				}
				$url_parameters = rtrim($url_parameters,'x');
			}
			if(isset($formData['time_left']) && $formData['time_left'] !='')
			{
				$url_parameters .='/timeleft/'.$formData['time_left'];
			}
			
			if(isset($formData['posted_date']) && $formData['posted_date'] !='')
			{
				if($formData['posted_date'] != 'between')
				{
					$url_parameters .='/dapd/'.$formData['posted_date'];
				} else {
					$posted_start = '';
					$posted_end = '';
					if(isset($formData['posted_start']) && $formData['posted_start']!='')
					{
						$posted_start = str_replace('-', 'v', $formData['posted_start']);
						//$url_parameters .='/dapd/'.$posted_start.'x';
					}
					if(isset($formData['posted_end']) && $formData['posted_end']!='')
					{
						$posted_end = str_replace('-', 'v', $formData['posted_end']);
						//$url_parameters .='/dapd/'.$posted_start.'x';
					}
					if($posted_start || $posted_end)
					{
						$url_parameters .='/dapd/'.$posted_start.'x'.$posted_end;
					}
					
					//print $formData['posted_start']; //2012-11-20
					
				}
			}
			
			if(isset($formData['chk_price_hourly']))
			{
				$url_parameters .='/pricehourly/'.(int)$formData['price_min'].'x'.(int)$formData['price_max'];
			}
			
			if(isset($formData['chk_price_fixed']))
			{
				$url_parameters .='/pricefixed/'.(int)$formData['price_min_fixed'].'x'.(int)$formData['price_max_fixed'];
			}
			
			//print $formData['chk_price_hourly']; die('hwer');
			
			/*
			if($formData['price_min'] && $formData['price_max'])
			{
				$url_parameters .='/pricehourly/'.(int)$formData['price_min'].'x'.(int)$formData['price_max'];
			}
			
			if($formData['price_min_fixed'] && $formData['price_max_fixed'])
			{
				$url_parameters .='/pricefixed/'.(int)$formData['price_min_fixed'].'x'.(int)$formData['price_max_fixed'];
			}*/
			
			if($formData['keywords'])
			{
				$keywords = preg_replace('!\s+!', ' ', $formData['keywords']);
				$keywords = str_replace(' ', '+', $keywords);
				$url_parameters .='/q/' . $keywords;
				//$url_parameters .='/price/'.(int)$formData['price_min'].'x'.(int)$formData['price_max'];
			}
			
			$this->_helper->redirector->gotoUrl('/projects/index' . $url_parameters);
			
			
			/*print $url_parameters;
			
			
			print_r($formData['category']);
			foreach($formData['category'] as $key=>$value)
			{
				print $key.'-'.$value;
			}
			
			print_r( $formData);*/
			//var_dump($formData);
		}
		
	}
    
    public function clearAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $projectModel = new Application_Model_DbTable_Projects();
        $projectModel->clearOld();
    }
    
}