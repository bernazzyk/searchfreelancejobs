<?php
class Application_Model_General extends Application_Model_Freelancer
{
	public function getUserId($user_name)
	{
		$sql_stmt = "SELECT `id` FROM accounts  WHERE `name` = '$user_name' LIMIT 1";
		return $this->db->fetchOne( $sql_stmt );
	}
	
	public function getUserIdByEmail($user_email)
	{
		$sql_stmt = "SELECT `id` FROM accounts  WHERE `email` = '$user_email' LIMIT 1";
		return $this->db->fetchOne( $sql_stmt );
	}
	
	public function getUserById($id)
	{
		$sql_stmt = "SELECT * FROM accounts  WHERE id = $id LIMIT 1";
		return $this->db->fetchRow( $sql_stmt );
	}
	
	public function getUserByEmail($user_email)
	{
		$sql_stmt = "SELECT * FROM accounts  WHERE `email` = '$user_email' LIMIT 1";
		return $this->db->fetchRow( $sql_stmt );
	}
	
	public function getUserInfo($user_id)
	{
		$sql_stmt = "SELECT *, (SELECT COALESCE(SUM(budget),0) FROM proposals WHERE `user_id` = $user_id AND accepted = 1) as total_earnings FROM accounts WHERE `id` = $user_id LIMIT 1";
		return $this->db->fetchRow( $sql_stmt );
	}
	
	public function getUserTotalEarnings($user_id)
	{
		$sql_stmt = "SELECT COALESCE(SUM(budget),0) FROM proposals WHERE `user_id` = $user_id AND accepted = 1";
		return $this->db->fetchRow( $sql_stmt );
	}
	
	public function getNrOfProjects()
	{
		$sql_stmt = "SELECT COUNT(id) from projects where ends > NOW() and id not in (select project_id as id from proposals where accepted = 1)";
		$NrOfProjects = $this->db->fetchOne( $sql_stmt );
		return $NrOfProjects;// $this->db->fetchOne( $sql_stmt );
	
	/*
	SELECT id from projects where ends > NOW()
and id not in (select project_id as id from proposals where accepted = 1)

	*/
	
	/*
	SELECT p.id FROM projects as p, proposals as prop WHERE p.id = prop.project_id and p.ends > NOW() group by p.id HAVING count(IF(prop.accepted=1,1,NULL)) = 0
	*/
	
	}
	
	public function getNrOfPlatforms()
	{
		$sql_stmt = "SELECT COUNT(*) AS nr_of_platforms FROM platforms";
		$NrOfPlatforms = $this->db->fetchOne( $sql_stmt );
		return $NrOfPlatforms;// $this->db->fetchOne( $sql_stmt );
	}
	
	public function getProjectTags($project_id)
	{
		$ProjectTags = array();
		$sql_stmt = "SELECT t.name, pt.tag_id FROM `projects_tags` as pt, tags as t WHERE pt.project_id = {$project_id} AND pt.tag_id = t.id";
		
		$ProjectTags = $this->db->fetchAll( $sql_stmt );
		
		//print_r($ProjectTags); die;
		return $ProjectTags;
	}
	
	public function getGetParamsNoP()
	{
		$request = Zend_Controller_Front::getInstance()->getRequest();
		$diffArray = array(
			$request->getActionKey(),
			$request->getControllerKey(),
			$request->getModuleKey()
		);
		$params = array_diff_key(
			$request->getUserParams(),
			array_flip($diffArray)
		);
		
		$url_part_param = '';
		foreach($params as $key=>$value)
		{	
			if($key!='p')
			{
				$value = str_replace(' ','+',$value);
				$url_part_param .= '/'.$key.'/'.$value;
				
			}
		}
		return $url_part_param;
	}
	
	public function BidsSum()
	{
		$sql_stmt = "SELECT SUM(budget) AS earns FROM proposals WHERE accepted = 1";
		$SumMoney = $this->db->fetchOne( $sql_stmt );
		return $SumMoney;
	}
	
	public function getNrOfFreelancers()
	{
		$sql_stmt = "SELECT COUNT(*) FROM freelancers WHERE active = 1";
		$SumMoney = $this->db->fetchOne( $sql_stmt );
		return $SumMoney;
	}
	
