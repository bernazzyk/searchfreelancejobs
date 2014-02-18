<?php

//FIX POST
$_REQUEST = array_map('strip_tags', $_REQUEST);
$_REQUEST = array_map('trim', $_REQUEST);

$data = array();
//Errors array
$errors = array();

//If errors or not
if ($errors) {
  $data['success'] = false;
  $data['errors'] = $errors;
} else {
  $sync = $application->syncContract($_REQUEST['engagement'], $_REQUEST['contractor']);
  
  if (!$sync){
    $data['success'] = false;
    $data['message'] = 'Error!!!';
  } else {
    $data['success'] = true;
    $data['message'] = 'Synchronized with id: ' . $sync;
  }
}
die(json_encode($data));