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

namespace MultiSafepay\Mirakl\Cron\Process;

use Exception;
use MultiSafepay\Mirakl\Exception\CronProcessException;
use MultiSafepay\Mirakl\Model\CustomerDebit;
use MultiSafepay\Mirakl\Model\CustomerRefund;
use MultiSafepay\Mirakl\Util\CustomerDebitUtil;

class VerifyRefund
{
    /**
     * @var CustomerDebitUtil
     */
    private $customerDebitUtil;

    /**
     * @param CustomerDebitUtil $customerDebitUtil
     */
    public function __construct(
        CustomerDebitUtil $customerDebitUtil
    ) {
        $this->customerDebitUtil = $customerDebitUtil;
    }

    /**
     * Check if the refund can be processed
     *
     * @throws Exception
     */
    public function execute(array $refundRequestData)
    {
        $customerDebit = $this->customerDebitUtil->getCustomerDebit($refundRequestData[CustomerRefund::ORDER_ID]);

        if ($customerDebit->getStatus() !== CustomerDebit::CUSTOMER_DEBIT_STATUS_PROCESSED_SUCCESSFULLY) {
            throw new CronProcessException(
                'Refund can not be processed, wrong CustomerDebit status: ' . $customerDebit->getStatus()
            );
        }
    }
}
