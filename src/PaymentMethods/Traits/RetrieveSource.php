<?php

namespace GiveStripe\PaymentMethods\Traits;

use Give\Framework\Exceptions\Primitives\Exception;
use Stripe\Source;

/**
 * @since 2.5.0
 */
trait RetrieveSource
{
    /**
     * @since 2.5.0
     * @throws Exception
     */
    public function getSourceDetails(string $sourceId, array $options = []): Source
    {
        // Set Application Info.
        give_stripe_set_app_info();

        try {
            // Retrieve Source Object.
            return Source::retrieve(
                $sourceId,
                wp_parse_args(
                    $options,
                    give_stripe_get_connected_account_options()
                )
            );
        } catch (\Exception $e) {
            // Something went wrong outside of Stripe.
            throw new Exception(
                sprintf(
                /* translators: %s Exception Message Body */
                    esc_html__('Unable to retrieve source. Details: %s', 'give-stripe'),
                    $e->getMessage()
                )
            );
        }
    }
}

