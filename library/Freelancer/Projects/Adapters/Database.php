<?php
class Freelancer_Projects_Adapters_Database extends Freelancer_Projects implements Freelancer_Projects_Interfaces_Apapter
{
    private $title;
    
    private $budget;
    
    private $posted;
    
    private $ends;
    
    private $platform;
    
    private $categories;
    
    public function __construct( $data )
    {
        $this->title 		= $data['title'];
        
        $this->budget 		= $data['budget'];
        
        $this->posted 		= $data['posted'];
        
        $this->ends 		= $data['ends'];
        
        $this->platform 	= $data['platform'];

        $this->categories 	= explode( ',', $data['categories'] );
    }
        
    public static function fetch( array $restrictions )
    {
        // Fetch all rows from database
        // Create individual arrays of $this->__construct( $data );
        
        $db = Zend_Registry::get( 'db' );

        $select = $db->select()->from( 'projects' );
        
        foreach( $restrictions as $k => $v )
        {
            if 
            ( 
                'budget' == $k 
                    
                && ( isset( $v['low'] ) && is_float( $v['low'] ) )
                    
                && ( isset( $v['high'] ) && is_float( $v['high'] ) )
            )
            {
                $select->where( 'budget > ?', $v['low'] );
                
                $select->where( 'budget < ?', $v['high'] );
            }
            
            if
            (
                'datePosted' == $k
                
                && ( isset( $v['low'] ) && (int) $v['low'] > 0 )
                
                && ( isset( $v['high'] ) && (int) $v['high'] > 0 )
            )
            {
                $select->where( 'posted > ?', $v['low'] );
                
                $select->where( 'posted < ? ' , $v['high'] );
            }
            
            
            if ( 'order' == $k )
            {
                $select->order( $v );
            }
            
            if 
            ( 
                'categories' == $k 
                    
                && is_array( $v )
            )
            {
                foreach( $v as $category )
                {
                    // Some sort of join here to a categories > projects relation table filtered by supplied category id's.
                    
                    // $select->leftJoin();
                }
            }            
        }
        
        // Add pagination here....
        
        $rows = $db->fetchAll( $select );
        
        $out = array();
        
        foreach( $rows as $row )
        {
            $out[] = new Freelancer_Projects_Adapter_Database( $row );
        }
        
        return $out;
    }
    
    public function title()
    {
        return $this->title;
    }
    
    public function budget()
    {
        return $this->budget;
    }
    
    public function posted()
    {
        return $this->posted;
    }
    
    public function ends()
    {
        return $this->ends;
    }
    
    public function platform()
    {
        return $this->platform;
    }
    
    public function categories()
    {
        return $ths->categories;
    }
}