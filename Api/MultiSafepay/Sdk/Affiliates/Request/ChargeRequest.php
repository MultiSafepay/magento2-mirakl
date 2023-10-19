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

namespace MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\Request;

use MultiSafepay\Api\Base\RequestBody;
use MultiSafepay\Api\Transactions\OrderRequest\Arguments\Description;
use MultiSafepay\ValueObject\Money;

class ChargeRequest extends RequestBody
{
    /**
     * @var string
     */
    private $orderId;

    /**
     * @var Money
     */
    private $money;

    /**
     * @var Description
     */
    private $description;

    /**
     * Return the info set in this object as array, removing those properties previously set as null
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->removeNullRecursive(array_merge(
            [
                'order_id' => $this->orderId,
                'currency' => $this->money ? $this->money->getCurrency() : null,
                'amount' => $this->money ? (int)round($this->money->getAmount()) : null,
                'description' => $this->description ? $this->description->getData() : null,
            ],
            $this->data
        ));
    }

    /**
     * Add Order ID
     *
     * @param string $orderId
     * @return ChargeRequest
     */
    public function addOrderId(string $orderId): ChargeRequest
    {
        $this->orderId = $orderId;
        return $this;
    }

    /**
     * Get Order ID
     *
     * @return string
     */
    public function getOrderId(): string
    {
        return $this->orderId;
    }

    /**
     * Add Money
     *
     * @param Money $money
     * @return ChargeRequest
     */
    public function addMoney(Money $money): ChargeRequest
    {
        $this->money = $money;
        return $this;
    }

    /**
     * Add Description
     *
     * @param Description $description
     * @return ChargeRequest
     */
    public function addDescription(Description $description): ChargeRequest
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Add Description as a string
     *
     * @param string $description
     * @return ChargeRequest
     */
    public function addDescriptionText(string $description): ChargeRequest
    {
        $this->description = (new Description())->addDescription($description);
        return $this;
    }
}
