<?php
ini_set("max_execution_time", "0");
require_once 'OAuth.php';

/**
 * Freelancer API Library 
 *
 * @link        http://stl.olfreelancer.com
 * @version     v0.7
 * @author    	Chunyou Zhao(zcy@olfreelancer.com)
 * @copyright 	Chunyou Zhao
 * @package		com.olfreelancer.stl
 * @license 	http://www.gnu.org/licenses/gpl.html GPLv3
 * @date		Agu 3, 2010 7:24:30 PM
 */
class SnowTigerLib {
	// Store the data get from the freelancer API
	protected $freelancerData;
	// user param
	private $_userParam = array ();
	// error info
	private $_errorInfo;
	// Contains the last HTTP status code returned.
  	public $http_code;
  	// Contains the last API call.
  	public $url;
	// Contains the last HTTP headers returned.
  	public $http_info;
  	public $http_header;
  	
  	public $method = 'GET';
  	private $cur_call;
	private $consumer;
	private $token;
	/**
	 * @var SnowTigerLib_Config
	 */
	public $ApiConfig;
	/**
	 * @var OAuthSignatureMethod_HMAC_SHA1 
	 */
	private static $hmac_method;

	public function __construct($oauth_token = null, $oauth_token_secret = null) {
		if (self :: $hmac_method == NULL) {
			self :: $hmac_method = new OAuthSignatureMethod_HMAC_SHA1();
		}

		$Config = SnowTigerLib_Config :: Init();

		$this->ApiConfig = $Config->getConfig();

		$this->consumer = new OAuthConsumer($this->ApiConfig->ConsumerToken, $this->ApiConfig->ConsumerSecret, $this->ApiConfig->CallBack);
		if (!empty ($oauth_token) && !empty ($oauth_token_secret)) {
			$this->token = new OAuthToken($oauth_token, $oauth_token_secret);
		} else {
			$this->token = NULL;
		}
	}

	public function __set($name, $value) {
		if ($this->freelancerData) {

			$this->_userParam = array ();
			$this->freelancerData = null;
			$this->_errorInfo = null;
			$this->method = 'GET';
		}

		$this->_userParam[$name] = trim($value);
	}

	public function setUserParam($userParam) {
		$this->_userParam = $userParam;
	}

	public function __get($name) {
		if (!empty ($this->_userParam[$name])) {

			return $this->_userParam[$name];
		}
	}
	public function __unset($name) {
		unset ($this->_userParam[$name]);
	}

	public function __isset($name) {
		return isset ($this->_userParam[$name]);
	}

	public function __destruct() {
		$this->_userParam = array ();
	}

	public function __toString() {
		return $this->createStrParam($this->_userParam);
	}

	/**
	 * private function: call the freelancer api
	 */
	private function _call_api() {		
		$url = $this->ApiConfig->apiurl . $this->url .'.'. $this->ApiConfig->Format;

		$request = OAuthRequest :: from_consumer_and_token($this->consumer, $this->token, $this->method, $url, $this->_userParam);
		$request->sign_request($this->getSignMethod(), $this->consumer, $this->token);
		
		switch ($this->method) {
			case 'GET' :
				$this->freelancerData = $this->http($request->to_url(), 'GET');
				break;
			default :
				$this->freelancerData = $this->http($request->get_normalized_http_url(), $this->method, $request->to_postdata());
		}
		$tempArr = array_merge(array('CallAPI'=>$this->cur_call),$this->_userParam, $request->get_parameters());
		$this->ApiCallLog($tempArr);
// 		print $this->http_code;die;
		if($this->http_code == 200){
			
			
			if($this->cur_call == 'getRequestToken' || $this->cur_call == 'getRequestTokenVerifier' || $this->cur_call == 'getRequestAccessToken')
				return $this;
			$result = $this->getArrayData();
			if (isset ($result['error'])) {
				if ($this->ApiConfig->RestNumberic) {

                    $this->ApiConfig->RestNumberic = $this->ApiConfig->RestNumberic - 1;

                    $this->_call_api($this->method);
                }else{
					$this->_errorInfo = new SnowTigerLib_Exception($result['error'], $tempArr, $this->ApiConfig->CloseError, $this->ApiConfig->Errorlog);
		
					if (!$this->ApiConfig->CloseError) {
						echo $this->_errorInfo->getErrorInfo();
					}
				}
			}
		}else{
			$this->_errorInfo = new SnowTigerLib_Exception($this->http_info, $this->_userParam, $this->ApiConfig->CloseError, $this->ApiConfig->Errorlog);
	
			if (!$this->ApiConfig->CloseError) {
				echo $this->_errorInfo->getErrorInfo();
			}
		}
		
		return $this;
	}
	
	/**
	* Make an HTTP request
	*
	* @return API results
	*/
	private function http($url, $method, $postfields = NULL) {
		
		$this->http_info = array ();
		$ci = curl_init();
		/* Curl settings */
		curl_setopt($ci, CURLOPT_USERAGENT, $this->ApiConfig->useragent);
		curl_setopt($ci, CURLOPT_CONNECTTIMEOUT, $this->ApiConfig->connecttimeout);
		curl_setopt($ci, CURLOPT_TIMEOUT, $this->ApiConfig->timeout);
		curl_setopt($ci, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ci, CURLOPT_HTTPHEADER, array (
			'Expect:'
		));
		curl_setopt($ci, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ci, CURLOPT_HEADERFUNCTION, array (
			$this,
			'getHeader'
		));
		curl_setopt($ci, CURLOPT_HEADER, FALSE);

		switch ($method) {
			case 'POST' :
				curl_setopt($ci, CURLOPT_POST, TRUE);
				if (!empty ($postfields)) {
					curl_setopt($ci, CURLOPT_POSTFIELDS, $postfields);
				}
				break;
			case 'DELETE' :
				curl_setopt($ci, CURLOPT_CUSTOMREQUEST, 'DELETE');
				if (!empty ($postfields)) {
					$url = "{$url}?{$postfields}";
				}
		}

//		echo $url."<hr/>";
		curl_setopt($ci, CURLOPT_URL, $url);
		$response = curl_exec($ci);
		$this->http_code = curl_getinfo($ci, CURLINFO_HTTP_CODE);
		$this->http_info = array_merge($this->http_info, curl_getinfo($ci));
//		print_r( $this->http_info );

		curl_close($ci);
//		print_r( $response );
		return $response;
	}
	
	/**
	 * Get the header info to store.
	 */
	private function getHeader($ch, $header) {
		$i = strpos($header, ':');
		if (!empty ($i)) {
			$key = str_replace('-', '_', strtolower(substr($header, 0, $i)));
			$value = trim(substr($header, $i +2));
			$this->http_header[$key] = $value;
		}
		return strlen($header);
	}

	/**
	 * @return SnowTigerLib
	 */
	public function setRestNumberic($rest) {
		$this->ApiConfig->setRestNumberic($rest);

		return $this;
	}
	
