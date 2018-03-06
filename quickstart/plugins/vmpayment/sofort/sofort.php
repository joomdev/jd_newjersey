<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @author Valérie Isaksen
 * @version $Id: sofort.php 9667 2017-11-15 11:17:36Z Milbo $
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.   - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
if (!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

class plgVmPaymentSofort extends vmPSPlugin {
	const RELEASE = 'VM 3.2.6';
	const SU_SOFORTBANKING = 'su';


	function __construct (& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; //virtuemart_sofort_id';
		$this->_tableId = 'id'; //'virtuemart_sofort_id';

		$varsToPush = $this->getVarsToPush();

		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

	}

	/**
	 * @return string
	 */
	public function getVmPluginCreateTableSQL () {

		return $this->createTableSQL('Payment Sofort Table');
	}

	/**
	 * @return array
	 */
	function getTableSQLFields () {

		$SQLfields = array(
			'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id' => 'int(1) UNSIGNED',
			'order_number' => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name' => 'varchar(1000)',
			'payment_order_total' => 'decimal(15,5) NOT NULL',
			'payment_currency' => 'smallint(1)',
			'email_currency' => 'smallint(1)',
			'cost_per_transaction' => 'decimal(10,2)',
			'cost_percent_total' => 'decimal(10,2)',
			'tax_id' => 'smallint(1)',
			'sofort_custom' => 'varchar(255)',
			'security' => 'varchar(50)',
			'sofort_response_amount' => 'decimal(15,5) NOT NULL',
			'sofort_response_currency' => 'varchar(50)',
			'sofort_response_status' => 'varchar(50)',
			'sofort_response_status_reason' => 'varchar(50)',
			'sofort_response_transaction' => 'varchar(100)',
			'sofort_response_invoice' => 'varchar(1000)'
		);
		return $SQLfields;
	}

	/**
	 * @param $cart
	 * @param $order
	 * @return bool|null
	 */
	function plgVmConfirmedOrder ($cart, $order) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$this->sendTransactionRequest( $cart, $order);

	}


	function displayErrors ($errors) {

		foreach ($errors as $error) {
			vmError(vmText::sprintf('VMPAYMENT_SOFORT_ERROR_FROM', $error ['message'], $error ['field'], $error ['code']));
			vmInfo(vmText::sprintf('VMPAYMENT_SOFORT_ERROR_FROM', $error ['message'], $error ['field'], $error ['code']));
			if ($error ['message'] == 401) {
				vmdebug('check you payment parameters: custom_id, project_id, api key');
			}
		}
	}


	function sendTransactionRequest ( $cart, $order, $doRedirect = true) {


		$session = JFactory::getSession();
		$return_context = $session->getId();

//$this->_debug = $method->debug;
		//$this->debugLog('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');
		vmdebug('SOFORT sendTransactionRequest');
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}
		if (!class_exists ('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		if (!class_exists('TableVendors')) {
			require(VMPATH_ADMIN . DS . 'tables' . DS . 'vendors.php');
		}

		$this->getPaymentCurrency($this->_currentMethod);
		$email_currency = $this->getEmailCurrency($this->_currentMethod);
		$currency_code_3 = shopFunctions::getCurrencyByID($this->_currentMethod->payment_currency, 'currency_code_3');
		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total,$this->_currentMethod->payment_currency);
		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);


// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName($this->_currentMethod, 'create_order');
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
		$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
		$dbValues['payment_currency'] = $this->_currentMethod->payment_currency;
		$dbValues['email_currency'] = $email_currency;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency['value'];
		$dbValues['tax_id'] = $this->_currentMethod->tax_id;
		$dbValues['sofort_custom'] = $return_context;

		$security = self::getSecurityKey();
		$dbValues['security'] = $security;

		$this->debugLog('comes from '.(int)$doRedirect.' order number'.$order['details']['BT']->order_number, "sendTransactionRequest ", 'debug');

		if (!class_exists('SofortLib')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib.php');
		}
		$sofort = new SofortLib_Multipay(trim($this->_currentMethod->configuration_key));
		$this->sofortLog($sofort);
		$sofort->setVersion(self::RELEASE);
		$sofort->setAmount($totalInPaymentCurrency['value'], $currency_code_3);
		$sofort->setReason($order['details']['BT']->order_number);
		$sofort->setSuccessUrl(self::getSuccessUrl($order));
		$sofort->setAbortUrl(self::getCancelUrl($order));
		$sofort->setNotificationUrl(self::getNotificationUrl($security, $order));
		$sofort->setSofortueberweisung();
		$sofort->setSofortueberweisungCustomerprotection($this->_currentMethod->buyer_protection);

		$jlang = JFactory::getLanguage ();
		$lang = $jlang->getTag ();
		$langArray = explode ("-", $lang);
		$lang = strtolower ($langArray[0]);
		$sofort->setLanguageCode($lang);

		$sofort->sendRequest();
		vmdebug('SOFORT sendTransactionRequest ... SofortLib_Multipay ... sendRequest()');
		if ($sofort->isError()) {
			$errors = $sofort->getErrors();
			vmdebug('SOFORT sendTransactionRequest ... SofortLib_Multipay ... getErrors()', $errors);
			$this->displayErrors($errors);
			$this->redirectToCart();
			return;
		}
		$url = $sofort->getPaymentUrl();

		$dbValues['sofort_response_transaction'] = $sofort->getTransactionId();

		$this->storePSPluginInternalData($dbValues);
		if ($doRedirect) {
			$mainframe = JFactory::getApplication();
			$mainframe->redirect($url);
		}

	}

	function redirectToCart ($msg = NULL) {

		if (!$msg) {
			$msg = vmText::_('VMPAYMENT_SOFORT_ERROR_TRY_AGAIN');
		}
		$app = JFactory::getApplication();
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&Itemid=' . vRequest::getInt('Itemid').'&lang='.vRequest::getCmd('lang',''), false), $msg);
	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetPaymentCurrency ($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$this->getPaymentCurrency($this->_currentMethod);
		$paymentCurrencyId = $this->_currentMethod->payment_currency;
	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetEmailCurrency ($virtuemart_paymentmethod_id, $virtuemart_order_id, &$emailCurrencyId) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		//vmdebug('plgVmgetEmailCurrency', $payments);

		if (empty($payments[0]->email_currency)) {
			$vendorId = 1; //VirtueMartModelVendor::getLoggedVendor();
			$db = JFactory::getDBO();
			$q = 'SELECT   `vendor_currency` FROM `#__virtuemart_vendors` WHERE `virtuemart_vendor_id`=' . $vendorId;
			$db->setQuery($q);
			$emailCurrencyId = $db->loadResult();
		} else {
			$emailCurrencyId = $payments[0]->email_currency;
		}

	}

	/**
	 * @param $html
	 * @return bool|null|string
	 */
	function plgVmOnPaymentResponseReceived (&$html) {

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		// the payment itself should send the parameter needed.
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);
		$order_number = vRequest::getString('on', 0);

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			//vmdebug('plgVmOnPaymentResponseReceived NOT getVmPluginMethod');
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod ->payment_element)) {
			//vmdebug('SOFORT plgVmOnPaymentResponseReceived NOT selectedThisElement');
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			//vmdebug('SOFORT plgVmOnPaymentResponseReceived NOT getOrderIdByOrderNumber');
			return NULL;
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		// may be we did not receive the notification
		// Thus the call of the success-URL should check, if the notification has already been arrived at the shop  .
		//If this is not true, a transaction detail request (step 4) should be triggered with the call of the success-URL,


		$html = $this->_getPaymentResponseHtml($this->_currentMethod, $order, $payments);
		//We delete the old stuff
		// get the correct cart / session
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();
		return TRUE;
	}

	/**
	 * @return bool|null
	 */
	function plgVmOnUserPaymentCancel () {


		$order_number = vRequest::getString('on', '');
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', '');
		if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			vmdebug('plgVmOnUserPaymentCancel', $order_number, $virtuemart_paymentmethod_id);
			return NULL;
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return NULL;
		}
		if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
			return NULL;
		}
		vmdebug('plgVmOnUserPaymentCancel', 'VMPAYMENT_SOFORT_PAYMENT_CANCELLED');

		VmInfo(vmText::_('VMPAYMENT_SOFORT_PAYMENT_CANCELLED'));
		$session = JFactory::getSession();
		$return_context = $session->getId();
		if (strcmp($paymentTable->sofort_custom, $return_context) === 0) {
			vmDebug('handlePaymentUserCancel');
			$this->handlePaymentUserCancel($virtuemart_order_id);
		} else {
			vmDebug('Return context', $paymentTable->sofort_custom, $return_context);

		}
		return TRUE;
	}

	/*
		 * plgVmOnPaymentNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
		 * Return:
		 * Parameters:
		 *  None
		 *  @author Valerie Isaksen
		 */

	/**
	 * @return bool|null
	 */
	function plgVmOnPaymentNotification () {

		//$this->_debug = true;

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);

		//$this->_debug=true;
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$order_number = vRequest::getString('on', '');
		if (empty($order_number)) {
			return FALSE;
		}
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return FALSE;
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			return FALSE;
		}
		$this->debugLog('OK','plgVmOnPaymentNotification', 'debug');


		if (!class_exists('SofortLib')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib.php');

		}

		$sofortLib_Notification = new SofortLib_Notification();
		$this->sofortLog($sofortLib_Notification);

		$transactionId = $sofortLib_Notification->getNotification();

		if($sofortLib_Notification->isError()){
			$this->debugLog('SOFORT notification return Error '.$sofortLib_Notification->getError(),'plgVmOnPaymentNotification', 'error');
		}
		//no valid parameters/xml
		if (empty($transactionId)) {
			$this->debugLog('no transaction ID for order number '. $order_number,'plgVmOnPaymentNotification', 'error');

		}

		if (empty($transactionId) || $sofortLib_Notification->isError()) {
			return FALSE;
		}

		$this->debugLog( $transactionId, 'plgVmOnPaymentNotification Transaction ID ','debug');

		$sofortLib_TransactionData = new SofortLib_TransactionData(trim($this->_currentMethod->configuration_key));
		$this->sofortLog($sofortLib_TransactionData);
		$sofortLib_TransactionData->setTransaction($transactionId)->sendRequest();

		// check that secret , and order are identical
		$security = vRequest::getString('security', '');
		if ($security != $payments[0]->security) {
			$this->debugLog("security token received: " . $security.  " security token expected: " . $payments[0]->security,'plgVmOnPaymentNotification', 'error');
			return false;
		}

		$paymentMethod = $sofortLib_TransactionData->getPaymentMethod();
		if ($paymentMethod != self::SU_SOFORTBANKING) {
			$this->debugLog( "Payment method is " . $paymentMethod . " Should be SU". 'plgVmOnPaymentNotification' , 'error');
			return false;
		}

		$sofort_data['sofort_response_amount'] = $sofortLib_TransactionData->getAmount();
		$sofort_data['sofort_response_currency'] = $sofortLib_TransactionData->getCurrency();

		// check that the amount is the same
		if (!$this->_checkAmountAndCurrency($sofort_data, $payments)) {
			return false;
		}

		$modelOrder = VmModel::getModel('orders');
		$order_history = array();
		$status = 'status_' . $sofortLib_TransactionData->getStatus();
		//$this->debugLog('plgVmOnPaymentNotification getStatus:' .$status. ' '.var_export($method, true) , 'message');

		$order_history['customer_notified'] = true;
		$order_history['order_status'] = $this->_currentMethod->$status;
		$order_history['comments'] = vmText::_('VMPAYMENT_SOFORT_RESPONSE_STATUS_REASON_' . $sofortLib_TransactionData->getStatusReason());

		$sofort_data['sofort_response_status_reason'] = $sofortLib_TransactionData->getStatusReason();
		$sofort_data['sofort_response_transaction'] = $sofortLib_TransactionData->getTransaction();
		$sofort_data['payment_name'] = str_replace(array('\t', '\n'), '', $this->renderPluginName($this->_currentMethod));
		$sofort_data['virtuemart_order_id'] = $payments[0]->virtuemart_order_id;
		$sofort_data['order_number'] = $payments[0]->order_number;
		$sofort_data['virtuemart_paymentmethod_id'] = $payments[0]->virtuemart_paymentmethod_id;
		$sofort_data['sofort_response_status'] = $sofortLib_TransactionData->getStatus();;
		$sofort_data['sofort_response_status_reason'] = $sofortLib_TransactionData->getStatusReason();

		$this->debugLog(var_export($sofort_data, true), 'plgVmOnPaymentNotification storePSPluginInternalData ' , 'debug');

		$this->storePSPluginInternalData($sofort_data);

		$modelOrder->updateStatusForOneOrder($payments[0]->virtuemart_order_id, $order_history, false);
	}

	function _checkAmountAndCurrency ($sofort_data, $payments) {
		$payment_currency_code_3 = shopFunctions::getCurrencyByID($payments[0]->payment_currency, 'currency_code_3');
		if (($sofort_data['sofort_response_amount'] != $payments[0]->payment_order_total) or ($sofort_data['sofort_response_currency'] != $payment_currency_code_3)) {
			$this->debugLog( $sofort_data['sofort_response_amount'] . ' ' . $payments[0]->payment_order_total.' '. $sofort_data['sofort_response_currency'] . ' ' . $payment_currency_code_3, 'plgVmOnPaymentNotification _checkAmountAndCurrency' , 'error');
			return false;
		}
		return true;
	}

	/**
	 * Display stored payment data for an order
	 * @param  int $virtuemart_order_id
	 * @param  int $payment_method_id
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment ($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId($payment_method_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$html = '<table class="adminlist table">' . "\n";
		$html .= $this->getHtmlHeaderBE();
		$code = "sofort_response_";
		$first = TRUE;
		foreach ($payments as $payment) {
			$html .= '<tr class="row1"><th>' . vmText::_('COM_VIRTUEMART_DATE') . '</th><th align="left">' . $payment->created_on . '</th></tr>';
			// Now only the first entry has this data when creating the order
			if ($first) {
				$html .= $this->getHtmlRowBE('SOFORT_PAYMENT_NAME', $payment->payment_name);
				// keep that test to have it backwards compatible. Old version was deleting that column  when receiving an IPN notification
				if ($payment->payment_order_total and  $payment->payment_order_total != 0.00) {
					$html .= $this->getHtmlRowBE('SOFORT_PAYMENT_ORDER_TOTAL', $payment->payment_order_total . " " . shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));
				}
				if ($payment->email_currency and  $payment->email_currency != 0) {
					$html .= $this->getHtmlRowBE('SOFORT_PAYMENT_EMAIL_CURRENCY', shopFunctions::getCurrencyByID($payment->email_currency, 'currency_code_3'));
				}
				if ($payment->email_currency and  $payment->email_currency != 0) {
					$html .= $this->getHtmlRowBE('SOFORT_RESPONSE_TRANSACTION', $payment->sofort_response_transaction);
				}
				$first = FALSE;
			} else {
				foreach ($payment as $key => $value) {
					// only displays if there is a value or the value is different from 0.00 and the value
					if ($value) {
						if (substr($key, 0, strlen($code)) == $code) {
							$html .= $this->getHtmlRowBE($key, $value);
						}
					}
				}
			}
		}
		$html .= '</table>' . "\n";
		return $html;
	}


	/**
	 * @param $method
	 * @param $order
	 * @return string
	 */
	function _getPaymentResponseHtml ($method, $order, $payments) {
		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}

		vmLanguage::loadJLang('com_virtuemart_orders',TRUE);

		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total,$order['details']['BT']->order_currency);
		$cart = VirtueMartCart::getCart();
		$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);

		$payment = end($payments);

		$pluginName = $this->renderPluginName($method, $where = 'post_payment');
		$html = $this->renderByLayout('post_payment', array(
		                                                   'order' => $order,
		                                                   'paymentInfos' => $payment,
		                                                   'pluginName' => $pluginName,
		                                                   'displayTotalInPaymentCurrency' => $totalInPaymentCurrency['display']
		                                              ));
		//vmdebug('_getPaymentResponseHtml', $html,$pluginName,$paypalTable );

		return $html;
	}

	/*
		 * @param $method plugin
	 *  @param $where from where tis function is called
		 */

	protected function renderPluginName ($method, $where = 'checkout') {

		$display_logos = "";

		$logos = $method->payment_logos;
		if (!empty($logos)) {
			$display_logos = $this->displayLogos($logos) . ' ';
		}
		$payment_name = $method->payment_name;
		$html = $this->renderByLayout('render_pluginname', array(
		                                                        'where' => $where,
		                                                        'logo' => $display_logos,
		                                                        'payment_name' => $payment_name,
		                                                        'payment_description' => $method->payment_desc,
		                                                   ));

		return $html;
	}

	/**
	 * @param VirtueMartCart $cart
	 * @param                $method
	 * @param                $cart_prices
	 * @return int
	 */
