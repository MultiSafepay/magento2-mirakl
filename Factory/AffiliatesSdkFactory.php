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

namespace MultiSafepay\Mirakl\Factory;

use Exception;
use MultiSafepay\ConnectCore\Client\Client;
use MultiSafepay\ConnectCore\Config\Config;
use MultiSafepay\Mirakl\Config\Config as MiraklConfig;
use MultiSafepay\ConnectCore\Factory\SdkFactory;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\AffiliatesSdk;
use Nyholm\Psr7\Factory\Psr17Factory;

class AffiliatesSdkFactory extends SdkFactory
{

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var Psr17Factory
     */
    protected $psr17Factory;

    /**
     * @var MiraklConfig
     */
    protected $miraklConfig;

    /**
     * Client constructor.
     *
     * @param Config $config
     * @param Client $client
     */
    public function __construct(
        Config $config,
        Client $client,
        MiraklConfig $miraklConfig
    ) {
        parent::__construct($config, $client);
        $this->miraklConfig = $miraklConfig;
    }

    /**
     * Get an instance of the AffiliatesSdk
     *
     * @param int|null $storeId
     * @return AffiliatesSdk
     * @throws Exception
     */
    public function createAffiliatesSdk(int $storeId = null): AffiliatesSdk
    {
        return new AffiliatesSdk(
            $this->miraklConfig->getAffiliateApiKey($storeId),
            $this->config->isLiveMode($storeId),
            $this->client,
            $this->psr17Factory,
            $this->psr17Factory
        );
    }
}
