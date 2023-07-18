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

use MultiSafepay\Mirakl\Model\ResourceModel\PayOut as PayOutResourceModel;

class PayOut extends AbstractModel
{
    public const MAGENTO_SHOP_ID = 'store_id';
    public const MIRAKL_SHOP_ID = 'shop_id';
    public const MAGENTO_ORDER_ID = 'order_commercial_id';
    public const MIRAKL_ORDER_ID = 'order_id';
    public const MIRAKL_CURRENCY_ISO_CODE = 'currency_iso_code';
    public const TIMESTAMP = 'timestamp';
    public const ORDER_LINES = 'order_lines';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(PayoutResourceModel::class);
    }

    /**
     * @return int
     */
    public function getMagentoShopId(): int
    {
        return (int)$this->getData(self::MAGENTO_SHOP_ID);
    }

    /**
     * @param int $magentoShopId
     * @return PayOut
     */
    public function setMagentoShopId(int $magentoShopId): PayOut
    {
        return $this->setData(self::MAGENTO_SHOP_ID, $magentoShopId);
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
     * @return PayOut
     */
    public function setMiraklShopId(int $magentoMiraklShopId): PayOut
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
     * @return PayOut
     */
    public function setMagentoOrderId(string $orderId): PayOut
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
     * @return PayOut
     */
    public function setMiraklOrderId(string $miraklOrderId): PayOut
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
     * @return PayOut
     */
    public function setMiraklCurrencyIsoCode(string $miraklCurrencyIsoCode): PayOut
    {
        return $this->setData(self::MIRAKL_CURRENCY_ISO_CODE, $miraklCurrencyIsoCode);
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
     * @return PayOut
     */
    public function setTimestamp(string $timestamp): PayOut
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
     * @return PayOut
     */
    public function setOrderLines(array $orderLines): PayOut
    {
        return $this->setData(self::ORDER_LINES, $orderLines);
    }
}
