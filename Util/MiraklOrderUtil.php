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

namespace MultiSafepay\Mirakl\Util;

use Mirakl\MMP\FrontOperator\Domain\Order;
use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient;
use MultiSafepay\Mirakl\Api\Mirakl\Request\OrderRequest as OrderRequestFactory;
use MultiSafepay\Mirakl\Api\Mirakl\Request\CancelOrderRequest as CancelOrderRequestFactory;

class MiraklOrderUtil
{
    /**
     * @var OrderRequestFactory
     */
    private $orderRequestFactory;

    /**
     * @var CancelOrderRequestFactory
     */
    private $cancelOrderRequestFactory;

    /**
     * @var FrontApiClient
     */
    private $frontApiClient;

    public function __construct(
        OrderRequestFactory $orderRequestFactory,
        CancelOrderRequestFactory $cancelOrderRequestFactory,
        FrontApiClient $frontApiClient
    ) {
        $this->orderRequestFactory = $orderRequestFactory;
        $this->cancelOrderRequestFactory = $cancelOrderRequestFactory;
        $this->frontApiClient = $frontApiClient;
    }

    /**
     * Retrieve the Mirakl Order through the Mirakl API by ID
     *
     * @param string $miraklOrderId
     * @return Order
     */
    public function getById(string $miraklOrderId): Order
    {
        $miraklGetOrderRequest = $this->orderRequestFactory->getById($miraklOrderId);
        $miraklFrontApiClient = $this->frontApiClient->get();

        return $miraklFrontApiClient->getOrders($miraklGetOrderRequest)->first();
    }

    /**
     * Cancel a Mirakl Order through the Mirakl API by ID
     *
     * @param string $miraklOrderId
     * @return void
     */
    public function cancelById(string $miraklOrderId): void
    {
        $cancelOrderRequest = $this->cancelOrderRequestFactory->setId($miraklOrderId);
        $miraklFrontApiClient = $this->frontApiClient->get();
        $miraklFrontApiClient->cancelOrder($cancelOrderRequest);
    }
}
