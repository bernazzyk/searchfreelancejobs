<?php
class Application_Model_TimeOutput extends Application_Model_Freelancer
{
	public function GetOutputTime($time) {
		$output_time = '';

		$hour_txt = ' hour ';
		$hours_txt = ' hours ';
		$min_txt = ' min ';
		$less_txt = ' less than one minut';
		$ago_txt = ' ago';
	
		$now_time = time();
		$my_time = strtotime($time);
		$diff_seconds = abs($now_time - $my_time);
	
		if($diff_seconds < (24*60*60)) {
			$hours = floor($diff_seconds/(1*60*60));
			$minutes = floor(($diff_seconds - ($hours*60*60))/(1*60));
		
			if($hours > 0) {
				if($hours == 1) {
					$output_time .= $hours . $hour_txt;
				} else {
					$output_time .= $hours . $hours_txt;
				}
			} else {
				$output_time .= '';
			}
		
			if($minutes > 0) {
				if($minutes == 1) {
					$output_time .= $minutes . $min_txt;
				} else {
					$output_time .= $minutes . $min_txt;
				}
			} else {
				if($hours < 1) {
					$output_time .= $less_txt;
					$output_time .= $minutes . $min_txt;
				}
			}
		
			$output_time .= $ago_txt;
		
		} else {			
			$output_time_temp = date("j M Y", strtotime($time));
			//$output_time = $this->convert_month($Language, $output_time_temp);
		}
	
		return $output_time_temp;    
	}
	
	
public function elapsed_time($timestamp, $precision = 2) {
// date_default_timezone_set("UTC");
$utc_str = date("M d Y H:i:s", time());
$utc = strtotime($utc_str);

$time = $utc - $timestamp;
//   print date('Y-m-d H:i:s', $gmtime);die;
  // $time = time() - 1353819167;
  
  //1354019167 
  //$a = array('decade' => 315576000, 'year' => 31557600, 'month' => 2629800, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'min' => 60, 'sec' => 1);
  $a = array('month' => 2629800, 'day' => 86400, 'hour' => 3600, 'min' => 60, 'sec' => 1);
  $i = 0;
    foreach($a as $k => $v) {
      $$k = floor($time/$v);
      if ($$k) $i++;
      $time = $i >= $precision ? 0 : $time - $$k * $v;
      $s = $$k > 1 ? 's' : '';
      $$k = $$k ? $$k.' '.$k.$s.' ' : '';
      @$result .= $$k;
    }
  return $result ? $result.'ago' : '1 sec to go';
}

public function time_left($timestamp, $precision = 3) {
	$utc_str = date("M d Y H:i:s");
	$utc = strtotime($utc_str);
  $time = $timestamp - $utc;
  //print $timestamp; die;
 if($timestamp == '')
 {
	return 'N/A';
 }
 if($time>0) 
 {
  //1354019167 
  //$a = array('decade' => 315576000, 'year' => 31557600, 'month' => 2629800, 'week' => 604800, 'day' => 86400, 'hour' => 3600, 'min' => 60, 'sec' => 1);
  $a = array('month' => 2629800, 'day' => 86400, 'hour' => 3600, 'min' => 60, 'sec' => 1);
  $i = 0;
    foreach($a as $k => $v) {
      $$k = floor($time/$v);
      if ($$k) $i++;
      $time = $i >= $precision ? 0 : $time - $$k * $v;
      $s = $$k > 1 ? 's' : '';
      $$k = $$k ? $$k.' '.$k.$s.' ' : '';
      @$result .= $$k;
    }
  return $result ? $result : '';
  }
  else return 'expired';
}
	
	public function smartdate($timestamp) {
		$diff = time() - $timestamp;
	 
		if ($diff <= 0) {
			return 'Now';
		}
		else if ($diff < 60) {
			return $this->grammar_date(floor($diff), ' second(s) ago');
		}
		else if ($diff < 60*60) {
			return $this->grammar_date(floor($diff/60), ' minute(s) ago');
		}
		else if ($diff < 60*60*24) {
			return $this->grammar_date(floor($diff/(60*60)), ' hour(s) ago');
		}
		else if ($diff < 60*60*24*30) {
			return $this->grammar_date(floor($diff/(60*60*24)), ' day(s) ago');
		}
		else if ($diff < 60*60*24*30*12) {
			return $this->grammar_date(floor($diff/(60*60*24*30)), ' month(s) ago');
		}
		else {
			return $this->grammar_date(floor($diff/(60*60*24*30*12)), ' year(s) ago');
		}
	}
 
 
	public function grammar_date($val, $sentence) {
		if ($val > 1) {
			return $val.str_replace('(s)', 's', $sentence);
		} else {
			return $val.str_replace('(s)', '', $sentence);
		}
	}
}