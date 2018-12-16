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

function twitchpress_videos_shortcode( $atts ) {              
    $html_output = '';
    
    $atts = shortcode_atts( array(             
        'id'       => null,
        'user_id'  => null,
        'game_id'  => null,
        'after'    => null,
        'before'   => null,
        'first'    => null,
        'language' => null,
        'period'   => null,
        'sort'     => null,
        'type'     => null,
        'links'    => false
    ), $atts, 'twitchpress_video' );    
     
    $transient_code = $atts['id'] . $atts['user_id'] . $atts['game_id'];
    
    if( $cache = get_transient( 'twitchpress_video' . $transient_code ) ) {
       // return $cache;
    }
    
    // Get the stream. 
    $helix = new TWITCHPRESS_Twitch_API();
    
    $result = $helix->get_videos( $atts['id'], $atts['user_id'], $atts['game_id'] );
    
    if( $result ) 
    {
        if( $atts['links'] )
        {
            $html_output .= '<ol>'; 
            foreach( $result->data as $key => $item )
            {                          
                $html_output .= '<li>';
                $html_output .= '<a href="' . $item->url . '">' . $item->title . '</a>';         
                $html_output .= '</li>';        
            }
            $html_output .= '</ol>';
        }
        else
        {
            $html_output .= '<ol>'; 
            foreach( $result->data as $key => $item )
            {                          
                $html_output .= '<li>';
  
                $html_output .= '
                <iframe
                    src="https://player.twitch.tv/?video=' . $item->id . '&autoplay=false"
                    height="720"
                    width="1280"
                    frameborder="0"
                    scrolling="no"
                    allowfullscreen="true">
                </iframe>';   
                                      
                $html_output .= '</li>';        
            }
            $html_output .= '</ol>';                  
        }
    }

    set_transient( 'twitchpress_videos' . $transient_code, $html_output, 86400 );
    
    return $html_output;
}                                       
add_shortcode( 'twitchpress_videos', 'twitchpress_videos_shortcode' );
   
function twitchpress_get_top_games_list_shortcode( $atts ) {              
    $html_output = '';
    
    $atts = shortcode_atts( array(             
        'total'   => 10,
    ), $atts, 'twitchpress_get_top_games_list' );
    
    if( $cache = get_transient( 'twitchpress_get_top_games_list' . $atts['total'] ) ) {
        return $cache;
    }                 
    // Get the stream. 
    $helix = new TWITCHPRESS_Twitch_API();
    
    $result = $helix->get_top_games( null, null, $atts['total'] );
    
    if( $result && isset( $result->data[0] ) ) 
    {
        $html_output .= '<ol>'; 
        foreach( $result->data as $key => $game )
        {                          
            $html_output .= '<li>';
            $html_output .= $game->name;        
            $html_output .= '</li>';        
        }
        $html_output .= '</ol>';
    }

    set_transient( 'twitchpress_get_top_games_list' . $atts['total'], $html_output, 86400 );
    
    return $html_output;
}                                       
add_shortcode( 'twitchpress_get_top_games_list', 'twitchpress_get_top_games_list_shortcode' );

