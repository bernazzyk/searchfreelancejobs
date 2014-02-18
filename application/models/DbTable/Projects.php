<?php

/**
* This is the DbTable class for the projects table
*/
class Application_Model_DbTable_Projects extends Zend_Db_Table_Abstract
{
    
    protected $_name = 'projects';
    protected $_primary = 'id';
    protected $_rowClass = 'Application_Model_DbTable_Project';
    
    /**
     * statement for bulk insert of imported projects
     * @var Zend_Db_Statement
     */
    private $_projectImportStmt;
    
    /**
     * statement for delete old files of imported projects
     * @var Zend_Db_Statement
     */
    private $_projectImportFilesDeleteStmt;
    
    /**
     * statement for bulk insert new files for imported projects
     * @var Zend_Db_Statement
     */
    private $_projectImportFilesInsertStmt;
    
    /**
     * statement for select project ID
     * @var Zend_Db_Statement
     */
    private $_projectImportSelectStmt;
    
    /**
     * statement for insert new category
     * @var Zend_Db_Statement
     */
    private $_projectImportInsertCategory;
    
    /**
     * get last imported project
     * @param int $platformId optional
     */
    public function getLast($platformId = null)
    {
        $where = '';
        if ($platformId) {
            $where = 'platform_id = ' . (int)$platformId;
        }
        
        return $this->fetchRow($where, 'posted DESC');
    }
    
