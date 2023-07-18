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

namespace MultiSafepay\Mirakl\Cron\Process\SavePayOutData;

use Magento\Framework\Exception\AlreadyExistsException;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderLine;
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Model\PayOutOrderLineFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine as PayOutOrderLineResourceModel;

class SavePayOutOrderLines
{
    /**
     * @var PayOutOrderLineFactory
     */
    private $payOutOrderLineFactory;

    /**
     * @var PayOutOrderLineResourceModel
     */
    private $payOutOrderLineResourceModel;

    /**
     * @param PayOutOrderLineFactory $payOutOrderLineFactory
     * @param PayOutOrderLineResourceModel $payOutOrderLineResourceModel
     */
    public function __construct(
        PayOutOrderLineFactory $payOutOrderLineFactory,
        PayOutOrderLineResourceModel $payOutOrderLineResourceModel
    ) {
        $this->payOutOrderLineFactory = $payOutOrderLineFactory;
        $this->payOutOrderLineResourceModel = $payOutOrderLineResourceModel;
    }

    /**
     * Save the PayOut Orderlines
     *
     * @param array $orderLines
     * @param int $payOutId
     * @return void
     * @throws AlreadyExistsException
     */
    public function execute(array $orderLines, int $payOutId)
    {
        /** @var OrderLine $orderLine */
        foreach ($orderLines as $orderLine) {
            $totalTaxes = $this->getTaxesFromOrderLine($orderLine);
            $totalShippingTaxes = $this->getShippingTaxesFromOrderLine($orderLine);
            $totalPriceIncludingTaxes = $orderLine->getTotalPrice() + $totalTaxes + $totalShippingTaxes;
            $commission = $orderLine->getCommission()->getTotal();
            $sellerAmount = $totalPriceIncludingTaxes - $commission;
            $pricePerProduct = $orderLine->getPrice() / $orderLine->getQuantity();

            /** @var PayOutOrderLine $payOutOrderLine */
            $payOutOrderLine = $this->payOutOrderLineFactory->create();
            $payOutOrderLine->setPayoutId($payOutId);
            $payOutOrderLine->setProductPrice($pricePerProduct);
            $payOutOrderLine->setProductQuantity($orderLine->getQuantity());
            $payOutOrderLine->setProductTaxes($this->getTaxesFromOrderLine($orderLine));
            $payOutOrderLine->setShippingPrice($orderLine->getShippingPrice());
            $payOutOrderLine->setShippingTaxes($this->getShippingTaxesFromOrderLine($orderLine));
            $payOutOrderLine->setTotalPriceIncludingTaxes($totalPriceIncludingTaxes);
            $payOutOrderLine->setTotalPriceExcludingTaxes($orderLine->getTotalPrice());
            $payOutOrderLine->setOperatorAmount($commission);
            $payOutOrderLine->setSellerAmount($sellerAmount);
            $payOutOrderLine->setMiraklOrderStatus($orderLine->getStatus()->getState() ?? '');
            $payOutOrderLine->setStatus(1);
            $payOutOrderLine->setMiraklOrderLineId((int)$orderLine->getId());

            $this->payOutOrderLineResourceModel->save($payOutOrderLine);
        }
    }

    /**
     * Return the total taxes for the given Mirakl OrderLine
     *
     * @param OrderLine $orderLine
     * @return float
     */
    private function getTaxesFromOrderLine(OrderLine $orderLine): float
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
     * @param OrderLine $orderLine
     * @return float
     */
    private function getShippingTaxesFromOrderLine(OrderLine $orderLine): float
    {
        $taxes = 0;
        foreach ($orderLine->getShippingTaxes()->getItems() as $tax) {
            $taxes += (float)$tax->getAmount();
        }

        return $taxes;
    }
}
