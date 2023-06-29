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
            if ('null' !== $shoppingCartItem->getMiraklShopId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if the shopping cart provided contains non Mirakl products
     *
     * @param  OrderItemInterface[] $shoppingCartItems
     * @return bool
     */
    public function hasOperatorMiraklProducts(array $shoppingCartItems): bool
    {
        foreach ($shoppingCartItems as $shoppingCartItem) {
            if (null === $shoppingCartItem->getMiraklShopId()) {
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
        if ($this->hasMiraklSellerProducts($shoppingCartItems) &&
            $this->hasOperatorMiraklProducts($shoppingCartItems)) {
            return true;
        }

        return false;
    }
}
