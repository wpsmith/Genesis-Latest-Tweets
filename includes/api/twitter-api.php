<?php
/**
 * Twitter API Wordpress library.
 * @author Tim Whitlock <@timwhitlock>
 */

global $GLTW_API;

class GLTW_API {
	public static $clients = array();
	
	function __construct() {
	}
	
	/**
	 * Call a Twitter API GET method.
	 * 
	 * @param string endpoint/method, e.g. "users/show"
	 * @param array Request arguments, e.g. array( 'screen_name' => 'timwhitlock' )
	 * @return array raw, deserialised data from Twitter
	 * @throws TwitterApiException
	 */ 
	public static function api_get( $path, array $args = array() ){
gltw_log();
	    $Client = GLTW_API::api_client();
	    return $Client->call( $path, $args, 'GET' );
	} 
	
	/**
	 * Call a Twitter API POST method.
	 * 
	 * @param string endpoint/method, e.g. "users/show"
	 * @param array Request arguments, e.g. array( 'screen_name' => 'timwhitlock' )
	 * @return array raw, deserialised data from Twitter
	 * @throws TwitterApiException
	 */ 
	public static function api_post( $path, array $args = array() ){
gltw_log();
	    $Client = GLTW_API::api_client();
	    return $Client->call( $path, $args, 'POST' );
	} 
	
	/**
	 * Enable caching of Twitter API responses using APC
	 * @param int Cache lifetime in seconds
	 * @return TwitterApiClient
	 */
	public static function api_enable_cache( $ttl ){
gltw_log();
	    $Client = GLTW_API::api_client();
	    return $Client->enable_cache( $ttl );
	}
	
	/**
	 * Disable caching of Twitter API responses
	 * @return TwitterApiClient
	 */
	public static function api_disable_cache( $ttl ){
gltw_log();
	    $Client = GLTW_API::api_client();
	    return $Client->disable_cache();
	}
	
	/**
	 * Get plugin local base directory in case __DIR__ isn't available (php<5.3)
	 */
	public static function api_basedir(){
gltw_log();
	    static $dir;
	    isset($dir) or $dir = dirname(__FILE__).'/..';
	    return $dir;    
	}    
	
	/**
	 * Get fully configured and authenticated Twitter API client.
	 * @return TwitterApiClient
	 */ 
	public static function api_client( $id = null ){
gltw_log();
	    if( ! isset($clients[$id]) ){
	        $clients[$id] = TwitterApiClient::create_instance( is_null($id) );
	    }
	    return $clients[$id];
	}
	
	/**
	 * Contact Twitter for a request token, which will be exchanged for an access token later.
	 * @return TwitterOAuthToken Request token
	 */
	public static function api_oauth_request_token( $consumer_key, $consumer_secret, $oauth_callback = 'oob' ){
gltw_log();
gltw_pr( 'method params', $consumer_key, $consumer_secret, $oauth_callback );
	    $Client = GLTW_API::api_client('oauth');
gltw_pr( $Client );
	    $Client->set_oauth( $consumer_key, $consumer_secret );
gltw_pr( $Client );		
	    $params = $Client->oauth_exchange( OAUTH_REQUEST_TOKEN_URL, compact('oauth_callback') );
gltw_pr( $params );
	    return new TwitterOAuthToken( $params['oauth_token'], $params['oauth_token_secret'] );
	}
	
	/**
	 * Exchange request token for an access token after authentication/authorization by user
	 * @return TwitterOAuthToken Access token
	 */
	public static function api_oauth_access_token( $consumer_key, $consumer_secret, $request_key, $request_secret, $oauth_verifier ){
gltw_log();
	    $Client = GLTW_API::api_client('oauth');
	    $Client->set_oauth( $consumer_key, $consumer_secret, $request_key, $request_secret );     
	    $params = $Client->oauth_exchange( OAUTH_ACCESS_TOKEN_URL, compact('oauth_verifier') );
	    return new TwitterOAuthToken( $params['oauth_token'], $params['oauth_token_secret'] );
	}
	
}

// Instantiate Class
$GLTW_API = new GLTW_API();
