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

use Exception;
use MultiSafepay\Mirakl\Cron\ProcessInterface;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Api\Mirakl\Request\ConfirmOrderDebitRequest;
use MultiSafepay\Mirakl\Api\Mirakl\Client\FrontApiClient as MiraklFrontApiClient;

class SendOrderDebitConfirmation implements ProcessInterface
{
    /**
     * @var MiraklFrontApiClient
     */
    private $miraklFrontApiClient;

    /**
     * @var ConfirmOrderDebitRequest
     */
    private $confirmOrderDebitRequest;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var MiraklFrontApiClient $miraklFrontApiClient
     * @var ConfirmOrderDebitRequest $confirmOrderDebitRequest
     * @param Logger $logger
     */
    public function __construct(
        MiraklFrontApiClient $miraklFrontApiClient,
        ConfirmOrderDebitRequest $confirmOrderDebitRequest,
        Logger $logger
    ) {
        $this->miraklFrontApiClient = $miraklFrontApiClient;
        $this->confirmOrderDebitRequest = $confirmOrderDebitRequest;
        $this->logger = $logger;
    }

    /**
     * Send a request to Mirakl API to confirm the order debit
     *
     * @param array $orderDebitData
     * @return array|true[]
     */
    public function execute(array $orderDebitData): array
    {
        try {
            $miraklFrontApiClient = $this->miraklFrontApiClient->get();
            $miraklConfirmOrderDebitRequest = $this->confirmOrderDebitRequest->get($orderDebitData);
            $miraklFrontApiClient->confirmOrderDebit($miraklConfirmOrderDebitRequest);
        } catch (Exception $exception) {
            $this->logger->logException($exception);
            return [
                ProcessInterface::SUCCESS_PARAMETER => false,
                ProcessInterface::MESSAGE_PARAMETER => $exception->getMessage()
            ];
        }

        return [ProcessInterface::SUCCESS_PARAMETER => true];
    }
}
