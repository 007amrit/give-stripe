<?php

namespace GiveStripe\Settings;

use Give\Helpers\Hooks;
use GiveStripe\Settings\Controllers\AddStripeAccountApiKeysController;
use GiveStripe\Settings\Controllers\UpdateStripeManualAccountDetailsController;

/**
 * Class ServiceProvider
 * @package GiveStripe\Settings
 *
 * @since 2.4.0
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
     */
    public function boot()
    {
        Hooks::addAction('wp_ajax_give_stripe_add_manual_account', AddStripeAccountApiKeysController::class);
        Hooks::addAction(
            'wp_ajax_give_stripe_update_manual_account',
            UpdateStripeManualAccountDetailsController::class
        );
        Hooks::addFilter(
            'give_stripe_manage_account_actions_html',
            StripeApiKeysAccountEditActions::class,
            '__invoke',
            10,
            2
        );
        Hooks::addAction('admin_enqueue_scripts', AdminAssets::class, '__invoke', 100);
    }
}
