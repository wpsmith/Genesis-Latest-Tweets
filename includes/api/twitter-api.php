<?php
/**
 * Twitter API Wordpress library.
 * @author Tim Whitlock <@timwhitlock>
 */

global $Genesis_Twitter_API;

class Genesis_Twitter_API {
	
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
	function api_get( $path, array $args = array() ){
	    $Client = $this->api_client();
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
	function api_post( $path, array $args = array() ){
	    $Client = $this->api_client();
	    return $Client->call( $path, $args, 'POST' );
	} 
	
	
	
	
	/**
	 * Enable caching of Twitter API responses using APC
	 * @param int Cache lifetime in seconds
	 * @return TwitterApiClient
	 */
	function api_enable_cache( $ttl ){
	    $Client = $this->api_client();
	    return $Client->enable_cache( $ttl );
	}
	
	
	
	
	/**
	 * Disable caching of Twitter API responses
	 * @return TwitterApiClient
	 */
	function api_disable_cache( $ttl ){
	    $Client = $this->api_client();
	    return $Client->disable_cache();
	}
	
	
	
	
	/**
	 * Get plugin local base directory in case __DIR__ isn't available (php<5.3)
	 */
	function api_basedir(){
	    static $dir;
	    isset($dir) or $dir = dirname(__FILE__).'/..';
	    return $dir;    
	}    
	
	
	
	
	/**
	 * Get fully configured and authenticated Twitter API client.
	 * @return TwitterApiClient
	 */ 
	function api_client( $id = null ){
	    static $clients = array();
	    if( ! isset($clients[$id]) ){
	        $clients[$id] = TwitterApiClient::create_instance( is_null($id) );
	    }
	    return $clients[$id];
	}
	
	
	
	
	/**
	 * Contact Twitter for a request token, which will be exchanged for an access token later.
	 * @return TwitterOAuthToken Request token
	 */
	function api_oauth_request_token( $consumer_key, $consumer_secret, $oauth_callback = 'oob' ){
	    $Client = $this->api_client('oauth');
	    $Client->set_oauth( $consumer_key, $consumer_secret );     
	    $params = $Client->oauth_exchange( $this->OAUTH_REQUEST_TOKEN_URL, compact('oauth_callback') );
	    return new TwitterOAuthToken( $params['oauth_token'], $params['oauth_token_secret'] );
	}
	
	
	
	
	/**
	 * Exchange request token for an access token after authentication/authorization by user
	 * @return TwitterOAuthToken Access token
	 */
	function api_oauth_access_token( $consumer_key, $consumer_secret, $request_key, $request_secret, $oauth_verifier ){
	    $Client = $this->api_client('oauth');
	    $Client->set_oauth( $consumer_key, $consumer_secret, $request_key, $request_secret );     
	    $params = $Client->oauth_exchange( $this->OAUTH_ACCESS_TOKEN_URL, compact('oauth_verifier') );
	    return new TwitterOAuthToken( $params['oauth_token'], $params['oauth_token_secret'] );
	}
	

}

$Genesis_Twitter_API = new Genesis_Twitter_API();