	public function fileNameNew($file = '') {
		$path_info = pathinfo($file['name']);
		$ext = $path_info['extension'];
		
		$name = rand(1000000000, 9999999999);
		$result = 'file-' . time() . '-' . mb_substr($name, rand(0, 5), 5) . '.' . $ext;
		
		return $result;
	}
	

	public function InsertProjects($ProjectsList, $platform_id) {
		$sql_to_search = "SELECT id, external_id FROM projects2 WHERE 1=2 ";
		
		foreach($ProjectsList as $Values)
		{
			$sql_to_search .= " OR (external_id = '$Values[external_id]' AND platform_id = $platform_id)";
		}
		$ExternalIdsAlreadyInserted = array();
		$result = $this->db->fetchAll( $sql_to_search );	
		if($result)
		{
			foreach($result as $values)
			{
				$ExternalIdsAlreadyInserted[(int)$values['id']] = $values['external_id'];
			}
			unset($result);
		}
		
		//$sql_stmt_to_insert = 'INSERT INTO `projects2` (`external_url`, `external_id`, `title`, `description`,`posted`, `platform_id`) VALUES';
		
		$sql_stmt_to_insert = 'INSERT INTO `projects2` (`external_url`, `external_id`, `title`, `description`, `posted`, `ends`, `budget_low`, `budget_high`, `platform_id`, `active`, `jobtype`,`bids`) VALUES';
		$sql_stmt_to_insert_on_update = 'INSERT INTO `projects2` (`id`,`external_url`, `external_id`, `title`, `description`, `posted`, `ends`, `budget_low`, `budget_high`, `platform_id`, `active`, `jobtype`,`bids`) VALUES';
		/*INSERT INTO table (id,Col1,Col2) VALUES (1,1,1),(2,2,3),(3,9,3),(4,10,12)
ON DUPLICATE KEY UPDATE Col1=VALUES(Col1),Col2=VALUES(Col2);*/
	
		$sql_stmt_to_insert_appendix = '';
		$sql_stmt_to_insert_on_update_appendix = '';
		foreach($ProjectsList as $Values)
		{
			$sql_values = "'$Values[external_url]','$Values[external_id]','$Values[title]','$Values[description]','$Values[posted]','$Values[ends]',".$Values['budget_low'].','.$Values['budget_high'].','.$platform_id.','.$Values['active'].','. $Values['jobtype'].','.$Values['bids'];
			
			if(!(in_array($Values['external_id'], $ExternalIdsAlreadyInserted)))
			{
				$sql_stmt_to_insert_appendix .= " ($sql_values),";
			} else {
				$id_key = array_search($Values['external_id'], $ExternalIdsAlreadyInserted); //find key (in in DB table) of given value
				$sql_stmt_to_insert_on_update_appendix .= " ({$id_key},$sql_values),";
			}
		}
		
		$result_insert = true;
		$result_insert_on_update = true;
		
		if($sql_stmt_to_insert_on_update_appendix != '')
		{
			$sql_stmt_to_insert_on_update_appendix = rtrim($sql_stmt_to_insert_on_update_appendix,',');
			$sql_stmt_to_insert_on_update_appendix .= ' ON DUPLICATE KEY UPDATE external_url= VALUES(external_url),
																				external_id	= VALUES(external_id),
																				title		= VALUES(title),
																				description	= VALUES(description),
																				posted		= VALUES(posted),
																				ends		= VALUES(ends),
																				budget_low	= VALUES(budget_low),
																				budget_high	= VALUES(budget_high),
																				platform_id	= VALUES(platform_id),
																				active		= VALUES(active),
																				jobtype		= VALUES(jobtype),
																				bids		= VALUES(bids);
																				';
			$result_insert_on_update = $this->db->query($sql_stmt_to_insert_on_update . $sql_stmt_to_insert_on_update_appendix);
		}
		
		if($sql_stmt_to_insert_appendix !='')
		{
			$sql_stmt_to_insert_appendix = rtrim($sql_stmt_to_insert_appendix,',');	
			//print $sql_stmt_to_insert . $sql_stmt_to_insert_appendix;
		//	die('aici');			
			$result_insert = $this->db->query($sql_stmt_to_insert . $sql_stmt_to_insert_appendix);
		}
		return $result_insert && $result_insert_on_update;
		
	}
	
	
	public function InsertProjectFromRSS($ProjectsList, $platform_id) {
		$sql_to_search = "SELECT external_id FROM projects WHERE 1=2 ";
		
		foreach($ProjectsList as $Values)
		{
			$sql_to_search .= " OR (external_id = '$Values[external_id]' AND platform_id = $platform_id)";
		}
		$ExternalIdsAlreadyInserted = array();
		$result = $this->db->fetchAll( $sql_to_search );	

		if($result)
		{
			foreach($result as $key => $values)
			{
				$ExternalIdsAlreadyInserted[] = $values['external_id'];
			}
			unset($result);
		}
		
		$sql_stmt_to_insert = 'INSERT INTO `projects2` (`external_url`, `external_id`, `title`, `description`,`posted`, `platform_id`) VALUES';
		
		
		
		$sql_stmt_to_insert_appendix = '';
		foreach($ProjectsList as $Values)
		{
			if(!(in_array($Values['external_id'], $ExternalIdsAlreadyInserted)))
			{
				$posted = date('Y-m-d H:i:s',strtotime($Values['pubDate']));
				$sql_stmt_to_insert_appendix .= " ('$Values[link]','$Values[external_id]','$Values[title]','$Values[description]','$posted',$platform_id),";
			}
		}
		
		if($sql_stmt_to_insert_appendix !='')
		{
			
			$sql_stmt_to_insert_appendix = rtrim($sql_stmt_to_insert_appendix,',');
		
		//	print $sql_stmt_to_insert . $sql_stmt_to_insert_appendix;
		
			return $this->db->query($sql_stmt_to_insert . $sql_stmt_to_insert_appendix);
		}
		return false;
		
		//print_r($ExternalIdsAlreadyInserted);
		//return $sql_to_search;
	}
	
