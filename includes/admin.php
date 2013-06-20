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
	 * Associative array of notice(s).
	 *
	 * @var array
	 */
	public $notices = array();
	
	/**
	 * Access token associative array.
	 *
	 * @var array
	 */
	public $access_token = array();
	
	/**
	 * Oauth token associative array.
	 *
	 * @var array
	 */
	public $oauth_token = array();
	
	/**
	 * Active token associative array.
	 *
	 * @var array
	 */
	public $token = array();
	
	/**
	 * TwitteroAuth object.
	 *
	 * @var TwitteroAuth
	 */
	public $connection;
	
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
			'save_button_text'  => __( 'Save Settings', GENESIS_TWITTER_DOMAIN ),
			'reset_button_text' => __( 'Reset Settings', GENESIS_TWITTER_DOMAIN ),
			'saved_notice_text' => __( 'Settings saved.', GENESIS_TWITTER_DOMAIN ),
			'reset_notice_text' => __( 'Settings reset.', GENESIS_TWITTER_DOMAIN ),
			'error_notice_text' => __( 'Error saving settings.', GENESIS_TWITTER_DOMAIN ),
		);

		$settings_field = 'genesis_twitter_widget_field';

		$default_settings = apply_filters(
			'genesis_twitter_widget_settings_defaults',
			array(
			)
		);

		$this->create( $page_id, $menu_ops, $page_ops, $settings_field, $default_settings );
		
		// Sanitize Settings
		add_action( 'genesis_settings_sanitizer_init', array( $this, 'sanitizer_filters' ) );
		
		// Start PHP Session
		//add_action( 'admin_init', array( $this, 'sessionstart' ) );
		add_action( 'admin_init', array( $this, 'do_action' ), 5 );
		
		// Add Notices
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
		
		add_meta_box( 'genesis-twitter-settings', __( 'Settings', GENESIS_TWITTER_DOMAIN ), array( $this, 'settings_box' ), $this->pagehook, 'main', 'high' );
		add_meta_box( 'genesis-twitter-info', __( 'Information', GENESIS_TWITTER_DOMAIN ), array( $this, 'info_box' ), $this->pagehook, 'main', 'high' );

	}
	
	function set_proxy() {
		$this->add_notice( 'Set Proxy.', 'updated' );
		$args = array(
			'_requires_proxy' => true,
			'_proxy'          => 'proxy.corp.ups.com',
			'_userpwd'        => 'dth8hxg:travis32',
			'_port'           => '8080',
			'_requires_auth'  => true,
		);
		$this->connection->setProxy($args);
	}
	
	function sessionstart() {
		if ( ! isset( $_SESSION['is_open'] ) ) {
			
			/* If access tokens are not available redirect to connect page. 
			if ( empty( $_SESSION['access_token'] ) || empty( $_SESSION['access_token']['oauth_token'] ) || empty( $_SESSION['access_token']['oauth_token_secret'] ) ) {
				$this->clearsessions();
				$this->add_notice( 'Access tokens are not available.', 'error' );
			}
			*/
			session_name( 'genesisTwitter' );
			session_start();
			$_SESSION['is_open'] = TRUE;
			$this->add_notice( 'Session started.', 'updated' );
			
		} else {
			$this->add_notice( 'Session ALREADY started.', 'error' );
		}
	}
	
	function clearsessions() {
	return;
		$this->sessionstart();
		session_destroy();
		$this->add_notice( 'Sessions cleared.', 'error' );
	}
	
	function do_action() {
		$a = $this->get_action();
		$a = '' != $a ? $a : 'none';
		
		$this->add_notice( 'Action: ' . $a, 'updated' );
		
		switch( $this->get_action() ) {
			case 'redirect':
				$this->redirect();
				break;
			case 'callback':
				$this->callback();
				break;
			default:
				$this->sessionstart();
				break;
		}
	}
	
	function get_action() {
		if ( genesis_is_menu_page( $this->page_id ) ) {
			if ( isset( $_GET['action'] ) )
				return $_GET['action'];
			else
				return '';
		}
	}
	
	function admin_notices() {
		if ( genesis_is_menu_page( $this->page_id ) ) {
			foreach( $this->notices as $notice ) {
				printf( '<div id="message" class="%s"><p><strong>%s</strong></p></div>', $notice['type'], $notice['msg'] );
			}				
		}
	}
	
	function update_token() {
	
		if ( empty( $this->access_token ) ) return false;
		$u = update_option(
			$this->settings_field,
			array( 'access_token' => $this->access_token )
		);
		
		$this->add_notice( $this->settings_field . ' Options updated.', 'updated' );
		$this->add_notice( 'AT: ' . print_r( array( 'access_token' => $this->access_token ), true ), 'updated' );
		
		return $u;
	}
	
	function create_connection( $token = '', $secret = '' ) {
		$this->add_notice( 'Create Connection.', 'updated' );
		
		/* Get app tokens out of the session. */
		if ( empty( $this->oauth_token ) && isset( $_SESSION['oauth_token'] ) && isset( $_SESSION['oauth_token_secret'] ) ) {
			$this->oauth_token = array(
				'oauth_token'        => $_SESSION['oauth_token'],
				'oauth_token_secret' => $_SESSION['oauth_token_secret'],
			);
			$this->token = $this->oauth_token;
			$this->add_notice( 'OAUTH TOKEN.', 'updated' );
		}

		/* Get user access tokens out of the session. */
		if ( empty( $this->access_token ) && isset( $_SESSION['access_token'] ) ) {
			$this->access_token = $_SESSION['access_token'];
			$this->token = $this->access_token;
			if ( !empty( $this->oauth_token ) ) {
				$this->oauth_token = array();
			}
			$this->add_notice( 'ACCESS TOKEN.', 'updated' );
		}
		
		/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
		if ( ! empty( $this->token ) ) {
			$this->add_notice( 'NOT Empty Token.', 'updated' );
			$this->connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, $this->token['oauth_token'], $this->token['oauth_token_secret']);
		} else {
			$this->add_notice( 'Empty Token.', 'updated' );
			$this->connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET );
		}
		
		//$this->set_proxy();
	}
	
    /**
     * 
     * 
     * @param string $msg 
     * @param string $type Takes 'updated' or 'error'
     * 
     */
	function add_notice( $msg, $type = 'error' ) {
		$this->notices[] = array(
			'type' => $type,
			'msg'  => $msg,
		);
	}

	/**
	 * Callback for Settings meta box.
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
		$this->add_notice( 'Settings box.', 'updated' );
		
		/* Create TwitteroAuth object */
		$this->create_connection();
		
		/* Ask Twitter for temporary credentials */
		$temporary_credentials = $this->connection->getRequestToken( add_query_arg( array( 'action' => 'callback', ), admin_url( 'admin.php?page=' . $this->page_id ) ) );
		$redirect_url          = $this->connection->getAuthorizeURL( $temporary_credentials, false );
		
		/* Sign in with Twitter Button */
		$content = sprintf(
			'<a href="%s"><img src="%s" alt="Sign in with Twitter" /></a>',
			//add_query_arg( array( 'action' => 'redirect', ), admin_url( 'admin.php?page=' . $this->page_id ) ),
			$redirect_url,
			plugins_url( 'twitteroauth/images/lighter.png', __FILE__ )
		);
		
		/* If method is set change API call made. Test is called by default. */
		//$content = $this->connection->get('account/rate_limit_status' );
		//printf( __( 'Current API hits remaining: %s', GENESIS_TWITTER_DOMAIN ), $content->remaining_hits );

		/* Get logged in user to help with tests. */
		//$user = $this->connection->get( 'account/verify_credentials' );
