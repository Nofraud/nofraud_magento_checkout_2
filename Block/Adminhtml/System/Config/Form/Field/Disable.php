<?php
/**
 * Created by Nofraud Checkout
 * Author: Sam Umaretiya
 * Date: 18/01/2023
 * Time: 9:41
 */

namespace NoFraud\Checkout\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Disable extends \Magento\Config\Block\System\Config\Form\Field
{
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }
}