		public function InsertProjectFromOdesk($ProjectsList, $platform_id) {
		$sql_to_search = "SELECT external_id FROM projects WHERE 1=2 ";
		
		foreach($ProjectsList as $Values)
		{
			$sql_to_search .= " OR (external_id = '$Values[ciphertext]' AND platform_id = $platform_id)";
		}
		$ExternalIdsAlreadyInserted = array();
		$result = $this->db->fetchAll( $sql_to_search );	

		if($result)
		{
			foreach($result as $key => $values)
			{
				$ExternalIdsAlreadyInserted[] = $values['ciphertext'];
			}
			unset($result);
		}
				
		$sql_stmt_to_insert = 'INSERT INTO `projects2` (`external_id`, `title`, `description`,`posted`, `ends`, `budget_high`, `platform_id`, `active`, `jobtype`) VALUES';
		
		$sql_stmt_to_insert_appendix = '';
		foreach($ProjectsList as $Values)
		{
			if(!(in_array($Values['ciphertext'], $ExternalIdsAlreadyInserted)))
			{
				$posted = date('Y-m-d H:i:s',strtotime($Values['date_posted'].' '.$Values['op_time_posted']));
				if($Values['op_end_date']!='')
				{
					$ends = date('Y-m-d H:i:s',strtotime($Values['op_end_date']));
				} else 
				{
					$ends='0000-00-00 00:00:00';
				}
				if($Values['job_type']=='Hourly')
				{
					$jobtype = 1;
				} else {
					$jobtype = 2;
				}
				//Fixed Hourly
				
				$sql_stmt_to_insert_appendix .= " ('$Values[ciphertext]','$Values[op_title]','$Values[op_description]','$posted','$ends',$Values[amount], $platform_id, 1,$jobtype),";
			}
		}
		
		if($sql_stmt_to_insert_appendix !='')
		{
			
			$sql_stmt_to_insert_appendix = rtrim($sql_stmt_to_insert_appendix,',');
		
		//	print $sql_stmt_to_insert . $sql_stmt_to_insert_appendix;
		
			return $this->db->query($sql_stmt_to_insert . $sql_stmt_to_insert_appendix);
		}
		return false;
		
		//print_r($ExternalIdsAlreadyInserted);
		//return $sql_to_search;
	}
	
