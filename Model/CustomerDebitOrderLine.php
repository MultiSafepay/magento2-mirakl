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
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebitOrderLine as CustomerDebitOrderLineResourceModel;

class CustomerDebitOrderLine extends AbstractModel
{
    public const ID = 'customer_debit_order_line_id';
    public const CUSTOMER_DEBIT_ID = 'customer_debit_id';
    public const OFFER_ID = 'offer_id';
    public const ORDER_LINE_AMOUNT = 'order_line_amount';
    public const ORDER_LINE_ID = 'order_line_id';
    public const ORDER_LINE_QUANTITY = 'order_line_quantity';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(CustomerDebitOrderLineResourceModel::class);
    }

    /**
     * @return int
     */
    public function getCustomerDebitId(): int
    {
        return (int)$this->getData(self::CUSTOMER_DEBIT_ID);
    }

    /**
     * @param int $customerDebitId
     * @return CustomerDebitOrderLine
     */
    public function setCustomerDebitId(int $customerDebitId): CustomerDebitOrderLine
    {
        return $this->setData(self::CUSTOMER_DEBIT_ID, $customerDebitId);
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
     * @return CustomerDebitOrderLine
     */
    public function setOfferId(string $offerId): CustomerDebitOrderLine
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
     * @return CustomerDebitOrderLine
     */
    public function setOrderLineAmount(float $orderLineAmount): CustomerDebitOrderLine
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
     * @return CustomerDebitOrderLine
     */
    public function setOrderLineId(string $orderLineId): CustomerDebitOrderLine
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
     * @return CustomerDebitOrderLine
     */
    public function setOrderLineQuantity(int $orderLineQuantity): CustomerDebitOrderLine
    {
        return $this->setData(self::ORDER_LINE_QUANTITY, $orderLineQuantity);
    }
}
