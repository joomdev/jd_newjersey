<?php
/**
 *
 * Paybox payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id$
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
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


class  PayboxHelperPaybox {
	const PBX_MAX_URL_LEN = 150;

	const RESPONSE_SUCCESS = '00000';

	const TYPEPAIEMENT_CARTE = 'CARTE';

	const TYPECARTE_CB = 'CB';

	const TYPE_DIRECT_AUTHORIZATION_ONLY = '00001';
	const TYPE_DIRECT_CAPTURE = '00002';
	const TYPE_DIRECT_AUTHORIZATION_CAPTURE = '00003';

	function __construct ($method, $plugin, $plugin_name) {
		$this->_method = $method;
		$this->_method->site_id = trim($this->_method->site_id) ;
		$this->_method->rang = trim($this->_method->rang) ;
		$this->_method->identifiant = trim($this->_method->identifiant) ;
		$this->_method->key = trim($this->_method->key) ;

		$this->plugin = $plugin;
		$this->plugin_name=$plugin_name;
	}

	/**
	 * @param $cart
	 * @param $order
	 * @return bool
	 */

	function confirmedOrder ($cart, $order) {

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}


		$this->plugin->getPaymentCurrency($this->_method);
		$q = 'SELECT `currency_numeric_code` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $this->_method->payment_currency . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$currency_numeric_code = $db->loadResult();
		$totalInPaymentCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $this->_method->payment_currency);
		$orderTotalVendorCurrency = $order['details']['BT']->order_total;
		$pbxOrderTotalInPaymentCurrency = $this->getPbxAmount($totalInPaymentCurrency['value']);
		$email_currency = $this->plugin->getEmailCurrency($this->_method);

		// If the file is not there anylonger, just create it
		//$this->plugin->createRootFile($this->_method->virtuemart_paymentmethod_id);

		if (!$this->getPayboxServerUrl()) {
			$this->redirectToCart();
			return false;
		}
		if (!($payboxReturnUrls = $this->getPayboxReturnUrls())) {
			$this->redirectToCart();
			return false;
		}


		$post_variables = Array(
			"PBX_SITE"        => $this->_method->site_id,
			"PBX_RANG"        => $this->_method->rang,
			"PBX_IDENTIFIANT" => $this->_method->identifiant,
			"PBX_TOTAL"       => $this->getPbxTotal($pbxOrderTotalInPaymentCurrency),
			"PBX_DEVISE"      => $currency_numeric_code,
			"PBX_CMD"         => $order['details']['BT']->order_number,
			"PBX_PORTEUR"     => $order['details']['BT']->email,
			"PBX_RETOUR"      => $this->getReturn(),
			"PBX_HASH"        => $this->getHashAlgo(),
			"PBX_TIME"        => $this->getTime(),
			"PBX_LANGUE"      => $this->getLangue(),
			//"PBX_TYPEPAIEMENT" => $this->getTypePaiement(),
			//"PBX_TYPECARTE"    => $this->getTypeCarte(),
			"PBX_EFFECTUE"    => $payboxReturnUrls['url_effectue'],
			//	"PBX_ATTENTE"     => $payboxReturnUrls['url_attente'],
			"PBX_ANNULE"      => $payboxReturnUrls['url_annule'],
			"PBX_REFUSE"      => $payboxReturnUrls['url_refuse'],
			"PBX_ERREUR"      => $payboxReturnUrls['url_erreur'],
			"PBX_REPONDRE_A"  => $payboxReturnUrls['url_notification'],
			"PBX_RUF1"        => 'POST',
		);
		if ($this->_method->debit_type == 'authorization_only') {
			$post_variables["PBX_DIFF"] = str_pad($this->_method->diff, 2, '0', STR_PAD_LEFT);
		}

		// min_amount_3dsecure is in vendor currency
		if (!($this->isActivate3ds($orderTotalVendorCurrency) )) {
			$post_variables["PBX_3DS"] = 'N';
		}
		jimport('joomla.environment.browser');
		$browser = JBrowser::getInstance();
		if ($browser->isMobile()) {
			$post_variables["PBX_SOURCE"] = 'XHTML';
		}
		$subscribe = array();
		$recurring = array();
		$post_variables["PBX_CMD"] = $order['details']['BT']->order_number;
		if ($this->_method->integration == "recurring" AND ($orderTotalVendorCurrency > $this->_method->recurring_min_amount)) {
			$recurring = $this->getRecurringPayments($pbxOrderTotalInPaymentCurrency);
			// PBX_TOTAL will be replaced in the array_merge.
			$post_variables = array_merge($post_variables, $recurring);
		} else {
			if ($this->_method->integration == "subscribe") {
				$subscribe_data = $this->getSubscribePayments($cart, $this->getPbxAmount($orderTotalVendorCurrency));
				if ($subscribe_data) {
					// PBX_TOTAL is the order total in this case
					$post_variables["PBX_TOTAL"] = $subscribe_data["PBX_TOTAL"];
					$post_variables["PBX_CMD"] .= $subscribe_data['PBX_CMD'];
				}
			}
		}

		$post_variables["PBX_HMAC"] = $this->getHmac($post_variables, $this->_method->key);

		// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$dbValues['payment_name'] = $this->plugin->renderPluginName($this->_method);
		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['paybox_custom'] = $this->getContext();
		$dbValues['cost_per_transaction'] = $this->_method->cost_per_transaction;
		$dbValues['cost_percent_total'] = $this->_method->cost_percent_total;
		$dbValues['payment_currency'] = $this->_method->payment_currency;
		$dbValues['email_currency'] = $email_currency;
		$dbValues['payment_order_total'] = $post_variables["PBX_TOTAL"];
		if (!empty($recurring)) {
			$dbValues['recurring'] = json_encode($recurring);
			$dbValues['recurring_number'] = $this->_method->recurring_number;
			$dbValues['recurring_periodicity'] = $this->_method->recurring_periodicity;
		} else {
			$dbValues['recurring'] = NULL;
		}
		if (!empty($subscribe)) {
			$dbValues['subscribe'] = json_encode($subscribe);
			//$dbValues['recurring_number'] = $this->_method->recurring_number;
			//$dbValues['recurring_periodicity'] = $this->_method->recurring_periodicity;
		} else {
			$dbValues['subscribe'] = NULL;
		}

		$dbValues['tax_id'] = $this->_method->tax_id;
		$this->plugin->storePSPluginInternalData($dbValues);

		$html = $this->getConfirmedHtml($post_variables, $this);

		// 	2 = don't delete the cart, don't send email and don't redirect
		$cart->_confirmDone = FALSE;
		$cart->_dataValidated = FALSE;
		$cart->setCartIntoSession();
		vRequest::setVar('display_title', false);
		vRequest::setVar('html', $html);

		return;
	}


	function isActivate3ds($orderTotalVendorCurrency) {
		 return $this->_method->activate_3dsecure=='active' OR  ($this->_method->activate_3dsecure=='selective' AND ($orderTotalVendorCurrency > $this->_method->min_amount_3dsecure));

		}

	/**
	 * @param $paybox_data
	 * @return bool
	 */

	function paymentResponseReceived ($paybox_data) {


		if ($payboxResponseValid = $this->isPayboxResponseValid( $paybox_data, false, true)) {
			// we don't do anything actually, it is probably an invalid signature.
			// we do not update order status and let IPN do his job
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
			$this->redirectToCart();
			return FALSE;
		}

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$paybox_data = $this->unsetNonPayboxData($paybox_data);
		$success = ($paybox_data['E'] == self::RESPONSE_SUCCESS);
		$extra_comment = "";
		// The order status is nly updated if the validation is ok

		if ($payboxResponseValid) {
			if (count($payments) == 1) {
				// NOTIFY not received
				$order_history = $this->updateOrderStatus( $paybox_data, $order, $payments);
				if (isset($order_history['extra_comment'])) {
					$extra_comment = $order_history['extra_comment'];
				}
			}
		}


		$html = $this->plugin->getResponseHTML($order, $paybox_data, $success, $extra_comment);
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();


		return $html;

	}


	/**
	 * @param $virtuemart_order_id
	 * @return string
	 */

	function showOrderBEPayment ($virtuemart_order_id) {


		$db = JFactory::getDBO();
		$q = 'SELECT * FROM `' . $this->plugin->getTablename() . '` WHERE ';
		$q .= ' `virtuemart_order_id` = ' . $virtuemart_order_id;

		$db->setQuery($q);
		$payments = $db->loadObjectList();

		$html = '<table class="adminlist table">' . "\n";
		$html .= $this->plugin->getHtmlHeaderBE();
		$first = TRUE;
		$lang = JFactory::getLanguage();
		foreach ($payments as $payment) {
			$html .= '<tr class="row1"><td>' . vmText::_('VMPAYMENT_' . $this->plugin_name . '_DATE') . '</td><td align="left">' . $payment->created_on . '</td></tr>';
			// Now only the first entry has this data when creating the order
			if ($first) {
				$html .= $this->plugin->getHtmlRowBE($this->plugin_name . '_PAYMENT_NAME', $payment->payment_name);
				// keep that test to have it backwards compatible. Old version was deleting that column  when receiving an IPN notification
				if ($payment->payment_order_total and  $payment->payment_order_total != 0.00) {
					$html .= $this->plugin->getHtmlRowBE($this->plugin_name . '_PAYMENT_ORDER_TOTAL', ($payment->payment_order_total * 0.01) . " " . shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));
				}
				if ($payment->email_currency and  $payment->email_currency != 0) {
					//$html .= $this->getHtmlRowBE($this->_name.'_PAYMENT_EMAIL_CURRENCY', shopFunctions::getCurrencyByID($payment->email_currency, 'currency_code_3'));
				}
				if ($payment->recurring) {

					$recurring_html = '<table class="adminlist table">' . "\n";
					$recurring = json_decode($payment->recurring);
					$recurring_html .= $this->plugin->getHtmlRowBE($this->plugin_name . '_CONF_RECURRING_PERIODICTY', $payment->recurring_periodicity);
					$recurring_html .= $this->plugin->getHtmlRowBE($this->plugin_name . '_CONF_RECURRING_NUMBER', $payment->recurring_number);
					//$recurring_html .= $this->getHtmlRowBE(VmText::_('VMPAYMENT_'.$this->_name.'_CONF_RECURRING_PERIODICTY').' '. $payment->recurring_periodicity, VmText::_('VMPAYMENT_'.$this->_name.'_CONF_RECURRING_NUMBER').' '. $payment->recurring_number);
					for ($i = 1; $i < $payment->recurring_number; $i++) {
						$index_mont = "PBX_2MONT" . $i;
						$index_date = "PBX_DATE" . $i;
						$text_mont = vmText::_('VMPAYMENT_' . $this->plugin_name . '_PAYMENT_RECURRING_2MONT') . " " . $i;
						$text_date = vmText::_('VMPAYMENT_' . $this->plugin_name . '_PAYMENT_RECURRING_DATE') . " " . $i;
						//$recurring_html .= $this->getHtmlRowBE($text_date, $recurring->$index_date);
						//$recurring_html .= $this->getHtmlRowBE($text_mont, ($recurring->$index_mont * 0.01) . " " . shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));
						$recurring_html .= $this->plugin->getHtmlRowBE($recurring->$index_date, ($recurring->$index_mont * 0.01) . " " . shopFunctions::getCurrencyByID($payment->payment_currency, 'currency_code_3'));
					}
					$recurring_html .= '</table>' . "\n";
					$html .= $this->plugin->getHtmlRowBE($this->plugin_name . '_RECURRING', $recurring_html);
				}
				$first = FALSE;
			} else {
				if (!empty($payment->paybox_fullresponse)) {
					$paybox_data = json_decode($payment->paybox_fullresponse);
					$showOrderBEFields = $this->getOrderBEFields();
					$prefix = $this->plugin_name . '_RESPONSE_';
					foreach ($showOrderBEFields as $showOrderBEField) {
						if (isset($paybox_data->$showOrderBEField) and !empty($paybox_data->$showOrderBEField)) {
							$key = $prefix . $showOrderBEField;
							if (method_exists($this, 'getValueBE_' . $showOrderBEField)) {
								$function = 'getValueBE_' . $showOrderBEField;
								$paybox_data->$showOrderBEField = $this->$function($paybox_data->$showOrderBEField);
							}
							$html .= $this->plugin->getHtmlRowBE($key, $paybox_data->$showOrderBEField);
						}
					}
					$html .= '<tr><td></td><td>
<a href="#" class="PayboxLogOpener" rel="' . $payment->id . '" >
	<div style="background-color: white; z-index: 100; right:0; display: none; border:solid 2px; padding:10px;" class="vm-absolute" id="PayboxLog_' . $payment->id . '">';
					foreach ($paybox_data as $key => $value) {
						$langKey = 'VMPAYMENT_' . $prefix . $key;
						if ($lang->hasKey($langKey)) {
							$label = vmText::_($langKey);
						} else {
							$label = $key;
						}
						$html .= ' <b>' . $label . '</b>:&nbsp;' . wordwrap($value, 50, "\n", true) . '<br />';
					}

					$html .= ' </div>
	<span class="icon-nofloat vmicon vmicon-16-xml"></span>&nbsp;';
					$html .= vmText::_('VMPAYMENT_' . $this->plugin_name . '_VIEW_TRANSACTION_LOG');
					$html .= '  </a>';
					$html .= ' </td></tr>';
				}
			}
		}

		$html .= '</table>' . "\n";
		$doc = JFactory::getDocument();
		$js = "
