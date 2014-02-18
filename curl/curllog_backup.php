<?php
	// Initialize cURL
session_start();

if(isset($_SESSION['Zend_Auth']['storage']) && (int)$_SESSION['Zend_Auth']['storage']!=0)
{

	if(isset($_GET['username'])&& isset($_GET['password']))
	{		
		$username = $_GET['username'];
		$password = $_GET['password'];
		$platform = (int)$_GET['pl'];
		
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
			$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwULLTE0NDI5OTY0MjAPZBYEAgMPFgIeBFRleHQFuQQ8IS0tIEdvb2dsZSBUYWcgTWFuYWdlciAtLT4NCjxub3NjcmlwdD48aWZyYW1lIHNyYz0iLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vbnMuaHRtbD9pZD1HVE0tTEYyMiINCmhlaWdodD0iMCIgd2lkdGg9IjAiIHN0eWxlPSJkaXNwbGF5Om5vbmU7dmlzaWJpbGl0eTpoaWRkZW4iPjwvaWZyYW1lPjwvbm9zY3JpcHQ%2BDQo8c2NyaXB0PihmdW5jdGlvbih3LGQscyxsLGkpe3dbbF09d1tsXXx8W107d1tsXS5wdXNoKHsnZ3RtLnN0YXJ0JzoNCm5ldyBEYXRlKCkuZ2V0VGltZSgpLGV2ZW50OidndG0uanMnfSk7dmFyIGY9ZC5nZXRFbGVtZW50c0J5VGFnTmFtZShzKVswXSwNCmo9ZC5jcmVhdGVFbGVtZW50KHMpLGRsPWwhPSdkYXRhTGF5ZXInPycmbD0nK2w6Jyc7ai5hc3luYz10cnVlO2ouc3JjPQ0KJy8vd3d3Lmdvb2dsZXRhZ21hbmFnZXIuY29tL2d0bS5qcz9pZD0nK2krZGw7Zi5wYXJlbnROb2RlLmluc2VydEJlZm9yZShqLGYpOw0KfSkod2luZG93LGRvY3VtZW50LCdzY3JpcHQnLCdkYXRhTGF5ZXInLCdHVE0tTEYyMicpOzwvc2NyaXB0Pg0KPCEtLSBFbmQgR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPmQCBw8WAh8ABdwEPHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPgogdmFyIGdhSnNIb3N0ID0gKCgiaHR0cHM6IiA9PSBkb2N1bWVudC5sb2NhdGlvbi5wcm90b2NvbCkgPyAiaHR0cHM6Ly9zc2wuIiA6ICJodHRwOi8vd3d3LiIpOwogZG9jdW1lbnQud3JpdGUodW5lc2NhcGUoIiUzQ3NjcmlwdCBzcmM9JyIgKyBnYUpzSG9zdCArICJnb29nbGUtYW5hbHl0aWNzLmNvbS9nYS5qcycgdHlwZT0ndGV4dC9qYXZhc2NyaXB0JyUzRSUzQy9zY3JpcHQlM0UiKSk7CiA8L3NjcmlwdD4KIDxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0Ij4KdHJ5ewogIHZhciBwYWdlVHJhY2tlciA9IF9nYXQuX2dldFRyYWNrZXIoIlVBLTQzMzY4OS00Iik7CiAgIHBhZ2VUcmFja2VyLl9zZXREb21haW5OYW1lKCIuZ3VydS5jb20iKTsKICBwYWdlVHJhY2tlci5fdHJhY2tQYWdldmlldygpOwogfSBjYXRjaChlcnIpe30KIGZ1bmN0aW9uIGdvb2dsZVNpdGVTZWFyY2gocGFybXMpe3RyeXt2YXIgcGFnZVRyYWNrZXIgPSBfZ2F0Ll9nZXRUcmFja2VyKCJVQS00MzM2ODktNCIpO3BhZ2VUcmFja2VyLl90cmFja1BhZ2V2aWV3KCIvc2VhcmNoPyIrcGFybXMpO30gY2F0Y2goZXJyKXt9fTwvc2NyaXB0PmRkG%2F04%2FQOTUFoJbKJavc7EfUH8o28%3D&__EVENTVALIDATION=%2FwEWBQKqxMb%2FDgLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC1384HZag%2BkKmLmhZ0w040qs4aPt&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox='.$username.'&ucLogin%24txtPassword%24txtPassword_TextBox='.$password.'&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';
		}
		
		else if($platform==8) {
	
			$login_url = 'https://www.peopleperhour.com/site/login';
			//$post_data = 'YII_CSRF_TOKEN=cd60855661ed0fed18457f08ae86f48001433e14&LoginForm%5Bemail%5D='.$username.'&LoginForm%5Bpassword%5D='.$password.'&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=Log+In';
			//$post_data = 'YII_CSRF_TOKEN=7d463000b6ea8ed5727ee62914ac3cbb0c579e68&LoginForm%5Bemail%5D='.$username.'&LoginForm%5Bpassword%5D='.$password.'&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=Log+In';
			//$post_data = 'YII_CSRF_TOKEN=cd60855661ed0fed18457f08ae86f48001433e14&LoginForm%5Bemail%5D=telfus64asd%40gmail.com&LoginForm%5Bpassword%5D=craca95tit&LoginForm%5BrememberMe%5D=0&LoginForm%5BrememberMe%5D=1&yt0=Log+In';
		
			
		}
		

        // Enable Posting.
        curl_setopt($Curl_Obj, CURLOPT_POST, 1);
				
		$UserId = (int)$_SESSION['Zend_Auth']['storage'];
		$cookieFile = $UserId.'cookie.txt';
		
		//$cookieFile = 'cookie.txt';
		if(!file_exists('./cookies/'.$cookieFile)) {
			$fh = fopen('./cookies/'.$cookieFile, "w");
			fwrite($fh, "");
			fclose($fh);
			chmod('./cookies/'.$cookieFile, 0777);
		}
		
        // Enable Cookies
		/*$cookieFile = 'cookie.txt';
		 if(!file_exists($cookieFile)) {
			$fh = fopen($cookieFile, "w");
			fwrite($fh, "");
			fclose($fh);
		}
		*/
        curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, dirname(__FILE__) . '/cookies/' . $cookieFile); 
        curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, dirname(__FILE__) . '/cookies/' .$cookieFile); 
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
		curl_setopt($Curl_Obj, CURLOPT_FOLLOWLOCATION, true);

        // Return output as string.
        curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);

		// Set up post fields from login form.
		curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 


		// Set the url to which the data will be posted.
		curl_setopt ($Curl_Obj, CURLOPT_URL, $login_url);
     
	 
		//print_r(get_headers($login_url));
	 
		// Execute the post and get the output.
		$response = curl_exec ($Curl_Obj);
		
		//print '------------------------------------------';
		//preg_match('/^Set-Cookie: (.*?);/m', $response, $m);
		
		//print_r($m);
		
		//var_dump(parse_url($m[1]));
		
		//$arr_cookies = parse_url($m[1]);
		//print_r($arr_cookies);
		
	    $http_code = curl_getinfo($Curl_Obj, CURLINFO_HTTP_CODE);
		
		$header_size = curl_getinfo($Curl_Obj, CURLINFO_HEADER_SIZE);
		$header = substr($response, 0, $header_size);
		$body = substr($response, $header_size);
		
		curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, "");
		
		if($platform==5)
		{
			if(strpos($header,'302 Found')!==false) //That means sign in process was successfully
			{
				$_SESSION['curl'][$platform]['post_data'] = $post_data;
			}
		}
		
		else if($platform==6 || $platform==9)
		{
			if(strpos($header,'Set-Cookie:')!==false) //That means sign in process was successfully
			{
				$_SESSION['curl'][$platform]['post_data'] = $post_data;
				print '-------------------------------------------';
			}
		}
		else if($platform==10)
		{
			if(strpos($header,'?login_error=1')===false) //That means sign in process was successfully
			{
				$_SESSION['curl'][$platform]['post_data'] = $post_data;
			}
		}
		else if($platform==7)
		{
			if(strpos($header,'ETag:')===false) //That means sign in process was successfully
			{
				$_SESSION['curl'][$platform]['post_data'] = $post_data;
				//die('*****************************');
			}
		}

	/*	
	curl_setopt ($Curl_Obj, CURLOPT_URL, 'https://www.peopleperhour.com/dashboard');
       // Execute query and obtain content.
       $output = curl_exec($Curl_Obj);
	   
	   echo $output;
		*/
	   
	   print $header; 
	   die;
	}
}
?>