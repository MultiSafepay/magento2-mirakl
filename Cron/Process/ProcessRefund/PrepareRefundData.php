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

namespace MultiSafepay\Mirakl\Cron\Process\ProcessRefund;

use MultiSafepay\Mirakl\Model\PayOutRefundOrderLine;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine\Collection as PayOutRefundOrderLineCollection;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine\CollectionFactory as PayOutRefundOrderLineFactory;

class PrepareRefundData
{
    public const SELLER_CHARGEBACK_AMOUNT = 'seller_chargeback_amount';
    public const COMMISSION_CHARGEBACK_AMOUNT = 'commission_chargeback_amount';

    /**
     * @var PayOutRefundOrderLineFactory
     */
    private $payOutRefundOrderLineCollectionFactory;

    /**
     * @param PayOutRefundOrderLineFactory $payOutRefundOrderLineCollectionFactory
     */
    public function __construct(
        PayOutRefundOrderLineFactory $payOutRefundOrderLineCollectionFactory
    ) {
        $this->payOutRefundOrderLineCollectionFactory = $payOutRefundOrderLineCollectionFactory;
    }

    /**
     * @param array $orderRefundData
     * @return array
     */
    public function execute(array $orderRefundData): array
    {
        $amountsToChargeBackToSeller = [];
        $amountsToChargeBackCommission = [];

        foreach ($orderRefundData['order_lines'] as $orderLine) {
            /** @var PayOutRefundOrderLineCollection $payOutRefundOrderLineCollection */
            $payOutRefundOrderLineCollection = $this->payOutRefundOrderLineCollectionFactory->create();
            $payOutRefundOrderLineCollection->filterByMiraklRefundId($orderLine['order_line_refund_id']);

            /** @var PayOutRefundOrderLine $payOutRefundOrderLine */
            foreach ($payOutRefundOrderLineCollection as $payOutRefundOrderLine) {
                if (!$payOutRefundOrderLine->getStatus()) {
                    continue;
                }

                $amount = $payOutRefundOrderLine->getAmount();
                $taxAmount = $payOutRefundOrderLine->getTaxAmount();
                $shippingAmount = $payOutRefundOrderLine->getShippingAmount();
                $shippingTaxAmount = $payOutRefundOrderLine->getShippingTaxAmount();

                $totalAmount = $amount + $taxAmount;
                $totalShippingAmount = $shippingAmount + $shippingTaxAmount;
                $commissionTotalAmount = $payOutRefundOrderLine->getCommissionTotalAmount();

                $amountsToChargeBackToSeller[] = ($totalAmount + $totalShippingAmount) - $commissionTotalAmount;
                $amountsToChargeBackCommission[] = $commissionTotalAmount;
            }
        }

        $totalSellerChargeBackAmount = array_sum($amountsToChargeBackToSeller);
        $totalCommissionChargeBackAmount = array_sum($amountsToChargeBackCommission);

        return [
            self::SELLER_CHARGEBACK_AMOUNT => $totalSellerChargeBackAmount,
            self::COMMISSION_CHARGEBACK_AMOUNT => $totalCommissionChargeBackAmount
        ];
    }
}
