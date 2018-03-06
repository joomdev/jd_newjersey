<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage ShopperGroup
* @author Markus �hler
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

<form action="index.php?option=com_virtuemart&view=shoppergroup" method="post" name="adminForm" id="adminForm">
<?php if ($this->task=='massxref_sgrps' or $this->task=='massxref_sgrps_exe') : ?>
<div id="header">
<div id="massxref_task">
	<table class="">
		<tr>
			<td align="left">
				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK') ?>
			</td>
			<td>
				<?php
				$options = array(
				'replace' => vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK_REPLACE'),
				'add' => vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK_ADD'),
				'remove' => vmText::_('COM_VIRTUEMART_PRODUCT_XREF_TASK_REMOVE')
				);
				echo VmHTML::selectList('massxref_task', 'replace', $options);
				?>
			</td>
		</tr>
	</table>
</div>
</div>
<?php endif; ?>
  <div id="editcell">
	  <table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
		<thead>
		  <tr>
			<th class="admin-checkbox">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>
			<th width="40%">
				<?php echo vmText::_('COM_VIRTUEMART_SHOPPERGROUP_NAME'); ?>
			</th>
			<th width="60%">
				<?php echo vmText::_('COM_VIRTUEMART_SHOPPERGROUP_DESCRIPTION'); ?>
			</th>
			<th width="40">
				<?php echo vmText::_('COM_VIRTUEMART_DEFAULT'); ?>
			</th>
			<th width="30px" >
				<?php echo vmText::_('COM_VIRTUEMART_PUBLISHED'); ?>
			</th>
			<?php if((Vmconfig::get('multix','none')!='none') && $this->showVendors){ ?>
			<th>
				<?php echo vmText::_('COM_VIRTUEMART_VENDOR'); ?>
			</th>
			<?php } ?>
			<th width="30px" >
				<?php echo vmText::_('COM_VIRTUEMART_ADDITIONAL'); ?>
			</th>
			<th>
				<?php echo $this->sort('virtuemart_shoppergroup_id', 'COM_VIRTUEMART_ID')  ?>
			</th>
		  </tr>
		</thead><?php

		$k = 0;
		for ($i = 0, $n = count( $this->shoppergroups ); $i < $n; $i++) {
			$row = $this->shoppergroups[$i];
			$published = $this->gridPublished( $row, $i );

			$checked = '';
			if ($row->default == 0) {
				$checked = JHtml::_('grid.id', $i, $row->virtuemart_shoppergroup_id,null,'virtuemart_shoppergroup_id');
			}

			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=shoppergroup&task=edit&virtuemart_shoppergroup_id[]=' . $row->virtuemart_shoppergroup_id);

			?>

		  <tr class="row<?php echo $k ; ?>">
			<td class="admin-checkbox">
				<?php echo $checked; ?>
			</td>
			<td align="left">
			  <a href="<?php echo $editlink; ?>"><?php echo vmText::_($row->shopper_group_name); ?></a>
			</td>
			<td align="left">
				<?php echo vmText::_($row->shopper_group_desc); ?>
			</td>
			<td align="center">
				<?php
				if ($row->default != 0) {
					echo JHtml::_('image','menu/icon-16-default.png', vmText::_('COM_VIRTUEMART_SHOPPERGROUP_DEFAULT'), NULL, true);
				}
				?>
			</td>
			<td align="center">
				<?php echo $published; ?>
			</td>
			<?php if((Vmconfig::get('multix','none')!='none') && $this->showVendors){ ?>
			<td align="left">
				<?php echo $row->virtuemart_vendor_id; ?>
			</td>
			<?php } ?>
			<td align="center">
				<?php 
				if ($row->sgrp_additional == 1) {
					echo JHtml::_('image','menu/icon-16-apply.png', vmText::_('COM_VIRTUEMART_SHOPPERGROUP_ADDITIONAL'), NULL, true);
				}
				?>
			</td>
			<td align="left">
				<?php echo $row->virtuemart_shoppergroup_id; ?>
			</td>
		  </tr><?php
			$k = 1 - $k;
		} ?>
		<tfoot>
		  <tr>
			<td colspan="10">
				<?php echo $this->sgrppagination->getListFooter(); ?>
			</td>
		  </tr>
		</tfoot>
	  </table>
  </div>

	<?php echo $this->addStandardHiddenToForm($this->_name,$this->task); ?>
</form><?php
AdminUIHelper::endAdminArea(); ?>