<?php
/**
 * Plugin Loader.
 *
 * @package mailchimp-form-wordpress-block
 * @since 1.0.0
 */

namespace MFWB;

use MFWB\Admin\Menu;

/**
 * MFWB_Loader
 *
 * @since 1.0.0
 */
class MFWB_Loader {

	/**
	 * Instance
	 *
	 * @access private
	 * @var object Class Instance.
	 * @since 1.0.0
	 */
	private static $instance;

	/**
	 * Initiator
	 *
	 * @since 1.0.0
	 * @return object initialized object of class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * Autoload classes.
	 *
	 * @param string $class class name.
	 */
	public function autoload( $class ) {
		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		$class_to_load = $class;

		$filename = strtolower(
			preg_replace(
				[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
				[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
				$class_to_load
			)
		);

		$file = MFWB_DIR . $filename . '.php';

		// if the file redable, include it.
		if ( is_readable( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		spl_autoload_register( [ $this, 'autoload' ] );

		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		add_action( 'init', [ $this, 'mailchimp_form_wordpress_block_block_creator' ] );

		// $this->setup_classes();
	}

	/**
	 * Include required classes.
	 */
	public function setup_classes() {

		/* Init API */
		ApiInit::get_instance();

		if ( is_admin() ) {
			/* Setup Menu */
			AdminMenu::get_instance();

			/* Ajax init */
			AjaxInit::get_instance();
		}
	}

	/**
	 * Registers the block using the metadata loaded from the `block.json` file.
	 * Behind the scenes, it registers also all assets so they can be enqueued
	 * through the block editor in the corresponding context.
	 *
	 * @see https://developer.wordpress.org/reference/functions/register_block_type/
	 */
	public function mailchimp_form_wordpress_block_block_creator() {
		register_block_type( MFWB_DIR . '/build' );
	}

	/**
	 * Load Plugin Text Domain.
	 * This will load the translation textdomain depending on the file priorities.
	 *      1. Global Languages /wp-content/languages/mailchimp-wp-form-block/ folder
	 *      2. Local dorectory /wp-content/plugins/mailchimp-wp-form-block/languages/ folder
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function load_textdomain() {
		// Default languages directory.
		$lang_dir = MFWB_DIR . 'languages/';

		/**
		 * Filters the languages directory path to use for plugin.
		 *
		 * @param string $lang_dir The languages directory path.
		 */
		$lang_dir = apply_filters( 'MFWB_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter.
		global $wp_version;

		$get_locale = get_locale();

		if ( $wp_version >= 4.7 ) {
			$get_locale = get_user_locale();
		}

		/**
		 * Language Locale for plugin
		 *
		 * @var $get_locale The locale to use.
		 * Uses get_user_locale()` in WordPress 4.7 or greater,
		 * otherwise uses `get_locale()`.
		 */
		$locale = apply_filters( 'plugin_locale', $get_locale, 'mailchimp-wp-form-block' );
		$mofile = sprintf( '%1$s-%2$s.mo', 'mailchimp-wp-form-block', $locale );

		// Setup paths to current locale file.
		$mofile_global = WP_LANG_DIR . '/plugins/' . $mofile;
		$mofile_local  = $lang_dir . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/mailchimp-wp-form-block/ folder.
			load_textdomain( 'mailchimp-wp-form-block', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/mailchimp-wp-form-block/languages/ folder.
			load_textdomain( 'mailchimp-wp-form-block', $mofile_local );
		} else {
			// Load the default language files.
			load_plugin_textdomain( 'mailchimp-wp-form-block', false, $lang_dir );
		}
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
MFWB_Loader::get_instance();
