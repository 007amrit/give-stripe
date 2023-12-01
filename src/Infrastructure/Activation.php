<?php

namespace GiveStripe\Infrastructure;


use Give_Stripe_Apple_Pay_Registration;

/**
 * Class responsible for registering and handling add-on activation hooks.
 *
 * @package     GiveRecurring
 * @copyright   Copyright (c) 2020, GiveWP
 */
class Activation
{
    /**
     * Activate add-on action hook.
     *
     * @since 2.4.0
     * @return void
     */
    public static function activateAddon()
    {
        if (!class_exists('Give_Stripe_Apple_Pay_Registration')) {
            require_once GIVE_STRIPE_PLUGIN_DIR . '/includes/admin/class-give-stripe-apple-pay-registration.php';
        }

        $applePayRegistration = new Give_Stripe_Apple_Pay_Registration();
        $applePayRegistration->registerDomainVerificationUrl();

        flush_rewrite_rules();
    }

    /**
     * Deactivate add-on action hook.
     *
     * @since 2.4.0
     * @return void
     */
    public static function deactivateAddon()
    {
    }

    /**
     * Uninstall add-on action hook.
     *
     * @since 2.4.0
     * @return void
     */
    public static function uninstallAddon()
    {
    }
}
