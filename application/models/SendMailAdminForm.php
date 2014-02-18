<?php
class Application_Model_SendMailAdminForm extends Zend_Form 
{
	public function init()
    {
      	
       
    }
	public function sendMailFrm()
	{
		$this->setMethod('POST')
             ->setAttrib('id', 'send_mail')
			  ->setAttrib('autocomplete', 'off');
			 
		$subject = new Zend_Form_Element_Text('subject');

        $subject->setLabel('Title')
				->setRequired(true)
                 ->addValidator('NotEmpty');
		
		
		$body = new Zend_Form_Element_Textarea('body');
		
        $body->setLabel('Body')

					->setAttrib('cols', '40')

					->setAttrib('rows', '6')
					->setRequired(true)
                     ->addValidator('NotEmpty');
		
	
					
					
	 	$f_submit = new Zend_Form_Element_Submit('submit');

        $f_submit->setAttrib("class","btn btn-primary")

				->setAttrib("value","Create")->setLabel('Send Mail');

		 $this->addElements(array(

									$subject,
									$body,
									$f_submit
									)
				
							);
			        
	}
	
}	
?>