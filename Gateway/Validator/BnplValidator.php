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

namespace MultiSafepay\Mirakl\Gateway\Validator;

use Magento\Payment\Gateway\Config\Config;
use Magento\Quote\Model\Quote;
use MultiSafepay\Mirakl\Util\BuyNowPayLaterUtil;
use MultiSafepay\Mirakl\Util\ShoppingCartUtil;

class BnplValidator
{

    /**
     * @var ShoppingCartUtil
     */
    private $shoppingCartUtil;

    /**
     * @param ShoppingCartUtil $shoppingCartUtil
     */
    public function __construct(
        ShoppingCartUtil $shoppingCartUtil
    ) {
        $this->shoppingCartUtil = $shoppingCartUtil;
    }

    /**
     * @param Quote $quote
     * @param Config $config
     * @param string $methodCode
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function validate(Quote $quote, Config $config, string $methodCode): bool
    {
        if (!in_array($methodCode, BuyNowPayLaterUtil::BNPL_PAYMENT_METHODS, true)) {
            return false;
        }

        if ($this->shoppingCartUtil->hasMultipleSellers($quote->getItems())) {
            return true;
        }

        return false;
    }
}
