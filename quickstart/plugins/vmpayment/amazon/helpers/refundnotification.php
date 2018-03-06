<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: refundnotification.php 8431 2014-10-14 14:11:46Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperRefundNotification extends amazonHelper {

	public function __construct (OffAmazonPaymentsNotifications_Model_RefundNotification $refundNotification, $method) {
		parent::__construct($refundNotification, $method);
	}

	function onNotificationUpdateOrderHistory ($order, $payments) {

		$order_history = array();
		$amazonState = "";
		$reasonCode = "";
		if (!$this->amazonData->isSetRefundDetails()) {
			$this->debugLog('NO isSetRefundDetails' . __FUNCTION__ . var_export($this->amazonData, true), 'error');
			return;
		}
		$details = $this->amazonData->getRefundDetails();
		if (!$details->isSetRefundStatus()) {
			$this->debugLog('NO isSetRefundStatus' . __FUNCTION__ . var_export($this->amazonData, true), 'error');
			return;
		}
		$status = $details->getRefundStatus();
		if (!$status->isSetState()) {
			$this->debugLog('NO isSetState' . __FUNCTION__ . var_export($this->amazonData, true), 'error');
			return;
		}
		$amazonState = $status->getState();

		if ($status->isSetReasonCode()) {
			$reasonCode = $status->getReasonCode();
		}
		// default value
		$order_history['customer_notified'] = 1;
		if ($amazonState == 'Completed') {
			$order_history['order_status'] = $this->_currentMethod->status_refunded;
			$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_REFUND_COMPLETED');
		} elseif ($amazonState == 'Declined') {
			$order_history['customer_notified'] = 0;
			$order_history['comments'] = vmText::sprintf('VMPAYMENT_AMAZON_COMMENT_STATUS_REFUND_DECLINED', $reasonCode);
			$order_history['order_status'] = $order['details']['BT']->order_status;

		} elseif ($amazonState == 'Pending') {
			$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_REFUND_PENDING');
			$order_history['order_status'] = $this->_currentMethod->status_orderconfirmed;
		}

		$orderModel = VmModel::getModel('orders');
		$orderModel->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, false);
		return $amazonState;
	}

	/**
	 * move to Pending => GetRefundDetails
	 * move to Declined => GetRefundDetails
	 * move to Completed => GetRefundDetails
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
		if ($this->amazonData->isSetRefundDetails()) {
			$details = $this->amazonData->getRefundDetails();
			if ($details->isSetRefundReferenceId()) {
				return $this->getVmReferenceId($details->getRefundReferenceId());
			}
		}
		return NULL;
	}

	public function getAmazonId () {
		if ($this->amazonData->isSetRefundDetails()) {
			$details = $this->amazonData->getRefundDetails();
			if ($details->isSetAmazonRefundId()) {
				return $details->getAmazonRefundId();
			}
		}
		return NULL;
	}

	public function getStoreInternalData () {
		//$amazonInternalData = $this->getStoreResultParams();
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetRefundDetails()) {
			$details = $this->amazonData->getRefundDetails();
			if ($details->isSetAmazonRefundId()) {
				$amazonInternalData->amazon_response_amazonRefundId = $details->getAmazonRefundId();
			}
			if ($details->isSetRefundStatus()) {
				$status = $details->getRefundStatus();
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
		$contents = $this->tableStart("Refund Notification");
		if ($this->amazonData->isSetRefundDetails()) {
			$contents .= $this->getRowFirstCol("RefundDetails");
			$refundDetails = $this->amazonData->getRefundDetails();
			if ($refundDetails->isSetAmazonRefundId()) {
				$contents .= $this->getRow("AmazonRefundId: ", $refundDetails->getAmazonRefundId());
			}
			if ($refundDetails->isSetRefundReferenceId()) {
				$contents .= $this->getRow("RefundReferenceId: ", $refundDetails->getRefundReferenceId());
			}
			if ($refundDetails->isSetRefundType()) {
				$contents .= $this->getRow("RefundType: ", $refundDetails->getRefundType());
			}
			if ($refundDetails->isSetRefundAmount()) {
				$more = '';
				$refundAmount = $refundDetails->getRefundAmount();
				if ($refundAmount->isSetAmount()) {
					$more .= "<br />      Amount " . $refundAmount->getAmount();
				}
				if ($refundAmount->isSetCurrencyCode()) {
					$more .= "<br />      CurrencyCode: " . $refundAmount->getCurrencyCode();
				}
				$contents .= $this->getRow("RefundAmount: ", $more);

			}
			if ($refundDetails->isSetFeeRefunded()) {
				$more = '';
				$feeRefunded = $refundDetails->getFeeRefunded();
				if ($feeRefunded->isSetAmount()) {
					$more .= "<br />      Amount " . $feeRefunded->getAmount();
				}
				if ($feeRefunded->isSetCurrencyCode()) {
					$more .= "<br />      CurrencyCode " . $feeRefunded->getCurrencyCode();
				}
				$contents .= $this->getRow("FeeRefunded: ", $more);

			}
			if ($refundDetails->isSetCreationTimestamp()) {
				$contents .= $this->getRow("CreationTimestamp: ", $refundDetails->getCreationTimestamp());

			}
			if ($refundDetails->isSetRefundStatus()) {
				$more = '';
				$refundStatus = $refundDetails->getRefundStatus();
				if ($refundStatus->isSetState()) {
					$more .= "<br />      State " . $refundStatus->getState();
				}
				if ($refundStatus->isSetLastUpdateTimestamp()) {
					$more .= "<br />      LastUpdateTimestamp " . $refundStatus->getLastUpdateTimestamp();
				}
				if ($refundStatus->isSetReasonCode()) {
					$more .= "<br />      ReasonCode " . $refundStatus->getReasonCode();
				}
				if ($refundStatus->isSetReasonDescription()) {
					$more .= "<br />      ReasonDescription " . $refundStatus->getReasonDescription();
				}
				$contents .= $this->getRow("RefundStatus: ", $more);

			}
			if ($refundDetails->isSetSoftDescriptor()) {
				$contents .= $this->getRow("SoftDescriptor: ", $refundDetails->getSoftDescriptor());

			}
		}
		$contents .= $this->tableEnd();
		return $contents;
	}
}


