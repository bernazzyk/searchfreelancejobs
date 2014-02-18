<?php
 
$current_page = 1;
$results_per_page = 50;
if (isset($_GET["currentPage"])) { 
    is_numeric($_GET["currentPage"]) or die ("ERROR: currentPage parameter MUST be an integer!");
    intval($_GET["currentPage"]) > 0     or die ("ERROR: currentPage must be greater than 0!");
    $current_page = $_GET["currentPage"];
}
 
if (isset($_GET["resultsPerPage"])) { 
    is_numeric($_GET["resultsPerPage"]) or die ("ERROR: resultsPerPage parameter MUST be an integer!");
    intval($_GET["resultsPerPage"]) > 0     or die ("ERROR: resultsPerPage must be greater than 0!");
    $results_per_page = $_GET["resultsPerPage"];	
}
 
$access_token = "4f21faa83340a00328000001|4905439|mZGy-AYTNkyztuEdQHfaRw";
 
// Open eLance URL. 
/*
$url = "http://api.elance.com/api2/jobs?access_token=" . $access_token . "&keywords=php%20mysql&" . 
    "sortCol=numProposals&sortOrder=asc&page=" . $current_page . "&rpp=" . $results_per_page;*/
	
$url = "http://api.elance.com/api2/jobs?access_token=" . $access_token . "&" . 
    "sortCol=postedDate&sortOrder=desc&page=" . $current_page . "&rpp=" . $results_per_page;
 
if (($r = @curl_init($url)) == false) {
    header("HTTP/1.1 500", true, 500);
    die("Cannot initialize cUrl session. Is cUrl enabled for your PHP installation?");
}
 
// Set cUrl to return text as a variable, instead of directly to the browser.
$curl_options = array (
    CURLOPT_FRESH_CONNECT => 1,
    CURLOPT_RETURNTRANSFER => 1
    );
curl_setopt_array($r, $curl_options);
 
// Access API, and check results.
$json_txt = curl_exec($r);
if (curl_errno($r) > 0) {
    header("HTTP/1.1 500", true, 500);
    die();
} else {
    $http_response = intval(curl_getinfo($r, CURLINFO_HTTP_CODE));
    if ($http_response != 200) {
        // Pass on any descriptive error information from the Elance server to 
        // the client.
        header("HTTP/1.1 " . $http_response, true, $http_response);
        header("Content-Type: application/json", true);
        echo($json_txt);
        flush();
        die();
    }
}
 
if ($json_txt == false) {
    header("HTTP/1.1 500", true, 500);
    die("Cannot retrieve Elance API URL using cUrl. URL: " . $url);
}
curl_close($r); 
 
header("Content-Type: application/json", true);

//echo $json_txt->data->totalResults;

$ProjectsList = (json_decode($json_txt, true));

print_r($ProjectsList['data']['pageResults']);

/*foreach($ProjectsList['data']['pageResults'] as $key=> $Values)
{
	//id 	external_url 	external_id 	title 	description 	posted 	ends 	budget_low 	budget_high 	platform_id 	active
	print_r($Values);//[''];
}
*/

//echo $json_txt;
 
?>