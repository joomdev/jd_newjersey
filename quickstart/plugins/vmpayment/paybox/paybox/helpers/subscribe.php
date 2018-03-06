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
class PayboxHelperPayboxSubscribe extends PayboxHelperPaybox {

	function __construct ($method, $plugin, $plugin_name) {
		parent::__construct($method, $plugin, $plugin_name);

	}

	function getOrderHistory ($paybox_data, $order, $payments) {
		$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
		$order_history['comments'] = vmText::sprintf('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_STATUS_CONFIRMED_RECURRING', $amountInCurrency['display'], $order['details']['BT']->order_number);

		$amountInCurrency = vmPSPlugin::getAmountInCurrency($paybox_data['M'] * 0.01, $order['details']['BT']->order_currency);
		$order_history['comments'] .= "<br />" . vmText::sprintf('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_STATUS_CONFIRMED_RECURRING_2', $amountInCurrency['display']);

		$order_history['comments'] .= "<br />" . vmText::_('VMPAYMENT_'.$this->plugin_name.'_RESPONSE_S') . ' ' . $paybox_data['S'];
		$subscribe_comment = '';

		$order_history['customer_notified'] = true;
		$order_history['comments'] .= $subscribe_comment;
		$order_history['recurring'] = $subscribe_comment;
		$order_history['order_status'] = $this->_method->status_success_subscribe;
		return $order_history;


	}

	function getValueBE_R ($value) {
		return $this->getOrderNumber($value);
	}

	function getOrderNumber ($pbx_cmd) {
		//  [PBX_CMD] => c07a01504PBX_2MONT0000000500PBX_FREQ02PBX_NBPAIE10PBX_QUAND02PBX_DELAIS00
		// ASSUMES that it is the first one sent
		// TODO changes
		$pos = strpos($pbx_cmd, 'PBX_FREQ');
		if ($pos === false) {
			return $pbx_cmd;
		}
		return substr($pbx_cmd, 0, $pos);

	}

	function getExtraPluginNameInfo () {
		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart();
		//if (!isset($cart->cartPrices)) {
			$cart->getCartPrices();
		//}
		$pbxTotalVendorCurrency = $this->getPbxAmount($cart->cartPrices['salesPrice']);
		$subscribe = $this->getSubscribeProducts($cart, $pbxTotalVendorCurrency);
		$extraInfo = false;
		if (!empty($subscribe)) {
			$extraInfo['subscribe'] = true;
			$amount2montInCurrency = vmPSPlugin::getAmountInCurrency($subscribe['PBX_2MONT'] * 0.01, $this->_method->payment_currency);
			$amount1montInCurrency = vmPSPlugin::getAmountInCurrency( $subscribe['PBX_TOTAL'] * 0.01, $this->_method->payment_currency);
			$extraInfo['subscribe_2mont']  = $amount2montInCurrency['display'];
			$extraInfo['subscribe_1mont']  = $amount1montInCurrency['display'];
			$extraInfo['subscribe_nbpaie'] = $subscribe['PBX_NBPAIE'];
			$extraInfo['subscribe_freq'] = $subscribe['PBX_FREQ'];
			$extraInfo['subscribe_quand'] = $this->_method->subscribe_quand;
			$extraInfo['subscribe_delais'] = $this->_method->subscribe_delais;
		}
		return $extraInfo;

	}

	function getSubscribePayments ($cart, $pbxTotalVendorCurrency) {
		$subscribe_data = false;
		$subscribe = $this->getSubscribeProducts($cart, $pbxTotalVendorCurrency);
		if (!empty($subscribe)) {
			$subscribe['PBX_2MONT'] = str_pad($subscribe['PBX_2MONT'], 10, "0", STR_PAD_LEFT) ;
			$subscribe['PBX_NBPAIE'] = str_pad($subscribe['PBX_NBPAIE'], 2, "0", STR_PAD_LEFT);
			$subscribe['PBX_FREQ'] = str_pad($subscribe['PBX_FREQ'], 2, "0", STR_PAD_LEFT);
			$subscribe['PBX_QUAND'] = str_pad($this->_method->subscribe_quand, 2, "0", STR_PAD_LEFT);
			$subscribe['PBX_DELAIS'] = str_pad($this->_method->subscribe_delais, 3, "0", STR_PAD_LEFT);
			$subscribe_data["PBX_TOTAL"] = $subscribe["PBX_TOTAL"];
			unset($subscribe["PBX_TOTAL"]);
			$subscribe_cmd = '';
			foreach ($subscribe as $key => $value) {
				$subscribe_cmd .= $key . $value;
			 }
			$subscribe_data['PBX_CMD'] =$subscribe_cmd;
		}

		return $subscribe_data;
	}

