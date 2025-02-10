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

namespace MultiSafepay\Mirakl\Cron\Process\ProcessRefund;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\Request\ChargeRequest;
use MultiSafepay\Mirakl\Config\Config;
use MultiSafepay\Mirakl\Factory\AffiliatesSdkFactory;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Util\AccountUtil;
use MultiSafepay\ValueObject\Money;
use Psr\Http\Client\ClientExceptionInterface;

class ChargeAccounts
{
    /**
     * @var AffiliatesSdkFactory
     */
    private $affiliatesSdkFactory;

    /**
     * @var OrderUtil
     */
    private $orderUtil;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var AccountUtil
     */
    private $accountUtil;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param AccountUtil $accountUtil
     * @param AffiliatesSdkFactory $affiliatesSdkFactory
     * @param Config $config
     * @param OrderUtil $orderUtil
     * @param Logger $logger
     */
    public function __construct(
        AccountUtil $accountUtil,
        AffiliatesSdkFactory $affiliatesSdkFactory,
        Config $config,
        OrderUtil $orderUtil,
        Logger $logger
    ) {
        $this->accountUtil = $accountUtil;
        $this->affiliatesSdkFactory = $affiliatesSdkFactory;
        $this->config = $config;
        $this->orderUtil = $orderUtil;
        $this->logger = $logger;
    }

    /**
     * Charge the MultiSafepay commission and seller accounts
     *
     * @param array $orderRefundData
     * @param array $chargeBack
     * @return void
     * @throws ClientExceptionInterface
     * @throws NoSuchEntityException
     */
    public function execute(array $orderRefundData, array $chargeBack)
    {
        $storeId = (int)$this->orderUtil->getOrderByIncrementId(
            $orderRefundData[CustomerRefund::ORDER_COMMERCIAL_ID]
        )->getStoreId();

        // Charge seller
        $sellerAccountId = $this->accountUtil->getSellerMultiSafepayAccountId(
            (int)$orderRefundData[CustomerRefund::SHOP_ID]
        );
        $this->charge(
            $orderRefundData,
            $chargeBack[PrepareRefundData::SELLER_CHARGEBACK_AMOUNT],
            $sellerAccountId,
            $storeId
        );

        $this->logger->logCronProcessInfo('Seller charged', [$orderRefundData, $chargeBack]);

        // Charge commission
        $commissionAccountId = $this->config->getCollectingAccountId($storeId);
        $this->charge(
            $orderRefundData,
            $chargeBack[PrepareRefundData::COMMISSION_CHARGEBACK_AMOUNT],
            $commissionAccountId,
            $storeId
        );

        $this->logger->logCronProcessInfo('Commission charged', [$orderRefundData, $chargeBack]);
    }

    /**
     * Send the charge request
     *
     * @param array $orderRefundData
     * @param float $amount
     * @param string $accountId
     * @param int $storeId
     * @return void
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    private function charge(array $orderRefundData, float $amount, string $accountId, int $storeId)
    {
        $currencyIsoCode = $orderRefundData[CustomerRefund::CURRENCY_ISO_CODE];
        $orderId = $orderRefundData[CustomerRefund::ORDER_ID];

        $chargeBackAmount = new Money($amount * 100, $currencyIsoCode);

        $chargeRequest = new ChargeRequest();

        $chargeRequest->addDescriptionText(__('Order ID: ') . $orderId)
            ->addOrderId($orderId)
            ->addMoney($chargeBackAmount);

        $affiliatesSdk = $this->affiliatesSdkFactory->createAffiliatesSdk($storeId);
        $affiliatesManager = $affiliatesSdk->getAffiliatesManager();

        $affiliatesManager->charge($accountId, $chargeRequest);
    }
}
