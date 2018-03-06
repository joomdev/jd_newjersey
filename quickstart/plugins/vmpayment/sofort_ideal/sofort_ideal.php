<?php
defined('_JEXEC') or die('Restricted access');

/**
 * @author Valérie Isaksen
 * @version $Id: sofort_ideal.php 9560 2017-05-30 14:13:21Z Milbo $
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

class plgVmPaymentSofort_Ideal extends vmPSPlugin {
	const RELEASE = 'VM 3.2.6';
	const PAYMENT_CURRENCY_CODE_3 = 'EUR';

	function __construct(& $subject, $config) {

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
	public function getVmPluginCreateTableSQL() {

		return $this->createTableSQL('Payment Sofort Ideal Table');
	}

	/**
	 * @return array
	 */
	function getTableSQLFields() {

		$SQLfields = array(
		'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
		'virtuemart_order_id' => 'int(1) UNSIGNED',
		'order_number' => 'char(64)',
		'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
		'payment_name' => 'varchar(1000)',
		'payment_order_total' => 'decimal(15,5) NOT NULL',
		'payment_currency' => 'smallint(1)',
		'cost_per_transaction' => 'decimal(10,2)',
		'cost_percent_total' => 'decimal(10,2)',
		'tax_id' => 'smallint(1)',
		'sofort_custom' => 'varchar(255)',
		'sofort_ideal_hidden_response_user_id' => 'int(1) UNSIGNED',
		'sofort_ideal_hidden_response_project_id' => 'int(1) UNSIGNED',
		'sofort_ideal_response_transaction' => 'varchar(27)',
		'sofort_ideal_response_sender_holder' => 'varchar(255)',
		'sofort_ideal_response_sender_account_number' => 'varchar(30)',
		'sofort_ideal_response_sender_bank_name' => 'varchar(255)',
		'sofort_ideal_response_sender_bank_bic' => 'varchar(50)',
		'sofort_ideal_response_sender_iban' => 'varchar(50)',
		'sofort_ideal_response_sender_country_id' => 'varchar(2)',
		'sofort_ideal_hidden_response_recipient_holder' => 'varchar(255)',
		'sofort_ideal_hidden_response_recipient_account_number' => 'varchar(30)',
		'sofort_ideal_hidden_response_recipient_bank_code' => 'varchar(30)',
		'sofort_ideal_hidden_response_recipient_bank_name' => 'varchar(255)',
		'sofort_ideal_hidden_response_recipient_bank_bic' => 'varchar(50)',
		'sofort_ideal_hidden_response_recipient_iban' => 'varchar(50)',
		'sofort_ideal_hidden_response_recipient_country_id' => 'varchar(2)',
		'sofort_ideal_response_amount' => 'decimal(15,5) NOT NULL',
		'sofort_ideal_response_currency_id' => 'varchar(3)',
		'sofort_ideal_hidden_response_reason_1' => 'char(255)',
		'sofort_ideal_response_created' => 'varchar(30)',
		'sofort_ideal_response_status' => 'varchar(20)',
			// this parameter is not documented, but it is returned, and important for order status update
		'sofort_ideal_response_status_reason' => 'varchar(20)',
		'sofort_ideal_response_status_modified' => 'varchar(20)',
		'sofort_ideal_hidden_response_hash' => 'varchar(20)', // hash is stored, but we do not need to display it
			// even though this parameter is in the doc
			//'sofort_ideal_response_amount_refunded' => 'decimal(15,5) NOT NULL',
		'sofort_ideal_hidden_response_amount_refunded_integer' => 'int(1) ',
		);
		return $SQLfields;
	}


	/**
	 * This shows the plugin for choosing in the payment list of the checkout process.
	 *
	 * @author Valerie Cartan Isaksen
	 */
	function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

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
		foreach ($this->methods as $method) {

			if ($this->checkConditions($cart, $method, $cart->cartPrices)) {

				$methodSalesPrice = $this->calculateSalesPrice($cart, $method, $cart->cartPrices);

				//$method->payment_name = $method->payment_name
				if (!class_exists('SofortLib')) {
					require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib.php');
				}
				if (!class_exists('SofortLib_iDealClassic')) {
					require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib_ideal_classic.php');
				}

				$selected_bank = self::_getSelectedBankCode($method->virtuemart_paymentmethod_id);
				if (empty($method->configuration_key) or empty($method->project_password)) {
					vmError('Missing essentials infos for this published payment. Check the configuration  key and the password:' . $method->payment_name . ' (' . $method->virtuemart_paymentmethod_id . ')');
					continue;
				}
				$sofort_ideal = new SofortLib_iDealClassic(trim($method->configuration_key), trim($method->project_password));
				$relatedBanks = $sofort_ideal->getRelatedBanks();
				if (empty($relatedBanks)) {
					vmError('getRelatedBanks: error, returned NULL' . $method->virtuemart_paymentmethod_id . '.');
					continue;
				}
				$relatedBanksDropDown = $this->getRelatedBanksDropDown($relatedBanks, $method->virtuemart_paymentmethod_id, $selected_bank);
				$logo = $this->displayLogos($method->payment_logos);
				$payment_cost = '';
				if ($methodSalesPrice) {
					$payment_cost = $currency->priceDisplay($methodSalesPrice);
				}
				if ($selected == $method->virtuemart_paymentmethod_id) {
					$checked = 'checked="checked"';
				} else {
					$checked = '';
				}
				$html = $this->renderByLayout('display_payment', array(
				'plugin' => $method,
				'checked' => $checked,
				'payment_logo' => $logo,
				'payment_cost' => $payment_cost,
				'relatedBanks' => $relatedBanksDropDown
				));

				$htmla[] = $html;
			}
		}
		if (!empty($htmla)) {
			$htmlIn[] = $htmla;
		}

		return true;
	}



	private function getRelatedBanksDropDown($relatedBanks, $paymentmethod_id, $selected_bank) {
		//vmdebug('getRelatedBanks', $relatedBanks);

		$attrs = '';
		$idA = $id = 'sofort_ideal_bank_selected_' . $paymentmethod_id;
		$options[] = array('value' => '', 'text' => vmText::_('VMPAYMENT_SOFORT_IDEAL_PLEASE_SELECT_BANK'));

		foreach ($relatedBanks as $key => $relatedBank) {
			$code = array('code' => $relatedBank['code'], 'name' => $relatedBank['name']);
			//$options[] = JHTML::_('select.option', json_encode($code), $relatedBank['name']);
			$options[] = JHTML::_('select.option', $relatedBank['code'], $relatedBank['name']);
		}

		return JHTML::_('select.genericlist', $options, $idA, $attrs, 'value', 'text', $selected_bank);
	}


	/**
	 * This is for checking the input data of the payment method within the checkout
	 *
	 * @author Valerie Cartan Isaksen
	 */
	function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		$payment_params = self::_getSofortIdealFromSession();
		$error_msg = "";
		// STEP 1. Check the validity of the data
		if (!$this->_validate_sofortideal_data($payment_params, $cart->virtuemart_paymentmethod_id, $error_msg)) {
			return false;
		}


		self::_setSofortIdealIntoSession($payment_params);
		return true;
	}


	/**
	 * @param $cart
	 * @param $order
	 * @return bool','null
	 */
	function plgVmConfirmedOrder($cart, $order) {

		if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return FALSE;
		}
		$this->setInConfirmOrder($cart);
		$session = JFactory::getSession();
		$return_context = $session->getId();

		$this->_debug = $method->debug;
		$this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');
		vmdebug('SOFORT plgVmConfirmedOrder');
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}

		if (!class_exists('TableVendors')) {
			require(VMPATH_ADMIN . DS . 'tables' . DS . 'vendors.php');
		}

		$currency_code_3 = self::PAYMENT_CURRENCY_CODE_3; //
		$currency_id = shopFunctions::getCurrencyIDByName($currency_code_3);
		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $currency_id);
		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);

		$address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);

		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);
		if ($totalInPaymentCurrency <= 0) {
			vmInfo(vmText::sprintf('VMPAYMENT_SOFORT_AMOUNT_INCORRECT', $order['details']['BT']->order_total, $totalInPaymentCurrency['value'], $currency_code_3));
			return FALSE;
		}
		// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName($method, 'order');
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
		$dbValues['cost_percent_total'] = $method->cost_percent_total;
		$dbValues['payment_currency'] = $currency_id;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency['value'];
		$dbValues['tax_id'] = $method->tax_id;
		$dbValues['sofort_custom'] = $return_context;
		$this->storePSPluginInternalData($dbValues);


		if (!class_exists('SofortLib')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib.php');
		}
		if (!class_exists('SofortLib_iDealClassic')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib_ideal_classic.php');
		}


		$sofort_ideal = new SofortLib_iDealClassic($method->configuration_key, $method->project_password);
		$sofort_ideal->setVersion(self::RELEASE);
		$sofort_ideal->setAmount($totalInPaymentCurrency['value'], $currency_code_3);
		$sofort_ideal->setSenderCountryId(ShopFunctions::getCountryByID($address->virtuemart_country_id, 'country_2_code'));
		$sofort_ideal->setReason($order['details']['BT']->order_number);
		$sofort_ideal->addUserVariable($order['details']['BT']->virtuemart_paymentmethod_id);
		//$sofort_ideal->setSuccessUrl(self::getSuccessUrl($order)); //user_variable_3
		//$sofort_ideal->setAbortUrl(self::getCancelUrl($order)); //user_variable_4
		//$sofort_ideal->setNotificationUrl(self::getNotificationUrl( $order['details']['BT']->order_number)); //user_variable_5
		$sofort_ideal->setSenderCountryId(ShopFunctions::getCountryByID($address->virtuemart_country_id, 'country_2_code')); //sender_country_id
		$sofort_ideal->setSenderBankCode(self::_getSelectedBankCode($order['details']['BT']->virtuemart_paymentmethod_id));

		$url = $sofort_ideal->getPaymentUrl();

		//$this->storePSPluginInternalData($dbValues);
		$mainframe = JFactory::getApplication();
		$mainframe->redirect($url);


	}


	function displayErrors($errors) {

		foreach ($errors as $error) {
			// TODO
			vmInfo(vmText::sprintf('VMPAYMENT_SOFORT_ERROR_FROM', $error ['message'], $error ['field'], $error ['code']));
			if ($error ['message'] == 401) {
				vmdebug('check you payment parameters: custom_id, project_id, api key');
			}
		}
	}

	private function _getSelectedBankCode($paymentmethod_id) {

		return self::_getSelectedBank($paymentmethod_id);
		/*$selected_bank = self::_getSelectedBank($paymentmethod_id);
		$selected_bank_decoded = json_decode($selected_bank);
		return $selected_bank_decoded->code;*/
	}

	private function _getSelectedBank($paymentmethod_id) {
		$payment_params = self::_getSofortIdealFromSession();

		$v = 'sofort_ideal_bank_selected_' . $paymentmethod_id;
		if (empty($payment_params->$v)) {
			return NULL;
		}
		return $payment_params->$v;
	}
	
	/**
	 * @param $html : Clients information
	 * @return bool','null','string
	 */
	function plgVmOnPaymentResponseReceived(&$html) {
		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);
		$order_number = vRequest::getString('on', 0);

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return NULL;
		}
		if (!($paymentTables = $this->getDatasByOrderId($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		vmLanguage::loadJLang('com_virtuemart');
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$paymentCurrency = CurrencyDisplay::getInstance($order['details']['BT']->order_currency);
		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $method->payment_currency);

		$cart = VirtueMartCart::getCart();
		$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);

		$nb = count($paymentTables);
		$pluginName = $this->renderPluginName($method, 'post_payment');
		$html = $this->renderByLayout('post_payment', array(
		'order' => $order,
		'paymentInfos' => $paymentTables[$nb - 1],
		'pluginName' => $pluginName,
		'displayTotalInPaymentCurrency' => $totalInPaymentCurrency['display']
		));
		vmdebug('_getPaymentResponseHtml', $paymentTables);

		$this->emptyCart();
		return $html;

	}

	/**
	 * @return bool|null
	 */
	function plgVmOnUserPaymentCancel() {

		$order_number = vRequest::getString('on', '');
		// cancel / abort link must be insterted in the SOFORT BE
		// must be http://mysite.com/index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=-REASON1-
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', '');
		if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL;
		}
		$error_codes = vRequest::getString('error_codes', '');
		if (!empty($error_codes)) {
			$errors = explode(",", $error_codes);
			foreach ($errors as $error) {
				// TODO
				$lang = JFactory::getLanguage();
				$lang_key = 'VMPAYMENT_SOFORT_IDEAL_ERROR_CODES_' . $error;
				if ($lang->hasKey($lang_key)) {
					vmInfo(vmText::_($lang_key));
				} else {
					vmInfo(vmText::sprintf('VMPAYMENT_SOFORT_IDEAL_ERROR_CODES_UNKNOWN_CODE', $error));
				}
			}
			//return false;
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
		if (!($method = $this->getVmPluginMethod($paymentTable->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			//vmdebug('IDEAL plgVmOnPaymentResponseReceived NOT selectedThisElement'  );
			return NULL;
		}
		vmdebug(__CLASS__ . '::' . __FUNCTION__, 'VMPAYMENT_SOFORT_PAYMENT_CANCELLED', $error_codes);
		if (empty($error_codes)) {
			VmInfo(vmText::_('VMPAYMENT_SOFORT_PAYMENT_CANCELLED'));
			$comment = '';
		} else {
			$comment = vmText::_($lang_key);
		}
		$session = JFactory::getSession();
		$return_context = $session->getId();
		vmDebug('handlePaymentUserCancel', $virtuemart_order_id, $paymentTable->sofort_custom, $return_context);
		if (strcmp($paymentTable->sofort_custom, $return_context) === 0) {
			vmDebug('handlePaymentUserCancel', $virtuemart_order_id);
			$this->handlePaymentUserCancel($virtuemart_order_id, $method->status_canceled, $comment);
		} else {
			vmDebug('Return context', $paymentTable->sofort_custom, $return_context);
		}
		return TRUE;
	}

	/*
	*  plgVmOnPaymentNotification() - This event is fired by Offline Payment. It can be used to validate the payment data as entered by the user.
		 *  Return:
		 *  Parameters:
		 *  None
		 *  @author Valerie Isaksen
		 */

	/**
	 * @return bool','null
	 */
	function plgVmOnPaymentNotification() {

		/*
							$this->_debug = true;

							 $this->logInfo('plgVmOnPaymentNotification '.var_export($_POST, true) , 'message')	;
							 $this->logInfo('plgVmOnPaymentNotification  '.var_export($_REQUEST, true) , 'message');
							// $paymentmethod_id = vRequest::getString('reason_2');
		*/

		$order_number = vRequest::getString('reason_1'); // is order number

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (empty($order_number)) {
			return FALSE;
		}
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return FALSE;
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			return FALSE;
		}

		$method = $this->getVmPluginMethod($payments[0]->virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}

		$hash_keys = array(
		'transaction',
		'user_id',
		'project_id',
		'sender_holder',
		'sender_account_number',
		'sender_bank_name',
		'sender_bank_bic',
		'sender_iban',
		'sender_country_id',
		'recipient_holder',
		'recipient_account_number',
		'recipient_bank_code',
		'recipient_bank_name',
		'recipient_bank_bic',
		'recipient_iban',
		'recipient_country_id',
		'amount',
		'currency_id',
		'reason_1',
		'reason_2',
		'user_variable_0',
		'user_variable_1',
		'user_variable_2',
		'user_variable_3',
		'user_variable_4',
		'user_variable_5',
		'created',
		'status',
		'status_modified',
		'notification_password'
		);

		foreach ($hash_keys as $key) {
			$hash_data[$key] = vRequest::getString($key, '');
		}
		$hash_data['notification_password'] = $method->notification_password;


		if (!$this->checkHash($hash_data)) {
			return false;
		}

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}


		$db = JFactory::getDBO();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery($query);
		$columns = $db->loadColumn(0);
		$prefix = 'sofort_ideal_response_';
		$prefix_hidden = 'sofort_ideal_hidden_response_';
		$prefix_len = strlen($prefix);
		$prefix_hidden_len = strlen($prefix_hidden);
		foreach ($columns as $key) {
			if (substr($key, 0, $prefix_len) == $prefix) {
				$postKey = substr($key, $prefix_len);
				$dbvalues[$key] = vRequest::getString($postKey, '');
			} elseif (substr($key, 0, $prefix_hidden_len) == $prefix_hidden) {
				$postKey = substr($key, $prefix_hidden_len);
				$dbvalues[$key] = vRequest::getString($postKey, '');
			}
		}
		$dbvalues['hidden_hash'] = vRequest::getString('hash', '');;
		$dbvalues['virtuemart_paymentmethod_id'] = $payments[0]->virtuemart_paymentmethod_id;
		$dbvalues['virtuemart_order_id'] = $virtuemart_order_id;
		$dbvalues['order_number'] = $order_number;

		$modelOrder = VmModel::getModel('orders');
		$order = array();
		$this->logInfo('before getNewOrderStatus   ' . var_export($dbvalues, true), 'message');
		$status = $this->getNewOrderStatus($dbvalues);

		$order['order_status'] = $method->$status;
		$order['comments'] = vmText::_('VMPAYMENT_SOFORT_IDEAL_RESPONSE_' . $status);
		$order['customer_notified'] = 1;

		//$this->logInfo('before storePSPluginInternalData   ' , 'message');
		$this->storePSPluginInternalData($dbvalues);
		$this->logInfo('after storePSPluginInternalData   ' . var_export($dbvalues, true), 'message');

		$this->logInfo('plgVmOnPaymentNotification return new_status:' . $order['order_status'], 'message');

		$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order, false);
		//// remove vmcart
		if (isset($payments[0]->sofort_custom)) {
			$this->emptyCart($payments[0]->sofort_custom, $order_number);
		}
	}

	private function checkHash($data) {

		$hash_received = vRequest::getString('hash');
		$data_implode = implode('|', $data);
		$hash_calculated = sha1($data_implode);

		if ($hash_calculated != $hash_received) {
			$this->logInfo('SOFORT IDEAL plgVmOnPaymentNotification Incorrect HASH calculated value:' . $hash_calculated . ' received value:' . $hash_received, 'message');
			$this->logInfo(' data:' . var_export($data, true), 'message');
			$emailBody = "Hello,\n\nerror while receiving a SOFORT IDEAL NOTIFICATION" . "\n";
			$emailBody .= "Incorrect HASH calculated value " . $hash_calculated . " received value" . $hash_received . "\n";
			//$emailBody .= "Calculated with " . var_export($data, true)  ."\n";
			$this->sendEmailToVendorAndAdmins(vmText::_('VMPAYMENT_SOFORT_ERROR_NOTIFICATION'), $emailBody);
			return false;
		}
		return true;
	}

	/*
	 * Not documented functionality
	 *
	 */
	private function getNewOrderStatus($dbvalues) {
		$newOrderStatus = array(
		'pending' => array('not_credited_yet' => 'status_pending'),
		'received' => array('credited' => 'status_confirmed'),
		'loss' => array('not_credited' => 'status_canceled'),
		'refunded' => array('refunded' => 'status_refunded', 'compensation' => 'status_compensation'),
			// Special case is the following status that can occur (only with iDEAL payments),
			//if after a timeout in our system the payment is marked as loss and then iDEAL reports (too late) a successful iDEAL payment.
			// Then our SOFORT backend starts an automatic refund which is reported to the shopsystem as follows:
		'late_succeed' => array('automatic_refund_to_customer' => 'status_refunded'),
		);
		$this->logInfo('IN getNewOrderStatus   ' . $dbvalues['sofort_ideal_response_status'] . ":" . $dbvalues['sofort_ideal_response_status_reason'], 'message');

		if (!(array_key_exists($dbvalues['sofort_ideal_response_status'], $newOrderStatus) AND
		array_key_exists($dbvalues['sofort_ideal_response_status_reason'], $newOrderStatus[$dbvalues['sofort_ideal_response_status']]))
		) {
			// received an unknown combination.
			//
			$this->logInfo('IN 1 getNewOrderStatus   array_key_exists PROBLEM', 'message');

			$this->sendEmailToVendorAndAdmins(vmText::_('VMPAYMENT_SOFORT_ERROR_ORDER_STATUS_SUB'), vmText::sprintf('VMPAYMENT_SOFORT_ERROR_ORDER_STATUS_BODY', $dbvalues['sofort_ideal_response_status'], $dbvalues['sofort_ideal_response_status_reason'], $dbvalues['order_number']));
			$this->logInfo('IN 1 sendEmailToVendorAndAdmins   ' . $dbvalues['sofort_ideal_response_status'] . '/' . $dbvalues['sofort_ideal_response_status_reason'], 'message');
			$this->logInfo('  ' . array_key_exists($dbvalues['sofort_ideal_response_status'], $newOrderStatus) . '/' . array_key_exists($dbvalues['sofort_ideal_response_status_reason'], $newOrderStatus[$dbvalues['sofort_ideal_response_status']]), 'message');

			return 'pending';
		}
		$this->logInfo('IN xx getNewOrderStatus   ' . $newOrderStatus[$dbvalues['sofort_ideal_response_status']][$dbvalues['sofort_ideal_response_status_reason']], 'message');

		return $newOrderStatus[$dbvalues['sofort_ideal_response_status']][$dbvalues['sofort_ideal_response_status_reason']];


	}


	/**
	 * Display stored payment data for an order
	 * @param  int $virtuemart_order_id
	 * @param  int $payment_method_id
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId($payment_method_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$html = '<table class="adminlist table" >' . "\n";
		$html .= $this->getHtmlHeaderBE();
		$code = "sofort_ideal_response_";
		$first = TRUE;
		foreach ($payments as $payment) {
			$html .= '<tr class="row1"><td>' . vmText::_('COM_VIRTUEMART_DATE') . '</td><td align="left">' . $payment->created_on . '</td></tr>';
			// Now only the first entry has this data when creating the order
			if ($first) {
				$html .= $this->getHtmlRowBE('COM_VIRTUEMART_PAYMENT_NAME', $payment->payment_name);
				if ($payment->payment_order_total and $payment->payment_order_total != 0.00) {
					$html .= $this->getHtmlRowBE('SOFORT_PAYMENT_ORDER_TOTAL', $payment->payment_order_total . " " . shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));
				}
				$html .= $this->getHtmlRowBE('SOFORT_PAYMENT_EMAIL_CURRENCY', shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));

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
	function _getPaymentResponseHtml($method, $order) {
		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}

		$cart = VirtueMartCart::getCart();

		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $method->payment_currency);
		$cart = VirtueMartCart::getCart();
		$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);


		$pluginName = $this->renderPluginName($method, 'post_payment');
		$html = $this->renderByLayout('post_payment', array(
		'order' => $order,
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

	protected function renderPluginName($method, $where = 'checkout') {

		$display_logos = "";

		$logos = $method->payment_logos;
		if (!empty($logos)) {
			$display_logos = $this->displayLogos($logos) . ' ';
		}
		$payment_name = $method->payment_name;
		if (!class_exists('SofortLib')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib.php');
		}
		if (!class_exists('SofortLib_iDealClassic')) {
			require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'sofort' . DS . 'sofort' . DS . 'library' . DS . 'sofortLib_ideal_classic.php');
		}
		$sofort_ideal = new SofortLib_iDealClassic(trim($method->configuration_key), trim($method->project_password));
		$relatedBanks = $sofort_ideal->getRelatedBanks();
		if (empty($relatedBanks)) {
			vmError('getRelatedBanks: error, returned NULL' . $method->virtuemart_paymentmethod_id . '.');
		}

		$bankCode = self::_getSelectedBankCode($method->virtuemart_paymentmethod_id);
		$bank_name = '';
		if($bankCode){
			foreach ($relatedBanks as $key => $relatedBank) {
				if($relatedBank['code'] == $bankCode){
					$bank_name = $relatedBank['name'];
					break;
				}
			}
		}

		$html = $this->renderByLayout('render_pluginname', array(
		'where' => $where,
		'logo' => $display_logos,
		'payment_name' => $payment_name,
		'bank_name' => $bank_name,
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
	 * @param $cart_prices : cart prices
	 * @param $payment
	 * @return true: if the conditions are fulfilled, false otherwise
	 *
	 */
	protected function checkConditions($cart, $method, $cart_prices) {

		$this->convert_condition_amount($method);
		$amount = $this->getCartAmount($cart_prices);
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
		OR
		($method->min_amount <= $amount AND ($method->max_amount == 0)));


		$countries[0] = ShopFunctions::getCountryIDByName('NL');
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
	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

		return $this->onStoreInstallPluginTable($jplugin_id);
	}

	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart : the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		// 1. Step 1: check the data
		$payment_params['sofort_ideal_bank_selected_' . $cart->virtuemart_paymentmethod_id] = vRequest::getVar('sofort_ideal_bank_selected_' . $cart->virtuemart_paymentmethod_id);

		if (empty($payment_params['sofort_ideal_bank_selected_' . $cart->virtuemart_paymentmethod_id])) {
			vmInfo('VMPAYMENT_SOFORT_IDEAL_PLEASE_SELECT_BANK');
			return false;
		}
		// STEP 3. Save in session
		self::_setSofortIdealIntoSession($payment_params);

		return true;
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
	 * @param array $cart_prices
	 * @param                $cart_prices_name
	 * @return bool','null
	 */

	public function plgVmOnSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

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
	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {

		$virtuemart_pluginmethod_id = 0;
		$nbMethod = $this->getSelectable($cart, $virtuemart_pluginmethod_id, $cart_prices);

		if ($nbMethod == NULL) {
			return NULL;
		} else {
			return 0;
		}
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 * @author Valerie Isaksen
	 */
	public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {


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
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id method used for this order
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
	 *
	 * public function plgVmOnUpdateOrderPayment(  $_formData) {
	 * return null;
	 * }
	 */
	/**
	 * Save updated orderline data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not actived.
	 *
	 * public function plgVmOnUpdateOrderLine(  $_formData) {
	 * return null;
	 * }
	 */
	/**
	 * plgVmOnEditOrderLineBE
	 * This method is fired when editing the order line details in the backend.
	 * It can be used to add line specific package codes
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise
	 *
	 * public function plgVmOnEditOrderLineBE(  $_orderId, $_lineId) {
	 * return null;
	 * }
	 */

	/**
	 * This method is fired when showing the order details in the frontend, for every orderline.
	 * It can be used to display line specific package codes, e.g. with a link to external tracking and
	 * tracing systems
	 *
	 * @param integer $_orderId The order ID
	 * @param integer $_lineId
	 * @return mixed Null for method that aren't active, text (HTML) otherwise
	 *
	 * public function plgVmOnShowOrderLineFE(  $_orderId, $_lineId) {
	 * return null;
	 * }
	 */
	function plgVmDeclarePluginParamsPaymentVM3(&$data) {
		return $this->declarePluginParams('payment', $data);
	}

	/**
	 * @param $name
	 * @param $id
	 * @param $table
	 * @return bool
	 */
	function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {

		return $this->setOnTablePluginParams($name, $id, $table);
	}

	private function _validate_sofortideal_data($payment_params, $paymentmethod_id, &$error_msg) {

		$errors = array();
		$v = 'sofort_ideal_bank_selected_' . $paymentmethod_id;
		if (empty($payment_params->$v)) {
			$errors[] = vmText::_('VMPAYMENT_SOFORT_IDEAL_PLEASE_SELECT_BANK');
		}

		if (!empty($errors)) {
			$error_msg .= "</br />";
			foreach ($errors as $error) {
				$error_msg .= " -" . $error . "</br />";
			}
			return FALSE;
		}
		return TRUE;
	}


	private static function _clearSofortIdealSession() {

		$session = JFactory::getSession();
		$session->clear('SofortIdeal', 'vm');
	}

	private static function _setSofortIdealIntoSession($data) {

		$session = JFactory::getSession();
		$session->set('SofortIdeal', json_encode($data), 'vm');
	}

	private static function _getSofortIdealFromSession() {

		$session = JFactory::getSession();
		$data = $session->get('SofortIdeal', 0, 'vm');
		if (empty($data)) {
			//return self::getEmptyPaymentParams ();
			return NULL;
		}
		return json_decode($data);
	}

	private static function   getSuccessUrl($order) {
		return JURI::base() . JROUTE::_("index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=" . $order['details']['BT']->virtuemart_paymentmethod_id . '&on=' . $order['details']['BT']->order_number . "&Itemid=" . vRequest::getInt('Itemid'), false);
	}

	private static function   getCancelUrl($order) {
		return JURI::base() . JROUTE::_("index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&pm=" . $order['details']['BT']->virtuemart_paymentmethod_id . '&on=' . $order['details']['BT']->order_number . '&Itemid=' . vRequest::getInt('Itemid'), false);
	}

	private static function   getNotificationUrl($order_number) {

		return JURI::base() . JROUTE::_("index.php?option=com_virtuemart&view=pluginresponse&tmpl=component&task=pluginnotification&on=" . $order_number, false);
	}


}

// No closing tag
