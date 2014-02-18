<?php
		session_start();
        $Curl_Obj = curl_init();
	
       
        // Enable Cookies
		
		//print_r($_SESSION);
		//print $_SESSION['Zend_Auth']['storage'];
		
		if(isset($_SESSION['Zend_Auth']['storage']) && (int)$_SESSION['Zend_Auth']['storage']!=0)
		{
			
			$UserId = (int)$_SESSION['Zend_Auth']['storage'];
			$cookieFile = $UserId.'cookie.txt';
		
			//$cookieFile = 'cookie.txt';
			/* if(!file_exists('./cookies/'.$cookieFile)) {
				$fh = fopen('./cookies/'.$cookieFile, "w");
				fwrite($fh, "");
				fclose($fh);
			}*/
		
		//print  dirname(__FILE__);
		
        curl_setopt ($Curl_Obj, CURLOPT_COOKIEFILE, '/var/www/clients/client1/web1/web/yta/application/controllers/cookies/' . $cookieFile); 
        curl_setopt ($Curl_Obj, CURLOPT_COOKIEJAR, '/var/www/clients/client1/web1/web/yta/application/controllers/cookies/' .$cookieFile); 
		
        // Set the browser you will emulate

        $userAgent = 'Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0';
        curl_setopt($Curl_Obj, CURLOPT_USERAGENT, $userAgent);

        // Don't include the header in the output.
        curl_setopt ($Curl_Obj, CURLOPT_HEADER, 0);

        // Allow referer field when following Location redirects.
        curl_setopt($Curl_Obj, CURLOPT_AUTOREFERER, TRUE);

        // Follow server redirects.
		//curl_setopt($Curl_Obj, CURLOPT_FOLLOWLOCATION, true);

        // Return output as string.
        //curl_setopt ($Curl_Obj, CURLOPT_RETURNTRANSFER, 1);

		// Set the url to which the data will be posted.
//		curl_setopt ($Curl_Obj, CURLOPT_URL, 'http://www.getacoder.com/users/changeuserinfo.php');
		
		//freelanceswitc
	//	curl_setopt ($Curl_Obj, CURLOPT_URL, 'http://jobs.freelanceswitch.com/account/edit');
	//	curl_setopt ($Curl_Obj, CURLOPT_URL, 'http://www.ifreelance.com/my/profiles.aspx');
		
		// Enable Posting.
        curl_setopt($Curl_Obj, CURLOPT_POST, 1);
		
		$post_data = 'id=155431&sum=65&period=20&descr=It+is+my+first+experience+on+getacoder&notifylowerbids=on&submit=Place+Bid';
		curl_setopt($Curl_Obj, CURLOPT_POSTFIELDS, $post_data); 
		
		curl_setopt ($Curl_Obj, CURLOPT_URL, 'http://www.getacoder.com/sellers/onplacebid.php');
     
	 //aici vom vedea oferta noastra jos
	 //http://www.getacoder.com/projects/website_design_155431.html#options
	 
		// Execute the post and get the output.
		$response = curl_exec ($Curl_Obj);
		print $response;
		
		curl_setopt($Curl_Obj, CURLOPT_POST, 0);
		
		
		curl_setopt ($Curl_Obj, CURLOPT_URL, 'http://www.getacoder.com/projects/website_design_155431.html#options');
		$response = curl_exec ($Curl_Obj);
		print $response;
		
		//if($response==1) means that bid was succesfully placed on getacoder platform
				
		
		
	   die;
	   }
?>