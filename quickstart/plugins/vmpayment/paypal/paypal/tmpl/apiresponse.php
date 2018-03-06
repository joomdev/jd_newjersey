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

$payment_name = $viewData['payment_name'];
$responseData = $viewData['responseData'];

?>

<style>
	.paypal_ordersummary td {padding:10px;}
</style>
<div class="paypal_ordersummary">
<table cellpadding="2" class="paypal_ordersummary">
	<?php 
	echo $this->getHtmlRow('VMPAYMENT_PAYPAL_API_PAYMENT_NAME', $payment_name);
	echo $this->getHtmlRow('COM_VIRTUEMART_ORDER_NUMBER', $viewData["order"]['details']['BT']->order_number);
	echo $this->getHtmlRow('VMPAYMENT_PAYPAL_API_AMOUNT', $responseData['AMT'] . ' ' . $responseData['CURRENCYCODE']);

	if ($viewData['success']) {
			echo $this->getHtmlRow('VMPAYMENT_PAYPAL_API_TRANSACTION_ID', $responseData['TRANSACTIONID']);

	} else {
		for ($i = 0; isset($responseData["L_ERRORCODE".$i]); $i++) {
			echo $this->getHtmlRow('VMPAYMENT_PAYPAL_API_ERROR_CODE', $responseData["L_ERRORCODE".$i]);
			$message = isset($responseData["L_LONGMESSAGE".$i]) ? $responseData["L_LONGMESSAGE".$i]: $responseData["L_SHORTMESSAGE".$i];
			echo $this->getHtmlRow('VMPAYMENT_PAYPAL_API_ERROR_DESC', $message);
		}
	}
	?>
</table>
</div>
<div class="paypal_orderlink">
	<a class="vm-button-correct" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=orders&layout=details&order_number='.$viewData["order"]['details']['BT']->order_number.'&order_pass='.$viewData["order"]['details']['BT']->order_pass, false)?>"><?php echo vmText::_('COM_VIRTUEMART_ORDER_VIEW_ORDER'); ?></a>
</div>