<?php

//$response = $api->get_request('https://www.odesk.com/api/hr/v2/companies.json');
//$data = json_decode($response);
//$company = $data->companies[0];
//$response = $api->get_request('https://www.odesk.com/api/hr/v2/companies/'.$company->reference.'/teams.json');
//$data2 = json_decode($response);
//
////var_dump($data2);
////die();
//
//
//$jobs_data = $api->get_request('https://www.odesk.com/api/hr/v2/jobs.json', array(
//  'buyer_team__reference' => $company->reference,
//  'order_by' => 'created_time;DESC',
////  'status' => 'open',
////  'page' => '7;5'
//  )
//);
//$jobs_data = json_decode($jobs_data);
//
//$total = $jobs_data->jobs->lister->total_items;
//$paging = $jobs_data->jobs->lister->paging;
//$jobs = $jobs_data->jobs->job;
////var_dump($total);
////var_dump($paging);
////var_dump($jobs_data->jobs->lister);
////var_dump($jobs_data);
////var_dump($jobs);
////die();
//
//$smarty->assign('total', $total);
//$smarty->assign('paging', $paging);
//$smarty->assign('jobs', $jobs);
//
//var_dump($jobs);
//die();

$smarty->assign('status', $_REQUEST['status'] ? (string) $_REQUEST['status'] : 'open');
$content = $smarty->fetch('jobs.tpl');