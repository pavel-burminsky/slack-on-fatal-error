<?php

class Slack_On_Fatal_Error_Admin {

	public function __construct() {

		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

	}


	public function admin_menu() {

		$id = add_options_page(
			'Slack On Fatal Error Settings',
			'Slack On Fatal Error',
			'manage_options',
			'slack-on-fatal-error',
			[ $this, 'settings_page' ]
		);

		add_action( 'load-' . $id, [ $this, 'enqueue_scripts' ] );

	}


	public function enqueue_scripts() {

		remove_all_actions( 'admin_notices' );

		wp_enqueue_style( 'slack-on-fatal-error', Slack_On_Fatal_Error_DIR_URL . 'assets/admin.css', [], SLACK_ON_FATAL_ERROR_VERSION );

	}


	public function settings_page() {

		if ( isset( $_POST['sofe_settings_nonce'] ) && wp_verify_nonce( $_POST['sofe_settings_nonce'], 'sofe_settings' ) ) {
			update_option( 'tmm_sofe_settings', map_deep( wp_unslash( $_POST['sofe_settings'] ), 'sanitize_text_field' ) );
			echo '<div id="message" class="updated fade"><p><strong>Settings saved.</strong></p></div>';
		}

		$settings = get_option( 'tmm_sofe_settings', [] );

		if ( empty( $settings ) ) {

			$settings = [
				'slack_webhook_url' => '',
				'levels'            => [],
			];

			foreach ( Slack_On_Fatal_Error()->error_levels as $level_id ) {

				if ( $level_id == 1 ) {
					$settings['levels'][ $level_id ] = true;
				} else {
					$settings['levels'][ $level_id ] = false;
				}
			}
		}

		?>

		<div class="wrap">


			<h2>Error Notification Settings</h2>

			<form id="tmm-slack-on-fatal-error-settings" action="" method="post">
				<?php wp_nonce_field( 'sofe_settings', 'sofe_settings_nonce' ); ?>
				<input type="hidden" name="action" value="update">

				<table class="form-table">
					<tr valign="top">
						<th scope="row">Slack Webhook URL</th>
						<td valign="top">
							<input class="regular-text" type="text" name="sofe_settings[slack_webhook_url]" value="<?php echo esc_attr( $settings['slack_webhook_url'] ); ?>" />
							<p class="description">Configured error notifications will be sent to this webhook url.</p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Error Levels To Notify</th>
						<td>
							<fieldset class="error-levels">

								<?php foreach ( Slack_On_Fatal_Error()->error_levels as $i => $level_id ) : ?>

									<?php $level_string = Slack_On_Fatal_Error()->map_error_code_to_type( $level_id ); ?>

									<?php
									if ( empty( $settings['levels'][ $level_id ] ) ) {
										$settings['levels'][ $level_id ] = false;}
										?>

									<label for="level_<?php echo $level_string; ?>">
										<input type="checkbox" name="sofe_settings[levels][<?php echo $level_id; ?>]" id="level_<?php echo $level_string; ?>" value="1" <?php checked( $settings['levels'][ $level_id ] ); ?>>
										<?php echo esc_html( $level_string ); ?>
									</label>

									<?php
									switch ( $level_string ) {
										case 'E_ERROR':
											echo '<span class="description"><strong>Recommended:</strong> A fatal run-time error that can\'t be recovered from.</span>';
											break;

										case 'E_WARNING':
											echo '<span class="description">Warnings indicate that something unexpected happened, but the site didn\'t crash.</span>';
											break;

										case 'E_PARSE':
											echo '<span class="description">A Parse error is uncommon but could be caused by an incomplete plugin update.</span>';
											break;

										case 'E_NOTICE':
											echo '<span class="description">Many plugins generate Notice-level errors, and these can usually be ignored.</span>';
											break;

										default:
											break;
									}
									?>

									<br />

								<?php endforeach; ?>
							</fieldset>

						</td>

				</table>

				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="Save Changes">
				</p>

			</form>

		</div>

		<?php
	}

}


