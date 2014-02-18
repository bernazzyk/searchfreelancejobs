<?php

$last = $_REQUEST['last'] ? $_REQUEST['last'] : 0;
$status = $_REQUEST['status'] ? (string) $_REQUEST['status'] : 'active';
$job = $_REQUEST['job'] ? $_REQUEST['job'] : null;

$eng_data = $application->getEngagements($last, $status, $job);

$total = $eng_data->engagements->lister->total_count;
$total_i = $eng_data->engagements->lister->total_items;
$engagements = $eng_data->engagements->engagement;
//var_dump($engagements);
//die();
//assign values
$smarty->assign('total', $total);
$smarty->assign('total_i', $total_i);
$smarty->assign('job', $job);
$smarty->assign('last', $last);
$smarty->assign('team_ref', $application->getCompany());

if (is_array($engagements)){
  $smarty->assign('engagements',  $application->getEngagementsLocalStatus($engagements));
} else {
  $smarty->assign('engagements',  $application->getEngagementsLocalStatus(array($engagements)));
}
$content = $smarty->fetch('load_engagements.tpl');

die($content);