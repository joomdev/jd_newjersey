<?php
/**
 *
 * KlarnaCheckout payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id:$
 * @package VirtueMart
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */


defined('_JEXEC') or die('Restricted access');

class KlarnaCheckoutHelperKCO_php extends KlarnaCheckoutHelperKlarnaCheckout {
	var $_currentMethod;
	var $mode;
	var $ssl;
	function __construct($method,$country_code_3, $currency_code_3) {
		parent::__construct($method,$country_code_3, $currency_code_3) ;
		if (!class_exists('KlarnaHandler')) {
			require(JPATH_ROOT . DS . 'plugins' . DS . 'vmpayment'  . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
		}
		if ($this->_currentMethod->server == 'beta') {
			$this->mode = Klarna::BETA;
		} else {
			$this->mode = Klarna::LIVE;
		}
		$this->ssl = KlarnaHandler::getKlarnaSSL($this->mode);

	}

	function getSnippet($klarna_checkout_order) {
		return $klarna_checkout_order['gui']['snippet'];
	}

	function getKlarnaUrl() {

		if ($this->_currentMethod->server == 'beta') {
			return Klarna_Checkout_Connector::BASE_TEST_URL;
		} else {
			return Klarna_Checkout_Connector::BASE_URL;
		}
	}

	function getKlarnaConnector() {
		return Klarna_Checkout_Connector::create($this->_currentMethod->sharedsecret, $this->getKlarnaUrl());
	}

	function checkoutOrder($klarna_checkout_connector, $klarna_checkout_uri) {
		return new Klarna_Checkout_Order($klarna_checkout_connector, $klarna_checkout_uri);

	}


	function getMerchantData(&$klarnaOrderData, $cart) {
		$klarnaOrderData['merchant']['id'] = $this->_currentMethod->merchantid;
		$klarnaOrderData['merchant']['terms_uri'] = $this->getTermsURI($cart->vendorId);
		$klarnaOrderData['merchant']['checkout_uri'] = JURI::root() . 'index.php?option=com_virtuemart&view=cart' . '&Itemid=' . JRequest::getInt('Itemid');
		$klarnaOrderData['merchant']['confirmation_uri'] = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=pluginresponsereceived&pm=' . $this->_currentMethod->virtuemart_paymentmethod_id . '&Itemid=' . JRequest::getInt('Itemid') . '&klarna_order={checkout.order.id}';
		// You can not receive push notification on non publicly available uri
		$klarnaOrderData['merchant']['push_uri'] = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=pluginnotification&tmpl=component&nt=kco-push-uri&pm=' . $this->_currentMethod->virtuemart_paymentmethod_id . '&klarna_order={checkout.order.id}';
		// attention if used must be https
		//$create['merchant']['validation_uri'] = JURI::root() .  'index.php?option=com_virtuemart&view=vmplg&task=pluginnotification&tmpl=component&nt=kco-validation&pm=' . $virtuemart_paymentmethod_id . '&klarna_order={checkout.order.uri}';

	}

	function getCartItems($cart, &$klarnaOrderData) {

		//vmdebug('getProductItems', $cart->pricesUnformatted);
		//self::includeKlarnaFiles();
		$i = 0;
		if (!class_exists('CurrencyDisplay'))
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');

		foreach ($cart->products as $pkey => $product) {

			$items[$i]['reference'] = !empty($product->sku) ? $product->sku : $product->virtuemart_product_id;
			$items[$i]['name'] = substr(strip_tags($product->product_name), 0, 127);
			$items[$i]['quantity'] = (int)$product->quantity;
			$price = !empty($product->prices['basePriceWithTax']) ? $product->prices['basePriceWithTax'] : $product->prices['basePriceVariant'];

			$itemInPaymentCurrency = vmPSPlugin::getAmountInCurrency($price, $this->_currentMethod->payment_currency);
			$items[$i]['unit_price'] = round($itemInPaymentCurrency['value'] * 100, 0);
			//$items[$i]['discount_rate'] = $discountRate;
			// Bug indoc: discount is not supported
			//$items[$i]['discount'] = abs($cart->cartPrices[$pkey]['discountAmount']*100);
			$tax_rate = round($this->getVatTaxProduct($cart->cartPrices[$pkey]['VatTax']));
			$items[$i]['tax_rate'] = $tax_rate * 100;

			$i++;
			// ADD A DISCOUNT AS A NEGATIVE VALUE FOR THAT PRODUCT
			if ($cart->cartPrices[$pkey]['discountAmount'] != 0.0) {
				$items[$i]['reference'] = $items[$i - 1]['reference'];
				$items[$i]['name'] = $items[$i - 1]['name'] . ' (' . vmText::_('VMPAYMENT_KLARNACHECKOUT_PRODUCTDISCOUNT') . ')';
				$items[$i]['quantity'] = (int)$product->quantity;
				$discount_tax_percent = 0.0;
				$discountInPaymentCurrency = vmPSPlugin::getAmountInCurrency(abs($cart->cartPrices[$pkey]['discountAmount']), $this->_currentMethod->payment_currency);
				$discountAmount = -abs(round($discountInPaymentCurrency['value'] * 100, 0));
				if ($cart->cartPrices[$pkey]['discountAmount'] > 0.0) {
					$items[$i]['tax_rate'] = $items[$i - 1]['tax_rate'];
				} else {
					$items[$i]['tax_rate'] = 0.0;
					$tax_rate = 0.0;
				}
				$items[$i]['unit_price'] = round($discountAmount * (1 + ($tax_rate * 0.01)), 0);


				$i++;
			}
		}
		if ($cart->cartPrices['salesPriceCoupon']) {
			$items[$i]['reference'] = 'COUPON';
			$items[$i]['name'] = 'Coupon discount';
			$items[$i]['quantity'] = 1;
			$couponInPaymentCurrency = vmPSPlugin::getAmountInCurrency($cart->cartPrices['salesPriceCoupon'], $this->_currentMethod->payment_currency);
			$items[$i]['unit_price'] = round($couponInPaymentCurrency['value'] * 100, 0);
			$items[$i]['tax_rate'] = 0;

			$i++;
		}
		if ($cart->cartPrices['salesPriceShipment']) {
			$items[$i]['reference'] = 'SHIPPING';
			$items[$i]['name'] = 'Shipping Fee';
			$items[$i]['quantity'] = 1;
			$shipmentInPaymentCurrency = vmPSPlugin::getAmountInCurrency($cart->cartPrices['salesPriceShipment'], $this->_currentMethod->payment_currency);
			$items[$i]['unit_price'] = round($shipmentInPaymentCurrency['value'] * 100, 0);
			$items[$i]['tax_rate'] = $this->getTaxShipment($cart->cartPrices['shipment_calc_id']);

		}
		$currency = CurrencyDisplay::getInstance($cart->paymentCurrency);
		$klarnaOrderData['cart']['items'] = $items;
		return;

	}

	function getCheckoutOrderId($klarna_checkout_order) {
		return $klarna_checkout_order['id'];
	}

	function isKlarnaOrderStatusSuccess($klarna_checkout_order) {
		return ($klarna_checkout_order['status'] == 'checkout_complete');
	}

	function getStoreInternalData($klarna_checkout_order, $dbValues) {
		$dbValues['payment_order_total'] = $klarna_checkout_order['cart']['total_price_including_tax'] / 100;
		$dbValues['payment_currency'] = ShopFunctions::getCurrencyIDByName($klarna_checkout_order['purchase_currency']);;

		$dbValues['klarna_id'] = $klarna_checkout_order['id'];
		$dbValues['klarna_status'] = $klarna_checkout_order['status'];
		$dbValues['klarna_reservation'] = $klarna_checkout_order['reservation'];
		$dbValues['klarna_reference'] = $klarna_checkout_order['reference'];
		$dbValues['klarna_started_at'] = $klarna_checkout_order['started_at'];
		$dbValues['klarna_completed_at'] = $klarna_checkout_order['completed_at'];
		$dbValues['klarna_expires_at'] = $klarna_checkout_order['expires_at'];
		$dbValues['format'] = 'none';
	}

	function getStoredData($payment) {
		if ($payment->format == 'json') {
			$data = (object)json_decode($payment->data, true);
		} elseif ($payment->format == 'none') {
			return $payment;
		} else {
			$data = unserialize($payment->data);
		}
		return $data;
	}

	/**
	* Order management
	 */
	public function getUpdateOrderPaymentAction($new_order_status, $old_order_status, $payments) {

		$lastPayment = $payments[(count($payments)) - 1];
		$klarna_status = $lastPayment->klarna_status;
		$actions = array('activate', 'cancelReservation', 'changeReservation', 'creditInvoice', 'Refund');
		$klarnaCheckoutData=NULL;
		foreach ($actions as $action) {
			$status = 'status_' . $action;

			if ($this->_currentMethod->$status == $new_order_status and $this->authorizedAction($klarna_status, $new_order_status, $old_order_status, $action, $this->_currentMethod)) {
				return $action;
				break;
			}
		}

		return FALSE;
	}




	function authorizedAction($klarna_status, $new_order_status, $old_order_status, $action) {
		if ($old_order_status == $this->_currentMethod->status_checkout_complete) {
			$authorize = array(
				'cancelReservation' => $this->_currentMethod->status_cancelReservation,
				'changeReservation' => $this->_currentMethod->status_changeReservation,
				'activate' => $this->_currentMethod->status_activate,
			);
			if (in_array($new_order_status, $authorize)) {
				return TRUE;
			}
		} elseif ($old_order_status == $this->_currentMethod->status_activate) {
			$authorize = array(
				'creditInvoice' => $this->_currentMethod->status_creditInvoice,
				'returnAmount' => $this->_currentMethod->status_returnAmount,
				'creditPart' => $this->_currentMethod->status_creditPart,
			);
			if (in_array($new_order_status, $authorize)) {
				return TRUE;
			}
		}

		return FALSE;
	}




	/**
	 * The following variables are no longer order specific and should be fixed:
	 * pclass, -1 for all Klarna Checkout orders
	 * pno, null for all Klarna Checkout orders
	 * @param $order
	 * @param $method
	 * @param $payments
	 * @return bool
	 */
	function activate($order, $payments) {
		$rno = $this->getReservationNumber($payments);


		if (!$rno) {
			return; // error already sent
		}
		// TO DO ASK KLARNA ABOUT KLARNA MODE
		//$mode = KlarnaHandler::getKlarnaMode($method,  $this->getPurchaseCountry($method));
		//$ssl = KlarnaHandler::getKlarnaSSL($mode);
		// Instantiate klarna object.



		$klarna = new Klarna_virtuemart();
		$klarna->config($this->_currentMethod->merchantid, $this->_currentMethod->sharedsecret, $this->country_code_3, NULL, $this->currency_code_3, $this->mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $this->ssl);
		$modelOrder = VmModel::getModel('orders');

		try {
			$return = $klarna->activate($rno);
			if ($return[0] == 'ok') {
				VmInfo(vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ACTIVATE_RESERVATION', $rno));

				$history = array();
				$history['customer_notified'] = 0;
				$history['order_status'] = $this->_currentMethod->status_activate;
				$history['comments'] = vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_PAYMENT_STATUS_ACTIVATE', $rno); // $order['details']['BT']->order_number);
				$modelOrder->updateStatusForOneOrder($order->virtuemart_order_id, $history, false);

				$dbValues['order_number'] = $order->order_number;
				$dbValues['virtuemart_order_id'] = $order->virtuemart_order_id;
				$dbValues['payment_name'] = '';
				$dbValues['virtuemart_paymentmethod_id'] = $payments[0]->virtuemart_paymentmethod_id;
				$dbValues['action'] = 'activate';
				$dbValues['klarna_status'] = 'activate';

			} else {
				$this->KlarnacheckoutError('activate returned KO', vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));
			}

		} catch (Exception $e) {
			$this->KlarnacheckoutError($e->getMessage(), vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));

			return FALSE;
		}


		return $return;
	}

