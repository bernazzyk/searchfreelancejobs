<?php

class Helper
{

  static public function printErrors($errors, $format = 'break')
  {
    $output = '';

    switch ($format) {
      case 'list':
        $output .= '<ul>';
        foreach ($errors as $error) {
          $output .= '<li>' . $error . '</li>';
        }
        $output .= '</ul>';
        break;
      default:
        foreach ($errors as $error) {
          $output .= $error . '<br/>';
        }
    }
    return $output;
  }

  static public function makeBitlyUrl($url, $login, $appkey, $format = 'txt')
  {
    //create the URL
    $connectURL = 'http://api.bit.ly/v3/shorten?login=' . $login . '&apiKey=' . $appkey . '&uri=' . urlencode($url) . '&format=' . $format;
    return self::curl_get_result($connectURL);
  }

  static public function isXmlHttpRequest()
  {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
      return true;
    }
    return false;
  }

  static private function curl_get_result($url)
  {
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
  }

}