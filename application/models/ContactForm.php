<?php

class Application_Model_ContactForm extends Zend_Form 
{
     
    public function init() 
    { 
        $this->setName('contact_us')
            ->setMethod('POST');

        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Name')
            ->addFilter('StringTrim')
            ->addFilter('StripTags')
            ->addValidator('StringLength', false, array('min' => 2, 'max' => 50))
            ->setRequired(true);
        $this->addElement($name);

        $subject = new Zend_Form_Element_Text('subject');
        $subject->setLabel('Subject')
            ->addFilter('StringTrim')
            ->addFilter('StripTags')
            ->addValidator('StringLength', false, array('min' => 2, 'max' => 50))
            ->setRequired(true);
        $this->addElement($subject);

        $email = new Zend_Form_Element_Text('email');
        $email->setLabel('Email address')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array('min' => 2, 'max' => 50))
            ->addValidator('EmailAddress')
            ->setRequired(true);
        $this->addElement($email);
        
        $this->addElement('textarea', 'message', array(
            'label' => 'Message',
            'filters' => array('StringTrim'),
            'validators' => array(new Zend_Validate_StringLength(array('min' => 2, 'max' => 5000))),
            'required' => true
        ));

        $submit = new Zend_Form_Element_Submit('submit');
        $submit->setLabel('Contact us');
        $this->addElement($submit);
    }
     
}