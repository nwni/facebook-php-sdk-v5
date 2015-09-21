<?php
session_start();
require_once __DIR__ . '/src/Facebook/autoload.php';

$fb = new Facebook\Facebook([
  'app_id' => 'APP_ID',
  'app_secret' => 'APP_SECRET',
  'default_graph_version' => 'v2.4',
  ]);

$helper = $fb->getCanvasHelper();
	
try {
	if (isset($_SESSION['facebook_access_token'])) {
		$accessToken = $_SESSION['facebook_access_token'];
	} else {
  		$accessToken = $helper->getAccessToken();
	}
} catch(Facebook\Exceptions\FacebookResponseException $e) {
 	// When Graph returns an error
 	echo 'Graph returned an error: ' . $e->getMessage();
  	exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
 	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
  	exit;
 }

if (isset($accessToken)) {

	if(isset($_SESSION['facebook_access_token'])) {
		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	} else {
	  	// Logged in!
	  	$_SESSION['facebook_access_token'] = (string) $accessToken;

	  	// OAuth 2.0 client handler
		$oAuth2Client = $fb->getOAuth2Client();

		// Exchanges a short-lived access token for a long-lived one
		$longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($_SESSION['facebook_access_token']);

		$fb->setDefaultAccessToken($_SESSION['facebook_access_token']);
	}

	// validating the access token
	try {
	$request = $fb->get('/me?fields=name,email');
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	// When Graph returns an error
	if ($e->getCode() == 190) {
		$helper = $fb->getRedirectLoginHelper();
		$permissions = ['email', 'publish_actions']; // optional
		$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/APP_NAMESPACE/', $permissions);
		echo "<script>window.top.location.href='".$loginUrl."'</script>";
	}
	exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
	}

	// getting basic info about logged-in user
	try {
	$request = $fb->get('/me?fields=name,email');
	$profile = $request->getGraphUser();
	} catch(Facebook\Exceptions\FacebookResponseException $e) {
	// When Graph returns an error
	if ($e->getCode() == 190) {
		$helper = $fb->getRedirectLoginHelper();
		$permissions = ['email', 'publish_actions']; // optional
		$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/APP_NAMESPACE/', $permissions);
		echo "<script>window.top.location.href='".$loginUrl."'</script>";
	}
	exit;
	} catch(Facebook\Exceptions\FacebookSDKException $e) {
	// When validation fails or other local issues
	echo 'Facebook SDK returned an error: ' . $e->getMessage();
	exit;
	}

	echo $profile['name'];

  	// Now you can redirect to another page and use the access token from $_SESSION['facebook_access_token']
} else {
	$helper = $fb->getRedirectLoginHelper();
	$permissions = ['email', 'publish_actions']; // optional
	$loginUrl = $helper->getLoginUrl('https://apps.facebook.com/APP_NAMESPACE/', $permissions);

	echo "<script>window.top.location.href='".$loginUrl."'</script>";
}
