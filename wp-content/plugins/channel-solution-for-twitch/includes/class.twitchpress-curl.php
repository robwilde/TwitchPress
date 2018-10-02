<?php
/**
 * Uses @WP_Http_Curl to make API calls - designed to work with a specific format of data.
 * 
 * This class has been designed as a single procedure with the primary intention of using the entire object
 * in any way required. So the focus is on adding all required data to $this and 
 *
 * @class    TwitchPress_Curl
 * @version  1.0
 * @package  TwitchPress/ Classes
 * @category Class
 * @author   Ryan Bayne
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class TwitchPress_Curl extends WP_Http_Curl {
    
    private $WP_Http_Curl_Object = null;
    
    public $headers = array();
    
    /**
    * All variables will be placed into one array.
    * 
    * @var array
    */
    private $call_parameters = array(); 

    /**
    * get, put, delete, post
    * 
    * @var mixed
    */
    public $type = 'get';
    
    /**
    * The endpoint to be called.
    * 
    * @var string
    */
    public $endpoint = 'https://api.twitch.tv/kraken/games/top'; 
    
    /**
    * Can this call be cached? 
    * 
    * @var boolean
    */
    public $can_cache = false;
    
    /**
    * Cache storage time limit in seconds.
    * 
    * @var integer
    */
    public $cache_time = 120;
    
    /**
    * Use to tell TwitchPress to queue a request if the system is busy. 
    * Basically rendering the request a background operation. 
    * 
    * We have the class to do this properly! 
    * 
    * @var mixed
    */
    public $can_queue = false;
    
    /**
    * API name will be used for accessing the correct API endpoints.
    * 
    * @var string
    */
    public $api_name = 'twitch';
    
    /**
    * If call related to a WP user this is the WP user ID.
    * 
    * @var integer
    */
    public $giving_user = null;
    
    /**
    * WP user ID if the visitor is logged in.
    * 
    * @var integer
    */
    public $current_user = null;
    
    /**
    * Is the call being done for a specific WP user?
    * 
    * @var boolean 
    */
    public $user_specific = false;
    
    /**
    * If call fails should we try again?
    * 
    * @var boolean 
    */
    public $retry = false;
    
    /**
    * Servers version of curl
    * 
    * @var mixed
    */
    public $curl_version = null;
      
    /**
    * Call request within body included: method,body,user-agent,stream,filename,decompress
    * 
    * @var array      
    */
    private $curl_request = array();
    
    /**
    * The request body array as added to the parent request array.
    * 
    * @var array
    */
    private $curl_request_body = array();

    /**
    * The raw curl reply.
    * 
    * @var array
    */
    private $curl_reply = array(); 
    
    /**
    * Curl reply body after json_decode()
    * 
    * @var mixed
    */
    public $curl_reply_body = array();
    
    /**
    * The response value of a curl reply.
    * 
    * @var array
    */
    public $curl_reply_response = array();
    
    public $transient_name = null;
    
    /**
    * The code taking from the response array within the complete reply.
    * 
    * @var mixed
    */
    public $response_code = null;
    
    /**
    * The message taking from the response array within the complete reply.
    * 
    * @var mixed
    */
    public $response_message = null;
    
    public $originating_function = null;

    public $originating_line = null;
    
    public function __construct() {
        $this->curl_version = curl_version();
        $this->current_user = get_current_user_id();
    }        
    
    /**
    * Optional function offering all parameters when making a WP_Http_Curl 
    * call within the TwitchPress system. Use this to learn which values are available
    * and then pass the returned array to call_start() as the $optional_args value.
    * 
    * Any arguments not made will initially be populated by this classes defaults
    * but later we might have options that over-ride in-line defaults. 
    * 
    * @uses WP_Http_Curl() 
    * @uses curl_version() 
    * 
    * @param string $type get|post|put|delete
    * @param string $endpoint from functions.twitch-api-endpoints.php
    * @param string $calling_function __FUNCTION__
    * @param mixed $can_cache
    * @param mixed $cache_time
    * @param boolean $can_queue true allows rate limiting measures
    * 
    * @since 2.5.0
    * @version 2.0
    */    
    public function call_params( $type = null, $endpoint = null, $can_cache = null, $cache_time = null, $can_queue = null, $giving_user = null, $user_specific = null, $retry = null, $originating_function = null, $originating_line = null ) {
        if( $type )
        {
            $this->type = $type; 
        }
        else
        {
            // Apply the admin setting to override the classes default...
        }
        
        if( $endpoint )
        {
            $this->endpoint = $endpoint;
        }
        else
        {
            // Apply the admin setting to override the classes default...
        }
                
        if( $can_cache ) 
        {
            $this->can_cache = $can_cache;
        }
        else
        {
            // Apply the admin setting to override the classes default...
        }
                
        if( $cache_time )
        {
            $this->cache_time = $cache_time;
        }
        else
        {
            // Apply the admin setting to override the classes default...
        }
                
        if( $can_queue )
        {
            $this->can_queue = $can_queue;
        }
        else
        {
            // Apply the admin setting to override the classes default...
        }
                               
        if( $user_specific )
        {
            $this->user_specific = $user_specific;
        }
        else
        {
            // Apply the admin setting to override the classes default...
        }
                
        if( $retry )
        {
            $this->retry = $retry;    
        }
        else
        {
            // Apply the admin setting to override the classes default...
        }
        
        // Values that will not have settings...
        $this->giving_user = $giving_user;
        $this->originating_function = $originating_function;
        $this->originating_line = $originating_line;           
    }
                    
    /**
    * Required method when making a WP_Http_Curl call.
    * 
    * This one contains all available parameters to guide developers.
    * 
    * @uses WP_Http_Curl() 
    * @uses curl_version() 
    * 
    * @since 2.5.0
    * @version 1.0
    */    
    public function call_setup( $api_name, $optional_args = array() ) {
        // Set $this values
        $this->api_name = $api_name;
        
        // Create the WordPress Http Curl object
        $this->WP_Http_Curl_Object = new WP_Http_Curl();
        
        // Optional arguments submitted will over-ride $this default...
        $this->arguments( $optional_args );   
        
        // Determine if we should queue this request...
        $this->queue();
        
        // This will or will not set $this->curl_response...
        if( $this->can_cache )
        {        
            $this->get_transient();
        }
        
        // Execute call if transient holds nothing for us...
        if( !empty( $this->curl_reply ) ) 
        {
            $this->call_execute();
        }
        
        // Check, set and react to the response code...
        $this->check_response_code();
        
        // Use json_decode() to set $this->curl_response_body...
        $this->decode_body();         
    }
    
    /**
    * Prepare final request values - always use this after the developer has been giving
    * a chance to set the calls parameters.
    * 
    * @param mixed $optional_args
    * @version 1.0
    */
    public function arguments( $optional_args ) {
        
        // Apply the $optional_args but default to this classes in-line values...
        $this->call_parameters = wp_parse_args( $optional_args, array( 
                'type'             => $this->type,
                'endpoint'         => $this->endpoint,
                'can_cache'        => $this->can_cache,
                'cache_time'       => $this->cache_time,
                'can_queue'        => $this->can_queue,
                'api_name'         => $this->api_name,
                'giving_user'      => $this->giving_user,
                'current_user'     => $this->current_user,
                'user_specific'    => $this->user_specific,
                'retry'            => $this->retry,
                'function'         => $this->originating_function,
                'line'             => $this->originating_line,
            )
        );

        // Prepare arguments...
        $this->curl_request = array( 
            'headers'    => array(), 
            'method'     => strtoupper( $this->type ), 
            'body'       => $this->curl_request_body,
            'user-agent' => 'curl/' . $this->curl_version['version'],
            'stream'     => false,
            'filename'   => false,
            'decompress' => false 
        );      
        
        // Add custom headers...
        if( $this->headers && is_array( $this->headers ) ) 
        {
            $this->curl_request['headers'] = array_merge( $this->curl_request['headers'], $this->headers );    
        }
     
    }
    
    /**
    * Decide if the call can/should be made to Twitch.tv at this time or queue.
    * 
    * @version 1.0
    */
    public function queue() {
        if( !$this->can_queue ) { $this->call_execute(); }  
        
        // We will check for a recent request and result that matches
        $this->get_transient(); 
    }
    
    /**                        
    * Check if there is a transient cache of the exact same call!
    * 
    * Use transients with care as they are not 100% matched. Requests should only
    * be cached when confident that the originating call does not change it's 
    * parameters/credentails frequently or is not a request for critical data
    * i.e. a general information or statistics update might be cached to avoid
    * too many requests. 
    * 
    * @version 2.0
    */
    public function get_transient() {
        // Create transient name using encoded values of the curl request.
        $prepend = $this->api_name;
        $append = twitchpress_encode_transient_name( $this->endpoint, $this->originating_function, $this->originating_line );
        $this->transient_name = 'TwitchPress_' . $prepend . '_' . $append;
        
        // Is WordPress storing a transient? 
        $trans_check = get_transient( $this->transient_name );
        if( $trans_check ) 
        {
            // We have a transient to rely on...
            $transient_value = get_transient( $this->transient_name );
            
            // We just need the original raw response and move forward as normal...
            $this->curl_reply_response = $transient_value['response'];
        }    
    }
    
    public function call_execute() {

        $this->curl_reply = $this->WP_Http_Curl_Object->request( $this->endpoint, $this->curl_request );       
           
        // Should this curl request be cached?
        if( $this->can_cache )
        {
            $transient_value = array(
                'curl_body'     => $this->curl_request_body,
                'curl_request'  => $this->curl_request,
                'curl_reply'    => $this->curl_reply
            );
            
            // Cache the call and response.
            set_transient( $this->transient_name, $transient_value, $this->cache_time ); 
        }
    }  
    
    public function check_response_code() {
        if( isset( $this->curl_reply['response']['code'] ) ) {
            $this->response_code = $this->curl_reply['response']['code'];
        }   
        
        if( isset( $this->curl_reply['response']['message'] ) ) {
            $this->response_message = $this->curl_reply['response']['message'];    
        }
    }
                  
    public function decode_body() {
        if( isset( $this->curl_reply['response']['code'] ) && $this->curl_reply['response']['code'] == 200 ) {
            if( isset( $this->curl_reply['body'] ) ) {
                $this->curl_reply_body = json_decode( $this->curl_reply['body'] );
            }
        }
    }  
    
    public function user_output() {
        
    }
    
    public function developer_output() {
        
    }
    
    public function get_decoded_body() {
        if( !$this->curl_reply_body ) { return false; }
        return $this->curl_reply_body;    
    }
    
    public function set_curl_body( array $body ) {
        $this->curl_request_body = $body;
    }
}