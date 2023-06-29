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

use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine as PayOutOrderLineResourceModel;

class PayOutOrderLine extends AbstractModel
{
    public const PAYOUT_ORDER_LINE_ID = 'payout_order_line_id';
    public const PAYOUT_ID = 'payout_id';
    public const PRODUCT_PRICE = 'product_price';
    public const PRODUCT_QUANTITY = 'product_quantity';
    public const PRODUCT_TAXES = 'product_taxes';
    public const SHIPPING_PRICE = 'shipping_price';
    public const SHIPPING_TAXES = 'shipping_taxes';
    public const TOTAL_PRICE_INCLUDING_TAXES = 'total_price_including_taxes';
    public const TOTAL_PRICE_EXCLUDING_TAXES = 'total_price_excluding_taxes';
    public const OPERATOR_AMOUNT = 'operator_amount';
    public const SELLER_AMOUNT = 'seller_amount';
    public const IS_REFUNDED = 'is_refunded';
    public const MIRAKL_ORDER_LINE_ID = 'mirakl_order_line_id';
    public const MIRAKL_ORDER_STATUS = 'mirakl_order_status';
    public const STATUS = 'status';

    public function _construct()
    {
        $this->_init(PayOutOrderLineResourceModel::class);
    }

    /**
     * @return int
     */
    public function getPayoutOrderLineId(): int
    {
        return (int)$this->getData(self::PAYOUT_ORDER_LINE_ID);
    }

    /**
     * @return int
     */
    public function getPayoutId(): int
    {
        return (int)$this->getData(self::PAYOUT_ID);
    }

    /**
     * @param int $payoutId
     * @return PayOutOrderLine
     */
    public function setPayoutId(int $payoutId): PayOutOrderLine
    {
        return $this->setData(self::PAYOUT_ID, $payoutId);
    }

    /**
     * @return float
     */
    public function getProductPrice(): float
    {
        return (float)$this->getData(self::PRODUCT_PRICE);
    }

    /**
     * @param float $productPrice
     * @return PayOutOrderLine
     */
    public function setProductPrice(float $productPrice): PayOutOrderLine
    {
        return $this->setData(self::PRODUCT_PRICE, $productPrice);
    }

    /**
     * @return int
     */
    public function getProductQuantity(): int
    {
        return (int)$this->getData(self::PRODUCT_QUANTITY);
    }

    /**
     * @param int $productQuantity
     * @return PayOutOrderLine
     */
    public function setProductQuantity(int $productQuantity): PayOutOrderLine
    {
        return $this->setData(self::PRODUCT_QUANTITY, $productQuantity);
    }

    /**
     * @return float
     */
    public function getProductTaxes(): float
    {
        return (float)$this->getData(self::PRODUCT_TAXES);
    }

    /**
     * @param float $productTaxes
     * @return PayOutOrderLine
     */
    public function setProductTaxes(float $productTaxes): PayOutOrderLine
    {
        return $this->setData(self::PRODUCT_TAXES, $productTaxes);
    }

    /**
     * @return float
     */
    public function getShippingPrice(): float
    {
        return (float)$this->getData(self::SHIPPING_PRICE);
    }

    /**
     * @param $shippingPrice
     * @return PayOutOrderLine
     */
    public function setShippingPrice($shippingPrice): PayOutOrderLine
    {
        return $this->setData(self::SHIPPING_PRICE, $shippingPrice);
    }

    /**
     * @return float
     */
    public function getShippingTaxes(): float
    {
        return (float)$this->getData(self::SHIPPING_TAXES);
    }

    /**
     * @param float $shippingTaxes
     * @return PayOutOrderLine
     */
    public function setShippingTaxes(float $shippingTaxes): PayOutOrderLine
    {
        return $this->setData(self::SHIPPING_TAXES, $shippingTaxes);
    }

    /**
     * @return float
     */
    public function getTotalPriceIncludingTaxes(): float
    {
        return (float)$this->getData(self::TOTAL_PRICE_INCLUDING_TAXES);
    }

    /**
     * @param float $totalPriceIncludingTaxes
     * @return PayOutOrderLine
     */
    public function setTotalPriceIncludingTaxes(float $totalPriceIncludingTaxes): PayOutOrderLine
    {
        return $this->setData(self::TOTAL_PRICE_INCLUDING_TAXES, $totalPriceIncludingTaxes);
    }

    /**
     * @return float
     */
    public function getTotalPriceExcludingTaxes(): float
    {
        return (float)$this->getData(self::TOTAL_PRICE_EXCLUDING_TAXES);
    }

    /**
     * @param float $totalPriceExcludingTaxes
     * @return PayOutOrderLine
     */
    public function setTotalPriceExcludingTaxes(float $totalPriceExcludingTaxes): PayOutOrderLine
    {
        return $this->setData(self::TOTAL_PRICE_EXCLUDING_TAXES, $totalPriceExcludingTaxes);
    }

    /**
     * @return float
     */
    public function getOperatorAmount(): float
    {
        return (float)$this->getData(self::OPERATOR_AMOUNT);
    }

    /**
     * @param float $operatorAmount
     * @return PayOutOrderLine
     */
    public function setOperatorAmount(float $operatorAmount): PayOutOrderLine
    {
        return $this->setData(self::OPERATOR_AMOUNT, $operatorAmount);
    }

    /**
     * @return float
     */
    public function getSellerAmount(): float
    {
        return (float)$this->getData(self::SELLER_AMOUNT);
    }

    /**
     * @param float $sellerAmount
     * @return PayOutOrderLine
     */
    public function setSellerAmount(float $sellerAmount): PayOutOrderLine
    {
        return $this->setData(self::SELLER_AMOUNT, $sellerAmount);
    }

    /**
     * @return int
     */
    public function getIsRefunded(): int
    {
        return (int)$this->getData(self::IS_REFUNDED);
    }

    /**
     * @param int $isRefunded
     * @return PayOutOrderLine
     */
    public function setIsRefunded(int $isRefunded): PayOutOrderLine
    {
        return $this->setData(self::IS_REFUNDED, $isRefunded);
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
     * @return PayOutOrderLine
     */
    public function setMiraklOrderLineId(int $miraklOrderLineId): PayOutOrderLine
    {
        return $this->setData(self::MIRAKL_ORDER_LINE_ID, $miraklOrderLineId);
    }

    /**
     * @return string
     */
    public function getMiraklOrderStatus(): string
    {
        return (string)$this->getData(self::MIRAKL_ORDER_STATUS);
    }

    /**
     * @param string $miraklOrderStatus
     * @return PayOutOrderLine
     */
    public function setMiraklOrderStatus(string $miraklOrderStatus): PayOutOrderLine
    {
        return $this->setData(self::MIRAKL_ORDER_STATUS, $miraklOrderStatus);
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
     * @return PayOutOrderLine
     */
    public function setStatus(int $status): PayOutOrderLine
    {
        return $this->setData(self::STATUS, $status);
    }
}
