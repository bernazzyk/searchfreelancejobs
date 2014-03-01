<?php

class ProfileController extends Zend_Controller_Action
{

    public function init()
    {
	
		Zend_Session::start();
		if(isset($_SESSION['returnUrl']) && $_SESSION['returnUrl']!='')
		{
			
			$this->_redirect($_SESSION['returnUrl']);
		}
	
		/*if(isset($_SESSION['connected_platform'][1]['is_connected'])&& !isset($_SESSION['connected_platform'][1]['profile']))
		{
			$this->getRemotePlatformProfile(1);
		}
		if(!isset($_SESSION['connected_platform'][3]['profile']) && isset($_SESSION['connected_platform'][3]['is_connected'] ) && $_SESSION['connected_platform'][3]['is_connected']=1 && isset($_SESSION['connected_platform'][3]['access_token']))
		{
			$this->getRemotePlatformProfile(3);
		}
		if(!isset($_SESSION['connected_platform'][4]['profile']) && isset($_SESSION['connected_platform'][4]['is_connected']) && $_SESSION['connected_platform'][4]['is_connected']=1 && isset($_SESSION['connected_platform'][4]['access_token']) && isset($_SESSION['connected_platform'][4]['access_token_secret']))
		{
			$this->getRemotePlatformProfile(4);
		}*/
    }
    
    /**
     * frameset for displaying "return to profile" link
     */
    public function backtoprofileAction()
    {
        $request = $this->getRequest();
        $platformId = $request->getParam('pl');
        $modelFromGeneral = new Application_Model_General();
        $platform = $platformId ? $modelFromGeneral->getPlatform($platformId) : null;
        $this->view->assign('platform', $platform);
        $this->_helper->layout->setLayout('ajaxlayout');
    }
    
	public function remoteregisterAction()
	{
		Zend_Session::start();
		
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		
		if($user_id != 0)
		{
		
			$request = $this->getRequest();
			$platform_id = $request->getParam('pl');
			$_SESSION['zend_platform_to_connect'] = $platform_id;

			$modelFromGeneral = new Application_Model_General();
			$Platform = $modelFromGeneral->getPlatform($platform_id);
			$this->view->assign('Platform',$Platform);
		}else {
			$this->view->message_not_logged = '<span class="f_error_msg">You should Sign In in order to view your profile</span>';		
		}
		
		$this->_helper->layout->setLayout('ajaxlayout');
	}
    
    public function disconnectAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
        
        $platformId = (int)$this->_getParam('pl');
        
        if (!$platformId) {
            throw new Zend_Controller_Action_Exception('Empty parameters');
        }
        
        $platformModel = new Application_Model_DbTable_Platforms();
        $accountModel = new Application_Model_DbTable_Accounts();
        
        $platform = $platformModel->find($platformId)->current();
        $account = $accountModel->find($userId)->current();
        if (null === $platform || null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        $account->removeConnection($platformId);
        
        $this->_redirect('/profile/my');
    }
    
	public function elanceinfoAction()
	{
		Zend_Session::start();
	
		$modelFromGeneral = new Application_Model_General(); 
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		if($user_id != 0)
		{
			if(isset($_SESSION['connected_platform'][3]['profile']['data']['providerProfile']))
			{
				//print_r($_SESSION['connected_platform'][3]['profile']['data']['providerProfile']);
				$this->view->feedback = $_SESSION['connected_platform'][3]['profile']['data']['providerProfile'];
			}
		}
		//die('her');
	}
	
	public function tAction()
	{
		include_once('HTMLParser/simple_html_dom.php');
		$html = str_get_html('http://www.guru.com/pro/Messages.aspx');
		print $html;
		die;
	}
	
	public function guruinfoAction()
	{
		Zend_Session::start();
		include_once('HTMLParser/simple_html_dom.php');
		$modelFromGeneral = new Application_Model_General(); 
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		if($user_id != 0)
		{
			if(isset($_SESSION['connected_platform'][5]['is_connected']))
			{
				$curl_url = 'http://www.guru.com/pro/Messages.aspx';
				
				$CurlFeedHtml = $this->getCurlFeedback($user_id,$curl_url);
				$html = str_get_html($CurlFeedHtml);
		
				$feedback = $html->find('#results',0);
				//print $feedback;
				if($feedback)
				{
					foreach($feedback->find('tr') as $tr)
					{
						$tr->find('td',0)->outertext = '';
						$tr->find('td',1)->find('div',3)->outertext = '';
						//$tr->find('td',1)->find('div',3)->outertext = '';
						$div_loctime = $tr->find('td',4)->find('div.loctime',0);
						$div_loctime->innertext =  $div_loctime->getAttribute('data-date'); //'est';
					
						$img = $tr->find('td',2)->find('div',0)->find('img',0);
						$img->setAttribute('src', 'http://www.guru.com'.$img->getAttribute('src'));
					}
					
					$this->view->feedback = $feedback;
				} else {
					$this->view->feedback = '<span class="fl_no_reviews">You did not recieve any feedback</span>';
				}
			//	print_r($UserPlatformData);
			//	die('here');
			}
		}
	}
	
	public function ifreelanceinfoAction()
	{
		Zend_Session::start();
		include_once('HTMLParser/simple_html_dom.php');
		$modelFromGeneral = new Application_Model_General(); 
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		$request = $this->getRequest();
		$action = $request->getParam('faction');
		if($user_id != 0)
		{
			if(isset($_SESSION['connected_platform'][9]['is_connected']))
			{
				switch($action)
				{
					case 'mymessages' : {
						$curl_url = 'http://www.ifreelance.com/my/messages/alerts.aspx';
						
						$CurlMessagesHtml = $this->getCurlFeedback($user_id,$curl_url);
						
						if($CurlMessagesHtml)
						{
							$html = str_get_html($CurlMessagesHtml);
							$this->view->messages = $html->find('div#main',0)->find('div.main-holder',0)->find('div.region1',0)->find('div',1);;
						}
						$Display = $this->view->render('profile/ifreelance_messages.phtml');
						$this->view->Display = $Display;
						break;
					}
				}
			}
		}
	}
	
