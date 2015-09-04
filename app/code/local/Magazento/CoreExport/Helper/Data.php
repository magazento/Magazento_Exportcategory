<?php

/*
* @category   Magazento
* @package    Magazento_CoreExport
* @author     Kate Mironova
* @author     Ivan Proskuryakov
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Magazento_CoreExport_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function log($value) {
        Mage::log($value, null, 'magazento_coreexport.log');
    }

}