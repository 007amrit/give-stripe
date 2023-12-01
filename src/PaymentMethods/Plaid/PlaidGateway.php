<?php

namespace GiveStripe\PaymentMethods\Plaid;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\PaymentCommand;
use Give\Framework\PaymentGateways\Commands\PaymentComplete;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\Helpers\Form\Utils;
use Give\PaymentGateways\Gateways\Stripe\Actions\CreatePaymentIntent;
use Give\PaymentGateways\Gateways\Stripe\Actions\GetOrCreateStripeCustomer;
use Give\PaymentGateways\Gateways\Stripe\Actions\SaveDonationSummary;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentIntent;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;
use Give_Scripts;
use GiveStripe\PaymentMethods\Plaid\Actions\GetOrAddBankAccountToStripeCustomer;
use GiveStripe\PaymentMethods\Plaid\Actions\RetrieveTokenForPaymentFromPlaid;

/**
 * @since 2.5.0
 */
class PlaidGateway extends PaymentGateway
{

    /**
     * @since 2.5.0
     * @return void
     */
    public function enqueuePublicAssets()
    {
        Give_Scripts::register_script(
            'give-plaid-checkout-js',
            give_stripe_ach_get_plaid_checkout_url(),
            ['jquery'],
            null
        );
        wp_enqueue_script('give-plaid-checkout-js');

        Give_Scripts::register_script(
            'give-stripe-ach-js',
            GIVE_STRIPE_PLUGIN_URL . 'assets/dist/js/give-stripe-ach.js',
            ['jquery'],
            GIVE_STRIPE_VERSION
        );
        wp_enqueue_script('give-stripe-ach-js');

        wp_localize_script(
            'give-stripe-ach-js',
            'give_stripe_ach_vars',
            [
                'sitename' => get_bloginfo('name'),
                'plaid_endpoint' => give_stripe_ach_get_api_endpoint(),
                'plaid_api_version' => give_stripe_ach_get_current_api_version(),
            ]
        );
    }

    /**
     * @inheritDoc
     */
    public function getLegacyFormFieldMarkup(int $formId, array $args): string
    {
        if (Utils::isLegacyForm()) {
            return '';
        }

        // If viewing a non-Legacy form, output a note explaining the
        // process of completing an iDEAL gateway donation offsite
        return sprintf(
            '
            <fieldset class="no-fields">
                <div style="display: flex; justify-content: center; margin-top: 20px;">%4$s</div>
                <p style="text-align: center;"><b>%1$s</b></p>
                <p style="text-align: center;"><b>%2$s</b> %3$s</p>
            </fieldset>
            ',
            esc_html__('Make your donation quickly and securely directly through your bank account', 'give-stripe'),
            esc_html__('How it works:', 'give-stripe'),
            esc_html__(
                'A window will open after you click the Donate Now button where you can securely make your donation through your bank account. You will then be brought back to this page to view your receipt.',
                'give-stripe'
            ),
            $this->getGatewayLogo()
        );
    }

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'stripe_ach';
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
    public function getName(): string
    {
        return esc_html__('Stripe + Plaid', 'give-stripe');
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return esc_html__('Bank Account', 'give-stripe');
    }

    /**
     * @inheritDoc
     *
     * @return PaymentCommand
     */
    public function createPayment(Donation $donation, $gatewayData): PaymentCommand
    {
        $stripeAchData = $gatewayData['stripeAchData'];
        $bankAccountTokenId = Call::invoke(RetrieveTokenForPaymentFromPlaid::class, $stripeAchData);
        $donationSummary = Call::invoke(SaveDonationSummary::class, $donation);
        $stripeCustomer = Call::invoke(GetOrCreateStripeCustomer::class, $donation);
        $bankAccountId = Call::invoke(
            GetOrAddBankAccountToStripeCustomer::class,
            $stripeCustomer,
            $bankAccountTokenId
        );

        $createIntentAction = new CreatePaymentIntent($this->getPaymentIntentArgs());

        /* @var PaymentIntent $paymentIntent */
        $paymentIntent = $createIntentAction(
            $donation,
            $donationSummary,
            $stripeCustomer,
            new PaymentMethod($bankAccountId)
        );

        return new PaymentComplete($paymentIntent->id());
    }

    /**
     * @since 2.5.0
     * @return string
     */
    private function getGatewayLogo(): string
    {
        return file_get_contents(
            trailingslashit(GIVE_STRIPE_PLUGIN_DIR) . 'src/PaymentMethods/Plaid/resources/images/plaid-logo.svg'
        );
    }

    /**
     * @since 2.5.0
     * @inerhitDoc
     * @throws Exception
     */
    public function refundDonation(Donation $donation)
    {
        throw new Exception('Method has not been implemented yet. Please use the legacy method in the meantime.');
    }

    /**
     * @since 2.5.0
     */
    private function getPaymentIntentArgs(): array
    {
        return [
            'payment_method_types' => ['us_bank_account'],
            'setup_future_usage' => 'on_session',
            'mandate_data' => [
                'customer_acceptance' => [
                    'type' => 'online',
                    'online' => [
                        'ip_address' => give_get_ip(),
                        'user_agent' => give_get_user_agent(),
                    ],
                ],
            ],
        ];
    }
}
