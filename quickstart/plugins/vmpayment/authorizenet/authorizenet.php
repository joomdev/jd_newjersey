<?php

/**
 *
 * @author Valérie Isaksen
 * @version $Id: authorize.php 5122 2011-12-18 22:24:49Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-2008 soeren, 2012-2015 The VirtueMart team and authors - All rights reserved.
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

if (!class_exists('Creditcard')) {
	require_once(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'creditcard.php');
}
if (!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

class plgVmpaymentAuthorizenet extends vmPSPlugin {

	private $_cc_name = '';
	private $_cc_type = '';
	private $_cc_number = '';
	private $_cc_cvv = '';
	private $_cc_expire_month = '';
	private $_cc_expire_year = '';
	private $_cc_valid = FALSE;
	private $_errormessage = array();
	protected $_authorizenet_params = array(
		"version" => "3.1",
		"delim_char" => ",",
		"delim_data" => "TRUE",
		"relay_response" => "FALSE",
		"encap_char" => "|",
	);
	public $approved;
	public $declined;
	public $error;
	public $held;

	const APPROVED = 1;
	const DECLINED = 2;
	const ERROR = 3;
	const HELD = 4;

	const AUTHORIZE_DEFAULT_PAYMENT_CURRENCY = "USD";

	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param object $subject The object to observe
	 * @param array $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	// instance of class
	function __construct(& $subject, $config) {

		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$this->tableFields = array_keys($this->getTableSQLFields());
		$varsToPush = $this->getVarsToPush();

		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);
	}

	protected function getVmPluginCreateTableSQL() {
		return $this->createTableSQL('Payment AuthorizeNet Table');
	}

	function getTableSQLFields() {

		$SQLfields = array(
			'id' => 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id' => 'int(1) UNSIGNED',
			'order_number' => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name' => 'varchar(5000)',
			'payment_order_total' => 'decimal(15,5) NOT NULL',
			'payment_currency' => 'smallint(1)',
			'return_context' => 'char(255)',
			'cost_per_transaction' => 'decimal(10,2)',
			'cost_percent_total' => 'char(10)',
			'tax_id' => 'smallint(1)',
			'authorizenet_response_authorization_code' => 'char(10)',
			'authorizenet_response_transaction_id' => 'char(128)',
			'authorizenet_response_response_code' => 'char(128)',
			'authorizenet_response_response_subcode' => 'char(13)',
			'authorizenet_response_response_reason_code' => 'decimal(10,2)',
			'authorizenet_response_response_reason_text' => 'text',
			'authorizenet_response_transaction_type' => 'char(50)',
			'authorizenet_response_account_number' => 'char(4)',
			'authorizenet_response_card_type' => 'char(128)',
			'authorizenet_response_card_code_response' => 'char(5)',
			'authorizenet_response_cavv_response' => 'char(1)',
			'authorizeresponse_raw' => 'text'
		);
		return $SQLfields;
	}

	/**
	 * This shows the plugin for choosing in the payment list of the checkout process.
	 *
	 * @author Valerie Cartan Isaksen
	 */
	function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {

		//JHTML::_ ('behavior.tooltip');

		if ($this->getPluginMethods($cart->vendorId) === 0) {
			if (empty($this->_name)) {
				$app = JFactory::getApplication();
				$app->enqueueMessage(vmText::_('COM_VIRTUEMART_CART_NO_' . strtoupper($this->_psType)));
				return FALSE;
			} else {
				return FALSE;
			}
		}

		$method_name = $this->_psType . '_name';

		vmLanguage::loadJLang('com_virtuemart', true);
		vmJsApi::jCreditCard();
		$htmla = array();
		$html = '';
		foreach ($this->methods as $this->_currentMethod) {
			if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {
				$cartPrices=$cart->cartPrices;
				$methodSalesPrice = $this->setCartPrices($cart, $cartPrices, $this->_currentMethod);
				$this->_currentMethod->$method_name = $this->renderPluginName($this->_currentMethod);
				$html = $this->getPluginHtml($this->_currentMethod, $selected, $methodSalesPrice);
				if ($selected == $this->_currentMethod->virtuemart_paymentmethod_id) {
					$this->_getAuthorizeNetFromSession();
				} else {
					$this->_cc_type = '';
					$this->_cc_number = '';
					$this->_cc_cvv = '';
					$this->_cc_expire_month = '';
					$this->_cc_expire_year = '';
				}

				if (empty($this->_currentMethod->creditcards)) {
					$this->_currentMethod->creditcards = self::getCreditCards();
				} elseif (!is_array($this->_currentMethod->creditcards)) {
					$this->_currentMethod->creditcards = (array)$this->_currentMethod->creditcards;
				}
				$creditCards = $this->_currentMethod->creditcards;
				$creditCardList = '';
				if ($creditCards) {
					$creditCardList = ($this->_renderCreditCardList($creditCards, $this->_cc_type, $this->_currentMethod->virtuemart_paymentmethod_id, FALSE));
				}
				$sandbox_msg = "";
				if ($this->_currentMethod->sandbox) {
					$sandbox_msg .= '<br />' . vmText::_('VMPAYMENT_AUTHORIZENET_SANDBOX_TEST_NUMBERS');
				}

				$cvv_images = $this->_displayCVVImages($this->_currentMethod);
				$html .= '<br /><span class="vmpayment_cardinfo">' . vmText::_('VMPAYMENT_AUTHORIZENET_COMPLETE_FORM') . $sandbox_msg . '
		    <table border="0" cellspacing="0" cellpadding="2" width="100%">
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="creditcardtype">' . vmText::_('VMPAYMENT_AUTHORIZENET_CCTYPE') . '</label>
		        </td>
		        <td>' . $creditCardList .
					'</td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="cc_type">' . vmText::_('VMPAYMENT_AUTHORIZENET_CCNUM') . '</label>
		        </td>
		        <td>
				<script type="text/javascript">
				//<![CDATA[  
				  function checkAuthorizeNet(id, el)
				   {
				     ccError=razCCerror(id);
					CheckCreditCardNumber(el.value, id);
					if (!ccError) {
					el.value=\'\';}
				   }
				//]]> 
				</script>
		        <input type="text" class="inputbox" id="cc_number_' . $this->_currentMethod->virtuemart_paymentmethod_id . '" name="cc_number_' . $this->_currentMethod->virtuemart_paymentmethod_id . '" value="' . $this->_cc_number . '"    autocomplete="off"   onchange="javascript:checkAuthorizeNet(' . $this->_currentMethod->virtuemart_paymentmethod_id . ', this);"  />
		        <div id="cc_cardnumber_errormsg_' . $this->_currentMethod->virtuemart_paymentmethod_id . '"></div>
		    </td>
		    </tr>
		    <tr valign="top">
		        <td nowrap width="10%" align="right">
		        	<label for="cc_cvv">' . vmText::_('VMPAYMENT_AUTHORIZENET_CVV2') . '</label>
		        </td>
		        <td>
		            <input type="text" class="inputbox" id="cc_cvv_' . $this->_currentMethod->virtuemart_paymentmethod_id . '" name="cc_cvv_' . $this->_currentMethod->virtuemart_paymentmethod_id . '" maxlength="4" size="5" value="' . $this->_cc_cvv . '" autocomplete="off" />

			<span class="hasTip" title="' . vmText::_('VMPAYMENT_AUTHORIZENET_WHATISCVV') . '::' . vmText::sprintf("VMPAYMENT_AUTHORIZENET_WHATISCVV_TOOLTIP", $cvv_images) . ' ">' .
					vmText::_('VMPAYMENT_AUTHORIZENET_WHATISCVV') . '
			</span></td>
		    </tr>
		    <tr>
		        <td nowrap width="10%" align="right">' . vmText::_('VMPAYMENT_AUTHORIZENET_EXDATE') . '</td>
		        <td> ';
				$html .= shopfunctions::listMonths('cc_expire_month_' . $this->_currentMethod->virtuemart_paymentmethod_id, $this->_cc_expire_month);
				$html .= " / ";
				$html .= '
				<script type="text/javascript">
				//<![CDATA[  
				  function changeDate(id, el)
				   {
				     var month = document.getElementById(\'cc_expire_month_\'+id); if(!CreditCardisExpiryDate(month.value,el.value, id))
					 {el.value=\'\';
					 month.value=\'\';}
				   }
				//]]> 
				</script>';

				$html .= shopfunctions::listYears('cc_expire_year_' . $this->_currentMethod->virtuemart_paymentmethod_id, $this->_cc_expire_year, NULL, null, " onchange=\"javascript:changeDate(" . $this->_currentMethod->virtuemart_paymentmethod_id . ", this);\" ");
				$html .= '<div id="cc_expiredate_errormsg_' . $this->_currentMethod->virtuemart_paymentmethod_id . '"></div>';
				$html .= '</td>  </tr>  	</table></span>';

				$htmla[] = $html;
			}
		}
		$htmlIn[] = $htmla;

		return TRUE;
	}

	/**

	 */
	static function getCreditCards() {
		return array(
			'Visa',
			'Mastercard',
			'AmericanExpress',
			'Discover',
			'DinersClub',
			'JCB',
		);

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
		$this->convert_condition_amount($method);
		$amount = $this->getCartAmount($cart_prices);
		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount_cond = ($amount >= $method->min_amount AND $amount <= $method->max_amount
			OR
			($method->min_amount <= $amount AND ($method->max_amount == 0)));
		if (!$amount_cond) {
			return FALSE;
		}
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
		if (count($countries) == 0 || in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			return TRUE;
		}

		return FALSE;
	}


	function _setAuthorizeNetIntoSession ()
	{
		if (!class_exists('vmCrypt')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmcrypt.php');
		}
		$session = JFactory::getSession();
		$sessionAuthorizeNet = new stdClass();
		// card information
		$sessionAuthorizeNet->cc_type = $this->_cc_type;
		$sessionAuthorizeNet->cc_number = vmCrypt::encrypt($this->_cc_number);
		$sessionAuthorizeNet->cc_cvv = vmCrypt::encrypt($this->_cc_cvv);
		$sessionAuthorizeNet->cc_expire_month = $this->_cc_expire_month;
		$sessionAuthorizeNet->cc_expire_year = $this->_cc_expire_year;
		$sessionAuthorizeNet->cc_valid = $this->_cc_valid;
		$session->set('authorizenet', json_encode($sessionAuthorizeNet), 'vm');
	}

	function _getAuthorizeNetFromSession() {
		if (!class_exists('vmCrypt')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'vmcrypt.php');
		}
		$session = JFactory::getSession();
		$authorizenetSession = $session->get('authorizenet', 0, 'vm');

		if (!empty($authorizenetSession)) {
			$authorizenetData = (object)json_decode($authorizenetSession,true);
			$this->_cc_type = $authorizenetData->cc_type;
			$this->_cc_number =  vmCrypt::decrypt($authorizenetData->cc_number);
			$this->_cc_cvv =  vmCrypt::decrypt($authorizenetData->cc_cvv);
			$this->_cc_expire_month = $authorizenetData->cc_expire_month;
			$this->_cc_expire_year = $authorizenetData->cc_expire_year;
			$this->_cc_valid = $authorizenetData->cc_valid;
		}
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

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}
		$this->_getAuthorizeNetFromSession();
		return $this->_validate_creditcard_data(TRUE);

	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 */
	function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {

		return parent::onStoreInstallPluginTable($jplugin_id);
	}

	/**
	 * This is for adding the input data of the payment method to the cart, after selecting
	 *
	 * @author Valerie Isaksen
	 *
	 * @param VirtueMartCart $cart
	 * @return null if payment not selected; true if card infos are correct; string containing the errors id cc is not valid
	 */
	public function plgVmOnSelectCheckPayment(VirtueMartCart $cart, &$msg) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return FALSE;
		}

		//$cart->creditcard_id = vRequest::getVar('creditcard', '0');
		$this->_cc_type = vRequest::getVar('cc_type_' . $cart->virtuemart_paymentmethod_id, '');
		$this->_cc_name = vRequest::getVar('cc_name_' . $cart->virtuemart_paymentmethod_id, '');
		$this->_cc_number = str_replace(" ", "", vRequest::getVar('cc_number_' . $cart->virtuemart_paymentmethod_id, ''));
		$this->_cc_cvv = vRequest::getVar('cc_cvv_' . $cart->virtuemart_paymentmethod_id, '');
		$this->_cc_expire_month = vRequest::getVar('cc_expire_month_' . $cart->virtuemart_paymentmethod_id, '');
		$this->_cc_expire_year = vRequest::getVar('cc_expire_year_' . $cart->virtuemart_paymentmethod_id, '');

		if (!$this->_validate_creditcard_data(TRUE)) {
			return FALSE; // returns string containing errors
		}
		$this->_setAuthorizeNetIntoSession();
		return TRUE;
	}

	public function plgVmOnSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$payment_name) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		$this->_getAuthorizeNetFromSession();
		$cart_prices['payment_tax_id'] = 0;
		$cart_prices['payment_value'] = 0;

		if (!$this->checkConditions($cart, $this->_currentMethod, $cart_prices)) {
			return FALSE;
		}
		$payment_name = $this->renderPluginName($this->_currentMethod);

		$this->setCartPrices($cart, $cart_prices, $this->_currentMethod);

		return TRUE;
	}

	/*
		 * @param $plugin plugin
		 */

	protected function renderPluginName($plugin) {

		$return = '';
		$plugin_name = $this->_psType . '_name';
		$plugin_desc = $this->_psType . '_desc';
		$description = '';
		// 		$params = new JParameter($plugin->$plugin_params);
		// 		$logo = $params->get($this->_psType . '_logos');
		$logosFieldName = $this->_psType . '_logos';
		$logos = $plugin->$logosFieldName;
		if (!empty($logos)) {
			$return = $this->displayLogos($logos) . ' ';
		}
		$sandboxWarning = '';
		if ($plugin->sandbox) {
			$sandboxWarning .= ' <span style="color:red;font-weight:bold">Sandbox (' . $plugin->virtuemart_paymentmethod_id . ')</span><br />';
		}
		if (!empty($plugin->$plugin_desc)) {
			$description = '<span class="' . $this->_type . '_description">' . $plugin->$plugin_desc . '</span>';
		}
		$this->_getAuthorizeNetFromSession();
		$extrainfo = $this->getExtraPluginNameInfo();
		$pluginName = $return . '<span class="' . $this->_type . '_name">' . $plugin->$plugin_name . '</span>' . $description;
		$pluginName .= $sandboxWarning . $extrainfo;
		return $pluginName;
	}

	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPaymentPlugin::plgVmOnShowOrderPaymentBE()
	 */
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_payment_id) {

		if (!($this->_currentMethod = $this->selectedThisByMethodId($virtuemart_payment_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
			return NULL;
		}
		vmLanguage::loadJLang('com_virtuemart');

		$html = '<table class="adminlist table">' . "\n";
		$html .= $this->getHtmlHeaderBE();
		$html .= $this->getHtmlRowBE('COM_VIRTUEMART_PAYMENT_NAME', $paymentTable->payment_name);
		$html .= $this->getHtmlRowBE('AUTHORIZENET_PAYMENT_ORDER_TOTAL', $paymentTable->payment_order_total . " " . self::AUTHORIZE_DEFAULT_PAYMENT_CURRENCY);
		$html .= $this->getHtmlRowBE('AUTHORIZENET_COST_PER_TRANSACTION', $paymentTable->cost_per_transaction);
		$html .= $this->getHtmlRowBE('AUTHORIZENET_COST_PERCENT_TOTAL', $paymentTable->cost_percent_total);
		$code = "authorizenet_response_";
		foreach ($paymentTable as $key => $value) {
			if (substr($key, 0, strlen($code)) == $code) {
				$html .= $this->getHtmlRowBE($key, $value);
			}
		}
		$html .= '</table>' . "\n";
		return $html;
	}

	/**
	 * Reimplementation of vmPaymentPlugin::plgVmOnConfirmedOrderStorePaymentData()
	 */

	/**
	 * Reimplementation of vmPaymentPlugin::plgVmOnConfirmedOrder()
	 *
	 * @link http://www.authorize.net/support/AIM_guide.pdf
	 * Credit Cards Test Numbers
	 * Visa Test Account           4007000000027
	 * Amex Test Account           370000000000002
	 * Master Card Test Account    6011000000000012
	 * Discover Test Account       5424000000000015
	 * @author Valerie Isaksen
	 */
	function plgVmConfirmedOrder(VirtueMartCart $cart, $order) {

		if (!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		$this->setInConfirmOrder($cart);
		$usrBT = $order['details']['BT'];
		$usrST = ((isset($order['details']['ST'])) ? $order['details']['ST'] : '');
		$session = JFactory::getSession();
		$return_context = $session->getId();

		$payment_currency_id = shopFunctions::getCurrencyIDByName(self::AUTHORIZE_DEFAULT_PAYMENT_CURRENCY);
		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $payment_currency_id);
		$cd = CurrencyDisplay::getInstance($cart->pricesCurrency);

		// Set up data
		$formdata = array();
		$formdata = array_merge($this->_setHeader(), $formdata);
		$formdata = array_merge($this->_setResponseConfiguration(), $formdata);
		$formdata = array_merge($this->_setBillingInformation($usrBT), $formdata);
		if (!empty($usrST)) {
			$formdata = array_merge($this->_setShippingInformation($usrST), $formdata);
		}
		$formdata = array_merge($this->_setTransactionData($order['details']['BT'], $totalInPaymentCurrency['value']), $formdata);
		$formdata = array_merge($this->_setMerchantData(), $formdata);
		// prepare the array to post
		$poststring = '';
		foreach ($formdata AS $key => $val) {
			$poststring .= urlencode($key) . "=" . urlencode($val) . "&";
		}
		$poststring = rtrim($poststring, "& ");

		// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$dbValues['payment_method_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
		$dbValues['return_context'] = $return_context;
		$dbValues['payment_name'] = parent::renderPluginName($this->_currentMethod);
		$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
		$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
		$dbValues['payment_order_total'] = $totalInPaymentCurrency['value'];
		$dbValues['payment_currency'] = $payment_currency_id;
		$this->debugLog("before store", "plgVmConfirmedOrder", 'debug');

		$this->storePSPluginInternalData($dbValues);

		// send a request
		$response = $this->_sendRequest($poststring);

		$this->debugLog($response, "plgVmConfirmedOrder", 'debug');


		$authnet_values = array(); // to check the values???
		// evaluate the response
		$html = $this->_handleResponse($response, $authnet_values, $order, $dbValues['payment_name']);
		if ($this->error) {
			$new_status = $this->_currentMethod->payment_declined_status;
			$this->_handlePaymentCancel($order['details']['BT']->virtuemart_order_id, $html);
			return; // will not process the order
		} else {
			if ($this->approved) {
				$this->_clearAuthorizeNetSession();
				$new_status = $this->_currentMethod->payment_approved_status;
			} else {
				if ($this->declined) {
					vRequest::setVar('html', $html);
					$new_status = $this->_currentMethod->payment_declined_status;
					$this->_handlePaymentCancel($order['details']['BT']->virtuemart_order_id, $html);
					return;
				} else {
					if ($this->held) {
						$this->_clearAuthorizeNetSession();
						$new_status = $this->_currentMethod->payment_held_status;
					}
				}
			}
		}
		$modelOrder = VmModel::getModel('orders');
		$order['order_status'] = $new_status;
		$order['customer_notified'] = 1;
		$order['comments'] = '';
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order, TRUE);

		//We delete the old stuff
		$cart->emptyCart();
		vRequest::setVar('html', $html);
	}

	function _handlePaymentCancel($virtuemart_order_id, $html) {

		if (!class_exists('VirtueMartModelOrders')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		}
		$modelOrder = VmModel::getModel('orders');
		//$modelOrder->remove(array('virtuemart_order_id' => $virtuemart_order_id));
		// error while processing the payment
		$mainframe = JFactory::getApplication();
		vmWarn($html);
		$mainframe->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&task=editpayment', FALSE), vmText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
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
		$this->_currentMethod->payment_currency = self::AUTHORIZE_DEFAULT_PAYMENT_CURRENCY;

		if (!class_exists('VirtueMartModelVendor')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		}
		$vendorId = 1; //VirtueMartModelVendor::getLoggedVendor();
		$db = JFactory::getDBO();

		$q = 'SELECT   `virtuemart_currency_id` FROM `#__virtuemart_currencies` WHERE `currency_code_3`= "' . self::AUTHORIZE_DEFAULT_PAYMENT_CURRENCY . '"';
		$db->setQuery($q);
		$paymentCurrencyId = $db->loadResult();
	}

	function _clearAuthorizeNetSession() {

		$session = JFactory::getSession();
		$session->clear('authorizenet', 'vm');
	}

	/**
	 * renderPluginName
	 * Get the name of the payment method
	 *
	 * @author Valerie Isaksen
	 * @param  $payment
	 * @return string Payment method name
	 */
	function getExtraPluginNameInfo() {

		$creditCardInfos = '';
		if ($this->_validate_creditcard_data(FALSE)) {
			$cc_number = "**** **** **** " . substr($this->_cc_number, -4);
			$creditCardInfos .= '<br /><span class="vmpayment_cardinfo">' . vmText::_('VMPAYMENT_AUTHORIZENET_CCTYPE') . $this->_cc_type . '<br />';
			$creditCardInfos .= vmText::_('VMPAYMENT_AUTHORIZENET_CCNUM') . $cc_number . '<br />';
			$creditCardInfos .= vmText::_('VMPAYMENT_AUTHORIZENET_CVV2') . '****' . '<br />';
			$creditCardInfos .= vmText::_('VMPAYMENT_AUTHORIZENET_EXDATE') . $this->_cc_expire_month . '/' . $this->_cc_expire_year;
			$creditCardInfos .= "</span>";
		}
		return $creditCardInfos;
	}

	/**
	 * Creates a Drop Down list of available Creditcards
	 *
	 * @author Valerie Isaksen
	 */
	function _renderCreditCardList($creditCards, $selected_cc_type, $paymentmethod_id, $multiple = FALSE, $attrs = '') {

		$idA = $id = 'cc_type_' . $paymentmethod_id;
		//$options[] = JHTML::_('select.option', '', vmText::_('VMPAYMENT_AUTHORIZENET_SELECT_CC_TYPE'), 'creditcard_type', $name);
		if (!is_array($creditCards)) {
			$creditCards = (array)$creditCards;
		}
		foreach ($creditCards as $creditCard) {
			$options[] = JHTML::_('select.option', $creditCard, vmText::_('VMPAYMENT_AUTHORIZENET_' . strtoupper($creditCard)));
		}
		if ($multiple) {
			$attrs = 'multiple="multiple"';
			$idA .= '[]';
		}
		return JHTML::_('select.genericlist', $options, $idA, $attrs, 'value', 'text', $selected_cc_type);
	}

	/*
		 * validate_creditcard_data
		 * @author Valerie isaksen
		 */

	function _validate_creditcard_data($enqueueMessage = TRUE) {
		static $force=true;
		if(empty($this->_cc_number) and empty($this->_cc_cvv)){
			return false;
		}
		$html = '';
		$this->_cc_valid = true;//!empty($this->_cc_number) and !empty($this->_cc_cvv) and !empty($this->_cc_expire_month) and !empty($this->_cc_expire_year);

		if (!empty($this->_cc_number) and !Creditcard::validate_credit_card_number($this->_cc_type, $this->_cc_number)) {
			$this->_errormessage[] = 'VMPAYMENT_AUTHORIZENET_CARD_NUMBER_INVALID';
			$this->_cc_valid = FALSE;
		}

		if (!Creditcard::validate_credit_card_cvv($this->_cc_type, $this->_cc_cvv)) {
			$this->_errormessage[] = 'VMPAYMENT_AUTHORIZENET_CARD_CVV_INVALID';
			$this->_cc_valid = FALSE;
		}
		if (!Creditcard::validate_credit_card_date($this->_cc_type, $this->_cc_expire_month, $this->_cc_expire_year)) {
			$this->_errormessage[] = 'VMPAYMENT_AUTHORIZENET_CARD_EXPIRATION_DATE_INVALID';
			$this->_cc_valid = FALSE;
		}
		if (!$this->_cc_valid) {
			//$html.= "<ul>";
			foreach ($this->_errormessage as $msg) {
				//$html .= "<li>" . vmText::_($msg) . "</li>";
				$html .= vmText::_($msg) . "<br/>";
			}
			//$html.= "</ul>";
		}
		if (!$this->_cc_valid && $enqueueMessage && $force) {
			vmWarn($html);
			$force=false;
		}

		return $this->_cc_valid;
	}

	function _getLoginId() {

		return trim($this->_currentMethod->sandbox ? $this->_currentMethod->sandbox_login_id : $this->_currentMethod->login_id);
	}

	function _getTransactionKey() {

		return trim($this->_currentMethod->sandbox ? $this->_currentMethod->sandbox_transaction_key : $this->_currentMethod->transaction_key);
	}

	/**
	 * Gets the gateway Authorize.net URL
	 *
	 * @return string
	 * @access protected
	 */
	function _getPostUrl() {

		if ($this->_currentMethod->sandbox) {
			if (isset($this->_currentMethod->sandbox_hostname)) {
				return $this->_currentMethod->sandbox_hostname;
			} else {
				return 'https://test.authorize.net/gateway/transact.dll';
			}
		} else {
			if (isset($this->_currentMethod->hostname)) {
				return $this->_currentMethod->hostname;
			} else {
				return 'https://secure.authorize.net/gateway/transact.dll';
			}
		}
	}

	function _recurringPayment() {

		return ''; //$params->get('recurring_payment', '0');
	}

	/**
	 * _getFormattedDate
	 *
	 *
	 */
	function _getFormattedDate($month, $year) {

		return sprintf('%02d-%04d', $month, $year);
	}

	function _setHeader() {

		return $this->_authorizenet_params;
	}

	function _setMerchantData() {

		return array(
			'x_login' => $this->_getLoginId(),
			'x_tran_key' => $this->_getTransactionKey(),
			'x_relay_response' => 'FALSE'
		);
	}

	function _setResponseConfiguration() {

		return array(
			'x_delim_data' => 'TRUE',
			'x_delim_char' => '|',
			'x_relay_response' => 'FALSE'
		);
	}

	function _getfield($string, $length) {
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		return ShopFunctionsF::vmSubstr($string, 0, $length);
	}

	function _setBillingInformation($usrBT) {
		if (!class_exists('ShopFunctions'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'shopfunctions.php');
		$clientIp= ShopFunctions::getClientIP();
		// Customer Name and Billing Address
		return array(
			'x_email' => isset($usrBT->email) ? $this->_getField($usrBT->email, 100) : '', //get email
			'x_first_name' => isset($usrBT->first_name) ? $this->_getField($usrBT->first_name, 50) : '',
			'x_last_name' => isset($usrBT->last_name) ? $this->_getField($usrBT->last_name, 50) : '',
			'x_company' => isset($usrBT->company) ? $this->_getField($usrBT->company, 50) : '',
			'x_address' => isset($usrBT->address_1) ? $this->_getField($usrBT->address_1, 60) : '',
			'x_city' => isset($usrBT->city) ? $this->_getField($usrBT->city, 40) : '',
			'x_zip' => isset($usrBT->zip) ? $this->_getField($usrBT->zip, 20) : '',
			'x_state' => isset($usrBT->virtuemart_state_id) ? $this->_getField(ShopFunctions::getStateByID($usrBT->virtuemart_state_id), 40) : '',
			'x_country' => isset($usrBT->virtuemart_country_id) ? $this->_getField(ShopFunctions::getCountryByID($usrBT->virtuemart_country_id), 60) : '',
			'x_phone' => isset($usrBT->phone_1) ? $this->_getField($usrBT->phone_1, 25) : '',
			'x_fax' => isset($usrBT->fax) ? $this->_getField($usrBT->fax, 25) : '',
			'x_customer_ip' => $clientIp,
		);
	}

	function _setShippingInformation($usrST) {

		// Customer Name and Billing Address
		return array(
			'x_ship_to_first_name' => isset($usrST->first_name) ? $this->_getField($usrST->first_name, 50) : '',
			'x_ship_to_last_name' => isset($usrST->first_name) ? $this->_getField($usrST->last_name, 50) : '',
			'x_ship_to_company' => isset($usrST->company) ? $this->_getField($usrST->company, 50) : '',
			'x_ship_to_address' => isset($usrST->first_name) ? $this->_getField($usrST->address_1, 60) : '',
			'x_ship_to_city' => isset($usrST->city) ? $this->_getField($usrST->city, 40) : '',
			'x_ship_to_zip' => isset($usrST->zip) ? $this->_getField($usrST->zip, 20) : '',
			'x_ship_to_state' => isset($usrST->virtuemart_state_id) ? $this->_getField(ShopFunctions::getStateByID($usrST->virtuemart_state_id), 40) : '',
			'x_ship_to_country' => isset($usrST->virtuemart_country_id) ? $this->_getField(ShopFunctions::getCountryByID($usrST->virtuemart_country_id), 60) : '',
		);
	}

	function _setTransactionData($orderDetails, $totalInPaymentCurrency) {

		// backward compatible
		if (isset($this->_currentMethod->xtype)) {
			$xtype = $this->_currentMethod->xtype;
		} else {
			$xtype = 'AUTH_CAPTURE';
		}
		return array(
			'x_amount' => $totalInPaymentCurrency,
			'x_invoice_num' => $orderDetails->order_number,
			'x_method' => 'CC',
			'x_type' => $xtype,
			'x_recurring_billing' => 0, //$this->_recurringPayment($params),
			'x_card_num' => $this->_cc_number,
			'x_card_code' => $this->_cc_cvv,
			'x_exp_date' => $this->_getFormattedDate($this->_cc_expire_month, $this->_cc_expire_year)
		);
	}

	/**
	 * _sendRequest
	 * Posts the request to AuthorizeNet & returns response using curl
	 *
	 * @author Valerie Isaksen
	 * @param string $url
	 * @param string $content
	 *
	 */
	function _sendRequest($post_string) {
		$post_url = $this->_getPostUrl();
		$this->debugLog($this->removeCC($post_string), "_sendRequest", 'debug');

		$curl_request = curl_init($post_url);
		//Added the next line to fix SSL verification issue (CURL error verifying the far end SSL Cert)
		curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl_request, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($curl_request, CURLOPT_HEADER, 0);
		curl_setopt($curl_request, CURLOPT_TIMEOUT, 45);
		curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($curl_request, CURLOPT_POST, 1);
		if (preg_match('/xml/', $post_url)) {
			curl_setopt($curl_request, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		}

		$response = curl_exec($curl_request);

		if ($curl_error = curl_error($curl_request)) {
			$this->debugLog($curl_error, '_sendRequest CURL error', 'error');
			vmError('Authorize.net: ' . "----CURL ERROR---- " . $curl_error);
		}

		curl_close($curl_request);

		return $response;
	}

	/**
	 * Proceeds the simple payment
	 * http://developer.authorize.net/guides/AIM/wwhelp/wwhimpl/js/html/wwhelp.htm#href=4_TransResponse.6.4.html
	 * @param string $resp
	 * @param array $submitted_values
	 * @return object Message object
	 *
	 */
	function _handleResponse($response, $submitted_values, $order, $payment_name) {

		$delimiter = $this->_authorizenet_params['delim_char'];
		$encap_char = $this->_authorizenet_params['encap_char'];

		if ($response) {
			// Split Array
			if ($encap_char) {
				//$response_array = explode($encap_char . $delimiter . $encap_char, substr($response, 1, -1));
				$response_array = explode($encap_char, $response);
			} else {
				$response_array = explode($delimiter, $response);
			}

			/**
			 * If AuthorizeNet doesn't return a delimited response.
			 */
			if (count($response_array) < 10) {
				$this->approved = FALSE;
				$this->error = TRUE;
				$error_message = vmText::_('VMPAYMENT_AUTHORIZENET_UNKNOWN') . $response;
				$this->debugLog($error_message, 'getOrderIdByOrderNumber', 'error');
				return $error_message;
			}

			$authorizeNetResponse['response_code'] = $response_array[0];
			$this->approved = ($authorizeNetResponse['response_code'] == self::APPROVED);
			$this->declined = ($authorizeNetResponse['response_code'] == self::DECLINED);
			$this->error = ($authorizeNetResponse['response_code'] == self::ERROR);
			$this->held = ($authorizeNetResponse['response_code'] == self::HELD);
			$authorizeNetResponse['response_subcode'] = $response_array[1];
			$authorizeNetResponse['response_reason_code'] = $response_array[2];
			$authorizeNetResponse['response_reason_text'] = $response_array[3];
			$authorizeNetResponse['authorization_code'] = $response_array[4];
			$authorizeNetResponse['avs_response'] = $response_array[5]; //Address Verification Service
			$authorizeNetResponse['transaction_id'] = $response_array[6];
			$authorizeNetResponse['invoice_number'] = $response_array[7];
			$authorizeNetResponse['description'] = $response_array[8];
			if ($this->approved) {
				$authorizeNetResponse['amount'] = $response_array[9];
				$authorizeNetResponse['method'] = $response_array[10];
				$authorizeNetResponse['transaction_type'] = $response_array[11];
				$authorizeNetResponse['customer_id'] = $response_array[12];
				$authorizeNetResponse['first_name'] = $response_array[13];
				$authorizeNetResponse['last_name'] = $response_array[14];
				$authorizeNetResponse['company'] = $response_array[15];
				$authorizeNetResponse['address'] = $response_array[16];
				$authorizeNetResponse['city'] = $response_array[17];
				$authorizeNetResponse['state'] = $response_array[18];
				$authorizeNetResponse['zip_code'] = $response_array[19];
				$authorizeNetResponse['country'] = $response_array[20];
				$authorizeNetResponse['phone'] = $response_array[21];
				$authorizeNetResponse['fax'] = $response_array[22];
				$authorizeNetResponse['email_address'] = $response_array[23];
				$authorizeNetResponse['ship_to_first_name'] = $response_array[24];
				$authorizeNetResponse['ship_to_last_name'] = $response_array[25];
				$authorizeNetResponse['ship_to_company'] = $response_array[26];
				$authorizeNetResponse['ship_to_address'] = $response_array[27];
				$authorizeNetResponse['ship_to_city'] = $response_array[28];
				$authorizeNetResponse['ship_to_state'] = $response_array[29];
				$authorizeNetResponse['ship_to_zip_code'] = $response_array[30];
				$authorizeNetResponse['ship_to_country'] = $response_array[31];
				$authorizeNetResponse['tax'] = $response_array[32];
				$authorizeNetResponse['duty'] = $response_array[33];
				$authorizeNetResponse['freight'] = $response_array[34];
				$authorizeNetResponse['tax_exempt'] = $response_array[35];
				$authorizeNetResponse['purchase_order_number'] = $response_array[36];
				$authorizeNetResponse['md5_hash'] = $response_array[37];
				$authorizeNetResponse['card_code_response'] = $response_array[38];
				$authorizeNetResponse['cavv_response'] = $response_array[39]; //// cardholder_authentication_verification_response
				$authorizeNetResponse['account_number'] = $response_array[50];
				$authorizeNetResponse['card_type'] = $response_array[51];
				$authorizeNetResponse['split_tender_id'] = $response_array[52];
				$authorizeNetResponse['requested_amount'] = $response_array[53];
				$authorizeNetResponse['balance_on_card'] = $response_array[54];
			}


			if ($this->error or $this->declined) {
				// Prepare data that should be stored in the database
				$dbValues['authorizenet_response_response_code'] = $authorizeNetResponse['response_code'];
				$dbValues['authorizenet_response_response_subcode'] = $authorizeNetResponse['response_subcode'];
				$dbValues['authorizenet_response_response_reason_code'] = $authorizeNetResponse['response_reason_code'];
				$dbValues['authorizenet_response_response_reason_text'] = $authorizeNetResponse['response_reason_text'];
				//$this->storePSPluginInternalData($dbValues, 'id', true);
				$html = vmText::sprintf('VMPAYMENT_AUTHORIZENET_ERROR', $authorizeNetResponse['response_reason_text'], $authorizeNetResponse['response_code']) . "<br />";
				$this->debugLog($html, '_handleResponse PAYMENT DECLINED', 'message');
				return $html;
			}



			$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($authorizeNetResponse['invoice_number']);
			if (!$virtuemart_order_id) {
				$this->approved = FALSE;
				$this->error = TRUE;
				$this->debugLog(vmText::sprintf('VMPAYMENT_AUTHORIZENET_NO_ORDER_NUMBER', $authorizeNetResponse['invoice_number']), 'getOrderIdByOrderNumber', 'error');
				$html = vmText::sprintf('VMPAYMENT_AUTHORIZENET_ERROR', $authorizeNetResponse['response_reason_text'], $authorizeNetResponse['response_code']) . "<br />";
				$this->debugLog($html, '_handleResponse PAYMENT DECLINED', 'message');

				return $html;
			}

		} else {
			$this->approved = FALSE;
			$this->error = TRUE;
			$this->debugLog(vmText::_('VMPAYMENT_AUTHORIZENET_CONNECTING_ERROR'), '_handleResponse', 'error');
			return vmText::_('VMPAYMENT_AUTHORIZENET_CONNECTING_ERROR');
		}
		// Prep
		// get all know columns of the table
		$db = JFactory::getDBO();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery($query);
		$columns = $db->loadColumn(0);

		foreach ($authorizeNetResponse as $key => $value) {
			$table_key = 'authorizenet_response_' . $key;
			if (in_array($table_key, $columns)) {
				$response_fields[$table_key] = $value;
			}
		}
		$response_fields['virtuemart_order_id'] = $virtuemart_order_id;
		$response_fields['invoice_number'] = $authorizeNetResponse['invoice_number'];
		$response_fields['authorizeresponse_raw'] = $response;

		$this->storePSPluginInternalData($response_fields, 'virtuemart_order_id', TRUE);

		$html = '<table class="adminlist table">' . "\n";
		$html .= $this->getHtmlRow('AUTHORIZENET_PAYMENT_NAME', $payment_name);
		$html .= $this->getHtmlRow('AUTHORIZENET_ORDER_NUMBER', $authorizeNetResponse['invoice_number']);
		$html .= $this->getHtmlRow('AUTHORIZENET_AMOUNT', $authorizeNetResponse['amount'] . ' ' . self::AUTHORIZE_DEFAULT_PAYMENT_CURRENCY);
		//$html .= $this->getHtmlRow('AUTHORIZENET_RESPONSE_AUTHORIZATION_CODE', $authorizeNetResponse['authorization_code']);
		if ($authorizeNetResponse['transaction_id']) {
			$html .= $this->getHtmlRow('AUTHORIZENET_RESPONSE_TRANSACTION_ID', $authorizeNetResponse['transaction_id']);
		}
		$html .= '</table>' . "\n";
		$this->debugLog(vmText::_('VMPAYMENT_AUTHORIZENET_ORDER_NUMBER') . " " . $authorizeNetResponse['invoice_number'] . ' payment approved', '_handleResponse', 'debug');

		return $html;
	}

	/**
	 * @param $method
	 * @return html|mixed|string
	 */
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
	 * We must reimplement this triggers for joomla 1.7
	 */

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

		$return = $this->onCheckAutomaticSelected($cart, $cart_prices);
		if (isset($return)) {
			return 0;
		} else {
			return NULL;
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
	protected function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {

		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
		return TRUE;
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
	function plgVmOnShowOrderPrintPayment($order_number, $method_id) {

		return parent::onShowOrderPrint($order_number, $method_id);
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

	function removeCC($data) {
		$keys = array('x_card_num=', 'x_card_code=');
		foreach ($keys as $key) {
			preg_match('/' . $key . '[^&]+&/i', $data, $result);
			if (is_array($result) and isset($result[0])) {
				$field = $result[0];
				$old_value = substr($field, strlen($key), -1);
				$new_value = str_repeat('*', strlen($old_value));
				$data = str_replace($old_value, $new_value, $data);
			}
		}

		return $data;
	}
}

// No closing tag
