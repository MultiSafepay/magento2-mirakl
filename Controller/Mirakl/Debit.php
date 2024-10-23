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

namespace MultiSafepay\Mirakl\Controller\Mirakl;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\ResponseInterface;
use MultiSafepay\ConnectCore\Util\JsonHandler;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Service\RegisterCustomerDebit;

/**
 * CustomerDebit controller which receives and handles the Customer Debit Connector requests
 */
class Debit extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var JsonHandler
     */
    private $jsonHandler;

    /**
     * @var RegisterCustomerDebit
     */
    private $registerCustomerDebit;

    /**
     * Notification constructor.
     *
     * @param Logger $logger
     * @param JsonHandler $jsonHandler
     * @param RegisterCustomerDebit $registerCustomerDebit
     * @param Context $context
     */
    public function __construct(
        Logger $logger,
        JsonHandler $jsonHandler,
        RegisterCustomerDebit $registerCustomerDebit,
        Context $context
    ) {
        $this->logger = $logger;
        $this->jsonHandler = $jsonHandler;
        $this->registerCustomerDebit = $registerCustomerDebit;
        parent::__construct($context);
    }

    /**
     * @param RequestInterface $request
     * @return InvalidRequestException|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    /**
     * @param RequestInterface $request
     * @return bool|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * Process the debit request HTTP notification.
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        $miraklRequest = $this->getRequest()->getContent();
        $miraklCustomerDebit = $this->jsonHandler->readJSON($miraklRequest);

        $this->logger->logMiraklCustomerDebitRequest($miraklRequest);

        if (empty($miraklCustomerDebit['order'][0]['order_lines'] ?? [])) {
            $this->logger->info('Mirakl request received without order lines.');
            return $this->getResponse()->setContent('OK');
        }

        foreach ($miraklCustomerDebit['order'] as $miraklCustomerDebitItem) {
            $this->registerCustomerDebit->saveCustomerDebit($miraklCustomerDebitItem);
        }

        return $this->getResponse()->setContent('OK');
    }
}
