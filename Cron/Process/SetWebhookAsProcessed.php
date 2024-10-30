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
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\Webhook;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook as WebhookResourceModel;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook\CollectionFactory;
use MultiSafepay\Mirakl\Util\WebhookUtil;

class SetWebhookAsProcessed
{
    /**
     * @var WebhookResourceModel
     */
    private $webhookResourceModel;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var WebhookUtil
     */
    private $webhookUtil;

    /**
     * SetWebhookAsProcessed constructor.
     *
     * @param WebhookResourceModel $webhookResourcemodel
     * @param WebhookUtil $webhookUtil
     * @param Logger $logger
     */
    public function __construct(
        WebhookResourceModel $webhookResourcemodel,
        WebhookUtil $webhookUtil,
        Logger $logger
    ) {
        $this->webhookResourceModel = $webhookResourcemodel;
        $this->webhookUtil = $webhookUtil;
        $this->logger = $logger;
    }

    /**
     * Update the webhook status as processed
     *
     * @param array $webhookRequestData
     * @return void
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     */
    public function execute(array $webhookRequestData)
    {
        $webhookRequest = $this->webhookUtil->getWebhookRequest($webhookRequestData[Webhook::WEBHOOK_ORDER_ID]);

        $webhookRequest->setStatus(Webhook::WEBHOOK_STATUS_PROCESSED_SUCCESSFULLY);
        $this->webhookResourceModel->save($webhookRequest);
        $this->logger->logCronProcessInfo(
            'multisafepay_mirakl_webhook status updated',
            $webhookRequestData
        );
    }

    /**
     * Update the webhook status as processed with error
     *
     * @param array $webhookRequestData
     * @param Exception $exception
     * @return void
     */
    public function withError(array $webhookRequestData, Exception $exception): void
    {
        try {
            $webhookRequest = $this->webhookUtil->getWebhookRequest($webhookRequestData[Webhook::WEBHOOK_ORDER_ID]);

            $webhookRequest->setStatus(Webhook::WEBHOOK_STATUS_PROCESSED_WITH_ERRORS);
            $webhookRequest->setObservations($exception->getMessage());
            $this->webhookResourceModel->save($webhookRequest);
        } catch (Exception|AlreadyExistsException|NoSuchEntityException $exception) {
            $this->logger->logWebhookException($webhookRequestData, $exception);
        }
    }
}
