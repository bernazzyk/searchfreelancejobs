<?php
class Application_Model_DbTable_Paymentpagesetting extends Zend_Db_Table_Abstract
{
     
    protected $_name = 'paymentpage_setting';
	public function save($data,$id)
	{
		if($id!=NULL)
		{
			$this->update($data,'id = '.$id);
			return true;
		}
		else
		{
			$this->insert($data);
			return true;
		}
	}
	public function getDetails($array,$id)
	{
		$select = $this->select()
				->from($this,$array)
				->where($this->_name.'.id = ?',$id);
		$row = $this->fetchRow($select);
		if($row!=NULL)
		{
			$row = $row->toArray();
		}
		else
		{
			$row = NULL;
		}	
		//print_r($row);die;
		return $row;
	}
}	