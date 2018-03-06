<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @version $Id: klarnahandler.php 9420 2017-01-12 09:35:36Z Milbo $
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
class KlarnaHandler {

	/**
	 * @static
	 * @return array
	 */
	static function countriesData () {

		$countriesData = array(
			'NOR' => array(
				'pno_encoding'    => 3,
				'language'        => 97,
				'language_code'   => 'nb',
				'country'         => 164,
				'currency'        => 1,
				'currency_code'   => 'NOK',
				'currency_symbol' => 'kr',
				'country_code'    => 'no'
			),
			'SWE' => array(
				'pno_encoding'    => 2,
				'language'        => 138,
				'language_code'   => 'sv',
				'country'         => 209,
				'country_code'    => 'se',
				'currency'        => 0,
				'currency_code'   => 'SEK',
				'currency_symbol' => 'kr'
			),
			'DNK' => array(
				'pno_encoding'    => 5,
				'language'        => 27,
				'language_code'   => 'da',
				'country'         => 59,
				'country_code'    => 'dk',
				'currency'        => 3,
				'currency_code'   => 'DKK',
				'currency_symbol' => 'kr',
			),
			'FIN' => array(
				'pno_encoding'    => 4,
				'language'        => 37,
				'language_code'   => 'fi',
				'country'         => 73,
				'country_code'    => 'fi',
				'currency'        => 2,
				'currency_code'   => 'EUR',
				'currency_symbol' => '&#8364;'
			),
			'NLD' => array(
				'pno_encoding'    => 7,
				'language'        => 101,
				'language_code'   => 'nl',
				'country'         => 154,
				'country_code'    => 'nl',
				'currency'        => 2,
				'currency_code'   => 'EUR',
				'currency_symbol' => '&#8364;',
			),
			'DEU' => array(
				'pno_encoding'    => 6,
				'language'        => 28,
				'language_code'   => 'de',
				'country'         => 81,
				'country_code'    => 'de',
				'currency'        => 2,
				'currency_code'   => 'EUR',
				'currency_symbol' => '&#8364;'
			)
		);
		return $countriesData;
	}

	/**
	 * @static
	 * @param $method
	 * @param $country
	 * @return array|null
	 */
	static function countryData ($method, $country) {

		$countriesData = self::countriesData();
		$lower_country = strtolower($country);
		if (array_key_exists(strtoupper($country), $countriesData)) {
			$cData = $countriesData[strtoupper($country)];
			$eid = 'klarna_merchantid_' . $lower_country;
			$secret = 'klarna_sharedsecret_' . $lower_country;
			$invoice_fee = 'klarna_invoicefee_' . $lower_country;
			$min_amount = 'klarna_min_amount_part_' . $lower_country;
			$payment_activated = 'klarna_payments_' . $lower_country;
			$active = 'klarna_active_' . $lower_country;
			$cData['eid'] = $method->$eid;
			$cData['secret'] = $method->$secret;
			$cData['invoice_fee'] = (double)$method->$invoice_fee;
			$cData['country_code_3'] = $country;
			$cData['virtuemart_currency_id'] = ShopFunctions::getCurrencyIDByName($cData['currency_code']);
			$cData['virtuemart_country_id'] = ShopFunctions::getCountryIDByName($country);
			$cData['mode'] = KlarnaHandler::getKlarnaMode($method, $country);
			$cData['min_amount'] = $method->$min_amount;
			$cData['active'] = $method->$active;
			if (empty($method->$payment_activated)) {
				$method->$payment_activated = array('invoice', 'part');
			}
			$cData['payments_activated'] = $method->$payment_activated;
			if (!class_exists('VirtueMartModelVendor')) {
				require(VMPATH_ADMIN . DS . 'models' . DS . 'vendor.php');
			}
			$vendor_id = 1;
			$cData['vendor_currency'] = VirtueMartModelVendor::getVendorCurrency($vendor_id)->vendor_currency;
			return $cData;
		} else {
			return NULL;
		}
	}

	/**
	 * @static
	 * @param $method
	 * @param $country
	 * @return array|null
	 */
	public static function getCountryData ($method, $country) {

		//$country = self::convertToThreeLetterCode($country);
		return self::countryData($method, $country);
	}

	/**
	 * @static
	 * @param $method
	 * @param $country
	 * @return mixed
	 */
	public static function convertCountry ($method, $country) {

		$country_data = self::countryData($method, $country);
		return $country_data['country_code'];
	}


	/**
	 * @static
	 * @param $method
	 * @param $country
	 * @return mixed
	 */
	public static function getLanguageForCountry ($method, $country) {

		$country = self::convertToThreeLetterCode($country);
		$country_data = self::countryData($method, $country);
		return $country_data['language_code'];
	}

	/**
	 * @static
	 * @param $method
	 * @param $country
	 * @return mixed
	 */
	public static function getCurrencySymbolForCountry ($method, $country) {

		$country_data = self::countryData($method, $country);
		return $country_data['currency_symbol'];
	}

	/**
	 * @static
	 * @param $method
	 * @param $country
	 * @return mixed
	 */
	public static function getInvoiceFee ($method, $country) {

		$invoice_fee = 'klarna_invoicefee_' . strtolower($country);
		return $method->$invoice_fee;
	}

	/**
	 * @static
	 * @param $method
	 * @param $country
	 * @return mixed
	 */
	public static function getInvoiceTaxId ($method, $country) {

		$invoice_fee_tax = 'klarna_invoice_tax_id_' . strtolower($country);
		return $method->$invoice_fee_tax;
	}

