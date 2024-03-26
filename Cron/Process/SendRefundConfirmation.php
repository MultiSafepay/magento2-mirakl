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

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirakl\MMP\FrontOperator\Request\Payment\Refund\ConfirmOrderRefundRequest;
use MultiSafepay\Api\Transactions\TransactionResponse\RelatedTransaction;
use MultiSafepay\ConnectCore\Factory\SdkFactory;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient as MiraklFrontApiClient;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use Psr\Http\Client\ClientExceptionInterface;

class SendRefundConfirmation
{
    /**
     * @var MiraklFrontApiClient
     */
    private $miraklFrontApiClient;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var OrderUtil
     */
    private $orderUtil;

    /**
     * @var SdkFactory
     */
    private $sdkFactory;

    /**
     * @param MiraklFrontApiClient $miraklFrontApiClient
     * @param Logger $logger
     * @param OrderUtil $orderUtil
     * @param SdkFactory $sdkFactory
     */
    public function __construct(
        MiraklFrontApiClient $miraklFrontApiClient,
        Logger $logger,
        OrderUtil $orderUtil,
        SdkFactory $sdkFactory
    ) {
        $this->miraklFrontApiClient = $miraklFrontApiClient;
        $this->logger = $logger;
        $this->orderUtil = $orderUtil;
        $this->sdkFactory = $sdkFactory;
    }

    /**
     * Send the refund confirmation request to Mirakl
     *
     * @param array $orderRefundData
     * @param array $refundResponse
     *
     * @return void
     *
     * @throws NoSuchEntityException
     * @throws ClientExceptionInterface
     * @throws Exception
     */
    public function execute(array $orderRefundData, array $refundResponse)
    {
        $miraklFrontApiClient = $this->miraklFrontApiClient->get();

        $orderCommercialId = $orderRefundData[CustomerRefund::ORDER_COMMERCIAL_ID];
        $storeId = $this->orderUtil->getOrderByIncrementId($orderCommercialId)->getStoreId();
        $transactionManager = $this->sdkFactory->create((int)$storeId)->getTransactionManager();
        $transaction = $transactionManager->get($orderCommercialId);

        foreach ($transaction->getRelatedTransactions() as $relatedTransaction) {
            if ($relatedTransaction->getTransactionId() === (string)$refundResponse['refund_id']) {
                $transactionDate = $relatedTransaction->getCreated();
            }
        }

        foreach ($orderRefundData['order_lines'] as $orderLine) {
            $input = ['refunds' => [
                'amount' => (string)$orderLine['order_line_amount'],
                'refund_id' =>  (string)$orderLine['order_line_refund_id'],
                'payment_status' => 'OK',
            ]];

            if (isset($refundResponse['refund_id'])) {
                $input['refunds']['transaction_number'] = (string)$refundResponse['refund_id'];
            }

            if (isset($transactionDate)) {
                $input['refunds']['transaction_date'] = $transactionDate;
            }

            $request = new ConfirmOrderRefundRequest($input);

            $miraklFrontApiClient->confirmOrderRefund($request);
            $this->logger->logCronProcessInfo(
                'confirmOrderRefund Request send to Mirakl',
                $request->toArray()
            );
        }
    }
}
