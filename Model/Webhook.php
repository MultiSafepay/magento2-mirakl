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

namespace MultiSafepay\Mirakl\Model;

use Magento\Framework\Model\AbstractModel;
use MultiSafepay\Mirakl\Model\ResourceModel\Webhook as WebhookResourceModel;

class Webhook extends AbstractModel
{
    public const WEBHOOK_ID = 'webhook_id';
    public const WEBHOOK_EVENT_TYPE = 'webhook_event_type';
    public const WEBHOOK_ORDER_ID = 'webhook_order_id';
    public const WEBHOOK_PAYLOAD = 'webhook_payload';
    public const TIMESTAMP = 'timestamp';
    public const STATUS = 'status';
    public const OBSERVATIONS = 'observations';

    public const WEBHOOK_STATUS_PROCESSED_SUCCESSFULLY = 0;
    public const WEBHOOK_STATUS_PENDING_TO_BE_PROCESSED = 1;
    public const WEBHOOK_STATUS_PROCESSED_WITH_ERRORS = 2;

    /**
     * @return void
     */
    public function _construct()
    {
        $this->_init(WebhookResourceModel::class);
    }

    /**
     * @return string
     */
    public function getEventType(): string
    {
        return (string)$this->getData(self::WEBHOOK_EVENT_TYPE);
    }

    /**
     * @param string $eventType
     * @return Webhook
     */
    public function setEventType(string $eventType): Webhook
    {
        return $this->setData(self::WEBHOOK_EVENT_TYPE, $eventType);
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return (string)$this->getData(self::WEBHOOK_ORDER_ID);
    }

    /**
     * @param string $orderId
     * @return Webhook
     */
    public function setOrderId(string $orderId): Webhook
    {
        return $this->setData(self::WEBHOOK_ORDER_ID, $orderId);
    }

    /**
     * @return string
     */
    public function getPayload(): string
    {
        return (string)$this->getData(self::WEBHOOK_PAYLOAD);
    }

    /**
     * @param string $payload
     * @return Webhook
     */
    public function setPayload(string $payload): Webhook
    {
        return $this->setData(self::WEBHOOK_PAYLOAD, $payload);
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return (int)$this->getData(self::STATUS);
    }

    /**
     * @param int $status
     * @return Webhook
     */
    public function setStatus(int $status): Webhook
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getObservations(): string
    {
        return (string)$this->getData(self::OBSERVATIONS);
    }

    /**
     * @param string $observations
     * @return Webhook
     */
    public function setObservations(string $observations): Webhook
    {
        return $this->setData(self::OBSERVATIONS, $observations);
    }
}
