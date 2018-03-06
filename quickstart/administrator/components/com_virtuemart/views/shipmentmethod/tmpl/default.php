<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Shipment
* @author RickG
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: default.php 9585 2017-06-22 13:08:16Z Milbo $
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
				<?php echo $this->sort('l.shipment_name', 'COM_VIRTUEMART_SHIPMENT_NAME_LBL'); ?>
			</th>
                        <th>
				<?php echo vmText::_('COM_VIRTUEMART_SHIPMENT_LIST_DESCRIPTION_LBL'); ?>
			</th>
                        <th width="20">
				<?php echo vmText::_('COM_VIRTUEMART_SHIPPING_SHOPPERGROUPS'); ?>
			</th>
                        <th>
				<?php echo $this->sort('i.shipment_element', 'COM_VIRTUEMART_SHIPMENTMETHOD'); ?>
			</th>
			<th>
				<?php echo $this->sort('i.ordering', 'COM_VIRTUEMART_LIST_ORDER'); ?>
			</th>
			<th width="20"><?php echo $this->sort('i.published', 'COM_VIRTUEMART_PUBLISHED'); ?></th>
			<?php if($this->showVendors()){ ?>
				<th width="20">
				<?php echo vmText::_( 'COM_VIRTUEMART_SHARED')  ?>
				</th><?php }  ?>
			 <th><?php echo $this->sort('i.virtuemart_shipmentmethod_id', 'COM_VIRTUEMART_ID')  ?></th>
		</tr>
		</thead>
		<?php
		$k = 0;
		$set_automatic_shipment = VmConfig::get('set_automatic_shipment',false);
		for ($i=0, $n=count( $this->shipments ); $i < $n; $i++) {
			$row = $this->shipments[$i];
			$published = $this->gridPublished($row, $i);
			//$row->published = 1;
			$checked = JHtml::_('grid.id', $i, $row->virtuemart_shipmentmethod_id);
			if ($this->showVendors) {
				$shared = $this->toggle($row->shared, $i, 'toggle.shared');
			}
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=shipmentmethod&task=edit&cid[]='.$row->virtuemart_shipmentmethod_id);
			if(empty($row->shipment_name)){
				$row->shipment_name = vmText::sprintf('COM_VM_TRANSLATION_MISSING','virtuemart_shipment_id',$row->virtuemart_shipmentmethod_id);
			}
	?>
			<tr class="row<?php echo $k; ?>">
				<td class="admin-checkbox">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<?php echo JHtml::_('link', $editlink, vmText::_($row->shipment_name)); ?>
					<?php if ($set_automatic_shipment == $row->virtuemart_shipmentmethod_id) {
						?><i class="icon-featured"></i><?php
					}
					?>
				</td>
                                <td align="left">
					<?php echo $row->shipment_desc; ?>
				</td>
                                <td>
					<?php echo $row->shipmentShoppersList; ?>
				</td>
                                <td align="left">
					<?php echo $row->shipment_element; //JHtml::_('link', $editlink, vmText::_($row->shipment_element)); ?>
				</td>
				<td align="left">
					<?php echo vmText::_($row->ordering); ?>
				</td>
				<td><?php echo $published; ?></td>
				<?php
				if($this->showVendors) {
				?><td align="center">
				<?php echo $shared; ?>
				</td>
				<?php }?>
				<td align="center">
					<?php echo $row->virtuemart_shipmentmethod_id; ?>
				</td>


			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="10">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

	<?php echo $this->addStandardHiddenToForm(); ?>
</form>



<?php AdminUIHelper::endAdminArea(); ?>