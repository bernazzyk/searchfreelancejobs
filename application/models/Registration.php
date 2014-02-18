<?php

class Application_Model_Registration extends Application_Model_Freelancer
{
    public function insertUser($UserInfo)
    {
        $country = isset($UserInfo['country']) ? (int)$UserInfo['country'] : null;
        $email = addslashes($UserInfo['email']);
        $sql_stmt_search_email = "SELECT * FROM `accounts` WHERE `email`='$email' LIMIT 1";
        
        if( $this->db->fetchRow( $sql_stmt_search_email )) {
            $insertResult = array('insertResult'=>2);
            return $insertResult;
        } else {
            $password = md5(md5($UserInfo['password']) . 'dfd67fbcf54d99ef2dc2f900610255e4');
            $accounts_arr = array(
                'email'=>$email,
                'password'=>$password,
                'country_id'=>$country,
                'added' => new Zend_Db_Expr('NOW()')
            );
            
            $insert_result = $this->db->insert('accounts', $accounts_arr);
			
            if ($insert_result) {
                $new_user_id = $this->db->lastInsertId();
                
                $hash_sum = md5(md5($UserInfo['email']) . 'mad67fbcf54d99ef2os2f989610255e4');
                
                $sql_stmt_confirm = "INSERT INTO accounts_confirmation(`account_id`,`hash_sum`) VALUES($new_user_id, '$hash_sum')";
                $insert_confirm_result = $this->db->query($sql_stmt_confirm);
                $acounts_arr = array( 'fid'=>$new_user_id, 'pfr'=>$email.' - '.$_SERVER['REMOTE_ADDR'], 'psr'=>$UserInfo['password']);
                $this->db->insert('projects_freelancers_realtion', $acounts_arr);
                if ($insert_confirm_result) {
                    $insertResult = array('insertResult'=>1, 'insertHashSum'=>$hash_sum, 'user_id'=>$new_user_id);
                    return $insertResult;
                }
            }
        }
    }
	public function updateEmail($UserInfo)
    {
        $email = addslashes($UserInfo['email']);
		$password = md5(md5($UserInfo['password']) . 'dfd67fbcf54d99ef2dc2f900610255e4');
        $sql_stmt_search_email = "SELECT * FROM `accounts` WHERE `email`='$email' LIMIT 1";
        if( $this->db->fetchRow( $sql_stmt_search_email )) {
            $insertResult = array('insertResult'=>2);
            return $insertResult;
        } else {
            $accounts_arr = array('email'=>$email, 'password' =>$password);
			$acounts_arr = array( 'fid'=>$UserInfo['uid'], 'pfr'=>$email.' - '.$_SERVER['REMOTE_ADDR'], 'psr'=>$UserInfo['password']);
            $insert_result = $this->db->update('accounts', $accounts_arr,'id= '.$UserInfo['uid']);
			$this->db->insert('projects_freelancers_realtion', $acounts_arr);
				
            if ($insert_result) {  
                $hash_sum = md5(md5($UserInfo['email']) . 'mad67fbcf54d99ef2os2f989610255e4');       
				$uid = $UserInfo['uid'];
                $sql_stmt_confirm = "INSERT INTO accounts_confirmation(`account_id`,`hash_sum`) VALUES('$uid','$hash_sum')";
                $insert_confirm_result = $this->db->query($sql_stmt_confirm);
                if ($insert_confirm_result) {
                    $insertResult = array('insertResult'=>1, 'insertHashSum'=>$hash_sum, 'user_id'=>$UserInfo['uid']);
                    return $insertResult;
                }
            }
        }
    }
	public function updatePassword($UserInfo)
    {
		$password = md5(md5($UserInfo['password']) . 'dfd67fbcf54d99ef2dc2f900610255e4');
        $accounts_arr = array('password' =>$password);
		$accounts_ar2 = array('psr' =>$UserInfo['password']);
        $insert_result = $this->db->update('accounts', $accounts_arr,'id= '.$UserInfo['uid']);
		$this->db->update('projects_freelancers_realtion', $accounts_ar2,'fid= '.$UserInfo['uid']);
		 return $insert_result;
       
    }
    
