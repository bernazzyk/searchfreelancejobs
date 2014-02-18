<?php

class Application_Model_RegisterForm extends Zend_Form 
{
    
    public function init() 
    { 
	
    }
	public function RegisterFrom($data)
	{
        $this->setName('register')->setAttrib('autocomplete','off');
        $f_email = new Zend_Form_Element_Text('email');
        $f_email->setLabel('Email address <span class="star">*</span>')
            ->addFilter('StringToLower')
            ->setRequired(true)
            ->addValidator('NotEmpty', true)
            ->addValidator('EmailAddress');
        $f_email->getDecorator('Label')->setOption('escape',false);
		
		
		$f_password = new Zend_Form_Element_Password('password');
        $f_password->setLabel('Password  <span class="star">*</span>')
            ->setRequired(true)
            ->addValidator('NotEmpty', true);
        $f_password->getDecorator('Label')->setOption('escape',false);
		
        $f_submit = new Zend_Form_Element_Submit('submit');
        $f_submit->setAttrib("class","button-green")
            ->setAttrib("value","Registrate")->setLabel('Update');
         $this->addElements(array($f_email,$f_password,$f_submit));	
	}
	public function unsubscribeFrom($data)
	{
        $this->setName('unsubscribe');
        $f_email = new Zend_Form_Element_Text('email');
        $f_email->setLabel('Email address <span class="star">*</span>')
            ->addFilter('StringToLower')
            ->setRequired(true)
            ->addValidator('NotEmpty', true)
            ->addValidator('EmailAddress');
        $f_email->getDecorator('Label')->setOption('escape',false);
        $f_submit = new Zend_Form_Element_Submit('submit');
        $f_submit->setAttrib("class","button-green")
            ->setAttrib("value","Unsubscribe")->setLabel('Unsubscribe');
         $this->addElements(array($f_email, $f_submit));	
	}
	public function passwordForm($data)
	{
		$this->setName('updatePassword')->setAttrib('autocomplete','off');		
		$f_password = new Zend_Form_Element_Password('password');
        $f_password->setLabel('Password  <span class="star">*</span>')
            ->setRequired(true)
            ->addValidator('NotEmpty', true);
        $f_password->getDecorator('Label')->setOption('escape',false);
        $f_submit = new Zend_Form_Element_Submit('submit');
        $f_submit->setAttrib("class","button-green")
            ->setAttrib("value","Registrate")->setLabel('Update');
         $this->addElements(array($f_password,$f_submit));	

	}
}