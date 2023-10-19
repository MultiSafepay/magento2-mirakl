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

use Magento\Framework\Exception\AlreadyExistsException;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\PayOutRefundOrderLine;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine\Collection as PayOutRefundOrderLineCollection;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine\CollectionFactory as PayOutRefundOrderLineFactory;
use MultiSafepay\Mirakl\Util\PayOutRefundOrderLineUtil;

class SetOrderLinesAsProcessed
{
    /**
     * @var PayOutRefundOrderLineFactory
     */
    private $payOutRefundOrderLineCollectionFactory;

    /**
     * @var PayOutRefundOrderLineUtil
     */
    private $payOutRefundOrderLineUtil;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param PayOutRefundOrderLineFactory $payOutRefundOrderLineCollectionFactory
     * @param PayOutRefundOrderLineUtil $payOutRefundOrderLineUtil
     * @param Logger $logger
     */
    public function __construct(
        PayOutRefundOrderLineFactory $payOutRefundOrderLineCollectionFactory,
        PayOutRefundOrderLineUtil $payOutRefundOrderLineUtil,
        Logger $logger
    ) {
        $this->payOutRefundOrderLineCollectionFactory = $payOutRefundOrderLineCollectionFactory;
        $this->payOutRefundOrderLineUtil = $payOutRefundOrderLineUtil;
        $this->logger = $logger;
    }

    /**
     * Set the payout refund order lines as processed
     *
     * @param array $orderRefundData
     * @return void
     * @throws AlreadyExistsException
     */
    public function execute(array $orderRefundData)
    {
        foreach ($orderRefundData['order_lines'] as $orderLine) {
            /** @var PayOutRefundOrderLineCollection $payOutRefundOrderLineCollection */
            $payOutRefundOrderLineCollection = $this->payOutRefundOrderLineCollectionFactory->create();
            $payOutRefundOrderLineCollection->filterByMiraklRefundId($orderLine['order_line_refund_id']);

            /** @var PayOutRefundOrderLine $payOutRefundOrderLine */
            foreach ($payOutRefundOrderLineCollection as $payOutRefundOrderLine) {
                if (!$payOutRefundOrderLine->getStatus()) {
                    continue;
                }

                $this->payOutRefundOrderLineUtil->setOrderLineAsProcessed($payOutRefundOrderLine->getId());

                $this->logger->logCronProcessInfo(
                    'multisafepay_mirakl_payout_refund_order_line status updated',
                    $orderLine
                );
            }
        }
    }
}
