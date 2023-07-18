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

use Mirakl\MMP\FrontOperator\Request\Shop\GetShopsRequest;

class ShopRequest
{
    /**
     * Return a GetShopsRequest object
     *
     * @param int $shop_id
     * @return GetShopsRequest
     */
    public function getById(int $shop_id): GetShopsRequest
    {
        return new GetShopsRequest(['shop_ids' => $shop_id]);
    }
}
