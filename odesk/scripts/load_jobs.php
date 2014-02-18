<?php

$last = $_REQUEST['last'] ? $_REQUEST['last'] : 0;
$status = $_REQUEST['status'] ? (string) $_REQUEST['status'] : 'open';

$jobs_data = $application->getJobs($last, $status);

$total = $jobs_data->jobs->lister->total_items;
$jobs = $jobs_data->jobs->job;
//echo '<code class="span12">';
//var_dump($jobs);
//echo '</code>';
//die();
$smarty->assign('total', $total);
$smarty->assign('last', $last);

if (is_array($jobs)){
  $smarty->assign('jobs', $jobs);
} else {
  $smarty->assign('jobs', array($jobs));
}
$content = $smarty->fetch('load_jobs.tpl');

die($content);