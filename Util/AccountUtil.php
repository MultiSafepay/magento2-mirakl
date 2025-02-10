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

namespace MultiSafepay\Mirakl\Util;

use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient as MiraklFrontApiClient;
use MultiSafepay\Mirakl\Api\Mirakl\Request\ShopRequest;
use MultiSafepay\Mirakl\Logger\Logger;

class AccountUtil
{
    /**
     * @var MiraklFrontApiClient
     */
    private $miraklFrontApiClient;

    /**
     * @var ShopRequest
     */
    private $shopRequest;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param MiraklFrontApiClient $miraklFrontApiClient
     * @param ShopRequest $shopRequest
     * @param Logger $logger
     */
    public function __construct(
        MiraklFrontApiClient $miraklFrontApiClient,
        ShopRequest $shopRequest,
        Logger $logger
    ) {
        $this->miraklFrontApiClient = $miraklFrontApiClient;
        $this->shopRequest = $shopRequest;
        $this->logger = $logger;
    }

    /**
     * Return the MultiSafepay account ID from Mirakl using the S20 Mirakl endpoint
     *
     * @param int $miraklShopId
     * @return string
     */
    public function getSellerMultiSafepayAccountId(int $miraklShopId): string
    {
        $shopRequest = $this->shopRequest->getById($miraklShopId);
        $miraklFrontApiClient = $this->miraklFrontApiClient->get();
        $shopInfo = $miraklFrontApiClient->getShops($shopRequest);

        if ($shopInfo->isEmpty()) {
            $this->logger->logCronProcessInfo('Shop not found in Mirakl: ', ['shop_id' => $miraklShopId]);
            return '';
        }

        $multiSafepayAccountId = (string) $this->getMultiSafepayAccountIdFromAdditionalValues(
            $shopInfo->first()->getAdditionalFieldValues()->getItems()
        );

        if (empty($multiSafepayAccountId)) {
            $this->logger->logCronProcessInfo(
                'MultiSafepay account ID not found in Mirakl: ',
                ['shop_id' => $miraklShopId]
            );

            return '';
        }

        return $multiSafepayAccountId;
    }

    /**
     * Extract the MultiSafepay merchant id from the Mirakl API response
     *
     * @param array $additionalFields
     * @return void
     */
    private function getMultiSafepayAccountIdFromAdditionalValues(array $additionalFields): string
    {
        foreach ($additionalFields as $field) {
            $data = $field->getData();
            if ($data['code'] === 'multisafepay-merchant-id') {
                return $data['value'];
            }
        }
        return '';
    }
}
