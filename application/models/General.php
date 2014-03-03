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
	
	public function checkIfFirstTimeLogin($user_id)
	{
		$sql_stmt = "SELECT first_time FROM accounts  WHERE id = $user_id LIMIT 1";
		if($this->db->fetchOne( $sql_stmt ) == '0')
		{
			return true;
		}
		return false;
	}
	
	public function setFirstTimeLogin($user_id)
	{
		$sql_stmt = "UPDATE accounts set first_time = 1 WHERE id=$user_id LIMIT 1";
		$this->db->query( $sql_stmt );
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
	
	public function getFreelancerInfo($user_id)
	{
		$sql_stmt = "SELECT * FROM freelancers WHERE `account_id` = $user_id LIMIT 1";
		return $this->db->fetchRow( $sql_stmt );
	}
	
	public function getUserTotalEarnings($user_id)
	{
		$sql_stmt = "SELECT COALESCE(SUM(budget),0) FROM proposals WHERE `user_id` = $user_id AND accepted = 1";
		return $this->db->fetchRow( $sql_stmt );
	}
	
	public function getNrOfProjects()
	{
		//$sql_stmt = "SELECT COUNT(id) from projects where ends > NOW() and id not in (select project_id as id from proposals where accepted = 1)";
		$sql_stmt = "SELECT COUNT(id) from projects WHERE hidden = 0";
		$NrOfProjects = $this->db->fetchOne( $sql_stmt );
		return $NrOfProjects + 40000;// $this->db->fetchOne( $sql_stmt );
	
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
		$sql_stmt = "SELECT COUNT(*) AS nr_of_platforms FROM `platforms` WHERE `active` = 1";
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
		//$sql_stmt = "SELECT COUNT(*) FROM freelancers WHERE active = 1";
		$sql_stmt = "SELECT COUNT(*) FROM accounts";
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
	

	public function InsertProjects($ProjectsList, $platform_id, $ProjectFiles = array()) 
	{
	
		$sql_to_search = "SELECT id, external_id FROM projects WHERE 1=2 ";
		
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
		
					   $sql_stmt_to_insert = 'INSERT INTO `projects` (`url`,`external_url`, `external_id`, `external_second_id`, `title`, `description`, `posted`, `ends`, `budget_low`, `budget_high`, `platform_id`, `active`, `jobtype`,`bids`, `bids_avg`, `budget_currency`,`external_user_id`) VALUES';
		$sql_stmt_to_insert_on_update = 'INSERT INTO `projects` (`id`,`url`,`external_url`, `external_id`, `external_second_id`, `title`, `description`, `posted`, `ends`, `budget_low`, `budget_high`, `platform_id`, `active`, `jobtype`,`bids`, `bids_avg`, `budget_currency`,`external_user_id`) VALUES';
	
		$sql_stmt_to_insert_appendix = '';
		$sql_stmt_to_insert_on_update_appendix = '';
		foreach($ProjectsList as $Key => $Values)
		{ 
			$Values['description'] = addslashes($Values['description']);
			$url = $this->FilterURL($Values['title']).'-pl-'.$platform_id.'-'.$Values['external_id'];
			$sql_values = "'{$url}','$Values[external_url]','$Values[external_id]', '$Values[external_second_id]', '$Values[title]','$Values[description]','$Values[posted]','$Values[ends]',".$Values['budget_low'].','.$Values['budget_high'].','.$platform_id.','.$Values['active'].','. $Values['jobtype'].','.$Values['bids'].','.$Values['bids_avg'].','.$Values['budget_currency'].','."'$Values[external_user_id]'";
			
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
																				external_second_id	= VALUES(external_second_id),
																				title		= VALUES(title),
																				description	= VALUES(description),
																				posted		= VALUES(posted),
																				ends		= VALUES(ends),
																				budget_low	= VALUES(budget_low),
																				budget_high	= VALUES(budget_high),
																				platform_id	= VALUES(platform_id),
																				active		= VALUES(active),
																				jobtype		= VALUES(jobtype),
																				bids		= VALUES(bids),
																				bids_avg	= VALUES(bids_avg),
																				budget_currency	= VALUES(budget_currency),
																				external_user_id = VALUES(external_user_id);
																				';
			$result_insert_on_update = $this->db->query($sql_stmt_to_insert_on_update . $sql_stmt_to_insert_on_update_appendix);
		}
		
		if($sql_stmt_to_insert_appendix !='')
		{
			$sql_stmt_to_insert_appendix = rtrim($sql_stmt_to_insert_appendix,',');	
			//print $sql_stmt_to_insert . $sql_stmt_to_insert_appendix;
			//die('aici');		 	
			$result_insert = $this->db->query($sql_stmt_to_insert . $sql_stmt_to_insert_appendix);
		}
		
		
		if($ProjectFiles) 
		{
			$sql_delete_attachments = "DELETE FROM projects_attachments WHERE 1=2 OR";
			$sql_stmt_to_insert_attachments = 'INSERT INTO `projects_attachments` (`project_id`,`file_name`, `file_url`) VALUES ';
			$sql_stmt_to_insert_attachments_appendix = '';
			foreach($ProjectFiles as $ExternalProjectId => $FilesList)
			{
				$sql_extract_id = 'SELECT id FROM projects WHERE external_id = "'.$ExternalProjectId.'" AND platform_id = '.$platform_id;
				$project_id = $this->db->fetchOne( $sql_extract_id );
			
				$sql_delete_attachments .= " (project_id={$project_id}) OR";
				foreach( $FilesList as $FileValues)
				{
					$file_name = (isset($FileValues['file_name']))? $FileValues['file_name'] : '';
					$file_url = (isset($FileValues['file_url']))? $FileValues['file_url'] : '';
					//$file_name = $FileValues['file_name'];
					//$file_url  = $FileValues['file_url'];
					$sql_stmt_to_insert_attachments_appendix .= " ({$project_id},'{$file_name}','{$file_url}'),";
				}
			}
			$sql_stmt_to_insert_attachments_appendix = rtrim($sql_stmt_to_insert_attachments_appendix,',');
			$sql_delete_attachments = rtrim($sql_delete_attachments, "OR");
			
			$this->db->query($sql_delete_attachments);
			if($sql_stmt_to_insert_attachments_appendix!='')
			{
				$this->db->query($sql_stmt_to_insert_attachments . $sql_stmt_to_insert_attachments_appendix);
			}
		}
		
		return $result_insert && $result_insert_on_update;
		
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
		
	public function selectJobTypeTitle($id)
	{
		$sql_stmt = "SELECT type FROM job_types WHERE id = $id";
		$Type = $this->db->fetchOne( $sql_stmt );
		return $Type;
	}
	
	//public function 
	
	public function cURLExtractJSONContent($url)
		{
			if (($r = @curl_init($url)) == false) {
				header("HTTP/1.1 500", true, 500);
			//	die("Cannot initialize cUrl session. Is cUrl enabled for your PHP installation?");
			}
			 
			// Set cUrl to return text as a variable, instead of directly to the browser.
			$curl_options = array (
				CURLOPT_FRESH_CONNECT => 1,
		//		CURLOPT_USERPWD, "telfus64asd:craca95tit",
		//		CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
				CURLOPT_RETURNTRANSFER => 1
				);
			curl_setopt_array($r, $curl_options);
		
			 
			// Access API, and check results.
			
			$json_txt = curl_exec($r);

			if (curl_errno($r) > 0) {
				header("HTTP/1.1 500", true, 500);
				//die();
			} else {
				$http_response = intval(curl_getinfo($r, CURLINFO_HTTP_CODE));
				//print '+++'.$http_response;
				if ($http_response != 200) {
					// Pass on any descriptive error information from the Elance server to 
					// the client.
					header("HTTP/1.1 " . $http_response, true, $http_response);
					//header("Content-Type: application/json", true);
					echo $json_txt. '--';
					flush();
					//die();
				}
			}
			 
			if ($json_txt == false) {
				header("HTTP/1.1 500", true, 500);
				//die("Cannot retrieve API URL using cUrl. URL: " . $url);
			}
			curl_close($r); 
			 
			//header("Content-Type: application/json", true);

			return json_decode($json_txt, true);
		}
	
	public function getUserPlatformData($user_id,$platform_id)
	{
		$EncKey = '179BBE947B70F7CD0F2CE35EA9F5D590';
		$sql_stmt = "SELECT *,AES_DECRYPT(username,'$EncKey') as username,AES_DECRYPT(password,'$EncKey') as password, AES_DECRYPT(access_token,'$EncKey') as access_token, AES_DECRYPT(access_token_secret,'$EncKey') as access_token_secret FROM curl_platforms_users_data WHERE user_id={$user_id} AND platform_id={$platform_id}";
		return $this->db->fetchRow( $sql_stmt );
	}
	
	public function getConnectedPlatforms($user_id)
	{
		$sql_stmt = "SELECT cp.platform_id as c_plid, p.* FROM curl_platforms_users_data as cp, platforms as p WHERE user_id={$user_id} AND cp.connected = 1 AND cp.platform_id=p.id";
		return $this->db->fetchAll( $sql_stmt );
	}
	
	public function getCurrenciesArray()
	{
		$sql_stmt = "SELECT * FROM currency";
		$CurrArr = $this->db->fetchAll( $sql_stmt );
		$CurrencyArray = array();
		foreach($CurrArr as $key=>$value)
		{
			$CurrencyArray[$value['id']] = $value['sign'];
		}
		//print_r($CurrencyArray);
		return $CurrencyArray;
	}
	
	/*
	public function ConnectUserPlatform($user_id,$platform_id)
	{
		$sql_stmt = "SELECT id FROM connected_platforms WHERE user_id={$user_id} AND platform_id={$platform_id}";
		$exist_record =  $this->db->fetchOne( $sql_stmt );
		if($exist_record) {
			$update_stmt = "UPDATE `connected_platforms` SET `connected` = 1 WHERE `user_id`={$user_id} AND `platform_id`={$platform_id}";
			$this->db->query( $update_stmt );
		} else {
			$insert_stmt = "INSERT INTO `connected_platforms`(`user_id`,`platform_id`,`connected`) VALUES({$user_id},{$platform_id},1)";
			$this->db->query( $insert_stmt );
		}
	}*/
	
	public function DisconnectUserFromPlatform($user_id, $platform_id)
	{
		$sql_stmt = "DELETE FROM `curl_platforms_users_data` WHERE user_id={$user_id} AND platform_id={$platform_id}";
		$this->db->query( $sql_stmt );
	}
	
	public function MMTest()
	{
		$sql_stmt = "SELECT platform_id, AES_DECRYPT( access_token, '179BBE947B70F7CD0F2CE35EA9F5D590' ) AS aaaa FROM `curl_platforms_users_data` WHERE platform_id = 3";
		$this->db->query( $sql_stmt );
		print_r( $this->db->fetchRow($sql_stmt ));
		die;
	}
	
	
	
	public function insertApiPlatformUser($platform_id, $user_id, $access_token=null ,$access_token_secret=null)
	{
		$EncKey = '179BBE947B70F7CD0F2CE35EA9F5D590';
		$sql_stmt = "SELECT id FROM `curl_platforms_users_data` WHERE user_id={$user_id} AND platform_id={$platform_id}";
		$exist_record =  $this->db->fetchOne( $sql_stmt );
		if($exist_record) {
			$update_stmt = "UPDATE `curl_platforms_users_data` SET `access_token` = AES_ENCRYPT('{$access_token}','{$EncKey}'), `access_token_secret` = AES_ENCRYPT('{$access_token_secret}','{$EncKey}'), connected = 1  WHERE `user_id`={$user_id} AND `platform_id`={$platform_id}";
			$this->db->query( $update_stmt );
		} else {
			$insert_stmt = "INSERT INTO `curl_platforms_users_data`(`platform_id`,`user_id`,`access_token`,`access_token_secret`,`connected`) VALUES({$platform_id},{$user_id}, AES_ENCRYPT('{$access_token}','{$EncKey}'),AES_ENCRYPT('{$access_token_secret}','{$EncKey}'),1)";
			$this->db->query( $insert_stmt );
		}
	}
	
	public function insertRemotePlatformUser($platform_id, $user_id, $username,$password)
	{
		$EncKey = '179BBE947B70F7CD0F2CE35EA9F5D590';
		$sql_stmt = "SELECT id FROM `curl_platforms_users_data` WHERE user_id={$user_id} AND platform_id={$platform_id}";
		$exist_record =  $this->db->fetchOne( $sql_stmt );
		if($exist_record) {
			$update_stmt = "UPDATE `curl_platforms_users_data` SET `username` = AES_ENCRYPT('{$username}','{$EncKey}'), `password` = AES_ENCRYPT('{$password}','{$EncKey}'), connected = 1 WHERE `user_id`={$user_id} AND `platform_id`={$platform_id}";
			$this->db->query( $update_stmt );
		} else {
			$insert_stmt = "INSERT INTO `curl_platforms_users_data`(`platform_id`,`user_id`,`username`,`password`,`connected`) VALUES({$platform_id},{$user_id},AES_ENCRYPT('{$username}','{$EncKey}'),AES_ENCRYPT('{$password}','{$EncKey}'),1)";
			$this->db->query( $insert_stmt );
		}
	}
	
	public function getProjectByPlatformAndExternalId($platform_id,$external_id)
	{
		$sql_stmt = "SELECT * FROM projects WHERE platform_id={$platform_id} AND external_id='{$external_id}' LIMIT 1";
		return $this->db->fetchRow($sql_stmt );
	}
	
	public function extractCURLPlatforms ()
	{
		$sql_stmt = "SELECT * FROM platforms WHERE id = 5 OR id = 6 OR id = 7 OR id = 8 OR id = 9 OR id = 10";
		return $this->db->fetchAll($sql_stmt );
	}
	
	public function getAllUserConnectedPlatforms($user_id)
	{
		$EncKey = '179BBE947B70F7CD0F2CE35EA9F5D590';
		$sql_stmt = "SELECT pud.*, AES_DECRYPT(pud.username,'$EncKey') as username, AES_DECRYPT(pud.password,'$EncKey') as password, AES_DECRYPT(pud.access_token,'$EncKey') as access_token, AES_DECRYPT(pud.access_token_secret,'$EncKey') as access_token_secret, p.is_curl FROM curl_platforms_users_data as pud, platforms AS p WHERE pud.user_id={$user_id} AND pud.connected = 1 AND pud.platform_id = p.id";
		//print $sql_stmt;
		return $this->db->fetchAll($sql_stmt );
	}
	
	public function getPlatform($platform_id)
	{
		$sql_stmt = "SELECT * FROM platforms WHERE id = {$platform_id} LIMIT 1";
		return $this->db->fetchRow($sql_stmt );
	}
	
	public function extractProjectsFiles($project_id)
	{
		$sql_stmt = "SELECT * FROM projects_attachments WHERE project_id = {$project_id}";
		return $this->db->fetchAll($sql_stmt );
	}
	
    public function extractFiles(array $projectIds)
    {
        $projectIds = implode(', ', array_map('intval', $projectIds));
        $stmt = "SELECT * FROM projects_attachments WHERE project_id IN ({$projectIds})";
        $files = $this->db->fetchAll($stmt);
        
        $result = array();
        foreach ($files as $file) {
            $result[$file['project_id']][] = $file;
        }
        
        return $result;
	}
	
	public function FilterURL ( $url ) {
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
		
	public function FreelancerConvertObjectToArr($InboxMessages)
	{
		$InboxMessagesArr = (array)$InboxMessages;
		foreach($InboxMessagesArr as $key=>$value)
		{
			if(strpos($key,'freelancerData')!==false)
			{
				$freelancerDataKey = $key;
				break;
			}
		}
		$FreelancerInboxData = json_decode($InboxMessagesArr[$freelancerDataKey],true);
		return $FreelancerInboxData;
	}
	
	public function OdeskGetRealExternalId($exteranl_id_fake)
	{
		$sql_stmt = "SELECT external_id_real FROM odesk_real WHERE exteranl_id_fake = '{$exteranl_id_fake}' LIMIT 1";
		return $this->db->fetchOne($sql_stmt );
	}
	
	public function OdeskInsertExternalId($exteranl_id_fake,$external_id_real)
	{
		$insert_stmt = "INSERT INTO `odesk_real`(exteranl_id_fake,external_id_real) VALUES('$exteranl_id_fake','$external_id_real')";
		$this->db->query( $insert_stmt );
	}

}