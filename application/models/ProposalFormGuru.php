<?php
class Application_Model_ProposalFormGuru extends Zend_Form 
{
	public function __construct($options = null) 
    { 
        parent::__construct($options);
		
        $this->setName('proposalform');
		$this->setAttrib('enctype', 'multipart/form-data');
		
		$f_profile = new Zend_Form_Element_Select('profile');
        $f_profile->setLabel('Select profile from actives')
             // ->setMultiOptions(array('mr'=>'Mr', 'mrs'=>'Mrs'))
              ->setMultiOptions($options)
              ->setRequired(true)->addValidator('NotEmpty', true);
		
        $f_budget = new Zend_Form_Element_Text('budget');
        $f_budget->setLabel('Proposed budget <span class="star">*</span>')
                  ->setRequired(true)
                  ->addValidator('NotEmpty');
		$f_budget->getDecorator('Label')->setOption('escape',false);
		
        $f_proposal = new Zend_Form_Element_Textarea('proposal');
        $f_proposal->setLabel('Proposal <span class="star">*</span>')
					->setAttrib('cols', '40')
					->setAttrib('rows', '4')
                 ->setRequired(true)
                 ->addValidator('NotEmpty');
		$f_proposal->getDecorator('Label')->setOption('escape',false);
				 
		 //$file = new App_Form_Element_File('file');
		 
		

		$f_file = new Zend_Form_Element_File('files', array('isArray' => true));
		$f_file->setLabel('Add Attachment')
				->setAttrib('class', 'proposal_file')
				->addValidator('Size', false, 11024000)
				->setMultiFile(3);
			//	->destination('/data/uploads');
		   

		$f_submit = new Zend_Form_Element_Submit('submit');
        $f_submit->setAttrib("class","blue_submit search_btn")
				->setAttrib("value","Apply")->setLabel('Apply');
		

         $this->addElements(array($f_profile, $f_budget, $f_proposal,$f_file,$f_submit));
	}
}
?>