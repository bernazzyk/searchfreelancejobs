<?php

class TtestController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */

		$Curl_Obj = curl_init();
        // Set the browser you will emulate

        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0';
        curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);

        // Don't include the header in the output.
        curl_setopt ($Curl_Obj, CURLOPT_HEADER, 1);

        // Allow referer field when following Location redirects.
        curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);
		
		// Enable Posting.
        curl_setopt($Curl_Obj, CURLOPT_POST, 1);
        
		//TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it out directly.
		curl_setopt($Curl_Obj, CURLOPT_RETURNTRANSFER, true);
		
		$post_data = 'id=44231&user=15012&md5=164278cb086e7d3e64f357ebe0537acf&value=10';
		
		curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 
		
		curl_setopt($Curl_Obj, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json; charset=utf-8',                                                                                
			'Content-Length: ' . strlen($post_data))                                                                       
		);
			
		curl_setopt ($Curl_Obj, CURLOPT_URL, 'http://zigmarks.com/vote.php');
		$response = curl_exec($Curl_Obj);
		print $response; 
		die('AAAA');
    }
	
	public function testelanceAction()
	{
		Zend_Session::start(); 
		require_once('ElanceExtraction/elance-auth-lib.php');
		$elance_auth = new ElanceAuthentication();
		$get_params = array("action"	=>	'bidSubmit',
							'baseRate'	=> 15.00,
'bidDesc'	=> 'I think i can elp you',
'bid_companyid'	=> 4905439,
'bid_desc_type'	=> 'plaintext',
'bid_userid'	=>	4905439,
'duration'		=> 5,	
'hourlyRate'	=>	16.44,
'hours'		=>	20,
'jobid'	=> 36201170);

$get_params = '?jobid=36201170&bid_userid=4905439&bid_companyid=4905439&bid_desc_type=plaintext&fileGroupId=&baseRate=15.00&hourlyRate=16.44&hours=20&duration=5&bidDesc=I%20think%20i%20can%20elp%20you&action=bidSubmit';

		$req = $elance_auth->ExecRequest('https://www.elance.com/php/bid/main/proposalSubmitAHR.php?t=1356766474176', '4f21faa83340a00328000001|4905439|3QByDZLUbNchBOxwaoW8SA', $get_params);
		var_dump($req);
		die;
	}
	
	public function testAction() {
	Zend_Session::start();
	require_once('Odesk/OdeskConfig.php');
		$t = $_SESSION['connected_platform'][4]['access_token'];
		$ts = $_SESSION['connected_platform'][4]['access_token_secret'];

		print 't--' . $t;
		print 'ts--' . $ts;
//		print_r($_SESSION['connected_platform'][4]);
		

/*
$api_sig = md5('6c21f6884bcbd3cccapi_key8f3e8ef823d8a928240d48309f1cf054api_token'.$t);
 
$url = 'https://www.odesk.com/api/auth/v1/info.json?api_key=8f3e8ef823d8a928240d48309f1cf054&api_token='.$t.'&api_sig='.$api_sig;
print $url; die('asd');
*/
		
				$secret_key     = $consumerSec . '&' . $ts;
				
				$params = array(
					'oauth_consumer_key'    => $consumerKey,
					'oauth_signature_method'=> $sigMethod,
					'oauth_timestamp'       => time(),
					'oauth_nonce'           => substr(md5(microtime(true)), 5),
					'oauth_token'           => $t
					//'buyer_team__reference'	=>	5744
				//	'message_from_buyer'	=> 'I am sure I can do it,just need a little time',
				//	'hourly_pay_rate'		=> 12.00,
				//	'job__reference'		=> 201995217
					/*'offer_data' => array(
										'job__reference'=>201997962,
										'fixed_pay_amount_agreed'=>50.00							
					)*/
				);

				ksort($params);

				//print_r($params);
				
				$method = 'POST';
				//$method = 'GET';
				
				//offer reference 229620198
				
				//229620198
				$params_string  = http_build_query($params);

				//$url = 'https://www.odesk.com/api/auth/v1/info.json';
				//$url = 'https://www.odesk.com/api/hr/v2/offers/229620198.json';
				//$url = 'https://www.odesk.com/api/hr/v2/offers.json';
				//$url = 'https://www.odesk.com/api/hr/v2/offers.json';
				$url = 'https://www.odesk.com/api/hr/v2/offers.json';
				
				/*$base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
				$signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

				$params['oauth_signature'] = $signature;

				$params_string = http_build_query($params);
				
				$url .= '?' . $params_string;*/
				//print $url; die;
				
				$url .= '?oauth_signature=VrAY3%2F9mEoG6YGsrjjGn92utfDs%3D&oauth_consumer_key=8f3e8ef823d8a928240d48309f1cf054&oauth_nonce=c43ec6bca684cee8d91ec34d331&oauth_signature_method=HMAC-SHA1&oauth_timestamp=1356788455&oauth_token=463044c8ddce56fa507f26ac58c33f8f&offer_data[job__reference]=201997962';
				
				$modelFromGeneral = new Application_Model_General();
				$job_bid = $modelFromGeneral->cURLExtractJSONContent($url);
				var_dump($job_bid);
				die;
	}
	
	public function addBidToFreelancer($param)
	{
		require_once ('Freelancer/SnowTigerLib.php');
		$t = $_SESSION['connected_platform'][1]['access_token'];
		$ts = $_SESSION['connected_platform'][1]['access_token_secret'];
		$stl = new SnowTigerLib($t, $ts);
		$stl->placeBidOnProject($param);
		$PlaceBidStatute = $stl->getArrayData();
		if(isset($PlaceBidStatute['statusconfirmation']) && $PlaceBidStatute['statusconfirmation']==1)//success bid placement
		{
			return true;
		}
		return false;
	
	}
	
	public function testaAction()
	{
	
		/*$post1 = '"I am a desginer"';
		//$post1 = addslashes('"I am a desginer"');
		print $post1;
		

		$post_data = json_encode($post1);
		$post_data = addslashes($post_data);
		
		print '<br>'.$post_data;*/
			
		//die('---');
	
		/*$html = <<<TEXT
		<form method = "POST" action="https://www.guru.com/pro/ProjectDetail.aspx/SubmitProposal">
		<input type="hidden" name="Proposal" value='"{"ProjectID":"906592","CompanyID":"415440","ProfileID":1589705,"BidType":"1","ProposalCost":"50","IsPremium":false,"Description":"I have done this before","AttachmentGroupID":"","SaveAsTemplate":false,"TemplateID":null,"TemplateName":""}"'>
		<input type="submit" value="send">
		</form>
TEXT;

		print $html; die("asd");*/
		$GuruPostArray = array('Proposal'=>array(
					'ProjectID'=>'905507',
					'CompanyID'=>'378569',
					'ProfileID'=>1596172,
					'BidType'=>'1',
					'ProposalCost'=>'50',
					'IsPremium'=>false,
					'Description'=>'"I can help you"',
					'AttachmentGroupID'=>'',
					'SaveAsTemplate'=>false,
					'TemplateID'=>null,
					'TemplateName'=>''
		));
		//$post_data = '{"Proposal":"{"ProjectID":"905497","CompanyID":"576209","ProfileID":1590813,"BidType":"1","ProposalCost":"488","IsPremium":false,"Description":"I can help you","AttachmentGroupID":"","SaveAsTemplate":false,"TemplateID":null,"TemplateName":""}"}';
		//$post_data = '{"Proposal":"{\"ProjectID\":\"905507\",\"CompanyID\":\"378569\",\"ProfileID\":1594462,\"BidType\":\"1\",\"ProposalCost\":\"30\",\"IsPremium\":false,\"Description\":\"I can help\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":false,\"TemplateID\":null,\"TemplateName\":\"\"}"}';
		//$post_data = addslashes($post_data);
		$post_data = json_encode($GuruPostArray);
		print $post_data . '<br>';
		
		$post_data = addslashes($post_data);
		
		print '<br><br>'.$post_data. '<br><br>';
		
		$post_data = json_encode($post_data);
		
			print '<br><br>'.$post_data. '<br><br>';
		
		$post_data2 = '{"Proposal":"{\"ProjectID\":\"906593\",\"CompanyID\":\"576435\",\"ProfileID\":1589705,\"BidType\":\"1\",\"ProposalCost\":\"28\",\"IsPremium\":false,\"Description\":\"I ca do it with no problems\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":false,\"TemplateID\":null,\"TemplateName\":\"\"}"}';
		print $post_data2;
		
		die;
		
		//$post_data = '{"Proposal":"{\"ProjectID\":\"906593\",\"CompanyID\":\"576435\",\"ProfileID\":1589705,\"BidType\":\"1\",\"ProposalCost\":\"28\",\"IsPremium\":false,\"Description\":\"I ca do it with no problems\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":false,\"TemplateID\":null,\"TemplateName\":\"\"}"}';
		
		//$post_data = '{"Proposal":"{\"ProjectID\":\"906598\",\"CompanyID\":\"576435\",\"ProfileID\":1596172,\"BidType\":\"1\",\"ProposalCost\":\"28\",\"IsPremium\":false,\"Description\":\"I ca do it with no problems\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":false,\"TemplateID\":null,\"TemplateName\":\"\"}"}';
		
		//$post_data = '{"Proposal":"{\"ProjectID\":\"906592\",\"CompanyID\":\"576435\",\"ProfileID\":1596172,\"BidType\":\"1\",\"ProposalCost\":\"28\",\"IsPremium\":false,\"Description\":\"I ca do it with no problems\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":false,\"TemplateID\":null,\"TemplateName\":\"\"}"}';
		
		//$result_data = '{"d":"{\"Status\":1,\"Result\":{\"ProposalID\":12509532,\"ProjectID\":906598,\"CompanyID\":136487,\"ProfileID\":1596172,\"BidType\":1,\"ProfileCategory\":\"Websites & Ecommerce\",\"ProposalCost\":28.0000,\"IsPremium\":false,\"Description\":\"I ca do it with no problems\",\"DatePosted\":\"2013-01-08-14-00-44\",\"UrlConvertedDescription\":\"I ca do it with no problems\",\"Attachments\":[]}}"}';
		//print_r(json_decode($result_data,true));
		//die;
		
		/*
		{"Proposal":"{\"ProjectID\":\"906597\",\"CompanyID\":\"576435\",\"ProfileID\":1596172,\"BidType\":\"1\",\"ProposalCost\":\"34\",\"IsPremium\":false,\"Description\":\"Choose me\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":false,\"TemplateID\":null,\"TemplateName\":\"\"}"}
		*/
		/*
		{"d":"{\"Status\":1,\"Result\":{\"ProposalID\":12509558,\"ProjectID\":906597,\"CompanyID\":576435,\"ProfileID\":1596172,\"BidType\":1,\"ProfileCategory\":\"Websites & Ecommerce\",\"ProposalCost\":34.0000,\"IsPremium\":false,\"Description\":\"Choose me\",\"DatePosted\":\"2013-01-08-14-10-06\",\"UrlConvertedDescription\":\"Choose me\",\"Attachments\":[]}}"}
		*/
		
		/*
		{"Proposal":"{\"ProjectID\":\"906974\",\"CompanyID\":\"566891\",\"ProfileID\":1596172,\"BidType\":\"2\",\"ProposalCost\":\"38\",\"IsPremium\":false,\"Description\":\"I am a professional designer, I did this before\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":true,\"TemplateID\":null,\"TemplateName\":\"Template1\"}"}
		*/
		
		/*
		
		{"Proposal":"{\"ProjectID\":\"906987\",\"CompanyID\":\"572351\",\"ProfileID\":1596172,\"BidType\":\"1\",\"ProposalCost\":\"385\",\"IsPremium\":false,\"Description\":\"I am a designer I workde with \\\"photoshop\\\" \\\"corel draw\\\" + css knowledge and css3\",\"AttachmentGroupID\":\"\",\"SaveAsTemplate\":true,\"TemplateID\":null,\"TemplateName\":\"TEMP2\"}"}
		
		*/
		
		
		//print $post_data;
		$this->addtest($post_data, 44, 5);	
		die;
	}
	
	public function addtest($post_data, $user_id, $platform_id)
	{
		
		$CurlOptUrl = array(
			6=>'http://www.getacoder.com/sellers/onplacebid.php',
			8=>'http://www.peopleperhour.com/stream/createProposalFromJob',
			5=>'https://www.guru.com/pro/ProjectDetail.aspx/SubmitProposal'
		);
		//die('-----------------------------------------------------');
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
		curl_setopt($Curl_Obj, CURLOPT_RETURNTRANSFER, true);
		
		curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 
		
		curl_setopt($Curl_Obj, CURLOPT_HTTPHEADER, array(                                                                          
			'Content-Type: application/json; charset=utf-8',                                                                                
			'Content-Length: ' . strlen($post_data))                                                                       
		);
			
		curl_setopt ($Curl_Obj, CURLOPT_URL, $CurlOptUrl[$platform_id]);
		$response = curl_exec($Curl_Obj);
		print $response; die('AAAA');
		if($platform_id == 6)
		{
			if($response==='1')
			{
				return true;
			} else {
				return false;
			}
		} 
		else if ($platform_id == 8)
		{
			return json_decode($response,true);
		}
	}
	
	
	public function addBidToRemotePlatform($post_data, $user_id, $platform_id)
	{
		$CurlOptUrl = array(
						6=>'http://www.getacoder.com/sellers/onplacebid.php',
						8=>'http://www.peopleperhour.com/stream/createProposalFromJob'	
						);
		//die('-----------------------------------------------------');
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
		if($platform_id == 8)
		{
			curl_setopt($Curl_Obj, CURLOPT_RETURNTRANSFER, true); 
		}
		//$post_data = 'id=155431&sum=65&period=20&descr=It+is+my+first+experience+on+getacoder&notifylowerbids=on&submit=Place+Bid';
		//curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data.'&notifylowerbids=on&submit=Place+Bid'); 
		
		//$post_data2 = 'id=155503&sum=20&period=20&descr=It+is+my+first+experience+on+getacoder&notifylowerbids=on&submit=Place+Bid';
		curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 
		
		curl_setopt ($Curl_Obj, CURLOPT_URL, $CurlOptUrl[$platform_id]);
		$response = curl_exec($Curl_Obj);
		
		if($platform_id == 6)
		{
			if($response=='1')
			{
				return true;
			} else {
				return false;
			}
		} 
		else if ($platform_id == 8)
		{
			return json_decode($response,true);
		}
						
	}

    public function indexAction()
    {			
        // action body
		Zend_Session::start();
		$modelFromGeneral = new Application_Model_General(); 
		if(isset($_SERVER['HTTP_REFERER']))
		{
			//strpos($mystring, $findme);
			if(strpos($_SERVER['HTTP_REFERER'],'/projects/index/')!==false)
			{
				$_SESSION['proposal_http_refferer'] = $_SERVER['HTTP_REFERER'];
			}
			else if(strpos($_SERVER['HTTP_REFERER'],'/proposal/index/')===false){
				unset($_SESSION['proposal_http_refferer']);
			}
		}
		else {
			unset($_SESSION['proposal_http_refferer']);
		}

		$auth = Zend_Auth::getInstance();
		$authStorage = $auth->getStorage();
		$user_id = $authStorage->read();
		
		$request = $this->getRequest();
		$project_id = $request->getParam('projectid');
		
		$model = new Application_Model_Index(); 
		$Project = $model->ajaxDetailedProject($project_id);
		
		//print_r($Project);
		
		$this->view->assign('Project', $Project);
		
		$modelTimeOutput = new Application_Model_TimeOutput();
	
		$date_posted = $modelTimeOutput->elapsed_time(strtotime($Project['posted']),2);
		$this->view->assign('date_posted', $date_posted);

		$time_left = $modelTimeOutput->time_left(strtotime($Project['ends']), 2);
		$this->view->assign('time_left', $time_left);
		
		$this->view->assign('no_apply_button', true);
		
		$ProjectFiles = $modelFromGeneral->extractProjectsFiles($project_id);
		if($ProjectFiles)
		{		
			$this->view->assign('ProjectFiles',$ProjectFiles);
		}
		
		$ProjectDetailBlock = $this->view->render('index/ajaxdetailed.phtml');
		$this->view->assign('ProjectDetailBlock', $ProjectDetailBlock);
		
		if($user_id == '')
		{
			//$this->view->form = 'You should Sign In in order to make a propose for this project';
			$this->view->mess_error = 'You should Sign In in order to make a propose for this project';
		}
		else
		{
			$modelFromProposal = new Application_Model_Proposal();
			if($modelFromProposal->checkIfAlreadyProposed($project_id, $user_id))
			{
				//$this->view->form = 'You have already made an offer for this project';
				$this->view->mess_error = 'You have already made an offer for this project';
			}
			else 
			{
				if(isset($_SESSION['connected_platform'][$Project['platform_id']]['is_connected']) && $_SESSION['connected_platform'][$Project['platform_id']]['is_connected'] == 1)
				{
					if($Project['platform_id']==8)
					{
						if(isset($_POST['pph_submit']))
						{
							$amt = 0.0;
							foreach($_POST['ProposalDetail'] as $key=>$value)
							{
								$ProposalDetail[]=$value;
								$amt+=(float)$value['cost'];
								//unset($_POST['ProposalDetail'][$key]);
							}
							
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
									
							if($Project['jobtype']=2)
							{
								$amt_per_time = 'fixed';
							} else if($Project['jobtype']=1)
							{
								$amt_per_time = 'hour';
							}
							$NotifyOnEmail = (isset($_POST['pph_notify']))? (int)$_POST['pph_notify'] : 0;
							
							$Proposal = array(	'amt'=>$amt,
												'amt_per_time'	=>	$amt_per_time,
												'bid_desc'	=>	'',
												'emailNotifications'	=> $NotifyOnEmail,
												'deposit'				=> (float)$_POST['pph_deposit'], //$amt, //(0.4*$amt),
												'isJob'				=> true,
												'proj_id'				=> (int)$Project['external_id']
											);
											
							$StreamMessage = array('ackPolicy'=>0,'publish'=>0,'txt'=>$_POST['StreamMessage']['txt']);
							$MY_POST_DATA = array('ProposalDetail'=>$ProposalDetail,'Proposal'=>$Proposal,'StreamMessage'=>$StreamMessage);
						
							$post_data = http_build_query($MY_POST_DATA);
							$post_data = $YII_CSRF_TOKEN . '&' . $post_data;
							
							$StatusExec = $this->addBidToRemotePlatform($post_data, $user_id, $Project['platform_id']);
							
							if($StatusExec['error']==1)
							{
								
								$ErrorTxt = '';
								foreach($StatusExec['errorText'] as $ErrorInfo)
								{
									$ErrorTxt .= $ErrorInfo .'<br>';
								}
								$this->view->message = '<span class="f_error_msg">Could not send your bid<br>'.$ErrorTxt.'</span>';
								//unset($_POST);
							}
							else {
							
								$formData['budget'] 	= $amt;
								$formData['proposal']	= $_POST['StreamMessage']['txt'];
								$result_of_insertion = $modelFromProposal->addProposal($project_id, $formData, '', $user_id);
							
								$flashMessenger = $this->_helper->getHelper('FlashMessenger');
								$flashMessenger->addMessage('<span class="f_success_msg">Your proposal send with success. View more details on <a href="'.$StatusExec['streamUrl'].'" target="_blank">'. $StatusExec['streamUrl'] .'</a></span>');
											
								$mess_arr = $flashMessenger->getMessages();
								$this->view->message = $mess_arr[0];
											
								$this->_helper->redirector->gotoUrl($this->getRequest()->getRequestUri());
								
								
								//$this->view->message = '<span class="f_success_msg">Your proposal send with success</span>'.$ErrorTxt.'</span>';
							}
						}				
						$this->view->form = $this->view->render('proposal/pphform.phtml'); 
					}
					else
					{	
						$user_id = (int)$user_id; //(int) $modelFromGeneral->getUserIdByEmail($user_email);
						
						if($Project['platform_id']==1)
						{
							$form = new Application_Model_ProposalFormFreelancer();
						} else {
							$form = new Application_Model_ProposalForm();
						}
						if ($this->_request->isPost()) 
						{
							$formData = $this->_request->getPost();

							if ($form->isValid($formData)) 
							{
								$adapter = $form->files->getTransferAdapter();				
								$file = $form->files->getFileInfo();
							
								$bid_placed_successfully = false;
								if($Project['platform_id']==1)
								{
									$param = array('milestone'=>$formData['milestone'],'projectid'=>$Project['external_id'],'amount'=>$formData['budget'],'days'=>$formData['period'],'description'=>$formData['proposal']);
									$bid_placed_successfully = $this->addBidToFreelancer($param);
								}
								else if($Project['platform_id']==6)//Daca este getACoder
								{
									$description = str_replace(' ', '+', $formData['proposal']);
									$post_data = 'id='. $Project['external_id'] .'&sum='.$formData['budget'].'&period='.$formData['period'].'&descr='.$description.'&notifylowerbids=on&submit=Place+Bid';
									$MY_POST_DATA = array(
															'id' => $Project['external_id'],
															'sum' => $formData['budget'],
															'period' => $formData['period'],
															'descr' => $formData['proposal'],
															'notifylowerbids' => 'on',
															'submit' => 'Place Bid');
															
									//$post_data = http_build_query($MY_POST_DATA);
									
									//print 'id=155643&sum=190&period=10&descr=I+can+help+you&notifylowerbids=on&submit=Place+Bid'; die;
									
									
						
									$bid_placed_successfully = $this->addBidToRemotePlatform($post_data, $user_id, $Project['platform_id']);
								}
								if(!$bid_placed_successfully)
								{
									$freelancer_send_error = '';
									if(isset($_SESSION['FreelancerErrorDetails']))
									{
										include_once('HTMLParser/simple_html_dom.php');
										$html = str_get_html($_SESSION['FreelancerErrorDetails']);
										//print $_SESSION['FreelancerErrorDetails'];
										unset( $_SESSION['FreelancerErrorDetails']);
										$freelancer_send_error = $html->find('font',0)->find('table',0)->find('tr',1)->find('th',0)->plaintext;
										//print $html;
									}
									
									$this->view->message = '<span class="f_error_msg">Could not send your bid. '. $freelancer_send_error .'</span>';
								} 
								else 
								{
									$result_of_insertion = $modelFromProposal->addProposal($project_id, $formData, $file, $user_id);
									
									if((int)$result_of_insertion==2) {
										$this->view->message = '<span class="f_error_msg">Could not send your bid. You have already made an offer for this job</span>';
									}
									else if($result_of_insertion){
										$flashMessenger = $this->_helper->getHelper('FlashMessenger');
										$flashMessenger->addMessage('<span class="f_success_msg">Your proposal send with success</span>');
											
										$mess_arr = $flashMessenger->getMessages();
										$this->view->message = $mess_arr[0];
											
										$this->_helper->redirector->gotoUrl($this->getRequest()->getRequestUri());
									}
								}	  
							} 
							else {
								$form->populate($formData);
							}
						}
						
						$this->view->form = $form;
					}
				}
				else 
				{
					
					$Platform = $modelFromGeneral->getPlatform($Project['platform_id']);//Get platform info
					
					//$this->view->form = 'You should be connected to '. $Platform['name'] .' platform in order to make a bid for this project. <a href="/profile/my/">Connect Now</a>';
					$this->view->mess_error = 'You should be connected to '. $Platform['name'] .' platform in order to make a bid for this project. <a href="/profile/my/">Connect Now</a>';
				}
			}
		}
    }
	
	public function fileNameNew($file = '') {
		$path_info = pathinfo($file['name']);
		$ext = $path_info['extension'];
		
		$name = rand(1000000000, 9999999999);
		$result = 'file-' . time() . '-' . mb_substr($name, rand(0, 5), 5) . '.' . $ext;
		
		return $result;
	}	
}