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

namespace MultiSafepay\Mirakl\Service;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderLine as MiraklOrderLine;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\CustomerDebitFactory;
use MultiSafepay\Mirakl\Model\CustomerDebitOrderLine;
use MultiSafepay\Mirakl\Model\CustomerDebitOrderLineFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit as CustomerDebitResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebitOrderLine as CustomerDebitOrderLineResourceModel;
use MultiSafepay\Mirakl\Util\CustomerDebitUtil;
use MultiSafepay\Mirakl\Util\MiraklOrderUtil;

/**
 * CustomerDebit controller which receives and handles the Customer Debit Connector requests
 */
class RegisterCustomerDebit
{
    /**
     * @var CustomerDebitFactory
     */
    private $customerDebitFactory;

    /**
     * @var CustomerDebitResourceModel
     */
    private $customerDebitResourceModel;

    /**
     * @var CustomerDebitOrderLineFactory
     */
    private $customerDebitOrderLineFactory;

    /**
     * @var CustomerDebitOrderLineResourceModel
     */
    private $customerDebitOrderLineResourceModel;

    /**
     * @var MiraklOrderUtil
     */
    private $miraklOrderUtil;

    /**
     * @var CustomerDebitUtil
     */
    private $customerDebitUtil;

    /**
     * Notification constructor.
     *
     * @param CustomerDebitFactory $customerDebitFactory
     * @param CustomerDebitResourceModel $customerDebitResourceModel
     * @param CustomerDebitOrderLineFactory $customerDebitOrderLineFactory
     * @param CustomerDebitOrderLineResourceModel $customerDebitOrderLineResourceModel
     * @param MiraklOrderUtil $miraklOrderUtil
     * @param CustomerDebitUtil $customerDebitUtil
     */
    public function __construct(
        CustomerDebitFactory $customerDebitFactory,
        CustomerDebitResourceModel $customerDebitResourceModel,
        CustomerDebitOrderLineFactory $customerDebitOrderLineFactory,
        CustomerDebitOrderLineResourceModel $customerDebitOrderLineResourceModel,
        MiraklOrderUtil $miraklOrderUtil,
        CustomerDebitUtil $customerDebitUtil
    ) {
        $this->customerDebitFactory = $customerDebitFactory;
        $this->customerDebitResourceModel = $customerDebitResourceModel;
        $this->customerDebitOrderLineFactory = $customerDebitOrderLineFactory;
        $this->customerDebitOrderLineResourceModel = $customerDebitOrderLineResourceModel;
        $this->miraklOrderUtil = $miraklOrderUtil;
        $this->customerDebitUtil = $customerDebitUtil;
    }

    /**
     * Process the debit request HTTP notification.
     *
     * @param string $miraklOrderID
     * @return void
     *
     * @throws AlreadyExistsException
     */
    public function execute(string $miraklOrderID): void
    {
        try {
            $this->customerDebitUtil->getCustomerDebit($miraklOrderID);
        } catch (NoSuchEntityException $noSuchEntityException) {
            $miraklOrder = $this->miraklOrderUtil->getById($miraklOrderID);
            $miraklCustomerDebitItem = [];

            $miraklCustomerDebitItem[CustomerDebit::CUSTOMER_ID] = $miraklOrder->getCustomer()->getCustomerId();
            $miraklCustomerDebitItem[CustomerDebit::ORDER_ID] = $miraklOrderID;
            $miraklCustomerDebitItem[CustomerDebit::ORDER_COMMERCIAL_ID] = $miraklOrder->getCommercialId();
            $miraklCustomerDebitItem[CustomerDebit::SHOP_ID] = $miraklOrder->getShopId();

            $miraklCustomerDebitItem['debit_entity'] = [];
            $miraklCustomerDebitItem['debit_entity']['type'] = 'ORDER';
            $miraklCustomerDebitItem['debit_entity']['id'] = $miraklOrderID;

            $miraklCustomerDebitItem[CustomerDebit::CURRENCY_ISO_CODE] = $miraklOrder->getCurrencyIsoCode();
            $miraklCustomerDebitItem[CustomerDebit::AMOUNT] = $this->getOrderAmount($miraklOrder);

            $orderLines = $miraklOrder->getOrderLines();

            /** @var MiraklOrderLine $orderLine */
            foreach ($orderLines as $orderLine) {
                $orderLineId = $orderLine->getId();
                $debitOrderLine = [];

                $debitOrderLine[CustomerDebitOrderLine::OFFER_ID] = $orderLine->getOffer()->getId();
                $debitOrderLine[CustomerDebitOrderLine::ORDER_LINE_AMOUNT] = $this->getOrderLineAmount($orderLine);
                $debitOrderLine[CustomerDebitOrderLine::ORDER_LINE_ID] = $orderLine->getId();
                $debitOrderLine[CustomerDebitOrderLine::ORDER_LINE_QUANTITY] = $orderLine->getQuantity();

                $miraklCustomerDebitItem[CustomerDebit::ORDER_LINES]['order_line'][$orderLineId] = $debitOrderLine;
            }

            $this->saveCustomerDebit($miraklCustomerDebitItem);
        }
    }

