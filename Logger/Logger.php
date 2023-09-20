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

use DateTimeZone;
use Exception;
use Mirakl\MMP\FrontOperator\Domain\Collection\Order\OrderCollection;
use MultiSafepay\ConnectCore\Logger\Logger as CoreLogger;
use MultiSafepay\ConnectCore\Util\JsonHandler;

class Logger extends CoreLogger
{
    /**
     * @var JsonHandler
     */
    private $jsonHandler;

    /**
     * @param string $name
     * @param JsonHandler $jsonHandler
     * @param array $handlers
     * @param array $processors
     * @param DateTimeZone|null $timezone
     */
    public function __construct(
        string $name,
        JsonHandler $jsonHandler,
        array $handlers = [],
        array $processors = [],
        ?DateTimeZone $timezone = null
    ) {
        parent::__construct($name, $handlers, $processors, $timezone);
        $this->jsonHandler = $jsonHandler;
    }

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
            $message .= $this->jsonHandler->convertToJSON($shoppingCartItem->getData());
        }

        $this->addRecord(
            self::DEBUG,
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
     * Log the incomming Mirakl customer refund request
     *
     * @param string $miraklRequest
     * @return void
     */
    public function logMiraklCustomerRefundRequest(string $miraklRequest): void
    {
        $this->addRecord(
            self::INFO,
            sprintf(
                'Mirakl Refund request received: %1$s',
                $miraklRequest
            )
        );
    }

    /**
     * Log the start of a cron process
     *
     * @param string $className
     * @param array $processData
     * @param string $message
     * @return void
     */
    public function logCronProcessStep(string $className, array $processData, string $message): void
    {
        $this->addRecord(
            self::INFO,
            sprintf(
                '(Cron process %1$s, Order ID: %2$s, Mirakl Order ID: %3$s): %4$s',
                $className,
                $processData['order_commercial_id'] ?? 'Unknown',
                $processData['order_id'] ?? 'Unknown',
                $message
            )
        );
    }

    /**
     * Log an exception thrown during the execution of a cron process
     *
     * @param string $className
     * @param array $processData
     * @param Exception $exception
     * @return void
     */
    public function logCronProcessException(string $className, array $processData, Exception $exception)
    {
        $this->addRecord(
            self::ERROR,
            sprintf(
                '(Process %1$s, Order ID: %2$s, Mirakl Order ID: %3$s): Error occurred: %4$s (%5$d, %6$d, %7$s)',
                $className,
                $processData['order_commercial_id'] ?? 'Unknown',
                $processData['order_id'] ?? 'Unknown',
                $exception->getMessage(),
                $exception->getCode(),
                $exception->getLine(),
                $exception->getFile()
            )
        );
    }

    /**
     * Log debug information during the execution of a cron process
     *
     * @param string $message
     * @param array $processData
     * @param int $logLevel
     * @return void
     */
    public function logCronProcessInfo(string $message, array $processData, int $logLevel = self::INFO): void
    {
        $this->addRecord(
            $logLevel,
            sprintf(
                '%1$s. Details: %2$s',
                $message,
                $this->jsonHandler->convertToJSON($processData)
            )
        );
    }
}
