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

use Exception;
use Magento\Framework\Exception\AlreadyExistsException;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund as CustomerRefundResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund\CollectionFactory;

class SetRefundAsProcessed
{
    /**
     * @var CustomerRefundResourceModel
     */
    private $customerRefundResourceModel;

    /**
     * @var CollectionFactory
     */
    private $customerRefundCollectionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param CollectionFactory $customerRefundCollectionFactory
     * @param CustomerRefundResourceModel $customerRefundResourcemodel
     * @param Logger $logger
     */
    public function __construct(
        CollectionFactory $customerRefundCollectionFactory,
        CustomerRefundResourceModel $customerRefundResourcemodel,
        Logger $logger
    ) {
        $this->customerRefundCollectionFactory = $customerRefundCollectionFactory;
        $this->customerRefundResourceModel = $customerRefundResourcemodel;
        $this->logger = $logger;
    }

    /**
     * Set the customer refund status
     *
     * @param array $orderRefundData
     * @return void
     * @throws AlreadyExistsException
     */
    public function execute(array $orderRefundData)
    {
        /** @var Collection $refundRequestCollection */
        $refundRequestCollection = $this->customerRefundCollectionFactory->create();

        /** @var CustomerRefund $customerRefundRequest */
        $customerRefundRequest = $refundRequestCollection->getItemById(
            $orderRefundData[CustomerRefund::CUSTOMER_REFUND_ID]
        );
        $customerRefundRequest->setStatus(CustomerRefund::CUSTOMER_REFUND_STATUS_PROCESSED_SUCCESSFULLY);

        $this->customerRefundResourceModel->save($customerRefundRequest);
    }

    /**
     * Set the customer debit request as processed in database with error
     *
     * @param array $orderRefundData
     * @param Exception $exception
     * @return void
     */
    public function withError(array $orderRefundData, Exception $exception): void
    {
        /** @var Collection $refundRequestCollection */
        $refundRequestCollection = $this->customerRefundCollectionFactory->create();

        /** @var CustomerRefund $customerRefundRequest */
        $customerRefundRequest = $refundRequestCollection->getItemById(
            $orderRefundData[CustomerRefund::CUSTOMER_REFUND_ID]
        );
        $customerRefundRequest->setStatus(CustomerRefund::CUSTOMER_REFUND_STATUS_PROCESSED_WITH_ERRORS);
        $customerRefundRequest->setObservations($exception->getMessage());

        try {
            $this->customerRefundResourceModel->save($customerRefundRequest);
        } catch (Exception|AlreadyExistsException $exception) {
            $this->logger->logRefundException($orderRefundData, $exception);
        }
    }
}
