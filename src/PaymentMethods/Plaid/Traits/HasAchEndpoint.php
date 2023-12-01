<?php

namespace GiveStripe\PaymentMethods\Plaid\Traits;

use GiveStripe\PaymentMethods\Plaid\Repositories\Plaid;
use http\Exception\InvalidArgumentException;

/**
 * @since 2.5.0
 */
trait HasAchEndpoint
{
	/**
	 * @param $tokenType
	 *
	 * @return string
	 * @throws \Give\Framework\Exceptions\Primitives\InvalidArgumentException
	 */
	public function getAchEndpointByTokenType($tokenType)
	{
		switch ($tokenType) {
			case 'exchange':
				$endpoint_url = esc_url('https://%1$s.plaid.com/item/public_token/exchange');
				break;

			case 'bank_account':
				$endpoint_url = esc_url('https://%1$s.plaid.com/processor/stripe/bank_account_token/create');
				break;

			default:
				throw new InvalidArgumentException('Invalid token type for Plaid Ach endpoint.');
		}

		return sprintf(
			$endpoint_url,
			give(Plaid::class)->getApiMode()
		);
	}
}
