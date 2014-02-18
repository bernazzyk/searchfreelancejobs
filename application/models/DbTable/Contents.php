<?php

class Application_Model_DbTable_Contents extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'contents';
    
    private static $_instance;
    
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self;
        }
        
        return self::$_instance;
    }
    
    public static function get($id)
    {
        $row = self::getInstance()->find($id)->current();
        return null === $row ? '' : $row->content;
    }
    
    public static function set($id, $content)
    {
        $row = self::getInstance()->find($id)->current();
        if (null !== $row) {
            $row->content = $content;
            $row->save();
        }
        
        return true;
    }
    
}