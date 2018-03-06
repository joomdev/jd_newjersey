<?php
/**
 *
 * Amazon payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id: response.php 8703 2015-02-15 17:11:16Z alatak $
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
if ($viewData['include_amazon_css']) {
	$doc = JFactory::getDocument()->addStyleSheet(JURI::root(true) . '/plugins/vmpayment/amazon/assets/css/amazon.css');
}
$success = $viewData["success"];
$order = $viewData["order"];

?>
<div id="amazonResponse">

	<h3> <?php echo vmText::_('VMPAYMENT_AMAZON_THANK_YOU'); ?></h3>


	<div class="amazonResponseOrderId">
		<label><?php echo vmText::_('VMPAYMENT_AMAZON_ORDER_ID'); ?> </label>
		 <?php echo $viewData["amazonOrderId"]; ?>

	</div>
	<?php if ($success) { ?>
		<div class="amazonResponseItem">
			<a class="vm-button-correct" href="<?php echo JRoute::_('index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $viewData["order"]['details']['BT']->order_number . '&order_pass=' . $viewData["order"]['details']['BT']->order_pass, false) ?>"><?php echo vmText::_('COM_VIRTUEMART_ORDER_VIEW_ORDER'); ?></a>
		</div>
	<?php } ?>
</div>