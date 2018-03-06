<?php
/**
 *
 * Handle the orders view
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author Oscar van Eijk, Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: view.html.php 9656 2017-10-25 11:20:38Z Milbo $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Load the view framework
if(!class_exists('VmView'))require(VMPATH_SITE.DS.'helpers'.DS.'vmview.php');


/**
 * Handle the orders view
 */
class VirtuemartViewOrders extends VmView {

	public function display($tpl = null)
	{

		$mainframe = JFactory::getApplication();
		$pathway = $mainframe->getPathway();
		$task = vRequest::getCmd('task', 'list');

		$layoutName = vRequest::getCmd('layout', 'list');

		$this->setLayout($layoutName);

		$_currentUser = JFactory::getUser();
		$document = JFactory::getDocument();

		if(!empty($tpl)){
			$format = $tpl;
		} else {
			$format = vRequest::getCmd('format', 'html');
		}
		$this->assignRef('format', $format);

		if($format=='pdf'){
			$document->setTitle( vmText::_('COM_VIRTUEMART_INVOICE') );

			//PDF needs more RAM than usual
			VmConfig::ensureMemoryLimit(96);

		} else {
		    if ($layoutName == 'details') {
			$document->setTitle( vmText::_('COM_VIRTUEMART_ACC_ORDER_INFO') );
			$pathway->additem(vmText::_('COM_VIRTUEMART_ACC_ORDER_INFO'));
		    } else {
			$document->setTitle( vmText::_('COM_VIRTUEMART_ORDERS_VIEW_DEFAULT_TITLE') );
			$pathway->additem(vmText::_('COM_VIRTUEMART_ORDERS_VIEW_DEFAULT_TITLE'));
		    }
		}

		$orderModel = VmModel::getModel('orders');

		$this->order_list_link = JRoute::_('index.php?option=com_virtuemart&view=orders&layout=list', FALSE);

		$this->trackingByOrderPass = -1;
		if ($layoutName == 'details') {
			$orderDetails = $orderModel ->getMyOrderDetails();
			if(!$orderDetails or empty($orderDetails['details'])){
				$layoutName = 'list';
				$this->setLayout($layoutName);
				if($orderDetails) {
					$this->trackingByOrderPass = false;
				} else {
					vmInfo('COM_VIRTUEMART_ORDER_NOTFOUND');
					//return;
				}
			}
		}

		if ($layoutName == 'details') {

			$userFieldsModel = VmModel::getModel('userfields');
			$_userFields = $userFieldsModel->getUserFields(
				 'account'
			, array('captcha' => true, 'delimiters' => true) // Ignore these types
			, array('delimiter_userinfo','user_is_vendor' ,'username','password', 'password2', 'agreed', 'address_type') // Skips
			);
			$orderbt = $orderDetails['details']['BT'];
			$orderst = $orderDetails['details']['ST'];//(array_key_exists('ST', $orderDetails['details'])) ? $orderDetails['details']['ST'] : $orderbt;
			$this->userfields = $userFieldsModel->getUserFieldsFilled(
			$_userFields
			,$orderbt
			);
			$_userFields = $userFieldsModel->getUserFields(
				 'shipment'
			, array() // Default switches
			, array('delimiter_userinfo', 'username', 'email', 'password', 'password2', 'agreed', 'address_type') // Skips
			);

			$this->shipmentfields = $userFieldsModel->getUserFieldsFilled(
			$_userFields
			,$orderst
			);

			$this->shipment_name='';
			if (!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
			JPluginHelper::importPlugin('vmshipment');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEShipment',array(  $orderDetails['details']['BT']->virtuemart_order_id, $orderDetails['details']['BT']->virtuemart_shipmentmethod_id, &$this->shipment_name));

			$this->payment_name='';
			if(!class_exists('vmPSPlugin')) require(VMPATH_PLUGINLIBS.DS.'vmpsplugin.php');
			JPluginHelper::importPlugin('vmpayment');
			$dispatcher = JDispatcher::getInstance();
			$returnValues = $dispatcher->trigger('plgVmOnShowOrderFEPayment',array( $orderDetails['details']['BT']->virtuemart_order_id, $orderDetails['details']['BT']->virtuemart_paymentmethod_id,  &$this->payment_name));

			if($format=='pdf'){
				$invoiceNumberDate = array();
				$return = $orderModel->createInvoiceNumber($orderDetails['details']['BT'], $invoiceNumberDate );
				if(empty($invoiceNumberDate)){
					$invoiceNumberDate[0] = 'no invoice number accessible';
					$invoiceNumberDate[1] = 'no invoice date accessible';
				}
				$this->assignRef('invoiceNumber', $invoiceNumberDate[0]);
				$this->assignRef('invoiceDate', $invoiceNumberDate[1]);
			}

			$this->assignRef('orderdetails', $orderDetails);

			if($_currentUser->guest){
				$details_url = juri::root().'index.php?option=com_virtuemart&view=orders&layout=details&tmpl=component&order_pass=' . vRequest::getString('order_pass',false) .'&order_number='.vRequest::getString('order_number',false);
			} else {
				$details_url = juri::root().'index.php?option=com_virtuemart&view=orders&layout=details&tmpl=component&virtuemart_order_id=' . $this->orderdetails['details']['BT']->virtuemart_order_id;
			}
			$this->assignRef('details_url', $details_url);

			$tmpl = vRequest::getCmd('tmpl');
			$dyn = vRequest::getCmd('dynamic');
			$this->print = false;
			if($tmpl and !$dyn){
				$this->print = true;
			}
			$this->prepareVendor();


			$emailCurrencyId = $orderDetails['details']['BT']->user_currency_id;
			$exchangeRate = FALSE;
			if (!class_exists ('vmPSPlugin')) {
				require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
			}

			if (!class_exists ('CurrencyDisplay')) {
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
			}
			/*
			 * Deprecated trigger will be renamed or removed
			 */
			JPluginHelper::importPlugin ('vmpayment');
			$dispatcher = JDispatcher::getInstance ();
			$dispatcher->trigger ('plgVmgetEmailCurrency', array($orderDetails['details']['BT']->virtuemart_paymentmethod_id, $orderDetails['details']['BT']->virtuemart_order_id, &$emailCurrencyId));

			$currency = CurrencyDisplay::getInstance ($emailCurrencyId, $orderDetails['details']['BT']->virtuemart_vendor_id);
			if ($emailCurrencyId) {
				$currency->exchangeRateShopper = $orderDetails['details']['BT']->user_currency_rate;
			}
			$this->assignRef ('currency', $currency);
			$this->user_currency_id = $emailCurrencyId;

			if($l = VmConfig::get('layout_order_detail',false)){
				$this->setLayout( strtolower( $l ) );
			}
		} else { // 'list' -. default

			$this->useSSL = vmURI::useSSL();
			$this->useXHTML = false;
			if ($_currentUser->get('id') == 0) {
				// getOrdersList() returns all orders when no userID is set (admin function),
				// so explicetly define an empty array when not logged in.
				$this->orderlist = array();
			} else {
				$this->orderlist = $orderModel->getOrdersList($_currentUser->get('id'), TRUE);
				if (!class_exists ('CurrencyDisplay')) {
						require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
				}
				foreach ($this->orderlist as $k =>$order) {
					$vendorId = 1;
					$emailCurrencyId = $order->user_currency_id;
					$exchangeRate = FALSE;
					if (!class_exists ('vmPSPlugin')) {
						require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
					}
					JPluginHelper::importPlugin ('vmpayment');
					$dispatcher = JDispatcher::getInstance ();
					$dispatcher->trigger ('plgVmgetEmailCurrency', array($order->virtuemart_paymentmethod_id, $order->virtuemart_order_id, &$emailCurrencyId));
					
					$currency = CurrencyDisplay::getInstance ($emailCurrencyId, $vendorId);
					$this->assignRef ('currency', $currency);
					if ($emailCurrencyId) {
						$currency->exchangeRateShopper = $order->user_currency_rate;
					}
					$order->currency = $currency;
					$order->invoiceNumber = $orderModel->getInvoiceNumber($order->virtuemart_order_id);
					$this->orderlist[$k] = $order;
				}
			}
			if($l = VmConfig::get('layout_order_list',false)){
				vmdebug('Set new order list layout',$l);
				$this->setLayout( strtolower( $l ) );
			}

			if($this->trackingByOrderPass == -1){
				$ordertracking = VmConfig::get('ordertracking','guests');
				$this->trackingByOrderPass = false; //(VmConfig::get( 'orderGuestLink', 0 ) or !VmConfig::get('oncheckout_only_registered',0)) ;
				if($ordertracking == 'guests' or $ordertracking == 'guestlink'){
					$this->trackingByOrderPass = true;
				}
			}
		}

		$orderStatusModel = VmModel::getModel('orderstatus');

		$_orderstatuses = $orderStatusModel->getOrderStatusList(true);
		$this->orderstatuses = array();
		foreach ($_orderstatuses as $_ordstat) {
			$this->orderstatuses[$_ordstat->order_status_code] = vmText::_($_ordstat->order_status_name);
		}

		$document = JFactory::getDocument();
		$document->setMetaData('robots','NOINDEX, NOFOLLOW, NOARCHIVE, NOSNIPPET');

		// this is no setting in BE to change the layout !
		//shopFunctionsF::setVmTemplate($this,0,0,$layoutName);

		parent::display($tpl);
	}

	// add vendor for cart
	function prepareVendor(){

		$vendorModel = VmModel::getModel('vendor');
		$vendor =  $vendorModel->getVendor();
		$this->assignRef('vendor', $vendor);
		$vendorModel->addImages($this->vendor,1);

	}

}
