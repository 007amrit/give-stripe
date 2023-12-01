<?php

namespace GiveStripe\PaymentMethods\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Stripe\BankAccount;
use Stripe\Card;
use Stripe\Source;
use Stripe\Token as StripeToken;

/**
 * @since 2.5.0
 */
trait RetrieveToken
{
    /**
     * This function will be used to fetch token details for given stripe token id.
     *
     * @since 2.5.0
     *
     * @throws Exception
     */
    protected function getTokenDetails(string $tokenId, array $options = []): StripeToken
    {
        give_stripe_set_app_info();

        try {
            return StripeToken::retrieve(
                $tokenId,
                wp_parse_args(
                    $options,
                    give_stripe_get_connected_account_options()
                )
            );
        } catch (\Exception $e) {
            throw new Exception(
                sprintf(
                /* translators: 1: Exception Message Body */
                    esc_html__('Unable to retrieve token. Details: %1$s', 'give-stripe'),
                    $e->getMessage()
                )
            );
        }
    }
}

