<?php

class Application_Model_RegisterUser extends Zend_Form 
{
    
    public function init() 
    { 
        $this->setName('registeruser');
        
        $f_email = new Zend_Form_Element_Text('email');
        $f_email->setLabel('Email address <span class="star">*</span>')
            ->addFilter('StringToLower')
            ->setRequired(true)
            ->addValidator('NotEmpty', true)
            ->addValidator('EmailAddress');
        $f_email->getDecorator('Label')->setOption('escape',false);
        
        $f_password = new Zend_Form_Element_Password('password');
        $f_password ->setLabel('Password <span class="star">*</span>')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty')
            ->addValidator('IdenticalField', false, array('password_confirm', 'Confirm Password'));
        $f_password->getDecorator('Label')->setOption('escape',false);
        
        $f_password_confirm = new Zend_Form_Element_Password('password_confirm');
        $f_password_confirm->setLabel('Confirm Password <span class="star">*</span>')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty');
        $f_password_confirm->getDecorator('Label')->setOption('escape',false);
        
        $f_submit = new Zend_Form_Element_Submit('submit');
        $f_submit->setAttrib("class","button-green")
            ->setAttrib("value","Registrate")->setLabel('Start Bidding »');
        
         $this->addElements(array($f_email, $f_password,$f_password_confirm, $f_submit));
    }
}