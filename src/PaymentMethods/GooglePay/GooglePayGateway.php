<?php

namespace GiveStripe\PaymentMethods\GooglePay;

use Give\Framework\PaymentGateways\SubscriptionModule;
use Give\PaymentGateways\Gateways\Stripe\CreditCardGateway;
use Give\PaymentGateways\Gateways\Stripe\Traits\CreditCardForm;
use Give\PaymentGateways\Gateways\Stripe\Traits\HandlePaymentIntentStatus;

/**
 * @since 2.5.0
 */
class GooglePayGateway extends CreditCardGateway
{
    use HandlePaymentIntentStatus;
    use CreditCardForm;

    /**
     * @since 2.5.0
     *
     * @param SubscriptionModule|null $subscriptionModule
     */
    public function __construct(SubscriptionModule $subscriptionModule = null)
    {
        parent::__construct($subscriptionModule);

        // Setup Error Messages.
        $this->errorMessages['accountConfiguredNoSsl'] = esc_html__(
            'Google Pay button is disabled because your site is not running securely over HTTPS.',
            'give-stripe'
        );
        $this->errorMessages['accountNotConfiguredNoSsl'] = esc_html__(
            'Google Pay button is disabled because Stripe is not connected and your site is not running securely over HTTPS.',
            'give-stripe'
        );
        $this->errorMessages['accountNotConfigured'] = esc_html__(
            'Google Pay button is disabled. Please connect and configure your Stripe account to accept donations.',
            'give-stripe'
        );
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        // This payment method does not need credit card field.
        // Donor will see google pay button instead of "Donate Now" button to process donation.
        return '';
    }

    /**
     * This function is used to render Google Pay donate button.
     *
     * @since 2.5.0
     *
     * @param  int  $formId  Donation Form ID.
     * @param  array  $args  List of essential arguments.
     *
     * @return void
     */
    public function renderDonateButton(int $formId, array $args)
    {
        if ($this->getId() !== give_get_chosen_gateway($formId)) {
            return;
        }

        echo give_stripe_payment_request_donate_button($formId, $args, $this->canShowFields());
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'stripe_google_pay';
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return esc_html__('Stripe - Google Pay', 'give-stripe');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return esc_html__('Google Pay', 'give-stripe');
    }
}
