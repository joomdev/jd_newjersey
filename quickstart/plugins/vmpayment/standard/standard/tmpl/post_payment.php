<?php
defined ('_JEXEC') or die();

/**
 * @author Valérie Isaksen
 * @version $Id$
 * @package VirtueMart
 * @subpackage payment
 * @copyright Copyright (C) 2004-Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.   - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

?>
<div class="post_payment_payment_name" style="width: 100%">
	<span class="post_payment_payment_name_title"><?php echo vmText::_ ('VMPAYMENT_STANDARD_PAYMENT_INFO'); ?> </span>
	<?php echo  $viewData["payment_name"]; ?>
</div>

<div class="post_payment_order_number" style="width: 100%">
	<span class="post_payment_order_number_title"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_NUMBER'); ?> </span>
	<?php echo  $viewData["order_number"]; ?>
</div>

<div class="post_payment_order_total" style="width: 100%">
	<span class="post_payment_order_total_title"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_TOTAL'); ?> </span>
	<?php echo  $viewData['displayTotalInPaymentCurrency']; ?>
</div>
<?php
$tracking = VmConfig::get('ordertracking','guests');
if($tracking !='none' and !($tracking =='registered' and empty($viewData["virtuemart_user_id"]) )){

$orderlink = 'index.php?option=com_virtuemart&view=orders&layout=details&order_number='.$viewData["order_number"];
if( $tracking == 'guestlink' or ( $tracking == 'guests' and empty($viewData["virtuemart_user_id"]))){
	$orderlink .= '&order_pass='.$viewData["order_pass"];
}
?>
<a class="vm-button-correct" href="<?php echo JRoute::_($orderlink, false)?>"><?php echo vmText::_('COM_VIRTUEMART_ORDER_VIEW_ORDER'); ?></a>
<?php
}
?>