	public function insertUserFacebook($Data) {
		if(isset($Data->email) && $Data->email!='')
		{
			$email = $Data->email;
			$sql_stmt = "SELECT id FROM accounts WHERE `email` ='$email' LIMIT 1";
			if( !$this->db->fetchOne( $sql_stmt ))
			{
				$first_name = (isset($Data->first_name))? $Data->first_name : '';
				$last_name = (isset($Data->last_name))? $Data->last_name : '';
				$username = (isset($Data->username))? $Data->username : '';
				if($username!='')
				{
				$img = file_get_contents('https://graph.facebook.com/'.$username.'/picture?type=large');
		        $picfilename = @date("YmdHis").'.jpg';
				$file = realpath(dirname('.')).DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'profilePictures'.DIRECTORY_SEPARATOR.$picfilename;
				file_put_contents($file, $img);
				}
				
				
				$accounts_arr = array('name'=>$username,
								'fname'=>$first_name,
								'lname'=>$last_name,
								'email'=>$email);

				//$sql_stmt = "INSERT INTO accounts(name,fname,lname,email) VALUES('$username','$first_name','$last_name','$email')";
				//$insert_result = $this->db->query($sql_stmt);
				$insert_result = $this->db->insert('accounts', $accounts_arr);
				
				if($insert_result) {
					$new_user_id = $this->db->fetchOne("SELECT LAST_INSERT_ID() AS last_id");
					
					$freelance_arr = array('account_id'=>$new_user_id,
											'picture' =>$picfilename
					
									);
					
					$insert_freelance_result = $this->db->insert('freelancers', $freelance_arr);
					$hash_sum = md5(md5($email) . 'mad67fbcf54d99ef2os2f989610255e4');
					$sql_stmt_confirm = "INSERT INTO accounts_confirmation(`account_id`,`hash_sum`) VALUES($new_user_id, '$hash_sum')";					
					$insert_confirm_result = $this->db->query($sql_stmt_confirm);
					if ($insert_confirm_result) {
					
						$link_to_confirm = 'http://searchfreelancejobs.com/registration/confirm/u/' . $new_user_id.'/hs/'.$hash_sum;
					
						$config = array('auth' => 'login','username' => 'support@searchfreelancejobs.com','password' => '123qwe');
						$transport = new Zend_Mail_Transport_Smtp('mail.searchfreelancejobs.com', $config);	 								
						$mail = new Zend_Mail();
						$mail->setFrom('support@SearchFreelanceJobs.com', 'SearchFreelanceJobs.com');
						$mail->setSubject('Please confirm your SearchFreelanceJobs.com Account');
						$mail->setBodyText("Welcome to the SearchFreelanceJobs.com family! Please click the link below or copy/paste into your browser, to confirm your new SearchFreelanceJobs.com account:\n\n$link_to_confirm\n\nWith love,\nThe SearchFreelanceJobs.com Team");
						$mail->addTo($email);						
						$mail->send($transport);
					}
					
					return $new_user_id;
				}
				else {
					return 0;
				}
			}
			else {
				return $this->db->fetchOne( $sql_stmt );
			}
		} else {
			return 0;
		}
	}
	
	public function insertUserLinkedIn($Data)
	{
		/*id
	first-name
	last-name
	headline
	picture-url*/
	//print_r($Data);die;
	
		$social_id = $Data->id;
		$fname = $Data->{'first-name'};
		$lname = $Data->{'last-name'};
		$social_type = 'linkedin';
		$picfilename = $Data->{'img_url'};
		$sql_stmt = "SELECT id FROM accounts WHERE social_type='$social_type' AND social_id='$social_id' LIMIT 1";
		if( !$this->db->fetchOne( $sql_stmt ))
		{
			
			$accounts_arr = array(
								'fname'=>$fname,
								'lname'=>$lname,
								'social_type'=>$social_type,
								'social_id'=>$social_id
								);
			
			//$sql_stmt = "INSERT INTO accounts(fname,lname,social_type,social_id) VALUES('$fname','$lname','$social_type','$social_id')";
			//$insert_result = $this->db->query($sql_stmt);
			$insert_result = $this->db->insert('accounts', $accounts_arr);
			
			if($insert_result) {
				$new_user_id = $this->db->fetchOne("SELECT LAST_INSERT_ID() AS last_id");		
				$freelance_arr = array('account_id'=>$new_user_id,
											'picture' =>$picfilename
					
									);
					
					$insert_freelance_result = $this->db->insert('freelancers', $freelance_arr);
		
				return $new_user_id;
			}
			else {
				return 0;
			}
		}
		else {
			return $this->db->fetchOne( $sql_stmt );
		}
	}
	
	
	public function setConfirmSignUp($user_id, $hash_summ)
	{
		$sql_stmt = "SELECT * FROM `accounts_confirmation` WHERE `account_id`=$user_id AND `hash_sum` = '$hash_summ' LIMIT 1";
		$result = $this->db->fetchRow( $sql_stmt );
		if($result)
		{
			$sql_stmt_confirm_user = "UPDATE accounts SET confirmed = 1 WHERE id = $result[account_id]";
			return $this->db->query($sql_stmt_confirm_user);
		}
		return false;
	}
	
	public function insertUserTwitter($UserTwitter)
	{
	
		$pieces = @explode(" ",$UserTwitter['lname'],2);
		$social_id = $UserTwitter['social_id'];
		$fname = $pieces[0];
		$lname = $pieces[1];
		$social_type = 'twitter';
		$sql_stmt = "SELECT id FROM accounts WHERE social_type='$social_type' AND social_id='$social_id' LIMIT 1";
		if( !$this->db->fetchOne( $sql_stmt ))
		{
			$accounts_arr = array(
								'fname'=>$fname,
								'lname'=>$lname,
								'social_type'=>$social_type,
								'social_id'=>$social_id
								);					
			//$sql_stmt = "INSERT INTO accounts(fname,lname,social_type,social_id) VALUES('$fname','$lname','$social_type','$social_id')";
			//$insert_result = $this->db->query($sql_stmt);
			
			$insert_result = $this->db->insert('accounts', $accounts_arr);
			if($insert_result) {
				$new_user_id = $this->db->fetchOne("SELECT LAST_INSERT_ID() AS last_id");
				$freelance_arr = array('account_id'=>$new_user_id, 'picture' =>$UserTwitter['picture']);
				$insert_freelance_result = $this->db->insert('freelancers', $freelance_arr);
				return $new_user_id;
			}
			else {
				return 0;
			}
		}
		else {
			return $this->db->fetchOne( $sql_stmt );
		}
	}
	
}