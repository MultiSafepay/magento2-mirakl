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

use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Quote\Api\Data\CartItemInterface;

class ShoppingCartUtil
{
    /**
     * Returns true if the shopping cart provided contains Mirakl products
     *
     * @param  OrderItemInterface[] $shoppingCartItems
     * @return bool
     */
    public function hasMiraklSellerProducts(array $shoppingCartItems): bool
    {
        foreach ($shoppingCartItems as $shoppingCartItem) {
            if ($shoppingCartItem->hasMiraklShopId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the shopping cart provided does not contain Mirakl products
     *
     * @param  OrderItemInterface[] $shoppingCartItems
     * @return bool
     */
    public function hasMiraklOperatorProducts(array $shoppingCartItems): bool
    {
        foreach ($shoppingCartItems as $shoppingCartItem) {
            if (!$shoppingCartItem->hasMiraklShopId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the shopping cart contains operator's products and seller's products
     *
     * @param  OrderItemInterface[] $shoppingCartItems
     * @return bool
     */
    public function isMixedShoppingCart(array $shoppingCartItems): bool
    {
        return $this->hasMiraklSellerProducts($shoppingCartItems) &&
            $this->hasMiraklOperatorProducts($shoppingCartItems);
    }

    /**
     * Returns true if the quote items contains products from multiple sellers
     *
     * @param CartItemInterface[] $shoppingCartItems
     * @return bool
     */
    public function hasMultipleSellers(array $shoppingCartItems): bool
    {
        $shopIds = [];

        foreach ($shoppingCartItems as $shoppingCartItem) {
            if ($shoppingCartItem->hasMiraklShopId()) {
                $shopId = $shoppingCartItem->getMiraklShopId();
                if (!in_array($shopId, $shopIds, true)) {
                    $shopIds[] = $shopId;
                }
            }
        }

        return count($shopIds) > 1;
    }
}
