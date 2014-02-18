<?php
 
require_once('elance-auth-lib.php');
 
if (!isset($_GET["code"])) {
    die("Require the code parameter to validate!");
}
 
$code = $_GET["code"];
$elance_auth = new ElanceAuthentication();
$json = $elance_auth->GetAccessToken("511d7902e4b0c1666cb1238f", "4jMn7TtctT48RZFslvNLuA", $code);
 
$acces_token = $json->data->access_token;

		$ProposalArr = array(
			'action'			=>	'bidSubmit',
			'baseRate'			=>	888,
			'bidDesc'			=>	'I have a 3 years experience workin in wordpress, I can help you',
			'bid_companyid'		=>	4905439,
			'bid_desc_type'		=>	'plaintext', 
			'bid_userid'		=>	4905439,
			'deliveryDate'		=>	13002,
			'jobid'				=>	37530685,
			'proposalAmount'	=>	973.15
		);

$Proposal = http_build_query($ProposalArr);

//$Proposal ='jobid=37530685&backurl=aHR0cHM6Ly93d3cuZWxhbmNlLmNvbS9yL2pvYnMvcS13ZWI%3D&bid_userid=4905439&bid_companyid=4905439&bid_desc_type=plaintext&fileGroupId=&baseRate=888&proposalAmount=973.15&deliveryDate=13002&bidDesc=I%20have%20a%203%20years%20experience%20workin%20in%20wordpress%2C%20I%20can%20help%20you&action=bidSubmit';
		

$arr = $elance_auth->ExecRequest('https://www.elance.com/php/bid/main/proposalSubmitAHR.php?t=' . time(), $acces_token,$Proposal );
var_dump($arr);
//Output code
echo "Access token is " . $json->data->access_token . "<p/>";

	$url = 'https://api.elance.com/api2/profiles/my?access_token='.$acces_token;
		 
		if (($r = @curl_init($url)) == false) {
			header("HTTP/1.1 500", true, 500);
			die("Cannot initialize cUrl session. Is cUrl enabled for your PHP installation?");
		}
		 
		// Set cUrl to return text as a variable, instead of directly to the browser.
		$curl_options = array (
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1
			);
		curl_setopt_array($r, $curl_options);
		 
		// Access API, and check results.
		$json_txt = curl_exec($r);
		if (curl_errno($r) > 0) {
			header("HTTP/1.1 500", true, 500);
			die();
		} else {
			$http_response = intval(curl_getinfo($r, CURLINFO_HTTP_CODE));
			if ($http_response != 200) {
				// Pass on any descriptive error information from the Elance server to 
				// the client.
				header("HTTP/1.1 " . $http_response, true, $http_response);
				header("Content-Type: application/json", true);
				echo($json_txt);
				flush();
				die();
			}
		}
		 
		if ($json_txt == false) {
			header("HTTP/1.1 500", true, 500);
			die("Cannot retrieve Elance API URL using cUrl. URL: " . $url);
		}
		curl_close($r); 
		 
		header("Content-Type: application/json", true);

		$Profile = (json_decode($json_txt, true));

		$_SESSION['elance']['profile'] = $Profile;
		
		print_r($Profile);

?>