/*	function getCosts (VirtueMartCart $cart, $method, $cart_prices) {

		if (preg_match('/%$/', $method->cost_percent_total)) {
			$cost_percent_total = substr($method->cost_percent_total, 0, -1);
		} else {
			$cost_percent_total = $method->cost_percent_total;
		}
		return ($method->cost_per_transaction + ($cart_prices['salesPrice'] * $cost_percent_total * 0.01));
	}*/

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
	protected function checkConditions ($cart, $method, $cart_prices) {

		$this->convert_condition_amount($method);
		$amount = $this->getCartAmount($cart_prices);
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
			OR
			($method->min_amount <= $amount AND ($method->max_amount == 0)));

		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array($method->countries)) {
				$countries[0] = $method->countries;
			} else {
				$countries = $method->countries;
			}
		}
		// probably did not gave his BT:ST address
		if (!is_array($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id'])) {
			$address['virtuemart_country_id'] = 0;
		}
		if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			if ($amount_cond) {
				return TRUE;
			}
		}

		return FALSE;
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
	function plgVmOnStoreInstallPaymentPluginTable ($jplugin_id) {

		return $this->onStoreInstallPluginTable($jplugin_id);
	}

	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment (VirtueMartCart $cart, &$msg) {

		return $this->OnSelectCheck($cart);
	}

	/**
	 * plgVmDisplayListFEPayment
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
	 *
	 * @param object  $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 */
	public function plgVmDisplayListFEPayment (VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		if ($this->getPluginMethods($cart->vendorId) === 0) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(vmText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
				return false;
			} else {
				return false;
			}
		}
		$htmla = array();
		$html = '';
		vmLanguage::loadJLang('com_virtuemart');
		$currency = CurrencyDisplay::getInstance();
		foreach ($this->methods as $this->_currentMethod) {
			if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {
				$cartPrices = $cart->cartPrices;
				$methodSalesPrice = $this->calculateSalesPrice($cart, $this->_currentMethod, $cartPrices);

				$logo = $this->displayLogos($this->_currentMethod->payment_logos);
				$payment_cost = '';
				if ($methodSalesPrice) {
					$payment_cost = $currency->priceDisplay($methodSalesPrice);
				}
				if ($selected == $this->_currentMethod->virtuemart_paymentmethod_id) {
					$checked = 'checked="checked"';
				} else {
					$checked = '';
				}
				$html .= $this->renderByLayout('display_payment', array(
				                                                       'plugin' => $this->_currentMethod,
				                                                       'checked' => $checked,
				                                                       'payment_logo' => $logo,
				                                                       'payment_cost' => $payment_cost,
				                                                  ));

				$htmla[] = $html;
			}
		}
		if (!empty($htmla)) {
			$htmlIn[] = $htmla;
		}

		return true;
	}

	/**
	 * displays the logos of a VirtueMart plugin
	 *
	 * @author Valerie Isaksen
	 * @param array $logo_list
	 * @return html with logos
	 */
	protected function getLogoLink () {

		$jlang = JFactory::getLanguage ();
		$lang = $jlang->getTag ();
		$langArray = explode ("-", $lang);
		$lang = strtolower ($langArray[1]);
		$listOfLangs=array('de','en','nl', 'pl', 'fr', 'it','es');
		$linkLang='en';
		if (in_array($lang,$listOfLangs)) {
			$linkLang=$lang;
		}
		$logoLink="https://images.sofort.com/".$linkLang."/su/landing.php";

		return $logoLink;
	}
	/*
		 * plgVmonSelectedCalculatePricePayment
		 * Calculate the price (value, tax_id) of the selected method
		 * It is called by the calculator
		 * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
		 * @author Valerie Isaksen
		 * @cart: VirtueMartCart the current cart
		 * @cart_prices: array the new cart prices
		 * @return null if the method was not selected, false if the payment is not valid any more, true otherwise
		 *
		 *
		 */

	/**
	 * @param VirtueMartCart $cart
	 * @param array          $cart_prices
	 * @param                $cart_prices_name
	 * @return bool|null
	 */
	public function plgVmOnSelectedCalculatePricePayment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

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
	function plgVmOnCheckAutomaticSelectedPayment (VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {

		return $this->onCheckAutomaticSelected($cart, $cart_prices, $paymentCounter);
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	public function plgVmOnShowOrderFEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {


		if (!($this->selectedThisByMethodId($virtuemart_paymentmethod_id))) {
			return NULL;
		}
		$payments = $this->getDatasByOrderId($virtuemart_order_id);
		$nb = count($payments);

		$payment_name = $this->renderByLayout('order_fe', array(
		                                                       'paymentInfos' => $payments[$nb - 1],
		                                                       'paymentName' => $payments[0]->payment_name,
		                                                  ));
	}

	/**
	 * This event is fired during the checkout process. It can be used to validate the
	 * method data as entered by the user.
	 *
	 * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.

	public function plgVmOnCheckoutCheckDataPayment($psType, VirtueMartCart $cart) {
	return null;
	}
	 */

	/**
	 * This method is fired when showing when printing an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $order_number The order number
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	function plgVmonShowOrderPrintPayment ($order_number, $method_id) {

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

	/**
	 * @param $name
	 * @param $id
	 * @param $table
	 * @return bool
	 */
	function plgVmSetOnTablePluginParamsPayment ($name, $id, &$table) {

		return $this->setOnTablePluginParams($name, $id, $table);
	}


	static function   getSuccessUrl ($order) {
		return JURI::root()."index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=" . $order['details']['BT']->virtuemart_paymentmethod_id . '&on=' . $order['details']['BT']->order_number . "&Itemid=" . vRequest::getInt('Itemid'). '&lang='.vRequest::getCmd('lang',''); ;
	}

	static function   getCancelUrl ($order) {
		return  JURI::root()."index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&pm=" . $order['details']['BT']->virtuemart_paymentmethod_id . '&on=' . $order['details']['BT']->order_number . '&Itemid=' . vRequest::getInt('Itemid').'&lang='.vRequest::getCmd('lang','');
	}

	static function   getNotificationUrl ($security, $order) {

		return JURI::root()  .  "index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component&pm=" . $order['details']['BT']->virtuemart_paymentmethod_id . '&on=' . $order['details']['BT']->order_number . "&security=" . $security .'&lang='.vRequest::getCmd('lang','');
	}

	static function getSecurityKey () {
		if (!class_exists('SofortLib_SofortueberweisungClassic')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib_sofortueberweisung_classic.php');
		}
		return SofortLib_SofortueberweisungClassic::generatePassword();
	}

	function sofortLog($sofortLib){
		if ($this->_currentMethod->sofort_log) {
			$sofortLib->setLogEnabled();
			if (!class_exists( 'VmConfig' )) require(JPATH_ROOT .'/administrator/components/com_virtuemart/helpers/config.php');
			$logFileName=$this->getLogFileName();
			$path = JFactory::getConfig()->get('log_path', VMPATH_ROOT . "/log" ).'/'.$logFileName.'.log.php';
			if (!JFile::exists($path)) {
				// blank line to prevent information disclose: https://bugs.php.net/bug.php?id=60677
				// from Joomla log file
				$head = "#\n";
				$head .= '#<?php die("Forbidden."); ?>'."\n";
				$fp = fopen ($logFileName, 'a');
				if ($fp) {
					if ($head) {
						fwrite ($fp,  $head);
					}
			}
			}
			$sofortLib->setLogfilePath($path) ;
		}
	}

}

// No closing tag
