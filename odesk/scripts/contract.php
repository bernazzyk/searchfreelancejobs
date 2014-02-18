<?php
$content = '';
$contract = $application->getEngagement($_REQUEST['id']);
$content = var_dump($contract);
die($content);