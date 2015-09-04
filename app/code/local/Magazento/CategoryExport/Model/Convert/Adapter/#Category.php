<?php

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
        Mage::helper('coreexport')->log('@load');
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
        Mage::helper('coreexport')->log('@parse');

        parent::parse();
    }

    public function saveRow($importData)
    {
//        Mage::helper('coreexport')->log($importData);
        $category = $this->getCategoryModel();
        $category->setId(null);

        if (!Mage::getModel('categoryexport/category')->isUnique($importData['url_path'])) {
            $message = Mage::helper('categoryexport')->__('Skipping import row, category "%s" exists.', $importData['name']);
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

//        if (isset($importData['products_position'])) {
//            $values = unserialize($importData['products_position']);
////                      $collection = Mage::getResourceModel('catalog/product_collection')
////                    ->addAttributeToSelect('entity_id')
////                    ->addAttributeToFilter('entity_id', array('in' => array_keys($values)));
////
//            $res = array();
//            foreach ($values as $sku => $position) {
//                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
//                $res[$product->getId()] = $position;
//            }
//            $category->setPostedProducts($res);
//        }

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

//            if ($field == 'category_products') {
//
//                $values = explode(',',$value);
//                $collection = Mage::getResourceModel('catalog/product_collection')
//                    ->addAttributeToSelect('product_id')
//                    ->addAttributeToFilter('product_is', array('in' => $values));
//                $setValue = $collection->getAllIds();
//            }
                $category->setData($field, $setValue);
                Mage::helper('coreexport')->log($field);

            } elseif ($field =='category_products') {
                // category products

                $products = explode(',',$value);
                $productPositions = array();
                Mage::helper('coreexport')->log($products);
                foreach ($products as $productId) {
                    $productPositions[$productId] = 0;
                }
                $category->setPostedProducts($productPositions);

            } else {

                // skip row import
                continue;
            }

        }

//        Mage::helper('coreexport')->log($importData);

        $category->setImportMode(true);
        //  var_dump($category->getData('parent_id'));
        // die();
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
