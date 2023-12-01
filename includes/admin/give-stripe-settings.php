<?php
/**
 * Give - Stripe Premium | Admin Settings
 *
 * @since      2.2.0
 *
 * @package    Give
 * @subpackage Stripe Premium
 * @copyright  Copyright (c) 2019, GiveWP
 * @license    https://opensource.org/licenses/gpl-license GNU Public License
 */

// Exit, if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Give_Stripe_Premium_Settings' ) ) {
	/**
	 * Class Give_Stripe_Premium_Settings
	 *
	 * @since 1.0.0
	 */
	class Give_Stripe_Premium_Settings {

		/**
		 * Get only single instance.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @var Give_Stripe_Premium_Settings $instance
		 */
		static private $instance;

		/**
		 * Section ID.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @var string $section_id
		 */
		private $section_id;

		/**
		 * Section Label.
		 *
		 * @access private
		 * @since  1.0.0
		 *
		 * @var string $section_label
		 */
		private $section_label;

		/**
		 * Get single instance of class object.
		 *
		 * @return Give_Stripe_Premium_Settings
		 * @since  1.0.0
		 * @access public
		 *
		 */
		public static function get_instance() {
			if ( null === static::$instance ) {
				static::$instance = new static();
			}

			return static::$instance;
		}

		/**
		 * Setup hooks.
		 *
		 * @return void
		 * @since  1.0.0
		 * @access public
		 *
		 */
		public function setup_hooks() {

			$this->section_id    = 'stripe';
			$this->section_label = __( 'Stripe', 'give-stripe' );

			if ( is_admin() ) {
				add_action( 'give_admin_field_stripe_configure_apple_pay', array( $this, 'stripe_configure_apple_pay_field' ), 10, 2 );
				add_filter( 'give_stripe_register_groups', array( $this, 'register_groups' ) );
				add_action( 'give_stripe_add_additional_group_fields', array( $this, 'add_additional_group_fields' ) );
				add_action( 'give_stripe_premium_manual_api_fields', [ $this, 'add_manual_api_key_fields' ], 10, 2 );

			}
		}

		/**
		 * Register Groups.
		 *
		 * @param array $groups List of groups which will create vertical tabs navigation.
		 *
		 * @return array
		 * @since  2.6.0
		 * @access public
		 *
		 */
		public function register_groups( $groups ) {

			$groups['plaid']           = __( 'Plaid (ACH)', 'give-stripe' );
			$groups['payment_request'] = __( 'Google/Apple Pay', 'give-stripe' );

			return $groups;
		}

		/**
		 * Add Manual API Keys Fields.
		 *
		 * @param array $accounts All Stripe Accounts.
		 *
		 * @return void
		 * @since  2.2.6
		 * @access public
		 *
		 */
		public function add_manual_api_key_fields( $accounts ) {
			?>
			<div class="give-stripe-pro-api-key-button-wrap">
				<span class="give-stripe-pro-or-separator">
					<?php esc_html_e( 'OR', 'give' ); ?>
				</span>
				<button
						class="js-add-new-stripe-account button-secondary"
						data-value="manual"
						type="button"
				><?php esc_html_e( 'Enter API Keys', 'give-stripe' ); ?></button>
			</div>
			<?php
		}

		/**
		 * Register additional group fields.
		 *
		 * @param array $settings List of admin setting fields.
		 *
		 * @return array
		 * @since  2.2.0
		 * @access public
		 *
		 */
		public function add_additional_group_fields( $settings ) {

			// Payment Request.
			$settings['payment_request'][] = array(
					'id'   => 'give_title_stripe_payment_request',
					'type' => 'title',
			);

			$settings['payment_request'][] = array(
					'name'          => __( 'Configure Apple Pay', 'give-stripe' ),
					'desc'          => 'This option will help you configure Apple Pay with Stripe with just a single click.',
					'wrapper_class' => 'give-stripe-configure-apple-pay give-stripe-account-manager-wrap',
					'id'            => 'stripe_configure_apple_pay',
					'type'          => 'stripe_configure_apple_pay',
			);

			$settings['payment_request'][] = array(
					'name'          => __( 'Button Appearance', 'give-stripe' ),
					'desc'          => __( 'Adjust the appearance of the button style for Google and Apple Pay.', 'give-stripe' ),
					'id'            => 'stripe_payment_request_button_style',
					'wrapper_class' => 'stripe-payment-request-button-style-wrap',
					'type'          => 'radio_inline',
					'default'       => 'dark',
					'options'       => array(
							'light'         => __( 'Light', 'give-stripe' ),
							'light-outline' => __( 'Light Outline', 'give-stripe' ),
							'dark'          => __( 'Dark', 'give-stripe' ),
					),
			);

			$settings['payment_request'][] = array(
					'name'  => __( 'Stripe Gateway Documentation', 'give-stripe' ),
					'id'    => 'display_settings_payment_request_docs_link',
					'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
					'title' => __( 'Stripe Gateway Documentation', 'give-stripe' ),
					'type'  => 'give_docs_link',
			);

			$settings['payment_request'][] = array(
					'id'   => 'give_title_stripe_payment_request',
					'type' => 'sectionend',
			);

			// Plaid ( ACH ).
			$settings['plaid'][] = array(
					'id'   => 'give_title_stripe_plaid',
					'type' => 'title',
			);

			$settings['plaid'][] = array(
					'name'    => __( 'API Mode', 'give-stripe' ),
					'desc'    => sprintf(
					/* translators: %s Plaid API Host Documentation URL */
							__( 'Plaid has several API modes for testing and live transactions. "Test" mode allows you to test with a single sample bank account. "Development" mode allows you to accept up to 100 live donations without paying. "Live" mode allows for unlimited donations. Read the <a target="_blank" title="Plaid API Docs" href="%1$s">Plaid API docs</a> for more information.',
									'give-stripe' ),
							esc_url( 'https://plaid.com/docs/api/#api-host' )
					),
					'id'      => 'plaid_api_mode',
					'type'    => 'radio_inline',
					'default' => 'sandbox',
					'options' => array(
							'sandbox'     => __( 'Test', 'give-stripe' ),
							'development' => __( 'Development', 'give-stripe' ),
							'production'  => __( 'Live', 'give-stripe' ),
					),
			);

			$settings['plaid'][] = array(
					'name' => __( 'Plaid Client ID', 'give-stripe' ),
					'desc' => __( 'Enter your Plaid Client ID, found in your Plaid account dashboard.', 'give-stripe' ),
					'id'   => 'plaid_client_id',
					'type' => 'text',
			);

			$settings['plaid'][] = array(
					'name' => __( 'Plaid Secret Key', 'give-stripe' ),
					'desc' => __( 'Enter your Plaid secret key, found in your Plaid account dashboard.', 'give-stripe' ),
					'id'   => 'plaid_secret_key',
					'type' => 'api_key',
			);

			$settings['plaid'][] = array(
					'name'  => __( 'Stripe Gateway Documentation', 'give-stripe' ),
					'id'    => 'display_settings_plaid_docs_link',
					'url'   => esc_url( 'http://docs.givewp.com/addon-stripe' ),
					'title' => __( 'Stripe Gateway Documentation', 'give-stripe' ),
					'type'  => 'give_docs_link',
			);


			$settings['plaid'][] = array(
					'id'   => 'give_title_stripe_plaid',
					'type' => 'sectionend',
			);

			return $settings;
		}

		/**
		 * This function return hidden for fields which should get hidden on toggle of modal checkout checkbox.
		 *
		 * @param string $status Status - Enabled or Disabled.
		 *
		 * @return string
		 * @since  1.6
		 * @access public
		 *
		 */
		public function stripe_modal_checkout_status( $status = 'enabled' ) {
			$stripe_checkout = give_is_setting_enabled( give_get_option( 'stripe_checkout_enabled', 'disabled' ) );

			if (
					( $stripe_checkout && 'disabled' === $status ) ||
					( ! $stripe_checkout && 'enabled' === $status )
			) {
				return 'give-hidden';
			}

			return '';
		}

		/**
		 * Configure Apple Pay Field using Stripe.
		 *
		 * @param array  $value        List of values.
		 * @param string $option_value Option value.
		 *
		 * @since 2.0.8
		 */
		public function stripe_configure_apple_pay_field( $value, $option_value ) {
			$accounts = give_stripe_get_all_accounts();
			?>
			<tr <?php echo ! empty( $value['wrapper_class'] ) ? 'class="' . esc_attr( $value['wrapper_class'] ) . '"' : ''; ?>>
				<th scope="row" class="titledesc">
					<label for="configure_apple_pay">
						<?php esc_attr_e( 'Configure Apple Pay', 'give-stripe' ); ?>
					</label>
				</th>
				<td class="give-forminp give-forminp-api_key">
					<?php if ( is_multisite() && ! is_main_site() && ! is_subdomain_install() ) : ?>
						<div class="give-notice notice notice-error inline">
							<p>
								<?php esc_html_e( 'Error: Apple Pay can not be registered for a subdirectory site within a WordPress multisite environment due to Apple restrictions.',
										'give-stripe' ); ?>
							</p>
						</div>
					<?php endif; ?>
					<div class="give-stripe-account-manager-container">
						<?php if ( $accounts ) : ?>
							<div class="give-stripe-account-manager-list">
								<?php foreach ( $accounts as $name => $details ) :
									$account_name = $details['account_name'];
									$is_registered = isset( $details['register_apple_pay'] ) && $details['register_apple_pay'];
									$stripe_account_id = $details['account_id'];
									?>
									<div class="give-stripe-account-manager-list-item give-stripe-boxshadow-option-wrap">
										<span class="give-stripe-label"><?php esc_html_e( 'Account name', 'give-stripe' ); ?>:</span>
										<span class="give-stripe-account-name give-stripe-connect-data-field">
											<?php echo $account_name; ?>
										</span>
										<?php if ( $stripe_account_id ) : ?>
											<span class="give-stripe-label"><?php esc_html_e( 'Account ID', 'give-tripe' ); ?>:</span>
											<span class="give-stripe-account-id give-stripe-connect-data-field">
												<?php echo $stripe_account_id; ?>
											</span>
										<?php elseif ( 'manual' === $details['type'] ): ?>
											<span class="give-stripe-label"><?php esc_html_e( 'Connection Method', 'give-tripe' ); ?>:</span>
											<span class="give-stripe-account-id give-stripe-connect-data-field">
												<?php esc_html_e( 'API Keys', 'give-stripe' ); ?>
											</span>
										<?php endif; ?>

										<span class="give-stripe-account-register <?php echo $is_registered ? 'give-hidden' : ''; ?>">
											<button
												class="give-stripe-register-domain button button-primary"
												data-account="<?php echo $name; ?>"
											>
												<?php esc_html_e( 'Register domain', 'give-stripe' ); ?>
											</button>
										</span>
										<span class="give-stripe-account-actions <?php echo ! $is_registered ? 'give-hidden' : ''; ?>">
											<span class="give-stripe-account-badge">
												<span class="dashicons dashicons-yes"></span>
												<?php esc_html_e( 'Registered', 'give-stripe' ); ?>
											</span>
											<span class="give-stripe-account-reset">
												<button class="give-stripe-reset-domain button button-small"
												   data-account="<?php echo $name; ?>">
													<?php esc_html_e( 'Reset', 'give-stripe' ); ?>
												</button>
											</span>
										</span>
									</div>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>
					<p class="give-field-description">
						<?php
						echo sprintf(
						/* translators: 1. Stripe Apple settings page. */
								__( 'This option will help you <a href="%1$s" target="_blank">register your domain</a> to support Apple Pay for each of your Stripe accounts.', 'give-stripe' ),
								esc_url_raw( 'https://dashboard.stripe.com/settings/payments/apple_pay' )
						); ?>
					</p>
				</td>
			</tr>
			<?php
		}
	}
}

Give_Stripe_Premium_Settings::get_instance()->setup_hooks();