/**
* Shortcode outputs a basic status for the giving channel. 
* 
* [twitchpress_channel_status_line channel_id=""]
* 
* @version 1.1
*/
function twitchpress_channel_status_line_shortcode( $atts ) { 
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return twitchpress_channel_status_line_kraken( $atts );    
    }
    else
    {
        return twitchpress_channel_status_line_helix( $atts );    
    }
}
function twitchpress_channel_status_line_kraken( $atts ) {         
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
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status_line' ) ) 
    {
        return $cache; 
    }

    $twitchpress = new TWITCHPRESS_Twitch_API_Calls();

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
    
    set_transient( 'twitchpress_shortcode_channel_status_line', $html_output, 120 );
    
    return $html_output;
}
function twitchpress_channel_status_line_helix( $atts ) {  
    
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status' );
    
    // If no channel ID or name is giving...
    if( !$atts['channel_id'] && !$atts['channel_name'] ) {
        return __( 'Channel status-line shortcode has not been setup!', 'twitchpress' );      
    } 
    
    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $helix = new TWITCHPRESS_Twitch_API();
        $result = $helix->get_user_by_login_name_without_email_address( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            $html_output = sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 
    
    // Use cached HTML if we have a channel ID...
    if( $channel_id ) {
        $cache = get_transient( 'twitchpress_channel_status_line_' . $channel_id );
        if( $cache ) {
            return $cache;
        }
    }                  

    // Get the stream. 
    if( !$helix ){ $helix = new TWITCHPRESS_Twitch_API(); }
         
    $result = $helix->get_stream_by_userid( $channel_id );     

    if( !$result || $result->type !== 'live' )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';    
    } 
    else
    {                                  
        $html_output = '<p>' . __( 'Channel Live', 'twitchpress' ) . '</p>';  
    }
    
    set_transient( 'twitchpress_channel_status_line' . $channel_id, $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status_line', 'twitchpress_channel_status_line_shortcode' );


/**
* Shortcode outputs a status line for the giving channel. 
* 
* @version 2.0
*/
function twitchpress_channel_status_shortcode( $atts ) {   
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return twitchpress_channel_status_shortcode_kraken( $atts );    
    }
    else
    {
        return twitchpress_channel_status_shortcode_helix( $atts );    
    }
}
function twitchpress_channel_status_shortcode_kraken( $atts ) {
          
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
    
    $twitchpress = new TWITCHPRESS_Twitch_API_Calls();

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
function twitchpress_channel_status_shortcode_helix( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status' );
    
    // If no channel ID or name is giving...
    if( !$atts['channel_id'] && !$atts['channel_name'] ) {
        return __( 'Channel status shortcode has not been setup!', 'twitchpress' );      
    } 
    
    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $helix = new TWITCHPRESS_Twitch_API();
        $result = $helix->get_user_by_login_name_without_email_address( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            $html_output = sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 
    
    // Use cached HTML if we have a channel ID...
    if( $channel_id ) {
        $cache = get_transient( 'twitchpress_channel_status_line_' . $channel_id );
        if( $cache ) {
            return $cache;
        }
    }                  

    // Get the stream. 
    if( !$helix ){ $helix = new TWITCHPRESS_Twitch_API(); }
         
    $result = $helix->get_stream_by_userid( $channel_id );     

    if( !$result || $result->type !== 'live' )
    {          
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';   
    } 
    else
    {                               
        $html_output = '<p>';        
        $html_output .= ' ' . esc_html( $result->user_name ) . ' ';
        $html_output .= ' is live with ' . esc_html( $result->viewer_count ) . ' viewers ';
        $html_output .= '</p>';
    }
    
    set_transient( 'twitchpress_channel_status' . $channel_id, $html_output, 120 );
    
    return $html_output;
}
add_shortcode( 'twitchpress_channel_status', 'twitchpress_channel_status_shortcode' );


/**
* Shortcode outputs a status box with some extra information for the giving channel.
* 
* @version 2.0
*/
function twitchpress_channel_status_box_shortcode( $atts ) {
    if( TWITCHPRESS_API_NAME == 'kraken' )
    {
        return twitchpress_channel_status_box_shortcode_kraken( $atts );    
    }
    else
    {
        return twitchpress_channel_status_box_shortcode_helix( $atts );    
    }    
}
function twitchpress_channel_status_box_shortcode_kraken( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_channel_status_box' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    }   

    // Check cache. 
    if( $cache = get_transient( 'twitchpress_shortcode_channel_status_box' ) ) 
    {
        return $cache; 
    }

    $twitchpress = new TWITCHPRESS_Twitch_API_Calls();
    
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
    }
    
    set_transient( 'twitchpress_channel_status_box', $html_output, 120 );
    
    return $html_output;    
}
function twitchpress_channel_status_box_shortcode_helix( $atts ) {       
    $atts = shortcode_atts( array(             
            'channel_id'   => null,
            'channel_name' => null,
    ), $atts, 'twitchpress_shortcode_channel_status_box' );
    
    // Establish channel ID
    if( $atts['channel_id'] === null && $atts['channel_name'] === null ) 
    {
        return 'Shortcode has not been setup properly!';      
    }   
    $helix = new TWITCHPRESS_Twitch_API();

    // Establish an ID if we only have a channel name...  
    if( !$atts['channel_id'] && $atts['channel_name'] ) {              
        $helix = new TWITCHPRESS_Twitch_API();
        $result = $helix->get_user_without_email_by_login_name( $atts['channel_name'] );
        if( isset( $result->data[0]->id ) ) {
            $channel_id = $result->data[0]->id;
        } else {
            $channel_id = null;
            $html_output = sprintf( __( 'Failed to retrieve channel ID %s', 'twitchpress' ), esc_html( $atts['channel_name'] ) );
        }                   
    } 

    // Use cached HTML if we have a channel ID...
    if( $channel_id ) {
        $cache = get_transient( 'twitchpress_channel_status_box' . $channel_id );
        if( $cache ) {
            return $cache;
        }
    }                  
                                
    // Get the stream. 
    if( !$helix ){ $helix = new TWITCHPRESS_Twitch_API(); }
         
    $result = $helix->get_stream_by_userid( $channel_id );     
                       
    if( !$result || $result->type !== 'live' )
    {
        $html_output = '<p>' . __( 'Channel Offline', 'twitchpress' ) . '</p>';    
    } 
    else
    {                                                            
        $html_output = '<div>';
        $html_output .= 'Channel: ' . $result->user_name . ' ';
        $html_output .= '<br />Game: ' . $result->game_id . ' ';
        $html_output .= '<br />Viewers: ' . $result->viewer_count . ' ';
        $html_output .= '</div>';
    }
    
    set_transient( 'twitchpress_channel_status_box' . $channel_id, $html_output, 120 );
    
    return $html_output;    
}
add_shortcode( 'twitchpress_shortcode_channel_status_box', 'twitchpress_channel_status_box_shortcode' );
       
                                       
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