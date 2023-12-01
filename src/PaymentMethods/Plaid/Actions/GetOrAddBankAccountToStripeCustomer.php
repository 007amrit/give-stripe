<?php

namespace GiveStripe\PaymentMethods\Plaid\Actions;

use Give\Framework\Exceptions\Primitives\Exception;
use Give\Framework\PaymentGateways\Exceptions\PaymentGatewayException;
use Give_Stripe_Customer;
use GiveStripe\PaymentMethods\Traits\RetrieveToken;


/**
 * @since 2.5.0
 */
class GetOrAddBankAccountToStripeCustomer
{
    use RetrieveToken;

    /**
     * @throws Exception
     */
    public function __invoke(
        Give_Stripe_Customer $giveStripeCustomer,
        string $plaidApiBankAccountTokenId
    ): string {
        $stripeToken = $this->getTokenDetails(
            $plaidApiBankAccountTokenId,
            ['expand' => 'id']
        );

        $bankAccountId = $stripeToken->bank_account->id ?? '';
        $fingerprint = $stripeToken->bank_account->fingerprint ?? '';

        // Bank account id is required to create a payment.
        if (!$bankAccountId) {
            throw new PaymentGatewayException(
                esc_html__(
                    'There was a problem identifying your bank account with the payment gateway. Please try you donation again.',
                    'give-stripe'
                )
            );
        }

        try {
            $stripeCustomerBankAccounts = $giveStripeCustomer->customer_data
                ->sources
                ->all(['limit' => 100, 'object' => 'bank_account'])
                ->toArray();

            // Find attached bank account id.
            // Loop through sources and check for match with the new bank account ID.
            foreach ($stripeCustomerBankAccounts['data'] as $bankAccount) {
                // Bank account ID & fingerprint are both viable matching properties.
                if ($bankAccount['id'] === $bankAccountId) {
                    return $bankAccountId;
                }

                if ($bankAccount['fingerprint'] === $fingerprint) {
                    return $bankAccount['id'];
                }
            }

            return $giveStripeCustomer->customer_data
                ->sources
                ->create([
                    'source' => $plaidApiBankAccountTokenId,
                ])->id;
        } catch (\Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
