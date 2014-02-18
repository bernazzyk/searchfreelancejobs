<?php

class AccountController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    }

    public function indexAction()
    {
        // action body
    }

    public function signupAction()
    {
        $this->view->errors = array ();
        
        if ( $this->getRequest()->isPost() )
        {
            $customer = new Application_Model_Account();
            
            $result = $customer->save( $this->_request->getPost() );
            
            if ( ! is_array( $result ) )
            {
		        $this->_helper->json( array( 'success' => true ) );
            }
            else
            {
		        $this->_helper->json( array( 'success' => false, 'message' => implode( '\n\n', $result ) ) );
            }
        }
    }

    public function loginAction()
    {
        if ( $this->getRequest()->isPost() )
        {
            $email = $this->_request->getPost( 'email' );
            
            $password = $this->_request->getPost( 'password' );
            
            if ( empty( $email ) || empty( $password ) )
            {
                $this->view->errors[] = "Please provide your e-mail address and password.";
            }
            else
            {
                $authAdapter = new Zend_Auth_Adapter_DbTable( Zend_Registry::get( 'db' ) );
                
                $authAdapter
                	->setTableName( 'account' )
                	->setIdentityColumn( 'email' )
                	->setCredentialColumn( 'password' )
                	->setCredentialTreatment( 'MD5(?)' )
                	->setIdentity( $email )
                	->setCredential( $password );
                
                $auth = Zend_Auth::getInstance();
                
                $result = $auth->authenticate( $authAdapter );
                
                // Did the participant successfully login?
                if ( $result->isValid() )
                {
		            $this->_helper->json( array( 'success' => true ) );
                }
                else
                {
		            $this->_helper->json( array( 'success' => false, 'message' => 'Login failed. Have you confirmed your account?' ) );
                }
            }
        }
    }

    public function logoutAction()
    {
		Zend_Auth::getInstance()->clearIdentity();

		$this->_redirect('/');    
    }

    public function forgotPasswordAction()
    {
        // action body
    }

    public function changePasswordAction()
    {
        // action body
    }
}