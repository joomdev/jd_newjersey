<?php

defined('_JEXEC') or die('Direct Access to ' . basename(__FILE__) . 'is not allowed.');

/**
 *
 * @package    VirtueMart
 * @subpackage vmpayment
 * @version $Id: billingagreementnotification.php 8316 2014-09-22 15:24:16Z alatak $
 * @author Valérie Isaksen
 * @link http://www.virtuemart.net
 * @copyright Copyright (c) 2004 - November 10 2015 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 *
 */
class billingAgreementNotification extends amazonHelperNotification {
	public function __construct (OffAmazonPaymentsNotifications_Model_billingAgreement $billingAgreementNotification, $method) {
		parent::__construct($billingAgreementNotification, $method);
	}


}