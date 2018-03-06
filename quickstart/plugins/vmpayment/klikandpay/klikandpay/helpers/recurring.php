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
class KlikandpayHelperKlikandpayRecurring extends KlikandpayHelperKlikandpay {

	function __construct($method, $plugin) {
		parent::__construct($method, $plugin);

	}

	function getExtraPluginNameInfo() {
		$recurring = $this->getRecurringPayments();
		$extraInfo['recurring'] = $recurring['info'];
		return $extraInfo;

	}

	function onCheckoutCheckDataPayment(VirtueMartCart $cart) {
		static $displayInfoMsg = true;
		if ($cart->BT) {
			if (empty($cart->BT['phone_1']) and empty($cart->BT['phone_2']) and $displayInfoMsg) {
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

	function getRecurringPayments() {
		if (empty($this->_method->recurring_deposit) and $this->_method->subscribe_number != 2) {
			$recurring = $this->getRecurringIdenticalAmountMonthly();

		} else {
			$recurring = $this->getRecurringDeposit();
		}

		return $recurring;
	}

	/**
	 * Le montant total est divisé en 1, 2, .. 6 fois. Tous les montants à débiter sont équivalents.
	 * Le premier montant est débité au moment de la date d’achat,
	 * et les autres montants sont présentés en banque à date anniversaire à 1 mois d’intervalle.
	 * @param $totalInPaymentCurrency
	 */
	function getRecurringIdenticalAmountMonthly() {
		$totalInPaymentCurrency = $this->getTotal();
		$recurring["MONTANT"] = $totalInPaymentCurrency;
		$recurring["EXTRA"] = ($this->_method->recurring_number - 1) . "FOIS";
		$recurring["info"] = vmText::sprintf('VMPAYMENT_KLIKANDPAY_COMMENT_RECURRING_IDENTICAL', $this->_method->recurring_number);
		return $recurring;
	}

	/**
	 * Après versement d’un acompte immédiat, le solde à payer est divisé en 1, 2, ... 6 fois
	 * dont la date anniversaire peut être différente de celle du paiement immédiat.
	 * Chaque échéance pour le paiement du solde sera présentée à 1 mois d’intervalle à la date anniversaire définie
	 * si elle est différente du paiement initial.
	 *
	 * Indiquer une valeur pour la variable MONTANT qui sera immédiatement présentée en banque,
	 * MONTANT2 le montant du solde.
	 * EXTRA, le nombre d’échéances souhaitées.
	 *
	 *
	 * OU
	 *     * Paiement d’un acompte immédiat et paiement du solde à une date définie.
	 *
	 * Indiquer le montant à débiter immédiatement dans la variable MONTANT,
	 * le solde à débiter dans MONTANT2 et
	 * indiquer la date pour le paiement du solde dans la variable DATE2.
	 *
	 * @param $totalInPaymentCurrency
	 */
	function getRecurringDeposit() {
		$totalInPaymentCurrency = $this->getTotal();
		if (preg_match('/%$/', $this->_method->recurring_deposit)) {
			$deposit = substr($this->_method->recurring_deposit, 0, -1);
			$recurring_deposit = $totalInPaymentCurrency * $deposit * 0.01;
		} else {
			$recurring_deposit = $this->_method->recurring_deposit;
			if (!class_exists('CurrencyDisplay')) {
				require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
			}
			$recurring_deposit = vmPSPlugin::getAmountValueInCurrency($recurring_deposit, $this->_method->payment_currency);
			$cd = CurrencyDisplay::getInstance($this->cart->pricesCurrency);
		}
		$montant_currency = vmPSPlugin::getAmountInCurrency($recurring_deposit, $this->_method->payment_currency);
		$montant2_currency = vmPSPlugin::getAmountInCurrency($totalInPaymentCurrency - $recurring_deposit, $this->_method->payment_currency);
		$cd = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

		$recurring["MONTANT"] = number_format($recurring_deposit, 2, '.', '');
		$recurring["MONTANT2"] = $montant2_currency['value'];
		if ($this->_method->recurring_number > 2) {
			$recurring["EXTRA"] = ($this->_method->recurring_number - 1) . "FOIS";
		}

		if ($this->_method->recurring_date) {
			$recurring["DATE2"] = $this->getNextTermDate();
		}
		$recurring["info"] = vmText::sprintf('VMPAYMENT_KLIKANDPAY_COMMENT_RECURRING_DEPOSIT', $this->_method->recurring_number, $montant_currency['display']);


		return $recurring;
	}

	/**
	 * La valeur DATE, doit être au format : année-mois-jour
	 */
	function getNextTermDate() {
		return date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') + ($this->_method->recurring_date), date('Y')));
	}


	function getOrderHistory($klikandpay_data, $order, $payments) {
		if (count($payments) == 1) {
			$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
			$order_history['comments'] = vmText::sprintf('VMPAYMENT_KLIKANDPAY_PAYMENT_STATUS_CONFIRMED_RECURRING', $amountInCurrency['display'], $order['details']['BT']->order_number);
		}

		$amountInCurrency = vmPSPlugin::getAmountInCurrency($klikandpay_data['MONTANTXKP'], $order['details']['BT']->order_currency);
		$order_history['comments'] .= "<br />" . vmText::sprintf('VMPAYMENT_KLIKANDPAY_PAYMENT_STATUS_CONFIRMED_RECURRING_2', $amountInCurrency['display']);
		if (count($payments) == 1) {
			$recurring_comment = '';
			$payment = $payments[0];
			$recurring = json_decode($payment->recurring);

			$amountInCurrency = vmPSPlugin::getAmountInCurrency($recurring->MONTANT, $order['details']['BT']->order_currency);

			$recurring_comment .= "<br />" . vmText::_('VMPAYMENT_KLIKANDPAY_RECURRING_MONTANT') . ' ' . $amountInCurrency['display'];
			if (isset($recurring->MONTANT2)) {
				$amountInCurrency = vmPSPlugin::getAmountInCurrency($recurring->MONTANT2, $order['details']['BT']->order_currency);
				$recurring_comment .= "<br />" . vmText::_('VMPAYMENT_KLIKANDPAY_RECURRING_MONTANT2') . ' ' . $amountInCurrency['display'];
			}
			if (isset($recurring->EXTRA)) {
				$recurring_comment .= "<br />" . vmText::_('VMPAYMENT_KLIKANDPAY_RECURRING_EXTRA') . ' ' . substr($recurring->EXTRA, 0, 1);
			}
			if (isset($recurring->DATE2)) {
				$recurring_comment .= vmText::_('VMPAYMENT_KLIKANDPAY_RECURRING_DATE2') . ' ' . $recurring->DATE2;
			}
		}
		$nbRecurringDone = $this->getNbRecurringDone($payments);
		if ($nbRecurringDone < $this->_method->recurring_number) {
			$order_history['order_status'] = $this->_method->status_success_recurring;
		} else {
			$order_history['order_status'] = $this->_method->status_success_recurring_end;
		}
		$order_history['customer_notified'] = true;
		$order_history['comments'] .= $recurring_comment;

		return $order_history;


	}


	function getNbRecurringDone($payments) {
		$nb = 0;
		foreach ($payments as $payment) {
			if (!empty($payment->klikandpay_fullresponse)) {
				$nb++;
			}

		}
		return $nb;
	}

	function getKlikandpayServerUrl($id = NULL) {
		if ($this->_method->shop_mode == 'test') {
			$url = 'https://www.klikandpay.com/paiementtest/checkxfois.pl';
		} else {
			$url = 'https://www.klikandpay.com/paiement/checkxfois.pl';
		}
		return $url;

	}
}