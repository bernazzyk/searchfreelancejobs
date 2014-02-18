<?php
class Application_Model_GACMessageForm extends Zend_Form 
{
	public function __construct($options = null) 
    { 
        parent::__construct($options);

        $this->setName('messageform');
		$this->setAttrib('enctype', 'multipart/form-data');
		
		
        $f_proposal = new Zend_Form_Element_Textarea('message');
        $f_proposal->setLabel('Your can send your private meassege to user who added this project')
					->setAttrib('cols', '40')
					->setAttrib('rows', '4') 
                 ->setRequired(true)
                 ->addValidator('NotEmpty');
		$f_proposal->getDecorator('Label')->setOption('escape',false);
		 
		$f_submit = new Zend_Form_Element_Submit('gac_mess_submit');
        $f_submit->setAttrib("class","blue_submit search_btn")
				->setAttrib("value","Send")->setLabel('Send');
		

         $this->addElements(array($f_proposal,$f_submit));
	}
}
?>