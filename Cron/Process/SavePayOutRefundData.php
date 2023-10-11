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

namespace MultiSafepay\Mirakl\Cron\Process;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mirakl\MMP\Common\Domain\Collection\Order\Tax\OrderTaxAmountCollection;
use Mirakl\MMP\Common\Domain\Order\Refund;
use Mirakl\MMP\Common\Domain\Order\Tax\OrderTaxAmount;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderLine;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Model\PayOutRefund;
use MultiSafepay\Mirakl\Model\PayOutRefundFactory;
use MultiSafepay\Mirakl\Model\PayOutRefundOrderLine;
use MultiSafepay\Mirakl\Model\PayOutRefundOrderLineFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefund as PayOutRefundResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine as PayOutRefundOrderlineResourceModel;
use MultiSafepay\Mirakl\Util\MiraklOrderUtil;

class SavePayOutRefundData
{
    /**
     * @var PayOutRefundFactory
     */
    private $payOutRefundFactory;

    /**
     * @var MiraklOrderUtil
     */
    private $miraklOrderUtil;

    /**
     * @var OrderUtil
     */
    private $orderUtil;

    /**
     * @var PayOutRefundResourceModel
     */
    private $payOutRefundResourceModel;

    /**
     * @var PayOutRefundOrderLineFactory
     */
    private $payOutRefundOrderLineFactory;

    /**
     * @var PayOutRefundOrderLine
     */
    private $payOutRefundOrderLineResourceModel;

    /**
     * @param PayOutRefundFactory $payOutRefundFactory
     * @param PayOutRefundOrderLineFactory $payOutRefundOrderLineFactory
     * @param PayOutRefundResourceModel $payOutRefundResourceModel
     * @param PayOutRefundOrderlineResourceModel $payOutRefundOrderLineResourceModel
     * @param MiraklOrderUtil $miraklOrderUtil
     * @param OrderUtil $orderUtil
     */
    public function __construct(
        PayOutRefundFactory $payOutRefundFactory,
        PayOutRefundOrderLineFactory $payOutRefundOrderLineFactory,
        PayOutRefundResourceModel $payOutRefundResourceModel,
        PayOutRefundOrderlineResourceModel $payOutRefundOrderLineResourceModel,
        MiraklOrderUtil $miraklOrderUtil,
        OrderUtil $orderUtil
    ) {
        $this->payOutRefundFactory = $payOutRefundFactory;
        $this->payOutRefundOrderLineFactory = $payOutRefundOrderLineFactory;
        $this->payOutRefundResourceModel = $payOutRefundResourceModel;
        $this->payOutRefundOrderLineResourceModel = $payOutRefundOrderLineResourceModel;
        $this->miraklOrderUtil = $miraklOrderUtil;
        $this->orderUtil = $orderUtil;
    }

    /**
     * Save the Mirakl Refund Data in the database
     *
     * @param array $orderRefundData
     * @return void
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     */
    public function execute(array $orderRefundData): void
    {
        $miraklOrder = $this->miraklOrderUtil->getById($orderRefundData[CustomerRefund::ORDER_ID]);
        $orderCommercialId = $orderRefundData[CustomerRefund::ORDER_COMMERCIAL_ID];

        /** @var PayOutRefund $payoutRefund */
        $payoutRefund = $this->payOutRefundFactory->create();
        $storeId = $this->orderUtil->getOrderByIncrementId($orderCommercialId)->getStoreId();

        $payoutRefund->setMagentoStoreId((int)$storeId);
        $payoutRefund->setMiraklShopId((int)$miraklOrder->getShopId());
        $payoutRefund->setMagentoOrderId($miraklOrder->getCommercialId());
        $payoutRefund->setMiraklCurrencyIsoCode($miraklOrder->getCurrencyIsoCode());
        $payoutRefund->setMiraklOrderId($miraklOrder->getId());
        $payoutRefund->setFullyRefunded((bool)$miraklOrder->getData(PayOutRefund::FULLY_REFUNDED));

        $this->payOutRefundResourceModel->save($payoutRefund);

        $orderLines = $miraklOrder->getOrderLines();

        /** @var OrderLine $orderLine */
        foreach ($orderLines as $orderLine) {
            $refunds = $orderLine->getRefunds()->getItems();
            /** @var Refund $refund */
            foreach ($refunds as $refund) {
                /** @var PayOutRefundOrderLine $payoutRefundOrderLine */
                $payoutRefundOrderLine = $this->payOutRefundOrderLineFactory->create();

                $payoutRefundOrderLine->setPayoutRefundId((int)$payoutRefund->getId());
                $payoutRefundOrderLine->setAmount($refund->getAmount());
                $payoutRefundOrderLine->setTaxAmount($this->getTaxesFromMiraklRefund($refund->getTaxes()));
                $payoutRefundOrderLine->setCommissionAmount($refund->getCommissionAmount());
                $payoutRefundOrderLine->setCommissionTaxAmount($refund->getCommissionTaxAmount());
                $payoutRefundOrderLine->setCommissionTotalAmount($refund->getCommissionTotalAmount());
                $payoutRefundOrderLine->setQuantity($refund->getQuantity());
                $payoutRefundOrderLine->setShippingAmount($refund->getShippingAmount());

                $shippingTaxAmount = $this->getTaxesFromMiraklRefund($refund->getShippingTaxes());

                $payoutRefundOrderLine->setShippingTaxAmount($shippingTaxAmount);
                $payoutRefundOrderLine->setMiraklRefundId((int)$refund->getId());
                $payoutRefundOrderLine->setMiraklOrderLineId((int)$orderLine->getId());
                $payoutRefundOrderLine->setMiraklRefundState($refund->getState());
                $payoutRefundOrderLine->setStatus(1);

                $this->payOutRefundOrderLineResourceModel->save($payoutRefundOrderLine);
            }
        }
    }

    /**
     * Get a sum of the taxes inside the refund
     *
     * @param OrderTaxAmountCollection $taxes
     * @return float
     */
    private function getTaxesFromMiraklRefund(OrderTaxAmountCollection $taxes): float
    {
        $taxAmount = 0;

        /** @var OrderTaxAmount $tax */
        foreach ($taxes as $tax) {
            $taxAmount += $tax->getAmount();
        }

        return (float)$taxAmount;
    }
}