jQuery().ready(function($) {
	$('.PayboxLogOpener').click(function() {
		var logId = $(this).attr('rel');
		$('#PayboxLog_'+logId).toggle();
		return false;
	});
});";
		$doc->addScriptDeclaration($js);

		return $html;
	}
	/**
	 * @param $post_variables
	 * @return string
	 */
	function getConfirmedHtml ($post_variables) {
		$pbxServer = $this->getPayboxServerUrl();

		// add spin image
		$html='';
		if ($this->_method->debug) {
			$html .= '<form action="' . $pbxServer . '" method="post" name="vm_paybox_form" target="paybox">';
		} else {
			if (vmconfig::get('css')) {
				$msg = vmText::_('VMPAYMENT_PAYBOX_REDIRECT_MESSAGE', true);
			} else {
				$msg='';
			}

			vmJsApi::addJScript('vm.paymentFormAutoSubmit', '
  			jQuery(document).ready(function($){
   				jQuery("body").addClass("vmLoading");
  				var msg="'.$msg.'";
   				jQuery("body").append("<div class=\"vmLoadingDiv\"><div class=\"vmLoadingDivMsg\">"+msg+"</div></div>");
    			jQuery("#vmPaymentForm").submit();
			})
		');

			$html .= '<form action="' . $pbxServer . '" method="post" name="vm_paybox_form" id="vmPaymentForm">';
		}

		foreach ($post_variables as $name => $value) {
			$html .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
		}

		if ($this->_method->debug) {
			$this->plugin->debugLog($this->_method->virtuemart_paymentmethod_id, 'sendPostRequest: payment method', 'debug');
			$this->plugin->debugLog($pbxServer, 'sendPostRequest: Server', 'debug');
			$html .= '<div style="background-color:red;color:white;padding:10px;">
						<input type="submit"  value="The method is in debug mode. Click here to be redirected to Paybox" />
						</div>';
			$this->plugin->debugLog($post_variables, 'sendPostRequest:', 'debug');

		}else {
			$html .= '<input type="submit"  value="' . vmText::_('VMPAYMENT_PAYBOX_REDIRECT_MESSAGE') . '" />';

		}
		$html .= '</form>';

		return $html;
	}

	private function getContext () {

		$session = JFactory::getSession();
		return $session->getId();
	}


	/**
	 * @param $paybox_data
	 * @param bool $checkIps
	 * @param bool $useQuery
	 * @return bool
	 */
	function isPayboxResponseValid (  $paybox_data, $checkIps = false, $useQuery = false) {

		$unsetNonPayboxData = true;
		if ($this->checkSignature($paybox_data, $unsetNonPayboxData, $useQuery) != 1) {
			$msg = 'Got a Paybox request with invalid signature';
			$this->plugin->debugLog($msg, 'checkSignature', 'error', false);
			return FALSE;
		} else {
			$this->plugin->debugLog('Got a Paybox request VALID signature', 'checkSignature', 'debug', false);
		}


		return true;
	}

	/**
	 * @param $paybox_data
	 * @param bool $unsetNonPayboxData
	 * @param bool $useQuery
	 * @return bool
	 */
	private function checkSignature (  $paybox_data, $unsetNonPayboxData = true, $useQuery = true) {
		if (!$useQuery) {
			//
			// some SEF components changes the variable order !!!!!!!
			$paybox_data = $this->getVariablesInPbxOrder( $paybox_data);
			if ($unsetNonPayboxData) {
				$paybox_data = $this->unsetNonPayboxData($paybox_data);
			}
			$query_string = $this->stringifyArray($paybox_data);
		} else {
			//$this->plugin->debugLog('TAKE QUERY' ,'checkSignature', 'debug');
			parse_str($_SERVER['QUERY_STRING'], $paybox_data);
			$paybox_data = $this->getVariablesInPbxOrder( $paybox_data);
			$query_string = $this->stringifyArray($paybox_data);
			$query_string=$_SERVER['QUERY_STRING'];
		}
		//$this->plugin->debugLog('checkSignature query:' . $query_string, 'debug');
		$keyFile = $this->getKeyFileName();
		//$this->plugin->debugLog('checkSignature :' . $keyFile, 'debug');

		$pbxIsValidSignature = $this->pbxIsValidSignature($keyFile, $query_string);
		if (!$useQuery and !$pbxIsValidSignature) {
			// only send an error message if the error does not come from PBX_EFFECTUE
			//$msg .= '            ' . 'sig ' . $sig . '<br />';
			// we cannot send an error at this stage because may be the signature is not valid from the
			$this->plugin->debugLog(vmText::_('VMPAYMENT_'.$this->plugin_name.'_ERROR_SIGNATURE_INVALID'),'pbxIsValidSignature', 'error');
		}
		$this->plugin->debugLog('pbxIsValidSignature :' . $pbxIsValidSignature,'checkSignature', 'debug');
		return $pbxIsValidSignature;

	}

	/**
	 * @return string
	 */
	 function getKeyFileName () {
		$path=VMPATH_ROOT . DS. 'plugins'.DS.'vmpayment'.DS.$this->plugin_name.DS.$this->plugin_name.DS.'key' .DS;
		return $path .'pubkey.pem';
	}

	/**
	 * Display amount as decimal value
	 * @param $value
	 * @return mixed
	 */
	function getValueBE_M ($value) {
		return $value * 0.01;
	}

	/**
	 * display date
	 * @param $value
	 * @return string
	 */
	function getValueBE_D ($value) {
		return substr($value, 0, -2) . "/" . substr($value, -2);
	}

	function getOrderBEFields () {
		$fields = array(
			'M',
			//'R',
			//'T',
			'E',
			//	'A',
			'B',
			'P',
			'C',
			'S',
			//'Y',
			'N',
			'J',
			'D',
			//'H',
			'G',
			'O',
			'F',
			//	'W',
			//		'Z',
//			'K', // MUST BE THE LAST ONE
		);
		return $fields;

	}


	/**
	 * @param $this
	 * @param $paybox_data
	 * @param $order
	 * @return bool
	 */

	function updateOrderStatus ( $paybox_data, $order, $payments) {

		$success = ($paybox_data['E'] == self::RESPONSE_SUCCESS);
		if ($success) {
			$order_history = $this->getOrderHistory($paybox_data, $order, $payments);

		} else {
			$order_history['comments'] = vmText::sprintf('VMPAYMENT_' . $this->plugin_name . '_PAYMENT_STATUS_CANCELLED', $order['details']['BT']->order_number);
			$order_history['order_status'] = $this->_method->status_canceled;
			$order_history['customer_notified'] = true;
		}
		//$this->plugin->debugLog($success, 'updateOrderStatus', 'error', false);


		$db_values['virtuemart_order_id'] = $order['details']['BT']->virtuemart_order_id;
		$db_values['order_number'] = $order['details']['BT']->order_number;
		$db_values['virtuemart_paymentmethod_id'] = $this->_method->virtuemart_paymentmethod_id;
		// get all know columns of the table
		$db = JFactory::getDBO();
		$tablename= $this->plugin->getTablename();
		$query = 'SHOW COLUMNS FROM `' . $tablename . '` ';
		$db->setQuery($query);
		$columns = $db->loadColumn(0);
		foreach ($paybox_data as $key => $value) {
			$table_key = $this->plugin_name . '_response_' . $key;
			if (in_array($table_key, $columns)) {
				$db_values[$table_key] = $value;
			}
		}
		$db_values[$this->plugin_name . '_fullresponse'] = json_encode($paybox_data);

		$this->plugin->debugLog('updateOrderStatus storePSPluginInternalData:' . var_export($db_values, true), 'debug');

		$this->plugin->storePSPluginInternalData($db_values);

		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, TRUE);
		return $order_history;
	}

	/**
	 * URLS sent to Paybox must be less than 250 characters
	 * @param $this
	 * @return bool
	 */

	function getPayboxReturnUrls () {
		$urlLength = true;

			$url_cancelled = JURI::root() . 'index.php?option=com_virtuemart&view=cart&lang=' . vRequest::getCmd('lang', '') . '&Itemid=' . vRequest::getInt('Itemid');
			$payboxURLs['url_annule'] = $url_cancelled;
			$payboxURLs['url_refuse'] = $url_cancelled;
			$payboxURLs['url_erreur'] = $url_cancelled;
			$payboxURLs['url_notification'] = JURI::root() . 'index.php?option=com_virtuemart&format=raw&view=pluginresponse&task=pluginnotification&tmpl=component&pm=' . $this->_method->virtuemart_paymentmethod_id;
			$payboxURLs['url_effectue'] = $this->getUrlOk();


		foreach ($payboxURLs as $payboxURL) {
			//$this->plugin->debugLog($payboxURL, 'getPayboxReturnUrls','debug');
			if (!$this->checkURLsLength($payboxURL)) {
				$urlLength = false;
			}
		}
		if ($urlLength) {
			return $payboxURLs;
		} else {
			$this->plugin->debugLog('FALSE', 'getPayboxReturnUrls', 'debug');
			return false;
		}
	}


	function checkURLsLength ($url) {
		$public_msg = "";
		if (strlen($url) > self::PBX_MAX_URL_LEN) {
			$msg = 'checking URL length<br />Your URL:' . $url . ' has ' . strlen($url) . '  characters.';
			$msg .= 'The maximum allowed by the payment is ' . self::PBX_MAX_URL_LEN;
			$msg .= 'Please contact your payment provider';
			$this->pbxError($msg);
			$this->plugin->debugLog($msg, 'checkURLsLength FALSE', 'debug');
			return false;
		}
		return true;
	}


	function getUrlOk () {
		$urlOkParms = $this->getUrlOkParms();
		$query = $this->stringifyArray($urlOkParms);
		$urlOk = JURI::root() . 'index.php?' . $query;
		return $urlOk;
	}

	/**
	 *        $url_ok = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=pluginresponsereceived&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . vRequest::getInt('Itemid');
	 * @return array
	 */

	function getUrlOkParms () {
		$urlOkParms = array(
			"option" => "com_virtuemart",
			"view"   => "pluginresponse",
			"task"   => "pluginresponsereceived",
			"pm"     => $this->_method->virtuemart_paymentmethod_id,
			"lang"   => vRequest::uWord('lang', ''),
			"Itemid" => vRequest::getInt('Itemid'),
		);
		return $urlOkParms;
	}

	/**
	 *
	 */

	private function redirectToCart () {
		$app = JFactory::getApplication();
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&lg=&Itemid=' . vRequest::getInt('Itemid'), false), vmText::_('VMPAYMENT_PAYBOX_ERROR_TRY_AGAIN'));
	}

	public function getReturnFields () {
		$fields = array(
			'M',
			'R',
			'T',
			'A',
			'B',
			'P',
			'C',
			'S',
			'Y',
			'E',
			'D',
			'I',
			'N',
			'J',
			'H',
			'G',
			'O',
			'F',
			'W',
			'Z',
			'K', // MUST BE THE LAST ONE
		);
		return $fields;
	}
	/**
	 * some SEF components changes the variable order !!!!!!! ^
	 * we need to have them in the same order as they are recieved to check the signature
	 * @param $paybox_data
	 * @return mixed
	 */
	private function getVariablesInPbxOrder ($paybox_data) {
		$this->plugin->debugLog('getVariablesInPbxOrder :' . var_export($paybox_data, true), 'debug');

		$paybox_data_ordered = array();
		$urlOkParms = $this->getUrlOkParms();
		foreach ($urlOkParms as $key => $urlOkParm) {
			$paybox_data_ordered[$key] = $paybox_data[$key];
		}
		$returnFields = $this->getReturnFields();
		foreach ($returnFields as $returnField) {
			if (isset($paybox_data[$returnField])) {
				$paybox_data_ordered[$returnField] = $paybox_data[$returnField];
			}
		}
		$this->plugin->debugLog('getVariablesInPbxOrder PBX order:' . var_export($paybox_data_ordered, true), 'debug');

		return $paybox_data_ordered;


	}

	public function setOrder ($order) {
		$this->order = $order;
	}

	public function unsetNonPayboxData ($paybox_data) {
		$returnFields = $this->getReturnFields();
		foreach ($paybox_data as $key => $value) {
			if (!in_array($key, $returnFields)) {
				unset($paybox_data[$key]);
			}
		}
		return $paybox_data;
	}

	public function getReturn () {

		$returnFieldsString = '';
		$returnFields = $this->getReturnFields();
		foreach ($returnFields as $returnField) {
			$returnFieldsString .= $returnField . ":" . $returnField . ';';
		}
		return substr($returnFieldsString, 0, -1);

	}

	public function getType () {
		switch ($this->_method->type) {

			case 'authorization_capture':
				return self::TYPE_DIRECT_AUTHORIZATION_CAPTURE;
				break;
			case 'authorization_only':
			default:
				return self::TYPE_DIRECT_AUTHORIZATION_ONLY;
				break;
		}

	}

	function getPbxAmount ($amount) {
		return round($amount * 100);

	}

	/**
	 * @param $total
	 * @return string
	 */
	function getPbxTotal ($total) {
		return str_pad($total, 3, "0", STR_PAD_LEFT);
	}

	/**
	 * @param $value
	 * @return string
	 */
	function getUniqueId ($value) {
		return $value . '-' . time();
	}

	/**
	 * @param $post
	 * @param $payboxKey
	 * @return string
	 */

	function getHmac ($post, $payboxKey) {

		$msg = '';

		$msg = $this->stringifyArray($post);
		$hmac = $this->generateHMAC($msg, $payboxKey);
		return $hmac;
	}

	function stringifyArray ($array) {
		$string = '';
		foreach ($array as $key => $value) {
			$string .= trim($key) . "=" . trim($value) . '&';
		}
		return substr($string, 0, -1);
	}

	/**
	 * @return string
	 */

	function getHashAlgo () {

		return "SHA512";
	}

	private function generateHMAC ($msg, $payboxKey) {
		$binKey = pack("H*", $payboxKey);
		$hmac = strtoupper(hash_hmac($this->getHashAlgo(), $msg, $binKey));
		return $hmac;
	}

	function getLangue () {

		$langPaybox = array(
			'fr' => 'FRA',
			'en' => 'GBR',
			'es' => 'ESP',
			'it' => 'ITA',
			'de' => 'DEU',
			'nl' => 'NLD',
			'se' => 'SWE',
			'pt' => 'PRT',
		);
		$lang = JFactory::getLanguage();
		$tag = strtolower(substr($lang->get('tag'), 0, 2));
		if (array_key_exists($tag, $langPaybox)) {
			return $langPaybox[$tag];
		} else {
			return $langPaybox['en'];
		}
	}

	function getTypePaiement () {
		return self::TYPEPAIEMENT_CARTE;
	}

	function getTypeCarte () {
		return self::TYPECARTE_CB;
	}

	function getTime () {
		return date("c");
	}

	/**
	 * Returns Paybox available server URLS
	 * @return string
	 */

	function getPayboxServerUrl () {

		if ($this->_method->shop_mode == 'test') {
			$url = 'https://preprod-tpeweb.paybox.com/php/';
		} else {
			$url = 'https://' . $this->getPayboxServerAvailable() . '/php/';
		}
		return $url;

	}

	private function getPayboxServerAvailable () {

		$servers = array(
			'tpeweb.paybox.com', //serveur primaire
			'tpeweb1.paybox.com' //serveur secondaire
		);
		static $c = null;
		if(isset($c)) return $c;

		foreach ($servers as $server) {
			$doc = new DOMDocument();
			$doc->loadHTMLFile('https://' . $server . '/load.html');

			$server_status = "";
			$element = $doc->getElementById('server_status');
			if ($element) {
				$server_status = $element->textContent;
			}
			if ($server_status == "OK") {
				$c = $server;
				return $server;
			}
		}
		$c = FALSE;
		$this->plugin->debugLog('getPayboxServerAvailable : no server are available' . var_export($servers, true), 'error');
		return FALSE;
	}

	/**
	 * @param $paybox_data
	 * @return mixed
	 */
	function getOrderNumber ($order_number) {
		return $order_number;
	}

	/**
	 * @return array
	 */

	function getExtraPluginNameInfo () {

		return false;
	}

	/**
	 * @param $cart
	 * @param $method
	 * @param $cart_prices
	 * @return bool
	 */

	function checkConditions ($cart, $method, $cart_prices) {

		if (!$this->getPayboxServerUrl()) {
			$this->plugin->debugLog('getPayboxServerUrl FALSE', 'checkConditions', 'debug');
			$this->pbxError('No Pbx server available');
			return false;
		}
		if (!$this->getPayboxReturnUrls()) {
			$this->plugin->debugLog('getPayboxReturnUrls FALSE', 'checkConditions', 'debug');
			return false;
		}
		$this->convert_condition_amount($method);
		$address = $cart->getST();

		$amount = $cart_prices['salesPrice'];
		$amount_cond = true;

		if ($method->integration == 'recurring' AND $amount <= $method->recurring_min_amount) {
			$this->plugin->debugLog('recurring_min_amount FALSE' . $amount . ' ' . $method->recurring_min_amount, 'checkConditions', 'debug');
			return false;
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
		if (in_array($address['virtuemart_country_id'], $countries) || count($countries) == 0) {
			if ($amount_cond) {
				return TRUE;
			}
		}
		$this->plugin->debugLog(' FALSE', 'checkConditions', 'debug');
		return FALSE;
	}

	/**
	 * Converts amounts to accept different decimal format (, and .)
	 * @param $method
	 */
	function convert_condition_amount (&$method) {
		//$method->recurring_min_amount = (float)str_replace(',', '.', $method->recurring_min_amount);
		$method->min_amount_3dsecure = (float)str_replace(',', '.', $method->min_amount_3dsecure);
	}

	/**
	 * @param $paybox_data
	 * @param $order
	 * @return mixed
	 */

	function getOrderHistory ($paybox_data, $order, $payments) {
		$amountInCurrency = vmPSPlugin::getAmountInCurrency($paybox_data['M'] * 0.01, $order['details']['BT']->order_currency);
		$order_history['comments'] = vmText::sprintf('VMPAYMENT_' . $this->plugin_name . '_PAYMENT_STATUS_CONFIRMED', $amountInCurrency['display'], $order['details']['BT']->order_number);
		$order_history['comments'] .= "<br />" . vmText::_('VMPAYMENT_' . $this->plugin_name . '_RESPONSE_S') . ' ' . $paybox_data['S'];

		$order_history['customer_notified'] = true;
		$status_success = 'status_success_' . $this->_method->debit_type;
		$order_history['order_status'] = $this->_method->$status_success;
		return $order_history;
	}


	/**
	 * @param        $keyfile
	 * @param bool   $pub
	 * @param string $pass
	 * @return bool|resource
	 */
	private function loadKey ($keyfile, $public_key = TRUE, $pass = '') {


		$fp = $filedata = $key = FALSE; // initialisation variables
		$fsize = filesize($keyfile); // taille du fichier
		if (!$fsize) {
			$this->pbxError('loadKey :' . 'Key File:' . $keyfile . ' not found');
			$this->plugin->debugLog('loadKey :' . 'Key File:' . $keyfile . ' not found', 'error');
			return FALSE;
		}
		$fp = fopen($keyfile, 'r'); // ouverture fichier
		if (!$fp) {
			$this->pbxError('Cannot open Key File' . $keyfile);
			$this->plugin->debugLog('loadKey :' . 'Cannot open Key File' . $keyfile, 'error');
			return FALSE;
		}
		$filedata = fread($fp, $fsize);
		fclose($fp);
		if (!$filedata) {
			$this->pbxError('Empty Key File' . $keyfile);
			$this->plugin->debugLog('loadKey :' . 'Empty Key File' . $keyfile, 'error');
			return FALSE;
		}
		if ($public_key) {
			$key = openssl_pkey_get_public($filedata);
		} // recuperation de la cle publique
		else // ou recuperation de la cle privee
		{
			$key = openssl_pkey_get_private(array($filedata, $pass));
		}
		return $key; // renvoi cle ( ou erreur )
	}

	/**
	 * @param $keyfile
	 * @param $queryString
	 * @return bool
	 */

	public function pbxIsValidSignature ($keyfile, $queryString) {
		//return true;
		$key = $this->loadKey($keyfile);
		if (!$key) {
			return false;
		}
		$sig = '';
		$queryStringNoSig = "";
		$this->GetSignedData($queryString, $queryStringNoSig, $sig);
		$sigURLDecoded = $this->getSignatureDecoded($sig, true);
		$sigURLNotDecoded = $this->getSignatureDecoded($sig, false);
		$verifySigURLDecoded = openssl_verify($queryStringNoSig, $sigURLDecoded, $key);
		$verifySigURLNotDecoded = openssl_verify($queryStringNoSig, $sigURLNotDecoded, $key);
		openssl_free_key($key);
		// openssl_verify: verification : 1 si valide, 0 si invalide, -1 si erreur
		if ($verifySigURLDecoded or $verifySigURLNotDecoded) {
			$msg = 'PbxVerSign :' . 'openssl_verify return value DECODED: ' . $verifySigURLDecoded . '<br />';
			$msg .= 'PbxVerSign :' . 'openssl_verify return value NOT DECODED: ' . $verifySigURLNotDecoded . '<br />';
			$this->plugin->debugLog($msg, 'pbxIsValidSignature', 'debug');
			return true;
		}
		$msg = 'PbxVerSign :' . 'openssl_verify return value DECODED: ' . $verifySigURLDecoded . '<br />';
		$msg .= 'PbxVerSign :' . 'openssl_verify return value NOT DECODED: ' . $verifySigURLNotDecoded . '<br />';
		$msg .= '            ' . 'query sign ' . $queryString . '<br />';
		$msg .= '            ' . 'data ' . $queryStringNoSig . '<br />';
		//$msg .= '            ' . 'sig ' . $sig . '<br />';
		// we cannot send an error at this stage because may be the signature is not valid from the
		$this->plugin->debugLog($msg, 'pbxIsValidSignature', 'debug');
		return false;

	}

	/**
	 * @param $message
	 */

	function pbxError ($message) {
		$public = "";
		if ($this->_method->debug) {
			$public = $message;
		}
		vmError($message, $public);
	}

	/**
	 * renvoi les donnes signees et la signature
	 * @param $qrystr
	 * @param $data
	 * @param $sig
	 */
	public function GetSignedData ($qrystr, &$data, &$sig) {
		$pos = strrpos($qrystr, '&');
		$data = substr($qrystr, 0, $pos);
		$pos = strpos($qrystr, '=', $pos) + 1;
		$sig = substr($qrystr, $pos);
	}


	/**
	 * @param $sig
	 * @param $doDecode
	 * @return string
	 */
	function getSignatureDecoded ($sig, $doDecode) {
		if ($doDecode) {
			$this->plugin->debugLog('URL DO DECODE', 'debug');
			$sig = urldecode($sig);
		} else {
			$this->plugin->debugLog('URL NOT DECODE', 'debug');
		}
		$sig = base64_decode($sig); //decodage Base 64
		return $sig;
	}


}
