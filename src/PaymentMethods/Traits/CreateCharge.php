<?php

namespace GiveStripe\PaymentMethods\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Stripe\ApplicationFee;
use Stripe\Charge;

/**
 * @since 2.5.0
 */
trait CreateCharge
{
    /**
     * @since 2.5.0
     *
     * @throws Exception
     */
    public function createCharge(array $stripeChargeRequestArgs, array $options = []): Charge
    {
        give_stripe_set_app_info();

        try {
            // Charge application fee, only if the Stripe premium add-on is not active.
            if (ApplicationFee::canAddfee()) {
                $stripeChargeRequestArgs['application_fee_amount'] = give_stripe_get_application_fee_amount(
                    $stripeChargeRequestArgs['amount']
                );
            }

            return Charge::create(
                $stripeChargeRequestArgs,
                wp_parse_args(
                    $options,
                    give_stripe_get_connected_account_options()
                )
            );
        } catch (\Exception $e) {
            throw new Exception(
                sprintf(
                /* translators: 1: Exception Error Message */
                    esc_html__('Unable to create a successful charge. Details: %1$s', 'give-stripe'),
                    $e->getMessage()
                )
            );
        }
    }
}
