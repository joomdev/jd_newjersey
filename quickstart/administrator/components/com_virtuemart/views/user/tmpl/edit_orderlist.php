<?php
/**
*
* User details, Orderlist
*
* @package	VirtueMart
* @subpackage User
* @author Oscar van Eijk
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit_orderlist.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

?>

<div id="editcell">
	<table class="adminlist table" cellspacing="0" cellpadding="0">
	<thead>
	<tr>

		<th>
			<?php echo vmText::_('COM_VIRTUEMART_ORDER_LIST_NUMBER'); ?>
		</th>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_PRINT_VIEW'); ?>
		</th>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_ORDER_CDATE'); ?>
		</th>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_ORDER_LIST_MDATE'); ?>
		</th>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_STATUS'); ?>
		</th>
		<th>
			<?php echo vmText::_('COM_VIRTUEMART_TOTAL'); ?>
		</th>
	</thead>
	<?php
		$k = 0;
		$n = 1;
		foreach ($this->orderlist as $i => $row) {
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=orders&task=edit&virtuemart_order_id=' . $row->virtuemart_order_id);
			?>
			<tr class="row<?php echo $k ; ?>">
				 
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->order_number; ?></a>
				</td>
				<td align="center">
					<?php // echo $print_link; ?>
				</td>
				<td align="left">
					<?php echo vmJsApi::date($row->created_on,'LC2',true); ?>
				</td>
				<td align="left">
					<?php echo vmJsApi::date($row->modified_on,'LC2',true); ?>
				</td>
				<td align="left">
					<?php echo shopFunctionsF::getOrderStatusName($row->order_status); ?>
				</td>
				<td align="left">
					<?php echo $this->currency->priceDisplay($row->order_total); ?>
				</td>
			</tr>
	<?php
			$k = 1 - $k;
		}
	?>
	</table>
</div>
