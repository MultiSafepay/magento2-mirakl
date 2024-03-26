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
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Exception\ApiException;
use MultiSafepay\Mirakl\Cron\Process\ProcessRefund;
use MultiSafepay\Mirakl\Cron\Process\SavePayOutRefundData;
use MultiSafepay\Mirakl\Cron\Process\SendRefundConfirmation;
use MultiSafepay\Mirakl\Cron\Process\SetRefundAsProcessed;
use MultiSafepay\Mirakl\Cron\Process\VerifyRefund;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund\CollectionFactory as CustomerRefundCollectionFactory;
use Psr\Http\Client\ClientExceptionInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfirmOrderRefund
{
    /**
     * @var CustomerRefundCollectionFactory
     */
    private $customerRefundCollectionFactory;

    /**
     * @var SavePayOutRefundData
     */
    private $savePayOutRefundData;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var ProcessRefund
     */
    private $processRefund;

    /**
     * @var VerifyRefund
     */
    private $verifyRefund;

    /**
     * @var SendRefundConfirmation
     */
    private $sendRefundConfirmation;

    /**
     * @var SetRefundAsProcessed
     */
    private $setRefundAsProcessed;

    /**
     * @param CustomerRefundCollectionFactory $customerRefundCollectionFactory
     * @param SavePayOutRefundData $savePayOutRefundData
     * @param ProcessRefund $processRefund
     * @param Logger $logger
     * @param VerifyRefund $verifyRefund
     * @param SendRefundConfirmation $sendRefundConfirmation
     * @param SetRefundAsProcessed $setRefundAsProcessed
     */
    public function __construct(
        CustomerRefundCollectionFactory $customerRefundCollectionFactory,
        SavePayOutRefundData $savePayOutRefundData,
        ProcessRefund $processRefund,
        Logger $logger,
        VerifyRefund $verifyRefund,
        SendRefundConfirmation $sendRefundConfirmation,
        SetRefundAsProcessed $setRefundAsProcessed
    ) {
        $this->customerRefundCollectionFactory = $customerRefundCollectionFactory;
        $this->savePayOutRefundData = $savePayOutRefundData;
        $this->processRefund = $processRefund;
        $this->logger = $logger;
        $this->verifyRefund = $verifyRefund;
        $this->sendRefundConfirmation = $sendRefundConfirmation;
        $this->setRefundAsProcessed = $setRefundAsProcessed;
    }

    /**
     * Process the refunds
     *
     * @return void
     */
    public function execute()
    {
        $processes = [
            $this->verifyRefund,
            $this->savePayOutRefundData,
            $this->processRefund,
            $this->sendRefundConfirmation,
            $this->setRefundAsProcessed
        ];

        /** @var Collection $refundCollection */
        $refundCollection = $this->customerRefundCollectionFactory->create();
        $refundCollection->filterByStatus(CustomerRefund::CUSTOMER_REFUND_STATUS_PENDING_TO_BE_PROCESSED)
            ->withOrderLines();

        $refundResponse = [];

        foreach ($refundCollection->getItems() as $refundRequest) {
            foreach ($processes as $process) {
                $this->logger->logCronProcessStep(get_class($process), $refundRequest->getData(), 'Process started');

                try {
                    switch ($process) {
                        case $this->processRefund:
                            $refundResponse = $this->processRefund->execute($refundRequest->getData());
                            break;

                        case $this->sendRefundConfirmation:
                            $this->sendRefundConfirmation->execute($refundRequest->getData(), $refundResponse);
                            break;

                        default:
                            $process->execute($refundRequest->getData());
                    }
                } catch (AlreadyExistsException|NoSuchEntityException|Exception|ClientExceptionInterface|
                ApiException $e) {
                    $this->logger->logCronProcessException(get_class($process), $refundRequest->getData(), $e);
                    $this->setRefundAsProcessed->withError($refundRequest->getData(), $e);

                    break;
                }

                $this->logger->logCronProcessStep(get_class($process), $refundRequest->getData(), 'Process ended');
            }
        }
    }
}
