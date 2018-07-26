<?php
/**
 * Plugin Name: TwitchPress
 * Plugin URI: https://twitchpress.wordpress.com/
 * Github URI: https://github.com/RyanBayne/TwitchPress
 * Description: Add Twitch stream and channel management services to WordPress. 
 * Version: 2.0.2
 * Author: Ryan Bayne
 * Author URI: https://ryanbayne.wordpress.com/
 * Requires at least: 4.7
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

// Core files.                                            
include_once( 'includes/functions.twitchpress-core.php' );
include_once( 'includes/functions.twitchpress-credentials.php' );
include_once( 'includes/functions.twitchpress-validate.php' );

/**
 * Main TwitchPress Class.
 *
 * @class TwitchPress
 */
final class WordPressTwitchPress {
    
    /**
     * TwitchPress version.
     *
     * @var string
     */
    public $version = '2.0.2';

    /**
     * Minimum WP version.
     *
     * @var string
     */
    public $min_wp_version = '4.7';
    
    /**
     * The single instance of the class.
     *
     * @var TwitchPress
     * @since 2.1
     */
    protected static $_instance = null;

    /**
     * Session instance.
     *
     * @var TwitchPress_Session
     */
    public $session = null; 

    /**
    * Quick and dirty way to debug by adding values that are dumped in footer.
    * 
    * @var mixed
    */
    public $dump = array();
    
    /* Main application credentials */
    public $app_id = null; 
    public $app_secret = null;// Possibly change to protected?
    public $app_redirect = null;
    public $app_token = null;
    public $app_token_scopes = null; 
    
    /* Main users Twitch oauth credentials for channel specific requests */
    public $main_channels_code = null;
    public $main_channels_wpowner_id  = null;
    public $main_channels_token = null;
    public $main_channels_refresh = null; 
    public $main_channels_scopes = null;
    public $main_channels_name = null;
    public $main_channels_id = null;
     
    /*
    
        $arr[ 'twitchpress_apiversion' ] = array();
    
    // Twitch Application Credentials Group
    $arr[ 'twitchpress_app_id' ] = array();// Client ID
    $arr[ 'twitchpress_app_secret' ] = array();// Client Secret
    $arr[ 'twitchpress_app_redirect' ] = array();// Redirect URL
    $arr[ 'twitchpress_app_token' ] = array();// Generated Token
    $arr[ 'twitchpress_app_token_scopes' ] = array();// Tokens Scopes
    
    // API calls made on behalf 
    $arr[ 'twitchpress_main_channels_code' ] = array();// Main users own channel oauth code. 
    $arr[ 'twitchpress_main_channels_wpowner_id' ] = array();// WordPress ID of the main channel owner. 
    $arr[ 'twitchpress_main_channels_token' ] = array();// Main channels oauth token. 
    $arr[ 'twitchpress_main_channels_refresh' ] = array();// Main channels oauth refresh token. 
    $arr[ 'twitchpress_main_channels_scopes' ] = array();// Main users accepted API scope. 
    $arr[ 'twitchpress_main_channel_name' ] = array();// Main channel name (this might be the title of channel and not lowercase, please confirm)
    $arr[ 'twitchpress_main_channel_id' ] = array();// Main channels Twitch ID (same as user ID)
    
    
    $arr[ 'twitchpress_main_code' ] = array();// Generated on behalf of the main user. // depreciated 
    $arr[ 'twitchpress_main_token' ] = array();// Generated on behalf of the main user. // depreciated
    $arr[ 'twitchpress_main_token_scopes' ] = array();// Generated on behalf of the main user. // depreciated  
    
    // Depreciated
    $arr[ 'twitchpress_main_client_secret' ] = array();// Depreciated use twitchpress_app_secret
    $arr[ 'twitchpress_main_client_id' ] = array();// Depreciated use twitchpress_main_client_id
    $arr[ 'twitchpress_main_redirect_uri' ] = array();// Depreciated use twitchpress_app_redirect
    $arr[ 'twitchpress_main_channel_postid' ] = array();// Generated on behalf of the main user.
    
    */
    
