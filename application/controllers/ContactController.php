<?php

class ContactController extends Zend_Controller_Action
{
    public function init()
    {
		Zend_Session::start();
		if(isset($_SESSION['returnUrl']) && $_SESSION['returnUrl']!='')
		{
			
			$this->_redirect($_SESSION['returnUrl']);
		}
		
		//print_r($messages); 
    }
	
    public function indexAction()
    {
        $request = $this->getRequest();
        
        $form = new Application_Model_ContactForm();
        
        if ($request->isPost() && $form->isValid($request->getPost())) {
            $message = "Message from {$form->name->getValue()} {$form->email->getValue()}\n\n"
                . $form->message->getValue();
            $mail = new Zend_Mail('utf-8');
            $mail->setSubject($form->subject->getValue())
                ->setBodyText($message)
                ->setFrom('support@freelancer.fm', 'Freelancer.fm')
                ->addTo('support@freelancer.fm', 'Freelancer.fm');
            $mail->send();
            
            $this->view->sent = true;
        }
        
        $this->view->form = $form;
    }
	public function testAction()
	{
		
	}
    
}