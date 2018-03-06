<?php

defined('_JEXEC') or die('Direct Access to' . basename(__FILE__) . 'isnotallowed.');

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
class amazonHelperGetOrderReferenceDetailsResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_GetOrderReferenceDetailsResponse $getOrderReferenceDetailsResponse, $method) {
		parent::__construct($getOrderReferenceDetailsResponse, $method);
	}


	public function onResponseUpdateOrderHistory ($order) {
		/*
				$order_history=array();
				$amazonState="";
				$reasonCode="";
				$authorizeResponse=$this->amazonData;
				//ifam

				$authorizeResult=$authorizeResponse->getAuthorizeResult();
				$orderReferenceDetails=$authorizeResult->getOrderReferenceDetails();
				if($orderReferenceDetails->isSetOrderReferenceStatus()){
					$orderReferenceStatus=$orderReferenceDetails->getOrderReferenceStatus();
					if(!$orderReferenceStatus->isSetState()){
						returnfalse;
					}
					$amazonState=$orderReferenceStatus->getState();

					if($orderReferenceStatus->isSetReasonCode()){
						$reasonCode=$orderReferenceStatus->getReasonCode();
					}
				}
				//Inasynchronousmode,OrderReferenceResponseisalwaysPending.Orderstatusisnotupdated
				if($amazonState=='Pending'){
					return$amazonState;
				}

				//SYNCHRONOUSMODE:amazonreturnsinrealtimethefinalprocessstatus
				if($amazonState=='Open'){
					//itshouldalwaysbethecaseiftheCaptureNow==false
					$order_history['order_status']=$this->_currentMethod->status_orderreference;
					$order_history['comments']=vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_AUTHORIZATION_OPEN');
					$order_history['customer_notified']=1;
				}elseif($amazonState=='Closed'){
					//itshouldalwaysbethecaseiftheCaptureNow==true
					if(!($orderReferenceDetails->isSetCaptureNow()and$orderReferenceDetails->getCaptureNow())){
						$this->debugLog('SYNCHRONOUS,captureNow,andAmazonStateisNOTCLOSED'.__FUNCTION__.var_export($authorizeResponse,true),'error');
						return$amazonState;
					}
					$order_history['order_status']=$this->_currentMethod->status_capture;
					$order_history['comments']=vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_CAPTURED');
					$order_history['customer_notified']=1;

				}elseif($amazonState=='Declined'){
					//handlingDeclinedOrderReferences
					$order_history['order_status']=$this->_currentMethod->status_cancel;
					$order_history['comments']=$reasonCode;
					if($orderReferenceStatus->isSetReasonDescription()){
						$order_history['comments'].="".$orderReferenceStatus->getReasonDescription();
					}
					$order_history['customer_notified']=0;

				}
				$order_history['amazonState']=$amazonState;
				$modelOrder=VmModel::getModel('orders');
				$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id,$order_history,TRUE);


				return$amazonState;
		*/
	}


	public function getStoreInternalData () {
		$amazonInternalData = newstdClass();
		if ($this->amazonData->isSetGetOrderReferenceDetailsResult()) {
			$getOrderReferenceDetailsResult = $this->amazonData->getGetOrderReferenceDetailsResult();
			if ($getOrderReferenceDetailsResult->isSetOrderReferenceDetails()) {
				$orderReferenceDetails = $getOrderReferenceDetailsResult->getOrderReferenceDetails();
				if ($orderReferenceDetails->isSetOrderReferenceStatus()) {
					$orderReferenceStatus = $orderReferenceDetails->getOrderReferenceStatus();
					if ($orderReferenceStatus->isSetState()) {
						$amazonInternalData->amazon_response_state = $orderReferenceStatus->getState();
					}
					if ($orderReferenceStatus->isSetReasonCode()) {
						$amazonInternalData->amazon_response_reasonCode = $orderReferenceStatus->getReasonCode();
					}
					if ($orderReferenceStatus->isSetReasonDescription()) {
						$amazonInternalData->amazon_response_reasonDescription = $orderReferenceStatus->getReasonDescription();
					}
					if ($orderReferenceDetails->isSetAmazonOrderReferenceId()) {
						$amazonInternalData->amazon_response_amazonOrderReferenceId = $orderReferenceDetails->getAmazonOrderReferenceId();
					}
				}

			}
			return $amazonInternalData;
		}
	}


	function getContents () {

		$contents = $this->tableStart("GetOrderReferenceDetailsResponse");
		if ($this->amazonData->isSetGetOrderReferenceDetailsResult()) {
			$getOrderReferenceDetailsResult = $this->amazonData->getGetOrderReferenceDetailsResult();

			if ($getOrderReferenceDetailsResult->isSetOrderReferenceDetails()) {
				$contents .= $this->getRowFirstCol("GetOrderReferenceDetailsResult");
				$orderReferenceDetails = $getOrderReferenceDetailsResult->getOrderReferenceDetails();

				$orderReferenceDetails = $orderReferenceDetails->getOrderReferenceDetails();
				if ($orderReferenceDetails->isSetAmazonOrderReferenceId()) {
					$contents .= $this->getRow("AmazonOrderReferenceId:", $orderReferenceDetails->getAmazonOrderReferenceId());

				}
				if ($orderReferenceDetails->isSetOrderReferenceReferenceId()) {
					$contents .= $this->getRow("OrderReferenceReferenceId:", $orderReferenceDetails->getOrderReferenceReferenceId());
				}

				if ($orderReferenceDetails->isSetBuyer()) {
					$more = '';
					$buyer = $orderReferenceDetails->getBuyer();
					if ($buyer->isSetName()) {
						$more .= "Name:" . $buyer->getName() . "<br/>";
					}
					if ($buyer->isSetEmail()) {
						$more .= "Email:" . $buyer->getEmail() . "<br/>";
					}
					if ($buyer->isSetPhone()) {
						$more .= "Phone:" . $buyer->getPhone() . "<br/>";
					}
					$contents .= $this->getRow("Buyer:", $more);

				}
				if ($orderReferenceDetails->isSetOrderTotal()) {
					$more = "";
					$orderTotal = $orderReferenceDetails->getOrderTotal();
					if ($orderTotal->isSetCurrencyCode()) {
						$more .= "CurrencyCode:" . $orderTotal->getCurrencyCode() . "<br/>";
					}
					if ($orderTotal->isSetAmount()) {
						$more .= "Amount:" . $orderTotal->getAmount() . "<br/>";
					}
					$contents .= $this->getRow("OrderTotal:", $more);
				}
				if ($orderReferenceDetails->isSetSellerNote()) {
					$contents .= $this->getRow("SellerNote:", $orderReferenceDetails->getSellerNote());

				}
				if ($orderReferenceDetails->isSetDestination()) {
					$more = '';
					$destination = $orderReferenceDetails->getDestination();
					if ($destination->isSetDestinationType()) {
						$more .= "DestinationType:" . $destination->getDestinationType() . "<br/>";
					}
					if ($destination->isSetPhysicalDestination()) {
						$contents .= "PhysicalDestination" . "<br/>";
						$physicalDestination = $destination->getPhysicalDestination();
						if ($physicalDestination->isSetName()) {
							$more .= "Name:" . $physicalDestination->getName() . "<br/>";
						}
						if ($physicalDestination->isSetAddressLine1()) {
							$more .= "AddressLine1:" . $physicalDestination->getAddressLine1() . "<br/>";
						}
						if ($physicalDestination->isSetAddressLine2()) {
							$more .= "AddressLine2:" . $physicalDestination->getAddressLine2() . "<br/>";
						}
						if ($physicalDestination->isSetAddressLine3()) {
							$more .= "AddressLine3:" . $physicalDestination->getAddressLine3() . "<br/>";
						}
						if ($physicalDestination->isSetCity()) {
							$more .= "City:" . $physicalDestination->getCity() . "<br/>";
						}
						if ($physicalDestination->isSetCounty()) {
							$more .= "County:" . $physicalDestination->getCounty() . "<br/>";
						}
						if ($physicalDestination->isSetDistrict()) {
							$more .= "District:" . $physicalDestination->getDistrict() . "<br/>";
						}
						if ($physicalDestination->isSetStateOrRegion()) {
							$more .= "StateOrRegion:" . $physicalDestination->getStateOrRegion() . "<br/>";
						}
						if ($physicalDestination->isSetPostalCode()) {
							$more .= "PostalCode:" . $physicalDestination->getPostalCode() . "<br/>";
						}
						if ($physicalDestination->isSetCountryCode()) {
							$more .= "CountryCode:" . $physicalDestination->getCountryCode() . "<br/>";
						}
						if ($physicalDestination->isSetPhone()) {
							$more .= "Phone:" . $physicalDestination->getPhone() . "<br/>";
						}
						$contents .= $this->getRow("PhysicalDestination:", $more);

					}
				}
				if ($orderReferenceDetails->isSetReleaseEnvironment()) {
					$contents .= $this->getRow("ReleaseEnvironment:", $orderReferenceDetails->getReleaseEnvironment());
				}


				if ($orderReferenceDetails->isSetIdList()) {
					$more = '';
					$idList = $orderReferenceDetails->getIdList();
					$memberList = $idList->getmember();
					foreach ($memberList as $member) {
						$more .= "<br/>member:" . $member;
					}
					$contents .= $this->getRow("IdList:", $more);

				}
				if ($orderReferenceDetails->isSetSellerOrderAttributes()) {
					$more = '';
					$sellerOrderAttributes = $orderReferenceDetails->getSellerOrderAttributes();
					if ($sellerOrderAttributes->isSetSellerOrderId()) {
						$more .= "SellerOrderId:" . $sellerOrderAttributes->getSellerOrderId() . "<br/>";
					}
					if ($sellerOrderAttributes->isSetStoreName()) {
						$more .= "StoreName:" . $sellerOrderAttributes->getStoreName() . "<br/>";
					}
					if ($sellerOrderAttributes->isSetOrderItemCategories()) {
						$more .= "OrderItemCategories" . "<br/>";
						$orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
						$orderItemCategoryList = $orderItemCategories->getOrderItemCategory();
						foreach ($orderItemCategoryList as $orderItemCategory) {
							$more .= "OrderItemCategory:" . $orderItemCategory;
						}
					}
					if ($sellerOrderAttributes->isSetCustomInformation()) {
						$more .= "CustomInformation:" . $sellerOrderAttributes->getCustomInformation() . "<br/>";
					}
					$contents .= $this->getRow("SellerOrderAttributes:", $more);

				}
				if ($orderReferenceDetails->isSetConstraints()) {
					$more = '';
					$constraints = $orderReferenceDetails->getConstraints();
					$constraintList = $constraints->getConstraint();
					foreach ($constraintList as $constraint) {
						$more .= "Constraint" . "<br/>";
						if ($constraint->isSetConstraintID()) {
							$more .= "ConstraintID:" . $constraint->getConstraintID() . "<br/>";
						}
						if ($constraint->isSetDescription()) {
							$more .= "Description:" . $constraint->getDescription() . "<br/>";
						}
					}
					$contents .= $this->getRow("Constraints:", $more);

				}

				if ($orderReferenceDetails->isSetCreationTimestamp()) {
					$contents .= $this->getRow("CreationTimestamp:", $orderReferenceDetails->getCreationTimestamp());
				}
				if ($orderReferenceDetails->isSetExpirationTimestamp()) {
					$contents .= $this->getRow("ExpirationTimestamp:", $orderReferenceDetails->getExpirationTimestamp());

				}
				if ($orderReferenceDetails->isSetOrderReferenceStatus()) {
					$more = '';
					$orderReferenceStatus = $orderReferenceDetails->getOrderReferenceStatus();
					if ($orderReferenceStatus->isSetState()) {
						$more .= "<br/>State:" . $orderReferenceStatus->getState();
					}
					if ($orderReferenceStatus->isSetLastUpdateTimestamp()) {
						$more .= "<br/>LastUpdateTimestamp:" . $orderReferenceStatus->getLastUpdateTimestamp();
					}
					if ($orderReferenceStatus->isSetReasonCode()) {
						$more .= "<br/>ReasonCode:" . $orderReferenceStatus->getReasonCode();
					}
					if ($orderReferenceStatus->isSetReasonDescription()) {
						$more .= "<br/>ReasonDescription:" . $orderReferenceStatus->getReasonDescription();
					}
					$contents .= $this->getRow("OrderReferenceStatus:", $more);

				}
				if ($orderReferenceDetails->isSetParentDetails()) {
					$more = '';
					$parentDetails = $orderReferenceDetails->getParentDetails();
					if ($parentDetails->isSetId()) {
						$more .= "Id:" . $parentDetails->getId() . "<br/>";
					}
					if ($parentDetails->isSetType()) {
						$more .= "Type:" . $parentDetails->getType() . "<br/>";
					}
					$contents .= $this->getRow("ParentDetails:", $more);

				}
			}
		}
		/*
				if ($this->amazonData->isSetResponseMetadata()) {
					$contents .= $this->getRowFirstCol("ResponseMetadata");
					$responseMetadata = $this->amazonData->getResponseMetadata();
					if ($responseMetadata->isSetRequestId()) {
						$contents .= $this->getRow("RequestId:", $responseMetadata->getRequestId());
					}
				}
				$contents .= $this->getRowFirstCol("ResponseHeaderMetadata" . $this->amazonData->getResponseHeaderMetadata());
		*/
		$contents .= $this->tableEnd();

		return $contents;
	}


}