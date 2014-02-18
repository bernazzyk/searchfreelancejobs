<?php

//FIX POST
$_POST = array_map('strip_tags', $_POST);
$_POST = array_map('trim', $_POST);

$data = array();
//Errors array
$errors = array();

//Validation
if (!$_POST['subject']){
  $errors['subject'] = 'subject is required.';
}
if (!$_POST['body']){
  $errors['body'] = 'body is required.';
}
if (!$_POST['recipients'] || $_POST['recipients'] == ""){
  $errors['recipients'] = 'recipients is required.';
}

//If errors or not
if ($errors) {
  $data['success'] = false;
  $data['errors'] = $errors;
} else {
  //POST THE JOB!!!
  $message = $application->sendMessage($_POST['recipients'], $_POST['subject'], $_POST['body']);
  
  if (!$message->thread_id){
    $data['success'] = false;
    $data['errors']['Error'] = 'API return error '.$message->info->http_code;
  } else {
    $data['success'] = true;
    $data['message'] = $message;
  }
}
die(json_encode($data));