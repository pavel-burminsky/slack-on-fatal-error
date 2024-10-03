<?php

/**
 * Plugin Name: Send Slack Message On Fatal Error
 * Description: Receive Slack notifications when errors occur on your WordPress site.
 * Plugin URI: https://github.com/pavel-burminsky/slack-on-fatal-error
 * Version: 1.1
 * Author: Pavel Burminsky
 * Author URI: https://github.com/pavel-burminsky
 * Text Domain: slack-on-fatal-error
 */

if ( ! function_exists( 'add_action' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

define( 'SLACK_ON_FATAL_ERROR_VERSION', '1.0' );

if ( ! class_exists( 'Slack_On_Fatal_Error' ) ) {

	final class Slack_On_Fatal_Error {

		public $admin;
		private static $instance;
		public $error_levels = [
			E_ERROR,
			E_WARNING,
			E_PARSE,
			E_NOTICE,
			E_USER_ERROR,
			E_USER_WARNING,
			E_STRICT,
			E_DEPRECATED,
		];


		public static function instance() {

			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Slack_On_Fatal_Error ) ) {

				self::$instance = new Slack_On_Fatal_Error();
				self::$instance->setup_constants();
				self::$instance->includes();

				self::$instance->admin = new Slack_On_Fatal_Error_Admin();

			}

			return self::$instance;
		}


		private function setup_constants() {

			if ( ! defined( 'Slack_On_Fatal_Error_DIR_PATH' ) ) {
				define( 'Slack_On_Fatal_Error_DIR_PATH', plugin_dir_path( __FILE__ ) );
			}

			if ( ! defined( 'Slack_On_Fatal_Error_PLUGIN_PATH' ) ) {
				define( 'Slack_On_Fatal_Error_PLUGIN_PATH', plugin_basename( __FILE__ ) );
			}

			if ( ! defined( 'Slack_On_Fatal_Error_DIR_URL' ) ) {
				define( 'Slack_On_Fatal_Error_DIR_URL', plugin_dir_url( __FILE__ ) );
			}

		}


		private function includes() {

			require_once Slack_On_Fatal_Error_DIR_PATH . 'includes/admin/class-admin.php';
			require_once Slack_On_Fatal_Error_DIR_PATH . 'includes/class-public.php';

		}


		public function map_error_code_to_type( $code ) {

			switch ( $code ) {
				case E_ERROR:
					return 'E_ERROR';
				case E_WARNING:
					return 'E_WARNING';
				case E_PARSE:
					return 'E_PARSE';
				case E_NOTICE:
					return 'E_NOTICE';
				case E_CORE_ERROR:
					return 'E_CORE_ERROR';
				case E_CORE_WARNING:
					return 'E_CORE_WARNING';
				case E_COMPILE_ERROR:
					return 'E_COMPILE_ERROR';
				case E_COMPILE_WARNING:
					return 'E_COMPILE_WARNING';
				case E_USER_ERROR:
					return 'E_USER_ERROR';
				case E_USER_WARNING:
					return 'E_USER_WARNING';
				case E_USER_NOTICE:
					return 'E_USER_NOTICE';
				case E_STRICT:
					return 'E_STRICT';
				case E_RECOVERABLE_ERROR:
					return 'E_RECOVERABLE_ERROR';
				case E_DEPRECATED:
					return 'E_DEPRECATED';
				case E_USER_DEPRECATED:
					return 'E_USER_DEPRECATED';
			}

		}


	}

}


if ( ! function_exists( 'Slack_On_Fatal_Error' ) ) {

	function Slack_On_Fatal_Error() {
		return Slack_On_Fatal_Error::instance();
	}

	Slack_On_Fatal_Error();

}