	/**
	 * The invoice fee is in the vendor currency, and should be converted to the payment currency
	 *
	 * @static
	 * @param $method
	 * @param $country
	 * @return mixed
	 */
	public static function getInvoiceFeeInclTax ($method, $country, $cartPricesCurrency, $cartPaymentCurrency, &$display_invoice_fee, &$invoice_fee) {

		$method_invoice_fee = self::getInvoiceFee($method, $country);
		$invoice_tax_id = self::getInvoiceTaxId($method, $country);
		vmdebug('getInvoiceFeeInclTax', $cartPaymentCurrency, $invoice_fee);
		if (!class_exists('calculationHelper')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'calculationh.php');
		}
		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}

		if (!class_exists('VirtueMartModelVendor')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'vendor.php');
		}

		$vendor_id = 1;
		$vendor_currency = VirtueMartModelVendor::getVendorCurrency($vendor_id);

		//$currency = CurrencyDisplay::getInstance ();
		$paymentCurrency = CurrencyDisplay::getInstance($cartPaymentCurrency);
		$invoice_fee = (double)round($paymentCurrency->convertCurrencyTo($cartPaymentCurrency, $method_invoice_fee, FALSE), 2);
		$currencyDisplay = CurrencyDisplay::getInstance($cartPricesCurrency);

		$paymentCurrency = CurrencyDisplay::getInstance($cartPaymentCurrency);
		$display_invoice_fee = $paymentCurrency->priceDisplay($method_invoice_fee, $cartPaymentCurrency);
		$currencyDisplay = CurrencyDisplay::getInstance($cartPricesCurrency);

		vmdebug('getInvoiceFeeInclTax', $cartPaymentCurrency, $invoice_fee, $invoice_tax_id, $display_invoice_fee);
		return;
	}

	/*
		* @depredecated
		*/

	/**
	 * @static
	 * @param $country
	 * @return string
	 */
	public static function convertToThreeLetterCode ($country) {

		switch (strtolower($country)) {
			case "se":
				return "swe";
			case "de":
				return "deu";
			case "dk":
				return "dnk";
			case "nl":
				return "nld";
			case "fi":
				return "fin";
			case "no":
				return "nor";
			default:
				return $country;
		}
	}

	/**
	 * @static
	 * @return array
	 */
	public static function getKlarnaCountries () {

		$klarna_countries = array("swe", "deu", "dnk", "nld", "fin", "nor");
		return $klarna_countries;
	}

	/**
	 * @static
	 * @return array
	 */
	static function getDataFromEditPayment () {
		vmLanguage::loadJLang('com_virtuemart_shoppers', true);

		$kIndex = 'klarna_';
		$klarna['klarna_paymentmethod'] = vRequest::getVar($kIndex . 'paymentmethod');
		if ($klarna['klarna_paymentmethod'] == 'klarna_invoice') {
			$klarna_option = 'invoice';
		} elseif ($klarna['klarna_paymentmethod'] == 'klarna_partPayment') {
			$klarna_option = 'part';
		} elseif ($klarna['klarna_paymentmethod'] == 'klarna_speccamp') {
			$klarna_option = 'spec';
		} else {
			return NULL;

		}
		$prefix = $klarna_option . '_' . $kIndex;
		//Removes spaces, tabs, and other delimiters.
		$klarna['pno'] = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', vRequest::getVar($prefix . 'pnum', ''));
		$klarna['socialNumber'] = preg_replace('/[ \t\,\.\!\#\;\:\r\n\v\f]/', '', vRequest::getVar($prefix . 'socialNumber'));
		$klarna['phone'] = vRequest::getVar($prefix . 'phone');
		$klarna['email'] = vRequest::getVar($prefix . 'emailAddress');
		$klarna['street'] = vRequest::getVar($prefix . 'street');
		$klarna['house_no'] = vRequest::getVar($prefix . 'homenumber');
		$klarna['house_ext'] = vRequest::getVar($prefix . 'house_extension');
		$klarna['year_salary'] = vRequest::getVar($prefix . 'ysalary');
		$klarna['reference'] = vRequest::getVar($prefix . 'reference');
		$klarna['city'] = vRequest::getVar($prefix . 'city');
		$klarna['zip'] = vRequest::getVar($prefix . 'zipcode');
		$klarna['first_name'] = vRequest::getVar($prefix . 'firstName');
		$klarna['last_name'] = vRequest::getVar($prefix . 'lastName');
		$klarna['invoice_type'] = vRequest::getVar('klarna_invoice_type');
		$klarna['company_name'] = vRequest::getVar('klarna_company_name');
		$klarna['phone'] = vRequest::getVar($prefix . 'phone');
		$klarna['consent'] = vRequest::getVar($prefix . 'consent');
		$klarna['gender'] = vRequest::getVar($prefix . 'gender');
		switch ($klarna['gender']) {
			case KlarnaFlags::MALE :
				$klarna['title'] = vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MR');
				break;
			case KlarnaFlags::FEMALE:
				//$this->klarna_gender = KlarnaFlags::FEMALE;
				$klarna['title'] = vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MRS');
				break;
		}
		$klarna['birth_day'] = vRequest::getVar($prefix . 'birth_day', '');
		$klarna['birth_month'] = vRequest::getVar($prefix . 'birth_month', '');
		$klarna['birth_year'] = vRequest::getVar($prefix . 'birth_year', '');
		if (isset($klarna['birth_year']) and !empty($klarna['birth_year'])) {
			// due to the select list
			if ($klarna['birth_month'] != 0 and $klarna['birth_month'] != 0) {
				$klarna['birthday'] = $klarna['birth_year'] . "-" . $klarna['birth_month'] . "-" . $klarna['birth_day'];
				$klarna['pno_frombirthday'] = vRequest::getVar($prefix . 'birth_day') . vRequest::getVar($prefix . 'birth_month') . vRequest::getVar($prefix . 'birth_year');
				$klarna['birth_day'] = vRequest::getVar($prefix . 'birth_day') ;
				$klarna['birth_month'] = vRequest::getVar($prefix . 'birth_month') ;
				$klarna['birth_year'] = vRequest::getVar($prefix . 'birth_year') ;
			} else {
				$klarna['birthday'] = '';
			}
		} else {
			$klarna['birthday'] = '';
		}
		return $klarna;
	}

	/**
	 * @static
	 * @param $cData
	 * @param $order
	 * @return KlarnaAddr
	 */
	private static function getBilling ($cData, $order) {

		$bt = $order['BT'];
		$bill_country = shopFunctions::getCountryByID($bt['virtuemart_country_id'], 'country_2_code');

		//$cData = self::countryData($method, $country);
		$bill_street = $bt['address_1'];
		$bill_ext = "";
		$bill_number = "";
		if (strtolower($bill_country) == "de" || strtolower($bill_country) == "nl") {
			$splitAddress = array('', '', '');
			$splitAddress = self::splitAddress($bt['address_1']);
			$bill_street = $splitAddress[0];
			$bill_number = $splitAddress[1];
			switch ($bt['title']) {
				case vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MR'):
					//$this->klarna_gender = KlarnaFlags::MALE;
					break;
				case vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MISS'):
				case vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MRS'):
					//$this->klarna_gender = KlarnaFlags::FEMALE;
					break;
				default:
					//$this->klarna_gender = NULL;
					break;
			}
			if (strtolower($bill_country) == "nl") {
				$bill_ext = $splitAddress[2];
			}
		}
		$billing = new KlarnaAddr($bt['email'], $bt['phone_1'], @$bt['phone_2'], utf8_decode($bt['first_name']), utf8_decode($bt['last_name']), '', utf8_decode($bill_street), $bt['zip'], utf8_decode($bt['city']), $bill_country, $bill_number, $bill_ext);

		return $billing;
	}

	/**
	 * @static
	 * @param $method
	 * @param $order
	 * @param $klarna_pclass
	 * @return array|bool
	 * @throws Exception
	 */
	public static function addTransaction ($method, $order, $klarna_pclass) {

		if (!class_exists('KlarnaAddr')) {
			require(JPATH_VMKLARNAPLUGIN . DS . 'klarna' . DS . 'api' . DS . 'klarnaaddr.php');
		}
		$session = JFactory::getSession();
		$sessionKlarna = $session->get('Klarna', 0, 'vm');
		$sessionKlarnaData = (object)json_decode($sessionKlarna,true );

		if (!isset($sessionKlarnaData)) {
			throw new Exception("No klarna Session data set");
		}
		$klarnaData = $sessionKlarnaData->KLARNA_DATA;
		// let's put it back as an array
		$klarnaData=(array)$klarnaData;
		if (VMKLARNA_SHIPTO_SAME_AS_BILLTO) {
			$shipTo = $order['details']['BT'];
		} else {
			$shipTo = (!isset($order['details']['ST']) or empty($order['details']['ST']) or count($order['details']['ST']) == 0) ? $order['details']['BT'] : $order['details']['ST'];
		}

		$billTo = $order['details']['BT'];
		$country = shopFunctions::getCountrybyID($shipTo->virtuemart_country_id, 'country_3_code');
		$cData = self::countryData($method, $country);

		//$total_price_excl_vat = self::convertPrice($order['details']['BT']->order_subtotal, $cData['currency_code']);
		//$total_price_incl_vat = self::convertPrice($order['details']['BT']->order_subtotal + $order['details']['BT']->order_tax, $cData['currency_code'], $order['details']['BT']->order_currency);

		$mode = KlarnaHandler::getKlarnaMode($method, $cData['country_code_3']);
		$ssl = KlarnaHandler::getKlarnaSSL($mode);
		// Instantiate klarna object.
		$klarna = new Klarna_virtuemart();
		$klarna->config($cData['eid'], $cData['secret'], $cData['country_code'], $cData['language'], $cData['currency_code'], $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $ssl);

		// Sets order id's from other systems for the upcoming transaction.
		$klarna->setEstoreInfo($order['details']['BT']->order_number);

		// Fill the good list the we send to Klarna
		foreach ($order['items'] as $item) {

			if ($item->product_basePriceWithTax != 0.0) {
				if ($item->product_basePriceWithTax != $item->product_final_price) {
					$price = $item->product_basePriceWithTax;
				} else {
					$price = $item->product_final_price;
				}
			} else {
				if ($item->product_priceWithoutTax != $item->product_item_price) {
					$price = $item->product_item_price;
				} else {
					$price = $item->product_discountedPriceWithoutTax;
				}
			}

			$item_price = self::convertPrice($price, $order['details']['BT']->order_currency, $cData['currency_code']);

			$item_price = (double)(round($item_price, 2));
			$item_tax_percent = 0;
			foreach ($order['calc_rules'] as $calc_rule) {
				if ($calc_rule->virtuemart_order_item_id == $item->virtuemart_order_item_id AND $calc_rule->calc_kind == 'VatTax') {
					$item_tax_percent = $calc_rule->calc_value;
					break;
				}
			}
			//$item_discount_percent = (double)(round (abs (($item->product_subtotal_discount / $item->product_quantity) * 100 / $price), 2));
			$item_discount_percent = 0.0;
			$discount_tax_percent = 0.0;
			$klarna->addArticle($item->product_quantity, utf8_decode($item->order_item_sku), utf8_decode(strip_tags($item->order_item_name)), $item_price, (double)$item_tax_percent, $item_discount_percent, KlarnaFlags::INC_VAT);
			$discount_tax_percent = 0.0;
			$includeVat = KlarnaFlags::INC_VAT;
			if ($item->product_subtotal_discount != 0.0) {
				if ($item->product_subtotal_discount > 0.0) {
					$discount_tax_percent = $item_tax_percent;
					$includeVat = 0;
				}
				$name = utf8_decode(strip_tags($item->order_item_name)) . ' (' . vmText::_('VMPAYMENT_KLARNA_PRODUCTDISCOUNT') . ')';
				$discount = self::convertPrice(abs($item->product_subtotal_discount), $order['details']['BT']->order_currency, $cData['currency_code']);
				$discount = (double)(round(abs($discount), 2)) * -1;
				$klarna->addArticle(1, utf8_decode($item->order_item_sku), $name, $discount, (double)$discount_tax_percent, $item_discount_percent, $includeVat);
			}
		}
// this is not correct yet
		/*
				foreach($order['calc_rules'] as $rule){
					if ($rule->calc_kind == 'DBTaxRulesBill' or $rule->calc_kind == 'taxRulesBill' or $rule->calc_kind == 'DATaxRulesBill') {
						$klarna->addArticle (1, "", $rule->calc_rule_name, $rule->calc_amount, 0.0, 0.0, 0);

					}

				}
				*/

// Add shipping
		$shipment = self::convertPrice($order['details']['BT']->order_shipment + $order['details']['BT']->order_shipment_tax, $order['details']['BT']->order_currency, $cData['currency_code']);
		foreach ($order['calc_rules'] as $calc_rule) {
			if ($calc_rule->calc_kind == 'shipment') {
				$shipment_tax_percent = $calc_rule->calc_value;
				break;
			}
		}
		$klarna->addArticle(1, "shippingfee", vmText::_('VMPAYMENT_KLARNA_SHIPMENT'), ((double)(round(($shipment), 2))), round($shipment_tax_percent, 2), 0, KlarnaFlags::IS_SHIPMENT + KlarnaFlags::INC_VAT);


		// Add invoice fee
		if ($klarna_pclass == -1) { //Only for invoices!
			$payment_without_tax = self::convertPrice($order['details']['BT']->order_payment, $order['details']['BT']->order_currency, $cData['currency_code']);
			$payment_with_tax = self::convertPrice($order['details']['BT']->order_payment + $order['details']['BT']->order_payment_tax, $order['details']['BT']->order_currency, $cData['currency_code']);
			foreach ($order['calc_rules'] as $calc_rule) {
				if ($calc_rule->calc_kind == 'payment') {
					$payment_tax_percent = $calc_rule->calc_value;
					break;
				}
			}
			if ($payment_without_tax > 0) {
				//vmdebug('invoicefee', $payment, $payment_tax);
				$klarna->addArticle(1, "invoicefee", utf8_decode(vmText::_('VMPAYMENT_KLARNA_INVOICE_FEE_TITLE')), ((double)(round(($payment_with_tax), 2))), (double)round($payment_tax_percent, 2), 0, KlarnaFlags::IS_HANDLING + KlarnaFlags::INC_VAT);
			}
		}
		// Add coupon if there is any
		if (abs($order['details']['BT']->coupon_discount) > 0.0) {
			$coupon_discount = self::convertPrice(round($order['details']['BT']->coupon_discount), $order['details']['BT']->order_currency, $cData['currency_code']);
			$coupon_discount = (double)(round(abs($coupon_discount), 2)) * -1;
			//vmdebug('discount', $coupon_discount);
			$klarna->addArticle(1, 'discount', utf8_decode(vmText::_('VMPAYMENT_KLARNA_DISCOUNT')) . ' ' . utf8_decode($order['details']['BT']->coupon_code), $coupon_discount, 0, 0, KlarnaFlags::INC_VAT);
		}


		try {
			$klarna_shipping = new KlarnaAddr($order['details']['BT']->email, $shipTo->phone_1, isset($shipTo->phone_2) ? $shipTo->phone_2 : "", utf8_decode($shipTo->first_name), utf8_decode($shipTo->last_name), '', utf8_decode($shipTo->address_1), $shipTo->zip, utf8_decode($shipTo->city), utf8_decode($cData['country']), KlarnaHandler::setHouseNo(isset($shipTo->house_no) ? $shipTo->house_no : "", $cData['country_code_3']), KlarnaHandler::setAddress2($shipTo->address_2, $cData['country_code_3']));
		} catch (Exception $e) {
			VmInfo($e->getMessage());
			return FALSE;
		}

		$klarna_reference = ""; // what is that?
		if ($klarnaData['invoice_type'] == 'company') {
			$klarna_shipping->isCompany = TRUE;
			$klarna_shipping->setCompanyName($shipTo->company);
			$klarna_comment = $shipTo->first_name . ' ' . $shipTo->last_name; //$klarnaData['reference'];

			if ($klarna_shipping->getLastName() == "") {
				$klarna_shipping->setLastName("-");
			}
			if ($klarna_shipping->getFirstName() == "") {
				$klarna_shipping->setFirstName("-");
			}
		} else {
			$klarna_reference = "";
			$klarna_comment = "";
		}

		// Only allow billing and shipping to be the same for Germany and the Netherlands
		if (VMKLARNA_SHIPTO_SAME_AS_BILLTO) {
			$klarna_billing = $klarna_shipping;
		} else {
			$klarna_billing = self::getBilling($cData, $order);
		}

		$klarna_flags = KlarnaFlags::RETURN_OCR; // get ocr back from KO.

		$klarna->setComment($klarna_comment);
		$klarna->setReference($klarna_reference, "");
		$pno = self::getPNOfromSession($sessionKlarnaData->KLARNA_DATA, $country);
		try {
			$klarna->setAddress(KlarnaFlags::IS_SHIPPING, $klarna_shipping);
			$klarna->setAddress(KlarnaFlags::IS_BILLING, $klarna_billing);
			if (isset($klarnaData['year_salary'])) {
				$klarna->setIncomeInfo("'yearly_salary'", $klarnaData['year_salary']);
			}

			$result = $klarna->addTransaction($pno, ($klarna->getCountry() == KlarnaCountry::DE || $klarna->getCountry() == KlarnaCountry::NL) ? $klarnaData['gender'] : NULL, $klarna_flags, $klarna_pclass);
			$result['eid'] = $cData['eid'];
			$result['status_code'] = $result[2];
			$result['status_text'] = vmText::_('VMPAYMENT_KLARNA_ORDER_STATUS_TEXT_' . $result[2]);
			return $result; //return $result;
		} catch (Exception $e) {
			$result['status_code'] = KlarnaFlags::DENIED;
			$result['status_text'] = mb_convert_encoding($e->getMessage(), 'UTF-8', 'ISO-8859-1') . "  (#" . $e->getCode() . ")";
			return $result; //return $result;
			//self::redirectPaymentMethod('error', htmlentities($e->getMessage()) .  "  (#" . $e->getCode() . ")");
		}

	}

	private static function setAddress2 ($address2, $country) {

		if ($country == 'NLD') {
			return $address2;
		} else {
			return NULL;
		}
	}

	private static function setHouseNo ($houseNo, $country) {

		if (($country == 'DEU') or ($country == 'NLD')) {
			return $houseNo;
		} else {
			return NULL;
		}
	}


	/**
	 * Returns a collection of addresses that are connected to the
	 * supplied SSN
	 *
	 * @param <type> $pno The SSN of the user. This method is only available
	 * for swedish customers
	 * @return array
	 */
	public static function getAddresses ($pno, $settings, $method) {

		// Only available for sweden.
		$addresses = array();
		$klarna = new Klarna_virtuemart();
		$mode = KlarnaHandler::getKlarnaMode($method, $settings['country_code_3']);
		$klarna->config($settings['eid'], $settings['secret'], KlarnaCountry::SE, KlarnaLanguage::SV, KlarnaCurrency::SEK, $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), $mode);
		try {
			$addresses = $klarna->getAddresses($pno, NULL, KlarnaFlags::GA_GIVEN);
		} catch (Exception $e) {
			// the message is returned NOT in UTF-8
			$msg = mb_convert_encoding($e->getMessage(), 'UTF-8', 'ISO-8859-1');
			VmInfo($msg);
		}
		unset($klarna);
		return $addresses;
	}

	/**
	 * @static
	 * @param $method
	 * @return array
	 */
	public static function fetchAllPClasses ($method) {

		$message = '';
		$success = '';
		$results = array();

		$countries = self::getKlarnaCountries();

		$pc_type = KlarnaHandler::getKlarna_pc_type();
		if (empty($pc_type)) {
			return FALSE;
		} else {
			// delete the file directly
			if (file_exists($pc_type)) {
				unlink($pc_type);
			}
		}

		foreach ($countries as $country) {
			$active_country = "klarna_active_" . $country;
			if ($method->$active_country) {
				// country is CODE 3==> converting to 2 letter country
				//$country = self::convertCountryCode($method, $country);
				$lang = self::getLanguageForCountry($method, $country);
				$flagImg = JURI::root(TRUE) . '/administrator/components/com_virtuemart/assets/images/flag/' . strtolower($lang) . '.png';
				$flag = "<img src='" . $flagImg . "' />";
				try {
					$settings = self::getCountryData($method, $country);
					$klarna = new Klarna_virtuemart();
					$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], KlarnaHandler::getKlarnaMode($method, $settings['country_code_3']), VMKLARNA_PC_TYPE, $pc_type, TRUE);
					$klarna->fetchPClasses($country);
					$success .= shopFunctions::getCountryByID($settings['virtuemart_country_id']);
				} catch (Exception $e) {
					$message .= $flag . " " . shopFunctions::getCountryByID($settings['virtuemart_country_id']) . ": " . $e->getMessage() . ' Error Code #' . $e->getCode() . '</span></br>';
				}
			}
		}
		$results['msg'] = $message;
		$results['notice'] = $success;
		return $results;
		//echo $notice;
	}

	static function createKlarnaFolder () {

		$safePath = VmConfig::get('forSale_path', '');
		if ($safePath) {
			$exists = JFolder::exists($safePath . 'klarna');
			if (!$exists) {
				$created = JFolder::create($safePath . 'klarna');
				if ($created) {
					return TRUE;
				}
			} else {
				return TRUE;
			}
		}
		$uri = JFactory::getURI();
		$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
		VmError(vmText::sprintf('VMPAYMENT_KLARNA_CANNOT_STORE_CONFIG', '<a href="' . $link . '">' . $link . '</a>', vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH')));
		return FALSE;
	}

	/**
	 * Redirects user to payment method stage.
	 *
	 * @param <type> $type e.g. 'error', ...
	 * @param <type> $message
	 */
	public static function redirectPaymentMethod ($type = NULL, $message = NULL) {

		$log = utf8_encode($message);
		//Display the error.
		if (strlen($log) > 0) {
			if ($type === NULL) {
				$type = 'message';
			}
			$app = JFactory::getApplication();
			$app->enqueueMessage(vmText::_(urldecode($log)), $type);
		}
		//Redirect to previous page.
		$session = JFactory::getSession();
		$sessionKlarna = new stdClass();
		$sessionKlarna->klarna_error = addslashes($message);
		$session->set('Klarna', json_encode($sessionKlarna), 'vm');
		if (isset($_SESSION['klarna_paymentmethod'])) {
			$pid = $_SESSION['klarna_paymentmethod'];
			unset($_SESSION['klarna_paymentmethod']);
		}
		//$_SESSION['klarna_error'] = addslashes($message);
		$app = JFactory::getApplication();
		$app->enqueueMessage($message);
		$app->redirect(JRoute::_('index.php?option=com_virtuemart&view=cart', FALSE), vmText::_('COM_VIRTUEMART_CART_ORDERDONE_DATA_NOT_VALID'));
	}

	/**
	 *
	 * @param  <type> $address
	 * @return <type>
	 */
	public static function splitAddress ($address) {

		$numbers = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
		$characters = array(
			'-',
			'/',
			' ',
			'#',
			'.',
			'a',
			'b',
			'c',
			'd',
			'e',
			'f',
			'g',
			'h',
			'i',
			'j',
			'k',
			'l',
			'm',
			'n',
			'o',
			'p',
			'q',
			'r',
			's',
			't',
			'u',
			'v',
			'w',
			'x',
			'y',
			'z',
			'A',
			'B',
			'C',
			'D',
			'E',
			'F',
			'G',
			'H',
			'I',
			'J',
			'K',
			'L',
			'M',
			'N',
			'O',
			'P',
			'Q',
			'R',
			'S',
			'T',
			'U',
			'V',
			'W',
			'X',
			'Y',
			'Z'
		);
		$specialchars = array('-', '/', ' ', '#', '.');

		//Where do the numbers start? Allow for leading numbers
		$numpos = self::strpos_arr($address, $numbers, 2);
		//Get the streetname by splitting off the from the start of the numbers
		$streetname = substr($address, 0, $numpos);
		//Strip off spaces at the end
		$streetname = trim($streetname);

		//Get the housenumber+extension
		$numberpart = substr($address, $numpos);
		//and strip off spaces
		$numberpart = trim($numberpart);

		//Get the start position of the extension
		$extpos = self::strpos_arr($numberpart, $characters, 0);

		//See if there is one, if so
		if ($extpos != '') {
			//get the housenumber
			$housenumber = substr($numberpart, 0, $extpos);
			// and the extension
			$houseextension = substr($numberpart, $extpos);
			// and strip special characters from it
			$houseextension = str_replace($specialchars, '', $houseextension);
		} else {
			//Otherwise, we already have the housenumber
			$housenumber = $numberpart;
		}

		return array($streetname, $housenumber, $houseextension);
	}

	/**
	 *
	 * @param  <type> $haystack
	 * @param  <type> $needle
	 * @param  <type> $where
	 * @return <type>
	 */
	private static function strpos_arr ($haystack, $needle, $where) {

		$defpos = 10000;
		if (!is_array($needle)) {
			$needle = array($needle);
		}
		foreach ($needle as $what) {
			if (($pos = strpos($haystack, $what, $where)) !== FALSE) {
				if ($pos < $defpos) {
					$defpos = $pos;
				}
			}
		}
		return $defpos;
	}


	/**
	 * gets Eid and Secret for activated countries.
	 */
	public static function getEidSecretArray ($method) {

		$eid_array = array();
		if (isset($method->klarna_merchantid_swe) && $method->klarna_merchantid_swe != "" && $method->klarna_sharedsecret_swe != "") {
			$eid_array['swe']['secret'] = $method->klarna_sharedsecret_swe;
			$eid_array['swe']['eid'] = (int)$method->klarna_merchantid_swe;
		}

		if (isset($method->klarna_merchantid_nor) && $method->klarna_merchantid_nor != "" && $method->klarna_sharedsecret_nor != "") {
			$eid_array['nor']['secret'] = $method->klarna_sharedsecret_nor;
			$eid_array['nor']['eid'] = $method->klarna_merchantid_nor;
		}

		if (isset($method->klarna_merchantid_deu) && $method->klarna_merchantid_deu != "" && $method->klarna_sharedsecret_deu != "") {
			$eid_array['deu']['secret'] = $method->klarna_sharedsecret_deu;
			$eid_array['deu']['eid'] = $method->klarna_merchantid_deu;
		}

		if (isset($method->klarna_nld_merchantid) && $method->klarna_nld_merchantid != "" && $method->klarna_sharedsecret_nld != "") {
			$eid_array['nld']['secret'] = $method->klarna_sharedsecret_nld;
			$eid_array['nld']['eid'] = $method->klarna_nld_merchantid;
		}

		if (isset($method->klarna_merchantid_dnk) && $method->klarna_merchantid_dnk != "" && $method->klarna_sharedsecret_dnk != "") {
			$eid_array['dnk']['secret'] = $method->klarna_sharedsecret_dnk;
			$eid_array['dnk']['eid'] = $method->klarna_merchantid_dnk;
		}

		if (isset($method->klarna_merchantid_fin) && $method->klarna_merchantid_fin != "" && $method->klarna_sharedsecret_fin != "") {
			$eid_array['fin']['secret'] = $method->klarna_sharedsecret_fin;
			$eid_array['fin']['eid'] = $method->klarna_merchantid_fin;
		}

		return $eid_array;
	}

	/**
	 * @param     $obj
	 * @param int $level
	 * @return array
	 */
	public function xmlToArray ($obj, $level = 0) {

		$aResult = array();

		if (!is_object($obj)) {
			return $aResult;
		}

		$aChild = (array)$obj;

		if (sizeof($aChild) > 1) {
			foreach ($aChild as $sName => $mValue) {
				if ($sName == "@attributes") {
					$sName = "_attributes";
				}

				if (is_array($mValue)) {
					foreach ($mValue as $ee => $ff) {
						if (!is_object($ff)) {
							$aResult[$sName][$ee] = $ff;
						} else {
							if (get_class($ff) == 'SimpleXMLElement') {
								$aResult[$sName][$ee] = self::xmlToArray($ff, $level + 1);
							}
						}
					}
				} else {
					if (!is_object($mValue)) {
						$aResult[$sName] = $mValue;
					} else {
						if (get_class($mValue) == 'SimpleXMLElement') {
							$aResult[$sName] = self::xmlToArray($mValue, $level + 1);
						}
					}
				}
			}
		} else {
			if (sizeof($aChild) > 0) {
				foreach ($aChild as $sName => $mValue) {
					if ($sName == "@attributes") {
						$sName = "_attributes";
					}

					if (!is_array($mValue) && !is_object($mValue)) {
						$aResult[$sName] = $mValue;
					} else {
						if (is_object($mValue)) {
							$aResult[$sName] = self::xmlToArray($mValue, $level + 1);
						} else {
							foreach ($mValue as $sNameTwo => $sValueTwo) {
								if (!is_object($sValueTwo)) {
									$aResult[$obj->getName()][$sNameTwo] = $sValueTwo;
								} else {
									if (get_class($sValueTwo) == 'SimpleXMLElement') {
										$aResult[$obj->getName()][$sNameTwo] = self::xmlToArray($sValueTwo, $level + 1);
									}
								}
							}
						}
					}
				}
			}
		}

		return $aResult;
	}

	/**
	 * @static
	 * @param $settings
	 * @param $mode
	 * @param $klarna_invoice_no
	 * @return string
	 */
	public static function checkOrderStatus ($settings, $mode, $orderNumber) {

		try {
			$klarna = new Klarna_virtuemart();
			$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), TRUE);
			vmdebug('checkOrderStatus', $klarna);
			$os = $klarna->checkOrderStatus($orderNumber, 1);
		} catch (Exception $e) {
			$msg = $e->getMessage() . ' #' . $e->getCode() . ' </br>';
			VmError($msg);
			return $msg;
		}
		//$os = self::getStatusForCode($os);
		return $os;
	}


	/**
	 * Return pclasses stored in json file.
	 */
	public static function getPClasses ($type = NULL, $mode, $settings) {

		//$settings = self::countryData($method, $country);
		try {
			$klarna = new Klarna_virtuemart();
			$klarna->config($settings['eid'], $settings['secret'], $settings['country'], $settings['language'], $settings['currency'], $mode, VMKLARNA_PC_TYPE, KlarnaHandler::getKlarna_pc_type(), TRUE);
			return $klarna->getPClasses($type);
		} catch (Exception $e) {

		}
	}

	static function getCheapestPclass ($kCheckout, &$cheapest, &$minimum) {

		$pclasses = $kCheckout->aPClasses;
		if (empty($pclasses)) {
			$minimum = 0;
			return;
		}
		$cheapest = 0;
		$minimum = '';
		foreach ($pclasses as $pclass) {
			if ($cheapest == 0 || $pclass['monthlyCost'] < $cheapest) {
				$cheapest = $pclass['monthlyCost'];
			}
			if ($pclass['pclass']->getMinAmount() < $minimum || $minimum === '') {
				$minimum = $pclass['pclass']->getMinAmount();
			}
		}

	}

	/**
	 * @param string $fld
	 * @return string
	 */
	public static function getVendorCountry ($fld = 'country_3_code') {

		if (!class_exists('VirtueMartModelVendor')) {
			JLoader::import('vendor', JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_virtuemart' . DS . 'models');
		}
		$virtuemart_vendor_id = 1;
		$model = VmModel::getModel('vendor');
		$vendorAddress = $model->getVendorAdressBT($virtuemart_vendor_id);
		$vendor_country = ShopFunctions::getCountryByID($vendorAddress->virtuemart_country_id, $fld);
		return $vendor_country;
	}

	/**
	 * @param $klarnaError
	 * @param $klarnaOption
	 * @return bool
	 */
	function getKlarnaError (&$klarnaError, &$klarnaOption) {

		$session = JFactory::getSession();
		$sessionKlarna = $session->get('Klarna', 0, 'vm');
		if (empty($sessionKlarna)) {
			return FALSE;
		}
		$sessionKlarnaData = json_decode($sessionKlarna );

		if (isset($sessionKlarnaData->klarna_error) and isset($sessionKlarnaData->klarna_paymentmethod)) {
			$klarnaError = $sessionKlarnaData->klarna_error; // it is a message to display
			$klarnaOption = $sessionKlarnaData->klarna_paymentmethod;
			return TRUE;
		} else {
			return FALSE;
		}

		return FALSE;
	}

	function setKlarnaErrorInSession ($msg, $option) {

		$session = JFactory::getSession();
		$sessionKlarna = $session->get('Klarna', 0, 'vm');
		if (empty($sessionKlarna)) {
			$sessionKlarnaData = new stdClass();
		} else {
			$sessionKlarnaData =(object) json_decode($sessionKlarna, true );
		}
		$sessionKlarnaData->klarna_error = $msg;
		//$sessionKlarnaData->klarna_option = $option;
		$session->set('Klarna', json_encode($sessionKlarnaData), 'vm');
	}

	/**
	 *
	 */
	function clearKlarnaError () {

		$session = JFactory::getSession();
		$sessionKlarna = $session->get('Klarna', 0, 'vm');
		if ($sessionKlarna) {
			$sessionKlarnaData = json_decode($sessionKlarna );
			if (isset($sessionKlarnaData->klarna_error)) {
				unset($sessionKlarnaData->klarna_error);
				//unset($sessionKlarnaData->klarna_option);
				$session->set('Klarna', json_encode($sessionKlarnaData), 'vm');
			}
		}
	}

	/**
	 * @static
	 * @param $method
	 * @return int
	 */
	static function getKlarnaMode ($method, $country) {
		//return Klarna::BETA;
		// It is the VM specific store ID to test
		$merchant_id = strtolower('klarna_merchantid_' . $country);
		if ($method->$merchant_id == VMPAYMENT_KLARNA_MERCHANT_ID_VM or $method->$merchant_id == VMPAYMENT_KLARNACHECKOUT_MERCHANT_ID_VM or $method->$merchant_id == VMPAYMENT_KLARNA_MERCHANT_ID_DEMO) {
			return Klarna::BETA;
		} else {
			return Klarna::LIVE;
		}
	}

	/**
	 * @static
	 * @param $mode
	 * @return bool
	 */
	static function getKlarnaSSL ($mode) {

		return ($mode == Klarna::LIVE);
	}

	/**
	 * @static
	 * @param        $price
	 * @param string $toCurrency
	 * @return float
	 */
	static function convertPrice ($price, $fromCurrency, $toCurrency = '', $cartPricesCurrency = '') {

		if (!(is_int($toCurrency) or is_numeric($toCurrency)) && !empty($toCurrency)) {
			$toCurrency = ShopFunctions::getCurrencyIDByName($toCurrency);
		}
		if ($fromCurrency == $toCurrency) {
			return $price;
		}
		// product prices or total in cart is always in vendor currency
		$priceInNewCurrency = vmPSPlugin::getAmountInCurrency($price, $toCurrency);

		// set back the currency display
		if (empty($cartPricesCurrency)) {
			$cartPricesCurrency = $fromCurrency;
		}
		$cd = CurrencyDisplay::getInstance($cartPricesCurrency);
		vmDebug('convertPrice', $price, $toCurrency, $fromCurrency, $cartPricesCurrency, $priceInNewCurrency);
		return $priceInNewCurrency['value'];
	}

	/*
		* if client has not given address then get cdata depending on the currency
		* otherwise get info depending on the country
		*/

	/**
	 * @static
	 * @param $method
	 * @param $address
	 * @return array|null
	 */
	static function getcData ($method, $address) {

		if (!isset($address['virtuemart_country_id'])) {
			$vendor_country = KlarnaHandler::getVendorCountry();
			$cData = self::countryData($method, $vendor_country);
		} else {
			$cart_country_code_3 = ShopFunctions::getCountryByID($address['virtuemart_country_id'], 'country_3_code');
			// the user gave an address, get info according to his country
			$cData = self::countryData($method, $cart_country_code_3);
		}
		return $cData;
	}

	/**
	 * @static
	 * @return null|string
	 */
	static function getKlarna_pc_type () {

		$safePath = VmConfig::get('forSale_path', '');
		if ($safePath) {
			return $safePath . "klarna/klarna.json";
		} else {
			$uri = JFactory::getURI();
			$link = $uri->root() . 'administrator/index.php?option=com_virtuemart&view=config';
			VmError(vmText::sprintf('VMPAYMENT_KLARNA_CANNOT_STORE_CONFIG', '<a href="' . $link . '">' . $link . '</a>', vmText::_('COM_VIRTUEMART_ADMIN_CFG_MEDIA_FORSALE_PATH')));
			return NULL;
		}
	}


	/**
	 * Sweden: yymmdd-nnnn, it can be sent with or without dash "-" or with or without the two first numbers in the year.
	 * Finland: ddmmyy-nnnn
	 * Denmark: ddmmyynnnn
	 * Norway: ddmmyynnnnn
	 * Germany: ddmmyyyy
	 * Netherlands: ddmmyyyy
	 *
	 * @static
	 * @param $billTo
	 * @param $country
	 * @return string
	 */
	static function getPNOfromSession ($sessionData, $country) {

		if (($country == "NLD" || $country == "DEU")) {
			$pno = $sessionData['pno_frombirthday'];
		} else {
			$pno = $sessionData['socialNumber'];
		}

		return $pno;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	static function checkDataFromEditPayment ($data, $country3) {

		if (!class_exists('VirtueMartModelUserfields')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'userfields.php');
		}
		$errors = array();

		/*
		if ($country3 == "DEU") {
			$consent = vRequest::getVar ('klarna_consent');
			if ($consent != 'on') {
				$errors = vmText::_ ('VMPAYMENT_KLARNA_NO_CONSENT');
			}
		}

		// todo later
		$userFieldsModel = VmModel::getModel ('userfields');

		$userFields = $userFieldsModel->getUserFields (
			'account'
			, array('required'   => FALSE,
			        'delimiters' => TRUE,
			        'captcha'    => TRUE,
			        'system'     => FALSE)
			, array('delimiter_userinfo', 'name', 'username', 'password', 'password2', 'address_type_name', 'address_type', 'user_is_vendor', 'agreed'));

		$required_shopperfields_vm = Klarnahandler::getKlarnaVMGenericShopperFields (FALSE);
		$required_shopperfields_bycountry = KlarnaHandler::getKlarnaSpecificShopperFields ();

		$required_shopperfields = array_merge ($required_shopperfields_vm, $required_shopperfields_bycountry[$country3]);

		foreach ($userFields as $userField) {
			if (in_array ($userField->name, $required_shopperfields)) {
				if (empty($data[$userField->name])) {
					$errors[] = vmText::_($userField->title);
				}
			}
		}

*/
		// Quick and durty .. but it works
		$kIndex = "klarna_";
		if ($country3 == "SWE") {
			if (vRequest::getVar('klarna_invoice_type') == 'company') {
				if (strlen(trim((string)vRequest::getVar('klarna_company_name'))) == 0) {
					$errors[] = 'VMPAYMENT_KLARNA_COMPANY_NAME';
				}
			} else {
				if (!KlarnaEncoding::checkPNO($data['socialNumber'], KlarnaEncoding::PNO_SE)) {
					$errors[] = 'VMPAYMENT_KLARNA_PERSONALORORGANISATIO_NUMBER';
				}
			}
		} else {
			if ($data['phone'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_PHONE_NUMBER';
			}
			if ($data['street'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_STREET_ADRESS';
			}
			if ($data['first_name'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_FIRST_NAME';
			}
			if ($data['last_name'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_LAST_NAME';
			}
			if ($data['city'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_ADDRESS_CITY';
			}
			if ($data['zip'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_ADDRESS_ZIP';
			}
		}
		// German and dutch
		if ($country3 == "NLD" || $country3 == "DEU") {
			if ($data['street'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_STREET_ADRESS';
			}
			if ($data['house_no'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_ADDRESS_HOMENUMBER';
			}
			if ($country3 == "DEU") {
				if ($data['consent'] != 'on') {
					$errors[] = 'VMPAYMENT_KLARNA_NO_CONSENT';
				}
			}

			if ($data['pno_frombirthday'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_PERSONALORORGANISATIO_NUMBER';
			}
			if ($data['gender'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_SEX';
			}
		}
		// General
		/* the email is not in the payment form
		  if ($data['emailAddress'] == '') {
			  $errors[] = 'VMPAYMENT_KLARNA_EMAIL';
		  }
  */
		// Norwegian, Danish and Finnish
		if (($country3 == "NOR") || ($country3 == "DNK") || $country3 == "FIN") {
			if ($data['socialNumber'] == '') {
				$errors[] = 'VMPAYMENT_KLARNA_PERSONALORORGANISATIO_NUMBER';
			}
		}

		if (!empty($errors)) {
			$msg = vmText::_('VMPAYMENT_KLARNA_ERROR_TITLE_2');
			foreach ($errors as $error) {
				$msg .= "<li>" . vmText::_($error) . "</li>";
			}
			$option = NULL;
			self::setKlarnaErrorInSession($msg, $option);

			return $msg;
		}
		return NULL;

	}

	/**
	 * @static
	 * @return array
	 */
	static function getKlarnaSpecificShopperFields () {

		return array(
			"SWE" => array("email"),
			"DNK" => array(), // should not be given to the shopper  year_salary
			"NOR" => array(),
			"FIN" => array(),
			"NLD" => array("address_2", "house_no"),
			"DEU" => array("house_no")
		);

	}

	/**
	 * @return array
	 */
	static function getKlarnaVMGenericShopperFields ($all = TRUE) {

		$required = array("first_name", "last_name", "address_1", "city", "zip", "phone_1", "virtuemart_country_id");
		if ($all) {
			$required = array_merge($required, array("company"));
		}
		return $required;

	}

	static function getKlarnaShopperFieldsType () {

		return array(
			"socialNumber" => "text",
			"email"        => "email",
			"birthday"     => "date",
			"address_2"    => "text",
			"house_no"     => "text",
		);
	}

	/**
	 *
	 */
	function getByFieds () {

		$required_shopperfields_byfields = array(
			"socialNumber" => array("SWE", "DNK", "NOR", "FIN"),
			"year_salary"  => array("DNK"),
			"address_2",
			"birthday"     => array("DEU", "NLD"),
			"house_no"     => array("NLD"),
			"email"        => array("SWE")
		);
	}

	/**
	 * Get the shipToAddress which might differ from default address.
	 * From VM shopperFields to Klarna Fields
	 */
	public static function getShipToAddress ($cart) {

		//vmdebug('getShipToAddress',$cart);
		if (VMKLARNA_SHIPTO_SAME_AS_BILLTO) {
			$shipTo = $cart->BT;
		} else {
			$shipTo = (($cart->ST == 0 or empty($cart->ST)) ? $cart->BT : $cart->ST);
		}
		return self::getKlarnaFieldsFromVmShopperFields($shipTo, $cart->BT['email']);

	}

	/**
	 * @static
	 * @param $from
	 * @param $from_email
	 * @return array
	 */
	static function getKlarnaFieldsFromVmShopperFields ($from, $from_email) {

		$klarnaFields = array();
		switch ($from['title']) {
			case vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MR'):
				$klarnaFields['gender'] = KlarnaFlags::MALE;
				break;
			case vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MISS'):
			case vmText::_('COM_VIRTUEMART_SHOPPER_TITLE_MRS'):
				$klarnaFields['gender'] = KlarnaFlags::FEMALE;
				break;
			default:
				$klarnaFields['gender'] = NULL;
				break;
		}
		$country_code_3 = ShopFunctions::getCountryByID($from['virtuemart_country_id'], 'country_3_code');
		$klarnaFields['email'] = $from_email;
		$klarnaFields['country'] = @ShopFunctions::getCountryByID(@$from['virtuemart_country_id'], 'country_3_code');
		$klarnaFields['socialNumber'] = @$from['socialNumber'];
		$klarnaFields['houseNr'] = @$from['house_no'];
		$klarnaFields['houseExt'] = @$from['address_2'];
		$klarnaFields['first_name'] = @$from['first_name'];
		if ($country_code_3 == 'NLD') {
			$klarnaFields['last_name'] = @$from['middle_name'] . " " . @$from['last_name'];
		} else {
			$klarnaFields['last_name'] = @$from['last_name'];
		}

		$klarnaFields['reference'] = $from['first_name'] . ' ' . $from['last_name'];
		$klarnaFields['company_name'] = @$from['company_name'];
		$klarnaFields['phone'] = @$from['phone_1'];
		$klarnaFields['street'] = @$from['address_1'];
		$klarnaFields['city'] = @$from['city'];
		$klarnaFields['country'] = $country_code_3;
		$klarnaFields['state'] = @$from['state'];
		$klarnaFields['zip'] = @$from['zip'];
		$klarnaFields['birthday'] = @$from['birthday'];
		if (isset($from['birthday']) and !empty($from['birthday'])) {
			$date = explode("-", $from['birthday']);
			if (is_array($date)) {
				$klarnaFields['birth_year'] = $date['0'];
				$klarnaFields['birth_month'] = $date['1'];
				$klarnaFields['birth_day'] = $date['2'];
			}
		}
		return $klarnaFields;
	}

	function checkNLpriceCondition ($price) {
		//  Since 12/09/12: merchants can sell goods with Klarna Invoice up to thousands of euros.

		if ($price > 250) {
			// We can't show our payment options for Dutch customers
			// if price exceeds 250 euro. Will be replaced with ILT in
			// the future.
			return FALSE;
		}
		return TRUE;

	}

	function checkPartNLpriceCondition ($cart) {
//  Since 12/09/12: merchants can sell goods with Klarna Invoice up to thousands of euros.
		// convert price in euro
		//$euro_currency_id = ShopFunctions::getCurrencyByName( 'EUR');
		$price = KlarnaHandler::convertPrice($cart->cartPrices['billTotal'], $cart->pricesCurrency, 'EUR', $cart->pricesCurrency);
		return self::checkNLpriceCondition($price);
	}

	function checkPartpriceCondition ($cData, $cart) {
//  Since 12/09/12: merchants can sell goods with Klarna Invoice up to thousands of euros.
		// convert price in euro
		//$euro_currency_id = ShopFunctions::getCurrencyByName( 'EUR');
		$amount = KlarnaHandler::convertPrice($cart->cartPrices['billTotal'], $cart->pricesCurrency, 'EUR', $cart->pricesCurrency);

		if ($amount <= $cData['min_amount'] AND !empty($cData['min_amount'])) {
			return FALSE;
		}


		return true;

	}
}

