<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: authorizeresponse.php 8259 2014-08-31 13:43:36Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
class amazonHelperGetCaptureDetailsResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_GetCaptureDetailsResponse $getCaptureDetailsResponse, $method) {
		parent::__construct($getCaptureDetailsResponse, $method);
	}


	public function onResponseUpdateOrderHistory ($order) {
		/*
				$order_history = array();
				$amazonState = "";
				$reasonCode = "";
				$authorizeResponse = $this->amazonData;
				// if am

				$authorizeResult = $authorizeResponse->getAuthorizeResult();
				$captureDetails = $authorizeResult->getCaptureDetails();
				if ($captureDetails->isSetCaptureStatus()) {
					$captureStatus = $captureDetails->getCaptureStatus();
					if (!$captureStatus->isSetState()) {
						return false;
					}
					$amazonState = $captureStatus->getState();

					if ($captureStatus->isSetReasonCode()) {
						$reasonCode = $captureStatus->getReasonCode();
					}
				}
				// In asynchronous mode, CaptureResponse is always Pending. Order status is not updated
				if ($amazonState == 'Pending') {
					return $amazonState;
				}

				// SYNCHRONOUS MODE: amazon returns in real time the final process status
				if ($amazonState == 'Open') {
					// it should always be the case if the CaptureNow == false
					$order_history['order_status'] = $this->_currentMethod->status_capture;
					$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_OPEN');
					$order_history['customer_notified'] = 1;
				} elseif ($amazonState == 'Closed') {
					// it should always be the case if the CaptureNow == true
					if (!($captureDetails->isSetCaptureNow() and $captureDetails->getCaptureNow())) {
						$this->debugLog('SYNCHRONOUS , capture Now, and Amazon State is NOT CLOSED' . __FUNCTION__ . var_export($authorizeResponse, true), 'error');
						return $amazonState;
					}
					$order_history['order_status'] = $this->_currentMethod->status_capture;
					$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURED');
					$order_history['customer_notified'] = 1;

				} elseif ($amazonState == 'Declined') {
					// handling Declined Captures
					$order_history['order_status'] = $this->_currentMethod->status_cancel;
					$order_history['comments'] = $reasonCode;
					if ($captureStatus->isSetReasonDescription()) {
						$order_history['comments'] .= " " . $captureStatus->getReasonDescription();
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
		if ($this->amazonData->isSetGetCaptureDetailsResult()) {
			$getCaptureDetailsResult = $this->amazonData->getGetCaptureDetailsResult();
			if ($getCaptureDetailsResult->isSetCaptureDetails()) {
				$captureDetails = $getCaptureDetailsResult->getCaptureDetails();
				if ($captureDetails->isSetCaptureStatus()) {
					$captureStatus = $captureDetails->getCaptureStatus();
					if ($captureStatus->isSetState()) {
						$amazonInternalData->amazon_response_state = $captureStatus->getState();
					}
					if ($captureStatus->isSetReasonCode()) {
						$amazonInternalData->amazon_response_reasonCode = $captureStatus->getReasonCode();
					}
					if ($captureStatus->isSetReasonDescription()) {
						$amazonInternalData->amazon_response_reasonDescription = $captureStatus->getReasonDescription();
					}
					if ($captureDetails->isSetAmazonCaptureId()) {
						$amazonInternalData->amazon_response_amazonCaptureId = $captureDetails->getAmazonCaptureId();
					}
				}

			}
			return $amazonInternalData;
		}
	}

	function getState () {
		if (!$this->amazonData->isSetGetCaptureDetailsResult()) {
			return NULL;
		}
		if (!$this->amazonData->isSetGetCaptureDetailsResult()) {
			return NULL;
		}
		$getCaptureDetailsResult = $this->amazonData->getGetCaptureDetailsResult();

		if (!$getCaptureDetailsResult->isSetCaptureDetails()) {
			return NULL;
		}
		$captureDetails = $getCaptureDetailsResult->getCaptureDetails();
		if (!$captureDetails->isSetCaptureStatus()) {
			return NULL;
		}

		$captureStatus = $captureDetails->getCaptureStatus();
		if (!$captureStatus->isSetState()) {
			return NULL;
		}
		return $captureStatus->getState();

	}

	function getContents () {

		$contents = $this->tableStart("GetCaptureDetailsResponse");
		if ($this->amazonData->isSetGetCaptureDetailsResult()) {
			$getCaptureDetailsResult = $this->amazonData->getGetCaptureDetailsResult();

			if ($getCaptureDetailsResult->isSetCaptureDetails()) {
				$contents .= $this->getRowFirstCol("GetCaptureDetailsResult");
				$captureDetails = $getCaptureDetailsResult->getCaptureDetails();

				if ($captureDetails->isSetAmazonCaptureId()) {
					$contents .= $this->getRow("AmazonCaptureId: ", $captureDetails->getAmazonCaptureId());

				}
				if ($captureDetails->isSetCaptureReferenceId()) {
					$contents .= $this->getRow("CaptureReferenceId: ", $captureDetails->getCaptureReferenceId());
				}


				if ($captureDetails->isSetSellerCaptureNote()) {
					$contents .= $this->getRow("SellerCaptureNote: ", $captureDetails->getSellerCaptureNote());

				}

				if ($captureDetails->isSetCaptureAmount()) {
					$capturedAmount = $captureDetails->getCaptureAmount();
					$more='';
					if ($capturedAmount->isSetAmount()) {
						$more .= "<br />    Amount: " . $capturedAmount->getAmount();
					}
					if ($capturedAmount->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $capturedAmount->getCurrencyCode();
					}
					$contents .=$this->getRow("CapturedAmount: ",  $more);

				}
				if ($captureDetails->isSetRefundedAmount()) {
					$refundedAmount = $captureDetails->getRefundedAmount();
					$more = '';
					if ($refundedAmount->isSetAmount()) {
						$more .= "<br />    Amount: " . $refundedAmount->getAmount();
					}
					if ($refundedAmount->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $refundedAmount->getCurrencyCode();
					}
					$contents .= $this->getRow("RefundedAmount: ", $more);

				}
				if ($captureDetails->isSetCaptureFee()) {
					$more = '';
					$captureFee = $captureDetails->getCaptureFee();
					if ($captureFee->isSetAmount()) {
						$more .= "<br />    Amount: " . $captureFee->getAmount();
					}
					if ($captureFee->isSetCurrencyCode()) {
						$more .= "<br />    CurrencyCode: " . $captureFee->getCurrencyCode();
					}
					$contents .= $this->getRow("CaptureFee: ", $more);

				}
				if ($captureDetails->isSetIdList()) {
					$more = '';
					$idList = $captureDetails->getIdList();
					$memberList = $idList->getmember();
					foreach ($memberList as $member) {
						$more .= "<br />    member: " . $member;
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
						$more .= "<br />    State: " . $captureStatus->getState();
					}
					if ($captureStatus->isSetLastUpdateTimestamp()) {
						$more .= "<br />    LastUpdateTimestamp: " . $captureStatus->getLastUpdateTimestamp();
					}
					if ($captureStatus->isSetReasonCode()) {
						$more .= "<br />    ReasonCode: " . $captureStatus->getReasonCode();
					}
					if ($captureStatus->isSetReasonDescription()) {
						$more .= "<br />    ReasonDescription: " . $captureStatus->getReasonDescription();
					}
					$contents .= $this->getRow("CaptureStatus: ", $more);

				}

				if ($captureDetails->isSetSoftDescriptor()) {
					$contents .= $this->getRow("SoftDescriptor: ", $captureDetails->getSoftDescriptor());
				}

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