<?php

class Freelancer_Projects
{
    private $enabledAdapters = array();
    
    public function __construct()
    {
        // Some sort of database query or config here & accompanying verification of adapter existence...
        $this->enabledAdapters = array( 'Freelancer_Projects_Adapters_Database' );
    }
    
	public function fetch( )
	{
	    $all = array();
	    
	    foreach( $this->enabledAdapters as $adapter )
	    {
	        if ( $adapter instanceof Freelancer_Projects_Interfaces_Adapter )
	        {
	            $all[ $adapter ] = $adapter::fetch();
	        }
	    }
	    
	    return $all;
	}
}