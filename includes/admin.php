<?php
/**
 * Genesis Twitter Widget Admin Page.
 *
 * @package Genesis\Admin
 * @author  StudioPress
 * @license http://www.opensource.org/licenses/gpl-license.php GPL-2.0+
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
class GLTW_Widget_Admin_Settings extends Genesis_Admin_Boxes {

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
				'page_title'  => __( 'Genesis - Twitter Widget Settings', GLTW_DOMAIN ),
				'menu_title'  => __( 'Twitter Widget Settings', GLTW_DOMAIN )
			)
		);

		$page_ops = array(
			'screen_icon'       => 'options-general',
			'save_button_text'  => __( 'Save Settings', GLTW_DOMAIN ),
			'reset_button_text' => __( 'Reset Settings', GLTW_DOMAIN ),
			'saved_notice_text' => __( 'Settings saved.', GLTW_DOMAIN ),
			'reset_notice_text' => __( 'Settings reset.', GLTW_DOMAIN ),
			'error_notice_text' => __( 'Error saving settings.', GLTW_DOMAIN ),
		);

		$settings_field = 'gltw_widget_field';

		$default_settings = apply_filters(
			'gltw_widget_settings_defaults',
			array(
	
			)
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );

		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );
		
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
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
		global $GLTW_API, $gltw_errors;
		extract( gltw_api_config() );
		add_meta_box( 'gltw-settings', __( 'Twitter API Settings', GLTW_DOMAIN ), array( $this, 'settings_box' ), $this->pagehook, 'main', 'high' );
		add_meta_box( 'gltw-info', __( 'Twitter API Information', GLTW_DOMAIN ), array( $this, 'info_box' ), $this->pagehook, 'main', 'high' );
		
		if ( empty( $consumer_key ) || empty( $consumer_secret ) || empty( $access_key ) || empty( $access_secret ) || ! empty( $gltw_errors ) ) {
			add_meta_box( 'gltw-help', __( 'Help Setup', GLTW_DOMAIN ), array( $this, 'help_box' ), $this->pagehook, 'main', 'high' );
		} else {
			$rate = $GLTW_API->api_get('application/rate_limit_status', array( 'resources' => 'application', ) );
			if ( isset( $rate['errors'] ) )
				add_meta_box( 'gltw-help', __( 'Help Setup', GLTW_DOMAIN ), array( $this, 'help_box' ), $this->pagehook, 'main', 'high' );
		}
	}

	function admin_notices() {
		gltw_errors();
	}

	/**
	 * Callback for Twitter API Information meta box.
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
		global $GLTW_API, $gltw_errors;
		extract( gltw_api_config() );
		
		if ( !empty( $gltw_errors ) ) {
			echo gltw_errors( 'gltw-error', false );
		}
		if ( !empty( $consumer_key ) && !empty( $consumer_secret ) && !empty( $access_key ) && !empty( $access_secret ) ) {
			$rate = $GLTW_API->api_get('application/rate_limit_status', array( 'resources' => 'application', ) );
			if ( !empty( $gltw_errors ) || isset( $rate['errors'] ) ) {
				echo gltw_errors( 'gltw-error', false );
			}
			else {
				$rate_limit_status = $rate['resources']['application']['/application/rate_limit_status'];
				
				printf( '<%1$s>%2$s</%1$s>', 'h4', __( 'Rate Limit Status', GLTW_DOMAIN ) );
				printf(
					'<ul><%1$s>%2$s</%1$s><%1$s>%3$s</%1$s></ul>',
					'li',
					sprintf(
						'<%1$s>%2$s</%1$s>', 
						'strong', 
						__( 'Remaining:', GLTW_DOMAIN ) . ' '
					) . $rate_limit_status['remaining'] . __( ' out of ', GLTW_DOMAIN ) . $rate_limit_status['limit'],
					sprintf( '<%1$s>%2$s</%1$s>', 'strong', __( 'Reset:', GLTW_DOMAIN ) ) . ' ' . date( 'l F jS, Y \a\t h:i:s A', $rate_limit_status['reset'] )
				);
			}
		}
		else {
			
		}
	}
	
	function help_box() {
		echo '<ol>';
			printf(
				'<%1$s>%2$s<a href="https://dev.twitter.com/apps" target="_blank">%3$s</a><ul>%3$s</ul></%1$s>', 
				'li', 
				__( 'Create a Twitter App from your Developer\'s Dashboard', GLTW_DOMAIN ), 
				__( 'your Twitter dashboard', GLTW_DOMAIN ),
				sprintf( '<%1$s>%2$s</%1$s>', 'li', __( 'Callback URL optional.', GLTW_DOMAIN ) )
			);
			printf( '<%1$s>%2$s</%1$s>', 'li', __( 'Create Your Access Token', GLTW_DOMAIN ) );
			printf( '<%1$s>%2$s</%1$s>', 'li', __( 'Copy and Pase the Customer Key &amp; Secret as well as the Access Key &amp; Secret.', GLTW_DOMAIN ) );
			
		echo '</ol>';
		
		echo '<ol style="clear:both;">';
			printf( '<%1$s style="list-style-type:none; float: left; padding-right: 10px;"><a href="%2$s"><img width="100px" src="%2$s"/></a></%1$s>', 'li', plugins_url( 'images/create-twitter-plugin-01.png', __FILE__ ) );
			printf( '<%1$s style="list-style-type:none; float: left; padding-right: 10px;"><a href="%2$s"><img width="100px" src="%2$s"/></a></%1$s>', 'li', plugins_url( 'images/create-twitter-plugin-02.png', __FILE__ ) );
			printf( '<%1$s style="list-style-type:none; float: left; padding-right: 10px;"><a href="%2$s"><img width="100px" src="%2$s"/></a></%1$s>', 'li', plugins_url( 'images/create-twitter-plugin-03.png', __FILE__ ) );
		echo '</ol>'; 
	}
	
	/**
	 * Callback for Twitter Settings meta box.
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
	function settings_box() {
		extract( gltw_api_config() );
		?>
		<p>
			<label for="twitter-api--consumer-key"><?php _e( 'OAuth Consumer Key:', GLTW_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="gltw_widget_field[consumer_key]" id="twitter-api--consumer-key" value="<?php echo $consumer_key; ?>" />
		</p>
		<p>
			<label for="twitter-api--consumer-secret"><?php _e( 'OAuth Consumer Secret:', GLTW_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="gltw_widget_field[consumer_secret]" id="twitter-api--consumer-secret" value="<?php echo $consumer_secret; ?>" />
		</p>
		<p>
			<label for="twitter-api--access-key"><?php _e( 'OAuth Access Token:', GLTW_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="gltw_widget_field[access_key]" id="twitter-api--access-key" value="<?php echo $access_key; ?>" />
		</p>
		<p>
			<label for="twitter-api--access-secret"><?php _e( 'OAuth Access Secret:', GLTW_DOMAIN ); ?></label><br />
			<input type="text" size="64" name="gltw_widget_field[access_secret]" id="twitter-api--access-secret" value="<?php echo $access_secret;?>" />
		</p>
		<small>
		<?php echo esc_html__( 'These details are available in', GLTW_DOMAIN )?> 
		<a href="https://dev.twitter.com/apps" target="_blank"><?php echo esc_html__( 'your Twitter dashboard', GLTW_DOMAIN )?></a>
		</small>
		<?php

	}
	
	/**
	 * Render "Connect" button for authenticating at twitter.com
	 * @param string OAuth application Consumer Key
	 * @param string OAuth application Consumer Secret
	 */
	function render_login( $consumer_key, $consumer_secret ){
		try {
			$callback = $this->base_uri();
			$Token = $this->oauth_request_token( $consumer_key, $consumer_secret, $callback );
		}
		catch( Exception $Ex ){
			echo '<div class="error"><p><strong>Error:</strong> ',esc_html( $Ex->getMessage() ),'</p></div>';
			return;
		}
		// Remember request token and render link to authorize
		// we're storing permanently - not using session here, because WP provides no session API.
		gltw_api_config( array( 'request_secret' => $Token->secret ) );
		$href = $Token->get_authorization_url();
		echo '<p><a class="button-primary" href="',esc_html($href),'">'.esc_html__('Connect to Twitter').'</a></p>';
		echo '<p>&nbsp;</p>';
	}
	
	/**
	 * Calculate base URL for admin OAuth callbacks
	 * @return string
	 */
	function base_uri(){
		static $base_uri;
		if( ! isset($base_uri) ){
			$port = isset($_SERVER['HTTP_X_FORWARDED_PORT']) ? $_SERVER['HTTP_X_FORWARDED_PORT'] : $_SERVER['SERVER_PORT'];
			$prot = '443' === $port ? 'https:' : 'http:';
			$base_uri = $prot.'//'.$_SERVER['HTTP_HOST'].''.current( explode( '&', $_SERVER['REQUEST_URI'], 2 ) );
		}
		return $base_uri;
	}
	
	/**
	 * Contact Twitter for a request token, which will be exchanged for an access token later.
	 * @return TwitterOAuthToken Request token
	 */
	function oauth_request_token( $consumer_key, $consumer_secret, $oauth_callback = 'oob' ){
		$Client = GLTW_API::api_client();
		$Client->set_oauth( $consumer_key, $consumer_secret );     
		$params = $Client->oauth_exchange( TWITTER_OAUTH_REQUEST_TOKEN_URL, compact('oauth_callback') );
		return new TwitterOAuthToken( $params['oauth_token'], $params['oauth_token_secret'] );
	}

	/**
	 * Exchange request token for an access token after authentication/authorization by user
	 * @return TwitterOAuthToken Access token
	 */
	function oauth_access_token( $consumer_key, $consumer_secret, $request_key, $request_secret, $oauth_verifier ){
		$Client = GLTW_API::api_client();
		$Client->set_oauth( $consumer_key, $consumer_secret, $request_key, $request_secret );     
		$params = $Client->oauth_exchange( TWITTER_OAUTH_ACCESS_TOKEN_URL, compact('oauth_verifier') );
		return new TwitterOAuthToken( $params['oauth_token'], $params['oauth_token_secret'] );
	}

}
 
 
new GLTW_Widget_Admin_Settings();