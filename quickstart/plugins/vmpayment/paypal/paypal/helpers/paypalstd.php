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

//https://cms.paypal.com/mx/cgi-bin/?cmd=_render-content&content_ID=developer/e_howto_html_Appx_websitestandard_htmlvariables

class PaypalHelperPayPalStd extends PaypalHelperPaypal {

	var $merchant_email = '';

	function __construct($method, $paypalPlugin) {
		parent::__construct($method, $paypalPlugin);
		//Set the credentials
		if ($this->_method->sandbox) {
			$this->merchant_email = $this->_method->sandbox_merchant_email;
		} else {
			$this->merchant_email = $this->_method->paypal_merchant_email;
		}
		if (empty($this->merchant_email)) {
			$sandbox = "";
			if ($this->_method->sandbox) {
				$sandbox = 'SANDBOX_';
			}
			$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_' . $sandbox . 'MERCHANT'), $this->_method->payment_name, $this->_method->virtuemart_paymentmethod_id);
			vmError($text, $text);
			return FALSE;
		}
	}

	public function ManageCheckout() {
		return $this->preparePost();
	}

	public function preparePost() {

		$post_variables = $this->initPostVariables($this->_method->payment_type);
		$paymentCurrency = CurrencyDisplay::getInstance($this->_method->payment_currency);
		$order_number_text=$this->getItemName(vmText::_('COM_VIRTUEMART_ORDER_NUMBER'));

		switch ($this->_method->payment_type) {
			case '_xclick':
			case '_donations':
				$post_variables['item_name'] = $order_number_text . ': ' . $this->order['details']['BT']->order_number;
				$post_variables['amount'] = $this->total;
				break;
			case '_oe-gift-certificate':
				$post_variables['item_name'] = $order_number_text . ': ' . $this->order['details']['BT']->order_number;
				//$post_variables['amount'] = round ($paymentCurrency->convertCurrencyTo ($this->_method->payment_currency, $this->order['details']['BT']->order_total, FALSE), 2);;
				$post_variables['fixed_denom'] = vmPSPlugin::getAmountValueInCurrency($this->order['details']['BT']->order_salesPrice, $this->_method->payment_currency);
				//$post_variables['min_denom'] = $this->total;
				//$post_variables['max_denom'] = $this->total;
				$post_variables['shopping_url'] = JURI::root();
				$post_variables['buyer_name'] = $this->order['details']['BT']->first_name . ' ' . $this->order['details']['BT']->last_name;
				if (array_key_exists('ST', $this->order['details'])) {
					$post_variables['recipient_name'] = $this->order['details']['ST']->first_name . ' ' . $this->order['details']['ST']->last_name;
				}
				break;

			case '_cart':
				$this->addPrices($post_variables);

				break;

			case '_xclick-subscriptions':

				$post_variables['item_name'] = $order_number_text . ': ' . $this->order['details']['BT']->order_number;

				if ($this->_method->subcription_trials) {
					$post_variables['a1'] = ($this->_method->trial1_price) ? $this->_method->trial1_price : 0; //Trial1 price.
					$post_variables['p1'] = $this->getDurationValue($this->_method->trial1_duration);
					$post_variables['t1'] = $this->getDurationUnit($this->_method->trial1_duration);
				}
				/*if ($this->_method->subcription_trials == 2) {
					$post_variables['a2']		= ($this->_method->trial2_price) ? $this->_method->trial2_price : 0; //Trial2 price.
					$post_variables['p2']       = $this->getDurationValue($this->_method->trial2_duration);
					$post_variables['t2']       = $this->getDurationUnit($this->_method->trial2_duration);
				}*/
				$post_variables['a3'] = $this->total; //Regular subscription price.
				$post_variables['p3'] = $this->getDurationValue($this->_method->subscription_duration);
				$post_variables['t3'] = $this->getDurationUnit($this->_method->subscription_duration);

				$post_variables['src'] = 1; //Recurring payments. Subscription payments recur unless subscribers cancel their subscriptions before the end of the current billing cycle or you limit the number of times that payments recur with the value that you specify for srt
				$post_variables['srt'] = $this->_method->subscription_term; //Recurring times. Number of times that subscription payments recur. Specify an integer with a minimum value of 1 and a maximum value of 52. Valid only if you specify src="1"
				$post_variables['sra'] = 1; //Reattempt on failure. If a recurring payment fails, PayPal attempts to collect the payment two more times before canceling the subscription.
				$post_variables['modify'] = 0; //Modification behavior. Allowable values are:
				//0 – allows subscribers only to sign up for new subscriptions,
				//1 – allows subscribers to sign up for new subscriptions and modify their current subscriptions
				//2 – allows subscribers to modify only their current subscriptions
				break;

			case '_xclick-auto-billing':
				$post_variables['item_name'] = $order_number_text . ': ' . $this->order['details']['BT']->order_number;
				//A description of the automatic billing plan.
				$post_variables['max_text'] = $this->_method->payment_desc;
				//Specify whether to let buyers enter maximum billing limits in a text box or choose from a list of maximum billing limits that you specify.
				//Allowable values are:
				//max_limit_own – your button displays a text box for buyers to enter their own maximums above a minimum billing limit that you set with the min_amount variable.
				//max_limit_defined – your button displays a dropdown menu of product options with prices to let buyers choose their maximum billing limits.
				$post_variables['set_customer_limit'] = 'max_limit_defined';
				//The minimum monthly billing limit, if you have one. Valid only if set_customer_limit = max_limit_own.
				//$post_variables['min_amount'] 	= 0;
				$post_variables['min_amount'] = $this->total;
				switch ($this->_method->billing_max_amount_type) {
					case 'cust':
						$post_variables["max_amount"] = vmPSPlugin::getAmountValueInCurrency($this->customerData->getVar('autobilling_max_amount'), $this->_method->payment_currency);
						break;
					case 'value':
						$post_variables["max_amount"] = vmPSPlugin::getAmountValueInCurrency($this->_method->billing_max_amount, $this->_method->payment_currency);
						break;
					case 'perc':
						$percentage = $this->_method->billing_max_amount;
						$max_amount = ($this->total * floatval($percentage)) / 100 + $this->total;
						$post_variables['max_amount'] = round($max_amount, 2);
						break;
					case 'cart':
					default:
						$post_variables['max_amount'] = $this->total;
						break;
				}
				break;

			case '_xclick-payment-plan':

				$post_variables['item_name'] = $order_number_text . ': ' . $this->order['details']['BT']->order_number;
				$post_variables['disp_tot'] = 'Y'; //Display the total payment amount to buyers during checkout
				$post_variables['option_index'] = 0;
				$post_variables['option_select0_type'] = 'E'; //F – pay in full, at checkout, E – pay in equal periods, beginning at checkout or sometime later, V – pay in variable periods, beginning at checkout
				if ($this->_method->payment_plan_defer) {
					$post_variables['option_select0_a0'] = '0.00';
					$post_variables['option_select0_p0'] = $this->getDurationValue($this->_method->payment_plan_defer_duration);
					$post_variables['option_select0_t0'] = $this->getDurationUnit($this->_method->payment_plan_defer_duration);
					$post_variables['option_select0_n0'] = 1;

					$post_variables['option_select0_a1'] = round($this->total / $this->_method->payment_plan_term, 2);
					$post_variables['option_select0_p1'] = $this->getDurationValue($this->_method->payment_plan_duration);
					$post_variables['option_select0_t1'] = $this->getDurationUnit($this->_method->payment_plan_duration);
					$post_variables['option_select0_n1'] = $this->_method->payment_plan_term;
				} else {
					$post_variables['option_select0_a0'] = round($this->total / $this->_method->payment_plan_term, 2);
					$post_variables['option_select0_p0'] = $this->getDurationValue($this->_method->payment_plan_duration);
					$post_variables['option_select0_t0'] = $this->getDurationUnit($this->_method->payment_plan_duration);
					$post_variables['option_select0_n0'] = $this->_method->payment_plan_term;
				}
				$post_variables['os0'] = 'pay-in-' . $this->_method->payment_plan_term;
				$post_variables['option_select0'] = 'pay-in-' . $this->_method->payment_plan_term;
				$post_variables['option_select0_name'] = $this->_method->payment_name;

		}

		$url = $this->_getPayPalUrl();
		if (vmconfig::get('css')) {
			$msg = vmText::_('VMPAYMENT_PAYPAL_REDIRECT_MESSAGE', true);
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

		$html = '';
		if ($this->_method->debug) {
			$html .= '<form action="' . $url . '" method="post" name="vm_paypal_form" target="paypal">';
		} else {
			$html .= '<form action="' . $url . '" method="post" name="vm_paypal_form" id="vmPaymentForm" accept-charset="UTF-8">';
		}
		$html .= '<input type="hidden" name="charset" value="utf-8">';

		foreach ($post_variables as $name => $value) {
			$html .= '<input type="hidden" name="' . $name . '" value="' . htmlspecialchars($value) . '" />';
		}
		if ($this->_method->debug) {

			$html .= '<div style="background-color:red;color:white;padding:10px;">
						<input type="submit"  value="The method is in debug mode. Click here to be redirected to PayPal" />
						</div>';
			$this->debugLog($post_variables, 'PayPal request:', 'debug');

		} else {
			$html .= '<input type="submit"  value="' . vmText::_('VMPAYMENT_PAYPAL_REDIRECT_MESSAGE') . '" />';

		}
		$html .= '</form>';

		return $html;
	}

	// todo check the paypal langauge: can it be sent. Atm sent in the country lanaguge

	function initPostVariables($payment_type) {

		$address = ((isset($this->order['details']['ST'])) ? $this->order['details']['ST'] : $this->order['details']['BT']);

		$post_variables = Array();
		$post_variables['cmd'] = '_ext-enter';
		$post_variables['redirect_cmd'] = $payment_type;
		$post_variables['paymentaction'] = strtolower($this->_method->payment_action);
		$post_variables['upload'] = '1';	//We may need this configurable $this->_method->upload
		$post_variables['business'] = $this->merchant_email; //Email address or account ID of the payment recipient (i.e., the merchant).
		$post_variables['receiver_email'] = $this->merchant_email; //Primary email address of the payment recipient (i.e., the merchant
		$post_variables['order_number'] = $this->order['details']['BT']->order_number;
		$post_variables['invoice'] = $this->order['details']['BT']->order_number;
		$post_variables['custom'] = $this->context;
		$post_variables['currency_code'] = $this->currency_code_3;
		if ($payment_type == '_xclick') {
			$post_variables['address_override'] = $this->_method->address_override; // 0 ??   Paypal does not allow your country of residence to ship to the country you wish to
		}
		$post_variables['first_name'] = $address->first_name;
		$post_variables['last_name'] = $address->last_name;
		$post_variables['address1'] = $address->address_1;
		$post_variables['address2'] = isset($address->address_2) ? $address->address_2 : '';
		$post_variables['zip'] = $address->zip;
		$post_variables['city'] = $address->city;
		$post_variables['state'] = isset($address->virtuemart_state_id) ? ShopFunctions::getStateByID($address->virtuemart_state_id, 'state_2_code') : '';
		$post_variables['country'] = ShopFunctions::getCountryByID($address->virtuemart_country_id, 'country_2_code');
		$post_variables['email'] = $this->order['details']['BT']->email;
		$post_variables['night_phone_b'] = $address->phone_1;

		$lang = vRequest::getCmd('lang', '');
		if(!empty($lang)){
			$lang = '&lang='.$lang;
		}
		$post_variables['return'] = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=pluginresponsereceived&on=' . $this->order['details']['BT']->order_number . '&pm=' . $this->order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . vRequest::getInt('Itemid') . $lang;
		//Keep this line, needed when testing
		//$post_variables['return'] 		= JRoute::_(JURI::root().'index.php?option=com_virtuemart&view=vmplg&task=notify&tmpl=component'),
		$post_variables['notify_url'] = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=notify&tmpl=component' . '&lang=' . vRequest::getCmd('lang', '');
		$post_variables['cancel_return'] = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=pluginUserPaymentCancel&on=' . $this->order['details']['BT']->order_number . '&pm=' . $this->order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');

		//$post_variables['undefined_quantity'] = "0";
		//$post_variables['test_ipn'] = $this->_method->debug;
		$post_variables['rm'] = '2'; // the buyer’s browser is redirected to the return URL by using the POST method, and all payment variables are included
		// todo: check when in subdirectories
		// todo add vendor image
		//$post_variables['image_url'] 			= JURI::root() . $vendor->images[0]->file_url;
		$post_variables['bn'] = self::BNCODE;

		$post_variables['no_shipping'] = $this->_method->no_shipping;
		$post_variables['no_note'] = "1";

		if (empty($this->_method->headerimg) OR $this->_method->headerimg == -1) {
			$post_variables['image_url'] = $this->getLogoImage();
		} else {
			$post_variables['cpp_header_image'] = $this->getLogoImage($this->_method->headerimg);
		}
		/*
		 * The HTML hex code for your principal identifying color.
* Valid only for Buy Now and Add to Cart buttons and the Cart Upload command.
* Not used with Subscribe, Donate, or Buy Gift Certificate buttons.
		 */
		if ($this->_method->bordercolor) {
			$post_variables['cpp_cart_border_color'] = str_replace('#', '', strtoupper($this->_method->bordercolor));
		}
// TODO Check that paramterer
		/*
		 * cpp_payflow_color The background color for the checkout page below the header.
		 * Deprecated for Buy Now and Add to Cart buttons and the Cart Upload command
		 *
		 */
		//	$post_variables['cpp_payflow_color'] = 'ff0033';

		return $post_variables;
	}

