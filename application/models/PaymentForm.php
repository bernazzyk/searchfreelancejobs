<?php

class Application_Model_PaymentForm extends Zend_Form
{
    
    public function init()
    {
        $this->setMethod('POST')
            ->setAttrib('id', 'payment-form');
        
        $this->addElement('radio', 'paytype', array(
            'label' => 'Payment Method',
            'multiOptions' => array(
                'cc' => 'Credit Card',
                'paypal' => 'PayPal'
            ),
            'required' => true,
            'value' => 'cc'
        ));
        
        $this->addElement('text', 'fname', array(
            'label' => 'First Name',
            'filters' => array('StringTrim'),
            'validators' => array('Alnum', new Zend_Validate_StringLength(array('min' => 2, 'max' => 50))),
            'required' => true,
            'class' => 'cc-field'
        ));
        
        $this->addElement('text', 'lname', array(
            'label' => 'Last Name',
            'filters' => array('StringTrim'),
            'validators' => array('Alnum', new Zend_Validate_StringLength(array('min' => 2, 'max' => 50))),
            'required' => true,
            'class' => 'cc-field'
        ));
        
        $this->addElement('text', 'company', array(
            'label' => 'Company',
            'validators' => array(new Zend_Validate_StringLength(array('min' => 2, 'max' => 50))),
            'filters' => array('StringTrim'),
            'class' => 'cc-field'
        ));
        
        $this->addElement('text', 'street', array(
            'label' => 'Street Address',
            'filters' => array('StringTrim'),
            'validators' => array(new Zend_Validate_StringLength(array('min' => 2, 'max' => 50))),
            'required' => true,
            'class' => 'cc-field'
        ));
        
        /*$this->addElement('text', 'street2', array(
            'label' => 'Street Address line 2',
            'validators' => array(new Zend_Validate_StringLength(array('min' => 2, 'max' => 50))),
            'filters' => array('StringTrim'),
            'class' => 'cc-field'
        ));*/
        
        $this->addElement('text', 'city', array(
            'label' => 'City',
            'filters' => array('StringTrim'),
            'validators' => array(new Zend_Validate_StringLength(array('min' => 2, 'max' => 50))),
            'required' => true,
            'class' => 'cc-field'
        ));
        
        $this->addElement('text', 'state', array(
            'label' => 'State or Province',
            'filters' => array('StringTrim'),
            'validators' => array(new Zend_Validate_StringLength(array('min' => 2, 'max' => 50))),
            'required' => true,
            'class' => 'cc-field'
        ));
        
        $this->addElement('text', 'post_code', array(
            'label' => 'Zip or Postal Code',
            'filters' => array('stringTrim'),
            'validators' => array('Digits', new Zend_Validate_StringLength(array('min' => 5, 'max' => 8))),
            'required' => true,
            'class' => 'cc-field'
        ));
        
        $countries = new Application_Model_DbTable_Countries();
        $this->addElement('select', 'country_id', array(
            'label' => 'Country',
            'required' => true,
            'class' => 'cc-field',
            'multiOptions' => $countries->getPairs(),
            'value' => 244
        ));
        
        $this->addElement('select', 'cctype', array(
            'label' => 'Credit Card Type',
            'required' => true,
            'multiOptions' => array(
                'VI' => 'Visa',
                'MC' => 'MasterCard',
                'AX' => 'Amex',
                'DI' => 'Discover',
                'JC' => 'JCB'
            ),
            'class' => 'cc-field'
        ));
        
        $this->addElement('text', 'cc', array(
            'label' => 'Credit Card Number',
            'filters' => array('StringTrim'),
            'validators' => array(new Zend_Validate_CreditCard()),
            'class' => 'cc-field',
            'required' => true,
            'description' => 'We DO NOT store your credit card information'
        ));
        
        $this->addElement('text', 'ccv', array(
            'label' => 'Card Security Code',
            'filters' => array('StringTrim'),
            'validators' => array('Digits', new Zend_Validate_StringLength(array('min' => 3, 'max' => 5))),
            'class' => 'cc-field',
            'required' => true
        ));
        
        $this->addElement('select', 'ccexpmonth', array(
            'label' => 'Card Expiration',
            'multiOptions' => array_combine(range(1, 12), range(1, 12)),
            'class' => 'cc-field',
            'required' => true
        ));
        
        $this->addElement('select', 'ccexpyear', array(
            'multiOptions' => array_combine(range(date('Y'), date('Y') + 5), range(date('Y'), date('Y') + 5)),
            'class' => 'cc-field',
            'required' => true
        ));
        $this->ccexpyear->removeDecorator('label');
        
        $this->addElement('submit', 'submit', array(
            'label' => 'Submit'
        ));
    }
    
}