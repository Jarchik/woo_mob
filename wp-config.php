<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('WP_CACHE', true); //Added by WP-Cache Manager
define( 'WPCACHEHOME', '/home/woocommercemanag/public_html/wp-content/plugins/wp-super-cache/' ); //Added by WP-Cache Manager
define('DB_NAME', 'woocomme_wp');

/** MySQL database username */
define('DB_USER', 'woocomme_wpuser');

/** MySQL database password */
define('DB_PASSWORD', 'r.[Q-ORUs(a8');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'AJzAoa$d+9{V#-^1|0e)?VU+ZzTo_DS#!y[An+*7qjk[^lEZ!x-OKZ./ZR?S*]l8');
define('SECURE_AUTH_KEY',  'pn!aJ6+L sj$XMha!dp~*5d()`}CSKkbydwj9jj=|q$vu#qzq|FOgaZX]GT6K/_&');
define('LOGGED_IN_KEY',    '8 a![4ijuCNuAKeBmJ ?A-L381qH(*;=S+Is=ROdxm7_*W|]PrWxVJO|a/@<w_A!');
define('NONCE_KEY',        'y+pG3mQhpLFe}ijlp]V}}>D(yjD-Cy=ad(s|[Z:lM,y0J`Sl+<--f(z8Ij1mFt5^');
define('AUTH_SALT',        'N{=0y9G`=-C-o-1b.!(pB2b1MBP|-%*dC,E%k4g@}ZOK??H>-ux_RZ/J9>CPooRC');
define('SECURE_AUTH_SALT', 'W[.-zAaF-iWr:93(dchcG!>g$mq:745?9>j+a#$L9S,+}t64p7:ymc=w?,wgpb/h');
define('LOGGED_IN_SALT',   'o-u,kRwCfM#HU9=i?cRlL`jcrh|;6;[.|]kPa~&j4VS^[?`ewA|XO!(&Mu:dh}kS');
define('NONCE_SALT',       '6X*yGnLF~j<e]oFfb#K)}d5eu|b)ugDrH;Hs!Z@*K*!FV!VH4eb?Om_dNQ@x=^?^');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', FALSE);
define('WP_DEBUG_LOG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
