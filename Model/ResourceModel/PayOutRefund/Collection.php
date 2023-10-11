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

namespace MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefund;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MultiSafepay\Mirakl\Model\PayOutRefund;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOutRefund as PayOutRefundResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            PayOutRefund::class,
            PayOutRefundResourceModel::class
        );
    }

    /**
     * @param string $miraklOrderId
     * @return $this
     */
    public function filterByMiraklOrderId(string $miraklOrderId): Collection
    {
        $this->addFieldToFilter(PayOutRefund::MIRAKL_ORDER_ID, $miraklOrderId);
        return $this;
    }
}
