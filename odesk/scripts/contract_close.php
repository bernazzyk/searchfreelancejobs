<?php

if (isset($_POST['submit'])) {
  $data = array();
  $data['params'] = array(
    'contract_reference' => $_POST['contract_reference'],
    'reason' => $_POST['reason'],
    'would_hire_againnotes' => $_POST['would_hire_againnotes'],
  );
  
  $close = $application->closeContract($_POST['contract_reference'], $data['params']);
  
  if($close){
    $data['success'] = true;
    $data['message'] = <<<EOF
<div class='alert alert-success'>
<strong>Success!</strong><br/>
Contract {$_POST['contract_reference']} closed!!
</div>
EOF;
  } else {
    $data['success'] = false;
    $data['message'] = <<<EOF
<div class='alert alert-error'>
<button type='button' class='close' data-dismiss='alert' style='font-size: 15px;'><i class='icon-remove'></i></button>
<strong>Errors!</strong><br/>
Contract {$_POST['contract_reference']} could not be closed!!
</div>
EOF;
  }
  
  die(json_encode($data));
//  die($data['message']);
  return;
}

$values = array();
$values['contract_reference'] = $_REQUEST['id'];

$smarty->assign('values', $values);
$content = $smarty->fetch('contract_close.tpl');

die($content);