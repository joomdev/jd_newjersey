<?php
/**
 *
 * Paypal  payment plugin
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

class PaypalHelperCustomerData {

	private $_selected_method = '';
	private $_autobilling_max_amount = '';
	private $_cc_name = '';
	private $_cc_type = '';
	private $_cc_number = '';
	private $_cc_cvv = '';
	private $_cc_expire_month = '';
	private $_cc_expire_year = '';
	private $_cc_valid = false;
	private $_errormessage = array();
	private $_token = '';
	private $_payer_id = '';
	private $_first_name = '';
	private $_last_name = '';
	private $_payer_email = '';

//    private $_txn_id = '';
//    private $_txn_type = '';
//    private $_payment_status = '';
//    private $_pending_reason = '';


	public function load() {

		//$this->clear();
		if (!class_exists('vmCrypt')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcrypt.php');
		}
		$session = JFactory::getSession();
		$sessionData = $session->get('paypal', 0, 'vm');

		if (!empty($sessionData)) {
			$data =   (object)json_decode($sessionData, true);
			$this->_selected_method = $data->selected_method;
			// card information
			$this->_cc_type = $data->cc_type;
			$this->_cc_number = vmCrypt::decrypt( $data->cc_number);
			$this->_cc_cvv = vmCrypt::decrypt($data->cc_cvv);
			$this->_cc_expire_month = $data->cc_expire_month;
			$this->_cc_expire_year = $data->cc_expire_year;
			$this->_cc_valid = $data->cc_valid;
			//Customer settings
			$this->_autobilling_max_amount = $data->autobilling_max_amount;
			//PayPal Express
			$this->_token = $data->token;
			$this->_payer_id = $data->payer_id;
			$this->_first_name = $data->first_name;
			$this->_last_name = $data->last_name;
			$this->_payer_email = $data->payer_email;

//			$this->_txn_id = $data->txn_id;
//			$this->_txn_type = $data->txn_type;
//			$this->_payment_status = $data->payment_status;
//			$this->_pending_reason = $data->pending_reason;

			$this->save();
			return $data;
		}
	}

	public function loadPost() {
		if (!class_exists('vmCrypt')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcrypt.php');
		}
		// card information
		$virtuemart_paymentmethod_id = vRequest::getVar('virtuemart_paymentmethod_id', 0);
		//if ($virtuemart_paymentmethod_id) {
		//	print_trace();
		//$this->clear();
		//}

		$this->_selected_method = $virtuemart_paymentmethod_id;
		$cctype = vRequest::getVar('cc_type_' . $virtuemart_paymentmethod_id, '');
		if ($cctype) {
			$this->_cc_type = $cctype;
		}

		$cc_name = vRequest::getVar('cc_name_' . $virtuemart_paymentmethod_id, '');
		if ($cc_name) {
			$this->_cc_name = $cc_name;
		}

		$cc_number = vRequest::getVar('cc_number_' . $virtuemart_paymentmethod_id, '');
		if ($cc_number) {
			$this->_cc_number = $cc_number;
		}

		$cc_cvv = vRequest::getVar('cc_cvv_' . $virtuemart_paymentmethod_id, '');
		if ($cc_cvv) {
			$this->_cc_cvv = $cc_cvv;
		}

		$cc_expire_month = vRequest::getVar('cc_expire_month_' . $virtuemart_paymentmethod_id, '');
		if ($cc_expire_month) {
			$this->_cc_expire_month = $cc_expire_month;
		}

		$cc_expire_year = vRequest::getVar('cc_expire_year_' . $virtuemart_paymentmethod_id, '');
		if ($cc_expire_year) {
			$this->_cc_expire_year = $cc_expire_year;
		}

		//Customer settings
		$autobilling_max_amount = vRequest::getVar('autobilling_max_amount_' . $virtuemart_paymentmethod_id, '');
		if ($autobilling_max_amount) {
			$this->_autobilling_max_amount = $autobilling_max_amount;
		}

//		$this->_cc_name = vRequest::getVar('cc_name_' . $virtuemart_paymentmethod_id, '');
//		$this->_cc_number = str_replace(" ","",vRequest::getVar('cc_number_' . $virtuemart_paymentmethod_id, ''));
//		$this->_cc_cvv = vRequest::getVar('cc_cvv_' . $virtuemart_paymentmethod_id, '');
//		$this->_cc_expire_month = vRequest::getVar('cc_expire_month_' . $virtuemart_paymentmethod_id, '');
//		$this->_cc_expire_year = vRequest::getVar('cc_expire_year_' . $virtuemart_paymentmethod_id, '');
//		//Customer settings
//		$this->_autobilling_max_amount = vRequest::getVar('autobilling_max_amount_' . $virtuemart_paymentmethod_id, '');

		$this->save();
	}

	public function save() {
		if (!class_exists('vmCrypt')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcrypt.php');
		}
		$session = JFactory::getSession();
		$sessionData = new stdClass();
		$sessionData->selected_method = $this->_selected_method;
		// card information
		$sessionData->cc_type = $this->_cc_type;
		$sessionData->cc_number = vmCrypt::encrypt($this->_cc_number);
		$sessionData->cc_cvv =vmCrypt::encrypt( $this->_cc_cvv);
		$sessionData->cc_expire_month = $this->_cc_expire_month;
		$sessionData->cc_expire_year = $this->_cc_expire_year;
		$sessionData->cc_valid = $this->_cc_valid;
		//Customer settings
		$sessionData->autobilling_max_amount = $this->_autobilling_max_amount;
		//PayPal Express
		$sessionData->token = $this->_token;
		$sessionData->payer_id = $this->_payer_id;
		$sessionData->first_name = $this->_first_name;
		$sessionData->last_name = $this->_last_name;
		$sessionData->payer_email = $this->_payer_email;

//		$sessionData->txn_id = $this->_txn_id;
//		$sessionData->txn_type = $this->_txn_type;
//		$sessionData->payment_status = $this->_payment_status;
//		$sessionData->pending_reason = $this->_pending_reason;

		$session->set('paypal', json_encode($sessionData), 'vm');
	}

	public function reset() {
		$this->_selected_method = '';
		// card information
		$this->_cc_type = '';
		$this->_cc_number = '';
		$this->_cc_cvv = '';
		$this->_cc_expire_month = '';
		$this->_cc_expire_year = '';
		//Customer settings
		$this->_autobilling_max_amount = '';
		//PayPal Express
		$this->_token = '';
		$this->_payer_id = '';
		$this->_first_name = '';
		$this->_last_name = '';
		$this->_payer_email = '';

//		$this->_txn_id = '';
//		$this->_txn_type = '';
//		$this->_payment_status = '';
//		$this->_pending_reason = '';

		$this->save();
	}

	public function clear() {
		$session = JFactory::getSession();
		$session->clear('paypal', 'vm');
	}

	public function getVar($var) {
		$this->load();
		return $this->{'_' . $var};
	}

	public function setVar($var, $val) {
		$this->{'_' . $var} = $val;
	}

}
