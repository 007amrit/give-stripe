<?php

namespace GiveStripe\PaymentMethods\Ideal;

use Give\Donations\Models\Donation;
use Give\Donations\Models\DonationNote;
use Give\Donations\ValueObjects\DonationStatus;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Commands\RedirectOffsite;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give\Framework\PaymentGateways\Log\PaymentGatewayLog;
use Give\Framework\PaymentGateways\PaymentGateway;
use Give\Helpers\Call;
use Give\Helpers\Form\Utils;
use Give\PaymentGateways\Gateways\PayPalStandard\Actions\GenerateDonationFailedPageUrl;
use Give\PaymentGateways\Gateways\PayPalStandard\Actions\GenerateDonationReceiptPageUrl;
use Give\PaymentGateways\Gateways\Stripe\Actions\GetOrCreateStripeCustomer;
use Give\PaymentGateways\Gateways\Stripe\Actions\SaveDonationSummary;
use Give\PaymentGateways\Gateways\Stripe\ValueObjects\PaymentMethod;
use GiveStripe\PaymentMethods\Traits\CreateCharge;
use GiveStripe\PaymentMethods\Traits\CreateSource;
use GiveStripe\PaymentMethods\Traits\RetrieveSource;

/**
 * @since 2.5.0
 */
class IdealGateway extends PaymentGateway
{
    use CreateCharge;
    use CreateSource;
    use RetrieveSource;

    /**
     * @since 2.5.0
     * @inerhitDoc
     */
    public $secureRouteMethods = [
        'handleStripeIdealSourceAuthentication'
    ];

    /**
     * @since 2.5.0
     * @inerhitDoc
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
				<div style="display: flex; justify-content: center;">
					<img title="iDEAL_SVG" src="%4$s" alt="iDEAL_SVG" width="170" height="170">
				</div>
				<p style="text-align: center;"><b>%1$s</b></p>
				<p style="text-align: center;">
					<b>%2$s</b> %3$s
				</p>
			</fieldset>
			',
            esc_html__('Make your donation quickly and securely with iDEAL', 'give-stripe'),
            esc_html__('How it works:', 'give-stripe'),
            esc_html__(
                'In order to pay with iDEAL, you will be redirected to your online banking website where you can authenticate and confirm the donation payment.',
                'give-stripe'
            ),
            trailingslashit(GIVE_STRIPE_PLUGIN_URL) . 'assets/dist/images/ideal-logo.svg'
        );
    }

    /**
     * @since 2.5.0
     * @inheritDoc
     */
    public function getId(): string
    {
        return self::id();
    }

    /**
     * @since 2.5.0
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'stripe_ideal';
    }

    /**
     * @since 2.5.0
     * @inheritDoc
     */
    public function getName(): string
    {
        return esc_html__('Stripe iDEAL', 'give-stripe');
    }

    /**
     * @since 2.5.0
     * @inheritDoc
     */
    public function getPaymentMethodLabel(): string
    {
        return esc_html__('iDEAL', 'give-stripe');
    }

    /**
     * @since 2.5.0
     *
     * @param PaymentMethod $gatewayData
     *
     * @inheritDoc
     */
    public function createPayment(Donation $donation, $gatewayData = null)
    {
        try {
            $donationSummary = Call::invoke(SaveDonationSummary::class, $donation);
            $giveStripeCustomer = Call::invoke(GetOrCreateStripeCustomer::class, $donation);

            $stripeSourceArgs = [
                'type' => 'ideal',
                'amount' => $donation->amount->formatToMinorAmount(),
                'currency' => $donation->amount->getCurrency()->getCode(),
                'owner' => [
                    'name' => trim("{$donation->firstName} {$donation->lastName}"),
                    'email' => $donation->email,
                ],
                'statement_descriptor' => $donationSummary->getSummaryWithDonor(),
                'redirect' => [
                    'return_url' => $this->generateSecureGatewayRouteUrl(
                        'handleStripeIdealSourceAuthentication',
                        $donation->id,
                        [
                            'donation-id' => $donation->id,
                            'customer-id' => $giveStripeCustomer->get_id()
                        ]
                    )
                ],
            ];

            $source = $this->createSource($stripeSourceArgs);

            DonationNote::create([
                'donationId' => $donation->id,
                'content' => 'Stripe Source ID: ' . $source->id
            ]);

            $donation->status = DonationStatus::PROCESSING();
            $donation->save();

            return new RedirectOffsite($source->redirect->url);
        } catch (\Exception $e) {
            // Something went wrong outside of Stripe.
            PaymentGatewayLog::error(
                __('Stripe Ideal Payment Error', 'give-stripe'),
                [
                    'Error Message' => $e->getMessage(),
                    'Donation' => $donation->toArray()
                ]
            );

            give_set_error(
                'stripe_error',
                esc_html__('An error occurred while processing the donation. Please try again.', 'give-stripe')
            );

            give_send_back_to_checkout('?payment-mode=stripe_ideal');
        }
    }

    /**
     * @since 2.5.0
     */
    public function handleStripeIdealSourceAuthentication($queryParams)
    {
        $donationId = absint($queryParams['donation-id']);

        try {
            $source = $this->getSourceDetails($queryParams['source']);

            if ('chargeable' === $source->status) {
                $charge_args = [
                    'amount' => $source->amount,
                    'currency' => $source->currency,
                    'customer' => $queryParams['customer-id'],
                    'source' => $source->id,
                    'description' => $source->statement_descriptor,
                    'metadata' => give_stripe_prepare_metadata($donationId),
                ];

                $charge = $this->createCharge($charge_args);

                give_update_payment_status($donationId);
                give_set_payment_transaction_id($donationId, $charge->id);

                wp_safe_redirect(Call::invoke(GenerateDonationReceiptPageUrl::class, $donationId));
            } elseif ('consumed' === $source->status) {
                // Return to success page if iDEAL source is already consumed.
                // Newly created iDEAL source can be use only for one time.
                // Newly created iDEAL source status set to chargeable after creating charge for source that status update to consumed.
                // https://stripe.com/docs/api/sources/object#source_object-status
                wp_safe_redirect(Call::invoke(GenerateDonationReceiptPageUrl::class, $donationId));
            } else {
                throw new PaymentGatewayException(
                    esc_html__('Stripe Ideal Payment Authentication Error', 'give-stripe')
                );
            }
        } catch (Exception $ex) {
            give_update_payment_status($donationId, 'failed');

            PaymentGatewayLog::error(
                esc_html__('Stripe Ideal Payment Authentication Error', 'give-stripe'),
                [
                    'Request' => $queryParams,
                    'Error' => $ex->getMessage()
                ]
            );

            wp_safe_redirect(Call::invoke(GenerateDonationFailedPageUrl::class, $donationId));
        }

        give_die();
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
}
