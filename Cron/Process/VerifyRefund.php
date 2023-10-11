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
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Mirakl\Exception\CronProcessException;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\CollectionFactory as CustomerDebitCollectionFactory;

class VerifyRefund
{
    /**
     * @var CustomerDebitCollectionFactory
     */
    private $customerDebitCollectionFactory;

    /**
     * @param CustomerDebitCollectionFactory $customerDebitCollectionFactory
     */
    public function __construct(
        CustomerDebitCollectionFactory $customerDebitCollectionFactory
    ) {
        $this->customerDebitCollectionFactory = $customerDebitCollectionFactory;
    }

    /**
     * Check if the refund can be processed
     *
     * @throws Exception
     */
    public function execute(array $refundRequestData)
    {
        /** @var Collection $debitCollection */
        $debitCollection = $this->customerDebitCollectionFactory->create();
        $debitCollection->filterByOrderId($refundRequestData[CustomerRefund::ORDER_ID]);

        /** @var CustomerDebit $customerDebit */
        $customerDebit = $debitCollection->getFirstItem();

        if (!$customerDebit) {
            throw new NoSuchEntityException(__(
                'Refund can not be processed, unable to find CustomerDebit with Order ID:' .
                $refundRequestData[CustomerRefund::ORDER_ID]
            ));
        }

        if ($customerDebit->getStatus() !== CustomerDebit::CUSTOMER_DEBIT_STATUS_PROCESSED_SUCCESSFULLY) {
            throw new CronProcessException(
                'Refund can not be processed, wrong CustomerDebit status: ' . $customerDebit->getStatus()
            );
        }
    }
}
