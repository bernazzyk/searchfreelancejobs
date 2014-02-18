<?php

class Application_Model_Freelancer
{
    protected $db;

    public function __construct()
    {
        $this->db = Zend_Registry::get( 'db' );
    }
	
}
