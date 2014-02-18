<?php

/**
* This is the DbTable class for the platforms table
*/
class Application_Model_DbTable_Platforms extends Zend_Db_Table_Abstract
{
    
    const ODESK_ID = 4;
    const FREELANCER_ID = 1;
    const ELANCE_ID = 3;
    const GURU_ID = 5;
    const IFREELANCE_ID = 9;
    const GETACODER_ID = 6;
    const FREELANCE_ID = 10;
    const FREELANCESWITCH_ID = 7;
    const PEOPLEPERHOUR_ID = 8;
    const DESIGNCROWD_ID = 11;
    const NNDESIGN_ID = 12;
    const CROWDSPRING_ID = 13;
    const FLEXJOBS_ID = 14;
    const MONSTER_ID = 15;
	const COROFLOT_ID = 16;
	const BEHANCE_ID = 17;
	const DICE_ID = 18;
	const KROP_ID = 19;
	const CRAIGSLIST_ID = 20;
	const SIMPLYHIRED_ID = 21;
	
    protected $_name = 'platforms';
    protected $_primary = 'id';
    
    public function getAssoc($curlOnly = false)
    {
        $select = $this->getAdapter()->select()
            ->from($this->_name)
            ->order('name');
        if ($curlOnly) {
            $select->where('is_curl = 1');
        }
        return $this->getAdapter()->fetchAssoc($select);
    }
    
}