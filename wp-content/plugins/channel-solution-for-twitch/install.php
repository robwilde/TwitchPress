<?php
/**
 * Installation functions, excluding plugin updating and some optional installation
 * features that might relate to none active API or extension integration.
 * 
 * @author   Ryan Bayne
 * @category Admin
 * @package  TwitchPress/Core
 * @version  1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function twitchpress_database_change_versions() {
    $arr = array();
    
    // 0.0.0
    $arr['0.0.0'] = array(            
        'twitchpress_update_000_file_paths',
        'twitchpress_update_000_db_version',
    );
    
    return $arr;   
}
        
function twitchpress_activation_installation() {
    global $wpdb;

    if ( ! defined( 'TWITCHPRESS_INSTALLING' ) ) {
        define( 'TWITCHPRESS_INSTALLING', true );
    }

    // Flush administration notices because previous ones will no longer apply.
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-notices.php' );
    TwitchPress_Admin_Notices::remove_all_notices();
    
    twitchpress_create_roles();
    twitchpress_create_files();
    twitchpress_create_options();    
                   
    // Queue upgrades/setup wizard
    $current_installed_version = get_option( 'twitchpress_version', null );
    $current_db_version        = get_option( 'twitchpress_db_version', null );

    // No versions? This is a new install :)
    if ( is_null( $current_installed_version ) && is_null( $current_db_version ) && apply_filters( 'twitchpress_enable_setup_wizard', true ) ) {  
        TwitchPress_Admin_Notices::add_notice( 'install' );
        delete_transient( '_twitchpress_activation_redirect' );
        set_transient( '_twitchpress_activation_redirect', 1, 30 );
    }                           

    if ( !is_null( $current_db_version ) && version_compare( $current_db_version, max( array_keys( twitchpress_database_change_versions() ) ), '<' ) ) {
        TwitchPress_Admin_Notices::add_notice( 'update' );
    } else {
        twitchpress_update_db_version();
    }

    // Run automatic updates. 
    twitchpress_update();
    
    twitchpress_update_package_version();

    // Trigger action
    do_action( 'twitchpress_installed' );
}

/**
* Manual plugin update action. NOT CURRENTLY IN USE
* 
* @version 1.0
*/
function twitchpress_install_action_do_update() {
    if ( ! empty( $_GET['do_update_twitchpress'] ) ) {
        twitchpress_update();
        TwitchPress_Admin_Notices::add_notice( 'updating' );
    }        
}

/**
* Automatic updater - runs when plugin is activated which happens during
* a standard WordPress plugin update.
* 
* @version 1.0
*/
function twitchpress_update() {
    
    // 2.3.0 renames options
    if( $old = get_option( 'twitchpress_main_channel_name' ) ) {
        add_option( 'twitchpress_main_channels_name', $old );
        unset( $old );    
    }

    if( $old = get_option( 'twitchpress_main_channel_name' ) ) {
        add_option( 'twitchpress_main_channels_name', $old );
        unset( $old );    
    }
    
    if( $old = get_option( 'twitchpress_main_channel_id' ) ) {
        add_option( 'twitchpress_main_channels_id', $old );
        unset( $old );    
    }
    
    if( $old = get_option( 'twitchpress_main_redirect_uri' ) ) {
        add_option( 'twitchpress_app_redirect', $old );
        unset( $old );    
    }
    
    if( $old = get_option( 'twitchpress_main_client_id' ) ) {
        add_option( 'twitchpress_app_id', $old );
        unset( $old );    
    }
    
    if( $old = get_option( 'twitchpress_main_client_secret' ) ) {
        add_option( 'twitchpress_app_secret', $old );
        unset( $old );    
    }
    
    if( $old = get_option( 'twitchpress_main_code' ) ) {
        add_option( 'twitchpress_main_channels_code', $old );
        unset( $old );    
    }
   
}
    
