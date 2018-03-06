<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: helper.php 9420 2017-01-12 09:35:36Z Milbo $
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


/**
 * @property  request_type
 */
class  RealexHelperRealex {
	var $_method;
	var $cart;
	var $order;
	var $vendor;
	var $customerData;
	var $context;
	var $total;
	var $post_variables;
	var $post_string;
	var $requestData;
	var $response;
	var $currency_code_3;
	var $currency_display;
	var $plugin;

	const REQUEST_TYPE_AUTH = "auth";
	const REQUEST_TYPE_RECEIPT_IN = 'receipt-in';
	const REQUEST_TYPE_REBATE = "rebate";
	const REQUEST_TYPE_SETTLE = "settle";
	const REQUEST_TYPE_VOID = "void";
	const REQUEST_TYPE_REALVAULT_3DS_VERIFYENROLLED = "realvault-3ds-verifyenrolled";
	const REQUEST_TYPE_3DS_VERIFYENROLLED = "3ds-verifyenrolled";
	const REQUEST_TYPE_3DS_VERIFYSIG = "3ds-verifysig";
	const REQUEST_TYPE_DCCRATE = "dccrate";
	const REQUEST_TYPE_REALVAULT_DCCRATE = "realvault-dccrate";
	const REQUEST_TYPE_CARD_CANCEL_CARD = "card-cancel-card";
	const REQUEST_TYPE_CARD_UPDATE_CARD = "card-update-card";
	const REQUEST_TYPE_PAYER_NEW = "payer-new";
	const REQUEST_TYPE_CARD_NEW = "card-new";

	const RESPONSE_DCC_CHOICE_YES = "Yes";
	const DCCRATE_RESULT_SUCCESS = '00';


	const RESPONSE_CODE_SUCCESS = '00';
	const RESPONSE_CODE_DECLINED = '101';
	const RESPONSE_CODE_REFERRAL_B = '102';
	const RESPONSE_CODE_REFERRAL_A = '103';
	const RESPONSE_CODE_NOT_VALIDATED = '110';
	const RESPONSE_CODE_INVALID_ORDER_ID = '501'; // This order ID has already been used - please use another one
	const RESPONSE_CODE_PAYER_REF_NOTEXIST = '501'; // This Payer Ref payerref does not exist
	const RESPONSE_CODE_INVALID_PAYER_REF_USED = '501'; // This Payer Ref payerref has already been used - please use another one
	const RESPONSE_CODE_INVALID_PAYMENT_DETAILS = '509';

	const PAYER_SETUP_SUCCESS = "00";
	const PMT_SETUP_SUCCESS = "00";

	const ENROLLED_RESULT_ENROLLED = '00';
	const ENROLLED_RESULT_NOT_ENROLLED = '110';
	const ENROLLED_RESULT_INVALID_RESPONSE = '5xx';
	const ENROLLED_RESULT_FATAL_ERROR = '220';


	const ENROLLED_TAG_ENROLLED = 'Y';
	const ENROLLED_TAG_UNABLE_TO_VERIFY = 'U';
	const ENROLLED_TAG_NOT_ENROLLED = 'N';

	/**
	 * Response results from threedsecure = "3ds-verifysig";
	 */
	const THREEDSECURE_STATUS_AUTHENTICATED = 'Y';
	const THREEDSECURE_STATUS_NOT_AUTHENTICATED = 'N';
	const THREEDSECURE_STATUS_ACKNOWLEDGED = 'A';
	const THREEDSECURE_STATUS_UNAVAILABLE = 'U';


	const ECI_AUTHENTICATED_VISA = 5;
	const ECI_LIABILITY_SHIFT_VISA = 6;
	const ECI_NO_LIABILITY_SHIFT_VISA = 7;

	const ECI_AUTHENTICATED_MASTERCARD = '2';
	const ECI_LIABILITY_SHIFT_MASTERCARD = '1';
	const ECI_NO_LIABILITY_SHIFT_MASTERCARD = '0';


	/**
	 * Response results REQUEST_TYPE_3DS_VERIFYSIG = "3ds-verifysig";
	 */
	const VERIFYSIG_RESULT_VALIDATED = '00';
	const VERIFYSIG_RESULT_NOT_VALIDATED = '110';
	const VERIFYSIG_RESULT_INVALID_ACS_RESPONSE = '5xx';


	function __construct ($method, $plugin) {
		if ($method->dcc) {
			$method->settlement = 'auto';
			//$method->rebate_password = "";
		}
		if (!$method->realvault) {
			$method->offer_save_card = 0;
		}
		$this->_method = $method;
		$this->_method->shared_secret=trim($this->_method->shared_secret);
		$this->_method->merchant_id=trim($this->_method->merchant_id);
		$this->plugin = $plugin;
		$session = JFactory::getSession();
		$this->context = $session->getId();
	}

	public function setTotalInPaymentCurrency ($total) {

		if (!class_exists('CurrencyDisplay')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'currencydisplay.php');
		}
		$this->total = vmPSPlugin::getAmountValueInCurrency($total, $this->_method->payment_currency) * 100;

