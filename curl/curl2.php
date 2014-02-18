<html>
<head>
<link href="/media/css/global/freelance.css" type="text/css" rel="stylesheet">
</head>
<?php

session_start(); 



  // Initialize cURL

        $Curl_Obj = curl_init(); 
		$curent_site = $_GET['pl'];
		$SiteDetails['guru']=array(	'url'=>'https://www.guru.com/login.aspx',
									'username_field'=>'ucLogin$txtUserName$txtUserName_TextBox',
									'password_filed'=>'ucLogin$txtPassword$txtPassword_TextBox'
									);
									
		$SiteDetails['getacoder']=array(	'url'=>'http://www.getacoder.com/users/onlogin.php',
									'username_field'=>'username',
									'password_filed'=>'passwd'
									);

		
		//freelance.com
		//j_username
		//j_password
		//link  https://secure.freelance.com/j_acegi_security_check  /j_acegi_security_check
		//j_username=telfus64asd%40gmail.com&j_password=craca95tit&j_role=Freelancer

        // Enable Posting.
		
		$url = 'https://secure.freelance.com/j_acegi_security_check';

        curl_setopt($Curl_Obj, CURLOPT_POST, 1);



        // Enable Cookies

        curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, 'cookie.txt'); 



        // Set the browser you will emulate

        $userAgent = 'Mozilla/5.0 (X11; Linux i686; rv:2.0.1) Gecko/20100101 Firefox/4.0.1';

        curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);



        // Don't include the header in the output.

        curl_setopt ($Curl_Obj, CURLOPT_HEADER, 0);



        // Allow referer field when following Location redirects.

        curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);



        // Follow server redirects.

		curl_setopt($Curl_Obj, CURLOPT_FOLLOWLOCATION, true);



        // Return output as string.

        curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);

		
		//$post_data = $SiteDetails[$curent_site]['username_field'].'=zimbru&'.$SiteDetails[$curent_site]['password_filed'].'=craca95tit';
		$post_data = 'j_username=telfus64asd%40gmail.com&j_password=craca95tit&j_role=Freelancer';
		//$post_data = 'scriptMgr_HiddenField=&__LASTFOCUS=&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=%2FwEPDwULLTE0NDI5OTY0MjAPZBYEAgMPFgIeBFRleHQFuQQ8IS0tIEdvb2dsZSBUYWcgTWFuYWdlciAtLT4NCjxub3NjcmlwdD48aWZyYW1lIHNyYz0iLy93d3cuZ29vZ2xldGFnbWFuYWdlci5jb20vbnMuaHRtbD9pZD1HVE0tTEYyMiINCmhlaWdodD0iMCIgd2lkdGg9IjAiIHN0eWxlPSJkaXNwbGF5Om5vbmU7dmlzaWJpbGl0eTpoaWRkZW4iPjwvaWZyYW1lPjwvbm9zY3JpcHQ%2BDQo8c2NyaXB0PihmdW5jdGlvbih3LGQscyxsLGkpe3dbbF09d1tsXXx8W107d1tsXS5wdXNoKHsnZ3RtLnN0YXJ0JzoNCm5ldyBEYXRlKCkuZ2V0VGltZSgpLGV2ZW50OidndG0uanMnfSk7dmFyIGY9ZC5nZXRFbGVtZW50c0J5VGFnTmFtZShzKVswXSwNCmo9ZC5jcmVhdGVFbGVtZW50KHMpLGRsPWwhPSdkYXRhTGF5ZXInPycmbD0nK2w6Jyc7ai5hc3luYz10cnVlO2ouc3JjPQ0KJy8vd3d3Lmdvb2dsZXRhZ21hbmFnZXIuY29tL2d0bS5qcz9pZD0nK2krZGw7Zi5wYXJlbnROb2RlLmluc2VydEJlZm9yZShqLGYpOw0KfSkod2luZG93LGRvY3VtZW50LCdzY3JpcHQnLCdkYXRhTGF5ZXInLCdHVE0tTEYyMicpOzwvc2NyaXB0Pg0KPCEtLSBFbmQgR29vZ2xlIFRhZyBNYW5hZ2VyIC0tPmQCBw8WAh8ABdwEPHNjcmlwdCB0eXBlPSJ0ZXh0L2phdmFzY3JpcHQiPgogdmFyIGdhSnNIb3N0ID0gKCgiaHR0cHM6IiA9PSBkb2N1bWVudC5sb2NhdGlvbi5wcm90b2NvbCkgPyAiaHR0cHM6Ly9zc2wuIiA6ICJodHRwOi8vd3d3LiIpOwogZG9jdW1lbnQud3JpdGUodW5lc2NhcGUoIiUzQ3NjcmlwdCBzcmM9JyIgKyBnYUpzSG9zdCArICJnb29nbGUtYW5hbHl0aWNzLmNvbS9nYS5qcycgdHlwZT0ndGV4dC9qYXZhc2NyaXB0JyUzRSUzQy9zY3JpcHQlM0UiKSk7CiA8L3NjcmlwdD4KIDxzY3JpcHQgdHlwZT0idGV4dC9qYXZhc2NyaXB0Ij4KdHJ5ewogIHZhciBwYWdlVHJhY2tlciA9IF9nYXQuX2dldFRyYWNrZXIoIlVBLTQzMzY4OS00Iik7CiAgIHBhZ2VUcmFja2VyLl9zZXREb21haW5OYW1lKCIuZ3VydS5jb20iKTsKICBwYWdlVHJhY2tlci5fdHJhY2tQYWdldmlldygpOwogfSBjYXRjaChlcnIpe30KIGZ1bmN0aW9uIGdvb2dsZVNpdGVTZWFyY2gocGFybXMpe3RyeXt2YXIgcGFnZVRyYWNrZXIgPSBfZ2F0Ll9nZXRUcmFja2VyKCJVQS00MzM2ODktNCIpO3BhZ2VUcmFja2VyLl90cmFja1BhZ2V2aWV3KCIvc2VhcmNoPyIrcGFybXMpO30gY2F0Y2goZXJyKXt9fTwvc2NyaXB0PmRkG%2F04%2FQOTUFoJbKJavc7EfUH8o28%3D&__EVENTVALIDATION=%2FwEWBQKqxMb%2FDgLW3fTKCAL%2FyfnfBwKGyp6GDgKX0ebqC1384HZag%2BkKmLmhZ0w040qs4aPt&scriptMgr=&ucLogin%24txtUserName%24txtUserName_TextBox=zimbru&ucLogin%24txtPassword%24txtPassword_TextBox=craca95tit&btnLoginAccount%24btnLoginAccount_Button=Sign+in&hdnGuid=GUID';

		// Set up post fields from login form.

       curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data);


       // Set the url to which the data will be posted.

       //curl_setopt ($Curl_Obj, CURLOPT_URL, $SiteDetails[$curent_site]['url']);
       curl_setopt ($Curl_Obj, CURLOPT_URL, $url);



       // Execute the post and get the output.

       $output = curl_exec ($Curl_Obj);



       // Empty the post fields so you don't re-post on the next request.

      /* curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, "");
	   
	    // Set curl object url option.
       curl_setopt ($Curl_Obj, CURLOPT_URL, 'http://www.getacoder.com/users/changeuserinfo.php');
       // Execute query and obtain content.
       $output = curl_exec($Curl_Obj);*/
	   
	    // Set curl object url option.
       curl_setopt ($Curl_Obj, CURLOPT_URL, 'https://secure.freelance.com/en/portfolio/dbf92bec3badf8bf013bb1ef0f320abe');
       // Execute query and obtain content.
       $output = curl_exec($Curl_Obj);
	   
	   echo $output;
	  // var_dump( $output);
?>
</html>