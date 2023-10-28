<?php
declare(strict_types=1);

namespace RltSquare\UpdateShippingPrice\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class for ImportButton
 */
class ImportButton extends Field
{
    /**
     * @return $this
     */
    public function _prepareLayout(): ImportButton
    {
        parent::_prepareLayout();
        $this->setTemplate('system/config/update.phtml');
        return $this;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function _getElementHtml(AbstractElement $element): string
    {
        return $this->_toHtml();
    }

    /**
     * @return string
     */
    public function getSendUrl(): string
    {
        return $this->getUrl(
            'csv_import/import/index/'
        );
    }
}
