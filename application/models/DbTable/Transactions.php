<?php

class Application_Model_DbTable_Transactions extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'transactions';
    protected $_rowClass = 'Application_Model_DbTable_Transaction';
	
	public function checkIsFeatured($account_id)
	{
		$select = $this->select()
					->from($this,array('id'))
					->where('account_id = ?',$account_id)
					->where('payment_status = ?','Y');
					
		$row = $this->fetchRow($select);
		if($row!=NULL)
		{
			$row = $row->toArray();
		}
		else
		{
			$row = array();
		}
		return count($row);
	}
	public function checkExpiry()
	{
		$data = array(
			'payment_status' => 'N'
		);
		$where = array(
    					'expiry_date < ?' => date('Y-m-d H:i:s')
						
						);
			return $this->update($data, $where);
	}
    
}