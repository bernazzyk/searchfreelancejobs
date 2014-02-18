<?php
/**
 * @file
 * User has successfully authenticated with Twitter. Access tokens saved to session and DB.
 */

/* Load required lib files. */
session_start();
require_once('twitteroauth/twitteroauth.php');
require_once('config.php');

/* If access tokens are not available redirect to connect page. */
if (empty($_SESSION['access_token']) || empty($_SESSION['access_token']['oauth_token']) || empty($_SESSION['access_token']['oauth_token_secret'])) {
    header('Location: ./clearsessions.php');
}
/* Get user access tokens out of the session. */
$access_token = $_SESSION['access_token'];

/* Create a TwitterOauth object with consumer/user tokens. */
$connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $access_token['oauth_token'], $access_token['oauth_token_secret']);

/* If method is set change API call made. Test is called by default. */
$content = $connection->get('account/verify_credentials');
//$user = $connection->get('users/show', array('screen_name' => 'telfus'));

$_SESSION['twitter_fm']['screen_name'] = $content->screen_name;
$_SESSION['twitter_fm']['name'] = $content->name;
$_SESSION['twitter_fm']['id'] = $content->id;
$_SESSION['twitter_fm']['profile_image_url'] = $content->profile_image_url;

header('Location: /auth/twitter/?screen_name='.$content->screen_name.'&name='.$content->name.'&id='.$content->id. '&img_url='.$content->profile_image_url);

//var_dump($user);
//var_dump($content);


/* Some example calls */
//$connection->get('users/show', array('screen_name' => 'abraham'));
//$connection->post('statuses/update', array('status' => date(DATE_RFC822)));
//$connection->post('statuses/destroy', array('id' => 5437877770));
//$connection->post('friendships/create', array('id' => 9436992));
//$connection->post('friendships/destroy', array('id' => 9436992));

/* Include HTML to display on the page */
//include('html.inc');
