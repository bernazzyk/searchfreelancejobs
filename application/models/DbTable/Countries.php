<?php

class Application_Model_DbTable_Countries extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'countries';
    
    public function getPairs()
    {
        $select = $this->getAdapter()->select()
            ->from($this->_name, array('id', 'name'))
            ->order('name');
        return $this->getAdapter()->fetchPairs($select);
    }
    
}