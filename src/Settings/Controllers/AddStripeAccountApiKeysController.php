<?php

namespace GiveStripe\Settings\Controllers;

use Exception;
use Give\PaymentGateways\Stripe\Exceptions\DuplicateStripeAccountName;
use Give\PaymentGateways\Stripe\Exceptions\StripeAccountAlreadyConnected;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use GiveStripe\Settings\Actions\AddStripeAccountApiKeys;
use GiveStripe\Settings\DataTransferObjects\AddStripeAccountApiKeysDto;

/**
 * Class AddStripeAccountApiKeysController
 * @package GiveStripe\Settings\Controllers
 *
 * @since 2.4.0
 */
class AddStripeAccountApiKeysController
{
    /**
     * @since 2.4.0
     */
    public function __invoke()
    {
        $this->validateRequest();
        $requestData = AddStripeAccountApiKeysDto::fromArray(give_clean($_POST));

        try {
            $stripeAccount = AccountDetail::fromArray($requestData->getStripeAccountDetails());
            $action = give(AddStripeAccountApiKeys::class);
            $action($stripeAccount, $requestData->formId);
        } catch (StripeAccountAlreadyConnected $e) {
            wp_send_json_error([
                'error' => esc_html__(
                    'You are adding already connected account. Please use new Stripe api keys to connected new account.',
                    'give-stripe'
                )
            ]);
        } catch (DuplicateStripeAccountName $e) {
            wp_send_json_error([
                'error' => esc_html__(
                    'Stripe account already exist with same name. Please use different account name.',
                    'give-stripe'
                )
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'error' => esc_html__(
                    'We are unable to add Stripe account. Please check log for error detail and contact support team for assistance.',
                    'give'
                )
            ]);
        }

        wp_send_json_success();
    }

    /**
     * @since 2.4.0
     */
    private function validateRequest()
    {
        if (!current_user_can('manage_give_settings')) {
            die('Invalid request.');
        }
    }
}
