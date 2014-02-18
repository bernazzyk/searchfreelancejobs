<?php

class Application_Model_DbTable_Project extends Zend_Db_Table_Row_Abstract
{
    
    /**
     * platform's bid URL template can be defined here
     * @var string
     */
    private $_bidUrlTemplate;
    
    /**
     * get URL for bidding the project
     * @param string $bidUrl optional template of the platform's bid URL
     * @return string
     */
    public function getBidUrl($bidUrl = null)
    {
        if (!$bidUrl) {
            $bidUrl = $this->getBidUrlTemplate();
        }
        $values = $this->toArray();
        $keys = array_keys($values);
        foreach ($keys as &$key) {
            $key = '%' . $key . '%';
        }
        return str_ireplace($keys, $values, $bidUrl);
    }
    
    /**
     * set platform's bid URL template
     * @param string $bidUrlTemplate
     * @return boolean
     */
    public function setBidUrlTemplate($bidUrlTemplate)
    {
        $this->_bidUrlTemplate = $bidUrlTemplate;
        return true;
    }
    
    /**
     * set platform's bid URL template
     * @return string
     */
    public function getBidUrlTemplate()
    {
        return $this->_bidUrlTemplate;
    }
    
}