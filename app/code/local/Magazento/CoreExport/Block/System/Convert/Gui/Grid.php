<?php

class Magazento_CoreExport_Block_System_Convert_Gui_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {
        Mage::helper('coreexport')->log('Magazento_CoreExport_Block_System_Convert_Gui_Grid');
        parent::__construct();
        $this->setId('convertProfilemGrid');
        $this->setDefaultSort('profile_id');
    }

    protected function _prepareCollection() {
        $collection = Mage::getResourceModel('dataflow/profile_collection')
                ->addFieldToFilter('entity_type', array('notnull' => ''));

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn('profile_id', array(
            'header' => Mage::helper('adminhtml')->__('ID'),
            'width' => '50px',
            'index' => 'profile_id',
        ));
        $this->addColumn('name', array(
            'header' => Mage::helper('adminhtml')->__('Profile Name'),
            'index' => 'name',
        ));
        $this->addColumn('direction', array(
            'header' => Mage::helper('adminhtml')->__('Profile Direction'),
            'index' => 'direction',
            'type' => 'options',
            'options' => array('import' => 'Import', 'export' => 'Export'),
            'width' => '120px',
        ));
        $optionsArray= array('product' => 'Products', 'customer' => 'Customers');
        if (Mage::getStoreConfig('categoryexport/options/enable')){
            $optionsArray['category']='Categories';
        }
        if (Mage::getStoreConfig('orderexport/options/enable')) {
            $optionsArray['order'] = 'Orders';
        }
        if (Mage::getStoreConfig('attributeexport/options/enable')) {
            $optionsArray['attribute'] = 'Attributes';
        }
        if (Mage::getStoreConfig('reviewexport/options/enable')) {
            $optionsArray['review'] = 'Reviews';
        }
        $this->addColumn('entity_type', array(
            'header' => Mage::helper('adminhtml')->__('Entity Type'),
            'index' => 'entity_type',
            'type' => 'options',
            'options' =>$optionsArray,
            'width' => '120px',
        ));

        $this->addColumn('store_id', array(
            'header' => Mage::helper('adminhtml')->__('Store'),
            'type' => 'options',
            'align' => 'center',
            'index' => 'store_id',
            'type' => 'store',
            'width' => '200px',
        ));

        $this->addColumn('created_at', array(
            'header' => Mage::helper('adminhtml')->__('Created At'),
            'type' => 'datetime',
            'align' => 'center',
            'index' => 'created_at',
        ));
        $this->addColumn('updated_at', array(
            'header' => Mage::helper('adminhtml')->__('Updated At'),
            'type' => 'datetime',
            'align' => 'center',
            'index' => 'updated_at',
        ));

        $this->addColumn('action', array(
            'header' => Mage::helper('adminhtml')->__('Action'),
            'width' => '60px',
            'align' => 'center',
            'sortable' => false,
            'filter' => false,
            'type' => 'action',
            'actions' => array(
                array(
                    'url' => $this->getUrl('*/*/edit') . 'id/$profile_id',
                    'caption' => Mage::helper('adminhtml')->__('Edit')
                )
            )
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row) {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

}