//pr($user, 'user');
//var_dump( $user );
		
		/*
		$active = FALSE;
		if (empty($active) || empty($_GET['confirmed']) || $_GET['confirmed'] !== 'TRUE') {
			echo '<h1>Warning! This page will make many requests to Twitter.</h1>';
			echo '<h4>Performing these test might max out your rate limit.</h4>';
			echo '<h4>Statuses/DMs will be created and deleted. Accounts will be un/followed.</h4>';
			echo '<h4>Profile information/design will be changed.</h4>';
			echo '<h2>USE A DEV ACCOUNT!</h2>';
			echo '<h4>Before use you must set $active = TRUE in test.php</h4>';
			echo '<a href="./test.php?confirmed=TRUE">Continue</a> or <a href="./index.php">go back</a>.';
		}
		*/
		
		
		/* If method is set change API call made. Test is called by default. */
		//$content = $this->connection->get( 'account/verify_credentials' );
		
		/* Some example calls */
		//$this->connection->get('users/show', array('screen_name' => 'wp_smith'));
		//$this->connection->post('statuses/update', array('status' => date(DATE_RFC822)));
		//$this->connection->post('statuses/destroy', array('id' => 5437877770));
		//$this->connection->post('friendships/create', array('id' => 9436992));
		//$this->connection->post('friendships/destroy', array('id' => 9436992));

		/* Include HTML to display on the page */
		include( GENESIS_TWITTER_OAUTH . '/html.inc' );
		
