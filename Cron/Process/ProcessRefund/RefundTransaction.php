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

use DateTime;
use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Api\Transactions\RefundRequest;
use MultiSafepay\ConnectCore\Factory\SdkFactory;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Exception\ApiException;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\ValueObject\CartItem;
use MultiSafepay\ValueObject\Money;
use Psr\Http\Client\ClientExceptionInterface;

class RefundTransaction
{
    /**
     * @var SdkFactory
     */
    private $sdkFactory;

    /**
     * @var OrderUtil
     */
    private $orderUtil;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param SdkFactory $sdkFactory
     * @param OrderUtil $orderUtil
     * @param Logger $logger
     */
    public function __construct(
        SdkFactory $sdkFactory,
        OrderUtil $orderUtil,
        Logger $logger
    ) {
        $this->sdkFactory = $sdkFactory;
        $this->orderUtil = $orderUtil;
        $this->logger = $logger;
    }

    /**
     * Refund the transaction amount
     *
     * @param string $orderCommercialId
     * @param float $amount
     * @param string $isoCode
     *
     * @return array
     *
     * @throws ClientExceptionInterface
     * @throws NoSuchEntityException
     * @throws Exception
     */
    public function execute(string $orderCommercialId, float $amount, string $isoCode): array
    {
        $storeId = $this->orderUtil->getOrderByIncrementId($orderCommercialId)->getStoreId();

        $transactionManager = $this->sdkFactory->create((int)$storeId)->getTransactionManager();
        $transaction = $transactionManager->get($orderCommercialId);

        if ($transaction->requiresShoppingCart()) {
            $refundRequest = $transactionManager->createRefundRequest($transaction);

            $item = new CartItem();

            $item->addDescription('Refund for order: ' . $orderCommercialId);
            $item->addName('Mirakl Refund');
            $item->addMerchantItemId('adjustment-' . (new DateTime())->getTimestamp());
            $item->addQuantity(1);
            $item->addTaxRate(0);
            $item->addUnitPrice((new Money($amount * 100, $isoCode))->negative());

            $refundRequest->getCheckoutData()->addItem($item);
            $refund = $transactionManager->refund($transaction, $refundRequest);

            $this->logger->logCronProcessInfo('Shopping cart refund done', $refundRequest->getData());

            return $refund->getResponseData();
        }

        $refundRequest = new RefundRequest();
        $refundRequest->addMoney(new Money($amount * 100, $isoCode));
        $refundRequest->addDescriptionText('Refund for order: ' . $orderCommercialId);
        $refund = $transactionManager->refund($transaction, $refundRequest);

        $this->logger->logCronProcessInfo('Amount refund done', $refundRequest->getData());

        return $refund->getResponseData();
    }
}
