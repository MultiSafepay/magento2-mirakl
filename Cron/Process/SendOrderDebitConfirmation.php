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
use MultiSafepay\Api\Transactions\TransactionResponse;
use MultiSafepay\ConnectCore\Factory\SdkFactory;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Exception\ApiException;
use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient as MiraklFrontApiClient;
use MultiSafepay\Mirakl\Api\Mirakl\Request\ConfirmOrderDebitRequest;
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use Psr\Http\Client\ClientExceptionInterface;

class SendOrderDebitConfirmation implements ProcessInterface
{
    /**
     * @var MiraklFrontApiClient
     */
    private $miraklFrontApiClient;

    /**
     * @var OrderUtil;
     */
    private $orderUtil;

    /**
     * @var SdkFactory;
     */
    private $sdkFactory;

    /**
     * @var ConfirmOrderDebitRequest
     */
    private $confirmOrderDebitRequest;

    /**
     * @param MiraklFrontApiClient $miraklFrontApiClient
     * @param OrderUtil $orderUtil
     * @param SdkFactory $sdkFactory
     * @param ConfirmOrderDebitRequest $confirmOrderDebitRequest
     */
    public function __construct(
        MiraklFrontApiClient $miraklFrontApiClient,
        OrderUtil $orderUtil,
        SdkFactory $sdkFactory,
        ConfirmOrderDebitRequest $confirmOrderDebitRequest
    ) {
        $this->miraklFrontApiClient = $miraklFrontApiClient;
        $this->orderUtil = $orderUtil;
        $this->sdkFactory = $sdkFactory;
        $this->confirmOrderDebitRequest = $confirmOrderDebitRequest;
    }

    /**
     * Send a request to Mirakl API to confirm the order debit
     *
     * @param array $orderDebitData
     * @return void
     * @throws ClientExceptionInterface
     * @throws NoSuchEntityException
     */
    public function execute(array $orderDebitData): void
    {
        $transaction = $this->getMultiSafepayTransaction($orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID]);

        $miraklFrontApiClient = $this->miraklFrontApiClient->get();
        $miraklConfirmOrderDebitRequest = $this->confirmOrderDebitRequest->get($orderDebitData, $transaction);
        $miraklFrontApiClient->confirmOrderDebit($miraklConfirmOrderDebitRequest);
    }

    /**
     * Return the MultiSafepay transaction
     *
     * @param string $orderId
     * @return TransactionResponse
     * @throws ClientExceptionInterface
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws ApiException
     */
    private function getMultiSafepayTransaction(string $orderId): TransactionResponse
    {
        $order = $this->orderUtil->getOrderByIncrementId($orderId);
        $transactionManager = $this->sdkFactory->create((int)$order->getStoreId())->getTransactionManager();

        return $transactionManager->get($orderId);
    }
}
