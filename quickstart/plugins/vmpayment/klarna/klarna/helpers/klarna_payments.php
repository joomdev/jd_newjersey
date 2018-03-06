<?php

defined ('_JEXEC') or die('Restricted access');

/**
 * @version $Id: klarna_payments.php 8388 2014-10-07 21:06:30Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (C) 2012 iStraxx - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
class klarna_payments {

	// Estore ID
	/**
	 * @var
	 */
	private $eid;
	// Estore Shared Secret
	private $secret;
	// LIVE or BETA
	private $mode;
	// SSL or not
	private $ssl;
	// Klarna API instance
	private $klarna;
	// Klarna Checkout instance
	private $kCheckout;
	// Country
	private $country;
	private $country_code_3;
	//lang
	private $lang;
	// Currency
	private $currency;
	private $virtuemart_currency_id;
	// Web Root directory
	private $web_root;
	// Title
	private $title;

	private $code;
	// Enabled modules
	private $enabled;
	// User information from Virtuemart & Joomla
	private $shipTo;
	// Variables for the the html page.
	private $klarna_addr;
	private $klarna_first_name;
	private $klarna_last_name;
	private $klarna_gender;
	private $klarna_street;
	private $klarna_houseNr;
	private $klarna_houseExt;
	private $klarna_phone;
	private $klarna_email;
	private $klarna_reference;
	private $payment_charge_link;
	private $klarna_year_salary;
	private $splitAddress;
	private $klarna_bday;

	/**
	 * @param $cData
	 * @param $shipTo
	 */
	function __construct ($cData, $shipTo) {

		$this->shipTo = $shipTo;

		$this->country = $cData['country_code'];
		$this->country_code_3 = $cData['country_code_3'];
		$this->currency = $cData['currency_code'];
		$this->virtuemart_currency_id = $cData['virtuemart_currency_id'];
		//$this->currency = $vendor_currency;
		// Get EID and Secret
		$this->eid = $cData['eid'];
		$this->secret = $cData['secret'];
		$this->lang = $cData['language_code'];
		// Is Invoice enabled?
		$this->enabled = TRUE;
		// Set modes
		$this->mode = $cData['mode'];
		$this->ssl = KlarnaHandler::getKlarnaSSL ($this->mode);

		$this->web_root = JURI::base ();
		try {
			$this->klarna = new Klarna_virtuemart();
			$this->klarna->config ($this->eid, $this->secret, $this->country, $this->lang, $this->currency, $this->mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type (), $this->ssl);
		}
		catch (Exception $e) {
			VmError ('klarna_payments', $e);
			unset($this->klarna);
		}
	}


	/**
	 * Attempt to fill in some of what we've already filled in if we
	 * come back after failing a purchase.
	 */
	private function setPreviouslyFilledIn ($klarna_data) {

		if (is_object($klarna_data)) {
			$klarna_data=(array)$klarna_data;
		}
		if (($this->country == "nl" ) && isset($klarna_data['pno'])) {
			$pno = $klarna_data['pno'];
			$this->birth_year = $klarna_data['birth_year'];
			$this->birth_month = $klarna_data['birth_month'];
			$this->birth_day = $klarna_data['birth_day'];
		}
		elseif ( $this->country == "de") {
			$pno = $klarna_data['pno'];
			$this->birth_year = $klarna_data['birth_year'];
			$this->birth_month = $klarna_data['birth_month'];
			$this->birth_day = $klarna_data['birth_day'];
		} else {
			$this->socialNumber=$klarna_data['socialNumber'];
		}
		$this->klarna_street = ((isset($klarna_data['street']) &&
			!isset($this->klarna_street)) ? $klarna_data['street'] :
			$this->klarna_street);
		$this->klarna_houseNr = ((isset($klarna_data['house_no']) &&
			!isset($this->klarna_houseNr)) ? $klarna_data['house_no'] :
			$this->klarna_houseNr);
		$this->klarna_houseExt = ((isset($klarna_data['house_ext']) &&
			!isset($this->klarna_houseExt)) ? $klarna_data['house_ext'] :
			$this->klarna_houseExt);
		$this->klarna_gender = ((isset($klarna_data['gender']) &&
			!isset($this->klarna_gender)) ? $klarna_data['gender'] :
			$this->klarna_gender);
		$this->klarna_year_salary = ((isset($klarna_data['year_salary']) && !isset($this->klarna_year_salary)) ? $klarna_data['year_salary'] : $this->klarna_year_salary);
	}

	/**
	 * Build the Payment params
	 */
	public function get_payment_params ($method, $payment_type, $cart = NULL, $country_currency_code = '', $vendor_currency='') {

		if (!class_exists ('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		if (!class_exists ('KlarnaAPI')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
		}
		$payment_params = array();
		$invoice_fee = 0;
		if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
			return NULL;
		}
		$payment_params['payment_currency_info'] = "";
		if ($cart->pricesCurrency != $this->virtuemart_currency_id) {
			$payment_params['payment_currency_info'] = vmText::_ ('VMPAYMENT_KLARNA_PAYMENT_CURRENCY_INFO');
		}
		if ($payment_type == 'invoice') {
			KlarnaHandler::getInvoiceFeeInclTax ($method, $this->country_code_3, $cart->pricesCurrency, $this->virtuemart_currency_id, $display_invoice_fee, $invoice_fee);
			$billTotalInCountryCurrency = 0;
			$aTypes = NULL;
			$payment_params['pClasses'] = NULL;
		} else {
			$display_fee = 0;

			$billTotalInCountryCurrency = 0;
			if (isset($cart->cartPrices['billTotal'])) {
				$billTotalInCountryCurrency = KlarnaHandler::convertPrice ($cart->cartPrices['billTotal'], $vendor_currency, $country_currency_code, $cart->cartPrices);
			}
			if ($billTotalInCountryCurrency <= 0) {
				return NULL;
			}

			//$aTypes = array(KlarnaPClass::ACCOUNT, KlarnaPClass::CAMPAIGN, KlarnaPClass::FIXED);
			$aTypes = array(KlarnaPClass::ACCOUNT, KlarnaPClass::CAMPAIGN);

		}
		$payment_params['sType'] = $payment_type;
		$kCheckout = new KlarnaAPI($this->country, $this->lang, $payment_type, $billTotalInCountryCurrency, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, $aTypes, JPATH_VMKLARNAPLUGIN);

		if ($payment_type == 'invoice') {
			if ($invoice_fee) {
				$payment_params['module'] = vmText::sprintf ('VMPAYMENT_KLARNA_INVOICE_TITLE', $display_invoice_fee);

			} else {
				$payment_params['module'] = vmText::_ ('VMPAYMENT_KLARNA_INVOICE_TITLE_NO_PRICE');
			}
			$payment_params['pClasses'] = NULL;
			$payment_params['id'] = 'klarna_invoice';
		} elseif ($payment_type == 'part') {
			KlarnaHandler::getCheapestPclass ($kCheckout, $cheapest, $minimum);

			if ($billTotalInCountryCurrency < $minimum) {
				return NULL;
			}
			if (!class_exists ('VirtueMartModelCurrency')) {
				require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
			}
			// Cheapest is in the Klarna country currency, convert it to the current currency display
			//$currencyDisplay = CurrencyDisplay::getInstance( );
			//$countryCurrencyId = $this->virtuemart_currency_id;
			//$sFee = $currencyDisplay->priceDisplay($cheapest, 0, 1,false);

			$sFee = $kCheckout->getPresentableValuta ($cheapest);
			$payment_params['module'] = vmText::sprintf ('VMPAYMENT_KLARNA_PARTPAY_TITLE', $sFee);
			$payment_params['pClasses'] = $kCheckout->getPClassesInfo ();
			$payment_params['id'] = 'klarna_partPayment';
		}
		else {
			$pclasses = $kCheckout->aPClasses;
			if (empty($pclasses)) {
				return NULL;
			}
			$payment_params['module'] = vmText::_ ('VMPAYMENT_KLARNA_SPEC_TITLE');
			$payment_params['pClasses'] = $kCheckout->getPClassesInfo ();
			$payment_params['id'] = 'klarna_SpecCamp';
		}
		$payment_params['payment_link'] = "https://online.klarna.com/villkor.yaws?eid=" . $this->eid . "&charge=" . $invoice_fee;

		if (strtolower ($this->country) == 'de') {
			$vendor_id = 1;
			$payment_params['agb_link'] = JRoute::_ ('index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendor_id);
		}

		//$lang = KlarnaHandler::getLanguageForCountry($method, $this->country);
		$symbol = KlarnaHandler::getCurrencySymbolForCountry ($method, $this->country);

		if (KlarnaHandler::getKlarnaError ($klarnaError, $klarnaOption)) {
			if ($klarnaOption == 'klarna_' . $payment_type) {
				$payment_params['red_baloon_content'] = $klarnaError;
				$payment_params['red_baloon_paymentBox'] = 'klarna_box_' . $klarnaOption;
				//KlarnaHandler::clearKlarnaError ();
			}
		}

		// Something went wrong, refill what we can.
		$session = JFactory::getSession ();
		$sessionKlarna = $session->get ('Klarna', 0, 'vm');

		if (!empty($sessionKlarna)) {
			$sessionKlarnaData = (object)  json_decode($sessionKlarna ,true);
			if (isset($sessionKlarnaData->KLARNA_DATA)) {
				$klarnaData = (array)$sessionKlarnaData->KLARNA_DATA;
				$this->setPreviouslyFilledIn ($klarnaData);
			}
		}

		$payment_params['paymentPlan'] = '';

		if (is_array ($kCheckout->aPClasses)) {
			foreach ($kCheckout->aPClasses as $pclass) {
				if ($pclass['default'] === TRUE) {
					$payment_params['paymentPlan'] = $pclass['pclass']->getId ();
					break;
				}
			}
		}

		if ($payment_type != "spec") {
			//$payment_params['conditionsLink'] = $aTemplateData['conditions'];
		}
		$payment_params['fields'] = $this->shipTo;
		$payment_params['payment_id'] = 'virtuemart_paymentmethod_id';
		$payment_params['checkout'] = $this->klarna->checkoutHTML ();
		$payment_params['eid'] = $this->eid;
		$payment_params['year_salary'] = $this->klarna_year_salary;
		$payment_params['agreement_link'] = $this->payment_charge_link;
		$payment_params['sum'] = $invoice_fee;
		$payment_params['fee'] = $invoice_fee;
		$payment_params['invoice_fee'] = $invoice_fee;
		$payment_params['langISO'] = $this->lang;
		$payment_params['countryCode'] = $this->country;
		$payment_params['flag'] = KlarnaFlags::CHECKOUT_PAGE;
		$payment_params['payment_id'] = "payment";
		$payment_params['invoice_name'] = 'klarna_invoice';
		$payment_params['part_name'] = 'klarna_partPayment';
		$payment_params['spec_name'] = 'klarna_SpecCamp';
		$payment_params['fields']['socialNumber'] = isset($this->socialNumber)?$this->socialNumber:"";
		$payment_params['fields']['birth_day'] = isset($this->birth_day)?$this->birth_day:"";
		$payment_params['fields']['birth_month'] = isset($this->birth_month)?$this->birth_month:"";
		$payment_params['fields']['birth_year'] = isset($this->birth_year)?$this->birth_year:"";

		return $payment_params;
	}

	/**
	 * Build the Payment params
	 */
	public function getCheapestMonthlyCost ($cart = NULL, $cData) {

		if (!class_exists ('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		if (!class_exists ('KlarnaAPI')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
		}

		if (!isset($this->klarna) || !($this->klarna instanceof Klarna_virtuemart)) {
			return NULL;
		}

		$display_fee = 0;

		$billTotalInCountryCurrency = 0;
		if (isset($cart->cartPrices['billTotal'])) {
			$billTotalInCountryCurrency = KlarnaHandler::convertPrice ($cart->cartPrices['billTotal'], $cData['vendor_currency'], $cData['virtuemart_currency_id'],  $cart->pricesCurrency);
		}
		if ($billTotalInCountryCurrency <= 0) {
			return NULL;
		}
		$aTypes = array(KlarnaPClass::ACCOUNT, KlarnaPClass::CAMPAIGN);
		$kCheckout = new KlarnaAPI($this->country, $this->lang, 'part', $billTotalInCountryCurrency, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, $aTypes, JPATH_VMKLARNAPLUGIN);

		KlarnaHandler::getCheapestPclass ($kCheckout, $cheapest, $minimum);
vmdebug('getCheapestMonthlyCost',$cart->cartPrices['billTotal'], $billTotalInCountryCurrency , $cheapest,$minimum);

		if ($billTotalInCountryCurrency < $minimum) {
			return NULL;
		}
		if (!class_exists ('VirtueMartModelCurrency')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'currency.php');
		}

		$sFee = $kCheckout->getPresentableValuta ($cheapest);
		return $sFee;

	}

	/**
	 * @return string
	 */
	public function getTermsLink () {

		return 'https://static.klarna.com/external/html/' . KLARNA_SPECIAL_CAMPAIGN . '_' . strtolower ($this->country) . '.html';
	}

	/**
	 * @param $pid
	 * @param $totalSum
	 * @return string
	 */
	function displayPclass ($pid, $totalSum) {

		if (!class_exists ('KlarnaAPI')) {
			require (JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'helpers' . DS . 'klarnaapi.php');
		}
		$kCheckout = new KlarnaAPI($this->country, $this->lang, 'part', $totalSum, KlarnaFlags::CHECKOUT_PAGE, $this->klarna, array(KlarnaPClass::ACCOUNT, KlarnaPClass::CAMPAIGN, KlarnaPClass::FIXED), JPATH_VMKLARNAPLUGIN);
		return $kCheckout->renderPClass ($pid);
	}

}

