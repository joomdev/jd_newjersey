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

class PayboxHelperPayboxRecurring extends PayboxHelperPaybox {

	function __construct ($method, $plugin, $plugin_name) {
		parent::__construct($method, $plugin, $plugin_name);

	}

	function getExtraPluginNameInfo () {
		$extraInfo['recurring'] = true;
		$extraInfo['recurring_number'] = $this->_method->recurring_number;
		$extraInfo['recurring_periodicity'] = $this->_method->recurring_periodicity;
		return $extraInfo;

	}

	function getRecurringPayments ($pbxTotalInPaymentCurrency) {
		$pbxTermAmount = round($pbxTotalInPaymentCurrency / $this->_method->recurring_number);
		$pbxFirstAmount = $pbxTotalInPaymentCurrency - ($pbxTermAmount * ($this->_method->recurring_number - 1));
		for ($i = 1; $i < $this->_method->recurring_number; $i++) {
			$recurring["PBX_2MONT" . $i] = $this->getPbxTotal($pbxTermAmount);
			$recurring["PBX_DATE" . $i] = date('d/m/Y', mktime(0, 0, 0, date('m'), date('d') + ($i * $this->_method->recurring_periodicity), date('Y')));
		}
		$recurring["PBX_TOTAL"] = $this->getPbxTotal($pbxFirstAmount);
		return $recurring;
	}

	function getOrderHistory ($paybox_data, $order, $payments) {
		$amountInCurrency = vmPSPlugin::getAmountInCurrency($order['details']['BT']->order_total, $order['details']['BT']->order_currency);
		$order_history['comments'] = vmText::sprintf('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_STATUS_CONFIRMED_RECURRING', $amountInCurrency['display'], $order['details']['BT']->order_number);

		$amountInCurrency = vmPSPlugin::getAmountInCurrency($paybox_data['M'] * 0.01, $order['details']['BT']->order_currency);
		$order_history['comments'] .= "<br />" . vmText::sprintf('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_STATUS_CONFIRMED_RECURRING_2', $amountInCurrency['display']);

		$order_history['comments'] .= "<br />" . vmText::_('VMPAYMENT_'.$this->plugin_name.'_RESPONSE_S') . ' ' . $paybox_data['S'];
		$recurring_comment = '';
		$payment = $payments[0];
		$recurring = json_decode($payment->recurring);

		if (count($payments) == 1) {
			$recurring_comment .= "<br />" . vmText::sprintf('VMPAYMENT_'.$this->plugin_name.'_COMMENT_RECURRING_INFO', $payment->recurring_number, $payment->recurring_periodicity);
			$recurring_comment .= "<br />" . vmText::_('VMPAYMENT_'.$this->plugin_name.'_COMMENT_NEXT_DEADLINES');

			$recurring_comment .= $this->getOrderRecurringTerms($payment, $order, 1);
			$status_success='status_success_'.$this->_method->debit_type;
			$order_history['order_status'] = $this->_method->$status_success;
		} else {
			$nbRecurringDone = $this->getNbRecurringDone($payments);
			$this->debugLog('getNbRecurringDone:' . $nbRecurringDone, 'getOrderHistoryRecurring', 'debug', false);
			if ($nbRecurringDone < $payment->recurring_number) {
				$recurring_comment .= $this->getOrderRecurringTerms($payment, $order, $nbRecurringDone);
				$order_history['order_status'] = $this->_method->status_success_recurring;
			} else {
				$order_history['order_status'] = $this->_method->status_success_recurring_end;
			}
			$this->debugLog('Next status:' . $order_history['order_status'], 'getOrderHistoryRecurring', 'debug', false);

			$index_mont = "PBX_2MONT" . $nbRecurringDone;
			$index_date = "PBX_DATE" . $nbRecurringDone;
			//$text_mont = vmText::_('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_RECURRING_2MONT') ;
			//$text_date = vmText::_('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_RECURRING_DATE');
			//$recurring_comment .= "<br />" . $text_date . " " . $recurring->$index_date . " ";
			$amountInCurrency = vmPSPlugin::getAmountInCurrency($recurring->$index_mont * 0.01, $order['details']['BT']->order_currency);
			//$recurring_comment .= $text_mont . " " . $amountInCurrency['display'];
			$recurring_comment .= "<br />" . $recurring->$index_date . " " . $amountInCurrency['display'];
		}
		$order_history['customer_notified'] = true;
		$order_history['comments'] .= $recurring_comment;
		$order_history['recurring'] = $recurring_comment;

		return $order_history;


	}

	function getOrderRecurringTerms ($payment, $order, $start) {
		$recurring = json_decode($payment->recurring);
		$recurring_comment = "";
		for ($i = $start; $i < $payment->recurring_number; $i++) {
			$index_mont = "PBX_2MONT" . $i;
			$index_date = "PBX_DATE" . $i;
			$text_mont = vmText::_('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_RECURRING_2MONT') . " ";
			$text_date = vmText::_('VMPAYMENT_'.$this->plugin_name.'_PAYMENT_RECURRING_DATE') . " ";
			$recurring_comment .= "<br />" . $text_date . " " . $recurring->$index_date . " ";
			$amountInCurrency = vmPSPlugin::getAmountInCurrency(($recurring->$index_mont) * 0.01, $order['details']['BT']->order_currency);
			$recurring_comment .= $text_mont . " " . $amountInCurrency['display'];
		}
		return $recurring_comment;
	}

	function getNbRecurringDone ($payments) {
		$nb = 0;
		foreach ($payments as $payment) {
			if (!empty($payment->paybox_fullresponse)) {
				$nb++;
			}
			return $nb;
		}
	}

}