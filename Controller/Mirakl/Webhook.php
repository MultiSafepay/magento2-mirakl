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
use Magento\Framework\Exception\NoSuchEntityException;
use MultiSafepay\ConnectCore\Util\JsonHandler;
use MultiSafepay\Mirakl\Logger\Logger;
use MultiSafepay\Mirakl\Model\WebhookFactory;
use MultiSafepay\Mirakl\Config\Config;
use MultiSafepay\Mirakl\Util\WebhookUtil;

class Webhook extends Action implements CsrfAwareActionInterface, HttpPostActionInterface
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
     * @var Config
     */
    private $config;

    /**
     * @var WebhookUtil
     */
    private $webhookUtil;

    /**
     * Notification constructor.
     *
     * @param Logger $logger
     * @param JsonHandler $jsonHandler
     * @param WebhookUtil $webhookUtil
     * @param Config $config
     * @param Context $context
     */
    public function __construct(
        Logger $logger,
        JsonHandler $jsonHandler,
        WebhookUtil $webhookUtil,
        Config $config,
        Context $context
    ) {
        $this->logger = $logger;
        $this->jsonHandler = $jsonHandler;
        $this->webhookUtil = $webhookUtil;
        $this->config = $config;
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
     * Process the UPDATE webhook HTTP POST request.
     * Mirakl documentation states that the endpoint must acknowledge an event within a 1s period.
     *
     * @see https://help.mirakl.net/bundle/customers/page/topics/Mirakl/mmp/Operator/config_webhooks.htm
     *
     * @return ResponseInterface
     * @throws Exception
     *
     * @phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(): ResponseInterface
    {
        $miraklRequest = $this->getRequest()->getContent();
        $authorization = $this->getRequest()->getHeader('Authorization');

        $this->logger->logMiraklOrderWebhookRequest($miraklRequest);

        if ($authorization !== $this->config->getWebhookSecretKey()) {
            $errorCode = 401;

            $this->logger->logFailedMiraklOrderWebhookRequest(
                $miraklRequest,
                (string)$errorCode
            );

            return $this->getResponse()->setHttpResponseCode($errorCode);
        }

        $miraklUpdate = $this->jsonHandler->readJSON($miraklRequest);

        if (!isset($miraklUpdate['event_type']) || $miraklUpdate['event_type'] !== 'ORDER') {
            return $this->getResponse()->setContent('OK');
        }

        $payloads = $miraklUpdate['payload'] ?? null;

        if (!$payloads) {
            $errorCode = 422;

            $this->logger->logFailedMiraklOrderWebhookRequest(
                $miraklRequest,
                (string)$errorCode
            );

            return $this->getResponse()->setHttpResponseCode($errorCode);
        }

        foreach ($payloads as $payload) {
            $payloadType = $payload['type'] ?? null;
            $changes = $payload['details']['changes'] ?? null;

            if (!isset($payloadType) || $payloadType !== 'UPDATE' || !isset($changes) || !is_array($changes)) {
                continue;
            }

            foreach ($changes as $change) {
                if ($change['field'] !== 'STATE') {
                    continue;
                }

                if ($change['from'] !== 'SHIPPING' && $change['to'] !== 'SHIPPED') {
                    continue;
                }

                try {
                    $this->webhookUtil->getWebhookRequest($payload['id']);

                    continue;
                } catch (NoSuchEntityException $exception) {
                    // Webhook request does not exist, continue processing
                }

                try {
                    $this->webhookUtil->savePayloadWebhookRequest($payload);
                } catch (Exception|AlreadyExistsException $exception) {
                    $this->logger->logWebhookException($payload['id'], $exception);

                    return $this->getResponse()->setHttpResponseCode(500);
                }
            }
        }

        return $this->getResponse()->setContent('OK');
    }
}