    /**
     * Save the customer debit request
     *
     * @param array $miraklCustomerDebitItem
     * @return void
     * @throws AlreadyExistsException
     */
    public function saveCustomerDebit(array $miraklCustomerDebitItem): void
    {
        /** @var CustomerDebit $customerDebit */
        $customerDebit = $this->customerDebitFactory->create();
        $customerDebit->setCustomerId($miraklCustomerDebitItem[$customerDebit::CUSTOMER_ID]);
        $customerDebit->setOrderId($miraklCustomerDebitItem[$customerDebit::ORDER_ID]);
        $customerDebit->setOrderCommercialId($miraklCustomerDebitItem[$customerDebit::ORDER_COMMERCIAL_ID]);
        $customerDebit->setShopId((string)$miraklCustomerDebitItem[$customerDebit::SHOP_ID]);
        $customerDebit->setDebitEntityType($miraklCustomerDebitItem['debit_entity']['type']);
        $customerDebit->setDebitEntityId($miraklCustomerDebitItem['debit_entity']['id']);
        $customerDebit->setCurrencyIsoCode($miraklCustomerDebitItem[$customerDebit::CURRENCY_ISO_CODE]);
        $customerDebit->setAmount($miraklCustomerDebitItem[$customerDebit::AMOUNT]);
        $customerDebit->setStatus($customerDebit::CUSTOMER_DEBIT_STATUS_PENDING_TO_BE_PROCESSED);

        $savedCustomerDebit = $this->customerDebitResourceModel->save($customerDebit);

        $orderLines = $miraklCustomerDebitItem[$customerDebit::ORDER_LINES] ?? [];

        if ($savedCustomerDebit && !empty($orderLines)) {
            foreach ($orderLines['order_line'] as $miraklCustomerDebitOrderLineItem) {
                $this->saveCustomerDebitOrderLines(
                    (int) $customerDebit->getId(),
                    $miraklCustomerDebitOrderLineItem
                );
            }
        }
    }

    /**
     * Save the order lines for the given customer debit id
     *
     * @param int $customerDebitId
     * @param array $orderLineItem
     * @return void
     * @throws AlreadyExistsException
     */
    private function saveCustomerDebitOrderLines(int $customerDebitId, array $orderLineItem): void
    {
        /** @var CustomerDebitOrderLine $customerDebitOrderLine */
        $customerDebitOrderLine = $this->customerDebitOrderLineFactory->create();

        $customerDebitOrderLine->setCustomerDebitId($customerDebitId);
        $customerDebitOrderLine->setOfferId((string)$orderLineItem[$customerDebitOrderLine::OFFER_ID]);
        $customerDebitOrderLine->setOrderLineAmount($orderLineItem[$customerDebitOrderLine::ORDER_LINE_AMOUNT]);
        $customerDebitOrderLine->setOrderLineId($orderLineItem[$customerDebitOrderLine::ORDER_LINE_ID]);
        $customerDebitOrderLine->setOrderLineQuantity($orderLineItem[$customerDebitOrderLine::ORDER_LINE_QUANTITY]);

        $this->customerDebitOrderLineResourceModel->save($customerDebitOrderLine);
    }

    /**
     * Get the total amount of the Mirakl order
     *
     * @param MiraklOrder $miraklOrder
     * @return float
     */
    private function getOrderAmount(MiraklOrder $miraklOrder): float
    {
        $total = 0;

        /** @var MiraklOrderLine $orderLine */
        foreach ($miraklOrder->getOrderLines() as $orderLine) {
            $total += $this->getOrderLineAmount($orderLine);
        }

        return $total;
    }

    /**
     * Get the total amount for the given Mirakl OrderLine
     *
     * @param MiraklOrderLine $orderLine
     * @return float
     */
    private function getOrderLineAmount(MiraklOrderLine $orderLine): float
    {
        return $orderLine->getTotalPrice() + $this->getTaxesFromOrderLine($orderLine)
            + $this->getShippingTaxesFromOrderLine($orderLine);
    }

    /**
     * Return the total taxes for the given Mirakl OrderLine
     *
     * @param MiraklOrderLine $orderLine
     * @return float
     */
    private function getTaxesFromOrderLine(MiraklOrderLine $orderLine): float
    {
        $taxes = 0;

        foreach ($orderLine->getTaxes() as $tax) {
            $taxes += (float)$tax->getAmount();
        }

        return $taxes;
    }

    /**
     * Return the total of the shipping taxes for the given Mirakl OrderLine
     *
     * @param MiraklOrderLine $orderLine
     * @return float
     */
    private function getShippingTaxesFromOrderLine(MiraklOrderLine $orderLine): float
    {
        $taxes = 0;

        foreach ($orderLine->getShippingTaxes()->getItems() as $tax) {
            $taxes += (float)$tax->getAmount();
        }

        return $taxes;
    }
}
