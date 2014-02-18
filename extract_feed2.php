<?php
	/*$doc1 = new DOMDocument();
	//$doc1->load("http://www.ifreelance.com/feeds/rss/projects.aspx?v=2.0&page=2");
	$doc1->load("http://www.ifreelance.com/feeds/rss/projects.aspx?v=2.0");
	$xpath = new DOMXpath($doc1);

	$ProjectItems = $xpath->query("channel/item");
	$ProjectsList = array();
	
	for ($i=1; $i<=$ProjectItems->length;$i++) {
		
		$ProjectsList[$i]['title'] = $xpath->query("channel/item[{$i}]/title")->item(0)->nodeValue;
		$ProjectsList[$i]['description'] = $xpath->query("channel/item[{$i}]/description")->item(0)->nodeValue;
		$ProjectsList[$i]['category'] = $xpath->query("channel/item[{$i}]/category")->item(0)->nodeValue;
		$ProjectsList[$i]['pubDate'] = $xpath->query("channel/item[{$i}]/pubDate")->item(0)->nodeValue;
		$ProjectsList[$i]['link'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		$ProjectsList[$i]['guid'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		$ProjectsList[$i]['dc:creator'] = $xpath->query("channel/item[{$i}]/dc:creator")->item(0)->nodeValue;
		
	}*/
	
	/*$doc1 = new DOMDocument();
	$doc1->load("http://www.getacoder.com/projects/rss.xml");
	$xpath = new DOMXpath($doc1);

	$ProjectItems = $xpath->query("channel/item");
	$ProjectsList = array();
	
	for ($i=1; $i<=$ProjectItems->length;$i++) {
		
		$ProjectsList[$i]['title'] = $xpath->query("channel/item[{$i}]/title")->item(0)->nodeValue;
		$ProjectsList[$i]['description'] = $xpath->query("channel/item[{$i}]/description")->item(0)->nodeValue;
		$ProjectsList[$i]['category'] = $xpath->query("channel/item[{$i}]/category")->item(0)->nodeValue;
		$ProjectsList[$i]['pubDate'] = $xpath->query("channel/item[{$i}]/pubDate")->item(0)->nodeValue;
		$ProjectsList[$i]['link'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		$ProjectsList[$i]['guid'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		$ProjectsList[$i]['dc:creator'] = $xpath->query("channel/item[{$i}]/dc:creator")->item(0)->nodeValue;
		
	}
	
	print count($ProjectsList);
	print_r($ProjectsList);*/
	
	/*$doc1 = new DOMDocument();
	$doc1->load("http://www.freelance.com//resources/rss/42130d8cd9a10409651edea53684d560.xml");
	$xpath = new DOMXpath($doc1);

	$ProjectItems = $xpath->query("channel/item");
	$ProjectsList = array();
	
	for ($i=1; $i<=$ProjectItems->length;$i++) {
		
		$ProjectsList[$i]['title'] = $xpath->query("channel/item[{$i}]/title")->item(0)->nodeValue;
		$ProjectsList[$i]['description'] = $xpath->query("channel/item[{$i}]/description")->item(0)->nodeValue;
		//$ProjectsList[$i]['category'] = $xpath->query("channel/item[{$i}]/category")->item(0)->nodeValue;
		$ProjectsList[$i]['pubDate'] = $xpath->query("channel/item[{$i}]/pubDate")->item(0)->nodeValue;
		$ProjectsList[$i]['link'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		$ProjectsList[$i]['guid'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		//$ProjectsList[$i]['dc:creator'] = $xpath->query("channel/item[{$i}]/dc:creator")->item(0)->nodeValue;
		
	}
	
	print count($ProjectsList);
	print_r($ProjectsList);*/
	
	//http://www.guru.com/pro/ProjectResults.aspx?BID=0&LOC=2
	
	$doc1 = new DOMDocument();
	$doc1->load("http://www.guru.com/pro/ProjectResults.aspx?BID=0&LOC=2");
	$xpath = new DOMXpath($doc1);

	$ProjectItems = $xpath->query("channel/item");
	$ProjectsList = array();
	
	for ($i=1; $i<=$ProjectItems->length;$i++) {
		
		$ProjectsList[$i]['title'] = $xpath->query("channel/item[{$i}]/title")->item(0)->nodeValue;
		$ProjectsList[$i]['description'] = $xpath->query("channel/item[{$i}]/description")->item(0)->nodeValue;
		//$ProjectsList[$i]['category'] = $xpath->query("channel/item[{$i}]/category")->item(0)->nodeValue;
		$ProjectsList[$i]['pubDate'] = $xpath->query("channel/item[{$i}]/pubDate")->item(0)->nodeValue;
		$ProjectsList[$i]['link'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		$ProjectsList[$i]['guid'] = $xpath->query("channel/item[{$i}]/link")->item(0)->nodeValue;
		//$ProjectsList[$i]['dc:creator'] = $xpath->query("channel/item[{$i}]/dc:creator")->item(0)->nodeValue;
		
	}
	
	print count($ProjectsList);
	print_r($ProjectsList);
	
		
	$GuruCategories = array('Admin Support'=>1300, 
							'Broadcasting'=>600,
							'Business Consulting'=>1400,
							'Engineering & CAD'=>1700,
							'ERP & CRM'=>1000,
							'Fashion & Interior Designs'=>700,
							'Finance & Accounting'=>1600,
							'Graphic Design & Multimedia'=>200,
							'Illustration & Art'=>300,
							'Legal'=>1500,
							'Marketing & Communications'=>1800,
							'Networking & Telephone Systems'=>900,
							'Photography & Videography'=>400,
							'Programming & Databases'=>800,
							'Sales & Telemarketing'=>1200,
							'Websites & Ecommerce'=>100,
							'Writing, Editing & Translation'=>500
							);
	
	/*
	//Admin Suport
	http://www.guru.com/pro/ProjectResults.aspx?CID=1300&BID=0&LOC=2
	
	//Broadcasting
	http://www.guru.com/pro/ProjectResults.aspx?CID=600&BID=0&LOC=2
	
	//Bussiness and consulting
	http://www.guru.com/pro/ProjectResults.aspx?CID=1400&BID=0&LOC=2
	
	//Engineering & CAD
	http://www.guru.com/pro/ProjectResults.aspx?CID=1700&BID=0&LOC=2
	
	//ERP & CRM
	http://www.guru.com/pro/ProjectResults.aspx?CID=1000&BID=0&LOC=2
	
	//Fashion & Interior designs
	http://www.guru.com/pro/ProjectResults.aspx?CID=700&BID=0&LOC=2
	
	//Finance and counting
	http://www.guru.com/pro/ProjectResults.aspx?CID=1600&BID=0&LOC=2
	
	//Graphic Design and Multimedia
	http://www.guru.com/pro/ProjectResults.aspx?CID=200&BID=0&LOC=2
	
	//Ilustration and art
	http://www.guru.com/pro/ProjectResults.aspx?CID=300&BID=0&LOC=2
	
	//Legal
	http://www.guru.com/pro/ProjectResults.aspx?CID=1500&BID=0&LOC=2
	
	//Marketin and comunications
	http://www.guru.com/pro/ProjectResults.aspx?CID=1800&BID=0&LOC=2
	
	//Networkin & Telephone Systems
	http://www.guru.com/pro/ProjectResults.aspx?CID=900&BID=0&LOC=2
	
	//Photography and Videography
	http://www.guru.com/pro/ProjectResults.aspx?CID=400&BID=0&LOC=2
	
	//Programing and Database
	http://www.guru.com/pro/ProjectResults.aspx?CID=800&BID=0&LOC=2
	
	//Sales and Telemarketing
	http://www.guru.com/pro/ProjectResults.aspx?CID=1200&BID=0&LOC=2
	
	//Websites & Ecomerces
	http://www.guru.com/pro/ProjectResults.aspx?CID=100&BID=0&LOC=2
	
	//Writing,Editing & Translation
	http://www.guru.com/pro/ProjectResults.aspx?CID=500&BID=0&LOC=2
	*/

?>