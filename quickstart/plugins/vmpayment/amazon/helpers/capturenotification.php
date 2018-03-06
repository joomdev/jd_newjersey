<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: capturenotification.php 8685 2015-02-05 18:40:30Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperCaptureNotification extends amazonHelper {

	public function __construct (OffAmazonPaymentsNotifications_Model_CaptureNotification $captureNotification, $method) {
		parent::__construct($captureNotification, $method);
	}

	/**
	 * if synchronous, then should not update order status
	 * @param $order
	 * @param $payments
	 */
	function onNotificationUpdateOrderHistory ($order, $payments) {
		if ($this->_currentMethod->authorization_mode_erp_disabled == 'automatic_synchronous') {
			return;
		}
		$order_history = array();
		$amazonState = "";
		$reasonCode = "";
		if ($this->amazonData->isSetCaptureDetails()) {
			$details = $this->amazonData->getCaptureDetails();
			if ($details->isSetCaptureStatus()) {
				$status = $details->getCaptureStatus();
				if ($status->isSetState()) {
					$amazonState = $status->getState();
				} else {
					// TODO THIS IS AN ERROR
				}
				if ($status->isSetReasonCode()) {
					$reasonCode = $status->getReasonCode();
				}
			}
			// default value
			$order_history['customer_notified'] = 1;
			if ($amazonState == 'Completed') {
				$order_history['order_status'] = $this->_currentMethod->status_capture;
				$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURE_NOTIFICATION');

			} elseif ($amazonState == 'Declined') {
				if ($reasonCode == 'AmazonRejected') {
					$order_history['order_status'] = $this->_currentMethod->status_cancel;
				} elseif ($reasonCode == 'ProcessingFailure') {
					// TODO  retry the Capture again if in Open State, and then call the capture again
					$order_history['order_status'] = $this->_currentMethod->status_cancel;
				}
				$order_history['comments'] = vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURE_DECLINED', $reasonCode);
			} elseif ($amazonState == 'Pending') {
				$order_history['order_status'] = $this->_currentMethod->status_orderconfirmed;
				$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURE_PENDING');
				$order_history['customer_notified'] = 0;

			} elseif ($amazonState == 'Closed') {
				// keep old status
				$order_history['customer_notified'] = 0;
				$order_history['order_status'] = $order['details']['BT']->order_status;
				$order_history['comments'] = vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURE_CLOSED', $reasonCode);
			}

			$order_history['amazonState'] = $amazonState;
			$orderModel = VmModel::getModel('orders');
			$orderModel->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, false);
		}
	}



	/**
	 * move to Pending => GetCaptureDetails
	 * move to Declined => GetCaptureDetails
	 * move to Closed => GetCaptureDetails
	 * move to Completed => GetCaptureDetails, Refund
	 * @param $order
	 * @param $payments
	 * @param $amazonState
	 * @return bool|string
	 */
	/*
	public function onNotificationNextOperation($order, $payments, $amazonState) {
		return false;

	}
*/

	public function getReferenceId () {
		if ($this->amazonData->isSetCaptureDetails()) {
			$details = $this->amazonData->getCaptureDetails();
			if ($details->isSetCaptureReferenceId()) {
				return $this->getVmReferenceId($details->getCaptureReferenceId());
			}
		}
		return NULL;
	}

	public function getAmazonId () {
		if ($this->amazonData->isSetCaptureDetails()) {
			$details = $this->amazonData->getCaptureDetails();
			if ($details->isSetAmazonCaptureId()) {
				return $details->getAmazonCaptureId();
			}
		}
		return NULL;
	}

	public function getStoreInternalData () {
		//$amazonInternalData = $this->getStoreResultParams();
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetCaptureDetails()) {
			$details = $this->amazonData->getCaptureDetails();
			if ($details->isSetAmazonCaptureId()) {
				$amazonInternalData->amazon_response_amazonCaptureId = $details->getAmazonCaptureId();
			}
			if ($details->isSetCaptureStatus()) {
				$status = $details->getCaptureStatus();
				if ($status->isSetState()) {
					$amazonInternalData->amazon_response_state = $status->getState();
				}
				if ($status->isSetReasonCode()) {
					$amazonInternalData->amazon_response_reasonCode = $status->getReasonCode();
				}
				if ($status->isSetReasonDescription()) {
					$amazonInternalData->amazon_response_reasonDescription = $status->getReasonDescription();
				}
			}
		}
		return $amazonInternalData;
	}


	function getContents () {
		$contents = $this->tableStart("Capture Notification");
		if ($this->amazonData->isSetCaptureDetails()) {
			$contents .= $this->getRowFirstCol("CaptureDetails");
			$captureDetails = $this->amazonData->getCaptureDetails();
			if ($captureDetails->isSetAmazonCaptureId()) {
				$contents .= $this->getRow("AmazonCaptureId: ", $captureDetails->getAmazonCaptureId());

			}
			if ($captureDetails->isSetCaptureReferenceId()) {
				$contents .= $this->getRow("CaptureReferenceId: ", $captureDetails->getCaptureReferenceId());

			}
			if ($captureDetails->isSetCaptureAmount()) {
				$more = '';
				$captureAmount = $captureDetails->getCaptureAmount();
				if ($captureAmount->isSetAmount()) {
					$more .= "<br />Amount: " . $captureAmount->getAmount();
				}
				if ($captureAmount->isSetCurrencyCode()) {
					$more .= "<br />CurrencyCode: " . $captureAmount->getCurrencyCode();
				}
				$contents .= $this->getRow("CaptureAmount: ", $more);

			}
			if ($captureDetails->isSetRefundedAmount()) {
				$more = '';
				$refundedAmount = $captureDetails->getRefundedAmount();
				if ($refundedAmount->isSetAmount()) {
					$more .= "<br />Amount:" . $refundedAmount->getAmount();
				}
				if ($refundedAmount->isSetCurrencyCode()) {
					$more .= "<br />CurrencyCode: " . $refundedAmount->getCurrencyCode();
				}
				$contents .= $this->getRow("RefundedAmount: ", $more);
			}
			if ($captureDetails->isSetCaptureFee()) {
				$more = '';
				$captureFee = $captureDetails->getCaptureFee();
				if ($captureFee->isSetAmount()) {
					$more .= "<br />Amount: " . $captureFee->getAmount();
				}
				if ($captureFee->isSetCurrencyCode()) {
					$more .= "<br />CurrencyCode: " . $captureFee->getCurrencyCode();
				}
				$contents .= $this->getRow("CaptureFee: ", $more);

			}
			if ($captureDetails->isSetIdList()) {
				$more = '';
				$idList = $captureDetails->getIdList();
				$memberList = $idList->getId();
				foreach ($memberList as $member) {
					$more .= "<br />      member: " . $member;
				}
				$contents .= $this->getRow("IdList: ", $more);
			}
			if ($captureDetails->isSetCreationTimestamp()) {
				$contents .= $this->getRow("CreationTimestamp: ", $captureDetails->getCreationTimestamp());

			}
			if ($captureDetails->isSetCaptureStatus()) {
				$more = '';
				$captureStatus = $captureDetails->getCaptureStatus();
				if ($captureStatus->isSetState()) {
					$more .= "<br />          State";
					$more .= $captureStatus->getState();
				}
				if ($captureStatus->isSetLastUpdateTimestamp()) {
					$more .= "<br /> LastUpdateTimestamp: " . $captureStatus->getLastUpdateTimestamp();
				}
				if ($captureStatus->isSetReasonCode()) {
					$more .= "<br /> ReasonCode: " . $captureStatus->getReasonCode();
				}
				if ($captureStatus->isSetReasonDescription()) {
					$more .= "<br /> ReasonDescription: " . $captureStatus->getReasonDescription();
				}
				$contents .= $this->getRow("CaptureStatus: ", $more);
			}
			if ($captureDetails->isSetSoftDescriptor()) {
				$contents .= $this->getRow("SoftDescriptor: ", $captureDetails->getSoftDescriptor());

			}
		}
		$contents .= $this->tableEnd();
		return $contents;
	}


}