	function getSubscribeProducts (VirtueMartCart $cart, $pbxTotalVendorCurrency) {

		if ($this->_method->subscribe_customfield == 0) {
			return false;
		}

		// get id of parent custom field
		$paybox_parent_field = $this->_method->subscribe_customfield;
		vmdebug(''.$this->plugin_name.' getSubscriptionData paybox_parent_field', $paybox_parent_field);
		// amount in base currency
		$subscribe = array();
		$pbx_freqs = array();
		$pbx_nbpaies = array();

		$products = $cart->products;
		$usbscribe_pbx_2mont = 0;
		// go through basket prods
		foreach ($products as $key => $product) {
			$product_custom_fields = $this->getProdCustomFields($product->virtuemart_product_id);
			vmdebug(''.$this->plugin_name.' getSubscriptionData getProdCustomFields', $product_custom_fields);
			 $pbx_2mont = 0;
			$paybox_parent_field_found = false;
			foreach ($product_custom_fields as $product_custom_field) {
				if ($product_custom_field->custom_parent_id == $paybox_parent_field) {
					if ($product_custom_field->custom_title == 'PBX_FREQ') {
						$pbx_freqs[] = $product_custom_field->custom_value;
					}
					if ($product_custom_field->custom_title == 'PBX_NBPAIE') {
						$pbx_nbpaies[] = $product_custom_field->custom_value;
					}
					if ($product_custom_field->custom_title == 'PBX_2MONT') {
						$pbx_2mont = $product_custom_field->custom_value;
					}
					$paybox_parent_field_found = true;
				}
			}

			if ($paybox_parent_field_found) {
				vmdebug(''.$this->plugin_name.' getSubscribeProducts $paybox_parent_field_found', $pbx_freqs, $pbx_nbpaies);
				$pbx_2monts = $pbx_2mont * $product->quantity;
				$usbscribe_pbx_2mont += $this->getPbxAmount($pbx_2monts);

			}
			vmdebug(''.$this->plugin_name.' getSubscriptionData BY PRODUCT', $subscribe);
		}
		if (!empty($pbx_freqs) and !empty($pbx_freqs)) {
			$pbx_freq = array_unique($pbx_freqs); // Nombre de prélèvements
			$pbx_nbpaie = array_unique($pbx_nbpaies); // Fréquence des prélèvements en mois.
			if (count($pbx_freq) > 1 or count($pbx_nbpaie) > 1) {
				// Fréquence des prélèvements en mois
				if (count($pbx_freq)) {
					vmInfo('VMPAYMENT_'.$this->plugin_name.'_ERROR_PBX_FREQ');
				}
				// Nombre de prélèvements (0 = toujours).
				if (count($pbx_nbpaie)) {
					vmInfo('VMPAYMENT_'.$this->plugin_name.'_ERROR_PBX_NBPAIE');
				}
				vmInfo('VMPAYMENT_'.$this->plugin_name.'_ERROR_SUBCRIBE');

				return FALSE;
			}


			if ($usbscribe_pbx_2mont) {
				$subscribe['PBX_FREQ'] = $pbx_freq[0];
				$subscribe['PBX_NBPAIE'] = $pbx_nbpaie[0];
				// Montant total de l’achat en centimes sans virgule ni point.
				vmdebug(''.$this->plugin_name.' getSubscriptionData TOTAL', $subscribe);
				$subscribe['PBX_TOTAL'] = $pbxTotalVendorCurrency - ($usbscribe_pbx_2mont * ($subscribe['PBX_NBPAIE']-1));
				$subscribe['PBX_2MONT'] = str_pad($usbscribe_pbx_2mont, 10, "0", STR_PAD_LEFT);
			}
		}


		return $subscribe;
	}

	function getProdCustomFields ($virtuemart_product_id) {
		$product = new stdClass();
		$product->virtuemart_product_id = $virtuemart_product_id;
		$customfields = VmModel::getModel('Customfields');
		$product_customfields = $customfields->getProductCustomsField($product);
		return $product_customfields;
	}

	function getProductAmount ($productPricesUnformatted) {
		if ($productPricesUnformatted['salesPriceWithDiscount']) {
			return $productPricesUnformatted['salesPriceWithDiscount'];
		} else {
			return $productPricesUnformatted['salesPrice'];
		}
	}
}