<?php

namespace GiveStripe\PaymentMethods\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Stripe\ApplicationFee;
use Stripe\Source;

/**
 * @since 2.5.0
 */
trait CreateSource
{
    /**
     * @since 2.5.0
     *
     * @throws Exception
     */
    public function createSource(array $stripeSourceRequestArgs, array $options = []): Source
    {
        give_stripe_set_app_info();

        try {
            // Charge application fee, only if the Stripe premium add-on is not active.
            if (ApplicationFee::canAddfee()) {
                $stripeSourceRequestArgs['application_fee_amount'] = give_stripe_get_application_fee_amount(
                    $stripeSourceRequestArgs['amount']
                );
            }

            return Source::create(
                $stripeSourceRequestArgs,
                wp_parse_args(
                    $options,
                    give_stripe_get_connected_account_options()
                )
            );
        } catch (\Exception $e) {
            throw new Exception(
                sprintf(
                /* translators: 1: Exception Error Message */
                    esc_html__('Unable to create a successful source. Details: %1$s', 'give-stripe'),
                    $e->getMessage()
                )
            );
        }
    }
}

