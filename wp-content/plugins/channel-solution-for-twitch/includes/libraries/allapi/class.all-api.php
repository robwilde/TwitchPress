<?php 
/**
 * All-API class helps to manage API consumption in a plugin consuming many API.
 * 
 * @class    TWITCHPRESS_All_API
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/All-API
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Make sure we meet our dependency requirements
if (!extension_loaded('curl')) trigger_error('cURL is not installed on your server, please install cURL to use TwitchPress.');
if (!extension_loaded('json')) trigger_error('PECL JSON or pear JSON is not installed, please install either PECL JSON or compile pear JSON to use TwitchPress.');

if( !class_exists( 'TWITCHPRESS_All_API' ) ) :

class TWITCHPRESS_All_API {

    /**
    * Human readable ID i.e. handle or username related to a person or group.
    * 
    * A unique string or integer. It is the value that a user
    * would identify their Twitch or YouTube channel by. 
    * 
    * @var mixed
    */
    protected $allapi_subject_name = null;// i.e. ZypheREvolved
    
    /**
    * Services internal ID (not always known to users). This could be
    * the numeric ID of a Twitch channel which is only used for technical purposes
    * and obtained using the API, not user input.
    * 
    * @var mixed
    */
    protected $allapi_subject_id = null;
    
    protected $allapi_service_object = null; 

    // App Credentials
    protected $allapi_service = null;// streamlabs
    protected $allapi_profile = null;// development transition support i.e. choose kraken or helix 
    
    // User Credentials
    protected $allapi_user_wordpress_id = null;
    protected $allapi_user_service_id   = null; 
    protected $allapi_user_oauth_code   = null;
    protected $allapi_user_oauth_token  = null;
    protected $allapi_user_scope        = array();
    
    // Debugging variables.
    public $allapi_call_name = 'Unknown';
    public $allapi_sandbox_mode = false;
           
    public function __construct( $service = 'none', $profile = 'default' ){ 
        
        if( !$service || $service == 'none' ) { return; }
         
        // Load logging, reporting and debugging service. 
        $this->bugnet = new BugNet();
        
        // Begin setting object to support desired API.
        $this->allapi_service = strtolower( $service );
        $this->allapi_profile = $profile;
        
        // Set the current or queried user credentials.
        $this->set_user_credentials();
    } 
    
    
    /**
    * Sets user API credentials.
    * 
    * @version 1.0
    */
    public function set_user_credentials( $user_id = null ) {
        if( !$user_id || !is_numeric( get_current_user_id() ) ) {
            if( !is_user_logged_in() ) { return false; }
            $user_id = get_current_user_id();
        }
        
        $this->user_access_token     = get_user_meta( $user_id, 'twitchpress_allapi_access_token_' . $this->allapi_service, true );    
        $this->user_refresh_token    = get_user_meta( $user_id, 'twitchpress_allapi_refresh_token_' . $this->allapi_service, true );
        $this->user_token_lifetime   = get_user_meta( $user_id, 'twitchpress_allapi_token_lifetime' . $this->allapi_service, true );
        $this->user_token_created_at = get_user_meta( $user_id, 'twitchpress_allapi_token_created_at_' . $this->allapi_service, true );
        $this->allapi_user_scope     = get_user_meta( $user_id, 'twitchpress_allapi_scope_' . $this->allapi_service, true );
        
        return true;
    }
    
    public static function init() {   
     
    }
    
    public function new_state( $attributes ) {
         $default = array( 
            'redirectto' => admin_url( 'index.php?page=twitchpress-setup&step=next_steps' ),
            'userrole'   => null,
            'outputtype' => 'admin',// use to configure output levels, sensitivity of data and styling.
            'reason'     => 'oauth2request',// use in conditional statements to access applicable procedures.
            'function'   => __FUNCTION__,
            'statekey'   => twitchpress_random14(),// add this to the "state=" value of the API request.
        ); 
        $final = wp_parse_args( $attributes, $default );  
        set_transient( 'twitchpress_streamlabs_oauthstate_' . $default['statekey'], $final ); 
        return $final; 
    }
}

endif;