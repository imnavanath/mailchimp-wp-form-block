<?php

namespace MFWB\Core;

use MFWB\Core\Services;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Front-end AJAX handler for the builder interface. We use this
 * instead of wp_ajax because that only works in the admin and
 * certain things like some shortcodes won't render there. AJAX
 * requests handled through this method only run for logged in users
 * for extra security. Developers creating custom modules that need
 * AJAX should use wp_ajax instead.
 *
 * @since 1.0.0
 */
class Ajax {

	/**
	 * An array of registered action data.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var array $actions
	 */
	static private $actions = [];

	/**
	 * Initializes hooks.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function init() {
		add_action( 'wp_ajax_sjea_add_subscriber', 'Services::add_subscriber' );
		add_action( 'wp_ajax_nopriv_sjea_add_subscriber', 'Services::add_subscriber' );
		add_action( 'wp_ajax_sjea_submit_support_form', 'Services::submit_support' );

		add_action( 'admin_init', __CLASS__ . '::run' );
	}

	/**
	 * Runs builder's frontend AJAX.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	static public function run() {
		self::add_actions();
		self::call_action();
	}

	/**
	 * Adds a callable AJAX action.
	 *
	 * @since 1.0.0
	 * @param string $action The action name.
	 * @param string $method The method to call.
	 * @param array $args An array of method arg names that are present in the post data.
	 * @return void
	 */
	static public function add_action( $action, $method, $args = [] ) {
		self::$actions[ $action ] = [
			'action' => $action,
			'method' => $method,
			'args'	 => $args
		];
	}

	/**
	 * Removes an AJAX action.
	 *
	 * @since 1.0.0
	 * @param string $action The action to remove.
	 * @return void
	 */
	static public function remove_action( $action ) {
		if ( isset( self::$actions[ $action ] ) ) {
			unset( self::$actions[ $action ] );
		}
	}

	/**
	 * Adds all callable AJAX actions.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	static private function add_actions() {
		self::add_action( 'render_service_settings', 'Services::render_settings' );
		self::add_action( 'render_service_fields', 'Services::render_fields' );
		self::add_action( 'connect_service', 'Services::connect_service' );
		self::add_action( 'delete_service_account', 'Services::delete_account' );
		self::add_action( 'save_mailer_campaign', 'Services::save_campaign' );
		self::add_action( 'delete_mailer_campaign', 'Services::delete_campaign' );
	}

	/**
	 * Runs the current AJAX action.
	 *
	 * @since 1.0.0
	 * @access private
	 * @return void
	 */
	static private function call_action() {

		if ( ! is_user_logged_in() ) {
			// Only run for logged in users.
			return;
		}

		if ( ! empty( $_REQUEST['action'] ) ) {
			// Get the action.
			$action = $_REQUEST['action'];
		} else if( ! empty( $post_data['action'] ) ) {
			$action = $post_data['action'];
		} else {
			return;
		}

		// Extendable before action.
		do_action( 'mfwb_before_ajax_action', $action );

		// Make sure the action exists.
		if ( ! isset( self::$actions[ $action ] ) ) {
			return;
		}

		// Get the action data.
		$action 	= self::$actions[ $action ];
		$args   	= array();
		$keys_args  = array();

		// Build the args array.
		foreach ( $action['args'] as $arg ) {
			$args[] = $keys_args[ $arg ] = isset( $post_data[ $arg ] ) ? $post_data[ $arg ] : null;
		}

		// WordPress is doing AJAX request.
		if ( ! defined( 'DOING_AJAX' ) ) {
			define( 'DOING_AJAX', true );
		}

		// Allow developers to hook before the action runs.
		do_action( 'mfwb_ajax_before_' . $action['action'], $keys_args );

		// Call the action and allow developers to filter the result.
		$result = apply_filters( 'mfwb_ajax_' . $action['action'], call_user_func_array( $action['method'], $args ), $keys_args );

		// Allow developers to hook after the action runs.
		do_action( 'mfwb_ajax_after_' . $action['action'], $keys_args );

		// Extendable after action.
		do_action( 'mfwb_after_ajax_action', $action );

		// JSON encode the result.
		echo json_encode( $result );

		// Complete the request.
		die();
	}
}

Ajax::init();
