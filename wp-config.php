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
define('DB_NAME', 'root_wordpress-hammydowns');

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
define('AUTH_KEY',         'p*T>ebhxq+G3My(DOu->q$_hF;eofJ@W&WS@`pi-4f{EV]KugYz|Q|5 :%80v7Z<');
define('SECURE_AUTH_KEY',  '_z1W*eD=Xhy7(:<<>``LtW/#JBzobto7,ga>[<{2E.4|R_;y_E@-Pu#91ssE,g+J');
define('LOGGED_IN_KEY',    ' -WX6B@)]{).ur3*<-6EJ6kp(3Z@SaZExLCgPu*HDzT^KgE@cE<Tf/)]8gd5-3k9');
define('NONCE_KEY',        'W/038FyT*@,0[-~AAnvF5,dfg:LJqA{TY}jF.p8E?_*K`yOwYX9 :.9=ChSTwGms');
define('AUTH_SALT',        'jo`X~P02Df:1Prl1ro}~FH&)0?5bafSjN|zam>6(RFR/41Dv(Kga%t[eA4Sl+-^v');
define('SECURE_AUTH_SALT', 't39EW>g54BD!F)pN&RI&E?r <{?gN(S})eF`?lG?r?{c,OY>-um|6SMK3GV%6 Y-');
define('LOGGED_IN_SALT',   'jR}>ruhpF;35ZSwoG|LijCvcyU}:1=o$){6W~jh+qW%!b7=rI+Y|;;|l3k#DI!#_');
define('NONCE_SALT',       'G{=}^R6PXgn%,dN=7=-]#},.+N%h87+:x,V_76h-LcwCCu!Xs/!ZPh7]Hc fso5Z');

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
