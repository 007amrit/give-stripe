<?php

namespace GiveStripe\Settings\DataTransferObjects;

/**
 * Class UpdateStripeManualAccountDetailsDto
 * @package GiveStripe\Settings\DataTransferObjects
 *
 * @since 2.4.0
 */
class UpdateStripeManualAccountDetailsDto
{
    /**
     * @var string
     */
    public $type;
    /**
     * @var string
     */
    public $accountSlug;
    /**
     * @var mixed|string
     */
    public $accountName;
    /**
     * @var mixed|string
     */
    public $liveSecretKey;
    /**
     * @var mixed|string
     */
    public $testSecretKey;
    /**
     * @var mixed|string
     */
    public $livePublishableKey;
    /**
     * @var mixed|string
     */
    public $testPublishableKey;
    /**
     * @var string
     */
    public $accountEmail;
    /**
     * @var string
     */
    public $accountCountry;
    /**
     * @var string
     */
    public $accountId;

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

        $self->type = 'manual';
        $self->accountSlug = !empty($array['account_slug']) ? $array['account_slug'] : give_stripe_get_unique_account_slug(
            give_stripe_get_all_accounts()
        );
        $self->accountName = !empty($array['account_name']) ? $array['account_name'] : give_stripe_convert_slug_to_title(
            $self->accountSlug
        );
        $self->liveSecretKey = !empty($array['live_secret_key']) ? $array['live_secret_key'] : '';
        $self->testSecretKey = !empty($array['test_secret_key']) ? $array['test_secret_key'] : '';
        $self->livePublishableKey = !empty($array['live_publishable_key']) ? $array['live_publishable_key'] : '';
        $self->testPublishableKey = !empty($array['test_publishable_key']) ? $array['test_publishable_key'] : '';

        // This parameter will be empty for manual API Keys Stripe account.
        $self->accountEmail = '';
        $self->accountCountry = '';
        $self->accountId = '';

        return $self;
    }

    /**
     * @since 2.4.0
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'type' => $this->type,
            'account_id' => $this->accountId,
            'account_name' => $this->accountName,
            'account_slug' => $this->accountSlug,
            'account_country' => $this->accountCountry,
            'account_email' => $this->accountEmail,
            'live_secret_key' => $this->liveSecretKey,
            'test_secret_key' => $this->testSecretKey,
            'live_publishable_key' => $this->livePublishableKey,
            'test_publishable_key' => $this->testPublishableKey
        ];
    }
}
