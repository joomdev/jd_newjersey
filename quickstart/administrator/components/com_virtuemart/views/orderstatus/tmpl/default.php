<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage OrderStatus
* @author Oscar van Eijk
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<div id="editcell">
		<table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th class="admin-checkbox">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>

			<th>
			<?php echo $this->sort('order_status_name') ?>
			</th>
			<th>
			<?php echo $this->sort('order_status_code') ?>
			</th>
			<th>
				<?php echo vmText::_('COM_VIRTUEMART_ORDER_STATUS_STOCK_HANDLE'); ?>
			</th>
			<th>
				<?php echo vmText::_('COM_VIRTUEMART_ORDER_STATUS_COLOR'); ?>
			</th>
			<th>
				<?php echo vmText::_('COM_VIRTUEMART_DESCRIPTION'); ?>
			</th>
			<th>
			<?php  echo $this->sort('ordering')  ?>
			<?php echo JHtml::_('grid.order',  $this->orderStatusList ); ?>
			</th>
			<th width="20">
				<?php echo vmText::_('COM_VIRTUEMART_PUBLISHED'); ?>
			</th>
			<th><?php echo $this->sort('virtuemart_orderstate_id', 'COM_VIRTUEMART_ID')  ?></th>
		</tr>
		</thead>
		<?php
		$k = 0;

		for ($i = 0, $n = count($this->orderStatusList); $i < $n; $i++) {
			$row = $this->orderStatusList[$i];
			$published = $this->gridPublished( $row, $i );

			$checked = JHtml::_('grid.id', $i, $row->virtuemart_orderstate_id);

			$coreStatus = (in_array($row->order_status_code, $this->lists['vmCoreStatusCode']));
			$image = 'admin/checked_out.png';
			$image = JHtml::_('image', $image, vmText::_('COM_VIRTUEMART_ORDER_STATUS_CODE_CORE'),'',true);
			$checked = ($coreStatus) ?
				'<span class="hasTip" title="'. vmText::_('COM_VIRTUEMART_ORDER_STATUS_CODE_CORE').'">'. $image .'</span>' :
				JHtml::_('grid.id', $i, $row->virtuemart_orderstate_id);

			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=orderstatus&task=edit&cid[]=' . $row->virtuemart_orderstate_id);
			$deletelink	= JROUTE::_('index.php?option=com_virtuemart&view=orderstatus&task=remove&cid[]=' . $row->virtuemart_orderstate_id);
			$ordering = $row->ordering ;
		?>
			<tr class="row<?php echo $k ; ?>">
				<td class="admin-checkbox">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<?php
					$lang =JFactory::getLanguage();
					if ($lang->hasKey($row->order_status_name)) {
						echo '<a href="' . $editlink . '">'. vmText::_($row->order_status_name) .'</a> ('.$row->order_status_name.')';
					} else {
						echo '<a href="' . $editlink . '">'. $row->order_status_name .'</a> ';
					}
					?>
				</td>
				<td align="left">
					<?php echo $row->order_status_code; ?>
				</td>
				<td align="left">
					<?php echo  vmText::_($this->stockHandelList[$row->order_stock_handle]); ?>
				</td>
				<?php
				$colorStyle = '';
				if ($row->order_status_color) {
					$colorStyle = 'style="background-color:' . $row->order_status_color.'"';
				}
				?>
				<td align="left" <?php echo $colorStyle ?>>
					<?php echo vmText::_($row->order_status_color); ?>
				</td>
				<td align="left">
					<?php echo vmText::_($row->order_status_description); ?>
				</td>
				<td align="center" class="order">
					<span><?php echo $this->pagination->vmOrderUpIcon($i, $row->ordering, 'orderUp', vmText::_('COM_VIRTUEMART_MOVE_UP')); ?></span>
					<span><?php echo $this->pagination->vmOrderDownIcon( $i, $row->ordering,$n, true, 'orderDown', vmText::_('COM_VIRTUEMART_MOVE_DOWN')); ?></span>
					<input class="ordering" type="text" name="order[<?php echo $i?>]" id="order[<?php echo $i?>]" size="5" value="<?php echo $row->ordering; ?>" style="text-align: center" />
				</td>
				<td align="center"><?php echo $published; ?></td>
				<td width="10">
					<?php echo $row->virtuemart_orderstate_id; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
	</table>
</div>

	<?php echo $this->addStandardHiddenToForm(); ?>
</form>

<?php AdminUIHelper::endAdminArea(); ?>
