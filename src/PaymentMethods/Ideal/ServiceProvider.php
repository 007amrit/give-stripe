<?php

namespace GiveStripe\PaymentMethods\Ideal;

use Give\Framework\PaymentGateways\PaymentGatewayRegister;
use Give\Helpers\Hooks;
use GiveStripe\PaymentMethods\Ideal\Notices\InvalidDonationCurrencyNotice;
use GiveStripe\PaymentMethods\Ideal\Notices\SupportedCurrencyNotice;

/**
 * @since 2.5.0
 */
class ServiceProvider implements \Give\ServiceProviders\ServiceProvider
{

    /**
     * @inerhitDoc
     */
    public function register()
    {
    }

    /**
     * @inerhitDoc
     */
    public function boot()
    {
        add_action(
            'givewp_register_payment_gateway',
            function (PaymentGatewayRegister $paymentGatewayRegister) {
                $paymentGatewayRegister->registerGateway(IdealGateway::class);
            }
        );
        Hooks::addAction('admin_notices', SupportedCurrencyNotice::class);
        Hooks::addAction('give_checkout_error_checks', InvalidDonationCurrencyNotice::class);
    }
}
