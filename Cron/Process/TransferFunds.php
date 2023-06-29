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

namespace MultiSafepay\Mirakl\Cron\Process;

use Mirakl\MMP\Common\Domain\Order\OrderState;
use MultiSafepay\Api\Transactions\OrderRequest\Arguments\Description;
use MultiSafepay\Api\Transactions\RefundRequest;
use MultiSafepay\Exception\ApiException;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\AffiliatesManager;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\FundRequest\FundRequest;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\AffiliatesSdk;
use MultiSafepay\Mirakl\Config\Config;
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Factory\AffiliatesSdkFactory;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\PayOut;
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut\CollectionFactory as PayOutCollectionFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine as PayOutOrderLineResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine\CollectionFactory as PayOutOrderLineCollectionFactory;
use MultiSafepay\Mirakl\Util\AccountUtil;
use MultiSafepay\ValueObject\Money;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TransferFunds implements ProcessInterface
{
    /**
     * @var PayOutCollectionFactory
     */
    private $payOutCollectionFactory;

    /**
     * @var PayOutOrderLineResourceModel
     */
    private $payOutOrderLineResourceModel;

    /**
     * @var PayOutOrderLineCollectionFactory
     */
    private $payOutOrderLineCollectionFactory;

    /**
     * @var AffiliatesSdkFactory;
     */
    private $affiliatesSdkFactory;

    /**
     * @var AccountUtil
     */
    private $accountUtil;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param PayOutCollectionFactory $payOutCollectionFactory
     * @param PayOutOrderLineResourceModel $payOutOrderLineResourceModel
     * @param PayOutOrderLineCollectionFactory $payOutOrderLineCollectionFactory
     * @param AffiliatesSdkFactory $affiliatesSdkFactory
     * @param AccountUtil $accountUtil
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        PayOutCollectionFactory $payOutCollectionFactory,
        PayOutOrderLineResourceModel $payOutOrderLineResourceModel,
        PayOutOrderLineCollectionFactory $payOutOrderLineCollectionFactory,
        AffiliatesSdkFactory $affiliatesSdkFactory,
        AccountUtil $accountUtil,
        Config $config,
        Logger $logger
    ) {
        $this->payOutCollectionFactory = $payOutCollectionFactory;
        $this->payOutOrderLineResourceModel = $payOutOrderLineResourceModel;
        $this->payOutOrderLineCollectionFactory = $payOutOrderLineCollectionFactory;
        $this->affiliatesSdkFactory = $affiliatesSdkFactory;
        $this->accountUtil = $accountUtil;
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param array $orderDebitData
     * @return true[]
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(array $orderDebitData): array
    {
        /** @var \MultiSafepay\Mirakl\Model\ResourceModel\PayOut\Collection $payOutCollection */
        $payOutCollection = $this->payOutCollectionFactory->create();
        $payOutCollection->withOrderLines();
        $items = $payOutCollection->getItemsByColumnValue(PayOut::MIRAKL_ORDER_ID, $orderDebitData['order_id']);

        $refundData = [];
        $transferFundsData = [];

        /** @var PayOut $payOutItem */
        foreach ($items as $payOutItem) {
            /** @var PayOutOrderLine[] $orderLines */
            $orderLines = $payOutItem->getOrderLines();
            foreach ($orderLines as $orderLine) {
                if ($orderLine[PayOutOrderLine::MIRAKL_ORDER_STATUS] === OrderState::REFUSED &&
                    $orderLine[PayOutOrderLine::STATUS] === '1'
                ) {
                    $refundData['order_id'] = $payOutItem->getMiraklOrderId();
                    $refundData['store_id'] = $payOutItem->getMagentoShopId();
                    $refundData['amount'][] = $orderLine[PayOutOrderLine::TOTAL_PRICE_INCLUDING_TAXES];
                    $refundData['payout_order_line_ids'][] = $orderLine[PayOutOrderLine::PAYOUT_ORDER_LINE_ID];
                }

                if ($orderLine[PayOutOrderLine::MIRAKL_ORDER_STATUS] === OrderState::WAITING_DEBIT_PAYMENT &&
                    $orderLine[PayOutOrderLine::STATUS] === '1'
                ) {
                    $transferFundsData['store_id'] = $payOutItem->getMagentoShopId();
                    $transferFundsData['shop_id']  = $payOutItem->getMiraklShopId();
                    $transferFundsData['order_id'] = $payOutItem->getMiraklOrderId();
                    $transferFundsData['seller_amount'][] = $orderLine[PayOutOrderLine::SELLER_AMOUNT];
                    $transferFundsData['operator_amount'][] = $orderLine[PayOutOrderLine::OPERATOR_AMOUNT];
                    $transferFundsData['payout_order_line_ids'][] = $orderLine[PayOutOrderLine::PAYOUT_ORDER_LINE_ID];
                }
            }
        }

        // Amount that needs to be refunded.
        if (!empty($refundData['amount']) && array_sum($refundData['amount']) > 0) {
            $this->processCustomerRefund($orderDebitData, $refundData);
        }

        // Amount that needs to be transfer to the seller.
        if (!empty($transferFundsData) &&
            (array_sum($transferFundsData['seller_amount']) > 0 || array_sum($transferFundsData['operator_amount']) > 0)
        ) {
            try {
                $this->processTransferFundsData($transferFundsData);
            } catch (ClientExceptionInterface $clientException) {
                return [
                    ProcessInterface::SUCCESS_PARAMETER => false,
                    ProcessInterface::MESSAGE_PARAMETER => $clientException->getMessage()
                ];
            } catch (ApiException $apiException) {
                return [
                    ProcessInterface::SUCCESS_PARAMETER => false,
                    ProcessInterface::MESSAGE_PARAMETER => $apiException->getMessage()
                ];
            }
        }

        return [ProcessInterface::SUCCESS_PARAMETER => true];
    }

    /**
     * @throws \Exception
     */
    private function processCustomerRefund(array $orderDebitData, array $refundData)
    {
        // We need to check somehow if the shopping cart is required to process this refund or not.
        $orderId = $orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID];
        $amount = (float) array_sum($refundData['amount']);
        $money = new Money(($amount * 100), $orderDebitData[CustomerDebit::CURRENCY_ISO_CODE]);

        $refundRequest = (new RefundRequest())->addMoney($money)
            ->addDescription(
                Description::fromText('Refund for Mirakl order: ' . $orderId)
            )->addData([
                    'refund_order_id' => $orderDebitData[CustomerDebit::ORDER_ID]
            ]);

        $transactionManager = $this->affiliatesSdkFactory->create(
            (int)$refundData['store_id']
        )->getTransactionManager();

        try {
            $transaction = $transactionManager->get($orderId);
        } catch (ClientExceptionInterface $clientException) {
            $this->logger->logExceptionForOrder(
                $orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID],
                $clientException
            );
        }

        try {
            $transactionManager->refund($transaction, $refundRequest, $orderId);
        } catch (ClientExceptionInterface $clientException) {
            $this->logger->logExceptionForOrder(
                $orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID],
                $clientException
            );
        }

        foreach ($refundData['payout_order_line_ids'] as $order_line_id_processed) {
            $this->setOrderLineAsProcessed($order_line_id_processed);
        }
    }

    /**
     * @param array $transferFundsData
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @throws \Exception
     * @throws ClientExceptionInterface
     */
    private function processTransferFundsData(array $transferFundsData)
    {
        // Check for seller amount
        if (array_sum($transferFundsData['seller_amount']) > 0) {
            $sellerAmount = array_sum($transferFundsData['seller_amount']);
            $this->transferFundsToSeller($transferFundsData, $sellerAmount);
        }

        // Check for operator amount.
        if (array_sum($transferFundsData['seller_amount']) > 0) {
            $operatorAmount = array_sum($transferFundsData['operator_amount']);
            $this->transferFundsToOperator($transferFundsData, $operatorAmount);
        }

        foreach ($transferFundsData['payout_order_line_ids'] as $order_line_id_processed) {
            $this->setOrderLineAsProcessed($order_line_id_processed);
        }
    }

    /**
     * @param $transferFundsData
     * @param $accoundId
     * @return void
     * @throws \Exception
     * @throws ClientExceptionInterface
     */
    public function transferFundsToOperator($transferFundsData, $operatorAmount)
    {
        /** @var AffiliatesSdk $affiliatesManager */
        $affiliatesSdk = $this->affiliatesSdkFactory->createAffiliatesSdk(
            (int)$transferFundsData['store_id']
        );

        $fundRequest = new FundRequest();
        $fundRequest->addDescriptionText('Fund Description' . $transferFundsData['order_id'])
            ->addOrderId($transferFundsData['order_id'])
            ->addMoney(new Money((float)($operatorAmount * 100), 'EUR'));

        $accountId = (int)$this->config->getCollectingAccountId();

        /** @var AffiliatesManager $affiliatesManager */
        $affiliatesManager = $affiliatesSdk->getAffiliatesManager();
        try {
            $affiliatesManager->fund($accountId, $fundRequest);
        } catch (ApiException $apiException) {
            $this->logger->error($apiException->getMessage());
        }
    }

    /**
     * @param $transferFundsData
     * @param $accoundId
     * @return void
     * @throws \Exception
     * @throws ClientExceptionInterface
     */
    public function transferFundsToSeller($transferFundsData, $sellerAmount)
    {
        /** @var AffiliatesSdk $affiliatesManager */
        $affiliatesSdk = $this->affiliatesSdkFactory->createAffiliatesSdk(
            (int)$transferFundsData['store_id']
        );

        $fundRequest = new FundRequest();
        $fundRequest->addDescriptionText('Fund Description' . $transferFundsData['order_id'])
            ->addOrderId($transferFundsData['order_id'])
            ->addMoney(new Money((float)($sellerAmount * 100), 'EUR'));

        $accountId = $this->accountUtil->getSellerMultiSafepayAccountId($transferFundsData['shop_id']);

        /** @var AffiliatesManager $affiliatesManager */
        $affiliatesManager = $affiliatesSdk->getAffiliatesManager();
        try {
            $affiliatesManager->fund($accountId, $fundRequest);
        } catch (ApiException $apiException) {
            $this->logger->error($apiException->getMessage());
        }
    }

    /**
     * @param $order_line_id
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     */
    private function setOrderLineAsProcessed($order_line_id): void
    {
        /** @var \MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine\Collection $payOutOrderLineCollectionFactory */
        $payOutOrderLineCollectionFactory = $this->payOutOrderLineCollectionFactory->create();

        /** @var PayOutOrderLine $payOutOrderLine */
        $payOutOrderLine = $payOutOrderLineCollectionFactory->getItemById($order_line_id);
        $payOutOrderLine->setStatus(0);
        $this->payOutOrderLineResourceModel->save($payOutOrderLine);
    }
}
