<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: authorizeresponse.php 8259 2014-08-31 13:43:36Z alatak $
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */
class amazonHelperGetCaptureDetailsRequest extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_GetCaptureDetailsRequest $getCaptureDetailsRequest, $method) {
		parent::__construct($getCaptureDetailsRequest, $method);
	}




	function getContents () {

		$contents = $this->tableStart("GetCaptureDetailsRequest ");
		$contents .= $this->getRow("Dump: ", var_export($this->amazonData, true));
		$contents .= $this->tableEnd();

		return $contents;
	}


}