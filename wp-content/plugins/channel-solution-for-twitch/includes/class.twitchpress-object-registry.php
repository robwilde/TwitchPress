<?php
/**
 * TwitchPress - The object registry provides object access throughout WordPress
 * without using globals.  
 * 
 * @author   Ryan Bayne
 * @category Scripts
 * @package  TwitchPress/Core
 * @since    1.0.0
 */
 
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
           
if( !class_exists( 'TwitchPress_Object_Registry' ) ) :

class TwitchPress_Object_Registry {

    static $storage = array();

    static function add( $id, $class ) {
        self::$storage[ $id ] = $class; 
    }

    static function get( $id ) {
        return array_key_exists( $id, self::$storage ) ? self::$storage[$id] : NULL;    
    }
    
    static function update_var( $id, $var, $new, $old = null ) {
        self::$storage[$id]->$var = $new;     
    }
}

endif;

