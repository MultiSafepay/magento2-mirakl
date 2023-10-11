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

use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine as PayOutRefundOrderLineResourceModel;

class PayOutRefundOrderLine extends AbstractModel
{
    public const PAYOUT_REFUND_ORDER_LINE_ID = 'payout_refund_order_line_id';
    public const PAYOUT_REFUND_ID = 'payout_refund_id';
    public const AMOUNT = 'amount';
    public const TAX_AMOUNT = 'tax_amount';
    public const COMMISSION_AMOUNT = 'commission_amount';
    public const COMMISSION_TAX_AMOUNT = 'commission_tax_amount';
    public const COMMISSION_TOTAL_AMOUNT = 'commission_total_amount';
    public const QUANTITY = 'quantity';
    public const SHIPPING_AMOUNT = 'shipping_amount';
    public const SHIPPING_TAX_AMOUNT = 'shipping_tax_amount';
    public const MIRAKL_REFUND_ID = 'mirakl_refund_id';
    public const MIRAKL_ORDER_LINE_ID = 'mirakl_order_line_id';
    public const MIRAKL_REFUND_STATE = 'mirakl_refund_state';
    public const STATUS = 'status';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(PayOutRefundOrderLineResourceModel::class);
    }

    /**
     * @return int
     */
    public function getPayoutRefundOrderLineId(): int
    {
        return (int)$this->getData(self::PAYOUT_REFUND_ORDER_LINE_ID);
    }

    /**
     * @return int
     */
    public function getRefundId(): int
    {
        return (int)$this->getData(self::PAYOUT_REFUND_ID);
    }

    /**
     * @param int $payoutRefundId
     * @return PayOutRefundOrderLine
     */
    public function setPayoutRefundId(int $payoutRefundId): PayOutRefundOrderLine
    {
        return $this->setData(self::PAYOUT_REFUND_ID, $payoutRefundId);
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
     * @return PayOutRefundOrderLine
     */
    public function setAmount(float $amount): PayOutRefundOrderLine
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @return float
     */
    public function getTaxAmount(): float
    {
        return (float)$this->getData(self::TAX_AMOUNT);
    }

    /**
     * @param float $taxAmount
     * @return PayOutRefundOrderLine
     */
    public function setTaxAmount(float $taxAmount): PayOutRefundOrderLine
    {
        return $this->setData(self::TAX_AMOUNT, $taxAmount);
    }

    /**
     * @return float
     */
    public function getCommissionAmount(): float
    {
        return (int)$this->getData(self::COMMISSION_AMOUNT);
    }

    /**
     * @param float $commissionAmount
     * @return PayOutRefundOrderLine
     */
    public function setCommissionAmount(float $commissionAmount): PayOutRefundOrderLine
    {
        return $this->setData(self::COMMISSION_AMOUNT, $commissionAmount);
    }

    /**
     * @return float
     */
    public function getCommissionTaxAmount(): float
    {
        return (int)$this->getData(self::COMMISSION_TAX_AMOUNT);
    }

    /**
     * @param float $commissionTaxAmount
     * @return PayOutRefundOrderLine
     */
    public function setCommissionTaxAmount(float $commissionTaxAmount): PayOutRefundOrderLine
    {
        return $this->setData(self::COMMISSION_TAX_AMOUNT, $commissionTaxAmount);
    }

    /**
     * @return float
     */
    public function getCommissionTotalAmount(): float
    {
        return (float)$this->getData(self::COMMISSION_TOTAL_AMOUNT);
    }

    /**
     * @param float $commissionTotalAmount
     * @return PayOutRefundOrderLine
     */
    public function setCommissionTotalAmount(float $commissionTotalAmount): PayOutRefundOrderLine
    {
        return $this->setData(self::COMMISSION_TOTAL_AMOUNT, $commissionTotalAmount);
    }

    /**
     * @return int
     */
    public function getQuantity(): int
    {
        return (int)$this->getData(self::QUANTITY);
    }

    /**
     * @param int $quantity
     * @return PayOutRefundOrderLine
     */
    public function setQuantity(int $quantity): PayOutRefundOrderLine
    {
        return $this->setData(self::QUANTITY, $quantity);
    }

    /**
     * @return float
     */
    public function getShippingAmount(): float
    {
        return (float)$this->getData(self::SHIPPING_AMOUNT);
    }

    /**
     * @param $shippingAmount
     * @return PayOutRefundOrderLine
     */
    public function setShippingAmount($shippingAmount): PayOutRefundOrderLine
    {
        return $this->setData(self::SHIPPING_AMOUNT, $shippingAmount);
    }

    /**
     * @return float
     */
    public function getShippingTaxAmount(): float
    {
        return (float)$this->getData(self::SHIPPING_TAX_AMOUNT);
    }

    /**
     * @param $shippingTaxAmount
     * @return PayOutRefundOrderLine
     */
    public function setShippingTaxAmount($shippingTaxAmount): PayOutRefundOrderLine
    {
        return $this->setData(self::SHIPPING_TAX_AMOUNT, $shippingTaxAmount);
    }

    /**
     * @return int
     */
    public function getMiraklRefundId(): int
    {
        return (int)$this->getData(self::MIRAKL_REFUND_ID);
    }

    /**
     * @param int $miraklRefundId
     * @return PayOutRefundOrderLine
     */
    public function setMiraklRefundId(int $miraklRefundId): PayOutRefundOrderLine
    {
        return $this->setData(self::MIRAKL_REFUND_ID, $miraklRefundId);
    }

    /**
     * @return int
     */
    public function getMiraklOrderLineId(): int
    {
        return (int)$this->getData(self::MIRAKL_ORDER_LINE_ID);
    }

    /**
     * @param int $miraklOrderLineId
     * @return PayOutRefundOrderLine
     */
    public function setMiraklOrderLineId(int $miraklOrderLineId): PayOutRefundOrderLine
    {
        return $this->setData(self::MIRAKL_ORDER_LINE_ID, $miraklOrderLineId);
    }

    /**
     * @return string
     */
    public function getMiraklRefundState(): string
    {
        return (string)$this->getData(self::MIRAKL_REFUND_STATE);
    }

    /**
     * @param string $miraklRefundState
     * @return PayOutRefundOrderLine
     */
    public function setMiraklRefundState(string $miraklRefundState): PayOutRefundOrderLine
    {
        return $this->setData(self::MIRAKL_REFUND_STATE, $miraklRefundState);
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
     * @return PayOutRefundOrderLine
     */
    public function setStatus(int $status): PayOutRefundOrderLine
    {
        return $this->setData(self::STATUS, $status);
    }
}
