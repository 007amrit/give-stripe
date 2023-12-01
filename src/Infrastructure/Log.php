<?php

namespace GiveStripe\Infrastructure;

/**
 * Class Log
 *
 * @package GiveStripe\Infrastructure
 * @since 2.3.0
 */
class Log extends \Give\Log\Log
{
    /**
     * @inheritDoc
     *
     * @since 2.3.0
     *
     * @param array $arguments
     *
     * @param string $name
     */
    public static function __callStatic($name, $arguments)
    {
        $arguments[1]['source'] = 'Give Stripe';

        parent::__callStatic($name, $arguments);
    }

}
