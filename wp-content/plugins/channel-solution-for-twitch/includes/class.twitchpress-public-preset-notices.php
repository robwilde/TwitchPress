<?php
/**
 * TwitchPress - Public Notice Management (does not handle output)
 *     
 * Do not confuse this files contents with the notices classes/functions which provide
 * the functionality for building and outputting notices. 
 * 
 * This file is purely for managing and accessing the the text.  
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TwitchPress/Notices
 * @since    1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if( !class_exists( 'TwitchPress_Public_PreSet_Notices' ) ) :

/**
 * TwitchPress Class for accessing messages 
 * and registering new messages using extensions.
 * 
 * @class    TwitchPress_Public_PreSet_Notices
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/UI
 * @version  1.0.0
 */
class TwitchPress_Public_PreSet_Notices {
    public $message_array = array(); 
    
    public $types = array( 'success', 'warning', 'error', 'info' );
    
    public function __construct() {      
        $this->messages = $this->message_list(); 
                      
        // Apply filtering by extensions which need to add more messages to the array. 
        apply_filters( 'twitchpress_filter_public_notices_array', $this->messages );                           
    }
    
    /**
    * Get messages with as many filters as possible.
    * 
    * Allow this method to become complex.
    * 
    * @version 1.0
    */
    public function get_messages( $atts ) {
        $args = shortcode_atts( 
            array(
                'minimum_id'   => '0',
                'maximum_id'   => '99999',
                'ignore_types' => array(),
                'contains'     => '',
            ), 
            $atts
        ); 
        
        return $this->messages_array; 
    }
    
    /**
    * Get a message by TWITCHPRESS_PLUGIN_BASENAME and the array key for the
    * plugin/extension being queried. 
    * 
    * @param mixed $plugin
    * @param mixed $integer
    * 
    * @version 1.0
    */
    public function get_message_by_id( $plugin, $integer ) {
        return $this->messages[ $plugin ][ $integer ];    
    }
    
    /**
    * None strict search on ALL message values.
    * 
    * @version 1.0
    */
    public function get_message_by_search() {
        
    }
    
    public function get_message_by_title_strict() {
        
    }
    
    public function get_message_by_title_search() {
        
    }
    
    public function get_message_by_info_strict() {
        
    }
    
    public function get_message_by_info_search() {
        
    }

    /**
    * Get message type by integer. 
    * 
    * 0 = success
    * 1 = warning
    * 2 = error
    * 3 = info
    * 
    * @param mixed $integer
    * 
    * @version 1.0
    */
    public function get_type( $type_key ) {
        return $this->types[ $type_key ];
    }
    
    /**
    * List of the public notices available for applicable procedures.
    * 
    * TYPES
    * 0 = success
    * 1 = warning
    * 2 = error
    * 3 = info
    * 
    * @version 1.0
    */
    public function message_list() {      
        $messages_array = array();
        
        /* 0 = success, 1 = warning, 2 = error, 3 = info */
        $messages_array['twitchpress'][0] = array( 'type' => 0, 'title' => __( 'No Update Performed', 'twitchpress' ), 'info' => __( 'We already have the latest Twitch data from your account.', 'twitchpress' ) );

        // Tests
        $messages_array['twitchpress'][1] = array( 'type' => 1, 'title' => __( 'Test Message 2', 'twitchpress' ), 'info' => __( 'This is test message 2.', 'twitchpress' ) );
        $messages_array['twitchpress'][2] = array( 'type' => 2, 'title' => __( 'Test Message 3', 'twitchpress' ), 'info' => __( 'This is test message 3.', 'twitchpress' ) );
        $messages_array['twitchpress'][3] = array( 'type' => 3, 'title' => __( 'Test Message 4', 'twitchpress' ), 'info' => __( 'This is test message 4.', 'twitchpress' ) );
        
        // Ultimate Member Extension (temporary until filter is applied)
        $messages_array['umextension'][0] = array( 'type' => 2, 'title' => __( 'No Subscription Plan', 'twitchpress' ), 'info' => __( 'You do not have a subscription plan for this sites main Twitch.tv channel. Your UM role has been set to the default.', 'twitchpress' ) );
        $messages_array['umextension'][1] = array( 'type' => 3, 'title' => __( 'Hello Administrator', 'twitchpress' ), 'info' => __( 'Your request must be rejected because you are an administrator. We cannot risk reducing your access.', 'twitchpress' ) );
        $messages_array['umextension'][2] = array( 'type' => 2, 'title' => __( 'Ultimate Member Role Invalid', 'twitchpress' ), 'info' => __( 'Sorry, the role value for subscription plan [%s] is invalid. This needs to be corrected in TwitchPress settings.', 'twitchpress' ) );
        $messages_array['umextension'][3] = array( 'type' => 0, 'title' => __( 'Ultimate Member Role Updated', 'twitchpress' ), 'info' => __( 'Your community role is now %s because your subscription plan is %s.', 'twitchpress' ) );
        
        // Streamlabs (temporary pending filter)
        $messages_array['officialstreamlabsextension'][0] = array( 'type' => 2, 'title' => __( 'No Update Performed', 'twitchpress' ), 'info' => __( 'We already have the latest Streamlabs data for you.', 'twitchpress' ) );
             
        return $messages_array;
    } 
}

endif;
