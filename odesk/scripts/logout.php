<?php

$params = array(
  'api_key' => OD_API_KEY,
  'api_token' => $_COOKIE['odesk_api_token'],
);

$resp = $api->delete_request('https://www.odesk.com/api/auth/v1/keys/token.json', $params);

setcookie('odesk_api_token', null);

$result = null;

if ($_REQUEST['od'] == 1) {
  
  $ch = curl_init();
  $fields = array(
    'action' => 'logout'
  );
  foreach ($fields as $key => $value) {
    $fields_string .= $key . '=' . $value . '&';
  }
  rtrim($fields_string, '&');

  $url = 'https://www.odesk.com/login.php';
  
  //set the url, number of POST vars, POST data
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, count($fields));
  curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
//  curl_setopt($post, CURLOPT_RETURNTRANSFER, 1);

  //execute post
  $result = curl_exec($ch);
  //close connection
  curl_close($ch);
}
session_start();
session_destroy();

header("Location: ./?action=index");