    /**
     * Main TwitchPress Instance.
     *
     * Ensures only one instance of TwitchPress is loaded or can be loaded.
     *
     * @since 1.0
     * @static
     * @see WordPressSeed()
     * @return TwitchPress - Main instance.
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }                    
        return self::$_instance;
    }

    /**
     * Cloning TwitchPress is forbidden.
     * @since 1.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'You\'re not allowed to do that!', 'twitchpress' ), '1.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     * @since 1.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'You\'re not allowed to do that!', 'twitchpress' ), '1.0' );
    }

    /**
     * Auto-load in-accessible properties on demand.
     * @param mixed $key
     * @return mixed
     */
    public function __get( $key ) {
        if ( in_array( $key, array( 'mailer' ) ) ) {
            return $this->$key();
        }
    }   
    
    /**
     * TwitchPress Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->load_debugger();        
        $this->includes();
        $this->init_hooks();
        
        $this->available_languages = array(
            //'en_US' => 'English (US)',
            //'fr_FR' => 'Français',
            //'de_DE' => 'Deutsch',
        );
                    
        do_action( 'twitchpress_loaded' );
    }

    /**
     * Define TwitchPress Constants.      
     */
    private function define_constants() {
        
        $upload_dir = wp_upload_dir();

        // Establish which Twitch API version to use.
        $api_version = get_option( 'twitchpress_apiversion' ); 
        if( $api_version == '6' )
        {
            if ( ! defined( 'TWITCHPRESS_API_NAME' ) ) { define( 'TWITCHPRESS_API_NAME', 'helix' ); }
            if ( ! defined( 'TWITCHPRESS_API_VERSION' ) ){ define( 'TWITCHPRESS_API_VERSION', '6' );}        
        }
        else
        {
            if ( ! defined( 'TWITCHPRESS_API_NAME' ) ) { define( 'TWITCHPRESS_API_NAME', 'kraken' ); }
            if ( ! defined( 'TWITCHPRESS_API_VERSION' ) ){ define( 'TWITCHPRESS_API_VERSION', '5' );}
        }  

        if(!defined( "TWITCHPRESS_CURRENTUSERID" ) ){define( "TWITCHPRESS_CURRENTUSERID", get_current_user_id() );}
              
        // Main (package) constants.
        if ( ! defined( 'TWITCHPRESS_ABSPATH' ) ) {           define( 'TWITCHPRESS_ABSPATH', __FILE__ ); }
        if ( ! defined( 'TWITCHPRESS_PLUGIN_BASENAME' ) ) {   define( 'TWITCHPRESS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) ); }
        if ( ! defined( 'TWITCHPRESS_PLUGIN_DIR_PATH' ) ) {   define( 'TWITCHPRESS_PLUGIN_DIR_PATH', plugin_dir_path( __FILE__ ) ); }
        if ( ! defined( 'TWITCHPRESS_VERSION' ) ) {           define( 'TWITCHPRESS_VERSION', $this->version ); }
        if ( ! defined( 'TWITCHPRESS_MIN_WP_VERSION' ) ) {    define( 'TWITCHPRESS_MIN_WP_VERSION', $this->min_wp_version ); }
        if ( ! defined( 'TWITCHPRESS_UPLOADS_DIR' ) ) {       define( 'TWITCHPRESS_UPLOADS_DIR', $upload_dir['basedir'] . 'twitchpress-uploads/' ); }
        if ( ! defined( 'TWITCHPRESS_LOG_DIR' ) ) {           define( 'TWITCHPRESS_LOG_DIR', TWITCHPRESS_PLUGIN_DIR_PATH . 'twitchpress-logs/' ); }
        if ( ! defined( 'TWITCHPRESS_SESSION_CACHE_GROUP')) { define( 'TWITCHPRESS_SESSION_CACHE_GROUP', 'twitchpress_session_id' ); }
        if ( ! defined( 'TWITCHPRESS_DEV_MODE' ) ) {          define( 'TWITCHPRESS_DEV_MODE', false ); }
        if ( ! defined( 'TWITCHPRESS_WORDPRESSORG_SLUG' ) ) { define( 'TWITCHPRESS_WORDPRESSORG_SLUG', false ); }
                           
        // Support (project) constants.
        if ( ! defined( 'TWITCHPRESS_HOME' ) ) {              define( 'TWITCHPRESS_HOME', 'https://wordpress.org/plugins/channel-solution-for-twitch' ); }
        if ( ! defined( 'TWITCHPRESS_FORUM' ) ) {             define( 'TWITCHPRESS_FORUM', 'https://wordpress.org/support/plugin/channel-solution-for-twitch' ); }
        if ( ! defined( 'TWITCHPRESS_TWITTER' ) ) {           define( 'TWITCHPRESS_TWITTER', false ); }
        if ( ! defined( 'TWITCHPRESS_DONATE' ) ) {            define( 'TWITCHPRESS_DONATE', 'https://www.patreon.com/ryanbayne' ); }
        if ( ! defined( 'TWITCHPRESS_SKYPE' ) ) {             define( 'TWITCHPRESS_SKYPE', 'https://join.skype.com/gxXhLoy6ce8e' ); }
        if ( ! defined( 'TWITCHPRESS_GITHUB' ) ) {            define( 'TWITCHPRESS_GITHUB', 'https://github.com/RyanBayne/TwitchPress' ); }
        if ( ! defined( 'TWITCHPRESS_SLACK' ) ) {             define( 'TWITCHPRESS_SLACK', false ); }
        if ( ! defined( 'TWITCHPRESS_DOCS' ) ) {              define( 'TWITCHPRESS_DOCS', false ); }
        if ( ! defined( 'TWITCHPRESS_DISCORD' ) ) {           define( 'TWITCHPRESS_DISCORD', 'https://discord.gg/NaRB3wE' ); }
       
        // Author (social) constants - can act as default when support constants are false.                                                                                                              
        if ( ! defined( 'TWITCHPRESS_AUTHOR_HOME' ) ) {       define( 'TWITCHPRESS_AUTHOR_HOME', 'https://ryanbayne.wordpress.com' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_FORUM' ) ) {      define( 'TWITCHPRESS_AUTHOR_FORUM', false ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_TWITTER' ) ) {    define( 'TWITCHPRESS_AUTHOR_TWITTER', 'http://www.twitter.com/Ryan_R_Bayne' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_FACEBOOK' ) ) {   define( 'TWITCHPRESS_AUTHOR_FACEBOOK', 'https://www.facebook.com/ryanrbayne' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_DONATE' ) ) {     define( 'TWITCHPRESS_AUTHOR_DONATE', 'https://www.patreon.com/zypherevolved' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_SKYPE' ) ) {      define( 'TWITCHPRESS_AUTHOR_SKYPE', 'https://join.skype.com/gNuxSa4wnQTV' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_GITHUB' ) ) {     define( 'TWITCHPRESS_AUTHOR_GITHUB', 'https://github.com/RyanBayne' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_LINKEDIN' ) ) {   define( 'TWITCHPRESS_AUTHOR_LINKEDIN', 'https://www.linkedin.com/in/ryanrbayne/' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_DISCORD' ) ) {    define( 'TWITCHPRESS_AUTHOR_DISCORD', 'https://discord.gg/PcqNqNh' ); }
        if ( ! defined( 'TWITCHPRESS_AUTHOR_SLACK' ) ) {      define( 'TWITCHPRESS_AUTHOR_SLACK', 'https://ryanbayne.slack.com/threads/team/' ); }
        
        // Twitch API
        if( ! defined( "TWITCHPRESS_KEY_NAME" ) ){               define( "TWITCHPRESS_KEY_NAME", 'name' );}
        if( ! defined( "TWITCHPRESS_DEFAULT_TIMEOUT" ) ){        define( "TWITCHPRESS_DEFAULT_TIMEOUT", 5 );}
        if( ! defined( "TWITCHPRESS_DEFAULT_RETURN_TIMEOUT" ) ){ define( "TWITCHPRESS_DEFAULT_RETURN_TIMEOUT", 20 );}
        if( ! defined( "TWITCHPRESS_TOKEN_SEND_METHOD" ) ){      define( "TWITCHPRESS_TOKEN_SEND_METHOD", 'HEADER' );}
        if( ! defined( "TWITCHPRESS_RETRY_COUNTER" ) ){          define( "TWITCHPRESS_RETRY_COUNTER", 2 );}
        if( ! defined( "TWITCHPRESS_CERT_PATH" ) ){              define( "TWITCHPRESS_CERT_PATH", '' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_DEFAULT" ) ){     define( "TWITCHPRESS_CALL_LIMIT_DEFAULT", '15' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_DOUBLE" ) ){      define( "TWITCHPRESS_CALL_LIMIT_DOUBLE", '30' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_MAX" ) ){         define( "TWITCHPRESS_CALL_LIMIT_MAX", '60' );}
        if( ! defined( "TWITCHPRESS_CALL_LIMIT_SETTING" ) ){     define( "TWITCHPRESS_CALL_LIMIT_SETTING", TWITCHPRESS_CALL_LIMIT_MAX );}     
    
        // Library Integration
        if ( ! defined( 'BUGNET_LOG_DIR' ) ) { define( 'BUGNET_LOG_DIR', TWITCHPRESS_LOG_DIR ); }        
    }
    
    /**
     * Include required core files.
     * 
     * @version 1.4
     */
    public function includes() {
        
        // SPL Autoloader Class
        include_once( 'includes/class.twitchpress-autoloader.php' );
        
        // Load the most common functions that need to be easily accessed by the entire site.
        include_once( plugin_basename( 'functions.php' ) );
        
        // Load class and libraries.
        require_once( 'includes/libraries/class.async-request.php' );
        require_once( 'includes/libraries/class.background-process.php' );            
        require_once( 'includes/class.twitchpress-post-types.php' );                
        require_once( 'includes/class.twitchpress-install.php' );
        require_once( 'includes/class.twitchpress-ajax.php' );
        require_once( 'includes/libraries/allapi/loader.php' );
        require_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/functions.twitch-api-statuses.php' );
        require_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/class.twitch-api.php' );
        require_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/class.twitch-api-calls.php' );        
        require_once( 'includes/toolbars/class.twitchpress-toolbars.php' );        
        require_once( 'includes/class.twitchpress-listener.php' );
        require_once( 'includes/class.twitchpress-feeds.php' );
        require_once( 'includes/class.twitchpress-sync.php' );
        require_once( 'includes/class.twitchpress-history.php' );
        include_once( plugin_basename( 'shortcodes.php' ) );
        
        // Load Core Objects
        $this->load_core_objects();
        
        // Load Public Objects
        $this->load_public_objects();                    
    }
    
    /**
    * Load class objects required by this core plugin for any request (front or abck) 
    * or at all times by extensions. 
    * 
    * @version 1.0
    */
    private function load_core_objects() {
        
        // Create CORE Objects (new approach April 2018) 
        
        
        
             $this->set_twitch_application();
    $this->set_main_channel_oauth_credentails();
    $this->set_current_users_oauth_credentials(); 
        
        echo '<pre>';
        var_dump( __LINE__ );
        echo '</pre>';                       this is calling $GLOBAL['twitchpress'] before it exists  
                                            need to establish credentails before this object 
        $this->twitch_calls   = new TWITCHPRESS_Twitch_API_Calls();
        echo '<pre>';
        var_dump( __LINE__ );
        echo '</pre>';        
        
        
        
        
        $this->sync           = new TwitchPress_Systematic_Syncing();
        $this->public_notices = new TwitchPress_Public_PreSet_Notices();
                        
        // Initialize systematic syncing.
        $this->sync->init();
        
        // Load CORE files only required when logged into the administration side.     
        if ( twitchpress_is_request( 'admin' ) ) {
            include_once( 'includes/admin/class.twitchpress-admin.php' );
            include_once( 'includes/admin/class.twitchpress-admin-deactivate.php' );
        }
    }
    
    /**
    * Load objects only required for a front-end request.
    * 
    * @version 1.0
    */
    private function load_public_objects() {
        // Load classes only required when viewing frontend/public side.
        if ( twitchpress_is_request( 'frontend' ) ) {
            $this->frontend_includes();
        }   
    }

    /**
     * Hook into actions and filters. 
     * 
     * Extensions hook into the init() before and after TwitchPress full init.
     * 
     * @version 1.0 
     */
    private function init_hooks() {
        register_activation_hook( __FILE__, array( 'TwitchPress_Install', 'install' ) );
        register_deactivation_hook( __FILE__, array( 'TwitchPress_Deactivate', 'deactivate' ) );

        add_action( 'init', array( $this, 'init_system' ), 0 );
        add_action( 'init', array( $this, 'output_errors' ), 1 );
        add_action( 'init', array( $this, 'output_actions' ), 1 );            
        add_action( 'init', array( $this, 'output_filters' ), 1 );        
    }
    
    public function init_system() {

        // Before init action.
        do_action( 'before_twitchpress_init' );    

        // Init action.
        do_action( 'twitchpress_init' ); 
        
        // Collect required scopes from extensions and establish system requirements. 
        global $system_scopes_status;
        $system_scopes_status = array();
        
        // Scopes for admin only or main account functionality that is always used. 
        $system_scopes_status['admin']['core']['required'] = array();
        
        // Scopes for admin only or main account features that may not be used.
        $system_scopes_status['admin']['core']['optional'] = array(); 
                    
        // Scopes for functionality that is always used. 
        $system_scopes_status['public']['core']['required'] = array();
        
        // Scopes for features that may not be used.
        $system_scopes_status['public']['core']['optional'] = array(); 
        
        $system_scopes_status = apply_filters( 'twitchpress_update_system_scopes_status', $system_scopes_status );  
    }
    
    /**
     * Include required frontend files.
     */
    public function frontend_includes() {
        include_once( plugin_basename( 'includes/class.twitchpress-frontend-scripts.php' ) );  
        include_once( plugin_basename( 'includes/functions.twitchpress-frontend-notices.php' ) );  
        include_once( plugin_basename( 'includes/functions.twitchpress-frontend.php' ) );
        include_once( plugin_basename( 'includes/class.twitchpress-public-preset-notices.php' ) );              
    }

    /**
    * Load a debugging class: BugNet Library
    * 
    * @version 2.0
    */
    public function load_debugger() {   
        global $bugnet;
        include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/libraries/bugnet/class.bugnet.php' );
        $bugnet = new BugNet();
        $bugnet->log_directory = TWITCHPRESS_LOG_DIR;
        $bugnet->plugin_name = 'twitchpress';    
    }
    
    /**
    * Set the values required for the Twitch API application to make any request
    * to Twitch.tv (Kraken or Helix). 
    * 
    * @version 1.0
    */
    public function set_twitch_application() {

        $this->app_id = get_option( 'twitchpress_app_id', 0 ); 
        $this->app_secret = get_option( 'twitchpress_app_secret', 0 );
        $this->app_redirect = get_option( 'twitchpress_app_redirect', 0 );
        $this->app_token = get_option( 'twitchpress_app_token', 0 );
        $this->app_token_scopes = get_option( 'twitchpress_app_token_scopes', 0 ); 

    }
    
    /**
    * Set the values required to make channel specific queries to the site owners
    * main/official channel as setup when that user completes oAuth2. 
    * 
    * @version 1.0
    */
    public function set_main_channel_oauth_credentails() {
                    
        $this->main_channels_code = get_option( 'twitchpress_main_channels_code', 0 );
        $this->main_channels_wpowner_id  = get_option( 'twitchpress_main_channels_wpowner_id', 0 );
        $this->main_channels_token = get_option( 'twitchpress_main_channels_token', 0 );
        $this->main_channels_refresh = get_option( 'twitchpress_main_channels_refresh', 0 ); 
        $this->main_channels_scopes = get_option( 'twitchpress_main_channels_scopes', 0 );
        $this->main_channels_name = get_option( 'twitchpress_main_channel_name', 0 );
        $this->main_channels_id = get_option( 'twitchpress_main_channel_id', 0 );
         
        /*

        // API calls made on behalf 
        $arr[ 'twitchpress_main_channels_code' ] = array();// Main users own channel oauth code. 
        $arr[ 'twitchpress_main_channels_wpowner_id' ] = array();// WordPress ID of the main channel owner. 
        $arr[ 'twitchpress_main_channels_token' ] = array();// Main channels oauth token. 
        $arr[ 'twitchpress_main_channels_refresh' ] = array();// Main channels oauth refresh token. 
        $arr[ 'twitchpress_main_channels_scopes' ] = array();// Main users accepted API scope. 
        $arr[ 'twitchpress_main_channel_name' ] = array();// Main channel name (this might be the title of channel and not lowercase, please confirm)
        $arr[ 'twitchpress_main_channel_id' ] = array();// Main channels Twitch ID (same as user ID)
        
        
        $arr[ 'twitchpress_main_code' ] = array();// Generated on behalf of the main user. // depreciated 
        $arr[ 'twitchpress_main_token' ] = array();// Generated on behalf of the main user. // depreciated
        $arr[ 'twitchpress_main_token_scopes' ] = array();// Generated on behalf of the main user. // depreciated  
        
        // Depreciated
        $arr[ 'twitchpress_main_client_secret' ] = array();// Depreciated use twitchpress_app_secret
        $arr[ 'twitchpress_main_client_id' ] = array();// Depreciated use twitchpress_main_client_id
        $arr[ 'twitchpress_main_redirect_uri' ] = array();// Depreciated use twitchpress_app_redirect
        $arr[ 'twitchpress_main_channel_postid' ] = array();// Generated on behalf of the main user.
        
        */        
    }
    
    /**
    * Set current users Twitch API oauth credentials. Current user would need to have completed
    * the oAuth2 procedure for the option values to exist.
    * 
    * @version 1.0
    */
    public function set_current_users_oauth_credentials() {
  
    }
    
    /**
    * Output errors with a plain dump.
    * 
    * Pre-BugNet measure. 
    *     
    * @version 1.0
    */
    public function output_errors() {
        // Display Errors Tool            
        if( !twitchpress_are_errors_allowed() ) { return false; }
                 
        ini_set( 'display_errors', 1 );
        error_reporting(E_ALL);
        
        add_action( 'shutdown', array( $this, 'show_errors' ), 1 );
        add_action( 'shutdown', array( $this, 'print_errors' ), 1 );                    
    }
   
    public function output_actions() {
        if( 'yes' !== get_option( 'twitchpress_display_actions') ) { return; }
                                                                       
        add_action( 'shutdown', array( $this, 'show_actions' ), 1 );                                                               
    }
        
    public function output_filters() {
        if( 'yes' !== get_option( 'twitchpress_display_filters') ) { return; }
                                                                       
        add_action( 'shutdown', array( $this, 'show_filters' ), 1 );                                                               
    }

    public static function show_errors() {
        global $wpdb, $bugnet;
        echo '<div id="bugnet-wperror-dump">';       
            _e( '<h1>BugNet: Possible Errors</h1>', 'twitchpress' );
            $wpdb->show_errors( true );
        echo '</div>';   
    }
    
    public static function print_errors() {
        global $wpdb;       
        $wpdb->print_error();    
    }    
    
    public function show_actions() {
        global $wp_actions;

        echo '<div id="bugnet-wpactions-dump">';
        _e( '<h1>BugNet: WordPress Actions</h1>', 'twitchpress' );
        echo '<pre>';
        print_r( $wp_actions );
        echo '</pre>';
        echo '</div>';  
    }
 
    public function show_filters() {
        global $wp_filter;

        echo '<div id="bugnet-wpfilters-dump">';
        _e( '<h1>BugNet: WordPress Filters</h1>', 'twitchpress' );
        echo '<pre>';
        //print_r( $wp_filter['admin_bar_menu'] );
        print_r( $wp_filter );
        echo '</pre>';
        echo '</div>';   
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

    /**
     * Get Ajax URL (this is the URL to WordPress core ajax file).
     * @return string
     */
    public function ajax_url() {                
        return admin_url( 'admin-ajax.php', 'relative' );
    }   
}

endif;

if( !function_exists( 'TwitchPress' ) ) {
    /**
     * Main instance of TwitchPress.
     *
     * Returns the main instance of TwitchPress to prevent the need to use globals.
     *
     * @since  1.0
     * @return TwitchPress
     */
    function TwitchPress() {        
        return WordPressTwitchPress::instance();
    }

    // Global for backwards compatibility.
    global $GLOBALS;
    $GLOBALS['twitchpress'] = TwitchPress();
    
    // ["version"]=> string(5) "2.0.0" 
    // ["min_wp_version"]=> string(3) "4.7" 
    // ["session"]=> NULL ["bugnet"]=> NULL 
    // ["available_languages"]=> array(0) { } }  
    
    // Define some of these globals to make it easy for extensiosn to access them. 
    $GLOBALS['twitchpress']->main_channel_name = twitchpress_get_main_channels_name(); 
    $GLOBALS['twitchpress']->main_channel_id = twitchpress_get_main_channels_twitchid();
    $GLOBALS['twitchpress']->main_channel_owner_wpid = twitchpress_get_main_channels_wpowner_id();  

    // Set Twitch API credentials.
    $GLOBALS['twitchpress']->set_twitch_application();
    $GLOBALS['twitchpress']->set_main_channel_oauth_credentails();
    $GLOBALS['twitchpress']->set_current_users_oauth_credentials(); 
                        
}