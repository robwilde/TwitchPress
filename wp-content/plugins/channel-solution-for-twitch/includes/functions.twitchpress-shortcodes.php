<?php  
/**
 * TwitchPress - Primary Shortcode File
 *
 * Shortcode files are included here, loaded and registered so that they can be
 * detected by other plugins.  
 *
 * @author   Ryan Bayne
 * @category Shortcodes
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
* Shortcode outputs a basic status for the giving channel. 
* 
* [twitchpress_channel_status_line channel_id=""]
* 
* @version 1.1
*/
function twitchpress_channel_status_shortcode( $atts ) {          
    $html_output = null;
           
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return '';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status' ) ) 
    {
        return $cache; 
    }
    
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        $twitchpress = new TWITCHPRESS_Twitch_API_Calls();
    }
    else
    {   # untested
        $helix = new TWITCHPRESS_Twitch_API();
    }

    // Get channel ID using the channel/username. 
    if( $atts['channel_id'] === null && $atts['channel_name'] !== null )
    {
        $user_object = $twitchpress->get_users( $atts['channel_name'] ); 
        
        if( isset( $user_object['users'][0]['_id'] ) )
        {
            $channel_id = $user_object['users'][0]['_id'];
        }
        else
        {
            return '';  
        }
    }
    elseif( $atts['channel_id'] !== null )
    {
        $channel_id = $atts['channel_id'];
    }
    
    // Get the stream. 
    $channel_object = $twitchpress->getStreamObject( $channel_id );     

    // Build $html_output and cache it and then return it.
    if( $channel_object === null )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';    
    } 
    else
    {                                  
        $html_output = '<p>' . __( 'Channel Live', 'twitchpress' ) . '</p>';  
    }
    
    set_transient( 'twitchpress_shortcode_channel_status', $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status', 'twitchpress_channel_status_shortcode' );

/**
* Shortcode outputs a status line for the giving channel. 
* 
* @version 1.0
*/
function twitchpress_channel_status_line_shortcode( $atts ) {          
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status_line' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return '';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status_line' ) ) 
    {
        return $cache; 
    }
    
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        $twitchpress = new TWITCHPRESS_Twitch_API_Calls();
    }
    else
    {   # untested
        $helix = new TWITCHPRESS_Twitch_API();
    }
    
    // Get channel ID using the channel/username. 
    if( $atts['channel_id'] === null && $atts['channel_name'] !== null )
    {
        $user_object = $twitchpress->get_users( $atts['channel_name'] ); 
        
        if( isset( $user_object['users'][0]['_id'] ) )
        {
            $channel_id = $user_object['users'][0]['_id'];
        }
        else
        {
            return '';  
        }
    }
    elseif( $atts['channel_id'] !== null )
    {
        $channel_id = $atts['channel_id'];
    }
    
    // Get the stream. 
    $channel_object = $twitchpress->getStreamObject( $channel_id );     

    if( $channel_object === null )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';   
    } 
    else
    {                                  
        $html_output = '<p>';        
        $html_output .= ' ' . esc_html( $channel_object['channel']['display_name'] ) . ' ';
        $html_output .= ' is playing ' . esc_html( $channel_object['game'] ) . ' ';
        $html_output .= ' ' . esc_html( $channel_object['stream_type'] ) . ' ';
        $html_output .= ' to ' . esc_html( $channel_object['viewers'] ) . ' viewers ';
        $html_output .= '</p>';
    }
    
    set_transient( 'twitchpress_shortcode_channel_status_line', $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status_line', 'twitchpress_channel_status_line_shortcode' );

/**
* Shortcode outputs a status box with some extra information for the giving channel.
* 
* @version 1.0
*/
function twitchpress_channel_status_box_shortcode( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status_box' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return '';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status_box' ) ) 
    {
        return $cache; 
    }
    
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        $twitchpress = new TWITCHPRESS_Twitch_API_Calls();
    }
    else
    {   # untested
        $helix = new TWITCHPRESS_Twitch_API();
    }
    
    // Get channel ID using the channel/username. 
    if( $atts['channel_id'] === null && $atts['channel_name'] !== null )
    {
        $user_object = $twitchpress->get_users( $atts['channel_name'] ); 
        
        if( isset( $user_object['users'][0]['_id'] ) )
        {
            $channel_id = $user_object['users'][0]['_id'];
        }
        else
        {
            return '';  
        }
    }
    elseif( $atts['channel_id'] !== null )
    {
        $channel_id = $atts['channel_id'];
    }
    
    // Get the stream. 
    $channel_object = $twitchpress->getStreamObject( $channel_id );     

    if( $channel_object === null )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';    
    } 
    else
    {                                  
        $html_output = '<div>';
        $html_output .= 'Channel: ' . $channel_object['channel']['display_name'] . ' ';
        $html_output .= '<br />Game: ' . $channel_object['game'] . ' ';
        $html_output .= '<br />Viewers: ' . $channel_object['viewers'] . ' ';
        $html_output .= '<br />Stream Type: ' . $channel_object['stream_type'] . ' ';
        $html_output .= '<br />Views: ' . $channel_object['channel']['views'] . ' ';
        $html_output .= '<br />Followers: ' . $channel_object['channel']['followers'] . ' ';
        $html_output .= '</div>';
        
        return $html_output;
    }
    
    set_transient( 'twitchpress_shortcode_channel_status_box', $html_output, 120 );
    
    return $html_output;    
}
add_shortcode( 'twitchpress_channel_status_box', 'twitchpress_channel_status_box_shortcode' );

