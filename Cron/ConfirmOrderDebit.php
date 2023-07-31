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

namespace MultiSafepay\Mirakl\Cron;

use Exception;
use MultiSafepay\Mirakl\Cron\Process\ConfirmPayment;
use MultiSafepay\Mirakl\Cron\Process\ProcessFunds;
use MultiSafepay\Mirakl\Cron\Process\SavePayOutData;
use MultiSafepay\Mirakl\Cron\Process\SendOrderDebitConfirmation;
use MultiSafepay\Mirakl\Cron\Process\SetCustomerDebitAsProcessed;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerDebitFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit\CollectionFactory as CustomerDebitCollectionFactory;

/**
 * This class uses a collection of the pending debit requests and tries to process them following a defined schedule
 *
 * @see https://help.mirakl.net/bundle/customers/page/topics/Mirakl/PSP_Project/topics/collecting_pending_debits_psp.htm
 */
class ConfirmOrderDebit
{
    /**
     * @var CustomerDebitCollectionFactory
     */
    private $customerDebitCollectionFactory;

    /**
     * @var ConfirmPayment
     */
    private $confirmPayment;

    /**
     * @var SavePayOutData
     */
    private $savePayOutData;

    /**
     * @var ProcessFunds
     */
    private $processFunds;

    /**
     * @var SendOrderDebitConfirmation
     */
    private $sendOrderDebitConfirmation;

    /**
     * @var SetCustomerDebitAsProcessed
     */
    private $setCustomerDebitAsProcessed;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param CustomerDebitCollectionFactory $customerDebitCollectionFactory
     * @param ConfirmPayment $confirmPayment
     * @param SavePayOutData $savePayOutData
     * @param ProcessFunds $processFunds
     * @param SendOrderDebitConfirmation $sendOrderDebitConfirmation
     * @param SetCustomerDebitAsProcessed $setCustomerDebitAsProcessed
     * @param Logger $logger
     */
    public function __construct(
        CustomerDebitCollectionFactory $customerDebitCollectionFactory,
        ConfirmPayment $confirmPayment,
        SavePayOutData $savePayOutData,
        ProcessFunds $processFunds,
        SendOrderDebitConfirmation $sendOrderDebitConfirmation,
        SetCustomerDebitAsProcessed $setCustomerDebitAsProcessed,
        Logger $logger
    ) {
        $this->customerDebitCollectionFactory = $customerDebitCollectionFactory;
        $this->confirmPayment = $confirmPayment;
        $this->savePayOutData = $savePayOutData;
        $this->processFunds = $processFunds;
        $this->sendOrderDebitConfirmation = $sendOrderDebitConfirmation;
        $this->setCustomerDebitAsProcessed = $setCustomerDebitAsProcessed;
        $this->logger = $logger;
    }

    /**
     * List customer debit requests pending to be processed to check the payment status,
     * transfer funds, and confirm the payment in Mirakl
     *
     * @return void
     */
    public function execute(): void
    {
        $processes = [
            $this->confirmPayment,
            $this->savePayOutData,
            $this->processFunds,
            $this->sendOrderDebitConfirmation,
            $this->setCustomerDebitAsProcessed
        ];

        /** @var Collection $debitCollection */
        $debitCollection = $this->customerDebitCollectionFactory->create();
        $debitCollection->filterByStatus(1);

        foreach ($debitCollection->getItems() as $debitRequest) {
            foreach ($processes as $process) {
                $this->logger->logCronProcessStep(get_class($process), $debitRequest->getData(), 'Process started');

                try {
                    $process->execute($debitRequest->getData());
                } catch (Exception $exception) {
                    $this->logger->logCronProcessException(
                        get_class($process),
                        $debitRequest->getData(),
                        $exception
                    );
                    break;
                }

                $this->logger->logCronProcessStep(get_class($process), $debitRequest->getData(), 'Process ended');
            }
        }
    }
}
