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

namespace MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MultiSafepay\Mirakl\Model\PayOutOrderLine;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutOrderLine as PayOutOrderLineResourceModel;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            PayOutOrderLine::class,
            PayOutOrderLineResourceModel::class
        );
    }
}
