<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: redirect.php 8892 2015-06-29 08:06:12Z alatak $
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


class RealexHelperRealexRedirect extends RealexHelperRealex {


	function __construct ($method, $plugin) {
		parent::__construct($method, $plugin);


	}

	public function confirmedOrder (&$postRequest) {
		$selectedCCParams = array();
		if (!$this->doRealvault($selectedCCParams)) {
			$response = $this->sendPostRequest();
			$postRequest = true;
		} else {
			$response = $this->realvaultReceiptIn($selectedCCParams);
		}
		return $response;
	}


	function doRealVault (&$selectedCCParams) {
		//$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
		//$selectedCCParams = $this->getSelectedCCParams($saved_cc_selected, $this->cart->virtuemart_paymentmethod_id);
		$doRealVault = false;

		if (!JFactory::getUser()->guest AND $this->_method->realvault and $this->getStoredCCs(JFactory::getUser()->id)) {
			//if (!$selectedCCParams->addNew) {
			$doRealVault = true;
			//}
		}
		$this->debugLog((int)$doRealVault, 'Realex doRealVault:', 'debug');
		return $doRealVault;
	}


	function sendPostRequest () {
		$post_variables = $this->getPostVariables();

		$jump_url = $this->getJumpUrl();

		$html = '';
		if ($this->_method->debug) {
			$html .= '<form action="' . $jump_url . '" method="post" name="vm_realex_form" target="realex">';
		} else {
			if (VmConfig::get('css')) {
				$msg = vmText::_('VMPAYMENT_REALEX_HPP_API_REDIRECT_MESSAGE', true);
			} else {
				$msg='';
			}

			vmJsApi::addJScript('vm.paymentFormAutoSubmit', '
  			jQuery(document).ready(function($){
   				jQuery("body").addClass("vmLoading");
  				var msg="'.$msg.'";
   				jQuery("body").append("<div class=\"vmLoadingDiv\"><div class=\"vmLoadingDivMsg\">"+msg+"</div></div>");
    			jQuery("#vmPaymentForm").submit();
			})
		');
			$html .= '<form action="' . $jump_url . '" method="post" name="vm_realex_form" id="vmPaymentForm" accept-charset="UTF-8">';
		}
		$html .= '<input type="hidden" name="charset" value="utf-8">';

		foreach ($post_variables as $name => $value) {
			$html .= '<input type="hidden" name="' . $name . '" value="' . $value . '" />';
		}

		if ($this->_method->debug) {

			$html .= '<div style="background-color:red;color:white;padding:10px;">
						<input type="submit"  value="The method is in debug mode. Click here to be redirected to Realex" />
						</div>';
			$this->debugLog($post_variables, 'sendPostRequest:', 'debug');

		}
		$html .= '</form>';

		return $html;
	}

	function getPostVariables () {

		$BT = $this->order['details']['BT'];
		$ST = ((isset($this->order['details']['ST'])) ? $this->order['details']['ST'] : $this->order['details']['BT']);


		// prepare postdata
		$post_variables = array();
		$post_variables['MERCHANT_ID'] = $this->_method->merchant_id;
		$post_variables['ACCOUNT'] = $this->_method->subaccount;
		$post_variables['ORDER_ID'] = $BT->order_number;
		$post_variables['AMOUNT'] = $this->getTotalInPaymentCurrency();
		$post_variables['CURRENCY'] = $this->getPaymentCurrency();
		$post_variables['LANG'] = $this->getPaymentLang();
		$post_variables['TIMESTAMP'] = $this->getTimestamp();
		$post_variables['DCC_ENABLE'] = $this->_method->dcc;

		$post_variables['COMMENT1'] = $this->setComment1();
		$post_variables['COMMENT2'] = 'virtuemart-rlx';

		$post_variables['MERCHANT_RESPONSE_URL'] = JURI::root() . 'index.php?option=com_virtuemart&format=raw&view=pluginresponse&task=pluginnotification&notificationTask=handleRedirect&tmpl=component';
		$post_variables['AUTO_SETTLE_FLAG'] = $this->getSettlement();

		if ($BT->virtuemart_user_id != 0) {
			//$post_variables['VAR_REF'] = $BT->order_number;
			$post_variables['CARD_STORAGE_ENABLE'] = $this->_method->realvault;
			if ($this->_method->realvault) {
				$payerRef = $this->getSavedPayerRef();
				if (!$payerRef) {
					$post_variables['PAYER_EXIST'] = 0;
					$post_variables['PMT_REF'] = '';
					$post_variables['PAYER_REF'] = $this->getNewPayerRef();
				} else {
					$post_variables['PAYER_REF'] = $payerRef;
					$post_variables['PAYER_EXIST'] = 1;
					$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
					// -1: use another card, empty no CC are stored
					if ($saved_cc_selected == -1 or empty($saved_cc_selected)) {
						$post_variables['PMT_REF'] = '';
					} else {
						$post_variables['PMT_REF'] = $this->getPmtRef();
					}
				}

				$post_variables['OFFER_SAVE_CARD'] = $this->_method->offer_save_card;

			} else {
				$post_variables['OFFER_SAVE_CARD'] = 0;
			}
		} else {
			$post_variables['OFFER_SAVE_CARD'] = 0;
			$post_variables['CARD_STORAGE_ENABLE'] = 0;
		}

		if ($this->_method->card_payment_button) {
			$post_variables['CARD_PAYMENT_BUTTON'] = $this->getCardPaymentButton($this->_method->card_payment_button);
		}

		if ($this->_method->realvault and $BT->virtuemart_user_id != 0) {
			$post_variables['SHA1HASH'] = $this->getSha1Hash($this->_method->shared_secret, $post_variables['TIMESTAMP'], $post_variables['MERCHANT_ID'], $post_variables['ORDER_ID'], $post_variables['AMOUNT'], $post_variables['CURRENCY'], $post_variables['PAYER_REF'], $post_variables['PMT_REF']);
		} else {
			$post_variables['SHA1HASH'] = $this->getSha1Hash($this->_method->shared_secret, $post_variables['TIMESTAMP'], $post_variables['MERCHANT_ID'], $post_variables['ORDER_ID'], $post_variables['AMOUNT'], $post_variables['CURRENCY']);
		}

		// use_tss? if uk
		if ($this->_method->tss) {
			$post_variables['RETURN_TSS'] = 1; // Transaction Suitability Score
			// <digits from postcode>|<digits from address>
			$post_variables['BILLING_CODE'] = $this->getCode($BT);
			$post_variables['BILLING_CO'] = ShopFunctions::getCountryByID($BT->virtuemart_country_id, 'country_2_code');

			$post_variables['SHIPPING_CODE'] = $this->getCode($ST);
			$post_variables['SHIPPING_CO'] = ShopFunctions::getCountryByID($ST->virtuemart_country_id, 'country_2_code');

		}

		$post_variables['gateway_url'] = $this->_getRealexUrl();

		return $post_variables;

	}

	/**
	 * @param $realex_data
	 * @return bool
	 */
	function cardStorageResponse ($realex_data) {
		$userfield=false;
		if (isset($realex_data['REALWALLET_CHOSEN']) and  $realex_data['REALWALLET_CHOSEN'] == 0) {
			return false;
		}

		if (isset($realex_data['PAYER_SETUP']) and  $realex_data['PAYER_SETUP'] != self::PAYER_SETUP_SUCCESS) {
			$this->debugLog('cardStorageResponse PAYER_SETUP not successfull:' . $realex_data['PAYER_SETUP'] . ' ' . $realex_data['PAYER_SETUP_MSG'], 'debug');
			return false;
		}
		if ((isset($realex_data['PAYER_SETUP']) and  $realex_data['PAYER_SETUP'] == self::PAYER_SETUP_SUCCESS)) {
			$this->saveNewPayerRef($realex_data['SAVED_PAYER_REF']);
		}

		if ((isset($realex_data['PMT_SETUP']) and  $realex_data['PMT_SETUP'] != self::PMT_SETUP_SUCCESS)) {
			$this->debugLog('cardStorageResponse PMT_SETUP not successfull:' . $realex_data['PMT_SETUP'] . ' ' . $realex_data['PMT_SETUP_MSG'], 'debug');
			return false;
		}
		if ((isset($realex_data['PMT_SETUP']) and  $realex_data['PMT_SETUP'] == self::PMT_SETUP_SUCCESS)) {
			$userfield = $this->getPaymentRef($realex_data);
			//$this->storeNewPayment($userfield);
		}
		return $userfield;
	}


	/**
	 * @param $realex_data
	 * @return mixed
	 */
	function getPaymentRef ($realex_data) {

		$fields = array(
			'SAVED_PMT_TYPE',
			'SAVED_PMT_REF',
			'SAVED_PMT_DIGITS',
			'SAVED_PMT_EXPDATE',
			'SAVED_PMT_NAME',
		);
		$userfield['virtuemart_user_id'] = $this->order['details']['BT']->virtuemart_user_id;
		$userfield['merchant_id'] = $this->_method->merchant_id;
		foreach ($fields as $field) {
			if (isset($realex_data[$field])) {
				if ($field == 'SAVED_PMT_DIGITS') {
					$realex_data[$field] = shopFunctionsF::mask_string($realex_data[$field], '*');
				}
				$userfield['realex_hpp_api_' . strtolower($field)] = $realex_data[$field];
			}
		}
		return $userfield;
	}

	/**
	 * @param bool $enqueueMessage
	 * @return bool
	 */

	function validateConfirmedOrder ($enqueueMessage = true) {

		return $this->validate();

	}

	/**
	 * @param bool $enqueueMessage
	 * @return bool
	 */
	public function validate ($enqueueMessage = true) {
		if (!JFactory::getUser()->guest AND $this->_method->realvault) {
			if ($storedCCs = $this->getStoredCCs(JFactory::getUser()->id)) {
				$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
				if ($this->customerData->getVar('selected_method') AND empty($saved_cc_selected)) {
					vmInfo('VMPAYMENT_REALEX_HPP_API_PLEASE_SELECT_OPTION');
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * @param bool $enqueueMessage
	 * @return bool
	 */
	public function validateSelectCheckPayment ($enqueueMessage = true) {
		return $this->validate();
	}

	/**
	 * @return bool
	 */
	function validateCheckoutCheckDataPayment () {
		return $this->validate();
	}

	function getExtraPluginInfo () {
		$extraPluginInfo = array();
		$saved_cc_selected = $this->customerData->getVar('saved_cc_selected');
		if ($saved_cc_selected != -1) {
			$selected_cc = $this->getSelectedCCParams($saved_cc_selected);
			if (!empty($selected_cc)) {
				$extraPluginInfo['cc_type'] = $selected_cc->realex_hpp_api_saved_pmt_type;
				$extraPluginInfo['cc_number'] = $selected_cc->realex_hpp_api_saved_pmt_digits;
				$extraPluginInfo['cc_name'] = $selected_cc->realex_hpp_api_saved_pmt_name;

				$extraPluginInfo['cc_expire_month'] = "";
				$extraPluginInfo['cc_expire_year'] = "";
			}
		} else {
			$extraPluginInfo['cc_number'] = vmText::_('VMPAYMENT_REALEX_HPP_API_USE_ANOTHER_CC');
		}


		return $extraPluginInfo;
	}

	/**
	 * Validate the response hash from Realex.
	 * timestamp.merchantid.orderid.amount.curr.payerref.pmtref
	 */
	function validateResponseHash ($post) {
		if (is_array($post)) {
			$message = stripslashes($post['MESSAGE']);
			$message = str_replace('&#39;', "'", $message);
			$hash = $this->getSha1Hash($this->_method->shared_secret, $post['TIMESTAMP'], $post['MERCHANT_ID'], $post['ORDER_ID'], $post['RESULT'], $message, isset($post['PASREF']) ? $post['PASREF'] : "", isset($post['AUTHCODE']) ? $post['AUTHCODE'] : "");
			if ($hash != $post['SHA1HASH']) {
				$this->debugLog('validateResponseHash :' . var_export($post, true), 'debug');
				//$this->displayError(vmText::sprintf('VMPAYMENT_REALEX_HPP_API_ERROR_WRONG_HASH', $hash, print_r($post, true)));
				//echo vmText::sprintf('VMPAYMENT_REALEX_HPP_API_ERROR_WRONG_HASH', $hash, $post['SHA1HASH']);
				//print_r($_POST);
				return FALSE;
			}
		} else {
			return parent::validateResponseHash($post);
		}


		return true;
	}

	function setComment1 () {
		$amountValue = vmPSPlugin::getAmountInCurrency($this->order['details']['BT']->order_total, $this->order['details']['BT']->order_currency);
		$currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

		$shop_name = $this->getVendorInfo('vendor_store_name');
		return vmText::sprintf('VMPAYMENT_REALEX_HPP_API_COMMENT1', $amountValue['display'], $this->order['details']['BT']->order_number, $shop_name);
	}



	/**
	 * JumpUrl is a prefined URL that must be configurated in Realex
	 * @return string
	 */
	function getJumpUrl () {
		return $this->_method->referring_url;

	}
}
