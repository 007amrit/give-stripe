<?php

namespace GiveStripe\PaymentMethods;

use Give\Helpers\Hooks;
use Give\ServiceProviders\ServiceProvider as GiveServiceProvider;
use GiveStripe\PaymentMethods\ApplePay\Controllers\RegisterApplePayDomainController;
use GiveStripe\PaymentMethods\ApplePay\Controllers\ResetRegisteredApplePayDomainController;

/**
 * Class ActionServiceProviders
 * @package GiveStripe\ServiceProviders
 *
 * @since 2.4.0
 */
class ServiceProvider implements GiveServiceProvider
{

    /**
     * @inheritDoc
     */
    public function register()
    {
    }

    /**
     * @inheritDoc
     */
    public function boot()
    {
        Hooks::addAction('wp_ajax_give_stripe_register_domain', RegisterApplePayDomainController::class);
        Hooks::addAction('wp_ajax_give_stripe_reset_domain', ResetRegisteredApplePayDomainController::class);
    }
}
