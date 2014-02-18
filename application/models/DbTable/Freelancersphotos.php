<?php
class Application_Model_DbTable_Freelancersphotos extends Zend_Db_Table_Abstract
{
     
    protected $_name = 'freelancers_photos';
	public function save($data,$id)
	{
		if($id!=NULL)
		{
			$this->update($data,'photos_id = '.$id);
			return true;
		}
		else
		{
			$this->insert($data);
			return true;
		}
	}
	public function getUserPhotos($account_id,$limit)
	{
		$select = $this->select()
				->from($this,array('*'))
				->where('account_id = ?',$account_id)
				->limit($limit);
		$row = $this->fetchAll($select);
		if($row!=NULL)
		{
			$row = $row->toArray();
			$count = 0;
			foreach($row as $rows)
			{
				$row[$count]['title'] = stripslashes($rows['title']);

			$count++;
			}
		}
		else
		{
			$row = NULL;
		}	
				//print_r($row);die;
		return $row;
	}
	public function countUserPhotos($account_id)
	{
		$select = $this->select()->setIntegrityCheck(false)
				->from($this,array('photos_id'))
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
	public function remove($photoId,$accountId)
	{
		$this->delete('photos_id = '.$photoId.' AND account_id = '.$accountId);
		return true;
	}
	public function getPhotoDetails($array,$photoId)
	{
		$select = $this->select()
				->from($this,$array)
				->where('photos_id = ?',$photoId);
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