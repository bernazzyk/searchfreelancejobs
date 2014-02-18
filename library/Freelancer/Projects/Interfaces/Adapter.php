<?php

interface Freelancer_Projects_Interfaces_Adapter
{
    public static function fetch();
    
    public function title();
    
    public function budget();
    
    public function posted();
    
    public function ends();
    
    public function platform();
    
    public function categories();
}
