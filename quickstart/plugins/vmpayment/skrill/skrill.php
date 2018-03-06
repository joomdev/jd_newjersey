<?php

/*
* @author Skrill Holdings Ltd.
* @version $Id: SKRILL.php 7487 2013-12-17 15:03:42Z alatak $
* @package VirtueMart
* @subpackage payment
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
*
* http://virtuemart.org
*/

defined ('_JEXEC') or die('Restricted access');
if (!class_exists ('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

class plgVmpaymentSkrill extends vmPSPlugin {

	function __construct (& $subject, $config) {

		parent::__construct ($subject, $config);
		// unique filelanguage for all SKRILL methods
		$jlang = JFactory::getLanguage ();
		$jlang->load ('plg_vmpayment_skrill', JPATH_ADMINISTRATOR, NULL, TRUE);
		$this->_loggable = TRUE;
		$this->_debug = TRUE;
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$this->_tablepkey = 'id'; //virtuemart_SKRILL_id';
		$this->_tableId = 'id'; //'virtuemart_SKRILL_id';

		$varsToPush = array('pay_to_email'        => array('', 'char'),
		                    'product'          => array('', 'char'),
		                    'hide_login'          => array(0, 'int'),
		                    'logourl'             => array('', 'char'),
		                    'secret_word'         => array('', 'char'),
		                    'payment_currency'    => array('', 'char'),
		                    'payment_logos'       => array('', 'char'),
		                    'countries'           => array('', 'char'),
		                    'cost_per_transaction'
		                                          => array('', 'int'),
		                    'cost_percent_total'
		                                          => array('', 'int'),
		                    'min_amount'          => array('', 'int'),
		                    'max_amount'          => array('', 'int'),
		                    'tax_id'              => array(0, 'int'),
		                    'status_pending'      => array('', 'char'),
		                    'status_success'      => array('', 'char'),
		                    'status_canceled'     => array('', 'char'));

		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);
	}

	public function getVmPluginCreateTableSQL () {

		return $this->createTableSQL ('Payment SKRILL Table');
	}

	function _getSKRILLURL ($method) {

		$url = 'www.skrill.com';

		return $url;
	}

	function _processStatus (&$mb_data, $vmorder, $method) {

		switch ($mb_data['status']) {
			case 2 :
				$mb_data['payment_status'] = 'Completed';
				break;
			case 0 :
				$mb_data['payment_status'] = 'Pending';
				break;
			case -1 :
				$mb_data['payment_status'] = 'Cancelled';
				break;
			case -2 :
				$mb_data['payment_status'] = 'Failed';
				break;
			case -3 :
				$mb_data['payment_status'] = 'Chargeback';
				break;
		}

		$md5data = $mb_data['merchant_id'] . $mb_data['transaction_id'] .
			strtoupper (md5 (trim($method->secret_word))) . $mb_data['mb_amount'] . $mb_data['mb_currency'] .
			$mb_data['status'];

		$calcmd5 = md5 ($md5data);
		if (strcmp (strtoupper ($calcmd5), $mb_data['md5sig'])) {
			return "MD5 checksum doesn't match - calculated: $calcmd5, expected: " . $mb_data['md5sig'];
		}

		return FALSE;
	}

	function _getPaymentResponseHtml ($paymentTable, $payment_name) {
		vmLanguage::loadJLang('com_virtuemart');

		$html = '<table>' . "\n";
		$html .= $this->getHtmlRow ('COM_VIRTUEMART_PAYMENT_NAME', $payment_name);
		if (!empty($paymentTable)) {
			$html .= $this->getHtmlRow ('SKRILL_ORDER_NUMBER', $paymentTable->order_number);
		}
		$html .= '</table>' . "\n";

		return $html;
	}

	function _getInternalData ($virtuemart_order_id, $order_number = '') {

		$db = JFactory::getDBO ();
		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		if ($order_number) {
			$q .= " `order_number` = '" . $order_number . "'";
		} else {
			$q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;
		}

		$db->setQuery ($q);
		if (!($paymentTable = $db->loadObject ())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		return $paymentTable;
	}

	function _storeInternalData ($method, $mb_data, $virtuemart_order_id) {

		// get all know columns of the table
		$db = JFactory::getDBO ();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery ($query);
		$columns = $db->loadColumn (0);

		$post_msg = '';
		foreach ($mb_data as $key => $value) {
			$post_msg .= $key . "=" . $value . "<br />";
			$table_key = 'mb_' . $key;
			if (in_array ($table_key, $columns)) {
				$response_fields[$table_key] = $value;
			}
		}

		$response_fields['payment_name'] = $this->renderPluginName ($method);
		$response_fields['mbresponse_raw'] = $post_msg;
		$response_fields['order_number'] = $mb_data['transaction_id'];
		$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
		$this->storePSPluginInternalData ($response_fields, 'virtuemart_order_id', TRUE);
	}

	function _parse_response ($response) {

		$matches = array();
		$rlines = explode ("\r\n", $response);

		foreach ($rlines as $line) {
			if (preg_match ('/([^:]+): (.*)/im', $line, $matches)) {
				continue;
			}

			if (preg_match ('/([0-9a-f]{32})/im', $line, $matches)) {
				return $matches;
			}
		}

		return $matches;
	}

	function getTableSQLFields () {

		$SQLfields = array('id'                     => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
		                   'virtuemart_order_id'    => 'int(1) UNSIGNED',
		                   'order_number'           => ' char(64)',
		                   'virtuemart_paymentmethod_id'
		                                             => 'mediumint(1) UNSIGNED',
		                   'payment_name'            => 'varchar(5000)',
		                   'payment_order_total'     => 'decimal(15,5) NOT NULL DEFAULT \'0.00000\'',
		                   'payment_currency'        => 'char(3) ',
		                   'cost_per_transaction'    => 'decimal(10,2)',
		                   'cost_percent_total'      => 'decimal(10,2)',
		                   'tax_id'                  => 'smallint(1)',

		                   'user_session'            => 'varchar(255)',

			// status report data returned by SKRILL to the merchant
		                   'mb_pay_to_email'         => 'varchar(50)',
		                   'mb_pay_from_email'       => 'varchar(50)',
		                   'mb_merchant_id'          => 'int(10) UNSIGNED',
		                   'mb_transaction_id'       => 'varchar(15)',
		                   'mb_rec_payment_id'       => 'int(10) UNSIGNED',
		                   'mb_rec_payment_type'     => 'varchar(16)',
		                   'mb_amount'               => 'decimal(19,2)',
		                   'mb_currency'             => 'char(3)',
		                   'mb_status'               => 'tinyint(1)',
		                   'mb_md5sig'               => 'char(32)',
		                   'mb_sha2sig'              => 'char(64)',
		                   'mbresponse_raw'          => 'varchar(512)');

		return $SQLfields;
	}

	function plgVmConfirmedOrder ($cart, $order) {

		if (!($method = $this->getVmPluginMethod ($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL;
		} // Another method was selected, do nothing

		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}

		$session = JFactory::getSession ();
		$return_context = $session->getId ();
		$this->logInfo ('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');

		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists ('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}

		$usrBT = $order['details']['BT'];
		$address = ((isset($order['details']['ST'])) ? $order['details']['ST'] : $order['details']['BT']);

		if (!class_exists ('TableVendors')) {
			require(VMPATH_ADMIN . DS . 'tables' . DS . 'vendors.php');
		}
		$vendorModel = VmModel::getModel ('Vendor');
		$vendorModel->setId (1);
		$vendor = $vendorModel->getVendor ();
		$vendorModel->addImages ($vendor, 1);
		$this->getPaymentCurrency ($method);

		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' .
			$method->payment_currency . '" ';
		$db = JFactory::getDBO ();
		$db->setQuery ($q);
		$currency_code_3 = $db->loadResult ();

		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total,$method->payment_currency);
		$cartCurrency = CurrencyDisplay::getInstance($cart->pricesCurrency);

		if ($totalInPaymentCurrency['value'] <= 0) {
			vmInfo (vmText::_ ('VMPAYMENT_SKRILL_PAYMENT_AMOUNT_INCORRECT'));
			return FALSE;
		}

		$merchant_email = $method->pay_to_email;
		if (empty($merchant_email)) {
			vmInfo (vmText::_ ('VMPAYMENT_SKRILL_MERCHANT_EMAIL_NOT_SET'));
			return FALSE;
		}
		$lang = JFactory::getLanguage ();
		$tag = substr ($lang->get ('tag'), 0, 2);
		$post_variables = Array('pay_to_email'             => $merchant_email,
		                        'pay_from_email'           => $address->email,
		                        'payment_methods'          => $method->product,
		                        'recipient_description'    => $vendorModel->getVendorName (),
		                        'transaction_id'           => $order['details']['BT']->order_number,

		                        'return_url'               => JURI::root () .
			                        'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on=' .
			                        $order['details']['BT']->order_number .
			                        '&pm=' .
			                        $order['details']['BT']->virtuemart_paymentmethod_id .
		                            '&Itemid=' . vRequest::getInt ('Itemid') .
								    '&lang='.vRequest::getCmd('lang','')
		                            ,
		                        'cancel_url'               => JURI::root () .
			                        'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=' .
			                        $order['details']['BT']->order_number .
			                        '&pm=' .
			                        $order['details']['BT']->virtuemart_paymentmethod_id .
		                            '&Itemid=' . vRequest::getInt ('Itemid') .
									'&lang='.vRequest::getCmd('lang','')
		                        ,
		                        'status_url'               => JURI::root () .
			                        'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component&lang='.vRequest::getCmd('lang','') ,
		                        'platform'                 => '21477272',
		                        'hide_login'               => $method->hide_login,
		                        'prepare_only'             => 1,
		                        'logo_url'                 => $method->logourl,
		                        'language'                 => strtoupper ($tag),
			//customer details
		                        "firstname"                => $address->first_name,
		                        "lastname"                 => $address->last_name,
		                        "address"                  => $address->address_1,
		                        "address2"                 => isset($address->address_2) ? $address->address_2 : '',
		                        "phone_number"             => $address->phone_1,
		                        "postal_code"              => $address->zip,
		                        "city"                     => $address->city,
		                        "state"                    => isset($address->virtuemart_state_id) ? ShopFunctions::getStateByID ($address->virtuemart_state_id, 'state_2_code') : '',
		                        "country"                  => ShopFunctions::getCountryByID ($address->virtuemart_country_id, 'country_3_code'),

			// payment details section
		                        'amount'                   => $totalInPaymentCurrency['value'],
		                        'currency'                 => $currency_code_3,
		                        'detail1_description'
		                                                   => vmText::_ ('VMPAYMENT_SKRILL_ORDER_NUMBER') . ': ', //ihh hardcoded colon
		                        'detail1_text'             => $order['details']['BT']->order_number);

		// Prepare data that should be stored in the database
		$dbValues['user_session'] = $return_context;
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $this->renderPluginName ($method, $order);
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['cost_per_transaction'] = $method->cost_per_transaction;
		$dbValues['cost_percent_total'] = $method->cost_percent_total;
		$dbValues['payment_currency'] = $method->payment_currency;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency['value'];
		$dbValues['tax_id'] = $method->tax_id;
		$this->storePSPluginInternalData ($dbValues);

		$content = http_build_query ($post_variables);
		$url = $this->_getSKRILLURL ($method);

		$header = "POST /app/payment.pl HTTP/1.1\r\n";
		$header .= "Host: $url\r\n";
		$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$header .= "Content-Length: " . strlen ($content) . "\r\n\r\n";

		$fps = fsockopen ('ssl://' . $url, 443, $errno, $errstr, 10); // timeout applies only to connecting not for I/O
		$sid = '';
		if (!$fps || !stream_set_blocking ($fps, 0)) {
			$this->sendEmailToVendorAndAdmins ("Error with SKRILL: ",
				vmText::sprintf ('VMPAYMENT_SKRILL_ERROR_POSTING_IPN', $errstr, $errno));
			$this->logInfo ('Process IPN ' . vmText::sprintf ('VMPAYMENT_SKRILL_ERROR_POSTING_IPN', $errstr, $errno),
				'message');

			vmInfo (vmText::_ ('VMPAYMENT_SKRILL_DISPLAY_GWERROR'));
			return NULL;
		} else {
			fwrite ($fps, $header);
			fwrite ($fps, $content);

			stream_set_timeout ($fps, 10);
			$read = array($fps);
			$write = $except = NULL;
			$msg = $rbuff = '';
			if (stream_select ($read, $write, $except, 10)) {
				$rbuff = fread ($fps, 2048);
				$msg .= $rbuff;
			}
			$response = $this->_parse_response ($msg);

			if (!count ($response)) {
				$this->logInfo ('Process IPN (empty or bad response) ' . $msg, 'message');
				vmInfo (vmText::_ ('VMPAYMENT_SKRILL_DISPLAY_GWERROR'));
				return NULL;
			}
			$sid = $response[0];
			$this->logInfo ($response[0], 'message');
		}
		fclose ($fps);

		$height = $method->hide_login ? 720 : 500;
		$html = '<html><head><title></title><script type="text/javascript">
                jQuery(document).ready(function () {
                    jQuery(\'#main h3\').css("display", "none");
                });
                </script></head><body>';
		$html .= '<iframe src="https://' . $this->_getSKRILLURL ($method) .
			'/app/payment.pl?sid=' . $sid . '" scrolling="yes" style="x-overflow: none;"
                frameborder="0" height="' . (string)$height . 'px" width="650px"></iframe>';

		$cart->_confirmDone = FALSE;
		$cart->_dataValidated = FALSE;
		$cart->setCartIntoSession ();
		vRequest::setVar ('html', $html);
	}

	function plgVmgetPaymentCurrency ($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL;
		} // Another method was selected, do nothing

		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}

		$this->getPaymentCurrency ($method);
		$paymentCurrencyId = $method->payment_currency;
	}

	function plgVmOnPaymentResponseReceived (&$html) {

		if (!class_exists ('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		if (!class_exists ('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
		$mb_data = vRequest::getPost();


		// the payment itself should send the parameter needed.
		$virtuemart_paymentmethod_id = vRequest::getInt ('pm', 0);
		$order_number = vRequest::getString ('on', 0);
		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL;
		} // Another method was selected, do nothing

		if (!$this->selectedThisElement ($method->payment_element)) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($order_number))) {
			return NULL;
		}

		if (!($paymentTable = $this->getDataByOrderId ($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		vmLanguage::loadJLang('com_virtuemart');
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		vmdebug ('SKRILL plgVmOnPaymentResponseReceived', $mb_data);
		$payment_name = $this->renderPluginName ($method);
		$html = $this->_getPaymentResponseHtml ($paymentTable, $payment_name);
		$link=	JRoute::_("index.php?option=com_virtuemart&view=orders&layout=details&order_number=".$order['details']['BT']->order_number."&order_pass=".$order['details']['BT']->order_pass, false) ;

		$html .='<br />
		<a class="vm-button-correct" href="'.$link.'">'.vmText::_('COM_VIRTUEMART_ORDER_VIEW_ORDER').'</a>';

		$cart = VirtueMartCart::getCart ();
		$cart->emptyCart ();
		return TRUE;
	}

	function plgVmOnUserPaymentCancel () {

		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		$order_number = vRequest::getString ('on', '');
		$virtuemart_paymentmethod_id = vRequest::getInt ('pm', '');
		if (empty($order_number) or
			empty($virtuemart_paymentmethod_id) or
			!$this->selectedThisByMethodId ($virtuemart_paymentmethod_id)
		) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($order_number))) {
			return NULL;
		}

		if (!($paymentTable = $this->getDataByOrderId ($virtuemart_order_id))) {
			return NULL;
		}

		VmInfo (vmText::_ ('VMPAYMENT_SKRILL_PAYMENT_CANCELLED'));
		$session = JFactory::getSession ();
		$return_context = $session->getId ();
		if (strcmp ($paymentTable->user_session, $return_context) === 0) {
			$this->handlePaymentUserCancel ($virtuemart_order_id);
		}

		return TRUE;
	}

	function plgVmOnPaymentNotification () {

		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		$mb_data = vRequest::getPost();

		if (!isset($mb_data['transaction_id'])) {
			return;
		}

		$order_number = $mb_data['transaction_id'];
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($mb_data['transaction_id']))) {
			return;
		}

		if (!($payment = $this->getDataByOrderId ($virtuemart_order_id))) {
			return;
		}

		$method = $this->getVmPluginMethod ($payment->virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}

		if (!$payment) {
			$this->logInfo ('getDataByOrderId payment not found: exit ', 'ERROR');
			return NULL;
		}
		$this->_storeInternalData ($method, $mb_data, $virtuemart_order_id);

		$modelOrder = VmModel::getModel ('orders');
		$vmorder = $modelOrder->getOrder ($virtuemart_order_id);
		$order = array();
		$error_msg = $this->_processStatus ($mb_data, $vmorder, $method);

		if ($error_msg) {
			$order['customer_notified'] = 0;
			$order['order_status'] = $method->status_canceled;
			$order['comments'] = 'process IPN ' . $error_msg;
			$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
			$this->logInfo ('process IPN ' . $error_msg, 'ERROR');
		} else {
			$this->logInfo ('process IPN OK', 'message');
		}

		if (empty($mb_data['payment_status']) ||
			($mb_data['payment_status'] != 'Completed' &&
				$mb_data['payment_status'] != 'Pending')
		) { // can't get status or payment failed
			//return false;
		}
		$order['customer_notified'] = 1;

		if (strcmp ($mb_data['payment_status'], 'Completed') == 0) {
			$order['order_status'] = $method->status_success;
			$order['comments'] = vmText::sprintf ('VMPAYMENT_SKRILL_PAYMENT_STATUS_CONFIRMED', $order_number);
		} elseif (strcmp ($mb_data['payment_status'], 'Pending') == 0) {
			$order['comments'] = vmText::sprintf ('VMPAYMENT_SKRILL_PAYMENT_STATUS_PENDING', $order_number);
			$order['order_status'] = $method->status_pending;
		}
		else {
			$order['order_status'] = $method->status_canceled;
		}

		$this->logInfo ('plgVmOnPaymentNotification return new_status:' . $order['order_status'], 'message');

		$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);

		//// remove vmcart
		$this->emptyCart ($payment->user_session, $mb_data['transaction_id']);
	}

	function plgVmOnShowOrderBEPayment ($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId ($payment_method_id)) {
			return NULL;
		} // Another method was selected, do nothing

		if (!($paymentTable = $this->_getInternalData ($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		$this->getPaymentCurrency ($paymentTable);
		$q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' .
			$paymentTable->payment_currency . '" ';
		$db = JFactory::getDBO ();
		$db->setQuery ($q);
		$currency_code_3 = $db->loadResult ();
		$html = '<table class="adminlist table">' . "\n";
		$html .= $this->getHtmlHeaderBE ();
		$html .= $this->getHtmlRowBE ('PAYMENT_NAME', $paymentTable->payment_name);

		$code = "mb_";
		foreach ($paymentTable as $key => $value) {
			if (substr ($key, 0, strlen ($code)) == $code) {
				$html .= $this->getHtmlRowBE ($key, $value);
			}
		}
		$html .= '</table>' . "\n";
		return $html;
	}

	protected function checkConditions ($cart, $method, $cart_prices) {

		$this->convert_condition_amount($method);

		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount = $this->getCartAmount($cart_prices);
		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
			OR
			($method->min_amount <= $amount AND ($method->max_amount == 0)));

		$countries = array();
		if (!empty($method->countries)) {
			if (!is_array ($method->countries)) {
				$countries[0] = $method->countries;
			} else {
				$countries = $method->countries;
			}
		}

		// probably did not gave his BT:ST address
		if (!is_array ($address)) {
			$address = array();
			$address['virtuemart_country_id'] = 0;
		}

		if (!isset($address['virtuemart_country_id'])) {
			$address['virtuemart_country_id'] = 0;
		}
		if (in_array ($address['virtuemart_country_id'], $countries) || count ($countries) == 0) {
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

		return $this->onStoreInstallPluginTable ($jplugin_id);
	}

	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @author Max Milbers
	 * @author Valérie isaksen
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not valid
	 *
	 */
	public function plgVmOnSelectCheckPayment (VirtueMartCart $cart,  &$msg) {

		return $this->OnSelectCheck ($cart);
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
	 * @author Max Milbers
	 */
	public function plgVmDisplayListFEPayment (VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		return $this->displayListFE ($cart, $selected, $htmlIn);
	}


	public function plgVmonSelectedCalculatePricePayment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {

		return $this->onSelectedCalculatePrice ($cart, $cart_prices, $cart_prices_name);
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

		return $this->onCheckAutomaticSelected ($cart, $cart_prices, $paymentCounter);
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
	public function plgVmOnShowOrderFEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

		$this->onShowOrderFE ($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}

	/**
	 * This event is fired during the checkout process. It can be used to validate the
	 * method data as entered by the user.
	 *
	 * @return boolean True when the data was valid, false otherwise. If the plugin is not activated, it should return null.
	 * @author Max Milbers

	public function plgVmOnCheckoutCheckDataPayment($psType, VirtueMartCart $cart) {
	return null;
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
	function plgVmonShowOrderPrintPayment ($order_number, $method_id) {

		return $this->onShowOrderPrint ($order_number, $method_id);
	}

	/**
	 * Save updated order data to the method specific table
	 *
	 * @param array $_formData Form data
	 * @return mixed, True on success, false on failures (the rest of the save-process will be
	 * skipped!), or null when this method is not activated.

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

	function plgVmSetOnTablePluginParamsPayment ($name, $id, &$table) {

		return $this->setOnTablePluginParams ($name, $id, $table);
	}

} // end of class plgVmpaymentSkrill

// No closing tag
