<?php
    session_start();
//	include_once ('../library/Zend/Registry.php');
//	include_once ('../application/models/Freelancer.php');
//	include_once ('../application/models/General.php');

	

    $config['base_url']             =   'http://freelancer.fm/linkedin/auth.php';
    $config['callback_url']         =   'http://freelancer.fm/linkedin/demo.php';
    $config['linkedin_access']      =   'qbj09mlp0ade';
    $config['linkedin_secret']      =   'wrLYQ1aJTqDJdR2l';

    include_once "linkedin.php";
   
    
    # First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
    $linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );
    //$linkedin->debug = true;

   if (isset($_REQUEST['oauth_verifier'])){
        $_SESSION['oauth_verifier']     = $_REQUEST['oauth_verifier'];

        $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
        $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
        $linkedin->getAccessToken($_REQUEST['oauth_verifier']);

        $_SESSION['oauth_access_token'] = serialize($linkedin->access_token);
        header("Location: " . $config['callback_url']);
        exit;
   }
   else{
        $linkedin->request_token    =   unserialize($_SESSION['requestToken']);
        $linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
        $linkedin->access_token     =   unserialize($_SESSION['oauth_access_token']);
   }


    # You now have a $linkedin->access_token and can make calls on behalf of the current member
    $xml_response = $linkedin->getProfile("~:(id,first-name,last-name,headline,picture-url)");

	//$modelFromGeneral = new Application_Model_General();
	//print  $modelFromGeneral->BidsSum();
	
    echo '<pre>';
    echo 'My Profile Info';
    echo $xml_response;
	$xml = simplexml_load_string($xml_response);
    echo $xml->{'first-name'};
	
	/*id
	first-name
	last-name
	headline
	picture-url*/
	
	
    echo '<br />';
    echo '</pre>';

	$_SESSION['salut'] = 'salut';

    $search_response = $linkedin->search("?company-name=facebook&count=10");
    //$search_response = $linkedin->search("?title=software&count=10");

    //echo $search_response;
    $xml = simplexml_load_string($search_response);

    echo '<pre>';
    echo 'Look people who worked in facebook';
    print_r($xml);
    echo '</pre>';
?>
