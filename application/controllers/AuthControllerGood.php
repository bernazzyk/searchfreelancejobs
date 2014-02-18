<?php
    class AuthController extends Zend_Controller_Action
    { 		
		public function elanceAction() {
			Zend_Session::start(); 
			require_once('ElanceExtraction/elance-auth-lib.php');
			$auth = Zend_Auth::getInstance();
			$authStorage = $auth->getStorage();
			$user_id = (int) $authStorage->read();
			$platform_id = 3;
			if($user_id!=0)
			{
				$modelFromGeneral = new Application_Model_General();
				$UserPlatformData = $modelFromGeneral->getUserPlatformData($user_id,$platform_id);
				if(isset($UserPlatformData['access_key']))
				{
					$_SESSION['elance']['access_key'] = $UserPlatformData['access_key'];
					$url = SITE_URL . "auth/callbackelance";
				}
				else
				{
					error_reporting(E_ALL);
					 
					$elance_auth = new ElanceAuthentication();
					$url = $elance_auth->RequestAccessCode("4f21faa83340a00328000001", SITE_URL . "auth/callbackelance");
					//header("Location: " . $url);
				}
				 $this->_redirect($url); //sau...
			}
		}
		
		public function callbackelanceAction()
		{
			Zend_Session::start(); 
			require_once('ElanceExtraction/elance-auth-lib.php');
			if(isset($_SESSION['elance']['access_key']))
			{
				$acces_token = $_SESSION['elance']['access_key'];
				//print $acces_token; die;
			}
			else {
		
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
	 
				if (!isset($_MY_GET["code"])) {
					die("Require the code parameter to validate!");
				}
				 
				$code = $_MY_GET["code"];
				$elance_auth = new ElanceAuthentication();
				$json = $elance_auth->GetAccessToken("4f21faa83340a00328000001", "tYL5A3ymBkl0zjwokx4BjA", $code);
				 
				$acces_token = $json->data->access_token;	
			}

			//Output code
			$url = 'https://api.elance.com/api2/profiles/my?access_token='.$acces_token;
						
			$modelFromGeneral = new Application_Model_General();
				
			$_SESSION['connected_platform'][3]=1;			
			$_SESSION['profile']['elance'] = $modelFromGeneral->cURLExtractJSONContent($url);
		
			header("Location: " . '/auth/closewindowopener/');
			die;
		}
		
		public function odeskAction()
		{
 			Zend_Session::start(); 
			
			$auth = Zend_Auth::getInstance();
			$authStorage = $auth->getStorage();
			$user_id = (int) $authStorage->read();
			$platform_id = 4;
			if($user_id!=0)
			{
				$consumerKey = '8f3e8ef823d8a928240d48309f1cf054'; // consumer key, got in console
				$consumerSec = '6c21f6884bcbd3cc'; // consumer secret, got in console with key
					
				//$consumerKey = 'a42d40a24fe99df1466bb781ce13c4ac'; // consumer key, got in console
				//$consumerSec = '5e722d5383eff298'; // consumer secret, got in console with key
				$sigMethod   = 'HMAC-SHA1'; // signature method, e.g. HMAC-SHA1
				$callbackUrl = SITE_URL . 'auth/odesk/'; // callback url, full url to your script, e.g. http://localhost/oauth.php

				$requestTokenUrl        = 'https://www.odesk.com/api/auth/v1/oauth/token/request';
				$accessTokenUrl         = 'https://www.odesk.com/api/auth/v1/oauth/token/access';
				$userAuthorizationUrl   = 'https://www.odesk.com/services/api/auth';
					
				$modelFromGeneral = new Application_Model_General();
				$UserPlatformData = $modelFromGeneral->getUserPlatformData($user_id,$platform_id);
				if(isset($UserPlatformData['access_token']) && isset($UserPlatformData['access_token_secret']))
				{
					$_SESSION['odesk']['access_token'] = $UserPlatformData['access_token'];
					$_SESSION['odesk']['access_token_secret'] = $UserPlatformData['access_token_secret'];
				}
				else 
				{
					$config = array(
							'version'               => '1.0',
							'callbackUrl'           => $callbackUrl,
							'signatureMethod'       => $sigMethod,
							'requestTokenUrl'       => $requestTokenUrl,
							'accessTokenUrl'        => $accessTokenUrl,
							'userAuthorizationUrl'  => $userAuthorizationUrl,
							'consumerKey'           => $consumerKey,
							'consumerSecret'        => $consumerSec
					);

					$consumer = new Zend_Oauth_Consumer($config);
					 
					// Get request token
					if (!isset($_SESSION['ODESK_REQUEST_TOKEN']) && !isset($_SESSION['ODESK_ACCESS_TOKEN'])) {
						$token = $consumer->getRequestToken();

						$_SESSION['ODESK_REQUEST_TOKEN'] = serialize($token);
						$consumer->redirect();
					}
					
					$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
					parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
												
					// Get access token
					if (!empty($_MY_GET) && isset($_SESSION['ODESK_REQUEST_TOKEN'])) {
						$token = $consumer->getAccessToken(
									 $_MY_GET,
									 unserialize($_SESSION['ODESK_REQUEST_TOKEN'])
								 );
								 
						// Serialize and save token
						$_SESSION['ODESK_ACCESS_TOKEN'] = serialize($token);
						// Now that we have an Access Token, we can discard the Request Token
						$_SESSION['ODESK_REQUEST_TOKEN'] = null;	
					}
					
					// Make an example GET request to API
					// We configure parameters and Zend_Http_Client manually,
					// but you can use your own preferred method and logic
					if (!empty($_SESSION['ODESK_ACCESS_TOKEN'])) {
						
						//It seems that this does mean that we have already login into odesk account
						
						$token = unserialize($_SESSION['ODESK_ACCESS_TOKEN']);
						$t  = $token->getToken();
						$ts = $token->getTokenSecret();
						
						$_SESSION['odesk']['access_token'] = $t;
						$_SESSION['odesk']['access_token_secret'] = $ts;
						
					}
				}
				
				if(isset($_SESSION['odesk']['access_token']) && isset($_SESSION['odesk']['access_token_secret']))
				{					
					$t = $_SESSION['odesk']['access_token'];
					$ts = $_SESSION['odesk']['access_token_secret'];
						
					$secret_key     = $consumerSec . '&' . $ts;
				
					$params = array(
					'oauth_consumer_key'    => $consumerKey,
					'oauth_signature_method'=> $sigMethod,
					'oauth_timestamp'       => time(),
					'oauth_nonce'           => substr(md5(microtime(true)), 5),
				 //   'oauth_callback'        => $callbackUrl,
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
				$_SESSION['profile']['odesk'] = $modelFromGeneral->cURLExtractJSONContent($url);
				$_SESSION['connected_platform'][4] = 1;
				
				header("Location: " . '/auth/closewindowopener/');
				die;					
			}
			}
		}
		
		public function connectToPlatform($username,$password,$platform, $user_id)
		{	
			
			$Curl_Obj = curl_init();
			if($platform==6)
			{
				$login_url = 'http://www.getacoder.com/users/onlogin.php';
				$post_data = 'username='.$username.'&passwd='.$password;
			} else if($platform==10) {
				$login_url = 'https://secure.freelance.com/j_acegi_security_check';
				$post_data = 'j_username='.$username.'&j_password='.$password;
			} else if($platform==7) {
				$login_url = 'http://jobs.freelanceswitch.com/session';
				$post_data = 'login='.$username.'&password='.$password.'&return_to=&commit=Log+In';
			}
			else if($platform==9) {
				$login_url = 'https://www.ifreelance.com/user/login.aspx';
				$post_data = '__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwULLTE0NjE1NjE1NDAPFgIeFFJlZmVycmVyUGF0aEFuZFF1ZXJ5BQEvFgJmD2QWAgIDD2QWDAIDDxYCHgdWaXNpYmxlaGQCBA8WAh4LXyFJdGVtQ291bnQCBRYKZg9kFgJmDxUDIS9maW5kL3Byb2plY3RzL2Jyb3dzZS5hc3B4P3NjPTI4OQ5XZWJzaXRlIERlc2lnbgIxM2QCAQ9kFgJmDxUDIS9maW5kL3Byb2plY3RzL2Jyb3dzZS5hc3B4P3NjPTI1MhNXZWJzaXRlIFByb2dyYW1taW5nAjExZAICD2QWAmYPFQMhL2ZpbmQvcHJvamVjdHMvYnJvd3NlLmFzcHg%2Fc2M9MTM2IEludGVybmV0IE1hcmtldGluZyAvIEFkdmVydGlzaW5nATVkAgMPZBYCZg8VAyEvZmluZC9wcm9qZWN0cy9icm93c2UuYXNweD9zYz0yNTcXT3RoZXIgV3JpdGluZyAvIEVkaXRpbmcBNGQCBA9kFgJmDxUDIS9maW5kL3Byb2plY3RzL2Jyb3dzZS5hc3B4P3NjPTI2ORlBcnRpY2xlIFdyaXRpbmcgLyBFZGl0aW5nATRkAgcPFgIfAWhkAgkPFgIeBGhyZWYFFC9idXllci9yZWdpc3Rlci5hc3B4ZAIKDxYCHwMFKmh0dHBzOi8vd3d3LmlmcmVlbGFuY2UuY29tL3VzZXIvbG9naW4uYXNweGQCCw8WAh8BaBYGZg8WAh8DBTFodHRwOi8vd3d3LmlmcmVlbGFuY2UuY29tL215L21lc3NhZ2VzL2FsZXJ0cy5hc3B4ZAIBDxYCHwFoZAICDxYCHwFoZBgBBR5fX0NvbnRyb2xzUmVxdWlyZVBvc3RCYWNrS2V5X18WAgU8Y3RsMDAkbWFpbkJsb2NrQ29udGVudFBsYWNlSG9sZGVyJGxpTG9naW4kcmVtZW1iZXJNZUNoZWNrQm94BTJjdGwwMCRtYWluQmxvY2tDb250ZW50UGxhY2VIb2xkZXIkbGlMb2dpbiRidG5Mb2dpbg%3D%3D&ctl00%24mainBlockContentPlaceHolder%24liLogin%24txtLoginName='.$username.'&ctl00%24mainBlockContentPlaceHolder%24liLogin%24txtPassword='.$password.'&ctl00%24mainBlockContentPlaceHolder%24liLogin%24rememberMeCheckBox=on&ctl00%24mainBlockContentPlaceHolder%24liLogin%24btnLogin.x=31&ctl00%24mainBlockContentPlaceHolder%24liLogin%24btnLogin.y=6&ctl00%24searchSelectBox=1';
			}
			else if($platform==5) {
				$login_url = 'https://www.guru.com/login.aspx';
				//$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwULLTE0NDI5OTY0MjAPZBYEAgMPFgIeBFRleHQFuQQ8IS0tIEdvb2dsZSBUYWcgTWFuYWdlciAtLT4NCjxub3NjcmlwdD48aWZyYW1lIHNyYz0iLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vbnMuaHRtbD9pZD1HVE0tTEYyMiINCmhlaWdodD0iMCIgd2lkdGg9IjAiIHN0eWxlPSJkaXNwbGF5Om5vbmU7dmlzaWJpbGl0eTpoaWRkZW4iPjwvaWZyYW1lPjwvbm9zY3JpcHQ%2BDQo8c2NyaXB0PihmdW5jdGlvbih3LGQscyxsLGkpe3dbbF09d1tsXXx8W107d1tsXS5wdXNoKHsnZ3RtLnN0YXJ0JzoNCm5ldyBEYXRlKCkuZ2V0VGltZSgpLGV2ZW50OidndG0uanMnfSk7dmFyIGY9ZC5nZXRFbGVtZW50c0J5VGFnTmFtZShzKVswXSwNCmo9ZC5jcmVhdGVFbGVtZW50KHMpLGRsPWwhPSdkYXRhTGF5ZXInPycmbD0nK2w6Jyc7ai5hc3luYz10cnVlO2ouc3JjPQ0KJy8vd3d3Lmdvb2dsZXRhZ21hbmFnZXIuY29tL2d0bS5qcz9pZD0nK2krZGw7Zi5wYXJlbnROb2RlLmluc2VydEJlZm9yZShqLGYpOw0KfSkod2luZG93LGRvY3VtZW50LCdzY3JpcHQnLCdkYXRhTGF5ZXInLCdHVE0tTEYyMicpOzwvc2NyaXB0Pg0KPCEtLSBFbmQgR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPmQCBw8WAh8ABdwEPHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPgogdmFyIGdhSnNIb3N0ID0gKCgiaHR0cHM6IiA9PSBkb2N1bWVudC5sb2NhdGlvbi5wcm90b2NvbCkgPyAiaHR0cHM6Ly9zc2wuIiA6ICJodHRwOi8vd3d3LiIpOwogZG9jdW1lbnQud3JpdGUodW5lc2NhcGUoIiUzQ3NjcmlwdCBzcmM9JyIgKyBnYUpzSG9zdCArICJnb29nbGUtYW5hbHl0aWNzLmNvbS9nYS5qcycgdHlwZT0ndGV4dC9qYXZhc2NyaXB0JyUzRSUzQy9zY3JpcHQlM0UiKSk7CiA8L3NjcmlwdD4KIDxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0Ij4KdHJ5ewogIHZhciBwYWdlVHJhY2tlciA9IF9nYXQuX2dldFRyYWNrZXIoIlVBLTQzMzY4OS00Iik7CiAgIHBhZ2VUcmFja2VyLl9zZXREb21haW5OYW1lKCIuZ3VydS5jb20iKTsKICBwYWdlVHJhY2tlci5fdHJhY2tQYWdldmlldygpOwogfSBjYXRjaChlcnIpe30KIGZ1bmN0aW9uIGdvb2dsZVNpdGVTZWFyY2gocGFybXMpe3RyeXt2YXIgcGFnZVRyYWNrZXIgPSBfZ2F0Ll9nZXRUcmFja2VyKCJVQS00MzM2ODktNCIpO3BhZ2VUcmFja2VyLl90cmFja1BhZ2V2aWV3KCIvc2VhcmNoPyIrcGFybXMpO30gY2F0Y2goZXJyKXt9fTwvc2NyaXB0PmRkG%2F04%2FQOTUFoJbKJavc7EfUH8o28%3D&__EVENTVALIDATION=%2FwEWBQKqxMb%2FDgLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC1384HZag%2BkKmLmhZ0w040qs4aPt&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox='.$username.'&ucLogin%24txtPassword%24txtPassword_TextBox='.$password.'&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';
				$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwUKLTg2MzEwMzM4Nw9kFgICAw8WAh4EVGV4dAW5BDwhLS0gR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPg0KPG5vc2NyaXB0PjxpZnJhbWUgc3JjPSIvL3d3dy5nb29nbGV0YWdtYW5hZ2VyLmNvbS9ucy5odG1sP2lkPUdUTS1MRjIyIg0KaGVpZ2h0PSIwIiB3aWR0aD0iMCIgc3R5bGU9ImRpc3BsYXk6bm9uZTt2aXNpYmlsaXR5OmhpZGRlbiI%2BPC9pZnJhbWU%2BPC9ub3NjcmlwdD4NCjxzY3JpcHQ%2BKGZ1bmN0aW9uKHcsZCxzLGwsaSl7d1tsXT13W2xdfHxbXTt3W2xdLnB1c2goeydndG0uc3RhcnQnOg0KbmV3IERhdGUoKS5nZXRUaW1lKCksZXZlbnQ6J2d0bS5qcyd9KTt2YXIgZj1kLmdldEVsZW1lbnRzQnlUYWdOYW1lKHMpWzBdLA0Kaj1kLmNyZWF0ZUVsZW1lbnQocyksZGw9bCE9J2RhdGFMYXllcic%2FJyZsPScrbDonJztqLmFzeW5jPXRydWU7ai5zcmM9DQonLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vZ3RtLmpzP2lkPScraStkbDtmLnBhcmVudE5vZGUuaW5zZXJ0QmVmb3JlKGosZik7DQp9KSh3aW5kb3csZG9jdW1lbnQsJ3NjcmlwdCcsJ2RhdGFMYXllcicsJ0dUTS1MRjIyJyk7PC9zY3JpcHQ%2BDQo8IS0tIEVuZCBHb29nbGUgVGFnIE1hbmFnZXIgLS0%2BZGTEvZJMBaDQTuYZNi2b%2BZTWAdUHDw%3D%3D&__EVENTVALIDATION=%2FwEWBQK%2BrNvoBwLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC0WW7GcvKbKyjAOby%2FugKuooJj1a&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox=Consultingonline&ucLogin%24txtPassword%24txtPassword_TextBox=165c3660&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';			
			}
			
			else if($platform==8) {
				$login_url = 'https://www.peopleperhour.com/site/login';
				//$post_data = 'YII_CSRF_TOKEN=9b9329bce9d820582149bd4ecaf512cd9ffe09b7&LoginForm%5Bemail%5D='.$username.'&LoginForm%5Bpassword%5D='.$password.'&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=Log+In';
			}
					
			// Enable Posting.
			//curl_setopt($Curl_Obj, CURLOPT_POST, 1);
							
			$UserId = (int)$_SESSION['Zend_Auth']['storage'];
			$cookieFile = dirname(__FILE__).'/cookies/'.$UserId.'cookie.txt';
			
			//print $cookieFile;
			//$cookieFile = 'cookie.txt';
			if(!file_exists( $cookieFile)) {
				$fh = fopen($cookieFile, "w");
				fwrite($fh, '');
				fclose($fh);
				chmod($cookieFile, 0777);
			}
			
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, $cookieFile); 
			curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, $cookieFile); 
			//curl_setopt( $Curl_Obj, CURLOPT_COOKIE, 'PHPSESSID=llco5540lkp5h3j89nce6u9fh2; path=/' );
			//curl_setopt( $Curl_Obj, CURLOPT_COOKIE, 'YII_CSRF_TOKEN=7d463000b6ea8ed5727ee62914ac3cbb0c579e68; path=/' );
			
			// Set the browser you will emulate
			//$userAgent = 'Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';
			$userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0';
			curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);

			// Don't include the header in the output.
			curl_setopt ($Curl_Obj, CURLOPT_HEADER, 1);

			// Allow referer field when following Location redirects.
			curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);

			// Follow server redirects.
			//curl_setopt($Curl_Obj, CURLOPT_FOLLOWLOCATION, true);

			// Return output as string.
			curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);
					
			curl_setopt($Curl_Obj, CURLOPT_VERBOSE, 1);
			
			if($platform==8)
			{
				curl_setopt($Curl_Obj, CURLOPT_POST, 0);
				curl_setopt ($Curl_Obj, CURLOPT_URL, $login_url);
				$response = curl_exec ($Curl_Obj);
						
				$header_size = curl_getinfo($Curl_Obj, CURLINFO_HEADER_SIZE);
				$header = substr($response, 0, $header_size);
				if(strpos($header,'YII_CSRF_TOKEN=')!==false)
				{
					$start = strpos($header,'YII_CSRF_TOKEN=');
					$end = strpos($header, ';', $start);
					$YII_CSRF_TOKEN = substr($header, $start, ($end-$start));
				} else { //Daca nu e in headers inseamna ca ea deja este in fisierul [user_id]cookies.ttx
					$lines = file($cookieFile);
					//print_r($lines);
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
				}
				$post_data = $YII_CSRF_TOKEN.'&LoginForm%5Bemail%5D='.$username.'&LoginForm%5Bpassword%5D='.$password.'&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=Log+In';
				curl_setopt($Curl_Obj, CURLOPT_POST, 1);
			}
			// Set up post fields from login form.
			curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 

			// Set the url to which the data will be posted.
			curl_setopt ($Curl_Obj, CURLOPT_URL, $login_url);
				
			//print_r(get_headers($login_url));
				 
			// Execute the post and get the output.
			$response = curl_exec ($Curl_Obj);
			
			//print $response;
					
			$http_code = curl_getinfo($Curl_Obj, CURLINFO_HTTP_CODE);
					
			$header_size = curl_getinfo($Curl_Obj, CURLINFO_HEADER_SIZE);
			$header = substr($response, 0, $header_size);
			$body = substr($response, $header_size);
					
			curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, "");
					
			$connected_success = false;

			if($platform==5)
			{
				if(strpos($header,'302 Found')!==false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform] = 1;
					$connected_success = true;
				}
			}
			else if($platform==8)
			{
				if(strpos($header,'Location: https://www.peopleperhour.com/dashboard')!==false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform] = 1;
					$connected_success = true;
				}
			}
			else if($platform==6 || $platform==9)
			{
				if(strpos($header,'Set-Cookie:')!==false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform] = 1;
					$connected_success = true;
				}
			}
			else if($platform==10)
			{
				if(strpos($header,'?login_error=1')===false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform] = 1;
					$connected_success = true;
				}
			}
			else if($platform==7)
			{
				if(strpos($header,'ETag:')===false) //That means sign in process was successfully
				{
					$_SESSION['connected_platform'][$platform] = 1;
					$connected_success = true;
				}
			}
			
			if($connected_success)
			{	
				$modelFromGeneral = new Application_Model_General();
				$modelFromGeneral->ConnectUserPlatform($user_id,$platform);
				$modelFromGeneral->insertRemotePlatformUser($platform, $user_id, $username,$password);
				return true;
			}
			return false;

			/*	
			curl_setopt ($Curl_Obj, CURLOPT_URL, 'https://www.peopleperhour.com/dashboard');
			// Execute query and obtain content.
			$output = curl_exec($Curl_Obj);
				   
			echo $output;
			*/
		}
		
		public function connectPlatformsForUser($user_id)
		{
			$modelFromGeneral = new Application_Model_General();
			$PlatformsInfoArray = $modelFromGeneral->getAllUserConnectedPlatforms($user_id);
			
			//print_r($PlatformsInfoArray);
			
			//die('here1');
			
			foreach($PlatformsInfoArray as $Values)
			{
				$this->connectToPlatform($Values['username'], $Values['password'], (int)$Values['platform_id'], $user_id);
			}
		}
		
		
		public function remoteAction()
		{
			session_start();

			if(isset($_SESSION['Zend_Auth']['storage']) && (int)$_SESSION['Zend_Auth']['storage']!=0)
			{
				$user_id = (int)$_SESSION['Zend_Auth']['storage'];
				$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
				parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
				
				if(isset($_MY_GET['username'])&& isset($_MY_GET['password']))
				{	
					$username = $_MY_GET['username'];
					$password = $_MY_GET['password'];
					$platform = (int)$_MY_GET['pl'];
					
					if($this->connectToPlatform($username,$password,$platform,$user_id))
					{
						die('1');
					}
				}
			}
		}
		

		
		public function closewindowopenerAction() {
			echo("<script>window.opener.location.href = window.opener.location.href; self.close ();</script>");
			die;
		}
				

		
		public function odeskprofileAction()
		{
			$xml_string = '<iframe id="odesk_profile" src="https://www.odesk.com/api/auth/v1/info.json"></iframe>';
			$javascript = "<script>alert($('#odesk_profile').contents().find('html').html());</script>";
			print $xml_string.$javascript;
			//die;
		}
		
        public function loginAction()
        {	
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
                //    'email',
                    'id',
                    'password',
					'(?) AND `confirmed` = 1'
                    );
				
				$email = $loginForm->getValue('email');
				$modelFromGeneral = new Application_Model_General();
				$user_id = $modelFromGeneral->getUserIdByEmail($email);
				
				if($user_id)
				{
					$adapter->setIdentity($user_id);
					//$adapter->setIdentity($loginForm->getValue('email'));
					//$password = md5(md5($UserInfo['password']) . 'dfd67fbcf54d99ef2dc2f900610255e4');
					$adapter->setCredential(md5(md5($loginForm->getValue('password')). 'dfd67fbcf54d99ef2dc2f900610255e4') );
				   
	//			   $adapter->setCredential($loginForm->getValue('password'));
		 
					$auth   = Zend_Auth::getInstance();
					$result = $auth->authenticate($adapter);
		 
					if ($result->isValid()) {
					
						$this->connectPlatformsForUser($user_id);
						
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
						$this->view->message = '<span class="f_error_msg">Email or password is wrong</span>';
					}
				} else {
					$this->view->message = '<span class="f_error_msg">Email or password is wrong</span>';
				}
            }
     
			$this->view->form = $loginForm;
        }
		
	public function loginajaxAction()
    {
		 $db = $this->_getParam('db');
        if ( $this->getRequest()->isPost() )
        {
            $email = $this->_request->getPost( 'email' );
            $password = $this->_request->getPost( 'password' );
            
            if ( empty( $email ) || empty( $password ) )
            {
                //$this->view->errors[] = "Please provide your e-mail address and password.";
				 $this->_helper->json( array( 'success' => false, 'message' => 'Please provide your e-mail address and password.' ) );
            }
            else
            {
               // $authAdapter = new Zend_Auth_Adapter_DbTable( Zend_Registry::get( 'db' ) );
                
				  $adapter = new Zend_Auth_Adapter_DbTable(
                    $db,
                    'accounts',
                    'id',
                    'password',
					'(?) AND `confirmed` = 1'
                    );
				
				$modelFromGeneral = new Application_Model_General();
				$user_id = $modelFromGeneral->getUserIdByEmail($email);
				
				if($user_id)
				{
				
					$adapter->setIdentity($user_id);
					//$password = md5(md5($UserInfo['password']) . 'dfd67fbcf54d99ef2dc2f900610255e4');
					$adapter->setCredential(md5(md5($password). 'dfd67fbcf54d99ef2dc2f900610255e4') );
					
					/*$authAdapter
						->setTableName( 'account' )
						->setIdentityColumn( 'email' )
						->setCredentialColumn( 'password' )
						->setCredentialTreatment( 'MD5(?)' )
						->setIdentity( $email )
						->setCredential( $password );*/
					
					$auth = Zend_Auth::getInstance();
					
					$result = $auth->authenticate( $adapter );
					
					// Did the participant successfully login?
					if ( $result->isValid() )
					{
						$this->connectPlatformsForUser($user_id);
						$this->_helper->json( array( 'success' => true ) );
					}
					else
					{
						$this->_helper->json( array( 'success' => false, 'message' => 'Login failed. Have you confirmed your account?' ) );
					}
				} else
				{
					$this->_helper->json( array( 'success' => false, 'message' => 'Login failed. Have you confirmed your account?' ) );
				}
            }
        }
    }
		
		
		public function logoutAction()  
		{  
			// clear everything - session is cleared also!  
			Zend_Auth::getInstance()->clearIdentity(); 
			session_destroy();
			$this->_redirect('/');  
			
		}
		
		public function facebookAction() {
			$token = $this->getRequest()->getParam('token',false);
			if($token == false) {
				return false; // redirect instead
			}
		 
			$auth = Zend_Auth::getInstance();
			$adapter = new Zend_Auth_Adapter_Facebook($token);
			$result = $auth->authenticate($adapter);
			if($result->isValid()) {
			//	die('amus');
				//$user = $adapter->getUser();
				$new_user_id = $adapter->getNewUserId();
				$this->connectPlatformsForUser($new_user_id);
				$auth->getStorage()->write($new_user_id);
				echo("<script>window.opener.location.href = window.opener.location.href; self.close ();</script>");
				die;
				$this->_redirect('/'); 
				return true; // redirect instead
			}
			return false; // redirect instead
		}
		
		public function twitter2Action() {
		
			
			
			//var_dump($adapter); die;
			
			$configs = array('consumerSecret' => 'ZbsOVnrTb4N2LlO6PiSSnSj3K5EXCYQQ4GeZG3xRy8',
			'consumerKey' => 'Qk2UG6kOCoWFVvxre6St2w', 'callbackUrl' => '');
			
			$auth = Zend_Auth::getInstance();
			$adapter = new Zend_Auth_Adapter_Twitter($configs);
			
			
			
			$result = $auth->authenticate($adapter);
			
		//	var_dump($adapter); die('wwwwwwwwwwwwww');
			
			if($result->isValid()) {
			die('amus');
			$user = $adapter->getUser();
			$auth->getStorage()->write($user);
			$this->_redirect('/'); 
			return true; // redirect instead
		}
			/*
		
		//	die('12');
			$token = new Zend_Oauth_Token_Access;
			$token->setParams(array(
			'oauth_token' => '856085400-C5w3S6doDW7k4ojiENOnFkFMadb73HZyyAjH8xEn',
			'oauth_token_secret' => 'RBrqcD1A8WnUmjN4JSNyE2MzoEm44HvzrBR8fYwYv0'
			));

		//	var_dump($token); die('----------');
			
			$twitter = new Zend_Service_Twitter(array(
			'consumerKey' => 'Qk2UG6kOCoWFVvxre6St2w', 
			'consumerSecret' => 'ZbsOVnrTb4N2LlO6PiSSnSj3K5EXCYQQ4GeZG3xRy8',
			'accessToken' => $token
));

			var_dump($twitter);*/
			//die('here');
		}
		
		public function linkedinAction() {
			session_start();
			
			$config['base_url']             =   SITE_URL . 'auth/linkedin/';
			$config['callback_url']         =   SITE_URL . 'auth/linkedinprofile/';
			$config['linkedin_access']      =   'qbj09mlp0ade';
			$config['linkedin_secret']      =   'wrLYQ1aJTqDJdR2l';

			include_once "LinkedInAuth/linkedin.php";

			 $linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );
				//$linkedin->debug = true;

				# Now we retrieve a request token. It will be set as $linkedin->request_token
				$linkedin->getRequestToken();
				//$session = new Zend_Session_Namespace('redirection');
				//$session->requestToken = serialize($linkedin->request_token);
				$_SESSION['requestToken'] = serialize($linkedin->request_token);
			  
				# With a request token in hand, we can generate an authorization URL, which we'll direct the user to
				//echo "Authorization URL: " . $linkedin->generateAuthorizeUrl() . "\n\n";
				header("Location: " . $linkedin->generateAuthorizeUrl());
		}
				
		public function insertStorage($storage) {
			$auth = Zend_Auth::getInstance();	
			$auth->getStorage()->write($storage);
		}
		
		public function linkedinprofileAction() {
			session_start();
			
		//	$session = new Zend_Session_Namespace('redirection');
			$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
  
			
			parse_str(parse_url($curent_url, PHP_URL_QUERY), $r);
			
			$config['base_url']             =   SITE_URL . 'auth/linkedin/';
			$config['callback_url']         =   SITE_URL . 'auth/linkedinprofile/';
			$config['linkedin_access']      =   'qbj09mlp0ade';
			$config['linkedin_secret']      =   'wrLYQ1aJTqDJdR2l';

			include_once "LinkedInAuth/linkedin.php";
			
	   
			# First step is to initialize with your consumer key and secret. We'll use an out-of-band oauth_callback
			$linkedin = new LinkedIn($config['linkedin_access'], $config['linkedin_secret'], $config['callback_url'] );

		    if (isset($r['oauth_verifier'])){
				$_SESSION['oauth_verifier']     = $r['oauth_verifier'];

				$linkedin->request_token    =   unserialize($_SESSION['requestToken']);
				$linkedin->oauth_verifier   =   $_SESSION['oauth_verifier'];
				$linkedin->getAccessToken($r['oauth_verifier']);

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

			$xml_object = simplexml_load_string($xml_response);
	
			if((int)$xml_object->id != 404 && (int)$xml_object->id != 401)
			{
				$modelFromRegistration = new Application_Model_Registration();
				$new_user_id = $modelFromRegistration->insertUserLinkedIn($xml_object);
				
				if($new_user_id != 0)
				{
					$_SESSION['Zend_Auth']['storage'] = $new_user_id;
					$this->connectPlatformsForUser($new_user_id);
				}
				
				//$this->insertStorage($xml_object->id);
				
				echo("<script>window.opener.location.href = window.opener.location.href; self.close ();</script>");
				
			}
die;
/*
			$search_response = $linkedin->search("?company-name=facebook&count=10");
			//$search_response = $linkedin->search("?title=software&count=10");

			//echo $search_response;
			$xml = simplexml_load_string($search_response);

			echo '<pre>';
			echo 'Look people who worked in facebook';
			print_r($xml);
			echo '</pre>';*/
		}

		public function twitterAction() {
			session_start();
			if(isset($_SESSION['twitter_fm']['screen_name']) && isset($_SESSION['twitter_fm']['name']) )
			{
				$UserTwitter = array();
				$UserTwitter['fname'] = $_SESSION['twitter_fm']['screen_name'];
				unset($_SESSION['twitter_fm']['screen_name']);
				
				$UserTwitter['lname'] = $_SESSION['twitter_fm']['name'];
				unset($_SESSION['twitter_fm']['name']);
				
				$UserTwitter['social_id'] = $_SESSION['twitter_fm']['id'];
				unset($_SESSION['twitter_fm']['id']);
				
				$modelFromRegistration = new Application_Model_Registration();
				$new_user_id = $modelFromRegistration->insertUserTwitter($UserTwitter);
				
				if($new_user_id != 0)
				{
					$this->connectPlatformsForUser($new_user_id);
					$_SESSION['Zend_Auth']['storage'] = $new_user_id;
				}
				
				echo("<script>window.opener.location.href = window.opener.location.href; self.close ();</script>");
				die;
				
			}
		}
		
		
		
		public function flAction()
		{
// 			session_start();
			$consumerKey = '209dda8981fd6a37be106cbee02cc9d4bec42ce4'; // consumer key, got in console
			$consumerSec = 'b847fab7e91ffe4f7fe8c317017ae45c226deb73'; // consumer secret, got in console with key
			
			//$consumerKey = 'a42d40a24fe99df1466bb781ce13c4ac'; // consumer key, got in console
			//$consumerSec = '5e722d5383eff298'; // consumer secret, got in console with key
			$sigMethod   = 'HMAC-SHA1'; // signature method, e.g. HMAC-SHA1
			$callbackUrl = SITE_URL . 'auth/fl/'; // callback url, full url to your script, e.g. http://localhost/oauth.php
			//$url         = 'https://www.odesk.com/services/api/keys/a42d40a24fe99df1466bb781ce13c4ac'; // api's url, e.g. http://www.odesk.com/api/mc/v1/threads/my_odesk_uid/22222.json (see MC documentation)

			$api_base = 'http://api.freelancer.com';
			
			$requestTokenUrl        = $api_base . '/RequestRequestToken/requestRequestToken.json';
			$accessTokenUrl         = $api_base . '/RequestAccessToken/requestAccessToken.json';
			//$userAuthorizationUrl   = 'https://www.odesk.com/services/api/auth';

			$config = array(
						'version'               => '1.0',
						'callbackUrl'           => $callbackUrl,
						'signatureMethod'       => $sigMethod,
						'requestTokenUrl'       => $requestTokenUrl,
						'accessTokenUrl'        => $accessTokenUrl,
				//		'userAuthorizationUrl'  => $userAuthorizationUrl,
						'consumerKey'           => $consumerKey,
						'consumerSecret'        => $consumerSec
			);

		
			
			$consumer = new Zend_Oauth_Consumer($config);
			 
			// Get request token
			if (!isset($_SESSION['REQUEST_TOKEN']) && !isset($_SESSION['ACCESS_TOKEN'])) {
				$token = $consumer->getRequestToken();

				$_SESSION['REQUEST_TOKEN'] = serialize($token);

				die('here');
				$consumer->redirect();
			}
			
			$curent_url =  $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			parse_str(parse_url($curent_url, PHP_URL_QUERY), $_MY_GET);
			//print_r($_MY_GET);

			// Get access token
			if (!empty($_MY_GET) && isset($_SESSION['REQUEST_TOKEN'])) {

				$token = $consumer->getAccessToken(
							 $_MY_GET,//$_GET,
							 unserialize($_SESSION['REQUEST_TOKEN'])
						 );
						 
				// Serialize and save token
				$_SESSION['ACCESS_TOKEN'] = serialize($token);
				// Now that we have an Access Token, we can discard the Request Token
				$_SESSION['REQUEST_TOKEN'] = null;
				
			}

			// Make an example GET request to API
			// We configure parameters and Zend_Http_Client manually,
			// but you can use your own preferred method and logic
			if (!empty($_SESSION['ACCESS_TOKEN'])) {
				
				//It seems that this does mean that we have already login into odesk account
				
				$token = unserialize($_SESSION['ACCESS_TOKEN']);
				$t  = $token->getToken();
				$ts = $token->getTokenSecret();
				
				$params = array(
                'oauth_consumer_key'    => $consumerKey,
                'oauth_signature_method'=> $sigMethod,
                'oauth_timestamp'       => time(),
                'oauth_nonce'           => substr(md5(microtime(true)), 5),
             //   'oauth_callback'        => $callbackUrl,
                'oauth_token'           => $t
				);

				ksort($params);

				$method = 'GET';
				$secret_key     = $consumerSec . '&' . $ts;
				$params_string  = http_build_query($params);

				$url = 'https://www.odesk.com/api/auth/v1/info.json';
				$base_string= $method . '&' . urlencode($url) . '&' . urlencode($params_string);
				$signature  = base64_encode(hash_hmac('sha1', $base_string, $secret_key, true));

				$params['oauth_signature'] = $signature;

				$params_string = http_build_query($params);
				
				$url .= '?' . $params_string;
				$modelFromGeneral = new Application_Model_General();
				$_SESSION['profile']['odesk'] = $modelFromGeneral->cURLExtractJSONContent($url);
				header("Location: " . '/auth/closewindowopener/');
			
				die;						
			}
		} 
}
?>