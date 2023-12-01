<?php

namespace GiveStripe\Settings;

use Give_Scripts;
use GiveStripe\Infrastructure\View;

/**
 * Class AdminAssets
 * @package GiveStripe\Settings
 *
 * @since 2.4.0
 */
class AdminAssets
{
    /**
     * @since 2.4.0
     */
    public function __invoke()
    {
        $this->registerScripts();
        $this->registerStyles();
    }

    /**
     * @since 2.4.0
     */
    public function registerScripts()
    {
        Give_Scripts::register_script(
            'give-stripe-admin-js',
            GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-admin.js',
            'jquery',
            GIVE_STRIPE_VERSION
        );
        wp_enqueue_script('give-stripe-admin-js');

        wp_set_script_translations('give-stripe-admin-js', 'give');

        wp_localize_script(
            'give-stripe-admin-js',
            'giveStripe',
            [
                'registerStripeAccountApiKeyFormHtml' => View::load('stripe-api-key-registration-form'),
                'i18n' => [
                    'add' => esc_html__('Add', 'give-stripe'),
                    'adding' => esc_html__('Adding', 'give-stripe'),
                    'added' => esc_html__('Added', 'give-stripe')
                ]
            ]
        );
    }

    /**
     * @since 2.4.0
     */
    public function registerStyles()
    {
        wp_register_style(
            'give-stripe-admin-css',
            GIVE_STRIPE_PLUGIN_URL . 'assets/dist/css/give-stripe-admin.css',
            false,
            GIVE_STRIPE_VERSION
        );
        wp_enqueue_style('give-stripe-admin-css');
    }
}