	public function getacoderinfoAction()
	{
		Zend_Session::start();
		include_once('HTMLParser/simple_html_dom.php');
		$modelFromGeneral = new Application_Model_General(); 
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		$request = $this->getRequest();
		$action = $request->getParam('faction');
		if($user_id != 0)
		{
			if(isset($_SESSION['connected_platform'][6]['is_connected']))
			{
				switch($action)
				{
					case 'myfeedbacks' : 
					{
						$UserPlatformData = $modelFromGeneral->getUserPlatformData($user_id,6);
						$UserPlatformData['username'];
						//$curl_url = 'http://www.getacoder.com/users/'.$UserPlatformData['username'].'/'. $UserPlatformData['username'] .'_feedback.htm';
						$curl_url = 'http://www.getacoder.com/users/capobrid/capobrid_feedback.htm';
						
						$CurlFeedHtml = $this->getCurlFeedback($user_id,$curl_url);
						$html = str_get_html($CurlFeedHtml);
						print $html;
						die;
						$feedback = $html->find('#feedback_container',0);
						if($feedback)
						{
							foreach($feedback->find('a') as $a)
							{
								$a->removeAttribute('href');
							}
							$tbl_summary = $feedback->find('#tbl_feedback_summary',0);
							foreach($tbl_summary->find('img') as $img)
							{
								$img->setAttribute('src','/media/image/new_design/star.gif');
							}
							$tbl_feedback_data = $feedback->find('#tbl_feedback_data',0);
							foreach($tbl_feedback_data->find('img') as $img)
							{
								$img->setAttribute('src','/media/image/new_design/star.gif');
							}
							$this->view->feedback = $feedback;
						} else {
							$this->view->feedback = '<span class="fl_no_reviews">You did not recieve any feedback</span>';
						}
						$Display = $this->view->render('profile/getacoder_feedbacks.phtml');
						$this->view->Display = $Display;
						break;
					}
					case 'mymessages' : {
						$curl_url = 'http://www.getacoder.com/users/get_unread_message.php';
						
						$CurlFeedHtml = $this->getCurlFeedback($user_id,$curl_url);
			
						if($CurlFeedHtml)
						{
							$this->view->messages = $CurlFeedHtml;
						}
						$Display = $this->view->render('profile/getacoder_messages.phtml');
						$this->view->Display = $Display;
						break;
					}
				}

			}
		}
	}
	
	public function peopleperhourinfoAction()
	{
		Zend_Session::start();
		include_once('HTMLParser/simple_html_dom.php');
		$modelFromGeneral = new Application_Model_General(); 
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		$request = $this->getRequest();
		$action = $request->getParam('faction');
		if($user_id != 0)
		{
			if(isset($_SESSION['connected_platform'][8]['is_connected']))
			{
				switch($action)
				{
					case 'messages' : 
					{
						$curl_url = 'http://www.peopleperhour.com/stream/list';				
						$CurlFeedHtml = $this->getCurlFeedback($user_id,$curl_url);

						$html = str_get_html($CurlFeedHtml);
						
						$message = $html->find('#stream-list ul.items',0);
						if($message)
						{
							foreach ($message->find('li.item') as $li)
							{
								$aMemberLink = $li->find('a.pMemberLink',0);
								$aMemberLink->setAttribute('target','_blank');
								
								$amessage = $li->find('a.message',0);
								parse_str(parse_url($amessage->getAttribute('href'), PHP_URL_QUERY), $_MY_GET);
								
								$new_href = '/profile/peopleperhourinfo/faction/sendmessage/mid/' . $_MY_GET['id'];
								$amessage->setAttribute('href',$new_href);
								
								$li->find('.span-3',0)->find('.clear',0)->outertext = '';
								//$li->find('.span-3',0)->outertext = $li->find('.span-3',0)->makeup() . $li->find('.span-3',0)->innertext . '<span id="wer"></span>';
							}
							$this->view->message = '<div id="stream-list">'.$message.'</div>';
						} else {
							$this->view->message = '<span class="fl_no_reviews">You did not recieve any message</span>';
						}
						$Display = $this->view->render('profile/pph_messages.phtml');
						$this->view->Display = $Display;
						break;
					}
					case 'sendmessage' : {
						$message_id = $request->getParam('mid');
						if(isset($_POST['pph_send_mess_submit']))
						{
							$cookieFile = dirname(__FILE__).'/cookies/'.$user_id.'cookie.txt';
							$lines = file($cookieFile);

							foreach($lines as $key => $value)
							{
								if(strpos($value,'YII_CSRF_TOKEN')!==false)
								{
									$str_len = strlen($value);
									$pos = strpos($value,'YII_CSRF_TOKEN');
									$YII_CSRF_TOKEN = substr($value,$pos,($str_len-$pos-1));
									$YII_CSRF_TOKEN = str_replace("\t", '=', $YII_CSRF_TOKEN);
								}
							}
							
							//print $_POST['StreamMessage']['ackPolicy'];
							//print (int)$_POST['StreamMessage']['ackPolicy'];
						
							//die;
							
							$ackPolicy = (isset($_POST['StreamMessage']['ackPolicy']))? 1 : 0;
							
							$StreamMessage = array('txt'=>$_POST['StreamMessage']['txt'],'publish'=>0,'ackPolicy'=>$ackPolicy);
							$MY_POST_DATA = array('StreamMessage'=>$StreamMessage);
							
							$post_data = http_build_query($MY_POST_DATA);
							$post_data = $YII_CSRF_TOKEN . '&' . $post_data;
						
						//	print $post_data; die;
						
							$StatusExec = $this->CurlPlatformPost('http://www.peopleperhour.com/stream/createSimpleMessage?id='.$message_id.'&XDEBUG_SESSION_START=1',$post_data, $user_id,8);
							var_dump($StatusExec);
						}
						
						$Display = $this->view->render('profile/pph_send_message.phtml');
						$this->view->Display = $Display;
					}
				}
			}
		}
		
	}
	
