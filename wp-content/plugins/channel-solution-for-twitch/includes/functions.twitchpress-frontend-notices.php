<?php
/**
 * TwitchPress - Frontend Notices 
 *
 * @author   Ryan Bayne
 * @category User Interface
 * @package  TwitchPress/Notices
 * @since    1.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
} 

add_action( 'wp_head', 'twitchpress_display_frontend_notices', 10 );
function twitchpress_display_frontend_notices() {     
    add_filter( 'the_content', 'twitchpress_display_frontend_notices_the_content' );
}

function twitchpress_frontend_notice_types() {
    return array( 'error', 'success', 'warning', 'info' );
}

/**
* Build a message for frontend output using hardcoded and stored values from
* a recent request. This is ideal for none authenticated visitors but applies 
* to authenticated ones too.
* 
* See umrole-update-button.php shortcode in the UM extension for an example of where
* we begin. The user clicks a link, request is processed, a redirect happens and then
* we need to generate the output. 
*                        
* @param mixed $post_content
* 
* @version 1.0
*/
function twitchpress_display_frontend_notices_the_content( string $post_content ) {  
    global $GLOBALS;
                                                   
    if( !isset( $_GET['twitchpress_notice'] ) || !is_string( $_GET['twitchpress_notice'] ) ) { return $post_content; }
    elseif( !isset( $_GET['source'] ) || !is_string( $_GET['source'] ) ) { return $post_content; }
    elseif( !isset( $_GET['key'] ) || !is_numeric( $_GET['key'] ) ) { return $post_content; }

    // Get our frontend notice from class.twitchpress-public-notices.php
    $the_message = $GLOBALS['twitchpress']->public_notices->get_message_by_id( $_GET['source'], $_GET['key'] );
    
    // If title or info contain placeholders, get the short life transient holding the applicable values. 
    if( strstr( $the_message[ 'title'], '%s' ) || strstr( $the_message[ 'info'], '%s' ) ) 
    {
        // Get values stored in transient, required for inserting into messages.
        $transient = get_transient( 'twitchpress_shortcode_' . $_GET['source'] . $_GET['key'] );
        
        $the_message[ 'title'] = sprintf( $the_message[ 'title'], $transient['title_values'] );        
        $the_message[ 'info'] = sprintf( $the_message[ 'info'], $transient['info_values'] );        
    }
                           
    $content = "
    <div class='twitchpress-frontend-message'>
        <h2>" . esc_html( $the_message[ 'title'] ) . "</h2>
        <p>" . esc_html( $the_message[ 'info'] ) . "</p>
    </div>\n\n" . $post_content;
    
    // Remove the action calling this function once it's run, to prevent it running elsewhere.
    remove_filter( 'the_content', 'twitchpress_display_frontend_notices_the_content', 5 );
    remove_filter( 'post_updated', 'twitchpress_display_frontend_notices_the_content', 5 );
    
    return $content;
}                   

add_action( 'wp_head', 'twitchpress_display_frontend_notices_undertitle', 10 );
function twitchpress_display_frontend_notices_undertitle() {
    add_filter( 'the_title', 'twitchpress_display_frontend_notices_the_title' );
}

/**
* Do not use, see functions.twitchpress-frontend.php which is easily safer and the
* next approach in class.twitchpress-public-notices.php which will offer notice management. 
* 
* @param mixed $content
* 
* @deprecated
*/
function twitchpress_display_frontend_notices_the_title( string $content ) {
    return $content;
    if( !isset( $_GET['twitchpress_notice'] ) || !is_string( $_GET['twitchpress_notice'] ) ) { return; }
    elseif( !isset( $_GET['twitchpress_title'] ) || !is_string( $_GET['twitchpress_title'] ) ) { return; }
    elseif( !isset( $_GET['twitchpress_info'] ) || !is_string( $_GET['twitchpress_info'] ) ) { return; }

    // Remove the action calling this function once it's run, to prevent it running elsewhere.
    remove_action( 'post_updated', 'twitchpress_display_frontend_notices_the_title', 11 );

    $content = "
    <div class='twitchpress-frontend-message'>
        <h2>" . esc_html( $_GET['twitchpress_title'] ) . "</h2>
        <p>" . esc_html( $_GET['twitchpress_info'] ) . "</p>
    </div>\n\n" . $content;
    
    return $content;
}
