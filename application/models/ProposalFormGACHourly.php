<?php
class Application_Model_ProposalFormGACHourly extends Zend_Form 
{
	public function __construct($options = null) 
    { 
        parent::__construct($options);

        $this->setName('proposalform');
		$this->setAttrib('enctype', 'multipart/form-data');
		
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


		$f_notifylowerbids = new Zend_Form_Element_Checkbox('notifylowerbids');
		$f_notifylowerbids->setLabel('Notify me by e-mail if someone bids lower than me on this project.');
		$f_notifylowerbids->setCheckedValue('on');
		$f_notifylowerbids->setUncheckedValue('off');
		 
		$f_submit = new Zend_Form_Element_Submit('submit');
        $f_submit->setAttrib("class","blue_submit search_btn")
				->setAttrib("value","Apply")->setLabel('Apply');
		

         $this->addElements(array($f_budget,$f_proposal,$f_notifylowerbids,$f_submit));
	}
}
?>