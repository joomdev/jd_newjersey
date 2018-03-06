<?php
/**
 *
 * Paypal payment plugin
 *
 * @author Jeremy Magne
 * @author Valérie Isaksen
 * @version $Id: paypal.php 7217 2013-09-18 13:42:54Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

defined('_JEXEC') or die('Restricted access');
if (!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

if (!class_exists('PaypalHelperPaypal')) {
	require(VMPATH_ROOT .DS.'plugins'.DS.'vmpayment'.DS.'paypal'.DS.'paypal'.DS.'helpers'.DS.'paypal.php');
}
if (!class_exists('PaypalHelperCustomerData')) {
	require(VMPATH_ROOT .DS.'plugins'.DS.'vmpayment'.DS.'paypal'.DS.'paypal'.DS.'helpers'.DS.'customerdata.php');
}
if (!class_exists('PaypalHelperPayPalStd')) {
	require(VMPATH_ROOT . DS.'plugins'.DS.'vmpayment'.DS.'paypal'.DS.'paypal'.DS.'helpers'.DS.'paypalstd.php');
}
if (!class_exists('PaypalHelperPayPalExp')) {
	require(VMPATH_ROOT . DS.'plugins'.DS.'vmpayment'.DS.'paypal'.DS.'paypal'.DS.'helpers'.DS.'paypalexp.php');
}
if (!class_exists('PaypalHelperPayPalHosted')) {
	require(VMPATH_ROOT . DS.'plugins'.DS.'vmpayment'.DS.'paypal'.DS.'paypal'.DS.'helpers'.DS.'paypalhosted.php');
}
if (!class_exists('PaypalHelperPayPalApi')) {
	require(VMPATH_ROOT . DS.'plugins'.DS.'vmpayment'.DS.'paypal'.DS.'paypal'.DS.'helpers'.DS.'paypalapi.php');
}
class plgVmPaymentPaypal extends vmPSPlugin {

	// instance of class
	private $customerData;
	private $_autobilling_max_amount = '';
	private $_cc_name = '';
	private $_cc_type = '';
	private $_cc_number = '';
	private $_cc_cvv = '';
	private $_cc_expire_month = '';
	private $_cc_expire_year = '';
	private $_cc_valid = false;
	private $_user_data_valid = false;
	private $_errormessage = array();

	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->customerData = new PaypalHelperCustomerData();
		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; //virtuemart_paypal_id';
		$this->_tableId = 'id'; //'virtuemart_paypal_id';
		$varsToPush = array(
			'paypal_merchant_email' => array('', 'char'),
			'accelerated_onboarding' => array('', 'int'),
			'api_login_id' => array('', 'char'),
			'api_password' => array('', 'char'),
			'authentication' => array('', 'char'),
			'api_signature' => array('', 'int'),
			'api_certificate' => array('', 'char'),

			'sandbox' => array(0, 'int'),
			'sandbox_merchant_email' => array('', 'char'),
			'sandbox_api_login_id' => array('', 'char'),
			'sandbox_api_password' => array('', 'char'),
			'sandbox_api_signature' => array('', 'char'),
			'sandbox_api_certificate' => array('', 'char'),
			'sandbox_payflow_vendor' => array('', 'char'),
			'sandbox_payflow_partner' => array('', 'char'),
			'payflow_vendor' => array('', 'char'),
			'payflow_partner' => array('', 'char'),
			'creditcards' => array('', 'int'),
			'cvv_required' => array('', 'int'),
			'cvv_images' => array('', 'int'),

			'paypalproduct' => array('', 'char'),
			'paypal_verified_only' => array('', 'int'),
			'payment_currency' => array('', 'int'),
			'email_currency' => array('', 'char'),
			'log_ipn' => array('', 'int'),
			'payment_logos' => array('', 'char'),
			'debug' => array(0, 'int'),
			'log' => array(0, 'int'),
			'status_pending' => array('', 'char'),
			'status_success' => array('', 'char'),
			'status_canceled' => array('', 'char'),
			'status_expired' => array('', 'char'),
			'status_capture' => array('', 'char'),
			'status_refunded' => array('', 'char'),
			'status_denied' => array('', 'char'),
			'status_partial_refunded' => array('', 'char'),
			'expected_maxamount' => array('', 'int'),

			'secure_post' => array('', 'int'),
			'ipn_test' => array('', 'int'),
			'no_shipping' => array('', 'int'),
			'address_override' => array('', 'int'),
			'payment_type' => array('_xclick', 'char'),
			'subcription_trials' => array(0, 'int'),
			'trial1_price' => array('', 'int'),
			'trial1_duration' => array('', 'char'),
			//'trial2_price'         	 => array('', 'int'),
			//'trial2_duration'	     => array('', 'char'),
			'subscription_duration' => array('', 'char'),
			'subscription_term' => array('', 'int'),

			'payment_plan_duration' => array('', 'char'),
			'payment_plan_term' => array('', 'int'),
			'payment_plan_defer' => array('', 'int'),
			'payment_plan_defer_duration' => array('', 'char'),
			'payment_plan_defer_strtotime' => array('', 'char'),

			'billing_max_amount_type' => array('', 'char'),
			'billing_max_amount' => array('', 'float'),
			//Settlement
			'sftp_login' => array('', 'char'),
			'sftp_password' => array('', 'char'),
			'sftp_host' => array('', 'char'),
			'sftp_sandbox_login' => array('', 'char'),
			'sftp_sandbox_password' => array('', 'char'),

			//Restrictions
			'countries' => array('', 'char'),
			'min_amount' => array('', 'float'),
			'max_amount' => array('', 'float'),
			'publishup' => array('', 'char'),
			'publishdown' => array('', 'char'),
			'virtuemart_shipmentmethod_ids' => array('', 'int'),

			//discount
			'cost_per_transaction' => array('', 'float'),
			'cost_percent_total' => array('', 'char'),
			'cost_method' => array('', 'int'),
			'tax_id' => array(0, 'int'),

			//Layout
			'headerBgColor' => array('', 'char'),
			'headerHeight' => array('', 'char'),
			'logoFont' => array('', 'char'),
			'logoFontColor' => array('', 'char'),
			'logoFontSize' => array('', 'char'),
			'bodyBgImg' => array('', 'char'),
			'bodyBgColor' => array('', 'char'),
			'PageTitleTextColor' => array('', 'char'),
			'PageCollapseBgColor' => array('', 'char'),
			'PageCollapseTextColor' => array('', 'char'),

			'orderSummaryBgColor' => array('', 'char'),
			'orderSummaryBgImage' => array('', 'char'),
			'footerTextColor' => array('', 'char'),
			'footerTextlinkColor' => array('', 'char'),

			'pageButtonBgColor' => array('', 'char'),
			'pageButtonTextColor' => array('', 'char'),
			'pageTitleTextColor' => array('', 'char'),
			'sectionBorder' => array('', 'char'),

			'bordercolor' => array('', 'char'),
			'headerimg' => array('', 'char'),
			'logoimg' => array('', 'char'),
			'payment_action' => array('sale', 'char'),
			'template' => array('', 'char'),
			'add_prices_api' => array('', 'int'),
			'offer_credit' => array('', 'int'),
			'itemise_in_cart' => array('0','int')
		);

		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
	}

	public function getVmPluginCreateTableSQL() {
		return $this->createTableSQL('PayPal Table');
	}

	function getTableSQLFields() {

		$SQLfields = array(
			'id' => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id' => 'int(1) UNSIGNED',
			'order_number' => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name' => 'varchar(5000)',
			'payment_order_total' => 'decimal(15,5) NOT NULL',
			'payment_currency' => 'smallint(1)',
			'email_currency' => 'smallint(1)',
			'cost_per_transaction' => 'decimal(10,2)',
			'cost_percent_total' => 'decimal(10,2)',
			'tax_id' => 'smallint(1)',
			'paypal_custom' => 'varchar(255)',
			'paypal_method' => 'varchar(200)',

			'paypal_response_mc_gross' => 'decimal(10,2)',
			'paypal_response_mc_currency' => 'char(10)',
			'paypal_response_invoice' => 'char(32)',
			'paypal_response_protection_eligibility' => 'char(128)',
			'paypal_response_payer_id' => 'char(13)',
			'paypal_response_tax' => 'decimal(10,2)',
			'paypal_response_payment_date' => 'char(28)',
			'paypal_response_payment_status' => 'char(50)',
			'paypal_response_pending_reason' => 'char(50)',
			'paypal_response_mc_fee' => 'decimal(10,2)',
			'paypal_response_payer_email' => 'char(128)',
			'paypal_response_last_name' => 'char(64)',
			'paypal_response_first_name' => 'char(64)',
			'paypal_response_business' => 'char(128)',
			'paypal_response_receiver_email' => 'char(128)',
			'paypal_response_transaction_subject' => 'char(128)',
			'paypal_response_residence_country' => 'char(2)',
			'paypal_response_txn_id' => 'char(32)',
			'paypal_response_txn_type' => 'char(32)', //The kind of transaction for which the IPN message was sent
			'paypal_response_parent_txn_id' => 'char(32)',
			'paypal_response_case_creation_date' => 'char(32)',
			'paypal_response_case_id' => 'char(32)',
			'paypal_response_case_type' => 'char(32)',
			'paypal_response_reason_code' => 'char(32)',
			'paypalresponse_raw' => 'varchar(512)',
			'paypal_fullresponse' => 'text',
		);
		return $SQLfields;
	}

	/**
	 * @param $product
	 * @param $productDisplay
	 * @return bool
	 */
	function plgVmOnProductDisplayPayment($product, &$productDisplay) {
		//return;
		$vendorId = 1;
		if ($this->getPluginMethods($vendorId) === 0) {
			return FALSE;
		}

		foreach ($this->methods as $this->_currentMethod) {
			if ($this->_currentMethod->paypalproduct == 'exp') {
				$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod);
				$paypalInterface = $this->_loadPayPalInterface();

				$productDisplayHtml = $this->renderByLayout('expproduct',
				array(
				'paypalInterface' => $paypalInterface,
				'offer_credit' => $this->_currentMethod->offer_credit,
				'sandbox' => $this->_currentMethod->sandbox,
				'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id
				)
				);
				$productDisplay[] = $productDisplayHtml;


			}
		}
		return TRUE;
	}

	/**
	 * @param VirtuemartViewUser $user
	 * @param                    $html
	 * @param bool               $from_cart
	 * @return bool|null
	 */
	function plgVmDisplayLogin(VmView $view, &$html, $from_cart = FALSE) {

		// only to display it in the cart, not in list orders view
		if (!$from_cart) {
			return NULL;
		}

		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}

		$cart = VirtueMartCart::getCart();
		if ($this->getPluginMethods($cart->vendorId) === 0) {
			return FALSE;
		}

		if (!($selectedMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}
		if (!$this->isExpToken($selectedMethod, $cart) ) {
			$html .= $this->getExpressCheckoutHtml( $cart);
		}

		return;

	}

	/**
	 * @param $cart
	 * @param $payment_advertise
	 * @return bool|null
	 */
	function plgVmOnCheckoutAdvertise($cart, &$payment_advertise) {

		if ($this->getPluginMethods($cart->vendorId) === 0) {
			return FALSE;
		}
		if (!($selectedMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL;
		}
		if (isset($cart->cartPrices['salesPrice']) && $cart->cartPrices['salesPrice'] <= 0.0) {
			return NULL;
		}

		if (!$this->isExpToken($selectedMethod, $cart))  {
			$payment_advertise[] = $this->getExpressCheckoutHtml($cart, true);
		}

		return;
	}

/**
 * check if selected method is PayPalEC, and if a token exist
 */
	function isExpToken($selectedMethod, $cart) {

		if (!$this->selectedThisElement($selectedMethod->payment_element)) {
			return FALSE;
		}
		if ($selectedMethod->paypalproduct == 'exp') {
			$this->_currentMethod = $selectedMethod;

			$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod);
			$paypalExpInterface = $this->_loadPayPalInterface();
			$paypalExpInterface->loadCustomerData();

				$paypalExpInterface->setCart($cart);
				$paypalExpInterface->loadCustomerData();
				$token = $paypalExpInterface->customerData->getVar('token');
				$payerid = $paypalExpInterface->customerData->getVar('payer_id');
				if (empty($token) and empty($payerid)) {
					$paypalExpInterface->customerData->clear();
					$cart->virtuemart_paymentmethod_id = 0;
					$cart->setCartIntoSession();
					return false;
				}
				if (!empty($token) and !empty($payerid)) {
					return true;
				}
		}
		return false;
	}

	/**
	 * @param $cart
	 * @return null|string
	 */
	function getExpressCheckoutHtml( $cartm, $adv = false) {

		$html = '';
		foreach ($this->methods as $this->_currentMethod) {
			if ($this->_currentMethod->paypalproduct == 'exp') {

				if($adv and $this->_currentMethod->itemise_in_cart) continue;

				$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod);
				$paypalInterface = new PaypalHelperPayPalExp($this->_currentMethod, $this);
				$html .= $this->renderByLayout('expcheckout',
				array(
				'paypalInterface' => $paypalInterface,
				'offer_credit' => $this->_currentMethod->offer_credit,
				'sandbox' => $this->_currentMethod->sandbox,
				'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
				'method' => $this->_currentMethod
				)
				);
			}
		}
		return $html;
	}

	static function getPaymentCurrency (&$method, $selectedUserCurrency = false) {

		if (empty($method->payment_currency)) {
			$vendor_model = VmModel::getModel('vendor');
			$vendor = $vendor_model->getVendor($method->virtuemart_vendor_id);
			$method->payment_currency = $vendor->vendor_currency;
			return $method->payment_currency;
		} else {

			$vendor_model = VmModel::getModel( 'vendor' );
			$vendor_currencies = $vendor_model->getVendorAndAcceptedCurrencies( $method->virtuemart_vendor_id );

			if(!$selectedUserCurrency) {
				if($method->payment_currency == -1) {
					$mainframe = JFactory::getApplication();
					$selectedUserCurrency = $mainframe->getUserStateFromRequest( "virtuemart_currency_id", 'virtuemart_currency_id', vRequest::getInt( 'virtuemart_currency_id', $vendor_currencies['vendor_currency'] ) );
				} else {
					$selectedUserCurrency = $method->payment_currency;
				}
			}

			$vendor_currencies['all_currencies'] = explode(',', $vendor_currencies['all_currencies']);
			if(in_array($selectedUserCurrency,$vendor_currencies['all_currencies'])){
				$method->payment_currency = $selectedUserCurrency;
			} else {
				$method->payment_currency = $vendor_currencies['vendor_currency'];
			}

			return $method->payment_currency;
		}

	}



	/**
	 *
	 * @param $cart
	 * @param $order
	 * @return bool|null|void
	 */
	function plgVmConfirmedOrder($cart, $order) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}
		$html='';
		$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod,$order['details']['BT']->payment_currency_id);
		//$this->_currentMethod->payment_currency=$order['details']['BT']->user_currency_id;
		$email_currency = $this->getEmailCurrency($this->_currentMethod);

		$payment_name = $this->renderPluginName($this->_currentMethod, $order);

		$paypalInterface = $this->_loadPayPalInterface();

		$paypalInterface->debugLog('order number: ' . $order['details']['BT']->order_number, 'plgVmConfirmedOrder', 'debug');
		$paypalInterface->setCart($cart);
		$paypalInterface->setOrder($order);

		$paypalInterface->setTotal($order['details']['BT']->order_total);
		$paypalInterface->loadCustomerData();


		// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = $payment_name;
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['paypal_custom'] = $paypalInterface->getContext();
		$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
		$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
		$dbValues['payment_currency'] = $this->_currentMethod->payment_currency;
		$dbValues['email_currency'] = $email_currency;
		$dbValues['payment_order_total'] = $paypalInterface->getTotal();
		$dbValues['tax_id'] = $this->_currentMethod->tax_id;
		$this->storePSPluginInternalData($dbValues);
		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);

		$paypalInterface->debugLog('Amount/Currency stored ' . $dbValues['payment_order_total'].' paymentcurrency '.$dbValues['payment_currency'].' orderusercurrency'.$order['details']['BT']->user_currency_id, 'plgVmConfirmedOrder', 'message');

		if ($this->_currentMethod->paypalproduct == 'std') {
			$html = $paypalInterface->ManageCheckout();
			// 	2 = don't delete the cart, don't send email and don't redirect
			$cart->_confirmDone = FALSE;
			$cart->_dataValidated = FALSE;
			$cart->setCartIntoSession();
			vRequest::setVar('html', $html);

		} else {
			if ($this->_currentMethod->paypalproduct == 'exp') {
				$success = $paypalInterface->ManageCheckout();
				$response = $paypalInterface->getResponse();

			$payment = $this->_storePaypalInternalData(  $response, $order['details']['BT']->virtuemart_order_id, $cart->virtuemart_paymentmethod_id, $order['details']['BT']->order_number);

				if ($success) {
					$new_status = $paypalInterface->getNewOrderStatus();

					if ($this->_currentMethod->payment_type == '_xclick-subscriptions' || $this->_currentMethod->payment_type == '_xclick-payment-plan') {
						$profilesuccess = $paypalInterface->GetRecurringPaymentsProfileDetails($response['PROFILEID']);
						$response = $paypalInterface->getResponse();
					$this->_storePaypalInternalData(  $response, $order['details']['BT']->virtuemart_order_id, $cart->virtuemart_paymentmethod_id, $order['details']['BT']->order_number);
					}
					$this->customerData->clear();
					$returnValue = 1;
					$html = $this->renderByLayout('expresponse',
						array("method" => $this->_currentMethod,
							"success" => $success,
							"payment_name" => $payment_name,
							"response" => $response,
							"order" => $order));
					$cart->BT=0;
					$cart->ST=0;
					$cart->setCartIntoSession();
					return $this->processConfirmedOrderPaymentResponse($returnValue, $cart, $order, $html, $payment_name, $new_status);
				} else {
					$new_status = $this->_currentMethod->status_canceled;
					$returnValue = 2;
					$cart->virtuemart_paymentmethod_id = 0;
					$cart->setCartIntoSession();
					$this->customerData->clear();
					VmInfo('VMPAYMENT_PAYPAL_PAYMENT_NOT_VALID');
					$paypalInterface->debugLog($response, 'plgVmConfirmedOrder, response:', 'error');

					$app = JFactory::getApplication();
					$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&Itemid=' . vRequest::getInt('Itemid'), false));
				}


			} else {
				if ($this->_currentMethod->paypalproduct == 'api') {
					$this->setInConfirmOrder($cart);

					$success = $paypalInterface->ManageCheckout();
					$response = $paypalInterface->getResponse();
			$payment = $this->_storePaypalInternalData(  $response, $order['details']['BT']->virtuemart_order_id, $cart->virtuemart_paymentmethod_id, $order['details']['BT']->order_number);
					if ($success) {
						if ($this->_currentMethod->payment_action == 'Authorization' || $this->_currentMethod->payment_type == '_xclick-payment-plan') {
							$new_status = $this->_currentMethod->status_pending;
						} else {
							$new_status = $this->_currentMethod->status_success;
						}
						if ($this->_currentMethod->payment_type == '_xclick-subscriptions' || $this->_currentMethod->payment_type == '_xclick-payment-plan') {
							$profilesuccess = $paypalInterface->GetRecurringPaymentsProfileDetails($response['PROFILEID']);
							$response = $paypalInterface->getResponse();
					$this->_storePaypalInternalData(  $response, $order['details']['BT']->virtuemart_order_id, $cart->virtuemart_paymentmethod_id, $order['details']['BT']->order_number);
						}
						$this->customerData->clear();
						$returnValue = 1;
					} else {
						$cart->virtuemart_paymentmethod_id = 0;
						$cart->setCartIntoSession();
						$this->redirectToCart();
						return;
					}
//			$this->customerData->clear();
					$html = $this->renderByLayout('apiresponse', array('method' => $this->_currentMethod, 'success' => $success, 'payment_name' => $payment_name, 'responseData' => $response, "order" => $order));
					return $this->processConfirmedOrderPaymentResponse($returnValue, $cart, $order, $html, $payment_name, $new_status);
				} else {
					if ($this->_currentMethod->paypalproduct == 'hosted') {
						$paypalInterface->ManageCheckout();
						if ($this->_currentMethod->template == 'templateD') {
							jimport('joomla.environment.browser');
							$browser = JBrowser::getInstance();


							// this code is only called incase of iframe (templateD), in all other cases redirecttopayapl has been done
							$html = $this->renderByLayout('hostediframe', array("url" => $paypalInterface->response['EMAILLINK'],
								"isMobile" => $browser->isMobile()
							));
						}
						// 	2 = don't delete the cart, don't send email and don't redirect
						$cart->_confirmDone = FALSE;
						$cart->_dataValidated = FALSE;
						$cart->setCartIntoSession();
						vRequest::setVar('html', $html);
					} else {
						vmError('Unknown Paypal mode');
					}
				}
			}
		}
	}

	/**
	 * @param null $msg
	 */
	function redirectToCart ($msg = NULL) {
		if (!$msg) {
			$msg = vmText::_('VMPAYMENT_PAYPAL_ERROR_TRY_AGAIN');
		}
		$this->customerData->clear();
		$app = JFactory::getApplication();
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&Itemid=' . vRequest::getInt('Itemid'), false), $msg);
	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool|null
	 */
	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		$paymentCurrencyId = $this->getPaymentCurrency($this->_currentMethod);
	}

	function plgVmgetEmailCurrency($virtuemart_paymentmethod_id, $virtuemart_order_id, &$emailCurrencyId) {

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return FALSE;
		}
		/*if (!($payments = $this->_getPaypalInternalData($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}*/
		$emailCurrencyId = $this -> getEmailCurrency($method);
		return $emailCurrencyId;

	}

	function getEmailCurrency (&$method) {

		if(empty($method->email_currency) or $method->email_currency == 'vendor'){
			$vendor_model = VmModel::getModel('vendor');
			$vendor = $vendor_model->getVendor($method->virtuemart_vendor_id);
			$emailCurrencyId = $vendor->vendor_currency;
		} else if($method->email_currency == 'payment'){
			$emailCurrencyId = $this->getPaymentCurrency($method);
		}
		else if($method->email_currency == 'user'){

		}
		return $emailCurrencyId;
	}

	/**
	 * @param $html
	 * @return bool|null|string
	 */
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

		// the payment itself should send the parameter needed.
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);
		$expresscheckout = vRequest::getVar('expresscheckout', '');
		if ($expresscheckout) {
			return;

		}
		$order_number = vRequest::getString('on', 0);
		$vendorId = 0;
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return NULL;
		}
		if (!($payments = $this->getDatasByOrderNumber($order_number))) {
			return '';
		}

		vmLanguage::loadJLang('com_virtuemart');
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod,$order['details']['BT']->payment_currency_id);
		$payment_name = $this->renderPluginName($this->_currentMethod);
		$payment = end($payments);


		// to do: this
		$this->debugLog($payment, 'plgVmOnPaymentResponseReceived', 'debug', false);
		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		//$currency = CurrencyDisplay::getInstance('', $order['details']['BT']->order_currency);
		$currency = CurrencyDisplay::getInstance('', $order['details']['BT']->payment_currency_id);
		$paypal_data = new stdClass();
		if ($payment->paypal_fullresponse) {
			$paypal_data = json_decode($payment->paypal_fullresponse);
			$success = ($paypal_data->payment_status == 'Completed' or $paypal_data->payment_status == 'Pending');
		} else {
			$success = false;
		}

		$html = $this->renderByLayout($this->_currentMethod->paypalproduct . 'response', array("success" => $success,
			"payment_name" => $payment_name,
			"payment" => $paypal_data,
			"order" => $order,
			"currency" => $currency,
		));

		//We delete the old stuff
		// get the correct cart / session
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();
		return TRUE;
	}

	function plgVmOnUserPaymentCancel() {

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}

		$order_number = vRequest::getString('on', '');
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', '');
		if (empty($order_number) or empty($virtuemart_paymentmethod_id) or !$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL;
		}
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return NULL;
		}
		if (!($paymentTable = $this->getDataByOrderNumber($order_number))) {
			return NULL;
		}

		$method = $this->getPluginMethod($virtuemart_paymentmethod_id);
		$oM = VmModel::getModel('orders');
		$theorder = $oM->getOrder($virtuemart_order_id);
		if ($theorder['details']['BT']->order_status == $method->status_success || $theorder['details']['BT']->order_status == $method->status_pending ||   $theorder['details']['BT']->order_status == $method->status_capture) {
			return NULL;
		}

		VmInfo(vmText::_('VMPAYMENT_PAYPAL_PAYMENT_CANCELLED'));
		$session = JFactory::getSession();
		$return_context = $session->getId();
		if (strcmp($paymentTable->paypal_custom, $return_context) === 0) {
			$this->handlePaymentUserCancel($virtuemart_order_id);
		}
		return TRUE;
	}

	function plgVmOnPaymentNotification() {

		//https://developer.paypal.com/webapps/developer/docs/classic/ipn/integration-guide/IPNandPDTVariables/

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		$paypal_data = $_POST;

		//Recuring payment return rp_invoice_id instead of invoice
		if (array_key_exists('rp_invoice_id', $paypal_data)) {
			$paypal_data['invoice'] = $paypal_data['rp_invoice_id'];
		}
		if (!isset($paypal_data['invoice'])) {
			return FALSE;
		}

		$order_number = $paypal_data['invoice'];
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($paypal_data['invoice']))) {
			return FALSE;
		}

		if (!($payments = $this->getDatasByOrderNumber($order_number))) {
			return FALSE;
		}

		$this->_currentMethod = $this->getVmPluginMethod($payments[0]->virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod,$order['details']['BT']->payment_currency_id);

		$paypalInterface = $this->_loadPayPalInterface();
		$paypalInterface->setOrder($order);
		$paypalInterface->debugLog($paypal_data, 'PaymentNotification, paypal_data:', 'debug');
		$paypalInterface->debugLog($order_number, 'PaymentNotification, order_number:', 'debug');
		$paypalInterface->debugLog($payments[0]->virtuemart_paymentmethod_id, 'PaymentNotification, virtuemart_paymentmethod_id:', 'debug');
		$order_history = $paypalInterface->processIPN($paypal_data, $payments);
		if (!$order_history) {
			return false;
		} else {
			$this->_storePaypalInternalData( $paypal_data, $virtuemart_order_id, $payments[0]->virtuemart_paymentmethod_id, $order_number);
			$paypalInterface->debugLog('plgVmOnPaymentNotification order_number:' . $order_number . ' new_status:' . $order_history['order_status'], 'plgVmOnPaymentNotification', 'debug');

			$orderModel->updateStatusForOneOrder($virtuemart_order_id, $order_history, TRUE);
			//// remove vmcart
			if (isset($paypal_data['custom'])) {
				$this->emptyCart($paypal_data['custom'], $order_number);
				$paypalInterface->debugLog('plgVmOnPaymentNotification empty cart ', 'plgVmOnPaymentNotification', 'debug');
			}
		}
	}

	/*********************/
	/* Private functions */
	/*********************/
	private function _loadPayPalInterface() {

		static $paypalInterface = true;

		if(empty($this->_currentMethod->paypalproduct)) $this->_currentMethod->paypalproduct = $this->getPaypalProduct($this->_currentMethod);

		if($paypalInterface === true){
			if ($this->_currentMethod->paypalproduct == 'std') {
				$paypalInterface = new PaypalHelperPayPalStd($this->_currentMethod, $this);
			} else if ($this->_currentMethod->paypalproduct == 'api') {
				$paypalInterface = new PaypalHelperPayPalApi($this->_currentMethod, $this);
			} else if ($this->_currentMethod->paypalproduct == 'exp') {
				$paypalInterface = new PaypalHelperPayPalExp($this->_currentMethod, $this);
			} else if ($this->_currentMethod->paypalproduct == 'hosted') {
				$paypalInterface = new PaypalHelperPayPalHosted($this->_currentMethod, $this);
			} else if ($this->_currentMethod->paypalproduct == 'plus') {
				$paypalInterface = new PaypalHelperPayPalPlus($this->_currentMethod, $this);
			} else {
				Vmerror('Wrong paypal mode');
				return NULL;
			}
		} else {
			$paypalInterface->paypalPlugin = $this;
			$paypalInterface->_method = $this->_currentMethod;
		}

		return $paypalInterface;
	}

	private function _storePaypalInternalData( $paypal_data, $virtuemart_order_id, $virtuemart_paymentmethod_id, $order_number) {
		$paypalInterface = $this->_loadPayPalInterface();
		// get all know columns of the table
		$db = JFactory::getDBO();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery($query);
		$columns = $db->loadColumn(0);

		$post_msg = '';
		/*
        foreach ($paypal_data as $key => $value) {
            $post_msg .= $key . "=" . $value . "<br />";
            $table_key = 'paypal_response_' . $key;
            $table_key=strtolower($table_key);
            if (in_array($table_key, $columns)   ) {
                $response_fields[$table_key] = $value;
            }
        }
		*/
		//$response_fields = $paypalInterface->storePaypalInternalData($paypal_data);
		if (array_key_exists('PAYMENTINFO_0_PAYMENTSTATUS', $paypal_data)) {
			$response_fields['paypal_response_payment_status'] = $paypal_data['PAYMENTINFO_0_PAYMENTSTATUS'];
		} else {
			if (array_key_exists('PAYMENTSTATUS', $paypal_data)) {
				$response_fields['paypal_response_payment_status'] = $paypal_data['PAYMENTSTATUS'];
			} else {
				if (array_key_exists('PROFILESTATUS', $paypal_data)) {
					$response_fields['paypal_response_payment_status'] = $paypal_data['PROFILESTATUS'];
				} else {
					if (array_key_exists('STATUS', $paypal_data)) {
						$response_fields['paypal_response_payment_status'] = $paypal_data['STATUS'];
					}
				}
			}
		}


		if ($paypal_data) {
			$response_fields['paypal_fullresponse'] = json_encode($paypal_data);
		}
		$response_fields['order_number'] = $order_number;
		if (isset($paypal_data['invoice'])) {
			$response_fields['paypal_response_invoice'] = $paypal_data['invoice'];
		}

		$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
		$response_fields['virtuemart_paymentmethod_id'] = $virtuemart_paymentmethod_id;
		if (array_key_exists('custom', $paypal_data)) {
			$response_fields['paypal_custom'] = $paypal_data['custom'];
		}

		//$preload=true   preload the data here too preserve not updated data
		return $this->storePSPluginInternalData($response_fields, $this->_tablepkey, 0);

	}

	/**
	 * @param   int $virtuemart_order_id
	 * @param string $order_number
	 * @return mixed|string
	 */
	private function _getPaypalInternalData($virtuemart_order_id, $order_number = '') {
		if (empty($order_number)) {
			$orderModel = VmModel::getModel('orders');
			$order_number = $orderModel->getOrderNumber($virtuemart_order_id);
		}
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->_tablename . '` WHERE ';
		$q .= " `order_number` = '" . $order_number . "'";

		$db->setQuery($q);
		if (!($payments = $db->loadObjectList())) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		return $payments;
	}

	protected function renderPluginName($activeMethod) {
		$return = '';
		$plugin_name = $this->_psType . '_name';
		$plugin_desc = $this->_psType . '_desc';
		$description = '';
		// 		$params = new JParameter($plugin->$plugin_params);
		// 		$logo = $params->get($this->_psType . '_logos');
		$logosFieldName = $this->_psType . '_logos';
		$logos = $activeMethod->$logosFieldName;
		if (!empty($logos)) {
			$return = $this->displayLogos($logos) . ' ';
		}
		$pluginName = $return . '<span class="' . $this->_type . '_name">' . $activeMethod->$plugin_name . '</span>';
		if ($activeMethod->sandbox) {
			$pluginName .= ' <span style="color:red;font-weight:bold">Sandbox (' . $activeMethod->virtuemart_paymentmethod_id . ')</span>';
		}
		if (!empty($activeMethod->$plugin_desc)) {
			$pluginName .= '<span class="' . $this->_type . '_description">' . $activeMethod->$plugin_desc . '</span>';
		}
		$pluginName .= $this->displayExtraPluginNameInfo($activeMethod);
		return $pluginName;
	}

	function displayExtraPluginNameInfo($activeMethod) {
		$this->_currentMethod = $activeMethod;

		$paypalInterface = $this->_loadPayPalInterface();
		$paypalInterface->loadCustomerData();
		$extraInfo = $paypalInterface->displayExtraPluginInfo();

		return $extraInfo;

	}

	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $payment_method_id) {

		if (!$this->selectedThisByMethodId($payment_method_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($this->_currentMethod = $this->getVmPluginMethod($payment_method_id))) {
			return FALSE;
		}
		if (!($payments = $this->_getPaypalInternalData($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}

		//$html = $this->renderByLayout('orderbepayment', array($payments, $this->_psType));
		$html = '<table class="adminlist table" >' . "\n";
		$html .= $this->getHtmlHeaderBE();
		$code = "paypal_response_";
		$first = TRUE;
		foreach ($payments as $payment) {
			$html .= ' <tr class="row1"><td><strong>' . vmText::_('VMPAYMENT_PAYPAL_DATE') . '</strong></td><td align="left"><strong>' . $payment->created_on . '</strong></td></tr> ';
			// Now only the first entry has this data when creating the order
			if ($first) {
				$html .= $this->getHtmlRowBE('COM_VIRTUEMART_PAYMENT_NAME', $payment->payment_name);
				// keep that test to have it backwards compatible. Old version was deleting that column  when receiving an IPN notification
				if ($payment->payment_order_total and  $payment->payment_order_total != 0.00) {
					$html .= $this->getHtmlRowBE('COM_VIRTUEMART_TOTAL', $payment->payment_order_total . " " . shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));
				}

				$first = FALSE;
			} else {
				$paypalInterface = $this->_loadPayPalInterface();

				if (isset($payment->paypal_fullresponse) and !empty($payment->paypal_fullresponse)) {
					$paypal_data = json_decode($payment->paypal_fullresponse);
					$paypalInterface = $this->_loadPayPalInterface();
					$html .= $paypalInterface->onShowOrderBEPayment($paypal_data);

					$html .= '<tr><td></td><td>
    <a href="#" class="PayPalLogOpener" rel="' . $payment->id . '" >
        <div style="background-color: white; z-index: 100; right:0; display: none; border:solid 2px; padding:10px;" class="vm-absolute" id="PayPalLog_' . $payment->id . '">';

					foreach ($paypal_data as $key => $value) {
						$html .= ' <b>' . $key . '</b>:&nbsp;' . $value . '<br />';
					}

					$html .= ' </div>
        <span class="icon-nofloat vmicon vmicon-16-xml"></span>&nbsp;';
					$html .= vmText::_('VMPAYMENT_PAYPAL_VIEW_TRANSACTION_LOG');
					$html .= '  </a>';
					$html .= ' </td></tr>';
				} else {
					$html .= $paypalInterface->onShowOrderBEPaymentByFields($payment);
				}
			}


		}
		$html .= '</table>' . "\n";

		$doc = JFactory::getDocument();
		$js = "
	jQuery().ready(function($) {
		$('.PayPalLogOpener').click(function() {
			var logId = $(this).attr('rel');
			$('#PayPalLog_'+logId).toggle();
			return false;
		});
	});";
		$doc->addScriptDeclaration($js);
		return $html;

	}


	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 * @param VirtueMartCart $cart
	 * @param int $activeMethod
	 * @param array $cart_prices
	 * @return bool
	 */
	protected function checkConditions($cart, $activeMethod, $cart_prices) {

		//Check method publication start
		if ($activeMethod->publishup) {
			$nowDate = JFactory::getDate();
			$publish_up = JFactory::getDate($activeMethod->publishup);
			if ($publish_up->toUnix() > $nowDate->toUnix()) {
				return FALSE;
			}
		}
		if ($activeMethod->publishdown) {
			$nowDate = JFactory::getDate();
			$publish_down = JFactory::getDate($activeMethod->publishdown);
			if ($publish_down->toUnix() <= $nowDate->toUnix()) {
				return FALSE;
			}
		}


		if (!empty($activeMethod->virtuemart_shipmentmethod_ids)) {

			if (!is_array($activeMethod->virtuemart_shipmentmethod_ids)) {
				$activeMethod->virtuemart_shipmentmethod_ids = array($activeMethod->virtuemart_shipmentmethod_ids);
			}
			vmdebug('Check for shipment method ',$cart->virtuemart_shipmentmethod_id,$activeMethod->virtuemart_shipmentmethod_ids);
			if(empty($cart->virtuemart_shipmentmethod_id)){
				return false;
			} else {
				if(!in_array($cart->virtuemart_shipmentmethod_id,$activeMethod->virtuemart_shipmentmethod_ids)){
					vmdebug('Check for shipment method shipment method not allowed for paypal',$cart->virtuemart_shipmentmethod_id,$activeMethod->virtuemart_shipmentmethod_ids);
					return false;
				}
				vmdebug('Check for shipment method for paypal PASSED');
			}
		}

		$this->convert_condition_amount($activeMethod);

		$address = $cart->getST();

		$amount = $this->getCartAmount($cart_prices);
		$amount_cond = ($amount >= $activeMethod->min_amount AND $amount <= $activeMethod->max_amount
			OR
			($activeMethod->min_amount <= $amount AND ($activeMethod->max_amount == 0)));

		$countries = array();
		if (!empty($activeMethod->countries)) {
			if (!is_array($activeMethod->countries)) {
				$countries[0] = $activeMethod->countries;
			} else {
				$countries = $activeMethod->countries;
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
	 * @param $jplugin_id
	 * @return bool|mixed
	 */
	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {
		if ($jplugin_id != $this->_jid) {
			return FALSE;
		}
		$this->_currentMethod = $this->getPluginMethod(vRequest::getInt('virtuemart_paymentmethod_id'));
		if ($this->_currentMethod->published) {

			$sandbox = "";
			if ($this->_currentMethod->sandbox) {
				$sandbox = 'SANDBOX_';
				$sandbox_param = 'sandbox_';
			}


			if ($this->_currentMethod->paypalproduct == 'std') {
				if ($this->_currentMethod->sandbox) {
					$param = 'sandbox_merchant_email';
				} else {
					$param = 'paypal_merchant_email';
				}
				if (empty ($this->_currentMethod->$param)) {
					$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_' . $sandbox . 'MERCHANT'), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
					vmWarn($text);
				}
			}
			if ($this->_currentMethod->paypalproduct == 'exp' OR $this->_currentMethod->paypalproduct == 'hosted' OR $this->_currentMethod->paypalproduct == 'api') {
				$param = $sandbox_param . 'api_login_id';
				if (empty ($this->_currentMethod->$param)) {
					$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_' . $sandbox . 'USERNAME'), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
					vmWarn($text);
				}
				$param = $sandbox_param . 'api_password';
				if (empty ($this->_currentMethod->$param)) {
					$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_' . $sandbox . 'PASSWORD'), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
					vmWarn($text);
				}

				if ($this->_currentMethod->authentication == 'signature') {
					$param = $sandbox_param . 'api_signature';
					if (empty ($this->_currentMethod->$param)) {
						$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_' . $sandbox . 'SIGNATURE'), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
						vmWarn($text);
					}
				} else {
					$param = $sandbox_param . 'api_certificate';
					if (empty ($this->_currentMethod->$param)) {
						$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_' . $sandbox . 'CERTIFICATE'), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
						vmWarn($text);
					}
				}
			}
			if ($this->_currentMethod->paypalproduct == 'hosted') {
				$param = $sandbox_param . 'payflow_partner';
				if (empty ($this->_currentMethod->$param)) {
					$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_' . $sandbox . 'PAYFLOW_PARTNER'), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
					vmWarn($text);
				}
			}
			if ($this->_currentMethod->paypalproduct == 'exp' AND empty ($this->_currentMethod->expected_maxamount)) {
				$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_EXPECTEDMAXAMOUNT'), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
				vmWarn($text);
			}

		}

		return $this->onStoreInstallPluginTable($jplugin_id);
	}

	/**
	 *     * This event is fired after the payment method has been selected.
	 * It can be used to store additional payment info in the cart.
	 * @param VirtueMartCart $cart
	 * @param $msg
	 * @return bool|null
	 */
	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return null; // Another method was selected, do nothing
		}

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}

		$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod);
		$paypalInterface = $this->_loadPayPalInterface();
		$paypalInterface->setCart($cart);
		$paypalInterface->setTotal($cart->cartPrices['billTotal']);
		$paypalInterface->loadCustomerData();
		$paypalInterface->getExtraPluginInfo($this->_currentMethod);

		if (!$paypalInterface->validate()) {
			if ($this->_currentMethod->paypalproduct != 'api') {
				VmInfo('VMPAYMENT_PAYPAL_PAYMENT_NOT_VALID');
			}
			return false;
		}


		return true;
	}

	/*******************/
	/* Order cancelled */
	/* May be it is removed in VM 2.1
	/*******************/
	public function plgVmOnCancelPayment(&$order, $old_order_status) {
		return NULL;

	}

	/**
	 *  Order status changed
	 * @param $order
	 * @param $old_order_status
	 * @return bool|null
	 */
	public function plgVmOnUpdateOrderPayment(&$order, $old_order_status) {

		//Load the method
		if (!($this->_currentMethod = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if (!$this->selectedThisElement($this->_currentMethod -> payment_element)) {
			return NULL;
		}

		//Load only when updating status to shipped
		if ($order->order_status != $this->_currentMethod->status_capture AND $order->order_status != $this->_currentMethod->status_refunded) {
			//return null;
		}
		//Load the payments
		if (!($payments = $this->_getPaypalInternalData($order->virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}

		if ($this->_currentMethod->paypalproduct == 'std') {
			return null;
		}

		$oModel = VmModel::getModel('orders');
		$orderModelData = $oModel->getOrder($order->virtuemart_order_id);

		$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod,$orderModelData['details']['BT']->payment_currency_id);
		$payment = end($payments);
		if ($this->_currentMethod->payment_action == 'Authorization' and $order->order_status == $this->_currentMethod->status_capture) {
			$paypalInterface = $this->_loadPayPalInterface();
			$paypalInterface->setOrder($order);
			$paypalInterface->setTotal($order->order_total);
			$paypalInterface->loadCustomerData();
			if ($paypalInterface->DoCapture($payment)) {
				$paypalInterface->debugLog(vmText::_('VMPAYMENT_PAYPAL_API_TRANSACTION_CAPTURED'), 'plgVmOnUpdateOrderPayment', 'message', true);
				$this->_storePaypalInternalData(  $paypalInterface->getResponse(false), $order->virtuemart_order_id, $payment->virtuemart_paymentmethod_id, $order->order_number);
			}

		} elseif ($order->order_status == $this->_currentMethod->status_refunded OR $order->order_status == $this->_currentMethod->status_canceled) {
			$paypalInterface = $this->_loadPayPalInterface();
			$paypalInterface->setOrder($order);
			$paypalInterface->setTotal($order->order_total);
			$paypalInterface->loadCustomerData();
			if ($paypalInterface->RefundTransaction($payment)) {
				if ($this->_currentMethod->payment_type == '_xclick-subscriptions') {
					$paypalInterface->debugLog(vmText::_('VMPAYMENT_PAYPAL_SUBSCRIPTION_CANCELLED'), 'plgVmOnUpdateOrderPayment Refund', 'message', true);
				} else {
					//Mark the order as refunded
					// $order->order_status = $method->status_refunded;
					$paypalInterface->debugLog(vmText::_('VMPAYMENT_PAYPAL_API_TRANSACTION_REFUNDED'), 'plgVmOnUpdateOrderPayment Refund', 'message', true);
				}
				$this->_storePaypalInternalData( $paypalInterface->getResponse(false), $order->virtuemart_order_id, $payment->virtuemart_paymentmethod_id, $order->order_number);
			}
		}

		return true;
	}

	function plgVmOnUpdateOrderLinePayment(&$order) {
		// $xx=1;
	}

	/*******************/
	/* Credit Card API */
	/*******************/
	public function _displayCVVImages($method) {
		$cvv_images = $method->cvv_images;
		$img = '';
		if ($cvv_images) {
			$img = $this->displayLogos($cvv_images);
			$img = str_replace('"', "'", $img);
		}
		return $img;
	}


	/**
	 * * List payment methods selection
	 * @param VirtueMartCart $cart
	 * @param int $selected
	 * @param $htmlIn
	 * @return bool
	 */

	public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		if ($this->getPluginMethods($cart->vendorId) === 0) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(vmText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
				return false;
			} else {
				return false;
			}
		}
		$method_name = $this->_psType . '_name';

		$htmla = array();
		foreach ($this->methods as $this->_currentMethod) {
			if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {

				$html = '';
				$cartPrices=$cart->cartPrices;
				if (isset($this->_currentMethod->cost_method)) {
					$cost_method=$this->_currentMethod->cost_method;
				} else {
					$cost_method=true;
				}
				$methodSalesPrice = $this->setCartPrices($cart, $cartPrices, $this->_currentMethod, $cost_method);

				$this->_currentMethod->payment_currency = $this->getPaymentCurrency($this->_currentMethod);
				$this->_currentMethod->$method_name = $this->renderPluginName($this->_currentMethod);

				if (!$this->_currentMethod->itemise_in_cart and $this->_currentMethod->paypalproduct=='exp'){
					continue;
				}
				$html .= $this->getPluginHtml($this->_currentMethod, $selected, $methodSalesPrice);


				if ($this->_currentMethod->paypalproduct == 'api') {
					if (empty($this->_currentMethod->creditcards)) {
						$this->_currentMethod->creditcards = PaypalHelperPaypal::getPaypalCreditCards();
					} elseif (!is_array($this->_currentMethod->creditcards)) {
						$this->_currentMethod->creditcards = (array)$this->_currentMethod->creditcards;
					}
					$html .= $this->renderByLayout('creditcardform', array('creditcards' => $this->_currentMethod->creditcards,
						'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
						'method' => $this->_currentMethod,
						'sandbox' => $this->_currentMethod->sandbox,
						'customerData' => $this->customerData));
				}
				if ($this->_currentMethod->payment_type == '_xclick-auto-billing' && $this->_currentMethod->billing_max_amount_type == 'cust') {
					$html .= $this->renderByLayout('billingmax', array("method" => $this->_currentMethod, "customerData" => $this->customerData));
				}
				if ($this->_currentMethod->payment_type == '_xclick-subscriptions') {
					$paypalInterface = $this->_loadPayPalInterface();
					$html .= '<br/><span class="vmpayment_cardinfo">' . $paypalInterface->getRecurringProfileDesc() . '</span>';
				}
				if ($this->_currentMethod->payment_type == '_xclick-payment-plan') {
					$paypalInterface = $this->_loadPayPalInterface();
					$html .= '<br/><span class="vmpayment_cardinfo">' . $paypalInterface->getPaymentPlanDesc() . '</span>';
				}

				$htmla[] = $html;
			}
		}
		$htmlIn[] = $htmla;
		return true;

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
					$costDisplay = '<span class="'.$this->_type.'_cost fee"> ('.$fee.' +'.$costDisplay.")</span>";
				} else if($pluginSalesPrice<0) {
					$costDisplay = '<span class="'.$this->_type.'_cost discount"> ('.$discount.' -'.$costDisplay.")</span>";
				}
			} else {
				$costDisplay = '<span class="'.$this->_type.'_cost fee"> ('.$t.' +'.$costDisplay.")</span>";
			}
		}


		if ( $plugin->paypalproduct=='exp' and $plugin->itemise_in_cart ){
			$html = $this->renderByLayout('paymentitem', array("method" => $plugin));
		} else {
			$dynUpdate='';
			if( VmConfig::get('oncheckout_ajax',false)) {
				$dynUpdate=' data-dynamic-update="1" ';
			}

			$html = '<input type="radio" '.$dynUpdate.' name="' . $pluginmethod_id . '" id="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '"   value="' . $plugin->$pluginmethod_id . '" ' . $checked . ">\n"
			. '<label for="' . $this->_psType . '_id_' . $plugin->$pluginmethod_id . '">' . '<span class="' . $this->_type . '">' . $plugin->$pluginName . $costDisplay . "</span></label>\n";
		}


		return $html;
	}

	/**
	 * Validate payment on checkout
	 * @param VirtueMartCart $cart
	 * @return bool|null
	 */
	function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}

		//If PayPal express, make sure we have a valid token.
		//If not, redirect to PayPal to get one.
		$paypalInterface = $this->_loadPayPalInterface();

		$paypalInterface->setCart($cart);
		$cart->getCartPrices();
		$paypalInterface->setTotal($cart->cartPrices['billTotal']);

		// Here we only check for token, but should check for payer id ?
		$paypalInterface->loadCustomerData();
		$paypalInterface->getExtraPluginInfo($this->_currentMethod);
		$expressCheckout = vRequest::getVar('expresscheckout', '');
		if ($expressCheckout == 'cancel') {
			return true;
		}
		if (!$paypalInterface->validate()) {
			return false;
		}

		return true;
		//Validate amount
		//if ($totalInPaymentCurrency <= 0) {
		//	vmInfo (vmText::_ ('VMPAYMENT_PAYPAL_PAYMENT_AMOUNT_INCORRECT'));
		//	return FALSE;
		//}
	}


	/**
	 * For Express Checkout
	 * @param $type
	 * @param $name
	 * @param $render
	 * @return bool|null
	 */

	function plgVmOnSelfCallFE($type, $name, &$render) {

		if($name != $this->_name || $type != 'vmpayment') {
			return FALSE;
		}
		$action = vRequest::getCmd( 'action' );

		$virtuemart_paymentmethod_id = vRequest::getInt( 'pm' );
		//Load the method
		if(!($currentMethod = $this->getVmPluginMethod( $virtuemart_paymentmethod_id ))) {
			return NULL; // Another method was selected, do nothing
		}

		if($action == 'getPayPalCreditOffer' or $action == 'getPayPalOffer') {
			$this->_currentMethod = $currentMethod;
			$paypalInterface = new PaypalHelperPayPalExp( $this->_currentMethod, $this );

			if($action == 'getPayPalCreditOffer') {
				$link = 'https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html';
			} else {
				$exp = $paypalInterface->getExpressProduct();

				$link = $exp['link'];//'https://www.securecheckout.billmelater.com/paycapture-content/fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html';
			}


			$opts = array(
			'https' => array(
			'method' => "GET"
			)
			);

			$context = stream_context_create( $opts );
			$request = file_get_contents( $link, false, $context );

			if(!empty( $request )) {

				echo $request;
				jExit();
			}

			jExit();
		}

		if(!$this->selectedThisElement( $currentMethod->payment_element )) {
			return FALSE;
		}
		if ($action != 'SetExpressCheckout') {
			return false;
		}

		if(!class_exists( 'VirtueMartCart' )) {
			require(VMPATH_SITE.DS.'helpers'.DS.'cart.php');
		}
		$cart = VirtueMartCart::getCart();
		//$cart->prepareCartData();
		$cart->virtuemart_paymentmethod_id = $virtuemart_paymentmethod_id;
		$cart->setCartIntoSession();
		$this->_currentMethod = $currentMethod;
		$paypalInterface = $this->_loadPayPalInterface();
		$paypalInterface->setCart( $cart );
		$paypalInterface->setTotal( $cart->cartPrices['billTotal'] );
		$paypalInterface->loadCustomerData();
		// will perform $this->getExpressCheckoutDetails();
		$paypalInterface->getExtraPluginInfo( $this->_currentMethod );

		if(!$paypalInterface->validate()) {
			VmInfo( 'VMPAYMENT_PAYPAL_PAYMENT_NOT_VALID' );
			return false;
		} else {
			$app = JFactory::getApplication();
			$app->redirect( JRoute::_( 'index.php?option=com_virtuemart&view=cart&Itemid='.vRequest::getInt( 'Itemid' ), false ) );
		}


	}

	//Calculate the price (value, tax_id) of the selected method, It is called by the calculator
	//This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
	public function plgVmOnSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
		if (!($selectedMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}
		//$this->isExpToken($selectedMethod, $cart) ;
		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}


	/* backward compatibility */
	function getPaypalProduct() {
		if (isset($this->_currentMethod->paypalproduct) and !empty($this->_currentMethod->paypalproduct)) {
			return $this->_currentMethod->paypalproduct;
		} else {
			return 'std';
		}
	}


	// Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	// The plugin must check first if it is the correct type
	function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array(), &$paymentCounter) {
		return $this->onCheckAutomaticSelected($cart, $cart_prices, $paymentCounter);
	}

	// This method is fired when showing the order details in the frontend.
	// It displays the method-specific data.
	public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
	}

	// This method is fired when showing when priting an Order
	// It displays the the payment method-specific data.
	function plgVmonShowOrderPrintPayment($order_number, $method_id) {
		return $this->onShowOrderPrint($order_number, $method_id);
	}

	function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}

	function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
		return $this->setOnTablePluginParams($name, $id, $table);
	}

}

// No closing tag
