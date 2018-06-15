<?php

defined ('_JEXEC') or die('Restricted access');
/**
 * abstract class for payment/shipment plugins
 *
 * @package    VirtueMart
 * @subpackage Plugins
 * @author Max Milbers
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (C) 2004-2014 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 */
if (!class_exists ('vmPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmplugin.php');
}

abstract class vmPSPlugin extends vmPlugin {

	protected $_toConvert = false;
	public $methods = null;

	function __construct (& $subject, $config) {

		parent::__construct ($subject, $config);

		$this->_tablepkey = 'id'; //virtuemart_order_id';
		$this->_idName = 'virtuemart_' . $this->_psType . 'method_id';
		$this->_configTable = '#__virtuemart_' . $this->_psType . 'methods';
		$this->_configTableFieldName = $this->_psType . '_params';
		$this->_configTableFileName = $this->_psType . 'methods';
		$this->_configTableClassName = 'Table' . ucfirst ($this->_psType) . 'methods'; //TablePaymentmethods
		// 		$this->_configTableIdName = $this->_psType.'_jplugin_id';
		$this->_loggable = TRUE;

		//$this->_tableChecked = TRUE;
	}

	public function getVarsToPush () {
		return self::getVarsToPushByXML($this->_xmlFile,$this->_name.'Form');
	}

	public function setConvertable($toConvert) {
		$this->_toConvert = $toConvert;
	}

