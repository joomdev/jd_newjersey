<?php
defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: captureresponse.php 8316 2014-09-22 15:24:16Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperCaptureResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_CaptureResponse $captureResponse, $method) {
		parent::__construct($captureResponse, $method);

	}

	public function onResponseUpdateOrderHistory ($order) {

	}

	function getStoreInternalData () {
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetCaptureResult()) {
			$captureResult = $this->amazonData->getCaptureResult();
			if ($captureResult->isSetCaptureDetails()) {
				$captureDetails = $captureResult->getCaptureDetails();
				if ($captureDetails->isSetAmazonCaptureId()) {
					$amazonInternalData->amazon_response_amazonCaptureId = $captureDetails->getAmazonCaptureId();
				}
				if ($captureDetails->isSetCaptureReferenceId()) {
					$amazonInternalData->amazon_response_captureReferenceId = $captureDetails->getCaptureReferenceId();
				}
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
				}
				return $amazonInternalData;
			}


		}
		return NULL;
	}


	function getContents () {
		$contents = $this->tableStart("CaptureResponse");

		if ($this->amazonData->isSetCaptureResult()) {
			$contents .= $this->getRowFirstCol("CaptureResult");

			$captureResult = $this->amazonData->getCaptureResult();
			if ($captureResult->isSetCaptureDetails()) {
				$contents .= $this->getRowFirstCol("CaptureDetails");
				$captureDetails = $captureResult->getCaptureDetails();
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
						$more .= "<br />Amount: " . $refundedAmount->getAmount();
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
					$memberList = $idList->getmember();
					foreach ($memberList as $member) {
						$more .= "<br />member: " . $member;
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
						$more .= "<br />State: " . $captureStatus->getState();
					}
					if ($captureStatus->isSetLastUpdateTimestamp()) {
						$more .= "<br />LastUpdateTimestamp: " . $captureStatus->getLastUpdateTimestamp();
					}
					if ($captureStatus->isSetReasonCode()) {
						$more .= "<br />ReasonCode: " . $captureStatus->getReasonCode();
					}
					if ($captureStatus->isSetReasonDescription()) {
						$more .= "<br />ReasonDescription: " . $captureStatus->getReasonDescription();
					}
					if ($captureDetails->isSetSoftDescriptor()) {
						$more .= "<br />SoftDescriptor: " . $captureDetails->getSoftDescriptor();
					}
					$contents .= $this->getRow("CaptureStatus: ", $more);
				}
			}
		}
		/*
		if ($this->amazonData->isSetResponseMetadata()) {
			$more='';
			$responseMetadata = $this->amazonData->getResponseMetadata();
			if ($responseMetadata->isSetRequestId()) {
				$more .= "<br />RequestId: " . $responseMetadata->getRequestId();
			}
			$contents .=$this->getRow("ResponseMetadata: ", $more );
		}

		$contents .=$this->getRow("ResponseHeaderMetadata: ", $this->amazonData->getResponseHeaderMetadata() );
*/
		$contents .= $this->tableEnd();
		return $contents;
	}


}