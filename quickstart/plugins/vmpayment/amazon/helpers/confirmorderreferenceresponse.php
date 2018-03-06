<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: confirmorderreferenceresponse.php 8585 2014-11-25 11:11:13Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperConfirmOrderReferenceResponse extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_ConfirmOrderReferenceResponse $confirmOrderReferenceResponse, $method) {
		parent::__construct($confirmOrderReferenceResponse, $method);
	}

	function getStoreInternalData () {
		$amazonInternalDatas = new stdClass();
		if ($this->amazonData->isSetResponseMetadata()) {
			$responseMetadata = $this->amazonData->getResponseMetadata();
			if ($responseMetadata->isSetRequestId()) {

				$amazonInternalDatas->amazon_response_amazonRequestId = $responseMetadata->getRequestId();
			}
		}
		return $amazonInternalDatas;
	}

	/**
	 * Only send an email if the ERP is enabled, and authorization is done by ERP
	 * IN all other cases, there will be an authorization after OrderConfirmed, that will send an email
	 * @param $order
	 */
	function onResponseUpdateOrderHistory ($order) {
		$order_history['order_status'] = $this->_currentMethod->status_orderconfirmed;

			$order_history['customer_notified'] = $this->getCustomerNotified();

		$order_history['comments'] = vmText::_('VMPAYMENT_AMAZON_COMMENT_STATUS_ORDERCONFIRMED');
		$modelOrder = VmModel::getModel('orders');
		$modelOrder->updateStatusForOneOrder($order['details']['BT']->virtuemart_order_id, $order_history, false);
	}


	function getContents () {
		$contents = $this->tableStart("ConfirmOrderReferenceResponse");
		$contents .= $this->getRow("ResponseHeaderMetadata: ", $this->amazonData->getResponseHeaderMetadata());
		if ($this->amazonData->isSetResponseMetadata()) {
			$more = '';
			$responseMetadata = $this->amazonData->getResponseMetadata();
			if ($responseMetadata->isSetRequestId()) {
				$more .= "<br />RequestId: " . $responseMetadata->getRequestId();
			}
			$contents .= $this->getRow("ResponseMetadata: ", $more);
		}

		$contents .= $this->tableEnd();
		return $contents;
	}

}