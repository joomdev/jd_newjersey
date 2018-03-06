<?php

defined ('_JEXEC') or die();

/**
 * Heidelpay credit card plugin
 *
 * @author Heidelberger Payment GmbH <Jens Richter>
 * @version 17.08.08
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) Heidelberger Payment GmbH
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
if (!class_exists ('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

class plgVmPaymentHeidelpay extends vmPSPlugin {

	public static $_this = FALSE;
	protected $version = '17.08.08';

	function __construct (& $subject, $config) {

		parent::__construct ($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys ($this->getTableSQLFields ());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$this->secret = strtoupper (sha1 (mt_rand (10000, mt_getrandmax ())));

		$varsToPush = $this->getVarsToPush ();
		$this->setConfigParameterable ($this->_configTableFieldName, $varsToPush);

	}

	public function getVmPluginCreateTableSQL () {
		return $this->createTableSQL ('Payment Heidelpay');
	}

	function getTableSQLFields () {
		$SQLfields = array(
			'id'							=> 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'	=> 'int(1) UNSIGNED',
			'order_number'			=> 'char(64)',
			'virtuemart_paymentmethod_id'	=> 'mediumint(1) UNSIGNED',
			'unique_id'					=> 'varchar(48)',
			'short_id'					=> 'varchar(14)',
			'payment_code'			=> 'varchar(32)',
			'comment'					=> 'text NOT NULL',
			'date'							=> 'timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP',
			'payment_methode'	=> 'char(2)',
			'payment_type'			=> 'char(2)',
			'transaction_mode'		=> 'char(18)',
			'payment_name'			=> 'char(50)',
			'processing_result'		=> 'char(3)',
			'secret_hash'				=> 'char(50)',
			'response_ip'				=> 'char(20)'
		);
		return $SQLfields;
	}

	function plgVmOnShowOrderBEPayment ($virtuemart_order_id, $payment_id) {
		if (!$this->selectedThisByMethodId ($payment_id)) {
			return NULL; // Another method was selected, do nothing
		}

		$db = JFactory::getDBO ();
		$_q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery ($_q);
		if (!($paymentData = $db->loadObject ())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
		}

		$_html = '<table class="adminlist table">' . "\n";
		$_html .= '	<thead>' . "\n";
		$_html .= '		<tr>' . "\n";
		$_html .= '			<th colspan="2" width="100%">' . vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_PAYMENT_LBL') . '</th>' . "\n";
		$_html .= '		</tr>' . "\n";
		$_html .= '	</thead>' . "\n";
		$_html .= '	<tr>' . "\n";
		$_html .= '		<td>' . vmText::_ ('VMPAYMENT_HEIDELPAY_PAYMENT_RESULT') . '</td>' . "\n";
		if ($paymentData->processing_result == "ACK" AND $paymentData->payment_code == 80) {
			$_html .= '<td style="color: #FC0 ; font-weight:bold ">WAITING</td>';
		} elseif ($paymentData->processing_result == "ACK") {
			$_html .= '<td style="color: #55AA66; font-weight:bold">ACK</td>';
		}
		if ($paymentData->processing_result == "NOK") {
			$_html .= '<td style="color: #F00 ; font-weight:bold ">NOK</td>';
		}
		$_html .= '	</tr>' . "\n";
		$_html .= '	<tr>' . "\n";
		$_html .= '		<td>' . vmText::_ ('VMPAYMENT_HEIDELPAY_PAYMENT_METHOD') . '</td>' . "\n";
		$_html .= '		<td>' . $paymentData->payment_methode . '.' . $paymentData->payment_type . ' (' . $paymentData->payment_name . ')</td>' . "\n";
		$_html .= '	</tr>' . "\n";
		$_html .= '	<tr>' . "\n";
		$_html .= '		<td>UniqeID</td>' . "\n";
		$_html .= '		<td>' . $paymentData->unique_id . '</td>' . "\n";
		$_html .= '	</tr>' . "\n";
		$_html .= '	<tr>' . "\n";
		$_html .= '		<td>Short-ID</td>' . "\n";
		$_html .= '		<td>' . $paymentData->short_id . '</td>' . "\n";
		$_html .= '	</tr>' . "\n";
		$_html .= '	<tr>' . "\n";
		$_html .= '		<td>' . vmText::_ ('VMPAYMENT_HEIDELPAY_COMMENT') . '</td>' . "\n";
		$_html .= '		<td>' . $paymentData->comment . '</td>' . "\n";
		$_html .= '	</tr>' . "\n";
		$_html .= '</table>' . "\n";
		return $_html;
	}

	function plgVmOnConfirmedOrderStorePaymentData ($virtuemart_order_id, $orderData, $priceData) {
		if (!$this->selectedThisPayment ($this->_pelement, $orderData->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		return FALSE;
	}

	function plgVmConfirmedOrder($cart, $order) {
		if (!($method = $this->getVmPluginMethod ($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return FALSE;
		}
		$session = JFactory::getSession ();
		$return_context = $session->getId ();
		$this->_debug = $method->HEIDELPAY_DEBUG;

		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists ('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}

		$address = ((isset($order['details']['BT'])) ? $order['details']['BT'] : $order['details']['ST']);

		if (!class_exists ('TableVendors')) {
			require(VMPATH_ADMIN . DS . 'table' . DS . 'vendors.php');
		}
		$vendorModel = VmModel::getModel ('Vendor');
		$vendorModel->setId (1);
		$vendor = $vendorModel->getVendor ();
		$vendorModel->addImages ($vendor, 1);
		$this->getPaymentCurrency ($method);

		$currency_code_3 = shopFunctions::getCurrencyByID ($method->payment_currency, 'currency_code_3');
		$paymentCurrency = CurrencyDisplay::getInstance ($method->payment_currency);
		$totalInPaymentCurrency = round ($paymentCurrency->convertCurrencyTo ($method->payment_currency, $order['details']['BT']->order_total, FALSE), 2);
		$cd = CurrencyDisplay::getInstance ($cart->pricesCurrency);

		// prepare the post var values:
		//$languageTag = $this->getLang ();
		$params = array();
		/*
		* Default configuration for hco
		*/
		$params['FRONTEND.MODE'] = "DEFAULT";
		$params['FRONTEND.ENABLED'] = "true";
		$params['FRONTEND.POPUP'] = "false";
		$params['FRONTEND.REDIRECT_TIME'] = "0";
		$params['REQUEST.VERSION'] = "1.0";
		$params['FRONTEND.NEXTTARGET'] = "top.location.href";

		$params['PRESENTATION.AMOUNT'] = $totalInPaymentCurrency;
		$params['PRESENTATION.CURRENCY'] = $currency_code_3;
		$params['FRONTEND.LANGUAGE'] = strtolower(substr(vmConfig::$vmlangTag, 0,2));
		$params['CRITERION.LANG'] = $params['FRONTEND.LANGUAGE'];
		$params['IDENTIFICATION.TRANSACTIONID'] = $order['details']['BT']->order_number;
		/*
		* Set payment methode to PA for online transfer, invoice and prepayment
		*/
		$PaymentTypePA = array('OT', 'PP', 'IV');
		if (in_array (substr ($method->HEIDELPAY_PAYMENT_TYPE, 0, 2), $PaymentTypePA)) {
			$method->HEIDELPAY_PAYMENT_METHOD = "PA";
		} else {
			$method->HEIDELPAY_PAYMENT_METHOD = $method->HEIDELPAY_PAYMENT_METHOD;
		}

		$params['PAYMENT.CODE'] = substr ($method->HEIDELPAY_PAYMENT_TYPE, 0, 2) . "." . $method->HEIDELPAY_PAYMENT_METHOD;
		$params['TRANSACTION.CHANNEL'] = $method->HEIDELPAY_CHANNEL_ID;

		/*
		* Special case for paypal without hco iframe
		*/
		if ($method->HEIDELPAY_PAYMENT_TYPE == "VAPAYPAL") {
			$params['PAYMENT.CODE'] 						= "VA.DB";
			$params['ACCOUNT.BRAND']						= "PAYPAL";
			$params['FRONTEND.PM.DEFAULT_DISABLE_ALL'] = "true";
			$params['FRONTEND.PM.0.ENABLED']		= "true";
			$params['FRONTEND.PM.0.METHOD'] 		= "VA";
			$params['FRONTEND.PM.0.SUBTYPES']	= "PAYPAL";
		}
		/*
		  * Special case for MangirKart without hco iframe
		  */
		if ($method->HEIDELPAY_PAYMENT_TYPE == "PCMANGIR") {
			$params['PAYMENT.CODE']  = "PC.PA";
			$params['ACCOUNT.BRAND'] = "MANGIRKART";
		}
		/*
		   * case for GiroPay
		  */
		if ($method->HEIDELPAY_PAYMENT_TYPE == "OTGIR") {
			$params['FRONTEND.SEPA']     = 'YES';
			$params['FRONTEND.SEPASWITCH'] = 'NO';
		}
		/*
		   * Special case for BarPay without hco iframe
		  */
		if ($method->HEIDELPAY_PAYMENT_TYPE == "PPBARPAY") {
			$params['PAYMENT.CODE']  = "PP.PA";
			$params['ACCOUNT.BRAND'] = "BARPAY";
		}
		/*
		 * Special case for BillSAFE
		*/
		if ($method->HEIDELPAY_PAYMENT_TYPE == "IVBILLSAFE") {
			$toCheck = array('last_name', 'first_name', 'middle_name', 'phone_1', 'phone_2', 'fax', 'address_1', 'address_2', 'city', 'virtuemart_state_id', 'virtuemart_country_id', 'zip');

			$bsError = false;
			
			foreach($toCheck as $val){
				if(isset($order['details']['ST']->$val)){
					if($order['details']['ST']->$val != $order['details']['BT']->$val){
						$bsError = true;
						$errorVal = $val;
						break;
					}
				}
			}

			if($bsError){			
				$msg = vmText::_('VMPAYMENT_HEIDELPAY_TECHNICAL_ERROR')."<br />".
					vmText::_('VMPAYMENT_HEIDELPAY_BILLSAFE_ERROR')."<br />";
				$app = JFactory::getApplication();				
				$app->redirect('index.php?option=com_virtuemart&view=cart', $msg);
			}
			$params['PAYMENT.CODE']	= "IV.PA";
			$params['ACCOUNT.BRAND']	= "BILLSAFE";
			$params = array_merge($params, $this->getBasketDetails());
		}
		/*
		 *  User account information
		 */
		$params['ACCOUNT.HOLDER'] = $address->first_name . " " . $address->last_name;
		$params['NAME.GIVEN'] = $address->first_name;
		$params['NAME.FAMILY'] = $address->last_name;
		if(!empty($address->company)) $params['NAME.COMPANY'] = $address->company ;
		$params['ADDRESS.STREET'] = $address->address_1;
		isset($address->address_2) ? $params['ADDRESS.STREET'] .= " " . $address->address_2 : '';
		$params['ADDRESS.ZIP'] = $address->zip;
		$params['ADDRESS.CITY'] = $address->city;
		$params['ADDRESS.COUNTRY'] = ShopFunctions::getCountryByID ($address->virtuemart_country_id, 'country_2_code');
		$params['CONTACT.EMAIL'] = $order['details']['BT']->email;
		$params['CONTACT.IP'] = $_SERVER['REMOTE_ADDR'];
		
		if ($method->HEIDELPAY_PAYMENT_TYPE == "VAPAYPAL") {
			if (!empty($order['details']['ST'])) {
				$params['NAME.GIVEN'] 		= $order['details']['ST']->first_name;
				$params['NAME.FAMILY']		= $order['details']['ST']->last_name;
				if(!empty($order['details']['ST']->company)) $params['NAME.COMPANY'] = $order['details']['ST']->company ;
				$params['ADDRESS.STREET'] 	= $order['details']['ST']->address_1;
				isset($order['details']['ST']->address_2) ? $params['ADDRESS.STREET'] .= " " . $order['details']['ST']->address_2 : '';
				$params['ADDRESS.ZIP'] 		= $order['details']['ST']->zip;
				$params['ADDRESS.CITY'] 	= $order['details']['ST']->city;
				$params['ADDRESS.COUNTRY']	= ShopFunctions::getCountryByID ($order['details']['ST']->virtuemart_country_id, 'country_2_code');
			} 
			
		}
		
		/*
		* Add debug informations for merchiant support
		*/
		$params['SHOP.TYPE'] = 'VirtueMart '.VmConfig::getInstalledVersion();
		$params['SHOPMODULE.VERSION'] = $this->version;

		$params['CRITERION.PAYMENT_NAME'] = vmText::_ ('VMPAYMENT_HEIDELPAY_' . $method->HEIDELPAY_PAYMENT_TYPE);
		$params['CRITERION.PAYMENT_NAME'] = strip_tags($params['CRITERION.PAYMENT_NAME']);
		/*
		* Create hash to secure the response
		*/
		$params['CRITERION.SECRET'] = $this->createSecretHash ($order['details']['BT']->order_number,$method->HEIDELPAY_SECRET);
		/*
		* Set transaction mode
		*/
		if ($method->HEIDELPAY_TRANSACTION_MODE == 2) {
			$params['TRANSACTION.MODE'] = "LIVE";
		} elseif ($method->HEIDELPAY_TRANSACTION_MODE == 0) {
			$params['TRANSACTION.MODE'] = "INTEGRATOR_TEST";
		} else {
			$params['TRANSACTION.MODE'] = "CONNECTOR_TEST";
		}
		/*
		* Add response and css path
		*/
		$params['FRONTEND.RESPONSE_URL'] = JROUTE::_ (JURI::root(), $xhtml=true, $ssl=0) . 'plugins/vmpayment/heidelpay/heidelpay/heidelpay_response.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&on=' . urlencode($order['details']['BT']->order_number) . '&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id;
		$cssFile = "heidelpay_default.css";
		if (!empty($method->HEIDELPAY_STYLE)) {
			$cssFile = $method->HEIDELPAY_STYLE ;
		}

		$params['FRONTEND.CSS_PATH'] = JROUTE::_ (JURI::root(), $xhtml=true, $ssl=0) . 'plugins/vmpayment/heidelpay/heidelpay/' . $cssFile;
		$requestUrl = $method->HEIDELPAY_PAYMENT_URL;
		$params['SECURITY.SENDER'] = $method->HEIDELPAY_SECURITY_SENDER;
		$params['USER.LOGIN'] = $method->HEIDELPAY_USER_LOGIN;
		$params['USER.PWD'] = $method->HEIDELPAY_USER_PW;

		if(substr ($method->HEIDELPAY_PAYMENT_TYPE, 0, 2) == 'DD') {
			$sepaform = array();
			$sepaform = $this->switchDirectDebitFrom($method->HEIDELPAY_SEPA_FORM);
			$params = array_merge($sepaform , $params);
		}
		/*
		 * send request to payment server
		 */
		$response = $this->doRequest ($requestUrl, $params, $method->HEIDELPAY_DEBUG);

		if ($params['TRANSACTION.MODE'] != "LIVE") {
			vmInfo('VMPAYMENT_HEIDELPAY_PAYMENT_TESTMODE');
		}
		/*
		* On success show iframe or show error information for your customer
		*/
		$returnValue = 0;

		if ($response['PROCESSING_RESULT'] == "ACK" || $response['POST_VALIDATION'] == "ACK") {
			$returnValue = 2;
			$html = $this->renderByLayout ('displaypayment', array(
			                                                      'response'                       => $response['FRONTEND_REDIRECT_URL']
			                                                 ));
		} else {
			$html = vmText::_ ('VMPAYMENT_HEIDELPAY_TECHNICAL_ERROR') .
				" <br /> - " . addslashes ($response['PROCESSING_RETURN']) . "<br />" .
				vmText::_ ('VMPAYMENT_HEIDELPAY_CONTACT_SHOPOWNER');
		}
		/*
		* Show debug information
		*/
		if ($method->HEIDELPAY_DEBUG == 1) {
			vmDebug('HEIDELPAY plgVmConfirmedOrder', $params);
		}
		return $this->processConfirmedOrderPaymentResponse ($returnValue, $cart, $order, $html, '', '');
	}

	function plgVmgetPaymentCurrency ($virtuemart_paymentmethod_id, &$paymentCurrencyId) {
		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
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

		$virtuemart_paymentmethod_id = JRequest::getInt ('pm', 0);
		$order_number = JRequest::getString ('on', 0);

		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($order_number))) {
			return NULL;
		}
		$db = JFactory::getDBO ();
		$_q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery ($_q);
		if (!($paymentData = $db->loadObject ())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
		}
		vmdebug ('HEIDELPAY paymentdata', $paymentData);
	

		if ($paymentData->processing_result == "NOK") {
			vmError ('VMPAYMENT_HEIDELPAY_PAYMENT_FAILED','VMPAYMENT_HEIDELPAY_PAYMENT_FAILED');
			vmError (" - " . $paymentData->comment," - " . $paymentData->comment);
		} else {
			vmInfo ('VMPAYMENT_HEIDELPAY_PAYMENT_SUCESS');
			$html  = "<h3>".vmText::sprintf ('VMPAYMENT_HEIDELPAY_ORDER_NR') . ': ' . $order_number . " </h3>" ;
			$tmpkom	= preg_replace("/\(-/", '<a href="', $paymentData->comment);
			$tmpkom	= preg_replace('/-\)/', '" target="_blank">Barcode runterladen</a>', $tmpkom );
			$html .= $tmpkom;
			//delete basket only on success
			$cart = VirtueMartCart::getCart ();
			$cart->emptyCart ();
		}
		// if payment is in test mode
		if ($paymentData->transaction_mode != "LIVE") {
			vmInfo('VMPAYMENT_HEIDELPAY_PAYMENT_TESTMODE');
		}
		$orgSecret = $this->createSecretHash ($order_number, $method->HEIDELPAY_SECRET);
		$order['comments']="";
		if ($virtuemart_order_id) {
			$order['customer_notified'] = 0;
			$order['order_status'] = $this->getStatus ($method, $paymentData->processing_result);
			$modelOrder = VmModel::getModel ('orders');
			$orderitems = $modelOrder->getOrder ($virtuemart_order_id);
			$nb_history = count ($orderitems['history']);
			if ($orderitems['history'][$nb_history - 1]->order_status_code != $order['order_status']) {
				if ($method->HEIDELPAY_CONFIRM_EMAIL == 1 or ($method->HEIDELPAY_CONFIRM_EMAIL == 2 and $paymentData->processing_result == "ACK")) {
					$order['customer_notified'] = 1;
					$order['comments'] = vmText::sprintf ('VMPAYMENT_HEIDELPAY_EMAIL_SENT') . "<br />";
				}
				$order['comments'] .= $paymentData->comment;
				/*
				* Verify Payment response
				*/
				if ($orgSecret != $paymentData->secret_hash) {
					$order['customer_notified'] = 0;
					$order['comments'] = "Hash verification error, suspecting manipulation. IP: " . $paymentData->response_ip;
					$order['order_status'] = '';
				}
				$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
			}
			$dbu = JFactory::getDBO ();
			$_u = "UPDATE `" . $this->_tablename . "` "."SET `created_on` = NOW() WHERE `virtuemart_order_id` = " . (int)$virtuemart_order_id;
			$dbu->setQuery ($_u);
			$result = $dbu->execute();
		}
		return TRUE;
	}
	
	function plgVmOnUserPaymentCancel () {
		if (!class_exists ('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		
		
		$virtuemart_paymentmethod_id = JRequest::getInt ('pm', 0);
		$order_number = JRequest::getString ('on', 0);

		if (!($method = $this->getVmPluginMethod ($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement ($method->payment_element)) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber ($order_number))) {
			return NULL;
		}
		$db = JFactory::getDBO ();
		$_q = 'SELECT * FROM `' . $this->_tablename . '` '
			. 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
		$db->setQuery ($_q);
		if (!($paymentData = $db->loadObject ())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
		}
		vmdebug ('HEIDELPAY paymentdata', $paymentData);
	

		if ($paymentData->processing_result == "NOK") {
			vmError ('VMPAYMENT_HEIDELPAY_PAYMENT_FAILED','VMPAYMENT_HEIDELPAY_PAYMENT_FAILED');
			vmError (" - " . $paymentData->comment," - " . $paymentData->comment);
			$order['comments']="";
			if ($virtuemart_order_id) {
			$order['customer_notified'] = 0;
			$order['order_status'] = $this->getStatus ($method, $paymentData->processing_result);
			$modelOrder = VmModel::getModel ('orders');
			$orderitems = $modelOrder->getOrder ($virtuemart_order_id);
			$nb_history = count ($orderitems['history']);
			if ($orderitems['history'][$nb_history - 1]->order_status_code != $order['order_status']) {
				if ($method->HEIDELPAY_CONFIRM_EMAIL == 1 or ($method->HEIDELPAY_CONFIRM_EMAIL == 2 and $paymentData->processing_result == "ACK")) {
					$order['customer_notified'] = 1;
					$order['comments'] = vmText::sprintf ('VMPAYMENT_HEIDELPAY_EMAIL_SENT') . "<br />";
				}
				$order['comments'] .= $paymentData->comment;
				$modelOrder->updateStatusForOneOrder ($virtuemart_order_id, $order, TRUE);
			}
		}
			
		}
		
		
		
		
		
		
			
		
		
		$order_number = JRequest::getVar ('on');
		if (!$order_number) {
			return FALSE;
		}
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', '');
		if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL;
		}
		$db = JFactory::getDBO ();
		$query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";

		$db->setQuery ($query);
		$virtuemart_order_id = $db->loadResult ();

		if (!$virtuemart_order_id) {
			return NULL;
		}
		return TRUE;
	}

	function getStatus ($method, $status) {
		if ($status == 'ACK') {
			$new_status = $method->HEIDELPAY_STATUS_SUCCESS;
		} else {
			$new_status = $method->HEIDELPAY_STATUS_FAILED;
		}
		return $new_status;
	}

	function plgVmOnStoreInstallPaymentPluginTable ($jplugin_id) {
		return $this->onStoreInstallPluginTable ($jplugin_id);
	}

	public function plgVmOnSelectCheckPayment (VirtueMartCart $cart, &$msg) {
		return $this->OnSelectCheck ($cart);
	}

	public function plgVmDisplayListFEPayment (VirtueMartCart $cart, $selected = 0, &$htmlIn) {
		return $this->displayListFE ($cart, $selected, $htmlIn);
	}

	public function plgVmonSelectedCalculatePricePayment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
		return $this->onSelectedCalculatePrice ($cart, $cart_prices, $cart_prices_name);
	}

	function plgVmOnCheckAutomaticSelectedPayment (VirtueMartCart $cart, array $cart_prices = array()) {
		return $this->onCheckAutomaticSelected ($cart, $cart_prices);
	}

	public function plgVmOnShowOrderFEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
		$this->onShowOrderFE ($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}

	function plgVmonShowOrderPrintPayment ($order_number, $method_id) {
		return $this->onShowOrderPrint ($order_number, $method_id);
	}


	public function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}

	function plgVmSetOnTablePluginParamsPayment ($name, $id, &$table) {
		return $this->setOnTablePluginParams ($name, $id, $table);
	}

	public function plgVmOnUpdateOrderPayment ($_formData) {
		return NULL;
	}

	public function plgVmOnUpdateOrderLine ($_formData) {
		return NULL;
	}


	public function plgVmOnEditOrderLineBE ($_orderId, $_lineId) {
		return NULL;
	}


	public function plgVmOnShowOrderLineFE ($_orderId, $_lineId) {
		return NULL;
	}


	/*protected function getLang () {
		$language =& JFactory::getLanguage ();
		$tag = strtolower (substr ($language->get ('tag'), 0, 2));
		return $tag;
	}*/

	private function doRequest ($url, $params, $debug) {
		$data = $params;
		$result = "";
		// Erstellen des Strings für die Datenübermittlung
		foreach ($data AS $key => $value) {
			if ($this->isUTF8 ($value)) {
				$value = utf8_decode ($value);
			}
			$key = strtoupper ($key);
			$value = urlencode($value);
			$result .= $key. "=" . $value . "&";
		}
		$strPOST = stripslashes ($result);

		// prüfen ob CURL existiert
		if (function_exists ('curl_init')) {
			$ch = curl_init ();
			curl_setopt ($ch, CURLOPT_URL, $url);
			curl_setopt ($ch, CURLOPT_HEADER, 0);
			curl_setopt ($ch, CURLOPT_FAILONERROR, 1);
			curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 60);
			curl_setopt ($ch, CURLOPT_POST, 1);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $strPOST);
			curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt ($ch, CURLOPT_USERAGENT, "php ctpepost");
			curl_setopt ($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);

			$this->response = curl_exec ($ch);
			$this->error = curl_error ($ch);
			curl_close ($ch);

			$res = $this->response;
			if (!$this->response && $this->error) {
				$msg = urlencode ('Curl Fehler');
				$res = 'status=FAIL&msg=' . $this->error;
			}
		} else {
			$msg = urlencode ('Curl Fehler');
			$res = 'status=FAIL&&msg=' . $msg;
		}
		$result = NULL;
		parse_str ($res, $result);
		/*
		* Show debug information
		*/
		if ($debug == 1) {
			vmdebug ('Heildepay Response', $result);
		}
		return $result;
	}

	private function isUTF8 ($string) /*{{{*/ {
		if (is_array ($string)) {
			$enc = implode ('', $string);
			return @!((ord ($enc[0]) != 239) && (ord ($enc[1]) != 187) && (ord ($enc[2]) != 191));
		} else {
			return (utf8_encode (utf8_decode ($string)) == $string);
		}
	}

	protected function checkConditions ($cart, $method, $cart_prices) {
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount = $cart_prices['salesPrice'];
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

	function createSecretHash ($orderID, $secret) {
		$hash = sha1 ($orderID . $secret);
		return $hash;

	}
	/**
	 * methode to change the form fields of the hco(iframe)
	 * nessuccary for support of SEPA (single euro payments area)
	 * @param int $mode_id id to set version
	 * @return array parameter for hco call
	 */
	public function switchDirectDebitFrom($mode_id) {
		$params = array();
		switch ($mode_id){
			// account and bank no:
			case 1:
				$params['FRONTEND.SEPA']					= 'NO';
				$params['FRONTEND.SEPASWITCH'] 	= 'NO';
				break;
			// both methodes separeted with an or
			case 3:
				$params['FRONTEND.SEPA']					= 'YES';
				$params['FRONTEND.SEPASWITCH'] 	= 'YES';
				break;
			// both methodes with a selector
			case 4:
				$params['FRONTEND.SEPA']					= 'NO';
				$params['FRONTEND.SEPASWITCH'] 	= 'YES';
				break;
			//  IBAN and BIC
			default:
				$params['FRONTEND.SEPA']					= 'YES';
				$params['FRONTEND.SEPASWITCH']	= 'NO';
		}
		return $params;
	}
	/**
	 * generate BillSafe informations from basket
	 * @return array criterion for billSafe
	 */
	private function getBasketDetails(){
		$user = JFactory::getUser();
		$cart = VirtueMartCart::getCart(false);
		$items = $cart->products;
		$prices = $cart->cartPrices;

		$params = array();
		if ($items) {
			$i = 0;
			//	ITEMS
			foreach($items as $key => $item){
				$i++;
				$prefix = 'CRITERION.POS_'.sprintf('%02d', $i);

				$params[$prefix.'.POSITION']		= $i;
				$params[$prefix.'.QUANTITY'] 	= (int)$item->quantity;
				if (empty($item->product_unit)){ $item->product_unit = 'Stk.'; }
				$params[$prefix.'.UNIT'] 				= $item->product_unit;
				#price in cents
				$params[$prefix.'.AMOUNT_UNIT_GROSS'] = ($prices[$key]['basePriceWithTax'] * 100);
				$params[$prefix.'.AMOUNT_GROSS'] = ($prices[$key]['subtotal_with_tax'] * 100);
				$item->product_name = preg_replace('/%/','Proz.', $item->product_name);
				$item->product_name = preg_replace('/("|\'|!|$|=)/',' ', $item->product_name);
				$params[$prefix.'.TEXT'] 			= strlen($item->product_name) > 100 ? substr($item->product_name, 0, 90) . '...' : $item->product_name;
				$params[$prefix.'.ARTICLE_NUMBER'] 	= $item->product_sku;
				$params[$prefix.'.PERCENT_VAT'] 	= sprintf('%1.2f', $prices[$key]['VatTax'][$item->product_tax_id]['1']);
				$params[$prefix.'.ARTICLE_TYPE'] = 'goods';
			}

			//	SHIPPING			
			require(VMPATH_ADMIN . DS . 'models' . DS . 'shipmentmethod.php');
			$vmms = new VirtueMartModelShipmentmethod();
			$shipmentInfo = $vmms->getShipments();

			foreach($shipmentInfo as $skey => $svalue){
				if($svalue->virtuemart_shipmentmethod_id == $cart->virtuemart_shipmentmethod_id){
					$shipmentData = array();
					foreach (explode("|", $svalue->shipment_params) as $line) {
						list($key, $value) = explode('=', $line, 2);
						$shipmentData[$key] = str_replace('"','',$value);
					}
					$shipmentTaxId = $shipmentData['tax_id'];
					$shipmentTax = sprintf('%1.2f',$cart->cartData['VatTax'][$shipmentTaxId]['calc_value']);
				}
			}
			$i++;
			$prefix = 'CRITERION.POS_'.sprintf('%02d', $i);

			$params[$prefix.'.POSITION']		= $i;
			$params[$prefix.'.QUANTITY'] 	= '1';
			$params[$prefix.'.UNIT'] 				= 'Stk.';
			$params[$prefix.'.AMOUNT_UNIT_GROSS'] = ($prices['salesPriceShipment'] * 100);
			$params[$prefix.'.AMOUNT_GROSS']	= ($prices['salesPriceShipment'] * 100);
			$params[$prefix.'.TEXT'] 			= 'Shipping';
			$params[$prefix.'.ARTICLE_NUMBER']	= 'Shipping';
			$params[$prefix.'.PERCENT_VAT']	= $shipmentTax;
			$params[$prefix.'.ARTICLE_TYPE']	= 'shipment';

			//	COUPON
			if(isset($prices['couponValue']) && ($prices['couponValue'] != '')){
				$i++;
				$prefix = 'CRITERION.POS_'.sprintf('%02d', $i);

				$params[$prefix.'.POSITION']		= $i;
				$params[$prefix.'.QUANTITY'] 	= '1';
				$params[$prefix.'.UNIT'] 				= 'Stk.';
				$params[$prefix.'.AMOUNT_UNIT_GROSS'] = ($prices['couponValue'] * 100);
				$params[$prefix.'.AMOUNT_GROSS']	= ($prices['couponValue'] * 100);
				$params[$prefix.'.TEXT'] 			= 'Coupon';
				$params[$prefix.'.ARTICLE_NUMBER']	= 'Coupon';
				$params[$prefix.'.PERCENT_VAT']	= $prices['couponTax'];
				$params[$prefix.'.ARTICLE_TYPE']	= 'voucher';
			}
		}
		return $params;
	}
}