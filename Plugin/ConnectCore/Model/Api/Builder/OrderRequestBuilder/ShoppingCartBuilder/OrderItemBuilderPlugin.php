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

namespace MultiSafepay\Mirakl\Plugin\ConnectCore\Model\Api\Builder\OrderRequestBuilder\ShoppingCartBuilder;

use Magento\Sales\Api\Data\OrderItemInterface;
use MultiSafepay\ConnectCore\Model\Api\Builder\OrderRequestBuilder\ShoppingCartBuilder\OrderItemBuilder;

class OrderItemBuilderPlugin
{
    /**
     * Append to the Merchant Item ID, the Mirakl Shop ID
     *
     * @param OrderItemBuilder $orderItemBuilder
     * @param string $result
     * @param OrderItemInterface $item
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetMerchantItemId(
        OrderItemBuilder $orderItemBuilder,
        string $result,
        OrderItemInterface $item
    ): string {
        if ($item->hasMiraklShopId()) {
            return $result . '-' . $item->getMiraklShopId();
        }

        return $result;
    }
}
