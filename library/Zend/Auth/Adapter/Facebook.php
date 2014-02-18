<?php
class Zend_Auth_Adapter_Facebook implements Zend_Auth_Adapter_Interface  {
    private $token = null;
    private $user = null;
    private $new_user_id = null;
 
    public function __construct($token) {
        $this->token = $token;
    }
 
    public function getUser() {
        return $this->user;
    }
	
	 public function getNewUserId() {
        return $this->new_user_id;
    }
 
    public function authenticate()  {
        if($this->token == null) {
            return new Zend_Auth_Result(Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID,
                            false, array('Token was not set'));
        }
 
        $graph_url = "https://graph.facebook.com/me?access_token=" . $this->token;
        $details = json_decode(file_get_contents($graph_url));

		//var_dump($details); die;
	
		$model = new Application_Model_Registration();
		$new_user_id = $model->insertUserFacebook($details);
		$this->new_user_id = $new_user_id; 
		/*$user = $details->email;
		$model = new Application_Model_General();
		$exist_user = $model->getUserIdByEmail($user);
		
		if($exist_user ==  false) { // first time login, register user
            $model = new Application_Model_Registration();
			$model->insertUserFacebook($details);
			//registerUser($user); // NOT AN ACTUAL FUNCTION
        }
		*/
		
	  /* $user = lookUpUserInDB($details->email); // NOT AN ACTUALL FUNCTION
        if($user ==  false) { // first time login, register user
            registerUser($user) // NOT AN ACTUAL FUNCTION
        }
		*/
       // $this->user = $user;
        return new Zend_Auth_Result(Zend_Auth_Result::SUCCESS,$new_user_id);
    }
}
?>