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

namespace MultiSafepay\Mirakl\Controller\Adminhtml\Api;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseInterface;
use MultiSafepay\ConnectCore\Util\JsonHandler;
use MultiSafepay\Exception\InvalidDataInitializationException;
use MultiSafepay\Mirakl\Config\Config;
use MultiSafepay\Mirakl\Factory\AffiliatesSdkFactory;
use Psr\Http\Client\ClientExceptionInterface;

class ValidateAffiliate extends Action
{
    private const MODE_PARAM_KEY_NAME = 'mode';
    private const API_KEY_PARAM_KEY_NAME = 'apiKey';

    /**
     * @var JsonHandler
     */
    private $jsonHandler;

    /**
     * @var AffiliatesSdkFactory
     */
    private $affiliatesSdkFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * Validate constructor.
     *
     * @param Context $context
     * @param JsonHandler $jsonHandler
     * @param AffiliatesSdkFactory $affiliatesSdkFactory
     * @param Config $config
     * @param MiraklConfig $miraklConfig
     */
    public function __construct(
        Context $context,
        JsonHandler $jsonHandler,
        AffiliatesSdkFactory $affiliatesSdkFactory,
        Config $config
    ) {
        parent::__construct($context);
        $this->jsonHandler = $jsonHandler;
        $this->affiliatesSdkFactory = $affiliatesSdkFactory;
        $this->config = $config;
    }

    /**
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface
    {
        /** @var Http $response */
        $response = $this->getResponse();

        try {
            if (($data = $this->getRequest()->getParams())
                && isset($data[self::MODE_PARAM_KEY_NAME], $data[self::API_KEY_PARAM_KEY_NAME])
            ) {
                $key = (string)$data[self::API_KEY_PARAM_KEY_NAME];

                if (strpos($key, '****') !== false) {
                    $key = $this->config->getAffiliateApiKey((int)$data['storeId']);
                }

                if (substr($key, 0, 2) !== 'm_') {
                    return $this->getResponse()->representJson(
                        $this->jsonHandler->convertToJSON(
                            [
                                'status' => false,
                                'content' => __('Error. Account API Key should start with "m_."'),
                            ]
                        )
                    );
                }

                $this->affiliatesSdkFactory->createWithModeAndApiKey(
                    (bool)$data[self::MODE_PARAM_KEY_NAME],
                    $key
                )->getAccountManager()->get();

                $result = [
                    'status' => true,
                    'content' => __('API key is valid.'),
                ];
            } else {
                $result = [
                    'status' => false,
                    'content' => __('Error. Something went wrong. Please, try again.'),
                ];
            }
        } catch (InvalidDataInitializationException $invalidDataInitializationException) {
            $result = [
                'status' => false,
                'content' => 'Invalid API Key',
            ];
        } catch (ClientExceptionInterface $clientException) {
            $result = [
                'status' => false,
                'content' => $clientException->getMessage(),
            ];
        } catch (Exception $exception) {
            $result = [
                'status' => false,
                'content' => $exception->getMessage(),
            ];
        }

        return $response->representJson(
            $this->jsonHandler->convertToJSON($result)
        );
    }
}
