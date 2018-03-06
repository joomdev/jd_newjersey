<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id$
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperGetRefundDetailsResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_GetRefundDetailsResponse $getRefundDetailsResponse, $method) {
		parent::__construct($getRefundDetailsResponse, $method);
	}


	public function onResponseUpdateOrderHistory ($order) {
		/*
				$order_history = array();
				$amazonState = "";
				$reasonCode = "";
				$authorizeResponse = $this->amazonData;
				// if am

				$authorizeResult = $authorizeResponse->getAuthorizeResult();
				$refundDetails = $authorizeResult->getRefundDetails();
				if ($refundDetails->isSetRefundStatus()) {
					$refundStatus = $refundDetails->getRefundStatus();
					if (!$refundStatus->isSetState()) {
						return false;
					}
					$amazonState = $refundStatus->getState();

					if ($refundStatus->isSetReasonCode()) {
						$reasonCode = $refundStatus->getReasonCode();
					}
				}
				// In asynchronous mode, RefundResponse is always Pending. Order status is not updated
				if ($amazonState == 'Pending') {
					return $amazonState;
				}

				// SYNCHRONOUS MODE: amazon returns in real time the final process status
				if ($amazonState == 'Open') {
					// it should always be the case if the CaptureNow == false
					$order_history['order_status'] = $this->_currentMethod->status_refund;
					$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_OPEN');
					$order_history['customer_notified'] = 1;
				} elseif ($amazonState == 'Closed') {
					// it should always be the case if the CaptureNow == true
					if (!($refundDetails->isSetCaptureNow() and $refundDetails->getCaptureNow())) {
						$this->debugLog('SYNCHRONOUS , capture Now, and Amazon State is NOT CLOSED' . __FUNCTION__ . var_export($authorizeResponse, true), 'error');
						return $amazonState;
					}
					$order_history['order_status'] = $this->_currentMethod->status_capture;
					$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURED');
					$order_history['customer_notified'] = 1;

				} elseif ($amazonState == 'Declined') {
					// handling Declined Refunds
					$order_history['order_status'] = $this->_currentMethod->status_cancel;
					$order_history['comments'] = $reasonCode;
					if ($refundStatus->isSetReasonDescription()) {
						$order_history['comments'] .= " " . $refundStatus->getReasonDescription();
					}
					$order_history['customer_notified'] = 0;

				}
				$order_history['amazonState'] = $amazonState;
				$modelOrder = VmModel::getModel('orders');
				$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, TRUE);


				return $amazonState;
		*/
	}


	public function getStoreInternalData () {
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetGetRefundDetailsResult()) {
			$getRefundDetailsResult = $this->amazonData->getGetRefundDetailsResult();
			if ($getRefundDetailsResult->isSetRefundDetails()) {
				$refundDetails = $getRefundDetailsResult->getRefundDetails();
				if ($refundDetails->isSetRefundStatus()) {
					$refundStatus = $refundDetails->getRefundStatus();
					if ($refundStatus->isSetState()) {
						$amazonInternalData->amazon_response_state = $refundStatus->getState();
					}
					if ($refundStatus->isSetReasonCode()) {
						$amazonInternalData->amazon_response_reasonCode = $refundStatus->getReasonCode();
					}
					if ($refundStatus->isSetReasonDescription()) {
						$amazonInternalData->amazon_response_reasonDescription = $refundStatus->getReasonDescription();
					}
					if ($refundDetails->isSetAmazonRefundId()) {
						$amazonInternalData->amazon_response_amazonRefundId = $refundDetails->getAmazonRefundId();
					}
				}

			}
			return $amazonInternalData;
		}
	}


	/**
	 * @return mixed
	 */
	function getState () {
		if ($this->amazonData->isSetGetRefundDetailsResult()) {
			$getRefundDetailsResult = $this->amazonData->getGetRefundDetailsResult();
			return $getRefundDetailsResult->getRefundDetails()->getRefundStatus()->getState();

		}
		return NULL;
	}

	function getContents () {

		$contents = $this->tableStart("GetRefundDetailsResponse");
		if ($this->amazonData->isSetGetRefundDetailsResult()) {
			$getRefundDetailsResult = $this->amazonData->getGetRefundDetailsResult();
				$contents .= $this->getRowFirstCol("GetRefundDetailsResult");
			$refundDetails = $getRefundDetailsResult->getRefundDetails();

				if ($refundDetails->isSetAmazonRefundId()) {
					$contents .= $this->getRow("AmazonRefundId: ", $refundDetails->getAmazonRefundId());

				}
				if ($refundDetails->isSetRefundReferenceId()) {
					$contents .= $this->getRow("RefundReferenceId: ", $refundDetails->getRefundReferenceId());
				}

				if ($refundDetails->isSetSellerRefundNote()) {
					$contents .= $this->getRow("SellerRefundNote: ", $refundDetails->getSellerRefundNote());
				}
				if ($refundDetails->isSetRefundAmount()) {
					$refundAmount = $refundDetails->getRefundAmount();
					$more = '';
					if ($refundAmount->isSetAmount()) {
						$more .= "<br />    Amount: " . $refundAmount->getAmount();
					}
					if ($refundAmount->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $refundAmount->getCurrencyCode();
					}
					$contents .= $this->getRow("RefundAmount: ", $more);

				}
				if ($refundDetails->isSetFeeRefunded()) {
					$feeRefunded = $refundDetails->getFeeRefunded();
					$more = '';
					if ($feeRefunded->isSetAmount()) {
						$more .= "<br />    Amount: " . $feeRefunded->getAmount();
					}
					if ($feeRefunded->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $feeRefunded->getCurrencyCode();
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
						$more .= "<br />    State: " . $refundStatus->getState();
					}
					if ($refundStatus->isSetLastUpdateTimestamp()) {
						$more .= "<br />    LastUpdateTimestamp: " . $refundStatus->getLastUpdateTimestamp();
					}
					if ($refundStatus->isSetReasonCode()) {
						$more .= "<br />    ReasonCode: " . $refundStatus->getReasonCode();
					}
					if ($refundStatus->isSetReasonDescription()) {
						$more .= "<br />    ReasonDescription: " . $refundStatus->getReasonDescription();
					}
					$contents .= $this->getRow("RefundStatus: ", $more);

				}

				if ($refundDetails->isSetSoftDescriptor()) {
					$contents .= $this->getRow("SoftDescriptor: ", $refundDetails->getSoftDescriptor());

				}

		}
		/*
				if ($this->amazonData->isSetResponseMetadata()) {
					$contents .= $this->getRowFirstCol("ResponseMetadata");
					$responseMetadata = $this->amazonData->getResponseMetadata();
					if ($responseMetadata->isSetRequestId()) {
						$contents .= $this->getRow("RequestId: ", $responseMetadata->getRequestId());
					}
				}
				$contents .= $this->getRowFirstCol("ResponseHeaderMetadata " . $this->amazonData->getResponseHeaderMetadata());
		*/
		$contents .= $this->tableEnd();

		return $contents;
	}


}