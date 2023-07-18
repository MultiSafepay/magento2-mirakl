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
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit as CustomerDebitResourceModel;

class CustomerDebit extends AbstractModel
{
    public const CUSTOMER_DEBIT_ID = 'customer_debit_id';
    public const AMOUNT = 'amount';
    public const CURRENCY_ISO_CODE = 'currency_iso_code';
    public const CUSTOMER_ID = 'customer_id';
    public const DEBIT_ENTITY_ID = 'debit_entity_id';
    public const DEBIT_ENTITY_TYPE = 'debit_entity_type';
    public const ORDER_COMMERCIAL_ID = 'order_commercial_id';
    public const ORDER_ID = 'order_id';
    public const SHOP_ID = 'shop_id';
    public const STATUS = 'status';
    public const ORDER_LINES = 'order_lines';

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(CustomerDebitResourceModel::class);
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
     * @return CustomerDebit
     */
    public function setAmount(float $amount): CustomerDebit
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
     * @return CustomerDebit
     */
    public function setCurrencyIsoCode(string $currencyIsoCode): CustomerDebit
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
     * @return CustomerDebit
     */
    public function setCustomerId(string $customerId): CustomerDebit
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * @return string
     */
    public function getDebitEntityId(): string
    {
        return (string)$this->getData(self::DEBIT_ENTITY_ID);
    }

    /**
     * @param string $debitEntityId
     * @return CustomerDebit
     */
    public function setDebitEntityId(string $debitEntityId): CustomerDebit
    {
        return $this->setData(self::DEBIT_ENTITY_ID, $debitEntityId);
    }

    /**
     * @return string
     */
    public function getDebitEntityType(): string
    {
        return (string)$this->getData(self::DEBIT_ENTITY_TYPE);
    }

    /**
     * @param string $debitEntityType
     * @return CustomerDebit
     */
    public function setDebitEntityType(string $debitEntityType): CustomerDebit
    {
        return $this->setData(self::DEBIT_ENTITY_TYPE, $debitEntityType);
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
     * @return CustomerDebit
     */
    public function setOrderCommercialId(string $orderCommercialId): CustomerDebit
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
     * @return CustomerDebit
     */
    public function setOrderId(string $orderId): CustomerDebit
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
     * @return CustomerDebit
     */
    public function setShopId(string $shopId): CustomerDebit
    {
        return $this->setData(self::SHOP_ID, $shopId);
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
     * @return CustomerDebit
     */
    public function setStatus(int $status): CustomerDebit
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
     * @return CustomerDebit
     */
    public function setOrderLines(array $orderLines): CustomerDebit
    {
        return $this->setData(self::ORDER_LINES, $orderLines);
    }
}
