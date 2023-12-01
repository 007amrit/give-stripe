<?php

namespace GiveStripe\PaymentMethods\Plaid\Actions;

use Give\Framework\Exceptions\Primitives\Exception;
use GiveStripe\PaymentMethods\Plaid\Repositories\Plaid;
use GiveStripe\PaymentMethods\Plaid\Traits\HasAchEndpoint;
use GiveStripe\PaymentMethods\Plaid\ValueObjects\StripeAchData;
use stdClass;

/**
 * @since 2.5.0
 */
class RetrieveTokenForPaymentFromPlaid
{
    use HasAchEndpoint;

    /** @var Plaid $plaidSettings */
    private $plaidSettings;

    /**
     * @since 2.5.0
     */
    public function __construct(Plaid $plaidSettings)
    {
        $this->plaidSettings = $plaidSettings;
    }

    /**
     * @since 2.5.0
     *
     * @throws Exception
     */
    public function __invoke(StripeAchData $stripeAchData): string
    {
        return $this->getBankAccountToken(
            $stripeAchData,
            $this->getPublicExchangeToken($stripeAchData)
        );
    }

    /**
     * @since 2.5.0
     * Api request documentation: https://plaid.com/docs/api/tokens/#itempublic_tokenexchange
     *
     * @throws Exception
     */
    private function getPublicExchangeToken(StripeAchData $stripeAchData): stdClass
    {
        $response = wp_remote_post(
            $this->getAchEndpointByTokenType('exchange'),
            [
                'timeout' => 15,
                'body' => wp_json_encode(
                    [
                        'client_id' => $this->plaidSettings->getClientId(),
                        'secret' => $this->plaidSettings->getClientSecretKey(),
                        'public_token' => $stripeAchData->token,
                    ]
                ),
                'headers' => [
                    'Content-Type' => 'application/json;charset=UTF-8',
                ],
            ]
        );

        // Error check.
        if (is_wp_error($response)) {
            throw new Exception(
                sprintf(
                /* translators: %s Error Message */
                    esc_html__(
                        'The Stripe ACH gateway failed to make the call to the Plaid server to get the Stripe bank account token along with the Plaid access token that can be used for other Plaid API requests. Details: %s',
                        'give-stripe'
                    ),
                    $response->get_error_message()
                )
            );
        }

        $response = json_decode(wp_remote_retrieve_body($response));
        $this->validateApiResponse($response);

        return $response;
    }

    /**
     * @since 2.5.0
     *
     * Api request documentation: https://plaid.com/docs/api/processors/#processorstripebank_account_tokencreate
     *
     * @throws Exception
     */
    private function getBankAccountToken(StripeAchData $stripeAchData, stdClass $publicExchangeTokenResponse): string
    {
        $response = wp_remote_post(
            $this->getAchEndpointByTokenType('bank_account'),
            [
                'timeout' => 15,
                'body' => wp_json_encode([
                    'client_id' => $this->plaidSettings->getClientId(),
                    'secret' => $this->plaidSettings->getClientSecretKey(),
                    'access_token' => $publicExchangeTokenResponse->access_token,
                    'account_id' => $stripeAchData->bankAccountId,
                ]),
                'headers' => [
                    'Content-Type' => 'application/json;charset=UTF-8',
                ],
            ]
        );

        $response = json_decode(wp_remote_retrieve_body($response));

        if (empty($response)) {
            throw new Exception(
                sprintf(
                /* translators: %s Error Message */
                    esc_html__(
                        'An error occurred when processing a donation via Plaid\'s API. Details: %s',
                        'give-stripe'
                    ),
                    print_r(
                        [
                            'bank_request' => $response,
                            'exchange_response' => $publicExchangeTokenResponse,
                        ],
                        true
                    )
                )
            );
        }

        // Is there an error returned from the API?
        $this->validateApiResponse($response);

        return $response->stripe_bank_account_token;
    }

    /**
     * @since 2.5.0
     * @throws Exception
     */
    private function validateApiResponse($publicExchangeTokenResponse)
    {
        // Is there an error returned from the API?
        if (isset($publicExchangeTokenResponse->error_code)) {
            throw new Exception(
                sprintf(
                /* translators: %s Error Message */
                    esc_html__(
                        'An error occurred when processing a donation via Plaid\'s API. Details: %s',
                        'give-stripe'
                    ),
                    "{$publicExchangeTokenResponse->error_code} (error code) - {$publicExchangeTokenResponse->error_type} (error type) - {$publicExchangeTokenResponse->error_message}"
                )
            );
        }
    }
}
