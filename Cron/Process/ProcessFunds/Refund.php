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
use MultiSafepay\Api\Transactions\OrderRequest\Arguments\Description;
use MultiSafepay\Api\Transactions\RefundRequest;
use MultiSafepay\Mirakl\Factory\AffiliatesSdkFactory;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Util\PayOutOrderLineUtil;
use MultiSafepay\ValueObject\Money;
use Psr\Http\Client\ClientExceptionInterface;

class Refund
{
    /**
     * @var AffiliatesSdkFactory;
     */
    private $affiliatesSdkFactory;

    /**
     * @var PayOutOrderLineUtil
     */
    private $payOutOrderLineUtil;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param AffiliatesSdkFactory $affiliatesSdkFactory
     * @param PayOutOrderLineUtil $payOutOrderLineUtil
     * @param Logger $logger
     */
    public function __construct(
        AffiliatesSdkFactory $affiliatesSdkFactory,
        PayOutOrderLineUtil $payOutOrderLineUtil,
        Logger $logger
    ) {
        $this->affiliatesSdkFactory = $affiliatesSdkFactory;
        $this->payOutOrderLineUtil = $payOutOrderLineUtil;
        $this->logger = $logger;
    }

    /**
     * Process the refund to the end customer, when an item is rejected
     *
     * @param array $data
     * @return void
     * @throws AlreadyExistsException
     * @throws Exception
     */
    public function execute(array $data): void
    {
        if (!$this->shouldRefund($data['refundData'])) {
            return;
        }

        $orderId = $data['payOutData']['order_commercial_id'];
        $miraklOrderId = $data['payOutData']['order_id'];

        $amount = (float) array_sum($data['refundData']['amount']);
        $money = new Money(($amount * 100), $data['payOutData']['currency_iso_code']);

        $refundRequest = (new RefundRequest())->addMoney($money)
            ->addDescription(
                Description::fromText(__('Refund for Mirakl Order ID: ') . $miraklOrderId)
            )->addData([
                'refund_order_id' => $miraklOrderId
            ]);

        $transactionManager = $this->affiliatesSdkFactory->create(
            (int)$data['payOutData']['store_id']
        )->getTransactionManager();

        try {
            $transaction = $transactionManager->get($orderId);
            $transactionManager->refund($transaction, $refundRequest, $orderId);
        } catch (ClientExceptionInterface $clientException) {
            $this->logger->logExceptionForOrder(
                $data['payOutData']['order_commercial_id'],
                $clientException
            );
        }

        foreach ($data['refundData']['payout_order_line_ids'] as $order_line_id_processed) {
            $this->payOutOrderLineUtil->setOrderLineAsProcessed((int)$order_line_id_processed);
        }
    }

    /**
     * Check whether to do a refund
     *
     * @param array $refundData
     * @return bool
     */
    private function shouldRefund(array $refundData): bool
    {
        $amount = $refundData['amount'] ?? 0;

        if (!$amount) {
            return false;
        }

        // Amount that needs to be refunded.
        if (array_sum($refundData['amount']) > 0) {
            return true;
        }

        return false;
    }

    /**
     * Build the data that is required to process a refund
     *
     * @param array $orderLine
     * @param array $data
     * @return array
     */
    public function buildData(array $orderLine, array $data): array
    {
        $data['amount'][] = $orderLine[PayOutOrderLine::TOTAL_PRICE_INCLUDING_TAXES];
        $data['payout_order_line_ids'][] = $orderLine[PayOutOrderLine::PAYOUT_ORDER_LINE_ID];

        return $data;
    }
}