/**
* If key values are missing we will offer the wizard. 
* 
* Does not apply when the setup wizard has not been complete. This is
* currently done by checking 
* 
* @version 2.0
*/
function twitchpress_offer_wizard() {
    $offer_wizard = false;
    
    if( !current_user_can( 'manage_twitchpress') ) {
        return;    
    }
    
    // Avoid registering notice during the Setup Wizard.
    if( isset( $_GET['page']) && $_GET['page'] == 'twitchpress-setup' ) {
        return;    
    }
    
    // If already displaying the install notice, do not display.
    if( TwitchPress_Admin_Notices::has_notice( 'install' ) ) {
        return;
    }

    if( !twitchpress_get_main_channels_name() ) {
        
        $offer_wizard = 'twitchpress_main_channels_name';
        
    } elseif( !twitchpress_get_main_channels_twitchid() ) {
        
        $offer_wizard = 'twitchpress_main_channels_id';
        
    } elseif( !twitchpress_get_app_id() ) {
        
        $offer_wizard = 'twitchpress_app_id';
        
    } elseif( !twitchpress_get_app_secret() ) {
        
        $offer_wizard = 'twitchpress_app_secret';
        
    } elseif( !twitchpress_get_main_channels_code() ) {
        
        $offer_wizard = 'twitchpress_main_code';
        
    } elseif( !twitchpress_get_main_channels_token() ) {
        
        $offer_wizard = 'twitchpress_main_token';
        
    }     
    
    if( $offer_wizard === false ) { return; }
    
    $wizard_link = '<p><a href="' . admin_url( 'index.php?page=twitchpress-setup' ) . '" class="button button-primary">' . __( 'Setup wizard', 'twitchpress' ) . '</a></p>';
    
    TwitchPress_Admin_Notices::add_wordpress_notice(
        'missingvaluesofferwizard',
        'info',
        false,
        __( 'Twitch API Credentials Missing', 'twitchpress' ),
        sprintf( __( 'TwitchPress is not ready because the %s option is missing. If you have already been using the plugin and this notice suddenly appears then it suggests important options have been deleted or renamed. You can go through the Setup Wizard again to correct this problem. You should also report it. %s', 'twitchpress'), $offer_wizard, $wizard_link )    
    );           
}
    
/**
* Update plugin version.
* 
* @version 1.0
*/
function twitchpress_update_package_version() {
    delete_option( 'twitchpress_version' );
    add_option( 'twitchpress_version', TwitchPress()->version );
} 
        
/**
 * Update DB version to current.
 */
function twitchpress_update_db_version( $version = null ) {
    delete_option( 'twitchpress_db_version' );
    add_option( 'twitchpress_db_version', is_null( $version ) ? TwitchPress()->version : $version );
} 
    
/**
* Very strict capabilities for professional developers only.
* 
* @version 1.0
*/
function twitchpress_get_developer_capabilities() {
    $capabilities = array();

    $capabilities['core'] = array(
        'code_twitchpress',
        'twitchpressdevelopertoolbar'
    );

    return $capabilities;        
}

/**
 * Create roles and capabilities.
 * 
 * @version 1.0
 */
