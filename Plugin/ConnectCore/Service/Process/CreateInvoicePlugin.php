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
use MultiSafepay\ConnectCore\Service\Process\CreateInvoice;
use MultiSafepay\ConnectCore\Service\Transaction\StatusOperation\StatusOperationInterface;
use MultiSafepay\Mirakl\Util\ShoppingCartUtil;

class CreateInvoicePlugin
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
     * Skip the invoice process when the order only contains products from sellers
     *
     * @param CreateInvoice $createInvoice
     * @param callable $proceed
     * @param OrderInterface $order
     * @param array $transaction
     * @return array
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundExecute(
        CreateInvoice $createInvoice,
        callable $proceed,
        OrderInterface $order,
        array $transaction
    ): array {
        // If the order only contains products from sellers, this step must be skipped;
        // because $order->canInvoice() will return false, and that will stop the notification process
        if (!$this->shoppingCartUtil->hasMiraklOperatorProducts($order->getItems()) &&
            !$this->shoppingCartUtil->isMixedShoppingCart($order->getItems())) {
            return [StatusOperationInterface::SUCCESS_PARAMETER => true];
        }
        return $proceed($order, $transaction);
    }
}
