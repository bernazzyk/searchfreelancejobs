<?php
	//print strtotime('Thu, 06 Dec 2012 04:14:12 GMT');die;
	//1354767252
	
	print date('Y-m-d H:i:s',strtotime())
	//'YYYY-MM-DD HH:MM:SS'
	
	$platform_id = (int)$_GET['plid'];
	
		$RSS_Urls = array(	
						9=>'http://www.ifreelance.com/feeds/rss/projects.aspx?v=2.0',
						6=>'http://www.getacoder.com/projects/rss.xml',
						10=>'http://www.freelance.com//resources/rss/42130d8cd9a10409651edea53684d560.xml',
						5=>'http://www.guru.com/pro/ProjectResults.aspx?BID=0&LOC=2'
		);
		
		if (array_key_exists($platform_id, $RSS_Urls)) {
			$doc1 = new DOMDocument();
			$doc1->load($RSS_Urls[$platform_id]);
			$xpath = new DOMXpath($doc1);

			$ProjectItems = $xpath->query("channel/item");
			$ProjectsList = array();
				
			for ($i=1; $i<=$ProjectItems->length;$i++) {
					
				$ProjectsList[$i]['title'] = $xpath->query("channel/item[{$i}]/title")->item(0)->nodeValue;
				$ProjectsList[$i]['description'] = $xpath->query("channel/item[{$i}]/description")->item(0)->nodeValue;
				if(isset($xpath->query("channel/item[{$i}]/category")->item(0)->nodeValue))
				{
					$ProjectsList[$i]['category'] = $xpath->query("channel/item[{$i}]/category")->item(0)->nodeValue;
				}
				$ProjectsList[$i]['pubDate'] = $xpath->query("channel/item[{$i}]/pubDate")->item(0)->nodeValue;
				$ProjectsList[$i]['link'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
				$ProjectsList[$i]['guid'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
				
				if(isset($xpath->query("channel/item[{$i}]/category")->item(0)->nodeValue))
				{
					$ProjectsList[$i]['dc:creator'] = $xpath->query("channel/item[{$i}]/dc:creator")->item(0)->nodeValue;
				}
				
				if($platform_id==9)
				{
					parse_str(parse_url($ProjectsList[$i]['link'], PHP_URL_QUERY), $getParams);
					$ProjectsList[$i]['external_id'] = $getParams['projectid'];
				}
				
				else if($platform_id==5)
				{
					parse_str(parse_url($ProjectsList[$i]['link'], PHP_URL_QUERY), $getParams);
					$ProjectsList[$i]['external_id'] = $getParams['ProjectId'];
				}
				
				else if($platform_id==6)
				{
					//Dintr-un astefl de link http://www.getacoder.com/projects/windows upload client_155184.html
					//Extragem id-ul proiectului 155184

					$project_id = rtrim(substr($ProjectsList[$i]['link'], strrpos($ProjectsList[$i]['link'], '_') + 1, strlen($ProjectsList[$i]['link'])), '.html');
					
					if(is_numeric($project_id))
					{
						$ProjectsList[$i]['external_id'] = $project_id;
					}
				}
				
				else if($platform_id==10)
				{
					//Dintr-un astefl de link  http://www.freelance.com/en/mission/view/DBA-Sybase-Senior/dbf92bec3b5614e2013b655d6d6312d0
					//Extragem id-ul proiectului dbf92bec3b5614e2013b655d6d6312d0 

					$project_id = substr($ProjectsList[$i]['link'], strrpos($ProjectsList[$i]['link'], '/') + 1, strlen($ProjectsList[$i]['link']));
					$ProjectsList[$i]['external_id'] = $project_id;
				}
			}
			
			//$modelFromGeneral = new Application_Model_General();
			//print $modelFromGeneral($ProjectsList, $platform_id);

			//print count($ProjectsList);
			//print_r($ProjectsList);
			//die;
		}
?>