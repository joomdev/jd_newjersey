<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: orderreference.php 8316 2014-09-22 15:24:16Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperOrderReference extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_AuthorizeResponse $orderReference, $method) {
		parent::__construct($orderReference, $method);
	}

	public function getStoreInternalData () {
		$authorizationDetails = $this->amazonData->getAuthorizationDetails();
		$amazonInternalData = $this->getStoreResultParams();
		if ($authorizationDetails->isSetAuthorizationStatus()) {

			$authorizationStatus = $authorizationDetails->getAuthorizationStatus();
			if ($authorizationStatus->isSetState()) {
				$amazonInternalData->amazon_state = $authorizationStatus->getState();
			}
			if ($authorizationStatus->isSetReasonCode()) {
				$amazonInternalData->amazon_reasonCode = $authorizationStatus->getReasonCode();
			}
			if ($authorizationStatus->isSetReasonDescription()) {
				$amazonInternalData->amazon_reasonDescription = $authorizationStatus->getReasonDescription();
			}
		}
		return $amazonInternalData;
	}

	function getContents () {
	}
}