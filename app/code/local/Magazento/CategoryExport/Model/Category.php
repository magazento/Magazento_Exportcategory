<?php

/*
* @category   Magazento
* @package    Magazento_CategoryExport
* @author     Kate Mironova
* @author     Ivan Proskuryakov
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

class Magazento_CategoryExport_Model_Category extends Mage_Catalog_Model_Category
{

    public function getParentIdByPath($url_path)
    {
        if (strpos($url_path, '/') === 0) {
            $res = Mage_Catalog_Model_Category::TREE_ROOT_ID;
        } else {
            $arrayPath = array();
            $parent = pathinfo($url_path);
            $parentname = $parent['dirname'];

            $store = Mage::app()->getStore();
            $collection = Mage::getModel('catalog/category')->getCollection()
                ->setStore($store)
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('id');

            $collection->addAttributeToFilter('url_path', array('like' => $parentname . '.html'));
            foreach ($collection as $cat) {
                $arrayPath[] = $cat->getId();
            }
            if (!empty($arrayPath)) $res = implode(',', $arrayPath);
            else $res = Mage::app()->getWebsite(true)->getDefaultStore()->getRootCategoryId();

        }

        // var_dump(Mage::app()->getWebsite(true)->getStore()->getId());
        return $res;
    }

    public function getPathByUrlPath($elem)
    {
        $parentId = $this->getParentIdByPath($elem);

        $result = Mage::getModel('catalog/category')->load($parentId)->getPath();
        if (!$result) {
            $store = Mage::app()->getStore();
            $result = Mage::getModel('catalog/category')->load($store->getRootCategoryId())->getPath();
        }

        return $result;
    }

    public function isUnique($url_path)
    {
        $collection = Mage::getModel('catalog/category')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('id')
            ->addAttributeToFilter('url_path', $url_path);
        if ($collection->count() > 0) return false;
        else return true;
    }

}

?>
