<?php

namespace GiveStripe\PaymentMethods\Ideal\Notices;

use GiveStripe\PaymentMethods\Ideal\IdealGateway;

/**
 * @since 2.5.0
 */
class SupportedCurrencyNotice
{
    /**
     * @since 2.5.0
     * @return void
     */
    public function __invoke()
    {
        if ($this->canShowNotice()) {
            Give()->notices->register_notice(
                [
                    'id' => 'give-stripe-not-supported-currency-notice',
                    'type' => 'error',
                    'dismissible' => false,
                    'description' => sprintf(
                    /* translators: 1: Currency Settings Admin URL */
                        __(
                            'The currency must be set as "Euro" within Give\'s <a href="%1$s">Currency Settings</a> in order to use the Stripe iDEAL payment gateway.',
                            'give-stripe'
                        ),
                        admin_url(
                            'edit.php?post_type=give_forms&page=give-settings&tab=general&section=currency-settings'
                        )
                    ),
                    'show' => true,
                ]
            );
        }
    }

    /**
     * @since 2.5.0
     * @return bool
     */
    private function canShowNotice()
    {
        return current_user_can('manage_give_settings') &&
            'EUR' !== give_get_currency() &&
            give_is_gateway_active(IdealGateway::id());
    }
}
