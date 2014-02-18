<?php
include_once('../simple_html_dom.php');
$i=0;
$ProjectList = array();

	//$html = file_get_html('http://www.peopleperhour.com/freelance-jobs?sort=latest&page=1');
	//$nr_of_pages = (int) trim($html->find('.job-list',0)->find('header',0)->find('aside',0)->plaintext);
	
	for($page = 1; $page<=30; $page++)
	{
		$html = file_get_html('http://www.peopleperhour.com/freelance-jobs?sort=latest&page='.$page);
		foreach($html->find('div.hoverable') as $fli)
		{		
			$BoxModelFix = $fli->find('div.boxmodelfix',0);
			
			$a_job = $BoxModelFix->find('div.title',0)->find('h3',0)->find('a.job',0);
			
			$ProjectList[$i]['external_url'] = $a_job->href;
			$ProjectList[$i]['title'] = $a_job->plaintext;
			
			//$job_html = file_get_html($ProjectList[$i]['external_url']);
			//$ProjectList[$i]['description'] = $job_html->find('div.main-content',0)->find('div.content-text',0)->innertext();
			
			$items_ul = array();
			$ul_rating = $BoxModelFix->find('ul.horizontal',0);
			foreach($ul_rating->find('li') as $li)
			{
				$items_ul[] = $li;
			}
			
			$ProjectList[$i]['posted'] =  date('Y-m-d H:i:s',strtotime($items_ul[1]->find('time',0)->title));
			
			if(strpos(trim($items_ul[3]->find('span',0)->plaintext), 'proposal')!==false)
			{
				$ProjectList[$i]['bids'] = (int)trim($items_ul[3]->find('span',0)->plaintext);
			} else {
				$ProjectList[$i]['bids'] = 0;
			}
			
			$ProjectList[$i]['external_id'] = str_replace('#','',$items_ul[4]->find('span',0)->plaintext);
			
			$price_tag = $fli->find('div.details',0)->find('div.price-tag',0);
			
			if(strpos($price_tag->plaintext, 'hr')!==false)
			{
				$ProjectList[$i]['jobtype'] = 1;
			} else {
				$ProjectList[$i]['jobtype'] = 2;
			}
			
			$price = trim($price_tag->find('span',0)->plaintext);
			
			if($price==='-')
			{
				$price = 0.0;
				$ProjectList[$i]['jobtype'] = 3; //Not Available
			} else if( (strpos($price, '.')!==false) && (strpos($price, 'k')!==false) ) {
				$replace = array('$',',');
				$replace_with = array('','');
				$price = str_replace($replace,$replace_with,$price_tag->find('span',0)->title); 
			}
			
			$ProjectList[$i]['budget_low'] = $ProjectList[$i]['budget_high'] = (float)$price; 
			$i++;
		}
	}
		
	print_r($ProjectList);
?>