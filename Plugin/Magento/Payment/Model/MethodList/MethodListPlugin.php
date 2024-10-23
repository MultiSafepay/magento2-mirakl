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

namespace MultiSafepay\Mirakl\Plugin\Magento\Payment\Model\MethodList;

use Magento\Payment\Gateway\Config\Config;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Model\MethodList;
use Magento\Quote\Api\Data\CartInterface;
use MultiSafepay\Mirakl\Gateway\Validator\BnplValidator;

class MethodListPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var BnplValidator
     */
    private $bnplValidator;

    /**
     * MethodListPlugin constructor.
     *
     * @param BnplValidator $bnplValidator
     * @param Config $config
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        BnplValidator $bnplValidator,
        Config $config
    ) {
        $this->bnplValidator = $bnplValidator;
        $this->config = $config;
    }

    /**
     * @param MethodList $subject
     * @param $availableMethods
     * @param CartInterface $quote
     * @return MethodInterface[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAvailableMethods(
        MethodList $subject,
        $availableMethods,
        CartInterface $quote
    ): array {

        foreach ($availableMethods as $key => $method) {
            $methodCode = $method->getCode();
            $this->config->setMethodCode($methodCode);
            if ($this->bnplValidator->validate($quote, $this->config, $methodCode)) {
                unset($availableMethods[$key]);
            }
        }

        return $availableMethods;
    }
}
