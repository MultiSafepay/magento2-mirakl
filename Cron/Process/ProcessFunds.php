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
use Mirakl\MMP\Common\Domain\Order\OrderState;
use MultiSafepay\Mirakl\Cron\Process\ProcessFunds\Refund;
use MultiSafepay\Mirakl\Cron\Process\ProcessFunds\TransferFunds;
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Model\PayOut;
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut\CollectionFactory as PayOutCollectionFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut\Collection as PayOutCollection;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine\CollectionFactory as PayOutOrderLineCollectionFactory;
use Psr\Http\Client\ClientExceptionInterface;

class ProcessFunds implements ProcessInterface
{
    /**
     * @var PayOutCollectionFactory
     */
    private $payOutCollectionFactory;

    /**
     * @var PayOutOrderLineCollectionFactory
     */
    private $payOutOrderLineCollectionFactory;

    /**
     * @var Refund
     */
    private $refund;

    /**
     * @var TransferFunds
     */
    private $transferFunds;

    /**
     * @param PayOutCollectionFactory $payOutCollectionFactory
     * @param PayOutOrderLineCollectionFactory $payOutOrderLineCollectionFactory
     * @param Refund $refund
     * @param TransferFunds $transferFunds
     */
    public function __construct(
        PayOutCollectionFactory $payOutCollectionFactory,
        PayOutOrderLineCollectionFactory $payOutOrderLineCollectionFactory,
        Refund $refund,
        TransferFunds $transferFunds
    ) {
        $this->payOutCollectionFactory = $payOutCollectionFactory;
        $this->payOutOrderLineCollectionFactory = $payOutOrderLineCollectionFactory;
        $this->refund = $refund;
        $this->transferFunds = $transferFunds;
    }

    /**
     * Process the fund transaction to the seller and operator, and the refund if needed
     *
     * @param array $orderDebitData
     * @return void
     * @throws Exception
     * @throws ClientExceptionInterface
     */
    public function execute(array $orderDebitData): void
    {
        /** @var PayOutCollection $payOutCollection */
        $payOutCollection = $this->payOutCollectionFactory->create();
        $payOutCollection->withOrderLines();
        $items = $payOutCollection->getItemsByColumnValue(PayOut::MIRAKL_ORDER_ID, $orderDebitData['order_id']);

        $data = $this->buildDataFromItems($items);

        // Refund
        $this->refund->execute($data);

        // Transfer funds
        $this->transferFunds->execute($data);
    }

    /**
     * Build all the necessary data from the PayOutCollection items
     *
     * @param array $items
     * @return array
     */
    private function buildDataFromItems(array $items): array
    {
        $payOutData = [];
        $refundData = [];
        $transferFundsData = [];

        /** @var PayOut $payOutItem */
        foreach ($items as $payOutItem) {
            if ($payOutItem === reset($items)) {
                $payOutData = $this->buildPayOutData($payOutItem, $payOutData);
            }

            $orderLines = $payOutItem->getOrderLines();
            foreach ($orderLines as $orderLine) {
                if ($orderLine[PayOutOrderLine::STATUS] !== '1') {
                    continue;
                }

                if ($orderLine[PayOutOrderLine::MIRAKL_ORDER_STATUS] === OrderState::REFUSED) {
                    $refundData = $this->refund->buildData($orderLine, $refundData);
                }

                if ($orderLine[PayOutOrderLine::MIRAKL_ORDER_STATUS] === OrderState::SHIPPED ||
                    $orderLine[PayOutOrderLine::MIRAKL_ORDER_STATUS] === OrderState::WAITING_DEBIT_PAYMENT) {
                    $transferFundsData = $this->transferFunds->buildData($orderLine, $transferFundsData);
                }
            }
        }

        return [
            'payOutData' => $payOutData,
            'refundData' => $refundData,
            'transferFundsData' => $transferFundsData
        ];
    }

    /**
     * Build the PayOutData that contains values that will be used in both the refund and transfer funds processes
     *
     * @param PayOut $payOutItem
     * @param array $payOutData
     * @return array
     */
    private function buildPayOutData(PayOut $payOutItem, array $payOutData): array
    {
        $payOutData['currency_iso_code'] = $payOutItem->getMiraklCurrencyIsoCode();
        $payOutData['shop_id'] = $payOutItem->getMiraklShopId();
        $payOutData['store_id'] = $payOutItem->getMagentoStoreId();
        $payOutData['order_id'] = $payOutItem->getMiraklOrderId();
        $payOutData['order_commercial_id'] = $payOutItem->getMagentoOrderId();

        return $payOutData;
    }
}
