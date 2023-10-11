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

namespace MultiSafepay\Mirakl\Util;

use Magento\Framework\Exception\AlreadyExistsException;
use MultiSafepay\Mirakl\Model\PayOutRefundOrderLine;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine as PayOutRefundOrderLineResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefundOrderLine\CollectionFactory as PayOutRefundOrderLineFactory;

class PayOutRefundOrderLineUtil
{
    /**
     * @var PayOutRefundOrderLineFactory
     */
    private $payOutRefundOrderLineCollectionFactory;

    /**
     * @var PayOutRefundOrderLineResourceModel
     */
    private $payOutRefundOrderLineResourceModel;

    /**
     * @param PayOutRefundOrderLineFactory $payOutRefundOrderLineCollectionFactory
     * @param PayOutRefundOrderLineResourceModel $payOutRefundOrderLineResourceModel
     */
    public function __construct(
        PayOutRefundOrderLineFactory $payOutRefundOrderLineCollectionFactory,
        PayOutRefundOrderLineResourceModel $payOutRefundOrderLineResourceModel
    ) {
        $this->payOutRefundOrderLineCollectionFactory = $payOutRefundOrderLineCollectionFactory;
        $this->payOutRefundOrderLineResourceModel = $payOutRefundOrderLineResourceModel;
    }

    /**
     * Set a PayOutOrderLine as processed
     *
     * @param string $orderLineId
     * @return void
     * @throws AlreadyExistsException
     */
    public function setOrderLineAsProcessed(string $orderLineId): void
    {
        /** @var PayOutRefundOrderLineFactory $payOutRefundOrderLineCollectionFactory */
        $payOutRefundOrderLineCollectionFactory = $this->payOutRefundOrderLineCollectionFactory->create();

        /** @var PayOutRefundOrderLine $payOutRefundOrderLine */
        $payOutRefundOrderLine = $payOutRefundOrderLineCollectionFactory->getItemById($orderLineId);
        $payOutRefundOrderLine->setStatus(0);
        $this->payOutRefundOrderLineResourceModel->save($payOutRefundOrderLine);
    }
}
