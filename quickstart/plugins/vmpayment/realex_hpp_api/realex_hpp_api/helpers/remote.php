<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: remote.php 8343 2014-09-30 11:49:09Z alatak $
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


class RealexHelperRealexRemote extends RealexHelperRealex {


	function __construct ($method, $plugin) {
		parent::__construct($method, $plugin);

	}

	public function confirmedOrder (&$postRequest, &$request3DSecure) {
		$postRequest = false;
		$request3DSecure = false;
		if ($this->_method->dcc) {
			$response = $this->requestDccRate();
		} elseif ($this->_method->threedsecure and $this->isCC3DSVerifyEnrolled()) {
			$request3DSecure = true;
			$response = $this->request3DSecure();
		} else {
			$response = $this->requestAuth();
		}


		return $response;

	}


	/**
	 * @return array
	 */

	function getExtraPluginInfo () {
		return NULL;


	}

	function confirmedOrderDccRequest ($response_dcc) {
		$request3DSecure = false;
		if ($this->_method->threedsecure and $this->isCC3DSVerifyEnrolled()) {
			$request3DSecure = true;
			$response = $this->request3DSecure();
		} else {
			$selectedCCParams = array();
			if ($this->doRealVault($selectedCCParams)) {
				$newPayerRef = "";
				$responseNewPayer = $this->setNewPayer($newPayerRef);
				$setNewPayerSuccess = $this->manageSetNewPayer($responseNewPayer);
				if ($setNewPayerSuccess) {
					$newPaymentRef = "";
					$responseNewPayment = $this->setNewPayment($newPayerRef, $newPaymentRef);
					$setNewPaymentSuccess = $this->manageSetNewPayment($responseNewPayment, $newPayerRef, $newPaymentRef);
				}
			}
			$xml_response_dcc = simplexml_load_string($response_dcc);
// TODO if using CC stored?
			$response = $this->requestAuth($xml_response_dcc);
			$this->manageResponseRequestAuth($response);
		}


	}


	/**
	 * @return string
	 */
	function displayExtraPluginInfo () {

		return NULL;
	}

	/**
	 * @param bool $enqueueMessage
	 * @return bool
	 */
	function validateRemoteCCForm ($enqueueMessage = true) {
		if (!class_exists('Creditcard')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'creditcard.php');
		}
		$html = '';
		$cc_valid = true;
		$errormessages = array();
		$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
	$cc_type = $this->customerData->getVar('cc_type');
	$cc_number = $this->customerData->getVar('cc_number');
	$cc_name = $this->customerData->getVar('cc_name');
	$cc_cvv = $this->customerData->getVar('cc_cvv');
	$cc_expire_month = $this->customerData->getVar('cc_expire_month');
	$cc_expire_year = $this->customerData->getVar('cc_expire_year');

	if (!Creditcard::validate_credit_card_number($cc_type, $cc_number)) {
		$errormessages[] = 'VMPAYMENT_REALEX_HPP_API_CC_CARD_NUMBER_INVALID';
		$cc_valid = false;
	}

	if ($this->_method->cvn_checking AND !Creditcard::validate_credit_card_cvv($cc_type, $cc_cvv, true, $cc_number)) {
		$errormessages[] = 'VMPAYMENT_REALEX_HPP_API_CC_CARD_CVV_INVALID';
		$cc_valid = false;
	}
	if (!Creditcard::validate_credit_card_date($cc_type, $cc_expire_month, $cc_expire_year)) {
		$errormessages[] = 'VMPAYMENT_REALEX_HPP_API_CC_CARD_EXPIRATION_DATE_INVALID';
		$cc_valid = false;
	}
	if (empty($cc_name)) {
		$errormessages[] = 'VMPAYMENT_REALEX_HPP_API_CC_NAME_INVALID';
		$cc_valid = false;
	}
	if (!$cc_valid) {
		foreach ($errormessages as $msg) {
			$html .= vmText::_($msg) . "<br/>";
		}
	}
	if (!$cc_valid) {
		$app = JFactory::getApplication();
		$app->enqueueMessage($html, 'error');
		return false;
	}
		return true;




	}


}