	/**
	 *
	 */
	function cancelReservation($order, $payments) {
		$rno = $this->getReservationNumber($payments);
		if (!$rno) {
			return; // error already sent
		}

		$klarna = new Klarna_virtuemart();
		$klarna->config($this->_currentMethod->merchantid, $this->_currentMethod->sharedsecret, $this->country_code_3, NULL, $this->currency_code_3, $this->mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $this->ssl);
		$modelOrder = VmModel::getModel('orders');

		try {
			$result = $klarna->cancelReservation($rno);
			$info = vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_RESERVATION_CANCELED', $rno);
			VmInfo($info);
			$history = array();
			$history['customer_notified'] = 1;
			//$history['order_status'] = $this->_currentMethod->checkout_complete;
			$history['comments'] = $info; // $order['details']['BT']->order_number);
			$modelOrder->updateStatusForOneOrder($order->virtuemart_order_id, $history, TRUE);



		} catch (Exception $e) {
			$error = $e->getMessage();
			$this->KlarnacheckoutError($e->getMessage(), vmText::sprintf('VMPAYMENT_KLARNACHECKOUT_ERROR_OCCURRED', $this->_currentMethod->payment_name));

			return FALSE;
		}


		//$dbValues['data'] = $vm_invoice_name;

		return $result;
	}

