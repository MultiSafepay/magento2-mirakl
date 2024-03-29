<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * See DISCLAIMER.md for disclaimer details.
 */

declare(strict_types=1);

namespace MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates;

use MultiSafepay\Api\AbstractManager;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\Request\ChargeRequest;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\Request\FundRequest;
use MultiSafepay\Api\Base\Response;
use Psr\Http\Client\ClientExceptionInterface;

class AffiliatesManager extends AbstractManager
{

    /**
     * @param string $accountId
     * @param FundRequest $fundRequest
     * @return Response
     * @throws ClientExceptionInterface
     */
    public function fund(string $accountId, FundRequest $fundRequest): Response
    {
        return $this->client->createPostRequest(
            'json/accounts/' . $accountId . '/funds',
            $fundRequest,
            ['transaction' => $fundRequest->getData()]
        );
    }

    /**
     * @param string $accountId
     * @param ChargeRequest $chargeRequest
     * @return Response
     * @throws ClientExceptionInterface
     */
    public function charge(string $accountId, ChargeRequest $chargeRequest): Response
    {
        return $this->client->createPostRequest(
            'json/accounts/' . $accountId . '/charges',
            $chargeRequest,
            ['transaction' => $chargeRequest->getData()]
        );
    }
}
