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
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine as PayOutOrderLineResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine\CollectionFactory as PayOutOrderLineCollectionFactory;

class PayOutOrderLineUtil
{
    /**
     * @var PayOutOrderLineCollectionFactory
     */
    private $payOutOrderLineCollectionFactory;

    /**
     * @var PayOutOrderLineResourceModel
     */
    private $payOutOrderLineResourceModel;

    /**
     * @param PayOutOrderLineCollectionFactory $payOutOrderLineCollectionFactory
     * @param PayOutOrderLineResourceModel $payOutOrderLineResourceModel
     */
    public function __construct(
        PayOutOrderLineCollectionFactory $payOutOrderLineCollectionFactory,
        PayOutOrderLineResourceModel $payOutOrderLineResourceModel
    ) {
        $this->payOutOrderLineCollectionFactory = $payOutOrderLineCollectionFactory;
        $this->payOutOrderLineResourceModel = $payOutOrderLineResourceModel;
    }

    /**
     * Set a PayOutOrderLine as processed
     *
     * @param int $order_line_id
     * @return void
     * @throws AlreadyExistsException
     */
    public function setOrderLineAsProcessed(int $order_line_id): void
    {
        /** @var PayOutOrderLineCollectionFactory $payOutOrderLineCollectionFactory */
        $payOutOrderLineCollectionFactory = $this->payOutOrderLineCollectionFactory->create();

        /** @var PayOutOrderLine $payOutOrderLine */
        $payOutOrderLine = $payOutOrderLineCollectionFactory->getItemById($order_line_id);
        $payOutOrderLine->setStatus(0);
        $this->payOutOrderLineResourceModel->save($payOutOrderLine);
    }
}
