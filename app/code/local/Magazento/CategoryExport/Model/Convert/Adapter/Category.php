<?php

/*
* @category   Magazento
* @package    Magazento_CategoryExport
* @author     Kate Mironova
* @author     Ivan Proskuryakov
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Magazento_CategoryExport_Model_Convert_Adapter_Category extends Mage_Eav_Model_Convert_Adapter_Entity
{

    protected $_categoryModel;
    protected $_stores;
    protected $_attributes = array();

    const MULTI_DELIMITER = ' , ';

    public function __construct()
    {
        $this->setVar('entity_type', 'catalog/category');;
    }

    public function load()
    {
        //filter rules mast be there
        $attrFilterArray = array();
        $attrFilterArray ['name'] = 'like';
        $attrFilterArray ['meta_keywords'] = 'eq';
        $attrFilterArray ['is_active'] = 'eq';

        parent::setFilter($attrFilterArray);

        return parent::load();
        // return parent::load();
    }

    public function parse()
    {
        parent::parse();
    }

    public function saveRow($importData)
    {
        $category = $this->getCategoryModel();
        $category->setId(null);

        if (!Mage::getModel('categoryexport/category')->isUnique($importData['url_path'])) {
            $message = Mage::helper('categoryexport')->__('Skipping import row, category with path "%s" exists.', $importData['url_path']);
            Mage::throwException($message);
        }
        if (isset($importData['website'])) {
            $website = $this->getWebsiteByCode($importData['website']);
            $category->setWebsiteId($website->getId());
        }
        if (empty($importData['created_in']) || !$this->getStoreByCode($importData['created_in'])) {
            $category->setStoreId(0);
        } else {
            $category->setStoreId($this->getStoreByCode($importData['created_in'])->getId());
        }

        if (!isset($importData['path'])) {
            $model = Mage::getModel('categoryexport/category');
            $value = $model->getPathByUrlPath($importData['url_path']);

            $category->setData('path', $value);
        }

        foreach ($importData as $field => $value) {
            if ($field === 'parent_id') {
                $model = Mage::getModel('categoryexport/category');
                $value = $model->getParentIdByPath($importData['url_path']);
                //  continue;
            }

            $attribute = $this->getAttribute($field);
            if ($attribute) {

                // category attributes
                $isArray = false;
                $setValue = $value;

                if ($attribute->getFrontendInput() == 'multiselect') {
                    $value = explode(self::MULTI_DELIMITER, $value);
                    $isArray = true;
                    $setValue = array();
                }

                if ($attribute->usesSource()) {
                    $options = $attribute->getSource()->getAllOptions(false);

                    if ($isArray) {
                        foreach ($options as $item) {
                            if (in_array($item['label'], $value)) {
                                $setValue[] = $item['value'];
                            }
                        }
                    } else {
                        $setValue = null;
                        foreach ($options as $item) {
                            if ($item['label'] == $value) {
                                $setValue = $item['value'];
                            }
                        }
                    }
                }

                $category->setData($field, $setValue);
                Mage::helper('coreexport')->log($field);

            } elseif ($field =='category_products') {
            // Disabled, because does not work with flat data

//                $products = explode(',',$value);
//                $productPositions = array();
//                Mage::helper('coreexport')->log($products);
//                foreach ($products as $productId) {
//                    $productPositions[$productId] = 0;
//                }
//                $category->setPostedProducts($productPositions);

            } else {
                // skip row import
                continue;
            }

        }

        $category->setImportMode(true);
        $category->save();
    }

    public function getCategoryModel()
    {
        if (is_null($this->_categoryModel)) {
            $object = Mage::getModel('catalog/category');
            $this->_categoryModel = Mage::objects()->save($object);
        }
        return Mage::objects()->load($this->_categoryModel);
    }

    public function getWebsiteByCode($websiteCode)
    {
        if (is_null($this->_websites)) {
            $this->_websites = Mage::app()->getWebsites(true, true);
        }
        if (isset($this->_websites[$websiteCode])) {
            return $this->_websites[$websiteCode];
        }
        return false;
    }

    public function getStoreByCode($store)
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores(true, true);
        }
        if (isset($this->_stores[$store])) {
            return $this->_stores[$store];
        }
        return false;
    }

    public function getAttribute($code)
    {
        if (!isset($this->_attributes[$code])) {
            $this->_attributes[$code] = $this->getCategoryModel()->getResource()->getAttribute($code);
        }
        return $this->_attributes[$code];
    }

}

?>
