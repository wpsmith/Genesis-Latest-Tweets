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
* @const  GLTW_DIR  Plugin Directory.
*/
define( 'GLTW_DIR', dirname( __FILE__ ) );
define( 'GLTW_INC', GLTW_DIR . '/includes' );
define( 'GLTW_API', GLTW_INC . '/api' );
define( 'GLTW_DOMAIN', GLTW_DOMAIN );


add_action( 'init', 'gltw_widget_init' );
function gltw_widget_init() {

	load_plugin_textdomain( GLTW_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	
}

add_action( 'widgets_init', 'gltw_load_widget', 25 );	
/**
 * Load the Twitter widget and required files
 *
 * Normally I would load this on genesis_init skip the function_exists( 'genesis' ) 
 * but this needs to load on widgets_init
 */
function gltw_load_widget() {

	// Remove Genesis Twiter Widget
	if( function_exists( 'genesis' ) ){
	
		unregister_widget( 'Genesis_Latest_Tweets_Widget' );

		require_once( GLTW_INC . '/functions.php' );
gltw_log('inc-functions');
gltw_log('twitter-api-core');
		require_once( GLTW_API . '/twitter-api-core.php' );
gltw_log('twitter-api');
		require_once( GLTW_API . '/twitter-api.php' );

gltw_log('inc-widget');
		require_once( GLTW_INC . '/xhtml-widget.php' );
		
		register_widget( 'Genesis_Official_Twitter_Widget' );
		
		// Include application settings panel if in admin area
		if( is_admin() )
			require_once( GLTW_INC . '/admin.php' );

	}
	
}