	/**
	 * set result Format
	 * 
	 * @param string $format  xml/json
	 * @return SnowTigerLib_Config
	 */
	public function setFormat($format) {
		if($format == 'xml' || $format == 'json')
			$this->ApiConfig->Format = $format;
		return $this;
	}

	/**
	 * Turn on the error tips
	 * 
	 * @return SnowTigerLib
	 */
	public function setCloseError() {
		$this->ApiConfig->setCloseError(false);

		return $this;
	}

	/**
	 * set Access Token and Access Secret
	 * 
	 * @param string $oauth_token
	 * @param string $oauth_token_secret
	 * 
	 * @return SnowTigerLib
	 */
	public function setAccessToken($oauth_token, $oauth_token_secret) {
		$this->token = new OAuthToken($oauth_token, $oauth_token_secret);

		return $this;
	}

	public function ApiCallLog($logparam) {
		if ($this->ApiConfig->ApiLog) {
			$apilogpath = dirname(__FILE__) . '/api_call_log';
			if (!is_dir($apilogpath)) {
				@ mkdir($apilogpath);
			}
			if ($fp = @ fopen($apilogpath . '/' . $this->ApiConfig->ConsumerToken . '_' . date('Y-m-d') . '.log', 'a')) {
				foreach ($logparam as $key => $value) {
					$loginfotext[] = $key . " : " . $value;
				}
				@ fwrite($fp, implode("\t", $loginfotext) . "\r\n");
				fclose($fp);
			}
		}
	}

	/**
	 * Get the result data in XML format
	 * 
	 * @return xml the result data
	 */
	public function getXmlData() {
		if (empty ($this->freelancerData)) {
			return false;
		}
		return $this->freelancerData;
	}
	
	/**
	 * Get the result data in Json format
	 * 
	 * @return json the result data
	 */
	public function getJsonData ()
    {
        if (empty($this->freelancerData)) {
            return false;
        }
        if (substr($this->freelancerData, 0, 1) != '{') {

            if ($this->ApiConfig->Format == 'xml') {
                $Data = $this->getArrayData($this->freelancerData);
            }

            $Data = json_encode($Data);
            if (strpos($_SERVER['SERVER_SIGNATURE'], "Win32") > 0) {
                $Data = preg_replace("#\\\u([0-9a-f][0-9a-f])([0-9a-f][0-9a-f])#ie", "iconv('UCS-2','UTF-8',pack('H4', '\\1\\2'))", $Data);
            } else {
                $Data = preg_replace("#\\\u([0-9a-f][0-9a-f])([0-9a-f][0-9a-f])#ie", "iconv('UCS-2','UTF-8',pack('H4', '\\2\\1'))", $Data);
            }

        } else {
            $Data = $this->freelancerData;
        }
        return $Data;
    }

	/**
	 * Get the result data in Array format
	 * 
	 * @return Array the result data
	 */
	public function getArrayData() {
		if (empty ($this->freelancerData)) {
			return false;
		}
		
		if ($this->ApiConfig->Format == 'json') {
            $json = array_values(json_decode($this->freelancerData, true));
            return $json[0];
        } elseif ($this->ApiConfig->Format == 'xml') {
           	$xmlCode = simplexml_load_string($this->freelancerData, 'SimpleXMLElement', LIBXML_NOCDATA);
			$freelancerData = $this->get_object_vars_final($xmlCode);
            return $freelancerData;
        } else {
            return false;
        }
	}

	/**
	 * Return the error info
	 *
	 * @return array
	 */
	public function getErrorInfo() {
		if ($this->_errorInfo) {
			if (is_object($this->_errorInfo)) {

				return $this->_errorInfo->getErrorInfo();
			} else {
				return $this->_errorInfo;
			}
		}
		return null;
	}
	/**
	 * Return the param
	 *
	 * @return array
	 */
	public function getParam() {
		return $this->_userParam;
	}
	
	/**
	 * Return the Current Call
	 *
	 * @return string
	 */
	public function getCurCall() {
		return $this->cur_call;
	}

	private function get_object_vars_final($obj) {
		if (is_object($obj)) {
			$obj = get_object_vars($obj);
		}

		if (is_array($obj)) {
			foreach ($obj as $key => $value) {
				$obj[$key] = $this->get_object_vars_final($value);
			}
		}
		return $obj;
	}

	private function getSignMethod() {
		if ($this->ApiConfig->SignMethod == 'hmac')
			return self :: $hmac_method;
	}

	/*****************************************************************
	 * Authorize
	 ****************************************************************/
	/**
	 * Get a request_token from SnowTigerLib
	 * 
	 * @return Array a key/value array containing oauth_token and oauth_token_secret,Or false when error occur
	 */
	public function getRequestToken() {
		$this->cur_call = 'getRequestToken';
		$this->url = '/RequestRequestToken/requestRequestToken';
		$this->oauth_callback = $this->ApiConfig->CallBack;

		$this->_call_api();

		if ($this->_errorInfo) {
			return false;
		} else {
			$data = explode('&',$this->freelancerData);
			foreach ($data as $v){
				list($key, $value) = explode('=',$v);
				$result[$key] = $value;
			}
			$this->token = new OAuthToken($result['oauth_token'], $result['oauth_token_secret']);
			return array('oauth_token'=>$this->token->key,'oauth_token_secret'=>$this->token->secret);
		}
	}
	
	/**
	 * If the user already Authorized,you can use this method to get the verifier code.
	 * 
	 * @return string oauth_verifier code
	 */
	public function getRequestTokenVerifier() {
		$this->cur_call = 'getRequestTokenVerifier';
		$this->url = '/RequestAccessToken/getRequestTokenVerifier';
		$this->_call_api();

		if ($this->_errorInfo) {
			return false;
		} else {
			list($key, $value) = explode('=',$this->freelancerData);
			return $value;
		}
	}

	/**
	* Get the authorize URL
	*
	* @returns string the Authorize URL
	*/
	function getAuthorizeURL() {
		$this->cur_call = 'getAuthorizeURL';
		return $this->ApiConfig->weburl . "/users/api-token/auth.php?oauth_token=" . $this->token->key;
	}

	/**
	 * Exchange	 the request token and secret for an access token and
	 * secret, to sign API calls.
	 * 
	 * @return Array a key/value array containing oauth_token and oauth_token_secret,Or false when error occur
	 */
	public function getRequestAccessToken($oauth_verifier = '') {
		$this->cur_call = 'getRequestAccessToken';
		$this->url = '/RequestAccessToken/requestAccessToken';
		$this->oauth_verifier = $oauth_verifier;

		$this->_call_api();

		if ($this->_errorInfo) {
			return false;
		} else {
			$data = explode('&',$this->freelancerData);
			foreach ($data as $v){
				list($key, $value) = explode('=',$v);
				$result[$key] = $value;
			}
			$this->token = new OAuthToken($result['oauth_token'], $result['oauth_token_secret']);
			return array('oauth_token'=>$this->token->key,'oauth_token_secret'=>$this->token->secret);
		}
	}
	
