<?php
	require_once 'Zend/Mail.php';
	require_once 'Zend/Mail/Transport/Smtp.php'; 
  
	$config = array('auth' => 'login',
					'username' => 'support@searchfreelancejobs.com',
					'password' => '123qwe');
	
	$smtp = 'mail.searchfreelancejobs.com';
	$from = 'support@searchfreelancejobs.com';
	$from_name = 'searchfreelancejobs.com';
	$one_email = $formData['email'];
	$subject = 'Please confirm your SearchFreelanceJobs.com Account';
	$content = 'Welcome';
	
	$transport = new Zend_Mail_Transport_Smtp($smtp, $config);		
			
	$mail = new Zend_Mail();
	$mail->setFrom($from, $from_name);
	$mail->setSubject( $subject );
	$mail->setBodyText($content);
	$mail->addTo("bernazzyk@gmail.com");						
	$mail->send($transport);
						
?>