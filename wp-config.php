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
define('DB_NAME', 'wishmagn_wishmagnet');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         '{Drh3uIT+Va_F:?>3x|0,9d5cRf/*+w]A$h_+K5):nX2t*Q=tr4d1`-z?ugp0boZ');
define('SECURE_AUTH_KEY',  ']`BaXg7kf<NM0N4o?AX3M[3faTRDi+0E,[|YFP&I]=E}W-Dr2-N:}^gS?IX[iH,e');
define('LOGGED_IN_KEY',    'aRGJy{$f^UG{E5}>8&qLXHxY|PLW3}c|.>hP~FRcYgEt:SZc1Fa)r :Dj%EOx0A@');
define('NONCE_KEY',        '->Rj& YcT1t4@/!);-B;x3/9@t<)!qr{MH:g<m[M|q_r&rc[BXa;fn-kfW4~+K37');
define('AUTH_SALT',        'FTw-reG@nuotF(Gl/OBX,}1RqSZ>fiY74:_Csd`9e}Q^6hoRxI3g+0eH/a7[b=X8');
define('SECURE_AUTH_SALT', 'GbFj=|4Is^+5q2W)>hf8?+awLl7m+W`B^+2H[ObIO(l6W@+o TDLctz53BJv+-},');
define('LOGGED_IN_SALT',   'KDBk%px*vDK)N*}S+Lpb;M]~>j`rxqq+|htTP!^CyyBDAmz 3BcR#D^-#KHv3;P3');
define('NONCE_SALT',       'z*H;X|ZNF|W%}c%1%2$Dtwemt+oitG,|OgBh#UsY/U}hBaruMCZ6$sn>bHcZlk/L');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');