<?php



class Application_Model_Profile extends Application_Model_Freelancer

{

    public function addProfile($Data, $Files, $user_id, $sql_action)

    {
	
		$picture = '';
		if(!empty($Files['files_0_']['tmp_name']))
		{
			$picture = $Files['files_0_']['name'];
			@unlink('data/profilePictures/'.$Data['oldfile']);
		}
		else
		{
			$picture = $Data['oldfile'];
		}
		if($sql_action=='insert')
		{

			$sql_stmt = "INSERT INTO freelancers(account_id,description,experience,picture,skills,portfolio,industry,education) VALUES($user_id,'$Data[description]','$Data[experience]','$picture','".$Data['skills']."','".$Data['portfolio']."','".$Data['industry']."','".$Data['education']."')";
		}
		else if($sql_action=='update')
		{
			$sql_stmt = "UPDATE freelancers SET description = '$Data[description]',
												experience = '$Data[experience]',
												picture = '$picture',
												skills = '".$Data['skills']."',
												portfolio = '".$Data['portfolio']."',
												industry = '".$Data['industry']."',
												education = '".$Data['education']."'
											WHERE account_id = $user_id
											LIMIT 1";
		}
		$result = $this->db->query($sql_stmt);

		

		$sql_stmt_account_info = "UPDATE accounts SET 	fname = '$Data[fname]',

														lname = '$Data[lname]',

														company = '$Data[company]',

														state = '$Data[state]',

														post_code = '$Data[post_code]',

														phone = '$Data[phone]'

											WHERE id = $user_id

											LIMIT 1";

		$result_account_update = $this->db->query($sql_stmt_account_info);

		
		return $result && $result_account_update;

    }
	
	public function getProfile($account_id) 
	{
		$sql_stmt = "SELECT * FROM freelancers WHERE account_id = {$account_id} LIMIT 1";
		//$sql_stmt = "SELECT f.*, ac.* FROM freelancers as f, accounts as ac WHERE f.account_id = {$account_id} AND ac.id={$account_id} LIMIT 1";

		return $this->db->fetchRow($sql_stmt);
	}

	public function getAcountInfo($account_id)
	{

		$sql_stmt = "SELECT fname,lname,company,state,post_code,street,phone,paid_date,paytype,subscription_id,paypal_subscription_id,suspended_at FROM accounts WHERE id = {$account_id} LIMIT 1";
		return $this->db->fetchRow($sql_stmt);

	}

}