	/*****************************************************************
	 * User
	 ****************************************************************/
	/**
	 * Search for users using various search criteria.
	 * 
	 * @link http://developer.freelancer.com/GetUsersBySearch
	 * @param Array $param (<br/>
	 * 	username(Optional)	 	 	Username of the person for who you are searching.<br/>
	 *	expertise_csv(Optional)	 	Comma separated list of job categories, refer to the main page for a list of categories.<br/>
	 *	country_csv(Optional)	 	Comma separated list of countries<br/>
	 *	rating(Optional)	 		Minimum rating for the user<br/>
	 *	count(Optional)	 			Number of results (Default: 50)<br/>
	 *	page(Optional)	 			Page number (Default: 0)<br/>
	 * )
	 */
	public function getUsersBySearch($param = array()){
		$this->cur_call = 'getUsersBySearch';
		$this->url = '/User/getUsersBySearch';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Get feedback received by a particular user.
	 * 
	 * @link http://developer.freelancer.com/GetUserFeedback
	 * @param Array $param (<br/>
	 * 	username(Optional*)		Ether username or userid is mandatory<br/>
	 * 	userid(Optional*)		Ether username or userid is mandatory<br/>
	 * 	type(Optional)			P = Provider Only; B = Buyer Only; A = Default, All<br/>
	 * )
	 */
	public function getUserFeedback($param = array()){
		$this->cur_call = 'getUserFeedback';
		$this->url = '/User/getUserFeedback';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Get the list of projects that can be rated.
	 * 
	 * @link http://developer.freelancer.com/GetPendingFeedback
	 * @param string $type P - Provider;B - Buyer,Default
	 */
	public function getPendingFeedback($type = 'B'){
		$this->cur_call = 'getPendingFeedback';
		$this->url = '/Common/getPendingFeedback';
		$this->type = $type;
		
		return $this->_call_api();
	}
	
	/**
	 * Get the profile information for a particular user.
	 * 
	 * @link http://developer.freelancer.com/GetUserDetails
	 * @param Array $param (<br/>
	 * 	userid/username(Required)		Retrieve the profile information of this particular user.<br/>
	 * )
	 */
	public function getUserDetails($param = array()){
		$this->cur_call = 'getUserDetails';
		$this->url = '/User/getUserDetails';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	/*****************************************************************
	 * Job
	 ****************************************************************/
	/**
	 * Retrieve the list of current job categories. We frequently tune our job categories. As a result, applications are expected to retrieve and update jobs list dynamically.
	 *  
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetJobList
	 */
	public function getJobList(){
		$this->cur_call = 'getJobList';
		$this->url = '/Job/getJobList';
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve job(skill) list for current user.
	 *  
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetMyJobList
	 */
	public function getMyJobList(){
		$this->cur_call = 'getMyJobList';
		$this->url = '/Job/getMyJobList';
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve job list with super category.
	 *  
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetCategoryJobList
	 */
	public function getCategoryJobList(){
		$this->cur_call = 'getCategoryJobList';
		$this->url = '/Job/getCategoryJobList';
		
		return $this->_call_api();
	}
	/*****************************************************************
	 * Profile
	 ****************************************************************/
	/**
	 * Retrieve the profile information of user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetAccountDetails
	 */
	public function getAccountDetails(){
		$this->cur_call = 'getAccountDetails';
		$this->url = '/Profile/getAccountDetails';
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the profile information of another user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetProfileInfo
	 * @param string $userid
	 */
	public function getProfileInfo($userid){
		$this->cur_call = 'getProfileInfo';
		$this->url = '/Profile/getProfileInfo';
		$this->userid = $userid;
		
		return $this->_call_api();
	}
	
	/**
	 * Update the account information of user
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/SetProfileInfo
	 * @param Array $param (<br/>
	 * 	fullname(Optional*)	<br/>
	 *	company_name	<br/>
	 *	type_of_work	<br/>
 	 *	multipartpic	<br/>
	 *	addressline1	<br/>
	 *	addressline2	<br/>
	 *	city	<br/>
	 *	state	<br/>
	 *	country	<br/>
	 *	postalcode	<br/>
	 *	phone	<br/>
	 *	fax	<br/>
 	 *	notificationformat<br/>	
	 *	emailnotificationstatus<br/>	
	 *	receivenewsstatus	<br/>
	 *	bidwonnotificationstatus<br/>	
	 *	bidplacednotificationstatus	<br/>
	 *	newprivatemessagestatus	<br/>
	 *	qualificationcsv	<br/>
	 *	profiletext	<br/>
	 *	vision	<br/>
	 *	keywords	<br/>
	 *	hourlyrate<br/>
	 *  skill<br/>
	 * )<br/>
	 * * (at least one input needs to be specified for changing)
	 */
	public function setProfileInfo($param = array()){
		$this->cur_call = 'setProfileInfo';
		$this->url = '/Profile/setProfileInfo';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	/*****************************************************************
	 * Employer
	 ****************************************************************/
	/**
	 * Post a new project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PostNewProject
	 * @param Array $param (<br/>
	 * 	projectname(Required)	 	Project name to post<br/>
	 *	projectdesc(Required)	 	Project description<br/>
	 *	jobtypecsv(Required)	 	Job category associated with project<br/>
	 *	budgetoption(Required)		Budget of the project:<br>
	 *								0 - Customised Budget, only for FEATURED or FULLTIME project<br/>
	 *								1 - $250-750<br/>
	 *								2 - $750-1500<br/>
	 *								3 - $1500-3000<br/>
	 *								4 - $3000-5000<br/>
	 *								5 - $30-$250<br/>
	 *								6 - >$5000<br/>								
	 *	budget(Required)	 		Budget of the project. Required if using customised budget.<br/>
	 *	duration(Required)		 	Period of the project<br/>
	 *	isfeatured(Optional)		Set to 1 if post as a featured project.(Default: 0)<br/>
	 *	isnonpublic(Optional)	 	Set to 1 if post as a nonpublic project.(Default: 0)<br/>
	 *	isbidhidden(Optional)	 	Set to 1 if post as a sealbids project.(Default: 0)<br/>
	 *	isfulltime(Optional)	 	Set to 1 if post as a fulltime project.(Default: 0)<br/>
	 *	files(Optional)			 	Files attached to the project. (Default: 0)<br/>
	 * )
	 */
	public function postNewProject($param = array()){
		$this->cur_call = 'postNewProject';
		$this->url = '/Employer/postNewProject';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Post a new trial project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PostNewTrialProject
	 * @param Array $param (<br/>
	 * 	projectname(Required)	 	Project name to post<br/>
	 *	projectdesc(Required)	 	Project description<br/>
	 *	jobtypecsv(Required)	 	Job category associated with project<br/>
	 *	budgetoption(Required)	 	Budget of the project<br/>
	 *								* NOTICE: Customised budget is not allowed in trial project.<br/>
	 *								1 - $250-750<br/>
	 *								2 - $750-1500<br/>
	 *								3 - $1500-3000<br/>
	 *								4 - $3000-5000<br/>
	 *								5 - $30-$250<br/>
	 *								6 - >$5000<br/>	
	 *	duration(Required)		 	Period of the project<br/>
	 *	files(Optional)			 	Files attached to the project. (Default: 0)<br/>
	 * )
	 */
	public function postNewTrialProject($param = array()){
		$this->cur_call = 'postNewTrialProject';
		$this->url = '/Employer/postNewTrialProject';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Post a new draft project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PostNewDraftProject
	 * @param Array $param (<br/>
	 * 	projectname(Required)	 	Project name to post<br/>
	 *	projectdesc(Required)	 	Project description<br/>
	 *	jobtypecsv(Required)	 	Job category associated with project<br/>
	 *	budgetoption(Required)		Budget of the project:<br>
	 *								0 - Customised Budget, only for FEATURED or FULLTIME project<br/>
	 *								1 - $250-750<br/>
	 *								2 - $750-1500<br/>
	 *								3 - $1500-3000<br/>
	 *								4 - $3000-5000<br/>
	 *								5 - $30-$250<br/>
	 *								6 - >$5000<br/>	
	 *	budget(Required)	 		Budget of the project. Required if using customised budget.<br/>
	 *	duration(Required)		 	Period of the project<br/>
	 *	isfeatured(Optional)		Set to 1 if post as a featured project.(Default: 0)<br/>
	 *	isnonpublic(Optional)	 	Set to 1 if post as a nonpublic project.(Default: 0)<br/>
	 *	isbidhidden(Optional)	 	Set to 1 if post as a sealbids project.(Default: 0)<br/>
	 *	isfulltime(Optional)	 	Set to 1 if post as a fulltime project.(Default: 0)<br/>
	 *	files(Optional)			 	Files attached to the project. (Default: 0)<br/>
	 * )
	 */
	public function postNewDraftProject($param = array()){
		$this->cur_call = 'postNewDraftProject';
		$this->url = '/Employer/postNewDraftProject';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Allows a project creator to select a freelancer for their project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/ChooseWinnerForProject
	 * @param string $projectid
	 * @param string $useridcsv Allows multiple winner for ALL except full-time jobs. At-least one ID mandatory
	 */
	public function chooseWinnerForProject($projectid, $useridcsv){
		$this->cur_call = 'chooseWinnerForProject';
		$this->url = '/Employer/chooseWinnerForProject';
		$this->projectid = $projectid;
		$this->useridcsv = $useridcsv;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the list of projects posted by the current user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetPostedProjectList
	 * @param Array $param (<br/>
	 * 	status(Optional)	 	1 - All; 
	 *							2 - Open And Frozen - Default; 
	 *							3 - Frozen Awaiting your action; 
	 *							4 - Awaiting Bidder Action; 
	 *							5 - Closed Won; 
	 *							6 - Closed Lost; 
	 *							7 - Closed Canceled<br/>
	 *	
	 *	userid(Optional)	 	UserID of the Poster<br/>
	 *	projectid(Optional)		Project ID filter<br/>
	 *	count(Optional)			(Default: 50)<br/>
	 *	page(Optional)			(Default: 0)<br/>
	 *	projectoption	 		Project type:'trial' - Trial project list;'draft' - Draft project list;(Default: Active project list)<br/>
	 * )
	 */
	public function getPostedProjectList($param = array()){
		$this->cur_call = 'getPostedProjectList';
		$this->url = '/Employer/getPostedProjectList';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Invite a freelancer to bid on a created project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/InviteUserForProject
	 * @param Array $param (<br/>
	 *	useridcsv or usernamecsv (Required)	 	Can be CSV also to allow multiple user invite<br/>
	 *	projectid(Required)		<br/>
	 * )
	 */
	public function inviteUserForProject($param = array()){
		$this->cur_call = 'inviteUserForProject';
		$this->url = '/Employer/inviteUserForProject';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Update the details for a posted project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/UpdateProjectDetails
	 * @param Array $param (<br/>
	 *	projectid(Required)		<br/>
	 *	projectdesc<br/>
	 *	jobtypecsv<br/>
	 *	files<br/>
	 * )
	 */
	public function updateProjectDetails($param = array()){
		$this->cur_call = 'updateProjectDetails';
		$this->url = '/Employer/updateProjectDetails';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the eligibility for current user to post a trial project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/EligibleForTrialProject
	 */
	public function eligibleForTrialProject(){
		$this->cur_call = 'eligibleForTrialProject';
		$this->url = '/Employer/eligibleForTrialProject';
		
		return $this->_call_api();
	}
	
	/**
	 * Publish Draft project to Trial or Normal.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PublishDraftProject
	 * 
	 * @param string $projectid		Draft project ID
	 * @param string $publishoption Publish option:<b>trial</b> - Publish to Trial Project (if not eligible, saved as Draft project);<br/>
	 * 								<b>normal</b> - Publish to Normal Project (Default)
	 */
	public function publishDraftProject($projectid,$publishoption='normal'){
		$this->cur_call = 'publishDraftProject';
		$this->url = '/Employer/publishDraftProject';
		$this->projectid = $projectid;
		$this->publishoption = $publishoption;
		
		return $this->_call_api();
	}
	
	/**
	 * Delete draft project
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/DeleteDraftProject
	 * 
	 * @param string $projectid		Draft project ID
	 */
	public function deleteDraftProject($projectid){
		$this->cur_call = 'deleteDraftProject';
		$this->url = '/Employer/deleteDraftProject';
		$this->projectid = $projectid;
		
		return $this->_call_api();
	}
	
	/**
	 * Upgrade Trial project to Normal.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/UpgradeTrialProject
	 * 
	 * @param string $projectid		Trial project ID
	 */
	public function upgradeTrialProject($projectid){
		$this->cur_call = 'upgradeTrialProject';
		$this->url = '/Employer/upgradeTrialProject';
		$this->projectid = $projectid;
		
		return $this->_call_api();
	}
	/*****************************************************************
	 * Freelancer
	 ****************************************************************/
	/**
	 * Get the list of projects that have been bid by the current user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetProjectListForPlacedBids
	 * @param Array $param (<br/>
	 *	status(Optional)	 	1 - All;
	 *							2 - Open And Frozen - Default;
	 *							3 - Frozen Awaiting your action;
	 *							4 - Awaiting Bidder Action;
	 *							5 - Closed Won;
	 *							6 - Closed Lost;
	 *							7 - Closed Canceled<br/>
	 *	
	 *	userid(Optional)	 	UserID of the Poster<br/>
	 *	projectid(Optional)		Project ID filter<br/>
	 *	count(Optional)	 		(Default: 50)<br/>
	 *	page(Optional)	 		(Default: 0)<br/>
	 * )
	 */
	public function getProjectListForPlacedBids($param = array()){
		$this->cur_call = 'getProjectListForPlacedBids';
		$this->url = '/Freelancer/getProjectListForPlacedBids';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Place bid on project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PlaceBidOnProject
	 * @param Array $param (<br/>
	 * 	projectid(Required)<br/>
	 *	amount(Required) Price for your bid<br/>
	 *	days(Required) Delivery time<br/>
	 *	description(Required)<br/>
	 *	notificationStatus(Optional)	Notification on anyone else bid on this project at a lower price. 0 - no notification, 1 - notification (Default: 0)<br/>
	 *  highlighted(Optional)	Highlight bids. 0 - not highlighted, 1 - highlighted. (Default: 0)<br/>
	 *  milestone(Optional)	The initial milestone percentage declares the terms of your bid. This tells the employer that you require the specified percentage as a milestone payment before you start work.<br/>
	 * 	highlightedCurrencyId(Optional) The ID of currency for highlighting the bid. (Default: 1) -- USD as default<br/>
	 * )
	 */
	public function placeBidOnProject($param = array()){
		$this->cur_call = 'placeBidOnProject';
		$this->url = '/Freelancer/placeBidOnProject';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Retract a bid that has been placed on a project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/RetractBidFromProject
	 * @param string projectid
	 */
	public function retractBidFromProject($projectid){
		$this->cur_call = 'retractBidFromProject';
		$this->url = '/Freelancer/retractBidFromProject';
		$this->projectid = $projectid;
		
		return $this->_call_api();
	}
	
	/**
	 * After a freelancer has been selected for a project, this method should be called to accept the offer.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/AcceptBidWon
	 * @param string $projectid
	 * @param int $state 	0 - Decline; 
	 *						1 - Accept - Default
	 */
	public function acceptBidWon($projectid, $state = 1){
		$this->cur_call = 'acceptBidWon';
		$this->url = '/Freelancer/acceptBidWon';
		$this->projectid = $projectid;
		$this->state = $state;
		
		return $this->_call_api();
	}
	/*****************************************************************
	 * Common
	 ****************************************************************/
	/**
	 * Submit a request to cancel a project
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/RequestCancelProject
	 * @param Array $param (<br/>
	 * 	projectid(Required)<br/>
	 * 	selectedwinner(Required)			Selected winner ID for the project<br/>
	 *	commenttext(Required)<br/>
	 *	reasoncancellation(Optional)		1 - Mutual - Default;
	 *										2 - Service Done,Not Paid;
	 *										3 - Service Not Done;
	 *										4 - No Communication;
	 *										5 - Quality of Service;
	 *										6 - Other<br/>
	 *	followedguidelinesstatus(Optional)	1 - I followed; 0 - I didn't follow<br/>
	 *										(This is option is not needed unless felt necessary)<br/>
	 * )
	 */
	public function requestCancelProject($param = array()){
		$this->cur_call = 'requestCancelProject';
		$this->url = '/Common/requestCancelProject';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Post a feedback for a user
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PostFeedback
	 * @param Array $param (<br/>
	 * 	rating(Required)				1 to 10<br/>
	 *	feedbacktext(Required)	 		Text of feedback<br/>
	 *	userid or username(Required)	UserId or username the feedback posted to<br/>
	 *	projectid(Required)				Project Id associated with the feedback<br/>
	 * )
	 */
	public function postFeedback($param = array()){
		$this->cur_call = 'postFeedback';
		$this->url = '/Common/postFeedback';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Post a comment in reply to a feedback posted by another user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PostReplyForFeedback
	 * @param Array $param (<br/>
	 *	feedbacktext(Required)	 		Text of feedback<br/>
	 *	userid or username(Required)	UserId or username the feedback posted to<br/>
	 *	projectid(Required)				Project Id associated with the feedback<br/>
	 * )
	 */
	public function postReplyForFeedback($param = array()){
		$this->cur_call = 'postReplyForFeedback';
		$this->url = '/Common/postReplyForFeedback';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Submit a request to withdraw a feedback posted by another user on a finished project
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/RequestWithdrawFeedback
	 * @param Array $param (<br/>
	 *	userid or username(Required)	UserId or username the feedback posted to<br/>
	 *	projectid(Required)				Project Id associated with the feedback<br/>
	 * )
	 */
	public function requestWithdrawFeedback($param = array()){
		$this->cur_call = 'requestWithdrawFeedback';
		$this->url = '/Common/requestWithdrawFeedback';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Return the current config version
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetConfigVersion
	 * @param string $function	 withdrawalfee | projectfee | joblist | terms | budget, withdrawalfee for default
	 */
	public function getConfigVersion($function){
		$this->cur_call = 'getConfigVersion';
		$this->url = '/Common/getConfigVersion';
		$this->function = $function;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve Terms and Conditions
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetTerms
	 */
	public function getTerms(){
		$this->cur_call = 'getTerms';
		$this->url = '/Common/getTerms';
		
		return $this->_call_api();
	}
	
	/**
	 * Returns all supported currencies.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/ReleaseNotes/20100923
	 */
	public function getCurrencies(){
		$this->cur_call = 'getCurrencies';
		$this->url = '/Common/getCurrencies';
		
		return $this->_call_api();
	}
	
	/**
	 * Get the list of project budget options
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetProjectBudgetOptions
	 * @param int $currency(Required) Currency ID
	 */
	public function getProjectBudgetOptions($currency){
		$this->cur_call = 'getProjectBudgetOptions';
		$this->url = '/Common/getProjectBudgetOptions';
		$this->currency = $currency;
		
		return $this->_call_api();
	}
	
	/*****************************************************************
	 * Payments
	 ****************************************************************/
	/**
	 * Retrieve the current user's balance and the details of the last transaction.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetAccountBalanceStatus
	 */
	public function getAccountBalanceStatus(){
		$this->cur_call = 'getAccountBalanceStatus';
		$this->url = '/Payment/getAccountBalanceStatus';
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the list of transactions and details for an account.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetAccountTransactionList
	 * @param Array $param (<br/>
	 *	count(Optional)		(Default: 50)<br/>
	 *	page(Optional)		(Default: 0)<br/>
	 *	datefrom(Optional)	Get transactions from the date<br/>
	 *	dateto(Optional)	Get transactions up to the date<br/>
	 * )
	 */
	public function getAccountTransactionList($param = array()){
		$this->cur_call = 'getAccountTransactionList';
		$this->url = '/Payment/getAccountTransactionList';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Make a request to withdraw funds.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/RequestWithdrawal
	 * @param Array $param (<br/>
	 *	amount(Required)			Withdraw amount<br/>
	 *	method(Required)	 		paypal | moneybooker | wire | paynoneer(default)<br/>
	 *	additionaltext(Required)	Required for wire withdraw<br/>
	 *	paypalemail(Required)		Required for paypal withdraw<br/>
	 *	mb_account(Required)		Required for moneybooker withdraw<br/>
	 *	description(Required)		Required for wire withedraw<br/>
	 *	country_code(Required)	 	Required for wire withdraw<br/>
	 * )
	 */
	public function requestWithdrawal($param = array()){
		$this->cur_call = 'requestWithdrawal';
		$this->url = '/Payment/requestWithdrawal';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Create a milestone (escrow) payment.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/CreateMilestonePayment
	 * @param Array $param (<br/>
	 * 	projectid(Required)					Mandatory if Partial or Full payment for a project.<br/>
	 *	amount(Required)					Milestone amount<br/>
	 *	touserid or tousername(Required)	Userid or username create milestone payment to<br/>
	 *	reasontext(Required)				Text attached to transfer<br/>
	 *	reasontype(Required)				partial|full|other<br/>
	 * )
	 */
	public function createMilestonePayment($param = array()){
		$this->cur_call = 'createMilestonePayment';
		$this->url = '/Payment/createMilestonePayment';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Transfer money to another user. Please note that direct transfer is subject to higher charge. For detail please check <a href="http://www.freelancer.com">Freelancer.com</a>
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/TransferMoney
	 * @param Array $param (<br/>
	 * 	projectid(Required)					Mandatory if Partial or Full payment for a project.<br/>
	 *	amount(Required)					Min $30 Validation<br/>
	 *	touserid or tousername(Required)	Userid or username transfer money to<br/>
	 *	reasontext(Required)				Text attached to transfer<br/>
	 *	reasontype(Required)				partial|full|other<br/>
	 * )
	 */
	public function transferMoney($param = array()){
		$this->cur_call = 'transferMoney';
		$this->url = '/Payment/transferMoney';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Cancel a withdrawal request.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/RequestCancelWithdrawal
	 * @param string $Withdrawalid 
	 */
	public function requestCancelWithdrawal($Withdrawalid){
		$this->cur_call = 'requestCancelWithdrawal';
		$this->url = '/Payment/requestCancelWithdrawal';
		$this->Withdrawalid = $Withdrawalid;
		
		return $this->_call_api();
	}
	
	/**
	 * Cancel a milestone payment. This method is only for the payee of milestone(escrow)
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/CancelMilestone
	 * @param string $transactionid Transaction Id
	 */
	public function cancelMilestone($transactionid){
		$this->cur_call = 'cancelMilestone';
		$this->url = '/Payment/cancelMilestone';
		$this->transactionid = $transactionid;
		
		return $this->_call_api();
	}
	
	/**
	 * Get the list of incoming and outgoing milestone payments.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetAccountMilestoneList
	 * @param string $type Incoming(default) or Outgoing
	 * @param int $count default is 50
	 * @param int $page default is 0
	 */
	public function getAccountMilestoneList($type = 'Incoming',$count = 50, $page = 0){
		$this->cur_call = 'getAccountMilestoneList';
		$this->url = '/Payment/getAccountMilestoneList';
		$this->type = $type;
		$this->count = $count;
		$this->page = $page;
		
		return $this->_call_api();
	}
	
	/**
	 * View the list of withdrawals that have been requested and are pending.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetAccountWithdrawalList
	 * @param string $type Incoming(default) or Outgoing
	 * @param int $count default is 50
	 * @param int $page default is 0
	 */
	public function getAccountWithdrawalList($type = 'Incoming',$count = 50, $page = 0){
		$this->cur_call = 'getAccountWithdrawalList';
		$this->url = '/Payment/getAccountWithdrawalList';
		$this->type = $type;
		$this->count = $count;
		$this->page = $page;
		
		return $this->_call_api();
	}
	
	/**
	 * Send a request to payer for releasing an incoming milestone payment.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/RequestReleaseMilestone
	 * @param string $transactionid Transaction Id for milestone
	 */
	public function requestReleaseMilestone($transactionid){
		$this->cur_call = 'requestReleaseMilestone';
		$this->url = '/Payment/requestReleaseMilestone';
		$this->transactionid = $transactionid;
		
		return $this->_call_api();
	}
	
	/**
	 * Release a milestone payment.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/ReleaseMilestone
	 * @param string $transactionid 	Transaction Id for milestone
	 * @param string $fullname 			Fullname of the payer
	 */
	public function releaseMilestone($transactionid, $fullname){
		$this->cur_call = 'releaseMilestone';
		$this->url = '/Payment/releaseMilestone';
		$this->transactionid = $transactionid;
		$this->fullname = $fullname;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the balance for current user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PrepareTransfer
	 * @param string $projectid 	Mandatory if Partial or Full payment for a project.
	 * @param string $amount 	Min $30 Validation
	 * @param string $touserid 	Userid transfer money to.
	 * @param string $reasontype 	partial|full|other
	 */
	public function prepareTransfer($projectid, $amount, $touserid, $reasontype = 'full'){
		$this->cur_call = 'prepareTransfer';
		$this->url = '/Payment/prepareTransfer';
		$this->projectid = $projectid;
		$this->amount = $amount;
		$this->touserid = $touserid;
		$this->reasontype = $reasontype;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the balance for current user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetBalance
	 */
	public function getBalance(){
		$this->cur_call = 'getBalance';
		$this->url = '/Payment/getBalance';
		
		return $this->_call_api();
	}
	
	/**
	 * Transfer money to another user. Please note that direct transfer is subject to higher charge.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetProjectListForTransfer
	 */
	public function getProjectListForTransfer(){
		$this->cur_call = 'getProjectListForTransfer';
		$this->url = '/Payment/getProjectListForTransfer';
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the withdrawal fee.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetWithdrawalFees
	 */
	public function getWithdrawalFees(){
		$this->cur_call = 'getWithdrawalFees';
		$this->url = '/Payment/getWithdrawalFees';
		
		return $this->_call_api();
	}
	
	/*****************************************************************
	 * Notification
	 ****************************************************************/
	/**
	 * Get alert messages for the current user. This information is quite important if exists, applications are advised to show this information as soon as possible if it is available.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetNotification
	 */
	public function getNotification(){
		$this->cur_call = 'getNotification';
		$this->url = '/Notification/getNotification';
		
		return $this->_call_api();
	}
	
	/**
	 *Get the current news items posted by the Freelancer.com staff. Like the coming events.
	 * 
	 * @link http://developer.freelancer.com/GetNews
	 */
	public function getNews(){
		$this->cur_call = 'getNews';
		$this->url = '/Notification/getNews';
		
		return $this->_call_api();
	}
	/*****************************************************************
	 * Project
	 ****************************************************************/
	/**
	 * Search projects using a various set of criteria.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/SearchProjects
	 * @param Array $param (<br/>
	 * 	isfeatured	 		Gets only featured jobs<br/>
	 *	isnonpublic	 		Gets only Non Public Job<br/>
	 *	searchkeyword	<br/>
	 *	searchjobtypecsv	<br/>
	 *	status	 			Open | Frozen | Closed| ClosedAwarded| ClosedCanceled<br/>
	 *	budgetmin	 		250 | 750 |1500 |3000 | Any<br/>
	 *	budgetmax	<br/>
	 *	isfulltime	<br/>
	 *	istrial	<br/>
	 *	isgoldmembersonly	<br/>
	 *	bidendsduration	 	submitdate (default) | bid_enddate | id | state<br/>
	 *	count	<br/>
	 *	page	<br/>
	 *	tags	<br/>
	 *	sorter<br/>
	 * )
	 */
	public function searchProjects($param = array()){
		$this->cur_call = 'searchProjects';
		$this->url = '/Project/searchProjects';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Get the cost for various project posting options.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetProjectFees
	 */
	public function getProjectFees(){
		$this->cur_call = 'getProjectFees';
		$this->url = '/Project/getProjectFees';
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the details for a particular project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetProjectDetails
	 * @param string $projectid	 Project Id for bids
	 */
	public function getProjectDetails($projectid){
		$this->cur_call = 'getProjectDetails';
		$this->url = '/Project/getProjectDetails';
		$this->projectid = $projectid;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the list of bids, and the the details for the bids, for a particular project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetBidsDetails
	 * @param string $projectid	 Project Id for bids
	 */
	public function getBidsDetails($projectid){
		$this->cur_call = 'getBidsDetails';
		$this->url = '/Project/getBidsDetails';
		$this->projectid = $projectid;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve the public messages posted to a project clarification board.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetPublicMessages
	 * @param string $projectid	 Project Id for bids
	 */
	public function getPublicMessages($projectid){
		$this->cur_call = 'getPublicMessages';
		$this->url = '/Project/getPublicMessages';
		$this->projectid = $projectid;
		
		return $this->_call_api();
	}
	
	/**
	 * Post a public message to project clarification board.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/PostPublicMessage
	 * @param Array $param (<br/>
	 * 	projectid(Required)	 	Project Id associated with the message<br/>
	 *	messagetext(Required)	Message text<br/>
	 *	filename(Optional)		Multipart File Content<br/>
	 * )
	 */
	public function postPublicMessage($param = array()){
		$this->cur_call = 'postPublicMessage';
		$this->url = '/Project/postPublicMessage';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve budget setting.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetProjectBudgetConfig
	 */
	public function getProjectBudgetConfig(){
		$this->cur_call = 'getProjectBudgetConfig';
		$this->url = '/Project/getProjectBudgetConfig';
		
		return $this->_call_api();
	}
	
	/*****************************************************************
	 * Message
	 ****************************************************************/
	/**
	 * Retrieve private messages sent to the current user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetInboxMessages
	 * @param Array $param (<br/>
	 * 	projectid(Optional)	 	Get the private messages for the specific project<br/>
	 *	count(Optional)	<br/>
	 *	page(Optional)	<br/>
	 * )
	 */
	public function getInboxMessages($param = array()){
		$this->cur_call = 'getInboxMessages';
		$this->url = '/Message/getInboxMessages';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve private message sent by the current user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetSentMessages
	 * @param Array $param (<br/>
	 *	count(Optional)	<br/>
	 *	page(Optional)	<br/>
	 * )
	 */
	public function getSentMessages($param = array()){
		$this->cur_call = 'getSentMessages';
		$this->url = '/Message/getSentMessages';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve private message sent by the current user.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/GetUnreadCount
	 */
	public function getUnreadCount(){
		$this->cur_call = 'getUnreadCount';
		$this->url = '/Message/getUnreadCount';
		
		return $this->_call_api();
	}
	
	/**
	 * Send a private message.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/SendMessage
	 * @param Array $param (<br/>
	 * 	projectid(Required)	 		Project Id to identify the message send with<br/>
	 * 	messagetext(Required)		Message text to send<br/>
	 * 	userid or username(Required)Receiver of the message<br/>
	 *	filename(Optional)			Multipart File Content<br/>
	 * )
	 */
	public function sendMessage($param = array()){
		$this->cur_call = 'sendMessage';
		$this->url = '/Message/sendMessage';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
	
	/**
	 * mark an income message as read.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/MarkMessageAsRead
	 * @param string $id Message Id to be marked as read
	 */
	public function markMessageAsRead($id){
		$this->cur_call = 'markMessageAsRead';
		$this->url = '/Message/MarkMessageAsRead';
		$this->id = $id;
		
		return $this->_call_api();
	}
	
	/**
	 * Retrieve private messages sent between two users for same project.
	 * 
	 * OAuth Required
	 * 
	 * @link http://developer.freelancer.com/LoadMessageThread
	 * @param Array $param (<br/>
	 * 	projectid(Required) Get the private messages for the specific project<br/>
	 *  betweenuserid(Required)	 The user Id for conversation between<br/>
	 *	count(Optional)	<br/>
	 *	page(Optional)	<br/>
	 * )
	 */
	public function loadMessageThread($param){
		$this->cur_call = 'loadMessageThread';
		$this->url = '/Message/loadMessageThread';
		$this->_userParam = $param;
		
		return $this->_call_api();
	}
}

/**
 * Set Global variable
 *
 * @link        http://stl.olfreelancer.com
 * @version     v0.1
 * @author    	Chunyou Zhao
 * @copyright 	Chunyou Zhao
 * @package		com.olfreelancer.stl
 * @license 	http://www.gnu.org/licenses/gpl.html GPLv3
 * @date		Apr 23, 2010 10:12:04 PM
 */
class SnowTigerLib_Config {
	//store global variable
	private $_Config;

	/**
	 * @var  SnowTigerLib_Config
	 */
	private static $_init;

	private function __construct() {
		$this->_Config = require_once 'SnowTigerLib_Config.inc.php';
		$this->setTestMode($this->_Config['TestMode']);
	}

	/**
	 * @return SnowTigerLib_Config
	 */
	public static function Init() {
		if (!self :: $_init) {
			self :: $_init = new SnowTigerLib_Config();
		}
		return self :: $_init;
	}

	/**
	 * Set the data environment:true is sandbox environment, false is formal environment
	 * @param bool $test
	 * @return SnowTigerLib_Config
	 */
	public function setTestMode($test = true) {
		if ($test) {
			$this->_Config['apiurl'] = 'http://api.sandbox.freelancer.com';
			$this->_Config['weburl'] = 'http://www.sandbox.freelancer.com';
		} else {
			$this->_Config['apiurl'] = 'http://api.freelancer.com';
			$this->_Config['weburl'] = 'http://www.freelancer.com';
		}
		return $this;
	}
	
	/**
	 * set result Format
	 * 
	 * @param string $format  xml/json
	 * @return SnowTigerLib_Config
	 */
	public function setFormat($format) {
		if($format == 'xml' || $format == 'json')
			$this->_Config['Format'] = $format;
		return $this;
	}

	/**
	 * set Consumer Token 
	 * 
	 * @param int $consumer_token
	 * @return SnowTigerLib_Config
	 */
	public function setConsumerToken($consumer_token) {
		$this->_Config['ConsumerToken'] = $consumer_token;

		return $this;
	}

	/**
	 * set Consumer Secret 
	 * 
	 * @param string $consumer_secret
	 * @return SnowTigerLib_Config
	 */
	public function setConsumerSecret($consumer_secret) {
		$this->_Config['ConsumerSecret'] = $consumer_secret;

		return $this;
	}

	/**
	* set the signature method,only support HMAC-SHA1 now
	* 
	* @param string $sign_method
	* @return SnowTigerLib_Config
	*/
	public function setSignMethod($sign_method) {
		$this->_Config['SignMethod'] = $sign_method;

		return $this;
	}

	/**
	 * Turn on or off the error tips
	 * 
	 * @param bool $CloseError
	 * @return SnowTigerLib_Config
	 */
	public function setCloseError($CloseError = true) {
		$this->_Config['CloseError'] = (bool) $CloseError;

		return $this;
	}

	/**
	 * Turn on or off the API call logs
	 * 
	 * @param bool $Log
	 * @return SnowTigerLib_Config
	 */
	public function setApiLog($Log) {
		$this->_Config['ApiLog'] = (bool) $Log;

		return $this;
	}

	/**
	 * Turn on or off the error logs
	 * 
	 * @param bool $Errorlog
	 * @return SnowTigerLib_Config
	 */
	public function setErrorlog($Errorlog) {
		$this->_Config['Errorlog'] = $Errorlog;

		return $this;
	}

	/**
	 * Set the number of retries when failed to call the API
	 * This can improve the stability of API, the default is 3 times
	 * 
	 * @param int $RestNumberic
	 * @return SnowTigerLib_Config
	 */
	public function setRestNumberic($RestNumberic) {
		$this->_Config['RestNumberic'] = intval($RestNumberic);
		;

		return $this;
	}

	/**
	 * set Access Token 
	 * 
	 * @param int $access_token
	 * @return SnowTigerLib_Config
	 */
	public function setAccessToken($access_token) {
		$this->_Config['AccessToken'] = $access_token;

		return $this;
	}

	/**
	 * set Access Secret 
	 * 
	 * @param string $access_secret
	 * @return SnowTigerLib_Config
	 */
	public function setAccessSecret($access_secret) {
		$this->_Config['AccessSecret'] = $access_secret;

		return $this;
	}

	/**
	 * return the global set variable
	 * 
	 * @return object
	 */
	public function getConfig() {
		return (object) $this->_Config;
	}
}

/**
 * Exception deal Class
 * 
 * @package		com.olfreelancer.stl
 */
class SnowTigerLib_Exception {
	private $_ErrorInfo;
	public function __construct($error, $paramArr = null, $closeerror = false, $Errorlog = false) {
		return $this->ViewError($error, $paramArr, $closeerror, $Errorlog);
	}

	public function getErrorInfo() {
		return $this->_ErrorInfo;
	}

	public function WriteError($error, $paramArr) {
		$errorpath = dirname(__FILE__) . '/api_error_log';
		if (!is_dir($errorpath)) {
			@ mkdir($errorpath);
		}
		if ($fp = @ fopen($errorpath . '/' . date('Y-m-d') . '.log', 'a')) {
			$errorinfotext[] = date('Y-m-d H:i:s');
			if(isset($error['http_code'])){
				$errorinfotext[] = "HTTP Error	http_code:" . $error['http_code'];
			}else{
				$errorinfotext[] = "Code:" . $error['code'];
				$errorinfotext[] = "Error:" . $error['msg'];
				$errorinfotext[] = "Details:" . $error['longmsg'];
			}
			foreach ($paramArr as $key => $value) {
				$errorinfotext[] = $key . " : " . $value;
			}
			$errorinfotext = implode("\t", $errorinfotext) . "\r\n";
			@ fwrite($fp, $errorinfotext);
			fclose($fp);
		}
	}

	public function ViewError($error, $paramArr = null, $closeerror = false, $Errorlog = false) {
		$debug = debug_backtrace(false);
		rsort($debug);
		if (is_array($error)) {
			if ($Errorlog) {
				$this->WriteError($error, $paramArr);
			}
			if ($closeerror) {
				return false;
			}
			$errortitlediy = isset($error['http_code'])?"HTTP Error: http_code:".$error['http_code']:"Error(" . $error['code'] . "): " . $error['msg'];
		} else {
			$errortitlediy = $error;
		}

		$view[] = "<br /><font size='1'><table dir='ltr' border='1' cellspacing='0' cellpadding='1' width=\"100%\">";

		$view[] = "<tr><th align='left' bgcolor='#f57900' colspan=\"3\"><span style='background-color: #cc0000; color: #fce94f; font-size: x-large;'>( ! )</span> " . $errortitlediy . " in " . $debug[count($debug) - 2]['file'] . " on line <i>" . $debug[count($debug) - 2]['line'] . "</i></th></tr>";

		$view[] = "<tr><th align='left' bgcolor='#e9b96e' colspan='3'>Details: " . (isset($error['http_code'])?$error['url']:(is_array($error['longmsg'])?implode(",", $error['longmsg']):$error['longmsg'])) . "</th></tr>";

		$view[] = "<tr><th align='left' bgcolor='#e9b96e' colspan='3'>Functions</th></tr>";
		$view[] = "<tr><th align='center' bgcolor='#eeeeec' width='30'>#</th><th align='left' bgcolor='#eeeeec'>Function name</th><th align='left' bgcolor='#eeeeec'>In File</th></tr>";
		$mainfile = basename($debug[0]['file']);

		$view[] = "<tr><td bgcolor='#eeeeec' align='center'>1</td><td bgcolor='#eeeeec'>{main}(  )</td><td bgcolor='#eeeeec'>../{$mainfile}<b>:</b>0</td></tr>";

		foreach ($debug as $key => $value) {
			$value['file'] = basename($value['file']);
			$key = $key +2;
			$view[] = "<tr><td bgcolor='#eeeeec' align='center'>$key</td><td bgcolor='#eeeeec'>{$value['class']}{$value['type']}{$value['function']}(  )</td><td title='{$value['file']}' bgcolor='#eeeeec'>{$value['file']}<b>:</b>{$value['line']}</td></tr>";
		}

		$view[] = '</table></font>';
		if ($paramArr) {
			$view[] = "<br /><font size='1'><table dir='ltr' border='1' cellspacing='0' cellpadding='1' width=\"100%\">";
			$view[] = "<tr><th align='left' bgcolor='#e9b96e' colspan='4' height='25px'>Freelancer API Call Parameter list</th></tr>";
			$view[] = "<tr><th align='center' bgcolor='#eeeeec' width='30px'>#</th><th width='120' align='left' bgcolor='#eeeeec'>Parameter Name</th><th align='left' bgcolor='#eeeeec'>Value</th><th align='left' bgcolor='#eeeeec' width='50px'>Length</th></tr>";
			$i = 1;
			foreach ($paramArr as $key => $value) {
				if($key == 'CallAPI')
					continue;
				$view[] = "<tr><td bgcolor='#eeeeec' align='center'>$i</td><td bgcolor='#eeeeec'>{$key}</td><td bgcolor='#eeeeec'>" . implode(', ', explode(',', $value)) . "</td><td bgcolor='#eeeeec'><b>" . strlen($value) . "</b></td></tr>";
				$i++;
			}
			$view[] = '</table></font>';
		}

		$this->_ErrorInfo = implode("\n", $view);
	}
}
?>
