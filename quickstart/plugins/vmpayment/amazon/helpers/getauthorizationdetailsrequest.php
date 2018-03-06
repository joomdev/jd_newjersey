<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: authorizeresponse.php 8585 2014-11-25 11:11:13Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperGetAuthorizationDetailsRequest extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_GetAuthorizationDetailsRequest $getAuthorizationDetailsRequest, $method) {
		parent::__construct($getAuthorizationDetailsRequest, $method);
	}



	function getContents () {

		$contents = $this->tableStart("GetAuthorizationDetailsRequest");
		$contents .= $this->getRow("Dump: ", var_export($this->amazonData, true));

		$contents .= $this->tableEnd();

		return $contents;
	}


}