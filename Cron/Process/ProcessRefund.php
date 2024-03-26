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
use MultiSafepay\Exception\ApiException;
use MultiSafepay\Mirakl\Cron\Process\ProcessRefund\ChargeAccounts;
use MultiSafepay\Mirakl\Cron\Process\ProcessRefund\PrepareRefundData;
use MultiSafepay\Mirakl\Cron\Process\ProcessRefund\RefundTransaction;
use MultiSafepay\Mirakl\Cron\Process\ProcessRefund\SetOrderLinesAsProcessed;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use Psr\Http\Client\ClientExceptionInterface;

class ProcessRefund
{
    /**
     * @var RefundTransaction
     */
    private $refundTransaction;

    /**
     * @var PrepareRefundData
     */
    private $prepareRefundData;

    /**
     * @var SetOrderLinesAsProcessed
     */
    private $setOrderLinesAsProcessed;

    /**
     * @var ChargeAccounts
     */
    private $chargeAccounts;

    /**
     * @param RefundTransaction $refundTransaction
     * @param PrepareRefundData $prepareRefundData
     * @param SetOrderLinesAsProcessed $setOrderLinesAsProcessed
     * @param ChargeAccounts $chargeAccounts
     */
    public function __construct(
        RefundTransaction $refundTransaction,
        PrepareRefundData $prepareRefundData,
        SetOrderLinesAsProcessed $setOrderLinesAsProcessed,
        ChargeAccounts $chargeAccounts
    ) {
        $this->refundTransaction = $refundTransaction;
        $this->prepareRefundData = $prepareRefundData;
        $this->setOrderLinesAsProcessed = $setOrderLinesAsProcessed;
        $this->chargeAccounts = $chargeAccounts;
    }

    /**
     * Charge the amount from the seller and charge the commission amount
     *
     * @param array $orderRefundData
     * @return array
     * @throws NoSuchEntityException
     * @throws Exception
     * @throws ClientExceptionInterface
     * @throws ApiException
     */
    public function execute(array $orderRefundData): array
    {
        $chargeBack = $this->prepareRefundData->execute($orderRefundData);

        $this->chargeAccounts->execute($orderRefundData, $chargeBack);
        $this->setOrderLinesAsProcessed->execute($orderRefundData);
        return $this->refundTransaction->execute(
            $orderRefundData[CustomerRefund::ORDER_COMMERCIAL_ID],
            array_sum($chargeBack),
            $orderRefundData[CustomerRefund::CURRENCY_ISO_CODE]
        );
    }
}
