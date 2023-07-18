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

namespace MultiSafepay\Mirakl\Plugin\ConnectCore\Service\Process;

use Magento\Sales\Api\Data\OrderInterface;
use MultiSafepay\ConnectCore\Service\Transaction\StatusOperation\StatusOperationInterface;
use MultiSafepay\ConnectCore\Service\Process\SendOrderConfirmation;
use MultiSafepay\Mirakl\Util\ShoppingCartUtil;

class SendOrderConfirmationPlugin
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
     * Skip the process to send the order confirmation email when the order contains products from sellers
     *
     * @param SendOrderConfirmation $sendOrderConfirmation
     * @param callable $proceed
     * @param OrderInterface $order
     * @param array $transaction
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        SendOrderConfirmation $sendOrderConfirmation,
        callable $proceed,
        OrderInterface $order,
        array $transaction
    ): array {
        if (!$this->shoppingCartUtil->hasMiraklSellerProducts($order->getItems())) {
            return $proceed($order, $transaction);
        }
        return [StatusOperationInterface::SUCCESS_PARAMETER => true, 'save_order' => false];
    }
}
