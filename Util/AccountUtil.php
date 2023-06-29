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

use Magento\Framework\Exception\NoSuchEntityException;
use Mirakl\MMP\FrontOperator\Domain\Collection\Shop\ShopCollection;
use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient as MiraklFrontApiClient;
use MultiSafepay\Mirakl\Api\Mirakl\Request\ShopRequest;

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
     * @param MiraklFrontApiClient $miraklFrontApiClient
     * @param ShopRequest $shopRequest
     */
    public function __construct(
        MiraklFrontApiClient $miraklFrontApiClient,
        ShopRequest $shopRequest
    ) {
        $this->miraklFrontApiClient = $miraklFrontApiClient;
        $this->shopRequest = $shopRequest;
    }

    /**
     * @param int $miraklShopId
     * @return int
     * @throws NoSuchEntityException
     */
    public function getSellerMultiSafepayAccountId(int $miraklShopId): int
    {
        $shopInfo = [];

        try {
            $shopRequest = $this->shopRequest->getById($miraklShopId);
            $miraklFrontApiClient = $this->miraklFrontApiClient->get();
            /** @var ShopCollection $shopInfo */
            $shopInfo = $miraklFrontApiClient->getShops($shopRequest);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__('Requested order doesn\'t exist'));
        }

        return (int)$this->getMultiSafepayAccountIdFromAdditionalValues(
            $shopInfo->first()->getAdditionalFieldValues()->getItems()
        );
    }

    /**
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
