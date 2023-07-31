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
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Mirakl\Cron\Process\SavePayOutData\SavePayOut;
use MultiSafepay\Mirakl\Cron\Process\SavePayOutData\SavePayOutOrderLines;
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut\CollectionFactory as PayOutCollectionFactory;
use MultiSafepay\Mirakl\Util\MiraklOrderUtil;

class SavePayOutData implements ProcessInterface
{
    /**
     * @var PayOutCollectionFactory
     */
    private $payOutCollectionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var MiraklOrderUtil
     */
    private $miraklOrderUtil;

    /**
     * @var SavePayOut
     */
    private $savePayOut;

    /**
     * @var SavePayOutOrderLines
     */
    private $savePayOutOrderLines;

    /**
     * @param PayOutCollectionFactory $payOutCollectionFactory
     * @param Logger $logger
     * @param MiraklOrderUtil $miraklOrderUtil
     * @param SavePayOut $savePayOut
     * @param SavePayOutOrderLines $savePayOutOrderLines
     */
    public function __construct(
        PayOutCollectionFactory $payOutCollectionFactory,
        Logger $logger,
        MiraklOrderUtil $miraklOrderUtil,
        SavePayOut $savePayOut,
        SavePayOutOrderLines $savePayOutOrderLines
    ) {
        $this->payOutCollectionFactory = $payOutCollectionFactory;
        $this->logger = $logger;
        $this->miraklOrderUtil = $miraklOrderUtil;
        $this->savePayOut = $savePayOut;
        $this->savePayOutOrderLines = $savePayOutOrderLines;
    }

    /**
     * Getting the Magento order, and the Mirakl order to save the details about the payout in the database
     *
     * @param array $orderDebitData
     * @return void
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     */
    public function execute(array $orderDebitData): void
    {
        $miraklOrder = $this->miraklOrderUtil->getById($orderDebitData[CustomerDebit::ORDER_ID]);

        if ($this->hasPayOutRecord($miraklOrder->getId())) {
            $this->logger->logCronProcessInfo(
                'Skipping SavePayOutData action, since a previous record of this data has been found.',
                $orderDebitData
            );
            return;
        }

        $miraklPayOut = $this->savePayOut->execute($miraklOrder, $orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID]);

        $this->savePayOutOrderLines->execute($miraklOrder->getOrderLines()->getItems(), (int)$miraklPayOut->getId());
    }

    /**
     * Check if the payout record was saved previously to prevent save it twice
     *
     * @param string $miraklOrderId
     * @return bool
     */
    private function hasPayOutRecord(string $miraklOrderId): bool
    {
        /** @var Collection $payOutCollection */
        $payOutCollection = $this->payOutCollectionFactory->create();
        $results = $payOutCollection->filterByMiraklOrderId($miraklOrderId);

        if ($results->count() > 0) {
            return true;
        }

        return false;
    }
}
