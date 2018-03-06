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
class amazonHelperOrderReferenceNotification extends amazonHelper {

	public function __construct (OffAmazonPaymentsNotifications_Model_OrderReferenceNotification $orderNotification, $method) {
		parent::__construct($orderNotification, $method);
	}

	/**
	 *
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
		if (!$this->amazonData->isSetCaptureDetails()) {
			return NULL;
		}
		$details = $this->amazonData->getCaptureDetails();
		if (!$details->isSetCaptureStatus()) {
			return NULL;
		}
		$status = $details->getCaptureStatus();
		if (!$status->isSetState()) {
			return NULL;
		}
		$amazonState = $status->getState();

		if (!$status->isSetReasonCode()) {
			return NULL;
		}
		$reasonCode = $status->getReasonCode();
		// default value
		$order_history['customer_notified'] = 0;
		if ($amazonState != 'Open') {
			$order_history['order_status'] = $this->_currentMethod->status_cancel;
		} else {
			$order_history['order_status'] = $this->_currentMethod->status_authorization;
		}

		$order_history['comments'] = $reasonCode;

		$order_history['amazonState'] = $amazonState;
		$orderModel = VmModel::getModel('orders');
		$orderModel->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, false);
	}

	/**
	 *     *
	 * if moves to Open, then  allowed operations are: getOrderReferenceDetails, Cancel, CloseOrder, authorize
	 * if moves to Suspended, then  allowed operations are: getOrderReferenceDetails, Cancel, CloseOrder
	 * if moves to Canceled, then  allowed operations are: getOrderReferenceDetails
	 * if moves to Closed, then  allowed operations are: getOrderReferenceDetails
	 *
	 * @param $order
	 * @param $payments
	 * @param $amazonState
	 * @return bool|string
	 */
	public function onNotificationNextOperation ($order, $payments, $amazonState) {
		if ($amazonState == 'Open') {
			return 'onNotificationGetAuthorization';
		}
		return false;
	}

	public function getStoreInternalData () {
		//$amazonInternalData = $this->getStoreResultParams();
		$amazonInternalData = new stdClass();
		if ($this->amazonData->isSetOrderReference()) {
			$details = $this->amazonData->getOrderReference();
			if ($details->isSetAmazonOrderReferenceId()) {
				$amazonInternalData->amazon_response_amazonReferenceId = $details->getAmazonOrderReferenceId();
			}
			if ($details->isSetOrderReferenceStatus()) {
				$status = $details->getOrderReferenceStatus();
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


	function getReferenceId () {
		if (!$this->amazonData->isSetOrderReference()) {
			return NULL;
		}
		$orderReference = $this->amazonData->getOrderReference();
		if (!$orderReference->isSetSellerOrderAttributes()) {
			return NULL;
		}
		$sellerOrderAttributes = $orderReference->getSellerOrderAttributes();
		return $sellerOrderAttributes->getSellerOrderId();
	}

	function getAmazonReferenceId () {
		if (!$this->amazonData->isSetOrderReference()) {
			return NULL;
		}
		$orderReference = $this->amazonData->getOrderReference();
		if (!$orderReference->isSetAmazonOrderReferenceId()) {
			return NULL;
		}
		return $orderReference->getAmazonOrderReferenceId();
	}

	function getContents () {
		$contents = $this->tableStart("OrderReference Notification");
		if ($this->amazonData->isSetOrderReference()) {
			$contents .= $this->getRowFirstCol("OrderReference");
			$orderReference = $this->amazonData->getOrderReference();
			if ($orderReference->isSetAmazonOrderReferenceId()) {
				$contents .= $this->getRow("AmazonOrderReferenceId: ", $orderReference->getAmazonOrderReferenceId());
			}

			if ($orderReference->isSetOrderTotal()) {
				$more = '';
				$orderTotal = $orderReference->getOrderTotal();
				if ($orderTotal->isSetAmount()) {
					$more .= "<br />Amount: " . $orderTotal->getAmount();
				}
				if ($orderTotal->isSetCurrencyCode()) {
					$more .= "<br />CurrencyCode: " . $orderTotal->getCurrencyCode();
				}
				$contents .= $this->getRow("OrderTotal: ", $more);

			}

			if ($orderReference->isSetSellerOrderAttributes()) {
				$more = '';
				$sellerOrderAttributes = $orderReference->getSellerOrderAttributes();
				if ($sellerOrderAttributes->isSetSellerId()) {
					$more .= "<br />SellerId: " . $sellerOrderAttributes->getSellerId();
				}
				if ($sellerOrderAttributes->isSetSellerOrderId()) {
					$more .= "<br />SellerOrderId: " . $sellerOrderAttributes->getSellerOrderId();
				}
				if ($sellerOrderAttributes->isSetOrderItemCategories()) {
					$more .= "<br /> OrderItemCategories";
					$orderItemCategories = $sellerOrderAttributes->getOrderItemCategories();
					$orderItemCategoryList = $orderItemCategories->getOrderItemCategory();
					foreach ($orderItemCategoryList as $orderItemCategory) {
						$more .= "<br />  OrderItemCategory: " . $orderItemCategory;
					}
				}
				$contents .= $this->getRow("IdList: ", $more);
			}

			if ($orderReference->isSetOrderReferenceStatus()) {
				$more = '';
				$orderReferenceStatus = $orderReference->getOrderReferenceStatus();
				if ($orderReferenceStatus->isSetState()) {
					$more .= "<br /> State";
					$more .= $orderReferenceStatus->getState();
				}
				if ($orderReferenceStatus->isSetLastUpdateTimestamp()) {
					$more .= "<br /> LastUpdateTimestamp: " . $orderReferenceStatus->getLastUpdateTimestamp();
				}
				if ($orderReferenceStatus->isSetReasonCode()) {
					$more .= "<br /> ReasonCode: " . $orderReferenceStatus->getReasonCode();
				}
				if ($orderReferenceStatus->isSetReasonDescription()) {
					$more .= "<br /> ReasonDescription: " . $orderReferenceStatus->getReasonDescription();
				}
				$contents .= $this->getRow("CaptureStatus: ", $more);
			}
			if ($orderReference->isSetCreationTimestamp()) {
				$contents .= $this->getRow("CreationTimestamp: ", $orderReference->getSoftDescriptor());
			}
			if ($orderReference->isSetExpirationTimestamp()) {
				$contents .= $this->getRow("ExpirationTimestamp: ", $orderReference->getExpirationTimestamp());
			}
		}
		$contents .= $this->tableEnd();
		return $contents;
	}


}