<?php

/*
* @category   Magazento
* @package    Magazento_CategoryExport
* @author     Kate Mironova
* @author     Ivan Proskuryakov
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Magazento_CategoryExport_Model_Status extends Varien_Object
{
    const STATUS_ENABLED	= 1;
    const STATUS_DISABLED	= 2;

    static public function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => Mage::helper('categoryexport')->__('Enabled'),
            self::STATUS_DISABLED   => Mage::helper('categoryexport')->__('Disabled')
        );
    }
}