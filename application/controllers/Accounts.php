<?php

class Application_Model_DbTable_Accounts extends Zend_Db_Table_Abstract
{
    
    const KEY = '3a2a5b676d386b6a7d6d2864247a704a36515d2721d68ac72b697b7524497423';
    
    protected $_name = 'accounts';
    protected $_rowClass = 'Application_Model_DbTable_Account';
    
    public function encode($data)
    {
        $key = pack('H*', self::KEY);
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv);
        $ciphertext = $iv . $ciphertext;
        return base64_encode($ciphertext);
    }
    
    public function decode($data)
    {
        if (!$data) {
            return null;
        }
        $data = base64_decode($data);
        $key = pack('H*', self::KEY);
        $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
        $ivDec = substr($data, 0, $ivSize);
        $data = substr($data, $ivSize);
        return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $ivDec);
    }
    
    public function getNext()
    {
        $where = array(
            'agreed = 1',
            'subscription_id IS NOT NULL',
            'subscription_check < NOW() - INTERVAL 1 WEEK'
        );
        return $this->fetchRow($where, 'subscription_check DESC');
    }
    
    public function getNotBilled()
    {
        $where = array(
            "agreed = 0 OR (agreed = 1 AND paytype = 'paypal' AND paypal_subscription_id IS NULL)",
            'added < NOW() - INTERVAL 1 DAY',
            'free_trial = 0'
        );
        return $this->fetchRow($where, 'added');
    }
	public function getAccountsIdByEmail($email)
	{
		$select = $this->select()
				->from($this,array('id'))
				->where('email = ?',$email);
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