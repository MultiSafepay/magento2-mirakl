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

use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefund as PayOutRefundResourceModel;

class PayOutRefund extends AbstractModel
{
    public const PAYOUT_REFUND_ID = 'payout_refund_id';
    public const MAGENTO_STORE_ID = 'store_id';
    public const MIRAKL_SHOP_ID = 'shop_id';
    public const MAGENTO_ORDER_ID = 'order_commercial_id';
    public const MIRAKL_ORDER_ID = 'order_id';
    public const MIRAKL_CURRENCY_ISO_CODE = 'currency_iso_code';
    public const FULLY_REFUNDED = 'fully_refunded';
    public const TIMESTAMP = 'timestamp';
    public const ORDER_LINES = 'order_lines';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(PayOutRefundResourceModel::class);
    }

    /**
     * @return int
     */
    public function getPayOutRefundId(): int
    {
        return (int)$this->getData(self::PAYOUT_REFUND_ID);
    }

    /**
     * @param int $payOutRefundId
     * @return PayOutRefund
     */
    public function setPayOutRefundId(int $payOutRefundId): PayOutRefund
    {
        return $this->setData(self::PAYOUT_REFUND_ID, $payOutRefundId);
    }

    /**
     * @return int
     */
    public function getMagentoStoreId(): int
    {
        return (int)$this->getData(self::MAGENTO_STORE_ID);
    }

    /**
     * @param int $magentoStoreId
     * @return PayOutRefund
     */
    public function setMagentoStoreId(int $magentoStoreId): PayOutRefund
    {
        return $this->setData(self::MAGENTO_STORE_ID, $magentoStoreId);
    }

    /**
     * @return int
     */
    public function getMiraklShopId(): int
    {
        return (int)$this->getData(self::MIRAKL_SHOP_ID);
    }

    /**
     * @param int $magentoMiraklShopId
     * @return PayOutRefund
     */
    public function setMiraklShopId(int $magentoMiraklShopId): PayOutRefund
    {
        return $this->setData(self::MIRAKL_SHOP_ID, $magentoMiraklShopId);
    }

    /**
     * @return string
     */
    public function getMagentoOrderId(): string
    {
        return (string)$this->getData(self::MAGENTO_ORDER_ID);
    }

    /**
     * @param string $orderId
     * @return PayOutRefund
     */
    public function setMagentoOrderId(string $orderId): PayOutRefund
    {
        return $this->setData(self::MAGENTO_ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    public function getMiraklOrderId(): string
    {
        return (string)$this->getData(self::MIRAKL_ORDER_ID);
    }

    /**
     * @param string $miraklOrderId
     * @return PayOutRefund
     */
    public function setMiraklOrderId(string $miraklOrderId): PayOutRefund
    {
        return $this->setData(self::MIRAKL_ORDER_ID, $miraklOrderId);
    }

    /**
     * @return string
     */
    public function getMiraklCurrencyIsoCode(): string
    {
        return (string)$this->getData(self::MIRAKL_CURRENCY_ISO_CODE);
    }

    /**
     * @param string $miraklCurrencyIsoCode
     * @return PayOutRefund
     */
    public function setMiraklCurrencyIsoCode(string $miraklCurrencyIsoCode): PayOutRefund
    {
        return $this->setData(self::MIRAKL_CURRENCY_ISO_CODE, $miraklCurrencyIsoCode);
    }

    /**
     * @return bool
     */
    public function isFullyRefunded(): bool
    {
        return (bool)$this->getData(self::FULLY_REFUNDED);
    }

    /**
     * @param bool $fullyRefunded
     * @return PayOutRefund
     */
    public function setFullyRefunded(bool $fullyRefunded): PayOutRefund
    {
        return $this->setData(self::FULLY_REFUNDED, (int)$fullyRefunded);
    }

    /**
     * @return string
     */
    public function getTimestamp(): string
    {
        return (string)$this->getData(self::TIMESTAMP);
    }

    /**
     * @param string $timestamp
     * @return PayOutRefund
     */
    public function setTimestamp(string $timestamp): PayOutRefund
    {
        return $this->setData(self::TIMESTAMP, $timestamp);
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
     * @return PayOutRefund
     */
    public function setOrderLines(array $orderLines): PayOutRefund
    {
        return $this->setData(self::ORDER_LINES, $orderLines);
    }
}
