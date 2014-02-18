<?php

class Application_Model_DbTable_Bids extends Zend_Db_Table_Abstract
{
    
    const BIDS_FREE_LIMIT = 10;
    
    protected $_name = 'bids';
    
}