<?php

namespace GiveStripe\PaymentMethods\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use GiveStripe\PaymentMethods\Plaid\Actions\GetStripeAchDataFromRequest;

class GetPlaidGatewayData
{
    /**
     * Returns gatewayData array to be used in plaid gateway.
     *
     * @since 2.5.0
     *
     * @throws PaymentMethodException
     * @throws Exception
     */
    public function __invoke($gatewayData, Donation $donation): array
    {
        $stripeAchData = (new GetStripeAchDataFromRequest())($donation);

        $gatewayData['stripeAchData'] = $stripeAchData;

        return $gatewayData;
    }
}
