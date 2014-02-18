<?php
if (isset($_POST['submit'])) {
  $data = array();
  $data['params'] = array(
    'job' => $_POST['job'],
    'reason_code' => $_POST['reason_code'],
  );
  
  $cancel = $application->cancelJob($_POST['job'], $data['params']);
  
  if($cancel){
    $data['success'] = true;
    $data['message'] = <<<EOF
<div class='alert alert-success'>
<strong>Success!</strong><br/>
Job {$_POST['job']} canceled!!
</div>
EOF;
  } else {
    $data['success'] = false;
    $data['message'] = <<<EOF
<div class='alert alert-error'>
<button type='button' class='close' data-dismiss='alert' style='font-size: 15px;'><i class='icon-remove'></i></button>
<strong>Errors!</strong><br/>
Job {$_POST['job']} could not be canceled!!
</div>
EOF;
  }
  
  die(json_encode($data));
//  die($data['message']);
  return;
}

$values = array();
$values['job'] = $_REQUEST['id'];

$smarty->assign('values', $values);
$content = $smarty->fetch('job_cancel.tpl');

die($content);