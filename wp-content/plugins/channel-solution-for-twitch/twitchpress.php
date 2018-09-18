<?php
/**
 * Plugin Name: TwitchPress
 * Plugin URI: https://twitchpress.wordpress.com/
 * Github URI: https://github.com/RyanBayne/TwitchPress
 * Description: Add Twitch stream and channel management services to WordPress. 
 * Version: 2.5.0
 * Author: Ryan Bayne
 * Author URI: https://ryanbayne.wordpress.com/
 * Requires at least: 4.9
 * Tested up to: 4.9
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.txt
 * Domain Path: /i18n/languages/
 * 
 * @package TwitchPress
 * @category Core
 * @author Ryan Bayne (Gaming Handle: ZypheREvolved)
 * @license GNU General Public License, Version 3
 * @copyright 2016-2018 Ryan R. Bayne (SqueekyCoder@Gmail.com)
 */
 
// Exit if accessed directly. 
if ( ! defined( 'ABSPATH' ) ) { exit; }
                 
if ( ! class_exists( 'WordPressTwitchPress' ) ) :

if ( ! defined( 'TWITCHPRESS_VERSION' ) ) { define( 'TWITCHPRESS_VERSION', '2.5.0' ); }

if ( ! defined( 'TWITCHPRESS_ABSPATH' ) ) { define( 'TWITCHPRESS_ABSPATH', __FILE__ ); }

if ( ! defined( 'TWITCHPRESS_PLUGIN_BASENAME' ) ) { define( 'TWITCHPRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); }

if ( ! defined( 'TWITCHPRESS_PLUGIN_DIR_PATH' ) ) { define( 'TWITCHPRESS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
                
// Load object registry class to handle class objects without using $global. 
include_once( plugin_basename( 'includes/class.twitchpress-object-registry.php' ) );

// Load core functions with importance on making them available to third-party.                                            
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'install.php' );
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'functions.php' );
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'depreciated.php' );
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'integration.php' );
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'extensions.php' );
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/functions.twitchpress-formatting.php' );
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/functions.twitchpress-validate.php' );

// Run the plugin
include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'loader.php' );

endif;
