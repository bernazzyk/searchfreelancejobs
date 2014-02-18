<?php

class Application_Model_CreatePhotoForm extends Zend_Form 

{

	public function __construct($options = null) 

    { 

        parent::__construct($options);

        $this->setName('addPhoto');
		$this->setAttrib('enctype', 'multipart/form-data');
		//$this->setAttrib('target', '_top');

		$title = new Zend_Form_Element_Text('title');
	
        $title->setLabel('Project Title')->setRequired(true)->addValidator('NotEmpty', true);

		$f_file = new Zend_Form_Element_File('files', array('isArray' => true));

		$f_file->setLabel('Upload Photo')

				->setAttrib('class', 'upload_photo')
				 ->setRequired(true)
				 ->addValidator('NotEmpty', true)
				 ->addValidator('Extension', true, 'jpg,jpeg,png,gif')
				->addValidator('Size', false, 11024000);

			//	->destination('/data/uploads');

		  

		$f_submit = new Zend_Form_Element_Submit('submit');

        $f_submit->setAttrib("class","blue_submit search_btn sizefont")

				->setAttrib("value","save")->setLabel('Add Project');

         $this->addElements(array(

									$title,
									$f_file,

									$f_submit

									)

							);

	}

}

?>