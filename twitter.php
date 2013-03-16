<?php

include_once "/includes/OAuth/OAuthStore.php";
include_once "/includes/OAuth/OAuthRequester.php";

// register at http://twitter.com/oauth_clients and fill these two 
define("TWITTER_CONSUMER_KEY", "cPXQW25pQJ42HNNCLfFJaQ");
define("TWITTER_CONSUMER_SECRET", "3TXwaK87ehtyNM4r2DSHR9NyeyKhOeYxRkDzJcYFwuk");


define("TWITTER_OAUTH_HOST","https://api.twitter.com");
define("TWITTER_REQUEST_TOKEN_URL", TWITTER_OAUTH_HOST . "/oauth/request_token");
define("TWITTER_AUTHORIZE_URL", TWITTER_OAUTH_HOST . "/oauth/authorize");
define("TWITTER_ACCESS_TOKEN_URL", TWITTER_OAUTH_HOST . "/oauth/access_token");
define("TWITTER_PUBLIC_TIMELINE_API", TWITTER_OAUTH_HOST . "/statuses/public_timeline.json");
define("TWITTER_UPDATE_STATUS_API", TWITTER_OAUTH_HOST . "/statuses/update.json");

define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"])); 

// Twitter test
$options = array('consumer_key' => TWITTER_CONSUMER_KEY, 'consumer_secret' => TWITTER_CONSUMER_SECRET);
OAuthStore::instance("2Leg", $options);

try
{
        // Obtain a request object for the request we want to make
        $request = new OAuthRequester(TWITTER_REQUEST_TOKEN_URL, "POST");
        $result = $request->doRequest(0, array(CURLOPT_SSL_VERIFYPEER => false));
        parse_str($result['body'], $params);
		
		echo '<pre>';
		print_r($result['body']);
		echo '</pre>';

        // now make the request. 
    $request = new OAuthRequester(TWITTER_PUBLIC_TIMELINE_API, 'GET', $params);
    $result = $request->doRequest(0, array(CURLOPT_SSL_VERIFYPEER => false));
	
	echo "<pre>";
	print_r($result);
	echo "</pre>";
}
catch(OAuthException2 $e)
{
	echo "Exception" . $e->getMessage();
}


?>