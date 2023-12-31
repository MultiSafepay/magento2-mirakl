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

namespace MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund as CustomerRefundResourceModel;

class Collection extends AbstractCollection
{
    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            CustomerRefund::class,
            CustomerRefundResourceModel::class
        );
    }

    /**
     * @param int $status
     * @return $this
     */
    public function filterByStatus(int $status): Collection
    {
        $this->addFieldToFilter(CustomerRefund::STATUS, $status);
        return $this;
    }

    /**
     * @return $this
     */
    public function withOrderLines(): Collection
    {
        // phpcs:disable
        $this->getSelect()->joinLeft(
            [
                'order_line' => $this->getTable('multisafepay_mirakl_customer_refund_order_line')
            ],
            'main_table.customer_refund_id = order_line.customer_refund_id',
            [
                'order_lines' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(DISTINCT CONCAT_WS(":", order_line.customer_refund_order_line_id, order_line.customer_refund_id, order_line.offer_id, order_line.order_line_amount, order_line.order_line_id, order_line.order_line_refund_id, order_line.order_line_quantity) SEPARATOR ",")'
                )
            ]
        )->group('main_table.customer_refund_id');
        // phpcs:enable

        // Set the results of order_lines into an array at item level.
        $orderLines = [];

        /** @var CustomerRefund $item */
        foreach ($this->getItems() as $item) {
            $data = $item->getData();
            if (isset($data['order_lines'])) {
                $orderLines[$data['customer_refund_id']] = [];
                $lines = explode(',', $data['order_lines']);
                foreach ($lines as $line) {
                    $parts = explode(':', $line);
                    $orderLines[$data['customer_refund_id']][] = [
                        'customer_refund_order_line_id' => $parts[0],
                        'customer_refund_id' => $parts[1],
                        'offer_id' => $parts[2],
                        'order_line_amount' => $parts[3],
                        'order_line_id' => $parts[4],
                        'order_line_refund_id' => $parts[5],
                        'order_line_quantity' => $parts[6]
                    ];
                }
            }
            $item->setOrderLines($orderLines[$data['customer_refund_id']]);
        }

        $this->save();

        return $this;
    }
}
