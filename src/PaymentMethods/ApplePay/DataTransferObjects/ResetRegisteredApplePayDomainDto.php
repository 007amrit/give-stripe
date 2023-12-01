<?php

namespace GiveStripe\PaymentMethods\ApplePay\DataTransferObjects;

use Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

/**
 * Class ResetRegisteredApplePayDomainDto
 * @package GiveStripe\Actions\DataTransferObjects
 *
 * @since 2.4.0
 */
class ResetRegisteredApplePayDomainDto
{
    /**
     * @var mixed|void
     */
    public $secretKey;
    /**
     * @var mixed|void
     */
    public $accountType;
    /**
     * @var mixed|void
     */
    public $accountId;
    /**
     * @var mixed|string
     */
    public $accountSlug;
    /**
     * @var mixed
     */
    public $stripeDomainRegistrationId;

    /**
     * RegisterApplePayDomainDto constructor.
     *
     * @since 2.4.0
     */
    public function __construct()
    {
        $this->accounts = give_stripe_get_all_accounts();
    }

    /**
     * @since 2.4.0
     *
     * @param array $array
     *
     * @return self
     */
    public static function fromArray($array)
    {
        $self = new static();

        try {
            $self->accountSlug = !empty($array['slug']) ? $array['slug'] : '';
            $self->secretKey = $self->accounts[$self->accountSlug]['live_secret_key'];
            $self->stripeDomainRegistrationId = $self->accounts[$self->accountSlug]['register_apple_pay']['id'];
            $self->accountType = $self->accounts[$self->accountSlug]['type'];
            $self->accountId = $self->accounts[$self->accountSlug]['account_id'];
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }
        $self->accountSlug = !empty($array['slug']) ? $array['slug'] : '';

        return $self;
    }
}
