<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: amazon.php 8585 2014-11-25 11:11:13Z alatak $
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */
if(!class_exists('VmConfig')) {
	require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/config.php');
}

if(!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

defined ('AMAZON_IGNORE_SSL') or define ('AMAZON_IGNORE_SSL', 0);
/**
 * Class plgVmpaymentAmazon
 * payments.amazon.co.uk
 * payments.amazon.de
 * https://sellercentral.amazon.co.uk
 */
class plgVmpaymentAmazon extends vmPSPlugin {

	// instance of class

	static $widgetScriptLoaded = false;
	var $_amazonOrderReferenceId = NULL;
	const AMAZON_EMPTY_USER_FIELD = "amazon";
	const AMAZON_EMPTY_USER_FIELD_EMAIL = "dummy@domain.com";
	const AUTHORIZE_TRANSACTION_TIMEOUT = 1440;

	var $_currentMethod = NULL;
	private $_amount = 0.0;
	private $_is_digital = false;
	private $_order_number = NULL;
	private $_session = '';

	function __construct(& $subject, $config) {

		//if (self::$_this)
		//   return self::$_this;
		parent::__construct($subject, $config);

		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$varsToPush = $this->getVarsToPush();
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

		$this->setCryptedFields(array('accessKey', 'secretKey'));
		$amazon_library = JPATH_SITE . DS . 'plugins' . DS . 'vmpayment' . DS . 'amazon' . DS . 'library';


		//set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . "/../../."));
		set_include_path($amazon_library);
		//$this->loadAmazonClass('OffAmazonPaymentsService_Client');
		if(!class_exists('simNotes')) require (VMPATH_PLUGINS.'/vmpayment/amazon/helper/simnotes.php');
		if(!class_exists('vmAmazonSession')) require VMPATH_PLUGINS .'/vmpayment/amazon/helper/session.php';
		$this->_session = new vmAmazonSession();


		if(!JFactory::getApplication()->isSite()) {
			vmJsApi::jQuery();
			vmJsApi::addJScript('amazonadmin','/plugins/vmpayment/amazon/assets/js/admin.js');
			JFactory::getDocument()->addStyleSheet(JURI::root(true) . '/plugins/vmpayment/amazon/assets/css/amazon-admin.css');
		}

	}

	protected function getVmPluginCreateTableSQL() {

		return $this->createTableSQL('Payment Amazon Table');
	}

	function getTableSQLFields() {

		$SQLfields = array(
			'id' => 'int(1) unsigned NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id' => 'int(11) UNSIGNED',
			'order_number' => 'char(64)',
			'virtuemart_paymentmethod_id' => 'mediumint(1) UNSIGNED',
			'payment_name' => 'varchar(5000)',
			'amazonOrderReferenceId' => 'char(64)',
			//'payment_params'                          => 'varchar(5000)',
			'order_is_digital' => 'smallint(1)',
			'payment_order_total' => 'decimal(15,5)',
			'payment_currency' => 'smallint(1)',
			'email_currency' => 'smallint(1)',
			'recurring' => 'varchar(512)',
			'recurring_number' => 'smallint(1)',
			'recurring_periodicity' => 'smallint(1)',
			'cost_per_transaction' => 'decimal(10,2)',
			'cost_percent_total' => 'decimal(10,2)',
			'tax_id' => 'smallint(1)',
			'amazon_response_amazonReferenceId' => 'char(64)',
			'amazon_response_amazonAuthorizationId' => 'char(64)',
			'amazon_response_amazonCaptureId' => 'char(64)',
			'amazon_response_amazonRefundId' => 'char(64)',
			'amazon_response_state' => 'char(64)',
			'amazon_response_reasonCode' => 'char(64)',
			'amazon_response_reasonDescription' => 'char(64)',
			'amazon_class_request_type' => 'text',
			'amazon_request' => 'text',
			'amazon_class_response_type' => 'text',
			'amazon_response' => 'text',
			'amazon_class_notification_type' => 'text',
			'amazon_notification' => 'text',
		);

		return $SQLfields;
	}


	private function renderSignInButton($cart) {

		if(!$this->checkConditionSignIn($cart)) {
			return NULL;
		}
		//$cart->setOutOfCheckout();
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			vmdebug('renderSignInButton $client == NULL',$client);
			return;
		}
		//$buttonWidgetImageURL = $this->getButtonWidgetImageURL();
		//if(!empty($buttonWidgetImageURL)) {

			$this->addWidgetUrlScript($client);
			/** we do not need that. The button or the payment method do not appear atm in the displayListFE trigger
			 * if ($selected == $this->_currentMethod->virtuemart_paymentmethod_id) {
			 * $checked = 'checked="checked"';
			 * } else {
			 * $checked = '';
			 * }
			 */

			$redirect_page = $this->getSignInRedirectPage();

			$onlyDigitalGoods = $this->isOnlyDigitalGoods($cart);

			$signInButton = $this->renderByLayout('signin', array(
				'client' => $client,
				'cMethod' => $this->_currentMethod,
				'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
				'sellerId' => $this->_currentMethod->sellerId,
				'client_id' => $this->getPlatformId(),
				'sign_in_css' => $this->_currentMethod->sign_in_css,
				'include_amazon_css' => $this->_currentMethod->include_amazon_css,
				'renderAmazonAddressBook' => (!$onlyDigitalGoods),
				'redirect_page' => $redirect_page,
				'layout' => $cart->layout,

			));

			return $signInButton;
		//}


	}


	private function redisplayAddressbookWallet($client, $cart, $order_number) {
		if($cart == NULL) {
			$cart = VirtueMartCart::getCart();
		}
		$this->addWidgetUrlScript($client);
		if(empty($this->_amazonOrderReferenceId)) {
			$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
			if(empty($this->_amazonOrderReferenceId)) {
				$this->leaveAmazonCheckout();

				return;
			}
		}
		$html = $this->renderByLayout('addressbook_wallet', array(
			'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
			'sellerId' => $this->_currentMethod->sellerId,
			'addressbook_designWidth' => $this->getPixelValue($this->_currentMethod->addressbook_designWidth),
			'addressbook_designHeight' => $this->getPixelValue($this->_currentMethod->addressbook_designHeight),
			'wallet_designWidth' => $this->getPixelValue($this->_currentMethod->wallet_designWidth),
			'wallet_designHeight' => $this->getPixelValue($this->_currentMethod->wallet_designHeight),
			'include_amazon_css' => $this->_currentMethod->include_amazon_css,
			'amazonOrderReferenceId' => $this->_amazonOrderReferenceId,
			'renderAddressBook' => false,
			'renderWalletBook' => true,
			'readOnlyWidgets' => "Edit",
			'captureNow' => $this->isCaptureImmediate($cart)
		));

		$html .= $this->renderByLayout('display_wallet', array(
			'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
			'order_number' => $order_number,
			'include_amazon_css' => $this->_currentMethod->include_amazon_css,
			'useXHTML' => $cart->useXHTML,
			'useSSL' => $cart->useSSL,
		));

		return $html;
	}

	private function renderAddressbookWallet($readOnlyWidgets = false) {
		//if ($this->getRenderAddressDoneFromSession()) { return;}
		if(vRequest::getCmd('task')=='updatecartJS') return '';
		$this->loadVmClass('VirtueMartCart', JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = VirtueMartCart::getCart();
		$this->setCartLayout($cart);

		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		$this->addWidgetUrlScript($client);
		if(empty($this->_amazonOrderReferenceId)) {
			$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
			if(empty($this->_amazonOrderReferenceId)) {
				$this->leaveAmazonCheckout();

				return;
			}
		}
		$renderWalletBook = $cart->virtuemart_shipmentmethod_id;
		//$this->setRenderAddressDoneInSession();
		$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		$onlyDigitalGoods = $this->isOnlyDigitalGoods($cart);

		$html = $this->renderByLayout('addressbook_wallet', array(
			'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
			'sellerId' => $this->_currentMethod->sellerId,
			'include_amazon_css' => $this->_currentMethod->include_amazon_css,
			'addressbook_designWidth' => $this->getPixelValue($this->_currentMethod->addressbook_designWidth),
			'addressbook_designHeight' => $this->getPixelValue($this->_currentMethod->addressbook_designHeight),
			'wallet_designWidth' => $this->getPixelValue($this->_currentMethod->wallet_designWidth),
			'wallet_designHeight' => $this->getPixelValue($this->_currentMethod->wallet_designHeight),
			'amazonOrderReferenceId' => $this->_amazonOrderReferenceId,
			'renderAddressBook' => !$onlyDigitalGoods,
			'renderWalletBook' => $renderWalletBook,
			'readOnlyWidgets' => $readOnlyWidgets ? 'Read' : "Edit",
			'captureNow' => $this->isCaptureImmediate($cart)

		));
		echo $html;
	}


	private function checkConditionSignIn($cart) {
		$cart_prices = array();
		$cart_prices['salesPrice'] = $cart->pricesUnformatted['billTotal'];
		// atm, we only display the SignIn button via the trigger plgVmOnCheckoutAdvertise
		//if ($this->doSignInDisplay($sign_in_display) && $this->checkConditions($cart, $this->_currentMethod, $cart_prices) && $this->checkProductConditions($product, $this->_currentMethod)) {

		if($this->checkConditions($cart, $this->_currentMethod, $cart_prices)) {
			return true;
		}

		return false;
	}


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

		//vmTrace('checkConditions', true);
		//$this->debugLog( $cart_prices['salesPrice'], 'checkConditions','debug');
		if(!class_exists('vmAmazonConditions')) require VMPATH_PLUGINS .'/vmpayment/amazon/helper/conditions.php';
		$vmCond = new vmAmazonConditions();
		return $vmCond->checkConditions($cart, $method, $cart_prices);
	}

	/**
	 * @return bool
	 */
	private function isValidCountry($virtuemart_country_id) {
		$countries = array();
		if(!empty($this->_currentMethod->countries)) {
			if(!is_array($this->_currentMethod->countries)) {
				$countries[0] = $this->_currentMethod->countries;
			} else {
				$countries = $this->_currentMethod->countries;
			}
		}
		if(count($countries) == 0 || in_array($virtuemart_country_id, $countries)) {
			return TRUE;
		}

		return false;
	}


	/**
	/**
	 * $requiredKeys = array('merchantId',
	 * 'accessKey',
	 * 'secretKey',
	 * 'region',
	 * 'environment',
	 * 'applicationName',
	 * 'applicationVersion'
	 */
	private function  getOffAmazonPaymentsService_Client() {
		$config['serviceURL'] = '';
		$config['widgetURL'] = '';
		$config['caBundleFile'] = '';
		$config['clientId'] = '';
		$config['merchantId'] = $this->_currentMethod->sellerId;
		$config['accessKey'] = $this->_currentMethod->accessKey;
		$config['secretKey'] = $this->_currentMethod->secretKey;
		$config['applicationName'] = 'VirtueMart';
		$config['applicationVersion'] = '3.2.6';
		$config['region'] = $this->_currentMethod->region;
		$config['environment'] = $this->_currentMethod->environment;
		$config['cnName'] = 'sns.amazonaws.com';//$this->_currentMethod->cnname;

		if(!class_exists('OffAmazonPaymentsService_Client')) require VMPATH_PLUGINS.'/vmpayment/amazon/library/OffAmazonPaymentsService/Client.php';
		if(!class_exists('OffAmazonPaymentsService_Regions')) require VMPATH_PLUGINS.'/vmpayment/amazon/library/OffAmazonPaymentsService/Regions.php';
		try {
			$client = new OffAmazonPaymentsService_Client($config);

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return NULL;
		}

		return $client;
	}


	/**
	 * $requiredKeys = array('merchantId',
	 * 'accessKey',
	 * 'secretKey',
	 * 'region',
	 * 'environment',
	 * 'applicationName',
	 * 'applicationVersion'
	 */
	private function  getLPAAmazonPaymentsService_Client() {

		if(!class_exists('PayWithAmazon\Client'))	require VMPATH_PLUGINS.'/vmpayment/amazon/library/Client.php';
		if(!class_exists('PayWithAmazon\Regions'))	require VMPATH_PLUGINS.'/vmpayment/amazon/library/Regions.php';


		$region = new PayWithAmazon\Regions();

		$amaRegion = 'eu';

		$cFields = array('secretKey','accessKey');

		if(!class_exists('vmCrypt')){
			require(VMPATH_ADMIN.DS.'helpers'.DS.'vmcrypt.php');
		}

		if(isset($this->_currentMethod->modified_on)){
			$date = JFactory::getDate($this->_currentMethod->modified_on);
			$date = $date->toUnix();
		} else {
			$date = 0;
		}

		foreach($cFields as $field){

			if(isset($this->_currentMethod->$field)){
				$this->_currentMethod->$field = vmCrypt::decrypt($this->_currentMethod->$field,$date);
			}
			//vmdebug('getOffAmazonPaymentsService_Client',$field,$this->_currentMethod->$field);
		}

		//$config['serviceURL'] = $region->mwsServiceUrls[strtolower($amaRegion)];
		//$config['widgetURL'] = '';
		//$config['caBundle_file'] = '';
		$config['client_id'] = $this->getPlatformId();
		$config['merchant_id'] = $this->_currentMethod->sellerId;
		$config['access_key'] = $this->_currentMethod->accessKey;
		$config['secret_key'] = $this->_currentMethod->secretKey;
		$config['application_name'] = 'VirtueMart';
		$config['application_version'] = '3.2.6';
		$config['region'] = $this->_currentMethod->region;
		$config['sandbox'] = true;//$this->_currentMethod->environment;
		//$config['cnName'] = $this->_currentMethod->cnname;//$_SERVER['HTTP_HOST']; //$_SERVER['SERVER_NAME'] //REQUEST_URI

		vmdebug('getOffAmazonPaymentsService_Client $config',$config);
		/*if($this->_currentMethod->region == "other") {
			$prefix = $this->_currentMethod->environment;
			$serviceURL = $prefix . "_serviceURL";
			$widgetURL = $prefix . "_widgetURL";
			$config['serviceURL'] = $this->_currentMethod->$serviceURL;
			$config['widgetURL'] == $this->_currentMethod->$widgetURL;
		//}*/


		try {
			$client = new PayWithAmazon\Client($config);

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());
			vmdebug('Client NULL '. $e->getMessage(), $e->getCode());
			return NULL;
		}

		return $client;
	}

	private function amazonError($message, $code = '') {

		$public_msg = '';
		if($this->_currentMethod->debug) {
			$public_msg = $message;
		}
		vmError($message . " (" . $code . ")", $public_msg);
	}


	private function addWidgetUrlScript($client) {
		if(!self::$widgetScriptLoaded) {
			$widgetURL = $client->getMerchantValues()->getWidgetUrl();
			/*vmdebug('My widget URL',$widgetURL);
			$widgetURL = substr($widgetURL,6);
			vmJsApi::addJScript('amazon.widgets',$widgetURL, false, false, false);*/
			JHTML::script($widgetURL, false);
			self::$widgetScriptLoaded = true;
		}

	}

	/**
	 * @return
	 */
	private function getSignInRedirectPage() {

		$url = 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&format=raw&nt=getAmazonSessionId&pm=' . $this->_currentMethod->virtuemart_paymentmethod_id . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');

		//$_amazonOrderReferenceId = $this->getAmazonOrderReferenceId();
		if($this->_amazonOrderReferenceId) {
			//$url .= '&session=' . $this->_amazonOrderReferenceId;
		}
		$cart = VirtueMartCart::getCart();

		return JRoute::_($url, $cart->useXHTML, $cart->useSSL);

	}

	/**
	 * @param $product
	 * @param $productDisplay
	 * @return bool

	function plgVmOnProductDisplayPayment ($product, &$productDisplay) {
	 *
	 * $vendorId = 1;
	 * if ($this->getPluginMethods($vendorId) === 0) {
	 * return FALSE;
	 * }
	 *
	 * $productDisplay = $this->renderSignInButton('product', false, $product);
	 * return TRUE;
	 * }
	 */


	/**
	 * @return null
	 */
	public function plgVmOnPaymentNotification() {

		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);
		if(!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if(!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}

		$notificationTask = vRequest::getCmd('nt', '');

		switch ($notificationTask) {

			case 'getAmazonSessionId':

				if(!class_exists('VirtueMartCart')) {
					require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
				}
				$cart = VirtueMartCart::getCart(false);

				$cart->virtuemart_paymentmethod_id = $virtuemart_paymentmethod_id;
				$this->_amazonOrderReferenceId = $this->_session->saveAmazonOrderReferenceId($cart, $this->isOnlyDigitalGoods($cart), $virtuemart_paymentmethod_id);
				$this->_session->saveBTandSTInSession($cart);
				$this->setCartLayout($cart, false);
				$this->updateCartWithDefaultAmazonAddress($cart, $this->isOnlyDigitalGoods($cart));

				$this->redirectToCart();
				break;
			case 'ipn':
				$this->ipn();
				break;
			default:
				$this->amazonError(vmText::_('VMPAYMENT_AMAZON_INVALID_NOTIFICATION_TASK'));

				return;
		}

	}


	/**
	 * IPNs requires SSL. All merchant cannot have SSL. A system plugin simulate a cron job
	 */
	public function plgVmRetrieveIPN() {
		// check if table exists
		$db = JFactory::getDBO();
		$query = 'SHOW TABLES LIKE "' . str_replace('#__', $db->getPrefix(), $this->_tablename) . '"';

		$db->setQuery($query);
		if(!$db->loadResult()) {
			return false;
		}

		$q = "SELECT  * FROM " . $this->_tablename . " WHERE
	(
	  ( `amazon_class_response_type` LIKE 'OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse' AND `amazon_response_state`  IN (  'Open', 'Suspended') )
	  OR
	( `amazon_class_response_type` LIKE 'OffAmazonPaymentsService_Model_AuthorizeResponse' AND `amazon_response_state`  IN ('Pending',  'Open') )
    OR
    ( `amazon_class_response_type` LIKE 'OffAmazonPaymentsService_Model_CaptureResponse' AND `amazon_response_state`  IN ('Pending') )
    OR
    ( `amazon_class_response_type` LIKE 'OffAmazonPaymentsService_Model_RefundResponse' AND `amazon_response_state`  IN ('Pending') )
    )
	AND `id`  in (SELECT MAX( id ) FROM " . $this->_tablename . "  GROUP BY virtuemart_order_id )
	ORDER BY `created_on` DESC ";


		$db->setQuery($q);
		$payments = $db->loadObjectList();
		$done = array();
		//$this->debugLog("<pre>" . var_export($payments, true) . "</pre>", __FUNCTION__, 'debug');
		$this->loadAmazonServicesClasses();
		if(!$payments) {
			return;
		}
		foreach ($payments as $payment) {
			if(in_array($payment->order_number, $done)) {
				continue;
			}
			if(!$payment->amazon_request) {
				continue;
			}
			if(!($this->_currentMethod = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id))) {
				continue;
			}
			if($payment->amazon_class_response_type == 'OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse') {
				$this->retrieveIPNConfirmOrderReference($payment);
			} elseif($payment->amazon_class_response_type == 'OffAmazonPaymentsService_Model_RefundResponse') {
				$this->retrieveIPNRefund($payment);
			} elseif($payment->amazon_class_response_type == 'OffAmazonPaymentsService_Model_AuthorizeResponse') {
				$this->retrieveIPNAuthorization($payment);
			} elseif($payment->amazon_class_response_type == 'OffAmazonPaymentsService_Model_CaptureResponse') {
				$this->retrieveIPNCapture($payment);
			}
			$done[] = $payment->order_number;
		}

	}

	private function getNumberOfDays($payments) {
		$created_on = strtotime($payments[0]->created_on);
		$now = date_create(date('Y-m-d'));
		$now = time();
		//$number = date_diff($now, $created_on);
		//$number->format('%a');
		$days_between = ceil(abs($created_on - $now) / 86400);

		return $days_between;
	}


	/**
	 * if Open  and > 180 days=> poll
	 * if Suspended => poll
	 * @param $payment
	 */
	private function retrieveIPNConfirmOrderReference($payment) {
		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($payment->virtuemart_order_id);
		if(!($payments = $this->getDatasByOrderId($payment->virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}

		if($payment->amazon_response_state == 'Suspended' OR ($payment->amazon_response_state == 'Open' AND $this->getNumberOfDays($payments) > 180)) {
			$this->getAuthorizationState($payments, $order);
		}


	}

	/**
	 * if Pending => poll
	 * if closed or declined ==> fetch order
	 * @param $payment
	 */
	private function retrieveIPNRefund($payment) {
		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($payment->virtuemart_order_id);
		//Load the payments
		if(!($payments = $this->getDatasByOrderId($payment->virtuemart_order_id))) {
			return null;
		}
		// poll because refund state = pending
		$refundState = $this->getRefundState($payment, $order);
		if($refundState == 'Declined' or $refundState == 'Completed') {
			$this->capturePayment($payments, $order);
		}
	}


	private function getRefundState($payment, $order) {

		$amazonRefundId = $payment->amazon_response_amazonRefundId;

		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetRefundDetailsRequest');
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		try {
			$getRefundDetailsRequest = new OffAmazonPaymentsService_Model_GetRefundDetailsRequest();
			$getRefundDetailsRequest->setSellerId($this->_currentMethod->sellerId);
			$getRefundDetailsRequest->setAmazonRefundId($amazonRefundId);
			$getRefundDetails = $client->getRefundDetails($getRefundDetailsRequest);
		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return;
		}
		$this->loadHelperClass('amazonHelperGetRefundDetailsResponse');
		$amazonHelperGetRefundDetailsResponse = new amazonHelperGetRefundDetailsResponse($getRefundDetails, $this->_currentMethod);
		$storeInternalData = $amazonHelperGetRefundDetailsResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, $getRefundDetailsRequest, $getRefundDetails, NULL, NULL, $storeInternalData);

		return $amazonHelperGetRefundDetailsResponse->getState();

	}


	/**
	 * if Pending, authorization > 30 days ==> poll
	 * If Closed, Declined ==> fetch order
	 * @param $payment
	 */
	private function retrieveIPNAuthorization($payment) {
		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		if(!($payments = $this->getDatasByOrderId($payment->virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($payment->virtuemart_order_id);
		if($payment->amazon_response_state == 'Pending' OR ($payment->amazon_response_state == 'Open' AND $this->getNumberOfDays($payments) > 30)) {
			$amazonAuthorizationId = $this->getAmazonAuthorizationId($payments);
			if(!$amazonAuthorizationId) {
				return false;
			}
			$authorizationDetailsResponse = $this->getAuthorizationDetails($amazonAuthorizationId, $order);
			$this->loadHelperClass('amazonHelperGetAuthorizationDetailsResponse');
			$amazonHelperAuthorizationDetailsResponse = new amazonHelperGetAuthorizationDetailsResponse($authorizationDetailsResponse, $this->_currentMethod);

			$authorizationState = $amazonHelperAuthorizationDetailsResponse->getState();
			if($authorizationState == 'Closed' OR $authorizationState == 'Declined') {
				// check if status has changed
				// fetch Order
				//$this->_amazonOrderReferenceId = $payments[0]->amazonOrderReferenceId;
				//$this->vmConfirmedOrder(NULL, $order, FALSE);
				$getAuthorizationDetailsResult = $authorizationDetailsResponse->getGetAuthorizationDetailsResult();
				$getAuthorizationDetails = $getAuthorizationDetailsResult->getAuthorizationDetails();
				if($authorizationState == 'Closed'){
					$this->closeAuthorization($getAuthorizationDetails->getAmazonAuthorizationId(), $order);
				} else {
					vmWarn('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_DECLINED');
					$this->cancelPayment($payments,$order);
				}

				return;
			}
			if(!$authorizationDetailsResponse->isSetGetAuthorizationDetailsResult()) {
				return;
			}
			$getAuthorizationDetailsResult = $authorizationDetailsResponse->getGetAuthorizationDetailsResult();

			if(!$getAuthorizationDetailsResult->isSetAuthorizationDetails()) {
				return;
			}
			$getAuthorizationDetails = $getAuthorizationDetailsResult->getAuthorizationDetails();

			$this->updateAuthorizeBillingAddressInOrder($getAuthorizationDetails, $order);

			$amazonState = $amazonHelperAuthorizationDetailsResponse->onResponseUpdateOrderHistory($order);

		}
	}


	/**
	 * if Pending => poll
	 * if Completed, Closed, or declined ==> fetch order
	 * @param $payment
	 */
	private function retrieveIPNCapture($payment) {
		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($payment->virtuemart_order_id);
		//Load the payments
		if(!($payments = $this->getDatasByOrderId($payment->virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}
		$captureState = $this->getCaptureState($payments, $order);

		if($captureState == 'Completed' OR $captureState == 'Closed' OR $captureState == 'Declined') {
			// will update Billing address
			$client = $this->getOffAmazonPaymentsService_Client();
			if($client == NULL) {
				return;
			}

			$this->getAuthorization($client, NULL, $order, false);
		}

	}

	function plgVmOnSelfCallBE($type, $name, &$render) {
		if($name != $this->_name || $type != 'vmpayment') {
			return FALSE;
		}
		$action = vRequest::getCmd('action');
		$virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id');
		//Load the method
		if(!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		$virtuemart_order_id = vRequest::getInt('virtuemart_order_id');
		if(!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			return null;
		}
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$this->_order_number = $this->getUniqueReferenceId($order['details']['BT']->order_number);
		$this->_amount = vRequest::getFloat('amount');

		switch ($action) {
			case 'refundPayment':
				if($this->canDoRefund($payments, $order)) {
					$this->refundPayment($payments, $order);
				}
				break;
			case 'capturePayment':

				if($authorizationId = $this->canDoCapture($payments, $order)) {
					// may be we did a new authorization in case of partial capture
					$this->capturePayment($payments, $order);
				}
				break;
			case 'newAuthorization':

				if($this->canDoAuthorization($payments, $order)) {
					$client = $this->getOffAmazonPaymentsService_Client();
					if($client == NULL) {
						return;
					}

					$this->getAuthorization($client, NULL, $order, false);
				}
				break;
			default:
				vmError('VMPAYMENT_AMAZON_UPDATEPAYMENT_UNKNOWN_ACTION');
		}
		$app = JFactory::getApplication();
		$link = 'index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $virtuemart_order_id;

		$app->redirect(JRoute::_($link, FALSE));

	}

	function plgVmOnSelfCallFE($type, $name, &$render) {
		if($name != $this->_name || $type != 'vmpayment') {
			return FALSE;
		}
		$action = vRequest::getCmd('action');
		$virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id');
		//Load the method
		if(!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		//$this->debugLog($action, 'plgVmOnSelfCallFE', 'debug');

		if(!class_exists('VirtueMartCart')) {
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		switch ($action) {

			case 'updateCartWithAmazonAddress':
				//$client = $this->getOffAmazonPaymentsService_Client();
				//$this->setOrderReferenceDetails()
				$return = $this->updateCartWithAmazonAddress();
				$json = array();
				$json['reload'] = $return['error'];
				$json['error_msg'] = '';
				if(isset($return['error_msg'])) {
					$json['error_msg'] = $this->rendererErrorMessage($return['error_msg']);
				}


				JResponse::setHeader('Cache-Control', 'no-cache, must-revalidate');
				JResponse::setHeader('Expires', 'Mon, 6 Jul 2000 10:00:00 GMT');
				// Set the MIME type for JSON output.
				$document = JFactory::getDocument();
				$document->setMimeEncoding('application/json');
				JResponse::setHeader('Content-Disposition', 'attachment;filename="amazon.json"', TRUE);
				JResponse::sendHeaders();

				echo json_encode($json);
				jExit();
				//JFactory::getApplication()->close();
				break;


			case 'leaveAmazonCheckout':
				$this->leaveAmazonCheckout();
				$json = array();
				JResponse::setHeader('Cache-Control', 'no-cache, must-revalidate');
				JResponse::setHeader('Expires', 'Mon, 6 Jul 2000 10:00:00 GMT');
				// Set the MIME type for JSON output.
				$document = JFactory::getDocument();
				$document->setMimeEncoding('application/json');
				JResponse::setHeader('Content-Disposition', 'attachment;filename="amazon.json"', TRUE);
				JResponse::sendHeaders();

				echo json_encode($json);
				jExit();
				break;

			case 'resetAmazonReferenceId':
				$this->_session->clearAmazonSession();
				break;
			case 'onInvalidPaymentNewAuthorization':
				$html = $this->onInvalidPaymentNewAuthorization();
				echo $html;
				break;
			default:
				$this->amazonError(vmText::_('VMPAYMENT_AMAZON_INVALID_NOTIFICATION_TASK'));

				return;
		}

	}


	function rendererErrorMessage($msg) {
		return '<dl id="system-message"><dt class="error">Error</dt><dd class="error message"><ul><li>' . $msg . '</li></ul></dd></dl>';

	}


	/**
	 * @param VirtueMartCart $cart
	 */

	public function plgVmOnCheckoutCheckDataPayment(VirtueMartCart $cart) {
		static $checkoutCheckDataPaymentDone = false;

		if(!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			/*
		if ($cart->layout==$this->_name) {
			if (!class_exists('VmConfig')) {
				require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
			}
			VmConfig::loadConfig();
			$cart->layout = VmConfig::get('cartlayout', 'default');
			$cart->setCartIntoSession();
		}
		*/
			return NULL; // Another method was selected, do nothing
		}
		if(!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(empty($this->_amazonOrderReferenceId)) {
			//$message = vmText::_('VMPAYMENT_AMAZON_PAYWITHAMAZON_BUTTON');
			//vmError($message, $message);
			return false;
		}
		if($checkoutCheckDataPaymentDone) {
			return true;
		}
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		// incase the Address was not displayed
		if($this->isOnlyDigitalGoods($cart)) {
			return true;
		}
		$physicalDestination = $this->getPhysicalDestination();
		if(!$physicalDestination) {
			// may be we have just logged in, so there is no physical destination
			//$this->leaveAmazonCheckout();
			return false;
		}

		$update_data = $this->getUserInfoFromAmazon($physicalDestination);
		if(!$this->isValidCountry($update_data['virtuemart_country_id'])) {
			$country = shopFunctions::getCountryByID($update_data['virtuemart_country_id']);
			$app = JFactory::getApplication();
			$leaveAmazonCheckoutLink = $this->getLeaveAmazonCheckoutLink();
			//$app->enqueueMessage(vmText::sprintf('VMPAYMENT_AMAZON_UPDATECART_DELIVERYCOUNTRYNOTALLOWED', $country, $leaveAmazonCheckoutLink));
			$app->enqueueMessage(vmText::sprintf('VMPAYMENT_AMAZON_UPDATECART_DELIVERYCOUNTRYNOTALLOWED', $country));

			//$this->leaveAmazonCheckout();
			return false;
		}


		// setOrderReferenceDetails
		if(!$setOrderReferenceDetailsResponse = $this->setOrderReferenceDetails($client, $cart)) {
			$this->leaveAmazonCheckout();
			$this->onErrorRedirectToCart();

			return FALSE;
		}
		// getOrderReferenceDetails
		if(!$getOrderReferenceDetailsResponse = $this->getOrderReferenceDetails($client)) {
			$this->removeAmazonAddressFromCart($cart);
			$this->_session->clearAmazonSession();
			$cart->emptyCart();
			$this->onErrorRedirectToCart();

			return FALSE;
		}

		$orderReferenceDetails = $getOrderReferenceDetailsResponse->GetOrderReferenceDetailsResult->getOrderReferenceDetails();
		if($this->isSetConstraints($cart, $orderReferenceDetails)) {
			return false;
		}
		$checkoutCheckDataPaymentDone = true;

		return true;
	}


	private function getLeaveAmazonCheckoutLink() {
		$leaveAmazonCheckoutLink = '<a href="#" class="leaveAmazonCheckout">' . vmText::_('VMPAYMENT_AMAZON_LEAVE_PAY_WITH_AMAZON') . '</a>';

		return $leaveAmazonCheckoutLink;
	}

	/**
	 * Constraints indicates if mandatory information is missing or incorrect in the Order Reference Object
	 * @param $cart
	 * @param $orderReferenceDetails
	 * @return bool
	 */
	private function isSetConstraints($cart, $orderReferenceDetails) {
		if($orderReferenceDetails->isSetConstraints()) {
			$constraints = $orderReferenceDetails->getConstraints();
			$constraintList = $constraints->getConstraint();
			foreach ($constraintList as $constraint) {
				if($constraint->isSetDescription()) {
					$this->setInConfirmOrder($cart, false);
					switch ($constraint->isSetConstraintID($constraint)) {
						case 'ShippingAddressNotSet':
							$this->handleShippingAddressNotSetConstraint($constraint);
							break;
						case 'PaymentPlanNotSet':
							$this->handlePaymentPlanNotSetConstraint($constraint);
							break;
						case 'AmountNotSet':
							//$this->handleAmountNotSetConstraint($constraint);
							// PROGRAMMING ERRROR TODO
							break;
						case 'PaymentMethodNotAllowed':
							$this->handlePaymentMethodNotAllowedConstraint($constraint);
							break;
						default:
							vmError('VMPAYMENT_AMAZON_CONSTRAINTID_UNKOWN');
							$this->onErrorRedirectToCart();

							return true;
					}

					return true;
				}
			}

		}

		return false;
	}

	/**
	 * Display again the Amazon AddressBook widget to the buyer to collect shipping Information
	 * @param $constraint
	 */
	private function handleShippingAddressNotSetConstraint($constraint) {
		$this->renderAddressbookWallet();
	}

	/**
	 * Display again the Amazon Wallet widget to the buyer to collect payment Information
	 * @param $constraint
	 */
	private function handlePaymentPlanNotSetConstraint($constraint) {
		$this->renderAddressbookWallet();
	}

	/**
	 * Call again the SetOrderReferenceDetails operation with the order amount
	 * This constraint should not happen. It is a programming error
	 * @param $constraint
	 */
	private function handleAmountNotSetConstraint($constraint) {
		$this->amazonError('handleAmountNotSetConstraint: ' . $constraint->getDescription());

	}

	/**
	 * Display again the Amazon Wallet widget and Request the buyer to select a different payment method
	 * @param $constraint
	 */
	private function handlePaymentMethodNotAllowedConstraint($constraint) {
		$this->debugLog("<pre>" . var_export($constraint, true) . "</pre>", __FUNCTION__, 'debug');
		$this->renderAddressbookWallet('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT');
	}

	/**
	 * @param $cart
	 * @param $order
	 * @return bool|null
	 */
	public function plgVmConfirmedOrder($cart, $order) {

		if(!($this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if(!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return FALSE;
		}
		$this->setInConfirmOrder($cart);

		$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(!$this->_amazonOrderReferenceId) {
			$this->onErrorRedirectToCart();

			return FALSE;
		}
		$client = $this->getOffAmazonPaymentsService_Client();
		// the amount saved is in payment currency
		$this->_amount = $this->getTotalInPaymentCurrency($client, $order['details']['BT']->order_total, $order['details']['BT']->order_currency);
		$this->_is_digital = $this->isOnlyDigitalGoods($cart);
		$this->storeAmazonInternalData($order, NULL, NULL, NULL, $this->renderPluginName($this->_currentMethod), NULL, $this->_amazonOrderReferenceId, $this->_currentMethod);

		$this->_order_number = $order['details']['BT']->order_number;

		$html = $this->vmConfirmedOrder($cart, $order, false);
		vRequest::setVar('html', $html);
		vRequest::setVar('display_title', false);

	}

	/**
	 * Confirmed Order also when in synchronous mode, and InvalidPaymentMethod
	 * @param $cart
	 * @param $order
	 * @return bool|string
	 */
	private function vmConfirmedOrder($cart, $order, $orderReferenceModifiable = true) {
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		// if $cart=NULL may be coming from plgVmRetrieveIPN
		if($cart) {
			if(!$this->setOrderReferenceDetails($client, $cart, $order)) {
				$this->redirectToCart(vmText::_('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT'), true);
			}
		}
		//confirmOrderReference
		if(!$this->confirmOrderReference($client, $order)) {
			$this->redirectToCart(vmText::_('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT'), true);
		}
		// getorderdetails &  address email
		if(!$this->updateBuyerInOrder($client, $cart, $order)) {
			$this->redirectToCart(vmText::_('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT'), true);
		}
		/* why do i have that ? */
		if($cart) {
			$redirect = true;
		} else {
			$redirect = false;
		}
		$redirect = true;
		// at this point, since the authorization and capturing takes additional time to process
		// let's do that with a trigger
		if(!($amazonAuthorizationId = $this->getAuthorization($client, $cart, $order, $redirect))) {
			// getAuhtorization returns false if the the wallet needs to be displayed again
			$cart->_inConfirm = false;
			$html = $this->redisplayAddressbookWallet($client, $cart, $order['details']['BT']->order_number);

			return $html;
		}

		if(!class_exists('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);
		$success = true;
		$html = $this->renderByLayout('response', array(
			"success" => $success,
			"amazonOrderId" => $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id),
			"order" => $order,
			'include_amazon_css' => $this->_currentMethod->include_amazon_css,
		));


		$this->leaveAmazonCheckout();
		if(!$cart) {
			$cart = VirtueMartCart::getCart();
		}
		$cart->emptyCart();

		return $html;


	}


	/**
	 * @param      $order
	 * @param      $request
	 * @param      $response
	 * @param      $notification
	 * @param null $payment_name
	 * @param null $amazonParams
	 * @return array
	 */
	private function storeAmazonInternalData($order, $request, $response, $notification = NULL,
		$payment_name = NULL, $amazonParams = NULL, $amazonOrderReferenceId = NULL) {

		$db_values['order_number'] = $order['details']['BT']->order_number;
		$db_values['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$db_values['virtuemart_paymentmethod_id'] = $this->_currentMethod->virtuemart_paymentmethod_id;
		$db_values['order_is_digital'] = $this->_is_digital;
		$db_values['payment_order_total'] = $this->_amount;
		$db_values['payment_currency'] = $order['details']['BT']->user_currency_id;
		$db_values['amazon_request'] = $request ? serialize($request) : "";
		$db_values['amazon_class_request_type'] = $request ? get_class($request) : '';
		$db_values['amazon_response'] = $response ? serialize($response) : "";
		$db_values['amazon_class_response_type'] = $response ? get_class($response) : '';
		$db_values['amazon_notification'] = $notification ? serialize($notification) : "";
		$db_values['amazon_class_notification_type'] = $notification ? get_class($notification) : '';
		$db_values['amazonOrderReferenceId'] = $amazonOrderReferenceId ? $amazonOrderReferenceId : '';
		//$db_values['payment_params'] = $this->_currentMethod;
		$db_values['payment_name'] = $payment_name;
		if($amazonParams) {
			$amazonParamsArray = (array)($amazonParams);
			$db_values = array_merge($db_values, $amazonParamsArray);
		}

		//$preload=true   preload the data here too preserve not updated data
		return $this->storePSPluginInternalData($db_values, $this->_tablepkey, 0);

	}

	/**
	 * @return mixed
	 */

// TODO:  address line 3, district
	private function updateBuyerInOrder($client, $cart, $order) {

		$orderModel = VmModel::getModel('orders');
		$BT['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$order_userinfosTable = $orderModel->getTable('order_userinfos');

		$getOrderReferenceDetailsResponse = $this->getOrderReferenceDetails($client);
		$getOrderReferenceDetailsResult = $getOrderReferenceDetailsResponse->getGetOrderReferenceDetailsResult();
		$orderReferenceDetails = $getOrderReferenceDetailsResult->getOrderReferenceDetails();

		if($orderReferenceDetails->isSetBuyer()) {
			$buyer = $orderReferenceDetails->getBuyer();
			$BTFromAmazon = $this->getUserInfoFromAmazon($buyer, '', false, true);
			$BTFromAmazon['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
			$BTFromAmazon['address_type'] = 'BT';
			$this->debugLog("<pre>" . var_export($BTFromAmazon, true) . "</pre>", __FUNCTION__ . ' BT', 'debug');
			$order_userinfosTable->emptyCache();
			$order_userinfosTable->load($order['details']['BT']->virtuemart_order_id, 'virtuemart_order_id', " AND address_type='BT'");
			if(!$order_userinfosTable->bindChecknStore($BTFromAmazon, true)) {
				vmError($order_userinfosTable->getError());

				return false;
			}

		}

		// at this step, we should get it from amazon
		$onlyDigitalGoods = $this->isOnlyDigitalGoods($cart);
		if(!$onlyDigitalGoods) {

			$physicalDestination = $orderReferenceDetails->getDestination()->getPhysicalDestination();

			if($physicalDestination) {
				$ST = $this->getUserInfoFromAmazon($physicalDestination);
				$ST['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
				$ST['address_type'] = 'ST';
				$order_userinfosTable->emptyCache();
				// check if ST is there
				$query = "SELECT `#__virtuemart_order_userinfos`.*  FROM `#__virtuemart_order_userinfos`  WHERE `#__virtuemart_order_userinfos`.`virtuemart_order_id` = " . $order['details']['BT']->virtuemart_order_id . "  AND address_type='ST'";
				$db = JFactory::getDBO();
				$db->setQuery($query);
				if(!$db->loadResult()) {
					$order_userinfosTable = $orderModel->getTable('order_userinfos');
				}
				$order_userinfosTable->emptyCache();
				$order_userinfosTable->load($order['details']['BT']->virtuemart_order_id, 'virtuemart_order_id', " AND address_type='ST'");
				if(!$order_userinfosTable->bindChecknStore($ST, true)) {
					vmError($order_userinfosTable->getError());

					return false;
				}
				$this->debugLog("<pre>" . var_export($ST, true) . "</pre>", __FUNCTION__ . ' ST', 'debug');
			}
		}

		return true;
	}


	private function getUserInfoFromAmazon($amazonAddress, $prefix = '', $all = true, $getEmail = false) {

		if($amazonAddress->isSetName()) {
			$userInfoData[$prefix . 'last_name'] = $amazonAddress->getName();
			$userInfoData[$prefix . 'first_name'] = '';
		}

		if($getEmail AND $amazonAddress->isSetEmail()) {
			$userInfoData['email'] = $amazonAddress->getEmail();
		}
		if($amazonAddress->isSetPhone()) {
			$userInfoData['phone_1'] = $amazonAddress->getPhone();
		}
		if($all) {
			if($amazonAddress->isSetAddressLine1()) {
				$userInfoData[$prefix . 'address_1'] = $amazonAddress->getAddressLine1();
				if($amazonAddress->isSetAddressLine2()) {
					$userInfoData[$prefix . 'address_2'] = $amazonAddress->getAddressLine2();
				}
				if($amazonAddress->isSetAddressLine3()) {
					$userInfoData[$prefix . 'address_2'] .= ", " . $amazonAddress->getAddressLine3();
				}
			} else {
				if($amazonAddress->isSetAddressLine2()) {
					$userInfoData[$prefix . 'address_1'] = $amazonAddress->getAddressLine2();
				}
				if($amazonAddress->isSetAddressLine3()) {
					$userInfoData[$prefix . 'address_2'] = $amazonAddress->getAddressLine3();
				}
			}

			if($amazonAddress->isSetCity()) {
				$userInfoData[$prefix . 'city'] = $amazonAddress->getCity();
			}
			if($amazonAddress->isSetCounty()) {
				//$userInfoData['county'] = $amazonAddress->getCounty();
			}
			if($amazonAddress->isSetDistrict()) {
				//$userInfoData['district'] = $amazonAddress->GetDistrict();
			}
			if($amazonAddress->isSetStateOrRegion()) {
				$stateId = shopFunctions::getStateIDByName($amazonAddress->GetStateOrRegion());
				if($stateId) {
					$userInfoData[$prefix . 'virtuemart_state_id'] = $stateId;
				} else {
					$userInfoData[$prefix . 'virtuemart_state_id'] = 0;
				}
			}
			if($amazonAddress->isSetPostalCode()) {
				$userInfoData[$prefix . 'zip'] = $amazonAddress->GetPostalCode();
			}
			if($amazonAddress->isSetCountryCode()) {
				$userInfoData[$prefix . 'virtuemart_country_id'] = shopFunctions::getCountryIDByName($amazonAddress->GetCountryCode());
			}
		}


		return $userInfoData;
	}


	function getAmazonShipmentAddress() {
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest');

		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		try {
			$getOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
			$getOrderReferenceDetailsRequest->setSellerId($this->_currentMethod->sellerId);
			$getOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id));
			$referenceDetailsResultWrapper = $client->getOrderReferenceDetails($getOrderReferenceDetailsRequest);
			$physicalDestination = $referenceDetailsResultWrapper->GetOrderReferenceDetailsResult->getOrderReferenceDetails()->getDestination()->getPhysicalDestination();

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return;
		}

		return $physicalDestination;
	}

	function getAuthorizationDetails($amazonAuthorizationId, $order) {
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		try {

			$getAuthorizationDetailsRequest = new OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest();
			$getAuthorizationDetailsRequest->setSellerId($this->_currentMethod->sellerId);
			$getAuthorizationDetailsRequest->setAmazonAuthorizationId($amazonAuthorizationId);
			$getAuthorizationDetailsResponse = $client->getAuthorizationDetails($getAuthorizationDetailsRequest);

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return NULL;
		}
		$this->loadHelperClass('amazonHelperGetAuthorizationDetailsResponse');
		$amazonHelperGetAuthorizationDetailsResponse = new amazonHelperGetAuthorizationDetailsResponse($getAuthorizationDetailsResponse, $this->_currentMethod);
		$storeInternalData = $amazonHelperGetAuthorizationDetailsResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, $getAuthorizationDetailsRequest, $getAuthorizationDetailsResponse, NULL, NULL, $storeInternalData);

		return $getAuthorizationDetailsResponse;
	}


	private function getCaptureDetails($amazonCaptureId, $order) {
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		try {
			$getCaptureDetailsRequest = new OffAmazonPaymentsService_Model_GetCaptureDetailsRequest();
			$getCaptureDetailsRequest->setSellerId($this->_currentMethod->sellerId);
			$getCaptureDetailsRequest->setAmazonCaptureId($amazonCaptureId);

			$getCaptureDetails = $client->getCaptureDetails($getCaptureDetailsRequest);
		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return;
		}
		$this->loadHelperClass('amazonHelperGetCaptureDetailsResponse');
		$amazonHelperGetCaptureDetailsResponse = new amazonHelperGetCaptureDetailsResponse($getCaptureDetails, $this->_currentMethod);
		$storeInternalData = $amazonHelperGetCaptureDetailsResponse->getStoreInternalData();

		$this->storeAmazonInternalData($order, $getCaptureDetailsRequest, $getCaptureDetails, NULL, NULL, $storeInternalData);

		return $getCaptureDetails;
	}

	/*
	private function getAmazonBillingAddress ($amazonAuthorizationId) {

		$getAuthorizationDetails = $this->getAuthorizationDetails($amazonAuthorizationId);
		$billingAddress = $getAuthorizationDetails->getGetAuthorizationDetailsResult()->getAuthorizationDetails()->getAuthorizationBillingAddress();

		return $billingAddress;
	}

*/
	/**
	 * If we are going back to the cart because of InvalidPaymentMethod, then the order reference is not anylonger in a draft state
	 * @return bool
	 */
	private function setOrderReferenceDetails($client, $cart, $order = NULL) {

		$this->loadAmazonClass('OffAmazonPaymentsService_Model_OrderReferenceAttributes');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_OrderTotal');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_SellerOrderAttributes');
		if($order) {
			$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->user_currency_id);
			$amount = $amountInCurrency['value'];
		} else {
			$amount = $this->getTotalInPaymentCurrency($client, $cart->pricesUnformatted['billTotal'], $cart->pricesCurrency);
		}
		//$_amazonOrderReferenceId = $this->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(empty($this->_amazonOrderReferenceId)) {
			$this->amazonError(__FUNCTION__ . ' setOrderReferenceDetails, No $_amazonOrderReferenceId');

			return FALSE;
		}
		try {

			$setOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_SetOrderReferenceDetailsRequest();
			$setOrderReferenceDetailsRequest->setSellerId($this->_currentMethod->sellerId);
			$setOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
			$setOrderReferenceDetailsRequest->setOrderReferenceAttributes(new OffAmazonPaymentsService_Model_OrderReferenceAttributes());
			$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setOrderTotal(new OffAmazonPaymentsService_Model_OrderTotal());
			$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getOrderTotal()->setCurrencyCode($this->getCurrencyCode3($client));
			$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getOrderTotal()->setAmount($amount);
			$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setSellerNote(simNotes::getSellerNote($this->_currentMethod));
			$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setSellerOrderAttributes(new OffAmazonPaymentsService_Model_SellerOrderAttributes());
			if($order) {
				$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getSellerOrderAttributes()->setSellerOrderId($order['details']['BT']->order_number);
			}
			$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getSellerOrderAttributes()->setStoreName($this->getStoreName());
			//$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->getSellerOrderAttributes()->setCustomInformation($order['details']['BT']->customer_note);
			$setOrderReferenceDetailsRequest->getOrderReferenceAttributes()->setPlatformId($this->getPlatformId());

			$setOrderReferenceDetailsResponse = $client->setOrderReferenceDetails($setOrderReferenceDetailsRequest);

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());
			$this->_session->clearAmazonSession();

			return FALSE;
		}
		//$this->debugLog("<pre>" . var_export($setOrderReferenceDetailsRequest, true) . "</pre>", __FUNCTION__, 'debug');
		//$this->debugLog("<pre>" . var_export($setOrderReferenceDetailsResponse, true) . "</pre>", __FUNCTION__, 'debug');

		return $setOrderReferenceDetailsResponse;
	}

	/**
	 * @return bool
	 */
	private function getOrderReferenceDetails($client) {
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest');

		//$_amazonOrderReferenceId = $this->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(empty($this->_amazonOrderReferenceId)) {
			$this->amazonError(__FUNCTION__ . ', No $_amazonOrderReferenceId');

			return FALSE;
		}
		try {

			$getOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
			$getOrderReferenceDetailsRequest->setSellerId($this->_currentMethod->sellerId);
			$getOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);

			$getOrderReferenceDetailsResponse = $client->getOrderReferenceDetails($getOrderReferenceDetailsRequest);
			//$this->debugLog("<pre>" . var_export($getOrderReferenceDetailsRequest, true) . "</pre>", __FUNCTION__, 'debug');
			//$this->debugLog("<pre>" . var_export($getOrderReferenceDetailsResponse, true) . "</pre>", __FUNCTION__, 'debug');
		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());
			$this->_session->clearAmazonSession();

			return FALSE;
		}

		return $getOrderReferenceDetailsResponse;
	}


	/**
	 * @param $client
	 * @param $total
	 * @param $backToPricesCurrency
	 * @return array
	 */
	private function getTotalInPaymentCurrency($client, $total, $backToPricesCurrency) {
		if(!class_exists('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . '/helpers/currencydisplay.php');
		}
		$virtuemart_currency_id = $this->getCurrencyId($client);
		$totalInPaymentCurrency = vmPSPlugin::getAmountValueInCurrency($total, $virtuemart_currency_id);
		//$this->debugLog($totalInPaymentCurrency, __FUNCTION__, 'debug');

		$cd = CurrencyDisplay::getInstance($backToPricesCurrency);

		return $totalInPaymentCurrency;
	}

	/**
	 * @param $client
	 * @return int
	 */
	private function getCurrencyId($client) {
		$currencyCode3 = $this->getCurrencyCode3($client);
		$virtuemart_currency_id = shopFunctions::getCurrencyIDByName($currencyCode3);

		return $virtuemart_currency_id;
	}

	/**
	 * @param $client
	 * @return mixed
	 */
	private function getCurrencyCode3($client) {
		return $client->getMerchantValues()->getCurrency();
	}



	/**
	 * @return mixed
	 */
	private function getStoreName() {
		if(!class_exists('VirtueMartModelVendor')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		}
		$virtuemart_vendor_id = 1;
		$vendorModel = VmModel::getModel('vendor');
		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);

		return $vendor->vendor_store_name;

	}

	/**
	 * @return null|string
	 */
	private function getPlatformId() {
		if($this->_currentMethod->region == "UK") {
			return "AA3KB5JD2CWIH";
		}
		if($this->_currentMethod->region == "DE") {
			return "A264YAJNGET7NB";
		}

		return NULL;
	}


	private function onErrorRedirectToCart($msg = NULL) {
		if(!$msg) {
			$msg = vmText::sprintf('VMPAYMENT_AMAZON_ERROR_TRY_AGAIN', $this->getVendorLink());
		} else {
		}
		$this->redirectToCart($msg, true);
	}

	private function redirectToCart($msg = NULL, $clearAmazonSession = false) {


		if($clearAmazonSession) {
			$this->_session->clearAmazonSession();
		}

		$app = JFactory::getApplication();
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&Itemid=' . vRequest::getInt('Itemid'), false), $msg);
	}

	private function getVendorLink() {
		return JRoute::_('index.php?option=com_virtuemart&view=vendor&layout=contact&virtuemart_vendor_id=' . $this->_currentMethod->virtuemart_vendor_id);
	}

	/**
	 *
	 */
	private function confirmOrderReference($client, $order) {

		$this->loadHelperClass('amazonHelperConfirmOrderReferenceResponse');
		$confirmOrderReferenceResponse = '';
		try {
			$confirmOrderReferenceRequest = new OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest();
			$confirmOrderReferenceRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
			$confirmOrderReferenceRequest->setSellerId($this->_currentMethod->sellerId);
			$confirmOrderReferenceResponse = $client->confirmOrderReference($confirmOrderReferenceRequest);

		} catch (Exception $e) {
			// here we may have an error code when "Invalid Payment Method", "The OrderReferenceId xxx has constraints PaymentPlanNotSet and cannot be confirmed."
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());
			$this->debugLog("<pre>" . var_export($confirmOrderReferenceRequest, true) . "</pre>", __FUNCTION__, 'debug');
			$this->debugLog("<pre>" . var_export($confirmOrderReferenceResponse, true) . "</pre>", __FUNCTION__, 'debug');
			return false;
		}

		$amazonHelperconfirmOrderReferenceResponse = new amazonHelperConfirmOrderReferenceResponse($confirmOrderReferenceResponse, $this->_currentMethod);
		$amazonHelperconfirmOrderReferenceResponse->onResponseUpdateOrderHistory($order);
		$storeInternalData = $amazonHelperconfirmOrderReferenceResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, $confirmOrderReferenceRequest, $confirmOrderReferenceResponse, NULL, NULL, $storeInternalData);


		return true;
	}

	/**
	 * @param $client
	 * @param $cart
	 * @param $order
	 */
	private function getAuthorization($client, $cart, $order, $redirect = true) {
		$shouldRetry = false;
		$retries = 0;

		$this->loadAmazonClass('OffAmazonPaymentsService_Model_AuthorizeRequest');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_Price');
		$this->loadHelperClass('amazonHelperAuthorizeResponse');
		do {
			$authorizeRequest = new OffAmazonPaymentsService_Model_AuthorizeRequest();
			$authorizeRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
			$authorizeRequest->setSellerId($this->_currentMethod->sellerId);

			$authorizeRequest->setAuthorizationReferenceId($this->_order_number);
			$authorizeRequest->setSellerAuthorizationNote(simNotes::getSellerAuthorizationNote($this->_currentMethod));
			$authorizeRequest->setTransactionTimeout($this->getAuthorizationTransactionTimeout());
			// directly do the capture without the need to call the Capture Request
			if($this->isCaptureImmediate($cart)) {
				$authorizeRequest->setCaptureNow(true);
			} else {
				$authorizeRequest->setCaptureNow(false);
			}
			$authorizeRequest->setAuthorizationAmount(new OffAmazonPaymentsService_Model_Price());
			//$this->_amount is already on payment currency
			$authorizeRequest->getAuthorizationAmount()->setAmount($this->_amount);
			$authorizeRequest->getAuthorizationAmount()->setCurrencyCode($this->getCurrencyCode3($client));

			try {
				$authorizeResponse = $client->authorize($authorizeRequest);
				$amazonAuthorizationId = $authorizeResponse->getAuthorizeResult()->getAuthorizationDetails()->getAmazonAuthorizationId();
				//$this->debugLog("ERREUR<pre>" . var_export($authorizeRequest, true) . "</pre>", __FUNCTION__, 'debug');
				//$this->debugLog("ERREUR<pre>" . var_export($authorizeResponse, true) . "</pre>", __FUNCTION__, 'debug');

			} catch (Exception $e) {
				$msg = "An exception was thrown when trying to do the authorization:" . $e->getMessage() . "\n" . $e->getTraceAsString();
				while ($e = $e->getPrevious()) {
					$msg .= ("Caused by: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "");
					$msg .= "\n";
				}
				if($redirect) {
					if(!$cart) {
						$cart = VirtueMartCart::getCart();
					}

					$cart->setOutOfCheckout();
					$this->debugLog($msg, __FUNCTION__ . " Exception", 'error');

					$this->amazonError(__FUNCTION__ . ' ' . $msg);
					$this->redirectToCart(vmText::_('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT'), true);
				}

				return false;
			}
			if($authorizationDetails = $this->getAuthorizeDetailsFromAuthorizeResponse($authorizeResponse)) {
				$this->updateAuthorizeBillingAddressInOrder($authorizationDetails, $order);
			}


			$amazonHelperAuthorizeResponse = new amazonHelperAuthorizeResponse($authorizeResponse, $this->_currentMethod);
			$amazonState = $amazonHelperAuthorizeResponse->onResponseUpdateOrderHistory($order);

			$storeInternalData = $amazonHelperAuthorizeResponse->getStoreInternalData();
			$this->storeAmazonInternalData($order, $authorizeRequest, $authorizeResponse, NULL, $this->renderPluginName($this->_currentMethod), $storeInternalData);

			$reasonCode = $authorizeResponse->getAuthorizeResult()->getAuthorizationDetails()->getAuthorizationStatus()->getReasonCode();
			if($redirect) {
				if($amazonState == 'Declined' && $reasonCode == 'InvalidPaymentMethod' && $this->_currentMethod->soft_decline == 'soft_decline_enabled') {
					$this->_session->incrementRetryInvalidPaymentMethodInSession();

					return false;

				} elseif(($amazonState == 'Open' && $reasonCode == 'AmazonRejected') or ($amazonState == 'Declined' && ($reasonCode == 'TransactionTimedOut' or $reasonCode == 'ProcessingFailure')) or ($amazonState == 'Closed' && $reasonCode == 'TransactionTimedOut')) {
					if ($amazonState == 'Closed' ) $retries =2; // closed by amazon, then don't retry
					if($retries < 2) {
						$shouldRetry = true;
						$retries++;
					} else {
						$this->doCancelPayment($this->_amazonOrderReferenceId);
						$cart->setOutOfCheckout();
						$this->leaveAmazonCheckout();
						$this->redirectToCart(vmText::_('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT'), true);
					}

				} elseif($amazonState == 'Declined') {
					$this->doCancelPayment($this->_amazonOrderReferenceId);
					$cart->setOutOfCheckout();
					$this->leaveAmazonCheckout();
					$this->redirectToCart(vmText::_('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT'), true);
				} elseif($amazonState == 'Closed' && $reasonCode == 'MaxCapturesProcessed') {
					$getAuthorizationDetails = $authorizeResponse->getAuthorizeResult()->getAuthorizationDetails();
					$this->closeAuthorization($getAuthorizationDetails->getAmazonAuthorizationId(), $order);
				}
			}

		} while ($shouldRetry and $redirect);


		return $amazonAuthorizationId;
	}


	private function onInvalidPaymentNewAuthorization() {

		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
		$this->loadVmClass('VirtueMartCart', JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

		$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(!$this->_amazonOrderReferenceId) {
			$this->onErrorRedirectToCart();

			return FALSE;
		}
		$retryInvalidPaymentMethod = $this->_session->incrementRetryInvalidPaymentMethodInSession();
		if($retryInvalidPaymentMethod > 3) {
			//echo "TOO MANY RETRIES STOP";
			$this->leaveAmazonCheckout();
			$this->redirectToCart(vmText::_('VMPAYMENT_AMAZON_SELECT_ANOTHER_PAYMENT'), true);

			return;
		}
		if(!($order_number = vRequest::getWord('order_number'))) {
			$this->debugLog('no order number in submit', __FUNCTION__, 'debug');

			return true;
		}

		if(!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			$this->debugLog('no getOrderIdByOrderNumber: ' . $order_number, __FUNCTION__, 'debug');

			return true;
		}
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$this->_amount = $this->getTotalInPaymentCurrency($this->getOffAmazonPaymentsService_Client(), $order['details']['BT']->order_total, $order['details']['BT']->order_currency);
		$this->_order_number = $this->getUniqueReferenceId($order['details']['BT']->order_number);
		$this->loadVmClass('VirtueMartCart', JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$cart = $cart = VirtueMartCart::getCart();
		$html = $this->vmConfirmedOrder($cart, $order, false);

		return $html;


	}

	function getAuthorizeDetailsFromAuthorizeResponse($authorizeResponse) {
		if($authorizeResponse == NULL) {
			vmError('Amazon : programming error ' . __FUNCTION__);

			return false;
		}
		if(!$authorizeResponse->isSetAuthorizeResult()) {
			return;
		}
		$authorizeResult = $authorizeResponse->getAuthorizeResult();
		if(!$authorizeResult->isSetAuthorizationDetails()) {
			return;
		}
		$authorizationDetails = $authorizeResult->getAuthorizationDetails();

		return $authorizationDetails;

	}

	function updateAuthorizeBillingAddressInOrder($authorizationDetails, $order) {
		if(!$authorizationDetails->isSetAuthorizationBillingAddress()) {
			return;
		}

		$authorizationBillingAddress = $authorizationDetails->getAuthorizationBillingAddress();
		$BT = $this->getUserInfoFromAmazon($authorizationBillingAddress);

		$orderModel = VmModel::getModel('orders');
		$BT['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$order_userinfosTable = $orderModel->getTable('order_userinfos');
		$BT['address_type'] = 'BT';
		$order_userinfosTable->emptyCache();
		$order_userinfosTable->load($order['details']['BT']->virtuemart_order_id, 'virtuemart_order_id', " AND address_type='BT'");
		if(!$order_userinfosTable->bindChecknStore($BT, true)) {
			vmError($order_userinfosTable->getError());

			return false;
		}

	}

	/**
	 * @return int
	 */
	function getAuthorizationTransactionTimeout() {
		if($this->_currentMethod->erp_mode == "erp_mode_disabled") {
			if($this->_currentMethod->authorization_mode_erp_disabled == "automatic_synchronous") {
				return 0;
			} else {
				return self::AUTHORIZE_TRANSACTION_TIMEOUT;
			}
		} else {
			if($this->_currentMethod->authorization_mode_erp_enabled == "automatic_synchronous") {
				return 0;
			} else {
				return self::AUTHORIZE_TRANSACTION_TIMEOUT;
			}
		}
	}


	public function plgVmOnUpdateOrderPayment(&$order, $old_order_status) {
		static $updateOrderPaymentNumber = 0;
		// we don't do anything from the front end
		if(JFactory::getApplication()->isSite()) {
			//return NULL;
		}
		//Load the method
		if(!($this->_currentMethod = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if(!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}
		if($this->isERPModeEnabled()) {
			return;
		}
		if(!$this->isValidUpdateOrderStatus($order->order_status)) {
			if(!JFactory::getApplication()->isSite()) {
				vmError(vmText::_('VMPAYMENT_AMAZON_UPDATEPAYMENT_NO_ACTION'));
			}

			return;
		}
		if($updateOrderPaymentNumber > 10) {
			// todo: display message
			$updateOrderPaymentNumber = 0;
			sleep(5);
		}
		//Load the payments
		if(!($payments = $this->getDatasByOrderId($order->virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}
		$orderModel = VmModel::getModel('orders');
		$orderModelData = $orderModel->getOrder($order->virtuemart_order_id);
		$this->_amount = $payments[0]->payment_order_total; //  in payment currency
		$this->_order_number = $this->getUniqueReferenceId($orderModelData['details']['BT']->order_number);


		if($order->order_status == $this->_currentMethod->status_refunded and $this->canDoRefund($payments, $orderModelData)) {
			return $this->refundPayment($payments, $orderModelData);
		} elseif($order->order_status == $this->_currentMethod->status_capture and $this->canDoCapture($payments, $orderModelData)) {
			return $this->capturePayment($payments, $orderModelData);
		} elseif($order->order_status == $this->_currentMethod->status_cancel and $this->canDoCancel($payments, $orderModelData)) {
			return $this->cancelPayment($payments, $orderModelData);
		}
		$updateOrderPaymentNumber++;

		return false;
	}

	/**
	 * @param $payments
	 * @return bool
	 */

	private function getCaptureState($payments, $order) {
		$amazonCaptureId = $this->getAmazonCaptureId($payments);
		if(!$amazonCaptureId) {
			return false;
		}
		$captureDetailsResponse = $this->getCaptureDetails($amazonCaptureId, $order);
		$this->loadHelperClass('amazonHelperGetCaptureDetailsResponse');
		$amazonHelperCaptureDetailsResponse = new amazonHelperGetCaptureDetailsResponse($captureDetailsResponse, $this->_currentMethod);
		$captureState = $amazonHelperCaptureDetailsResponse->getState();

		$storeInternalData = $amazonHelperCaptureDetailsResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, NULL, $captureDetailsResponse, NULL, $this->renderPluginName($this->_currentMethod), $storeInternalData);

		return $captureState;
	}

	/**
	 * @param $payments
	 * @return bool
	 */

	private function canDoCancel($payments, $order) {
		$lastPayments = $payments[count($payments) - 1];
		// return when InvalidPaymentMethod
		if($lastPayments->amazon_response_state != "Suspended") {
			return true;
		}

		return false;
	}

	/**
	 * @param $payments
	 * @return bool
	 */

	private function canDoRefund($payments, $order) {
		$captureState = $this->getCaptureState($payments, $order);
		if($captureState === false) {
			vmInfo('VMPAYMENT_AMAZON_UPDATEPAYMENT_NOAMAZONCAPTUREID');

			return;
		}
		if($captureState != 'Completed') {
			vmInfo(vmText::sprintf('VMPAYMENT_AMAZON_UPDATEPAYMENT_CANTDOREFUND', $captureState));

			return false;
		}

		return true;
	}


	private function getAuthorizationState($payments, $order) {
		$amazonAuthorizationId = $this->getAmazonAuthorizationId($payments);
		if(!$amazonAuthorizationId) {
			return false;
		}
		$authorizationDetailsResponse = $this->getAuthorizationDetails($amazonAuthorizationId, $order);

		if(!$authorizationDetailsResponse) {
			return NULL;
		} //catch errors
		$this->loadHelperClass('amazonHelperGetAuthorizationDetailsResponse');
		$amazonHelperAuthorizationDetailsResponse = new amazonHelperGetAuthorizationDetailsResponse($authorizationDetailsResponse, $this->_currentMethod);
		$authorizationState = $amazonHelperAuthorizationDetailsResponse->getState();

		$storeInternalData = $amazonHelperAuthorizationDetailsResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, NULL, $authorizationDetailsResponse, NULL, $this->renderPluginName($this->_currentMethod), $storeInternalData);


		return $authorizationState;
	}

	/**
	 * if authorization object is in Open State, then the funds can be captured
	 */
	private function canDoCapture($payments, $order) {
		$authorizationState = $this->getAuthorizationState($payments, $order);
		if(!$authorizationState) {
			return false;
		}
		if($authorizationState != 'Open') {
			vmInfo(vmText::sprintf('VMPAYMENT_AMAZON_UPDATEPAYMENT_CANTDOCAPTURE', $authorizationState));

			return false;
		}

		return true;
	}


	/**
	 * if authorization object is in Open State, then the funds can be captured
	 */
	private function canDoAuthorization($payments, $order) {
		$this->_amazonOrderReferenceId = $this->getAmazonOrderReferenceIdFromPayments($payments);
		$orderReferencestate = $this->getOrderReferenceState();

		if($orderReferencestate != 'Open') {
			return false;
		}

		return true;
	}


	private function configCanDoAuthorization() {
		if($this->_currentMethod->erp_mode == "erp_mode_disabled" OR ($this->_currentMethod->erp_mode == "erp_mode_enabled" AND $this->_currentMethod->authorization_mode_erp_enabled != "authorization_done_by_erp")) {
			return true;
		} else {
			return false;
		}
	}


	private function doCancelPayment($amazonOrderReferenceId) {
		$this->cancelPayment(NULL, NULL, $amazonOrderReferenceId);
	}

	/**
	 * @param $payments
	 * @param $order
	 * if orderstate == Open, Suspended ==> closeOrderReference
	 * of orderstate= draft, open, , and no pending, completed, closed captures CancelOrderReference
	 */
	private function cancelPayment($payments, $order, $amazonOrderReferenceId=NULL) {
		$cancelOrderReferenceRequest = new OffAmazonPaymentsService_Model_CancelOrderReferenceRequest();
		if ($amazonOrderReferenceId==NULL) {
			$amazonOrderReferenceId = $this->getAmazonOrderReferenceId($payments);
		}

		$cancelOrderReferenceRequest->setSellerId($this->_currentMethod->sellerId);
		$cancelOrderReferenceRequest->setAmazonOrderReferenceId($amazonOrderReferenceId);
		$client = $this->getOffAmazonPaymentsService_Client();
		try {
			$client->cancelOrderReference($cancelOrderReferenceRequest);
			$this->debugLog("cancelPayment <pre>" . var_export($cancelOrderReferenceRequest, true) . "</pre>", __FUNCTION__, 'debug');
		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return FALSE;
		}
		$this->storeAmazonInternalData($order, $cancelOrderReferenceRequest, NULL, NULL, $this->renderPluginName($this->_currentMethod), NULL, NULL, $this->_amount);

	}

	private function capturePayment($payments, $order) {

		$amazonAuthorizationId = $this->getAmazonAuthorizationId($payments);

		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CaptureRequest');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_Price');
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		$captureRequest = new OffAmazonPaymentsService_Model_CaptureRequest();
		$captureRequest->setSellerId($this->_currentMethod->sellerId);
		$captureRequest->setAmazonAuthorizationId($amazonAuthorizationId);

		$captureRequest->setCaptureReferenceId($this->_order_number);
		$captureRequest->setCaptureAmount(new OffAmazonPaymentsService_Model_Price());
		$captureRequest->getCaptureAmount()->setAmount($this->_amount);
		$captureRequest->getCaptureAmount()->setCurrencyCode($this->getCurrencyCode3($client));

		try {
			$captureResponse = $client->capture($captureRequest);
			$amazonCaptureId = $captureResponse->getCaptureResult()->getCaptureDetails()->getAmazonCaptureId();
			$this->debugLog("<pre>" . var_export($captureRequest, true) . "</pre>", __FUNCTION__, 'debug');
			$this->debugLog("<pre>" . var_export($captureResponse, true) . "</pre>", __FUNCTION__, 'debug');

		} catch (Exception $e) {
			$msg = $e->getMessage();
			$log = "An exception was thrown when trying to capture payment:" . $e->getMessage() . "\n" . $e->getTraceAsString();
			while ($e = $e->getPrevious()) {
				$log .= ("Caused by: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "");
				$msg .= "Reason: " . $e->getMessage() . "<br />";
				$log .= "\n";
			}
			$this->debugLog($log, __FUNCTION__, 'debug');
			$this->amazonError(__FUNCTION__ . ' ' . $msg);

			return false;
		}
		$this->loadHelperClass('amazonHelperCaptureResponse');
		$amazonHelperCaptureResponse = new amazonHelperCaptureResponse($captureResponse, $this->_currentMethod);
		$amazonHelperCaptureResponse->onResponseUpdateOrderHistory($order);
		//$orderModel = VmModel::getModel('orders');

		//$orderModel->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, TRUE);
		$storeInternalData = $amazonHelperCaptureResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, $captureRequest, $captureResponse, NULL, $this->renderPluginName($this->_currentMethod), $storeInternalData, NULL, $this->_amount);

		$amazonState = $amazonHelperCaptureResponse->getState();
		if($amazonState == "Completed") {
			$amazonOrderReferenceId = $this->getAmazonOrderReferenceId($payments);
			if(!$amazonOrderReferenceId) {
				vmError('VMPAYMENT_AMAZON_UPDATEPAYMENT_NOAMAZONORDERREFERENCEID');
				return false;
			}
			$this->closeOrderReference($amazonOrderReferenceId, $order);
		}


		return $amazonCaptureId;
	}

	private function refundPayment($payments, $order) {

		$amazonCaptureId = $this->getAmazonCaptureId($payments);
		if(empty($amazonCaptureId)) {
			vmError(vmText::_('VMPAYMENT_AMAZON_UPDATEPAYMENT_NOAMAZONCAPTUREID'));

			return false;
		}

		$this->loadAmazonClass('OffAmazonPaymentsService_Model_RefundRequest');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_Price');

		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		$refund = new OffAmazonPaymentsService_Model_Price();
		$refund->setCurrencyCode($this->getCurrencyCode3($client));
		$refund->setAmount($this->_amount);

		$refundRequest = new OffAmazonPaymentsService_Model_RefundRequest();
		$refundRequest->setSellerId($this->_currentMethod->sellerId);
		$refundRequest->setAmazonCaptureId($amazonCaptureId);
		$refundRequest->setRefundReferenceId($this->getUniqueReferenceId($order['details']['BT']->order_number)); // random string
		$refundRequest->setSellerRefundNote(simNotes::getSellerRefundNote($this->_currentMethod));
		$refundRequest->setRefundAmount($refund);
		try {
			$refundResponse = $client->refund($refundRequest);
			$amazonRefundId = $refundResponse->getRefundResult()->getRefundDetails()->getAmazonRefundId();
			$this->debugLog("<pre>" . var_export($refundRequest, true) . "</pre>", __FUNCTION__, 'debug');
			$this->debugLog("<pre>" . var_export($refundResponse, true) . "</pre>", __FUNCTION__, 'debug');

		} catch (Exception $e) {
			$msg = $e->getMessage();
			$log = "An exception was thrown when trying to refund payment:" . $e->getMessage() . "\n" . $e->getTraceAsString();
			if($this->_currentMethod->debug) {
				while ($e = $e->getPrevious()) {
					$log .= ("Caused by: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "");
					$msg .= "Reason: " . $e->getMessage() . "<br />";
					$log .= "\n";
				}
				$this->debugLog($log, __FUNCTION__, 'debug');
			}

			vmError(__FUNCTION__ . ' ' . $msg);

			return false;
		}
		$this->loadHelperClass('amazonHelperRefundResponse');
		$amazonHelperRefundResponse = new amazonHelperRefundResponse($refundResponse, $this->_currentMethod);

		$storeInternalData = $amazonHelperRefundResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, $refundRequest, $refundResponse, NULL, $this->renderPluginName($this->_currentMethod), $storeInternalData, NULL, $refund);

		// refund will issue a notification in all cases. The notification will inform if the refund operation has been accepted or not.
		// so the order status is updated but the customer is not notified.
		// He will be notified when the notification arrives
		vRequest::setVar('customer_notified', 0);
		$orders = vRequest::getVar('orders');
		$virtuemart_order_id=$payments[0]->virtuemart_order_id;
		if (isset($orders[$virtuemart_order_id]) and isset($orders[$virtuemart_order_id]['customer_notified']))
			vRequest::setVar($orders[$virtuemart_order_id]['customer_notified'], 0);
		return true;
	}


	function getOrderReferenceState() {
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return false;
		}

		$getOrderReferenceDetailsResponse = $this->getOrderReferenceDetails($client);
		if($getOrderReferenceDetailsResponse) {
			return $getOrderReferenceDetailsResponse->getGetOrderReferenceDetailsResult()->getOrderReferenceDetails()->getOrderReferenceStatus()->getState();
		}

		return false;
	}

	/**
	 * Capture reference Id must always be unique
	 * @param $order_number
	 * @return string
	 */
	private function getUniqueReferenceId($order_number) {
		return $order_number . '-' . time();
	}

	function getAuthorizationResponse($payments) {
		// we don't care, may be he has change his payment config
		/*
	if($this->_currentMethod->capture_mode=='immediate_capture') {
		vmError(vmText::_('VMPAYMENT_AMAZON_UPDATEPAYMENT_CAPTUREMODE_IMMEDIATE'));
		return false;
	}
	*/
		foreach ($payments as $payment) {
			if($payment->amazon_class_response_type == 'OffAmazonPaymentsService_Model_AuthorizeResponse' or $payment->amazon_class_response_type == 'OffAmazonPaymentsNotifications_Model_authorizationNotification') {
				return $payment;
			}
		}
		vmError(vmText::_('VMPAYMENT_AMAZON_UPDATEPAYMENT_NOAMAZONAUTHORIZATIONID'));

		return false;
	}

	/**
	 *
	 */
	function getAmazonOrderReferenceId($payments) {

		//$payments_reverse = array_reverse($payments);
		foreach ($payments as $payment) {
			if(!empty($payment->amazonOrderReferenceId) and ($payment->amazonOrderReferenceId != NULL)) {
				return $payment->amazonOrderReferenceId;
			}
		}

		return NULL;
	}

	/**
	 * if amount = 0 then it is a full capture
	 * otherwise it is a partial capture
	 * in this case if a capture has already been done, a new authorization for that amount is requested
	 */
	function getAmazonAuthorizationId($payments) {

		$payments_reverse = array_reverse($payments);
		foreach ($payments_reverse as $payment) {
			if(!empty($payment->amazon_response_amazonAuthorizationId)) {
				return $payment->amazon_response_amazonAuthorizationId;
			}
		}

		return NULL;
	}


	/**
	 * @param $payments
	 * @return null
	 */
	function getAmazonCaptureId($payments) {
		$this->loadAmazonServicesClasses();
		$this->loadAmazonNotificationClasses();
		$payments_reverse = array_reverse($payments);
		foreach ($payments_reverse as $payment) {
			if(!empty($payment->amazon_response_amazonCaptureId)) {
				return $payment->amazon_response_amazonCaptureId;
			}
		}

		return NULL;
	}

	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $paymentCurrencyId
	 * @return bool
	 */
	function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if(!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if(!$this->selectedThisElement($method->payment_element)) {
			return FALSE;
		}
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		$method->payment_currency = $this->getCurrencyId($client);

		$paymentCurrencyId = $method->payment_currency;

		return TRUE;
	}

	/**
	 * @param $html
	 * @return bool|null
	 */
	function plgVmOnPaymentResponseReceived(&$html) {

		$this->loadVmClass('VirtueMartCart', JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

		if(!class_exists('shopFunctionsF')) {
			require(JPATH_VM_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');


		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);

		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);

		if(!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if(!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}

		$html = "We will send you an email confirmation with your order details shortly";
		$html .= "Our order number";
		$html .= "Amazon Reference";
		$html .= "Go tot payments.amazon... to see your payment history and other account information.";
		vRequest::setVar('display_title', false);
		vRequest::setVar('html', $html);

		return true;
	}

	function plgVmOnUserPaymentCancel() {

		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');


		$order_number = vRequest::getUword('on');
		if(!$order_number) {
			return FALSE;
		}

		if(!$virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number)) {
			return NULL;
		}
		if(!($paymentTable = $this->getDataByOrderId($virtuemart_order_id))) {
			return NULL;
		}

		$session = JFactory::getSession();
		$return_context = $session->getId();
		$field = $this->_name . '_custom';
		if(strcmp($paymentTable->$field, $return_context) === 0) {
			$this->handlePaymentUserCancel($virtuemart_order_id);
		}

		return TRUE;
	}

	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id) {

		if(!$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if(!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		$payments = $this->getDatasByOrderId($virtuemart_order_id);
		$html = '<table class="adminlist table"  >' . "\n";
		$html .= $this->getHtmlHeaderBE();
		$html .= $this->showActionOrderBEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, $payments);
		$html .= $this->showOrderBEPayment($virtuemart_order_id, $payments);
		$html .= '</table>' . "\n";

		return $html;
	}

	private function showActionOrderBEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, $payments) {
		//return;

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$options = array();
		$options[] = JHTML::_('select.option', 'capturePayment', JText::_('VMPAYMENT_AMAZON_ORDER_BE_CAPTURE'), 'value', 'text');

		$options[] = JHTML::_('select.option', 'refundPayment', JText::_('VMPAYMENT_AMAZON_ORDER_BE_REFUND'), 'value', 'text');
		$options[] = JHTML::_('select.option', 'newAuthorization', JText::_('VMPAYMENT_AMAZON_ORDER_BE_NEW_AUTHORIZATION'), 'value', 'text');
		$actionList = JHTML::_('select.genericlist', $options, 'action', '', 'value', 'text', 'capturePayment', 'action', true);


		//$html = '<table class="adminlist table-striped"  >' . "\n";
		$html = '';
		$html .= '<form action="index.php" method="post" name="updateOrderBEPayment" id="updateOrderBEPayment">';

		$html .= '<tr ><td >';
		$html .= $actionList;
		$html .= ' </td><td>';
		$html .= '<input type="text" id="amount" name="amount" size="20" value="" class="required" maxlength="25"  placeholder="' . vmText::sprintf('VMPAYMENT_AMAZON_ORDER_BE_AMOUNT', shopFunctions::getCurrencyByID($payments[0]->payment_currency, 'currency_code_3')) . '"/>';
		$html .= '<input type="hidden" name="type" value="vmpayment"/>';
		$html .= '<input type="hidden" name="name" value="amazon"/>';
		$html .= '<input type="hidden" name="view" value="plugin"/>';
		$html .= '<input type="hidden" name="option" value="com_virtuemart"/>';
		$html .= '<input type="hidden" name="virtuemart_order_id" value="' . $virtuemart_order_id . '"/>';
		$html .= '<input type="hidden" name="virtuemart_paymentmethod_id" value="' . $virtuemart_paymentmethod_id . '"/>';
		$html .= '<span class="icon-nofloat vmicon vmicon-16-save"></span>';
		$html .= '<a class="updateOrderBEPayment" href="#"  >' . Jtext::_('COM_VIRTUEMART_SAVE') . '</a>';
		$html .= '</form>';
		$html .= ' </td></tr>';

		$doc = JFactory::getDocument();

		$doc->addScriptDeclaration("
	//<![CDATA[

	jQuery(document).ready( function($) {
	/*
		jQuery('#action').change(function() {
			if ($('#action').val()=='authorizationNew') {
				$('#amount').hide();
			} else {
				$('#amount').show();
			}
		});
		*/
		jQuery('.updateOrderBEPayment').click(function() {
			document.updateOrderBEPayment.submit();
			return false;

});
});
//]]>
");

		//$html .= '</table>'  ;
		return $html;

	}

	private function showOrderBEPayment($virtuemart_order_id, $payments) {


		$db = JFactory::getDBO();
		$query = 'SHOW COLUMNS FROM `' . $this->_tablename . '` ';
		$db->setQuery($query);
		$columns = $db->loadColumn(0);

		$html = '';
		$first = TRUE;
		$lang = JFactory::getLanguage();
		foreach ($payments as $payment) {
			if($payment->amazon_class_request_type) {
				$this->loadAmazonClass($payment->amazon_class_request_type);
			}
			if($payment->amazon_class_response_type) {
				$this->loadAmazonClass($payment->amazon_class_response_type);
			}
			if($payment->amazon_class_notification_type) {
				$this->loadAmazonClass($payment->amazon_class_notification_type);
			}

			$html .= '<tr class="row1"><td><strong>' . vmText::_('VMPAYMENT_AMAZON_DATE') . '</strong></td><td align="left"><strong>' . $payment->created_on . '</strong></td></tr>';
			// Now only the first entry has this data when creating the order
			if($first) {
				$html .= $this->getHtmlRowBE('AMAZON_PAYMENT_NAME', $payment->payment_name);
				// keep that test to have it backwards compatible. Old version was deleting that column  when receiving an IPN notification
				if($payment->payment_order_total and $payment->payment_order_total != 0.00) {
					$html .= $this->getHtmlRowBE('COM_VIRTUEMART_TOTAL', ($payment->payment_order_total) . " " . shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));
				}
				if($payment->email_currency and $payment->email_currency != 0) {
					//$html .= $this->getHtmlRowBE($this->_name.'_PAYMENT_EMAIL_CURRENCY', shopFunctions::getCurrencyByID($payment->email_currency, 'currency_code_3'));
				}

				$first = FALSE;
			} else {


				$amazon_classes = array();
				/*
			if (!empty($payment->amazon_request)) {
				$amazon_data = unserialize($payment->amazon_request);
				$amazon_classes[get_class($amazon_data)] = $payment->amazon_request;
			}
			*/
				$this->loadAmazonServicesClasses();
				$this->loadAmazonNotificationClasses();
				if(!empty($payment->amazon_request)) {
					$amazon_classes[$payment->amazon_class_request_type] = $payment->amazon_request;
					$vmClass = $this->getVmClass($payment->amazon_class_request_type);
					$html .= $this->getHtmlRowBE(vmText::_('VMPAYMENT_AMAZON_REQUEST_TYPE'), vmText::_('VMPAYMENT_AMAZON_REQUEST_TYPE_' . strtoupper($vmClass)));
					$transactionLogContent = $this->getTransactionLogContent($payment->amazon_class_request_type, $payment->amazon_request, $payment);
					if(empty($transactionLogContent)) {
						vmError("getTransactionLogContent" . $payment->amazon_class_request_type . ' ');
						vmError("getTransactionLogContent" . $payment->amazon_request . ' ');
					}
					$html .= $transactionLogContent;
				}
				if(!empty($payment->amazon_response)) {
					$vmClass = $this->getVmClass($payment->amazon_class_response_type);
					$amazon_classes[$payment->amazon_class_response_type] = $payment->amazon_response;
					$html .= $this->getHtmlRowBE(vmText::_('VMPAYMENT_AMAZON_RESPONSE_TYPE'), vmText::_('VMPAYMENT_AMAZON_RESPONSE_TYPE_' . strtoupper($vmClass)));
					$html .= $this->getResponseData($payment);
					$html .= $this->getTransactionLogContent($payment->amazon_class_response_type, $payment->amazon_response, $payment);
				} elseif(!empty($payment->amazon_notification)) {
					$amazon_classes[$payment->amazon_class_notification_type] = $payment->amazon_notification;
					$vmClass = $this->getVmClass($payment->amazon_class_notification_type);
					$html .= $this->getHtmlRowBE(vmText::_('VMPAYMENT_AMAZON_NOTIFICATION_TYPE'), vmText::_('VMPAYMENT_AMAZON_NOTIFICATION_TYPE_' .strtoupper($vmClass) ));
					$html .= $this->getResponseData($payment);
					$html .= $this->getTransactionLogContent($payment->amazon_class_notification_type, $payment->amazon_notification, $payment);
				}

			}
		}


		$doc = JFactory::getDocument();
		$js = "
jQuery().ready(function($) {
$('.amazonLogOpener').click(function() {
	var logId = $(this).attr('rel');
	$('#amazonLog_'+logId).toggle();
	return false;
});
$('.amazonDetailsOpener').click(function() {
	var detailsId = $(this).attr('rel');
	$('#amazonDetails_'+detailsId).toggle();
	return false;
});
});";
		$doc->addScriptDeclaration($js);

		return $html;
	}


	function getResponseData($payment) {
		$html = '';
		$code = 'amazon_response_';
		foreach ($payment as $key => $value) {
			// only displays if there is a value or the value is different from 0.00 and the value
			if($value) {
				if(substr($key, 0, strlen($code)) == $code) {
					$html .= $this->getHtmlRowBE($key, $value);
				}
			}
		}

		return $html;
	}

	function getTransactionLogContent($amazon_class, $amazon_data_serialized, $payment) {
		$html = '';
		$this->loadAmazonClass($amazon_class);
		$vmClass = $this->getVmClass($amazon_class);
		$vmClassName = 'amazonHelper' . $vmClass;
		$amazon_data = unserialize($amazon_data_serialized);
		$this->loadHelperClass($vmClassName);
		$html .= '<tr><td>';

		if(!class_exists($vmClassName)) {
			vmError(__FUNCTION__ . ' Programming error: class name does not exist:' . $vmClassName);

			return NULL;

		} elseif(empty($vmClass)) {
			vmError(__FUNCTION__ . ' Programming error: class  does not exist:' . $vmClass);

			return NULL;

		} else {
			$obj = new $vmClassName($amazon_data, $this->_currentMethod);
			$contents = $obj->getContents();

			if(!empty($contents)) {
				$html .= '<a href="#" class="amazonDetailsOpener"   rel="' . $payment->id . $vmClass . '">';
				//$html .= '<div style="background-color: white; z-index: 100; right:0; display: none; border:solid 2px; padding:10px;" class="vm-absolute" id="amazonDetails_' . $payment->id . '">';
				//$html .= ' </div>';
				$html .= ' <span class="icon-nofloat vmicon vmicon-16-xml"></span>&nbsp;';
				$html .= vmText::_('VMPAYMENT_AMAZON_VIEW_TRANSACTION_DETAILS');
				$html .= '  </a>';
				$html .= '<div  style="display:none;" id="amazonDetails_' . $payment->id . $vmClass . '">';
				$html .= $contents;
				$html .= ' </div>';


			}
		}

		$html .= ' </td>';
		$html .= ' <td>';

		if($this->_currentMethod->debug) {
			//$html .= '<tr><td></td><td>' . $amazon_class . '
			$html .= '<td>' . '
<a href="#" class="amazonLogOpener" rel="' . $payment->id . $vmClass . '" >
<div style="background-color: white; z-index: 100; right:0; display: none; border:solid 2px; padding:10px;" class="vm-absolute" id="amazonLog_' . $payment->id . $vmClass . '">';
			$html .= "<pre>" . var_export($amazon_data, true) . "</pre>";
			//$html .= $obj->getContents();

			$html .= ' </div>
<span class="icon-nofloat vmicon vmicon-16-xml"></span>&nbsp;';
			$html .= vmText::_('VMPAYMENT_AMAZON_VIEW_TRANSACTION_LOG');
			$html .= '  </a>';

		}
		$html .= ' </td></tr>';

//}

		return $html;


	}

	function getVmClass($amazonClass) {
		$pos = strrpos($amazonClass, '_');
		$vmClass = substr($amazonClass, $pos + 1);

		return $vmClass;
	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 * @author Valérie Isaksen
	 *
	 */
	public function plgVmOnStoreInstallPaymentPluginTable($jplugin_id) {
		if($res = $this->selectedThisByJPluginId($jplugin_id)) {

			$virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id');
			$method = $this->getPluginMethod($virtuemart_paymentmethod_id);
			//vmdebug('plgVmOnStoreInstallPaymentPluginTable', $method, $virtuemart_paymentmethod_id);

			if(!extension_loaded('curl')) {
				vmError(vmText::sprintf('VMPAYMENT_AMAZON_CONF_MANDATORY_PHP_EXTENSION', 'curl'));
			}
			if(!extension_loaded('openssl')) {
				vmError(vmText::sprintf('VMPAYMENT_AMAZON_CONF_MANDATORY_PHP_EXTENSION', 'openssl'));
			}
		}

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
		if(!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL;
		}
		if(!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			$this->_session->clearAmazonSession();

			return NULL;
		}
		$_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(!$_amazonOrderReferenceId) {
			//$msg = vmText::_('VMPAYMENT_AMAZON_PAYWITHAMAZON_BUTTON');
			return false;
		}

		return TRUE; // this method was selected , and the data is valid by default


	}

	function removeAmazonAddressFromCart($cart) {
		$data = $this->_session->getDataFromSession();
		if(isset($data['BT'])) {
			$data['BT']['address_type'] = 'BT';
			$cart->saveAddressInCart($data['BT'], $data['BT']['address_type'], TRUE);
		}
		if(isset($data['ST'])) {
			$data['ST']['address_type'] = 'ST';
			$cart->saveAddressInCart($data['ST'], $data['ST']['address_type'], TRUE);
		}

		return;
	}

	/**
	 *
	 */
	 public function displayListFE(VirtueMartCart $cart, $selected = 0, &$htmlIn){

	 	static $c = true;
	 	if($c){
	 		parent::displayListFE($cart, $selected, $htmlIn);
			$c = false;
	 	}

	 }

	/**
	 * plgVmDisplayListFEPayment
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
	 *
	 * @param object $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
	 * @author Valerie Isaksen
	 */
	public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn) {
		return $this->displayListFE($cart, $selected, $htmlIn);
	}

	/**
	 * Used when the vmOPC is set to OFF
	 * @param $cart
	 * @param $payment_advertise
	 * @return null
	 * In case OPC is off: the login widget is displayed on the cart, and on the payment list
	 */
	function plgVmOnCheckoutAdvertise($cart, &$payment_advertise) {
		if(vmConfig::get('oncheckout_opc') == 0) {
			$html = NULL;
			$this->displayListFE($cart, $cart->virtuemart_paymentmethod_id, $html);
		}
	}

	function plgVmDisplayLogin(VmView $view, &$html, $from_cart = FALSE) {

		// only to display it in the cart, not in list orders view
		if(!$from_cart) {
			return NULL;
		}

		if(!class_exists( 'VirtueMartCart' )) {
			require(VMPATH_SITE.DS.'helpers'.DS.'cart.php');
		}

		$cart = VirtueMartCart::getCart();
		if($this->getPluginMethods( $cart->vendorId ) === 0) {
			return FALSE;
		}

		if(!($selectedMethod = $this->getVmPluginMethod( $cart->virtuemart_paymentmethod_id ))) {
			return FALSE;
		}
		$arrayIn= array();
		if(empty($cart->prices)){

			$cart->prepareCartData();
		}
		$this->displayListFE($cart, $cart->virtuemart_paymentmethod_id, $arrayIn);

	}

	/**
	 * @param $plugin
	 * @param $selectedPlugin
	 * @param $pluginSalesPrice
	 * @return string
	 */
	protected function getPluginHtml($method, $selectedPlugin, $pluginSalesPrice) {

		if($selectedPlugin == $method->virtuemart_paymentmethod_id) {
			$checked = 'checked="checked"';
			//return NULL;
		} else {
			$checked = '';
		}

		$this->_currentMethod = $method;
		$html = '';
		if(!class_exists('CurrencyDisplay')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$currency = CurrencyDisplay::getInstance();
		$costDisplay = "";
		if($pluginSalesPrice) {
			$costDisplay = $currency->priceDisplay($pluginSalesPrice);
			$costDisplay = '<span class="' . $this->_type . '_cost"> (' . JText::_('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') . $costDisplay . ")</span>";
		}
		//$html = '<input type="radio" name="virtuemart_paymentmethod_id" id="' . $this->_psType . '_id_' . $plugin->virtuemart_paymentmethod_id . '"   value="' . $plugin->virtuemart_paymentmethod_id . '" ' . $checked . ">\n". '<label for="' . $this->_psType . '_id_' . $plugin->virtuemart_paymentmethod_id . '">' . '<span class="' . $this->_type . '">' . $plugin->payment_name . $costDisplay . "</span></label>\n";

		// IF NOT SELECTED Then display the Pay With amazon Button
		$this->loadVmClass('VirtueMartCart', JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');

		//$this->debug('', 'updateCartWithAmazonAddress', 'debug');
		$cart = VirtueMartCart::getCart();
		$cartLayout = $cart->layout;
		if(empty($cart->products) and $cartLayout == $this->_name) {
			//$this->unsetCartLayoutAndPaymentMethod($cart);
			$this->leaveAmazonCheckout();
		}
		$amazonOrderReferenceIdWeight = $this->_session->getAmazonOrderReferenceIdWeightFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if($amazonOrderReferenceIdWeight) {
			if(isset($amazonOrderReferenceIdWeight['_amazonOrderReferenceId'])) {
				$this->_amazonOrderReferenceId = $amazonOrderReferenceIdWeight['_amazonOrderReferenceId'];
			}
			$referenceIdIsOnlyDigitalGoods = false;
			if(isset($amazonOrderReferenceIdWeight['isOnlyDigitalGoods'])) {
				$referenceIdIsOnlyDigitalGoods = $amazonOrderReferenceIdWeight['isOnlyDigitalGoods'];
			}
		}
		//vmdebug('checkConditionSignIn', $this->_currentMethod);
		if(!$this->_amazonOrderReferenceId OR $this->shouldLoginAgain($referenceIdIsOnlyDigitalGoods, $this->isOnlyDigitalGoods($cart))) {
			$html .= $this->renderSignInButton($cart);
		}

// amazon is not listed in the payment list. JS displays the signin button
		return $html;
	}


	/**
	 * reset the cart layout, unset the paymentmethod, put back the storeAddress
	 */
	private function leaveAmazonCheckout($msg = NULL) {
		if(!class_exists('VmConfig')) {
			require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
		}

		$cart = VirtueMartCart::getCart();
		$cart->layout = VmConfig::get('cartlayout', 'default');
		$cart->layoutPath = '';
		$cart->virtuemart_paymentmethod_id = 0;
		$previousAddress = $this->_session->getBTandSTFromSession();
		$cart->BT = $previousAddress['BT'];
		$cart->ST = $previousAddress['ST'];
		$cart->prepareAddressFieldsInCart(); // in VM2 prepareAddressDataInCart

		$cart->setCartIntoSession();
		$cart->setOutOfCheckout();
		$this->_session->clearAmazonSession();
		if($msg) {
			$app = JFactory::getApplication();
			$app->enqueueMessage($msg);
		}

		return;
	}


	private function setCartLayout($cart, $intoSession = true) {
		if(!class_exists('VmConfig')) {
			require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'helpers' . DS . 'config.php');
		}
		VmConfig::loadConfig();

		$cart->layoutPath = vmPlugin::getTemplatePath($this->_name, 'payment', 'cart');
		$cart->layout = 'cart';
		if($intoSession) {
			$cart->setCartIntoSession();
		}

	}

	/**
	 * plgVmonSelectedCalculatePricePayment
	 * Calculate the price (value, tax_id) of the selected method
	 * It is called by the calculator
	 * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
	 * @cart: VirtueMartCart the current cart
	 * @cart_prices: array the new cart prices
	 * @return null if the method was not selected, false if the shipping rate is not valid any more, true otherwise
	 *
	 *
	 */
	private static $cartPriceUpdatedDone = false;

	public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices,
		&$cart_prices_name) {

		if(!($this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		if(!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			$this->_session->clearAmazonSession();

			return NULL;
		}
		$amazonOrderReferenceIdWeight = $this->_session->getAmazonOrderReferenceIdWeightFromSession($this->_currentMethod->virtuemart_paymentmethod_id);

		if(!isset($amazonOrderReferenceIdWeight['_amazonOrderReferenceId']) or !$this->checkConditions($cart, $this->_currentMethod, $cart_prices)) {
			//vmInfo('VMPAYMENT_AMAZON_PAYMENT_NOT_AVAILABLE');

			//$this->unsetCartLayoutAndPaymentMethod($cart);
			//$this->leaveAmazonCheckout(vmText::_('VMPAYMENT_AMAZON_PAYMENT_NOT_AVAILABLE'));
			$this->leaveAmazonCheckout();
			return FALSE;
		}

		$this->_amazonOrderReferenceId = $amazonOrderReferenceIdWeight['_amazonOrderReferenceId'];
		$referenceIdIsOnlyDigitalGoods = $amazonOrderReferenceIdWeight['isOnlyDigitalGoods'];
		$cart_prices_name = '';
		$cart_prices['cost'] = 0;
		$layout = $cart->layout;

		if($this->shouldLoginAgain($referenceIdIsOnlyDigitalGoods, $this->isOnlyDigitalGoods($cart))) {
		} else {
			$check = self::$cartPriceUpdatedDone;
			if(!self::$cartPriceUpdatedDone) {
				$cartPriceUpdated = ($this->_session->getAmazonSalesPriceFromSession($this->_currentMethod->virtuemart_paymentmethod_id) == $cart_prices['salesPrice']) ? false : true;
				if($cartPriceUpdated) {
					$cart->_dataValidated = false;
					$cart->setCartIntoSession();
				}

			}
			$this->renderAddressbookWallet($cart->_dataValidated);
		}

		$cart_prices_name = $this->renderPluginName($this->_currentMethod);

		$this->setCartPrices($cart, $cart_prices, $this->_currentMethod);
		if(!self::$cartPriceUpdatedDone) {
			$this->_session->setSalesPriceInSession($cart_prices['salesPrice'], $this->_currentMethod->virtuemart_paymentmethod_id);
		}
		self::$cartPriceUpdatedDone = true;

		return TRUE;
	}

	private function shouldLoginAgain($referenceIdIsOnlyDigitalGoods, $isOnlyDigitalGoods) {
	return false;	//We need a automatic relog here, I think this is easier to solve with the new Amazon Pay API
		if(($isOnlyDigitalGoods and $referenceIdIsOnlyDigitalGoods) OR (!$isOnlyDigitalGoods and !$referenceIdIsOnlyDigitalGoods)) {
			return false;
		}
		static $enqueueMessageDone = false;
		if(!$enqueueMessageDone) {
			JFactory::getApplication()->enqueueMessage(vmText::_('VMPAYMENT_AMAZON_CLICK_SHOULD_LOGIN_AGAIN'));
			$enqueueMessageDone = true;
		}

		return true;
	}

	private function getPixelValue($value) {
		$value = str_replace("px", "", $value);
		$value = $value . 'px';

		return trim($value);
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
	public function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array()) {

		return $this->onCheckAutomaticSelected($cart, $cart_prices);
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

		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
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
	public function plgVmOnShowOrderPrintPayment($order_number, $method_id) {

		return $this->onShowOrderPrint($order_number, $method_id);
	}


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


	public function plgVmSetOnTablePluginParamsPayment($name, $id, &$table) {
		return $this->setOnTablePluginParams($name, $id, $table);
	}

	function getEmailCurrency(&$method) {

		return $method->payment_currency; // either the vendor currency, either same currency as payment
	}





	/*********************/
	/* Private functions */
	/*********************/


	/**
	 * get the partial shipping address (city, state, postal code, and country) by calling the GetOrderReferenceDetails operation
	 * to compute taxes and shipping costs or possible applicable shipping speed, and options.
	 * @param $client
	 * @param $cart
	 */
	function updateCartWithAmazonAddress() {

		$this->loadVmClass('VirtueMartCart', JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');
		$return = array();

		$cart = VirtueMartCart::getCart();
		//$this->debugLog('got my cart', 'updateCartWithAmazonAddress', 'debug');
		$physicalDestination = $this->getPhysicalDestination();
		if(!$physicalDestination) {
			$return['error'] = 'NoPhysicalDestination';
			$return['error_msg'] = vmText::_('VMPAYMENT_AMAZON_UPDATECART_ERROR');

			return $return;
		}
		//$this->debugLog($physicalDestination, 'updateCartWithAmazonAddress', 'debug');
		$update_data = $this->getUserInfoFromAmazon($physicalDestination);
		if(!$this->isValidCountry($update_data['virtuemart_country_id'])) {
			$this->updateCartWithDefaultAmazonAddress($cart, $this->isOnlyDigitalGoods($cart));
			$country = shopFunctions::getCountryByID($update_data['virtuemart_country_id']);
			$cart->_dataValidated = false;
			$cart->BT['virtuemart_country_id'] = 0;
			$cart->setCartIntoSession();
			$return['error'] = 'deliveryCountryNotAllowed';
			$return['error_msg'] = vmText::sprintf('VMPAYMENT_AMAZON_UPDATECART_DELIVERYCOUNTRYNOTALLOWED', $country);

			return $return;

		}
		if($this->isSameAddress($update_data, $cart)) {
			$return['error'] = 'sameAddress';

			return $return;

		}
		$update_data ['address_type'] = 'BT';
		$cart->saveAddressInCart($update_data, $update_data['address_type'], TRUE);


		// update BT and ST with Amazon Partial Address
		$prefix = 'shipto_';
		$update_data = $this->getUserInfoFromAmazon($physicalDestination, $prefix);
		$update_data ['address_type'] = 'ST';
		$cart->STsameAsBT = false;
		$cart->saveAddressInCart($update_data, $update_data['address_type'], TRUE, $prefix);
		//$cart->setCartIntoSession();

		$return['error'] = 'addressUpdated';

		return $return;
	}


	function getPhysicalDestination() {
		$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(empty($this->_amazonOrderReferenceId)) {
			//vmError('VMPAYMENT_AMAZON_LOGIN');
			return FALSE;
		}
		$orderReferenceDetails = $this->getOrderReferenceDetailsResponse();
		if(!$orderReferenceDetails) {
			$this->debugLog('getOrderReferenceDetailsResponse failed', 'getPhysicalDestination', 'debug');
			return false;
		}

		$destination = $orderReferenceDetails->GetOrderReferenceDetailsResult->getOrderReferenceDetails()->getDestination();
		if(empty($destination)) {
			// plgVmonSelectedCalculatePricePayment is also called in the module
			//$this->debug('Destination is empty, noot saving', 'saveAmazonPartialShipmentAddressIncart', 'debug');
			return false;
		}
		$physicalDestination = $destination->getPhysicalDestination();

		return $physicalDestination;

	}

	function updateCartWithDefaultAmazonAddress($cart, $STsameAsBT = true) {

		$this->loadVmClass('VirtueMartCart', JPATH_VM_SITE . DS . 'helpers' . DS . 'cart.php');


		$this->_amazonOrderReferenceId = $this->_session->getAmazonOrderReferenceIdFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		if(empty($this->_amazonOrderReferenceId)) {
			//vmError('VMPAYMENT_AMAZON_LOGIN');
			return FALSE;
		}
		$virtuemart_vendor_id = 1;
		$vendorModel = VmModel::getModel('vendor');
		$vendorModel->setId($virtuemart_vendor_id);
		$vendorFields = $vendorModel->getVendorAddressFields($virtuemart_vendor_id);
		$skips = array('name', 'username', 'agreed');

		// TODO if asynchronous authorization: we set some default fields to the vendor fields
		$update_dataBT = array();
		//$update_dataST = array();
		$prefix = 'shipto_';
		foreach ($vendorFields['fields'] as $field) {
			if(!$field['required']) {
				continue;
			}
			//vmdebug('updateCartWithDefaultAmazonAddress $field',$field);
			if($field['name'] == 'virtuemart_country_id') {
				if(!isset($field[$field['name']])) $field[$field['name']] = 0;
				$update_dataBT[$field['name']] = $field[$field['name']];
				$update_dataST[$prefix.$field['name']] = $field[$field['name']];
			} elseif($field['name'] == 'virtuemart_state_id') {
				if(!isset($field[$field['name']])) $field[$field['name']] = 0;
				$update_dataBT[$field['name']] = $field[$field['name']];
				$update_dataST[$prefix.$field['name']] = $field[$field['name']];
			} elseif($field['name'] == 'email') {
				$update_dataBT[$field['name']] = $field['value'];
				$update_dataST[$prefix . $field['name']] = $field['value'];
			}
			else {
				$update_dataBT[$field['name']] = '-';
				$update_dataST[$prefix.$field['name']] = '-';
			}
		}
			/*if($field['name'] == 'virtuemart_country_id') {
				$update_dataBT[$field['name']] = $field[$field['name']];
				$update_dataST[$prefix . $field['name']] = $field[$field['name']];

			} elseif($field['name'] == 'virtuemart_state_id') {
				$update_dataBT[$field['name']] = $field[$field['name']];
				$update_dataST[$prefix . $field['name']] = $field['value'];
			} elseif($field['name'] == 'email') {
				$update_dataBT[$field['name']] = $field['value'];
				$update_dataST[$prefix . $field['name']] = $field['value'];
			} else {
				$update_dataBT[$field['name']] = '-';
				$update_dataST[$prefix . $field['name']] = '-';
			}*/
		if($this->_currentMethod->region=='UK'){
			$update_dataBT ['virtuemart_country_id'] = $update_dataST ['virtuemart_country_id'] = 222;
		} else if($this->_currentMethod->region=='DE'){
			$update_dataBT ['virtuemart_country_id'] = $update_dataST ['virtuemart_country_id'] = 223;
		} else if($this->_currentMethod->region=='US'){
			$update_dataBT ['virtuemart_country_id'] = $update_dataST ['virtuemart_country_id'] = 81;
		}


		$update_dataBT ['address_type'] = 'BT';
		$cart->saveAddressInCart($update_dataBT, $update_dataBT['address_type'], TRUE);

		if(!$STsameAsBT) {
			$update_dataST ['address_type'] = 'ST';
			$cart->STsameAsBT = false;
			unset($update_dataST['shipto_company']);
			$cart->saveAddressInCart($update_dataST, $update_dataST['address_type'], TRUE, $prefix);
		} else {
			$cart->STsameAsBT = true;
		}
		$cart->setCartIntoSession();

		return true;
	}


	private function isSameAddress($update_data, $cart) {
		$chck = array('city','virtuemart_country_id', 'zip');
		foreach($chck as $f){
			if(!isset($cart->BT[$f]) or !isset($update_data[$f])){
				return false;
			} else if($cart->BT[$f] != $update_data[$f]) {
				return false;
			}
		}
		return true;
		/*if($cart->BT['city'] == $update_data['city'] and $cart->BT['virtuemart_country_id'] == $update_data['virtuemart_country_id'] AND $cart->BT['zip'] == $update_data['zip']) {
			return true;
		}

		return false;*/
	}

	/**
	 * IPN_Handler
	 *
	 * This trigger is invoked whenever a new notification needs to be processed,
	 * and will call the IPN API
	 *
	 *
	 */
	private function ipn() {
		// ERP mode turns off all automated authorization and capture functionality
		// as well as IPN reception and / or polling, and disables any admin UI functionality that may trigger
		//If the authorization is done by ERP then the IPN URL set in amazon should not be this one.
		// we keep it anyway, for testing purposes
		if($this->isERPModeEnabled() and $this->isAuthorizationDoneByErp()) {
			return;
		}
		// Fetch all HTTP request headers
		$headers = $this->getallheaders();
		$body = file_get_contents('php://input');


		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Client');
		$this->loadVmClass('VirtueMartModelOrders', JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

		try {
			$config['merchantId'] = $this->_currentMethod->sellerId;
			$config['accessKey'] = $this->_currentMethod->accessKey;
			$config['secretKey'] = $this->_currentMethod->secretKey;
			$config['applicationName'] = 'VirtueMart';
			$config['applicationVersion'] = '3.2.6';
			$config['region'] = $this->_currentMethod->region;
			$config['environment'] = $this->_currentMethod->environment;
			$config['cnName'] = 'sns.amazonaws.com'; //$this->_currentMethod->cnname;
			$client = new OffAmazonPaymentsNotifications_Client($config);

			//} catch (OffAmazonPaymentsNotifications_InvalidMessageException $e) {
		} catch (Exception $e) {
			$this->debugLog('new OffAmazonPaymentsNotifications_Client throws exception: '.$e->getMessage() . ' ' . __FUNCTION__ . ' $body', 'error');
			$this->debugLog(var_export($headers, true), 'AMAZON IPN HEADERS debug', 'debug');
			$this->debugLog(var_export($body, true), 'AMAZON IPN BODY debug', 'debug');
			header("HTTP/1.1 503 Service Unavailable");
			exit(0);
		}

		try {
			$notification = $client->parseRawMessage($headers, $body);
		} catch (Exception $e) {
			$this->debugLog('OffAmazonPaymentsNotifications_Client parseRawMessage throws exception: '.$e->getMessage() . ' ' . __FUNCTION__ . ' $body', 'error');
			$this->debugLog(var_export($headers, true), 'AMAZON IPN HEADERS debug', 'debug');
			$this->debugLog(var_export($body, true), 'AMAZON IPN BODY debug', 'debug');
			header("HTTP/1.1 503 Service Unavailable");
			exit(0);
		}
		$notificationType = $notification->getNotificationType();
		$this->debugLog($notificationType, 'ipn', 'debug');

		if(!$this->isValidNotificationtype($notificationType)) {
			$this->debugLog($notificationType, 'ipn NOT isValidNotificationtype', 'error');

			return;
		}

		$notificationClass = 'amazonHelper' . $notificationType;
		$notificationFile = JPATH_SITE . DS . 'plugins' . DS . 'vmpayment' . DS . 'amazon' . DS . 'handlers' . DS . strtolower($notificationType . '.php');
		if(!file_exists($notificationFile)) {
			$this->debugLog("Unknown notification Type: " . $notificationType, __FUNCTION__, 'error');

			return false;
		}
		if(!class_exists($notificationClass)) {
			require(JPATH_SITE . DS . 'plugins' . DS . 'vmpayment' . DS . 'amazon' . DS . 'handlers' . DS . 'helper.php');
			require($notificationFile);
		}

		$this->debugLog($notificationType, 'ipn', 'debug');


		$notificationResponse = new $notificationClass($notification, $this->_currentMethod);
		//$this->debugLog("<pre>" . var_export($notificationResponse->amazonData, true) . "</pre>", __FUNCTION__, 'debug');


		if(!($order_number = $notificationResponse->getReferenceId())) {
			/*
					// it is not really an error, orderReferenceNotification do not send a ReferenceId
					if ($amazonReferenceId=$notificationResponse->getAmazonReferenceId()) {
						$payments=$this->getDatasByAmazonReferenceId($amazonReferenceId);
						if (!$payments) {
							$this->debugLog('no ReferenceId IPN received', $notificationClass, 'error');
						}
						$orderModel = VmModel::getModel('orders');
						$order = $orderModel->getOrder($payments[0]->virtuemart_order_id);
						$this->storeAmazonInternalData($order, NULL, NULL, $notification, NULL, $notificationResponse->getStoreInternalData());
					}
		*/
			$this->debugLog('no ReferenceId IPN received', $notificationClass, 'error');

			return true;
		}

		if(!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			$this->debugLog('Received a ' . $notificationClass . ' with order number ' . $order_number . ' but no order in DB with that number', $notificationClass, 'error');

			return true;
		}
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);


		if(!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			// we ignore it because we receive also notification when refund/capture is done in the Amazon BE, and there is no valid reference
			//$this->debugLog('Received a ' . $newClass . ' with order number ' . $order_number . 'but no order in DB with that number in AMAZON payment table', $newClass, 'error');
			return true;
		}

		$amazonState = $notificationResponse->onNotificationUpdateOrderHistory($order, $payments);
		$this->debugLog('Amazon state is: '.$amazonState, __FUNCTION__, 'debug');

		$this->storeAmazonInternalData($order, NULL, NULL, $notification, NULL, $notificationResponse->getStoreInternalData());

		$nextOperation = $notificationResponse->onNotificationNextOperation($order, $payments, $amazonState);
		$this->debugLog('Next operation is: '.$nextOperation, __FUNCTION__, 'debug');

		if($nextOperation === false) {
			return;
		}
		if(!function_exists($nextOperation)) {
			//$this->debugLog('Trying to call ' . $nextOperation .  ' but the function does not exists: Programming error', $notificationClass, 'error');

		}
		$this->$nextOperation($payments, $order);
	}


	private function onNotificationGetAuthorization($payments, $order) {
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		$this->getAuthorization($client, NULL, $order, false);
	}

	private function onNotificationGetAuthorizationDetails($payments, $order) {
		$amazonAuthorizationId = $this->getAmazonAuthorizationId($payments);
		if(!$amazonAuthorizationId) {
			return false;
		}
		$authorizationDetailsResponse = $this->getAuthorizationDetails($amazonAuthorizationId, $order);

		if(!$authorizationDetailsResponse->isSetGetAuthorizationDetailsResult()) {
			return;
		}
		$getAuthorizationDetailsResult = $authorizationDetailsResponse->getGetAuthorizationDetailsResult();

		if(!$getAuthorizationDetailsResult->isSetAuthorizationDetails()) {
			return;
		}
		$getAuthorizationDetails = $getAuthorizationDetailsResult->getAuthorizationDetails();

		$this->updateAuthorizeBillingAddressInOrder($getAuthorizationDetails, $order);

		if($getAuthorizationDetails->isSetAuthorizationStatus()) {

			$authorizationStatus = $getAuthorizationDetails->getAuthorizationStatus();
			if($authorizationStatus->isSetState()) {
				$amazonState = $authorizationStatus->getState();
			}

			if($authorizationStatus->isSetReasonCode()) {
				$reasonCode = $authorizationStatus->getReasonCode();
			}
			if($amazonState == 'Closed') {
				$this->closeAuthorization($getAuthorizationDetails->getAmazonAuthorizationId(), $order);
			}

		}
		$this->loadHelperClass('amazonHelperGetAuthorizationDetailsResponse');
		$amazonHelperGetAuthorizationDetailsResponse = new amazonHelperGetAuthorizationDetailsResponse($authorizationDetailsResponse, $this->_currentMethod);
		$amazonHelperGetAuthorizationDetailsResponse->onResponseUpdateOrderHistory($order);


		return;
	}

	/**
	 * @param $amazonOrderReferenceId
	 * @param $order
	 */

	function closeOrderReference($amazonOrderReferenceId, $order,
		$closeReason = 'VMPAYMENT_AMAZON_CLOSE_REASON_ORDER_COMPLETE') {
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CloseOrderReferenceRequest');
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}
		try {
			$closeOrderReferenceRequest = new OffAmazonPaymentsService_Model_CloseOrderReferenceRequest();
			$closeOrderReferenceRequest->setSellerId($this->_currentMethod->sellerId);
			$closeOrderReferenceRequest->setAmazonOrderReferenceId($amazonOrderReferenceId);
			$closeOrderReferenceRequest->setClosureReason(vmText::_($closeReason));
			$closeOrderReferenceResponse = $client->closeOrderReference($closeOrderReferenceRequest);

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return;
		}

		$this->loadHelperClass('amazonHelpercloseOrderReferenceResponse');
		$amazonHelperCloseOrderReferenceResponse = new amazonHelpercloseOrderReferenceResponse($closeOrderReferenceResponse, $this->_currentMethod);
		$storeInternalData = $amazonHelperCloseOrderReferenceResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, $closeOrderReferenceRequest, $closeOrderReferenceResponse, NULL, NULL, $storeInternalData);


		return;
	}

	/**
	 * To close an authorization after the total amount of the authorization has been captured
	 * @return mixed
	 */
	function closeAuthorization($amazonAuthorizationId, $order,
		$closeReason = 'VMPAYMENT_AMAZON_CLOSE_REASON_ORDER_COMPLETE') {
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CloseAuthorizationRequest');
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return;
		}

		try {
			$closeAuthorizationRequest = new OffAmazonPaymentsService_Model_CloseAuthorizationRequest();
			$closeAuthorizationRequest->setSellerId($this->_currentMethod->sellerId);
			$closeAuthorizationRequest->setAmazonAuthorizationId($amazonAuthorizationId);
			$closeAuthorizationRequest->setClosureReason(vmText::_($closeReason));
			$closeAuthorizationResponse = $client->closeAuthorization($closeAuthorizationRequest);

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return;
		}


		$this->loadHelperClass('amazonHelperCloseAuthorizationResponse');
		$amazonHelperCloseAutorizationResponse = new amazonHelperCloseAuthorizationResponse($closeAuthorizationResponse, $this->_currentMethod);
		$storeInternalData = $amazonHelperCloseAutorizationResponse->getStoreInternalData();
		$this->storeAmazonInternalData($order, $closeAuthorizationRequest, $closeAuthorizationResponse, NULL, NULL, $storeInternalData);


		return;
	}

	private function isValidNotificationType($notificationType) {
		$validNotificationType = array(
			"OrderReferenceNotification",
			//"BillingAgreementNotification",
			"AuthorizationNotification",
			"CaptureNotification",
			"RefundNotification",
		);
		if(in_array($notificationType, $validNotificationType)) {
			$this->debugLog("received notificationType: " . $notificationType, __FUNCTION__, 'debug');

			return true;
		}

		return false;

	}




	private function getAmazonOrderReferenceIdFromPayments($payments) {
		foreach ($payments as $payment) {
			if($payment->amazon_class_request_type == 'OffAmazonPaymentsService_Model_ConfirmOrderReferenceRequest') {
				$amazon_request = unserialize($payment->amazon_request);

				return $amazon_request->AmazonOrderReferenceId;

			}
		}
	}



	/**
	 * Use the order reference object to query the order information, including
	 * the current physical delivery address as selected by the buyer
	 *
	 * @return OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse service response
	 */

	private function getOrderReferenceDetailsResponse($addressConsentToken = null) {
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_OrderReferenceDetails');
		//$_amazonOrderReferenceId = $this->getAmazonOrderReferenceId();
		if(empty($this->_amazonOrderReferenceId)) {
			vmError('VMPAYMENT_AMAZON_LOGIN');

			return FALSE;
		}
		$client = $this->getOffAmazonPaymentsService_Client();
		if($client == NULL) {
			return NULL;
		}

		try {
			$getOrderReferenceDetailsRequest = new OffAmazonPaymentsService_Model_GetOrderReferenceDetailsRequest();
			$getOrderReferenceDetailsRequest->setSellerId($this->_currentMethod->sellerId);
			$getOrderReferenceDetailsRequest->setAmazonOrderReferenceId($this->_amazonOrderReferenceId);
			if(is_null($addressConsentToken) == FALSE) {
				$decodedToken = urldecode($addressConsentToken);
				$getOrderReferenceDetailsRequest->setAddressConsentToken($decodedToken);
			}

			$orderReferenceDetailsResponse = $client->getOrderReferenceDetails($getOrderReferenceDetailsRequest);

		} catch (Exception $e) {
			$this->amazonError(__FUNCTION__ . ' ' . $e->getMessage(), $e->getCode());

			return FALSE;
		}
		//$this->debugLog($orderReferenceDetailsResponse, 'getOrderReferenceDetailsResponse', 'debug');

		return $orderReferenceDetailsResponse;
	}


	private function isValidUpdateOrderStatus($orderStatus) {
		$validOrderStatus = array(
			$this->_currentMethod->status_capture,
			$this->_currentMethod->status_refunded,
			$this->_currentMethod->status_cancel,
		);
		if(!in_array($orderStatus, $validOrderStatus)) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	private function isCaptureImmediate($cart) {
		if($cart) {
			return ($this->_currentMethod->capture_mode == "capture_immediate" OR $this->isSomeDigitalGoods($cart));
		} else {
			return false;
		}
	}


	private function isERPModeEnabled() {
		if($this->_currentMethod->erp_mode == "erp_mode_enabled") {
			return true;
		} else {
			return false;
		}
	}


	private function isAuthorizationDoneByErp() {
		if($this->_currentMethod->authorization_mode_erp_enabled == "authorization_done_by_erp") {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * in case of only Digital Goods, the Address Wallet is not displayed
	 * @param $cart
	 * @return bool
	 */
	private function isOnlyDigitalGoods($cart) {
		/*if(!$this->_currentMethod->digital_goods) {
			return false;
		}*/
		if($cart) {
			$weight = $this->getOrderWeight($cart, 'GR');
		} else {
			$weight = $this->_session->getisOnlyDigitalGoodsFromSession($this->_currentMethod->virtuemart_paymentmethod_id);
		}

		if($weight == 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * In case of some Digital goods, the capture is immediate
	 * @param $cart
	 * @return bool
	 */
	private function isSomeDigitalGoods($cart) {
		/*if(!$this->_currentMethod->digital_goods) {
			return false;
		}*/

		foreach ($cart->products as $product) {
			if($product->product_weight == 0) {
				return true;
			}
		}

		return false;
	}



	//
	// DEBUG AND LOG FUNCTIONS
	//
	/**
	 * @param string $message
	 * @param string $title
	 * @param string $type
	 * @param bool $echo
	 * @param bool $doVmDebug
	 */
	function debugLog($message, $title = '', $type = 'message', $echo = false, $doVmDebug = false) {

		if($this->_currentMethod->debug) {
			$this->debug($message, $title, true);
		}

		if($echo) {
			echo $message . '<br/>';
		}


		parent::debugLog($message, $title, $type, $doVmDebug);
	}

	private function debug($subject, $title = '', $echo = true) {

		$debug = '<div style="display:block; margin-bottom:5px; border:1px solid red; padding:5px; text-align:left; font-size:10px;white-space:nowrap; overflow:scroll;">';
		$debug .= ($title) ? '<br /><strong>' . $title . ':</strong><br />' : '';
		//$debug .= '<pre>';
		if(is_array($subject)) {
			$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", nl2br(str_replace(" ", " &nbsp; ", print_r($subject, true)))));
		} else {
			//$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", (str_replace(" ", " &nbsp; ", print_r($subject, true)))));
			$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", print_r($subject, true)));

		}

		//$debug .= '</pre>';
		$debug .= '</div>';
		if($echo) {
			echo $debug;
		} else {
			return $debug;
		}
	}


	//
	// Load classes / files  if not exists
	//

	function loadAmazonServicesClasses() {
		$this->loadAmazonClass('OffAmazonPaymentsService_Client');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_Price');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_OrderTotal');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_SellerOrderAttributes');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_ResponseHeaderMetadata');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_ResponseMetadata');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_AuthorizeResult');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_AuthorizationDetails');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_AuthorizeResponse');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_Address');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_IdList');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_Status');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CaptureDetails');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CaptureResult');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetCaptureDetailsResponse');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetCaptureDetailsResult');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_RefundResult');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_RefundDetails');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_RefundResponse');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CloseOrderReferenceRequest');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetAuthorizationDetailsResult');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_GetRefundDetailsResult');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CloseAuthorizationRequest');
		$this->loadAmazonClass('OffAmazonPaymentsService_Model_CloseAuthorizationResult');
	}


	function loadAmazonNotificationClasses() {
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Client');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_AuthorizationNotification');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_AuthorizationDetails');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_Price');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_IdList');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_Status');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_OrderItemCategories');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_RefundNotification');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_RefundDetails');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_CaptureNotification');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_CaptureDetails');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_IPNNotificationMetadata');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_NotificationImpl');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_SnsNotificationMetadata');
		$this->loadAmazonClass('OffAmazonPaymentsNotifications_Model_OrderTotal');
	}

	function loadAmazonClass($className) {
		if(!class_exists($className)) {
			$filePath = JPATH_SITE . DS . 'plugins' . DS . 'vmpayment' . DS . 'amazon' . DS . 'library' . DS . str_replace('_', DS, $className) . '.php';
			if(file_exists($filePath)) {
				require $filePath;

				return;
			} else {
				vmError('Programming error: trying to load:' . $filePath);
			}
		}
	}

	function loadHelperClass($className) {
		if(!class_exists('amazonHelper')) {
			require(JPATH_SITE . DS . 'plugins' . DS . 'vmpayment' . DS . 'amazon' . DS . 'handlers' . DS . 'helper.php');
		}
		if(!class_exists($className)) {
			$fileName = strtolower(str_replace('amazonHelper', '', $className)) . '.php';
			$fileNameAbsPath = JPATH_SITE . DS . 'plugins' . DS . 'vmpayment' . DS . 'amazon' . DS . 'handlers' . DS . $fileName;
			if(file_exists($fileNameAbsPath)) {
				require($fileNameAbsPath);
			} else {
				vmError('Programming error: ' . __FUNCTION__ . ' trying to load:' . $fileNameAbsPath);
			}
		}

	}

	function loadVmClass($className, $fileName) {
		if(!class_exists($className)) {
			if(file_exists($fileName)) {
				require($fileName);
			} else {
				vmError('Programming error:' . __FUNCTION__ . ' trying to load:' . $fileName);
			}
		}
	}

	function getallheaders() {
		if(!function_exists('getallheaders')) {
			//$this->debugLog("getallheaders PHP function does not exists" . "<pre>" . var_export($_SERVER, true) . "</pre>", __FUNCTION__, 'debug');
			$headers = '';
			foreach ($_SERVER as $name => $value) {
				if(substr($name, 0, 5) == 'HTTP_') {
					$headers[str_replace(' ', '-', strtolower(str_replace('_', ' ', substr($name, 5))))] = $value;
				}
			}

			//$this->debugLog("getallheaders Local function returns" . "<pre>" . var_export($headers, true) . "</pre>", __FUNCTION__, 'debug');

			return $headers;
		} else {
			return getallheaders();
		}
	}
}

// No closing tag
