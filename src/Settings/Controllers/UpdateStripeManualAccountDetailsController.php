<?php

namespace GiveStripe\Settings\Controllers;

use Exception;
use Give\PaymentGateways\Stripe\Models\AccountDetail;
use Give\PaymentGateways\Stripe\Repositories\Settings;
use GiveStripe\Settings\DataTransferObjects\UpdateStripeManualAccountDetailsDto;

/**
 * Class UpdateStripeManualAccountDetailsController
 * @package GiveStripe\Settings\Controllers
 * @unlreased
 */
class UpdateStripeManualAccountDetailsController
{
    /**
     * @since 2.4.0
     */
    public function __invoke()
    {
        $this->validateRequest();
        $requestData = UpdateStripeManualAccountDetailsDto::fromArray(give_clean($_POST));

        try {
            $stripeAccount = AccountDetail::fromArray($requestData->toArray());
            give(Settings::class)->updateStripeAccount($stripeAccount);
        } catch (Exception $e) {
            wp_send_json_error();
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
