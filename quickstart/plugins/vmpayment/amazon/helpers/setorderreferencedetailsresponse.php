<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package VirtueMart
 * @subpackage vmpayment
 * @version $Id: setorderreferencedetailsresponse.php 8325 2014-09-24 17:30:43Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperSetOrderReferenceDetailsResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_SetOrderReferenceDetailsResponse $setOrderReferenceDetailsResponse, $method) {
		parent::__construct($setOrderReferenceDetailsResponse, $method);
	}

	public function getStoreInternalData () {
		$response = $this->amazonData;
		$amazonInternalData = new stdClass();
		if ($response->isSetSetOrderReferenceDetailsResult()) {

			$setOrderReferenceDetailsResult = $response->getSetOrderReferenceDetailsResult();
			if ($setOrderReferenceDetailsResult->isSetOrderReferenceDetails()) {

				$orderReferenceDetails = $setOrderReferenceDetailsResult->getOrderReferenceDetails();
				if ($orderReferenceDetails->isSetAmazonOrderReferenceId()) {
					$amazonInternalData->amazon_response_amazonAuthorizationId = $orderReferenceDetails->getAmazonOrderReferenceId();
				}


				if ($orderReferenceDetails->isSetOrderReferenceStatus()) {

					$orderReferenceStatus = $orderReferenceDetails->getOrderReferenceStatus();
					if ($orderReferenceStatus->isSetState()) {
						$amazonInternalData->amazon_response_reasonCode = $orderReferenceStatus->getReasonCode();
					}

					if ($orderReferenceStatus->isSetReasonCode()) {
						$amazonInternalData->amazon_response_reasonCode = $orderReferenceStatus->getReasonCode();


					}
					if ($orderReferenceStatus->isSetReasonDescription()) {
						$amazonInternalData->amazon_response_reasonDescription = $orderReferenceStatus->getReasonDescription();

					}
				}

			}
		}

		return $amazonInternalData;
	}

	/**
	 * @return string
	 */
	function getContents () {

		$contents = "";
		$contents .= "Service Response" . "<br />";
		$contents .= "=============================" . "<br />";

		$contents .= "SetOrderReferenceDetailsResponse" . "<br />";
		if ($this->amazonData->isSetSetOrderReferenceDetailsResult()) {
			$contents .= "SetOrderReferenceDetailsResult" . "<br />";
			$setOrderReferenceDetailsResult = $this->amazonData->getSetOrderReferenceDetailsResult();
			if ($setOrderReferenceDetailsResult->isSetOrderReferenceDetails()) {
				$contents .= " OrderReferenceDetails" . "<br />";
				$orderReferenceDetails = $setOrderReferenceDetailsResult->getOrderReferenceDetails();
				if ($orderReferenceDetails->isSetAmazonOrderReferenceId()) {
					$contents .= "AmazonOrderReferenceId: " . $orderReferenceDetails->getAmazonOrderReferenceId() . "<br />";
				}
				if ($orderReferenceDetails->isSetBuyer()) {
					$contents .= "Buyer" . "<br />";
					$buyer = $orderReferenceDetails->getBuyer();
					if ($buyer->isSetName()) {
						$contents .= "Name: " . $buyer->getName() . "<br />";
					}
					if ($buyer->isSetEmail()) {
						$contents .= "Email: " . $buyer->getEmail() . "<br />";
					}
					if ($buyer->isSetPhone()) {
						$contents .= "Phone: " . $buyer->getPhone() . "<br />";
					}
				}
				if ($orderReferenceDetails->isSetOrderTotal()) {
					$contents .= "OrderTotal" . "<br />";
					$orderTotal = $orderReferenceDetails->getOrderTotal();
					if ($orderTotal->isSetCurrencyCode()) {
						$contents .= "CurrencyCode: " . $orderTotal->getCurrencyCode() . "<br />";
					}
					if ($orderTotal->isSetAmount()) {
						$contents .= "Amount: " . $orderTotal->getAmount() . "<br />";
					}
				}
				if ($orderReferenceDetails->isSetSellerNote()) {
					$contents .= "SellerNote: " . $orderReferenceDetails->getSellerNote() . "<br />";
				}
				if ($orderReferenceDetails->isSetDestination()) {
					$contents .= "Destination" . "<br />";
					$destination = $orderReferenceDetails->getDestination();
					if ($destination->isSetDestinationType()) {
						$contents .= "DestinationType: " . $destination->getDestinationType() . "<br />";
					}
					if ($destination->isSetPhysicalDestination()) {
						$contents .= "PhysicalDestination" . "<br />";
						$physicalDestination = $destination->getPhysicalDestination();
						if ($physicalDestination->isSetName()) {
							$contents .= " Name: " . $physicalDestination->getName() . "<br />";
						}
						if ($physicalDestination->isSetAddressLine1()) {
							$contents .= " AddressLine1: " . $physicalDestination->getAddressLine1() . "<br />";
						}
						if ($physicalDestination->isSetAddressLine2()) {
							$contents .= " AddressLine2: " . $physicalDestination->getAddressLine2() . "<br />";
						}
						if ($physicalDestination->isSetAddressLine3()) {
							$contents .= " AddressLine3: " . $physicalDestination->getAddressLine3() . "<br />";
						}
						if ($physicalDestination->isSetCity()) {
							$contents .= " City: " . $physicalDestination->getCity() . "<br />";
						}
						if ($physicalDestination->isSetCounty()) {
							$contents .= " County: " . $physicalDestination->getCounty() . "<br />";
						}
						if ($physicalDestination->isSetDistrict()) {
							$contents .= " District: " . $physicalDestination->getDistrict() . "<br />";
						}
						if ($physicalDestination->isSetStateOrRegion()) {
							$contents .= " StateOrRegion: " . $physicalDestination->getStateOrRegion() . "<br />";
						}
						if ($physicalDestination->isSetPostalCode()) {
							$contents .= " PostalCode: " . $physicalDestination->getPostalCode() . "<br />";
						}
						if ($physicalDestination->isSetCountryCode()) {
							$contents .= " CountryCode: " . $physicalDestination->getCountryCode() . "<br />";
						}
						if ($physicalDestination->isSetPhone()) {
							$contents .= " Phone: " . $physicalDestination->getPhone() . "<br />";
						}
					}
				}
				if ($orderReferenceDetails->isSetReleaseEnvironment()) {
					$contents .= "ReleaseEnvironment" . "<br />";
					$contents .= "" . $orderReferenceDetails->getReleaseEnvironment() . "<br />";
				}
				if ($orderReferenceDetails->isSetIdList()) {
					$contents .= "IdList" . "<br />";
					$idList = $orderReferenceDetails->getIdList();
					$memberList = $idList->getmember();
					foreach ($memberList as $member) {
						$contents .= "member: " . $member . "<br />";;
					}
				}
				if ($orderReferenceDetails->isSetSellerOrderAttributes()) {
					$contents .= "SellerOrderAttributes" . "<br />";
					$sellerOrderAttributes = $orderReferenceDetails->getSellerOrderAttributes();
					if ($sellerOrderAttributes->isSetSellerOrderId()) {
						$contents .= "SellerOrderId: " . $sellerOrderAttributes->getSellerOrderId() . "<br />";
					}
					if ($sellerOrderAttributes->isSetStoreName()) {
						$contents .= "StoreName: " . $sellerOrderAttributes->getStoreName() . "<br />";
					}
					if ($sellerOrderAttributes->isSetOrderItemCategories()) {
						$contents .= "OrderItemCategories" . "<br />";
						$orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
						$orderItemCategoryList = $orderItemCategories->getOrderItemCategory();
						foreach ($orderItemCategoryList as $orderItemCategory) {
							$contents .= " OrderItemCategory: " . $orderItemCategory;
						}
					}
					if ($sellerOrderAttributes->isSetCustomInformation()) {
						$contents .= "CustomInformation: " . $sellerOrderAttributes->getCustomInformation() . "<br />";
					}
				}
				if ($orderReferenceDetails->isSetOrderReferenceStatus()) {
					$contents .= "OrderReferenceStatus" . "<br />";
					$orderReferenceStatus = $orderReferenceDetails->getOrderReferenceStatus();
					if ($orderReferenceStatus->isSetState()) {
						$contents .= "State: " . $orderReferenceStatus->getState() . "<br />";
					}
					if ($orderReferenceStatus->isSetLastUpdateTimestamp()) {
						$contents .= "LastUpdateTimestamp: " . $orderReferenceStatus->getLastUpdateTimestamp() . "<br />";
					}
					if ($orderReferenceStatus->isSetReasonCode()) {
						$contents .= "ReasonCode: " . $orderReferenceStatus->getReasonCode() . "<br />";
					}
					if ($orderReferenceStatus->isSetReasonDescription()) {
						$contents .= "ReasonDescription: " . $orderReferenceStatus->getReasonDescription() . "<br />";
					}
				}
				if ($orderReferenceDetails->isSetConstraints()) {
					$contents .= "Constraints" . "<br />";
					$constraints = $orderReferenceDetails->getConstraints();
					$constraintList = $constraints->getConstraint();
					foreach ($constraintList as $constraint) {
						$contents .= "Constraint" . "<br />";
						if ($constraint->isSetConstraintID()) {
							$contents .= " ConstraintID: " . $constraint->getConstraintID() . "<br />";
						}
						if ($constraint->isSetDescription()) {
							$contents .= " Description: " . $constraint->getDescription() . "<br />";
						}
					}
				}
				if ($orderReferenceDetails->isSetCreationTimestamp()) {
					$contents .= "CreationTimestamp: " . $orderReferenceDetails->getCreationTimestamp() . "<br />";
				}
				if ($orderReferenceDetails->isSetExpirationTimestamp()) {
					$contents .= "ExpirationTimestamp: " . $orderReferenceDetails->getExpirationTimestamp() . "<br />";
				}
				if ($orderReferenceDetails->isSetParentDetails()) {
					$contents .= "ParentDetails" . "<br />";
					$parentDetails = $orderReferenceDetails->getParentDetails();
					if ($parentDetails->isSetId()) {
						$contents .= "Id: " . $parentDetails->getId() . "<br />";
					}
					if ($parentDetails->isSetType()) {
						$contents .= "Type: " . $parentDetails->getType() . "<br />";
					}
				}
			}
		}
		/*
		if ($this->amazonData->isSetResponseMetadata()) {
			$contents .= "ResponseMetadata" . "<br />";
			$responseMetadata = $this->amazonData->getResponseMetadata();
			if ($responseMetadata->isSetRequestId()) {
				$contents .= " RequestId: " . $responseMetadata->getRequestId() . "<br />";
			}
		}

		$contents .= "ResponseHeaderMetadata: " . $this->amazonData->getResponseHeaderMetadata() . "<br />";
		*/
		$contents .= $this->tableEnd();
		return $contents;
	}

}