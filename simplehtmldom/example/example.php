<?php
include_once('../simple_html_dom.php');
$i=0;
$FreeLancerInfo = array();
for($page=1;$page<13;$page++)
{
	$html = file_get_html('http://www.peopleperhour.com/find/rss+feeds#scroll=1&sort=latest&page='.$page);
	//echo $html;
	
	foreach($html->find('div.freelancer-list-item') as $fli)
	{
		$a_img = $fli->find('a.freelancer-image', 0);
		$freelancer_img = $a_img->find('img', 0);
		$FreeLancerInfo[$i]['image'] = $freelancer_img->src;
		
		$BoxModelFix = $fli->find('div.boxmodelfix',0);
		$a_freelancer = $BoxModelFix->find('h3',0)->find('a.freelancer',0);
		$FreeLancerInfo[$i]['url'] = $a_freelancer->href;
		$FreeLancerInfo[$i]['name'] = $a_freelancer->plaintext;
		$FreeLancerInfo[$i]['job_title'] = $BoxModelFix->find('div.job-title',0)->plaintext;
		
		$ul_rating = $BoxModelFix->find('ul.horizontal',0);
		foreach($ul_rating->find('li') as $li)
		{
			if($li->class=='rating')
			{
				$FreeLancerInfo[$i]['rating'] = $li->plaintext;
			}
			else {
				$FreeLancerInfo[$i]['city'] = $li->find('span.flag',0)->plaintext;
			}
		}
		
		$price_tag = $fli->find('div.last',0)->find('div.price-tag',0)->find('span',0)->plaintext;
		$FreeLancerInfo[$i]['price'] = (int)$price_tag; 
		
		$i++;
	}
}
	print $i;
	print_r($FreeLancerInfo);
    
?>