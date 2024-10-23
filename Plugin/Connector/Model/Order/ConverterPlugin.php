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

namespace MultiSafepay\Mirakl\Plugin\Connector\Model\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Mirakl\Connector\Model\Order\Converter;
use Mirakl\MMP\Common\Domain\Payment\PaymentWorkflow;
use Mirakl\MMP\Front\Domain\Order\Create\CreateOrder;
use MultiSafepay\Mirakl\Util\BuyNowPayLaterUtil;

class ConverterPlugin
{
    /**
     * Dynamically override the payment workflow based on the payment method
     *
     * If the payment method is a BNPL payment method, set the payment workflow to PAY_ON_DELIVERY
     * otherwise set the payment workflow to PAY_ON_ACCEPTANCE
     *
     * @param Converter $subject
     * @param CreateOrder $result
     * @param OrderInterface $order
     *
     * @return CreateOrder
     *
     * @phpcs:disable Magento2.CodeAnalysis.EmptyBlock
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterConvert(Converter $subject, CreateOrder $result, OrderInterface $order): CreateOrder
    {
        $payment = $order->getPayment();

        if ($payment === null) {
            return $result;
        }

        if (in_array($payment->getMethod(), BuyNowPayLaterUtil::BNPL_PAYMENT_METHODS, true)) {
            return $result->setPaymentWorkflow(PaymentWorkflow::PAY_ON_DELIVERY);
        }

        try {
            if ($payment->getMethodInstance()->getConfigData('is_multisafepay') === '1') {
                return $result->setPaymentWorkflow(PaymentWorkflow::PAY_ON_ACCEPTANCE);
            };
        } catch (LocalizedException $exception) {
            // Do nothing
        }

        return $result;
    }
}
