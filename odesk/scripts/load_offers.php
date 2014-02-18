<?php

$last = $_REQUEST['last'] ? $_REQUEST['last'] : 0;
$job = $_REQUEST['job'] ? $_REQUEST['job'] : null;

$offers_data = $application->getOffers($last, $job);

$total = $offers_data->offers->lister->total_items;
$offers = $offers_data->offers->offer;

//get country of applicant
if (is_array($offers)){
  $ids = '';
  foreach ($offers as $key => $offer) {
    $ids .= str_replace('https://www.odesk.com/users/', '', $offer->provider__profile_url).';';
  }
  $profiles = $application->getProviders(substr($ids, 0, -1));
  foreach ($profiles->profiles->profile as $key => $profile){
    $offers[$key]->country = $profile->dev_country;
  }
} else {
  if($total > 0 ) {
    $profile = $application->getProvider(str_replace('https://www.odesk.com/users/', '', $offers->provider__profile_url));
    $offers->country = $profile->profile->dev_country;
  }
}
//echo '<code class="span12">';
//var_dump($offers);
//echo '</code>';
//die();
//assign values
$smarty->assign('total', $total);
$smarty->assign('job', $job);
$smarty->assign('last', $last);

if (is_array($offers)){
  $smarty->assign('offers', $offers);
} else {
  $smarty->assign('offers', array($offers));
}
$content = $smarty->fetch('load_offers.tpl');

die($content);