<?php
class Application_Model_DbTable_Freelancers extends Zend_Db_Table_Abstract
{
     
    protected $_name = 'freelancers';
	
	public function getFeaturedFreelance($array,$searchArray,$limit,$page)
	{
		$select = $this->select()->setIntegrityCheck(false)
				->from($this,$array)
				->join(array('acc'=>'accounts'),'acc.id = '.$this->_name.'.account_id',array('email','name','fname','lname'))
				->join(array('t'=>'transactions'),'t.account_id = '.$this->_name.'.account_id',array('payment_status','added'))
				->joinLeft(array('c'=>'countries'),'c.id = acc.country_id',array('countryName'=>'name'))
				->where('t.payment_status = ?','Y')
				->group($this->_name.'.account_id');
				if(!empty($searchArray) && !empty($searchArray['sortBy']))
				{
					if($searchArray['sortBy']=='name')
					{
						$select = $select->order(array('acc.fname','lname'));
					}
					if($searchArray['sortBy']=='country')
					{
						$select = $select->order(array('countryName'));
					}
				}
				else
				{
					$select = $select->order(array('acc.fname','lname'));
				}
				//print_r($searchArray);
			return  $select;
		$row = $this->fetchAll($select);
		if($row!=NULL)
		{
			$row = $row->toArray();
		}
		else
		{
			$row = NULL;
		}
		//echo "<pre>";	
		//print_r($row);die;
		return $row;
	}
	public function getFreelanceDetails($array,$account_id)
	{
		$select = $this->select()->setIntegrityCheck(false)
				->from($this,$array)
				->join(array('acc'=>'accounts'),'acc.id = '.$this->_name.'.account_id',array('email','name','fname','lname'))
				->join(array('t'=>'transactions'),'t.account_id = '.$this->_name.'.account_id',array('payment_status','added'))
				->where('t.account_id = ?',$account_id);
				echo $select;
		$row = $this->fetchRow($select);
		if($row!=NULL)
		{
			$row = $row->toArray();
		}
		else
		{
			$row = NULL;
		}
		//echo "<pre>";	
		//print_r($row);die;
		return $row;
	}
	
}	