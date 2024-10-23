<?php

declare(strict_types=1);

namespace MultiSafepay\Mirakl\Util;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\ConnectCore\Util\JsonHandler;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook\Collection;
use MultiSafepay\Mirakl\Model\Webhook as WebhookModel;
use MultiSafepay\Mirakl\Model\WebhookFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook as WebhookResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook\CollectionFactory as WebhookCollectionFactory;

class WebhookUtil
{
    /**
     * @var WebhookFactory
     */
    private $webhookFactory;

    /**
     * @var WebhookCollectionFactory
     */
    private $webhookCollectionFactory;

    /**
     * @var WebhookResourceModel
     */
    private $webhookResourceModel;

    /**
     * @var JsonHandler
     */
    private $jsonHandler;

    /**
     * WebhookUtil constructor.
     *
     * @param WebhookFactory $webhookFactory
     * @param WebhookCollectionFactory $webhookCollectionFactory
     * @param WebhookResourceModel $webhookResourceModel
     * @param JsonHandler $jsonHandler
     */
    public function __construct(
        WebhookFactory $webhookFactory,
        WebhookCollectionFactory $webhookCollectionFactory,
        WebhookResourceModel $webhookResourceModel,
        JsonHandler $jsonHandler
    ) {
        $this->webhookFactory = $webhookFactory;
        $this->webhookCollectionFactory = $webhookCollectionFactory;
        $this->webhookResourceModel = $webhookResourceModel;
        $this->jsonHandler = $jsonHandler;
    }

    /**
     * Save the payload of a webhook request
     *
     * @param array $payload
     * @return void
     * @throws AlreadyExistsException
     */
    public function savePayloadWebhookRequest(array $payload): void
    {
        /** @var WebhookModel $webhook */
        $webhook = $this->webhookFactory->create();
        $webhook->setEventType('ORDER');
        $webhook->setOrderId($payload['id']);
        $webhook->setPayload($this->jsonHandler->convertToJSON($payload));
        $webhook->setStatus(WebhookModel::WEBHOOK_STATUS_PENDING_TO_BE_PROCESSED);

        $this->webhookResourceModel->save($webhook);
    }

    /**
     * Retrieve the webhook request
     *
     * @param string $orderId
     * @return WebhookModel
     * @throws NoSuchEntityException
     */
    public function getWebhookRequest(string $orderId): WebhookModel
    {
        /** @var Collection $webhook */
        $webhookCollection = $this->webhookCollectionFactory->create()->filterByOrderId($orderId);
        $webhook = $webhookCollection->getFirstItem();

        if (!$webhook->getData()) {
            throw new NoSuchEntityException(__('Webhook request with Order ID: ' . $orderId . ' was not found'));
        }

        return $webhook;
    }
}
