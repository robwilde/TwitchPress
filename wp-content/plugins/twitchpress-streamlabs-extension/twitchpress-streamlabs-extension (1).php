<?php 
/*
Plugin Name: TwitchPress Streamlabs Extension
Version: 1.0.0
Plugin URI: http://twitchpress.wordpress.com
Description: Streamlabs extension for the TwitchPress system.
Author: Ryan Bayne
Author URI: http://ryanbayne.wordpress.com
Text Domain: twitchpress-streamlabs
Domain Path: /languages
Copyright: © 2018 Ryan Bayne
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html
 
GPL v3 

This program is free software downloaded from WordPress.org: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. This means
it can be provided for the sole purpose of being developed further
and we do not promise it is ready for any one persons specific needs.
See the GNU General Public License for more details.

See <http://www.gnu.org/licenses/>.
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
define( 'TWITCHPRESS_STREAMLABS_VERSION', '1.0.0' );
define( 'TWITCHPRESS_STREAMLABS_MIN_PHP_VER', '5.6.0' );
define( 'TWITCHPRESS_STREAMLABS_MIN_TP_VER', '1.6.1' );
define( 'TWITCHPRESS_STREAMLABS_MAIN_FILE', __FILE__ );
define( 'TWITCHPRESS_STREAMLABS_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'TWITCHPRESS_STREAMLABS_PLUGIN_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

if ( ! class_exists( 'TwitchPress_Streamlabs' ) ) :

    class TwitchPress_Streamlabs {
        /**
         * @var Singleton
         */
        private static $instance;        

        /**
         * Get a *Singleton* instance of this class.
         *
         * @return Singleton The *Singleton* instance.
         * 
         * @version 1.0
         */
        public static function instance() {
            if ( null === self::$instance ) {
                self::$instance = new self();
            }
            return self::$instance;
        } 
        
        /**
         * Private clone method to prevent cloning of the instance of the
         * *Singleton* instance.
         *
         * @return void
         */
        private function __clone() {}

        /**
         * Private unserialize method to prevent unserializing of the *Singleton*
         * instance.
         *
         * @return void
         */
        private function __wakeup() {}    
        
        /**
         * Protected constructor to prevent creating a new instance of the
         * *Singleton* via the `new` operator from outside of this class.
         */
        protected function __construct() {
            
            $this->define_constants();
            
            // Load files and register actions required before TwitchPress core inits.
            add_action( 'before_twitchpress_init', array( $this, 'pre_twitchpress_init' ) );            
        }

        /**
         * Define TwitchPress Login Constants.
         * 
         * @version 1.0
         */
        private function define_constants() {
            
            $upload_dir = wp_upload_dir();
            
            // Main (package) constants.
            if ( ! defined( 'TWITCHPRESS_STREAMLABS_ABSPATH' ) )  { define( 'TWITCHPRESS_STREAMLABS_ABSPATH', __FILE__ ); }
            if ( ! defined( 'TWITCHPRESS_STREAMLABS_BASENAME' ) ) { define( 'TWITCHPRESS_STREAMLABS_BASENAME', plugin_basename( __FILE__ ) ); }
            if ( ! defined( 'TWITCHPRESS_STREAMLABS_DIR_PATH' ) ) { define( 'TWITCHPRESS_STREAMLABS_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
            
            // Constants for force hidden views to been seen for this plugin - do not rename these.
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_USERS' ) )    { define( 'TWITCHPRESS_SHOW_SETTINGS_USERS', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_BOT' ) )      { define( 'TWITCHPRESS_SHOW_SETTINGS_BOT', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_CHAT' ) )     { define( 'TWITCHPRESS_SHOW_SETTINGS_CHAT', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_JUKEBOX' ) )  { define( 'TWITCHPRESS_SHOW_SETTINGS_JUKEBOX', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_GAMES' ) )    { define( 'TWITCHPRESS_SHOW_SETTINGS_GAMES', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_COMMANDS' ) ) { define( 'TWITCHPRESS_SHOW_SETTINGS_COMMANDS', true ); }
            if ( ! defined( 'TWITCHPRESS_SHOW_SETTINGS_CONTENT' ) )  { define( 'TWITCHPRESS_SHOW_SETTINGS_CONTENT', true ); }      
        }  
                  
        public function pre_twitchpress_init() {
            $this->load_global_dependencies();
            
            /**
                Do things here required before TwitchPress core plugin does init. 
            */
            
            add_action( 'twitchpress_init', array( $this, 'after_twitchpress_init' ) );
        }
        
        public function after_twitchpress_init() {
            $this->attach_hooks();    
        }
        
        /**
         * Load all plugin dependencies.
         */
        public function load_global_dependencies() {

            // Include Classes
            // i.e. require_once( plugin_basename( 'classes/class-wc-connect-logger.php' ) );
            
            // Create Class Objects
            // i.e. $logger                = new WC_Connect_Logger( new WC_Logger() );
            
            // Set Class Objects In Singleton
            // i.e. $this->set_logger( $logger );

            // When doing admin_init load admin side dependencies.             
            add_action( 'admin_init', array( $this, 'load_admin_dependencies' ) );
        }
        
        public function load_admin_dependencies() {
             
        }
        
        /**
         * Hook into actions and filters.
         * 
         * @version 1.0
         */
        private function attach_hooks() {

            // Filters
            add_filter( 'twitchpress_get_sections_users', array( $this, 'settings_add_section_users' ), 50 );
            add_filter( 'twitchpress_get_settings_users', array( $this, 'settings_add_options_users' ), 50 );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            add_filter( 'twitchpress_update_system_scopes_status', array( $this, 'update_system_scopes_status' ), 1, 1 );
                                                   
            // Actions
            
            // Shortcodes
            add_shortcode( apply_filters( "twitchpress_connect_button_filter", 'twitchpress_connect_button' ), array( $this, 'shortcode_connect_button' ) );                        
        }

        public static function install() {
            
        }
        
        public static function deactivate() { 
            
        }
                      
        /**
         * Init the plugin after plugins_loaded so environment variables are set.
         * 
         * @version 1.0
         */
        public function init_hooks() {
               
            // Other hooks.
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            
            do_action( 'twitchpress_sync_loaded' );
        }
        
        public function init_filters() {
            // Add sections and settings to core pages.
            add_filter( 'twitchpress_get_sections_users', array( $this, 'settings_add_section_users' ) );
            add_filter( 'twitchpress_get_settings_users', array( $this, 'settings_add_options_users' ) );

            // Other hooks.
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            add_filter( 'twitchpress_update_system_scopes_status', array( $this, 'update_system_scopes_status' ), 1, 1 );                        
        }
        
        /**
        * Add scopes information (usually from extensions) to the 
        * system scopes status which is used to tell us what scopes are
        * required for the current system.
        * 
        * @param mixed $new_array
        */
        public function update_system_scopes_status( $filtered_array ) {
            
            $scopes = array();
            
            // Scopes for admin only or main account functionality that is always used. 
            $scopes['admin']['twitchpress-streamlabs-extension']['required'] = array();
            
            // Scopes for admin only or main account features that may not be used.
            $scopes['admin']['twitchpress-streamlabs-extension']['optional'] = array(); 
                        
            // Scopes for functionality that is always used. 
            $scopes['public']['twitchpress-streamlabs-extension']['required'] = array();
            
            // Scopes for features that may not be used.
            $scopes['public']['twitchpress-streamlabs-extension']['optional'] = array(); 
                        
            return array_merge_recursive( $filtered_array, $scopes );      
        }
                
        /**
        * Styles for login page hooked by login_enqueue_scripts
        * 
        * @version 1.0
        */
        public function twitchpress_login_styles() {

        }
        
        /**
        * Add a new section to the User settings tab.
        * 
        * @param mixed $sections
        * 
        * @version 1.0
        */
        public function settings_add_section_users( $sections ) {  
            global $only_section;
            
            // We use this to apply this extensions settings as the default view...
            // i.e. when the tab is clicked and there is no "section" in URL. 
            if( empty( $sections ) ){ $only_section = true; } else { $only_section = false; }
            
            // Add sections to the User Settings tab. 
            $new_sections = array(
                'testsection'  => __( 'Test Section', 'twitchpress-streamlabs' ),
            );

            return array_merge( $sections, $new_sections );           
        }
        
        /**
        * Add options to this extensions own settings section.
        * 
        * @param mixed $settings
        * 
        * @version 1.0
        */
        public function settings_add_options_users( $settings ) {
            global $current_section, $only_section;
            
            $new_settings = array();
            
            // This first section is default if there are no other sections at all.
            if ( 'testsection' == $current_section || !$current_section && $only_section ) {
                $new_settings = apply_filters( 'twitchpress_testsection_users_settings', array(
     
                    array(
                        'title' => __( 'Testing New Settings', 'twitchpress-streamlabs' ),
                        'type'     => 'title',
                        'desc'     => 'Attempting to add new settings.',
                        'id'     => 'testingnewsettings',
                    ),

                    array(
                        'desc'            => __( 'Checkbox Three', 'twitchpress-streamlabs' ),
                        'id'              => 'loginsettingscheckbox3',
                        'default'         => 'yes',
                        'type'            => 'checkbox',
                        'checkboxgroup'   => '',
                        'show_if_checked' => 'yes',
                        'autoload'        => false,
                    ),
                            
                    array(
                        'type'     => 'sectionend',
                        'id'     => 'testingnewsettings'
                    ),

                ));   
                
            }
            
            return array_merge( $settings, $new_settings );         
        }
        
        /**
         * Adds plugin action links
         *
         * @since 1.0.0
         */
        public function plugin_action_links( $links ) {
            $plugin_links = array(

            );
            return array_merge( $plugin_links, $links );
        }        

        /**
         * Get the plugin url.
         * @return string
         */
        public function plugin_url() {                
            return untrailingslashit( plugins_url( '/', __FILE__ ) );
        }

        /**
         * Get the plugin path.
         * @return string
         */
        public function plugin_path() {              
            return untrailingslashit( plugin_dir_path( __FILE__ ) );
        }                                                         
    }
    
endif;    

if( !function_exists( 'TwitchPress_Boiler_Ext' ) ) {

    function TwitchPress_Streamlabs_Ext() {        
        return TwitchPress_Streamlabs::instance();
    }

    // Global for backwards compatibility.
    $GLOBALS['twitchpress-streamlabs'] = TwitchPress_Streamlabs_Ext(); 
}

// Activation and Deactivation hooks.
register_activation_hook( __FILE__, array( 'TwitchPress_Streamlabs', 'install' ) );
register_deactivation_hook( __FILE__, array( 'TwitchPress_Streamlabs', 'deactivate' ) );