<?php

class Application_Model_CreateProfileForm extends Zend_Form 

{

	public function __construct($options = null) 

    { 

        parent::__construct($options);



        $this->setName('proposalform');

		$this->setAttrib('enctype', 'multipart/form-data');



		$f_fname = new Zend_Form_Element_Text('fname');

        $f_fname->setLabel('First Name');

		

		$f_lname = new Zend_Form_Element_Text('lname');

        $f_lname->setLabel('Last Name');

		

		$f_company = new Zend_Form_Element_Text('company');

        $f_company->setLabel('Company');

		

		$f_state = new Zend_Form_Element_Text('state');

        $f_state->setLabel('State');

		

		$f_postcode = new Zend_Form_Element_Text('post_code');

        $f_postcode->setLabel('Post code');

		

		$f_street = new Zend_Form_Element_Text('street');

        $f_street->setLabel('Street');

		

		$f_phone = new Zend_Form_Element_Text('phone');

		$f_phone->setLabel('Phone');

		

        $f_description = new Zend_Form_Element_Textarea('description');

        $f_description->setLabel('Description')

					->setAttrib('cols', '40')

					->setAttrib('rows', '4');

     

	    $f_experience = new Zend_Form_Element_Textarea('experience');

        $f_experience->setLabel('Work Experience')

					->setAttrib('cols', '40')

					->setAttrib('rows', '4');

					

		$f_portfolio = new Zend_Form_Element_Textarea('portfolio');

        $f_portfolio->setLabel('Portfolio')

					->setAttrib('cols', '40')

					->setAttrib('rows', '4');

	

				 

		 //$file = new App_Form_Element_File('file');



		$f_file = new Zend_Form_Element_File('files', array('isArray' => true));

		$f_file->setLabel('Profile picture')

				->setAttrib('class', 'proposal_file')

				->addValidator('Size', false, 11024000);

			//	->destination('/data/uploads');

		 $f_oldfile = new Zend_Form_Element_Hidden('oldfile');
		 
		 $f_skills = new Zend_Form_Element_Textarea('skills');
         $f_skills->setLabel('Skills')
					->setAttrib('cols', '40')
					->setAttrib('rows', '4');
					
		$f_industry = new Zend_Form_Element_Text('industry');	
        $f_industry->setLabel('Industry');
						
		$f_education = new Zend_Form_Element_Textarea('education');
        $f_education->setLabel('Education')
					->setAttrib('cols', '40')
					->setAttrib('rows', '4');			
					
	/*	$f_button = new Zend_Form_Element_Button('button');
        $f_button->setAttrib("class","grey_submit search_btn")
				 ->setAttrib("onClick","javascript:window.location.href='/profile/portfolio/'")		
				->setAttrib("value","")->setLabel('Projects');			

		$f_newproject = new Zend_Form_Element_Button('newproject');
        $f_newproject->setAttrib("class","blue_submit search_btn btn2")
				 ->setAttrib("onClick","javascript:window.location.href='/profile/uploadphoto/'")		
				->setAttrib("value","")->setLabel('Add Project');			



		$f_submit = new Zend_Form_Element_Submit('submit');

        $f_submit->setAttrib("class","blue_submit search_btn")

				->setAttrib("value","Create")->setLabel('Update');

		
  	*/
  
         $this->addElements(array(

									$f_fname,

									$f_lname,

									$f_company,

									$f_state,

									$f_postcode,

									$f_street,

									$f_phone,

									$f_description,

									$f_experience,

									$f_portfolio,

									$f_file,
									
									$f_oldfile,
									$f_skills,
									$f_industry,
									$f_education
									//$f_button,
									//$f_newproject,
									//$f_submit

									)

							);

	}

}

?>