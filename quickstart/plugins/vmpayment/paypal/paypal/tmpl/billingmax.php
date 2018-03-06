<?php
/**
 *
 * Paypal payment plugin
 *
 * @author Jeremy Magne
 * @version $Id: paypal.php 7217 2013-09-18 13:42:54Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
defined('_JEXEC') or die();

$method = $viewData["method"];
$customerData = $viewData['customerData'];
$pmid = $method->virtuemart_paymentmethod_id;

?>
<div id="paymentMethodOptions_<?php echo $pmid; ?>" class="paymentMethodOptions" style="display:none;">
    <br />
    <label for="autobilling_max_amount_<?php echo $pmid; ?>"><?php echo vmText::_('VMPAYMENT_PAYPAL_PAYMENT_BILLING_MAX_AMOUNT'); ?>:</label>
    <input type="text" class="inputbox" id="autobilling_max_amount_<?php echo $pmid; ?>" name="autobilling_max_amount_<?php echo $pmid; ?>" maxlength="4" size="5" value="<?php echo $customerData->getVar('autobilling_max_amount'); ?>" autocomplete="off" />
</div>