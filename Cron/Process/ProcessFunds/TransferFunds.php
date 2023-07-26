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

namespace MultiSafepay\Mirakl\Cron\Process\ProcessFunds;

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\FundRequest\FundRequest;
use MultiSafepay\Mirakl\Config\Config;
use MultiSafepay\Mirakl\Factory\AffiliatesSdkFactory;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Util\AccountUtil;
use MultiSafepay\Mirakl\Util\PayOutOrderLineUtil;
use MultiSafepay\ValueObject\Money;
use Psr\Http\Client\ClientExceptionInterface;

class TransferFunds
{
    /**
     * @var AffiliatesSdkFactory;
     */
    private $affiliatesSdkFactory;

    /**
     * @var AccountUtil
     */
    private $accountUtil;

    /**
     * @var PayOutOrderLineUtil
     */
    private $payOutOrderLineUtil;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param AffiliatesSdkFactory $affiliatesSdkFactory
     * @param AccountUtil $accountUtil
     * @param PayOutOrderLineUtil $payOutOrderLineUtil
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        AffiliatesSdkFactory $affiliatesSdkFactory,
        AccountUtil $accountUtil,
        PayOutOrderLineUtil $payOutOrderLineUtil,
        Config $config,
        Logger $logger
    ) {
        $this->affiliatesSdkFactory = $affiliatesSdkFactory;
        $this->accountUtil = $accountUtil;
        $this->payOutOrderLineUtil = $payOutOrderLineUtil;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Transfer funds to the seller, operator, and set the order lines as processed
     *
     * @param array $data
     * @return void
     * @throws AlreadyExistsException
     * @throws ClientExceptionInterface
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(array $data): void
    {
        if (empty($data['transferFundsData'])) {
            return;
        }

        if (array_sum($data['transferFundsData']['seller_amount'] ?? [])) {
            $this->transferFundsToSeller($data);
        }

        if (array_sum($data['transferFundsData']['operator_amount'] ?? [])) {
            $this->transferFundsToOperator($data);
        }

        foreach ($data['transferFundsData']['payout_order_line_ids'] as $order_line_id_processed) {
            $this->payOutOrderLineUtil->setOrderLineAsProcessed((int)$order_line_id_processed);
        }
    }

    /**
     * Transfer funds to the operator
     *
     * @param array $data
     * @return void
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    private function transferFundsToOperator(array $data): void
    {
        $amount = array_sum($data['transferFundsData']['operator_amount']);
        $accountId = $this->config->getCollectingAccountId();

        $this->transferFunds($data, $amount, $accountId);
    }

    /**
     * Transfer funds to the seller
     *
     * @param array $data
     * @return void
     * @throws ClientExceptionInterface
     * @throws NoSuchEntityException
     */
    private function transferFundsToSeller(array $data): void
    {
        $amount = array_sum($data['transferFundsData']['seller_amount']);
        $accountId = $this->accountUtil->getSellerMultiSafepayAccountId($data['payOutData']['shop_id']);

        $this->transferFunds($data, $amount, $accountId);
    }

    /**
     * Transfer funds to the operator or to the seller
     *
     * @param array $data
     * @param float $amount
     * @param int $accountId
     * @return void
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    private function transferFunds(array $data, float $amount, int $accountId): void
    {
        $affiliatesSdk = $this->affiliatesSdkFactory->createAffiliatesSdk(
            (int)$data['payOutData']['store_id']
        );

        $fundAmount = new Money(
            $amount * 100,
            $data['payOutData']['currency_iso_code']
        );

        $fundRequest = new FundRequest();
        $fundRequest->addDescriptionText(
            __('Order ID: ') . $data['payOutData']['order_id']
        )
            ->addOrderId($data['payOutData']['order_id'])
            ->addMoney($fundAmount);

        $affiliatesManager = $affiliatesSdk->getAffiliatesManager();

        $affiliatesManager->fund($accountId, $fundRequest);
    }

    /**
     * Build the data that is required to transfer the funds
     *
     * @param array $orderLine
     * @param array $transferFundsData
     * @return array
     */
    public function buildData(array $orderLine, array $transferFundsData): array
    {
        $transferFundsData['seller_amount'][] = $orderLine[PayOutOrderLine::SELLER_AMOUNT];
        $transferFundsData['operator_amount'][] = $orderLine[PayOutOrderLine::OPERATOR_AMOUNT];
        $transferFundsData['payout_order_line_ids'][] = $orderLine[PayOutOrderLine::PAYOUT_ORDER_LINE_ID];

        return $transferFundsData;
    }
}
