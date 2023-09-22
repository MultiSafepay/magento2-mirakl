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
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit as CustomerDebitRequestResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\CollectionFactory as CustomerDebitCollectionFactory;

class SetCustomerDebitAsProcessed implements ProcessInterface
{
    /**
     * @var CustomerDebitRequestResourceModel
     */
    private $customerDebitRequestResourceModel;

    /**
     * @var CustomerDebitCollectionFactory
     */
    private $customerDebitCollectionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param CustomerDebitRequestResourceModel $customerDebitRequestResourceModel
     * @param CustomerDebitCollectionFactory $customerDebitCollectionFactory
     * @param Logger $logger
     */
    public function __construct(
        CustomerDebitRequestResourceModel $customerDebitRequestResourceModel,
        CustomerDebitCollectionFactory $customerDebitCollectionFactory,
        Logger $logger
    ) {
        $this->customerDebitRequestResourceModel = $customerDebitRequestResourceModel;
        $this->customerDebitCollectionFactory = $customerDebitCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * Set the customer debit request as processed in database
     *
     * @param array $orderDebitData
     * @return void
     * @throws AlreadyExistsException
     */
    public function execute(array $orderDebitData): void
    {
        /** @var Collection $debitRequestCollection */
        $debitRequestCollection = $this->customerDebitCollectionFactory->create();

        /** @var CustomerDebit $customerDebitRequest */
        $customerDebitRequest = $debitRequestCollection->getItemById($orderDebitData[CustomerDebit::CUSTOMER_DEBIT_ID]);
        $customerDebitRequest->setStatus(CustomerDebit::CUSTOMER_DEBIT_STATUS_PROCESSED_SUCCESSFULLY);

        $this->customerDebitRequestResourceModel->save($customerDebitRequest);
    }

    /**
     * Set the customer debit request as processed in database with error
     *
     * @param array $orderDebitData
     * @param Exception $exception
     * @return void
     */
    public function withError(array $orderDebitData, Exception $exception): void
    {
        /** @var Collection $debitRequestCollection */
        $debitRequestCollection = $this->customerDebitCollectionFactory->create();

        /** @var CustomerDebit $customerDebitRequest */
        $customerDebitRequest = $debitRequestCollection->getItemById($orderDebitData[CustomerDebit::CUSTOMER_DEBIT_ID]);
        $customerDebitRequest->setStatus(CustomerDebit::CUSTOMER_DEBIT_STATUS_PROCESSED_WITH_ERRORS);
        $customerDebitRequest->setObservations($exception->getMessage());

        try {
            $this->customerDebitRequestResourceModel->save($customerDebitRequest);
        } catch (Exception|AlreadyExistsException $exception) {
            $this->logger->logCustomerDebitException(
                $orderDebitData,
                $exception
            );
        }
    }
}
