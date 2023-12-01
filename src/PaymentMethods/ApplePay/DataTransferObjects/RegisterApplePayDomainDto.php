<?php

namespace GiveStripe\PaymentMethods\ApplePay\DataTransferObjects;

use Exception;
use Give\Framework\Exceptions\Primitives\InvalidArgumentException;

/**
 * Class RegisterApplePayDomainDto
 * @package GiveStripe\Actions\DataTransferObjects
 *
 * @since 2.4.0
 */
class RegisterApplePayDomainDto
{
    /**
     * @var array
     */
    public $accounts;
    /**
     * @var mixed|string
     */
    public $accountSlug;
    /**
     * @var mixed
     */
    public $secretKey;
    /**
     * @var mixed
     */
    public $accountType;
    /**
     * @var mixed
     */
    public $accountId;

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
            $self->accountType = $self->accounts[$self->accountSlug]['type'];
            $self->accountId = $self->accounts[$self->accountSlug]['account_id'];
        } catch (Exception $e) {
            throw new InvalidArgumentException($e->getMessage());
        }

        return $self;
    }
}
