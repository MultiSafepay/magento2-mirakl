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

namespace MultiSafepay\Mirakl\Block\Adminhtml\Form\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use MultiSafepay\ConnectCore\Config\Config;
use Magento\Framework\Data\Form\Element\AbstractElement;

class AffiliateConditionalField extends Field
{
    public const ID_TEST_FIELDS = [
        'multisafepay_mirakl_multisafepay_mirakl_affiliate_test_api_key',
        'multisafepay_mirakl_multisafepay_mirakl_collecting_test_account_id'
    ];

    public const ID_LIVE_FIELDS = [
        'multisafepay_mirakl_multisafepay_mirakl_affiliate_live_api_key',
        'multisafepay_mirakl_multisafepay_mirakl_collecting_live_account_id'
    ];

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Config $config,
        Context $context,
        array $data = []
    ) {
        $this->config = $config;
        parent::__construct($context, $data);
    }

    /**
     * Decorate field row html
     *
     * @param AbstractElement $element
     * @param string $html
     * @return string
     */
    protected function _decorateRowHtml(AbstractElement $element, $html): string
    {
        $showFieldHtml = '<tr id="row_' . $element->getHtmlId() . '">' . $html . '</tr>';
        $hideFieldHtml = '<tr id="row_' . $element->getHtmlId() . '" style="display:none">' . $html . '</tr>';

        if (in_array($element->getHtmlId(), self::ID_LIVE_FIELDS)) {
            if ($this->config->isLiveMode()) {
                return $showFieldHtml;
            }

            return $hideFieldHtml;
        }

        if (in_array($element->getHtmlId(), self::ID_TEST_FIELDS)) {
            if (!$this->config->isLiveMode()) {
                return $showFieldHtml;
            }

            return $hideFieldHtml;
        }

        return $showFieldHtml;
    }
}
