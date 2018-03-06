<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Paymentmethod
* @author Max Milbers
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
			<th >
				<?php echo $this->sort('l.payment_name', 'COM_VIRTUEMART_PAYMENT_LIST_NAME'); ?>
			</th>
			 <th>
				<?php echo vmText::_('COM_VIRTUEMART_PAYMENT_LIST_DESCRIPTION_LBL'); ?>
			</th>
			<?php if($this->showVendors()){ ?>
			<th >
				<?php echo $this->sort('i.virtuemart_vendor_id', 'COM_VIRTUEMART_VENDOR');  ?>
			</th><?php }?>

			<th  >
				<?php echo vmText::_('COM_VIRTUEMART_PAYMENT_SHOPPERGROUPS'); ?>
			</th>
			<th >
				<?php echo $this->sort('i.payment_element', 'COM_VIRTUEMART_PAYMENT_ELEMENT'); ?>
			</th>
			<th  >
				<?php echo $this->sort('i.ordering', 'COM_VIRTUEMART_LIST_ORDER'); ?>
			</th>
			<th >
				<?php echo $this->sort('i.published', 'COM_VIRTUEMART_PUBLISHED'); ?>
			</th>
			<?php if($this->showVendors){ ?>
			<th width="10">
				<?php echo vmText::_('COM_VIRTUEMART_SHARED'); ?>
			</th>
			<?php } ?>
			 <th><?php echo $this->sort('i.virtuemart_paymentmethod_id', 'COM_VIRTUEMART_ID')  ?></th>
		</tr>
		</thead>
		<?php
		$k = 0;

		for ($i=0, $n=count( $this->payments ); $i < $n; $i++) {

			$row = $this->payments[$i];
			$checked = JHtml::_('grid.id', $i, $row->virtuemart_paymentmethod_id);
			$published = $this->gridPublished( $row, $i );
			if($this->showVendors){
				$shared = $this->toggle($row->shared, $i, 'toggle.shared');
			}
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=paymentmethod&task=edit&cid[]=' . $row->virtuemart_paymentmethod_id);
			if(empty($row->payment_name)){
				$row->payment_name = vmText::sprintf('COM_VM_TRANSLATION_MISSING','virtuemart_paymentmethod_id',$row->virtuemart_paymentmethod_id);
			}
			?>
			<tr class="<?php echo "row".$k; ?>">

				<td class="admin-checkbox">
					<?php echo $checked; ?>
				</td>
				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->payment_name; ?></a>
				</td>
				 <td align="left">
					<?php echo $row->payment_desc; ?>
				</td>
				<?php if($this->showVendors()){?>
				<td align="left">
					<?php echo vmText::_($row->virtuemart_vendor_id); ?>
				</td>
				<?php } ?>

				<td>
					<?php echo $row->paymShoppersList; ?>
				</td>
				<td>
					<?php echo $row->payment_element; ?>
				</td>
				<td>
					<?php echo $row->ordering; ?>
				</td>
				<td align="center">
					<?php echo $published; ?>
				</td>
				<?php if($this->showVendors){ ?>
				<td align="center">
					<?php echo $shared; ?>
				</td>
				<?php } ?>
				<td align="center">
					<?php echo $row->virtuemart_paymentmethod_id; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="21">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

	<?php echo $this->addStandardHiddenToForm(); ?>
</form>


<?php AdminUIHelper::endAdminArea(); ?>