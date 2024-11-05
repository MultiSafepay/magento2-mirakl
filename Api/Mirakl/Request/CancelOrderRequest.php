<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * See DISCLAIMER.md for disclaimer details
 */

declare(strict_types=1);

namespace MultiSafepay\Mirakl\Api\Mirakl\Request;

use Mirakl\MMP\FrontOperator\Request\Order\Workflow\CancelOrderRequest as MiraklCancelOrderRequest;

class CancelOrderRequest
{
    /**
     * Return a GetOrdersRequest object
     *
     * @param string $orderId
     * @return MiraklCancelOrderRequest
     */
    public function setId(string $orderId): MiraklCancelOrderRequest
    {
        return new MiraklCancelOrderRequest($orderId);
    }
}
