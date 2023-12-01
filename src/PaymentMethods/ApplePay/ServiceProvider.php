<?php

namespace GiveStripe\PaymentMethods\ApplePay;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Hooks;
use GiveStripe\PaymentMethods\Actions\GetStripeGatewayData;
use GiveStripe\PaymentMethods\ApplePay\Controllers\RegisterApplePayDomainController;
use GiveStripe\PaymentMethods\ApplePay\Controllers\ResetRegisteredApplePayDomainController;

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
        Hooks::addAction('wp_ajax_give_stripe_register_domain', RegisterApplePayDomainController::class);
        Hooks::addAction('wp_ajax_give_stripe_reset_domain', ResetRegisteredApplePayDomainController::class);

        add_action(
            'givewp_register_payment_gateway',
            function (PaymentGatewayRegister $paymentGatewayRegister) {
                $paymentGatewayRegister->registerGateway(ApplePayGateway::class);
            }
        );

        Hooks::addAction(
            'give_donation_form_after_cc_form',
            ApplePayGateway::class,
            'renderDonateButton',
            8898,
            2
        );

        // Edit ApplePay gateway config.
        add_filter('give_payment_gateways', function (array $gateways) {
            global $is_safari;

            $gateways['stripe_apple_pay']['admin_tooltip'] = esc_html__(
                'If enabled, donors will be able to make donations using Apple Pay on desktops or iPhones using the Safari browser.',
                'give-stripe'
            );

            $gateways['stripe_apple_pay']['is_visible'] = $is_safari;

            return $gateways;
        });

        Hooks::addFilter(
            sprintf(
                'givewp_create_payment_gateway_data_%1$s',
                ApplePayGateway::id()
            ),
            GetStripeGatewayData::class,
            '__invoke',
            10,
            2
        );
    }
}
