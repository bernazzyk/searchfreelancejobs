<?php

class Application_Model_PaymentpagesettingForm extends Zend_Form
{
    
    public function init()
    {
      	
       
    }
	public function paymentPageSettingFrm()
	{
		$this->setMethod('POST')
            ->setAttrib('id', 'payment_setting');
        $this->addElement('checkbox', 'active', array(
            'label' => 'Payment page display option',
       		'onclick'=>'this.form.submit();'
			 ));
	}
	public function platformsHeaderSettingFrm()
	{
		$this->setMethod('POST')
            ->setAttrib('id', 'platforms_header_setting');
        $this->addElement('checkbox', 'active', array(
            'label' => 'Header for all platforms display option',
       		'onclick'=>'this.form.submit();'
			 ));
	}
    
}
