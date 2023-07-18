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
use MultiSafepay\ConnectCore\Factory\SdkFactory;
use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\AffiliatesSdk;
use MultiSafepay\Mirakl\Config\Config;
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
     * AffiliatesSdkFactory constructor.
     *
     * @param Config $config
     * @param Client $client
     */
    public function __construct(
        Config $config,
        Client $client
    ) {
        $this->config = $config;
        $this->client = $client;
        parent::__construct($config, $client);
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
            $this->config->getAffiliateApiKey($storeId),
            $this->config->isLiveMode($storeId),
            $this->client,
            $this->psr17Factory,
            $this->psr17Factory
        );
    }
}