/**
* Shortcode outputs an unordered list of channels with status.
* 
* @version 1.0
*/
function twitchpress_channels_status_list_shortcode( $atts ) {       

}
add_shortcode( 'twitchpress_channels_status_list', 'twitchpress_channels_status_list_shortcode' );

/**
* Displays a list of buttons for initiating oAuth for each API.
* 
* @version 2.0
*/
function shortcode_visitor_api_services_buttons( $atts ) {         
    global $post; 
    
    // Ensure visitor is logged into WordPress. 
    if( !is_user_logged_in() ) {
        return '<p>' . __( 'You must be logged into WordPress to view the full contents of this page.', 'twitchpress' );
    }
    
    $html_output = '        
    <table class="form-table">
        <tbody>        
            <tr>
                <th>
                    <p>
                        Service
                    </p>
                </th>
                <th> 
                    <p>
                        Status
                    </p>                        
                </th>
                <th> 
                    <p>
                        Authorize
                    </p>                        
                </th>                
            </tr>';
        
    $permalink = get_post_permalink( $post->ID, true );
    
    $atts = shortcode_atts( array(             
            //'channel_id'   => null
    ), $atts, 'twitchpress_visitor_api_services_buttons' );    
                          
    // Twitch
    if( class_exists( 'TWITCHPRESS_Twitch_API' ) )
    {   
        $twitch_api = new TWITCHPRESS_Twitch_API();

        // Set the users current Twitch oAuth status. 
        $twitchpress_oauth_status = __( 'Not Setup', 'twitchpress' );
        if( twitchpress_is_user_authorized( get_current_user_id() ) )
        {
            $twitchpress_oauth_status = __( 'Ready', 'twitchpress' );
        }
        
        // Create a local API state. 
        $state = array( 'redirectto' => $permalink,
                        'userrole'   => 'visitor',
                        'outputtype' => 'public',
                        'reason'     => 'personaloauth',
                        'function'   => __FUNCTION__
        );  
                                                                      
        $url = twitchpress_generate_authorization_url( twitchpress_get_visitor_scopes(), $state );
        unset($twitch_api); 

        $html_output .= '                
        <tr>
            <td>
                Twitch.tv
            </td>
            <td> 
                ' . $twitchpress_oauth_status . '                        
            </td>
            <td> 
                <a href="' . $url . '" class="button button-primary">Setup</a>                          
            </td>            
        </tr>';           
    }

    // Streamlabs 
    if( class_exists( 'TWITCHPRESS_Streamlabs_API' ) )
    {
        $streamlabs_api = new TWITCHPRESS_Streamlabs_API();
        
        $state = array( 'redirectto' => $permalink,
                        'userrole'   => 'visitor',
                        'outputtype' => 'public',
                        'reason'     => 'personaloauth',
                        'function'   => __FUNCTION__
        );   
             
        // Set the users current Twitch oAuth status. 
        $streamlabs_oauth_status = __( 'Not Setup', 'twitchpress' );
        if( $streamlabs_api->is_user_ready( get_current_user_id() ) )
        {
            $streamlabs_oauth_status = __( 'Ready', 'twitchpress' );
        }
        
        $url = $streamlabs_api->oauth2_url_visitors( $state );
        unset($streamlabs_api); 

        $html_output .= '                
        <tr>
            <td>
                Streamlabs.com
            </td>
            <td> 
                ' . $streamlabs_oauth_status . '                        
            </td>            
            <td>
                <a href="' . $url . '" class="button button-primary">Setup</a>               
            </td>            
        </tr>';                      
    }
    
    $html_output .= '            
        </tbody>
    </table>';
                          
    return $html_output;    
}
add_shortcode( 'twitchpress_visitor_api_services_buttons', 'shortcode_visitor_api_services_buttons' );