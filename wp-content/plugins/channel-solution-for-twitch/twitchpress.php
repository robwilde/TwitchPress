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
    * BugNet library object and is used as a global.
    * 
    * @var mixed
    */
    public $bugnet = null;
            
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
        $this->includes();
        $this->init_hooks();
        $this->load_debugger();
        
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
        
        // Load class and libraries.
        include_once( 'includes/libraries/class.async-request.php' );
        include_once( 'includes/libraries/class.background-process.php' );            
        include_once( 'includes/class.twitchpress-post-types.php' );                
        include_once( 'includes/class.twitchpress-install.php' );
        include_once( 'includes/class.twitchpress-ajax.php' );
        include_once( 'includes/libraries/allapi/class.all-api.php' );
        include_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/functions.twitch-api-statuses.php' );
        include_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/class.twitch-api.php' );
        include_once( 'includes/libraries/twitch/' . TWITCHPRESS_API_NAME . '/class.twitch-api-calls.php' );        
        include_once( 'includes/toolbars/class.twitchpress-toolbars.php' );        
        include_once( 'includes/class.twitchpress-listener.php' );
        include_once( 'includes/class.twitchpress-feeds.php' );
        include_once( 'includes/class.twitchpress-sync.php' );
        include_once( 'includes/class.twitchpress-history.php' );
        
        // Create Objects (new approach April 2018)
        $this->sync           = new TwitchPress_Systematic_Syncing();
        $this->public_notices = new TwitchPress_Public_Notices();
                     
        // Initialize services.
        $this->sync->init();
        
        // Load files only required when logged into the administration side.     
        if ( twitchpress_is_request( 'admin' ) ) {
            include_once( 'includes/admin/class.twitchpress-admin.php' );
            include_once( 'includes/admin/class.twitchpress-admin-deactivate.php' );
        }

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

        add_action( 'init', array( $this, 'init' ), 0 );
        add_action( 'init', array( $this, 'output_errors' ), 1 );
        add_action( 'init', array( $this, 'output_actions' ), 1 );            
        add_action( 'init', array( $this, 'output_filters' ), 1 );        
    }
    
    public function init() {

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
        include_once( plugin_basename( 'includes/functions.twitchpress-frontend.php' ) );
        include_once( plugin_basename( 'includes/class.twitchpress-public-notices.php' ) );        
        include_once( plugin_basename( 'shortcodes.php' ) );                
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
    $GLOBALS['twitchpress'] = TwitchPress();
    // ["version"]=> string(5) "2.0.0" 
    // ["min_wp_version"]=> string(3) "4.7" 
    // ["session"]=> NULL ["bugnet"]=> NULL 
    // ["available_languages"]=> array(0) { } }  
}