<?php
/**
 * Plugin Name:       Mailchimp Form Wordpress Block
 * Description:       Example static block scaffolded with Create Block tool.
 * Requires at least: 5.8
 * Requires PHP:      7.0
 * Version:           1.0.0
 * Author:            Navanath Bhosale
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mfwb
 *
 * @package           mailchimp-form-wordpress-block
 */

/**
 * Set constants
 */
define( 'MFWB_FILE', __FILE__ );
define( 'MFWB_BASE', plugin_basename( MFWB_FILE ) );
define( 'MFWB_DIR', plugin_dir_path( MFWB_FILE ) );
define( 'MFWB_URL', plugins_url( '/', MFWB_FILE ) );
define( 'MFWB_VER', '1.0.0' );

require_once 'mfwb-loader.php';
