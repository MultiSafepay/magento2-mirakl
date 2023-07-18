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
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use MultiSafepay\ConnectCore\Util\JsonHandler;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\CustomerDebitFactory;
use MultiSafepay\Mirakl\Model\CustomerDebitOrderLine;
use MultiSafepay\Mirakl\Model\CustomerDebitOrderLineFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebit as CustomerDebitResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerDebitOrderLine as CustomerDebitOrderLineResourceModel;

/**
 * CustomerDebit controller which receives and handles the Customer Debit Connector requests
 */
class Debit extends Action implements CsrfAwareActionInterface
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
     * @var CustomerDebitFactory
     */
    private $customerDebitFactory;

    /**
     * @var CustomerDebitResourceModel
     */
    private $customerDebitResourceModel;

    /**
     * @var CustomerDebitOrderLineFactory
     */
    private $customerDebitOrderLineFactory;

    /**
     * @var CustomerDebitOrderLineResourceModel
     */
    private $customerDebitOrderLineResourceModel;

    /**
     * Notification constructor.
     *
     * @param Logger $logger
     * @param JsonHandler $jsonHandler
     * @param CustomerDebitFactory $customerDebitFactory
     * @param CustomerDebitResourceModel $customerDebitResourceModel
     * @param CustomerDebitOrderLineFactory $customerDebitOrderLineFactory
     * @param CustomerDebitOrderLineResourceModel $customerDebitOrderLineResourceModel
     * @param Context $context
     */
    public function __construct(
        Logger $logger,
        JsonHandler $jsonHandler,
        CustomerDebitFactory $customerDebitFactory,
        CustomerDebitResourceModel $customerDebitResourceModel,
        CustomerDebitOrderLineFactory $customerDebitOrderLineFactory,
        CustomerDebitOrderLineResourceModel $customerDebitOrderLineResourceModel,
        Context $context
    ) {
        $this->logger = $logger;
        $this->jsonHandler = $jsonHandler;
        $this->customerDebitFactory = $customerDebitFactory;
        $this->customerDebitResourceModel = $customerDebitResourceModel;
        $this->customerDebitOrderLineFactory = $customerDebitOrderLineFactory;
        $this->customerDebitOrderLineResourceModel = $customerDebitOrderLineResourceModel;
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

        $this->logger->logMiraklCustomerDebitRequest($miraklRequest);

        $miraklCustomerDebit = $this->jsonHandler->readJSON($miraklRequest);

        if (empty($miraklCustomerDebit['order'][0]['order_lines'] ?? [])) {
            $this->logger->info('Mirakl request received without order lines.');
            return $this->getResponse()->setContent('OK');
        }

        foreach ($miraklCustomerDebit['order'] as $miraklCustomerDebitItem) {
            $this->saveCustomerDebit($miraklCustomerDebitItem);
        }

        return $this->getResponse()->setContent('OK');
    }

    /**
     * Save the customer debit request
     *
     * @param array $miraklCustomerDebitItem
     * @return void
     * @throws AlreadyExistsException
     */
    private function saveCustomerDebit(array $miraklCustomerDebitItem): void
    {
        /** @var CustomerDebit $customerDebit */
        $customerDebit = $this->customerDebitFactory->create();
        $customerDebit->setCustomerId($miraklCustomerDebitItem[$customerDebit::CUSTOMER_ID]);
        $customerDebit->setOrderId($miraklCustomerDebitItem[$customerDebit::ORDER_ID]);
        $customerDebit->setOrderCommercialId($miraklCustomerDebitItem[$customerDebit::ORDER_COMMERCIAL_ID]);
        $customerDebit->setShopId($miraklCustomerDebitItem[$customerDebit::SHOP_ID]);
        $customerDebit->setDebitEntityType($miraklCustomerDebitItem['debit_entity']['type']);
        $customerDebit->setDebitEntityId($miraklCustomerDebitItem['debit_entity']['id']);
        $customerDebit->setCurrencyIsoCode($miraklCustomerDebitItem[$customerDebit::CURRENCY_ISO_CODE]);
        $customerDebit->setAmount($miraklCustomerDebitItem[$customerDebit::AMOUNT]);
        $customerDebit->setStatus(1);

        $savedCustomerDebit = $this->customerDebitResourceModel->save($customerDebit);

        $orderLines = $miraklCustomerDebitItem[$customerDebit::ORDER_LINES] ?? [];

        if ($savedCustomerDebit && !empty($orderLines)) {
            foreach ($orderLines['order_line'] as $miraklCustomerDebitOrderLineItem) {
                $this->saveCustomerDebitOrderLines(
                    (int) $customerDebit->getId(),
                    $miraklCustomerDebitOrderLineItem
                );
            }
        }
    }

    /**
     * Save the order lines for the given customer debit id
     *
     * @param int $customerDebitId
     * @param array $orderLineItem
     * @return void
     * @throws AlreadyExistsException
     */
    private function saveCustomerDebitOrderLines(int $customerDebitId, array $orderLineItem): void
    {
        /** @var CustomerDebitOrderLine $customerDebitOrderLine */
        $customerDebitOrderLine = $this->customerDebitOrderLineFactory->create();

        $customerDebitOrderLine->setCustomerDebitId($customerDebitId);
        $customerDebitOrderLine->setOfferId($orderLineItem[$customerDebitOrderLine::OFFER_ID]);
        $customerDebitOrderLine->setOrderLineAmount($orderLineItem[$customerDebitOrderLine::ORDER_LINE_AMOUNT]);
        $customerDebitOrderLine->setOrderLineId($orderLineItem[$customerDebitOrderLine::ORDER_LINE_ID]);
        $customerDebitOrderLine->setOrderLineQuantity($orderLineItem[$customerDebitOrderLine::ORDER_LINE_QUANTITY]);

        $this->customerDebitOrderLineResourceModel->save($customerDebitOrderLine);
    }
}
