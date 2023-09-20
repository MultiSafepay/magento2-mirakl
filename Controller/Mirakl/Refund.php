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
use Magento\Framework\Exception\AlreadyExistsException;
use MultiSafepay\ConnectCore\Util\JsonHandler;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Model\CustomerRefundFactory;
use MultiSafepay\Mirakl\Model\CustomerRefundOrderLine;
use MultiSafepay\Mirakl\Model\CustomerRefundOrderLineFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefund as CustomerRefundResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\CustomerRefundOrderLine as CustomerRefundOrderLineResourceModel;

class Refund extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var JsonHandler
     */
    private $jsonHandler;

    /**
     * @var CustomerRefundFactory
     */
    private $customerRefundFactory;

    /**
     * @var CustomerRefundResourceModel
     */
    private $customerRefundResourceModel;

    /**
     * @var CustomerRefundOrderLineFactory
     */
    private $customerRefundOrderLineFactory;

    /**
     * @var CustomerRefundOrderLineResourceModel
     */
    private $customerRefundOrderLineResourceModel;

    /**
     * Notification constructor.
     *
     * @param RequestInterface $request
     * @param Logger $logger
     * @param JsonHandler $jsonHandler
     * @param CustomerRefundFactory $customerRefundFactory
     * @param CustomerRefundResourceModel $customerRefundResourceModel
     * @param CustomerRefundOrderLineFactory $customerRefundOrderLineFactory
     * @param CustomerRefundOrderLineResourceModel $customerRefundOrderLineResourceModel
     * @param Context $context
     */
    public function __construct(
        RequestInterface $request,
        Logger $logger,
        JsonHandler $jsonHandler,
        CustomerRefundFactory $customerRefundFactory,
        CustomerRefundResourceModel $customerRefundResourceModel,
        CustomerRefundOrderLineFactory $customerRefundOrderLineFactory,
        CustomerRefundOrderLineResourceModel $customerRefundOrderLineResourceModel,
        Context $context
    ) {
        $this->request = $request;
        $this->logger = $logger;
        $this->jsonHandler = $jsonHandler;
        $this->customerRefundFactory = $customerRefundFactory;
        $this->customerRefundResourceModel = $customerRefundResourceModel;
        $this->customerRefundOrderLineFactory = $customerRefundOrderLineFactory;
        $this->customerRefundOrderLineResourceModel = $customerRefundOrderLineResourceModel;
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
     * Process the refund request HTTP notification.
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        $miraklRequest = $this->getRequest()->getContent();

        $this->logger->logMiraklCustomerRefundRequest($miraklRequest);

        $miraklCustomerRefund = $this->jsonHandler->readJSON($miraklRequest);

        if (empty($miraklCustomerRefund['order'][0]['order_lines'] ?? [])) {
            $this->logger->info('Mirakl refund request received without order lines.');
            return $this->getResponse()->setContent('OK');
        }

        foreach ($miraklCustomerRefund['order'] as $miraklCustomerRefundItem) {
            $this->saveCustomerRefund($miraklCustomerRefundItem);
        }

        return $this->getResponse()->setContent('OK');
    }

    /**
     * Save the customer refund request
     *
     * @param array $miraklCustomerRefundItem
     * @return void
     * @throws AlreadyExistsException
     */
    private function saveCustomerRefund(array $miraklCustomerRefundItem): void
    {
        /** @var CustomerRefund $customerRefund */
        $customerRefund = $this->customerRefundFactory->create();
        $customerRefund->setCustomerId($miraklCustomerRefundItem[$customerRefund::CUSTOMER_ID]);
        $customerRefund->setOrderId($miraklCustomerRefundItem[$customerRefund::ORDER_ID]);
        $customerRefund->setOrderCommercialId($miraklCustomerRefundItem[$customerRefund::ORDER_COMMERCIAL_ID]);
        $customerRefund->setShopId($miraklCustomerRefundItem[$customerRefund::SHOP_ID]);
        $customerRefund->setPaymentWorkflow($miraklCustomerRefundItem[$customerRefund::PAYMENT_WORKFLOW]);
        $customerRefund->setCurrencyIsoCode($miraklCustomerRefundItem[$customerRefund::CURRENCY_ISO_CODE]);
        $customerRefund->setAmount($miraklCustomerRefundItem[$customerRefund::AMOUNT]);
        $customerRefund->setStatus(1);

        $savedCustomerRefund = $this->customerRefundResourceModel->save($customerRefund);

        $orderLines = $miraklCustomerRefundItem[$customerRefund::ORDER_LINES] ?? [];

        if ($savedCustomerRefund && !empty($orderLines)) {
            foreach ($orderLines['order_line'] as $miraklCustomerRefundOrderLineItem) {
                $this->saveCustomerRefundOrderLines(
                    (int) $customerRefund->getId(),
                    $miraklCustomerRefundOrderLineItem
                );
            }
        }
    }

    /**
     * Save the order lines for the given customer refund id
     *
     * @param int $customerRefundId
     * @param array $orderLineItem
     * @return void
     * @throws AlreadyExistsException
     */
    private function saveCustomerRefundOrderLines(int $customerRefundId, array $orderLineItem): void
    {
        /** @var CustomerRefundOrderLine $customerRefundOrderLine */
        $customerRefundOrderLine = $this->customerRefundOrderLineFactory->create();

        $customerRefundOrderLine->setCustomerRefundId($customerRefundId);
        $customerRefundOrderLine->setOfferId($orderLineItem[$customerRefundOrderLine::OFFER_ID]);
        $customerRefundOrderLine->setOrderLineAmount($orderLineItem[$customerRefundOrderLine::ORDER_LINE_AMOUNT]);
        $customerRefundOrderLine->setOrderLineId($orderLineItem[$customerRefundOrderLine::ORDER_LINE_ID]);
        $customerRefundOrderLine->setOrderLineQuantity($orderLineItem[$customerRefundOrderLine::ORDER_LINE_QUANTITY]);

        $this->customerRefundOrderLineResourceModel->save($customerRefundOrderLine);
    }
}
