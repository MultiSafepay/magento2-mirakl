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

namespace MultiSafepay\Mirakl\Builder\OrderRequestBuilder\ShoppingCartBuilder;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Sales\Api\Data\OrderInterface;
use MultiSafepay\Api\Transactions\OrderRequest\Arguments\ShoppingCart\Item;
use MultiSafepay\ConnectCore\Config\Config;
use MultiSafepay\ConnectCore\Logger\Logger;
use MultiSafepay\ConnectCore\Model\Api\Builder\OrderRequestBuilder\ShoppingCartBuilder\ShoppingCartBuilderInterface;
use MultiSafepay\ValueObject\Money;

class MarketplaceShippingTotalBuilder implements ShoppingCartBuilderInterface
{
    /**
     * @var CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * MarketplaceShippingTotalBuilder constructor.
     *
     * @param Config $config
     * @param CartRepositoryInterface $quoteRepository
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        CartRepositoryInterface $quoteRepository,
        Logger $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Build the Item object for the Mirakl marketplace_shipping total
     *
     * @param OrderInterface $order
     * @param string $currency
     * @return array
     */
    public function build(OrderInterface $order, string $currency): array
    {
        $items = [];

        if (($quote = $this->getQuoteFromOrder($order)) === null) {
            return $items;
        }

        $totals = $quote->getTotals();

        if (!isset($totals['marketplace_shipping'])) {
            return $items;
        }

        $items[] = $this->buildItem($totals['marketplace_shipping'], $currency, $quote);

        return $items;
    }

    /**
     * Return the Item object for the given marketplace_shipping total.
     *
     * @param $total
     * @param string $currency
     * @param CartInterface $quote
     * @return Item
     */
    private function buildItem($total, string $currency, CartInterface $quote): Item
    {
        $title = $this->getTitle($total);

        $unitPrice = $total->getAmount() ? $this->getAmount($total, $quote->getStoreId()) : $total->getValue();

        return (new Item())
            ->addName($title)
            ->addUnitPrice(new Money(round($unitPrice * 100, 10), $currency))
            ->addQuantity(1)
            ->addDescription($title)
            ->addMerchantItemId($total->getCode())
            ->addTaxRate($this->getTaxRate($quote));
    }

    /**
     * Return the tax rate for the Mirakl marketplace_shipping total
     *
     * @param CartInterface $quote
     * @return float
     */
    private function getTaxRate(CartInterface $quote): float
    {
        if (!(float)$quote->getMiraklShippingTaxAmount()) {
            return 0;
        }

        if (!(float)$quote->getMiraklBaseShippingExclTax()) {
            return 0;
        }

        return round($quote->getMiraklShippingTaxAmount() / $quote->getMiraklBaseShippingExclTax() * 100);
    }

    /**
     * Return the total amount for the Mirakl marketplace_shipping total
     *
     * @param $total
     * @param int $storeId
     * @return float
     */
    private function getAmount($total, int $storeId): float
    {
        if ($this->config->useBaseCurrency($storeId)) {
            return (float)$total->getBaseAmount();
        }

        return (float)$total->getAmount();
    }

    /**
     * Return the title for the Mirakl marketplace_shipping total
     *
     * @param $total
     * @return string
     */
    private function getTitle($total): string
    {
        $title = $total->getTitle() ?: $total->getLabel();

        if ($title instanceof Phrase) {
            return (string)$title->render();
        }

        return (string)$title;
    }

    /**
     * Return the quote from the Order object.
     *
     * @param OrderInterface $order
     * @return CartInterface|null
     */
    private function getQuoteFromOrder(OrderInterface $order): ?CartInterface
    {
        try {
            return $this->quoteRepository->get($order->getQuoteId());
        } catch (NoSuchEntityException $noSuchEntityException) {
            $this->logger->error(
                __(
                    'Order ID: %1, Can\'t instantiate the quote. Error: %2',
                    $order->getIncrementId(),
                    $noSuchEntityException->getMessage()
                )
            );
        }

        return null;
    }
}