	public function getCurlFeedback($user_id,$curl_url)
	{
		$cookieFile = dirname(__FILE__).'/cookies/'.$user_id.'cookie.txt';
			$Curl_Obj = curl_init();

			if(!file_exists( $cookieFile)) {
				$fh = fopen($cookieFile, "w");
				fwrite($fh, '');
				fclose($fh);
				chmod($cookieFile, 0777);
			}
			
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, $cookieFile); 
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, $cookieFile); 

			// Set the browser you will emulate
			//$userAgent = 'Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';
			$userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0';
			curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);

			// Don't include the header in the output.
			curl_setopt ($Curl_Obj, CURLOPT_HEADER, 0);

			// Allow referer field when following Location redirects.
			curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);

			// Follow server redirects.
			//curl_setopt($Curl_Obj, CURLOPT_FOLLOWLOCATION, true);

			// Return output as string.
			curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);
					
			//curl_setopt($Curl_Obj, CURLOPT_VERBOSE, 1);
			curl_setopt($Curl_Obj, CURLOPT_POST, 0);
			curl_setopt ($Curl_Obj, CURLOPT_URL, $curl_url);
			$response = curl_exec ($Curl_Obj);
			//var_dump($response); die;
			return $response;
	}
	
	public function tbAction()
	{
		$time = time();
		$my_message = 'Testez prin CURL';
		$MY_POST_DATA = array(
		'xjxargs[]'=>'<xjxobj><e><k>to</k><v>S472074</v></e><e><k>date</k><v>S<![CDATA['. date('Y-m-d H:i:s',$time).']]></v></e><e><k>message</k><v>S<![CDATA['.$my_message.']]></v></e></xjxobj>',
		'xjxfun'	=>	'export_pmbAction',
		'xjxr'	=>$time
		);
		$post_data = http_build_query($MY_POST_DATA);
		//print $post_data; die;
		
		$cur_url = 'http://www.getacoder.com/pmb/get.php';
		$this->CurlPlatformPost($cur_url,$post_data, 44, 6);
		die;
	}
	
	public function CurlPlatformPost($cur_url,$post_data, $user_id, $platform_id)
	{
		$cookieFile = dirname(__FILE__).'/cookies/'.$user_id.'cookie.txt';
		
        $Curl_Obj = curl_init();
			
		curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, $cookieFile); 
		curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, $cookieFile); 
		
        // Set the browser you will emulate

        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0';
        curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);

        // Don't include the header in the output.
        curl_setopt ($Curl_Obj, CURLOPT_HEADER, 0);

        // Allow referer field when following Location redirects.
        curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);
		
		// Enable Posting.
        curl_setopt($Curl_Obj, CURLOPT_POST, 1);
        
		//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
		if($platform_id == 8 || $platform_id == 5)
		{
			curl_setopt($Curl_Obj, CURLOPT_RETURNTRANSFER, true); 
		}

		curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 
		if($platform_id==5)
		{
			curl_setopt($Curl_Obj, CURLOPT_HTTPHEADER, array(                                                                          
				'Content-Type: application/json; charset=utf-8',                                                                                
				'Content-Length: ' . strlen($post_data))                                                                       
			);
		}
		if($platform_id==6)
		{
			curl_setopt($Curl_Obj, CURLOPT_HTTPHEADER, array(                                                                          
				'text/xml ; charset="utf-8"',                                                                                
				'Content-Length: ' . strlen($post_data))                                                                       
			);
		}
		
		curl_setopt ($Curl_Obj, CURLOPT_URL, $cur_url);
		$response = curl_exec($Curl_Obj);
		
		if($platform_id==6)
		{
			print $response; die;
		}
		
		if ($platform_id == 8 || $platform_id == 5)
		{
			return json_decode($response,true);
		}				
	}
	
	public function freelancerinfoAction()
	{
		Zend_Session::start();
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		if($user_id != 0)
		{
			if(isset($_SESSION['connected_platform'][1]['is_connected']))
			{
				if(!isset($_SESSION['connected_platform'][1]['profile']))
				{
					$this->getRemotePlatformProfile(1);
				}
				
				require_once ('Freelancer/SnowTigerLib.php');
				$request = $this->getRequest();
				$action = $request->getParam('faction');
		
				$t = $_SESSION['connected_platform'][1]['access_token'];
				$ts = $_SESSION['connected_platform'][1]['access_token_secret'];
				$stl = new SnowTigerLib($t, $ts);
				// User Id		6792353
				// User Name	munka

				
				/*
				$param = array('projectid'=>4111980,'messagetext'=>'Till now i have created more than 30 iOS aplicattions');
				$stl->method = 'POST';
				$res = $stl->sendMessage($param);//work
				var_dump($res); 
				die;
				*/
				
				/*
				$param = array('rating'=>9,'feedbacktext'=>'I can convert your traffic spinners website into wordpress CMS with same layout and design','username'=>'erkmurat2007','projectid'=>4112074);
				$res = $stl->postFeedback($param);//work
				var_dump($res);
				die;
				*/
				
				
				//$param=array('username'=>'munka');
				//$param=array('userid'=>679847);
				//$feed = $stl->getUserFeedback($param);//work
				//var_dump($feed);
				//die;
				
				//$Project = $stl->getProjectDetails(4110277);
				//var_dump($Project);
				//die;
				
				switch($action)
				{
					case 'messageinbox' : {
						$stl->getInboxMessages();
						$FreelancerInboxData = $stl->getArrayData();
						//print_r($FreelancerInboxData);
			
						if(isset($FreelancerInboxData))
						{
							$ProjectInfo = array();
							$this->view->assign('FreelancerInboxData',$FreelancerInboxData);
							foreach($FreelancerInboxData['items'] as $Value)
							{
								if(!isset($ProjectInfo[(int)$Value['projectid']]))
								{
									$stl->getProjectDetails((int)$Value['projectid']);
									$ProjectDetailsData = $stl->getArrayData();
									$ProjectInfo[(int)$Value['projectid']]= array('name'=>$ProjectDetailsData['name'],'url'=>$ProjectDetailsData['url']);
								}
							}
							$this->view->assign('ProjectInfo',$ProjectInfo);
						}
						break;
					}
					case 'myfeedbacks' : {
						//$UserParam=array('userid'=>679847);
						$UserParam=array('userid'=>$_SESSION['connected_platform'][1]['userid']);

						$UserParam=array('userid'=>679847);
						$stl->getUserFeedback($UserParam);//work
						$UserFeedData =  $stl->getArrayData();
						
						//print_r($UserFeedData);die;
						$this->view->assign('UserFeedData',$UserFeedData);
						
						break;
					}
					default : {break;}
				}
				//print_r($FreelancerInboxData);
				//die;
			
			}
		}
	}
    
    public function step2Action()
    {
        $this->_redirect('/index/step3');
        
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
        
        $platformModel = new Application_Model_DbTable_Platforms();
        $accountModel = new Application_Model_DbTable_Accounts();
        
        $account = $accountModel->find($userId)->current();
        if (null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        $this->view->connections = $account->getConnections();
        $this->view->platforms = $platformModel->getAssoc();
    }
    
    public function connectAction()
    {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
        
        $username = trim($this->_getParam('username'));
        $password = trim($this->_getParam('password'));
        $platformId = (int)$this->_getParam('platformId');
        
        if (!$username || !$platformId) {
            throw new Zend_Controller_Action_Exception('Empty parameters');
        }
        
        $platformModel = new Application_Model_DbTable_Platforms();
        $accountModel = new Application_Model_DbTable_Accounts();
        
        $platform = $platformModel->find($platformId)->current();
        $account = $accountModel->find($userId)->current();
        if (null === $platform || null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        $account->setConnection($platformId, $username, $password);
    }
    
    public function connectionsAction()
    {
        $this->view->hideSteps = true;
        $this->_forward('step2');
    }
    
    public function myAction()
    {
        require_once ('Freelancer/SnowTigerLib.php');
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $user_id = (int) $authStorage->read();
        if ($user_id != 0) {
		$transationTable = new  Application_Model_DbTable_Transactions();
		$photoTable = new Application_Model_DbTable_Freelancersphotos();
		$accountTable = new Application_Model_DbTable_Accounts();
		$freelancerRow = $accountTable->getFreelanceDetails(array('email','name','fname','lname'),$user_id);
		$photoRows = $photoTable->getUserPhotos($user_id,'8');
		$this->view->photoRows = $photoRows;
		$this->view->freelancerRow = $freelancerRow;
		$this->view->isFeaturedUser = $transationTable->checkIsFeatured($user_id);
		$model = new Application_Model_Index();
		$ActiveCategories = $model->getActiveCategories();
		$subCatTable = new Application_Model_DbTable_Freelancerscategories();
		$subscribeCategories = $subCatTable->getUserSubscribeCategory($user_id);
		$this->view->subscribeCategories = $subscribeCategories;
        $this->view->ActiveCategories = $ActiveCategories;
		  
		  /* $platformModel = new Application_Model_DbTable_Platforms();
            $accountModel = new Application_Model_DbTable_Accounts();
            
            $account = $accountModel->find($user_id)->current();
            if (null === $account) {
                throw new Zend_Controller_Action_Exception('Wrong parameters');
            }
            
            $this->view->connections = $account->getConnections();
            $this->view->platforms = $platformModel->getAssoc();
            
            $modelFromGeneral = new Application_Model_General();
            
            if ($modelFromGeneral->checkIfFirstTimeLogin($user_id) && $this->getRequest()->getActionName()=='my') {
                $this->_redirect('/profile/step2/'); 
            }
            
            $ConnectedProfiles = array();
            
            $modelFromIndex = new Application_Model_Index();
            $Platforms = $modelFromIndex->getPlatforms();
            $this->view->assign('Platforms',$Platforms);
            
            $nr_of_platforms = $modelFromGeneral->getNrOfPlatforms();
            $this->view->assign('nr_of_platforms',$nr_of_platforms);
            
            $UserInfo = $modelFromGeneral->getUserInfo($user_id);
            $this->view->assign('UserInfo',$UserInfo);
            
            $ConnectedPlatforms = $modelFromGeneral->getConnectedPlatforms($user_id);
            $this->view->assign('ConnectedPlatforms',$ConnectedPlatforms);
            
            $FreelancerInfo = $modelFromGeneral->getFreelancerInfo($user_id);
            $this->view->assign('FreelancerInfo',$FreelancerInfo);
            
            $PublicProfileBlock = $this->view->render('profile/index.phtml');
            $this->view->assign('PublicProfileBlock',$PublicProfileBlock);
            
            
            $CurlPlatformsArray = $modelFromGeneral->extractCURLPlatforms();
            $this->view->assign('CurlPlatformsArray',$CurlPlatformsArray);
            
            if (isset($_SESSION['connected_platform'][1]['profile'])) {
                $this->view->assign('FreelacerProfile',$_SESSION['connected_platform'][1]['profile']);
                $FreelacerProfileBlock = $this->view->render('profile/freelancer.phtml');
                
                $ConnectedProfiles[1] = $FreelacerProfileBlock;
            }
			
			
			$model = new Application_Model_Index();
			$ActiveCategories = $model->getActiveCategories();
			$subCatTable = new Application_Model_DbTable_Freelancerscategories();
			$subscribeCategories = $subCatTable->getUserSubscribeCategory($user_id);
			$this->view->subscribeCategories = $subscribeCategories;
        	$this->view->ActiveCategories = $ActiveCategories;
            
            //!!!!!!!!!!! NU trebuie de sters rindurile de mai jos
            /*
            if(!isset($_SESSION['connected_platform'][3]['profile']) && isset($_SESSION['connected_platform'][3]['is_connected'] ) && $_SESSION['connected_platform'][3]['is_connected']=1 && isset($_SESSION['connected_platform'][3]['access_token']))
            {
                $url = 'https://api.elance.com/api2/profiles/my?access_token='.$_SESSION['connected_platform'][3]['access_token'];
                $_SESSION['connected_platform'][3]['profile'] = $modelFromGeneral->cURLExtractJSONContent($url);
                $_SESSION['connected_platform'][3]['userid'] = $_SESSION['connected_platform'][3]['profile']['data']['providerProfile']['userId'];
            }
            */
            
            /*if(isset($_SESSION['connected_platform'][3]['profile']['data']['providerProfile']))
            {
                $ElanceProfile = $_SESSION['connected_platform'][3]['profile']['data']['providerProfile'];
                
                $this->view->assign('ElanceProfile',$ElanceProfile);
                $ElanceProfileBlock = $this->view->render('profile/elance.phtml');
                $ConnectedProfiles[3] = $ElanceProfileBlock;
            }*/
            //4 - Odesk
            //!!!!!!!!!!! NU trebuie de sters rindurile de mai jos
			/*
			if(!isset($_SESSION['connected_platform'][4]['profile']) && isset($_SESSION['connected_platform'][4]['is_connected']) && $_SESSION['connected_platform'][4]['is_connected']=1 && isset($_SESSION['connected_platform'][4]['access_token']) && isset($_SESSION['connected_platform'][4]['access_token_secret']))
			{
				$t = $_SESSION['connected_platform'][4]['access_token'];
				$ts = $_SESSION['connected_platform'][4]['access_token_secret'];
				
				$secret_key     = $consumerSec . '&' . $ts;
				
				$params = array(
					'oauth_consumer_key'    => $consumerKey,
					'oauth_signature_method'=> $sigMethod,
					'oauth_timestamp'       => time(),
					'oauth_nonce'           => substr(md5(microtime(true)), 5),
					'oauth_token'           => $t
					);

				ksort($params);
				$method = 'GET';

				$params_string  = http_build_query($params);

				$url = 'https://www.odesk.com/api/auth/v1/info.json';
				$base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
				$signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

				$params['oauth_signature'] = $signature;

				$params_string = http_build_query($params);
				
				$url .= '?' . $params_string;
				$modelFromGeneral = new Application_Model_General();
				$_SESSION['connected_platform'][4]['profile'] = $modelFromGeneral->cURLExtractJSONContent($url);
				$_SESSION['connected_platform'][4]['userid'] = $_SESSION['connected_platform'][4]['profile']['auth_user']['uid'];
			}
			*/
			/*
			if(isset($_SESSION['connected_platform'][4]['profile']['auth_user']) && isset($_SESSION['connected_platform'][4]['profile']['info']))
			{
				$OdeskProfile = $_SESSION['connected_platform'][4]['profile'];
				$this->view->assign('OdeskProfile',$OdeskProfile);
				$OdeskProfileBlock = $this->view->render('profile/odesk.phtml');
				$ConnectedProfiles[4] = $OdeskProfileBlock;
			} 
			$this->view->assign('ConnectedProfiles',$ConnectedProfiles);
			
			*/
			
			
			
		} else {
			$this->view->message_not_logged = '<span class="f_error_msg">You should Sign In in order to view your profile</span>';		
		}
		
		//print_r($_SESSION['connected_platform'][3]['profile']);	
	}

	public function createAction()
	{
		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = (int) $authStorage->read();
		$modelFromGeneral = new Application_Model_General();
		//$user_id = (int) $modelFromGeneral->getUserIdByEmail($user_email);
		if($user_id != 0)
		{
			$model = new Application_Model_Profile();	 
			$previousProfile = $model->getProfile($user_id);
			$acountInfo = $model->getAcountInfo($user_id);
			//print_r($acountInfo);
			//die('hete');
			$photoTable = new Application_Model_DbTable_Freelancersphotos();
			$transationTable = new  Application_Model_DbTable_Transactions();
			$photoRows = $photoTable->getUserPhotos($user_id,'10');
			$this->view->photoRows = $photoRows;
			$this->view->acountInfo = $acountInfo;	
			$this->view->isFeaturedUser = $transationTable->checkIsFeatured($user_id);
			$this->view->featuredFreelancerDetails = $transationTable->getFeaturedFreelancerDetails($user_id);
			$request = $this->getRequest();
            $msg = (int) $request->getParam('msg');
			if($msg==1)
			$this->view->message = '<span class="f_success_msg">You have sucessfully canceled your profile from featured members!.</span>';
			else if($msg==2)
			$this->view->message = '<span class="f_error_msg">Error occured please try later or contact site admin for canceled the featured members profile</span>';
			//print_r($featuredFreelancerDetails);
	
			$form = new Application_Model_CreateProfileForm();
		
			if ($this->_request->isPost()) {

			$formData = $this->_request->getPost();

			if ($form->isValid($formData)) {
				$adapter = $form->files->getTransferAdapter();
			
			//	print_r ($adapter->getFileInfo()); die;
			
				$ImagesFiles = $adapter->getFileInfo();
				if($ImagesFiles['files_0_']['tmp_name'])
				{
					foreach ($ImagesFiles as $file) {
				
						$modelFromGeneral = new Application_Model_General();	 
						$newName = $modelFromGeneral->fileNameNew($file);
					  
						$adapter->addFilter('Rename', realpath(dirname('.')).
							DIRECTORY_SEPARATOR.
							'data'.
							DIRECTORY_SEPARATOR.
							'profilePictures'.
							DIRECTORY_SEPARATOR.
							$newName);
						$adapter->receive($file['name']);
					}
				}
				
				$file = $form->files->getFileInfo();
				
				//print_r($file); die;
				//var_dump($formData); die;
				
				$sql_action = ($previousProfile)? 'update' : 'insert';
				
				$result_of_insertion = $model->addProfile($formData, $file, $user_id, $sql_action);
				
				if($result_of_insertion){
					$this->view->message = '<span class="f_success_msg">Your profile was updated with success</span>';
					$this->_redirect('/profile/my/'); 
					
				}
				  
			  } else {
					$form->populate($formData);
				}
			}
			else {
				if($previousProfile)
				{
					$acountInfo = array_merge($previousProfile, $acountInfo);	
					$acountInfo['oldfile'] = $previousProfile['picture'];
				}
					$transationTable = new  Application_Model_DbTable_Transactions();
			   		if($transationTable->checkIsFeatured($user_id)==0)
			   		{
							$form->removeElement('skills');
							$form->removeElement('button');
							$form->removeElement('newproject');
							$form->removeElement('education');
							$form->removeElement('industry');
					}	
				$form->populate($acountInfo);
			}
			$this->view->form = $form;
		}
		else {
			$this->view->message = '<span class="f_error_msg">You should Sign In in order to create your profile</span>';
		}
		$this->view->userid = $user_id;
	}
	
	public function indexAction()
    {
		$modelFromGeneral = new Application_Model_General();
		
		$request = $this->getRequest();
		$user_id = (int)$request->getParam('uid');
	
		if($user_id == 0)
		{
			$this->view->message_not_user = 'User with such a name does not exist';
		}
		else 
		{
			$UserInfo = $modelFromGeneral->getUserInfo($user_id);
			$this->view->assign('UserInfo',$UserInfo);		
		}
	}
	
    public function index2Action()
    {
		$modelFromGeneral = new Application_Model_General();
		
		$request = $this->getRequest();
		$UserName = $request->getParam('un');
	
		if($UserName!='')
		{
			$user_id = (int) $modelFromGeneral->getUserId($UserName);
			if($user_id == 0)
			{
				$this->view->message_not_user = 'User with such a name does not exist';
			}
			else 
			{
				$UserInfo = $modelFromGeneral->getUserInfo($user_id);
				$this->view->assign('UserInfo',$UserInfo);
				
			}
		}	
	}
	
		public function getRemotePlatformProfile($platform_id)
		{
			Zend_Session::start();
			if($platform_id==3)
			{
				$modelFromGeneral = new Application_Model_General();
				$url = 'https://api.elance.com/api2/profiles/my?access_token='.$_SESSION['connected_platform'][3]['access_token'];
				$_SESSION['connected_platform'][3]['profile'] = $modelFromGeneral->cURLExtractJSONContent($url);
				$_SESSION['connected_platform'][3]['userid'] = $_SESSION['connected_platform'][3]['profile']['data']['providerProfile']['userId'];	
			}
			else if($platform_id==1)
			{
				require_once ('Freelancer/SnowTigerLib.php');
				$t = $_SESSION['connected_platform'][1]['access_token'];
				$ts = $_SESSION['connected_platform'][1]['access_token_secret'];
				$stl = new SnowTigerLib($t, $ts);
				$_SESSION['connected_platform'][1]['profile'] = $stl->getAccountDetails()->getArrayData();
				$_SESSION['connected_platform'][1]['userid'] = $_SESSION['connected_platform'][1]['profile']['userid'];
			}
			/*else if($platform_id==4)
			{
				require_once('Odesk/OdeskConfig.php');
				$t = $_SESSION['connected_platform'][4]['access_token'];
				$ts = $_SESSION['connected_platform'][4]['access_token_secret'];
				
				$secret_key     = $consumerSec . '&' . $ts;
				
				$params = array(
					'oauth_consumer_key'    => $consumerKey,
					'oauth_signature_method'=> $sigMethod,
					'oauth_timestamp'       => time(),
					'oauth_nonce'           => substr(md5(microtime(true)), 5),
					'oauth_token'           => $t
					);

				ksort($params);

				$method = 'GET';
				
				
				$params_string  = http_build_query($params);

				$url = 'https://www.odesk.com/api/auth/v1/info.json';
				$base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
				$signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

				$params['oauth_signature'] = $signature;

				$params_string = http_build_query($params);
				
				$url .= '?' . $params_string;
				
				$modelFromGeneral = new Application_Model_General();
				$_SESSION['connected_platform'][4]['profile'] = $modelFromGeneral->cURLExtractJSONContent($url);
				$_SESSION['connected_platform'][4]['userid'] = $_SESSION['connected_platform'][4]['profile']['auth_user']['uid'];
			}*/
		}
		
	public function mycategoryAction()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $user_id = (int) $authStorage->read();
        if ($user_id != 0) {
            $accountModel = new Application_Model_DbTable_Accounts();
			$model = new Application_Model_Index();
			$ActiveCategories = $model->getActiveCategories();
			$subCatTable = new Application_Model_DbTable_Freelancerscategories();
			$subscribeCategories = $subCatTable->getUserSubscribeCategory($user_id);
			$this->view->subscribeCategories = $subscribeCategories;
        	$this->view->ActiveCategories = $ActiveCategories;
			$account = $accountModel->find($user_id)->current();
            if (null === $account) {
                throw new Zend_Controller_Action_Exception('Wrong parameters');
            }
			} 
			else 
			{
		
			$this->view->message_not_logged = '<span class="f_error_msg">You should Sign In in order to view your profile</span>';		
		}
		//print_r($_SESSION['connected_platform'][3]['profile']);	
	}
	
	public function unsubscribeAction()
	{
		$form = new Application_Model_RegisterForm();
		$form->unsubscribeFrom('');
		if ($this->_request->isPost()) {
            $formData = $this->_request->getPost();
            if ($form->isValid($formData)){
			 	$accountModel = new Application_Model_DbTable_Accounts();
				$subCatTable = new Application_Model_DbTable_Freelancerscategories();
			 	$accountId = $accountModel->getAccountsIdByEmail($formData['email']);
				if(!empty($accountId))
				{
					if($subCatTable->remove($accountId['id']))
					{
						$this->_redirect('/'); 
					}
				}
				else
				{
					$email = $formData['email'];
					$this->view->message = "<span class='f_error_msg'>This $email email is not registered with us !</span>";
				}
			}
		}
		
		 $this->view->form = $form;		
				
	}
	public function portfolioAction()
    {
        $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $user_id = (int) $authStorage->read();
        if ($user_id != 0) {
			
			$photoTable = new Application_Model_DbTable_Freelancersphotos();
			$photoRows = $photoTable->getUserPhotos($user_id,'10');
			$this->view->photoRows = $photoRows;
			} 
			else 
			{
		
			$this->view->message_not_logged = '<span class="f_error_msg">You should Sign In in order to view your profile</span>';		
		}
		//print_r($_SESSION['connected_platform'][3]['profile']);	
	}
	public function uploadphotoAction()
	{
			$this->_helper->layout()->disableLayout(); 
			$this->_helper->layout->setLayout('ajaxlayout');
			 $auth = Zend_Auth::getInstance();
        	 $authStorage = $auth->getStorage();
        	 $user_id = (int) $authStorage->read();
			 if ($user_id != 0) {
			$photoTable = new Application_Model_DbTable_Freelancersphotos();
			$totalPhoto = $photoTable->countUserPhotos($user_id);
			if($totalPhoto < 10)
			{
			$form = new Application_Model_CreatePhotoForm();
			if ($this->_request->isPost()) {
			$formData = $this->_request->getPost();
			if ($form->isValid($formData)) {
				$adapter = $form->files->getTransferAdapter();
			//	print_r ($adapter->getFileInfo()); die;
			
				$ImagesFiles = $adapter->getFileInfo();
				if($ImagesFiles['files_0_']['tmp_name'])
				{
					foreach ($ImagesFiles as $file) {
				
						$modelFromGeneral = new Application_Model_General();	 
						$newName = $modelFromGeneral->fileNameNew($file);
					  
						$adapter->addFilter('Rename', realpath(dirname('.')).
							DIRECTORY_SEPARATOR.
							'data'.
							DIRECTORY_SEPARATOR.
							'portfolioPhoto'.
							DIRECTORY_SEPARATOR.
							$newName);
						$adapter->receive($file['name']);
					}
				}
				$file = $form->files->getFileInfo();
				//print_r($file); die;
				//var_dump($formData); die;
				$filename = '';
				if(!empty($file['files_0_']['tmp_name']))
				{
					$filename = $file['files_0_']['name'];
				}
				$data = array(
						'account_id' =>$user_id,
						'title'		=>$formData['title'],
						'photos'	=>$filename
				);
				
				if($photoTable->save($data,NULL))
				{
					//$this->_redirect('/profile/portfolio/msg/1'); 
					echo("<script>window.location=window.location; parent.$.fancybox.close();</script>");
					//$this->_redirect('/profile/create/');
				}
				
				  
			  } else {
					$form->populate($formData);
				}
			}
			 $this->view->form = $form;
			 $this->view->userid = $user_id;
			 } 
			 else 
			{
			$this->view->totalPhoto = $totalPhoto;
			$this->view->message= '<span class="f_error_msg">You can upload maximum 10 photos </span>';		
		}
		}
		else
		{
			$this->view->message= '<span class="f_error_msg">You should Sign In in order to view your profile</span>';	
		}	 
	}
	public function deletephotoAction()
	{
		$auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $user_id = (int) $authStorage->read();
		$request = $this->getRequest();
        $photoId = $request->getParam('pid');
		if ($user_id != 0 && $photoId!='') {
		$photoTable = new Application_Model_DbTable_Freelancersphotos();
		$photoRow = $photoTable->getPhotoDetails(array('photos'),$photoId);
		if($photoTable->remove($photoId,$user_id))
		{
			unlink('data/portfolioPhoto/'.$photoRow['photos']);
			$this->_redirect('/profile/create/'); 
			
		}
		}
			
	}
	public function upgradeAction()
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

        /*29.1.2014 hook for enable only paypal payment, to enable cc payment type comment the line below*/
						$transaction = $transactionModel->createRow();
						$transaction->account_id = $userId;
						$transaction->amount = UPGRADE_COST; //UPGRADE_COST set after test 
						$transaction->added = new Zend_Db_Expr('NOW()');
						$transaction->paytype = 'paypal';
						$transaction->payment_status = 'N';
						$transaction->expiry_date = date('Y-m-d H:i:s');
						$transaction->save();
						$this->view->sendForm = $payment->getPayPalFormForUpgrade($transaction);
		//end				
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
					 $account->save();
                }
               
                
                switch ($account->paytype) {
                    case 'paypal':
						$transaction = $transactionModel->createRow();
						$transaction->account_id = $userId;
						$transaction->amount = UPGRADE_COST; //UPGRADE_COST set after test 
						$transaction->added = new Zend_Db_Expr('NOW()');
						$transaction->paytype = 'paypal';
						$transaction->payment_status = 'N';
						$transaction->expiry_date = date('Y-m-d H:i:s');
						$transaction->save();
                        $this->view->sendForm = $payment->getPayPalFormForUpgrade($transaction);
                        break;
                    case 'cc':
                        $transaction = $transactionModel->createRow();
                        $subscriptionId = $payment->ccQuery($transaction, $account, $cc);
                        if ($subscriptionId) {
							$transaction->account_id = $userId;
                        	$transaction->amount = UPGRADE_COST;
                        	$transaction->added = new Zend_Db_Expr('NOW()');
                        	$transaction->paytype = 'cc';
							$transaction->payment_status = 'Y';
							$transaction->expiry_date = date('Y-m-d H:i:s', strtotime("+30 days"));
                        	$transaction->save();
                            $account->subscription_id = $subscriptionId;
                            $account->subscription_check = new Zend_Db_Expr('NOW()');
                            $account->agreed = 1;
                            $account->agreed_at = new Zend_Db_Expr('NOW()');
                            $account->save();
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
	public function cancelfreelancerprofileAction()
	{
		$this->_helper->layout()->disableLayout(); 
		$auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $user_id = (int) $authStorage->read();
		$transactionModel = new Application_Model_DbTable_Transactions();
		$paymentModel = new Application_Model_Payment();
		$row = $transactionModel->getFeaturedFreelancerDetails($user_id);
		$result = $paymentModel->cancelPayPal($row['paypal_subscription_id']);	
		if($result)	
		{
			$this->_redirect('/profile/create/msg/1'); 
		}
		else
		{
			$this->_redirect('/profile/create/msg/2'); 
		}
		 
		
	}
	public function cancelfreelancerprofiletestAction()
	{
		$this->_helper->layout()->disableLayout(); 
		$auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $user_id = (int) $authStorage->read();
		$transactionModel = new Application_Model_DbTable_Transactions();
		$paymentModel = new Application_Model_Payment();
		$row = $transactionModel->getFeaturedFreelancerDetails($user_id);
		
	/*	$config = array('auth' => 'login','username' => 'support@searchfreelancejobs.com','password' => '123qwe');
		$transport = new Zend_Mail_Transport_Smtp('mail.searchfreelancejobs.com', $config);	 								
		$mail = new Zend_Mail();
		$mail->setFrom('no-reply@SearchFreelanceJobs.com', 'SearchFreelanceJobs.com');
		$mail->setSubject('Your SearchFreelanceJobs Freelancer profile Cancel');
		$mail->setBodyText("You have successfully canceled your profile from featured members.\n\n\nWith love,\nThe SearchFreelanceJobs.com Team");
		$mail->addTo('arvindk427@gmail.com');						
		$mail->send($transport);
die; */
		//$result = $paymentModel->cancelPayPal($row['paypal_subscription_id']);		
		 $apiRequest = 'USER=' . urlencode('arvindkumar_api1.gmail.com')
            . '&PWD=' . urlencode('ZHGGLY32C93K38GH')
            . '&SIGNATURE=' . urlencode('An5ns1Kso7MWUdW4ErQKJJJ4qi4-A6FDIUS3FiUV5jy62C.R78f3k4Vq')
            . '&VERSION=76.0'
            . '&METHOD=ManageRecurringPaymentsProfileStatus'
            . '&PROFILEID=' . urlencode($row['paypal_subscription_id'])
            . '&ACTION=Cancel'
            . '&NOTE=' . urlencode('Profile cancelled at store');
        
        $url = 'https://api-3t.sandbox.paypal.com/nvp';
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $apiRequest);
        
        $response = curl_exec($ch);
        
        if (!$response) {
            return false;
        }
        //echo "<pre>";
		//print_r($response);
         curl_close($ch);
		 parse_str($response, $parsedResponse);
         if(isset($parsedResponse['ACK']) && 'Success' == $parsedResponse['ACK'])
		 {
		 	//echo "Your request receive.It take some time to process";
			$this->_redirect('/profile/create/msg/1');
		 }	 
		else
		{
			$this->_redirect('/profile/create/msg/2'); 
		}
		die;
		
	}
	public function upgradetestAction()
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

        /*29.1.2014 hook for enable only paypal payment, to enable cc payment type comment the line below*/
						$transaction = $transactionModel->createRow();
						$transaction->account_id = $userId;
						$transaction->amount = 1; //UPGRADE_COST set after test 
						$transaction->added = new Zend_Db_Expr('NOW()');
						$transaction->paytype = 'paypal';
						$transaction->payment_status = 'N';
						$transaction->expiry_date = date('Y-m-d H:i:s');
						$transaction->save();
						$this->view->sendForm = $payment->getPayPalFormForUpgradeSandBox($transaction);
		//end				
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
					 $account->save();
                }
               
                
                switch ($account->paytype) {
                    case 'paypal':
						$transaction = $transactionModel->createRow();
						$transaction->account_id = $userId;
						$transaction->amount = 1; //UPGRADE_COST set after test 
						$transaction->added = new Zend_Db_Expr('NOW()');
						$transaction->paytype = 'paypal';
						$transaction->payment_status = 'N';
						$transaction->expiry_date = date('Y-m-d H:i:s');
						$transaction->save();
                        $this->view->sendForm = $payment->getPayPalFormForUpgradeSandBox($transaction);
                        break;
                    case 'cc':
                        $transaction = $transactionModel->createRow();
                        $subscriptionId = $payment->ccQuery($transaction, $account, $cc);
                        if ($subscriptionId) {
							$transaction->account_id = $userId;
                        	$transaction->amount = UPGRADE_COST;
                        	$transaction->added = new Zend_Db_Expr('NOW()');
                        	$transaction->paytype = 'cc';
							$transaction->payment_status = 'Y';
							$transaction->expiry_date = date('Y-m-d H:i:s', strtotime("+30 days"));
                        	$transaction->save();
                            $account->subscription_id = $subscriptionId;
                            $account->subscription_check = new Zend_Db_Expr('NOW()');
                            $account->agreed = 1;
                            $account->agreed_at = new Zend_Db_Expr('NOW()');
                            $account->save();
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
		
    		
	
}