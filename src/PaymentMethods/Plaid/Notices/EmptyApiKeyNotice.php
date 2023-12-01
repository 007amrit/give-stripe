<?php

namespace GiveStripe\PaymentMethods\Plaid\Notices;

use GiveStripe\PaymentMethods\Plaid\Repositories\Plaid;

/**
 * @since 2.5.0
 */
class EmptyApiKeyNotice
{

    /**
     * @var Plaid
     */
    private $plaidRepository;

    /**
     * @since 2.5.0
     *
     * @param Plaid $plaidRepository
     */
    public function __construct(Plaid $plaidRepository)
    {
        $this->plaidRepository = $plaidRepository;
    }

    public function __invoke()
    {
        if ($this->canShowNotice()) {
            Give()->notices->register_notice(
                [
                    'id' => 'give-plaid-empty-api-key-notice',
                    'type' => 'error',
                    'dismissible' => false,
                    'description' => sprintf(
                    /* translators: 1: Stripe Plaid Settings Page URL */
                        __(
                            'The Plaid API Keys should not be empty in <a href="%1$s">Stripe + Plaid Settings</a> in order to use the Stripe + Plaid payment gateway.',
                            'give-stripe'
                        ),
                        admin_url(
                            'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=stripe-settings&group=plaid'
                        )
                    ),
                    'show' => true,
                ]
            );
        } // End if().
    }

    /**
     * @since 2.5.0
     * @return bool
     */
    private function canShowNotice()
    {
        return current_user_can('manage_give_settings') &&
            give_is_gateway_active('stripe_ach') &&
            (!$this->plaidRepository->getClientId() || !$this->plaidRepository->getClientSecretKey());
    }
}
