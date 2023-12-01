<?php

namespace GiveStripe\PaymentMethods\Plaid\Actions;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use GiveStripe\PaymentMethods\Plaid\ValueObjects\StripeAchData;

/**
 * @since 2.5.0
 */
class GetStripeAchDataFromRequest
{
    /**
     * @since 2.5.0
     * @return StripeAchData
     * @throws PaymentGatewayException|Exception
     */
    public function __invoke(Donation $donation)
    {
        // Sanity check: must have Plaid token.
        if (empty($_POST['give_stripe_ach_token'])) {
            throw new PaymentGatewayException(
                esc_html__(
                    'The Stripe ACH gateway failed to generate the Plaid token. Please try your donation again.',
                    'give-stripe'
                )
            );
        }

        if (empty($_POST['give_stripe_ach_account_id'])) {
            throw new PaymentGatewayException(
                esc_html__(
                    'The Stripe ACH gateway failed to generate the Plaid account ID. Please try your donation again.',
                    'give-stripe'
                )
            );
        }

        $stripeAchData = new StripeAchData();
        $stripeAchData->token = give_clean($_POST['give_stripe_ach_token']);
        $stripeAchData->bankAccountId = give_clean($_POST['give_stripe_ach_account_id']);

        give_update_meta($donation->id, '_give_stripe_source_id', $stripeAchData->bankAccountId);

        DonationNote::create([
            'donationId' => $donation->id,
            'content' => sprintf(
                esc_html__('Stripe Bank Account ID: %1$s, Bank Account Token: %2$s', 'give-stripe'),
                $stripeAchData->bankAccountId,
                $stripeAchData->token
            )
        ]);

        return $stripeAchData;
    }
}
