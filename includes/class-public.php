<?php

class Slack_On_Fatal_Error_Public {

	public function __construct() {

		add_action( 'shutdown', [ $this, 'shutdown' ], 1 );

	}


	public function shutdown() {

		$error = error_get_last();

		if ( is_null( $error ) ) {
			return;
		}

		if ( $this->is_dev_install() ) {
			return;
		}

		$ignore = apply_filters( 'sofe_ignore_error', false, $error );

		if ( $ignore ) {
			return;
		}

		if ( E_WARNING === $error['type'] && ( false !== strpos( $error['message'], 'unlink' ) || false !== strpos( $error['message'], 'rmdir' ) || false !== strpos( $error['message'], 'mkdir' ) ) ) {
			return;
		}

		$settings = get_option( 'tmm_sofe_settings', [] );

		if ( empty( $settings ) || empty( $settings['slack_webhook_url'] ) || empty( $settings['levels'] ) ) {
			return;
		}

		if ( empty( $settings['levels'][ $error['type'] ] ) ) {
			return;
		}

		$output  = "Error Level: " . Slack_On_Fatal_Error()->map_error_code_to_type( $error['type'] ) . "\n";
		$output .= "Message: " . $error['message'] . "\n";
		$output .= "File: " . $error['file'] . "\n";
		$output .= "Line: " . $error['line'] . "\n";
		$output .= "Request: " . $_SERVER['REQUEST_URI'] . "\n";

		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$referrer = urlencode( $_SERVER['HTTP_REFERER'] );
		} else {
			$referrer = 'unknown';
		}

		$output .= "Referrer: " . $referrer . "\n";

		$user_id = get_current_user_id();

		if ( ! empty( $user_id ) ) {
			$output .= "User ID: " . $user_id . "\n";
		}

		$hash      = md5( $error['message'] );
		$transient = get_transient( 'sofe_' . $hash );

		if ( strpos( $error['message'], 'Slack_On_Fatal_Error_test_error_function' ) !== false ) {
			$bypass = true;
		} else {
			$bypass = false;
		}

		if ( ! empty( $transient ) && false === $bypass ) {

			return;

		} else {

			set_transient( 'sofe_' . $hash, true, HOUR_IN_SECONDS );

		}

		$output = "An error occured on " . get_the_permalink() . ".\n\n" . $output;

		$this->send_slack_message( $settings['slack_webhook_url'], $output );

	}


	public function send_slack_message( $webhook_url, $message ) {
		$payload = json_encode( [
			'text' => $message,
			'username' => 'WordPress Bot',
			'icon_emoji' => ':exclamation:',
		] );

		$response = wp_remote_post( $webhook_url, [
			'body'    => $payload,
			'headers' => [
				'Content-Type' => 'application/json',
			],
		] );

		if ( is_wp_error( $response ) ) {
			error_log('Slack notification failed: ' . $response->get_error_message());
		}
	}


	public function is_dev_install() {
		$site_url = get_site_url();
		$primary_domain = $this->get_primary_domain( $site_url );

		return in_array( $primary_domain, [
			'wpengine.com',
			'wpenginepowered.com'
		] );
	}


	public function get_primary_domain( $url ) {
		$host = parse_url( $url, PHP_URL_HOST );
		$host_parts = explode( '.', $host );
		$count_parts = count( $host_parts );

		if ( $count_parts >= 2 ) {
			return $host_parts[$count_parts - 2] . '.' . $host_parts[$count_parts - 1];
		}

		return $host;
	}


}

new Slack_On_Fatal_Error_Public();


