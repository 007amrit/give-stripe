<?php

namespace GiveStripe\PaymentMethods\Plaid;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Hooks;
use GiveStripe\PaymentMethods\Actions\GetPlaidGatewayData;
use GiveStripe\PaymentMethods\Plaid\Controllers\AchLinkTokenController;
use GiveStripe\PaymentMethods\Plaid\Controllers\AttachClientIdToDonorHandler;
use GiveStripe\PaymentMethods\Plaid\Notices\EmptyApiKeyNotice;

/**
 * Class ServiceProvider
 * @package GiveStripe\PaymentMethos\Plaid
 * @since 2.3.0
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider
{

    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     * @since 2.3.0
     */
    public function boot()
    {
        Hooks::addAction('wp_ajax_give_stripe_get_ach_link_token', AchLinkTokenController::class, 'handle');
        Hooks::addAction('wp_ajax_nopriv_give_stripe_get_ach_link_token', AchLinkTokenController::class, 'handle');

        add_action(
            'givewp_register_payment_gateway',
            function (PaymentGatewayRegister $paymentGatewayRegister) {
                $paymentGatewayRegister->registerGateway(PlaidGateway::class);
            }
        );
        Hooks::addAction('wp_ajax_get_receipt', AttachClientIdToDonorHandler::class, 'handle', 9);
        Hooks::addAction('wp_ajax_nopriv_get_receipt', AttachClientIdToDonorHandler::class, 'handle', 9);
        Hooks::addAction('admin_notices', EmptyApiKeyNotice::class);

        // Load Stripe ACH scripts only when gateway is active.
        add_action('init', function () {
            if (give_is_gateway_active(PlaidGateway::id())) {
                Hooks::addAction('wp_enqueue_scripts', PlaidGateway::class, 'enqueuePublicAssets');
            }
        });

        Hooks::addFilter(
            sprintf(
                'givewp_create_payment_gateway_data_%1$s',
                PlaidGateway::id()
            ),
            GetPlaidGatewayData::class,
            '__invoke',
            10,
            2
        );
    }
}
