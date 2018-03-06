<?php
/**
 *
 * List/add/edit/remove Order Status Types
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 * @author Oscar van Eijk
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9413 2017-01-04 17:20:58Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

/**
 * HTML View class for maintaining the list of order types
 *
 * @package	VirtueMart
 * @subpackage OrderStatus
 */
class VirtuemartViewOrderstatus extends VmViewAdmin {

	function display($tpl = null) {

		// Load the helper(s)
		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		$model = VmModel::getModel();

		$layoutName = vRequest::getCmd('layout', 'default');

		// 'A' : sotck Available
		// 'O' : stock Out
		// 'R' : stock reserved
		$this->stockHandelList = array(
				'A' => 'COM_VIRTUEMART_ORDER_STATUS_STOCK_AVAILABLE',
				'R' => 'COM_VIRTUEMART_ORDER_STATUS_STOCK_RESERVED',
				'O' => 'COM_VIRTUEMART_ORDER_STATUS_STOCK_OUT'
			);

		$this->lists = array();
		$this->lists['vmCoreStatusCode'] = $model->getVMCoreStatusCode();

		if ($layoutName == 'edit') {
			$this->orderStatus = $model->getData();
			$this->SetViewTitle('',vmText::_($this->orderStatus->order_status_name) );
			if ($this->orderStatus->virtuemart_orderstate_id < 1) {
				$this->ordering = vmText::_('COM_VIRTUEMART_NEW_ITEMS_PLACE');
			} else {

				if (!class_exists('ShopFunctions'))
					require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
				$this->ordering = ShopFunctions::renderOrderingList('orderstates','order_status_name',$this->orderStatus->ordering);
			}

			// Vendor selection
			$vendor_model = VmModel::getModel('vendor');
			$vendor_list = $vendor_model->getVendors();
			$this->lists['vendors'] = JHtml::_('select.genericlist', $vendor_list, 'virtuemart_vendor_id', '', 'virtuemart_vendor_id', 'vendor_name', $this->orderStatus->virtuemart_vendor_id);

			$this->addStandardEditViewCommands();
		} else {
			$this->SetViewTitle('');
			$this->addStandardDefaultViewCommands();
			$this->addStandardDefaultViewLists($model);

			$this->orderStatusList = $model->getOrderStatusList(false);
			$this->pagination = $model->getPagination();
		}

		parent::display($tpl);
	}
}

//No Closing Tag
