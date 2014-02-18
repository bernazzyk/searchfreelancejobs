<?php
class Application_Model_LoginForm extends Zend_Form
{
    public function __construct($options = null) 
    { 
        parent::__construct($options);
        $this->setMethod('post');
 
        $this->addElement(
            'text', 'email', array(
                'label' => 'Email:',
                'required' => true,
                'filters'    => array('StringTrim'),
            ));
 
        $this->addElement('password', 'password', array(
            'label' => 'Password:',
            'required' => true,
            ));
 
        $this->addElement('submit', 'submit', array(
            'ignore'   => true,
            'label'    => 'Login',
            'class'    => 'blue_submit search_btn',
            ));

 
    }
}
?>