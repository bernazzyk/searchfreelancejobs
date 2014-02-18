<?php

class Application_Model_DbTable_PlatformCategories extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'platform_categories';
    
    public function getCategoryId($platformId, $platformCategoryId)
    {
        $category = $this->fetchRow(array(
            'platform_id = ?' => $platformId,
            'platform_category_id = ?' => $platformCategoryId
        ));
        return null === $category ? null : $category->category_id;
    }
    
}