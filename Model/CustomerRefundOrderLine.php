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
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefundOrderLine as CustomerRefundOrderLineResourceModel;

class CustomerRefundOrderLine extends AbstractModel
{
    public const CUSTOMER_REFUND_ID = 'customer_refund_id';
    public const OFFER_ID = 'offer_id';
    public const ORDER_LINE_AMOUNT = 'order_line_amount';
    public const ORDER_LINE_ID = 'order_line_id';
    public const ORDER_LINE_QUANTITY = 'order_line_quantity';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(CustomerRefundOrderLineResourceModel::class);
    }

    /**
     * @return int
     */
    public function getCustomerRefundRequestId(): int
    {
        return (int)$this->getData(self::CUSTOMER_REFUND_ID);
    }

    /**
     * @param int $customerRefundRequestId
     * @return CustomerRefundOrderLine
     */
    public function setCustomerRefundId(int $customerRefundRequestId): CustomerRefundOrderLine
    {
        return $this->setData(self::CUSTOMER_REFUND_ID, $customerRefundRequestId);
    }

    /**
     * @return string
     */
    public function getOfferId(): string
    {
        return (string)$this->getData(self::OFFER_ID);
    }

    /**
     * @param string $offerId
     * @return CustomerRefundOrderLine
     */
    public function setOfferId(string $offerId): CustomerRefundOrderLine
    {
        return $this->setData(self::OFFER_ID, $offerId);
    }

    /**
     * @return float
     */
    public function getOrderLineAmount(): float
    {
        return (float)$this->getData(self::ORDER_LINE_AMOUNT);
    }

    /**
     * @param float $orderLineAmount
     * @return CustomerRefundOrderLine
     */
    public function setOrderLineAmount(float $orderLineAmount): CustomerRefundOrderLine
    {
        return $this->setData(self::ORDER_LINE_AMOUNT, $orderLineAmount);
    }

    /**
     * @return string
     */
    public function getOrderLineId(): string
    {
        return (string)$this->getData(self::ORDER_LINE_ID);
    }

    /**
     * @param string $orderLineId
     * @return CustomerRefundOrderLine
     */
    public function setOrderLineId(string $orderLineId): CustomerRefundOrderLine
    {
        return $this->setData(self::ORDER_LINE_ID, $orderLineId);
    }

    /**
     * @return int
     */
    public function getOrderLineQuantity(): int
    {
        return (int)$this->getData(self::ORDER_LINE_QUANTITY);
    }

    /**
     * @param int $orderLineQuantity
     * @return CustomerRefundOrderLine
     */
    public function setOrderLineQuantity(int $orderLineQuantity): CustomerRefundOrderLine
    {
        return $this->setData(self::ORDER_LINE_QUANTITY, $orderLineQuantity);
    }
}
