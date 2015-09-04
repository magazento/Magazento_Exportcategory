<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Convert profile edit tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Magazento_CoreExport_Block_System_Convert_Gui_Edit_Tab_Wizard extends Mage_Adminhtml_Block_Widget_Container
//Mage_Adminhtml_Block_System_Convert_Gui_Edit_Tab_Wizard
//Mage_Adminhtml_Block_Widget_Container
{

    protected $_storeModel;
    protected $_attributes;
    protected $_addMapButtonHtml;
    protected $_removeMapButtonHtml;
    protected $_shortDateFormat;

    public function __construct() {
        Mage::helper('coreexport')->log('Magazento_CoreExport_Block_System_Convert_Gui_Edit_Tab_Wizard');

        parent::__construct();
        $this->setTemplate('magazento_coreexport/system/convert/profile/wizard.phtml');
       
    }

    protected function _prepareLayout() {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->setCanLoadCalendarJs(true);
        }
        return $this;
    }

    public function getAttributes($entityType) {
        if (!isset($this->_attributes[$entityType])) {
            switch ($entityType) {
                case 'product':
                    $attributes = Mage::getSingleton('catalog/convert_parser_product')
                            ->getExternalAttributes();
                    break;

                case 'customer':
                    $attributes = Mage::getSingleton('customer/convert_parser_customer')
                            ->getExternalAttributes();
                    break;
                case 'attribute':
                    $attributes = Mage::getSingleton('attributeexport/convert_parser_attribute')
                            ->getExternalAttributes();
                    break;
                case 'review':
                    $attributes = Mage::getSingleton('reviewexport/convert_parser_review')
                            ->getExternalAttributes();
                    break;
                case 'category':
                    $attributes = Mage::getSingleton('categoryexport/convert_parser_category')
                        ->getExternalAttributes();
                    break;
                case 'order':
                    $attributes = Mage::getSingleton('orderexport/convert_parser_order')
                        ->getExternalAttributes();
                    break;
            }

            array_splice($attributes, 0, 0, array('' => $this->__('Choose an attribute')));
            $this->_attributes[$entityType] = $attributes;
        }
        return $this->_attributes[$entityType];
    }

    public function getValue($key, $default = '', $defaultNew = null) {
        if (null !== $defaultNew) {
            if (0 == $this->getProfileId()) {
                $default = $defaultNew;
            }
        }

        $value = $this->getData($key);
        return $this->htmlEscape(strlen($value) > 0 ? $value : $default);
    }

    public function getSelected($key, $value) {
//        var_dump($this->getData($key));
        return $this->getData($key) == $value ? 'selected="selected"' : '';
    }

    public function getChecked($key) {
        return $this->getData($key) ? 'checked="checked"' : '';
    }

    public function getMappings($entityType) {
        $maps = $this->getData('gui_data/map/' . $entityType . '/db');
        return $maps ? $maps : array();
    }

    public function getAddMapButtonHtml() {
        if (!$this->_addMapButtonHtml) {
            $this->_addMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                            ->setClass('add')->setLabel($this->__('Add Field Mapping'))
                            ->setOnClick("addFieldMapping()")->toHtml();
        }
        return $this->_addMapButtonHtml;
    }

    public function getRemoveMapButtonHtml() {
        if (!$this->_removeMapButtonHtml) {
            $this->_removeMapButtonHtml = $this->getLayout()->createBlock('adminhtml/widget_button')->setType('button')
                            ->setClass('delete')->setLabel($this->__('Remove'))
                            ->setOnClick("removeFieldMapping(this)")->toHtml();
        }
        return $this->_removeMapButtonHtml;
    }

    public function getProductTypeFilterOptions() {
        $options = Mage::getSingleton('catalog/product_type')->getOptionArray();
        array_splice($options, 0, 0, array('' => $this->__('Any Type')));
        return $options;
    }

    public function getProductAttributeSetFilterOptions() {
        $options = Mage::getResourceModel('eav/entity_attribute_set_collection')
                ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
                ->load()
                ->toOptionHash();

        $opt = array();
        $opt = array('' => $this->__('Any Attribute Set'));
        if ($options)
            foreach ($options as $index => $value) {
                $opt[$index] = $value;
            }
        //array_slice($options, 0, 0, array(''=>$this->__('Any Attribute Set')));
        return $opt;
    }

    public function getProductVisibilityFilterOptions() {
        $options = Mage::getSingleton('catalog/product_visibility')->getOptionArray();

        array_splice($options, 0, 0, array('' => $this->__('Any Visibility')));
        return $options;
    }

    public function getProductStatusFilterOptions() {
        $options = Mage::getSingleton('catalog/product_status')->getOptionArray();

        array_splice($options, 0, 0, array('' => $this->__('Any Status')));
        return $options;
    }

    public function getStoreFilterOptions() {
        if (!$this->_filterStores) {
            #$this->_filterStores = array(''=>$this->__('Any Store'));
            $this->_filterStores = array();
            foreach (Mage::getConfig()->getNode('stores')->children() as $storeNode) {
                if ($storeNode->getName() === 'default') {
                    //continue;
                }
                $this->_filterStores[$storeNode->getName()] = (string) $storeNode->system->store->name;
            }
        }
        return $this->_filterStores;
    }

    public function getCustomerGroupFilterOptions() {
        $options = Mage::getResourceModel('customer/group_collection')
                ->addFieldToFilter('customer_group_id', array('gt' => 0))
                ->load()
                ->toOptionHash();

        array_splice($options, 0, 0, array('' => $this->__('Any Group')));
        return $options;
    }

    public function getCountryFilterOptions() {
        $options = Mage::getResourceModel('directory/country_collection')
                        ->load()->toOptionArray(false);
        array_unshift($options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('All countries')));
        return $options;
    }

    /**
     * Retrieve system store model
     *
     * @return Mage_Adminhtml_Model_System_Store
     */
    protected function _getStoreModel() {
        if (is_null($this->_storeModel)) {
            $this->_storeModel = Mage::getSingleton('adminhtml/system_store');
        }
        return $this->_storeModel;
    }

    public function getWebsiteCollection() {
        return $this->_getStoreModel()->getWebsiteCollection();
    }

    public function getGroupCollection() {
        return $this->_getStoreModel()->getGroupCollection();
    }

    public function getStoreCollection() {
        return $this->_getStoreModel()->getStoreCollection();
    }

    public function getStoreFilter() {
        $result = array();
        foreach ($this->getStoreCollection() as $store) {
            $result[$store['store_id']] = $store['name'];
        }
        return $result;
    }

    public function getShortDateFormat() {
        if (!$this->_shortDateFormat) {
            $this->_shortDateFormat = Mage::app()->getLocale()->getDateStrFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);
        }
        return $this->_shortDateFormat;
    }

    public function getCategoryStatusFilterOptions() {
        $array = array('0' => 'All', '1' => 'Only active');
        return $array;
    }

    public function getOrderStatusFilterOptions() {
        $model = Mage::getResourceModel('sales/order_status_collection');
        // var_dump($model->ToOptionArray());
        $res = array();
        foreach ($model->ToOptionArray() as $status) {
            $res[$status['value']] = $status['label'];
        }
        array_splice($res, 0, 0, array('' => $this->__('Any Statuses')));
        return $res;
    }

    public function getStoreSelectOptions() {
        $storeModel = Mage::getSingleton('adminhtml/system_store');
        /* @var $storeModel Mage_Adminhtml_Model_System_Store */

        $options = array();
        $options[0] = array(
            'label' => Mage::helper('adminhtml')->__('All Stores'),
            //'selected' => !$curWebsite && !$curStore,
            'style' => 'background:#ccc; font-weight:bold;',
        );
        foreach ($storeModel->getWebsiteCollection() as $website) {
            $websiteShow = false;
            foreach ($storeModel->getGroupCollection() as $group) {
                if ($group->getWebsiteId() != $website->getId()) {
                    continue;
                }
                $groupShow = false;
                foreach ($storeModel->getStoreCollection() as $store) {
                    if ($store->getGroupId() != $group->getId()) {
                        continue;
                    }
                    if (!$websiteShow) {
                        $websiteShow = true;
                        //     $options['website_' . $website->getCode()] = array(
                        $options['group_' . $website->getId()] = array(
                            'is_group' => true,
                            'label' => $website->getName(),
                            //  'url' => $url->getUrl('*/*/*', array('section' => $section, 'website' => $website->getCode())),
                            //         'selected' => !$curStore && $curWebsite == $website->getCode(),
                            'style' => 'padding-left:16px; background:#DDD; font-weight:bold;',
                        );
                    }
                    if (!$groupShow) {
                        $groupShow = true;
                        $options['group_' . $group->getId() . '_open'] = array(
                            'is_group' => true,
                            'is_close' => false,
                            'label' => $group->getName(),
                            'style' => 'padding-left:32px;'
                        );
                    }
                    //        $options['store_' . $store->getCode()] = array(
                    $options[$store->getId()] = array(
                        'label' => $store->getName(),
                        //   'url' => $url->getUrl('*/*/*', array('section' => $section, 'website' => $website->getCode(), 'store' => $store->getCode())),
                        'selected' => $curStore == $store->getCode(),
                        'style' => '',
                    );
                }
                if ($groupShow) {
                    $options['group_' . $group->getId() . '_close'] = array(
                        'is_group' => true,
                        'is_close' => true,
                    );
                }
            }
        }

        return $options;
    }

    public function getReviewStatusFilterOptions(){
        $option=array();
        foreach (Mage::getModel('review/review')->getStatusCollection() as $status){
            $option[$status->getId()]=$status->getStatusCode();
        }
          array_splice($option, 0, 0, array('' => $this->__('Any Statuses')));
        return $option;
        
    }

}

