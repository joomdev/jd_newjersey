<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: view.html.php 9661 2017-10-27 15:29:47Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
class VirtuemartViewMedia extends VmViewAdmin {

	function display($tpl = null) {

		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		$this->vendorId=vmAccess::isSuperVendor();

		// TODO add icon for media view
		$this->SetViewTitle();

		$model = VmModel::getModel('media');

		$layoutName = vRequest::getCmd('layout', 'default');
		if ($layoutName == 'edit') {
			$this->media = $model->getFile();
			$this->addStandardEditViewCommands();
        }
        else {
			$virtuemart_product_id = vRequest::getInt('virtuemart_product_id');
			if(is_array($virtuemart_product_id) && count($virtuemart_product_id) > 0){
				$virtuemart_product_id = (int)$virtuemart_product_id[0];
			} else {
				$virtuemart_product_id = (int)$virtuemart_product_id;
			}
        	$cat_id = vRequest::getInt('virtuemart_category_id',0);

			if(vmAccess::manager('media.new')){
				JToolBarHelper::custom('synchronizeMedia', 'new', 'new', vmText::_('COM_VIRTUEMART_TOOLS_SYNC_MEDIA_FILES'),false);
			}

			$this->addStandardDefaultViewCommands(true, false);

			if(vmAccess::manager('media.delete')){
				//JToolBarHelper::custom('deleteMedia', 'delete', 'deleteFile', vmText::_('COM_VM_MEDIA_DELETE_FILES'),false);
				//JToolBarHelper::custom('deleteEntry', 'delete', 'deleteEntry', vmText::_('COM_VM_MEDIA_DELETE_ENTRY'),false);

				$bar = JToolbar::getInstance('toolbar');
				$bar->appendButton('Confirm', 'COM_VM_MEDIA_DELETE_CONFIRM', 'delete', 'COM_VM_MEDIA_FILES_DELETE', 'deleteFiles', true);
				$bar->appendButton('Standard', 'delete', 'JTOOLBAR_DELETE', 'remove', true);
				//$bar->appendButton('Confirm', 'COM_VM_MEDIA_DELETE_CONFIRM', 'delete', $alt, $task, true);
				//JToolBarHelper::deleteList('COM_VM_MEDIA_DELETE_CONFIRM');
				JToolBarHelper::spacer('10');
			}

			$this->addStandardDefaultViewLists($model,null,null,'searchMedia');
			$options = array( '' => vmText::_('COM_VIRTUEMART_LIST_ALL_TYPES'),
				'product' => vmText::_('COM_VIRTUEMART_PRODUCT'),
				'category' => vmText::_('COM_VIRTUEMART_CATEGORY'),
				'manufacturer' => vmText::_('COM_VIRTUEMART_MANUFACTURER'),
				'vendor' => vmText::_('COM_VIRTUEMART_VENDOR')
				);
			$this->lists['search_type'] = VmHTML::selectList('search_type', vRequest::getVar('search_type'),$options,1,'','onchange="this.form.submit();" style="width:180px;"');

			$this->lists['vendors'] = Shopfunctions::renderVendorList();
			$options = array( '' => vmText::_('COM_VIRTUEMART_LIST_ALL_ROLES'),
				'file_is_displayable' => vmText::_('COM_VIRTUEMART_FORM_MEDIA_DISPLAYABLE'),
				'file_is_downloadable' => vmText::_('COM_VIRTUEMART_FORM_MEDIA_DOWNLOADABLE'),
				'file_is_forSale' => vmText::_('COM_VIRTUEMART_FORM_MEDIA_SET_FORSALE'),
				);
			$this->lists['search_role'] = VmHTML::selectList('search_role', vRequest::getVar('search_role'),$options,1,'','onchange="this.form.submit();" style="width:180px"');

			$this->files = $model->getFiles(false,false,$virtuemart_product_id,$cat_id);

			$this->pagination = $model->getPagination();

		}

		parent::display($tpl);
	}

}
// pure php no closing tag