<?php
/**
 *
 * Klikandpay payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id$
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
class KlikandpayHelperKlikandpaySubscribe extends KlikandpayHelperKlikandpay {

	function __construct($method, $paypalPlugin) {
		parent::__construct($method, $paypalPlugin);

	}

	/**
	 * @param $klikandpay_data
	 * @param $order
	 * @param $payments
	 * @return mixed
	 */
	function getOrderHistory($klikandpay_data, $order, $payments) {
		$subscribe_comment = '';
		$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
		$order_history['comments'] = vmText::sprintf('VMPAYMENT_KLIKANDPAY_PAYMENT_STATUS_CONFIRMED', $amountInCurrency['display'], $order['details']['BT']->order_number);

		$amountInCurrency = vmPSPlugin::getAmountInCurrency($klikandpay_data['MONTANTXKP'], $order['details']['BT']->order_currency);
		if (isset($klikandpay_data['PROCHAINE'])) {
			$subscribe_comment = vmText::_('VMPAYMENT_KLIKANDPAY_RESPONSE_PROCHAINE') . ' ' . $klikandpay_data['PROCHAINE'];
		}

		$order_history['customer_notified'] = true;
		$order_history['comments'] .= $subscribe_comment;
		// At the moment, KP only sends a notification on a successful transaction
		// it does not send notification , if a payment is not done, or if a subscription is stopped from KP BO
		$order_history['order_status'] = $this->_method->status_success_subscribe;
		return $order_history;


	}

	/**
	 * if ORNUMXKP = Original NUMXKP (from 1rst transaction)
	 * @param $klikandpay_data
	 * @param $order
	 * @param $payments
	 * @return bool
	 */
	function isResponseValid($klikandpay_data, $order, $payments) {
		if ($klikandpay_data['RESPONSE'] == self::RESPONSE_SUCCESS) {
			if (isset($klikandpay_data['ORNUMXKP'])) {
				foreach ($payments as $payment) {
					if ($payment->klikandpay_response_NUMXKP == $klikandpay_data['ORNUMXKP']) {
						return true;
					}
				}
				$this->plugin->debugLog(var_export($klikandpay_data, true), 'Notification received not valid', 'error', false);
				return false;
			}
			return true;
		} else {
			return false;
		}
	}

	/**
	 * check if mixed products incart
	 * */
	function checkConditions($cart) {
		$subscribe = $this->getSubscribeProducts($cart);
		if ($subscribe === false) {
			return false;
		} else {
			return true;
		}
	}

	function onCheckoutCheckDataPayment(VirtueMartCart $cart) {
		static $displayInfoMsg = true;
		$return = true;
		if (!$this->getSubscribeProducts($cart)) {
			$return = false;
		}
		if ($cart->BT) {
			if (empty($cart->BT['phone_1']) and empty($cart->BT['phone_2']) and $displayInfoMsg) {
				//vmInfo(vmText::sprintf('VMPAYMENT_KLIKANDPAY_SUBSCRIBE_TEL_REQUIRED',JRoute::_('index.php?option=com_virtuemart&view=cart')) );
				vmInfo(vmText::sprintf('VMPAYMENT_KLIKANDPAY_SUBSCRIBE_TEL_REQUIRED', JRoute::_('index.php?option=com_virtuemart&view=user&task=editaddresscheckout&addrtype=BT')));
				$return = false;
			}
		}
		$displayInfoMsg = false;
		return $return;
	}

	function onSelectCheck(VirtueMartCart $cart) {
		$this->onCheckoutCheckDataPayment($cart);
	}

	function getExtraPluginNameInfo() {
		if (!class_exists('VirtueMartCart')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'cart.php');
		}
		$cart = VirtueMartCart::getCart();
		if (!isset($cart->cartPrices)) {
			$cart->getCartPrices();
		}
		$extraInfo['subscribe'] = $this->getSubscribeProducts($cart, false);

		return $extraInfo;

	}

	function getSubscribeId($cart) {
		$subscribe = $this->getSubscribeProducts($cart);
		if ($subscribe) {
			return $subscribe['subscribe_id'];
		}
		return false;
	}

	/**
	 * Subscription products:
	 * 1. create a custom field type 'cart Variant', Cart Attribute= Yes
	 * 2. Add the custom field to the product
	 * 3. There must be a correspondence between custom in products / payment
	 * -- first item of custom field => subscribe_1
	 * -- second item of custom field => subscribe_2
	 * -- third item of custom field => subscribe_3
	 *
	 * @param VirtueMartCart $cart
	 * @return array or boolean
	 */
	function getSubscribeProducts(VirtueMartCart $cart) {
		static $displayErrorMsg = true;

		$subscribe = array();
		if ($this->_method->subscribe_customfield == 0) {
			return false;
		}
		JPluginHelper::importPlugin('vmcustom');
		$app = JFactory::getApplication();
		$productSubscribe = 0;
		$products = $cart->products;
		$previousSubscribeOptionSelected = 0;
		$first = true;
		foreach ($products as $priceKey => $product) {
			// TODO check it is the correct Abonnement id
			$product_custom_fields = $this->getProdCustomFields($product->virtuemart_product_id);
			if (!empty($product_custom_fields)) {
				foreach ($product_custom_fields as $product_custom_field) {
					if ($product_custom_field->virtuemart_custom_id == $this->_method->subscribe_customfield) {
						$subscribeOptionSelected = $this->getSubscribeOptionSelected($product_custom_field, $priceKey);
						if ($subscribeOptionSelected) {
							if (!$first and $previousSubscribeOptionSelected != $subscribeOptionSelected) {
								break;
							}
							$productSubscribe++;
							$subscribe_due_date_amount = 'subscribe_due_date_amount_' . $subscribeOptionSelected;
							$subscribe_frequency = 'subscribe_frequency_' . $subscribeOptionSelected;
							$subscribe_test_amount = 'subscribe_test_amount_' . $subscribeOptionSelected;
							$subscribe_test_period = 'subscribe_test_period_' . $subscribeOptionSelected;
							$subscribe_id = 'subscribe_id_' . $subscribeOptionSelected;
							$amountInCurrency = vmPSPlugin::getAmountInCurrency($this->_method->$subscribe_due_date_amount, $cart->pricesCurrency);
							$subscribe['subscribe_due_date_amount'] = $amountInCurrency['display'];
							$subscribe['subscribe_frequency'] = $this->_method->$subscribe_frequency;
							if ($this->_method->$subscribe_test_amount) {
								$amountInCurrency = vmPSPlugin::getAmountInCurrency($this->_method->$subscribe_test_amount, $cart->pricesCurrency);
								$subscribe['subscribe_test_amount'] = $amountInCurrency['display'];
							}

							$subscribe['subscribe_test_period'] = $this->_method->$subscribe_test_period;
							$subscribe['subscribe_id'] = $this->_method->$subscribe_id;
							$previousSubscribeOptionSelected = $subscribeOptionSelected;
							$first = false;
							$currencyDisplay = CurrencyDisplay::getInstance($cart->pricesCurrency);
						}

					}
				}
			}
		}
		$nbProducts = count($products);
		if ($productSubscribe AND $nbProducts != $productSubscribe) {
			if ($displayErrorMsg) {
				$displayErrorMsg = false;
				vmError('VMPAYMENT_KLIKANDPAY_SUBSCRIBE_MIXED_PRODUCTS', 'VMPAYMENT_KLIKANDPAY_SUBSCRIBE_MIXED_PRODUCTS');
			}
			return false;
		} else {
			return $subscribe;
		}

	}

	function getSubscribeOptionSelected($product_custom_field, $priceKey) {
		$variants = $this->parseModifier($priceKey);
		$i = 1;
		foreach ($product_custom_field->options as $key => $option) {
			if ($key == $variants[$product_custom_field->virtuemart_custom_id]) {
				return $i;
			}
			$i++;
		}
		return false;

	}

	/**
	 * parse Modifier is NOT a clone of the function in calculationh.php
	 * @param $priceKey
	 * @return array
	 */
	public function parseModifier($priceKey) {

		$variants = array();
		if ($index = strpos($priceKey, '::')) {
			$virtuemart_product_id = substr($priceKey, 0, $index);
			$allItems = substr($priceKey, $index + 2);
			$items = explode(';', $allItems);

			foreach ($items as $item) {
				if (!empty($item)) {
					//vmdebug('parseModifier $item',$item);
					$index2 = strpos($item, ':');
					if ($index2 != false) {
						$selected = substr($item, 0, $index2);
						$variant = substr($item, $index2 + 1);
						//	echo 'My selected '.$selected;
						//	echo ' My $variant '.$variant.' ';
						//TODO productCartId
						//MarkerVarMods
						//$variants[$selected] = $variant; //this works atm not for the cart
						$variants[$variant] = $selected; //but then the orders are broken
					}
				}
			}
		}
		//vmdebug('parseModifier $variants',$variants);
		return $variants;
	}

	function getProdCustomFields($virtuemart_product_id) {
		$product = new stdClass();
		$product->virtuemart_product_id = $virtuemart_product_id;
		$customfields = VmModel::getModel('Customfields');
		$product_customfields = $customfields->getProductCustomsFieldCart($product);
		return $product_customfields;
	}

	function getProductAmount($productPricesUnformatted) {
		if ($productPricesUnformatted['salesPriceWithDiscount']) {
			return $productPricesUnformatted['salesPriceWithDiscount'];
		} else {
			return $productPricesUnformatted['salesPrice'];
		}
	}

	function getKlikandpayServerUrl($subscribe_id) {
		if ($subscribe_id) {
			if ($this->_method->shop_mode == 'test') {
				$url = 'https://www.klikandpay.com/paiementtest/checkabon.pl';
			} else {
				$url = 'https://www.klikandpay.com/paiement/checkabon.pl';
			}
		} else {
			$url = parent::getKlikandpayServerUrl(NULL);
		}

		return $url;

	}
}