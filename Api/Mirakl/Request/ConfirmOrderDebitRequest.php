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

use Mirakl\MMP\FrontOperator\Request\Payment\Debit\ConfirmOrderDebitRequest as MiraklConfirmOrderDebitRequest;
use MultiSafepay\Mirakl\Model\CustomerDebit;

class ConfirmOrderDebitRequest
{

    /**
     * Return a MiraklConfirmOrderDebitRequest object
     *
     * @param array $debitRequestData
     * @return MiraklConfirmOrderDebitRequest
     */
    public function get(array $debitRequestData): MiraklConfirmOrderDebitRequest
    {
        return new MiraklConfirmOrderDebitRequest($this->getOrderDebitRequestData($debitRequestData));
    }

    /**
     * Build the arguments required to construct a MiraklConfirmOrderDebitRequest
     *
     * @param array $debitRequestData
     * @return array
     */
    private function getOrderDebitRequestData(array $debitRequestData): array
    {
        $orderDebitRequest[] = [
            "amount" => $debitRequestData[CustomerDebit::AMOUNT],
            "currency_iso_code" => $debitRequestData[CustomerDebit::CURRENCY_ISO_CODE],
            "customer_id" => $debitRequestData[CustomerDebit::CUSTOMER_ID],
            "debit_entity" => [
                "id" => $debitRequestData[CustomerDebit::DEBIT_ENTITY_ID],
                "type" => $debitRequestData[CustomerDebit::DEBIT_ENTITY_TYPE]
            ],
            "order_id" => $debitRequestData[CustomerDebit::ORDER_ID],
            "payment_status" => "OK"
        ];
        return $orderDebitRequest;
    }
}
