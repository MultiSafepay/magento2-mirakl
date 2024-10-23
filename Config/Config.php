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

namespace MultiSafepay\Mirakl\Config;

use Magento\Store\Model\ScopeInterface;
use MultiSafepay\ConnectCore\Config\Config as CoreConfig;
use Exception;

class Config extends CoreConfig
{
    public const MIRAKL_PATH_PATTERN = 'multisafepay/mirakl/%s';

    public const COLLECTING_TEST_ACCOUNT_ID = 'collecting_test_account_id';
    public const COLLECTING_LIVE_ACCOUNT_ID = 'collecting_live_account_id';
    public const AFFILIATE_LIVE_API_KEY = 'affiliate_live_api_key';
    public const AFFILIATE_TEST_API_KEY = 'affiliate_test_api_key';
    public const WEBHOOK_SECRET_KEY = 'webhook_secret_key';

    /**
     * Generic method to return config values from MultiSafepay Mirakl module
     *
     * @param string $field
     * @param null $storeId
     * @return mixed
     */
    public function getMiraklValue(string $field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            sprintf(self::MIRAKL_PATH_PATTERN, $field),
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return the affiliate API Key, according the selected environment
     *
     * @param null $storeId
     * @return string
     * @throws Exception
     */
    public function getAffiliateApiKey($storeId = null): string
    {
        return !$this->isLiveMode($storeId)
            ? $this->encryptorUtil->decrypt((string)$this->getMiraklValue(self::AFFILIATE_TEST_API_KEY, $storeId))
            : $this->encryptorUtil->decrypt((string)$this->getMiraklValue(self::AFFILIATE_LIVE_API_KEY, $storeId));
    }

    /**
     * Return the commission collecting account ID, according the selected environment
     *
     * @param null $storeId
     * @return string
     */
    public function getCollectingAccountId($storeId = null): string
    {
        return !$this->isLiveMode($storeId)
            ? (string)$this->getMiraklValue(self::COLLECTING_TEST_ACCOUNT_ID, $storeId)
            : (string)$this->getMiraklValue(self::COLLECTING_LIVE_ACCOUNT_ID, $storeId);
    }

    /**
     * Get the webhook secret key
     *
     * @param null $storeId
     * @return string
     * @throws Exception
     */
    public function getWebhookSecretKey($storeId = null): string
    {
        return $this->encryptorUtil->decrypt((string)$this->getMiraklValue(self::WEBHOOK_SECRET_KEY, $storeId));
    }
}