	/**
	 * check if it is the correct type
	 *
	 * @param string $psType either payment or shipment
	 * @return boolean
	 */
	public function selectedThisType ($psType) {

		if ($this->_psType <> $psType) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	public function onStoreInstallPluginTable ($jplugin_id, $name = FALSE) {

		if ($res = $this->selectedThisByJPluginId ($jplugin_id)) {
			vmdebug('onStoreInstallPluginTable, going to execute onStoreInstallPluginTable');
			parent::onStoreInstallPluginTable ($this->_psType);
		}
		return $res;
	}

	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Max Milbers
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function onSelectCheck (VirtueMartCart $cart) {

		$idName = $this->_idName;
		if (!$this->selectedThisByMethodId ($cart->$idName)) {
			return NULL; // Another method was selected, do nothing
		}
		return TRUE; // this method was selected , and the data is valid by default
	}

	/**
	 * displayListFE
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for example
	 *
	 * @param object  $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 */
	public function displayListFE (VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		if ($this->getPluginMethods ($cart->vendorId) === 0) {
			if (empty($this->_name)) {
				vmAdminInfo ('displayListFE cartVendorId=' . $cart->vendorId);
				$app = JFactory::getApplication ();
				$app->enqueueMessage (vmText::_ ('COM_VIRTUEMART_CART_NO_' . strtoupper ($this->_psType)));
				return FALSE;
			} else {
				return FALSE;
			}
		}

		$mname = $this->_psType . '_name';
		$idN = 'virtuemart_'.$this->_psType.'method_id';

		$ret = FALSE;
		foreach ($this->methods as $method) {
			if(!isset($htmlIn[$this->_psType][$method->$idN])) {
				if ($this->checkConditions ($cart, $method, $cart->cartPrices)) {

					// the price must not be overwritten directly in the cart
					$prices = $cart->cartPrices;
					$methodSalesPrice = $this->setCartPrices ($cart, $prices ,$method);

					//This makes trouble, because $method->$mname is used in  renderPluginName to render the Name, so it must not be called twice!
					$method->$mname = $this->renderPluginName ($method);

					$htmlIn[$this->_psType][$method->$idN] = $this->getPluginHtml ($method, $selected, $methodSalesPrice);

					$ret = TRUE;
				}
			} else {
				$ret = TRUE;
			}
		}

		return $ret;
	}

	/*
	 * onSelectedCalculatePrice
	* Calculate the price (value, tax_id) of the selected method
	* It is called by the calculator
	* This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
	* @author Valerie Isaksen
	* @cart: VirtueMartCart the current cart
	* @cart_prices: array the new cart prices
	* @return null if the method was not selected, false if the shipping rate is not valid any more, true otherwise
	*
	*/

	public function onSelectedCalculatePrice (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		$idName = $this->_idName;

		if (!($method = $this->selectedThisByMethodId ($cart->$idName))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$method = $this->getVmPluginMethod ($cart->$idName) or empty($method->$idName)) {
			return NULL;
		}

		$cart_prices_name = '';
		$cart_prices['cost'] = 0;

		if (!$this->checkConditions ($cart, $method, $cart_prices)) {
			return FALSE;
		}

		$cart_prices_name = $this->renderPluginName ($method);

		$this->setCartPrices ($cart, $cart_prices, $method);

		return TRUE;
	}


	/**
	 * onCheckAutomaticSelected
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function onCheckAutomaticSelected (VirtueMartCart $cart, array $cart_prices = array(), &$methodCounter = 0) {

		$virtuemart_pluginmethod_id = 0;

		$nbMethod = $this->getSelectable ($cart, $virtuemart_pluginmethod_id, $cart_prices);
		$methodCounter += $nbMethod;

		if ($nbMethod == NULL) {
			return NULL;
		} else {
			if ($nbMethod == 1) {
				return $virtuemart_pluginmethod_id;
			} else {
				return 0;
			}
		}
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 */
	protected function onShowOrderFE ($virtuemart_order_id, $virtuemart_method_id, &$method_info) {

		if (!($this->selectedThisByMethodId ($virtuemart_method_id))) {
			return NULL;
		}
		$method_info = $this->getOrderMethodNamebyOrderId ($virtuemart_order_id);
	}

	/**
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 * @param int $virtuemart_order_id
	 * @return string pluginName from the plugin table
	 */
	private function getOrderMethodNamebyOrderId ($virtuemart_order_id) {

		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . (int)$virtuemart_order_id;
		$db->setQuery ($q);
		if (!($pluginInfo = $db->loadObject ())) {
			vmdebug ('Attention, ' . $this->_tablename . ' has not any entry for order_id = '.$virtuemart_order_id);
			//if(!empty($err)){
			//	vmWarn ('Attention, ' . $this->_tablename . ' has not any entry for order_id = '.$virtuemart_order_id. ' err = '.$err);
			//}

			return NULL;
		}
		$idName = $this->_psType . '_name';

		return $pluginInfo->$idName;
	}


	/**
	 * check if it is the correct element
	 *
	 * @param string $element either standard or paypal
	 * @return boolean
	 */
	public function selectedThisElement ($element) {

		if ($this->_name <> $element) {
			return FALSE;
		} else {
			return TRUE;
		}
	}

	/**
	 * This method is fired when showing the order details in the backend.
	 * It displays the the payment method-specific data.
	 * All plugins *must* reimplement this method.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $_paymethod_id Payment method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Max Milbers
	 * @author Valerie Isaksen
	 */
	function onShowOrderBE ($_virtuemart_order_id, $_method_id) {
		return NULL;
	}

	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function onShowOrderPrint ($order_number, $method_id) {

		if (!$this->selectedThisByMethodId ($method_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($order_name = $this->getOrderPluginName ($order_number, $method_id))) {
			return NULL;
		}

		vmLanguage::loadJLang('com_virtuemart');

		$html = '<table class="admintable">' . "\n"
			. '	<thead>' . "\n"
			. '		<tr>' . "\n"
			. '			<td class="key" style="text-align: center;" colspan="2">' . vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_' . strtoupper($this->_type) . '_LBL') . '</td>' . "\n"
			. '		</tr>' . "\n"
			. '	</thead>' . "\n"
			. '	<tr>' . "\n"
			. '		<td class="key">' . vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_' . strtoupper($this->_type) . '_LBL') . ': </td>' . "\n"
			. '		<td align="left">' . $order_name . '</td>' . "\n"
			. '	</tr>' . "\n";

		$html .= '</table>' . "\n";
		return $html;
	}

	private function getOrderPluginName ($order_number, $pluginmethod_id) {

		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE `order_number` = "' . $order_number . '"
		AND `' . $this->_idName . '` =' . $pluginmethod_id;
		$db->setQuery ($q);
		if (!($order = $db->loadObject ())) {
			return NULL;
		}

		$plugin_name = $this->_psType . '_name';
		return $order->$plugin_name;
	}

	/**
	 * Save updated order data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.
	 * @author Oscar van Eijk
	 */
	public function onUpdateOrder ($formData) {
		return NULL;
	}

	/**
	 * Save updated orderline data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.
	 * @author Oscar van Eijk
	 */
	public function onUpdateOrderLine ($formData) {
		return NULL;
	}

	/**
	 * OnEditOrderLineBE
	 * This method is fired when editing the order line details in the backend.
	 * It can be used to add line specific package codes
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	public function onEditOrderLineBE ($orderId, $lineId) {
		return NULL;
	}

	/**
	 * This method is fired when showing the order details in the frontend, for every orderline.
	 * It can be used to display line specific package codes, e.g. with a link to external tracking and
	 * tracing systems
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise
	 * @author Oscar van Eijk
	 */
	public function onShowOrderLineFE ($orderId, $lineId) {
		return NULL;
	}

	/**
	 * This event is fired when the  method notifies you when an event occurs that affects the order.
	 * Typically,  the events  represents for payment authorizations, Fraud Management Filter actions and other actions,
	 * such as refunds, disputes, and chargebacks.
	 *
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 *
	 * @param      $return_context: it was given and sent in the payment form. The notification should return it back.
	 * Used to know which cart should be emptied, in case it is still in the session.
	 * @param int  $virtuemart_order_id : payment  order id
	 * @param char $new_status : new_status for this order id.
	 * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 */
	public function onNotification () {
		return NULL;
	}

	/**
	 * OnResponseReceived
	 * This event is fired when the  method returns to the shop after the transaction
	 *
	 *  the method itself should send in the URL the parameters needed
	 * NOTE for Plugin developers:
	 *  If the plugin is NOT actually executed (not the selected payment method), this method must return NULL
	 *
	 * @param int  $virtuemart_order_id : should return the virtuemart_order_id
	 * @param text $html: the html to display
	 * @return mixed Null when this method was not selected, otherwise the true or false
	 *
	 * @author Valerie Isaksen
	 *
	 */
	function onResponseReceived (&$virtuemart_order_id, &$html) {
		return NULL;
	}

	function getDebug () {
		return $this->_debug;
	}

	function setDebug ($params) {
		return $this->_debug = $params->get ('debug', 0);
	}

	/**
	 * Get Plugin Data for a go given plugin ID
	 *
	 * @author Valérie Isaksen
	 * @param int $pluginmethod_id The method ID
	 * @return  method data
	 */
	final protected function getPluginMethod ($method_id) {

		if (!$this->selectedThisByMethodId ($method_id)) {
			return FALSE;
		}

		return $this->getVmPluginMethod ($method_id);

	}

	/**
	 * Fill the array with all plugins found with this plugin for the current vendor
	 * Todo it would be nicer to use here the correct vmtable methods
	 * @return True when plugins(s) was (were) found for this vendor, false otherwise
	 * @author Oscar van Eijk
	 * @author max Milbers
	 * @author valerie Isaksen
	 */
	protected function getPluginMethods ($vendorId) {

		static $mC = array();

		$h = $vendorId.$this->_psType.$this->_name;
		if(isset($mC[$h])) {
			$this->methods = $mC[$h];
			//vmdebug('getPluginMethods return cached '.$h);
			return count($this->methods);
		}

		$usermodel = VmModel::getModel ('user');
		$user = $usermodel->getUser ();
		$user->shopper_groups = (array)$user->shopper_groups;

		$db = JFactory::getDBO ();
		if(empty($vendorId)) $vendorId = 1;
		$select = 'SELECT i.*, ';

		$extPlgTable = '#__extensions';
		$extField1 = 'extension_id';
		$extField2 = 'element';

		$select .= 'j.`' . $extField1 . '`,j.`name`, j.`type`, j.`element`, j.`folder`, j.`client_id`, j.`enabled`, j.`access`, j.`protected`, j.`manifest_cache`,
			j.`params`, j.`custom_data`, j.`system_data`, j.`checked_out`, j.`checked_out_time`, j.`state`,  s.virtuemart_shoppergroup_id ';

		if(!VmConfig::$vmlang){
			vmLanguage::initialise();
		}

		$joins = array();

		$langFields = array($this->_psType.'_name',$this->_psType.'_desc');

		$select .= ', '.implode(', ',VmModel::joinLangSelectFields($langFields));

		$joins = VmModel::joinLangTables('#__virtuemart_' . $this->_psType . 'methods','i','virtuemart_' . $this->_psType . 'method_id');
		array_unshift($joins, ' FROM #__virtuemart_' . $this->_psType . 'methods as i');

		$joins[]= ' LEFT JOIN `' . $extPlgTable . '` as j ON j.`' . $extField1 . '` =  i.`' . $this->_psType . '_jplugin_id` ';
		$joins[]= ' LEFT OUTER JOIN `#__virtuemart_' . $this->_psType . 'method_shoppergroups` AS s ON i.`virtuemart_' . $this->_psType . 'method_id` = s.`virtuemart_' . $this->_psType . 'method_id` ';

		$q = $select.implode(' '."\n",$joins);
		$q .= ' WHERE i.`published` = "1" AND j.`' . $extField2 . '` = "' . $this->_name . '"
	    						AND  (i.`virtuemart_vendor_id` = "' . $vendorId . '" OR i.`virtuemart_vendor_id` = "0" OR i.`shared` = "1")
	    						AND  (';

		foreach ($user->shopper_groups as $groups) {
			$q .= ' s.`virtuemart_shoppergroup_id`= "' . (int)$groups . '" OR';
		}
		$q .= ' (s.`virtuemart_shoppergroup_id`) IS NULL ) GROUP BY i.`virtuemart_' . $this->_psType . 'method_id` ORDER BY i.`ordering`';


		$db->setQuery ($q);
		$this->methods = $db->loadObjectList ();
		if($err = $db->getErrorMsg()){
			vmError('Error in slq vmpsplugin.php function getPluginMethods '.$err);
		}

		if ($this->methods) {
			foreach ($this->methods as $method) {
				VmTable::bindParameterable ($method, $this->_xParams, $this->_varsToPushParam);
				$this->decryptFields($method);
			}
		} else if($this->methods===null or empty($this->methods)){
			$this->methods = array();
			//vmError ('Error reading getPluginMethods ' . $q);
		}

		$mC[$h] = $this->methods;
		//vmdebug('getPluginMethods my query ',str_replace('#__',$db->getPrefix(),$db->getQuery()));
		return count($this->methods);
	}

	function decryptFields($method){
		if($this->_cryptedFields and is_array($this->_cryptedFields)){
			if(!class_exists('vmCrypt')){
				require(VMPATH_ADMIN .'/helpers/vmcrypt.php');
			}
			if(isset($method->modified_on) and $method->modified_on!='0000-00-00 00:00:00'){
				$date = JFactory::getDate($method->modified_on);
				$date = $date->toUnix();
			} else if(isset($method->created_on) and $method->created_on!='0000-00-00 00:00:00'){
				$date = JFactory::getDate($method->created_on);
				$date = $date->toUnix();
			} else {
				$date = 0;
			}

			foreach($this->_cryptedFields as $field){
				if(isset($method->$field)){
					$t = $method->$field;
					$method->$field = vmCrypt::decrypt($method->$field, $date);
					//vmdebug(' Field '.$field.' crypted '.$t.' decrypted = '.$method->$field);
				}
			}
			$this->_encrypted = false;
		}
	}

	/**
	 * Get Method Data for a given Payment ID
	 *
	 * @author Valérie Isaksen
	 * @param int $virtuemart_order_id The order ID
	 * @return  $methodData
	 */
	final protected function getDataByOrderId ($virtuemart_order_id) {

		$t = substr($this->_tablename,3);
		if(!vmTable::checkTableExists($t)) return false;
		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . (int)$virtuemart_order_id;

		$db->setQuery ($q);
		$methodData = $db->loadObject ();

		return $methodData;
	}

	/**
	 * Get Method Datas for a given Payment ID
	 *
	 * @author Valérie Isaksen
	 * @param int $virtuemart_order_id The order ID
	 * @return  $methodData
	 */
	final protected function getDatasByOrderId ($virtuemart_order_id) {

		$t = substr($this->_tablename,3);
		if(!vmTable::checkTableExists($t)) return false;
		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = "' . (int)$virtuemart_order_id. '" '
			. 'ORDER BY `id` ASC';

		$db->setQuery ($q);
		$methodData = $db->loadObjectList ();

		return $methodData;
	}
	/**
	 * Get Method Data for a given Payment ID
	 *
	 * @author Valérie Isaksen
	 * @param int $order_number The order Number
	 * @return  $methodData
	 */
	final protected function getDataByOrderNumber ($order_number) {

		$t = substr($this->_tablename,3);
		if(!vmTable::checkTableExists($t)) return false;
		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `order_number`="'.$db->escape($order_number).'"';

		$db->setQuery ($q);
		$methodData = $db->loadObject ();

		return $methodData;
	}

	/**
	 * Get Method Datas for a given Payment ID
	 *
	 * @author Valérie Isaksen
	 * @param int $order_number The order Number
	 * @return  $methodData
	 */
	final protected function getDatasByOrderNumber ($order_number) {

		$t = substr($this->_tablename,3);
		if(!vmTable::checkTableExists($t)) return false;
		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `order_number`="'.$db->escape($order_number).'" and payment_currency > "0"';

		$db->setQuery ($q);
		$methodData = $db->loadObjectList ();

		return $methodData;
	}

	/**
	 * Get the total weight for the order, based on which the proper shipping rate
	 * can be selected.
	 *
	 * @param object $cart Cart object
	 * @return float Total weight for the order
	 */
	protected function getOrderWeight (VirtueMartCart $cart, $to_weight_unit) {

		static $weight = array();
		if(!isset($weight[$to_weight_unit])) $weight[$to_weight_unit] = 0.0;
		if(count($cart->products)>0 and empty($weight[$to_weight_unit])){

			foreach ($cart->products as $product) {
				$weight[$to_weight_unit] += (ShopFunctions::convertWeightUnit ($product->product_weight, $product->product_weight_uom, $to_weight_unit) * $product->quantity);
			}
		}

		return $weight[$to_weight_unit];
	}

	/**
	 * getThisName
	 * Get the name of the method
	 *
	 * @param int $id The method ID
	 * @author Valérie Isaksen
	 * @return string Shipment name
	 */
	final protected function getThisName ($virtuemart_method_id) {

		$db = JFactory::getDBO ();
		$q = 'SELECT `' . $this->_psType . '_name` '
			. 'FROM #__virtuemart_' . $this->_psType . 'methods '
			. 'WHERE ' . $this->_idName . ' = "' . (int)$virtuemart_method_id . '" ';
		$db->setQuery ($q);
		return $db->loadResult (); // TODO Error check
	}


	/**
	 * Extends the standard function in vmplugin. Extendst the input data by virtuemart_order_id
	 * Calls the parent to execute the write operation
	 *
	 * @author Max Milbers
	 * @param array  $_values
	 * @param string $_table
	 */
	protected function storePSPluginInternalData ($values, $primaryKey = 0, $preload = FALSE) {

		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!isset($values['virtuemart_order_id'])) {
			$values['virtuemart_order_id'] = VirtueMartModelOrders::getOrderIdByOrderNumber ($values['order_number']);
		}
		return $this->storePluginInternalData ($values, $primaryKey, 0, $preload);
	}

	/**
	 * Something went wrong, Send notification to all administrators
	 *
	 * @param string subject of the mail
	 * @param string message
	 */
	protected function sendEmailToVendorAndAdmins ($subject = NULL, $message = NULL) {

		// recipient is vendor and admin
		$vendorId = 1;
		$vendorModel = VmModel::getModel('vendor');
		$vendor = $vendorModel->getVendor($vendorId);
		$vendorEmail = $vendorModel->getVendorEmail($vendorId);
		$vendorName = $vendorModel->getVendorName($vendorId);
		vmLanguage::loadJLang('com_virtuemart');
		if ($subject == NULL) {
			$subject = vmText::sprintf('COM_VIRTUEMART_ERROR_SUBJECT', $this->_name, $vendor->vendor_store_name);
		}
		if ($message == NULL) {
			$message = vmText::sprintf('COM_VIRTUEMART_ERROR_BODY', $subject, $this->getLogFilename().VmConfig::LOGFILEEXT);
		}
		JFactory::getMailer()->sendMail($vendorEmail, $vendorName, $vendorEmail, $subject, $message);

		$query = 'SELECT name, email, sendEmail FROM #__users WHERE sendEmail=1';

		$db = JFactory::getDBO();
		$db->setQuery($query);
		$rows = $db->loadObjectList();

		$subject = html_entity_decode($subject, ENT_QUOTES);

		// get superadministrators id
		foreach ($rows as $row) {
			if ($row->sendEmail) {
				$message = html_entity_decode($message, ENT_QUOTES);
				JFactory::getMailer()->sendMail($vendorEmail, $vendorName, $row->email, $subject, $message);
			}
		}
	}

	/**
	 * displays the logos of a VirtueMart plugin
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 * @param array $logo_list
	 * @return html with logos
	 */
	protected function displayLogos ($logo_list) {

		$img = "";

		if (!(empty($logo_list))) {
			if(!class_exists('JFolder')){
				require(VMPATH_LIBS.DS.'joomla'.DS.'filesystem'.DS.'folder.php');
			}
			$url ='images/virtuemart/' . $this->_psType ;

			if(!JFolder::exists( VMPATH_ROOT .'/'. $url)){
				$url = 'images/stories/virtuemart/' . $this->_psType;
				if(!JFolder::exists(VMPATH_ROOT .'/'. $url)){
					return $img;
				}
			}

			if (!is_array ($logo_list)) {
				$logo_list = (array)$logo_list;
			}
			foreach ($logo_list as $logo) {
				if(!empty($logo)){
					if(JFile::exists(VMPATH_ROOT .DS. $url .DS.$logo)){
						$alt_text = substr ($logo, 0, strpos ($logo, '.'));
						$img .= '<span class="vmCart' . ucfirst($this->_psType) . 'Logo" ><img align="middle" src="' . JUri::root().$url.'/'.$logo . '"  alt="' . $alt_text . '" /></span> ';
					}
				}
			}
		}
		return $img;
	}

	protected function renderPluginName ($plugin) {

		static $c = array();
		$idN = 'virtuemart_'.$this->_psType.'method_id';

		if(isset($c[$this->_psType][$plugin->$idN])){
			return $c[$this->_psType][$plugin->$idN];
		}

		$return = '';
		$plugin_name = $this->_psType . '_name';
		$plugin_desc = $this->_psType . '_desc';
		$description = '';
		$logosFieldName = $this->_psType . '_logos';
		$logos = property_exists($plugin,$logosFieldName)? $plugin->$logosFieldName:array();
		if (!empty($logos)) {
			$return = $this->displayLogos ($logos) . ' ';
		}
		if (!empty($plugin->$plugin_desc)) {
			$description = '<span class="' . $this->_type . '_description">' . $plugin->$plugin_desc . '</span>';
		}
		$c[$this->_psType][$plugin->$idN] = $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . '</span>' . $description;

		return $c[$this->_psType][$plugin->$idN];
	}

	protected function getPluginHtml ($plugin, $selectedPlugin, $pluginSalesPrice) {

		$pluginmethod_id = $this->_idName;
		$pluginName = $this->_psType . '_name';
		if ($selectedPlugin == $plugin->$pluginmethod_id) {
			$checked = 'checked="checked"';
		} else {
			$checked = '';
		}

		if (!class_exists ('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$currency = CurrencyDisplay::getInstance ();
		$costDisplay = "";
		if ($pluginSalesPrice) {
			$costDisplay = $currency->priceDisplay( $pluginSalesPrice );
			$t = vmText::_( 'COM_VIRTUEMART_PLUGIN_COST_DISPLAY' );
			if(strpos($t,'/')!==FALSE){
				list($discount, $fee) = explode( '/', vmText::_( 'COM_VIRTUEMART_PLUGIN_COST_DISPLAY' ) );
				if($pluginSalesPrice>=0) {
					$costDisplay = '<span class="'.$this->_type.'_cost fee"> ('.$fee.' '.$costDisplay.")</span>";
				} else if($pluginSalesPrice<0) {
					$costDisplay = trim(strip_tags($costDisplay),'-');
					$costDisplay = '<span class="'.$this->_type.'_cost discount"> ('.$discount.' '.$costDisplay.")</span>";
				}
			} else {
				$costDisplay = '<span class="'.$this->_type.'_cost fee"> ('.$t.' '.$costDisplay.")</span>";
			}
		}
		$dynUpdate='';
		if( VmConfig::get('oncheckout_ajax',false)) {
			//$url = JRoute::_('index.php?option=com_virtuemart&view=cart&task=updatecart&'. $this->_idName. '='.$plugin->$pluginmethod_id );
			$dynUpdate=' data-dynamic-update="1" ';
		}
		$html = '<input type="radio"'.$dynUpdate.' name="' . $pluginmethod_id . '" id="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '"   value="' . $plugin->$pluginmethod_id . '" ' . $checked . ">\n"
			. '<label for="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '">' . '<span class="' . $this->_type . '">' . $plugin->$pluginName . $costDisplay . "</span></label>\n";

		return $html;
	}

	protected function getHtmlHeaderBE () {

		$class = "class='key'";
		$html = ' 	<thead>' . "\n"
			. '		<tr>' . "\n"
			. '			<th ' . $class . ' style="text-align: center;" colspan="2">' . vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_' . strtoupper($this->_psType) . '_LBL') . '</th>' . "\n"
			. '		</tr>' . "\n"
			. '	</thead>' . "\n";

		return $html;
	}


	protected function getHtmlRow ($key, $value, $class = '') {

		$lang = JFactory::getLanguage ();
		$key_text = '';
		$complete_key = strtoupper ($this->_type . '_' . $key);

		if ($lang->hasKey($complete_key)) {
			$key_text = vmText::_ ($complete_key);
		} else {
			$key_text = vmText::_ ($key);
		}
		$more_key = strtoupper($complete_key . '_' . $value);
		if ($lang->hasKey ($more_key)) {
			$value .= " (" . vmText::_ ($more_key) . ")";
		}
		$html = "<tr>\n<td " . $class . ">" . $key_text . "</td>\n <td align='left'>" . $value . "</td>\n</tr>\n";
		return $html;
	}

	function getHtmlRowBE ($key, $value) {
		return $this->getHtmlRow ($key, $value, "class='key'");
	}

	/**
	 * getSelectable
	 * This method returns the number of valid methods
	 *
	 * @param VirtueMartCart cart: the cart object
	 * @param $method_id eg $virtuemart_shipmentmethod_id
	 *
	 */
	function getSelectable (VirtueMartCart $cart, &$method_id, $cart_prices) {

		$nbMethod = 0;

		if ($this->getPluginMethods ($cart->vendorId) === 0) {
			return FALSE;
		}

		foreach ($this->methods as $method) {
			if ($nb = (int)$this->checkConditions ($cart, $method, $cart_prices)) {

				$nbMethod = $nbMethod + $nb;
				$idName = $this->_idName;
				$method_id = $method->$idName;
			}
		}
		return $nbMethod;
	}

	/**
	 *
	 * Enter description here ...
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 * @param VirtueMartCart $cart
	 * @param int            $method
	 * @param array          $cart_prices
	 */
	protected function checkConditions ($cart, $method, $cart_prices) {

		vmAdminInfo ('vmPsPlugin function checkConditions not overriden, gives always back FALSE');
		return FALSE;
	}

  	/**
	 * @param $method
	 */
	function convert_condition_amount (&$method) {
		$method->min_amount = (float)str_replace(',','.',$method->min_amount);
		$method->max_amount = (float)str_replace(',','.',$method->max_amount);
	}

	/**
	 * @param      $method
	 * @param bool $getCurrency
	 */
	static function getPaymentCurrency (&$method, $getCurrency = FALSE) {

		if (!isset($method->payment_currency) or empty($method->payment_currency) or !$method->payment_currency or $getCurrency) {
			$vendor_model = VmModel::getModel('vendor');
			$vendor = $vendor_model->getVendor($method->virtuemart_vendor_id);
			$method->payment_currency = $vendor->vendor_currency;

		} elseif (isset($method->payment_currency) and $method->payment_currency== -1) {

			$vendor_model = VmModel::getModel('vendor');
			$vendor_currencies = $vendor_model->getVendorAndAcceptedCurrencies($method->virtuemart_vendor_id);

			$mainframe = JFactory::getApplication();
			$method->payment_currency = $mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id',vRequest::getInt('virtuemart_currency_id', $vendor_currencies['vendor_currency']) );
		}

	}

	function getEmailCurrency (&$method) {

		if (!isset($method->email_currency)  or $method->email_currency=='vendor') {
			$vendor_model = VmModel::getModel('vendor');
			$vendor = $vendor_model->getVendor($method->virtuemart_vendor_id);
			return $vendor->vendor_currency;
		} else {
			return $method->payment_currency; // either the vendor currency, either same currency as payment
		}
	}

	/**
	 * displayTaxRule
	 *
	 * @param int $tax_id
	 * @return string $html:
	 */
	function displayTaxRule ($tax_id) {

		$html = '';
		$db = JFactory::getDBO ();
		if (!empty($tax_id)) {
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . (int)$tax_id . '" ';
			$db->setQuery ($q);
			$taxrule = $db->loadObject ();

			$html = $taxrule->calc_name . '(' . $taxrule->calc_kind . ':' . $taxrule->calc_value_mathop . $taxrule->calc_value . ')';
		}
		return $html;
	}

	function getCosts (VirtueMartCart $cart, $method, $cart_prices) {

		if(!isset($method->cost_percent_total)) $method->cost_percent_total = 0.0;
		if (preg_match ('/%$/', $method->cost_percent_total)) {
			$method->cost_percent_total = substr ($method->cost_percent_total, 0, -1);
		} else {
			if(empty($method->cost_percent_total)){
				$method->cost_percent_total = 0;
			}
		}
		$cartPrice = !empty($cart->cartPrices['withTax'])? $cart->cartPrices['withTax']:$cart->cartPrices['salesPrice'];

		if(!isset($method->cost_per_transaction)) $method->cost_per_transaction = 0.0;

		$costs = $method->cost_per_transaction + $cartPrice * $method->cost_percent_total * 0.01;
		if(!empty($method->cost_min_transaction) and $method->cost_min_transaction!='' and $costs < $method->cost_min_transaction){
			return $method->cost_min_transaction;
		} else {
			return $costs;
		}
	}


	/**
	 * Get the cart amount for checking conditions if the payment conditions are fullfilled
	 * @param $cart_prices
	 * @return mixed
	 */
	function getCartAmount($cart_prices){
		if(empty($cart_prices['salesPrice'])) $cart_prices['salesPrice'] = 0.0;
		$cartPrice = !empty($cart_prices['withTax'])? $cart_prices['withTax']:$cart_prices['salesPrice'];
		if(empty($cart_prices['salesPriceShipment'])) $cart_prices['salesPriceShipment'] = 0.0;
		if(empty($cart_prices['salesPriceCoupon'])) $cart_prices['salesPriceCoupon'] = 0.0;
		$amount= $cartPrice + $cart_prices['salesPriceShipment'] + $cart_prices['salesPriceCoupon'] ;
		if ($amount <= 0) $amount=0;
		return $amount;

	}

	function convertToVendorCurrency(&$method){

		if($this->_toConvert){
			$idN = 'virtuemart_'.$this->_psType.'method_id';

			if(!isset($method->converted)){

				$calculator = calculationHelper::getInstance ();
				foreach($this->_toConvert as $c){
					if(!empty($method->$c)){
						$method->$c = $calculator->_currencyDisplay->convertCurrencyTo($method->currency_id,$method->$c,true);
					} else {
						$method->$c = 0.0;
					}
				}
				$method->converted = 1;

			}
		}

	}

	/**
	 * @param VirtueMartCart $cart
	 * @param $cart_prices
	 * @param $method
	 * @param bool $progressive
	 * @return mixed
	 */

	function setCartPrices (VirtueMartCart $cart, &$cart_prices, $method, $progressive = true) {

		if (!class_exists ('calculationHelper')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'calculationh.php');
		}
		$calculator = calculationHelper::getInstance ();

		$_psType = ucfirst ($this->_psType);
		if($this->_toConvert){
			$this->convertToVendorCurrency($method);
		}
		$cart_prices[$this->_psType . 'Value'] = $calculator->roundInternal ($this->getCosts ($cart, $method, $cart_prices), 'salesPrice');
		if(!isset($cart_prices[$this->_psType . 'Value'])) $cart_prices[$this->_psType . 'Value'] = 0.0;
		if(!isset($cart_prices[$this->_psType . 'Tax'])) $cart_prices[$this->_psType . 'Tax'] = 0.0;

		if($this->_psType=='payment'){
			$cartTotalAmountOrig = $this->getCartAmount($cart_prices);

			if(!isset($method->cost_percent_total)) $method->cost_percent_total = 0.0;
			if(!isset($method->cost_per_transaction)) $method->cost_per_transaction = 0.0;

			if(!$progressive){
				//Simple
				$cartTotalAmount=($cartTotalAmountOrig + $method->cost_per_transaction) * (1 +($method->cost_percent_total * 0.01));
				//vmdebug('Simple $cartTotalAmount = ('.$cartTotalAmountOrig.' + '.$method->cost_per_transaction.') * (1 + ('.$method->cost_percent_total.' * 0.01)) = '.$cartTotalAmount );
				//vmdebug('Simple $cartTotalAmount = '.($cartTotalAmountOrig + $method->cost_per_transaction).' * '. (1 + $method->cost_percent_total * 0.01) .' = '.$cartTotalAmount );
			} else {
				//progressive
				$cartTotalAmount = ($cartTotalAmountOrig + $method->cost_per_transaction) / (1 -($method->cost_percent_total * 0.01));
				//vmdebug('Progressive $cartTotalAmount = ('.$cartTotalAmountOrig.' + '.$method->cost_per_transaction.') / (1 - ('.$method->cost_percent_total.' * 0.01)) = '.$cartTotalAmount );
				//vmdebug('Progressive $cartTotalAmount = '.($cartTotalAmountOrig + $method->cost_per_transaction) .' / '. (1 - $method->cost_percent_total * 0.01) .' = '.$cartTotalAmount );
			}

			$cart_prices[$this->_psType . 'Value'] = $cartTotalAmount - $cartTotalAmountOrig;
			if(!empty($method->cost_min_transaction) and $method->cost_min_transaction!='' and $cart_prices[$this->_psType . 'Value'] < $method->cost_min_transaction){
				$cart_prices[$this->_psType . 'Value'] = $method->cost_min_transaction;

			}
		}

		if(!isset($cart_prices['salesPrice' . $_psType])) $cart_prices['salesPrice' . $_psType] = $cart_prices[$this->_psType . 'Value'];

		$taxrules = array();
		if(isset($method->tax_id) and (int)$method->tax_id === -1){

		} else if (!empty($method->tax_id)) {
			$cart_prices[$this->_psType . '_calc_id'] = $method->tax_id;

			$db = JFactory::getDBO ();
			$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $method->tax_id . '" ';
			$db->setQuery ($q);
			$taxrules = $db->loadAssocList ();

			if(!empty($taxrules) ){
				foreach($taxrules as &$rule){
					if(!isset($rule['subTotal'])) $rule['subTotal'] = 0;
					if(!isset($rule['taxAmount'])) $rule['taxAmount'] = 0;
					$rule['subTotalOld'] = $rule['subTotal'];
					$rule['taxAmountOld'] = $rule['taxAmount'];
					$rule['taxAmount'] = 0;
					$rule['subTotal'] = $cart_prices[$this->_psType . 'Value'];
					$rule['psType'] = $this->_psType;
					$cart_prices[$this->_psType . 'TaxPerID'][$rule['virtuemart_calc_id']] = $calculator->roundInternal($calculator->roundInternal($calculator->interpreteMathOp($rule, $rule['subTotal'])) - $rule['subTotal'], 'salesPrice');
					$cart_prices[$this->_psType . 'Tax'] += $cart_prices[$this->_psType . 'TaxPerID'][$rule['virtuemart_calc_id']];
				}
			}
		} else {

			if ( !isset($cart->cartData['taxRulesBill']) ) {
				$cart->cartData['taxRulesBill'] = array();
			}
			if ( !isset($cart->cartData['DBTaxRulesBill']) ) {
				$cart->cartData['DBTaxRulesBill'] = array();
			}
			// end code addition
			$taxrules = array_merge($cart->cartData['VatTax'],$cart->cartData['taxRulesBill']);
			$cartdiscountBeforeTax = $calculator->roundInternal($calculator->cartRuleCalculation($cart->cartData['DBTaxRulesBill'], $cart->cartPrices['salesPrice']));

			if(!empty($taxrules) ){

				foreach($taxrules as &$rule){
					//Quickn dirty
					if(!isset($rule['calc_kind'])) $rule = (array)VmModel::getModel('calc')->getCalc($rule['virtuemart_calc_id']);

					if(!isset($rule['subTotal'])) $rule['subTotal'] = 0;
					if(!isset($rule['taxAmount'])) $rule['taxAmount'] = 0;
					if(!isset($rule['DBTax'])) $rule['DBTax'] = 0;
					if(!isset($rule['percentage']) && $rule['subTotal'] < $cart->cartPrices['salesPrice']) {
						$rule['percentage'] = ($rule['subTotal'] + $rule['DBTax']) / ($cart->cartPrices['salesPrice'] + $cartdiscountBeforeTax);
					} else if(!isset($rule['percentage'])) {
						$rule['percentage'] = 1;
					}
					$rule['subTotalOld'] = $rule['subTotal'];
					$rule['subTotal'] = 0;
					$rule['taxAmountOld'] = $rule['taxAmount'];
					$rule['taxAmount'] = 0;
				}

				foreach($taxrules as &$rule){
					$rule['subTotal'] = $cart_prices[$this->_psType . 'Value'] * $rule['percentage'];
					$rule['psType'] = $this->_psType;

					if(!isset($cart_prices[$this->_psType . 'Tax'])) $cart_prices[$this->_psType . 'Tax'] = 0.0;
					$cart_prices[$this->_psType . 'TaxPerID'][$rule['virtuemart_calc_id']] = $calculator->roundInternal($calculator->roundInternal($calculator->interpreteMathOp($rule, $rule['subTotal'])) - $rule['subTotal'], 'salesPrice');
					$cart_prices[$this->_psType . 'Tax'] += $cart_prices[$this->_psType . 'TaxPerID'][$rule['virtuemart_calc_id']];

				}
			}
		}

		if(empty($method->cost_per_transaction)) $method->cost_per_transaction = 0.0;
		if(empty($method->cost_min_transaction)) $method->cost_min_transaction = 0.0;
		if(empty($method->cost_percent_total)) $method->cost_percent_total = 0.0;

		if (count ($taxrules) > 0 ) {

			$cart_prices['salesPrice' . $_psType] = $calculator->roundInternal ($calculator->executeCalculation ($taxrules, $cart_prices[$this->_psType . 'Value'],true,false), 'salesPrice');
//			$cart_prices[$this->_psType . 'Tax'] = $calculator->roundInternal (($cart_prices['salesPrice' . $_psType] -  $cart_prices[$this->_psType . 'Value']), 'salesPrice');
			reset($taxrules);

			foreach($taxrules as &$rule){
				if(!isset($cart_prices[$this->_psType . '_calc_id']) or !is_array($cart_prices[$this->_psType . '_calc_id'])) $cart_prices[$this->_psType . '_calc_id'] = array();
				$cart_prices[$this->_psType . '_calc_id'][] = $rule['virtuemart_calc_id'];

				if(isset($rule['subTotalOld'])) $rule['subTotal'] += $rule['subTotalOld'];
				if(isset($rule['taxAmountOld'])) $rule['taxAmount'] += $rule['taxAmountOld'];
				if(isset($rule['psType'])) unset($rule['psType']);
			}

		} else {

			$cart_prices['salesPrice' . $_psType] = $cart_prices[$this->_psType . 'Value'];
			$cart_prices[$this->_psType . 'Tax'] = 0;
			$cart_prices[$this->_psType . '_calc_id'] = 0;
		}
		//$c[$this->_psType][$method->$idN] =& $cart_prices;
		//if($_psType='Shipment')vmTrace('setCartPrices '.$cart_prices['salesPrice' . $_psType]);
		return $cart_prices['salesPrice' . $_psType];

	}

	/**
	 * calculateSalesPrice
	 *
	 * @param $value
	 * @param $tax_id: tax id
	 * @return $salesPrice
	 */
	protected function calculateSalesPrice ($cart, $method, $cart_prices) {

		return $this -> setCartPrices($cart,$cart_prices,$method);
	}

	/**
	 * setInConfirmOrder
	 * In VM 2.6.7, we introduced a double order checking.
	 * Now plugin itself can define if it should be possible to use the same trigger more than one time.
	 * this should be done at the begin of the trigger
	 * @author Valérie Isaksen
	 * @param $cart
	 */
	function setInConfirmOrder($cart) {
		$cart->_inConfirm = true;
		$cart->setCartIntoSession(false,true);
	}


	public function processConfirmedOrderPaymentResponse ($returnValue, $cart, $order, $html, $payment_name, $new_status = '') {

		if ($returnValue == 1) {
			//We delete the old stuff
			// send the email only if payment has been accepted
			// update status

			$modelOrder = VmModel::getModel ('orders');
			$order['order_status'] = $new_status;
			$order['customer_notified'] = 1;
			$order['comments'] = '';
			$modelOrder->updateStatusForOneOrder ($order['details']['BT']->virtuemart_order_id, $order, TRUE);

			$order['paymentName'] = $payment_name;
			//if(!class_exists('shopFunctionsF')) require(VMPATH_SITE.DS.'helpers'.DS.'shopfunctionsf.php');
			//shopFunctionsF::sentOrderConfirmedEmail($order);
			//We delete the old stuff
			$cart->emptyCart ();
			vRequest::setVar ('html', $html);
			// payment echos form, but cart should not be emptied, data is valid
		} elseif ($returnValue == 2) {
			$cart->_confirmDone = false;
			$cart->_dataValidated = false;
			$cart->_inConfirm = false;
			$cart->setCartIntoSession (false,true);
			vRequest::setVar ('html', $html);
		} elseif ($returnValue == 0) {
			// error while processing the payment
			$mainframe = JFactory::getApplication ();
			$mainframe->enqueueMessage ($html);
			$mainframe->redirect (JRoute::_ ('index.php?option=com_virtuemart&view=cart',FALSE), vmText::_ ('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
		}
	}

	/**
	 * @param $amount
	 * @param $currencyId
	 * @return array
	 */
	static function getAmountInCurrency($amount, $currencyId){
		$return = array();
		if (!class_exists ('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$paymentCurrency = CurrencyDisplay::getInstance($currencyId);

		$return['value'] = $paymentCurrency->roundForDisplay($amount,$currencyId,1.0,false,2);
		$return['display'] = $paymentCurrency->getFormattedCurrency($return['value']) ;
		return $return;
	}

	/**
	 * @param $amount
	 * @param $currencyId
	 * @return array
	 */
	static function getAmountValueInCurrency($amount, $currencyId){
		$return= vmPSPlugin::getAmountInCurrency($amount, $currencyId);
		return $return['value'];
	}

	function emptyCart ($session_id = NULL, $order_number = NULL) {

		if (!class_exists ('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$this->logInfo ('Notification: emptyCart ' . $session_id, 'message');
		if ($session_id != NULL and $order_number != NULL) {
			// Recover session from the storage session in wich the payment is done
			$this->emptyCartFromStorageSession ($session_id, $order_number);
		} else {

			$cart = VirtueMartCart::getCart ();
			$cart->emptyCart ();
		}
		return TRUE;
	}

	/*
		 * recovers the session from Storage, and only empty the cart if it has not been done already
		 */
	function emptyCartFromStorageSession ($session_id, $order_number) {

		$conf = JFactory::getConfig ();
		$handler = $conf->get ('session_handler', 'none');

		$config['session_name'] = 'site';
		$name = vRequest::getHash ($config['session_name']);
		$options['name'] = $name;
		$sessionStorage = JSessionStorage::getInstance ($handler, $options);

		// The session store MUST be registered.
		$sessionStorage->register ();

		// reads directly the session from the storage
		$sessionStored = $sessionStorage->read ($session_id);
		if (empty($sessionStored)) {
			return;
		}
		$sess2store = $this->_emptyCartFromStorageSession($sessionStored);
		if(!empty($sess2store)){
			$sessionStorage->write ($session_id, $sess2store);
		}

	}

	function _emptyCartFromStorageSession ($data) {

		if(strlen($data)<8) return false;
		$t = substr($data,7);
		if(empty($t)) return false;

		$unserb64enc = unserialize($t);

		if(!empty($unserb64enc)){
			$unser = base64_decode($unserb64enc);
			$unsunser = unserialize($unser);
			if($unsunser===FALSE){
				$m = 'Unserialize failed';
				vmError($m,$m);
				return false;
			} else {
				$cl = strtolower(get_class($unsunser));
				if(JVM_VERSION<3){
					$cld = 'jregistry';
				} else {
					$cld = 'joomla\registry\registry';
				}

				if($cl != $cld){
					$m = 'Wrong class in Session, check your installation, update your joomla
					 '.$cl;
					vmError($m, $m);
					return false;
				}
			}

			$vmObj = $unsunser->get('__vm',false);
			$tcart = json_decode($vmObj->vmcart);
			if(empty($tcart) or json_last_error()!=JSON_ERROR_NONE){
				return false;
			}
			if (!class_exists('VirtueMartCart')) require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
			if($tcart->virtuemart_cart_id){
				$model = new VmModel();
				$carts = $model->getTable('carts');
				if(!empty($tcart->virtuemart_cart_id)){
					$carts->delete($tcart->virtuemart_cart_id,'virtuemart_cart_id');
				}
			}
			VirtuemartCart::emptyCartValues($tcart,false);

			$vmObj->vmcart = vmJsApi::safe_json_encode($tcart);
			$unsunser->set('__vm',$vmObj);

			$runser = serialize($unsunser);
			$runserb64enc = base64_encode($runser);
			$rt = serialize($runserb64enc);
			return 'joomla|'.$rt;
		}

	}


	private static function session_decode ($session_data) {

		$decoded_session = array();
		$offset = 0;

		while ($offset < strlen ($session_data)) {
			if (!strstr (substr ($session_data, $offset), "|")) {
				return array();
			}
			$pos = strpos ($session_data, "|", $offset);
			$num = $pos - $offset;
			$varname = substr ($session_data, $offset, $num);
			$offset += $num + 1;

			$value = substr ($session_data, $offset);

			if(!empty($value) && !is_int($value)){
				$data = unserialize($value);
			}
			$decoded_session[$varname] = $data;
			$offset += strlen (serialize($data));
		}
		return $decoded_session;
	}


	private static function session_encode ($session_data_array) {

		$encoded_session = "";
		foreach ($session_data_array as $key => $session_data) {
			$encoded_session .= $key . "|" . serialize ($session_data);
		}
		return $encoded_session;
	}


	/**
	 *  @param integer $virtuemart_order_id the id of the order
	 */
	function handlePaymentUserCancel ($virtuemart_order_id) {

		if ($virtuemart_order_id) {
			// set the order to cancel , to handle the stock correctly
			if (!class_exists ('VirtueMartModelOrders')) {
				require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
			}

			$modelOrder = VmModel::getModel ('orders');
			$order['order_status'] = 'X';
			$order['virtuemart_order_id'] = $virtuemart_order_id;
			$order['customer_notified'] = 0;
			$order['comments'] = vmText::_ ('COM_VIRTUEMART_PAYMENT_CANCELLED_BY_SHOPPER');
			$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
			//$modelOrder->remove (array('virtuemart_order_id' => $virtuemart_order_id));
		}
	}

	/**
	 * logInfo
	 * to help debugging Payment notification for example
	 * Keep it for compatibilty
	 */
	protected function logInfo ($text, $type = 'message', $doLog=false) {
		if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
		VmConfig::loadConfig();
		if ((isset($this->_debug) and $this->_debug) OR $doLog) {
			$oldLogFileName= 	VmConfig::$logFileName;
			VmConfig::$logFileName =$this->getLogFileName() ;
			logInfo($text, $type);
			VmConfig::$logFileName =$oldLogFileName;
		}
	}

	/**
	 *
	 */
	function getLogFileName() {
		$name=$this->_idName;
		$methodId=0;
		if (isset ($this->_currentMethod) ) {
			$methodId=$this->_currentMethod->$name;
		}

		return $this->_name. '.'.$methodId ;
	}

	/**
	 * log all messages of type ERROR
	 * log in case the debug option is on, and the log option is on
	* @param string $message the message to write
	* @param string $title
	* @param string $type message, deb-ug,  info, error
	* @param boolean $doDebug in payment notification, we don't want to use vmdebug even if the debug option  is on
	 *
	 */
	public function debugLog($message, $title='', $type = 'message', $doDebug=true) {

		if ( isset($this->_currentMethod) and !$this->_currentMethod->log and $type !='error') {
			//Do not log message messages if we are not in LOG mode
			return;
		}

		if ( $type == 'error') {
			$this->sendEmailToVendorAndAdmins();
		}

		$this->logInfo($title.': '.print_r($message,true), $type, true);
	}


}

