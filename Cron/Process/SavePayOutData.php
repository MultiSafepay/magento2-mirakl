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
use Mirakl\MMP\FrontOperator\Domain\Collection\Order\OrderCollection;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;
use Mirakl\MMP\FrontOperator\Domain\Order\OrderLine;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient as MiraklFrontApiClient;
use MultiSafepay\Mirakl\Api\Mirakl\Request\OrderRequest as OrderRequestFactory;
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\PayOut;
use MultiSafepay\Mirakl\Model\PayOutFactory;
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Model\PayOutOrderLineFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut as PayOutResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine as PayOutOrderLineResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut\CollectionFactory as PayOutCollectionFactory;

class SavePayOutData implements ProcessInterface
{
    /**
     * @var OrderUtil;
     */
    private $orderUtil;

    /**
     * @var OrderRequestFactory
     */
    private $orderRequestFactory;

    /**
     * @var MiraklFrontApiClient
     */
    private $miraklFrontApiClient;

    /**
     * @var PayOutFactory
     */
    private $payOutFactory;

    /**
     * @var PayOutResourceModel
     */
    private $payOutResourceModel;

    /**
     * @var PayOutOrderLineFactory
     */
    private $payOutOrderLineFactory;

    /**
     * @var PayOutOrderLineResourceModel
     */
    private $payOutOrderLineResourceModel;

    /**
     * @var PayOutCollectionFactory
     */
    private $payOutCollectionFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param OrderUtil $orderUtil
     * @param OrderRequestFactory $orderRequestFactory
     * @param MiraklFrontApiClient $miraklFrontApiClient
     * @param PayOutFactory $payOutFactory
     * @param PayOutResourceModel $payOutResourceModel
     * @param PayOutOrderLineFactory $payOutOrderLineFactory
     * @param PayOutOrderLineResourceModel $payOutOrderLineResourceModel
     * @param PayOutCollectionFactory $payOutCollectionFactory
     * @param Logger $logger
     */
    public function __construct(
        OrderUtil $orderUtil,
        OrderRequestFactory $orderRequestFactory,
        MiraklFrontApiClient $miraklFrontApiClient,
        PayOutFactory $payOutFactory,
        PayOutResourceModel $payOutResourceModel,
        PayOutOrderLineFactory $payOutOrderLineFactory,
        PayOutOrderLineResourceModel $payOutOrderLineResourceModel,
        PayOutCollectionFactory $payOutCollectionFactory,
        Logger $logger
    ) {
        $this->orderUtil = $orderUtil;
        $this->orderRequestFactory = $orderRequestFactory;
        $this->miraklFrontApiClient = $miraklFrontApiClient;
        $this->payOutFactory = $payOutFactory;
        $this->payOutResourceModel = $payOutResourceModel;
        $this->payOutOrderLineFactory = $payOutOrderLineFactory;
        $this->payOutOrderLineResourceModel = $payOutOrderLineResourceModel;
        $this->payOutCollectionFactory = $payOutCollectionFactory;
        $this->logger = $logger;
    }

    /**
     * @param array $orderDebitData
     * @return array|true[]
     */
    public function execute(array $orderDebitData): array
    {
        // Getting the Magento Order
        try {
            $order = $this->orderUtil->getOrderByIncrementId(
                $orderDebitData[CustomerDebit::ORDER_COMMERCIAL_ID]
            );
        } catch (NoSuchEntityException $noSuchEntityException) {
            return [
                ProcessInterface::SUCCESS_PARAMETER => false,
                ProcessInterface::MESSAGE_PARAMETER => $noSuchEntityException->getMessage()
            ];
        }

        try {
            $miraklOrders = $this->getMiraklOrder(
                $orderDebitData[CustomerDebit::ORDER_ID]
            );
        } catch (NoSuchEntityException $noSuchEntityException) {
            return [
                ProcessInterface::SUCCESS_PARAMETER => false,
                ProcessInterface::MESSAGE_PARAMETER => $noSuchEntityException->getMessage()
            ];
        }

        /** @var MiraklOrder $miraklOrder */
        foreach ($miraklOrders->getItems() as $miraklOrder) {
            if (!$this->hasPayOutRecord($miraklOrder->getId())) {

                try {
                    $miraklPayOut = $this->saveMiraklOrderPayOut($miraklOrder, (int)$order->getStoreId());
                } catch (AlreadyExistsException $alreadyExistsException) {
                    return [
                        ProcessInterface::SUCCESS_PARAMETER => false,
                        ProcessInterface::MESSAGE_PARAMETER => $alreadyExistsException->getMessage()
                    ];
                }

                /** @var OrderLine $orderLine */
                foreach ($miraklOrder->getOrderLines()->getItems() as $orderLine) {
                    try {
                        $this->saveMiraklOrderLinePayOut(
                            $orderLine,
                            (int)$miraklPayOut->getId()
                        );
                    } catch (AlreadyExistsException $alreadyExistsException) {
                        return [
                            ProcessInterface::SUCCESS_PARAMETER => false,
                            ProcessInterface::MESSAGE_PARAMETER => $alreadyExistsException->getMessage()
                        ];
                    }
                }
            }
        }

        return [ProcessInterface::SUCCESS_PARAMETER => true];
    }

