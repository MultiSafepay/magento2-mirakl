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
use MultiSafepay\Api\Transactions\Transaction;
use MultiSafepay\ConnectCore\Factory\SdkFactory;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use Psr\Http\Client\ClientExceptionInterface;

class ConfirmPayment implements ProcessInterface
{
    /**
     * @var OrderUtil;
     */
    private $orderUtil;

    /**
     * @var SdkFactory;
     */
    private $sdkFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * ConfirmPayment constructor
     *
     * @param OrderUtil $orderUtil
     * @param SdkFactory $sdkFactory
     * @param Logger $logger
     */
    public function __construct(
        OrderUtil $orderUtil,
        SdkFactory $sdkFactory,
        Logger $logger
    ) {
        $this->orderUtil = $orderUtil;
        $this->sdkFactory = $sdkFactory;
        $this->logger = $logger;
    }

    /**
     * The confirm payment process which checks for the transaction status and order amount
     *
     * @param array $orderDebitData
     * @return array|bool[]
     */
    public function execute(array $orderDebitData): array
    {
        // Getting the Magento Order
        try {
            $order = $this->orderUtil->getOrderByIncrementId(
                $orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID]
            );
        } catch (NoSuchEntityException $noSuchEntityException) {
            return [
                ProcessInterface::SUCCESS_PARAMETER => false,
                ProcessInterface::MESSAGE_PARAMETER => $noSuchEntityException->getMessage()
            ];
        }

        // Getting an instance of the TransactionManager object
        try {
            $transactionManager = $this->sdkFactory->create(
                (int)$order->getStoreId()
            )->getTransactionManager();
        } catch (Exception $exception) {
            return [
                ProcessInterface::SUCCESS_PARAMETER => false,
                ProcessInterface::MESSAGE_PARAMETER => $exception->getMessage()
            ];
        }

        // Retrieve transaction details using MultiSafepay API
        try {
            $transaction = $transactionManager->get($orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID]);
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

        // Check if transaction amount is equal or higher than the one received in the order debit data
        if (!($transaction->getAmount() / 100) >= (float)$orderDebitData[CustomerDebit::AMOUNT]) {
            return [
                ProcessInterface::SUCCESS_PARAMETER => false,
                ProcessInterface::MESSAGE_PARAMETER => 'Order amount is higher than transaction amount'
            ];
        }

        // Check if transaction is completed
        if ($transaction->getStatus() !== Transaction::COMPLETED) {
            return [
                ProcessInterface::SUCCESS_PARAMETER => false,
                ProcessInterface::MESSAGE_PARAMETER => 'Transaction status is not completed'
            ];
        }

        return [ProcessInterface::SUCCESS_PARAMETER => true];
    }
}
