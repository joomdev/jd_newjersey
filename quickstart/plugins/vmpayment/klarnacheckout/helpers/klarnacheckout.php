<?php
/**
 *
 * KlarnaCheckout payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id:$
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

class  KlarnaCheckoutHelperKlarnaCheckout {
	function __construct($method, $country_code_3, $currency_code_3) {
		$this->_currentMethod = $method;
		$this->country_code_3 = $country_code_3;
		$this->currency_code_3 = $currency_code_3;
	}


	function getVatTaxProduct($vatTax) {
		$countRules = count($vatTax);
		if ($countRules == 0) {
			return 0;
		}
		if ($countRules > 1) {
			$this->KlarnacheckoutError('KlarnaCheckout: More then one VATax for the product:' . $countRules);
		}
		$tax = current($vatTax);
		if ($tax[2] != "+%") {
			$this->KlarnacheckoutError('KlarnaCheckout: expecting math operation to be +% but is ' . $tax[2]);
		}
		return $tax[1];

	}


	function getTaxShipment($shipment_calc_id) {
		// TO DO add shipmentTaxRate in the cart
		// assuming there is only one rule +%
		//-1 = no rules

		if (count($shipment_calc_id) > 1) {
			$this->KlarnacheckoutError('There is more then one rule for the shipment tax id.Please check your shipment tax configuration');
			//$this->debugLog(var_export($cart->cartPrices['shipment_calc_id'], true), 'getTaxShipment', 'debug');
			return;
		}
		if (!isset($shipment_calc_id[0])) {
			return 0;
		}
		$db = JFactory::getDBO();
		$q = 'SELECT * FROM #__virtuemart_calcs WHERE `virtuemart_calc_id`="' . $shipment_calc_id[0] . '" ';

		$db->setQuery($q);
		$taxrule = $db->loadObject();
		if ($taxrule->calc_value_mathop != "+%") {
			$this->KlarnacheckoutError('KlarnaCheckout getTaxShipment: expecting math operation to be +% but is ' . $taxrule->calc_value_mathop);
			//$this->debugLog(var_export($taxrule, true), 'getTaxShipment', 'debug');
			//$this->debugLog($q, 'getTaxShipment query', 'debug');
			return;
		}
		return $taxrule->calc_value * 100;



	}

	function getTermsURI($vendorId) {
		if (empty($this->_currentMethod->terms_uri)) {
			return JURI::root() . 'index.php?option=com_virtuemart&view=vendor&layout=tos&virtuemart_vendor_id=' . $vendorId . '&lang=' . vRequest::getCmd('lang', '');
		} else {
			return $this->_currentMethod->terms_uri;
		}

	}

	function acknowledge($klarna_checkout_order) {
	}

	function checkoutOrderManagement($klarna_checkout_connector, $klarna_checkout_uri) {
		return NULL;
	}

	function KlarnacheckoutError($admin_msg, $public_msg = '') {
		if ($this->_currentMethod->debug) {
			$public_msg = $admin_msg;
		}
		vmError($admin_msg, $public_msg);
	}
}