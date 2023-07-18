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

namespace MultiSafepay\Mirakl\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use MultiSafepay\Api\Transactions\OrderRequest;
use MultiSafepay\Mirakl\Logger\Logger;
use Magento\Sales\Api\Data\OrderInterface;
use MultiSafepay\Mirakl\Util\ShoppingCartUtil;

class OrderRequestObserver implements ObserverInterface
{
    /**
     * @var ShoppingCartUtil
     */
    private $shoppingCartUtil;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * OrderRequestObserver constructor.
     *
     * @param Logger $logger
     */
    public function __construct(
        ShoppingCartUtil $shoppingCartUtil,
        Logger $logger
    ) {
        $this->shoppingCartUtil = $shoppingCartUtil;
        $this->logger = $logger;
    }

    /**
     * Log the details of the shopping cart, and add plugin meta info in the order request
     *
     * @param  Observer $observer
     * @throws Exception
     */
    public function execute(Observer $observer): void
    {
        /** @var OrderInterface $order */
        $order = $observer->getData('order');
        $this->logger->logShoppingCartDetails($order->getItems() ?? []);

        $this->addPluginMetaInformation($observer->getData('orderRequest'));
    }

    /**
     * Add meta information related with the plugin into the order request.
     *
     * @param  OrderRequest $orderRequest
     * @return void
     */
    private function addPluginMetaInformation(OrderRequest $orderRequest): void
    {
        $pluginDetails = $orderRequest->getPluginDetails();

        $applicationName = $pluginDetails->getApplicationName();
        $pluginDetails->addApplicationName($applicationName . ' - Mirakl');

        $miraklModuleVersion = 'unknown';
        $multiSafepayMiraklVersion = 'unknown';

        if (method_exists('\Composer\InstalledVersions', 'getVersion')) {
            $miraklModuleVersion = \Composer\InstalledVersions::getVersion('mirakl/connector-magento2-plugin');
            $multiSafepayMiraklVersion = \Composer\InstalledVersions::getVersion('multisafepay/magento2-mirakl');
        }

        $pluginVersion = $pluginDetails->getPluginVersion()->getPluginVersion();
        $pluginDetails->addPluginVersion($pluginVersion . ' - ' . $multiSafepayMiraklVersion);

        $applicationVersion = $pluginDetails->getApplicationVersion();
        $pluginDetails->addApplicationVersion($applicationVersion . ' - ' . $miraklModuleVersion);
    }
}
