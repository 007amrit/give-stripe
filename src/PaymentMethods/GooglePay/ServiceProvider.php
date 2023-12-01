<?php

namespace GiveStripe\PaymentMethods\GooglePay;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Hooks;
use GiveStripe\PaymentMethods\Actions\GetStripeGatewayData;

/**
 * @since 2.5.0
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider
{
    /**
     * @since 2.5.0
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @since 2.5.0
     * @inheritDoc
     */
    public function boot()
    {
        add_action(
            'givewp_register_payment_gateway',
            function (PaymentGatewayRegister $paymentGatewayRegister) {
                $paymentGatewayRegister->registerGateway(GooglePayGateway::class);
            }
        );

        // Edit GooglePay gateway config.
        add_filter('give_payment_gateways', function (array $gateways) {
            global $is_chrome;

            $gateways['stripe_google_pay']['admin_tooltip'] = esc_html__(
                'If enabled, donors will be able to make donations using Google Pay on desktops or Android devices using the Chrome browser.',
                'give-stripe'
            );

            $gateways['stripe_google_pay']['is_visible'] = $is_chrome;

            return $gateways;
        });

        Hooks::addAction(
            'give_donation_form_after_cc_form',
            GooglePayGateway::class,
            'renderDonateButton',
            8898,
            2
        );

        Hooks::addFilter(
            sprintf(
                'givewp_create_payment_gateway_data_%1$s',
                GooglePayGateway::id()
            ),
            GetStripeGatewayData::class,
            '__invoke',
            10,
            2
        );
    }
}
