<?php
/**
 *
 * Paypal  Hosted Pro payment plugin
 *
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

//PayPal error codes:
//https://developer.paypal.com/webapps/developer/docs/classic/api/errorcodes/
//API Reference
//https://developer.paypal.com/webapps/developer/docs/classic/api/
// https://cms.paypal.com/cms_content/GB/en_GB/files/developer/HostedSolution.pdf

class PaypalHelperPayPalHosted extends PaypalHelperPaypal {

	// Pay Now button; since version 65.1
	const BM_BUTTON_TYPE = 'PAYMENT';
	//A secure button, not stored on PayPal, used only to initiate the Hosted Solution checkout flow;
	//default for Pay Now button. Since version 65.1
	const BM_BUTTON_CODE = 'TOKEN';
	const BM_BUTTON_VERSION = '104.0';
	const PAYPAL_USER_LG = 64;
	var $api_login_id = '';
	var $api_signature = '';
	var $api_password = '';

	function __construct($method,$paypalPlugin) {
		parent::__construct($method,$paypalPlugin);
		//Set the credentials
		if ($this->_method->sandbox  ) {
			$this->api_login_id = trim($this->_method->sandbox_api_login_id);
			$this->api_signature = trim($this->_method->sandbox_api_signature);
			$this->api_password = trim($this->_method->sandbox_api_password);
			if (empty($this->_method->sandbox_payflow_partner)) {
				$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_SANDBOX_PAYFLOW_PARTNER'), $this->_method->payment_name, $this->_method->virtuemart_paymentmethod_id);
				vmError($text);
			} else {
				$this->payflow_partner = trim($this->_method->sandbox_payflow_partner);
			}
			$this->payflow_vendor = trim($this->_method->sandbox_payflow_vendor);
		} else {
			$this->api_login_id = trim($this->_method->api_login_id);
			$this->api_signature = trim($this->_method->api_signature);
			$this->api_password = trim($this->_method->api_password);
			if (empty($this->_method->payflow_partner)) {
				$text = vmText::sprintf('VMPAYMENT_PAYPAL_PARAMETER_REQUIRED', vmText::_('VMPAYMENT_PAYPAL_PAYFLOW_PARTNER'), $this->_method->payment_name, $this->_method->virtuemart_paymentmethod_id);
				vmError($text);
			} else {
				$this->payflow_partner = trim($this->_method->payflow_partner);
			}
			$this->payflow_vendor = trim($this->_method->payflow_vendor);
		}

		if (empty($this->api_login_id) || empty($this->api_signature) || empty($this->api_password)) {
			$text = vmText::sprintf('VMPAYMENT_PAYPAL_CREDENTIALS_NOT_SET', $this->_method->payment_name, $this->_method->virtuemart_paymentmethod_id);
			vmError($text, $text);
		}
	}

	public function ManageCheckout() {
		return $this->preparePost();
	}

	// todo check the paypal langauge: can it be sent. Atm sent in the country lanaguge
	// verfiez la langue, à cause accent
	function initPostVariables($paypalMethod) {

		$post_variables = Array();
		$post_variables['METHOD'] = $paypalMethod;
		$post_variables['VERSION'] = self::BM_BUTTON_VERSION; //https://developer.paypal.com/webapps/developer/docs/classic/release-notes/
		$post_variables['USER'] = $this->api_login_id;
		$post_variables['PWD'] = $this->api_password;
		$post_variables['SIGNATURE'] = $this->api_signature;
		$post_variables['BUTTONTYPE'] = self::BM_BUTTON_TYPE;
		$post_variables['BUTTONCODE'] = self::BM_BUTTON_CODE;
		$post_variables['BUTTONIMAGEURL'] = 'https://www.paypal.com/en_US/i/btn/btn_paynow_SM.gif'; //we automatically redirect to paypal
		$post_variables['L_BUTTONVAR']['bn'] = self::BNCODE; // Identifies the source that built the code.
		$post_variables['L_BUTTONVAR']['custom'] = $this->context;

		$post_variables['L_BUTTONVAR']['partner'] = $this->payflow_partner;
		$post_variables['L_BUTTONVAR']['vendor'] = $this->payflow_vendor;
		return $post_variables;
	}

	function addBillTo(&$post_variables) {

		$addressBT = $this->order['details']['BT'];

		//Bill To
		$post_variables['L_BUTTONVAR']['billing_first_name'] = isset($addressBT->first_name) ? $this->truncate($addressBT->first_name, 50) : ''; // First name of person the item is being shipped to.
		$post_variables['L_BUTTONVAR']['billing_last_name'] = isset($addressBT->last_name) ? $this->truncate($addressBT->last_name, 60) : ''; // Last name of person the item is being shipped to.

		$post_variables['L_BUTTONVAR']['billing_address1'] = isset($addressBT->address_1) ? $this->truncate($addressBT->address_1, 60) : '';
		$post_variables['L_BUTTONVAR']['billing_address2'] = isset($addressBT->address_2) ? $this->truncate($addressBT->address_2, 60) : '';
		$post_variables['L_BUTTONVAR']['billing_city'] = isset($addressBT->city) ? $this->truncate($addressBT->city, 40) : '';
		$post_variables['L_BUTTONVAR']['billing_zip'] = isset($addressBT->zip) ? $this->truncate($addressBT->zip, 40) : '';
		$post_variables['L_BUTTONVAR']['billing_state'] = isset($addressBT->virtuemart_state_id) ? $this->truncate(ShopFunctions::getStateByID($addressBT->virtuemart_state_id), 20) : '';
		$post_variables['L_BUTTONVAR']['billing_country'] = ShopFunctions::getCountryByID($addressBT->virtuemart_country_id, 'country_2_code');
	}

	function addShipTo(&$post_variables) {

		$addressST = ((isset($this->order['details']['ST'])) ? $this->order['details']['ST'] : $this->order['details']['BT']);

		//Ship To
		$post_variables['L_BUTTONVAR']['first_name'] = isset($addressST->first_name) ? $this->truncate($addressST->first_name, 50) : ''; // First name of person the item is being shipped to.
		$post_variables['L_BUTTONVAR']['last_name'] = isset($addressST->last_name) ? $this->truncate($addressST->last_name, 60) : ''; // Last name of person the item is being shipped to.
		$post_variables['L_BUTTONVAR']['address1'] = isset($addressST->address_1) ? $this->truncate($addressST->address_1, 60) : '';
		$post_variables['L_BUTTONVAR']['address2'] = isset($addressST->address_2) ? $this->truncate($addressST->address_2, 60) : '';
		$post_variables['L_BUTTONVAR']['city'] = isset($addressST->city) ? $this->truncate($addressST->city, 40) : '';
		$post_variables['L_BUTTONVAR']['zip'] = isset($addressST->zip) ? $this->truncate($addressST->zip, 40) : '';
		$post_variables['L_BUTTONVAR']['state'] = isset($addressST->virtuemart_state_id) ? $this->truncate(ShopFunctions::getStateByID($addressST->virtuemart_state_id), 20) : '';
		$post_variables['L_BUTTONVAR']['country'] = ShopFunctions::getCountryByID($addressST->virtuemart_country_id, 'country_2_code');
	}

	function addPaymentPageParams(&$post_variables) {
		$post_variables['L_BUTTONVAR']['template'] = $this->_method->template;
		$post_variables['L_BUTTONVAR']['showHostedThankyouPage'] = 'false';

		if ($this->_method->bordercolor) {
			$post_variables['L_BUTTONVAR']['bodyBgColor'] = strtoupper($this->_method->bordercolor);
			$post_variables['L_BUTTONVAR']['payflowcolor'] = '#ff0033'; //str_replace('#','',strtoupper($this->_method->bordercolor));
		}

		$post_variables['L_BUTTONVAR']['headerBgColor'] = strtoupper($this->_method->headerBgColor);
		$post_variables['L_BUTTONVAR']['headerHeight'] = $this->_method->headerHeight;
		$post_variables['L_BUTTONVAR']['logoFont'] = $this->_method->logoFont;
		$post_variables['L_BUTTONVAR']['logoFontSize'] = $this->_method->logoFontSize;
		$post_variables['L_BUTTONVAR']['logoFontColor'] = $this->_method->logoFontColor;
		if (!empty($this->_method->bodyBgImg[0])) {
			$post_variables['L_BUTTONVAR']['bodyBgImg'] = $this->getLogoImage($this->_method->bodyBgImg[0]);

		}
		$post_variables['L_BUTTONVAR']['logoImage'] = $this->getLogoImage();

		$post_variables['L_BUTTONVAR']['bodyBgColor'] = $this->_method->bodyBgColor;
		$post_variables['L_BUTTONVAR']['PageTitleTextColor'] = $this->_method->PageTitleTextColor;
		$post_variables['L_BUTTONVAR']['PageCollapseBgColor'] = $this->_method->PageCollapseBgColor;
		//$post_variables['L_BUTTONVAR']['PageCollapseTextColor'] =    $this->_method->PageCollapseTextColor;
		$post_variables['L_BUTTONVAR']['orderSummaryBgColor'] = $this->_method->orderSummaryBgColor;
		if (!empty($this->_method->orderSummaryBgImage[0])) {
			$post_variables['L_BUTTONVAR']['orderSummaryBgImage'] = $this->getLogoImage($this->_method->orderSummaryBgImage[0]);
		}
		$post_variables['L_BUTTONVAR']['footerTextColor'] = $this->_method->footerTextColor;
		$post_variables['L_BUTTONVAR']['footerTextlinkColor'] = $this->_method->footerTextlinkColor;
		$post_variables['L_BUTTONVAR']['pageButtonBgColor'] = $this->_method->pageButtonBgColor;
		$post_variables['L_BUTTONVAR']['pageButtonTextColor'] = $this->_method->pageButtonTextColor;
		$post_variables['L_BUTTONVAR']['pageTitleTextColor'] = $this->_method->pageTitleTextColor;
		$post_variables['L_BUTTONVAR']['sectionBorder'] = $this->_method->sectionBorder;

	}

	function addOrderInfos(&$post_variables) {
		$post_variables['L_BUTTONVAR']['buyer_email'] = $this->order['details']['BT']->email; //Email address of the buyer.

		if (is_array($this->order) && is_object($this->order['details']['BT'])) {
			$post_variables['L_BUTTONVAR']['invoice'] = $this->order['details']['BT']->order_number;
		} else {
			if (is_object($this->order)) {
				$post_variables['L_BUTTONVAR']['invoice'] = $this->order->order_number;
			}
		}

	}

	function addPrices(&$post_variables) {

	}

	function addAmount(&$post_variables) {
		// Website Payment Standard has separate values for amount and quantity, whereas Hosted Solution uses subtotal only.
		// Ensure that the subtotal includes the amount you want to charge the buyer, taking into account any applicable discount and the quantity of items.
		$post_variables['L_BUTTONVAR']['subtotal'] = $this->total; // Amount charged for the transaction. If shipping, handling, Yes and taxes are not specified, this is the total amount charged.
		$post_variables['L_BUTTONVAR']['currency_code'] = $this->currency_code_3;
	}

	function addUrls(&$post_variables) {
		$post_variables['L_BUTTONVAR']['return'] = JURI::root().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginresponsereceived&paypalproduct=hosted&on=' . $this->order['details']['BT']->order_number . '&pm=' . $this->order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . vRequest::getInt('Itemid').'&lang='.  vRequest::getCmd('lang','');
		$post_variables['L_BUTTONVAR']['notify_url'] = JURI::root().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&tmpl=component'.'&lang='.  vRequest::getCmd('lang','');
		$post_variables['L_BUTTONVAR']['cancel_return'] =JURI::root().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&paypalproduct=hosted&on=' . $this->order['details']['BT']->order_number . '&pm=' . $this->order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . vRequest::getInt('Itemid').'&lang='.  vRequest::getCmd('lang','');

	}

	function addConfigPaymentParams(&$post_variables) {
		$post_variables['L_BUTTONVAR']['address_override'] = $this->_method->address_override;
		$post_variables['L_BUTTONVAR']['noshipping'] = $this->_method->no_shipping;
		// for version 104 payment action must be lower case
		$post_variables['L_BUTTONVAR']['paymentaction'] = strtolower($this->_method->payment_action); // Identifies the source that built the code for the button.


	}

	public function preparePost() {

		$post_variables = $this->initPostVariables('BMCreateButton');
		$this->setTimeOut();
		$this->addOrderInfos($post_variables);
		$this->addPrices($post_variables);
		$this->addBillTo($post_variables);
		$this->addShipTo($post_variables);
		$this->addAmount($post_variables);
		$this->addUrls($post_variables);
		$this->addConfigPaymentParams($post_variables);
		$this->addPaymentPageParams($post_variables);


		$btn = 0;
		foreach ($post_variables['L_BUTTONVAR'] as $key => $buttonVar) {
			if (!empty($buttonVar)) {
				$post_variables['L_BUTTONVAR' . $btn++] = $key . '=' . $buttonVar;
			}
		}
		unset($post_variables['L_BUTTONVAR']);


		$this->sendRequest($post_variables);
		$valid = $this->handleResponse();
		if ($valid) {
			//$this->customerData->setVar('token', $this->response['TOKEN']);
			//$this->customerData->save();
			if ($this->_method->template != 'templateD') {
				$this->redirectToPayPal();
			} else {
				return true;
			}

		} else {
			//$this->customerData->clear();
			return false;
		}
		return true;


	}

	function redirectToPayPal() {

		$websitecode = $this->response['WEBSITECODE'];
		$emailink = $this->response['EMAILLINK'];

		if ($this->_method->debug AND $this->_method->template != 'templateD') {
			echo '<div style="background-color:red;color:white;padding:10px;">The method is in debug mode. <a href="' . $emailink . '">Click here to be redirected to PayPal</a></div>';
			echo '<div style="background-color:red;color:white;padding:10px;">The method is in debug mode. ' . $websitecode . 'Click here to be redirected to PayPal</a></div>';
			jexit();
		} else {
			header('location: ' . $emailink);
		}
	}

	function DoCapture($payment) {

		$paypal_data = json_decode($payment->paypal_fullresponse);
		//Only capture payment if it still pending
		if ($paypal_data->payment_status != 'Pending' && $paypal_data->pending_reason != 'Authorization') {
			return false;
		}
		$post_variables = $this->initPostVariables('DoCapture');

		//Do we need to reauthorize ?
		$reauth = $this->doReauthorize($payment->paypal_response_txn_id, $paypal_data);

		// the authorisation identification number of the payment you want to capture.
		if ($reauth === false) {
			$post_variables['AUTHORIZATIONID'] = $paypal_data->txn_id;
		} else {
			$post_variables['AUTHORIZATIONID'] = $reauth;
		}


		// Amount to capture.
		$post_variables['AMT'] = $this->total;
		$post_variables['CURRENCYCODE'] = $this->currency_code_3;
		// The value Complete indicates that this the last capture you intend to make.
		// The value NotComplete indicates that you intend to make additional captures.
		// N O T E : If Complete, any remaining amount of the original authorised transaction is automatically voided and all remaining open authorisations are voided.
		$post_variables['COMPLETETYPE'] = 'Complete';

// (Optional) Your invoice number or other identification number that is displayed to the merchant and customer in his transaction history.
		$post_variables['INVNUM'] = $this->order->order_number;

		// (Optional) An informational note about this settlement that is displayed to the payer in email and in his transaction history.
		//$post_variables['NOTE']	= 'add comments if send yto user ?';

		$this->sendRequest($post_variables);
		$success = $this->handleResponse();
		if (!$success) {
			$this->doVoid($payment);
		}
		return $success;
	}

	/**
	 * https://developer.paypal.com/webapps/developer/docs/classic/paypal-payments-standard/integration-guide/authcapture/
	 * @param $AuthorizationID
	 * @param $paypal_data
	 * @return bool
	 */
	function doReauthorize($AuthorizationID, $paypal_data) {
		// TODO
		return false;
		/*
        $post_variables = $this->initPostVariables('DoReauthorization');
        $post_variables['TOKEN'] = $paypal_data->TOKEN;
        $post_variables['PAYERID'] = $paypal_data->payer_id; // Unique PayPal customer account identification number
        $post_variables['AUTHORIZATIONID'] = $AuthorizationID;
        $post_variables['PAYMENTACTION'] = 'DoReauthorization';
        $post_variables['AMT'] =  $paypal_data->mc_gross; // ???
        $post_variables['CURRENCYCODE'] = $paypal_data->mc_currency;

        $this->sendRequest($post_variables);
        if ($this->handleResponse()) {
            return $this->response['AUTHORIZATIONID'];
        } else {
            return false;
        }
		*/
	}

	function RefundTransaction($payment) {

		$paypal_data = json_decode($payment->paypal_fullresponse);
		if (strcasecmp($paypal_data->payment_status, 'Completed') == 0) {
			$post_variables = $this->initPostVariables('RefundTransaction');
			$post_variables['REFUNDTYPE'] = 'Full';
			$post_variables['TRANSACTIONID'] = $paypal_data->txn_id;

		} else if (strcasecmp($paypal_data->payment_status, 'Pending') == 0 && strcasecmp($paypal_data->pending_reason, 'authorization') == 0) {
			//  An authorisation for this transaction has been voided/cancelled
			$post_variables = $this->initPostVariables('DoVoid');
		} else {
			return false;
		}

		$post_variables['AuthorizationID'] = $paypal_data->txn_id;
		//$post_variables['TOKEN'] 		= $paypal_data->TOKEN;
		//$post_variables['PAYERID']	 	= $paypal_data->payer_id;

		$this->sendRequest($post_variables);
		return $this->handleResponse();
	}

	function doVoid($payment) {
		$paypal_data = json_decode($payment->paypal_fullresponse);
		$post_variables = $this->initPostVariables('DoVoid');
		$post_variables['AuthorizationID'] = $paypal_data->txn_id;
		$this->sendRequest($post_variables);
		return $this->handleResponse();
	}

	public function ManageCancelOrder($payment) {
		$this->RefundTransaction($payment);
		return;
	}

	function getOrderBEFields() {
		$showOrderBEFields = array(
			'TXN_ID' => 'txn_id',
			'PAYER_ID' => 'payer_id',
			'PAYER_STATUS' => 'payer_status',
			'MC_GROSS' => 'mc_gross',
			'MC_FEE' => 'mc_fee',
			'TAXAMT' => 'tax',
			'MC_CURRENCY' => 'mc_currency',
			'PAYMENT_STATUS' => 'payment_status',
			'PENDING_REASON' => 'pending_reason',
			'REASON_CODE' => 'reasoncode',
			'PROTECTION_ELIGIBILITY' => 'protection_eligibility',
			'CORRELATIONID' => 'CORRELATIONID',
			'REFUND_AMOUNT' => 'TOTALREFUNDEDAMOUNT',
			'method' => 'method',
		);
		return $showOrderBEFields;
	}

}