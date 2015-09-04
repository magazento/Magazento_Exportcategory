<?php

/*
* @category   Magazento
* @package    Magazento_CoreExport
* @author     Kate Mironova
* @author     Ivan Proskuryakov
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Magazento_CoreExport_Block_Info extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {

        public function render(Varien_Data_Form_Element_Abstract $element) {

            $html = $this->_getHeaderHtml($element);

            $html.= $this->_getFieldHtml($element);

            $html .= $this->_getFooterHtml($element);

            return $html;
        }

        protected function _getFieldHtml($fieldset) {
            $content = 'This extension is developed by <a href="http://Magazento.com/" target="_blank">Magazento.com</a><br/>';
            $content.= 'Magento Store Setup, modules, data migration, templates, upgrades and much more!';
            return $content;
        }


}
