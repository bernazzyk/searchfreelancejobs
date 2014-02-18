<?php

class Application_Model_Proposal extends Application_Model_Freelancer
{
	public function checkIfAlreadyProposed($project_id, $user_id)
	{
		$sql_stmt_user = "SELECT id FROM proposals WHERE user_id = {$user_id} AND project_id = {$project_id} LIMIT 1";
		if( $this->db->fetchOne( $sql_stmt_user ))
		{
			return true;
		}
		else {return false;};
	}
	
	public function addProposal($project_id,$Data, $Files, $user_id)
    {
		$sql_stmt_user = "SELECT id FROM proposals WHERE user_id = {$user_id} AND project_id = {$project_id} LIMIT 1";
		if( $this->db->fetchOne( $sql_stmt_user ))
		{
			return 2; //already_made_an_offer
		}
		else 
		{
			$budget = $Data['budget'];
			$proposal = $Data['proposal'];
			
			//$lock_sql_stmt = "LOCK TABLES `proposals` WRITE; SET AUTOCOMMIT = 0"; 
			//$this->db->query($lock_sql_stmt);
			
			//$unlock_sql_stmt = "UNLOCK TABLES";
			//$this->db->query($unlock_sql_stmt);
			
			$sql_stmt = "INSERT INTO `proposals`(project_id,budget,proposal,user_id) VALUES($project_id, $budget, '$proposal',$user_id)";
			
			//return $this->db->query($sql_stmt);
			
			$result_of_proposal_insertion = $this->db->query($sql_stmt);
			
			$proposal_id = $this->db->fetchOne("SELECT LAST_INSERT_ID() AS last_id");
			
			$result_of_proposal_attachment_insertion = true;
			if($result_of_proposal_insertion)
			{
				if(isset($Files['files_0_']['tmp_name']) && !empty($Files['files_0_']['tmp_name']))
				{
					$sql_file = 'INSERT INTO `proposal_attachments`(proposal_id,file_name,file_type,file_size,file_content,file_extension) VALUES ';
					//$sql_file = 'INSERT INTO `proposal_attachments`(proposal_id,file_name,file_type,file_size,file_extension) VALUES ';
					foreach($Files as $key => $FileInfo)
					{
						if(!empty($FileInfo['tmp_name']))
						{
							$tmp_name = $FileInfo['tmp_name'];
							
							$file_content = file_get_contents($FileInfo['tmp_name']);
							$file_content = addslashes($file_content);
							$file_extension = $path_info = pathinfo($FileInfo['name']);
							$file_size = (int)$FileInfo['size'];
							$ext = $path_info['extension'];
							
							//$sql_file .= "($proposal_id,'$FileInfo[name]','$FileInfo[type]',$file_size,'$file_content','$ext'),";
							$sql_file .= "($proposal_id,'$FileInfo[name]','$FileInfo[type]',$file_size,'$file_content','$ext'),";
						}
					}
					$sql_file = rtrim($sql_file,',');
					
					//INSERT INTO `proposal_attachments`(proposal_id,file_name,file_type,file_size,file_extension) VALUES (16,'file-1353487647-60780.JPG','image/jpeg',9222,'JPG')
					
					//print $sql_file ; die;
					
					$result_of_proposal_attachment_insertion = $this->db->query($sql_file);
				}
			}
			
			return $result_of_proposal_insertion&&$result_of_proposal_attachment_insertion;
		}
		
		//print $sql_file;
		//die('here');
		
		
		
		//die('asdasda');
    }
}

