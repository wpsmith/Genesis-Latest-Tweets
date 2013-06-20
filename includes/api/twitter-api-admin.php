<?php
/**
 * Genesis Framework.
 *
 * @package Genesis\Admin
 * @author  StudioPress
 * @license http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
 * @link    http://my.studiopress.com/themes/genesis/
 */

/**
 * Twitter API admin functions.
 * Configures and authenticates with the Twitter API.
 * @author Tim Whitlock <@timwhitlock>
 */
 
/**
 * Registers a new admin page, providing content and corresponding menu item for the Theme Settings page.
 *
 * Although this class was added in 1.8.0, some of the methods were originally* standalone functions added in previous
 * versions of Genesis.
 *
 * @package Genesis\Admin
 *
 * @since 1.8.0
 */
class Genesis_Twitter_Widget_Admin_Settings extends Genesis_Admin_Boxes {

	/**
	 * Create an admin menu item and settings page.
	 *
	 * @since 1.8.0
	 *
	 * @uses GENESIS_ADMIN_IMAGES_URL     URL for admin images.
	 * @uses GENESIS_SETTINGS_FIELD       Settings field key.
	 * @uses PARENT_DB_VERSION            Genesis database version.
	 * @uses PARENT_THEME_VERSION         Genesis framework version.
	 * @uses genesis_get_default_layout() Get default layout.
	 * @uses \Genesis_Admin::create()     Create an admin menu item and settings page.
	 */
	function __construct() {

		$page_id = 'genesis-twitter-widget-settings';

		$menu_ops = array(
			'submenu' => array(
				'parent_slug' => 'genesis',
				'page_title'  => __( 'Genesis - Twitter Widget Settings', GENESIS_TWITTER_DOMAIN ),
				'menu_title'  => __( 'Twitter Widget Settings', GENESIS_TWITTER_DOMAIN )
			)
		);

		$page_ops = array(
			'screen_icon'       => 'options-general',
			'save_button_text'  => __( 'Save Settings', 'genesis' ),
			'reset_button_text' => __( 'Reset Settings', 'genesis' ),
			'saved_notice_text' => __( 'Settings saved.', 'genesis' ),
			'reset_notice_text' => __( 'Settings reset.', 'genesis' ),
			'error_notice_text' => __( 'Error saving settings.', 'genesis' ),
		);

		$settings_field = 'genesis_twitter_widget_field';

		$default_settings = apply_filters(
			'genesis_twitter_widget_settings_defaults',
			array(
	
			)
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );

	}

	/**
	 * Register each of the settings with a sanitization filter type.
	 *
	 * @since 1.7.0
	 *
	 * @uses genesis_add_option_filter() Assign filter to array of settings.
	 *
	 * @see \Genesis_Settings_Sanitizer::add_filter() Add sanitization filters to options.
	 */
	public function sanitizer_filters() {

		genesis_add_option_filter(
			'one_zero',
			$this->settings_field,
			array(
				
			)
		);

		genesis_add_option_filter(
			'no_html',
			$this->settings_field,
			array(
				
			)
		);

		genesis_add_option_filter(
			'absint',
			$this->settings_field,
			array(
				
			)
		);

		genesis_add_option_filter(
			'safe_html',
			$this->settings_field,
			array(
				
			)
		);

		genesis_add_option_filter(
			'requires_unfiltered_html',
			$this->settings_field,
			array(
				
			)
		);

		genesis_add_option_filter(
			'url',
			$this->settings_field,
			array(
				
			)
		);

	}

	/**
 	 * Register meta boxes on the Theme Settings page.
 	 *
 	 * @since 1.0.0
 	 *
 	 */
	function metaboxes() {

		add_meta_box( 'genesis-theme-settings-version', __( 'Information', 'genesis' ), array( $this, 'info_box' ), $this->pagehook, 'main', 'high' );


	}


