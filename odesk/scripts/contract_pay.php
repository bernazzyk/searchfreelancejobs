<?php
if (isset($_POST['submit'])) {
  $data = array();
  $data['params'] = array(
    'engagement__reference' => $_POST['contract_reference'],
    'charge_amount' => $_POST['charge_amount'],
    'comments' => $_POST['comments'],
    'notes' => $_POST['notes'],
  );
  $validation_errors = array();
  
  if(!$_POST['contract_reference']) {
    $validation_errors['contract_reference'] = 'Contract reference is required!';
  }
  if(!$_POST['charge_amount']) {
    $validation_errors['charge_amount'] = 'Amount of money is required!';
  }
  if(!$_POST['comments']) {
    $validation_errors['comments'] = 'Comments is required!';
  }
  
  if (count($validation_errors)) {
    $data['success'] = false;
    $data['message'] = sprintf(<<<EOF
<div class='alert alert-error'>
<button type='button' class='close' data-dismiss='alert' style='font-size: 15px;'><i class='icon-remove'></i></button>
<strong>Errors!</strong><br/>
%s
</div>
EOF
   ,$helper->printErrors($validation_errors)
);
    die(json_encode($data));
    return;
  }
  
  $pay = $application->payContract($data['params']);
  
  if($pay){
    $data['success'] = true;
    $data['message'] = <<<EOF
<div class='alert alert-success'>
<strong>Success!</strong><br/>
Contract {$_POST['contract_reference']} paid with $ {$_POST['charge_amount']}!!
</div>
EOF;
  } else {
    $data['success'] = false;
    $data['message'] = <<<EOF
<div class='alert alert-error'>
<button type='button' class='close' data-dismiss='alert' style='font-size: 15px;'><i class='icon-remove'></i></button>
<strong>Errors!</strong><br/>   
Contract {$_POST['contract_reference']} could not be paid!!
</div>
EOF;
  }
  
  die(json_encode($data));
//  die($data['message']);
  return;
}

$values = array();
$values['contract_reference'] = $_REQUEST['id'];
$values['charge_amount'] = $_REQUEST['amount'];

$smarty->assign('values', $values);
$content = $smarty->fetch('contract_pay.tpl');

die($content);