<?php

declare(strict_types=1);

namespace MultiSafepay\Mirakl\Util;

use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\CollectionFactory as CustomerDebitCollectionFactory;

class CustomerDebitUtil
{
    /**
     * @var CustomerDebitCollectionFactory
     */
    private $customerDebitCollectionFactory;

    public function __construct(
        CustomerDebitCollectionFactory $customerDebitCollectionFactory
    ) {
        $this->customerDebitCollectionFactory = $customerDebitCollectionFactory;
    }

    /**
     * @param string $orderId
     * @return CustomerDebit
     * @throws NoSuchEntityException
     */
    public function getCustomerDebit(string $orderId): CustomerDebit
    {
        /** @var Collection $debitCollection */
        $debitCollection = $this->customerDebitCollectionFactory->create();
        $debitCollection->filterByOrderId($orderId);

        /** @var CustomerDebit $customerDebit */
        $customerDebit = $debitCollection->getFirstItem();

        if (!$customerDebit->getData()) {
            throw new NoSuchEntityException(__('No customer debit found with order ID: ' . $orderId));
        }

        return $customerDebit;
    }
}
