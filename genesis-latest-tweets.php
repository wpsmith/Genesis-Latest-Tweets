<?php
/*
Plugin Name: Genesis Latest Tweets Widget
Plugin URI: http://wpsmith.net/
Description: Genesis Latest Tweets Widget.
Version: 1.0.7
Author: wpsmith
Author URI: http://wpsmith.net/
Author Email: t@wpsmith.net
License:

  Copyright 2012 Travis Smith (t@wpsmith.net)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as 
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
*/


/**
* Genesis Twitter Plugin Directory.
* @const  GENESIS_TWITTER_DIR  Plugin Directory.
*/
define( 'GENESIS_TWITTER_DIR', dirname( __FILE__ ) );

/**
 * Genesis Twitter Class.
 *
 * @since 1.0.0
 *
 * @category Genesis_Twitter
 * @package	 Widgets
 * @author	 Travis Smith
 * @license  http://www.opensource.org/licenses/gpl-license.php GPL v2.0 (or later)
 * @link     http://www.studiopress.com/themes/genesis
 */
class Genesis_Twitter {

	/*--------------------------------------------*
	 * Constructor
	 *--------------------------------------------*/

	/**
	 * Initializes the plugin by setting localization, filters, and administration functions.
	 */
	function __construct() {

		load_plugin_textdomain( 'genesis-latest-tweets', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		add_action( 'widgets_init', array( $this, 'load_widget' ), 25 );
		
		// @todo Waiting on Nathan to determine activation/deactivation steps
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		//register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
		
		add_action( 'init', array( $this, 'activate' ) );

	} // end constructor
	
	/**
	 * Load the Twitter widget.
	 */
	public function load_widget() {
		// Remove Genesis Twiter Widget
		unregister_widget( 'Genesis_Latest_Tweets_Widget' );
	
		require_once( GENESIS_TWITTER_DIR . '/includes/functions.php' );
		require_once( GENESIS_TWITTER_DIR . '/includes/latest-tweets-widget.php' );
		register_widget( 'GLTW_Latest_Tweets_Widget' );
	} // end load_widget
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function activate( $network_wide ) {
		$latest = '1.9';

		$theme  = wp_get_theme();
		$parent = wp_get_theme( $theme->Template );
		if ( empty( $parent ) ) $parent = $theme;

		if ( 'genesis' != basename( get_template_directory()  ) || 'genesis' != $theme['Template'] ) {
			if ( !function_exists( 'deactivate_plugins' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( plugin_basename( __FILE__ ) ); /** Deactivate ourself */
			add_action( 'admin_notices', array( $this, 'deactivation_message' ), 5 );
		}
		
		// Remove -dev if present from trunk
		$version = $parent['Version'];
		$version = str_replace( '-dev', '', $version );
		$version = str_replace( '-beta', '', $version );
		$version = str_replace( '-alpha', '', $version );
		
		if ( version_compare( $version, $latest, '<' ) ) {
			if ( !function_exists( 'deactivate_plugins' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			deactivate_plugins( plugin_basename( __FILE__ ) ); /** Deactivate ourself */
			add_action( 'admin_notices', array( $this, 'deactivation_message' ), 5 );
		}
		
	} // end activate
	
	/**
	 * Outputs error message and removes activation message for a singularly
	 * activated plugin.
	 */
	function deactivation_message() {
		$latest = '1.9';
		
		// Output message
		printf(
			'<div id="message" class="error"><p>' . 
			__( 'Sorry, you can\'t activate Genesis Latest Tweets unless you have installed <a href="%s">Genesis %s</a> or greater', 'genesis-latest-tweets' ) . 
			'</p></div>', 
			'http://www.studiopress.com/genesis', 
			$latest 
		);
		
		// Remove single activation notice hack.
		unset( $_GET['activate'] );
		
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @params	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function deactivate( $network_wide ) {
		// TODO define deactivation functionality here		
	} // end deactivate
	 
  
} // end class

new Genesis_Twitter();