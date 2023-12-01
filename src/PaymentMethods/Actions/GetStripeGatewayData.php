<?php

namespace GiveStripe\PaymentMethods\Actions;

use Give\Donations\Models\Donation;
use Give\Framework\Exceptions\Primitives\Exception;
use Give\PaymentGateways\Gateways\Stripe\Actions\GetPaymentMethodFromRequest;
use Give\PaymentGateways\Gateways\Stripe\Exceptions\PaymentMethodException;
use Stripe\Exception\ApiErrorException;

class GetStripeGatewayData
{
    /**
     * Returns gatewayData array to be used in stripe gateways.
     * This will eventually be moved into core
     *
     * @since 2.5.0
     *
     * @throws PaymentMethodException
     * @throws Exception
     * @throws ApiErrorException
     */
    public function __invoke($gatewayData, Donation $donation): array
    {
        $paymentMethod = (new GetPaymentMethodFromRequest())($donation);

        $gatewayData['stripePaymentMethod'] = $paymentMethod;

        return $gatewayData;
    }
}
