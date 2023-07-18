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

namespace MultiSafepay\Mirakl\Api\MultiSafepay\Sdk;

use MultiSafepay\Mirakl\Api\MultiSafepay\Sdk\Affiliates\AffiliatesManager;
use MultiSafepay\Sdk;

class AffiliatesSdk extends Sdk
{
    /**
     * Return the AffiliatesManager object, used to process request related with affiliates
     *
     * @return AffiliatesManager
     */
    public function getAffiliatesManager(): AffiliatesManager
    {
        return new AffiliatesManager($this->client);
    }
}
