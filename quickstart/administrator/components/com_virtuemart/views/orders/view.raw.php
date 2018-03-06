<?php
/**
 * Generate orderdetails in Raw format for printing
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author Oscar van Eijk
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.raw.php 9413 2017-01-04 17:20:58Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
jimport( 'joomla.application.component.view');

/**
 * HTML View class for the VirtueMart Component
 *
 * @package		VirtueMart
 * @author
 */
if(!class_exists('VmViewAdmin'))require(VMPATH_ADMIN.DS.'helpers'.DS.'vmviewadmin.php');

class VirtuemartViewOrders extends VmViewAdmin {

	function display($tpl = null) {

		//Load helpers

		if (!class_exists('CurrencyDisplay'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');

		if (!class_exists('VmHTML'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');

		if(!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS.DS.'vmpsplugin.php');

		// Load addl models
		$orderModel = VmModel::getModel();
		$userFieldsModel = VmModel::getModel('userfields');
		$productModel = VmModel::getModel('product');

		/* Get the data */

		$virtuemart_order_id = vRequest::getvar('virtuemart_order_id');
		$order = $orderModel->getOrder($virtuemart_order_id);
		//$order = $this->get('Order');
		$orderNumber = $order['details']['BT']->virtuemart_order_number;
		$orderbt = $order['details']['BT'];
		$orderst = (array_key_exists('ST', $order['details'])) ? $order['details']['ST'] : $orderbt;

		$currency = CurrencyDisplay::getInstance('',$order['details']['BT']->virtuemart_vendor_id);
		$this->assignRef('currency', $currency);


		$_userFields = $userFieldsModel->getUserFields(
				 'account'
				, array('captcha' => true, 'delimiters' => true) // Ignore these types
				, array('delimiter_userinfo','user_is_vendor' ,'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
		);
		$userfields = $userFieldsModel->getUserFieldsFilled(
				 $_userFields
				,$orderbt
		);
		$_userFields = $userFieldsModel->getUserFields(
				 'shipment'
				, array() // Default switches
				, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
		);
		$shipmentfields = $userFieldsModel->getUserFieldsFilled(
				 $_userFields
				,$orderst
		);

		// Create an array to allow orderlinestatuses to be translated
		// We'll probably want to put this somewhere in ShopFunctions...
		$_orderStats = $this->get('OrderStatusList');
		$_orderStatusList = array();
		foreach ($_orderStats as $orderState) {
				$_orderStatusList[$orderState->order_status_code] = vmText::_($orderState->order_status_name);
		}

		/*foreach($order['items'] as $_item) {
			if (!empty($_item->product_attribute)) {
				$_attribs = preg_split('/\s?<br\s*\/?>\s?/i', $_item->product_attribute);

				$product = $productModel->getProduct($_item->virtuemart_product_id);
				$_productAttributes = array();
				$_prodAttribs = explode(';', $product->attribute);
				foreach ($_prodAttribs as $_pAttr) {
					$_list = explode(',', $_pAttr);
					$_name = array_shift($_list);
					$_productAttributes[$_item->virtuemart_order_item_id][$_name] = array();
					foreach ($_list as $_opt) {
						$_optObj = new stdClass();
						$_optObj->option = $_opt;
						$_productAttributes[$_item->virtuemart_order_item_id][$_name][] = $_optObj;
					}
				}
			}
		}*/
		//$_shipmentInfo = ShopFunctions::getShipmentRateDetails($orderbt->virtuemart_shipmentmethod_id);

		/* Assign the data */
		$this->assignRef('orderdetails', $order);
		$this->assignRef('orderNumber', $orderNumber);
		$this->assignRef('userfields', $userfields);
		$this->assignRef('shipmentfields', $shipmentfields);
		$this->assignRef('orderstatuslist', $_orderStatusList);
		$this->assignRef('orderbt', $orderbt);
		$this->assignRef('orderst', $orderst);
		$this->assignRef('virtuemart_shipmentmethod_id', $orderbt->virtuemart_shipmentmethod_id);

		error_reporting(0);
		parent::display($tpl);
	}

}

