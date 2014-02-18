<?php

class Application_Model_AccountForm extends Zend_Form
{
    
    public function init()
    {
        $this->addElement('text', 'email', array(
            'label' => 'Email',
            'filters' => array('StringTrim'),
            'validators' => array('EmailAddress', new Zend_Validate_StringLength(array('min' => 3, 'max' => 40))),
            'required' => true
        ));
        
        $this->addElement('select', 'type', array(
            'multiOptions' => array(
                'user' => 'User',
                'admin' => 'Administrator'
            ),
            'label' => 'Type',
            'required' => true
        ));
        
        $this->addElement('checkbox', 'confirmed', array(
            'label' => 'Confirmed'
        ));
        
        $this->addElement('submit', 'save', array(
            'label' => 'Save'
        ));
    }
    
}