	function changeReservation() {

	}

	function creditInvoice() {

	}

	function creditPart() {

	}

	function refund() {

	}



	function getReservationNumber($payments) {
		foreach ($payments as $payment) {
			if ($payment->klarna_status == "checkout_complete") {
				$klarna_order = $this->getStoredData($payment);
				// BC
				if (isset($klarna_order->reservation)) {
					return $klarna_order->reservation;
				} else {
					return $klarna_order->klarna_reservation;
				}
			}
		}
		$this->KlarnacheckoutError('VMPAYMENT_KLARNACHECKOUT_ERROR_NO_RNO', 'VMPAYMENT_KLARNACHECKOUT_ERROR_NO_RNO');
		return null;
	}


	/**
	 * @param $klarna_invoice_pdf
	 * @param $vm_invoice_name
	 * @return bool
	 */
	function getInvoice ($invoice_number, &$vm_invoice_name) {


		//$klarna_invoice = explode ('/', $klarna_invoice_pdf);
		if ($this->_currentMethod->server == 'live') {
			$klarna_invoice_name = "https://online.klarna.com/packslips/" . $invoice_number . '.pdf';
		} else {
			$klarna_invoice_name = "https://online.testdrive.klarna.com/packslips/" . $invoice_number . '.pdf';
		}

		$vm_invoice_name = 'klarna_' . $invoice_number . '.pdf';

		return $klarna_invoice_name;
	}




}
