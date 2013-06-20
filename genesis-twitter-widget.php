<?php
/*
Plugin Name: Genesis Latest Tweets Widget
Plugin URI: http://wpsmith.net/
Description: Genesis Latest Tweets Widget.
Version: 1.1.0
Author: wpsmith,Nick_theGeek
Author URI: http://wpsmith.net/
Author Email: t@wpsmith.net 
License:

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
define( 'GENESIS_TWITTER_DOMAIN', 'genesis-latest-tweets' );


add_action( 'init', 'genesis_twitter_widget_init' );
function genesis_twitter_widget_init() {

	load_plugin_textdomain( GENESIS_TWITTER_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	
}

add_action( 'widgets_init', 'genesis_twitter_load_widget', 25 );	
/**
 * Load the Twitter widget and required files
 *
 * Normally I would load this on genesis_init skip the function_exists( 'genesis' ) 
 * but this needs to load on widgets_init
 */
function genesis_twitter_load_widget() {
	// Remove Genesis Twiter Widget
	if( function_exists( 'genesis' ) ){
	
		unregister_widget( 'Genesis_Latest_Tweets_Widget' );
	
		require_once( GENESIS_TWITTER_DIR . '/includes/api/twitter-api.php' );
		require_once( GENESIS_TWITTER_DIR . '/includes/api/twitter-api-core.php' );
		require_once( GENESIS_TWITTER_DIR . '/includes/functions.php' );
		require_once( GENESIS_TWITTER_DIR . '/includes/xhtml-widget.php' );
		
		register_widget( 'Genesis_Official_Twitter_Widget' );
		
		// Include application settings panel if in admin area
		if( is_admin() )
			require_once( GENESIS_TWITTER_DIR . '/includes/api/twitter-api-admin.php' );

	}
}