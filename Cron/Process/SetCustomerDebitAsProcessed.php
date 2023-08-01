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
use MultiSafepay\Mirakl\Cron\ProcessInterface;
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
     * @param CustomerDebitRequestResourceModel $customerDebitRequestResourceModel
     * @param CustomerDebitCollectionFactory $customerDebitCollectionFactory
     */
    public function __construct(
        CustomerDebitRequestResourceModel $customerDebitRequestResourceModel,
        CustomerDebitCollectionFactory $customerDebitCollectionFactory
    ) {
        $this->customerDebitRequestResourceModel = $customerDebitRequestResourceModel;
        $this->customerDebitCollectionFactory = $customerDebitCollectionFactory;
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
        $customerDebitRequest->setStatus(0);

        $this->customerDebitRequestResourceModel->save($customerDebitRequest);
    }
}
