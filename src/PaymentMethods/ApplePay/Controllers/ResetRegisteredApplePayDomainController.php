<?php

namespace GiveStripe\PaymentMethods\ApplePay\Controllers;

use Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;
use GiveStripe\Infrastructure\Log;
use GiveStripe\PaymentMethods\ApplePay\DataTransferObjects\ResetRegisteredApplePayDomainDto;
use GiveStripe\PaymentMethods\Repositories\StripeAccounts;
use Stripe\ApplePayDomain;
use Stripe\Stripe;

/**
 * Class ResetRegisteredApplePayDomain
 * @package GiveStripe\Actions\DataTransferObjects
 *
 * @since 2.4.0
 */
class ResetRegisteredApplePayDomainController
{
    /**
     * @var ResetRegisteredApplePayDomainDto
     */
    private $resetRegisteredApplePayDomainDto;
    /**
     * @var StripeAccounts
     */
    private $stripeAccountsRepository;

    /**
     * ResetRegisteredApplePayDomain constructor.
     *
     * @since 2.4.0
     */
    public function __construct(StripeAccounts $stripeAccountsRepository)
    {
        $this->stripeAccountsRepository = $stripeAccountsRepository;
        try {
            $this->resetRegisteredApplePayDomainDto = ResetRegisteredApplePayDomainDto::fromArray(give_clean($_POST));
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
            $this->unregisterDomainOnStripe();
            $this->stripeAccountsRepository
                ->unsetWebsiteDomainForApplePay($this->resetRegisteredApplePayDomainDto->accountSlug);

            wp_send_json_success();
        } catch (Exception $e) {
            Log::error(
                'Apple Pay Registration - Error',
                [
                    'Error Detail' => $e->getMessage()
                ]
            );

            $message = sprintf(
            /* translators: %s Exception Message Body */
                esc_html__('Unable to unregister domain association with Apple Pay. Details: %s', 'give-stripe'),
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
        if (!$this->stripeAccountsRepository->isAccountExist($this->resetRegisteredApplePayDomainDto->accountSlug)) {
            wp_send_json_error(['message' => esc_html__('Invalid Stripe account.', 'give-stripe')]);
        }
    }

    /**
     * @since 2.4.0
     * @throws Exception
     */
    private function unregisterDomainOnStripe()
    {
        Stripe::setApiKey($this->resetRegisteredApplePayDomainDto->secretKey);

        $connectArgs = [];
        if ('connect' === $this->resetRegisteredApplePayDomainDto->accountType) {
            $connectArgs = ['stripe_account' => $this->resetRegisteredApplePayDomainDto->accountId,];
        }

        try {
            $listOfApplePayDomains = ApplePayDomain::all(
                [],
                $connectArgs
            )->data;

            foreach ($listOfApplePayDomains as $domainData) {
                if ($domainData['domain_name'] === $this->stripeAccountsRepository->getWebsiteDomainName()) {
                    $applePayDomain = new ApplePayDomain($domainData['id']);
                    $applePayDomain->delete([], $connectArgs);

                    return;
                }
            }
        } catch (Exception $e) {
            //  404 http status code missing domain does not exist on Stripe. That means someone deleted domain manually.
            // so instead of throw error reset value in db to allow admin to register domain again.
            if ($e->getHttpStatus() !== '404') {
                throw $e;
            }
        }
    }
}
