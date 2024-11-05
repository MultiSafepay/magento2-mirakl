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

namespace MultiSafepay\Mirakl\Plugin\ConnectCore\Service\Transaction;

use Exception;
use Magento\Sales\Api\Data\OrderInterface;
use MultiSafepay\Api\Transactions\Transaction;
use MultiSafepay\ConnectCore\Service\Transaction\StatusOperationManager;
use MultiSafepay\Mirakl\Service\RegisterCustomerDebit;
use MultiSafepay\Mirakl\Util\BuyNowPayLaterUtil;
use MultiSafepay\Mirakl\Util\MiraklOrderUtil;
use MultiSafepay\Mirakl\Util\ShoppingCartUtil;

class StatusOperationManagerPlugin
{
    public const SUFFIX_BNPL_MIRAKL_ORDER = '-A';

    /**
     * @var ShoppingCartUtil
     */
    private $shoppingCartUtil;

    /**
     * @var RegisterCustomerDebit
     */
    private $registerCustomerDebit;

    /**
     * @var MiraklOrderUtil
     */
    private $miraklOrderUtil;

    /**
     * @param ShoppingCartUtil $shoppingCartUtil
     * @param RegisterCustomerDebit $registerCustomerDebit
     * @param MiraklOrderUtil $miraklOrderUtil
     */
    public function __construct(
        ShoppingCartUtil $shoppingCartUtil,
        RegisterCustomerDebit $registerCustomerDebit,
        MiraklOrderUtil $miraklOrderUtil
    ) {
        $this->shoppingCartUtil = $shoppingCartUtil;
        $this->registerCustomerDebit = $registerCustomerDebit;
        $this->miraklOrderUtil = $miraklOrderUtil;
    }

    /**
     * Check if the completed notification belongs to an order which contains products from Mirakl
     * and if that's the case, check if is BNPL order, and if the financial_status is completed.
     *
     * If all the conditions are met, then it will register the order details required to transfer the funds to the
     * affiliates and the operator
     *
     * @param StatusOperationManager $statusOperationManager
     * @param array $result
     * @param OrderInterface $order
     * @param array $transaction
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws Exception
     */
    public function afterProcessStatusOperation(
        StatusOperationManager $statusOperationManager,
        array $result,
        OrderInterface $order,
        array $transaction
    ): array {

        // If the order doesn't contain products from Mirakl sellers, we just return early.
        if (!$this->shoppingCartUtil->hasMiraklSellerProducts($order->getItems())) {
            return $result;
        }

        // We need to check if the order is paid using a BNPL payment method.
        $payment = $order->getPayment();

        if ($payment === null) {
            return $result;
        }

        $paymentMethod = $payment->getMethod();

        if (!in_array($paymentMethod, BuyNowPayLaterUtil::BNPL_PAYMENT_METHODS, true)) {
            return $result;
        }

        $orderId = $order->getIncrementId();

        if ($transaction['status'] === Transaction::EXPIRED) {
            $this->miraklOrderUtil->cancelById($orderId . self::SUFFIX_BNPL_MIRAKL_ORDER);
            return $result;
        }

        if ($transaction['status'] !== Transaction::SHIPPED) {
            return $result;
        }

        if ($transaction['financial_status'] === Transaction::COMPLETED) {
            $this->registerCustomerDebit->execute($orderId . self::SUFFIX_BNPL_MIRAKL_ORDER);
        }

        return $result;
    }
}