    /**
     * @param MiraklOrder $miraklOrder
     * @param int $store_id
     * @return PayOut
     * @throws AlreadyExistsException
     */
    private function saveMiraklOrderPayOut(MiraklOrder $miraklOrder, int $store_id): PayOut
    {
        /** @var PayOut $payOut */
        $payOut = $this->payOutFactory->create();
        $payOut->setMagentoShopId($store_id);
        $payOut->setMiraklShopId($miraklOrder->getShopId());
        $payOut->setMagentoOrderId($miraklOrder->getCommercialId());
        $payOut->setMiraklOrderId($miraklOrder->getId());

        $this->payOutResourceModel->save($payOut);

        return $payOut;
    }

    /**
     * @param OrderLine $orderLine
     * @param int $payOutId
     * @return PayOutOrderLine
     * @throws AlreadyExistsException
     */
    public function saveMiraklOrderLinePayOut(OrderLine $orderLine, int $payOutId): PayOutOrderLine
    {
        $totalTaxes = $this->getTaxesFromOrderLine($orderLine);
        $totalShippingTaxes = $this->getShippingTaxesFromOrderLine($orderLine);
        $totalPriceIncludingTaxes = $orderLine->getTotalPrice() + $totalTaxes + $totalShippingTaxes;
        $commission = $orderLine->getCommission()->getTotal();
        $sellerAmount = $totalPriceIncludingTaxes - $orderLine->getCommission()->getTotal();
        $pricePerProduct = $orderLine->getPrice() / $orderLine->getQuantity();

        /** @var PayOutOrderLine $payOutOrderLine */
        $payOutOrderLine = $this->payOutOrderLineFactory->create();
        $payOutOrderLine->setPayoutId($payOutId);
        $payOutOrderLine->setProductPrice($pricePerProduct);
        $payOutOrderLine->setProductQuantity($orderLine->getQuantity());
        $payOutOrderLine->setProductTaxes($this->getTaxesFromOrderLine($orderLine));
        $payOutOrderLine->setShippingPrice($orderLine->getShippingPrice());
        $payOutOrderLine->setShippingTaxes($this->getShippingTaxesFromOrderLine($orderLine));
        $payOutOrderLine->setTotalPriceIncludingTaxes($totalPriceIncludingTaxes);
        $payOutOrderLine->setTotalPriceExcludingTaxes($orderLine->getTotalPrice());
        $payOutOrderLine->setOperatorAmount($commission);
        $payOutOrderLine->setSellerAmount($sellerAmount);
        $payOutOrderLine->setMiraklOrderStatus($orderLine->getStatus()->getState() ?? '');
        $payOutOrderLine->setStatus(1);
        $payOutOrderLine->setMiraklOrderLineId((int)$orderLine->getId());

        $this->payOutOrderLineResourceModel->save($payOutOrderLine);

        return $payOutOrderLine;
    }

    /**
     * @param $miraklOrderId
     * @return OrderCollection
     * @throws NoSuchEntityException
     */
    private function getMiraklOrder($miraklOrderId): OrderCollection
    {
        try {
            $miraklGetOrderRequest = $this->orderRequestFactory->getById($miraklOrderId);
            $miraklFrontApiClient = $this->miraklFrontApiClient->get();
            $miraklOrder = $miraklFrontApiClient->getOrders($miraklGetOrderRequest);
        } catch (\Exception $exception) {
            throw new NoSuchEntityException(__('Requested order doesn\'t exist'));
        }

        return $miraklOrder;
    }

    private function getTaxesFromOrderLine(OrderLine $orderLine): float
    {
        $taxes = 0;
        foreach ($orderLine->getTaxes() as $tax) {
            $taxes += (float)$tax->getAmount();
        }

        return $taxes;
    }

    private function getShippingTaxesFromOrderLine(OrderLine $orderLine): float
    {
        $taxes = 0;
        foreach ($orderLine->getShippingTaxes()->getItems() as $tax) {
            $taxes += (float)$tax->getAmount();
        }

        return $taxes;
    }

    /**
     * @param string $miraklOrderId
     * @return bool
     */
    private function hasPayOutRecord(string $miraklOrderId): bool
    {
        /** @var Collection $payOutCollection */
        $payOutCollection = $this->payOutCollectionFactory->create();
        $results = $payOutCollection->filterByMiraklOrderId($miraklOrderId);

        if ((int)$results->count() > 0) {
            return true;
        }
        return false;
    }
}
