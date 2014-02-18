<?php
/**
 * THIS IS AN EXAMPLE FOR TOKEN BASED AUTHENTICATION METHOD
 */
/*
 $Proposal['amt']	= 10;
$Proposal['amt_per_time']	='hour';
$Proposal['bid_desc']	= 'I can help you, because i did it before';
 
$params_string  = http_build_query($Proposal);
print $params_string;
die;*/

 
require 'oDeskAPI.lib.php';
			   	
$secret     = '6c21f6884bcbd3cc'; // your secret key, keep it secure according to logic and                                  // architecture of your project
$api_key    = '8f3e8ef823d8a928240d48309f1cf054'; // your application key
$company    = 'freelancerfm'; // your company's name

$odesk_user = 'freelancerfm';
$odesk_pass = 'Emp12345';

$url        = 'http://www.odesk.com/api/team/v1/teamrooms/'.$company.'.json';

// our app
$api = new oDeskAPI($secret, $api_key);


$api->auth(); // auth process: here your app will be redirected to odesk,
//$api->auth2(); // auth process: here your app will be redirected to odesk,
              // where you need to login and authorize app, if it hasn't been authorized yet


// add additional requests to URI
$params = array('offer_data' => array(
										'job__reference'=>201997962				
					));
					
$params = array('offer_data' => array(
										'job__reference'=>202040941				
					));
					
					
			

$url = 'https://www.odesk.com/api/hr/v2/offers.json';

$url = 'https://www.odesk.com/api/hr/v2/offers/202040941.json';
//$url = 'https://www.odesk.com/api/hr/v2/users/me.json';
			
//$url = 'https://www.odesk.com/api/auth/v1/info.json';
//$url = 'https://www.odesk.com/api/hr/v2/jobs.json';
//$url = 'https://www.odesk.com/api/hr/v2/userroles.json';
// make GET request
$response = $api->get_request($url, array());
//$response = $api->post_request($url, $params);
print_r($response);
$data = json_decode($response);

die('1');

var_dump($data->teamroom->snapshot[0]->report_url);
?>
