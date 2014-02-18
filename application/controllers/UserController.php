<?php

class UserController extends Zend_Controller_Action
{
    public function loginAction()
    {
	
        $auth = TBS\Auth::getInstance();
        
        $providers = $auth->getIdentity();
        
        // Here the response of the providers are registered
        if ($this->_hasParam('provider')) {
            $provider = $this->_getParam('provider');
            
            switch ($provider) {
                case "facebook":
                    if ($this->_hasParam('code')) {
                        $adapter = new TBS\Auth\Adapter\Facebook(
                                $this->_getParam('code'));
                        $result = $auth->authenticate($adapter);
                    }
                    if($this->_hasParam('error')) {
                        throw new Zend_Controller_Action_Exception('Facebook login failed, response is: ' . 
                            $this->_getParam('error'));
                    }
                    break;
                case "twitter":
                    if ($this->_hasParam('oauth_token')) {
                        $adapter = new TBS\Auth\Adapter\Twitter($_GET);
                        $result = $auth->authenticate($adapter);
                    }
                    break;
                case "google":
                    
                    if ($this->_hasParam('code')) {
                        $adapter = new TBS\Auth\Adapter\Google(
                                $this->_getParam('code'));
                        $result = $auth->authenticate($adapter);
                    }
                    if($this->_hasParam('error')) {
                        throw new Zend_Controller_Action_Exception('Google login failed, response is: ' . 
                            $this->_getParam('error'));
                    }
                    break;
                
            }
            // What to do when invalid
            if (isset($result) && !$result->isValid()) {
                $auth->clearIdentity($this->_getParam('provider'));
                throw new Zend_Controller_Action_Exception('Login failed');
            } else {
                $this->_redirect('/user/connect');
            }
        } else { // Normal login page
            print TBS\Auth\Adapter\Facebook::getAuthorizationUrl();
            die('aici');
            
            $this->view->googleAuthUrl = TBS\Auth\Adapter\Google::getAuthorizationUrl();
            $this->view->googleAuthUrlOffline = TBS\Auth\Adapter\Google::getAuthorizationUrl(true);
            $this->view->facebookAuthUrl = TBS\Auth\Adapter\Facebook::getAuthorizationUrl();
            
            $this->view->twitterAuthUrl = \TBS\Auth\Adapter\Twitter::getAuthorizationUrl();
        }

    }
    public function connectAction()
    {
        $auth = TBS\Auth::getInstance();
        if (!$auth->hasIdentity()) {
            throw new Zend_Controller_Action_Exception('Not logged in!', 404);
        }
        $this->view->providers = $auth->getIdentity();
    }

    public function logoutAction()
    {
        \TBS\Auth::getInstance()->clearIdentity();
        $this->_redirect('/');
    }
    
    public function pauthAction()
    {
		
		Zend_Session::start();
	    $auth = Zend_Auth::getInstance();
        $authStorage = $auth->getStorage();
        $userId = (int)$authStorage->read();
        if (!$userId) {
            throw new Zend_Controller_Action_Exception('not logged in');
        }
		if(isset($_SESSION['msgReturnUrl']) && $_SESSION['msgReturnUrl']!='' && $_SESSION['rid']==$userId)
		{
			$url = 	$_SESSION['msgReturnUrl'];
			unset($_SESSION['msgReturnUrl']);
			$this->_redirect($url);
		}
        $this->_redirect('/');   
        $this->_helper->layout->setLayout('ajaxlayout');
        
       
        
        $accountModel = new Application_Model_DbTable_Accounts();
        $platformModel = new Application_Model_DbTable_Platforms();
        
        $account = $accountModel->find($userId)->current();
        if (null === $account) {
            throw new Zend_Controller_Action_Exception('Wrong parameters');
        }
        
        $platformId = (int)$this->_getParam('p');
        
        $platforms = $account->getConnections();
        
        $platformsList = $platformModel->getAssoc($curlOnly = true);
        $platforms = array_intersect($platforms, array_keys($platformsList));
        
        if (!$platforms) {
            $this->_redirect('/');
        }
        
        if ($platformId) {
            $this->_helper->layout->disableLayout();
        }
        
        $currentPlatformId = (int)$this->_getParam('next') ?: current($platforms);
        $nextPlatformKey = array_search($currentPlatformId, $platforms) + 1;
        $nextPlatformId = isset($platforms[$nextPlatformKey]) ? $platforms[$nextPlatformKey] : null;
        
        $nextUrl = $nextPlatformId
            ? '/user/pauth/next/' . $nextPlatformId
            : '/';
        
        if ($platformId) {
            $connection = $account->getConnection($platformId);
            if (!$connection) {
                die('connection is not found');
            }
            
            $this->view->username = $connection['username'];
            $this->view->password = $connection['password'];
        }
        
        $this->view->platformId = $platformId;
        $this->view->currentPlatformId = $currentPlatformId;
        $this->view->nextPlatformId = $nextPlatformId;
        $this->view->nextUrl = $nextUrl;
    }
    
    public function backAction()
    {
        $this->_helper->layout->setLayout('ajaxlayout');
        $this->view->platformId = (int)$this->_getParam('p');
    }
    
}