<?php
class Application_Model_Curl extends Application_Model_Freelancer
{
	public function CurlPlatformPostClassic($options=array())
	{
        $Curl_Obj = curl_init();		
		curl_setopt_array($Curl_Obj, $options);
		$response = curl_exec($Curl_Obj);
		curl_close($Curl_Obj);
		return $response;	
	}
	
	public function curl_setopt_custom_postfields($ch, $postfields, $headers = null) {
		$algos = hash_algos();
		$hashAlgo = null;
		foreach ( array('sha1', 'md5') as $preferred ) {
			if ( in_array($preferred, $algos) ) {
				$hashAlgo = $preferred;
				break;
			}
		}
		if ( $hashAlgo === null ) { list($hashAlgo) = $algos; }
		$boundary =
			'----------------------------' .
			substr(hash($hashAlgo, 'cURL-php-multiple-value-same-key-support' . microtime()), 0, 12);

		$body = array();
		$crlf = "\r\n";
		$fields = array();
		foreach ( $postfields as $key => $value ) {
			if ( is_array($value) ) {
				foreach ( $value as $key_2=>$v ) {
					$fields[] = array($key.'['.$key_2.']', $v);
				}
			} else {
				$fields[] = array($key, $value);
			}
		}
		foreach ( $fields as $field ) {
			list($key, $value) = $field;
			if ( strpos($value, '@') === 0 ) {
				preg_match('/^@(.*?)$/', $value, $matches);
				list($dummy, $filename) = $matches;
				$body[] = '--' . $boundary;
				$body[] = 'Content-Disposition: form-data; name="' . $key . '"; filename="' . basename($filename) . '"';
				$body[] = 'Content-Type: application/octet-stream';
				$body[] = '';
				if($filename!='')
				{
					$body[] = file_get_contents($filename);
				}
			} else {
				$body[] = '--' . $boundary;
				$body[] = 'Content-Disposition: form-data; name="' . $key . '"';
				$body[] = '';
				$body[] = $value;
			}
		}
		$body[] = '--' . $boundary . '--';
		$body[] = '';
		
		$contentType = 'multipart/form-data; boundary=' . $boundary;
		$content = join($crlf, $body);
		//print $content; die;
		$contentLength = strlen($content);

		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Length: ' . $contentLength,
			'Expect: 100-continue',
			'Content-Type: ' . $contentType,
		));

		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
	}
	
	public function getCookieFile($user_id)
	{
		$cookieFile = dirname(__FILE__).'/../controllers/cookies/'.$user_id.'cookie.txt';
		//print $cookieFile; die;
		//$cookieFile = 'cookie.txt';
		if(!file_exists( $cookieFile)) {
			$fh = fopen($cookieFile, "w");
			fwrite($fh, '');
			fclose($fh);
			chmod($cookieFile, 0777);
		}
		return $cookieFile;
	}

}