	function addPrices(&$post_variables) {

		$paymentCurrency = CurrencyDisplay::getInstance($this->_method->payment_currency);

		$i = 1;
		// Product prices
		if ($this->cart->products) {
			foreach ($this->cart->products as $key => $product) {
				$post_variables["item_name_" . $i] = $this->getItemName($product->product_name);
				if ($product->product_sku) {
					$post_variables["item_number_" . $i] = $product->product_sku;
				}
				$post_variables["amount_" . $i] = $this->getProductAmount($this->cart->cartPrices[$key]);
				$post_variables["quantity_" . $i] = $product->quantity;
				$i++;
			}
		}

		$discount = $this->addRulesBill($this->cart->cartData['DBTaxRulesBill']);

		$post_variables["handling_cart"] = 0;
		$post_variables["handling_cart"] += $this->addRulesBill($this->cart->cartData['taxRulesBill']);

		$discount += $this->addRulesBill($this->cart->cartData['DATaxRulesBill']);
		if(!empty($discount)){
			$post_variables["discount_amount_cart"] = abs($discount);
		}


		$post_variables["handling_cart"] += vmPSPlugin::getAmountValueInCurrency($this->cart->cartPrices['salesPriceShipment'], $this->_method->payment_currency);
		$post_variables["handling_cart"] += vmPSPlugin::getAmountValueInCurrency($this->cart->cartPrices['salesPricePayment'], $this->_method->payment_currency);

		$post_variables['currency_code'] = $this->currency_code_3;
		if (!empty($this->cart->cartPrices['salesPriceCoupon'])) {
			$post_variables['discount_amount_cart'] = abs(vmPSPlugin::getAmountValueInCurrency($this->cart->cartPrices['salesPriceCoupon'], $this->_method->payment_currency));
		}
		$pricesCurrency = CurrencyDisplay::getInstance($this->cart->pricesCurrency);
	}

