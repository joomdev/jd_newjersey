<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: customerdata.php 8343 2014-09-30 11:49:09Z alatak $
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

class RealexHelperCustomerData {
	const REALEX_FOLDERNAME = "realex";
	const REALEX_SESSION = "RealexCustomerData";
	private $_cc_name = '';
	private $_cc_type = '';
	private $_cc_number = '';
	private $_cc_cvv = '';
	private $_cc_cvv_realvault = '';
	private $_cc_expire_month = '';
	private $_cc_expire_year = '';
	private $_cc_valid = false;
	private $_selected_method = '';
	private $_saved_cc_selected = '';
	private $_save_card = '';
	private $_dcc_choice = '';


	public function load () {

		//$this->_clear();
		/* TODO
		$store = 'none';
		$options['expire']= 60* 60;
		$session = JFactory::getSession($store,$options);
		*/
		$session = JFactory::getSession();
		$sessionData = $session->get(self::REALEX_SESSION, 0, 'vm');
		if (!class_exists('vmCrypt')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcrypt.php');
		}
		if (!empty($sessionData)) {
			$data =   (object)json_decode($sessionData, true);
			$this->_saved_cc_selected = $data->saved_cc_selected;
			$this->_save_card = $data->save_card;
			$this->_selected_method = $data->selected_method;
			$this->_dcc_choice = $data->dcc_choice;
			$this->_cc_cvv_realvault = $data->cc_cvv_realvault;
			$this->_cc_type = $data->cc_type;
			// card information are not  saved  in session
		}

	}

	public function loadPost () {

		$this->_selected_method = vRequest::getInt('virtuemart_paymentmethod_id', 0);

		$saved_cc_selected = vRequest::getInt('saved_cc_selected', 0);
		//$saved_cc_selected = vRequest::getInt('saved_cc_selected' , 0);
		if ($saved_cc_selected) {
			$this->_saved_cc_selected = $saved_cc_selected;
		}
		$this->_save_card = vRequest::getInt('save_card', 0);
		$dcc_choice = vRequest::getInt('dcc_choice', '');
		if ($dcc_choice) {
			$this->_dcc_choice = $dcc_choice;
		}

		$cctype = vRequest::getString('cc_type', '');
		if ($cctype) {
			$this->_cc_type = $cctype;
		}


		/**
		 * name on CC should be restricted to letters only.
		 */
		$cc_name = vRequest::getString('cc_name', '');
		if ($cc_name) {
			$this->_cc_name = $cc_name;
		}

		$cc_number = vRequest::getString('cc_number', '');
		if ($cc_number) {
			$this->_cc_number = $cc_number;
		}

		$cc_cvv = vRequest::getInt('cc_cvv', '');
		if ($cc_cvv) {
			$this->_cc_cvv = $cc_cvv;
		}

		$cc_expire_month = vRequest::getInt('cc_expire_month', '');
		if ($cc_expire_month) {
			$this->_cc_expire_month = $cc_expire_month;
		}

		$cc_expire_year = vRequest::getInt('cc_expire_year', '');
		if ($cc_expire_year) {
			$this->_cc_expire_year = $cc_expire_year;
		}

		$cc_cvv_realvault = vRequest::getInt('cc_cvv_realvault', '');
		if ($cc_cvv_realvault) {
			$this->_cc_cvv_realvault = $cc_cvv_realvault;
		}

		$this->save();

	}

	/**
	 * save CustomerData when realvault
	 * @param $data
	 *
	 */
	public function saveCustomerRealVaultData ($data) {
		if (isset($data['realex_hpp_api_saved_pmt_type'])) {
			$this->_cc_type = $data['realex_hpp_api_saved_pmt_type'];
		}
		if (isset($data['realex_hpp_api_saved_pmt_digits'])) {
			$this->_cc_number = $data['realex_hpp_api_saved_pmt_digits'];
		}
		if (isset($data['realex_hpp_api_saved_pmt_name'])) {
			$this->_cc_name = $data['realex_hpp_api_saved_pmt_name'];
		}
		$this->save();
	}

	/**
	 * save the cc infos returned in the md (3DSverifySig)
	 * @param $md
	 */
	public function saveCustomerMDData ($md) {

		$this->_cc_type = $md['cc_type'];
		$this->_cc_number = $md['cc_number'];
		$this->_cc_name = $md['cc_name'];
		$this->_cc_cvv = $md['cc_cvv'];
		$this->_cc_expire_month = $md['cc_expire_month'];
		$this->_cc_expire_year = $md['cc_expire_year'];
		$this->save();
	}

	/**
	 *
	 */
	public function unsetCustomerData () {
		$this->_cc_type = '';
		$this->_cc_name = '';
		$this->_cc_number = '';
		$this->_cc_cvv = '';
		$this->_cc_expire_month = '';
		$this->_cc_expire_year = '';
	}

	/**
	 * save data in session
	 */
	public function save () {

		$session = JFactory::getSession();
		$sessionData = new stdClass();
		$sessionData->selected_method = $this->_selected_method;
		$sessionData->saved_cc_selected = $this->_saved_cc_selected;
		$sessionData->save_card = $this->_save_card;
		$sessionData->dcc_choice = $this->_dcc_choice;
		$sessionData->cc_cvv_realvault = $this->_cc_cvv_realvault;
		$sessionData->cc_type = $this->_cc_type;
		// card information should not be saved  in session
		$session->set(self::REALEX_SESSION, json_encode($sessionData), 'vm');
	}


	/**
	 * @param $var
	 * @return mixed
	 */
	public function getVar ($var) {
		$this->load();
		return $this->{'_' . $var};
	}

	/**
	 * @param $var
	 * @param $val
	 */
	public function setVar ($var, $val) {
		$this->{'_' . $var} = $val;
	}

	public function clear () {
		$session = JFactory::getSession();
		$session->clear(self::REALEX_SESSION, 'vm');
	}

	/**
	 * Remove some numbers from the CC when displaying it
	 * when debug or log option is on
	 *
	 */
	function getMaskedCCnumber () {
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		return shopFunctionsF::mask_string($this->getVar('cc_number'), '*');
		$this->getVar('cc_number');

	}


}
