<?php

$body_text = "";
$results_per_page = 25;
 
 
// What page of data are we displaying? Defaults to 1.
$current_page = 1;
if (isset($_GET["currentPage"])) { 
    is_numeric($_GET["currentPage"]) or die ("ERROR: currentPage parameter MUST be an integer!");
    intval($_GET["currentPage"]) > 0 or die ("ERROR: currentPage parameter MUST be greater than 0!");
    $current_page = $_GET["currentPage"];
}
 
// Open eLance URL. 
$url = "http://api.elance.com/api/search/jobs?eauth_consumer_key=" .         
    "8dfc476b57fee5b7dc08cc0f1d5a0fcdab409b3c&keywords=php%20mysql&" . 
    "sortCol=numProposals&sortOrder=asc&page=" . $current_page;
 
if (!($r = @curl_init($url))) {
    die("Cannot initialize cUrl session. Is cUrl enabled for your PHP installation?");
}
 
// Set cUrl to return text as a variable, instead of directly to the browser.
$curl_options = array (
    CURLOPT_FRESH_CONNECT => 1,
    CURLOPT_RETURNTRANSFER => 1
    );
curl_setopt_array($r, $curl_options);
 
// Convert response to JSON.
$json_txt = curl_exec($r);
if ($json_txt == false) {
    die("Cannot retrieve Elance API URL using cUrl. URL: " . $url);
}
curl_close($r); 
 
$json_response = json_decode($json_txt);
if ($json_response == null) {
    die("Retrieved Elance API call, but the response was not valid JSON code.");
}
 
// Generate a success or error response depending on the value of rcode.
if (!$json_response->rcode) { 
    $body = "Call did not complete. JSON error returned.";
} else {
    if ($json_response->data->numResults == 0) {
        $body = "No records returned.";
    } else {
        // Display paging information. 
        $max_result_num = $json_response->data->page * $results_per_page;
        $first_rec_num = $max_result_num - ($results_per_page - 1);
        $body = "<div style=\"width:75%;position:relative;\">\n";
        $body .= "<span style=\"width:37%;position:absolute;left:0;\">Displaying records " . $first_rec_num . " - " . (($json_response->data->page - 1)* $results_per_page + $json_response->data->numResults) . " out of " . $json_response->data->totalResults . " total results</span>\n";
 
        // Display paging controls. 
        $body .= "<span style=\"width:37%;position:absolute; right:0; text-align:right;\">";
        $sep_text = "";
        if ($json_response->data->page > 1) {
            $body .= "<a href=\"elance.php?currentPage=" . ($json_response->data->page - 1) . "\">< Previous</a>&nbsp;";
            $sep_text = "|&nbsp;";
        }
        if ($json_response->data->page != $json_response->data->totalPages) {
            $body .= $sep_text;
            $body .= "<a href=\"elance.php?currentPage=" . ($json_response->data->page + 1) . "\">Next ></a>&nbsp;";
        }
        $body .= "</span>\n";
 
        $body .= "</div>\n";
 
	$body .= "<div style=\"padding-top:20px;\"><table style=\"width:75%;table-layout:fixed;\">\n"; 
        $body .= "<tr style=\"background-color:#cccccc;font-weight:bold;\">
            <th style=\"width:10%;\">Job ID</th>
            <th style=\"width:30%;\">Job Title</th>
            <th style=\"width:20%;\">Budget</th>
            <th style=\"width:30%;\">Description</th>
            <th style=\"width:10%;\"># of Bids</th>
            </tr>";
 
        foreach ($json_response->data->pageResults as $job_obj) {
            $body .= "<tr class=\"basicRow\">\n";
            $body .= "<td>" . $job_obj->jobId . "</td>\n";
            $body .= "<td><a href=\"" . $job_obj->jobURL . "\">" . $job_obj->name . "</a></td>\n";
            $body .= "<td>" . $job_obj->budget . "</td>\n";
            $body .= "<td style=\"word-wrap:break-word;\">" . word_trim($job_obj->description, 50, TRUE) . "</td>\n";
            $body .= "<td style=\"text-align:center;\">" . $job_obj->numProposals . "<td>\n";
            $body .= "</tr>\n";
        }
 
        $body .= "</table></div>";
    }
}
 
// word_trim courtesy of Jeff Robbins @ Lullabot( http://www.lullabot.com/articles/trim-a-string-to-a-given-word-count)
function word_trim($string, $count, $ellipsis = FALSE){
  $words = explode(' ', $string);
  if (count($words) > $count){
    array_splice($words, $count);
    $string = implode(' ', $words);
    if (is_string($ellipsis)){
      $string .= $ellipsis;
    }
    elseif ($ellipsis){
      $string .= '&hellip;';
    }
  }
  return $string;
}
 
?>
 
<html>
 
<head>
<title>Elance API Sample using cUrl and JSON in PHP</title>
</head>
 
<style>
.basicRow {
    vertical-align:top;
}
</style>
 
<body>
 
<div style="width:100%; text-align:center;">
<h1>Elance API Search Results for Low-Bid PHP/mySql Jobs</h1>
</div>
 
<?php 
    echo($body); 
?>
 
</body>
 
</html>