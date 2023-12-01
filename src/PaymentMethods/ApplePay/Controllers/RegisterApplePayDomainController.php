<?php

namespace GiveStripe\PaymentMethods\ApplePay\Controllers;

use Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use GiveStripe\Infrastructure\Log;
use GiveStripe\PaymentMethods\ApplePay\DataTransferObjects\RegisterApplePayDomainDto;
use GiveStripe\PaymentMethods\Repositories\StripeAccounts;
use Stripe\ApplePayDomain;
use Stripe\Stripe;

/**
 * Class RegisterApplePayDomain
 * @package GiveStripe\Actions
 *
 * @since 2.4.0
 */
class RegisterApplePayDomainController
{
    /**
     * @var RegisterApplePayDomainDto
     */
    private $registerApplePayDomainDto;

    /**
     * @var StripeAccounts
     */
    private $stripeAccountsRepository;

    /**
     * RegisterApplePayDomain constructor.
     *
     * @since 2.4.0
     */
    public function __construct(StripeAccounts $stripeAccountsRepositories)
    {
        $this->stripeAccountsRepository = $stripeAccountsRepositories;

        try {
            $this->registerApplePayDomainDto = RegisterApplePayDomainDto::fromArray(give_clean($_POST));
        } catch (InvalidArgumentException $e) {
            wp_send_json_error([
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * @since 2.4.0
     */
    public function __invoke()
    {
        $this->validRequest();
        $this->validStripeAccount();

        try {
            $response = $this->registerDomainOnStripe();
            $this->stripeAccountsRepository
                ->setWebsiteDomainForApplePay($this->registerApplePayDomainDto->accountSlug);

            wp_send_json_success(['response' => $response]);
        } catch (Exception $e) {
            Log::error(
                'Apple Pay Registration - Error',
                [
                    'Error Detail' => $e->getMessage()
                ]
            );

            $message = sprintf(
            /* translators: %s Exception Message Body */
                esc_html__('Unable to register domain association with Apple Pay. Details: %s', 'give-stripe'),
                $e->getMessage()
            );
            wp_send_json_error(['error' => $message]);
        }
    }

    /**
     * @since 2.4.0
     */
    private function validRequest()
    {
        if (!current_user_can('manage_give_settings')) {
            wp_send_json_error(['message' => esc_html__('Unauthorized access.', 'give-stripe')]);
        }
    }

    /**
     * @since 2.4.0
     */
    private function validStripeAccount()
    {
        if (
            !$this->stripeAccountsRepository
                ->isAccountExist($this->registerApplePayDomainDto->accountSlug)
        ) {
            wp_send_json_error(['message' => esc_html__('Invalid Stripe account.', 'give-stripe')]);
        }
    }

    /**
     * @since 2.4.0
     */
    private function registerDomainOnStripe()
    {
        Stripe::setApiKey($this->registerApplePayDomainDto->secretKey);

        $connectArgs = [];
        if ('connect' === $this->registerApplePayDomainDto->accountType) {
            $connectArgs = ['stripe_account' => $this->registerApplePayDomainDto->accountId,];
        }

        ApplePayDomain::create(
            ['domain_name' => $this->stripeAccountsRepository->getWebsiteDomainName(),],
            $connectArgs
        );
    }
}
