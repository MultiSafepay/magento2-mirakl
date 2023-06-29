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

namespace MultiSafepay\Mirakl\Logger;

use MultiSafepay\ConnectCore\Logger\Handler as CoreHandler;

class Handler extends CoreHandler
{
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/multisafepay-mirakl.log';
}
