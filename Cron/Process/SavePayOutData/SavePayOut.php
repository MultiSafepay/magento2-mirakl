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

namespace MultiSafepay\Mirakl\Cron\Process\SavePayOutData;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Mirakl\MMP\FrontOperator\Domain\Order as MiraklOrder;
use MultiSafepay\ConnectCore\Util\OrderUtil;
use MultiSafepay\Mirakl\Model\PayOut;
use MultiSafepay\Mirakl\Model\PayOutFactory;
use MultiSafepay\Mirakl\Model\ResourceModel\PayOut as PayOutResourceModel;

class SavePayOut
{
    /**
     * @var PayOutFactory
     */
    private $payOutFactory;

    /**
     * @var PayOutResourceModel
     */
    private $payOutResourceModel;
    /**
     * @var OrderUtil
     */
    private $orderUtil;

    public function __construct(
        OrderUtil $orderUtil,
        PayOutFactory $payOutFactory,
        PayOutResourceModel $payOutResourceModel
    ) {
        $this->orderUtil = $orderUtil;
        $this->payOutFactory = $payOutFactory;
        $this->payOutResourceModel = $payOutResourceModel;
    }

    /**
     * Save the MiraklOrder Payout Data
     *
     * @param MiraklOrder $miraklOrder
     * @param string $orderCommercialId
     * @return PayOut|null
     * @throws AlreadyExistsException
     * @throws NoSuchEntityException
     */
    public function execute(MiraklOrder $miraklOrder, string $orderCommercialId): ?PayOut
    {
        $storeId = (int)$this->getOrder($orderCommercialId)->getStoreId();

        /** @var PayOut $payOut */
        $payOut = $this->payOutFactory->create();
        $payOut->setMagentoStoreId($storeId);
        $payOut->setMiraklShopId((int)$miraklOrder->getShopId());
        $payOut->setMagentoOrderId($miraklOrder->getCommercialId());
        $payOut->setMiraklCurrencyIsoCode($miraklOrder->getCurrencyIsoCode());
        $payOut->setMiraklOrderId($miraklOrder->getId());

        $this->payOutResourceModel->save($payOut);

        return $payOut;
    }

    /**
     * Retrieve the Magento Order
     *
     * @param $orderCommercialId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    private function getOrder($orderCommercialId): OrderInterface
    {
        return $this->orderUtil->getOrderByIncrementId($orderCommercialId);
    }
}
