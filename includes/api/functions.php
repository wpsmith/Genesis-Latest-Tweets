<?php

/**
 * Render "Connect" button for authenticating at twitter.com
 * @param string OAuth application Consumer Key
 * @param string OAuth application Consumer Secret
 */
function twitter_api_admin_render_login( $consumer_key, $consumer_secret ){
    try {
        $callback = twitter_api_admin_base_uri();
        $Token = twitter_api_oauth_request_token( $consumer_key, $consumer_secret, $callback );
    }
    catch( Exception $Ex ){
        echo '<div class="error"><p><strong>Error:</strong> ',esc_html( $Ex->getMessage() ),'</p></div>';
        return;
    }
    // Remember request token and render link to authorize
    // we're storing permanently - not using session here, because WP provides no session API.
    gltw_api_config( array( 'request_secret' => $Token->secret ) );
    $href = $Token->get_authorization_url();
    echo '<p><a class="button-primary" href="',esc_html($href),'">'.esc_html__('Connect to Twitter', GLTW_DOMAIN ).'</a></p>';
    echo '<p>&nbsp;</p>';
}
 
 
 
 
/**
 * Render full admin page
 */ 
function twitter_api_admin_render_page(){
    if ( ! current_user_can('manage_options') ){
        twitter_api_admin_render_header( __("You don't have permission to manage Twitter API settings", GLTW_DOMAIN ),'error');
        twitter_api_admin_render_footer();
        return;
    }
    try {

        // update applicaion settings if posted
        if( isset($_POST['saf_twitter']) && is_array( $update = $_POST['saf_twitter'] ) ){
            $conf = gltw_api_config( $update );
        }

        // else get current settings
        else {
            $conf = gltw_api_config();
        }

        // check whether we have any OAuth params
        extract( $conf );
        if( ! $consumer_key || ! $consumer_secret ){
            throw new Exception( __('Twitter application not fully configured', GLTW_DOMAIN ) );
        }

        // else exchange access token if callback // request secret saved as option
        if( isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']) ){
            $Token = twitter_api_oauth_access_token( $consumer_key, $consumer_secret, $_GET['oauth_token'], $request_secret, $_GET['oauth_verifier'] );
            // have access token, update config and destroy request secret
            $conf = gltw_api_config( array(
                'request_secret' => '',
                'access_key'     => $Token->key,
                'access_secret'  => $Token->secret,
            ) );
            extract( $conf );
            // fall through to verification of credentials
        }

        // else administrator needs to connect / authenticate with Twitter.
        if( ! $access_key || ! $access_secret ){
            twitter_api_admin_render_header( __('Plugin not yet authenticated with Twitter', GLTW_DOMAIN ), 'error' );
            twitter_api_admin_render_login( $consumer_key, $consumer_secret );
        }

        // else we have auth - verify that tokens are all still valid
        else {
            $me = twitter_api_get('account/verify_credentials');
            twitter_api_admin_render_header( sprintf( __('Authenticated as @%s', GLTW_DOMAIN ), $me['screen_name'] ), 'updated' );
        }

    }
    catch( TwitterApiException $Ex ){
        twitter_api_admin_render_header( $Ex->getStatus().': Error '.$Ex->getCode().', '.$Ex->getMessage(), 'error' );
        if( 401 === $Ex->getStatus() ){
            twitter_api_admin_render_login( $consumer_key, $consumer_secret );
        }
    }
    catch( Exception $Ex ){
        twitter_api_admin_render_header( $Ex->getMessage(), 'error' );
    }
    
    // end admin page with options form and close wrapper
    twitter_api_admin_render_form();
    twitter_api_admin_render_footer();
}



/**
 * Calculate base URL for admin OAuth callbacks
 * @return string
 */
function twitter_api_admin_base_uri(){
    static $base_uri;
    if( ! isset($base_uri) ){
        $port = isset($_SERVER['HTTP_X_FORWARDED_PORT']) ? $_SERVER['HTTP_X_FORWARDED_PORT'] : $_SERVER['SERVER_PORT'];
        $prot = '443' === $port ? 'https:' : 'http:';
        $base_uri = $prot.'//'.$_SERVER['HTTP_HOST'].''.current( explode( '&', $_SERVER['REQUEST_URI'], 2 ) );
    }
    return $base_uri;
}