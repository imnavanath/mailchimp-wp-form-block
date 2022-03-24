<?php

namespace MFWB\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Admin settings helper.
 *
 * @since x.x.x
 */
class Helper {

	/**
	 * Returns an option from the database for
	 * the admin settings page.
	 *
	 * @param string $key The option key.
	 * @param bool $network_override Whether to allow the network admin setting to be overridden on subsites.
	 * @return mixed
	 */
	static public function get_admin_settings_option( $key, $network = false ) {
		if ( is_network_admin() ) {
			// Get the site-wide option if we're in the network admin.
			$value = get_site_option( $key );
		} elseif ( $network && is_multisite() ) {
			$value = get_site_option( $key );
		} else {
			// This must be a single site install. Get the single site option.
			$value = get_option( $key );
		}

		return $value;
	}

	/**
	 * Updates an option from the admin settings page.
	 *
	 * @param string $key The option key.
	 * @param mixed $value The value to update.
	 * @param bool $network_override Whether to allow the network admin setting to be overridden on subsites.
	 * @return mixed
	 */
	static public function update_admin_settings_option( $key, $value, $network = false ) {
		if ( is_network_admin() || $network ) {
			// Update the site-wide option since we're in the network admin.
			update_site_option( $key, $value );
		} elseif ( $network && is_multisite() ) {
			update_site_option( $key, $value );
		} else {
			// Update the option for single install or subsite.
			update_option( $key, $value );
		}
	}

	/**
	 * Returns an option from the database for
	 * the admin settings page.
	 *
	 * @param string $key The option key.
	 * @param bool $network_override Whether to allow the network admin setting to be overridden on subsites.
	 * @return mixed
	 */
	static public function delete_admin_settings_option( $key, $network = false ) {
		if ( is_network_admin() ) {
			// Get the site-wide option if we're in the network admin.
			$value = delete_site_option( $key );
		} elseif ( $network && is_multisite() ) {
			$value = delete_site_option( $key );
		} else {
			// This must be a single site install. delete the single site option.
			$value = delete_option( $key );
		}

		return $value;
	}

	/**
	 * Renders html with respective input fields.
	 *
	 * @since 1.0.0
	 * @param string $input The connection slug.
	 * @param array  $settings The input type settings array.
	 * @return string The html string.
	 */
	static public function render_settings_field( $id = '', $settings = [] ) {

		if ( $id != '' && ! empty( $settings ) ) {

			$input = '<div class="sjea-field-wrap sjea-field-' . $id . '-wrap '.$settings['row_class'].'">';

			switch ( $settings['type'] ) {
				case 'text':
					$input .= '<div class="sjea-field-label">';
                    	$input .= '<label for="service">'.$settings['label'].'</label>';
					$input .= '</div>';
                    $input .= '<div class="sjea-field-input">';
						$input .= '<input type="text" name="' . $id . '" class="' . $settings['class'] . '" />';
                    $input .= '</div>';
					break;

				case 'select':
					$multi_select = isset( $settings['multi-select'] ) ? 'multiple' : '';
					$default_value = isset( $settings['default'] ) ? $settings['default'] : '';
					$input .= '<div class="sjea-field-label">';
                    	$input .= '<label for="service">'.$settings['label'].'</label>';
					$input .= '</div>';
                    $input .= '<div class="sjea-field-input">';
						$input .= '<select name="'.$id.'" class="'.$settings['class'].'" '.$multi_select.'>';
						foreach ( $settings['options'] as $key => $value ) {
							$selected = ( $default_value == $key ) ? 'selected' : '';
							$input .= '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
						}
                        $input .= '</select>';
                    $input .= '</div>';
					break;

				case 'button':
					$input .= '<div class="sjea-field-label"></div>';
                    $input .= '<div class="sjea-field-input">';
						$input .= '<span class="sjea-button button '.$settings['class'].'" href="javascript:void(0);" onclick="return false;">'.$settings['label'].'</span>';
                    $input .= '</div>';
					break;

				case 'checkbox':
					$input .= '<p><input type="checkbox" value="true" name="' . $id . '" class="' . $settings['class'] . ' cp-customizer-input" />';
					$input .= '<label><strong>' . $settings['label'] . '</strong></label></p>';
					break;

				default:
					$input .= '';
					break;
			}
			$input .= '</div>';

		}
		echo $input;
	}

	/**
	 * Returns an array of account data for all integrated services.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	static public function get_services() {
		return get_option( 'mfwb_mailer_services', [] );
	}

	/**
	 * Updates the account data for an integrated service.
	 *
	 * @since 1.0.0
	 * @param string $service The service id.
	 * @param string $account The account name.
	 * @param array $data The account data.
	 * @return void
	 */
	static public function update_services( $service, $account, $data ) {
		$services = self::get_services();
		$account  = sanitize_text_field( $account );

		if ( ! isset( $services[ $service ] ) ) {
			$services[ $service ] = [];
		}

		$services[ $service ][ $account ] = $data;

		update_option( 'mfwb_mailer_services', $services );
	}

	/**
	 * Deletes an account for an integrated service.
	 *
	 * @since 1.0.0
	 * @param string $service The service id.
	 * @param string $account The account name.
	 * @return void
	 */
	static public function delete_service_account( $service, $account ) {
		$services = self::get_services();

		if ( isset( $services[ $service ][ $account ] ) ) {
			unset( $services[ $service ][ $account ] );
		}
		if ( 0 === count( $services[ $service ] ) ) {
			unset( $services[ $service ] );
		}

		update_option( 'mfwb_mailer_services', $services );
	}

	/**
	 * Returns an array of campaign data.
	 *
	 * @since 1.0.0
	 * @return array
	 */
	static public function get_campaigns( $name = false ) {
		$campaign = get_option( 'mfwb_mailer_campaigns', [] );

		if ( $name !== false ) {
			if( isset( $campaign[$name] ) ) {
				return $campaign[$name];
			}
			else {
				$response = [
					'error' => 'Mailer campaign is deleted from list. Please check mailer campaign configuration.',
				];
				return $response;
			}
		}

		return $campaign;
	}

	/**
	 * Updates the campaign data.
	 *
	 * @since 1.0.0
	 * @param string $service The service id.
	 * @param string $account The account name.
	 * @param array $data The account data.
	 * @return void
	 */
	static public function update_campaign( $name, $data ) {

		$campaigns = self::get_campaigns();
		$name  = sanitize_text_field( $name );

		$response = [
			'error' => false,
		];

		if ( isset( $campaigns[ $name ] ) ) {
			$response   = [
				'error' => __( 'Campaign name is already exists.', 'mfwb' )
			];

			return $response;
		}

		$campaigns[ $name ] = $data;

		update_option( 'mfwb_mailer_campaigns', $campaigns );

		return $response;
	}

	/**
	 * Delete campaign.
	 *
	 * @since 1.0.0
	 * @param string $service The service id.
	 * @param string $account The account name.
	 * @return void
	 */
	static public function delete_campaign( $campaign ) {
		$campaigns = self::get_campaigns();

		if ( ! isset( $campaigns[ $campaign ] ) ) {
			return;
		}

		unset( $campaigns[ $campaign ] );

		update_option( 'mfwb_mailer_campaigns', $campaigns );
	}
}
