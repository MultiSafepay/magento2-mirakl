<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is provided with Magento in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * See DISCLAIMER.md for disclaimer details
 */

declare(strict_types=1);

namespace MultiSafepay\Mirakl\Api\Mirakl\Client;

use Mirakl\Api\Helper\Config as MiraklApiHelperConfig;
use Mirakl\MMP\Front\Client\FrontApiClient as MiraklFrontApiClient;

class FrontApiClient
{
    /**
     * @var MiraklApiHelperConfig
     */
    private $miraklApiHelperConfig;

    /**
     * FrontApiClientFactory constructor.
     *
     * @param MiraklApiHelperConfig $miraklApiHelperConfig
     */
    public function __construct(
        MiraklApiHelperConfig $miraklApiHelperConfig
    ) {
        $this->miraklApiHelperConfig = $miraklApiHelperConfig;
    }

    /**
     * Get an instance of the FrontApiClient
     *
     * @return MiraklFrontApiClient
     */
    public function get(): MiraklFrontApiClient
    {
        return new MiraklFrontApiClient(
            $this->miraklApiHelperConfig->getApiUrl(),
            $this->miraklApiHelperConfig->getApiKey()
        );
    }
}
