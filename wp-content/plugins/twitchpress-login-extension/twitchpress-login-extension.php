<?php 
/*
Plugin Name: TwitchPress Login Extension
Version: 1.8.1
Plugin URI: http://twitchpress.wordpress.com
Description: Social login and register on WordPress using Twitch.
Author: Ryan Bayne             
Author URI: http://ryanbayne.wordpress.com
Text Domain: twitchpress-login
Domain Path: /languages
Copyright: © 2017 - 2018 Ryan Bayne
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html  
*/                                                                                                                         

// Prohibit direct script loading
defined( 'ABSPATH' ) || die( 'Direct script access is not allowed!' );

/**
 * Check if TwitchPress is active, else avoid activation.
 **/
if ( !in_array( 'channel-solution-for-twitch/twitchpress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    return;
}

/**
 * Required minimums and constants
 */
define( 'TWITCHPRESS_LOGIN_VERSION', '1.8.1' );
define( 'TWITCHPRESS_LOGIN_MIN_PHP_VER', '5.6.0' );
define( 'TWITCHPRESS_LOGIN_MIN_TP_VER', '2.3.0' );
define( 'TWITCHPRESS_LOGIN_MAIN_FILE', __FILE__ );
define( 'TWITCHPRESS_LOGIN_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'TWITCHPRESS_LOGIN_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

// We have a different class for each Twitch API version...
if( TWITCHPRESS_API_NAME == 'kraken' )
{
    include_once( plugin_basename( '/class.twitchpress-login-kraken.php' ) );    
}
else
{
    include_once( plugin_basename( '/class.twitchpress-login-helix.php' ) );    
}

