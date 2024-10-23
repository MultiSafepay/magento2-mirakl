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

namespace MultiSafepay\Mirakl\Util;

use MultiSafepay\ConnectCore\Model\Ui\Gateway\AfterpayConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\BnplinstmConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\BnplmfConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\EinvoicingConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\In3B2bConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\In3ConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\KlarnaConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\PayafterConfigProvider;
use MultiSafepay\ConnectCore\Model\Ui\Gateway\ZiniaConfigProvider;

class BuyNowPayLaterUtil
{
    /**
     * BNPL payment methods
     */
    public const BNPL_PAYMENT_METHODS = [
        BnplinstmConfigProvider::CODE,
        PayafterConfigProvider::CODE,
        In3ConfigProvider::CODE,
        In3B2bConfigProvider::CODE,
        KlarnaConfigProvider::CODE,
        AfterpayConfigProvider::CODE,
        ZiniaConfigProvider::CODE,
        EinvoicingConfigProvider::CODE,
        BnplmfConfigProvider::CODE
    ];
}
