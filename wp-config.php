<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** Database username */
define( 'DB_USER', 'wordpress' );

/** Database password */
define( 'DB_PASSWORD', 'wordpress' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'Yd3$Hj7*Kl9@Mn2!Pq5#Rs8%Tv6&Wx4(Yz1)' );
define( 'SECURE_AUTH_KEY',  'Ab5$Cd7*Ef9@Gh2!Ij5#Kl8%Mn6&Op4(Qr1)' );
define( 'LOGGED_IN_KEY',    'St3$Uv7*Wx9@Yz2!Ab5#Cd8%Ef6&Gh4(Ij1)' );
define( 'NONCE_KEY',        'Kl3$Mn7*Op9@Qr2!St5#Uv8%Wx6&Yz4(Ab1)' );
define( 'AUTH_SALT',        'Cd3$Ef7*Gh9@Ij2!Kl5#Mn8%Op6&Qr4(St1)' );
define( 'SECURE_AUTH_SALT', 'Uv3$Wx7*Yz9@Ab2!Cd5#Ef8%Gh6&Ij4(Kl1)' );
define( 'LOGGED_IN_SALT',   'Mn3$Op7*Qr9@St2!Uv5#Wx8%Yz6&Ab4(Cd1)' );
define( 'NONCE_SALT',       'Ef3$Gh7*Ij9@Kl2!Mn5#Op8%Qr6&St4(Uv1)' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
