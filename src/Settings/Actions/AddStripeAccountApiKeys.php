<?php

namespace GiveStripe\Settings\Actions;

use Give\PaymentGateways\Exceptions\InvalidPropertyName;
use Give\PaymentGateways\Stripe\Exceptions\DuplicateStripeAccountName;
use Give\PaymentGateways\Stripe\Exceptions\StripeAccountAlreadyConnected;
use Give\PaymentGateways\Stripe\Models\AccountDetail as AccountDetailModel;
use Give\PaymentGateways\Stripe\Repositories\Settings;

/**
 * Class AddStripeAccountApiKeys
 * @package GiveStripe\Settings\Actions
 *
 * @since 2.4.0
 */
class AddStripeAccountApiKeys
{
    /**
     * @var Settings
     */
    private $settingsRepository;

    /**
     * AddStripeAccountApiKeys constructor.
     *
     * @since 2.4.0
     *
     * @param Settings $settingsRepository
     */
    public function __construct(Settings $settingsRepository)
    {
        $this->settingsRepository = $settingsRepository;
    }

    /**
     * @since 2.4.0
     *
     * @param AccountDetailModel $stripeAccount
     * @param int $formId
     *
     * @throws DuplicateStripeAccountName
     * @throws InvalidPropertyName
     * @throws StripeAccountAlreadyConnected
     */
    public function __invoke(AccountDetailModel $stripeAccount, $formId)
    {
        if (!$this->settingsRepository->hasDefaultGlobalStripeAccountSlug()) {
            $this->settingsRepository->setDefaultGlobalStripeAccountSlug($stripeAccount->accountSlug);
        }

        $this->settingsRepository->addNewStripeAccount($stripeAccount);

        if ($formId) {
            $this->setAccountToDefault($stripeAccount, $formId);
            give()->form_meta->update_meta($formId, 'give_stripe_per_form_accounts', 'enabled');
        }
    }

    /**
     * @since 2.4.0
     *
     * @param AccountDetailModel $stripeAccount
     * @param int $formId
     */
    private function setAccountToDefault(AccountDetailModel $stripeAccount, $formId)
    {
        // Set default account to fetch the correct API details only if previously not any global stripe account slug added.
        if (!$this->settingsRepository->getDefaultStripeAccountSlugForDonationForm($formId)) {
            $this->settingsRepository->setDefaultStripeAccountSlugForDonationForm($formId, $stripeAccount->accountSlug);
        }
    }
}