    /**
     * insert imported projects
     * @param array $projectData
     * @param int $platformId
     * @param array $projectFiles
     * @return boolean
     */
    public function importProject(array $projectData, $platformId, array $projectFiles = array())
    {
        if (null === $this->_projectImportStmt) {
            $this->_projectImportStmt = $this->getAdapter()->prepare('
                INSERT INTO projects
                    (`url`, `external_url`, `external_id`, `external_second_id`, `title`, `description`,
                        `posted`, `ends`, `budget_low`, `budget_high`, `platform_id`, `active`, `jobtype`,
                        `bids`, `bids_avg`, `budget_currency`, `external_user_id`, `added`)
                    VALUES (:url, :externalUrl, :externalId, :externalSecondId, :title, :description,
                        :posted, :ends, :budgetLow, :budgetHigh, :platformId, :active, :jobtype,
                        :bids, :bidsAvg, :budgetCurrency, :externalUserId, NOW())
                    ON DUPLICATE KEY UPDATE
                        url = :url,
                        external_url = :externalUrl,
                        external_second_id = :externalSecondId,
                        title = :title,
                        ends = :ends,
                        budget_low = :budgetLow,
                        budget_high = :budgetHigh,
                        active = :active,
                        jobtype = :jobtype,
                        bids = :bids,
                        bids_avg = :bidsAvg,
                        budget_currency = :budgetCurrency,
                        external_user_id = :externalUserId
            ');
        }
        $this->_projectImportStmt->execute($data = array(
            ':url' => $this->_filterURL($projectData['title']) . '-pl-' . $platformId . '-' . $projectData['external_id'],
            ':externalUrl' => $projectData['external_url'],
            ':externalId' => $projectData['external_id'],
            ':externalSecondId' => isset($projectData['external_second_id']) ? $projectData['external_second_id'] : null,
            ':title' => $projectData['title'],
            ':description' => $projectData['description'],
            ':posted' => $projectData['posted'],
            ':ends' => $projectData['ends'],
            ':budgetLow' => $projectData['budget_low'],
            ':budgetHigh' => $projectData['budget_high'],
            ':platformId' => $platformId,
            ':active' => $projectData['active'],
            ':jobtype' => $projectData['jobtype'],
            ':bids' => $projectData['bids'],
            ':bidsAvg' => $projectData['bids_avg'],
            ':budgetCurrency' => $projectData['budget_currency'],
            ':externalUserId' => $projectData['external_user_id']
        ));
        
        if ($projectData['category_id'] || $projectFiles) {
            if (null === $this->_projectImportSelectStmt) {
                $this->_projectImportSelectStmt = $this->getAdapter()->prepare('
                    SELECT id FROM projects WHERE external_id = :externalId AND platform_id = :platformId
                ');
            }
            $result = $this->_projectImportSelectStmt->execute(array(
                ':externalId' => $projectData['external_id'],
                ':platformId' => $platformId
            ));
            $projectId = (int)$this->_projectImportSelectStmt->fetch(PDO::FETCH_COLUMN);
        }
        
        if ($projectData['category_id']) {
            if (null === $this->_projectImportInsertCategory) {
                $this->_projectImportInsertCategory = $this->getAdapter()->prepare('
                    REPLACE INTO project_categories SET project_id = :projectId, category_id = :categoryId
                ');
            }
            $this->_projectImportInsertCategory->execute(array(
                ':projectId' => $projectId,
                ':categoryId' => $projectData['category_id']
            ));
        }
        
        if ($projectFiles) {
            if (null === $this->_projectImportFilesDeleteStmt) {
                $this->_projectImportFilesDeleteStmt = $this->getAdapter()->prepare('
                    DELETE FROM projects_attachments WHERE project_id = :projectId
                ');
            }
            $this->_projectImportFilesDeleteStmt->execute(array(
                ':projectId' => $projectId
            ));
            
            if (null === $this->_projectImportFilesInsertStmt) {
                $this->_projectImportFilesInsertStmt = $this->getAdapter()->prepare('
                    INSERT INTO projects_attachments (project_id, file_name, file_url)
                        VALUES (:projectId, :fileName, :fileUrl)
                ');
            }
            foreach ($projectFiles as $file) {
                $this->_projectImportFilesInsertStmt->execute(array(
                    ':projectId' => $projectId,
                    ':fileName' => (isset($file['file_name']))? $file['file_name'] : '',
                    ':fileUrl' => (isset($file['file_url']))? $file['file_url'] : ''
                ));
            }
        }
        
        return true;
    }
    
    public function clearOld()
    {
        $query = "UPDATE projects SET hidden = 1 WHERE NOW() > IF(ends = '0000-00-00 00:00:00', posted + INTERVAL 2 WEEK, ends)";
        $this->getAdapter()->query($query);
        
        return true;
    }
    
    /**
     * filter string to be able to use it in URL
     * string $url
     */
    private function _filterURL($url)
    {
        $url = strtolower(trim(strip_tags($url)));
    
        $url = str_replace ("ă", "a", $url);
        $url = str_replace ("î", "i", $url);
        $url = str_replace ("ș", "s", $url);
        $url = str_replace ("ş", "s", $url);
        $url = str_replace ("ţ", "t", $url);
        $url = str_replace ("ț", "t", $url);
    
        $url = str_replace ("â", "a", $url);
    
        //replace single quotes and double quotes first
        $url = preg_replace('/[\']/i', '', $url);
        $url = preg_replace('/["]/i', '', $url);
    
        $url = preg_replace('/&/', 'and', $url);
    
        //remove non-valid characters
        $url = preg_replace('/[^-a-z0-9]/i', '-', $url);
        $url = preg_replace('/-[-]*/i', '-', $url);
    
        //remove from beginning and end
        $url = preg_replace('/' . '-' . '$/i', '', $url);
        $url = preg_replace('/^' . '-' . '/i', '', $url);
    
        if ($url != '') {
            // Romanian replacements
        }
    
        return $url;
    }
	public function getUserSubscribeProject($array,$account_id,$limit)
    {
		$select = $this->select()->setIntegrityCheck(false)
				->from($this,$array)
				->join(array('pc'=>'project_categories'),'pc.project_id = '.$this->_name.'.id',array('category_id'))
				->join(array('fc'=>'freelancers_categories'),'fc.category_id = pc.category_id',array('account_id'))
				->where('fc.account_id = ?',$account_id)
				->limit($limit)
				->order('posted DESC');
				//echo $select;die;
		$row = $this->fetchAll($select);
		if($row!=NULL)
		{
			$row = $row->toArray();
		}
		else
		{
			$row = NULL;
		}	
		return $row;
	}
	public function getLatestProjectFromEachPlatforms()
    {
		$select = $this->select()->setIntegrityCheck(false)
				->from($this,array(new Zend_Db_Expr('max(id) as maxId')))
				->group('platform_id')
				->order('posted DESC');		
		$row = $this->fetchAll($select);
		if($row!=NULL)
		{
			$row = $row->toArray();
		}
		else
		{
			$row = NULL;
		}	
		return $row;
	}
}