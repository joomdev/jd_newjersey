<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: authorizeresponse.php 8585 2014-11-25 11:11:13Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperAuthorizeResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_AuthorizeResponse $authorizationResponse, $method) {
		parent::__construct($authorizationResponse, $method);
	}

	/**
	 * if asynchronous mode= state= pending
	 * if asynchronous mode=> timeOut was set to > 0
	 * if synchronous mode=> timeOut ==0
	 * -- if InvalidPaymentMethod and asynchronous mode, the state= suspended ==> send an email
	 * -- if InvalidPaymentMethod and synchronous mode: return to cart, redisplay wallet widget
	 * -- AmazonRejected: if state == open, then retry authorization, else Declined
	 * -- Processing failure: retry the request in 2 minutes ???
	 * --
	 * @return mixed
	 */


	public function onResponseUpdateOrderHistory ($order) {

		$order_history = array();
		$amazonState = "";
		$reasonCode = "";
		$authorizeResponse = $this->amazonData;
		// if am

		$authorizeResult = $authorizeResponse->getAuthorizeResult();
		$authorizationDetails = $authorizeResult->getAuthorizationDetails();
		if ($authorizationDetails->isSetAuthorizationStatus()) {
			$authorizationStatus = $authorizationDetails->getAuthorizationStatus();
			if (!$authorizationStatus->isSetState()) {
				return false;
			}
			$amazonState = $authorizationStatus->getState();

			if ($authorizationStatus->isSetReasonCode()) {
				$reasonCode = $authorizationStatus->getReasonCode();
			}
		}
		// In asynchronous mode, AuthorizationResponse is always Pending. Order status is not updated
		if ($amazonState == 'Pending') {
			return $amazonState;
		}
		$order_history['customer_notified'] = $this->getCustomerNotified();

		// SYNCHRONOUS MODE: amazon returns in real time the final process status
		if ($amazonState == 'Open') {
			// it should always be the case if the CaptureNow == false
			$order_history['order_status'] = $this->_currentMethod->status_authorization;
			$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_OPEN');
		} elseif ($amazonState == 'Closed') {
			// it should always be the case if the CaptureNow == true
			if (!($authorizationDetails->isSetCaptureNow() and $authorizationDetails->getCaptureNow())) {
				$this->debugLog('SYNCHRONOUS , capture Now, and Amazon State is NOT CLOSED' . __FUNCTION__ . var_export($authorizeResponse, true), 'error');
				return $amazonState;
			}
			$order_history['order_status'] = $this->_currentMethod->status_capture;
			$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURED');
			$order_history['customer_notified'] = 1;

		} elseif ($amazonState == 'Declined') {
			// handling Declined Authorizations
			$order_history['order_status'] = $this->_currentMethod->status_cancel;
			$order_history['comments'] = $reasonCode;
			if ($authorizationStatus->isSetReasonDescription()) {
				$order_history['comments'] .= " " . $authorizationStatus->getReasonDescription();
			}
			$order_history['customer_notified'] = 0;
		}
		$order_history['amazonState'] = $amazonState;
		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, false);


		return $amazonState;
	}


	public function getStoreInternalData () {
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetAuthorizeResult()) {
			$authorizeResult = $this->amazonData->getAuthorizeResult();
			$authorizationDetails = $authorizeResult->getAuthorizationDetails();
			if ($authorizationDetails->isSetAuthorizationStatus()) {
				$authorizationStatus = $authorizationDetails->getAuthorizationStatus();
				if ($authorizationStatus->isSetState()) {
					$amazonInternalData->amazon_response_state = $authorizationStatus->getState();
				}
				if ($authorizationStatus->isSetReasonCode()) {
					$amazonInternalData->amazon_response_reasonCode = $authorizationStatus->getReasonCode();
				}
				if ($authorizationStatus->isSetReasonDescription()) {
					$amazonInternalData->amazon_response_reasonDescription = $authorizationStatus->getReasonDescription();
				}
				if ($authorizationDetails->isSetAmazonAuthorizationId()) {
					$amazonInternalData->amazon_response_amazonAuthorizationId = $authorizationDetails->getAmazonAuthorizationId();
				}
			}

		}
		return $amazonInternalData;
	}

	public function isCaptureNow () {
		$authorizeResult = $this->amazonData->getAuthorizeResult();
		$authorizationDetails = $authorizeResult->getAuthorizationDetails();
		if ($authorizationDetails->isSetCaptureNow()) {
			return $authorizationDetails->getCaptureNow();
		}
		return false;
	}


	function getAmazonAuthorizationId () {
		return $this->amazonData->getAuthorizeResult()->getAuthorizationDetails()->getAmazonAuthorizationId();
	}

	function getContents () {

		$contents = $this->tableStart("AuthorizeResult");
		if ($this->amazonData->isSetAuthorizeResult()) {
			$authorizeResult = $this->amazonData->getAuthorizeResult();
			if ($authorizeResult->isSetAuthorizationDetails()) {
				$contents .= $this->getRowFirstCol("AuthorizationDetails");

				$authorizationDetails = $authorizeResult->getAuthorizationDetails();
				if ($authorizationDetails->isSetAmazonAuthorizationId()) {
					$contents .= $this->getRow("AmazonAuthorizationId: ", $authorizationDetails->getAmazonAuthorizationId());

				}
				if ($authorizationDetails->isSetAuthorizationReferenceId()) {
					$contents .= $this->getRow("AuthorizationReferenceId: ", $authorizationDetails->getAuthorizationReferenceId());

				}
				if ($authorizationDetails->isSetAuthorizationBillingAddress()) {
					$authorizationBillingAddress = $authorizationDetails->getAuthorizationBillingAddress();
					$address = '';
					if ($authorizationBillingAddress->isSetName()) {
						$address .= "<br />Name: " . $authorizationBillingAddress->getName();
					}
					if ($authorizationBillingAddress->isSetAddressLine1()) {
						$address .= "<br />AddressLine1: " . $authorizationBillingAddress->getAddressLine1();
					}
					if ($authorizationBillingAddress->isSetAddressLine2()) {
						$address .= "<br />AddressLine2: " . $authorizationBillingAddress->getAddressLine2();
					}
					if ($authorizationBillingAddress->isSetAddressLine3()) {
						$address .= "<br />AddressLine3: " . $authorizationBillingAddress->getAddressLine3();
					}
					if ($authorizationBillingAddress->isSetCity()) {
						$address .= "<br />City: " . $authorizationBillingAddress->getCity();
					}
					if ($authorizationBillingAddress->isSetCounty()) {
						$address .= "<br />County: " . $authorizationBillingAddress->getCounty();
					}
					if ($authorizationBillingAddress->isSetDistrict()) {
						$address .= "<br />District: " . $authorizationBillingAddress->getDistrict();
					}
					if ($authorizationBillingAddress->isSetStateOrRegion()) {
						$address .= "<br />StateOrRegion: " . $authorizationBillingAddress->getStateOrRegion();
					}
					if ($authorizationBillingAddress->isSetPostalCode()) {
						$address .= "<br />PostalCode: " . $authorizationBillingAddress->getPostalCode();
					}
					if ($authorizationBillingAddress->isSetCountryCode()) {
						$address .= "<br />CountryCode: " . $authorizationBillingAddress->getCountryCode();
					}
					if ($authorizationBillingAddress->isSetPhone()) {
						$address .= "<br />Phone: " . $authorizationBillingAddress->getPhone();
					}
					$contents .= $this->getRow("AuthorizationBillingAddress: ", $address);

				}
				if ($authorizationDetails->isSetSellerAuthorizationNote()) {
					$contents .= $this->getRow("SellerAuthorizationNote: ", $authorizationDetails->getSellerAuthorizationNote());

				}
				if ($authorizationDetails->isSetAuthorizationAmount()) {
					$authorizationAmount = $authorizationDetails->getAuthorizationAmount();
					$more = '';
					if ($authorizationAmount->isSetAmount()) {
						$more .= "<br />    Amount: " . $authorizationAmount->getAmount();
					}
					if ($authorizationAmount->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $authorizationAmount->getCurrencyCode();
					}
					$contents .= $this->getRow("AuthorizationAmount: ", $more);

				}
				if ($authorizationDetails->isSetCapturedAmount()) {
					$capturedAmount = $authorizationDetails->getCapturedAmount();
					$more = '';
					if ($capturedAmount->isSetAmount()) {
						$more .= "<br />    Amount: " . $capturedAmount->getAmount();
					}
					if ($capturedAmount->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $capturedAmount->getCurrencyCode();
					}
					$contents .= $this->getRow("CapturedAmount: ", $more);

				}
				if ($authorizationDetails->isSetAuthorizationFee()) {
					$more = '';
					$authorizationFee = $authorizationDetails->getAuthorizationFee();
					if ($authorizationFee->isSetAmount()) {
						$more .= "<br />    Amount: " . $authorizationFee->getAmount();
					}
					if ($authorizationFee->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $authorizationFee->getCurrencyCode();
					}
					$contents .= $this->getRow("AuthorizationFee: ", $more);

				}
				if ($authorizationDetails->isSetIdList()) {
					$more = '';
					$idList = $authorizationDetails->getIdList();
					$memberList = $idList->getmember();
					foreach ($memberList as $member) {
						$more .= "<br />    member: " . $member;
					}
					$contents .= $this->getRow("IdList: ", $more);

				}
				if ($authorizationDetails->isSetCreationTimestamp()) {
					$contents .= $this->getRow("CreationTimestamp: ", $authorizationDetails->getCreationTimestamp());
				}
				if ($authorizationDetails->isSetExpirationTimestamp()) {
					$contents .= $this->getRow("ExpirationTimestamp: ", $authorizationDetails->getExpirationTimestamp());

				}
				if ($authorizationDetails->isSetAuthorizationStatus()) {
					$more = '';
					$authorizationStatus = $authorizationDetails->getAuthorizationStatus();
					if ($authorizationStatus->isSetState()) {
						$more .= "<br />    State: " . $authorizationStatus->getState();
					}
					if ($authorizationStatus->isSetLastUpdateTimestamp()) {
						$more .= "<br />    LastUpdateTimestamp: " . $authorizationStatus->getLastUpdateTimestamp();
					}
					if ($authorizationStatus->isSetReasonCode()) {
						$more .= "<br />    ReasonCode: " . $authorizationStatus->getReasonCode();
					}
					if ($authorizationStatus->isSetReasonDescription()) {
						$more .= "<br />    ReasonDescription: " . $authorizationStatus->getReasonDescription();
					}
					$contents .= $this->getRow("AuthorizationStatus: ", $more);

				}
				if ($authorizationDetails->isSetOrderItemCategories()) {
					$more = '';
					$orderItemCategories = $authorizationDetails->getOrderItemCategories();
					$orderItemCategoryList = $orderItemCategories->getOrderItemCategory();
					foreach ($orderItemCategoryList as $orderItemCategory) {
						$more .= "<br />    OrderItemCategory";
						$more .= "<br />" . $orderItemCategory;
					}
					$contents .= $this->getRow("OrderItemCategories: ", $more);
				}
				if ($authorizationDetails->isSetCaptureNow()) {
					$contents .= $this->getRow("CaptureNow: ", $authorizationDetails->getCaptureNow());

				}
				if ($authorizationDetails->isSetSoftDescriptor()) {
					$contents .= $this->getRow("SoftDescriptor: ", $authorizationDetails->getSoftDescriptor());

				}
				if ($authorizationDetails->isSetAddressVerificationCode()) {
					$contents .= $this->getRow("AddressVerificationCode: ", $authorizationDetails->getAddressVerificationCode());

				}
			}
		}
		/*
				if ($this->amazonData->isSetResponseMetadata()) {
					$contents .=$this->getRowFirstCol("ResponseMetadata");
					$responseMetadata = $this->amazonData->getResponseMetadata();
					if ($responseMetadata->isSetRequestId()) {
						$contents .=$this->getRow("RequestId: ",  $responseMetadata->getRequestId());
					}
				}
		*/
		//$contents .= $this->getRowFirstCol("ResponseHeaderMetadata " . $this->amazonData->getResponseHeaderMetadata());

		$contents .= $this->tableEnd();

		return $contents;
	}


}