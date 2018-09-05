<?php 
/*
Plugin Name: TwitchPress Streamlabs Extension
Version: 1.2.0
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
define( 'TWITCHPRESS_STREAMLABS_VERSION', '1.2.0' );
define( 'TWITCHPRESS_STREAMLABS_MIN_PHP_VER', '5.6.0' );
define( 'TWITCHPRESS_STREAMLABS_MIN_TP_VER', '2.3.0' );
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
        * Store user data here during a procedure to help avoid 
        * repeating database queries.  
        * 
        * @var mixed
        */
        public $users = array();        

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
            $this->load_dependencies();
            
            /**
                Do things here required before TwitchPress core plugin does init. 
            */
            
            add_action( 'twitchpress_init', array( $this, 'after_twitchpress_init' ) );
        }
        
        public function after_twitchpress_init() {

            // Load the Streamlabs API with default profile.
            //$this->streamlabs_api = new TWITCHPRESS_All_API( 'streamlabs', 'default' );
            $this->streamlabs_api = new TWITCHPRESS_Streamlabs_API( 'default' );
            
            // Set a false value to prevent any attempt to make requests.  
            if( !$this->streamlabs_api->is_app_set() ) {
    
            }

            // Attack the rest of our hooks.
            $this->attach_remaining_hooks();    
        }
            
        /**
         * Load all plugin dependencies.
         */
        public function load_dependencies() {

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
        private function attach_remaining_hooks() {
                                      
            // Actions
            add_action( 'twitchpress_allapi_application_update_streamlabs' , array( $this, 'do_application_being_updated' ), 5, 4 );

            // Shortcodes
            add_shortcode( 'twitchpress_streamlabs_current_users_points', array( $this, 'shortcode_twitchpress_streamlabs_current_users_points') );
                                  
            // Filters
            add_filter( 'twitchpress_get_sections_users', array( $this, 'settings_add_section_users' ), 50 );
            add_filter( 'twitchpress_get_settings_users', array( $this, 'settings_add_options_users' ), 50 );
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            add_filter( 'twitchpress_update_system_scopes_status', array( $this, 'update_system_scopes_status' ), 1, 1 );

            // User Table Filters
            add_filter( 'manage_users_columns', array( $this, 'user_table_streamlabs_points' ) );
            add_filter( 'manage_users_custom_column', array( $this, 'user_table_streamlabs_points_row' ), 10, 3 );
                                          
            // Add sections and settings to core pages.
            add_filter( 'twitchpress_get_sections_users', array( $this, 'settings_add_section_users' ) );
            add_filter( 'twitchpress_get_settings_users', array( $this, 'settings_add_options_users' ) );
            add_filter( 'twitchpress_get_sections_otherapi', array( $this, 'settings_add_api_section'), 5 );
            add_filter( 'twitchpress_otherapi_switches_settings', array( $this, 'settings_add_api_switches'), 5 );

            // Other hooks.
            add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
            add_filter( 'twitchpress_update_system_scopes_status', array( $this, 'update_system_scopes_status' ), 1, 1 );
                    
            do_action( 'twitchpress_sync_loaded' );
        }

        public static function install() {
            
        }
        
        public static function deactivate() { 
            
        }

        public function settings_add_api_section( $filtered_array ) {
            return array_merge( $filtered_array, array( 'streamlabs' => __( 'Streamlabs', 'twitchpress' ) ) );   
        }
        
        /**
        * Filters list of AllAPI activation switches by adding one for Streamlabs.
        * 
        * @param mixed $filtered_array
        * 
        * @version 1.0
        */
        public function settings_add_api_switches( $filtered_array ) {

            $last_item_key = count ( $filtered_array ) - 1;
            
            // Get the last item (not an option)
            $last = end( $filtered_array );
                               
            // Remove last item.
            unset( $filtered_array[ $last_item_key ] );
            
            // Add our extensions API switchs.
            $merged = array_merge( $filtered_array, array( array(
                'title'         => __( 'Streamlabs API test', 'twitchpress' ),
                'desc'          => __( 'Activate Streamlabs Services.', 'twitchpress' ),
                'id'            => 'twitchpress_switch_streamlabs_api_services',
                'type'          => 'checkbox',
                'default'       => 'no',
                'checkboxgroup' => 'start',
                'autoload'      => false,
            ) ) );            
            
            $merged = array_merge( $merged, array( array(
                'desc'            => __( 'Log Streamlabs API Activity', 'twitchpress' ),
                'id'              => 'twitchpress_switch_streamlabs_api_logs',
                'default'         => 'yes',
                'type'            => 'checkbox',
                'checkboxgroup'   => '',
                'show_if_checked' => 'yes',
                'autoload'        => false,
            ) ) );
        
            // Re-add the last item.
            $merged[] = $last;

            return $merged;
        }

        /**
        * Runs when an API's credentials are being changed. 
        * 
        * GitHub issue created because the $redirect_uri is not being applied yet. 
        * https://github.com/RyanBayne/TwitchPress/issues/263
        * 
        * @param mixed $service
        * @param mixed $redirect_uri
        * @param mixed $key
        * @param mixed $secret
        */
        public function do_application_being_updated( $service, $redirect_uri, $key, $secret ) { 
            // Update Streamlabs object with newly submitted redirect URI.   
            $this->streamlabs_api->allapi_app_uri = $redirect_uri; 
                                                 
            // Generate local oauth state credentials for security.
            $new_state = $this->streamlabs_api->new_state( array (             
                'redirectto' => admin_url( 'admin.php?page=twitchpress&tab=otherapi&section=streamlabs' ),
                'userrole'   => 'administrator',
                'outputtype' => 'admin',// use to configure output levels, sensitivity of data and styling.
                'reason'     => 'streamlabsextensionowneroauth2request',// use in conditional statements to access applicable procedures.
                'function'   => __FUNCTION__,
                'file'       => __FILE__,
            ));  
            
            // Add the random state key for our credentials to the API request for validation on return. 
            $uri = add_query_arg( 'state', $new_state['statekey'], $this->streamlabs_api->oauth2_url_mainaccount() );

            twitchpress_redirect_tracking( $uri, __LINE__, __FUNCTION__, __FILE__ );
            exit;
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
                //'testsectionalpha'  => __( 'Test Section Repeat One', 'twitchpress-streamlabs' ),
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
     
        public function user_table_streamlabs_points( $column ) {
            $column['streamlabs_points'] = 'Streamlabs Points';
            return $column;
        }

        public function user_table_streamlabs_points_row( $val, $column_name, $wp_user_id ) {
            switch ($column_name) {
                case 'streamlabs_points' :
                
                    // Confirm that the current user has setup Streamlabs access. 
                    if( !$this->streamlabs_api->is_user_ready( $wp_user_id ) )
                    {
                        return __( 'Not Ready', 'twitchpress' );
                    }
                    
                    global $GLOBALS;
                    $main_channel = TwitchPress_Object_Registry::get( 'mainchannelauth' );
                    $points = $this->streamlabs_api->get_users_points_meta( $wp_user_id, $main_channel->main_channels_name );
                    if( !$points ) { return 0; }

                    break;
                default:
            }
            return $val;
        }               
        
        public function shortcode_twitchpress_streamlabs_current_users_points( $atts ) {     
            $html_output = 0;
            if( !is_user_logged_in() ) {
                return __( 'Please Login', 'twitchpress' );
            }
            
            if( !$this->streamlabs_api->is_user_ready( get_current_user_id() ) ) {
                return __( 'Not Setup', 'twitchpress' );
            }

            $points = $this->get_current_users_points();
            
            if( !$points ) {
                return $html_output;
            }       
                           
            return $html_output;      
        }       
     
        public function get_current_users_points() {
            global $GLOBALS;
            $main_channel = TwitchPress_Object_Registry::get( 'mainchannelauth' );
            return $this->streamlabs_api->get_users_points_meta( get_current_user_id(), $main_channel->main_channel_name );
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