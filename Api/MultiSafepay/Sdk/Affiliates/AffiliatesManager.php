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
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\FundRequest\FundRequest;
use MultiSafepay\Api\Base\Response;
use Psr\Http\Client\ClientExceptionInterface;

class AffiliatesManager extends AbstractManager
{

    /**
     * @param int $accountId
     * @param FundRequest $fundRequest
     * @return Response
     * @throws ClientExceptionInterface
     */
    public function fund(int $accountId, FundRequest $fundRequest): Response
    {
        return $this->client->createPostRequest(
            'json/accounts/' . $accountId . '/funds',
            $fundRequest,
            ['transaction' => $fundRequest->getData()]
        );
    }
}