	/**
	 * @return value
	 */
/*	function getHandlingAmount () {
		$handling = 0;
		$handling += $this->addRulesBill($this->cart->cartData['DBTaxRulesBill']);
		$handling += $this->addRulesBill($this->cart->cartData['taxRulesBill']);
		$handling += $this->addRulesBill($this->cart->cartData['DATaxRulesBill']);
		$handling += vmPSPlugin::getAmountValueInCurrency($this->cart->cartPrices['salesPricePayment'], $this->_method->payment_currency);
		return $handling;
	}*/

	function getExtraPluginInfo() {
		return;
	}

	function getOrderBEFields() {
		$showOrderBEFields = array(
			'TXN_ID' => 'txn_id',
			'PAYER_ID' => 'payer_id',
			'PAYER_STATUS' => 'payer_status',
			'PAYMENT_TYPE' => 'payment_type',
			'MC_GROSS' => 'mc_gross',
			'MC_FEE' => 'mc_fee',
			'TAXAMT' => 'tax',
			'MC_CURRENCY' => 'mc_currency',
			'PAYMENT_STATUS' => 'payment_status',
			'PENDING_REASON' => 'pending_reason',
			'REASON_CODE' => 'reason_code',
			'PROTECTION_ELIGIBILITY' => 'protection_eligibility',
			'ADDRESS_STATUS' => 'address_status'
		);


		return $showOrderBEFields;
	}

	function onShowOrderBEPaymentByFields($payment) {
		$prefix = "paypal_response_";
		$html = "";
		$showOrderBEFields = $this->getOrderBEFields();
		foreach ($showOrderBEFields as $key => $showOrderBEField) {
			$field = $prefix . $showOrderBEField;
			// only displays if there is a value or the value is different from 0.00 and the value
			if (isset($payment->$field)) {
				if($payment->$field){
					$html .= $this->paypalPlugin->getHtmlRowBE($prefix . $key, $payment->$field);
				}
			} else {
				//$this->debugLog($payment, 'onShowOrderBEPaymentByFields: missing field '.$field, 'debug');
			}
		}


		return $html;
	}
}