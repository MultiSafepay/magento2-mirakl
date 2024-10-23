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
use MultiSafepay\Mirakl\Exception\CronProcessException;
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
     * ConfirmPayment constructor
     *
     * @param OrderUtil $orderUtil
     * @param SdkFactory $sdkFactory
     */
    public function __construct(
        OrderUtil $orderUtil,
        SdkFactory $sdkFactory
    ) {
        $this->orderUtil = $orderUtil;
        $this->sdkFactory = $sdkFactory;
    }

    /**
     * The confirm payment process which checks for the transaction status and order amount
     *
     * @param array $orderDebitData
     * @return void
     * @throws ClientExceptionInterface
     * @throws CronProcessException
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(array $orderDebitData): void
    {
        // Getting the Magento Order
        $order = $this->orderUtil->getOrderByIncrementId($orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID]);

        // Getting an instance of the TransactionManager object
        $transactionManager = $this->sdkFactory->create((int)$order->getStoreId())->getTransactionManager();

        // Retrieve transaction details using MultiSafepay API
        $transaction = $transactionManager->get($orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID]);

        // Check if transaction amount is equal or higher than the one received in the order debit data
        if (!($transaction->getAmount() / 100) >= (float)$orderDebitData[CustomerDebit::AMOUNT]) {
            throw (new CronProcessException('Order amount is higher than transaction amount'));
        }

        // Check if transaction is partial_refunded
        if ($transaction->getStatus() === Transaction::PARTIAL_REFUNDED) {
            $amount = ($transaction->getAmount() / 100) - ($transaction->getAmountRefunded() / 100);

            if ((float)$orderDebitData[CustomerDebit::AMOUNT] > $amount) {
                throw (new CronProcessException('Order amount is higher than current transaction amount'));
            }

            return;
        }

        // Check if transaction is completed
        if ($transaction->getStatus() !== Transaction::COMPLETED &&
            $transaction->getStatus() !== Transaction::SHIPPED) {
            throw (new CronProcessException('Transaction status is not completed'));
        }
    }
}
