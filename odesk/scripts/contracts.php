<?php
$job = $_REQUEST['job'] ? $_REQUEST['job'] : null;
$smarty->assign('status', $_REQUEST['status'] ? (string) $_REQUEST['status'] : 'all');
if ($job) {
  $job_data = $application->getJob($job);
  $smarty->assign('job', $job_data);
}
$content = $smarty->fetch('contracts.tpl');