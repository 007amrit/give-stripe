<?php

namespace GiveStripe\Settings;

use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Models\AccountDetail;

/**
 * Class StripeApiKeysAccountEditActions
 * @package GiveStripe\Settings
 *
 * @since 2.4.0
 */
class StripeApiKeysAccountEditActions
{
    /**
     * @unlreased
     *
     * @param string $html
     * @param array $stripeAccount
     *
     * @throws InvalidPropertyName
     */
    public function __invoke($html, $stripeAccount)
    {
        if ('manual' !== $stripeAccount['type']) {
            return $html;
        }

        $accountDetailModel = AccountDetail::fromArray($stripeAccount);

        $html .= sprintf(
            '<a class="js-%1$s-%2$s-account button button-small" href="#">%3$s</a>',
            'edit',
            $accountDetailModel->type,
            esc_html__('Edit', 'give-stripe')
        );

        return $html;
    }
}
