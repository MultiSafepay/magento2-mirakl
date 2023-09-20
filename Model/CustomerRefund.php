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

namespace MultiSafepay\Mirakl\Model;

use Magento\Framework\Model\AbstractModel;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund as CustomerRefundResourceModel;

class CustomerRefund extends AbstractModel
{
    public const AMOUNT = 'amount';
    public const CURRENCY_ISO_CODE = 'currency_iso_code';
    public const CUSTOMER_ID = 'customer_id';
    public const ORDER_COMMERCIAL_ID = 'order_commercial_id';
    public const ORDER_ID = 'order_id';
    public const PAYMENT_WORKFLOW = 'payment_workflow';
    public const SHOP_ID = 'shop_id';
    public const STATUS = 'status';
    public const ORDER_LINES = 'order_lines';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(CustomerRefundResourceModel::class);
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return (float)$this->getData(self::AMOUNT);
    }

    /**
     * @param float $amount
     * @return CustomerRefund
     */
    public function setAmount(float $amount): CustomerRefund
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @return string
     */
    public function getCurrencyIsoCode(): string
    {
        return (string)$this->getData(self::CURRENCY_ISO_CODE);
    }

    /**
     * @param string $currencyIsoCode
     * @return CustomerRefund
     */
    public function setCurrencyIsoCode(string $currencyIsoCode): CustomerRefund
    {
        return $this->setData(self::CURRENCY_ISO_CODE, $currencyIsoCode);
    }

    /**
     * @return string
     */
    public function getCustomerId(): string
    {
        return (string)$this->getData(self::CUSTOMER_ID);
    }

    /**
     * @param string $customerId
     * @return CustomerRefund
     */
    public function setCustomerId(string $customerId): CustomerRefund
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return string
     */
    public function getOrderCommercialId(): string
    {
        return (string)$this->getData(self::ORDER_COMMERCIAL_ID);
    }

    /**
     * @param string $orderCommercialId
     * @return CustomerRefund
     */
    public function setOrderCommercialId(string $orderCommercialId): CustomerRefund
    {
        return $this->setData(self::ORDER_COMMERCIAL_ID, $orderCommercialId);
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return (string)$this->getData(self::ORDER_ID);
    }

    /**
     * @param string $orderId
     * @return CustomerRefund
     */
    public function setOrderId(string $orderId): CustomerRefund
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    public function getShopId(): string
    {
        return (string)$this->getData(self::SHOP_ID);
    }

    /**
     * @param string $shopId
     * @return CustomerRefund
     */
    public function setShopId(string $shopId): CustomerRefund
    {
        return $this->setData(self::SHOP_ID, $shopId);
    }

    /**
     * @return string
     */
    public function getPaymentWorkflowId(): string
    {
        return (string)$this->getData(self::PAYMENT_WORKFLOW);
    }

    /**
     * @param string $paymentWorkflow
     * @return CustomerRefund
     */
    public function setPaymentWorkflow(string $paymentWorkflow): CustomerRefund
    {
        return $this->setData(self::PAYMENT_WORKFLOW, $paymentWorkflow);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * @param int $status
     * @return CustomerRefund
     */
    public function setStatus(int $status): CustomerRefund
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return array
     */
    public function getOrderLines(): array
    {
        return (array)$this->getData(self::ORDER_LINES);
    }

    /**
     * @param array $orderLines
     * @return CustomerRefund
     */
    public function setOrderLines(array $orderLines): CustomerRefund
    {
        return $this->setData(self::ORDER_LINES, $orderLines);
    }
}
