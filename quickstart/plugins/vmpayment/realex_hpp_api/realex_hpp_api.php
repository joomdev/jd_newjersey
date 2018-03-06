<?php

defined('_JEXEC') or die();

/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: realex_hpp_api.php 9560 2017-05-30 14:13:21Z Milbo $
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
if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart'.DS.'helpers'.DS.'config.php');

if (!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

if (!class_exists('RealexHelperRealex')) {
	require(VMPATH_ROOT . DS.'plugins'. DS.'vmpayment'. DS.'realex_hpp_api'. DS.'realex_hpp_api'. DS.'helpers'. DS.'helper.php');
}
if (!class_exists('RealexHelperCustomerData')) {
	require(VMPATH_ROOT .   DS.'plugins'. DS.'vmpayment'. DS.'realex_hpp_api'. DS.'realex_hpp_api'. DS.'helpers'. DS.'customerdata.php');
}

if (!class_exists('vmPSPlugin')) {
	require(VMPATH_PLUGINLIBS . DS . 'vmpsplugin.php');
}

class plgVmPaymentRealex_hpp_api extends vmPSPlugin {

	private $customerData;

	function __construct (& $subject, $config) {
		parent::__construct($subject, $config);
		if (!class_exists('RealexHelperCustomerData')) {
			require(VMPATH_ROOT . DS.'plugins'.DS.'vmpayment'.DS.'realex_hpp_api'.DS.'realex_hpp_api'.DS.'helpers'.DS.'customerdata.php');
		}
		$this->customerData = new RealexHelperCustomerData();
		$this->_loggable = TRUE;
		$this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id';
		$this->_tableId = 'id';
		$varsToPush = $this->getVarsToPush();
		$this->setCryptedFields(array('shared_secret', 'rebate_password'));
		$this->setConfigParameterable($this->_configTableFieldName, $varsToPush);


	}

	/**
	 * Create the table for this plugin if it does not yet exist.
	 */
	protected function getVmPluginCreateTableSQL () {
		return $this->createTableSQL('Payment Realex_hpp_api Table');
	}

	/**
	 * Fields to create the payment table
	 * @return string SQL Fileds
	 */
	function getTableSQLFields () {
		$SQLfields = array(
			'id'                           => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_order_id'          => 'int(11) UNSIGNED',
			'order_number'                 => 'char(64)',
			'virtuemart_paymentmethod_id'  => 'mediumint(1) UNSIGNED',
			'payment_name'                 => 'varchar(5000)',
			'payment_order_total'          => 'decimal(15,5) NOT NULL',
			'payment_currency'             => 'smallint(1)',
			'email_currency'               => 'smallint(1)',
			'cost_per_transaction'         => 'decimal(10,2)',
			'cost_percent_total'           => 'decimal(10,2)',
			'tax_id'                       => 'smallint(1)',
			'realex_hpp_api_custom'                => 'varchar(255)',
			'realex_hpp_api_request_type_response' => 'varchar(32)',
			'realex_hpp_api_response_result'       => 'varchar(3)',
			'realex_hpp_api_response_pasref'       => 'varchar(50)',
			'realex_hpp_api_response_authcode'     => 'varchar(10)',
			'realex_hpp_api_fullresponse_format'   => 'varchar(10)',
			'realex_hpp_api_fullresponse'          => 'text',
		);
		return $SQLfields;
	}

	/**
	 * @param $cart
	 * @param $order
	 * @return bool|null
	 */
	public function plgVmConfirmedOrder ($cart, $order) {

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
		//$this->setInConfirmOrder($cart);
		$email_currency = $this->getEmailCurrency($this->_currentMethod);

		$payment_name = $this->renderPluginName($this->_currentMethod, 'order');

		$realexInterface = $this->_loadRealexInterface();
		$realexInterface->loadCustomerData();

		$realexInterface->debugLog('order number: ' . $order['details']['BT']->order_number, 'plgVmConfirmedOrder', 'debug');
		$realexInterface->setCart($cart);
		/*
		if (!$realexInterface->validateConfirmedOrder()) {
			vmInfo('VMPAYMENT_REALEX_HPP_API_PLEASE_SELECT_OPTION');
			return false;
		}
		*/
		$realexInterface->setOrder($order);
		$realexInterface->setPaymentCurrency();
		$realexInterface->setTotalInPaymentCurrency($order['details']['BT']->order_total);


		// Prepare data that should be stored in the database
		$dbValues['order_number'] = $order['details']['BT']->order_number;
		$dbValues['payment_name'] = str_replace(array('\t', '\n'), '', $payment_name);

		$dbValues['virtuemart_paymentmethod_id'] = $cart->virtuemart_paymentmethod_id;
		$dbValues['realex_hpp_api_custom'] = $realexInterface->getContext();
		$dbValues['cost_per_transaction'] = $this->_currentMethod->cost_per_transaction;
		$dbValues['cost_percent_total'] = $this->_currentMethod->cost_percent_total;
		$dbValues['payment_currency'] = $realexInterface->getPaymentCurrency();
		$dbValues['email_currency'] = $email_currency;
		$dbValues['payment_order_total'] = $realexInterface->getTotalInPaymentCurrency();
		$dbValues['tax_id'] = $this->_currentMethod->tax_id;
		$this->storePSPluginInternalData($dbValues);

		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);

		$selectedCCParams = array();
		if ($this->_currentMethod->integration == 'redirect') {
			if (!$realexInterface->doRealvault($selectedCCParams)) {
				$html = $realexInterface->sendPostRequest();
				vRequest::setVar('html', $html);
				$cart->_confirmDone = FALSE;
				$cart->_dataValidated = FALSE;
				$cart->setCartIntoSession();

			} else {

				if (!JFactory::getUser()->guest AND $this->_currentMethod->realvault) {
					$remoteCCFormParams =$realexInterface->getRemoteCCFormParams();
					$html = $this->renderByLayout('remote_cc_form', $remoteCCFormParams);
					vRequest::setVar('html', $html);
					vRequest::setVar('display_title', false);
					return;
				}
				$response = $realexInterface->requestReceiptIn($selectedCCParams);
				$request_type = $realexInterface->request_type . '_request';
				$this->_storeRealexInternalData($realexInterface->xml_request, $this->_currentMethod->virtuemart_paymentmethod_id, $order['details']['BT']->virtuemart_order_id, $order['details']['BT']->order_number, $request_type);

				$realexInterface->manageResponseRequestReceiptIn($response);
				$xml_response = simplexml_load_string($response);
				$success = $realexInterface->isResponseSuccess($xml_response);
				if ($success) {
					$status = $this->_currentMethod->status_success;
					$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
					$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);
					$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CONFIRMED', $amountInCurrency['display'], $order['details']['BT']->order_number);

				} else {
					$order_history['comments'] = vmText::_('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED');
					$status = $this->_currentMethod->status_canceled;
				}
				$order_history['customer_notified'] = true;
				$order_history['order_status'] = $status;

				$modelOrder = VmModel::getModel('orders');
				$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, TRUE);

				$payments = $this->getDatasByOrderId($realexInterface->order['details']['BT']->virtuemart_order_id);
				$params = $realexInterface->getResponseParams($payments);
				$params['payment_name'] = $this->renderPluginName($this->_currentMethod, 'order');

				$html = $this->renderByLayout('response',$params);
				vRequest::setVar('html', $html);
				$this->customerData->clear();

				if ($success) {
					if (isset($payments[0]->realex_hpp_api_custom)) {
						$cart->emptyCart();
					}
				}

			}
		} else {
			$remoteCCFormParams =$realexInterface->getRemoteCCFormParams();
			$html = $this->renderByLayout('remote_cc_form', $remoteCCFormParams);
			vRequest::setVar('html', $html);
			vRequest::setVar('display_title', false);
		}


		return true;


	}


	function updateOrderStatus ($order, $useTriggers = true) {
		$realexInterface = $this->_loadRealexInterface();
		$realexInterface->setOrder($order);
		$realexInterface->setPaymentCurrency();
		$realexInterface->setTotalInPaymentCurrency($order['details']['BT']->order_total);
		$cart = VirtueMartCart::getCart();
		$realexInterface->setCart($cart, false);
		if (!($payments = $this->getDatasByOrderId($order['details']['BT']->virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}
		$payment = end($payments);
		$xml_response = simplexml_load_string($payment->realex_hpp_api_fullresponse);
		$order_history = array();

		$success = $realexInterface->isResponseSuccess($xml_response);
		if ($success) {
			$status = $this->_currentMethod->status_success;
			$amountValue = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
			//$amountValueInPaymentCurrency = vmPSPlugin::getAmountInCurrency($realexInterface->getTotalInPaymentCurrency(), $realexInterface->getPaymentCurrency());
			$currencyDisplay = CurrencyDisplay::getInstance($realexInterface->cart->pricesCurrency);

			$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CONFIRMED', $amountValue['display'], $order['details']['BT']->order_number);
			$order_history['success'] = true;

			if (isset($xml_response->dccinfo) AND isset($xml_response->dccinfo->cardholderrate)) {
				$order_history['comments'] .= "<br />";
				if ($xml_response->dccinfo->cardholderrate != 1.0) {
					$order_history['comments'] .= vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_CHARGED', $this->getCardHolderAmount($xml_response->dccinfo->merchantamount), $xml_response->dccinfo->merchantcurrency, $this->getCardHolderAmount($xml_response->dccinfo->cardholderamount), $xml_response->dccinfo->cardholdercurrency);
				} else {
					$order_history['comments'] .= vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_MERCHANT_CURRENCY', $this->getCardHolderAmount($xml_response->dccinfo->merchantamount), $xml_response->dccinfo->merchantcurrency);
				}
				$order_history['comments'] .= "<br />";
			} else {

			}


		} else {
			if ($realexInterface->isResponseDeclined($xml_response)) {
				$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_DECLINED', $realexInterface->order['details']['BT']->order_number);

			} else {
				$order_history['comments'] = vmText::_('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED');
			}
			$status = $this->_currentMethod->status_canceled;
			$order_history['success'] = false;
		}

		$order_history['customer_notified'] = true;
		$order_history['order_status'] = $status;

		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, $useTriggers);
		return $success;
	}

	function redirectToCart ($msg = NULL) {

		if (!$msg) {
			$msg = vmText::_('VMPAYMENT_REALEX_HPP_API_ERROR_TRY_AGAIN');
		}
		$this->customerData->clear();
		$app = JFactory::getApplication();
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart&Itemid=' . vRequest::getInt('Itemid'), false), $msg);
	}


	/*********************/
	/* Private functions */
	/*********************/
	private function _loadRealexInterface () {

		if ($this->_currentMethod->integration == 'redirect') {
			if (!class_exists('RealexHelperRealexRedirect')) {
				require(VMPATH_ROOT .  DS  .'plugins'. DS  .'vmpayment'. DS  .'realex_hpp_api'. DS  .'realex_hpp_api'. DS  .'helpers'. DS  .'redirect.php');
			}
			$realexInterface = new RealexHelperRealexRedirect($this->_currentMethod, $this);
		} else {
			if ($this->_currentMethod->integration == 'remote') {
				if (!class_exists('RealexHelperRealexRemote')) {
					require(VMPATH_ROOT .   DS  .'plugins'. DS  .'vmpayment'. DS  .'realex_hpp_api'. DS  .'realex_hpp_api'. DS  .'helpers'. DS  .'remote.php');
				}
				$realexInterface = new RealexHelperRealexRemote($this->_currentMethod, $this);
			} else {
				Vmerror('Wrong Realex Integration method - developer error '.$this->_currentMethod->integration, 'Wrong Realex Integration method ');
				return NULL;
			}
		}
		return $realexInterface;
	}


	public function plgVmOnPaymentResponseReceived (&$html) {

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

		$order_number = vRequest::getString('on', 0);
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return NULL;
		}

		$payments = $this->getDatasByOrderId($virtuemart_order_id);

		vmLanguage::loadJLang('com_virtuemart');
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$realexInterface = $this->_loadRealexInterface();
		$realexInterface->loadCustomerData();
		$realexInterface->setOrder($order);

		$params = $realexInterface->getResponseParams($payments);
		$params['payment_name'] = $this->renderPluginName($this->_currentMethod, 'order');

		$html = $this->renderByLayout('response',$params);
		$this->customerData->clear();
		$cart = VirtueMartCart::getCart();
		$cart->emptyCart();


		return TRUE;
	}


	public function plgVmOnUserPaymentCancel() {
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', 0);

		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}
		JFactory::getApplication()->enqueueMessage(vmText::_('VMPAYMENT_REALEX_HPP_API_ERROR_TRY_AGAIN'));
	}

	/**
	 * Display stored payment data for an order
	 *
	 * @see components/com_virtuemart/helpers/vmPSPlugin::plgVmOnShowOrderBEPayment()
	 */
	public function plgVmOnShowOrderBEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id) {

		if (!$this->selectedThisByMethodId($virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return FALSE;
		}
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return '';
		}
		$html = $this->showActionOrderBEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, $payments);

		//$html = $this->renderByLayout('orderbepayment', array($payments, $this->_psType));
		//$html = '<table class="adminlist table-striped" >' . "\n";
		//$html .= $this->getHtmlHeaderBE();
		$code = "realex_hpp_api_response_";
		$first = TRUE;
		foreach ($payments as $payment) {
			$html .= '<tr class="row1"><td><strong>' . vmText::_('VMPAYMENT_REALEX_HPP_API_DATE') . '</strong></td><td align="left"><strong>' . $payment->created_on . '</strong></td></tr>';
			// Now only the first entry has this data when creating the order
			if ($first) {
				$html .= $this->getHtmlRowBE('COM_VIRTUEMART_PAYMENT_NAME', $payment->payment_name);
				// keep that test to have it backwards compatible. Old version was deleting that column  when receiving an IPN notification
				if ($payment->payment_order_total and  $payment->payment_order_total != 0.00) {
					$html .= $this->getHtmlRowBE('COM_VIRTUEMART_TOTAL', $payment->payment_order_total * 0.01 . " " . $payment->payment_currency);
				}

				$first = FALSE;
			} else {
				$realexInterface = $this->_loadRealexInterface();

				if (isset($payment->realex_hpp_api_fullresponse) and !empty($payment->realex_hpp_api_fullresponse)) {
					//$realex_data = json_decode($payment->realex_hpp_api_fullresponse);
					if ($payment->realex_hpp_api_fullresponse_format == 'json') {
						$realex_data = json_decode($payment->realex_hpp_api_fullresponse);
					} elseif ($payment->realex_hpp_api_fullresponse_format == 'xml') {
						$html .= $this->getHtmlRowBE('VMPAYMENT_REALEX_HPP_API_RESPONSE_TYPE', $payment->realex_hpp_api_request_type_response);

						$realex_data = simplexml_load_string($payment->realex_hpp_api_fullresponse);
					}

					$html .= $realexInterface->onShowOrderBEPayment($realex_data, $payment->realex_hpp_api_fullresponse_format, $payment->realex_hpp_api_request_type_response, $virtuemart_order_id);

					$html .= '<tr><td></td><td>
    <a href="#" class="RealexLogOpener" rel="' . $payment->id . '" >
        <div style="background-color: white; z-index: 100; right:0; display: none; border:solid 2px; padding:10px;" class="vm-absolute" id="RealexLog_' . $payment->id . '">';
					if ($payment->realex_hpp_api_fullresponse_format != 'xml') {
						foreach ($realex_data as $key => $value) {
							if ($key=='SHA1HASH' OR $key=='SAVED_PMT_DIGITS') {
								$value = $realexInterface->obscureValue($value);
							}
							$html .= ' <b>' . $key . '</b>:&nbsp;' . $value . '<br />';
						}
					} else {
						$xml_realex_hpp_api_fullresponse = simplexml_load_string($payment->realex_hpp_api_fullresponse);
						$xml_realex_hpp_api_fullresponse = $realexInterface->obscureSha1hash($xml_realex_hpp_api_fullresponse);
						//$html .= "<pre>" . htmlentities(wordwrap($realex_hpp_api_fullresponse, 100, "\n", true)) . "</pre>";
						//$html .= $xml_realex_hpp_api_fullresponse->asXML();
						$html .= "<pre>" . wordwrap(print_r($xml_realex_hpp_api_fullresponse, true), 100, "\n", true) . "</pre>";
					}


					$html .= ' </div>
        <span class="icon-nofloat vmicon vmicon-16-xml"></span>&nbsp;';
					$html .= vmText::_('VMPAYMENT_REALEX_HPP_API_VIEW_TRANSACTION_LOG');
					$html .= '  </a>';
					$html .= ' </td></tr>';
				} else {
					//$html .= $realexInterface->onShowOrderBEPaymentByFields($payment);
				}
			}


		}
		$html .= '</table>' . "\n";


		$js = "
	jQuery().ready(function($) {
		$('.RealexLogOpener').click(function() {
			var logId = $(this).attr('rel');
			$('#RealexLog_'+logId).toggle();
			return false;
		});
	});";
		vmJsApi::addJScript("RealexLogOpener",$js);
		return $html;

	}

	private function showActionOrderBEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, $payments) {
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$options = array();
		if ($this->isDelayedSettlement()) {
			$options[] = JHTML::_('select.option', 'settlePayment', vmText::_('VMPAYMENT_REALEX_HPP_API_ORDER_BE_CAPTURE'), 'value', 'text');
		}
		$options[] = JHTML::_('select.option', 'rebatePayment', vmText::_('VMPAYMENT_REALEX_HPP_API_ORDER_BE_REBATE'), 'value', 'text');
		$actionList = JHTML::_('select.genericlist', $options, 'action', '', 'value', 'text', 'capturePayment', 'action', true);


		$html = '<table class="adminlist table"  >' . "\n";
		$html .= $this->getHtmlHeaderBE();
		$html .= '<form action="index.php" method="post" name="updateOrderBEPayment" id="updateOrderBEPayment">';

		$html .= '<tr ><td >';
		$html .= $actionList;
		$html .= ' </td><td>';
		$html .= '<input type="text" id="amount" name="amount" size="20" value="" class="required" maxlength="25"  placeholder="' . vmText::sprintf('VMPAYMENT_REALEX_HPP_API_ORDER_BE_AMOUNT', shopFunctions::getCurrencyByID($payments[0]->payment_currency, 'currency_code_3')) . '"/>';
		$html .= '<input type="hidden" name="type" value="vmpayment"/>';
		$html .= '<input type="hidden" name="name" value="realex_hpp_api"/>';
		$html .= '<input type="hidden" name="view" value="plugin"/>';
		$html .= '<input type="hidden" name="option" value="com_virtuemart"/>';
		$html .= '<input type="hidden" name="virtuemart_order_id" value="' . $virtuemart_order_id . '"/>';
		$html .= '<input type="hidden" name="virtuemart_paymentmethod_id" value="' . $virtuemart_paymentmethod_id . '"/>';

		$html .= '<a class="updateOrderBEPayment btn btn-small" href="#"   >' . vmText::_('COM_VIRTUEMART_SAVE') . '</a>';
		$html .= '</form>';
		$html .= ' </td></tr>';

		vmJsApi::addJScript('vmRealex.updateOrderBEPayment',"
		jQuery(document).ready( function($) {
			jQuery('.updateOrderBEPayment').click(function() {
				document.updateOrderBEPayment.submit();
				return false;

	});
});
");

		//$html .= '</table>'  ;
		return $html;

	}

	/**
	 *  Order status changed
	 * @param $order
	 * @param $old_order_status
	 * @return bool|null
	 */
	public function plgVmOnUpdateOrderPayment (&$order, $old_order_status) {

		//Load the method
		if (!($this->_currentMethod = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}

		//Load the payments
		if (!($payments = $this->getDatasByOrderId($order->virtuemart_order_id))) {
			// JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}
		$updateOrderPaymentStatus = array(
			$this->_currentMethod->status_capture,
			$this->_currentMethod->status_canceled,
			$this->_currentMethod->status_rebate,
		);
		if (!in_array($order->order_status, $updateOrderPaymentStatus)) {
			//vmInfo(vmText::_('VMPAYMENT_REALEX_HPP_API_NO_ACTION'));
			return true;
		}
		$orderModel = VmModel::getModel('orders');
		$orderData = $orderModel->getOrder($order->virtuemart_order_id);
		$requestSent = false;
		$order_history_comment = '';
		$realexInterface = $this->_loadRealexInterface();
		$canDo = true;
		if ($order->order_status == $this->_currentMethod->status_capture AND !$this->isDcc() AND $this->isDelayedSettlement() AND  ($canDo = $this->canDoSettle($realexInterface, $old_order_status, $payments))) {
			$requestSent = true;
			$order_history_comment = vmText::_('VMPAYMENT_REALEX_HPP_API_UPDATE_STATUS_CAPTURE');
			$realexInterface->setOrder($orderData);
			$realexInterface->setPaymentCurrency();
			$realexInterface->setTotalInPaymentCurrency($orderData['details']['BT']->order_total);
			$realexInterface->loadCustomerData();
			$response = $realexInterface->settleTransaction($payments);

		} elseif ($order->order_status == $this->_currentMethod->status_canceled AND ($canDo = $this->canDoVoid($realexInterface, $old_order_status, $payments))) {
			$requestSent = true;
			$order_history_comment = vmText::_('VMPAYMENT_REALEX_HPP_API_UPDATE_STATUS_CANCELED');
			$realexInterface->setOrder($orderData);
			$realexInterface->setPaymentCurrency();
			$realexInterface->setTotalInPaymentCurrency($orderData['details']['BT']->order_total);
			$realexInterface->loadCustomerData();
			$response = $realexInterface->voidTransaction($payments);

		} elseif ($order->order_status == $this->_currentMethod->status_rebate AND ($canDo = $this->canDoRebate($realexInterface, $old_order_status, $payments))) {
			$requestSent = true;
			$response = $this->doRebate($realexInterface, $orderData, $payments);
		}
		if ($requestSent) {
			if ($response) {
				$db_values = $this->_storeRealexInternalData($response, $this->_currentMethod->virtuemart_paymentmethod_id, $orderData['details']['BT']->virtuemart_order_id, $orderData['details']['BT']->order_number, $realexInterface->request_type);

				$xml_response = simplexml_load_string($response);
				$success = $realexInterface->isResponseSuccess($xml_response);
				if (!$success) {
					$error = $xml_response->message . " (" . (string)$xml_response->result . ")";
					$realexInterface->displayError($error);
					return false;
					//return NULL;
				} else {
					$order_history = array();
					$order_history['comments'] = $order_history_comment;
					$order_history['customer_notified'] = false;
					$order_history['order_status'] = $order->order_status;
					$modelOrder = VmModel::getModel('orders');
					$modelOrder->updateStatusForOneOrder($orderData['details']['BT']->virtuemart_order_id, $order_history, false);
					return true;
				}
			} else {
				vmError('VMPAYMENT_REALEX_HPP_API_NO_RESPONSE');
				return false;
			}

		} else {
			//vmInfo(vmText::_('VMPAYMENT_REALEX_HPP_API_NO_ACTION'));
		}
		return $canDo;
	}

	function plgVmOnSelfCallBE ($type, $name, &$render) {
		if ($name != $this->_name || $type != 'vmpayment') {
			return FALSE;
		}

		$virtuemart_paymentmethod_id = vRequest::getInt('virtuemart_paymentmethod_id');
		//Load the method
		if (!($this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}

		$amount = vRequest::getFloat('amount');
		$actions=array('rebatePayment', 'settlePayment');
		$action = vRequest::getCmd('action');
		if (!in_array($action, $actions)) {
			vmError('VMPAYMENT_REALEX_HPP_API_UPDATEPAYMENT_UNKNOWN_ACTION');
			return NULL;
		}
		$virtuemart_order_id = vRequest::getInt('virtuemart_order_id');
		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			return null;
		}
		$orderModel = VmModel::getModel('orders');
		$orderData = $orderModel->getOrder(vRequest::getInt('virtuemart_order_id'));
		$requestSent = false;
		$order_history_comment = '';
		$realexInterface = $this->_loadRealexInterface();
		$canDo = true;
		if ( $action=='settlePayment' ) {

			$requestSent = true;
			$order_history_comment = vmText::_('VMPAYMENT_REALEX_HPP_API_UPDATE_STATUS_CAPTURE');
			$realexInterface->setOrder($orderData);
			$realexInterface->setPaymentCurrency();
			$realexInterface->setTotalInPaymentCurrency($amount);
			$realexInterface->loadCustomerData();
			$response = $realexInterface->settleTransaction($payments);

		} elseif ( $action=='rebatePayment') {
			$requestSent = true;
			$response = $this->doRebate($realexInterface, $orderData, $payments, $amount);
		}
		if ($requestSent) {
			if ($response) {
				$db_values = $this->_storeRealexInternalData($response, $this->_currentMethod->virtuemart_paymentmethod_id, $orderData['details']['BT']->virtuemart_order_id, $orderData['details']['BT']->order_number, $realexInterface->request_type);

				$xml_response = simplexml_load_string($response);
				$success = $realexInterface->isResponseSuccess($xml_response);
				if (!$success) {
					$error = $xml_response->message . " (" . (string)$xml_response->result . ")";
					$realexInterface->displayError($error);

				} else {
					$order_history = array();
					$order_history['comments'] = $order_history_comment;
					$order_history['customer_notified'] = false;
					$order_history['order_status'] = $orderData['details']['BT']->order_status;
					$modelOrder = VmModel::getModel('orders');
					$modelOrder->updateStatusForOneOrder($orderData['details']['BT']->virtuemart_order_id, $order_history, false);
				}
			} else {
				vmError('VMPAYMENT_REALEX_HPP_API_NO_RESPONSE');
			}

		}

		$app = JFactory::getApplication();
		$link = 'index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $virtuemart_order_id;

		$app->redirect(JRoute::_($link, FALSE));

	}



	/**
	 * Merchants can Rebate for any amount up to 115% of the original order value.
	 * Pop up will ask for the amount
	 * @param $realexInterface
	 * @param $orderData
	 * @param $payments
	 */
	function doRebate ($realexInterface, $orderData, $payments, $amount=false) {


		$order_history_comment = vmText::_('VMPAYMENT_REALEX_HPP_API_UPDATE_STATUS_REBATE');
		$realexInterface->setOrder($orderData);
		$realexInterface->setPaymentCurrency();
		if ($amount===false) {
			$amount=$orderData['details']['BT']->order_total;
		}
		$realexInterface->setTotalInPaymentCurrency($amount);
		$realexInterface->loadCustomerData();
		$response = $realexInterface->rebateTransaction($payments);
		return $response;
	}


	/**
	 * Check if Dcc option
	 * @return bool
	 */
	private function isDcc () {
		if ($this->_currentMethod->dcc) {
			return true;
		}
		return false;
	}


	/** check if the setelement is set to Delayed */
	private function isDelayedSettlement () {
		if (($this->_currentMethod->settlement == 'delayed')) {
			return true;
		}
		return false;
	}


	/**
	 * Check if Can do settle
	 * @param $realexInterface
	 * @param $old_order_status
	 * @param $payments
	 * @return bool
	 */
	private function canDoSettle ($realexInterface, $old_order_status, $payments) {

		// Delayed settlement
		if (!($old_order_status == $this->_currentMethod->status_success)) {
			vmError('VMPAYMENT_REALEX_HPP_API_ERROR_CANNOT_SETTLE');
			return false;
		}


		return true;
	}

	/**
	 * Before settlement, it is possible to void an authorisation
	 * Void (if same day or delayed settlement)
	 *
	 * @param $old_order_status
	 * @param $payments
	 * @param $realexInterface
	 * @return bool
	 */
	private function canDoVoid ($realexInterface, $old_order_status, $payments) {

		if ($this->_currentMethod->settlement == 'auto') {
			if (!($old_order_status == $this->_currentMethod->status_success)) {
				vmError('VMPAYMENT_REALEX_HPP_API_ERROR_CANNOT_VOID');
				return false;
			}
		} else {
			if (!($old_order_status == $this->_currentMethod->status_success OR  $old_order_status == $this->_currentMethod->status_capture)) {
				vmError('VMPAYMENT_REALEX_HPP_API_ERROR_CANNOT_VOID');
				return false;
			}
		}

		return true;
	}

	/**
	 * And in live mode, merchants can only rebate transactions the day after they've settled.
	 * It maybe would be best not to allow merchants to attempt rebate on the same day a transactions is processed.
	 * @param $old_order_status
	 * @param $payments
	 * @param $realexInterface
	 * @return bool
	 */
	private function canDoRebate ($realexInterface, $old_order_status, $payments) {
		if ($this->transactionIsDcc($realexInterface, $payments)) {
			vmError(vmText::_('VMPAYMENT_REALEX_HPP_API_ERROR_REBATE_DCC_TRANSACTION'));
			return false;
		}
		if ($this->_currentMethod->settlement == 'auto') {
			if (!($old_order_status == $this->_currentMethod->status_success)) {
				vmError('VMPAYMENT_REALEX_HPP_API_ERROR_CANNOT_REBATE');
				return false;
			}
		} else {
			if (!($old_order_status == $this->_currentMethod->status_capture)) {
				vmError('VMPAYMENT_REALEX_HPP_API_ERROR_CANNOT_REBATE');
				return false;
			}
		}

		/*
		if ($this->_currentMethod->settlement=='auto') return true;
		if (!($settleTime = $this->transactionIsSettled($realexInterface, $payments))) {
			vmError(vmText::sprintf('VMPAYMENT_REALEX_HPP_API_ERROR_REBATE_SETTLE_FIRST', $payments[0]->order_number));
			return false;
		}
		//And in live mode, merchants can only rebate transactions the day after they've settled.
		if ($this->_currentMethod->shop_mode == 'sandbox') {
			return true;
		}
*/
		return true;
	}

	private function transactionIsSettled ($realexInterface, $payments) {
		$payment = $realexInterface->getTransactionData($payments, array($realexInterface::REQUEST_TYPE_SETTLE));
		if (!$payment) {
			return false;
		}
		return $payment->created_on;
	}


	private function transactionIsDcc ($realexInterface, $payments) {
		if (!$this->_currentMethod->dcc) {
			return false;
		}

		$payment = $realexInterface->getTransactionData($payments, array($realexInterface::REQUEST_TYPE_SETTLE));
		if (!$payment) {
			return false;
		}
		return true;
	}

	private function transactionIsAuth ($realexInterface, $payments) {
		$payment = $realexInterface->getLastTransactionData($payments, array(
		                                                                    $realexInterface::REQUEST_TYPE_AUTH,
		                                                                    $realexInterface::REQUEST_TYPE_RECEIPT_IN
		                                                               ));
		if (!$payment) {
			return false;
		}
		return $payment->created_on;
	}

	function plgVmOnUpdateOrderLinePayment (&$order) {
		if (!($this->_currentMethod = $this->getVmPluginMethod($order->virtuemart_paymentmethod_id))) {
			return NULL; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			return NULL;
		}
	}


	/**
	 * Create the table for this plugin if it does not yet exist.
	 * This functions checks if the called plugin is active one.
	 * When yes it is calling the standard method to create the tables
	 *
	 */

	public function plgVmOnStoreInstallPaymentPluginTable ($jplugin_id) {
		if ($jplugin_id != $this->_jid) {
			return FALSE;
		}
		$this->_currentMethod = $this->getPluginMethod(vRequest::getInt('virtuemart_paymentmethod_id'));
		if ($this->_currentMethod->published) {
			$required_parameters = array('merchant_id', 'shared_secret', 'subaccount');
			foreach ($required_parameters as $required_parameter) {
				if (empty ($this->_currentMethod->$required_parameter)) {
					$text = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_REALEX_HPP_API_' . $required_parameter), $this->_currentMethod->payment_name, $this->_currentMethod->virtuemart_paymentmethod_id);
					vmWarn($text);
				}
			}

		}

		$this->createPayerRefTable();
		$this->createPmtRefTable();

		return $this->onStoreInstallPluginTable($jplugin_id);
	}


	private function createPmtRefTable () {
		$db = JFactory::getDBO();
		$q = 'SELECT `extension_id` FROM `#__extensions` WHERE `folder` = "vmuserfield" and `state`="0" AND `element` = "'.$this->_name.'"';
		$db->setQuery($q);
		$extension_id = $db->loadResult();
		if (empty($extension_id)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(vmText::_('VMPAYMENT_REALEX_HPP_API_NO_PLUGIN_INSTALLED'));
			return;
		}
		// publish the plugin
		$q="UPDATE  `#__extensions` SET  `enabled` =  '1' WHERE  `extension_id` =".$extension_id;
		$db->setQuery($q);
		$db->execute();

// is this plugin already
		$q = 'SELECT `virtuemart_userfield_id` FROM `#__virtuemart_userfields` WHERE `userfield_jplugin_id` = ' . $extension_id;
		$db->setQuery($q);
		$virtuemart_userfield_id = $db->loadResult();
		if (empty($virtuemart_userfield_id)) {
			//$app = JFactory::getApplication();
			//$app -> enqueueMessage(vmText::_('VMUSERFIELD_REALEX_NO_PLUGIN_ALREADY_INSTALLED'));

			$userFieldsModel = VmModel::getModel('UserFields');

			$data['virtuemart_userfield_id'] = 0;

			$data['published'] = 1;
			$data['userfield_jplugin_id'] = $extension_id;
			$data['required'] = 0;
			$data['account'] = 1;
			$data['shipment'] = 0;
			$data['registration'] = 0;
			$data['vNames'] = array();
			$data['vValues'] = array();
			$data['name'] = 'realex_hpp_api';
			$data['type'] = 'pluginrealex_hpp_api';
			$data['title'] = 'Payment means';
			$ret = $userFieldsModel->store($data);

			if (!$ret) {
				vmError(vmText::_('VMPAYMENT_REALEX_HPP_API_CREATE_USERFIELD_FAILED') . " " . $data['name'] . " " . $ret);
			} else {
				vmInfo(vmText::_('VMPAYMENT_REALEX_HPP_API_CREATE_USERFIELD_OK') . " " . $data['name']);
			}
		}

		JLoader::import('joomla.plugin.helper');
		JPluginHelper::importPlugin('vmuserfield');
		JFactory::getApplication()->triggerEvent('plgVmOnStoreInstallPluginTable', array(
		                                                          'userfield',
		                                                          'realex_hpp_api'
		                                                     ));
	}

	/**
	 * Fields to create the payment table
	 * @return string SQL Fileds
	 */
	private function getPayerRefTableSQLFields () {
		// We must save both , since the customer number can be changed
		$SQLfields = array(
			'id'                 => 'int(11) UNSIGNED NOT NULL AUTO_INCREMENT',
			'virtuemart_user_id' => 'int(11) UNSIGNED',
			'payer_ref'          => 'char(32)',
			'merchant_id'        => 'varchar(128)',
		);
		return $SQLfields;
	}

	/**
	 * @param $tableComment
	 * @return string
	 */
	private function createPayerRefTable ($tablesFields = 0) {
		$payerRefTableName = $this->getPayerRefTableName();
		$query = "CREATE TABLE IF NOT EXISTS `" . $payerRefTableName . "` (";

		$SQLfields = $this->getPayerRefTableSQLFields();
		$loggablefields = $this->getTableSQLLoggablefields();
		foreach ($SQLfields as $fieldname => $fieldtype) {
			$query .= '`' . $fieldname . '` ' . $fieldtype . " , ";
		}
		foreach ($loggablefields as $fieldname => $fieldtype) {
			$query .= '`' . $fieldname . '` ' . $fieldtype . ", ";
		}

		$query .= "	      PRIMARY KEY (`id`)
	    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Realex PayerRef Table' AUTO_INCREMENT=1 ;";


		$db = JFactory::getDBO();
		$db->setQuery($query);
		if (!$db->execute()) {
			JError::raiseWarning(1, $payerRefTableName . '::createPayerRefTable: ' . vmText::_('COM_VIRTUEMART_SQL_ERROR') . ' ' . $db->stderr(TRUE));
			echo $payerRefTableName . '::createPayerRefTable: ' . vmText::_('COM_VIRTUEMART_SQL_ERROR') . ' ' . $db->stderr(TRUE);
		}

	}

	/**
	 * This event is fired after the payment method has been selected. It can be used to store
	 * additional payment info in the cart.
	 *
	 * @param VirtueMartCart $cart: the actual cart
	 * @return null if the payment was not selected, true if the data is valid, error message if the data is not vlaid
	 *
	 */
	public function plgVmOnSelectCheckPayment (VirtueMartCart $cart) {
		return $this->onSelectCheck($cart);
	}

	/**
	 * This is for checking the input data of the payment method within the checkout
	 *
	 */
	public function plgVmOnCheckoutCheckDataPayment (VirtueMartCart $cart) {

		if (!$this->selectedThisByMethodId($cart->virtuemart_paymentmethod_id)) {
			return NULL; // Another method was selected, do nothing
		}
		if (!($this->_currentMethod = $this->getVmPluginMethod($cart->virtuemart_paymentmethod_id))) {
			return NULL;
		}

		return true;
	}


	/**
	 * plgVmDisplayListFEPayment
	 * This event is fired to display the pluginmethods in the cart (edit shipment/payment) for exampel
	 *
	 * @param VirtueMartCart  $cart Cart object
	 * @param integer $selected ID of the method selected
	 * @return boolean True on success, false on failures, null when this plugin was not selected.
	 * On errors, JError::raiseWarning (or JError::raiseError) must be used to set a message.
	 *
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
		foreach ($this->methods as $this->_currentMethod) {
			if ($this->checkConditions($cart, $this->_currentMethod, $cart->cartPrices)) {

				$html = '';
				$cart_prices = array();
				$cart_prices['withTax'] = '';
				$cart_prices['salesPrice'] = '';
				$methodSalesPrice = $this->setCartPrices($cart, $cart_prices, $this->_currentMethod);
				//if ($selected == $method->virtuemart_paymentmethod_id) {
				//	$this->customerData->load();
				//}
				$html .= '<br />';
				$payment_name = $this->renderPluginName($this->_currentMethod, 'DisplayListFEPayment');
				//$html .= $this->getPluginHtml($this->_currentMethod, $selected, $methodSalesPrice);
				$realexInterface = $this->_loadRealexInterface();
				if ($realexInterface == NULL) {
					vmdebug('renderPluginName', $this->_currentMethod);
					break;
				}
				$realexInterface->loadCustomerData();

				if ($selected == $this->_currentMethod->virtuemart_paymentmethod_id) {
					$checked = 'checked="checked"';
				} else {
					$checked = '';
				}
				/*
				$ccDropdown = "";
				if ($this->_currentMethod->integration == 'redirect') {
					if (!JFactory::getUser()->guest AND $this->_currentMethod->realvault) {
						$selected_cc = $this->customerData->getVar('saved_cc_selected');
						$ccDropdown = $realexInterface->getCCDropDown($this->_currentMethod->virtuemart_paymentmethod_id, JFactory::getUser()->id, $selected_cc);
					}
				}
				*/
				$html .= $this->renderByLayout('redirect_form', array(
				                                                     //'creditcardsDropDown'         => $ccDropdown,
				                                                     'virtuemart_paymentmethod_id' => $this->_currentMethod->virtuemart_paymentmethod_id,
				                                                     'payment_name'                => $payment_name,
				                                                     'checked'                     => $checked,
				                                                ));

				$htmla[] = $html;
			}
		}
		$htmlIn[] = $htmla;
		return true;

	}


	/**
	 * Check if the payment conditions are fulfilled for this payment method
	 * @param VirtueMartCart $cart
	 * @param int            $activeMethod
	 * @param array          $cart_prices
	 * @return bool
	 */
	protected function checkConditions ($cart, $method, $cart_prices) {


		$method->min_amount = (float)$method->min_amount;
		$method->max_amount = (float)$method->max_amount;

		$address = (($cart->ST == 0) ? $cart->BT : $cart->ST);

		$amount = $this->getCartAmount($cart_prices);
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
	 * plgVmonSelectedCalculatePricePayment
	 * Calculate the price (value, tax_id) of the selected method
	 * It is called by the calculator
	 * This function does NOT to be reimplemented. If not reimplemented, then the default values from this function are taken.
	 * @cart: VirtueMartCart the current cart
	 * @cart_prices: array the new cart prices
	 * @return null if the method was not selected, false if the shiiping rate is not valid any more, true otherwise
	 *
	 *
	 */

	public function plgVmonSelectedCalculatePricePayment (VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name) {
		return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
	}

	/*
			 * @param $method plugin
		 *  @param $where from where tis function is called
			 */

	function renderPluginName ($method, $where = 'checkout') {

		$display_logos = "";
		if (!class_exists('RealexHelperCustomerData')) {
			require(VMPATH_ROOT   .'plugins'. DS  .'vmpayment'. DS  .'realex_hpp_api'. DS  .'realex_hpp_api'. DS  .'helpers'. DS  .'customerdata.php');
		}
		$this->_currentMethod = $method;
		$realexInterface = $this->_loadRealexInterface();
		if ($realexInterface == NULL) {
			vmdebug('renderPluginName', $method);
			return;
		}
		$realexInterface->getCustomerData();
		$extraInfo = '';
		if ($realexInterface->customerData->getVar('selected_method') == $method->virtuemart_paymentmethod_id) {
			//$extraInfo = $realexInterface->getExtraPluginInfo();
			//$extraInfo['cc_number'] =$realexInterface->cc_mask($extraInfo['cc_number']);
		}


		$logos = $method->payment_logos;
		if (!empty($logos)) {
			$display_logos = $this->displayLogos($logos) . ' ';
		}
		$payment_name = $method->payment_name;

		$html = $this->renderByLayout('render_pluginname', array(
		                                                        'where'                       => $where,
		                                                        'shop_mode'                   => $method->shop_mode,
		                                                        'virtuemart_paymentmethod_id' => $method->virtuemart_paymentmethod_id,
		                                                        'logo'                        => $display_logos,
		                                                        'payment_name'                => $payment_name,
		                                                        'extraInfo'                   => $extraInfo,
		                                                        'payment_description'         => $method->payment_desc,
		                                                   ));
		$html = $this->rmspace($html);
		return $html;
	}

	private function rmspace ($buffer) {
		return preg_replace('~>\s*\n\s*<~', '><', $buffer);
	}

	public function plgVmgetPaymentCurrency ($virtuemart_paymentmethod_id, &$paymentCurrencyId) {

		if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
			return null; // Another method was selected, do nothing
		}
		if (!$this->selectedThisElement($method->payment_element)) {
			return false;
		}
		$this->getPaymentCurrency($method);

		$paymentCurrencyId = $method->payment_currency;
		//! $method->payment_currency might not be correct
	}

	/**
	 * plgVmOnCheckAutomaticSelectedPayment
	 * Checks how many plugins are available. If only one, the user will not have the choice. Enter edit_xxx page
	 * The plugin must check first if it is the correct type
	 * @param VirtueMartCart cart: the cart object
	 * @return null if no plugin was found, 0 if more then one plugin was found,  virtuemart_xxx_id if only one plugin is found
	 *
	 */
	function plgVmOnCheckAutomaticSelectedPayment (VirtueMartCart $cart, array $cart_prices = array(), &$methodCounter = 0) {
		return $this->onCheckAutomaticSelected($cart, $cart_prices, $paymentCounter);
	}

	/**
	 * This method is fired when showing the order details in the frontend.
	 * It displays the method-specific data.
	 *
	 * @param integer $order_id The order ID
	 * @return mixed Null for methods that aren't active, text (HTML) otherwise
	 */
	public function plgVmOnShowOrderFEPayment ($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name) {
		$this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);

		return true;
	}


	/**
	 * This method is fired when showing when priting an Order
	 * It displays the the payment method-specific data.
	 *
	 * @param integer $_virtuemart_order_id The order ID
	 * @param integer $method_id  method used for this order
	 * @return mixed Null when for payment methods that were not selected, text (HTML) otherwise
	 */
	public function plgVmonShowOrderPrintPayment ($order_number, $method_id) {
		return $this->onShowOrderPrint($order_number, $method_id);
	}

	public function plgVmDeclarePluginParamsPaymentVM3( &$data) {
		return $this->declarePluginParams('payment', $data);
	}


	public function plgVmSetOnTablePluginParamsPayment ($name, $id, &$table) {

		return $this->setOnTablePluginParams($name, $id, $table);
	}


	public function plgVmOnPaymentNotification () {

		if (!class_exists('VirtueMartModelOrders')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
		}
		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}

		$notificationTask = vRequest::getCmd('notificationTask', '');
		// this is not our notification
		if (empty($notificationTask)) {
			return;
		}

		if ($notificationTask == 'jumpRedirect') {
			$this->jumpRedirect();
		} elseif ($notificationTask == 'handleRedirect') {
			$this->handleRedirect();
		} elseif ($notificationTask == 'handleRemoteDccForm') {
			$this->handleRemoteDccForm();
		} elseif ($notificationTask == 'handleRemoteCCForm') {
			$this->handleRemoteCCForm();
		} elseif ($notificationTask == 'handleVerify3D') {
			$this->handleVerify3D();
		} elseif ($notificationTask == 'handle3DSRequest') {
			$this->handle3DSRequest();
		}
		return true;
	}

	private function handleRedirect () {

		$realex_data = vRequest::getPost();

		$this->debugLog('plgVmOnPaymentNotification :' . var_export($realex_data, true), 'debug');
		if (!isset($realex_data['ORDER_ID'])) {
			return false;
		}
		$order_number = $realex_data['ORDER_ID'];
		if (empty($order_number)) {
			return FALSE;
		}

		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			return FALSE;
		}

		if (!($payments = $this->getDatasByOrderId($virtuemart_order_id))) {
			return FALSE;
		}

		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);

		$this->_currentMethod = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			//echo "selectedThisElement PB";
			return FALSE;
		}

		$realexInterface = $this->_loadRealexInterface();

		if (!$realexInterface->validateResponseHash($realex_data)) {
			$this->returnToVm($realex_data, false, $order['details']['BT']->virtuemart_paymentmethod_id);
			return FALSE;
		}

		$result = $realex_data['RESULT'];

		$realexInterface->setOrder($order);
		//$cart = VirtueMartCart::getCart();
		//$realexInterface->setCart($cart, false);

		$order_history = array();
		$success = ($result == $realexInterface::RESPONSE_CODE_SUCCESS);
		if ($success) {
			$status = $this->_currentMethod->status_success;

			$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
			//$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);

			$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CONFIRMED', $amountInCurrency['display'], $order_number);

			if (isset($realex_data['DCCCHOICE']) and $realex_data['DCCCHOICE'] == $realexInterface::RESPONSE_DCC_CHOICE_YES) {
				$order_history['comments'] .= "<br />";
				$order_history['comments'] .= vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_CHARGED', $this->getCardHolderAmount($realex_data['DCCMERCHANTAMOUNT']), $realex_data['DCCMERCHANTCURRENCY'], $this->getCardHolderAmount($realex_data['DCCCARDHOLDERAMOUNT']), $realex_data['DCCCARDHOLDERCURRENCY']);
			}
			$userfield = $realexInterface->cardStorageResponse($realex_data);
			$realexInterface->storeNewPayment($userfield);
			if (isset($realex_data['REALWALLET_CHOSEN']) and  $realex_data['REALWALLET_CHOSEN'] == 1) {
				if ($userfield) {
					$cardStorageResponseText = vmText::_('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_SUCCESS');
				} else {
					$cardStorageResponseText = vmText::_('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_FAILED');
				}
				$order_history['comments'] .= "<br />";
				$order_history['comments'] .= $cardStorageResponseText;
			}
		} else {
			/**
			 * Note: If a transaction is processed through your account that triggers one of the scenarios that you have set up to reject,
			 * HPP will send a post back to your response script with a Result Code of 110 and a relevant error message. The transaction will not be processed.
			 */

			$order_history['comments'] = vmText::_('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED');
			// here we check if wee need to add the message
			/*
			if ($realex_data['RESULT'] == (int)$realexInterface::RESPONSE_CODE_NOT_VALIDATED) {
				$order_history['comments'] .= "<br />";
				$order_history['comments'] .= $realex_data['MESSAGE'];
			}
			*/
			$status = $this->_currentMethod->status_canceled;
		}


		$order_history['customer_notified'] = true;
		$order_history['order_status'] = $status;


		$db_values['payment_name'] = $this->renderPluginName($this->_currentMethod, 'order');
		$db_values['virtuemart_order_id'] = $virtuemart_order_id;
		$db_values['order_number'] = $order_number;
		$db_values['virtuemart_paymentmethod_id'] = $this->_currentMethod->virtuemart_paymentmethod_id;
		$db_values['realex_hpp_api_response_result'] = $realex_data['RESULT'];
		$db_values['realex_hpp_api_request_type_response'] = $realexInterface::REQUEST_TYPE_AUTH;
		$db_values['realex_hpp_api_response_pasref'] = isset($realex_data['PASREF']) ? $realex_data['PASREF'] : "";
		$db_values['realex_hpp_api_response_authcode'] = isset($realex_data['AUTHCODE']) ? $realex_data['AUTHCODE'] : "";
		$db_values['realex_hpp_api_fullresponse'] = json_encode($realex_data);
		$db_values['realex_hpp_api_fullresponse_format'] = 'json';

		$this->storePSPluginInternalData($db_values);

		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($virtuemart_order_id, $order_history, TRUE);
		if ($result == $realexInterface::RESPONSE_CODE_SUCCESS) {
			if (isset($payments[0]->realex_hpp_api_custom)) {
				$this->emptyCart($payments[0]->realex_hpp_api_custom, $order_number);
			}
		}
		//$this->displayMessageToRealex($realexInterface, $realex_data, $success, $order_history['comments'], $payments[0]->virtuemart_paymentmethod_id);
		$this->returnToVm($realex_data, $success, $order['details']['BT']->virtuemart_paymentmethod_id);
	}


	private function initRealexInterface ($loadCDFromPost = true) {
		// TODO check if cart is empty
		$virtuemart_paymentmethod_id = vRequest::getInt('pm', false);

		$this->_currentMethod = $this->getVmPluginMethod($virtuemart_paymentmethod_id);
		if (!$this->selectedThisElement($this->_currentMethod->payment_element)) {
			vmError('Programmer error: missing the pm parameter');
			$this->redirectToCart();
			return FALSE;
		}

		$realexInterface = $this->_loadRealexInterface();
		$realexInterface->loadCustomerData($loadCDFromPost);

		$order_number = vRequest::getString('order_number', false);
		if (!($virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order_number))) {
			$this->redirectToCart();
			return FALSE;
		}
		$orderModel = VmModel::getModel('orders');
		$order = $orderModel->getOrder($virtuemart_order_id);
		$realexInterface->setOrder($order);
		$realexInterface->setPaymentCurrency();
		$realexInterface->setTotalInPaymentCurrency($order['details']['BT']->order_total);
		return $realexInterface;
	}

	private function handleRemoteDccForm () {

		$realexInterface = $this->initRealexInterface(false);
		$cart = VirtueMartCart::getCart();
		$realexInterface->setCart($cart, false);
		if (!($payments = $this->getDatasByOrderId($realexInterface->order['details']['BT']->virtuemart_order_id))) {
			$this->redirectToCart();
			return FALSE;
		}
		$dcc_payment = $realexInterface->getTransactionData($payments, array(
		                                                                    $realexInterface::REQUEST_TYPE_DCCRATE,
		                                                                    $realexInterface::REQUEST_TYPE_REALVAULT_DCCRATE
		                                                               ));
		if (!$dcc_payment) {
			$this->redirectToCart();
			return FALSE;
		}

		$this->handleRemoteCCForm($dcc_payment->realex_hpp_api_fullresponse);
		/*
				$realexInterface->confirmedOrderDccRequest($dcc_payment->realex_hpp_api_fullresponse);
				$this->updateOrderStatus($realexInterface->order);

				$this->customerData->clear();

				$cart->emptyCart();
				$submit_url = JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=' . $this->_currentMethod->virtuemart_paymentmethod_id . '&on=' . $realexInterface->order['details']['BT']->order_number . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');
				$app = JFactory::getApplication();
				$app->redirect(JRoute::_($submit_url));
		*/
	}

	private function handleRemoteCCForm ($response_dcc = NULL, $loadFromPost = true) {

		$realexInterface = $this->initRealexInterface($loadFromPost);
		$realvaultData = false;

		if (! $this->validateCCForm($realexInterface, $realvaultData)) {
			return;
		}

		if ($this->_currentMethod->dcc AND empty($response_dcc)) {
			$response = $realexInterface->requestDccRate($realvaultData);
			$realexInterface->manageResponseDccRate($response);
			//$this->_storeRealexInternalData($response, $this->_currentMethod->virtuemart_paymentmethod_id, $realexInterface->order['details']['BT']->virtuemart_order_id, $realexInterface->order['details']['BT']->order_number, $realexInterface->request_type);
			$xml_response = simplexml_load_string($response);
			/*
			* 105: Card not supported by eDCC
			* 00: Card supported by eDCC
			*/
			$success = $realexInterface->isResponseSuccess($xml_response);
			if ($success) {
				$remoteDCCFormParams =$realexInterface->getRemoteDCCFormParams($xml_response);
				$html = $this->renderByLayout('remote_cc_form', $remoteDCCFormParams);
				echo $html;
				return;
			} else {
				//vmError($xml_response->message);
				//$this->redirectToCart();
				//$response = $realexInterface->requestAuth();
				//$realexInterface->manageResponseRequestAuth($response);
			}

		}
		if ($realvaultData) {
			$pmt_type = $realvaultData->realex_hpp_api_saved_pmt_type;
		} else {
			$pmt_type = NULL;
		}
		if ($this->_currentMethod->threedsecure and $realexInterface->isCC3DSVerifyEnrolled($pmt_type)) {
			$response3DSVerifyEnrolled = $realexInterface->request3DSVerifyEnrolled($realvaultData);
			$realexInterface->manageResponse3DSVerifyEnrolled($response3DSVerifyEnrolled);
			$eci = $realexInterface->getEciFrom3DSVerifyEnrolled($response3DSVerifyEnrolled);
			$xml_response3DSVerifyEnrolled = simplexml_load_string($response3DSVerifyEnrolled);
			$result = (string)$xml_response3DSVerifyEnrolled->result;
			//   503 - no entry for MERCHANT in RealMPI merchant_details table
			if ($eci === false and $result != 503) {
				//$this->_storeRealexInternalData($response, $this->_currentMethod->virtuemart_paymentmethod_id, $realexInterface->order['details']['BT']->virtuemart_order_id, $realexInterface->order['details']['BT']->order_number, $realexInterface->request_type);
				if ($result == $realexInterface::RESPONSE_CODE_SUCCESS) {
					$realexInterface->redirect3DSRequest($response3DSVerifyEnrolled);
					return;
				} else {
					// we should be here if Result=110 + enrolled =U // result=5xx and enrolled not available // result =220 (ENROLLED_RESULT_FATAL_ERROR)
					$this->redirectToCart();
					return FALSE;
				}
			} else {
				$xml_response3DSVerifyEnrolled = simplexml_load_string($response3DSVerifyEnrolled);
				$xml_response3DSVerifyEnrolled->addChild('eci', $eci);
				$xml_response_dcc = simplexml_load_string($response_dcc);
				//$response = $realexInterface->requestAuth($response_dcc, $xml_response3DSVerifyEnrolled);
				if ($realvaultData) {
					$response = $realexInterface->requestReceiptIn($realvaultData, $xml_response_dcc, $xml_response3DSVerifyEnrolled);
				} else {
					$response = $realexInterface->requestAuth($xml_response_dcc, $xml_response3DSVerifyEnrolled);
				}
				$realexInterface->manageResponseRequestAuth($response);

			}

		} else {
			//$userfield = $realexInterface->handleCardStorage($saved_cc_selected);
			// TODO eci missing?
			$xml_response_dcc = NULL;
			if ($response_dcc) {
				$xml_response_dcc = simplexml_load_string($response_dcc);
			}
			if ($realvaultData) {
				$response = $realexInterface->requestReceiptIn($realvaultData, $xml_response_dcc);
			} else {
				$response = $realexInterface->requestAuth($xml_response_dcc);
			}
			$realexInterface->manageResponseRequestAuth($response);
		}
		//$payments = $this->getDatasByOrderId($realexInterface->order['details']['BT']->virtuemart_order_id);
		$success = $this->updateOrderStatus($realexInterface->order, false);
		if ($success) {
			$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
			$userfield = $realexInterface->handleCardStorage($saved_cc_selected);
			$realexInterface->storeNewPayment($userfield);
			$this->customerData->clear();
			$cart = VirtueMartCart::getCart();
			$cart->emptyCart();
			$submit_url = JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=' . $this->_currentMethod->virtuemart_paymentmethod_id . '&on=' . $realexInterface->order['details']['BT']->order_number . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');
			$app = JFactory::getApplication();
			$app->redirect(JRoute::_($submit_url));
		} else {
			$this->redirectToCart();
		}


	}


	private function validateCCForm($realexInterface, &$realvaultData) {
		$realvaultData = false;
		$return=false;
		if (!JFactory::getUser()->guest AND $this->_currentMethod->realvault) {
			$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
			if ($saved_cc_selected > 0) {
				$realvaultData = $realexInterface->getStoredCCsData($saved_cc_selected);
				if (!$cvv_realvault = $realexInterface->validateCvv()) {
					$remoteCCFormParams =$realexInterface->getRemoteCCFormParams(NULL, true);
					$html = $this->renderByLayout('remote_cc_form', $remoteCCFormParams);
					echo $html;
					return false;
				}
				$realvaultData->cc_cvv_realvault = $this->customerData->getVar('cc_cvv_realvault');
				$this->customerData->saveCustomerRealVaultData((array)$realvaultData);
				return true;
			} else {

				if ($this->_currentMethod->integration == 'redirect') {
					$html = $realexInterface->sendPostRequest();
					echo $html;
					$cart = VirtueMartCart::getCart();
					$cart->_confirmDone = FALSE;
					$cart->_dataValidated = FALSE;
					$cart->setCartIntoSession();
					return false;
				} else {
					if (!$realexInterface->validateRemoteCCForm()) {
						$remoteCCFormParams =$realexInterface->getRemoteCCFormParams();
						$html = $this->renderByLayout('remote_cc_form', $remoteCCFormParams);
						echo $html;
						return false;
					} else {
						return true;
					}
				}
			}
		} else {
			if (!$realexInterface->validateRemoteCCForm()) {
				$remoteCCFormParams =$realexInterface->getRemoteCCFormParams();
				$html = $this->renderByLayout('remote_cc_form', $remoteCCFormParams);
				echo $html;
				return false;
			} else {
				return true;
			}
		}
		return $return;
	}
	/**
	 * @return bool
	 */
	private function handleVerify3D () {
		$realexInterface = $this->initRealexInterface();
		$realvaultData=false;
		if (! $this->validateCCForm($realexInterface, $realvaultData)) {
			return;
		}
		$cart = VirtueMartCart::getCart();
		$realexInterface->setCart($cart, false);

		$response3DSVerifyEnrolled = $realexInterface->request3DSVerifyEnrolled($realvaultData);
		$eci = $realexInterface->manageResponse3DSVerifyEnrolled($response3DSVerifyEnrolled);
		$xml_response3DSVerifyEnrolled = simplexml_load_string($response3DSVerifyEnrolled);
		$result = (string)$xml_response3DSVerifyEnrolled->result;
		if (!$eci  and   $result != '503') {
			$realexInterface->redirect3dsRequest($response3DSVerifyEnrolled);
			return;
		}
		$saved_cc_selected=NULL;
		if ($eci !== false or  $result == '503') {
			$realexInterface->handleCardStorage($saved_cc_selected);
			$xml_response3DSVerifyEnrolled = simplexml_load_string($response3DSVerifyEnrolled);
			$response = $realexInterface->requestAuth(NULL, $xml_response3DSVerifyEnrolled);
			$realexInterface->manageResponseRequestAuth($response);
			$xml_response = simplexml_load_string($response);
			$success = $realexInterface->isResponseSuccess($xml_response);
		} else {
			$success = false;
		}

		$order_history = array();

		if ($success) {
			$status = $this->_currentMethod->status_success;
			$amountValue = vmPSPlugin::getAmountInCurrency($realexInterface->order['details']['BT']->order_total, $realexInterface->order['details']['BT']->order_currency);
			$currencyDisplay = CurrencyDisplay::getInstance($realexInterface->cart->pricesCurrency);

			$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CONFIRMED', $amountValue['display'], $realexInterface->order['details']['BT']->order_number);

		} else {
			$order_history['comments'] = vmText::_('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED');
			$status = $this->_currentMethod->status_canceled;
		}

		$order_history['customer_notified'] = true;
		$order_history['order_status'] = $status;

		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($realexInterface->order['details']['BT']->virtuemart_order_id, $order_history, TRUE);

		//$payments = $this->getDatasByOrderId($realexInterface->order['details']['BT']->virtuemart_order_id);

		//$html = $realexInterface->getResponseHTML($payments);
		$this->customerData->clear();
		$cart->emptyCart();
		$submit_url = JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=' . $this->_currentMethod->virtuemart_paymentmethod_id . '&on=' . $realexInterface->order['details']['BT']->order_number . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');
		$app = JFactory::getApplication();
		$app->redirect(JRoute::_($submit_url));

		return true;
	}

	private function handle3DSRequest () {
		$realexInterface = $this->initRealexInterface(false);
		$cart = VirtueMartCart::getCart();
		$realexInterface->setCart($cart, false);
		$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
		$realvault = false;
		if ($saved_cc_selected > 0) {
			$realvault = $realexInterface->getStoredCCsData($saved_cc_selected);
			$realvault->cc_cvv_realvault = $this->customerData->getVar('cc_cvv_realvault');
			$this->customerData->saveCustomerRealVaultData((array)$realvault);

		}
		if (!($payments = $this->getDatasByOrderId($realexInterface->order['details']['BT']->virtuemart_order_id))) {
			$this->redirectToCart();
			return FALSE;
		}
		$dcc_payment = $realexInterface->getTransactionData($payments, array(
		                                                                    $realexInterface::REQUEST_TYPE_DCCRATE,
		                                                                    $realexInterface::REQUEST_TYPE_REALVAULT_DCCRATE
		                                                               ));
		if ($dcc_payment) {
			$xml_dcc_payment = simplexml_load_string($dcc_payment->realex_hpp_api_fullresponse);
		} else {
			$xml_dcc_payment = NULL;
		}

		$response3DSVerifysig = $realexInterface->request3DSVerifysig($realvault);
		$realexInterface->manageResponse3DSVerifysig($response3DSVerifysig);

		$eci = $realexInterface->getEciFrom3DSVerifysig($response3DSVerifysig, $this->_currentMethod->require_liability);
		$xml_response3DSVerifysig = simplexml_load_string($response3DSVerifysig);
		if ($eci !== false) {
			$xml_response3DSVerifysig->threedsecure->eci = $eci;

			if ($realvault) {
				$response = $realexInterface->requestReceiptIn($realvault, $xml_dcc_payment, $xml_response3DSVerifysig);
			} else {
				$response = $realexInterface->requestAuth($xml_dcc_payment, $xml_response3DSVerifysig);
			}

			$realexInterface->manageResponseRequestAuth($response);
			$xml_response = simplexml_load_string($response);
			$success = $realexInterface->isResponseSuccess($xml_response);
		} else {
			$success = false;
		}


		$order_history = array();
		$redirectToCart = false;
		if ($success) {
			$userfield = $realexInterface->handleCardStorage($saved_cc_selected);
			$realexInterface->storeNewPayment($userfield);
			$status = $this->_currentMethod->status_success;
			$amountValue = vmPSPlugin::getAmountInCurrency($realexInterface->order['details']['BT']->order_total, $realexInterface->order['details']['BT']->order_currency);
			$currencyDisplay = CurrencyDisplay::getInstance($realexInterface->cart->pricesCurrency);

			$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CONFIRMED', $amountValue['display'], $realexInterface->order['details']['BT']->order_number);
			if (isset($xml_response->dccinfo) AND isset($xml_response->dccinfo->cardholderrate)) {
				$order_history['comments'] .= "<br />";
				if ($xml_response->dccinfo->cardholderrate != 1.0) {
					$order_history['comments'] .= vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_CHARGED', $this->getCardHolderAmount($xml_response->dccinfo->merchantamount), $xml_response->dccinfo->merchantcurrency, $this->getCardHolderAmount($xml_response->dccinfo->cardholderamount), $xml_response->dccinfo->cardholdercurrency);
				} else {
					$order_history['comments'] .= vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_MERCHANT_CURRENCY', $this->getCardHolderAmount($xml_response->dccinfo->merchantamount), $xml_response->dccinfo->merchantcurrency);
				}
				$order_history['comments'] .= "<br />";
			} else {

			}
		} else {
			$msgToShopper='';
			$status = $this->_currentMethod->status_canceled;
			if ($realexInterface->isResponseDeclined($xml_response3DSVerifysig)) {
				$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_DECLINED', $realexInterface->order['details']['BT']->order_number);
				$msgToShopper=$xml_response3DSVerifysig->message;
			} elseif ($realexInterface->isResponseWrongPhrase($xml_response3DSVerifysig)) {
				$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED', $realexInterface->order['details']['BT']->order_number);
				$msgToShopper=$xml_response3DSVerifysig->message;
			} elseif ($realexInterface->isResponseAlreadyProcessed($xml_response3DSVerifysig)) {
				$order_history['comments'] = $xml_response3DSVerifysig->message;
				$msgToShopper=$xml_response3DSVerifysig->message;
				// log this response, but do not change the order status
				$status = $realexInterface->order['details']['BT']->order_status;

				/* } elseif ($xml_response and $realexInterface->isResponseInvalidPaymentDetails($xml_response)) {

					$order_history['comments'] =$xml_response->message;
					if ($realvault) {
						$accountURL=JRoute::_('index.php?option=com_virtuemart&view=user&layout=edit');
						$msgToShopper=vmText::sprintf('VMPAYMENT_REALEX_HPP_API_INVALID_PAYMENT_DETAILS_REALVAULT',$xml_response->message, $accountURL);
					} else {
						$msgToShopper=vmText::sprintf('VMPAYMENT_REALEX_HPP_API_INVALID_PAYMENT_DETAILS',$xml_response->message);
					}
	*/

			} else {
				$order_history['comments'] = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED', $realexInterface->order['details']['BT']->order_number);
			}
			$redirectToCart = true;
		}

		$order_history['customer_notified'] = true;
		$order_history['order_status'] = $status;
		//	$this->updateOrderStatus($realexInterface->order, $redirectToCart);
		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($realexInterface->order['details']['BT']->virtuemart_order_id, $order_history, false);

		/*
				$payments = $this->getDatasByOrderId($realexInterface->order['details']['BT']->virtuemart_order_id);

				$html = $realexInterface->getResponseHTML($payments);
				$this->customerData->clear();
				$cart = VirtueMartCart::getCart();
				$cart->emptyCart();
				vRequest::setVar('display_title', false);
				vRequest::setVar('html', $html);
				echo $html;
		*/
		//$html = $realexInterface->getResponseHTML($payments);
		if ($redirectToCart) {
			$this->redirectToCart($msgToShopper);
		} else {
			$this->customerData->clear();
			$cart = VirtueMartCart::getCart();
			$cart->emptyCart();
			$submit_url = JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&pm=' . $this->_currentMethod->virtuemart_paymentmethod_id . '&on=' . $realexInterface->order['details']['BT']->order_number . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');
			$app = JFactory::getApplication();
			$app->redirect(JRoute::_($submit_url));
		}


		return true;
	}




	/**
	 * This message allows to redirect from Realex payment form to VM
	 * @param $realex_data
	 * @param $success
	 * @param $virtuemart_paymentmethod_id
	 */
	private function returnToVm ($realex_data, $success, $virtuemart_paymentmethod_id) {

		$html='';
		// add spin image
		$html .= '<form action="' . JURI::root(false) . '" method="post" id="vmPaymentForm" name="vm_realex_form" accept-charset="UTF-8">';
		$html .= '<input type="hidden" name="charset" value="utf-8">';

		$html .= '<input type="hidden" name="option" value="com_virtuemart" />';
		$html .= '<input type="hidden" name="view" value="pluginresponse" />';
		if ($success) {
			$html .= '<input type="hidden" name="task" value="pluginresponsereceived" />';
		} else {
			$html .= '<input type="hidden" name="task" value="pluginUserPaymentCancel" />';
		}

		$html .= '<input type="hidden" name="on" value="' . $realex_data['ORDER_ID'] . '" />';
		$html .= '<input type="hidden" name="pm" value="' . $virtuemart_paymentmethod_id . '" />';
		$html .= '<input type="hidden" name="Itemid" value="' . vRequest::getInt('Itemid') . '" />';
		$html .= '<input type="hidden" name="lang" value="' . vRequest::getCmd('lang', '') . '" />';

		$html .= '<input type="submit"  value="' . vmText::_('VMPAYMENT_REALEX_HPP_API_REDIRECT_MESSAGE') . '" />
					<script type="text/javascript">';
		$html .= '		document.vm_realex_form.submit();';
		$html .= '	</script>';
		$html .= '</form>';
		echo $html;


	}


	function getCardHolderAmount ($dcccardholderamount) {
		return sprintf("%01.2f", $dcccardholderamount * 0.01);
	}

	/*******************/
	/* Credit Card API */
	/*******************/
	public function _getCVVImages ($cvv_images) {
		$img = '';
		if ($cvv_images) {
			$img = $this->displayLogos($cvv_images);
			$img = str_replace('"', "'", $img);
		}
		return $img;
	}


	public function plgVmOnRealexDeletedStoredCard ($element, $storedCC, &$success) {
		if (!$this->selectedThisElement($element)) {
			return FALSE;
		}
		$vendorId = 1;
		if ($this->getPluginMethods($vendorId) === 0) {
			return false;
		}


		foreach ($this->methods as $method) {
			if ($method->merchant_id == $storedCC['merchant_id']) {
				// the crypted fields are decrypted with that function
				if (!($this->_currentMethod = $this->getVmPluginMethod($method->virtuemart_paymentmethod_id))) {
					return FALSE; // this should not happen
				}
				break;
			}
		}

		$realexInterface = $this->_loadRealexInterface();
		if (!$realexInterface) {
			return false;
		}
		$success = $realexInterface->deleteStoredCard($storedCC);
		return $success;
	}

	public function plgVmOnRealexUpdateStoredCard ($element, $storedCC, &$success) {
		if (!$this->selectedThisElement($element)) {
			return FALSE;
		}
		$vendorId = 1;
		if ($this->getPluginMethods($vendorId) === 0) {
			return false;
		}


		foreach ($this->methods as $method) {
			if ($method->merchant_id == $storedCC['merchant_id']) {
				// the crypted fields are decrypted with that function
				if (!($this->_currentMethod = $this->getVmPluginMethod($method->virtuemart_paymentmethod_id))) {
					return FALSE; // this should not happen
				}
				break;
			}
		}
		if (empty($this->_currentMethod)) {
			Vmerror('No payment has been found with ' . $storedCC['merchant_id']);
			return FALSE;
		}
		//vmdebug('plgVmOnRealexUpdateStoredCard',$this->_currentMethod );
		$realexInterface = $this->_loadRealexInterface();
		if (!$realexInterface) {
			return false;
		}
		$success = $realexInterface->updateStoredCard($storedCC);
		return $success;
	}

	/**
	 * @return string
	 */
	function getPayerRefTableName () {
		return $this->_tablename . '_payerref';
	}

	/**
	 * @param $response
	 * @param $virtuemart_paymentmethod_id
	 * @param $virtuemart_order_id
	 * @param $order_number
	 * @param $request_type
	 * @return mixed
	 */
	function _storeRealexInternalData ($response, $virtuemart_paymentmethod_id, $virtuemart_order_id, $order_number, $request_type) {
		$xml_response = simplexml_load_string($response);
		//$db_values['payment_name'] = $this->renderPluginName($this->_currentMethod, 'order');
		$db_values['virtuemart_order_id'] = $virtuemart_order_id;
		$db_values['order_number'] = $order_number;
		$db_values['virtuemart_paymentmethod_id'] = $virtuemart_paymentmethod_id;
		$db_values['realex_hpp_api_response_result'] = (string)$xml_response->result;
		if (isset($xml_response->pasref)) {
			$db_values['realex_hpp_api_response_pasref'] = (string)$xml_response->pasref;
		}
		if (isset($xml_response->authcode)) {
			$db_values['realex_hpp_api_response_authcode'] = (string)$xml_response->authcode;
		}
		$db_values['realex_hpp_api_request_type_response'] = $request_type;
		$db_values['realex_hpp_api_fullresponse_format'] = 'xml';
		$db_values['realex_hpp_api_fullresponse'] = $response;


		$this->storePSPluginInternalData($db_values);
		return $db_values;
	}

	private function jumpRedirect () {
		// url sent in get
		$url = vRequest::getVar('gateway_url');
		unset($_POST['gateway_url']);
		?>
		<html>
		<head>
			<title>Transferring...</title>
			<meta http-equiv="Content-Type"
			      content="text/html; charset=iso-8859-1">
		</head>

		<body bgcolor="#FFFFFF" text="#000000">

		<form
			name="form1"
			action="<?php echo $url; ?>"
			method="POST">

			<?php
			// get the posted vars
			$field_array = array_keys($_POST);

			//loop posted fields
			for ($i = 0; $i < count($field_array); $i++) {
				$actual_var = $field_array[$i];
				$actual_val = stripslashes(vRequest::getVar($actual_var));

				//hidden form field
				echo("<input type=\"hidden\" name=\"");
				echo($actual_var . "\" value=\"");
				echo(trim($actual_val) . "\" />\n");
			}

			?>
		</form>

		<script language="javascript">
			var f = document.forms;
			f = f[0];
			f.submit();
		</script>

		</body>
		</html>
	<?php
	}

	/**
 * createToken to avoid double form submit
 * @return string
 */
	function createToken () {
		static $chars = '0123456789abcdef';
		$max = strlen($chars) - 1;
		$token = '';
		$name = session_name();
		$length = 32;
		for ($i = 0; $i < $length; ++$i) {
			$token .= $chars[(mt_rand(0, $max))];
		}

		return md5($token . $name);
	}

	function saveTokenInSession ($token) {
		$session = JFactory::getSession();
		$session->set('RealexToken', $token, 'vm');
	}

	function checkToken ($token) {
		$session = JFactory::getSession();
		$sessionToken = $session->get('RealexToken', 0, 'vm');
		if ($token == $sessionToken) {
			return true;
		}
		return false;
	}


	function clearToken() {
		$session = JFactory::getSession();
		$session->clear('RealexToken', 'vm');
	}


} // class

// No closing tag
