<?php
/**
*
* Orderlist
* NOTE: This is a copy of the edit_orderlist template from the user-view (which in turn is a slighly
*       modified copy from the backend)
*
* @package	VirtueMart
* @subpackage Orders
* @author Oscar van Eijk, Andrew Hutson
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2016 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: list.php 9653 2017-10-18 12:59:33Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$ajaxUpdate = '';
if(VmConfig::get ('ajax_order', TRUE)){
	$ajaxUpdate = 'data-dynamic-update="1"';
}

?>
<div class="vm-wrap">
	<div class="vm-orders-list">
<h1><?php echo vmText::_('COM_VIRTUEMART_ORDERS_VIEW_DEFAULT_TITLE'); ?></h1>
<?php
if (count($this->orderlist) == 0) {

	echo shopFunctionsF::getLoginForm(false,$this->trackingByOrderPass);
} else { ?>
<div id="editcell">
	<table class="adminlist" width="80%">
	<thead>
	<tr>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_ORDER_LIST_ORDER_NUMBER'); ?>
		</th>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_ORDER_LIST_CDATE'); ?>
		</th>
		<!--th>
			<?php //echo vmText::_('COM_VIRTUEMART_ORDER_LIST_MDATE'); ?>
		</th -->
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_ORDER_LIST_STATUS'); ?>
		</th>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_ORDER_LIST_TOTAL'); ?>
		</th>
	</tr>
	</thead>
	<?php
		$k = 0;
		foreach ($this->orderlist as $row) {
			$editlink = JRoute::_('index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $row->order_number, FALSE);
			?>
			<tr class="<?php echo "row$k"; ?>">
				<td align="left">
					<a href="<?php echo $editlink; ?>" rel="nofollow" <?php echo $ajaxUpdate?> ><?php echo $row->order_number; ?></a>
					<?php echo shopFunctionsF::getInvoiceDownloadButton($row) ?>
				</td>
				<td align="left">
					<?php echo vmJsApi::date($row->created_on,'LC4',true); ?>
				</td>
				<!--td align="left">
					<?php //echo vmJsApi::date($row->modified_on,'LC3',true); ?>
				</td -->
				<td align="left">
					<?php echo shopFunctionsF::getOrderStatusName($row->order_status); ?>
				</td>
				<td align="left">
					<?php echo $this->currency->priceDisplay($row->order_total, $row->currency); ?>
				</td>
			</tr>
	<?php
			$k = 1 - $k;
		}
	?>
	</table>
</div>
<?php } ?>
	</div>
	<div class="vm-orders-information"></div>
</div>
<?php
if(VmConfig::get ('ajax_order', TRUE)){
$j = "Virtuemart.containerSelector = '.vm-orders-information';
Virtuemart.container = jQuery(Virtuemart.containerSelector);";

vmJsApi::addJScript('ajax_order',$j);
vmJsApi::jDynUpdate();
}
?>
