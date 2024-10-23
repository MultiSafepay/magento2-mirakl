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

namespace MultiSafepay\Mirakl\Model\ResourceModel\Webhook;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MultiSafepay\Mirakl\Model\Webhook;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook as WebhookResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            Webhook::class,
            WebhookResourceModel::class
        );
    }

    /**
     * @param int $status
     * @return $this
     */
    public function filterByStatus(int $status): Collection
    {
        $this->addFieldToFilter(Webhook::STATUS, $status);
        return $this;
    }

    /**
     * @param string $orderId
     * @return $this
     */
    public function filterByOrderId(string $orderId): Collection
    {
        $this->addFieldToFilter(Webhook::WEBHOOK_ORDER_ID, $orderId);
        return $this;
    }
}
