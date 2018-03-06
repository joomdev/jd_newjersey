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
class amazonHelperRefundResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_RefundResponse $refundResponse, $method) {
		parent::__construct($refundResponse, $method);

	}

	public function onResponseUpdateOrderHistory ($order) {

	}

	function getStoreInternalData () {
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetRefundResult()) {
			$refundResult = $this->amazonData->getRefundResult();
			if ($refundResult->isSetRefundDetails()) {
				$refundDetails = $refundResult->getRefundDetails();
				if ($refundDetails->isSetAmazonRefundId()) {
					$amazonInternalData->amazon_response_amazonRefundId = $refundDetails->getAmazonRefundId();
				}

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

				}
				return $amazonInternalData;
			}


		}
		return NULL;
	}


	function getContents () {
		$contents = $this->tableStart("RefundResponse");
		if ($this->amazonData->isSetRefundResult()) {
			$contents .= $this->getRowFirstCol("RefundResult");
			$refundResult = $this->amazonData->getRefundResult();
			if ($refundResult->isSetRefundDetails()) {
				$contents .= $this->getRowFirstCol("RefundDetails");
				$refundDetails = $refundResult->getRefundDetails();
				if ($refundDetails->isSetAmazonRefundId()) {
					$contents .= $this->getRow("AmazonRefundId: ", $refundDetails->getAmazonRefundId());
				}
				if ($refundDetails->isSetRefundReferenceId()) {
					$contents .= $this->getRow("RefundReferenceId: ", $refundDetails->getRefundReferenceId());
				}
				if ($refundDetails->isSetSellerRefundNote()) {
					$contents .= $this->getRow("SellerRefundNote: ", $refundDetails->getSellerRefundNote());
				}
				if ($refundDetails->isSetRefundType()) {
					$contents .= $this->getRow("RefundType: ", $refundDetails->getRefundType());
				}
				if ($refundDetails->isSetRefundAmount()) {
					$more = '';
					$refundAmount = $refundDetails->getRefundAmount();
					if ($refundAmount->isSetAmount()) {
						$more .= "Amount: ";
						$more .= $refundAmount->getAmount() . "<br/>";;
					}
					if ($refundAmount->isSetCurrencyCode()) {
						$more .= "CurrencyCode: ";
						$more .= $refundAmount->getCurrencyCode() . "<br/>";;
					}
					$contents .= $this->getRow("RefundAmount: ", $more);
				}
				if ($refundDetails->isSetFeeRefunded()) {
					$more = '';
					$feeRefunded = $refundDetails->getFeeRefunded();
					if ($feeRefunded->isSetAmount()) {
						$more .= "Amount: ";
						$more .= $feeRefunded->getAmount() . "<br/>";;
					}
					if ($feeRefunded->isSetCurrencyCode()) {
						$more .= "CurrencyCode: ";
						$more .= $feeRefunded->getCurrencyCode() . "<br/>";;
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
						$more .= "State: ";
						$more .= $refundStatus->getState() . "<br/>";;
					}
					if ($refundStatus->isSetLastUpdateTimestamp()) {
						$more .= "LastUpdateTimestamp: ";
						$more .= $refundStatus->getLastUpdateTimestamp() . "<br/>";;
					}
					if ($refundStatus->isSetReasonCode()) {
						$more .= "ReasonCode: ";
						$more .= $refundStatus->getReasonCode() . "<br/>";;
					}
					if ($refundStatus->isSetReasonDescription()) {
						$more .= "ReasonDescription: ";
						$more .= $refundStatus->getReasonDescription() . "<br/>";;
					}
					$contents .= $this->getRow("RefundStatus: ", $more);
				}
				if ($refundDetails->isSetSoftDescriptor()) {
					$contents .= $this->getRow("SoftDescriptor: ", $refundDetails->getSoftDescriptor());
				}
			}
		}
		/*
		if ($this->amazonData->isSetResponseMetadata()) {
			$more='';
			$responseMetadata = $this->amazonData->getResponseMetadata();
			if ($responseMetadata->isSetRequestId()) {
				$more .= " RequestId: ";
				$more .= $responseMetadata->getRequestId() . "<br/>";;
			}
			$contents .=$this->getRow("ResponseMetadata: ",$more );
		}
*/
		//$contents .= $this->getRow("ResponseHeaderMetadata: ", $this->amazonData->getResponseHeaderMetadata());
		$contents .= $this->tableEnd();
		return $contents;
	}


}