<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: authorizerequest.php 9413 2017-01-04 17:20:58Z Milbo $
 * @author Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - November 21 2017 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class amazonHelperAuthorizeRequest extends amazonHelper {

	public function __construct (OffAmazonPaymentsService_Model_AuthorizeRequest $authorizationRequest, $method) {
		parent::__construct($authorizationRequest, $method);
	}



	function getContents () {

		$contents = $this->tableStart("AuthorizeRequest");
		$contents .= $this->getRow("Dump: ", var_export($this->amazonData, true));

		$contents .= $this->tableEnd();

		return $contents;
	}


}