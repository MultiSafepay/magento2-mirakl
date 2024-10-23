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

namespace MultiSafepay\Mirakl\Cron;

use Exception;
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\Api\Transactions\UpdateRequest;
use MultiSafepay\ConnectCore\Factory\SdkFactory;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Exception\ApiException;
use MultiSafepay\Mirakl\Cron\Process\SetWebhookAsProcessed;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\ResourceModel\WebHook\Collection;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook\CollectionFactory as WebhookCollectionFactory;
use MultiSafepay\Mirakl\Model\Webhook;
use MultiSafepay\Mirakl\Util\MiraklOrderUtil;
use Psr\Http\Client\ClientExceptionInterface;

class SetMultiSafepayTransactionToShipped
{
    /**
     * @var WebhookCollectionFactory
     */
    private $webhookCollectionFactory;

    /**
     * @var MiraklOrderUtil
     */
    private $miraklOrderUtil;

    /**
     * @var OrderUtil
     */
    private $orderUtil;

    /**
     * @var SetWebhookAsProcessed
     */
    private $setWebhookAsProcessed;

    /**
     * @var SdkFactory
     */
    private $sdkFactory;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param WebhookCollectionFactory $webhookCollectionFactory
     * @param MiraklOrderUtil $miraklOrderUtil
     * @param OrderUtil $orderUtil
     * @param SetWebhookAsProcessed $setWebhookAsProcessed
     * @param SdkFactory $sdkFactory
     * @param Logger $logger
     */
    public function __construct(
        WebhookCollectionFactory $webhookCollectionFactory,
        MiraklOrderUtil $miraklOrderUtil,
        OrderUtil $orderUtil,
        SetWebhookAsProcessed $setWebhookAsProcessed,
        SdkFactory $sdkFactory,
        Logger $logger
    ) {
        $this->webhookCollectionFactory = $webhookCollectionFactory;
        $this->miraklOrderUtil = $miraklOrderUtil;
        $this->orderUtil = $orderUtil;
        $this->setWebhookAsProcessed = $setWebhookAsProcessed;
        $this->sdkFactory = $sdkFactory;
        $this->logger = $logger;
    }

    /**
     * Process the webhook
     *
     * @return void
     */
    public function execute(): void
    {
        /** @var Collection $webhookCollection */
        $webhookCollection = $this->webhookCollectionFactory->create();
        $webhookCollection->filterByStatus(Webhook::WEBHOOK_STATUS_PENDING_TO_BE_PROCESSED);
        foreach ($webhookCollection->getItems() as $webhookRequest) {
            $webhookRequestData = $webhookRequest->getData();
            try {
                $miraklOrderId = $webhookRequestData[Webhook::WEBHOOK_ORDER_ID];
                $miraklOrder = $this->miraklOrderUtil->getById($miraklOrderId);
                $orderIncrementId = $miraklOrder->getCommercialId();
                $order = $this->orderUtil->getOrderByIncrementId($orderIncrementId);

                $updateRequest = (new UpdateRequest())
                    ->addId($orderIncrementId)
                    ->addStatus('shipped');

                $this->sdkFactory->create((int)$order->getStoreId())
                ->getTransactionManager()
                ->update($orderIncrementId, $updateRequest)
                ->getResponseData();

                $this->setWebhookAsProcessed->execute($webhookRequestData);

            } catch (ApiException|ClientExceptionInterface|Exception|NoSuchEntityException $exception) {
                $this->setWebhookAsProcessed->withError($webhookRequestData, $exception);
            }
        }
    }
}
