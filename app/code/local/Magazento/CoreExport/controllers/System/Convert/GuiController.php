<?php

/*
* @category   Magazento
* @package    Magazento_CoreExport
* @author     Kate Mironova
* @author     Ivan Proskuryakov
* @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*/

require_once('Mage/Adminhtml/controllers/System/Convert/GuiController.php');

class Magazento_CoreExport_System_Convert_GuiController extends Mage_Adminhtml_System_Convert_GuiController
//Mage_Adminhtml_Controller_action
{

    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('coreexport/items')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    protected function _initProfile($idFieldName = 'id')
    {
        $this->_title($this->__('System'))
            ->_title($this->__('Import and Export'))
            ->_title($this->__('Profiles'));

        $profileId = (int)$this->getRequest()->getParam($idFieldName);
        $profile = Mage::getModel('coreexport/profile');

        if ($profileId) {
            $profile->load($profileId);
            if (!$profile->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(
                    $this->__('The profile you are trying to save no longer exists'));
                $this->_redirect('*/*');
                return false;
            }
        }

        Mage::register('current_convert_profile', $profile);

        return $this;
    }

    /**
     * Profile edit action
     */
    public function editAction()
    {
        $this->_initProfile();
        $this->loadLayout();

        $profile = Mage::registry('current_convert_profile');

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getConvertProfileData(true);

        if (!empty($data)) {
            $profile->addData($data);
        }

        $this->_title($profile->getId() ? $profile->getName() : $this->__('New Profile'));

        $this->_setActiveMenu('system/convert');


        $this->_addContent(
            $this->getLayout()->createBlock('coreexport/system_convert_gui_edit')
        //$this->getLayout()->createBlock('categoryexport/adminhtml_export_edit')
        );

        /**
         * Append edit tabs to left block
         */
        $this->_addLeft($this->getLayout()->createBlock('coreexport/system_convert_gui_edit_tabs'));
        //          $this->_addLeft($this->getLayout()->createBlock('categoryexport/adminhtml_export_edit_tabs'));

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('coreexport/system_convert_gui_grid')->toHtml());
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))
            ->_title($this->__('Import and Export'))
            ->_title($this->__('Profiles'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }
        $this->loadLayout();

        /**
         * Set active menu item
         */
        $this->_setActiveMenu('system/convert');

        /**
         * Append profiles block to content
         */
        $this->_addContent(
            $this->getLayout()->createBlock('coreexport/system_convert_gui', 'convert_profile')
        );

        /**
         * Add breadcrumb item
         */
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Import/Export'), Mage::helper('adminhtml')->__('Import/Export'));
        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('Profiles'), Mage::helper('adminhtml')->__('Profiles'));

        $this->renderLayout();
    }

}