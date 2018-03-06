<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage Plugins  - Elements
 * @package VirtueMart
 * @subpackage
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id$
 *
 * http://www1.paybox.com/telechargements/ManuelIntegrationPaybox_V5.08_FR.pdf
 * Pour accéder au Back-office commerçant: https://preprod-admin.paybox.com
 */
if (!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

class plgVmpaymentPaybox extends vmPSPlugin {

	// instance of class

	function __construct(& $subject, $config) {

		//if (self::$_this)
		//   return self::$_this;
		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; //virtuemart_paybox_id';
		$this->_tableId = 'id'; //'virtuemart_paybox_id';
		$varsToPush = $this->getVarsToPush();
		//$this->setEncryptedFields(array('params'));
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

		$this->setCryptedFields(array('key'));

	}

	protected function getVmPluginCreateTableSQL() {

		return $this->createTableSQL('Payment paybox Table');
	}

	function getTableSQLFields() {

		$SQLfields = array(
			'id' => 'int(1) unsigned NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id' => 'int(11) UNSIGNED',
			'order_number' => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name' => 'varchar(5000)',
			'payment_order_total' => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
			'payment_currency' => 'smallint(1)',
			'email_currency' => 'smallint(1)',
			'recurring' => 'varchar(512)',
			'recurring_number' => 'smallint(1)',
			'recurring_periodicity' => 'smallint(1)',
			'cost_per_transaction' => 'decimal(10,2)',
			'cost_percent_total' => 'decimal(10,2)',
			'tax_id' => 'smallint(1)',
			'paybox_custom' => 'varchar(255) ',
// ONLY SAVE THE ONE WE EVENTUALLY WANT TO DO A SEARCH
			'paybox_response_T' => 'smallint(1)',
			//Numéro d’appel Paybox
			'paybox_response_A' => 'char(10)',
			//numéro d’Autorisation (numéro remis par le centre d’autorisation) : URL encodé
			'paybox_response_B' => 'char(13)',
			// numéro d’aBonnement (numéro remis par Paybox)
			//'paybox_response_C'            => 'char(13)', // Type de Carte retenu (cf. PBX_TYPECARTE)
			//'paybox_response_D'           => 'char(28)', // Date de fin de validité de la carte du porteur. Format : AAMM
			'paybox_response_E' => 'char(6)',
			// Code réponse de la transaction (cf. Tableau 3 : Codes réponse PBX_RETOUR)
			//'paybox_response_F'             => 'char(1)', //Etat de l’authentiFication du porteur vis-à-vis du programme 3-D Secure :
			//'paybox_response_G'              => 'char(1)', // Garantie du paiement par le programme 3-D Secure. Format : O ou N
			//'paybox_response_J'       => 'smallint(1)', // 2 derniers chiffres du numéro de carte du porteur
			//'paybox_response_N'       => 'smallint(1)', // 6 premiers chiffres (« biN6 ») du numéro de carte de l’acheteur
			//'paybox_response_O'       => 'char(1)', // 6 premiers chiffres (« biN6 ») du numéro de carte de l’acheteur
			'paybox_response_S' => 'smallint(1)',
			//Numéro de TranSaction Paybox


			'paybox_fullresponse' => 'text'
		);
		return $SQLfields;
	}

	function plgVmConfirmedOrder($cart, $order) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$payboxInterface = $this->_loadPayboxInterface($this);
		$this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');
		$payboxInterface->confirmedOrder($cart, $order);

		return;
	}


	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return FALSE;
		}
		$this->getPaymentCurrency($method);
		$paymentCurrencyId = $method->payment_currency;
		return TRUE;
	}


	function plgVmOnPaymentResponseReceived(&$html) {

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}


		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);

		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}
		$paybox_data = vRequest::getGet();

		$this->debugLog('"<pre>plgVmOnPaymentResponseReceived :' . var_export($paybox_data, true) . "</pre>", 'debug');
		$payboxInterface = $this->_loadPayboxInterface($this);
		$html = $payboxInterface->paymentResponseReceived($paybox_data);
		vRequest::setVar('display_title', false);
		vRequest::setVar('html', $html);
		return true;
	}


	function plgVmOnUserPaymentCancel() {

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		$order_number = vRequest::getUword('on');
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', '');
		if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL;
		}
		$numerr = vRequest::getString('E', '');
		if ($numerr) {
			VmInfo('VMPAYMENT_' . $this->_name . '_PBX_NUMERR_' . abs($numerr));
		}
		if (!$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number)) {
			return NULL;
		}
		if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
			return NULL;
		}

		$session = JFactory::getSession();
		$return_context = $session->getId();
		$field = $this->_name . '_custom';
		if (strcmp($paymentTable->$field, $return_context) === 0) {
			$this->handlePaymentUserCancel($virtuemart_order_id);
		}
		return TRUE;
	}

	/**
	 *   plgVmOnPaymentNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
	 * Return:
	 * Parameters:
	 *  None
	 * @author Valerie Isaksen
	 */

	function plgVmOnPaymentNotification() {

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		$paybox_data = $_POST;

		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);
		$this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return;
		}
		$paybox_data_log=$paybox_data;
		unset($paybox_data_log['K']);
		$this->debugLog(var_export($paybox_data_log, true), 'plgVmOnPaymentNotification', 'debug', false);
		$payboxInterface = $this->_loadPayboxInterface($this);
		if (!$payboxInterface->isPayboxResponseValid( $paybox_data, true, false)) {
			return FALSE;
		}
		$order_number = $payboxInterface->getOrderNumber($paybox_data['R']);
		if (empty($order_number)) {
			$this->debugLog($order_number, 'getOrderNumber not correct' . $paybox_data['R'], 'debug', false);
			return FALSE;
		}
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return FALSE;
		}

		if (!($payments = $this->getPluginDatasByOrderId($virtuemart_order_id))) {
			$this->debugLog('no payments found', 'getDatasByOrderId', 'debug', false);
			return FALSE;
		}

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$extra_comment = "";
		if (count($payments) == 1) {
			// NOTIFY not received
			$order_history = $payboxInterface->updateOrderStatus($paybox_data, $order, $payments);
			if (isset($order_history['extra_comment'])) {
				$extra_comment = $order_history['extra_comment'];
			}
		}

		if (!empty($payments[0]->paybox_custom)) {
			$this->emptyCart($payments[0]->paybox_custom, $order['details']['BT']->order_number);
			$this->setEmptyCartDone($payments[0]);
		}
		return TRUE;
	}
	/**
	 * @param $paybox_data
	 * @return bool
	 */

	function paymentNotification ($paybox_data) {


		if (!$this->isPayboxResponseValid( $paybox_data, true, false)) {
			return FALSE;
		}
		$order_number = $this->getOrderNumber($paybox_data['R']);
		if (empty($order_number)) {
			$this->plugin->debugLog($order_number, 'getOrderNumber not correct' . $paybox_data['R'], 'debug', false);
			return FALSE;
		}
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return FALSE;
		}

		if (!($payments = $this->plugin->getPluginDatasByOrderId($virtuemart_order_id))) {
			$this->plugin->debugLog('no payments found', 'getDatasByOrderId', 'debug', false);
			return FALSE;
		}

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$extra_comment = "";
		if (count($payments) == 1) {
			// NOTIFY not received
			$order_history = $this->updateOrderStatus($paybox_data, $order, $payments);
			if (isset($order_history['extra_comment'])) {
				$extra_comment = $order_history['extra_comment'];
			}
		}


		return $payments[0]->paybox_custom;
	}


	/**
	 * @param $firstPayment
	 */
	function setEmptyCartDone($firstPayment) {
		$firstPayment = (array)$firstPayment;
		$firstPayment['paybox_custom'] = NULL;
		$this->storePSPluginInternalData($firstPayment, $this->_tablepkey, true);
	}

	function   storePSPluginInternalData($values, $primaryKey = 0, $preload = FALSE) {
		parent::storePSPluginInternalData($values, $primaryKey, $preload);
	}

	/**
	 * Get Method Datas for a given Payment ID
	 *
	 * @author Valérie Isaksen
	 * @param int $virtuemart_order_id The order ID
	 * @return  $methodData
	 */
	function getPluginDatasByOrderId($virtuemart_order_id) {

		return $this->getDatasByOrderId($virtuemart_order_id);
	}

	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id) {

		if (!$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		$payboxInterface = $this->_loadPayboxInterface($this);
		$html = $payboxInterface->showOrderBEPayment($virtuemart_order_id);


		return $html;
	}

	function getHtmlHeaderBE() {
		return parent:: getHtmlHeaderBE();
	}

	/**
	 * @param plugin $method
	 * @return mixed|string
	 */
	function renderPluginName($method) {
		$logos = $method->payment_logos;
		$display_logos = '';
		if (!empty($logos)) {
			$display_logos = $this->displayLogos($logos) . ' ';
		}
		$payment_name = $method->payment_name;
		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$this->_currentMethod = $method;
		$extraInfo = $this->getExtraPluginNameInfo($method);

		$html = $this->renderByLayout('render_pluginname', array(
			'shop_mode' => $method->shop_mode,
			'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id,
			'logo' => $display_logos,
			'payment_name' => $payment_name,
			'payment_description' => $method->payment_desc,
			'extraInfo' => $extraInfo,
		));
		$html = $this->rmspace($html);
		return $html;
	}

	private function getExtraPluginNameInfo($activeMethod) {
		$this->_method = $activeMethod;

		$payboxInterface = $this->_loadPayboxInterface();
		$extraInfo = $payboxInterface->getExtraPluginNameInfo();

		return $extraInfo;

	}

	private function rmspace($buffer) {
		return preg_replace('~>\s*\n\s*<~', '><', $buffer);
	}

	function getCosts(VirtueMartCart $cart, $method, $cart_prices) {

		if (preg_match('/%$/', $method->cost_percent_total)) {
			$cost_percent_total = substr($method->cost_percent_total, 0, -1);
		} else {
			$cost_percent_total = $method->cost_percent_total;
		}
		return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
	}

	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 *
	 * @author: Valerie Isaksen
	 *
	 * @param $cart_prices: cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions($cart, $method, $cart_prices) {
		//vmTrace('checkConditions', true);
		//$this->debugLog( $cart_prices['salesPrice'], 'checkConditions','debug');
		$this->_currentMethod = $method;
		$payboxInterface = $this->_loadPayboxInterface();
		return $payboxInterface->checkConditions($cart, $method, $cart_prices);
	}


	/**
	 * We must reimplement this triggers for joomla 1.7
	 */

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {
		if ($res = $this->selectedThisByJPluginId($jplugin_id)) {

			$virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id');
			$method = $this->getPluginMethod($virtuemart_paymentmethod_id);
			vmdebug('plgVmOnStoreInstallPaymentPluginTable', $method, $virtuemart_paymentmethod_id);
			//$this->createRootFile($method->virtuemart_paymentmethod_id);
			/*
						$mandatory_fields = array('site_id', 'rang', 'identifiant', 'key');
						foreach ($mandatory_fields as $mandatory_field) {
							if (empty($method->$mandatory_field)) {
								vmError(vmText::sprintf('VMPAYMENT_'.$this->_name.'_CONF_MANDATORY_PARAM', vmText::_('VMPAYMENT_'.$this->_name.'_CONF_' . $mandatory_field)));
							}
						}
			*/
			if (!extension_loaded('curl')) {
				vmError(vmText::sprintf('VMPAYMENT_' . $this->_name . '_CONF_MANDATORY_PHP_EXTENSION', 'curl'));
			}
			if (!extension_loaded('openssl')) {
				vmError(vmText::sprintf('VMPAYMENT_' . $this->_name . '_CONF_MANDATORY_PHP_EXTENSION', 'openssl'));
			}
		}

		return $this->onStoreInstallPluginTable($jplugin_id);
	}


	/**
	 * @param $virtuemart_paymentmethod_id
	 * @return bool
	 */
	function createRootFile($virtuemart_paymentmethod_id) {
		$created = false;
		$filename = $this->getPayboxRootFileName($virtuemart_paymentmethod_id);
		if (!JFile::exists($filename)) {
			$content = '
				<?php
				/**
				* File used by the Paybox VirtueMart Payment plugin
				**/
				$get=filter_var_array($_GET, FILTER_SANITIZE_STRING);
				$_GET["option"]="com_virtuemart";
				$_GET["element"]="paybox";
				$_GET["pm"]=' . $virtuemart_paymentmethod_id . ';
				$_REQUEST["option"]="com_virtuemart";
				$_REQUEST["element"]="paybox";
				$_REQUEST["pm"]=' . $virtuemart_paymentmethod_id . ';
				if ($get["pbx"]=="ok") {
					$_GET["view"]="pluginresponse";
					$_GET["task"]="pluginresponsereceived";
					$_REQUEST["view"]="pluginresponse";
					$_REQUEST["task"]="pluginresponsereceived";
				} elseif ($get["pbx"]=="no") {
					$_GET["view"]="pluginresponse";
					$_GET["task"]="pluginnotification";
					$_GET["format"]="raw";
					$_GET["tmpl"]="component";
					$_REQUEST["view"]="pluginresponse";
					$_REQUEST["task"]="pluginnotification";
					$_REQUEST["format"]="raw";
					$_REQUEST["tmpl"]="component";
					} elseif ($get["pbx"]=="ko") {
					$_GET["view"]="pluginresponse";
					$_REQUEST["view"]="pluginresponse";
					$_REQUEST["view"]="pluginUserPaymentCancel";
				}
				include("index.php");
				';
			if (!JFile::write($filename, $content)) {
				$msg = 'Could not write in file  ' . $filename . ' to store paybox information. Check your file ' . $filename . ' permissions.';
				vmError($msg);
			}
			$created = true;
		}
		return $created;
	}

	function getPayboxRootFileName($virtuemart_paymentmethod_id) {
		$filename = JPATH_SITE . '/' . $this->getPayboxFileName($virtuemart_paymentmethod_id);
		return $filename;
	}

	function getPayboxFileName($virtuemart_paymentmethod_id) {
		return 'vmpayment' . '_' . $virtuemart_paymentmethod_id . '.php';
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
	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg) {

		return $this->OnSelectCheck($cart);
	}

	/**
	 * plgVmDisplayListFEPayment
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
	 *
	 * @param object $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on succes, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 * @author Max Milbers
	 */
	public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		return $this->displayListFE($cart, $selected, $htmlIn);
	}

	/*
* plgVmonSelectedCalculatePricePayment
* Calculate the price (value, tax_id) of the selected method
* It is called by the calculator
* This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
* @author Valerie Isaksen
* @cart: VirtueMartCart the current cart
* @cart_prices: array the new cart prices
* @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
*
*
*/

	public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}

	/**
	 * plgVmOnCheckAutomaticSelectedPayment
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 *
	 * @author Valerie Isaksen
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {

		return $this->onCheckAutomaticSelected($cart, $cart_prices);
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
	public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}

	/**
	 * This event is fired during the checkout process. It can be used to validate the
	 * method data as entered by the user.
	 *
	 * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
	 * @author Max Milbers

	public function plgVmOnCheckoutCheckDataPayment (VirtueMartCart $cart) {
	return NULL;
	}
	 */

	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmonShowOrderPrintPayment($order_number, $method_id) {

		return $this->onShowOrderPrint($order_number, $method_id);
	}

	/**
	 * Save updated order data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.

	public function plgVmOnUpdateOrderPayment(  $_formData) {
	return null;
	}
	 */
	/**
	 * Save updated orderline data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.

	public function plgVmOnUpdateOrderLine(  $_formData) {
	return null;
	}
	 */
	/**
	 * plgVmOnEditOrderLineBE
	 * This method is fired when editing the order line details in the backend.
	 * It can be used to add line specific package codes
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise

	public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
	return null;
	}
	 */

	/**
	 * This method is fired when showing the order details in the frontend, for every orderline.
	 * It can be used to display line specific package codes, e.g. with a link to external tracking and
	 * tracing systems
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise

	public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
	return null;
	}
	 */
	function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}

	function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
		return $this->setOnTablePluginParams($name, $id, $table);
	}


	/**
	 * @param $response
	 * @param $order
	 * @return null|string
	 */
	function getResponseHTML($order, $paybox_data, $success, $extra_comment) {

		$payment_name = $this->renderPluginName($this->_currentMethod);
		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $order['details']['BT']->order_currency . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$currency_numeric_code = $db->loadResult();
		$html = $this->renderByLayout('response', array(
			"success" => $success,
			"payment_name" => $payment_name,
			"transactionId" => $paybox_data['S'],
			"amount" => $paybox_data['M'] * 0.01,
			"extra_comment" => $extra_comment,
			"currency" => $currency_numeric_code,
			"order_number" => $order['details']['BT']->order_number,
			"order_pass" => $order['details']['BT']->order_pass,
		));
		return $html;


	}

	/*********************/
	/* Private functions */
	/*********************/
	private function _loadPayboxInterface() {
		if (!class_exists('PayboxHelperPaybox')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . $this->_name . DS . $this->_name . DS . 'helpers' . DS . 'paybox.php');
		}
		if ($this->_currentMethod->integration == 'recurring') {
			if (!class_exists('PayboxHelperPayboxRecurring')) {
				require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . $this->_name . DS . $this->_name . DS . 'helpers' . DS . 'recurring.php');
			}
			$payboxInterface = new PayboxHelperPayboxRecurring($this->_currentMethod, $this, $this->_name);
		} elseif ($this->_currentMethod->integration == 'subscribe') {
			if (!class_exists('PayboxHelperPayboxSubscribe')) {
				require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . $this->_name . DS . $this->_name . DS . 'helpers' . DS . 'subscribe.php');
			}
			$payboxInterface = new PayboxHelperPayboxSubscribe($this->_currentMethod, $this, $this->_name);
		} else {
			$payboxInterface = new PayboxHelperPaybox($this->_currentMethod, $this, $this->_name);
		}
		return $payboxInterface;
	}


	function getEmailCurrency(&$method) {

		if (!isset($method->email_currency)  or $method->email_currency == 'vendor') {
			$vendor_model = VmModel::getModel('vendor');
			$vendor = $vendor_model->getVendor($method->virtuemart_vendor_id);
			return $vendor->vendor_currency;
		} else {
			return $method->payment_currency; // either the vendor currency, either same currency as payment
		}
	}

	private function getKeyFileName() {
		return 'pubkey.pem';
	}

	function getTablename() {
		return $this->_tablename;
	}

	/**
	 * @param string $message
	 * @param string $title
	 * @param string $type
	 * @param bool $echo
	 * @param bool $doVmDebug
	 */
	public function debugLog($message, $title = '', $type = 'message', $echo = false, $doVmDebug = false) {

		if ($this->_currentMethod->debug) {
			$this->debug($message, $title, true);
		}

		if ($echo) {
			echo $message . '<br/>';
		}


		parent::debugLog($message, $title, $type, $doVmDebug);
	}

	public function debug($subject, $title = '', $echo = true) {

		$debug = '<div style="display:block; margin-bottom:5px; border:1px solid red; padding:5px; text-align:left; font-size:10px;white-space:nowrap; overflow:scroll;">';
		$debug .= ($title) ? '<br /><strong>' . $title . ':</strong><br />' : '';
		//$debug .= '<pre>';
		if (is_array($subject)) {
			$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", nl2br(str_replace(" ", " &nbsp; ", print_r($subject, true)))));
		} else {
			//$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", (str_replace(" ", " &nbsp; ", print_r($subject, true)))));
			$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", print_r($subject, true)));

		}

		//$debug .= '</pre>';
		$debug .= '</div>';
		if ($echo) {
			echo $debug;
		} else {
			return $debug;
		}
	}

}

// No closing tag
