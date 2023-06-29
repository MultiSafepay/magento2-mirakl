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

namespace MultiSafepay\Mirakl\Model\ResourceModel\PayOut;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use MultiSafepay\Mirakl\Model\PayOut;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut as PayOutResourceModel;

class Collection extends AbstractCollection
{
    public function _construct()
    {
        $this->_init(
            PayOut::class,
            PayOutResourceModel::class
        );
    }

    /**
     * @param string $miraklOrderId
     * @return $this
     */
    public function filterByMiraklOrderId(string $miraklOrderId)
    {
        $this->addFieldToFilter(PayOut::MIRAKL_ORDER_ID, $miraklOrderId);
        return $this;
    }

    public function withOrderLines()
    {
        // phpcs:disable
        $this->getSelect()->joinLeft(
            [
                'order_line' => $this->getTable('multisafepay_mirakl_payout_order_line')
            ],
            'main_table.payout_id = order_line.payout_id',
            [
                'order_lines' => new \Zend_Db_Expr(
                    'GROUP_CONCAT(DISTINCT CONCAT_WS(":", order_line.payout_order_line_id, order_line.payout_id, order_line.product_price, order_line.product_quantity, order_line.product_taxes, order_line.shipping_price, order_line.shipping_taxes, order_line.total_price_including_taxes, order_line.total_price_excluding_taxes, order_line.operator_amount, order_line.seller_amount, order_line.is_refunded, order_line.mirakl_order_line_id, order_line.mirakl_order_status, order_line.status) SEPARATOR ",")'
                )
            ]
        )->group('main_table.payout_id');
        // phpcs:enable

        // Set the results of order_lines into an array at item level.
        $orderLines = [];

        /** @var PayOut $item */
        foreach ($this->getItems() as $item) {
            $data = $item->getData();
            if (isset($data['order_lines'])) {
                $orderLines[$data['payout_id']] = [];
                $lines = explode(',', $data['order_lines']);
                foreach ($lines as $line) {
                    $parts = explode(':', $line);
                    $orderLines[$data['payout_id']][] = [
                        'payout_order_line_id' => $parts[0],
                        'payout_id' => $parts[1],
                        'product_price' => $parts[2],
                        'product_quantity' => $parts[3],
                        'product_taxes' => $parts[4],
                        'shipping_price' => $parts[5],
                        'shipping_taxes' => $parts[6],
                        'total_price_including_taxes' => $parts[7],
                        'total_price_excluding_taxes' => $parts[8],
                        'operator_amount' => $parts[9],
                        'seller_amount' => $parts[10],
                        'is_refunded' => $parts[11],
                        'mirakl_order_line_id' => $parts[12],
                        'mirakl_order_status' => $parts[13],
                        'status' => $parts[14]
                    ];
                }
            }
            $item->setOrderLines($orderLines[$data['payout_id']]);
        }

        $this->save();

        return $this;
    }
}