		$cd = CurrencyDisplay::getInstance($this->cart->pricesCurrency);
	}

	public function getTotalInPaymentCurrency () {

		return $this->total;

	}

	public function setPaymentCurrency () {
		vmPSPlugin::getPaymentCurrency($this->_method);
		$this->currency_code_3 = shopFunctions::getCurrencyByID($this->_method->payment_currency, 'currency_code_3');
	}

	public function getPaymentCurrency () {
		return $this->currency_code_3;
	}

	public function setContext ($context) {
		$this->context = $context;
	}

	public function getContext () {
		return $this->context;
	}

	public function setCart ($cart, $doGetCartPrices = true) {
		$this->cart = $cart;
		if ($doGetCartPrices AND !isset($this->cart->cartPrices)) {
			$this->cart->getCartPrices();
		}
	}

	public function setOrder ($order) {
		$this->order = $order;
	}

	/**
	 * The digits from the first line of the address should be concatenated with the post code digits with a '|' in the middle.
	 * * For example: Flat 123, No. 7 Grove Park, E98 7QJ
	 * Billing Code: '987|123', the number of digits on each side of the '|' should also be restricted to 5.
	 * @param $address
	 */
	public function getCode ($address) {
		// get first digits of the address line,
		$digits_addr = $this->stripnonnumeric($address->address_1, 5);
		// get digits from zip,
		$digits_zip = $this->stripnonnumeric($address->zip, 5);
		// concatenate with |
		return $digits_zip . "|" . $digits_addr;
	}

	private function stripnonnumeric ($code, $maxLg) {
		$code = preg_replace("/[^0-9]/", "", $code);
		$code = substr($code, 0, $maxLg);
		return $code;
	}

	function _getRealexUrl () {
		if ($this->_method->shop_mode == 'sandbox') {
			//return 'https://realcontrol.sandbox.realexpayments.com';
			return $this->_method->sandbox_gateway_url;
		} else {
			//return ' https://realcontrol.realexpayments.com';
			return $this->_method->gateway_url;
		}

	}

	function redirect3DSRequest ($response) {
		$xml_response = simplexml_load_string($response);

		// Merchant Data. Any data that you would like echoed back to you by the ACS.
		// Useful data here is your order id and the card details (so that you can send the authorisation message on receipt of a positive authentication).
		// Any information in this field must be encrypted then compressed and base64 encoded.
		$md = $this->setMd();

		// The URL that the ACS should reply to. This should be on your website and must be an HTTPS address.
		$url_validation = JURI::root() . 'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&notificationTask=handle3DSRequest&order_number=' . $this->order['details']['BT']->order_number . '&pm=' . $this->order['details']['BT']->virtuemart_paymentmethod_id . '&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', '');

		$this->display3DSForm((string)$xml_response->url, (string)$xml_response->pareq, $md, $url_validation);
	}

	function setMd () {
		if (!class_exists('vmCrypt')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcrypt.php');
		}

		$md = array(
			'cc_type'         => $this->customerData->getVar('cc_type'),
			'cc_name'         => $this->customerData->getVar('cc_name'),
			'cc_number'       => $this->customerData->getVar('cc_number'),
			'cc_cvv'          => $this->customerData->getVar('cc_cvv'),
			'cc_expire_month' => $this->customerData->getVar('cc_expire_month'),
			'cc_expire_year'  => $this->customerData->getVar('cc_expire_year'),
		);
		$jsonencodeMd = json_encode($md);
		$encryptMd = vmCrypt::encrypt($jsonencodeMd);
		return $encryptMd;

	}

	function getMd ($cryptedMd) {
		if (!class_exists('vmCrypt')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmcrypt.php');
		}
		$decryptedMd = vmCrypt::decrypt($cryptedMd);
		$md =  json_decode($decryptedMd, true);
		return $md;
	}

	/**
	 * 4a. Realex send the URL of the cardholder’s bank ACS
	 * (this is the webpage that the cardholder uses to enter their password). Also included is the PAReq (this is needed by the ACS).
	 * POST this encoded PAReq (along with the TermURL and any merchant data you require).
	 * This will result in the cardholder being presented with the authenticaton page where they will be asked to confirm the amount and enter their password.
	 * 6. Once the cardholder enters their password, the ACS POSTS the encoded PARes to the merchants TermURL.
	 * @param $url_redirect
	 * @param $pareq
	 * @param $md64
	 */
	public function display3DSForm ($url_redirect, $pareq, $md, $url_validation) {
		?>
		<HTML>
		<HEAD>
			<TITLE><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_3DS_VERIFICATION') ?></TITLE>
			<SCRIPT LANGUAGE="Javascript">
				<!--
				function OnLoadEvent() {
					document.form.submit();
				}
				//-->
			</SCRIPT>
		</HEAD>
		<BODY onLoad="OnLoadEvent()">
		<FORM NAME="form" ACTION="<?php echo $url_redirect ?>" METHOD="POST">
			<INPUT TYPE="hidden" NAME="PaReq" VALUE="<?php echo $pareq ?>">
			<INPUT TYPE="hidden" NAME="TermUrl" VALUE="<?php echo $url_validation ?>">
			<INPUT TYPE="hidden" NAME="MD" VALUE="<?php echo $md ?>">
			<NOSCRIPT><INPUT TYPE="submit"></NOSCRIPT>
		</FORM>
		</BODY>
		</HTML>
		<?php
		exit;
	}

	function getRemoteDCCFormParams ($xml_response_dcc) {
		$params = $this->getRemoteCCFormParams($xml_response_dcc);
		return $params;

	}


	/**
	 * Checking if a card is 3D Secure enabled
	 * @return bool|mixed
	 */
	function request3DSVerifyEnrolled ($realvault = false) {
		if ($realvault) {
			$response = $this->requestRealVault3DSVerifyEnrolled($realvault);
		} else {
			$response = $this->requestCard3DSVerifyEnrolled();
		}
		return $response;
	}

	/**
	 * Checking if a card is 3D Secure enabled
	 * @return bool|mixed
	 */
	function requestCard3DSVerifyEnrolled () {

		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_3DS_VERIFYENROLLED);
		$xml_request .= $this->getXmlRequestCard();
		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), $this->getCCnumber());
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);
		return $response;
	}

	/**
	 * Checking if a card is 3D Secure enabled in case of A card stored in RealVault
	 * @param $realvault
	 * @return bool|mixed
	 */
	function requestRealVault3DSVerifyEnrolled ($realvault) {
		$payerRef = $this->getSavedPayerRef();
		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_REALVAULT_3DS_VERIFYENROLLED);
		$xml_request .= '<payerref>' . $payerRef . '</payerref>
			<paymentmethod>' . $realvault->realex_hpp_api_saved_pmt_ref . '</paymentmethod>';
		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), $payerRef);
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);
		return $response;
	}

	/**
	 * const VERIFYSIG_RESULT_VALIDATED = '00';
	const VERIFYSIG_RESULT_NOT_VALIDATED = '110';
	const VERIFYSIG_RESULT_INVALID_ACS_RESPONSE = '5xx';
	 *
	 * /**
	 * Response results from threedsecure = "3ds-verifysig";

	const THREEDSECURE_STATUS_AUTHENTICATED = 'Y';
	const THREEDSECURE_STATUS_NOT_AUTHENTICATED = 'N';
	const THREEDSECURE_STATUS_ACKNOWLEDGED = 'A';
	const THREEDSECURE_STATUS_UNAVAILABLE = 'U';
	 * 6. Take these values returned from the issuer, and create a 3ds-verifysig request. Send it to Realex.
	7. Depending on the result take the following action:
	a. If the result is “00” the message has not been tampered with. Continue:
	i. If the status is “Y”, the cardholder entered their passphrase correctly.
	 * This is a full 3DSecure transaction, go to step 8b.
	ii. If the status is “N”, the cardholder entered the wrong passphrase.
	 * No shift in liability, do not proceed to authorisation.
	iii. If the status is “U”, then the Issuer was having problems with their systems at the time
	 * and was unable to check the passphrase. You may continue with the transaction (go to step 8c) but there will be no shift in liability.
	iv. If the status is “A” the issuing bank acknowledges the attempt made by the merchant and accepts the liability shift. Continue to step 8a.
	19
	￼
	￼￼￼8.
	b. If the result is “110”, the digital signatures do not match the message and most likely the message has been tampered with. No shift in liability, do not proceed to authorisation.
	 * @param $response
	 * @return bool|int|string
	 */
	function  getEciFrom3DSVerifysig ($response, $requireLiabilityShift = true) {
		$xml_response = simplexml_load_string($response);
		$result = (string)$xml_response->result;

		if (substr($result, 0, 1) == '5') {
			$result = '5xx';
		}
		$allowLiabilityShift = false;
		$threedsecure = $xml_response->threedsecure;
		$threedsecure_status = (string)$threedsecure->status;
		if ($result == self::VERIFYSIG_RESULT_VALIDATED) {
			switch ($threedsecure_status) {
				case self::THREEDSECURE_STATUS_AUTHENTICATED:
					$allowLiabilityShift = true;
					$threedSecureAuthentication = true;
					break;

				case self::THREEDSECURE_STATUS_ACKNOWLEDGED:
					/**
					 * If the status is “A” the issuing bank acknowledges the attempt made by the merchant and
					 * accepts the liability shift.
					 * Continue to step 8a.
					 * a. Send a normal Realex authorisation message (set the ECI field to 6 or 1).
					 * The merchant will not be liable for repudiation chargebacks.
					 * */
					$allowLiabilityShift = true;
					$threedSecureAuthentication = false;
					break;

				case self::THREEDSECURE_STATUS_NOT_AUTHENTICATED:
					/**
					 * If the status is “N”, the cardholder entered the wrong passphrase.
					 * No shift in liability, do not proceed to authorisation.
					 * if the merchants chooses "Require liability shift" as "No"
					 * then the transaction will proceed to authorisation
					 * but there will not be a liability shift and the ECI value should be 7
					 */
					$allowLiabilityShift = false;
					$threedSecureAuthentication = false;
					break;

				default:
				case self::THREEDSECURE_STATUS_UNAVAILABLE:
					/**
					 * If the status is “U”, then the Issuer was having problems with their systems at the time
					 * and was unable to check the passphrase.
					 * You may continue with the transaction (go to step 8c) but there will be no shift in liability.
					 * Send a normal Realex authorisation message (set the ECI field to 7 or 0).
					 * The merchant will be liable for repudiation chargebacks.
					 */
					$allowLiabilityShift = false;
					$threedSecureAuthentication = false;
					break;
			}
		} else {
			/**
			 * If the result is “110”, the digital signatures do not match the message
			 * and most likely the message has been tampered with. No shift in liability, do not proceed to authorisation.
			 */
			$allowLiabilityShift = false;
			$threedSecureAuthentication = false;
		}
		if (!$allowLiabilityShift and $requireLiabilityShift) {
			return false;
		}
		$eci = $this->getEciValue($allowLiabilityShift, $threedSecureAuthentication);
		return $eci;
	}

	/**
	 * 4. Depending on the response take the following action:
	a. If the response code is “00” and the enrolled tag is “Y” there will also be a URL returned. Redirect the cardholder to this URL using a hidden form.
	b. If the response code is “110” and the enrolled tag is “N” the cardholder is not enrolled. Skip to step 8a.
	c. If the enrolled tag is “U” the enrolled status could not be verified. Skip to step 8c.
	d. If the response is “220” the card scheme directory server may be unavailable. You may
	still proceed to authorisation but no liability shift will be received. Skip to step 8c.
	 * @param $xml_response
	 * @return bool|int|null
	 */

	public function getEciFrom3DSVerifyEnrolled ($response) {
		$xml_response = simplexml_load_string($response);
		$result = (string)$xml_response->result;

		if (substr($result, 0, 1) == '5') {
			$result = '5xx';
		}

		switch ($result) {
			case self::ENROLLED_RESULT_ENROLLED:
				$liabilityShift = true;
				break;

			case self::ENROLLED_RESULT_NOT_ENROLLED:
				if ($xml_response->enrolled == self::ENROLLED_TAG_NOT_ENROLLED) {
					$liabilityShift = true;
				} else {
					$liabilityShift = false;
				}
				break;

			default:
			case self::ENROLLED_RESULT_INVALID_RESPONSE:
			case self::ENROLLED_RESULT_FATAL_ERROR:
				$liabilityShift = false;
				break;
		}

		// if there is no liability shift, and it is required by the client, throw exception
		$eci = false;
		if (!$liabilityShift && $this->_method->require_liability) {
			return $eci;
		}

		// determine the eci value to use if the card is not enrolled in the 3D Secure scheme
		if ($xml_response->enrolled != self::ENROLLED_TAG_ENROLLED) {
			$eci = $this->getEciValue($liabilityShift);
		}

		return $eci;
	}

	/**
	 * Retrieve the ECI value for the provided card type, liability and 3D Secure result.
	 *
	 */
	public function getEciValue ($allowLiabilityShift, $threedSecureAuthentication = false) {
		$eci_value = false;
		$cc_type = $this->customerData->getVar('cc_type');
		if ($cc_type == '') {
			$this->debugLog('CC TYPE IS EMPTY', 'getEciValue', 'debug');
		}
		if ($cc_type == 'VISA' or $cc_type == 'AMEX') {
			if ($threedSecureAuthentication === true) {
				$eci_value = self::ECI_AUTHENTICATED_VISA;

			} else {
				if ($allowLiabilityShift === true) {
					$eci_value = self::ECI_LIABILITY_SHIFT_VISA;
				} else {
					$eci_value = self::ECI_NO_LIABILITY_SHIFT_VISA;
				}
			}
		} else {
			if ($threedSecureAuthentication === true) {
				$eci_value = self::ECI_AUTHENTICATED_MASTERCARD;

			} else {
				if ($allowLiabilityShift === true) {
					$eci_value = self::ECI_LIABILITY_SHIFT_MASTERCARD;
				} else {
					$eci_value = self::ECI_NO_LIABILITY_SHIFT_MASTERCARD;

				}
			}
		}

		return $eci_value;
	}


	/**
	 * @param $response
	 * @param $order
	 * @return null|string
	 */
	private function manageResponse3DSecure ($response) {
		$this->_storeRealexInternalData($response, $this->_currentMethod->virtuemart_paymentmethod_id, $this->order['details']['BT']->virtuemart_order_id, $this->order['details']['BT']->order_number, $this->request_type);

		$xml_response_3DSecure = simplexml_load_string($response);
		$responseAuth = '';

		$BT = $this->order['details']['BT'];
		$order_number = $BT->order_number;
		$success = $this->isResponseSuccess($xml_response_3DSecure);

		$eci = $this->getEciFrom3DSVerifyEnrolled($xml_response_3DSecure);
		if ($eci == NULL) {
			return NULL;
		}
		if (!$eci) {
			$this->redirect3DSRequest($xml_response_3DSecure);

		} else {
			$xml_response_3DSecure->addChild('eci', $eci);
			$responseAuth = $this->requestAuth(NULL, $xml_response_3DSecure);
			$this->manageResponseRequestAuth($response);
		}


		return $responseAuth;

	}

	/**
	 * @return bool|mixed
	 */
	function request3DSVerifysig ($realvault = false) {
		$paRes = vRequest::getVar('PaRes', '');
		if (empty($paRes)) {
			$this->plugin->redirectToCart(vmText::_('VMPAYMENT_REALEX_HPP_API_ERROR_TRY_AGAIN'));
		}

		$jsonencodeMd = vRequest::getVar('MD', '');
		$md = $this->getMd($jsonencodeMd);
		$this->customerData->saveCustomerMDData($md);

		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_3DS_VERIFYSIG);
		if (!$realvault) {
			$xml_request .= $this->getXmlRequestCard();

			$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), $this->getCCnumber());
		} else {
			$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), "");

		}

		$xml_request .= '<pares>' . $paRes . '</pares>
		';

		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);
		return $response;
	}


	/**
	 * @param null $xml_response_dcc
	 * @param null $xml_3Dresponse
	 * @return bool|mixed
	 */
	function requestAuth ($xml_response_dcc = NULL, $xml_3Dresponse = NULL) {

		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_AUTH);
		$xml_request .= '<card>
				<number>' . $this->getCCnumber() . '</number>
				<expdate>' . $this->getFormattedExpiryDateForRequest() . '</expdate>
				<chname>' . $this->sanitize($this->customerData->getVar('cc_name')) . '</chname>
				<type>' . $this->getCCtype($this->customerData->getVar('cc_type')) . '</type>
				<issueno></issueno>
				<cvn>
				<number>' . $this->customerData->getVar('cc_cvv') . '</number>
				<presind>1</presind>
				</cvn>
				</card>
				';
		if ($this->_method->dcc) {
			$xml_request .= '<autosettle flag="1" />
			';
		} else {
			$xml_request .= '<autosettle flag="' . $this->getSettlement() . '" />
			';
		}

		$xml_request .= $this->setMpi($xml_3Dresponse);
		if ($this->_method->dcc) {
			$xml_request .= $this->setDccInfo($xml_response_dcc);
		}
		$xml_request .= $this->setComments();
		$xml_request .= $this->setTssInfo();

		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), $this->getCCnumber());
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '<md5hash></md5hash>';
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);

		return $response;
	}


	/**
	 *
	 *7. Depending on the result take the following action:
	a. If the result is “00” the message has not been tampered with. Continue:
	i. If the status is “Y”, the cardholder entered their passphrase correctly. This is a full 3DSecure transaction, go to step 8b.
	ii. If the status is “N”, the cardholder entered the wrong passphrase. No shift in liability, do not proceed to authorisation.
	iii. If the status is “U”, then the Issuer was having problems with their systems at the time and was unable to check the passphrase. You may continue with the transaction (go to step 8c) but there will be no shift in liability.
	iv. If the status is “A” the issuing bank acknowledges the attempt made by the merchant and accepts the liability shift. Continue to step 8a.
	19
	￼
	￼￼￼8.
	b. If the result is “110”, the digital signatures do not match the message and most likely the message has been tampered with. No shift in liability, do not proceed to authorisation.
	 *
	 * @param $response3D
	 * @return bool|mixed
	 */
	function manageResponse3DSVerifyEnrolled ($response3D) {
		$this->manageResponseRequest($response3D);
	}

	function manageResponse3DSVerifysig ($response3DSVerifysig) {
		$this->manageResponseRequest($response3DSVerifysig);

	}

	/**
	 * @param $response
	 */
	function manageResponseRequestAuth ($response) {
		$this->manageResponseRequest($response);
	}

	/**
	 * @param $response
	 */

	function manageResponseRequest3DSecure ($response) {
		$this->manageResponseRequest($response);


	}

	/**
	 * @param $xml_response_dcc
	 * @return string
	 */
	function setDCCInfo ($xml_response_dcc) {
		$dcc_choice = $this->customerData->getVar('dcc_choice');

		if ($dcc_choice) {
			$rate = $xml_response_dcc->dccinfo->cardholderrate;
			$currency = $xml_response_dcc->dccinfo->cardholdercurrency;
			$amount = $xml_response_dcc->dccinfo->cardholderamount;
		} else {
			$rate = 1;
			if ($xml_response_dcc AND $this->isResponseSuccess($xml_response_dcc)) {
				$currency = $xml_response_dcc->dccinfo->merchantcurrency;
				$amount = $xml_response_dcc->dccinfo->merchantamount;
			} else {
				$currency = $this->getPaymentCurrency();
				$amount = $this->getTotalInPaymentCurrency();
			}

		}
		$xml_request = '<dccinfo>
						<ccp>' . $this->_method->dcc_service . '</ccp>
						<type>1</type>
						<rate>' . $rate . '</rate>
						<ratetype>S</ratetype>
						<amount currency="' . $currency . '">' . $amount . '</amount>
						</dccinfo>
				';
		return $xml_request;
	}

	/**
	 * @param bool $realvault
	 * @return bool|mixed
	 */
	public function requestDccrate ($realvault = false) {
		$payerRef = $this->getSavedPayerRef();
		$request_type = ($realvault) ? self::REQUEST_TYPE_REALVAULT_DCCRATE : self::REQUEST_TYPE_DCCRATE;
		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, $request_type);

		if ($realvault) {
			$xml_request .= '<payerref>' . $payerRef . '</payerref>
			<paymentmethod>' . $realvault->realex_hpp_api_saved_pmt_ref . '</paymentmethod>
			';
			$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), $payerRef);

		} else {
			$xml_request .= $this->getXmlRequestCard();
			$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), $this->getCCnumber());

		}
		$xml_request .= '<dccinfo>
			<ccp>' . $this->_method->dcc_service . '</ccp>
			<type>1</type>
		</dccinfo>
		';


		$xml_request .= $this->setComments();

		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '<md5hash></md5hash>';
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);

		return $response;
	}


	/**
	 * possible results:
	 * 105: Card not supported by DCC
	 * 00: Card supported by eDCC
	 * @param $response
	 * @param $order
	 * @return null|string
	 */
	function manageResponseDccRate ($response) {
		$this->manageResponseRequest($response);
	}

	function getXmlRequestCard () {

		$xml_request = '<card>
		                    <number>' . $this->getCCnumber() . '</number>
		                    <expdate>' . $this->getFormattedExpiryDateForRequest() . '</expdate>
	                        <chname>' . $this->sanitize($this->customerData->getVar('cc_name')) . '</chname>
	                        <type>' . $this->getCCtype($this->customerData->getVar('cc_type')) . '</type>
						 </card>';

		return $xml_request;
	}

	function getCCnumber () {
		return str_replace(" ", "", $this->customerData->getVar('cc_number'));
	}

	/*********************/
	/* Log and Reporting */
	/*********************/
	public function debug ($subject, $title = '', $echo = true) {

		$debug = '<div style="display:block; margin-bottom:5px; border:1px solid red; padding:5px; text-align:left; font-size:10px;white-space:nowrap; overflow:scroll;">';
		$debug .= ($title) ? '<br /><strong>' . $title . ':</strong><br />' : '';
		//$debug .= '<pre>';
		if (is_array($subject)) {
			$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", nl2br(str_replace(" ", " &nbsp; ", print_r($subject, true)))));
		} else {
			$debug .= str_replace("=>", "&#8658;", str_replace("Array", "<font color=\"red\"><b>Array</b></font>", (str_replace(" ", " &nbsp; ", print_r($subject, true)))));

		}

		//$debug .= '</pre>';
		$debug .= '</div>';
		if ($echo) {
			echo $debug;
		} else {
			return $debug;
		}
	}

	function highlight ($string) {
		return '<span style="color:red;font-weight:bold">' . $string . '</span>';
	}

	public function debugLog ($message, $title = '', $type = 'message', $echo = false, $doVmDebug = false) {

		//Nerver log the full credit card number nor the CVV code.
		if (is_array($message)) {
			if (array_key_exists('REALEX_SAVED_PMT_DIGITS', $message)) {
				$message['REALEX_SAVED_PMT_DIGITS'] = "**** **** **** " . substr($message['REALEX_SAVED_PMT_DIGITS'], -4);
			}
			if (array_key_exists('SHA1HASH', $message)) {
				$message['SHA1HASH'] = '**MASKED**';
			}
			if (array_key_exists('CVV2', $message)) {
				$message['CVV2'] = str_repeat('*', strlen($message['CVV2']));
			}
			if (array_key_exists('signature', $message)) {
				$message['signature'] = '**MASKED**';
			}
			if (array_key_exists('api_password', $message)) {
				$message['api_password'] = '**MASKED**';
			}
		}

		if ($this->_method->debug) {
			$this->debug($message, $title, true);
		}

		if ($echo) {
			echo $message . '<br/>';
		}


		$this->plugin->debugLog($message, $title, $type, $doVmDebug);
	}

	function getPaymentLang () {
		$available_languages = array(
			'en-GB' => 'en',
			'es-ES' => 'es',
		);
		$default_language = 'en';

		$language = JFactory::getLanguage();

		if (array_key_exists($language->getTag(), $available_languages)) {
			return $available_languages[$language->getTag()];
		} else {
			return $default_language;
		}
	}

	function getCardPaymentButton ($card_payment_button) {
		$lang = JFactory::getLanguage();
		if ($lang->hasKey($card_payment_button)) {
			return vmText::_($card_payment_button);
		} else {
			return $card_payment_button;
		}
	}

	/**
	 * @param $response
	 */

	function manageResponseRequest ($response) {
		if ($response == NULL) {
			$this->plugin->redirectToCart(vmText::_('VMPAYMENT_REALEX_HPP_API_ERROR_TRY_AGAIN'));
		}
		if (!$this->validateResponseHash($response)) {
			$this->plugin->redirectToCart(vmText::_('VMPAYMENT_REALEX_HPP_API_ERROR_TRY_AGAIN'));
		}
		$xml_response = simplexml_load_string($response);
		if ($this->isResponseInvalidPaymentDetails($xml_response)) {
			$msgToShopper=vmText::sprintf('VMPAYMENT_REALEX_HPP_API_INVALID_PAYMENT_DETAILS',$xml_response->message);
			$this->plugin->redirectToCart($msgToShopper);
		}
		$this->plugin->_storeRealexInternalData($response, $this->_method->virtuemart_paymentmethod_id, $this->order['details']['BT']->virtuemart_order_id, $this->order['details']['BT']->order_number, $this->request_type);
		/*
				$xml_response = simplexml_load_string($response);

				$success = $this->isResponseSuccess($xml_response);
				$reponse_enrolled=($xml_response->result==self::ENROLLED_RESULT_NOT_ENROLLED);
				$not_enrolled = ($xml_response->enrolled == self::ENROLLED_TAG_NOT_ENROLLED);
				if (! ($success OR $reponse_enrolled)) {
					$error = (string)$xml_response->message . " (" . (string)$xml_response->result . ")";
					$this->displayError($error);
					$this->plugin->redirectToCart(vmText::_('VMPAYMENT_REALEX_HPP_API_ERROR_TRY_AGAIN'));
				}
		*/
	}

	/**
	 * @param $response
	 */
	function manageResponseRequestReceiptIn ($response) {
		$xml_response = simplexml_load_string($response);
		if ($this->isResponseInvalidPaymentDetails($xml_response)) {
			$accountURL=JRoute::_('index.php?option=com_virtuemart&view=user&layout=edit');
			$msgToShopper=vmText::sprintf('VMPAYMENT_REALEX_HPP_API_INVALID_PAYMENT_DETAILS_REALVAULT',$xml_response->message, $accountURL);
			$this->plugin->redirectToCart($msgToShopper);
		}

		$this->manageResponseRequest($response);
	}

	public function validateConfirmedOrder ($enqueueMessage = true) {
		return true;
	}

	public function validateSelectCheckPayment ($enqueueMessage = true) {
		return true;
	}

	/**
	 * @param bool $enqueueMessage
	 * @return bool
	 */
	function validateCheckoutCheckDataPayment () {
		return true;
	}

	function getOrderBEFields () {
		$showOrderBEFields = array(

			'RESULT'            => 'result',
			'PASREF'            => 'pasref',
			'AUTHCODE'          => 'authcode',
			'MESSAGE'           => 'message',
			'TSS'               => 'tss->result',
			'CAVV'              => 'cavv',
			'CVNRESULT'         => 'cvnresult',
			'AVSPOSTCODERESULT' => 'avspostcoderesult',
			'AVSPOSTCODERESPONSE' => 'avspostcoderesponse',
			'AVSADDRESSRESPONSE' => 'avsaddressresponse',
			'DCCCHOICE'         => 'dccchoice',
			'REALWALLET_CHOSEN' => 'realwallet_chosen',
		);


		return $showOrderBEFields;
	}


	function getOrderBEFieldsDcc () {
		$showOrderBEFields = array(

			'DCCRATE'                        => 'dccrate',
			'DCCMERCHANTAMOUNT'              => 'merchantamount',
			'DCCMERCHANTCURRENCY'            => 'merchantcurrency',
			'DCCCARDHOLDERAMOUNT'            => 'cardholderamount',
			'DCCCARDHOLDERCURRENCY'          => 'cardholdercurrency',

		);


		return $showOrderBEFields;
	}

	function getOrderBEFields3DS () {
		$showOrderBEFields = array(
			'status' => 'status',
			'eci' => 'eci',
		);


		return $showOrderBEFields;
	}

	function onShowOrderBEPayment ($data, $format, $request_type_response, $virtuemart_order_id) {

		$showOrderBEFields = $this->getOrderBEFields();
		$prefix = 'REALEX_HPP_API_RESPONSE_';

		$html = '';
		if ($request_type_response == 'receipt-in_request') {
			if (!class_exists('VirtueMartModelOrders')) {
				require(VMPATH_ADMIN . DS . 'models' . DS . 'orders.php');
			}
			$orderModel = VmModel::getModel('orders');
			$order = $orderModel->getOrder($virtuemart_order_id);
			$usedCC = $this->getStoredCCByPmt_ref($order['details']['BT']->virtuemart_user_id, $data->paymentmethod);
			$this->loadJLangThis('plg_vmuserfield_realex_hpp_api');
			$display_fields = array(
				'realex_hpp_api_saved_pmt_type',
				'realex_hpp_api_saved_pmt_digits',
				'realex_hpp_api_saved_pmt_expdate',
				'realex_hpp_api_saved_pmt_name'
			);
			foreach ($display_fields as $display_field) {
				$complete_key = strtoupper('VMUSERFIELD_' . $display_field);

				$value = $usedCC->$display_field;
				$key_text = vmText::_($complete_key);
				$value = vmText::_($value);
				if (!empty($value)) {
					$html .= "<tr>\n<td>" . $key_text . "</label></td>\n <td align='left'>" . $value . "</td>\n</tr>\n";
				}
			}

		} else {
			foreach ($showOrderBEFields as $key => $showOrderBEField) {
				if ($format == "xml") {
					$showOrderBEField = strtolower($showOrderBEField);
				} else {
					$showOrderBEField=$key;
				}
				if (isset($data->$showOrderBEField) and !empty($data->$showOrderBEField)) {
					$key = $prefix . $key;
						$html .= $this->plugin->getHtmlRowBE($key, $data->$showOrderBEField);
				}

			}

			if (isset($data->DCCCHOICE) or  isset($data->dccinfo)) {
				if ($format == "xml") {
					$dccinfo=$data->dccinfo;
				} else {
					$dccinfo=$data;
				}
				$showOrderBEFields = $this->getOrderBEFieldsDcc();
				foreach ($showOrderBEFields as $key => $showOrderBEField) {
					if ($format == "xml") {
						$showOrderBEField = strtolower($showOrderBEField);
					} else {
						$showOrderBEField=$key;
					}
					if (isset($dccinfo->$showOrderBEField)) {
						$key = $prefix . $key;
						$html .= $this->plugin->getHtmlRowBE($key, $dccinfo->$showOrderBEField);
					}

				}
			}

			if (isset($data->threedsecure)) {
				$showOrderBEFields = $this->getOrderBEFields3DS();
				$prefix = 'REALEX_HPP_API_RESPONSE_THREEDSECURE_';
				foreach ($showOrderBEFields as $key => $showOrderBEField) {
					if (isset($data->threedsecure->$showOrderBEField)) {
						$key = $prefix . $key;
						$html .= $this->plugin->getHtmlRowBE($key, $data->threedsecure->$showOrderBEField);
					}

				}
			}
		}
		return $html;
	}

	/**
	 * @param $response
	 * @param $order
	 * @return null|string
	 */
	function getResponseParams ($payments) {

		$dcc_info = "";
		$payer_info = "";
		$auth_info = "";
		$order_history = $this->order['history'];
		$last_history = end($order_history);
		if ($last_history->order_status_code == $this->_method->status_canceled) {
			$auth_info = $last_history->comments;
			$success = false;
		} else {

		}

		vmLanguage::loadJLang('com_virtuemart_orders', TRUE);

		// FROM PAYMENT LOG??? why not from history
		if ($payments) {
			foreach ($payments as $payment) {
				if (isset($payment->realex_hpp_api_fullresponse) and !empty($payment->realex_hpp_api_fullresponse)) {
					if ($payment->realex_hpp_api_fullresponse_format == 'xml') {
						$xml_response = simplexml_load_string($payment->realex_hpp_api_fullresponse);
						if ($payment->realex_hpp_api_request_type_response == $this::REQUEST_TYPE_AUTH OR $payment->realex_hpp_api_request_type_response == $this::REQUEST_TYPE_RECEIPT_IN) {
							$success = $this->isResponseSuccess($xml_response);

							if ($success) {
								if (isset($xml_response->dccinfo) AND isset($xml_response->dccinfo->cardholderrate)) {
									if ($xml_response->dccinfo->cardholderrate != 1.0) {
										$dcc_info = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_CHARGED', $this->plugin->getCardHolderAmount($xml_response->dccinfo->merchantamount), $xml_response->dccinfo->merchantcurrency, $this->plugin->getCardHolderAmount($xml_response->dccinfo->cardholderamount), $xml_response->dccinfo->cardholdercurrency);
									} else {
										$dcc_info = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_MERCHANT_CURRENCY', $this->plugin->getCardHolderAmount($xml_response->dccinfo->merchantamount), $xml_response->dccinfo->merchantcurrency);
									}
								}
								$amountValue = vmPSPlugin::getAmountInCurrency($this->order['details']['BT']->order_total, $this->order['details']['BT']->order_currency);
								//$currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

								$auth_info = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CONFIRMED', $amountValue['display'], $this->order['details']['BT']->order_number);
								$pasref = $payment->realex_hpp_api_response_pasref;
							} else {
								if ($this->isResponseDeclined($xml_response)) {
									$auth_info = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_DECLINED', $this->order['details']['BT']->order_number);
								} else {
									$auth_info = vmText::_('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED');
								}
							}

						} elseif ($payment->realex_hpp_api_request_type_response == $this::REQUEST_TYPE_CARD_NEW) {
							$success = $this->isResponseSuccess($xml_response);
							if ($success) {
								$payer_info = vmText::_('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_SUCCESS');
							} else {
								$payer_info = vmText::_('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_FAILED');
							}
						}

					} else {
						if ($payment->realex_hpp_api_fullresponse_format == 'json') {
							$realex_data = json_decode($payment->realex_hpp_api_fullresponse);
							$result = $payment->realex_hpp_api_response_result;
							$payer_info = '';
							$success = ($result == self::RESPONSE_CODE_SUCCESS);

							if ($success) {
								$amountValue = vmPSPlugin::getAmountInCurrency($this->order['details']['BT']->order_total, $this->order['details']['BT']->order_currency);
								//$currencyDisplay = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

								$auth_info = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CONFIRMED', $amountValue['display'], $this->order['details']['BT']->order_number);
								if (isset($realex_data->DCCCHOICE) and $realex_data->DCCCHOICE == $this::RESPONSE_DCC_CHOICE_YES) {
									$dcc_info = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_CHARGED', $this->plugin->getCardHolderAmount($realex_data->DCCMERCHANTAMOUNT), $realex_data->DCCMERCHANTCURRENCY, $this->plugin->getCardHolderAmount($realex_data->DCCCARDHOLDERAMOUNT), $realex_data->DCCCARDHOLDERCURRENCY);
								}
								if (isset($realex_data->REALWALLET_CHOSEN) and  $realex_data->REALWALLET_CHOSEN == 1) {
									if ((isset($realex_data->PMT_SETUP) and  $realex_data->PMT_SETUP == self::PMT_SETUP_SUCCESS)) {
										$payer_info = vmText::_('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_SUCCESS');
									} else {
										$payer_info = vmText::_('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_FAILED');
									}
								}
								$pasref = $realex_data->PASREF;


							} else {
								if ($this->isResponseDeclined($xml_response)) {
									$auth_info = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_DECLINED', $this->order['details']['BT']->order_number);
								} else {
									$auth_info = vmText::_('VMPAYMENT_REALEX_HPP_API_PAYMENT_STATUS_CANCELLED');
								}
							}
						}
					}
				}
			}
		}


		$params=  array(
			"success"      => $success,
			"dcc_info"     => $dcc_info,
			"auth_info"    => $auth_info,
			"payer_info"   => $payer_info,
			"pasref"       => $pasref,
			"order_number" => $this->order['details']['BT']->order_number,
			"order_pass"   => $this->order['details']['BT']->order_pass,
		);
		return $params;


	}

	function handleCardStorage ($saved_cc_selected) {
		$userfield = false;
		// if no card are already saved, then
		if ($saved_cc_selected == -1 OR empty($saved_cc_selected)) {
			if ($this->doRealVault()) {
				$setNewPayerSuccess = true;
				$payerRef = $this->getSavedPayerRef();
				if (!$payerRef) {
					$payerRef = $this->getNewPayerRef();
					$responseNewPayer = $this->setNewPayer($payerRef);
					$setNewPayerSuccess = $this->manageSetNewPayer($responseNewPayer);
					if ($setNewPayerSuccess) {
						$this->saveNewPayerRef($payerRef, $this->order['details']['BT']->virtuemart_user_id);
					}
				}

				if ($setNewPayerSuccess) {
					$newPaymentRef = "";
					$responseNewPayment = $this->setNewPayment($payerRef, $newPaymentRef);
					$userfield = $this->manageSetNewPayment($responseNewPayment, $payerRef, $newPaymentRef);
				}
			}
		}

		return $userfield;
	}

	/**
	 * @param $response
	 * @param $realexInterface
	 * @return bool
	 */
	function manageSetNewPayer ($response) {
		$this->plugin->_storeRealexInternalData($response, $this->_currentMethod->virtuemart_paymentmethod_id, $this->order['details']['BT']->virtuemart_order_id, $this->order['details']['BT']->order_number, $this->request_type);

		$xml_response = simplexml_load_string($response);

		$success = $this->isResponseSuccess($xml_response);

		if (!$success) {
			$error = $xml_response->message . " (" . (string)$xml_response->result . ")";
			$this->displayError($error);
			vmInfo('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_FAILED');
			return false;
		}
		return true;
	}


	/**
	 * @param $response
	 * @param $realexInterface
	 * @return bool
	 */
	function manageSetNewPayment ($response, $newPayerRef, $newPaymentRef) {
		$this->plugin->_storeRealexInternalData($response, $this->_method->virtuemart_paymentmethod_id, $this->order['details']['BT']->virtuemart_order_id, $this->order['details']['BT']->order_number, $this->request_type);

		$xml_response = simplexml_load_string($response);

		$success = $this->isResponseSuccess($xml_response);

		if (!$success) {
			$error = $xml_response->message . " (" . (string)$xml_response->result . ")";
			$this->displayError($error);
			vmInfo('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_FAILED');
			return false;
		}

		$userfield['virtuemart_user_id'] = $this->order['details']['BT']->virtuemart_user_id;
		$userfield['merchant_id'] = $this->_method->merchant_id;
		$userfield['realex_hpp_api_saved_pmt_ref'] = $newPaymentRef;
		//$userfield['realex_saved_payer_ref'] = $newPayerRef;
		$userfield['realex_hpp_api_saved_pmt_type'] = $this->customerData->getVar('cc_type');
		if (!class_exists('shopFunctionsF')) {
			require(VMPATH_SITE . DS . 'helpers' . DS . 'shopfunctionsf.php');
		}
		$userfield['realex_hpp_api_saved_pmt_digits'] = shopFunctionsF::mask_string($this->customerData->getVar('cc_number'), '*');
		$userfield['realex_hpp_api_saved_pmt_name'] = $this->customerData->getVar('cc_name');
		$userfield['realex_hpp_api_saved_pmt_expdate'] = $this->getFormattedExpiryDateForRequest();

		return $userfield;
	}

	/**
	 * Customer number gets sanitize to make sure that it contains only valid characters
	 * @return mixed
	 */
	function getNewPayerRef () {
		return $this->getUniqueId($this->order['details']['BT']->customer_number);
	}

	/**
	 * If Realvault, and new card, store after Authorization the card
	 */
	function storeNewPayment ($userfield) {
		if (!$userfield) {
			return;
		}
		if (!class_exists('VmTableData')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'vmtabledata.php');
		}
		JLoader::import('joomla.plugin.helper');
		JPluginHelper::importPlugin('vmuserfield');
		$app = JFactory::getApplication();
		$value = '';
		$userfield['fromPayment'] = true;
		$app->triggerEvent('plgVmPrepareUserfieldDataSave', array(
		                                                         'pluginrealex_hpp_api',
		                                                         'realex_hpp_api',
		                                                         &$userfield,
		                                                         &$value,
		                                                         $userfield
		                                                    ));


		//vmInfo('VMPAYMENT_REALEX_HPP_API_CARD_STORAGE_SUCCESS');
	}


	public function loadCustomerData ($loadCDFromPost = true) {
		if (!class_exists('RealexHelperCustomerData')) {
			require(JPATH_SITE . '/plugins/vmpayment/realex_hpp_api/realex_hpp_api/helpers/customerdata.php');
		}
		$this->getCustomerData();
		if ($loadCDFromPost) {
			$this->customerData->loadPost();
		}

	}

	public function getCustomerData () {
		if (!class_exists('RealexHelperCustomerData')) {
			require(JPATH_SITE . '/plugins/vmpayment/realex_hpp_api/realex_hpp_api/helpers/customerdata.php');
		}
		$this->customerData = new RealexHelperCustomerData();
		$this->customerData->load();
	}

	/**
	 * @param bool $enqueueMessage
	 * @return bool
	 */
	function validateCvv ($enqueueMessage = true) {
		if (!class_exists('Creditcard')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'creditcard.php');
		}
		if (!$this->_method->cvn_checking) {
			return true;
		}

		$cc_cvv_realvault = vRequest::getInt('cc_cvv_realvault', '');

		if (!Creditcard::validate_credit_card_cvv('', $cc_cvv_realvault, true)) {
			$app = JFactory::getApplication();
			$app->enqueueMessage(vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CARD_CVV_INVALID'), 'error');
			return false;
		}
		return true;
	}

	/**
	 * @param $saved_cc_selected
	 * @return mixed
	 */
	function getStoredCCsData ($saved_cc_selected) {
		$realvault = false;
		$storedCCs = $this->getStoredCCs(JFactory::getUser()->id);
		foreach ($storedCCs as $storedCC) {
			if ($storedCC->id == $saved_cc_selected) {
				$realvault = $storedCC;
				break;
			}
		}
		return $realvault;
	}

	/**
	 * @return array
	 */
	static function getRealexCreditCards () {
		return array(
			'VISA',
			'MC',
			'AMEX',
			'MAESTRO',
			'DINERS',
		);

	}

	function getVendorInfo ($field) {

		$virtuemart_vendor_id = 1;
		$vendorModel = VmModel::getModel('vendor');
		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);

		return $vendor->$field;

	}

	/**
	 * the reference to use for the payment method saved.
	 * If this field is not present an alphanumeric reference will be automatically generated.
	 * @return string
	 */
	function getPmtRef () {
		return '';
	}

	/**
	 * This field contains the payer reference used for this cardholder.
	 * If this field is empty or missing and PAYER_EXIST = 0, then a PAYER_REF will be automatically generated.
	 * To add another card to an existing payer the PAYER_REF field should be set to their existing payer reference.
	 * This field is mandatory if the CARD_STORAGE_ENABLE is set to 1 and
	 * the PAYER_EXIST flag is set to 1. If PAYER_EXIST = 1 and CARD_STORAGE_ENABLE = 1, a 5xx error will be returned if the field is empty or missing:
	 * 5xx “Mandatory field missing. PAYER_REF not present in request”
	 * @return string
	 */


	function saveNewPayerRef ($payerRef) {
		$virtuemart_user_id = $this->order['details']['BT']->virtuemart_user_id;

		if (!$this->getSavedPayerRef($virtuemart_user_id)) {
			$PayerRefTableName = $this->plugin->getPayerRefTableName();
			$q = 'INSERT INTO `' . $PayerRefTableName . '` (`virtuemart_user_id`,`payer_ref`, `merchant_id`) VALUES ("' . $virtuemart_user_id . '","' . $payerRef . '","' . $this->_method->merchant_id . '")';

			$db = JFactory::getDBO();
			$db->setQuery($q);
			$db->execute();
			$err = $db->getErrorMsg();
			if (!empty($err)) {
				vmError('Database error: Realex saveNewPayerRef ' . $err);
			}
		}

	}

	/**
	 * PayerRef is the customer number which is unique
	 * @return mixed
	 */
	function getSavedPayerRef ($userId = '') {
		if (empty($userId)) {
			if (JFactory::getApplication()->isSite()) {
				$userId = $this->order['details']['BT']->virtuemart_user_id;
			} else {
				$userId = vRequest::getVar('virtuemart_user_id', 0);
				if (is_array($userId)) {
					$userId = $userId[0];
				}
			}
		}

		$PayerRefTableName = $this->plugin->getPayerRefTableName();
		$q = 'SELECT `payer_ref` FROM `' . $PayerRefTableName . '` WHERE `virtuemart_user_id`="' . $userId . '" AND `merchant_id`="' . $this->_method->merchant_id . '" ';
		$db = JFactory::getDBO();
		$db->setQuery($q);
		$payer_ref = $db->loadResult();

		return $payer_ref;


	}

	/**
	 * @param $storedCCs
	 * @param $selected
	 * @return null
	 */
	function getSelectedCC ($storedCCs, $selected) {
		if (empty($storedCCs)) {
			return NULL;
		}
		foreach ($storedCCs as $storedCC) {
			if ($storedCC->id == $selected) {
				return $storedCC;
			}
		}

	}

	/**
	 * @param $selected_cc_id
	 * @return null|stdClass
	 */
	function getSelectedCCParams ($selected_cc_id) {


		if ($selected_cc_id == -1) {
			$selectedCCParams = new stdClass();
			$selectedCCParams->addNew = true;
			return $selectedCCParams;
		}
		$storedCCs = $this->getStoredCCs(JFactory::getUser()->id);

		$selectedCC = $this->getSelectedCC($storedCCs, $selected_cc_id);
		if ($selectedCC == NULL) {
			return NULL;
		}
		$selectedCCParams = $selectedCC;
		$selectedCCParams->addNew = false;
		return $selectedCCParams;
	}

	/**
	 * @var
	 */
	static $_storedCCsCache;

	function getStoredCCs ($virtuemart_user_id) {
		//$this->debugLog( $virtuemart_user_id, 'getStoredCCs', 'debug');

		if (!empty(self::$_storedCCsCache)) {
			if (isset(self::$_storedCCsCache[$virtuemart_user_id])) {
				$storedCCs = self::$_storedCCsCache[$virtuemart_user_id]['storedCC'];
				//$this->debugLog('from cache', 'getStoredCCs', 'debug');
				return $storedCCs;
			}
		}


		JLoader::import('joomla.plugin.helper');
		JPluginHelper::importPlugin('vmuserfield');
		$app = JFactory::getApplication();

		$storedCCs = "";
		$app->triggerEvent('plgVmOnPaymentDisplay', array('pluginrealex_hpp_api', $virtuemart_user_id, &$storedCCs));
		//$this->debugLog(var_export($storedCCs, true), 'getStoredCCs after plgVmOnPaymentDisplay', 'debug');
		if (empty(self::$_storedCCsCache)) {
			self::$_storedCCsCache = array();
		}
		if (empty(self::$_storedCCsCache[$virtuemart_user_id])) {
			self::$_storedCCsCache[$virtuemart_user_id] = array();
		}
		self::$_storedCCsCache[$virtuemart_user_id]['storedCC'] = $storedCCs;


		return $storedCCs;

	}

	function getStoredCCsByPaymentMethod ($virtuemart_user_id, $virtuemart_paymentmethod_id) {
		$storedCCsByPaymentMethod = array();
		$storeCCs = $this->getStoredCCs($virtuemart_user_id);
		if ($storeCCs) {
			foreach ($storeCCs as $storeCC) {
				if ($storeCC->virtuemart_paymentmethod_id == $virtuemart_paymentmethod_id) {
					$storedCCsByPaymentMethod[] = $storeCC;
				}
			}
		}

		return $storedCCsByPaymentMethod;
	}


	function getStoredCCByPmt_ref ($virtuemart_user_id, $pmt_ref) {
		$storedCCByPmt_ref = array();
		$storeCCs = $this->getStoredCCs($virtuemart_user_id);
		if ($storeCCs) {
			foreach ($storeCCs as $storeCC) {
				if ($storeCC->realex_hpp_api_saved_pmt_ref == $pmt_ref) {
					$storedCCByPmt_ref = $storeCC;
					break;
				}
			}
		}

		return $storedCCByPmt_ref;
	}

	function getRemoteCCFormParams ($xml_response_dcc = NULL, $error = FALSE) {
		$realvault = false;
		$useSSL = $this->useSSL();
		$submit_url = JRoute::_('index.php?option=com_virtuemart&Itemid=' . vRequest::getInt('Itemid') . '&lang=' . vRequest::getCmd('lang', ''), $this->cart->useXHTML, $useSSL);
		$card_payment_button = $this->getPaymentButton();
		if (!empty($xml_response_dcc)) {
			$notificationTask = "handleRemoteDccForm";
		} elseif ($this->_method->threedsecure AND $this->isCC3DSVerifyEnrolled()  AND !$error) {
			$notificationTask = "handleVerify3D";
		} else {
			$notificationTask = "handleRemoteCCForm";
		}


		if (empty($this->_method->creditcards)) {
			$this->_method->creditcards = RealexHelperRealex::getRealexCreditCards();
		} elseif (!is_array($this->_method->creditcards)) {
			$this->_method->creditcards = (array)$this->_method->creditcards;
		}
		$ccDropdown = "";
		$offer_save_card = false;
		if (!JFactory::getUser()->guest AND $this->_method->realvault) {

			$selected_cc = $this->customerData->getVar('saved_cc_selected');
			if (empty($selected_cc)) {
				$selected_cc = -1;
			}
			$use_another_cc = true;

			$ccDropdown = $this->getCCDropDown($this->_method->virtuemart_paymentmethod_id, JFactory::getUser()->id, $selected_cc, $use_another_cc, true);
			if ($selected_cc > 0) {
				$realvault = $this->getStoredCCsData($selected_cc);
			}
			$offer_save_card = $this->_method->offer_save_card;

		}
		$amountInCurrency = vmPSPlugin::getAmountInCurrency($this->order['details']['BT']->order_total, $this->_method->payment_currency);

		$order_amount = vmText::sprintf('VMPAYMENT_REALEX_HPP_API_PAYMENT_TOTAL', $amountInCurrency['display']);
		$cd = CurrencyDisplay::getInstance($this->cart->pricesCurrency);

		$payment_name = $this->plugin->renderPluginName($this->_method);
		$cvv_images = $this->_method->cvv_images;
		$cvv_info = "";
		$dccinfo = "";
		if ($xml_response_dcc) {
			$success = $this->isResponseSuccess($xml_response_dcc);
			if ($success AND isset($xml_response_dcc->dccinfo)) {
				$dccinfo = $xml_response_dcc->dccinfo;
			}
		}
		if ($xml_response_dcc AND $realvault) {
			$ccData['cc_type'] = $realvault->realex_hpp_api_saved_pmt_type;
			$ccData['cc_number'] = $realvault->realex_hpp_api_saved_pmt_digits;
			$ccData['cc_number_masked'] = $realvault->realex_hpp_api_saved_pmt_digits;
			$ccData['cc_name'] = $realvault->realex_hpp_api_saved_pmt_name;
			$ccData['cc_cvv_realvault'] = $this->customerData->getVar('cc_cvv_realvault');
			$ccData['cc_cvv_masked'] = '***';
		} else {
			$ccData['cc_type'] = $this->customerData->getVar('cc_type');
			$ccData['cc_number'] = $this->customerData->getVar('cc_number');
			$ccData['cc_number_masked'] = $this->customerData->getMaskedCCnumber();
			$ccData['cc_cvv'] = $this->customerData->getVar('cc_cvv');
			$ccData['cc_cvv_masked'] = '***';
			$ccData['cc_expire_month'] = $this->customerData->getVar('cc_expire_month');
			$ccData['cc_expire_year'] = $this->customerData->getVar('cc_expire_year');
			$ccData['cc_name'] = $this->customerData->getVar('cc_name');
			$ccData['save_card'] = $this->customerData->getVar('save_card');
		}

		return array(
			"order_amount"                => $order_amount,
			"payment_name"                => $payment_name,
			"submit_url"                  => $submit_url,
			"card_payment_button"         => $card_payment_button,
			"notificationTask"            => $notificationTask,
			'creditcardsDropDown'         => $ccDropdown,
			"dccinfo"                     => $dccinfo,
			"ccData"                      => $ccData,
			'creditcards'                 => $this->_method->creditcards,
			'offer_save_card'             => $offer_save_card,
			'order_number'                => $this->order['details']['BT']->order_number,
			'virtuemart_paymentmethod_id' => $this->_method->virtuemart_paymentmethod_id,
			'integration'                 => $this->_method->integration,
			'cvv_info'                    => $cvv_info,
			'cvn_checking'                => $this->_method->cvn_checking,
			'cvv_images'                  => $cvv_images);
	}

	/**
	 * Switch and Solo became Maestro
	 * @return bool
	 */
	function isCC3DSVerifyEnrolled ($pmt_type = NULL) {
		$CC3DSVerifyEnrolled = array('VISA', 'MC', 'MAESTRO', 'AMEX');
		if ($pmt_type == NULL) {
			$pmt_type = $this->customerData->getVar('cc_type');
		}
		return in_array($pmt_type, $CC3DSVerifyEnrolled);
	}


	/**
	 * @param $virtuemart_paymentmethod_id
	 * @param $virtuemart_user_id
	 * @param $selected_cc
	 * @return mixed|null
	 */
	function getCCDropDown ($virtuemart_paymentmethod_id, $virtuemart_user_id, $selected_cc, $use_another_cc = true, $radio = false) {

		//$storeCCs = $this->getStoredCCsByPaymentMethod($virtuemart_user_id, $virtuemart_paymentmethod_id);
		$storeCCs = $this->getStoredCCs($virtuemart_user_id);
		if (empty($storeCCs)) {
			return null;
		}
		if (!class_exists('VmHTML')) {
			require(VMPATH_ADMIN . DS . 'helpers' . DS . 'html.php');
		}
		$attrs = 'class="inputbox vm-chzn-select"';
		$idA = $id = 'saved_cc_selected';
		//$idA = $id = 'saved_cc_selected_';
		//$options[] = array('value' => '', 'text' => vmText::_('VMPAYMENT_REALEX_HPP_API_PLEASE_SELECT'));
		if ($use_another_cc) {
			$options[] = JHTML::_('select.option', -1, vmText::_('VMPAYMENT_REALEX_HPP_API_USE_ANOTHER_CC'));
			$radioOptions[-1] = vmText::_('VMPAYMENT_REALEX_HPP_API_USE_ANOTHER_CC');
		}
		foreach ($storeCCs as $storeCC) {
			$cc_type = vmText::_('VMPAYMENT_REALEX_HPP_API_CC_' . $storeCC->realex_hpp_api_saved_pmt_type);
			$name = $cc_type . ' ' . $storeCC->realex_hpp_api_saved_pmt_digits . ' ' . $this->renderExpDate($storeCC->realex_hpp_api_saved_pmt_expdate) . ' (' . $storeCC->realex_hpp_api_saved_pmt_name . ')';
			$options[] = JHTML::_('select.option', $storeCC->id, $name);
			$radioOptions[$storeCC->id] = $name;
		}

		if ($radio) {
			return VmHTML::radioList($idA, $selected_cc, $radioOptions, 'class="realexListCC"');
		} else {
			return JHTML::_('select.genericlist', $options, $idA, 'class="realexListCC inputbox vm-chzn-select" style="width: 350px;"', 'value', 'text', $selected_cc);
		}

	}

	/**
	 * stored format of the expired date is mmyy
	 * @param $expdate
	 * @return mixed
	 */
	function renderExpDate ($expdate) {
		return substr($expdate, 0, 2) . '/' . substr($expdate, -2);
	}


	function getOfferSaveCard ($checked, $paymentmethod_id) {
		$param = '';
		if ($checked) {
			$param = "checked";
		}
		$id = 'realex_offersavecard_' . $paymentmethod_id;
		$html = vmText::_('VMPAYMENT_REALEX_HPP_API_OFFERSAVECARD');
		$html .= "<br />";
		$html .= '<input type="checkbox" name="' . $id . '" id="' . $id . '" value="1" ' . $param . '/>';
		$html .= ' <label for="' . $id . '">' . vmText::_('VMPAYMENT_REALEX_HPP_API_OFFERSAVECARD_YES') . '</label>';
		return $html;
	}


	function doRealVault (&$selectedCCParams) {

		if (!JFactory::getUser()->guest AND $this->_method->realvault AND $this->_method->integration == 'remote') {
			if (!($this->_method->offer_save_card) OR
				($this->_method->offer_save_card AND $this->customerData->getVar('save_card'))
			) {
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * @return string
	 */
	function getRemoteURL () {
		return "https://epage.payandshop.com/epage-remote-plugins.cgi";

	}

	/**
	 * @return string
	 */

	function getPaymentButton () {
		if (empty($this->_method->card_payment_button)) {
			$card_payment_button = vmText::_('VMPAYMENT_REALEX_HPP_API_DCC_PAY_NOW');
		} else {
			$card_payment_button = $this->_method->card_payment_button;
		}
		return $card_payment_button;
	}

	/**
	 * Once the payer has been set up on the RealVault system, to raise a payment, use the receipt-in transaction.
	 * @param      $selectedCCParams
	 * @param bool $ask_dcc
	 * @param bool $set_dcc
	 * @return bool|mixed
	 */
	public function requestReceiptIn ($selectedCCParams, $xml_response_dcc = NULL, $xml_3Dresponse = NULL) {

		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_RECEIPT_IN);
		if (!empty($selectedCCParams->cc_cvv_realvault)) {
			$xml_request .= '<paymentdata>
					<cvn>
						<number>' . $selectedCCParams->cc_cvv_realvault . '</number>
					</cvn>
				</paymentdata>
				';
		}
		if ($this->_method->dcc) {
			$xml_request .= '<autosettle flag="1" />
			';
		} else {
			$xml_request .= '<autosettle flag="' . $this->getSettlement() . '" />
			';
		}
		$xml_request .= $this->setMpi($xml_3Dresponse);
		$payerRef = $this->getSavedPayerRef();
		$xml_request .= '<payerref>' . $this->getSavedPayerRef() . '</payerref>
		<paymentmethod>' . $selectedCCParams->realex_hpp_api_saved_pmt_ref . '</paymentmethod>';
		if ($xml_response_dcc) {
			$xml_request .= $this->setDccInfo($xml_response_dcc);
			/*
			$xml_request .= '
			<dccinfo>
				<ccp>' . $this->_method->dcc_service . '</ccp>
				<type>1</type>
				<rate>' . $xml_response_dcc->dccinfo->cardholderrate . '</rate>
				<ratetype>S</ratetype>
				<amount currency="' . $xml_response_dcc->dccinfo->cardholdercurrency . '">' . $xml_response_dcc->dccinfo->cardholderamount . '</amount>
			</dccinfo>';
			*/
		}
		$xml_request .= '<md5hash />
		';
		$hash = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), $payerRef);
		$xml_request .= $this->setSha1($hash);
		$xml_request .= $this->setComments();
		$xml_request .= $this->setTSSinfo();
		$xml_request .= '</request>';


		$response = $this->getXmlResponse($xml_request);

		return $response;

	}


	function setMPI ($xml_3Dresponse) {
		$xml_request = '';
		if ($xml_3Dresponse) {
			if (!empty($xml_3Dresponse->eci) AND isset($xml_3Dresponse->eci)) {
				$xml_request = '<mpi>' . '<eci>' . $xml_3Dresponse->eci . '</eci>
							</mpi>
							';
			}
			/**
			 * MPI: Merchant Plug In - it is the component that communicates with the other components during the actual transaction.
			 */
			if ($xml_3Dresponse AND !empty($xml_3Dresponse->threedsecure) AND isset($xml_3Dresponse->threedsecure)) {

				$xml_request = '<mpi>
				 <eci>' . $xml_3Dresponse->threedsecure->eci . '</eci>
				  <cavv>' . $xml_3Dresponse->threedsecure->cavv . '</cavv>
				  <xid>' . $xml_3Dresponse->threedsecure->xid . '</xid>
				 </mpi>
				 ';
			}
		}

		return $xml_request;
	}

	function getSettlement () {
		return ($this->_method->settlement == 'auto') ? 1 : 0;

	}

	/**
	 * Hash Value Syntax: Timestamp.merchantID.payerref.pmtref
	 * @param $storedCC : the stored Credit card to delete
	 * @return bool
	 */

	function deleteStoredCard ($storedCC) {
		$timestamp = $this->getTimestamp();
		$payerRef = $this->getSavedPayerRef($storedCC['virtuemart_user_id']);
		$xml_request = '
				<request timestamp="' . $timestamp . '" type="' . self::REQUEST_TYPE_CARD_CANCEL_CARD . '">
					<merchantid>' . $this->_method->merchant_id . '</merchantid>
					<card>
						<ref>' . $storedCC['realex_hpp_api_saved_pmt_ref'] . '</ref>
						<payerref>' . $payerRef . '</payerref>
					</card>';
		$sha1_request = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $payerRef, $storedCC['realex_hpp_api_saved_pmt_ref']);
		$xml_request .= $this->setSha1($sha1_request);
		$xml_request .= '</request>';

		$response = $this->getXmlResponse($xml_request);
		$xml_response = simplexml_load_string($response);
		$result = (string)$xml_response->result;

		$merchantid = (string)$xml_response->merchantid;
		$sha1hash = (string)$xml_response->sha1hash;
		$sha1_temp_response = sha1($timestamp . '.' . $merchantid . '.' . $payerRef . '.' . $storedCC['realex_hpp_api_saved_pmt_ref']);
		$sha1_response = sha1($sha1_temp_response . '.' . $this->_method->shared_secret);
		// 501 : Card Ref and Payer combination does not exist: ignore those cases
		if (($result == '00' || $result == '501') && $sha1_response == $sha1_request) {
			return true;
		} else {
			$this->displayError((string)$xml_response->message);
			return false;
		}


	}

	/**
	 * To update the Expiry Date or update the Cardholder Name on an existing card:
	 * Hash Value Syntax: Timestamp.merchantID.payerref.pmtref
	 * @param $storedCC : the stored Credit card to delete
	 * @return bool
	 */

	function updateStoredCard ($storedCC) {
		$timestamp = $this->getTimestamp();
		$payerRef = $this->getSavedPayerRef($storedCC['virtuemart_user_id']);
		$xml_request = '
				<request timestamp="' . $timestamp . '" type="' . self::REQUEST_TYPE_CARD_UPDATE_CARD . '">
					<merchantid>' . $this->_method->merchant_id . '</merchantid>
					<card>
						<ref>' . $storedCC['realex_hpp_api_saved_pmt_ref'] . '</ref>
						<payerref>' . $payerRef . '</payerref>
						<expdate>' . $storedCC['realex_hpp_api_saved_pmt_expdate'] . '</expdate>
						<chname>' . $storedCC['realex_hpp_api_saved_pmt_name'] . '</chname>
						<type>card</type>
					</card>';
		$sha1_request = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $payerRef, $storedCC['realex_hpp_api_saved_pmt_ref'], $storedCC['realex_hpp_api_saved_pmt_expdate'], "");
		$xml_request .= $this->setSha1($sha1_request);
		$xml_request .= '</request>';

		$response = $this->getXmlResponse($xml_request);
		$xml_response = simplexml_load_string($response);
		$result = (string)$xml_response->result;

		$merchantid = (string)$xml_response->merchantid;
		$sha1hash = (string)$xml_response->sha1hash;
		$sha1_temp_response = sha1($timestamp . '.' . $merchantid . '.' . $payerRef . '.' . $storedCC['realex_hpp_api_saved_pmt_ref'] . '.' . $storedCC['realex_hpp_api_saved_pmt_expdate'] . '.');
		$sha1_response = sha1($sha1_temp_response . '.' . $this->_method->shared_secret);
		// 501 : Card Ref and Payer combination does not exist: ignore those cases
		if (($result == '00' || $result == '501') && $sha1_response == $sha1_request) {
			return true;
		} else {
			$this->displayError((string)$xml_response->message);
			return false;
		}


	}

	/**
	 * set comment in request
	 * @return string
	 */
	function setComments () {
		$xml_request = '<comments>
				<comment id="2">virtuemart-rlx</comment>
			</comments>
			';
		return $xml_request;
	}

	/**
	 * set TSS info in request
	 * @return string
	 */
	function setTSSinfo () {
		$BT = $this->order['details']['BT'];
		$ST = ((isset($this->order['details']['ST'])) ? $this->order['details']['ST'] : $this->order['details']['BT']);

		$xml_request = '<tssinfo>
		                <address type="billing">
		                <code>' . $this->getCode($BT) . '</code>
						 <country>' . ShopFunctions::getCountryByID($BT->virtuemart_country_id, 'country_2_code') . '</country>
						 </address>
						<address type="shipping">
					     <code>' . $this->getCode($ST) . '</code>
						 <country>' . ShopFunctions::getCountryByID($ST->virtuemart_country_id, 'country_2_code') . '</country>
						 </address>
						 <custnum></custnum>
						 <varref></varref>
						 <prodid></prodid>
						 </tssinfo>
						 ';

		return $xml_request;
	}

	/**
	 * @return bool|mixed
	 */
	function setNewPayer ($newPayerRef) {
		$timestamp = $this->getTimestamp();

		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_PAYER_NEW, false);

		$BT = $this->order['details']['BT'];

		$xml_request .= '<payer type="Business" ref="' . $newPayerRef . '">
				<firstname>' . $this->sanitize($BT->first_name) . '</firstname>
				<surname>' . $this->sanitize($BT->last_name) . '</surname>
				';
		if (!empty($BT->company)) {
			$xml_request .= '<company>' . $BT->company . '</company>
		';
		}

		$xml_request .= '<address>
				<line1>' . $BT->address_1 . '</line1>
				<line2 >' . $BT->address_2 . '</line2>
				<line3 />
				<city>' . $BT->city . '</city>
				<county>' . ShopFunctions::getCountryByID($BT->virtuemart_country_id, 'country_2_code') . '</county>
				<postcode>' . $this->stripnonnumeric($BT->zip, 5) . '</postcode>
				<country code="' . ShopFunctions::getCountryByID($BT->virtuemart_country_id, 'country_2_code') . '"> ' . ShopFunctions::getCountryByID($BT->virtuemart_country_id, 'country_name') . ' </country>
				</address>
				<phonenumbers>
				<home />
				<work>' . $BT->phone_1 . '</work>
				<fax />
				<mobile>' . $BT->phone_2 . '</mobile>
				</phonenumbers>
				<email>' . $BT->email . '</email>
				<comments>
				<comment id="1" />
				<comment id="2" />
				</comments>
				</payer>
				';
		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, '', '', $newPayerRef);
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);
		return $response;


	}

	function sanitize ($string) {
		$string = $this->replaceNonAsciiCharacters($string);
		$string = vRequest::filterUword($string, ' ');
		return $string;
	}

	/**
	 * @return bool|mixed
	 */
	function setNewPayment ($newPayerRef, &$newPaymentRef) {
		$timestamp = $this->getTimestamp();
		$cc_number = str_replace(" ", "", $this->customerData->getVar('cc_number'));
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_CARD_NEW, false);
		$newPaymentRef = $this->getUniqueId($this->order['details']['BT']->order_number);
		$cc_name = $this->sanitize($this->customerData->getVar('cc_name'));
		$xml_request .= '<card>
		 <ref>' . $newPaymentRef . '</ref>
		<payerref>' . $newPayerRef . '</payerref>
		<number>' . $cc_number . '</number>
		<expdate>' . $this->getFormattedExpiryDateForRequest() . '</expdate>
		<chname>' . $cc_name . '</chname>
		<type>' . $this->getCCtype($this->customerData->getVar('cc_type')). '</type>
		<issueno />
		</card>
		';
		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, '', '', $newPayerRef, $cc_name, $cc_number);
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);
		return $response;

	}

	/**
	 * @param $value
	 * @return string
	 */
	function getUniqueId ($value) {
		$value = $this->sanitize($value);
		return $value . '-' . time();
	}

	/**
	 * cc_expire_year has format 4 digits
	 * set CC expiry date in required format mmyy
	 * @return string
	 */
	function getFormattedExpiryDateForRequest () {
		return $this->customerData->getVar('cc_expire_month') . substr($this->customerData->getVar('cc_expire_year'), -2);
	}

	/**
	 * set header request
	 * @param $timestamp
	 * @param $type
	 * @return string
	 */
	function setHeader ($timestamp, $type, $include_amount = true, $include_account=true) {
		$this->request_type = $type;
		$xml_request = '<request timestamp="' . $timestamp . '" type="' . $type . '">
						<merchantid>' . $this->_method->merchant_id . '</merchantid>';
		if ($include_account) {
		$xml_request .=			 '<account>' . $this->_method->subaccount . '</account>';
		}
		$xml_request .=			 '<orderid>' . $this->order['details']['BT']->order_number . '</orderid>
						 ';
		if ($include_amount) {
			$xml_request .= '<amount currency="' . $this->getPaymentCurrency() . '">' . $this->getTotalInPaymentCurrency() . '</amount>
			';
		}
		return $xml_request;
	}

	/**
	 * set sha1 in request
	 * @param $sha1hash
	 * @return string
	 */
	function setSha1 ($sha1hash) {
		$xml_request = '<sha1hash>' . $sha1hash . '</sha1hash>
		';
		return $xml_request;
	}

	function rebateTransaction ($payments) {

		$payment = $this->getTransactionData($payments);
		if ($payment === NULL) {
			return NULL;
		}
		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_REBATE, true, false);
		$xml_request .= '<pasref>' . $payment->realex_hpp_api_response_pasref . '</pasref>
				<authcode>' . $payment->realex_hpp_api_response_authcode . '</authcode>
				';

		$refundhash = sha1($this->_method->rebate_password);
		$xml_request .= '<refundhash>' . $refundhash . '</refundhash>
				 ';

		$xml_request .= $this->setComments();

		// NOTE: There is no cardnumber included in the rebate so the cardnumber field can be left blank in the hash  .

		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), "");
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '<md5hash></md5hash>
		';
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);

		return $response;
	}

	function settleTransaction ($payments) {
		$payment = $this->getTransactionData($payments);
		if ($payment === NULL) {
			return NULL;
		}
		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_SETTLE);
		$xml_request .= '
				<pasref>' . $payment->realex_hpp_api_response_pasref . '</pasref>
				<authcode>' . $payment->realex_hpp_api_response_authcode . '</authcode>
				';


		$xml_request .= $this->setComments();
		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), "");
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '<md5hash></md5hash>
		';
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);

		return $response;
	}

	/**
	 * Before settlement, it is possible to void an authorisation, manual, offline, or rebate request.
	 * If the transaction has been settled or batched, then it cannot be voided, although it can be submitted using another authorisation or rebate.
	 *
	 * @param $payments
	 * @return bool|mixed|null
	 */
	function voidTransaction ($payments) {
		$payment = $this->getTransactionData($payments);
		if ($payment === NULL) {
			return NULL;
		}
		$timestamp = $this->getTimestamp();
		$xml_request = $this->setHeader($timestamp, self::REQUEST_TYPE_VOID);
		$xml_request .= '
				<pasref>' . $payment->realex_hpp_api_response_pasref . '</pasref>
				<authcode>' . $payment->realex_hpp_api_response_authcode . '</authcode>
				';

		$xml_request .= $this->setComments();
// timestamp.merchantid.orderid...

		$sha1 = $this->getSha1Hash($this->_method->shared_secret, $timestamp, $this->_method->merchant_id, $this->order['details']['BT']->order_number, $this->getTotalInPaymentCurrency(), $this->getPaymentCurrency(), "");
		$xml_request .= $this->setSha1($sha1);
		$xml_request .= '<md5hash></md5hash>
		';
		$xml_request .= '</request>';
		$response = $this->getXmlResponse($xml_request);

		return $response;
	}

	/**
	 * @param        $payments
	 * @param string $request_type
	 * @return null
	 */
	function getTransactionData ($payments, $request_type = array(
		self::REQUEST_TYPE_AUTH,
		self::REQUEST_TYPE_RECEIPT_IN
	)) {
		foreach ($payments as $payment) {
			if (in_array($payment->realex_hpp_api_request_type_response, $request_type)) {
				return $payment;
			}
		}
		return NULL;
	}


	function getCCtype($cctype ) {
		if ($cctype=='MAESTRO') {
			return 'MC';
		} else {
			return $cctype;
		}
	}

	function getLastTransactionData ($payments, $request_type = array(
		self::REQUEST_TYPE_AUTH,
		self::REQUEST_TYPE_RECEIPT_IN
	)) {
		$payment = end($payments);
		if (in_array($payment->realex_hpp_api_request_type_response, $request_type)) {
			return $payment;
		}
		return NULL;
	}

	function  isResponseSuccess ($xml_response) {
		$result = (string)$xml_response->result;
		$success = ($result == self::RESPONSE_CODE_SUCCESS);
		return $success;
	}


	function  isResponseDeclined ($xml_response) {
		$result = (string)$xml_response->result;
		$result = ($result == self::RESPONSE_CODE_DECLINED);
		return $result;
	}

	function  isResponseWrongPhrase ($xml_response) {
		$threedsecure = $xml_response->threedsecure;
		$threedsecure_status = (string)$threedsecure->status;
		$result = (string)$xml_response->result;
		$result = ($result == self::RESPONSE_CODE_SUCCESS AND $threedsecure_status == self::THREEDSECURE_STATUS_NOT_AUTHENTICATED);
		return $result;
	}

	function  isResponseAlreadyProcessed ($xml_response) {
		$result = (string)$xml_response->result;
		$result = ($result == self::RESPONSE_CODE_INVALID_ORDER_ID);
		return $result;
	}

	function  isResponseNotValidated ($xml_response) {
		$result = (string)$xml_response->result;
		$success = ($result == self::RESPONSE_CODE_NOT_VALIDATED);
		return $success;
	}

	function  isResponseInvalidPaymentDetails ($xml_response) {
		$result = (string)$xml_response->result;
		$success = ($result == self::RESPONSE_CODE_INVALID_PAYMENT_DETAILS);
		return $success;
	}

	/**
	 * get HASH for Realex
	 * @param      $secret
	 * @param      $args
	 * @return string
	 */
	public function getSha1Hash ($secret, $args = null) {
		if (empty($secret)) {
			vmError('function getSha1Hash:no secret value for getSha1Hash', 'no secret value for getSha1Hash');
		}
		$args = func_get_args();
		array_shift($args);
		$hash = sha1(implode('.', $args));
		//$hash =$hash.$secret;
		$hash = sha1($hash . '.' . $secret);
		return $hash;
	}

	/**
	 * Validate the response hash from Realex.
	 * is always timestamp.merchantid.orderid.result.message.pasref.authcode
	 * @param      $xml_response simplexml_load_string
	 */
	protected function validateResponseHash ($response) {
		$xml_response = simplexml_load_string($response);
		if (! isset($xml_response->sha1hash)) {
			return true;
		}
		$hash = $this->getSha1Hash($this->_method->shared_secret, $xml_response->attributes()->timestamp, $this->_method->merchant_id, (string)$xml_response->orderid, (string)$xml_response->result, (string)$xml_response->message, (string)$xml_response->pasref, (string)$xml_response->authcode);

		if ($hash != $xml_response->sha1hash) {
			$this->displayError(vmText::sprintf('VMPAYMENT_REALEX_HPP_API_ERROR_WRONG_HASH', $hash, $xml_response->sha1hash));
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	function getTimestamp () {
		return strftime("%Y%m%d%H%M%S");
	}

	/**
	 * @param $xml
	 */
	function obscureSha1hash ($xml) {
		if (isset($xml->sha1hash)) {
			$xml->sha1hash = $this->obscureValue($xml->sha1hash);
		}
		return $xml;
	}

	/**
	 * @param $value
	 * @return string
	 */
	function obscureValue ($value) {

		$value_length = strlen($value);
		$value = str_repeat("*", $value_length);

		return $value;
	}
	/**
	 * @param $xml_request
	 * @return bool|mixed
	 */
	function getXmlResponse ($xml_request) {
		$this->xml_request = $xml_request;
		$requestToLog = $xml_request;
		$xml_requestToLog = simplexml_load_string($requestToLog);
		if ($xml_requestToLog) {
			if (isset($xml_requestToLog->card)) {
				$card_number = $xml_requestToLog->card->number;
				$cc_length = strlen($card_number);
				//$xml_requestToLog->card->number = str_repeat("*", $cc_length);
				$xml_requestToLog->card->number = $this->obscureValue($xml_requestToLog->card->number);
				if (isset($xml_requestToLog->card->cvn->number)) {
					$xml_requestToLog->card->cvn->number = $this->obscureValue($xml_requestToLog->card->cvn->number);
				}
			}
			if (isset($xml_requestToLog->paymentdata->cvn->number)) {
				$xml_requestToLog->paymentdata->cvn->number = $this->obscureValue($xml_requestToLog->paymentdata->cvn->number);
			}

			$xml_requestToLog = $this->obscureSha1hash($xml_requestToLog);


			//$this->debugLog(print_r($request, true), 'debug');
			$this->debugLog('<textarea style="margin: 0px; width: 100%; height: 250px;">' . $xml_requestToLog->asXML() . '</textarea>', 'Request', 'message');

		} else {
			// THIS IS AN ERROR: cannot log there was an error
			$this->debugLog('<textarea style="margin: 0px; width: 100%; height: 250px;">COULD NOT LOG INT THIS' . $requestToLog . '</textarea>', 'Request', 'error');
// we do not continue because anyway we will have   <message>Bad XML formation</message>
			return NULL;
		}

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->getRemoteURL());
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_request);
		$response = curl_exec($ch);
		curl_close($ch);

		$responseToLog = $response;
		$xml_responseToLog = simplexml_load_string($responseToLog);
		if (isset($xml_responseToLog->sha1hash)) {
			$sha1hash = $xml_responseToLog->sha1hash;
			$sha1hash_length = strlen($sha1hash);
			$xml_responseToLog->sha1hash = str_repeat("*", $sha1hash_length);
		}


		$this->debugLog('<textarea style="margin: 0px; width: 100%; height: 250px;">' . $xml_responseToLog->asXML() . '</textarea>', 'response :', 'message');

		if (empty($response)) {
			$this->displayError(vmText::_('VMPAYMENT_REALEX_HPP_API_EMPTY_RESPONSE'));
			return FALSE;
		}
		if (!$this->validateResponseHash($response)) {
			return FALSE;
		}

		return $response;
	}

	function useSSL () {
		if ($this->_method->shop_mode == 'sandbox') {
			return false;
		}
			return true;
	}

	/**
	 * @param $message
	 */
	static $displayErrorDone = false;

	function displayError ($admin, $public = '') {
		if ($admin == NULL) {
			$admin = "an error occurred";
		}

		if (empty($public) AND $this->_method->debug) {
			$public = $admin;
		}
		vmError((string)$admin, (string)$public);
	}



	/**
	 * Realex requires that non asci characters are removed from the cc name
	 * @param $string
	 * @return string
	 */
	function replaceNonAsciiCharacters ($string) {
		$replacements = $this->getReplacements();
		$string = strtr($string, $replacements);
		return $string;
	}

	/**
	 * Return array of URL characters to be replaced.
	 *
	 * @return array
	 */
	public function getReplacements () {
		return array(
			'Á'  => 'A',
			'Â'  => 'A',
			'Å'  => 'A',
			'Ă'  => 'A',
			'Ä'  => 'A',
			'À'  => 'A',
			'Æ'  => 'A',
			'Ć'  => 'C',
			'Ç'  => 'C',
			'Č'  => 'C',
			'Ď'  => 'D',
			'É'  => 'E',
			'È'  => 'E',
			'Ë'  => 'E',
			'Ě'  => 'E',
			'Ê'  => 'E',
			'Ì'  => 'I',
			'Í'  => 'I',
			'Î'  => 'I',
			'Ï'  => 'I',
			'Ĺ'  => 'L',
			'ľ'  => 'l',
			'Ľ'  => 'L',
			'Ń'  => 'N',
			'Ň'  => 'N',
			'Ñ'  => 'N',
			'Ò'  => 'O',
			'Ó'  => 'O',
			'Ô'  => 'O',
			'Õ'  => 'O',
			'Ö'  => 'O',
			'Ø'  => 'O',
			'Ŕ'  => 'R',
			'Ř'  => 'R',
			'Š'  => 'S',
			'Ś'  => 'S',
			'Ť'  => 'T',
			'Ů'  => 'U',
			'Ú'  => 'U',
			'Ű'  => 'U',
			'Ü'  => 'U',
			'Û'  => 'U',
			'Ý'  => 'Y',
			'Ž'  => 'Z',
			'Ź'  => 'Z',
			'á'  => 'a',
			'â'  => 'a',
			'å'  => 'a',
			'ä'  => 'a',
			'à'  => 'a',
			'æ'  => 'a',
			'ć'  => 'c',
			'ç'  => 'c',
			'č'  => 'c',
			'ď'  => 'd',
			'đ'  => 'd',
			'é'  => 'e',
			'ę'  => 'e',
			'ë'  => 'e',
			'ě'  => 'e',
			'è'  => 'e',
			'ê'  => 'e',
			'ì'  => 'i',
			'í'  => 'i',
			'î'  => 'i',
			'ï'  => 'i',
			'ĺ'  => 'l',
			'ń'  => 'n',
			'ň'  => 'n',
			'ñ'  => 'n',
			'ò'  => 'o',
			'ó'  => 'o',
			'ô'  => 'o',
			'ő'  => 'o',
			'ö'  => 'o',
			'ø'  => 'o',
			'š'  => 's',
			'ś'  => 's',
			'ř'  => 'r',
			'ŕ'  => 'r',
			'ť'  => 't',
			'ů'  => 'u',
			'ú'  => 'u',
			'ù'  => 'u',
			'ű'  => 'u',
			'ü'  => 'u',
			'û'  => 'u',
			'ý'  => 'y',
			'ž'  => 'z',
			'ź'  => 'z',
			'˙'  => '-',
			'ß'  => 'ss',
			'Ą'  => 'A',
			'µ'  => 'u',
			'ą'  => 'a',
			'Ę'  => 'E',
			'ż'  => 'z',
			'Ż'  => 'Z',
			'ł'  => 'l',
			'Ł'  => 'L',
			'А'  => 'A',
			'а'  => 'a',
			'Б'  => 'B',
			'б'  => 'b',
			'В'  => 'V',
			'в'  => 'v',
			'Г'  => 'G',
			'г'  => 'g',
			'Д'  => 'D',
			'д'  => 'd',
			'Е'  => 'E',
			'е'  => 'e',
			'Ж'  => 'Zh',
			'ж'  => 'zh',
			'З'  => 'Z',
			'з'  => 'z',
			'И'  => 'I',
			'и'  => 'i',
			'Й'  => 'I',
			'й'  => 'i',
			'К'  => 'K',
			'к'  => 'k',
			'Л'  => 'L',
			'л'  => 'l',
			'М'  => 'M',
			'м'  => 'm',
			'Н'  => 'N',
			'н'  => 'n',
			'О'  => 'O',
			'о'  => 'o',
			'П'  => 'P',
			'п'  => 'p',
			'Р'  => 'R',
			'р'  => 'r',
			'С'  => 'S',
			'с'  => 's',
			'Т'  => 'T',
			'т'  => 't',
			'У'  => 'U',
			'у'  => 'u',
			'Ф'  => 'F',
			'ф'  => 'f',
			'Х'  => 'Kh',
			'х'  => 'kh',
			'Ц'  => 'Tc',
			'ц'  => 'tc',
			'Ч'  => 'Ch',
			'ч'  => 'ch',
			'Ш'  => 'Sh',
			'ш'  => 'sh',
			'Щ'  => 'Shch',
			'щ'  => 'shch',
			'Ы'  => 'Y',
			'ы'  => 'y',
			'Э'  => 'E',
			'э'  => 'e',
			'Ю'  => 'Iu',
			'ю'  => 'iu',
			'Я'  => 'Ia',
			'я'  => 'ia',
			'Ъ'  => '',
			'ъ'  => '',
			'Ь'  => '',
			'ь'  => '',
			'Ё'  => 'E',
			'ё'  => 'e',
			'ου' => 'ou',
			'ού' => 'ou',
			'α'  => 'a',
			'β'  => 'b',
			'γ'  => 'g',
			'δ'  => 'd',
			'ε'  => 'e',
			'ζ'  => 'z',
			'η'  => 'i',
			'θ'  => 'th',
			'ι'  => 'i',
			'κ'  => 'k',
			'λ'  => 'l',
			'μ'  => 'm',
			'ν'  => 'n',
			'ξ'  => 'ks',
			'ο'  => 'o',
			'π'  => 'p',
			'ρ'  => 'r',
			'σ'  => 's',
			'τ'  => 't',
			'υ'  => 'i',
			'φ'  => 'f',
			'χ'  => 'x',
			'ψ'  => 'ps',
			'ω'  => 'o',
			'ά'  => 'a',
			'έ'  => 'e',
			'ί'  => 'i',
			'ή'  => 'i',
			'ό'  => 'o',
			'ύ'  => 'i',
			'ώ'  => 'o',
			'Ου' => 'ou',
			'Ού' => 'ou',
			'Α'  => 'a',
			'Β'  => 'b',
			'Γ'  => 'g',
			'Δ'  => 'd',
			'Ε'  => 'e',
			'Ζ'  => 'z',
			'Η'  => 'i',
			'Θ'  => 'th',
			'Ι'  => 'i',
			'Κ'  => 'k',
			'Λ'  => 'l',
			'Μ'  => 'm',
			'Ν'  => 'n',
			'Ξ'  => 'ks',
			'Ο'  => 'o',
			'Π'  => 'p',
			'Ρ'  => 'r',
			'Σ'  => 's',
			'Τ'  => 't',
			'Υ'  => 'i',
			'Φ'  => 'f',
			'Χ'  => 'x',
			'Ψ'  => 'ps',
			'Ω'  => 'o',
			'ς'  => 's',
			'Ά'  => 'a',
			'Έ'  => 'e',
			'Ή'  => 'i',
			'Ί'  => 'i',
			'Ό'  => 'o',
			'Ύ'  => 'i',
			'Ώ'  => 'o',
			'ϊ'  => 'i',
			'ΐ'  => 'i',

		);

	}
}
