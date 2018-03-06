<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id$
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperCloseOrderReferenceRequest extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_CloseOrderReferenceRequest $closeOrderReferenceRequest, $method) {
		parent::__construct($closeOrderReferenceRequest, $method);
	}



	function getContents () {

		$contents = $this->tableStart("closeOrderReferenceRequest");
		$contents .= $this->getRow("Dump: ", var_export($this->amazonData, true));
		$contents .= $this->tableEnd();

		return $contents;
	}


}