	/**
	 * Callback for Theme Settings Information meta box.
	 *
	 * If genesis-auto-updates is not supported, some of the fields will not display.
	 *
	 * @since 1.0.0
	 *
	 * @uses PARENT_THEME_RELEASE_DATE         Date of current release of Genesis Framework.
	 * @uses \Genesis_Admin::get_field_id()    Construct field ID.
	 * @uses \Genesis_Admin::get_field_name()  Construct field name.
	 * @uses \Genesis_Admin::get_field_value() Retrieve value of key under $this->settings_field.
	 *
	 * @see \Genesis_Admin_Settings::metaboxes() Register meta boxes on the Theme Settings page.
	 */
	function info_box() {
	
		extract( genesis_twitter_api_config() );

		?>
		<p>
			<label for="twitter-api--consumer-key"><?php _e( 'OAuth Consumer Key:', GENESIS_TWITTER_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="genesis_twitter_widget_field[consumer_key]" id="twitter-api--consumer-key" value="<?php echo esc_html($consumer_key)?>" />
		</p>
		<p>
			<label for="twitter-api--consumer-secret"><?php _e( 'OAuth Consumer Secret:', GENESIS_TWITTER_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="genesis_twitter_widget_field[consumer_secret]" id="twitter-api--consumer-secret" value="<?php echo esc_html($consumer_secret)?>" />
		</p>
		<p>
			<label for="twitter-api--access-key"><?php _e( 'OAuth Access Token:', GENESIS_TWITTER_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="genesis_twitter_widget_field[access_key]" id="twitter-api--access-key" value="<?php echo esc_html($access_key)?>" />
		</p>
		<p>
			<label for="twitter-api--access-secret"><?php _e( 'OAuth Access Secret:', GENESIS_TWITTER_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="genesis_twitter_widget_field[access_secret]" id="twitter-api--access-secret" value="<?php echo esc_html($access_secret)?>" />
		</p>
		<small>
		<?php echo esc_html__( 'These details are available in', GENESIS_TWITTER_DOMAIN )?> 
		<a href="https://dev.twitter.com/apps" target="_blank"><?php echo esc_html__( 'your Twitter dashboard', GENESIS_TWITTER_DOMAIN )?></a>
		</small>
		<?php

	}

}
 
 
new Genesis_Twitter_Widget_Admin_Settings();




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
    genesis_twitter_api_config( array( 'request_secret' => $Token->secret ) );
    $href = $Token->get_authorization_url();
    echo '<p><a class="button-primary" href="',esc_html($href),'">'.esc_html__('Connect to Twitter', GENESIS_TWITTER_DOMAIN ).'</a></p>';
    echo '<p>&nbsp;</p>';
}
 
 
 
 
/**
 * Render full admin page
 */ 
function twitter_api_admin_render_page(){
    if ( ! current_user_can('manage_options') ){
        twitter_api_admin_render_header( __("You don't have permission to manage Twitter API settings", GENESIS_TWITTER_DOMAIN ),'error');
        twitter_api_admin_render_footer();
        return;
    }
    try {

        // update applicaion settings if posted
        if( isset($_POST['saf_twitter']) && is_array( $update = $_POST['saf_twitter'] ) ){
            $conf = genesis_twitter_api_config( $update );
        }

        // else get current settings
        else {
            $conf = genesis_twitter_api_config();
        }

        // check whether we have any OAuth params
        extract( $conf );
        if( ! $consumer_key || ! $consumer_secret ){
            throw new Exception( __('Twitter application not fully configured', GENESIS_TWITTER_DOMAIN ) );
        }

        // else exchange access token if callback // request secret saved as option
        if( isset($_GET['oauth_token']) && isset($_GET['oauth_verifier']) ){
            $Token = twitter_api_oauth_access_token( $consumer_key, $consumer_secret, $_GET['oauth_token'], $request_secret, $_GET['oauth_verifier'] );
            // have access token, update config and destroy request secret
            $conf = genesis_twitter_api_config( array(
                'request_secret' => '',
                'access_key'     => $Token->key,
                'access_secret'  => $Token->secret,
            ) );
            extract( $conf );
            // fall through to verification of credentials
        }

        // else administrator needs to connect / authenticate with Twitter.
        if( ! $access_key || ! $access_secret ){
            twitter_api_admin_render_header( __('Plugin not yet authenticated with Twitter', GENESIS_TWITTER_DOMAIN ), 'error' );
            twitter_api_admin_render_login( $consumer_key, $consumer_secret );
        }

        // else we have auth - verify that tokens are all still valid
        else {
            $me = twitter_api_get('account/verify_credentials');
            twitter_api_admin_render_header( sprintf( __('Authenticated as @%s', GENESIS_TWITTER_DOMAIN ), $me['screen_name'] ), 'updated' );
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




