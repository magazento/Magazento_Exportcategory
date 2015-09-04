<?php

/*
* @category   Magazento
* @package    Magazento_CategoryExport
* @author     Kate Mironova
* @author     Ivan Proskuryakov
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Magazento_CategoryExport_Model_Convert_Parser_Category extends Mage_Eav_Model_Convert_Parser_Abstract {

    protected $_categoryModel;
    protected $_resource;
    protected $_collections;
    protected $_store;
    protected $_storeId;
    protected $_stores;
    protected $_websites;
    protected $_attributes = array();
    protected $_fields;

    public function getFields() {
        if (!$this->_fields) {
            $this->_fields = Mage::getConfig()->getFieldset('category_dataflow', 'admin');
        }
        return $this->_fields;
    }

    public function getStoreById($storeId) {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true);
        }
        if (isset($this->_stores[$storeId])) {
            return $this->_stores[$storeId];
        }
        return false;
    }

    public function unparse() {
        $systemFields = array();
        foreach ($this->getFields() as $code => $node) {
            if ($node->is('system')) {
                $systemFields[] = $code;
            }
        }

        $entityIds = $this->getData();
        $collection = Mage::getResourceModel('catalog/category_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', array('in' => $entityIds))
                ->addAttributeToFilter('level', array('neq' => 0))
                ->addAttributeToSort('level', 'asc');
        foreach ($collection as $i => $category) {


            $position = Mage::helper('catalog')->__('Line %d, Name: %s', ($i + 1), $category->getUrlPath());
            $this->setPosition($position);

            $row = array();

            foreach ($category->getData() as $field => $value) {

                if ($field == 'website_id') {
                    $website = $this->getWebsiteById($value);
                    if ($website === false) {
                        $website = $this->getWebsiteById(0);
                    }
                    $row['website'] = $website->getCode();
                    continue;
                }

                if (in_array($field, $systemFields) || is_object($value)) {
                    continue;
                }

                $attribute = $this->getAttribute($field);
                if (!$attribute) {
                    continue;
                }

                if ($attribute->usesSource()) {

                    $option = $attribute->getSource()->getOptionText($value);
                    if ($value && empty($option)) {
                        $message = Mage::helper('catalog')->__("An invalid option ID is specified for %s (%s), skipping the record.", $field, $value);
                        $this->addException($message, Mage_Dataflow_Model_Convert_Exception::ERROR);
                        continue;
                    }
                    if (is_array($option)) {
                        $value = join(self::MULTI_DELIMITER, $option);
                    } else {
                        $value = $option;
                    }
                    unset($option);
                } elseif (is_array($value)) {
                    continue;
                }
                $row[$field] = $value;
            }
            $store = $this->getStoreById($category->getStoreId());
            if ($store === false) {
                $store = $this->getStoreById(0);
            }
            $row['created_in'] = $store->getCode();
            $productCollection = Mage::getResourceModel('catalog/product_collection')
                ->addCategoryFilter($category);

            // Disabled, because does not work with flat data
//            $categoryProducts = array();
//            foreach ($productCollection as $_product) {
//                $categoryProducts[] = $_product->getId();
//            }
//            $row['category_products'] = implode(',',$categoryProducts);

            $batchExport = $this->getBatchExportModel()
                    ->setId(null)
                    ->setBatchId($this->getBatchModel()->getId())
                    ->setBatchData($row)
                    ->setStatus(1)
                    ->save();
        }
        return $this;
    }

    public function getAttribute($code) {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $this->getCategoryModel()->getResource()->getAttribute($code);
        }
        return $this->_attributes[$code];
    }

    public function getResource() {
        if (!$this->_resource) {
            $this->_resource = Mage::getResourceSingleton('catalog_entity/convert');
        }
        return $this->_resource;
    }

    public function getCategoryModel() {
        if (is_null($this->_categoryModel)) {
            $object = Mage::getModel('catalog/category');
            $this->_categoryModel = Mage::objects()->save($object);
        }
        return Mage::objects()->load($this->_categoryModel);
    }

    public function parse() {
        parent::parse();
    }

    public function getExternalAttributes() {
        $model = Mage::getModel('catalog/category');
        $attributes = $model->getAttributes(true);
        $internal = array(
            'store_id',
            'entity_id',
            'website_id',
            'group_id',
            'created_in',
        );
        foreach ($attributes as $attr) {
            $code = $attr->getAttributeCode();
            if (!(in_array($code, $internal) || $attr->getFrontendInput() == 'hidden')) {
                $attributes[$code] = $code;
            }
        }
        return $attributes;
    }

    public function getWebsiteById($websiteId) {
        if (is_null($this->_websites)) {
            $this->_websites = Mage::app()->getWebsites(true);
        }
        if (isset($this->_websites[$websiteId])) {
            return $this->_websites[$websiteId];
        }
        return false;
    }

}

?>
