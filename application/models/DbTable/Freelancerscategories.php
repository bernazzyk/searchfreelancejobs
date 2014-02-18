<?php
class Application_Model_DbTable_Freelancerscategories extends Zend_Db_Table_Abstract
{
     
    protected $_name = 'freelancers_categories';
	public function saveSubscribeCategory($data,$account_id)
	{
			if(empty($data) || $account_id=='')
			{
				return false;
			}
			$this->delete('account_id = '.$account_id);
			foreach($data as $category_id)
			{
			$catData = array(
					'account_id'=>$account_id,
					'category_id'=>$category_id
			
				);
				$this->insert($catData);
			}
			return true;
	}
	public function remove($account_id)
	{
		$this->delete('account_id = '.$account_id);
		return true;
	}
	public function getUserSubscribeCategory($account_id)
	{
		$select = $this->select()
				->from($this,array('*'))
				->where('account_id = ?',$account_id);
		$row = $this->fetchAll($select);
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
	public function countUserSubscribeCategory($account_id)
	{
		$select = $this->select()->setIntegrityCheck(false)
				->from($this,array('id'))
				->where('account_id = ?',$account_id);
		$row = $this->fetchAll($select);
		if($row!=NULL)
		{
			$row = count($row->toArray());
		}
		else
		{
			$row = NULL;
		}	
		return $row;
	}
	public function getAll()
	{
		$select = $this->select()->setIntegrityCheck(false)
				->from($this,array('*'))
				->join(array('acc'=>'accounts'),'acc.id = '.$this->_name.'.account_id',array('email'))
				//->where('account_id = ?',$account_id);
				->group('account_id');
		$row = $this->fetchAll($select);
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