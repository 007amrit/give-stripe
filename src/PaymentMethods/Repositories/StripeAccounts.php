<?php

namespace GiveStripe\PaymentMethods\Repositories;

/**
 * Class StripeAccounts
 * @package GiveStripe\PaymentMethods\ApplePay\Actions
 *
 * @since 2.4.0
 */
class StripeAccounts
{
    /**
     * @since 2.4.0
     *
     * @param $accountSlug
     *
     * @return bool
     */
    public function isAccountExist($accountSlug)
    {
        $accounts = give_stripe_get_all_accounts();
        return array_key_exists($accountSlug, $accounts);
    }

    /**
     * @since 2.4.0
     * @return string
     */
    public function getWebsiteDomainName()
    {
        $url = get_home_url();
        $urlParts = explode('://', untrailingslashit($url));

        return array_pop($urlParts);
    }

    /**
     * @since 2.4.0
     *
     * @return bool
     */
    public function setWebsiteDomainForApplePay($accountSlug)
    {
        $accounts = give_stripe_get_all_accounts();
        $accounts[$accountSlug]['register_apple_pay'] = true;

        return give_update_option('_give_stripe_get_all_accounts', $accounts);
    }

    /**
     * @since 2.4.0
     *
     * @return bool
     */
    public function unsetWebsiteDomainForApplePay($accountSlug)
    {
        $accounts = give_stripe_get_all_accounts();
        $accounts[$accountSlug]['register_apple_pay'] = false;

        return give_update_option('_give_stripe_get_all_accounts', $accounts);
    }
}
