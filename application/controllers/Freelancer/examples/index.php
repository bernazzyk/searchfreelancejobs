<?php
session_start();
require_once ('../SnowTigerLib.php');
//   unset($_SESSION['access_key']);
if (!isset ($_SESSION['access_key']) && !isset($_GET['token'])) {
	$stl = new SnowTigerLib();
	$token = $stl->getRequestToken();
	$_SESSION['token'] = $token;
	echo '<a href="'.$stl->getAuthorizeURL().'">Authorize with Freelancer.com</a>';
}else{
	if(isset($_GET['token'])){
		$_SESSION['access_key'] = array('oauth_token'=>$_GET['token'],'oauth_token_secret'=>$_GET['secret']);
	}
	$stl = new SnowTigerLib($_SESSION['access_key']['oauth_token'], $_SESSION['access_key']['oauth_token_secret']);
//	$stl->setFormat('json');

	//get the Account Details
	if(!isset($_SESSION['accountDetail'])){
		$accountDetail = $stl->getAccountDetails()->getArrayData();
		$_SESSION['accountDetail'] = $accountDetail;
	}else{
		$accountDetail = $_SESSION['accountDetail'];
	}
// 	print_r ($accountDetail);die;
	
	echo '<h1>Welcome,'.$accountDetail['fullname'].'</h1><br/>';
	
	$apis = array(
		'optgroup1'=>'User',
		'getUsersBySearch',
		'getUserFeedback',
		'getPendingFeedback',
		'getUserDetails',
		'optgroup2'=>'Job',
		'getJobList',
		'getMyJobList',
		'getCategoryJobList',
		'optgroup3'=>'Profile',
		'getAccountDetails',
		'getProfileInfo',
		'setProfileInfo',
		'optgroup4'=>'Employer',
		'postNewProject',
		'postNewTrialProject',
		'postNewDraftProject',
		'chooseWinnerForProject',
		'getPostedProjectList',
		'getPostedProjectList(draft)',
		'inviteUserForProject',
		'updateProjectDetails',
		'eligibleForTrialProject',
		'publishDraftProject',
		'deleteDraftProject',
		'upgradeTrialProject',
		'optgroup5'=>'Freelancer',
		'getProjectListForPlacedBids',
		'placeBidOnProject',
		'retractBidFromProject',
		'acceptBidWon',
		'optgroup6'=>'Common',
		'requestCancelProject',
		'postFeedback',
		'postReplyForFeedback',
		'requestWithdrawFeedback',
		'getConfigVersion',
		'getTerms',
		'getCurrencies',
		'getProjectBudgetOptions',
		'optgroup7'=>'Payments',
		'getAccountBalanceStatus',
		'getAccountTransactionList',
		'requestWithdrawal',
		'createMilestonePayment',
		'transferMoney',
		'requestCancelWithdrawal',
		'cancelMilestone',
		'getAccountMilestoneList',
		'getAccountWithdrawalList',
		'requestReleaseMilestone',
		'releaseMilestone',
		'prepareTransfer',
		'getBalance',
		'getProjectListForTransfer',
		'getWithdrawalFees',
		'optgroup8'=>'Notification',
		'getNotification',
		'getNews',
		'optgroup9'=>'Project',
		'searchProjects',
		'getProjectFees',
		'getProjectDetails',
		'getBidsDetails',
		'getPublicMessages',
		'postPublicMessage',
		'getProjectBudgetConfig',
		'optgroup10'=>'Message',
		'getInboxMessages',
		'getSentMessages',
		'getUnreadCount',
		'sendMessage',
		'markMessageAsRead',
		'loadMessageThread'
	);
	
	$action = isset($_GET['action'])&&in_array($_GET['action'],$apis)?$_GET['action']:'getAccountDetails';
		
	switch($action){
	/*****************************************************************
	 * User
	 ****************************************************************/
	case 'getUsersBySearch':
		$stl->getUsersBySearch();//work
		break;
	case 'getUserFeedback':
		$param=array('username'=>'snowtigersoft');
		$stl->getUserFeedback($param);//work
		break;
	case 'getPendingFeedback':
		$stl->getPendingFeedback('P');//work
		break;
	case 'getUserDetails':
		$stl->getUserDetails(array('username'=>'stltest001'));//work
		break;
	
	/*****************************************************************
	 * Job
	 ****************************************************************/
	case 'getJobList':
		$stl->getJobList();//work
		break;
	case 'getMyJobList':
		$stl->getMyJobList();
		break;
	case 'getCategoryJobList':
		$stl->getCategoryJobList();
		break;
	
	/*****************************************************************
	 * Profile
	 ****************************************************************/
	case 'getAccountDetails':
		$stl->getAccountDetails();//work
		break;
	case 'getProfileInfo':
		$stl->getProfileInfo('1619663');//work
		break;
	case 'setProfileInfo':
		$param = array('keywords'=>'test');
		$stl->setProfileInfo($param);//work
		break;
	
	/*****************************************************************
	 * Employer
	 ****************************************************************/
	case 'postNewProject':
		$param = array('projectname'=>'SnowTtigerLib test project'.time(),
				'projectdesc'=>'SnowTtigerLib test project desc',
				'jobtypecsv'=>'CakePHP,PHP',
				'budgetoption'=>'1',
				'duration'=>'50',
				'isfeatured'=>'1',
				'isnonpublic'=>'1');
		$stl->method = 'POST';// can use POST when post project
		$stl->postNewProject($param); //work
		break;
	case 'postNewTrialProject':
		$param = array('projectname'=>'SnowTtigerLib test project'.time(),
				'projectdesc'=>'SnowTtigerLib test project desc',
				'jobtypecsv'=>'CakePHP,PHP',
				'budgetoption'=>'1',
				'duration'=>'50',
				'isfeatured'=>'1',
				'isnonpublic'=>'1');
		$stl->method = 'POST';// can use POST when post project
		$stl->postNewTrialProject($param); //work
		break;
	case 'postNewDraftProject':
		$param = array('projectname'=>'SnowTtigerLib test project'.time(),
				'projectdesc'=>'SnowTtigerLib test project desc',
				'jobtypecsv'=>'CakePHP,PHP',
				'budgetoption'=>'0',
				'budget'=>'50-500',
				'duration'=>'50',
				'isfeatured'=>'1',
				'isfulltime'=>'1',
				'isnonpublic'=>'1');
		$stl->method = 'POST';// can use POST when post project
		$stl->postNewDraftProject($param); ///work
		break;
	case 'chooseWinnerForProject':
		$stl->chooseWinnerForProject(33,'1619752');//work
		break;
	case 'getPostedProjectList':
		$param = array('status'=>1);
		$stl->getPostedProjectList($param);//work
		break;
	case 'getPostedProjectList(draft)':
		$param = array('status'=>1,'projectoption'=>'draft');
		$stl->getPostedProjectList($param);//work
		break;		
	case 'inviteUserForProject':
		$param = array('usernamecsv'=>'stltest001','projectid'=>34);
		$stl->inviteUserForProject($param);//do not work! always return 2008(Invalid invitaion)
		break;
	case 'updateProjectDetails':
		$param = array('projectid'=>18,'projectdesc'=>'update test');
		$stl->updateProjectDetails($param);//work
		break;
	case 'eligibleForTrialProject':
		$stl->eligibleForTrialProject();//work
		break;
	case 'publishDraftProject':
		$stl->publishDraftProject('131');//work
		break;
	case 'deleteDraftProject':
		$stl->deleteDraftProject('283');//work
		break;
	case 'upgradeTrialProject':
		$stl->upgradeTrialProject('131');
		break;
	
	/*****************************************************************
	 * Freelancer
	 ****************************************************************/
	case 'getProjectListForPlacedBids':
		$param = array('status'=>1);
		$stl->getProjectListForPlacedBids($param);//work
		break;
	case 'placeBidOnProject':
		$param = array('projectid'=>33,'amount'=>150,'days'=>2,'description'=>'SnowTigerLib placeBidOnProject test');
		$stl->placeBidOnProject($param);//work
		break;
	case 'retractBidFromProject':
		$stl->retractBidFromProject(18);//work
		break;
	case 'acceptBidWon':
		$stl->acceptBidWon(33);//work
		break;
	
	/*****************************************************************
	 * Common
	 ****************************************************************/
	case 'requestCancelProject':
		$param = array('projectid'=>1,'selectedwinner'=>'1619752','commenttext'=>'SnowTigerLib requestCancelProject test');
		$stl->requestCancelProject($param);//work
		break;
	case 'postFeedback':
		$param = array('rating'=>10,'feedbacktext'=>'SnowTigerLib postFeedback test','username'=>'stltest001','projectid'=>18);
		$stl->postFeedback($param);//work
		break;
	case 'postReplyForFeedback':
		$param = array('feedbacktext'=>'SnowTigerLib postReplyForFeedback test','username'=>'snowtigersoft','projectid'=>18);
		$stl->postReplyForFeedback($param);//work
		break;
	case 'requestWithdrawFeedback':
		$param = array('username'=>'stltest001','projectid'=>18);
		$stl->requestWithdrawFeedback($param);//work
		break;
	case 'getConfigVersion':
		$stl->getConfigVersion('projectfee');
		break;
	case 'getTerms':
		$stl->getTerms();
		break;
	case 'getCurrencies':
		$stl->getCurrencies();
		break;
	case 'getProjectBudgetOptions':
		$stl->getProjectBudgetOptions(2);
		break;
		
	/*****************************************************************
	 * Payments
	 ****************************************************************/
	case 'getAccountBalanceStatus':
		$stl->getAccountBalanceStatus();//work
		break;
	case 'getAccountTransactionList':
		$stl->getAccountTransactionList();//work
		break;
	case 'requestWithdrawal':
		$param = array(
			'amount'=>'100',
	 		'method'=>'paypal',
	 		'additionaltext'=>'SnowTigerLib test',
	 		'paypalemail'=>'snowtigersoft@126.com',
//	 		'mb_account'=>'',
	 		'description'=>'SnowTigerLib test',
	 		'country_code'=>'+86'
		);
		$stl->requestWithdrawal($param);//do not work,may be parameter error
		break;
	case 'createMilestonePayment':
		$param = array(
			'projectid'=>18,
	 		'amount'=>150,
	 		'tousername'=>'stltest001',
	 		'reasontext'=>'SnowTigerLib test',
	 		'reasontype'=>'full',
		);
		$stl->createMilestonePayment($param);// work
		break;
	case 'transferMoney':
		$param = array(
			'projectid'=>18,
	 		'amount'=>150,
	 		'tousername'=>'stltest001',
	 		'reasontext'=>'SnowTigerLib test',
	 		'reasontype'=>'full',
		);
		$stl->transferMoney($param);// work
		break;
	case 'requestCancelWithdrawal':
		$stl->requestCancelWithdrawal(1);// not test
		break;
	case 'cancelMilestone':
		$stl->cancelMilestone(170);//work
		break;
	case 'getAccountMilestoneList':
		$stl->getAccountMilestoneList();//work
		break;
	case 'getAccountWithdrawalList':
		$stl->getAccountWithdrawalList();//work
		break;
	case 'requestReleaseMilestone':
		$stl->requestReleaseMilestone(174);// work
		break;
	case 'releaseMilestone':
		$stl->releaseMilestone(192, $accountDetail['fullname']);// work
		break;
	case 'prepareTransfer':
		$stl->prepareTransfer(18,150,'1619752','full');//work
		break;
	case 'getBalance':
		$stl->getBalance();//work
		break;
	case 'getProjectListForTransfer':
		$stl->getProjectListForTransfer();//work
		break;
	case 'getWithdrawalFees':
		$stl->getWithdrawalFees();//work
		break;
	
	/*****************************************************************
	 * Notification
	 ****************************************************************/
	case 'getNotification':
		$stl->getNotification();//work
		break;
	case 'getNews':
		$stl->getNews();//work
		break;
		
	/*****************************************************************
	 * Project
	 ****************************************************************/
	case 'searchProjects':
		$stl->searchProjects();//work
		break;
	case 'getProjectFees':
		$stl->getProjectFees();//work
		break;
	case 'getProjectDetails':
		$stl->getProjectDetails(18);//work
		break;
	case 'getBidsDetails':
		$stl->getBidsDetails(18);//work
		break;
	case 'getPublicMessages':
		$stl->getPublicMessages(18);//work
		break;
	case 'postPublicMessage':
		$param = array('projectid'=>18,'messagetext'=>'SnowTigerLib postPublicMessage test');
		$stl->method = 'POST';
		$stl->postPublicMessage($param);//work
		break;
	case 'getProjectBudgetConfig':
		$stl->getProjectBudgetConfig();//work
		break;
	
	/*****************************************************************
	 * Message
	 ****************************************************************/
	case 'getInboxMessages':
		$stl->getInboxMessages();//work
		break;
	case 'getSentMessages':
		$stl->getSentMessages();//work
		break;
	case 'getUnreadCount':
		$stl->getUnreadCount();//work
		break;
	case 'sendMessage':
		$param = array('projectid'=>18,'messagetext'=>'SnowTigerLib sendMessage test','username'=>'snowtigersoft');
		$stl->method = 'POST';
		$stl->sendMessage($param);//work
		break;
	case 'markMessageAsRead':
		$stl->markMessageAsRead(45);//work
		break;
	case 'loadMessageThread':
		$param = array('projectid'=>18,'betweenuserid'=>'snowtigersoft');
		$stl->loadMessageThread($param);//work
		break;
	}
	
	//print the selector
	echo '<strong>Notice:</strong> Only a few API can work here,because you need change their parameters yourself.<br/>' .
			'Please <a href="http://code.google.com/p/snowtigerlib/downloads/list"  target="_blank">download</a> the source code and try it yourself!<br/><br/>';
	$out = 'Call API: <select onChange="location.href = \'http://'.$_SERVER ['HTTP_HOST'].$_SERVER['PHP_SELF'].'?action=\'+this.value;">';
	foreach($apis as $k => $api){
		if(is_string($k)){
			if($k != 'optgroup1')
				$out .= '</optgroup>';
			$out .= '<optgroup label="'.$api.'">';
		}else{
			if($action == $api)
				$out .= '<option value="'.$api.'" selected>'.$api.'</option>';
			else
				$out .= '<option value="'.$api.'">'.$api.'</option>';
		}
	}
	$out .= '</optgroup>';
	$out .= '</select>';

	echo $out;
	print_r($stl->getArrayData());die;
	if($stl->getErrorInfo()){
		dump($stl->getParam(),'Parameters');
		dump($stl->getArrayData(),'Array Format');
		dump($stl->getXmlData(),'Xml Format');
		dump($stl->getJsonData(),'Json Format');
	}else{
		dump($stl->getCurCall(),'Current Call');
		dump($stl->getParam(),'Parameters');
		dump($stl->getArrayData(),'Array Format');
		dump($stl->getXmlData(),'Xml Format');
		dump($stl->getJsonData(),'Json Format');
	}
}

/**
* output vars,use in debug
*
* @package Core
*
* @param mixed $vars 
* @param string $label
* @param boolean $return
*/
function dump($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label != '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
//         $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) { return $content; }
    echo $content;
    return null;
}
?>
<script type="text/javascript" src="http://js.tongji.linezing.com/1706546/tongji.js"></script><noscript><a href="http://www.linezing.com"><img src="http://img.tongji.linezing.com/1706546/tongji.gif"/></a></noscript>