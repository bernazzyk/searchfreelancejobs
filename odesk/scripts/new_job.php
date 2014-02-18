<?php

//If form is posted:
if ($_POST['submit']) {
  
  //FIX POST
  $_POST = array_map('strip_tags', $_POST);
  $_POST = array_map('trim', $_POST);
  
  //Errors array
  $errors = array();
  
  //Validation
  if (!$_POST['title']){
    $errors['title'] = 'Title is required.';
  }
  if (!$_POST['description']){
    $errors['description'] = 'Description is required.';
  }
  if (!$_POST['category'] || $_POST['category'] == ""){
    $errors['category'] = 'Category is required.';
  }
  if (!$_POST['subcategory'] || $_POST['subcategory'] == ""){
    $errors['subcategory'] = 'Subcategory is required.';
  }
  if (!$_POST['end_date'] || $_POST['end_date'] == ""){
    $errors['end_date'] = 'End date is required.';
  } else {
    if ($_POST['end_date'] && !preg_match("/^([0-9]{2})-([0-9]{2})-([0-9]{4})$/", $_POST['end_date'])) {
      $errors['end_date'] = 'Not valid date! eg: 03-31-2012.';
    } else {
      $ed = explode("-", $_POST['end_date']);
      if (!checkdate($ed[0], $ed[1], $ed[2])){
        $errors['end_date'] = 'Not valid date! eg: 03-31-2012.';
      }
    }
  }
  if ($_POST['start_date'] && $_POST['start_date'] != "" && !preg_match("/^([0-9]{2})-([0-9]{2})-([0-9]{4})$/", $_POST['start_date'])) {
    $errors['start_date'] = 'Not valid date format! eg: 03-31-2012.';
  } else {
    $sd = explode("-", $_POST['start_date']);
    if ($_POST['start_date'] && $_POST['start_date'] != "" && !checkdate($sd[0], $sd[1], $sd[2])){
      $errors['start_date'] = 'Not valid date!';
    }
  }
  if (!$_POST['budget'] || $_POST['budget'] == ""){
    $errors['budget'] = 'Budget is required.';
  }
  if ($_POST['budget'] && !is_numeric($_POST['budget'])){
    $errors['budget'] = 'Budget must be a number.';
  }
  if ($_POST['budget'] && $_POST['budget'] < 5){
    $errors['budget'] = 'Minimum budget is 5 US Dollars.';
  }
  
  //If errors or not
  if ($errors) {
	$job = $application->postOffer();
	var_dump ($job);
    $smarty->assign('errors', $errors);
    $smarty->assign('values', $_POST);
  } else {
    //POST THE JOB!!!
    $job = $application->postJob($_POST);
    //if ok
    if ($job->job){
      $smarty->assign('message', array('type'=> 'success', 'body'=>'Job created successfully with ref: '. $job->job->reference));
    }
    else{
      $er = json_decode($job->response);
      $smarty->assign('errors', $er->error);
    }
  }
}

//Fetch categories from oDesk to populate fields.
$response = $api->get_request('http://www.odesk.com/api/profiles/v1/metadata/categories.json');
$data = json_decode($response);
$categories = $data->categories;
$smarty->assign('categories', $categories);

//date fields initialization
$smarty->assign('todayte', date('m-d-Y', time()));

$content = $smarty->fetch('new_job.tpl');