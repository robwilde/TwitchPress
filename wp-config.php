<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'twitchpressbeta');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'O/&%zH&AD#N*@uH+YGYgk;3?m7+ETZ5qx;wUEB:.7V1LXffD8}]]oIO8as,t7(IA');
define('SECURE_AUTH_KEY',  'e4Pp(w/#Q-X&7vQ+G?_LO-#q7N-c~1)P>2:[zsxMm&LRFIe8pNcX)<4WVJ$wJ2E1');
define('LOGGED_IN_KEY',    'e6B<.M m<F^kg+JF1;-o[+*WO)r1<&mcAVJsI.LoB,VLhY{(N,G/&g|elY52klf;');
define('NONCE_KEY',        'QsW%{VLGJdwQd,bDGD;D>}T2iD>%lf@1eYRv$DCkw;*SN;}VYe_Y;/kP#eBO<>C:');
define('AUTH_SALT',        '+NXj?o&u_^5/.zE`)B$q ^<SJs==<_4-iL%Q^=i}g.&hJ&a&wwd9?f]gK<gB^!5(');
define('SECURE_AUTH_SALT', '+wrmw;+qjudw[7-dWAim-K%t&3JgNKa|*CZ$+3vl.iqrCj`^7dseJMQ%il`B+Azt');
define('LOGGED_IN_SALT',   '=J2S53A1&$Vp~Hkj8(UwdF-e n$i$ tpj5PdF_?<iM0f?H=-QI}|XqcGp+n[bB#S');
define('NONCE_SALT',       '>9xp=@iv#CE1[[{..K.e+!>(GzppeZi73i,_Nxj=N|q=2-mX[Pfp2t,FTftC}a1/');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
