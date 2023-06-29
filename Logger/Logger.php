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

namespace MultiSafepay\Mirakl\Logger;

use Mirakl\MMP\FrontOperator\Domain\Collection\Order\OrderCollection;
use MultiSafepay\ConnectCore\Logger\Logger as CoreLogger;

class Logger extends CoreLogger
{
    /**
     * Log the shopping cart details when the order contains products that belongs to a seller
     *
     * @param array $shoppingCart
     * @return void
     */
    public function logShoppingCartDetails(array $shoppingCart): void
    {
        $message = '';

        foreach ($shoppingCart as $shoppingCartItem) {
            $message .= json_encode($shoppingCartItem->getData());
        }

        $this->addRecord(
            self::INFO,
            $message
        );
    }

    /**
     * Log the incomming Mirakl customer debit request
     *
     * @param string $miraklRequest
     * @return void
     */
    public function logMiraklCustomerDebitRequest(string $miraklRequest): void
    {
        $this->addRecord(
            self::INFO,
            sprintf(
                'Mirakl Debit request received: %1$s',
                $miraklRequest
            )
        );
    }

    /**
     * Log the start of a cron process
     *
     * @param string $className
     * @param array $processData
     * @return void
     */
    public function logCronProcessStep(string $className, array $processData): void
    {
        $this->addRecord(
            self::INFO,
            sprintf(
                'Cron process %1$s, related with Order ID: %2$s, and Mirakl Order ID: %3$s started',
                $className,
                $processData['order_commercial_id'],
                $processData['order_id']
            )
        );
    }

    /**
     * Log an error related with a cron process
     *
     * @param string $className
     * @param array $processData
     * @param string $errorMessage
     * @return void
     */
    public function logCronProcessError(string $className, array $processData, string $errorMessage): void
    {
        $this->addRecord(
            self::ERROR,
            sprintf(
                'Cron process %1$s, related with Order ID: %2$s, and Mirakl Order ID: %3$s return error %4$s',
                $className,
                $processData['order_commercial_id'],
                $processData['order_id'],
                $errorMessage
            )
        );
    }

    /**
     * Log Mirakl order details retrieve using the API
     *
     * @param OrderCollection $miraklOrder
     * @return void
     */
    public function logMiraklOrder(OrderCollection $miraklOrder): void
    {
        $this->addRecord(
            self::INFO,
            sprintf(
                'Mirakl order retrieve using the API: %1$s',
                json_encode($miraklOrder->toArray())
            )
        );
    }
}
