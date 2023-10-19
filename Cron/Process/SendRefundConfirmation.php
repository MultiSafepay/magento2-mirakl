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

namespace MultiSafepay\Mirakl\Cron\Process;

use Mirakl\MMP\FrontOperator\Request\Payment\Refund\ConfirmOrderRefundRequest;
use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient as MiraklFrontApiClient;
use MultiSafepay\Mirakl\Logger\Logger;

class SendRefundConfirmation
{
    /**
     * @var MiraklFrontApiClient
     */
    private $miraklFrontApiClient;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param MiraklFrontApiClient $miraklFrontApiClient
     * @param Logger $logger
     */
    public function __construct(
        MiraklFrontApiClient $miraklFrontApiClient,
        Logger $logger
    ) {
        $this->miraklFrontApiClient = $miraklFrontApiClient;
        $this->logger = $logger;
    }

    /**
     * Send the refund confirmation request to Mirakl
     *
     * @param array $orderRefundData
     * @return void
     */
    public function execute(array $orderRefundData)
    {
        $miraklFrontApiClient = $this->miraklFrontApiClient->get();

        foreach ($orderRefundData['order_lines'] as $orderLine) {
            $input = ['refunds' => [
                'amount' => (string)$orderLine['order_line_amount'],
                'refund_id' =>  (int)$orderLine['order_line_refund_id'],
                'payment_status' => 'OK'
            ]];

            $request = new ConfirmOrderRefundRequest($input);

            $miraklFrontApiClient->confirmOrderRefund($request);
            $this->logger->logCronProcessInfo(
                'confirmOrderRefund Request send to Mirakl',
                $request->toArray()
            );
        }
    }
}
