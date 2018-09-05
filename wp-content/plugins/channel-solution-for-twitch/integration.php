<?php
/**
 * Registration of objects that integrate with WordPress.
 *
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress
 * @version  1.0
 */

function twitchpress_integration() {
    add_filter( 'plugin_action_links_' . TWITCHPRESS_PLUGIN_BASENAME, 'twitchpress_plugin_action_links' );
    add_filter( 'plugin_row_meta', 'twitchpress_plugin_row_meta', 10, 2 );
    
    // Register post types
    TwitchPress_Post_types::register_post_types();
    TwitchPress_Post_types::register_taxonomies();    
}

                       
/**
 * Show action links on the plugin screen.
 *
 * @param    mixed $links Plugin Action links
 * @return    array
 * 
 * @version 1.2
 */
function twitchpress_plugin_action_links( $links ) {
    $action_links = array(
        'settings' => '<a href="' . admin_url( 'admin.php?page=twitchpress' ) . '" title="' . esc_attr( __( 'View TwitchPress Settings', 'twitchpress' ) ) . '">' . __( 'Settings', 'twitchpress' ) . '</a>',
    );

    return array_merge( $action_links, $links );
}

/**
 * Show row meta on the plugin screen.
 *
 * @param    mixed $links Plugin Row Meta
 * @param    mixed $file  Plugin Base file
 * @return    array
 */
function twitchpress_plugin_row_meta( $links, $file ) {     
    if ( $file == TWITCHPRESS_PLUGIN_BASENAME ) {
        $row_meta = array(
            'docs'    => '<a href="' . esc_url( apply_filters( 'twitchpress_docs_url', TWITCHPRESS_DOCS ) ) . '" title="' . esc_attr( __( 'View TwitchPress Documentation', 'twitchpress' ) ) . '">' . __( 'Docs', 'twitchpress' ) . '</a>',
            'support' => '<a href="' . esc_url( apply_filters( 'twitchpress_support_url', 'https://github.com/RyanBayne/TwitchPress/issues' ) ) . '" title="' . esc_attr( __( 'Visit Support Forum', 'twitchpress' ) ) . '">' . __( 'Support', 'twitchpress' ) . '</a>',
            'donate' => '<a href="' . esc_url( apply_filters( 'twitchpress_donate_url', TWITCHPRESS_DONATE ) ) . '" title="' . esc_attr( __( 'Donate to Project', 'twitchpress' ) ) . '">' . __( 'Donate', 'twitchpress' ) . '</a>',
            'blog' => '<a href="' . esc_url( apply_filters( 'twitchpress_blog_url', TWITCHPRESS_DONATE ) ) . '" title="' . esc_attr( __( 'Get project updates from the blog.', 'twitchpress' ) ) . '">' . __( 'Blog', 'twitchpress' ) . '</a>',
        );

        return array_merge( $links, $row_meta );
    }

    return (array) $links;
}