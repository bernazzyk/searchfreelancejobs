<?php

$matches = array(
  'recipients',
  'subject',
  'body',
);
$errors =array();
foreach ($_REQUEST as $key=>$error){
  if(in_array($key, $matches)) {
    $errors[$key] = $error;
  }
}

$smarty->assign('errors', $errors);
$content = $smarty->fetch('message_errors.tpl');

die($content);