function twitchpress_create_roles() {
    global $wp_roles;

    if ( ! class_exists( 'WP_Roles' ) ) {
        return;
    }

    if ( ! isset( $wp_roles ) ) {
        $wp_roles = new WP_Roles();
    }

    // TwitchPress Developer role
    add_role( 'twitchpressdeveloper', __( 'TwitchPress Developer', 'twitchpress' ), array(
        'level_9'                => true,
        'level_8'                => true,
        'level_7'                => true,
        'level_6'                => true,
        'level_5'                => true,
        'level_4'                => true,
        'level_3'                => true,
        'level_2'                => true,
        'level_1'                => true,
        'level_0'                => true,
        'read'                   => true,
        'read_private_pages'     => true,
        'read_private_posts'     => true,
        'edit_users'             => true,
        'edit_posts'             => true,
        'edit_pages'             => true,
        'edit_published_posts'   => true,
        'edit_published_pages'   => true,
        'edit_private_pages'     => true,
        'edit_private_posts'     => true,
        'edit_others_posts'      => true,
        'edit_others_pages'      => true,
        'publish_posts'          => true,
        'publish_pages'          => true,
        'delete_posts'           => true,
        'delete_pages'           => true,
        'delete_private_pages'   => true,
        'delete_private_posts'   => true,
        'delete_published_pages' => true,
        'delete_published_posts' => true,
        'delete_others_posts'    => true,
        'delete_others_pages'    => true,
        'manage_categories'      => true,
        'manage_links'           => true,
        'moderate_comments'      => true,
        'unfiltered_html'        => true,
        'upload_files'           => true,
        'export'                 => true,
        'import'                 => true,
        'list_users'             => true
    ) );

    // Add custom capabilities to our new TwitchPress Developers role. 
    $new_admin_capabilities = twitchpress_get_developer_capabilities();
    foreach ( $new_admin_capabilities as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $wp_roles->add_cap( 'twitchpressdeveloper', $cap );
            // Ensure the first administration account has all capabilities.
            if( get_current_user_id() == 1 ) {
                $wp_roles->add_cap( 'administrator', $cap );    
            }                 
        }
    }        
    
    // Add the plugins custom capabilities to administrators. 
    $new_admin_capabilities = twitchpress_get_core_capabilities();
    foreach ( $new_admin_capabilities as $cap_group ) {
        foreach ( $cap_group as $cap ) {
            $wp_roles->add_cap( 'administrator', $cap );                 
        }
    }
}

/**
 * Create files/directories with .htaccess and index files added by default.
 * 
 * @version 1.0
 */
function twitchpress_create_files() {
    // Install files and folders for uploading files and prevent hotlinking
    $upload_dir      = wp_upload_dir();
    $download_method = get_option( 'twitchpress_file_download_method', 'force' );
                                         
    $files = array(
        array(
            'base'         => $upload_dir['basedir'] . '/twitchpress_uploads',
            'file'         => 'index.html',
            'content'     => ''
        ),
        array(
            'base'         => TWITCHPRESS_LOG_DIR,
            'file'         => '.htaccess',
            'content'     => 'deny from all'
        ),
        array(
            'base'         => TWITCHPRESS_LOG_DIR,
            'file'         => 'index.html',
            'content'     => ''
        )
    );

    if ( 'redirect' !== $download_method ) {
        $files[] = array(
            'base'         => $upload_dir['basedir'] . '/twitchpress_uploads',
            'file'         => '.htaccess',
            'content'     => 'deny from all'
        );
    }

    foreach ( $files as $file ) {
        if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
            if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
                fwrite( $file_handle, $file['content'] );
                fclose( $file_handle );
            }
        }
    }
}
    
/**
 * Adds default options from settings files.
 * 
 * @version 1.0
 */
function twitchpress_create_options() {
    // Include settings so that we can run through defaults
    include_once( TWITCHPRESS_PLUGIN_DIR_PATH . 'includes/admin/class.twitchpress-admin-settings.php' );
    $settings = TwitchPress_Admin_Settings::get_settings_pages();

    foreach ( $settings as $section ) {
        if ( ! method_exists( $section, 'get_settings' ) ) {
            continue;
        }
        $subsections = array_unique( array_merge( array( '' ), array_keys( $section->get_sections() ) ) );

        foreach ( $subsections as $subsection ) {
            foreach ( $section->get_settings( $subsection ) as $value ) {
                if ( isset( $value['default'] ) && isset( $value['id'] ) ) {
                    $autoload = isset( $value['autoload'] ) ? (bool) $value['autoload'] : true;
                    add_option( $value['id'], $value['default'], '', ( $autoload ? 'yes' : 'no' ) );
                }
            }
        }
    }
}