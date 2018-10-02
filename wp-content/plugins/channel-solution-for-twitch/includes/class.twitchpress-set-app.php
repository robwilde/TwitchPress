<?php
/**
 * TwitchPress - Twitch API application credentials are set here ONLY!
 * 
 * In theory this class will be initiated once during any request. So the goal
 * is to establish a working set of application credentials for use during that
 * request.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'TwitchPress_Set_App' ) ) :

class TwitchPress_Set_App {
    
    // Twitch API application credentials 
    public $app_id = null; 
    public $app_secret = null;
    public $app_redirect = null;
    public $app_token = null;
    public $app_token_scopes = null;

    public function __construct() {
        $this->set();
        $this->bugnet = new BugNet();
    }
    
    public function set() {
        $this->app_id           = get_option( 'twitchpress_app_id', 0 ); 
        $this->app_secret       = get_option( 'twitchpress_app_secret', 0 );
        $this->app_redirect     = get_option( 'twitchpress_app_redirect', 0 );
        $this->app_token        = get_option( 'twitchpress_app_token', 0 );
        $this->app_token_scopes = get_option( 'twitchpress_app_token_scopes', 0 ); 
        $this->app_expiry       = get_option( 'twitchpress_app_expiry', 0 ); 

        // Handle a missing token post setup completion...
        if( !$this->app_token && $this->app_id && $this->app_secret && $this->app_redirect ) 
        {
            add_action( 'init', array( $this, 'missing_token' ), 5 );     
        }
        else
        {
            // Ensure token is still valid else request new one...ijk
            add_action( 'init', array( $this, 'validate_token' ), 5 );
        } 
    }
    
    public function get( $value = null ) {
        if( $value ) {
            return eval( '$this->main_app_$value' );
        }   
        return array(
            'id'       => $this->app_id,
            'secret'   => $this->app_secret,
            'redirect' => $this->app_redirect,
            'token'    => $this->app_token,
            'scopes'   => $this->app_token_scopes
        );
    }

    /**
    * Use application credentials to request an access_token which has
    * gone missing from WP options table. 
    * 
    * This method assumes that all other applications credentials exist.
    * 
    * @version 1.0
    */
    public function missing_token() {

        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TwitchPress_Curl();
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
                
        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params( 
            'POST', 
            'https://api.twitch.tv/kraken/oauth2/token?client_id=' . twitchpress_get_app_id(), 
            false, 
            0, 
            false, 
            null, 
            false, 
            false 
        );
        
        // Add app credentails to the request body
        $call_object->set_curl_body( array(
            'client_id'        => $this->app_id,
            'client_secret'    => $this->app_secret,
            'redirect_uri'     => $this->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );

        // Start + make the request to Twitch.tv API in one line... 
        $call_object->call_setup( 'twitch' );
        
        // Was the access_token value in $curl_reply_body set? 
        if( !isset( $call_object->curl_reply_body->access_token ) ) {
            $this->bugnet->log( __FUNCTION__, __( 'Attempt to replace missing application token was rejected by the Twitch API or failed!', 'twitchpress' ), array(), true, false );            
            return false;
        }
               
        if( !isset( $call_object->curl_reply_body->expires_in ) ) {
            $this->bugnet->log( __FUNCTION__, __( 'No expiry giving when replacing missing token!', 'twitchpress' ), array(), true, false );            
            return false;
        }
        
        // Update option record and object registry...            
        twitchpress_update_app_token( $call_object->curl_reply_body->access_token );
        twitchpress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 

        $this->bugnet->log( __FUNCTION__, __( 'Replacement access_token and expiry stored.', 'twitchpress' ), array(), true, false );            
    }
    
    /**
    * Validates the current application access_token and if not valid
    * will request a new one. 
    * 
    * @version 1.0
    */
    public function validate_token() {
        
        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TwitchPress_Curl();
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
                
        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params( 
            'get', 
            'https://id.twitch.tv/oauth2/validate', 
            false, 
            0, 
            false, 
            null, 
            false, 
            false 
        );

        // Add the access_token as an OAuth header...
        $call_object->headers = array(
            'Authorization' => 'OAuth ' . $this->app_token,
            'Accept'        => 'application/vnd.twitchtv.v5+json'
        );

        // Start + make the request to Twitch.tv API in one line... 
        $call_object->call_setup( 'twitch' );

        // Was the access_token value in $curl_reply_body set? 
        if( isset( $call_object->response_code ) && $call_object->response_code == '200' ) {
            return true;
        }
                
        if( !isset( $call_object->response_code ) ) {
            $this->bugnet->log( __FUNCTION__, __( 'No response code has been returned when validating an access_token', 'twitchpress' ), array(), true, false );            
        }
               
        if( $call_object->response_code !== '200' ) {   
            $this->bugnet->log( __FUNCTION__, __( 'An access_token has expired because validation did not return code: 200', 'twitchpress' ), array(), true, false );            
        }
 
        // Request a new access_token (stored as app_token in the TwitchPress system)...
        $this->new_token();
        
    }
    
    /**
    * Request a new access_token - usually on the expiry of a previous because
    * the first token is generated during the Setup Wizard. 
    * 
    * @version 1.0
    */
    public function new_token() {
 
        // Create our own special Curl object which uses WP_Http_Curl()
        $call_object = new TwitchPress_Curl();
        $call_object->originating_function = __FUNCTION__;
        $call_object->originating_line = __LINE__;
  
        // Set none API related parameters i.e. cache and rate controls...
        $call_object->call_params( 
            'POST', 
            'https://api.twitch.tv/kraken/oauth2/token?client_id=' . twitchpress_get_app_id(), 
            false, 
            0, 
            false, 
            null, 
            false, 
            false 
        );
        
        // Add app credentails to the request body
        $call_object->set_curl_body( array(
            'client_id'        => $this->app_id,
            'client_secret'    => $this->app_secret,
            'redirect_uri'     => $this->app_redirect,
            'grant_type'       => 'client_credentials'
        ) );

        // Start + make the request to Twitch.tv API in one line... 
        $call_object->call_setup( 'twitch' );
        
        // Was the access_token value in $curl_reply_body set? 
        if( !isset( $call_object->curl_reply_body->access_token ) ) {
            $this->bugnet->log( __FUNCTION__, __( 'Request for a new access_token was rejected by the Twitch API or failed!', 'twitchpress' ), array(), true, false );            
            return false;
        }
               
        if( !isset( $call_object->curl_reply_body->expires_in ) ) {
            $this->bugnet->log( __FUNCTION__, __( 'No expiry giving with a new access_token!', 'twitchpress' ), array(), true, false );            
        }
        
        // Update option record and object registry...            
        twitchpress_update_app_token( $call_object->curl_reply_body->access_token );
        twitchpress_update_app_token_expiry( $call_object->curl_reply_body->expires_in ); 

        $this->bugnet->log( __FUNCTION__, __( 'New access_token and expiry time stored.', 'twitchpress' ), array(), true, false );               
    }
}

endif;

TwitchPress_Object_Registry::add( 'twitchapp', new TwitchPress_Set_App() );