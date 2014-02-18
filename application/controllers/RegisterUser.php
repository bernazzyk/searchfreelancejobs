<?php
class Application_Form_RegisterUser extends Zend_Form 
{
	public function __construct($options = null) 
    { 
        parent::__construct($options);

        $this->setName('registeruser');
		$CountriesArr = array();
		$modelFromIndex = new Application_Model_Index();
		$Countries = $modelFromIndex->getCountries();
		foreach($Countries as $key=>$Value)
		{
			$CountriesArr[$Value['id']]=$Value['name'];
		}

        $f_firstName = new Zend_Form_Element_Text('firstName');
        $f_firstName->setLabel('First name')
                  ->setRequired(true)
                  ->addValidator('NotEmpty');

        $f_lastName = new Zend_Form_Element_Text('lastName');
        $f_lastName->setLabel('Last name')
                 ->setRequired(true)
                 ->addValidator('NotEmpty');
		
		$f_companyName = new Zend_Form_Element_Text('companyName');
        $f_companyName->setLabel('Company name')
                 ->setRequired(true)
                 ->addValidator('NotEmpty');
				 
		$f_country = new Zend_Form_Element_Select('country');
        $f_country->setLabel('Country')
              ->setMultiOptions($CountriesArr)
              ->setRequired(true)->addValidator('NotEmpty', true);

		$f_street = new Zend_Form_Element_Text('street');
        $f_street->setLabel('Street');
		
		$f_state = new Zend_Form_Element_Text('state');
        $f_state->setLabel('State');
		
		$f_post_code = new Zend_Form_Element_Text('post_code');
        $f_post_code->setLabel('Post code');
		
        $f_email = new Zend_Form_Element_Text('email');
        $f_email->setLabel('Email address')
              ->addFilter('StringToLower')
              ->setRequired(true)
              ->addValidator('NotEmpty', true)
              ->addValidator('EmailAddress'); 

		$f_userName = new Zend_Form_Element_Text('userName');
        $f_userName->setLabel('User name')
                 ->setRequired(true)
                 ->addValidator('NotEmpty');
		 
		/*$f_password = new Zend_Form_Element_Password('password');
		$f_password
			->setLabel('Password');*/
		
		/*	
		$pswd = new Zend_Form_Element_Password('password');
		$pswd->setLabel('New Password:');
		$pswd->setAttrib('size', 35);
		$pswd->setRequired(true);
		$pswd->removeDecorator('label');
		$pswd->removeDecorator('htmlTag');
		$pswd->removeDecorator('Errors');
		$pswd->addValidator('StringLength', false, array(4,15));
		$pswd->addErrorMessage('Please choose a password between 4-15 characters');

		$confirmPswd = new Zend_Form_Element_Password('confirm_pswd');
		$confirmPswd->setLabel('Confirm New Password:');
		$confirmPswd->setAttrib('size', 35);
		$confirmPswd->setRequired(true);
		$confirmPswd->addValidator('Identical', false, array('token' => 'password'));
		$confirmPswd->addErrorMessage('The passwords do not match');	*/

		$f_submit = new Zend_Form_Element_Submit('submit');
        $f_submit->setLabel('Submit');

         $this->addElements(array($f_firstName, $f_lastName,$f_companyName,$f_country, $f_street,$f_state,$f_post_code,$f_email, /*$f_password,*/ $f_userName, $f_submit));
	}
}
?>