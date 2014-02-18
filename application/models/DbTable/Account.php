<?php

class Application_Model_DbTable_Account extends Zend_Db_Table_Row_Abstract
{

    const TRIAL_TIME = 7;
    
    public function suspend()
    {
        $this->agreed = 0;
        $this->suspended_at = new Zend_Db_Expr('NOW()');
        $this->save();
        
        $mail = new Zend_Mail();
        $mail->setFrom('admin@searchfreelancejobs.com', 'SearchFreelanceJobs');
        $mail->setSubject('Account suspended due to non payment');
        $mail->setBodyText(<<<TEXT
Hello,

Unfortunately, your account has been suspended due to a payment error in your account. To lift the suspension and continue using our service, please login to your account and update your billing information.

Thank you,
The Freelancer.fm Team
TEXT
        );
        $mail->addTo($this->email);
        $mail->send();
    }
    
    public function isPaid()
    {
        if ('admin' == $this->type) {
            return true;
        }
        
        if ($this->agreed && $this->paytype == 'paypal' && !$this->paypal_subscription_id) {
            return false;
        }
        
        if ($this->agreed && $this->paytype == 'paypal' && $this->paypal_subscription_id) {
            return strtotime($this->agreed_at) + 86400 * (self::TRIAL_TIME + 1) > time();
        }
        
        if ($this->agreed && $this->subscription_id) {
            return true;
        }
        
        if (!$this->agreed) {
            return false;
        }
        return strtotime('+ 1 month', strtotime($this->paid_date) + 86400) > time();
    }
    
    public function isTrial()
    {
        return $this->free_trial && strtotime($this->free_trial_started) + 86400 * self::TRIAL_TIME > time();
    }
    
    public function sendTrialMail()
    {
        $mail = new Zend_Mail();
		$mail->setFrom('admin@searchfreelancejobs.com', 'SearchFreelanceJobs');        
        $mail->setSubject('No Billing? No Problem! Your Free Trial Started Today.');
        $mail->setBodyText(<<<TEXT
Hi there!

We've noticed you haven't completed the final step in the sign up process. We're so confident you'll love using Freelancer.fm that we granted access to your account to start bidding on jobs today! Your billing information will ONLY be required after one week if you decide to continue using our platform. 

Simply login to http://searchfreelancejobs.com with the email/password you used in the first step of the sign-up process to start searching for the perfect job today!

Happy Bidding,
The SearchFreelanceJobs Team
TEXT
        );
        $mail->addTo($this->email);
        $mail->send();
    }
    
    public function getConnections()
    {
        $model = $this->getTable();
        
        $connects = json_decode(trim($model->decode($this->connects)), true);
        if (!$connects) {
            $connects = array();
        }
        
        return array_keys($connects);
    }
    
    public function getConnection($platformId)
    {
        $model = $this->getTable();
        
        $connects = json_decode(trim($model->decode($this->connects)), true);
        if (!$connects) {
            $connects = array();
        }
        
        return array_key_exists($platformId, $connects) ? $connects[$platformId] : null;
    }
    
    public function setConnection($platformId, $username, $password)
    {
        $model = $this->getTable();
        
        $connects = array();
        if ($this->connects) {
            $connects = json_decode(trim($model->decode($this->connects)), true);
        }
        
        $connects[$platformId] = array(
            'username' => $username,
            'password' => $password
        );
        $this->connects = $model->encode(json_encode($connects));
        $this->save();
    }
    
    public function setCC($cc)
    {
        $model = $this->getTable();
        $this->cc = $model->encode(json_encode($cc));
        $this->save();
    }
    
    public function getCC()
    {
        $model = $this->getTable();
        $cc = json_decode(trim($model->decode($this->cc)), true);
        if (!$cc) {
            $connects = null;
        }
        return $cc;
    }
    
    public function removeConnection($platformId)
    {
        $model = $this->getTable();
        
        $connects = array();
        if ($this->connects) {
            $connects = json_decode(trim($model->decode($this->connects)), true);
        }
        
        if (array_key_exists($platformId, $connects)) {
            unset($connects[$platformId]);
        }
        
        $this->connects = $model->encode(json_encode($connects));
        $this->save();
    }
    
    public function getBids()
    {
        $model = new Application_Model_DbTable_Bids();
        return $model->fetchAll('account_id = ' . (int)$this->id);
    }
    
    public function getBidsCount()
    {
        $adapter = $this->getTable()->getAdapter();
        $select = $adapter->select()
            ->from('bids', 'COUNT(*)')
            ->where('account_id = ?', $this->id);
        return $adapter->fetchOne($select);
    }
    
    public function bid($projectId)
    {
        $adapter = $this->getTable()->getAdapter();
        
        $stmt = $adapter->prepare('INSERT IGNORE INTO bids SET account_id = :accountId, project_id = :projectId, added = NOW()');
        $stmt->execute(array(
            ':accountId' => $this->id,
            ':projectId' => $projectId
        ));
        
        return true;
    }
    
}