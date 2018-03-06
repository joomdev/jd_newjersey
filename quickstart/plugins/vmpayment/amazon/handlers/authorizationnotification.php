<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: authorizationnotification.php 8685 2015-02-05 18:40:30Z alatak $
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperAuthorizationNotification extends amazonHelper {

	public function __construct(OffAmazonPaymentsNotifications_Model_authorizationNotification $authorizationNotification, $method) {
		parent::__construct($authorizationNotification, $method);
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
	function onNotificationUpdateOrderHistory($order, $payments) {
		$order_history = array();
		$amazonState = "";
		$reasonCode = "";

		// get Old amazon State
		$lastPayment = end($payments);
		$previousAmazonState = $lastPayment->amazon_response_state;

		if (!$this->amazonData->isSetAuthorizationDetails()) {
			return false;
		}
		$authorizationDetails = $this->amazonData->getAuthorizationDetails();
		if (!$authorizationDetails->isSetAuthorizationStatus()) {
			return false;
		}

		$authorizationStatus = $authorizationDetails->getAuthorizationStatus();
		if (!$authorizationStatus->isSetState()) {
			return false;
		}
		$amazonState = $authorizationStatus->getState();
		// In synchronous Mode, order history has been updated by the Authorization Response
		// Other notifications may be received, but they are more informative: MaxCapturesProcessed if the FULL amount Capture has been done


		if ($authorizationStatus->isSetReasonCode()) {
			$reasonCode = $authorizationStatus->getReasonCode();
		}

		$order_history['customer_notified'] = 0;
		if ($amazonState != $previousAmazonState) {
			if ($amazonState == 'Open') {
				$order_history['order_status'] = $this->_currentMethod->status_authorization;
				$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_OPEN');
			} elseif ($amazonState == 'Declined') {
				$order_history['customer_notified'] = 1;
				if ($reasonCode == 'InvalidPaymentMethod') {
					if ($this->_currentMethod->soft_decline == 'soft_decline_enabled') {
						$order_history['comments'] = $this->getSoftDeclinedComment();
						$order_history['order_status'] = $this->_currentMethod->status_orderconfirmed;
					} else {
						$order_history['comments'] = vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_INVALIDPAYMENTMETHOD', $reasonCode);
						$order_history['order_status'] = $this->_currentMethod->status_cancel;
					}
				} elseif ($reasonCode == 'AmazonRejected') {
					$order_history['customer_notified'] = 1;
					$order_history['order_status'] = $this->_currentMethod->status_cancel;
					$order_history['comments'] = vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_DECLINED', $reasonCode);
				} elseif ($reasonCode == 'TransactionTimedOut') {
// TODO  retry the authorization again
					$order_history['order_status'] = $this->_currentMethod->status_cancel;
					$order_history['comments'] = vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_DECLINED', $reasonCode);
				}

			} elseif ($amazonState == 'Pending') {
				$order_history['order_status'] = $this->_currentMethod->status_orderconfirmed;
				$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_PENDING');
				$order_history['customer_notified'] = 0;
			} elseif ($amazonState == 'Closed') {
				if ($reasonCode == 'MaxCapturesProcessed' and $this->isCaptureNow()) {
					$order_history['order_status'] = $this->_currentMethod->status_capture;
					$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURE_NOTIFICATION');
					$order_history['customer_notified'] = 0;
				} else {
					$order_history['order_status'] = $this->_currentMethod->status_cancel;
					$order_history['comments'] = vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_CLOSED', $reasonCode);
					$order_history['customer_notified'] = 0;
				}

			}

			$orderModel = VmModel::getModel('orders');
			$orderModel->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, false);
		}

		return $amazonState;
	}

	private function getSoftDeclinedComment() {

		if (!class_exists('VirtueMartModelVendor')) {
			require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'vendor.php');
		}
		$virtuemart_vendor_id = 1;
		$vendorModel = VmModel::getModel('vendor');
		$vendorModel->setId($virtuemart_vendor_id);
		$vendor = $vendorModel->getVendor($virtuemart_vendor_id);
		$vendorFields = $vendorModel->getVendorAddressFields($virtuemart_vendor_id);
		$vendorEmail = $vendorFields['fields']['email']['value'];
		if (isset($vendorFields['fields']['phone_1']['value'])) {
			$vendorPhone = $vendorFields['fields']['phone_1']['value'];
		} else {
			$vendorPhone = "";
		}

		return vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_INVALIDPAYMENTMETHOD_SOFT_DECLINED', $vendor->vendor_store_name, $vendorEmail, $vendorPhone);

	}

	private function isSynchronousMode() {
		if (($this->_currentMethod->erp_mode == "erp_mode_disabled" AND $this->_currentMethod->authorization_mode_erp_disabled == "automatic_synchronous") or ($this->_currentMethod->erp_mode == "erp_mode_enabled" AND $this->_currentMethod->authorization_mode_erp_enabled == "automatic_synchronous")
		) {
			return true;
		}

		return false;
	}

	public function getStoreInternalData() {
		//$amazonInternalData = $this->getStoreResultParams();
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetAuthorizationDetails()) {
			$authorizationDetails = $this->amazonData->getAuthorizationDetails();
			if ($authorizationDetails->isSetAmazonAuthorizationId()) {
				$amazonInternalData->amazon_response_amazonAuthorizationId = $authorizationDetails->getAmazonAuthorizationId();
			}
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
			}
		}
		return $amazonInternalData;
	}

	/**
	 * move to Pending =>GetAuthorizationDetails, closeAuthorization
	 * move to Open => GetAuthorizationDetails, capture, closeAuthorization
	 * move to Declined => GetAuthorizationDetails
	 * move to Closed => GetAuthorizationDetails
	 * @param $order
	 * @param $payments
	 * @param $amazonState
	 * @return bool|string
	 */
	public function onNotificationNextOperation($order, $payments, $amazonState) {
		$getAuthorizationDetailsState = array('Pending', 'Open', 'Closed');
		$cancelPaymentState = array('Declined');
		if (in_array($amazonState, $getAuthorizationDetailsState)) {
			return 'onNotificationGetAuthorizationDetails';
		} elseif (in_array($amazonState, $cancelPaymentState)) {
			return 'cancelPayment';
		}
		return false;
	}

	public function getReferenceId() {
		if ($this->amazonData->isSetAuthorizationDetails()) {
			$authorizationDetails = $this->amazonData->getAuthorizationDetails();
			if ($authorizationDetails->isSetAuthorizationReferenceId()) {
				return $authorizationDetails->getAuthorizationReferenceId();
			}
		}
		return NULL;
	}

	public function getAmazonId() {
		if ($this->amazonData->isSetAuthorizationDetails()) {
			$authorizationDetails = $this->amazonData->getAuthorizationDetails();
			if ($authorizationDetails->isSetAmazonAuthorizationId()) {
				return $authorizationDetails->getAmazonAuthorizationId();
			}
		}
		return NULL;
	}

	public function isCaptureNow() {
		$authorizationDetails = $this->amazonData->getAuthorizationDetails();
		if ($authorizationDetails->isSetCaptureNow()) {
			return $authorizationDetails->getCaptureNow();
		}
		return false;
	}


	public function getAmazonAuthorizationId() {
		return $this->amazonData->getAuthorizationDetails()->getAmazonAuthorizationId();
	}

	public function getContents() {

		$contents = $this->tableStart("Authorization Notification");
		if ($this->amazonData->isSetAuthorizationDetails()) {
			$contents .= $this->getRowFirstCol("AuthorizeDetails");
			$authorizationDetails = $this->amazonData->getAuthorizationDetails();
			if ($authorizationDetails->isSetAmazonAuthorizationId()) {
				$contents .= $this->getRow("AmazonAuthorizationId: ", $authorizationDetails->getAmazonAuthorizationId());
			}
			if ($authorizationDetails->isSetAuthorizationReferenceId()) {
				$contents .= $this->getRow("AuthorizationReferenceId: ", $authorizationDetails->getAuthorizationReferenceId());
			}
			if ($authorizationDetails->isSetAuthorizationAmount()) {
				$more = '';
				$authorizationAmount = $authorizationDetails->getAuthorizationAmount();
				if ($authorizationAmount->isSetAmount()) {
					$more .= "Amount: " . $authorizationAmount->getAmount() . "<br />";
				}
				if ($authorizationAmount->isSetCurrencyCode()) {
					$more .= "CurrencyCode: " . $authorizationAmount->getCurrencyCode() . "<br />";
				}
				$contents .= $this->getRow("AuthorizationAmount: ", $more);
			}
			if ($authorizationDetails->isSetCapturedAmount()) {
				$more = '';
				$capturedAmount = $authorizationDetails->getCapturedAmount();
				if ($capturedAmount->isSetAmount()) {
					$more .= "Amount: " . $capturedAmount->getAmount() . "<br />";
				}
				if ($capturedAmount->isSetCurrencyCode()) {
					$more .= "CurrencyCode: " . $capturedAmount->getCurrencyCode() . "<br />";
				}
				$contents .= $this->getRow("CapturedAmount: ", $more);
			}
			if ($authorizationDetails->isSetAuthorizationFee()) {
				$more = '';

				$authorizationFee = $authorizationDetails->getAuthorizationFee();
				if ($authorizationFee->isSetAmount()) {
					$more .= "Amount: " . $authorizationFee->getAmount() . "<br />";
				}
				if ($authorizationFee->isSetCurrencyCode()) {
					$more .= "CurrencyCode: " . $authorizationFee->getCurrencyCode() . "<br />";
				}
				$contents .= $this->getRow("AuthorizationFee: ", $more);
			}
			if ($authorizationDetails->isSetIdList()) {

				$idList = $authorizationDetails->getIdList();
				$memberList = $idList->getId();
				$more = '';
				foreach ($memberList as $member) {
					$more .= "<br /> member: " . $member;
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
				$authorizationStatus = $authorizationDetails->getAuthorizationStatus();
				$more = '';
				if ($authorizationStatus->isSetState()) {
					$more .= "State: " . $authorizationStatus->getState() . "<br />";
				}
				if ($authorizationStatus->isSetLastUpdateTimestamp()) {
					$more .= "LastUpdateTimestamp: " . $authorizationStatus->getLastUpdateTimestamp() . "<br />";
				}
				if ($authorizationStatus->isSetReasonCode()) {
					$more .= "ReasonCode: " . $authorizationStatus->getReasonCode() . "<br />";
				}
				if ($authorizationStatus->isSetReasonDescription()) {
					$more .= "ReasonDescription: " . $authorizationStatus->getReasonDescription() . "<br />";
				}
				$contents .= $this->getRow("AuthorizationStatus", $more);

			}
			if ($authorizationDetails->isSetOrderItemCategories()) {
				$orderItemCategories = $authorizationDetails->getOrderItemCategories();
				$orderItemCategoryList = $orderItemCategories->getOrderItemCategory();
				$more = '';
				foreach ($orderItemCategoryList as $orderItemCategory) {
					$more .= "OrderItemCategory: " . $orderItemCategory . "<br />";
				}
				$contents .= $this->getRow("OrderItemCategories", $more);

			}
			if ($authorizationDetails->isSetCaptureNow()) {
				$contents .= $this->getRow("CaptureNow", $authorizationDetails->getCaptureNow());

			}
			if ($authorizationDetails->isSetSoftDescriptor()) {
				$contents .= $this->getRow("SoftDescriptor", $authorizationDetails->getSoftDescriptor());
			}
		}
		$contents .= $this->tableEnd();

		return $contents;
	}


}