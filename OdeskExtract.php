<?php
	
	$doc1 = new DOMDocument();
	$doc1->load("https://www.odesk.com/api/profiles/v1/search/jobs.xml?page=0;20&st='Open'");
	$xpath = new DOMXpath($doc1);

	$ProjectItems = $xpath->query("jobs/job");
	$ProjectsList = array();
	
	print $ProjectItems->length;
	
	for ($i=1; $i<=$ProjectItems->length;$i++) {
		$ProjectsList[$i]['op_title'] = $xpath->query("jobs/job[{$i}]/op_title")->item(0)->nodeValue;
		$ProjectsList[$i]['op_description'] = $xpath->query("jobs/job[{$i}]/op_description")->item(0)->nodeValue;
		$ProjectsList[$i]['amount'] = $xpath->query("jobs/job[{$i}]/amount")->item(0)->nodeValue;
		
		$ProjectsList[$i]['date_posted'] = $xpath->query("jobs/job[{$i}]/date_posted")->item(0)->nodeValue;
		$ProjectsList[$i]['op_end_date'] = $xpath->query("jobs/job[{$i}]/op_end_date")->item(0)->nodeValue;
		$ProjectsList[$i]['job_type'] = $xpath->query("jobs/job[{$i}]/job_type")->item(0)->nodeValue;
		$ProjectsList[$i]['ciphertext'] = $xpath->query("jobs/job[{$i}]/ciphertext")->item(0)->nodeValue;
		$ProjectsList[$i]['legacy_ciphertext'] = $xpath->query("jobs/job[{$i}]/legacy_ciphertext")->item(0)->nodeValue;
	}	
	
	foreach($ProjectsList as $key=>$value)
	{
		print '<b>Tilte: </b>' . $value['op_title'] . '<br>';
		print '<b>description: </b>' . $value['op_description'] . '<br>';
		print '<b>amount: </b>' . $value['amount'] . '<br>';
		print '<b>date_posted: </b>' . $value['date_posted'] . '<br>';
		print '<b>op_end_date: </b>' . $value['op_end_date'] . '<br>';
		print '<b>ciphertext: </b>' . $value['ciphertext'] . '<br>';
		print '<b>legacy_ciphertext: </b>' . $value['legacy_ciphertext'] . '<br>';
		print '<b>job_type: </b>' . $value['job_type'] . '<br><br><br>';
	}
	
	//print_r($ProjectsList);
	
	
	die('amis');
?>