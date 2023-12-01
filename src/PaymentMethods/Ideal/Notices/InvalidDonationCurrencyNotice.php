<?php

namespace GiveStripe\PaymentMethods\Ideal\Notices;

use GiveStripe\PaymentMethods\Ideal\IdealGateway;

/**
 * @since 2.5.0
 */
class InvalidDonationCurrencyNotice
{
    /**
     * @since 2.5.0
     *
     * @param array $donationData
     *
     * @return void
     */
    public function __invoke($donationData)
    {
        $currency = give_get_currency(absint($_POST['give-form-id']));
        if ('EUR' === $currency || IdealGateway::id() !== $_POST['give-gateway']) {
            return;
        }

        // Not Supported Currency by iDEAL.
        give_set_error(
            'give_stripe_ideal_invalid_donation_currency',
            sprintf(
            /* translators: 1. Current Currency */
                esc_html__('%1$s is not supported currency with iDEAL. Please try with EUR currency.', 'give-stripe'),
                $currency
            )
        );
    }
}