//pr($temporary_credentials, 'temporary_credentials');
//pr($redirect_url, 'redirect_url');
		
	}
	
	/**
	 * Callback for Information meta box.
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
		echo '<h4>lastStatusCode</h4>' . wpautop( $this->connection->lastStatusCode() );
		echo '<h4>lastAPICall</h4>' . wpautop( $this->connection->lastAPICall() );
	}
	
	function callback() {
		$this->sessionstart();

//pr( $_SESSION );
//pr( $_REQUEST );

		//http://dev.wpsmith.net/sandbox/wp-admin/admin.php?page=genesis-twitter-widget-settings&action=callback&oauth_token=nXcmGDpT9Y37u0Y7eTcukofKLP2roiRucLEyt8U0ws&oauth_verifier=1exT6GgziI4to0tG0aLS5CmDFEXhQbv3esxSqEmU
		$this->add_notice( 'Callback called.', 'updated' );
		
		/* If the oauth_token is old redirect to the connect page. */
		if ( isset( $_REQUEST['oauth_token'] ) && isset( $_SESSION['oauth_token'] ) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token'] ) {
//echo 'OLD TOKEN';
			$this->add_notice( 'Old Token, clear Sessions.', 'error' );
			$_SESSION['oauth_status'] = 'oldtoken';
			$this->clearsessions();
		}
		
		/* Create TwitteroAuth object */
		$this->create_connection();
//pr($this->connection,'$this->connection');		
		
		/* Request access tokens from twitter */
		$this->access_token = $this->connection->getAccessToken($_REQUEST['oauth_verifier']);
pr($this->access_token,'$this->access_token');

		/* Save the access tokens. Normally these would be saved in a database for future use. */
		if ( ! empty( $this->access_token ) ) {
			$_SESSION['access_token'] = $this->access_token;
			$this->update_token();
			
			/* Remove no longer needed request tokens */
			unset( $_SESSION['oauth_token'] );
			unset( $_SESSION['oauth_token_secret'] );
		}

		/* If HTTP response is 200 continue otherwise send to connect page to retry */
		$this->add_notice( 'Connection Code: ' . $this->connection->http_code, 'updated' );
		
		if ( 200 == $this->connection->http_code ) {
			/* The user has been verified and the access tokens can be saved for future use */
			$_SESSION['status'] = 'verified';
			$this->add_notice( 'The user has been verified and the access tokens can be saved for future use', 'updated' );
		} else {
			/* Save HTTP status for error dialog on connnect page.*/
			$this->add_notice( 'Save HTTP status for error dialog on connnect page', 'error' );
			$this->clearsessions();
		}
	}
	
	function redirect() {
		$this->add_notice( 'Redirect called.', 'updated' );
		/* Build TwitterOAuth object with client credentials. */
		$this->connection = new TwitterOAuth( CONSUMER_KEY, CONSUMER_SECRET );
		 
		/* Get temporary credentials. */
		$request_token = $this->connection->getRequestToken( OAUTH_CALLBACK );

		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];
		 
		/* If last connection failed don't display authorization link. */
		switch ( $this->connection->http_code ) {
			case 200:
				/* Build authorize URL and redirect user to Twitter. */
				$url = $this->connection->getAuthorizeURL($token);
				header('Location: ' . $url); 
				break;
			default:
				/* Show notification if something went wrong. */
				$this->add_notice( __( 'Could not connect to Twitter. Refresh the page or try again later.', GENESIS_TWITTER_DOMAIN ), 'error' );
				break;
		}
		
//pr($this->connection, 'connection');
//pr($_SESSION, '_SESSION');
//pr($request_token, 'request_token');
	}

}
 
$_Genesis_Twitter_Widget_Admin_Settings = new Genesis_Twitter_Widget_Admin_Settings();