	public function InsertProjectFromFreelancerCom($ProjectsList, $platform_id) {
		$sql_stmt_to_insert = 'INSERT INTO `projects` (`external_id`,`external_url`, `title`, `description`,`posted`, `ends`, `budget_low`, `budget_high`, `platform_id`, `active`) VALUES';
		
		$sql_stmt_to_insert_appendix = '';
		foreach($ProjectsList as $Values)
		{
			$sql_stmt_to_insert_appendix .= " ('$Values[external_id]','$Values[external_url]','$Values[title]','$Values[description]','$Values[posted]','$Values[ends]',$Values[budget_low],$Values[budget_high], $platform_id, 1),";
		}
			
		$sql_stmt_to_insert_appendix = rtrim($sql_stmt_to_insert_appendix,',');
		//print $sql_stmt_to_insert . $sql_stmt_to_insert_appendix; die;
		return $this->db->query($sql_stmt_to_insert . $sql_stmt_to_insert_appendix);
		
		//return false;
	}
	
	public function InsertProjectFromElance($ProjectsList, $platform_id) {
		
		//id 	external_url 	external_id 	title 	description 	posted 	ends 	budget_low 	budget_high 	platform_id 	active
		
		$sql_stmt_to_insert = 'INSERT INTO `projects` (`external_url`,`external_id`, `title`, `description`,`posted`, `ends`, `budget_low`, `budget_high`, `platform_id`, `active`, `jobtype`) VALUES';
		
		$sql_stmt_to_insert_appendix = '';
		foreach($ProjectsList as $Values)
		{
			$external_url = addslashes($Values['jobURL']);
			$external_id = addslashes($Values['jobId']);
			$title = addslashes($Values['name']);
			$description = addslashes($Values['description']);
			$posted = date('Y-m-d H:i:s',$Values['postedDate']);
			$ends = date('Y-m-d H:i:s',$Values['endDate']);
			$active = 1;
			if((int)$Values['isHourly']==1)
			{
				$budget_low = (float)$Values['hourlyRateMin'];
				$budget_high = (float)$Values['hourlyRateMax'];
				$jobtype = 1;
			} else {
				$budget_low = (float)$Values['budgetMin'];
				$budget_high = (float)$Values['budgetMax'];
				$jobtype = 2;
			}
			$sql_stmt_to_insert_appendix .= " ('$external_url','$external_id', '$title', '$description','$posted', '$ends', $budget_low, $budget_high, $platform_id, $active, $jobtype),";
		}
			
		$sql_stmt_to_insert_appendix = rtrim($sql_stmt_to_insert_appendix,',');
		//print $sql_stmt_to_insert . $sql_stmt_to_insert_appendix; die;
		return $this->db->query($sql_stmt_to_insert . $sql_stmt_to_insert_appendix);
		
		//return false;
	}
	
	public function cURLExtractJSONContent($url)
		{
			if (($r = @curl_init($url)) == false) {
				header("HTTP/1.1 500", true, 500);
				die("Cannot initialize cUrl session. Is cUrl enabled for your PHP installation?");
			}
			 
			// Set cUrl to return text as a variable, instead of directly to the browser.
			$curl_options = array (
				CURLOPT_FRESH_CONNECT => 1,
				CURLOPT_RETURNTRANSFER => 1
				);
			curl_setopt_array($r, $curl_options);
		
			 
			// Access API, and check results.
			
			$json_txt = curl_exec($r);

			if (curl_errno($r) > 0) {
				header("HTTP/1.1 500", true, 500);
				die();
			} else {
				$http_response = intval(curl_getinfo($r, CURLINFO_HTTP_CODE));
				if ($http_response != 200) {
					// Pass on any descriptive error information from the Elance server to 
					// the client.
					header("HTTP/1.1 " . $http_response, true, $http_response);
					header("Content-Type: application/json", true);
					echo($json_txt);
					flush();
					die();
				}
			}
			 
			if ($json_txt == false) {
				header("HTTP/1.1 500", true, 500);
				die("Cannot retrieve API URL using cUrl. URL: " . $url);
			}
			curl_close($r); 
			 
			header("Content-Type: application/json", true);

			
			
			return json_decode($json_txt, true);
		}
	
}