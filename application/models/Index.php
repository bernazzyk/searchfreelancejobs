<?php
class Application_Model_Index extends Application_Model_Freelancer
{
    public function test()
    {
        return $this->db->fetchAll( $this->db->select()->from( 'accounts' ) );
    }
	
	public function projects($categories_array, $tag,$price_limits_hourly_array,$price_limits_fixed_array,$time_left,$posted_date_days,$platforms_array,$job_type, $search,$limit='', $only_count,$projectId=NULL,$projectIdArray=NULL)
    {
		$sql_category_add = '';
		$poject_categories_table = '';
		$poject_tags_table = '';
		$sql_price_add = '';
		$sql_time_add = '';
		$sql_posted_date_add = '';
		$sql_platform_add = '';
		$sql_job_type_add = '';
		$sql_search_add = '';
		$sql_tag_add = '';
	
		
	
		if(count($categories_array)>0 && !in_array('0', $categories_array))
		{
			$poject_categories_table = ', project_categories as pc ';
			$sql_category_add .= 'AND (pr.id = pc.project_id) AND ( ';
			foreach($categories_array as $category_id)
			{
				$sql_category_add .= "pc.category_id = {$category_id} OR ";
			}
			$sql_category_add = rtrim($sql_category_add, 'OR ');
			$sql_category_add .= ' ) ';
		}
		
		if(count($price_limits_hourly_array)==2)
		{
			$price_min = $price_limits_hourly_array[0];
			$price_max = $price_limits_hourly_array[1];
			$projectHighCond = '';
			if ($price_max < 100) {
			    $projectHighCond = " AND (pr.budget_high <= {$price_max})";
			}
			$sql_price_add .= "AND ((pr.jobtype =1 AND (pr.budget_low >= {$price_min}) {$projectHighCond}) )";
		}
	
		
		if(count($price_limits_fixed_array)==2)
		{
			$price_min = $price_limits_fixed_array[0];
			$price_max = $price_limits_fixed_array[1];
			$projectHighCond = '';
			if ($price_max < 10000) {
			    $projectHighCond = " AND (pr.budget_high <= {$price_max})";
			}
			$sql_price_fixed = "(pr.jobtype >= 2 AND (pr.budget_low >= {$price_min}) {$projectHighCond})";
			
			if($sql_price_add!='')
			{
				$sql_price_add = rtrim($sql_price_add,')');
				$sql_price_add .= ' OR ' . $sql_price_fixed . ' )';
			} else {
				$sql_price_add .= 'AND ' . $sql_price_fixed;
			}
			
			//$sql_price_add .= "AND ((pr.jobtype =1 AND (pr.budget_low >= {$price_min}) AND (pr.budget_high <= {$price_max})))";
		}
		
		if($tag != '')
		{	
			//if(is_numeric($tag))
			//{
				$poject_tags_table = ', projects_tags as ptags, tags ';
				
				$sql_tag_add .= " AND (pr.id = ptags.project_id) AND (ptags.tag_id = tags.id) AND(tags.name='{$tag}') ";
			//}
			
		}
		
		//print $sql_price_add; die('here');
		
		if($time_left != '')
		{	
			$sql_time_add .= " AND (DATEDIFF(`ends`, NOW()) between 0 and {$time_left})";
		}
		
		if($posted_date_days != '')
		{	
			if(is_numeric($posted_date_days))
			{
				$sql_posted_date_add .= " AND (DATEDIFF(NOW(),`posted`) between 0 and {$posted_date_days})";
			}
			else {
				$posted_date = explode("x", $posted_date_days);
				
			//	print_r($posted_date);
				
			//	print $posted_date[0];
				
				if(isset($posted_date[0]))
				{
					$posted_date[0] = str_replace('v', '-', $posted_date[0]);
					if($this->IsValidDate($posted_date[0]))
					{
						//$sql_posted_date_add ='';
						$sql_posted_date_add .= " AND (date(pr.posted) >= date('$posted_date[0]')) ";
						//print $sql_posted_date_add; die('here');
					}
				}
				if(isset($posted_date[1]))
				{
					$posted_date[1] = str_replace('v', '-', $posted_date[1]);
					if($this->IsValidDate($posted_date[1]))
					{
						$sql_posted_date_add .= " AND (date(pr.posted) <= date('$posted_date[1]')) ";
					}
				}
			}
			//2012+11+20
		}
		
		if(count($platforms_array)>0)
		{
			$sql_platform_add .= ' AND(';
			foreach($platforms_array as $platform_id)
			{
				$sql_platform_add .= " (pr.platform_id = {$platform_id}) OR ";
			}
			$sql_platform_add = rtrim($sql_platform_add, 'OR ');
			$sql_platform_add .= ' ) ';
		}
		if($job_type!='')
		{
			$sql_job_type_add .= " AND (pr.jobtype = $job_type)";
		}

		if(!(empty($search)))
		{
			
			$search = stripslashes($search);
			$search = htmlspecialchars($search);
			$search = trim($search);
			
			$search = strtolower($search);
			
			$terms = preg_split('/[\+]|[\/ \/]|[\-]/', $search, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			foreach($terms as $id => $term) {
				$terms[$id] = htmlspecialchars(addslashes($terms[$id]), ENT_QUOTES, 'utf-8');
			}
			
		
			
			if (count($terms) > 0) {
				$TmpAddon = '';

				foreach ($terms as $id => $term) {
					if ($id > 0) $TmpAddon .= " OR";
					$TmpAddon .= " pr.title LIKE '%{$term}%' OR pr.description LIKE '%{$term}%'";
				}
				$sql_search_add .= " AND ({$TmpAddon})";
			}
				//print $sql_search_add; die('aiciiiii');
		}
		
		if($only_count)
		{
			$sql_stmt_nr_of_records = "SELECT COUNT(*) as nr_of_projects FROM projects as pr{$poject_categories_table} {$poject_tags_table} WHERE hidden = 0 {$sql_category_add} {$sql_tag_add} {$sql_price_add} {$sql_time_add} {$sql_posted_date_add} {$sql_platform_add} {$sql_job_type_add} {$sql_search_add}";
			//print $sql_stmt_nr_of_records; die('---');
			
			return $this->db->fetchOne( $sql_stmt_nr_of_records );
        } else {
            $AllProjects = array();
			 if($projectId!=NULL)
			 {
			 	 $order = "ORDER BY pr.id = {$projectId} DESC, pr.id,pr.posted DESC";
			 }
			 else 
			 {
			 	if(!empty($projectIdArray))
				{
				$ids = $projectIdArray;
				$list = implode(',', $ids);
				$order = 'ORDER BY';
				foreach ($ids as $item) {
				$order .= ' pr.id = ' . $item . ' DESC,';
				}
				$order = trim($order, ',');
				$order .= ', pr.posted DESC, pr.id DESC';
				}
				
				else
				{
			 		$order = 'ORDER BY pr.posted DESC, pr.id DESC';
				}
				
			 }
            $sql_stmt = "SELECT pr.*, DATEDIFF(NOW(), pr.posted) as date_diff_posted, DATEDIFF(pr.`ends`,NOW()) as date_diff_ends FROM projects as pr {$poject_categories_table} {$poject_tags_table} WHERE hidden = 0 {$sql_tag_add} {$sql_price_add} {$sql_category_add} {$sql_time_add} {$sql_posted_date_add} {$sql_platform_add} {$sql_job_type_add} {$sql_search_add} GROUP BY pr.id  {$order} ".$limit;
			
			//echo $sql_stmt;die;
            $platformsSelect = $this->db->select()
                ->from('platforms');
            $platforms = $this->db->fetchAssoc($platformsSelect);
            
            $AllProjects = $this->db->fetchAll($sql_stmt);
            
            $modelTimeOutput = new Application_Model_TimeOutput();
            foreach ($AllProjects as &$project) {
                $project['logo'] = $platforms[$project['platform_id']]['logo'];
                $project['platform_name'] = $platforms[$project['platform_id']]['name'];
                
                $datePosted = $modelTimeOutput->elapsed_time(strtotime($project['posted']), 2);
                $project['date_posted'] = $datePosted;
                
                if($project['ends'] != '0000-00-00 00:00:00') {
                    $timeLeft = $modelTimeOutput->time_left(strtotime($project['ends']), 2);
                } else {
                    $timeLeft = 'N/A';
                }
                $project['time_left'] = $timeLeft;
            }
            
            return $AllProjects;
        }
    }
	
	public function insertTags($Projects) {
		
		foreach($Projects as $Value)
		{
			$pid = $Value['id'];
			srand(mktime());
			$tag_id = (rand()%3) + 1; 

			$sql_stmt_nr_of_records = "INSERT INTO projects_tags(project_id,tag_id) VALUES($pid, $tag_id)";
			$this->db->query( $sql_stmt_nr_of_records );
			//print $sql_stmt_nr_of_records; die('---');
		}

	}
	
	public function IsValidDate($string_date) 
	{
		//$string_date trebuie sa fie in format european d-m-y
		$date_array = array();
		$date_array = explode("-", $string_date);
		
		//Array ( [0] => 2012 [1] => 11 [2] => 20 )
		
		$year = (int)$date_array[0];
		$month = (int)$date_array[1];
		$day = (int)$date_array[2];
		
		//print $year;
		
		//print_r($date_array);
		
		/*$tok = strtok($string_date, '-');
			
		while ($tok !== false) {
			$date_array[] = (int) $tok;
			$tok = strtok('-');
		}*/
		
		//print $date_array[1] .'-'. $date_array[2] .'-'. $date_array[0];
		
		return checkdate( $month , $day , $year );
	}
	
	public function NrOfProjects($categories_array, $price_limits_hourly_array, $time_left, $posted_date_days)
	{
		$sql_category_add = '';
		$poject_categories_table = '';
		$sql_price_add = '';
		$sql_time_add = '';
		$sql_posted_date_add = '';
		$sql_platform_add = '';
		
		if(count($categories_array)>0)
		{
			$poject_categories_table = ', project_categories as pc ';
			$sql_category_add .= 'AND (pr.id = pc.project_id) AND ( ';
			foreach($categories_array as $category_id)
			{
				$sql_category_add .= "pc.category_id = {$category_id} OR ";
			}
			$sql_category_add = rtrim($sql_category_add, 'OR ');
			$sql_category_add .= ' ) ';
		}
		
		if(count($price_limits_hourly_array)>0)
		{
			$price_min = (float)$price_limits_hourly_array[0];
			$price_max = (float)$price_limits_hourly_array[1];
			
			$sql_price_add .= " AND (pr.budget_low >= {$price_min})";
			$sql_price_add .= " AND (pr.budget_high <= {$price_max})";
		}
		if($time_left != '')
		{	
			$sql_time_add .= " AND (DATEDIFF(`ends`, NOW()) between 0 and {$time_left})";
		}
		
		if($posted_date_days != '')
		{	
			$sql_posted_date_add .= " AND (DATEDIFF(NOW(),`posted`) between 0 and {$posted_date_days})";
		}
		
		if(count($platforms_array)>0)
		{
			$sql_platform_add .= ' AND(';
			foreach($platforms_array as $platform_id)
			{
				$sql_platform_add .= " pr.platform_id = {$platform_id} OR ";
			}
			$sql_platform_add = rtrim($sql_category_add, 'OR ');
			$sql_platform_add .= ' ) ';
		}
		
		$sql_stmt_nr_of_records = "SELECT COUNT(*) as nr_of_projects FROM projects as pr{$poject_categories_table} WHERE hidden = 0 {$sql_category_add} {$sql_price_add} {$sql_time_add} {$sql_posted_date_add} {$sql_platform_add}";
		return $this->db->fetchOne( $sql_stmt_nr_of_records );
	}
	
	public function getActiveCategories()
	{
		$ActiveCategories = array();
		$sql_stmt = "SELECT * FROM categories WHERE active = 1";
		$ActiveCategories = $this->db->fetchAll( $sql_stmt );	
		return $ActiveCategories;		
	}
	
	public function getPlatforms()
	{
		$Platforms = array();
		$sql_stmt = "SELECT * FROM `platforms` WHERE active = 1";
		$Platforms = $this->db->fetchAll( $sql_stmt );	
		return $Platforms;		
	}
	
	public function getCountries()
	{
		$Countries = array();
		$sql_stmt = "SELECT * FROM countries ORDER BY name ASC";
		$Countries = $this->db->fetchAll( $sql_stmt );
		
		return $Countries;
	}
	
	public function ajaxDetailedProject($project_id)
    {
		if($project_id)
		{
			//$sql_stmt = "SELECT pr.*, pl.logo as logo, (select count(*) from proposals where project_id = $project_id) as bids, (select AVG(budget) from proposals where project_id = $project_id) as bids_average FROM projects pr, platforms as pl  WHERE pr.id = " . $project_id . " AND pr.platform_id = pl.id LIMIT 1";
			$sql_stmt = "SELECT pr.*, pl.logo as logo FROM projects pr, platforms as pl  WHERE pr.id = " . $project_id . " AND pr.platform_id = pl.id LIMIT 1";
			return $this->db->fetchRow( $sql_stmt );
		}
    }
	
	public function insertUser($UserInfo)
	{
		$country = (int)$UserInfo['country'];
		//print $country; die;
		$sql_stmt_search_email = "SELECT * FROM `accounts` WHERE `email`='$UserInfo[email]' LIMIT 1";
		//print $sql_stmt_search_email; die;
		if( $this->db->fetchRow( $sql_stmt_search_email ))
		{
			return 2;
			    //$flashMessenger = $this->_helper->getHelper('FlashMessenger');
				//$flashMessenger->addMessage('We did something in the last request');
		} else {
			$sql_stmt = "INSERT INTO accounts(name, fname, lname, email, company, country_id, street, state, post_code) VALUES('{$UserInfo['userName']}','{$UserInfo['firstName']}','{$UserInfo['lastName']}','{$UserInfo['email']}','{$UserInfo['companyName']}',{$country},'{$UserInfo['street']}','{$UserInfo['state']}','{$UserInfo['post_code']}')";
			$insert_result = $this->db->query($sql_stmt);
			if($insert_result) {
				return 1;
			}
		}
		//print $sql_stmt_search_email; die;
		
		
	//	print $sql_stmt; die;
		
		
	}
	
	public function inserta()
	{
		$sql_stmt = 'INSERT INTO `project_categories`(project_id, category_id) VALUES';
		
		srand();
		$category_id = (rand()%10) + 1;
		
		for($i=1;$i<1780;$i++)
		{
			$sql_stmt .= "($i, $category_id), ";
		}
		$sql_stmt = rtrim ($sql_stmt, ',');
		
		$this->db->query($sql_stmt);
	}
	
	public function parseGetParamInArray($get_param, $delimiter)
	{
		$tokenize_category = strtok($get_param, $delimiter);
		$parsed_array = array();
	
		$parsed_array = array();
		$i = 0;
		while ($tokenize_category !== false) 
		{
			if(is_numeric($tokenize_category))
			{
				$parsed_array[$i] = $tokenize_category;
			}
			
			$tokenize_category = strtok($delimiter);
			$i++;
		}
		return $parsed_array;
	}
	
	
	
	
}