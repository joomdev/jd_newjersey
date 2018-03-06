<?php
/**
*
* User listing view
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
* @version $Id: default.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

AdminUIHelper::startAdminArea($this);

?>
<form action="<?php echo JRoute::_( 'index.php?option=com_virtuemart&view=user' );?>" method="post" name="adminForm" id="adminForm">
	<div id="header">
	<div id="filterbox">
		<table>
			<tr>
				<td width="100%">
					<?php echo vmText::_('COM_VIRTUEMART_FILTER'); ?>:
					<input type="text" name="search" id="search" value="<?php echo $this->lists['search'];?>" class="text_area" onchange="document.adminForm.submit();" />
					<?php
					$selected = vRequest::getString('searchTable','juser');
					$searchOptionTables = array(
						'0' => array('searchTable' => 'juser', 'searchTable_name' => vmText::_('COM_VIRTUEMART_ONLY_JUSER')),
						'1' => array('searchTable' => 'all', 'searchTable_name' => vmText::_('JALL'))
					);
					echo JHtml::_('Select.genericlist', $searchOptionTables, 'searchTable', '', 'searchTable', 'searchTable_name', $selected );
					?>
					<button class="btn btn-small" onclick="this.form.submit();"><?php echo vmText::_('COM_VIRTUEMART_GO'); ?></button>
					<button class="btn btn-small" onclick="document.adminForm.search.value='';this.form.submit();"><?php echo vmText::_('COM_VIRTUEMART_RESET'); ?></button>
				</td>
			</tr>
		</table>
	</div>
	<div id="resultscounter"><?php echo $this->pagination->getResultsCounter();?></div>
	</div>
	<div id="editcell">
		<table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
		<thead>
		<tr>
			<th class="admin-checkbox">
				<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
			</th>

			<th width="25%"><?php echo $this->sort('ju.username', 'COM_VIRTUEMART_USERNAME')  ?></th>
			<th width="25%"><?php echo $this->sort('ju.name', 'COM_VIRTUEMART_USER_DISPLAYED_NAME')  ?></th>
			<th width="25%"><?php echo $this->sort('ju.email', 'COM_VIRTUEMART_EMAIL'); ?></th>
<?php	/*	<th><?php echo vmText::_('COM_VIRTUEMART_USER_GROUP'); ?></th> 	*/ ?>
			<th width="25%"><?php echo $this->sort('shopper_group_name', 'COM_VIRTUEMART_SHOPPERGROUP')  ?></th>
			<?php if(Vmconfig::get('multix','none')!=='none'){ ?>
			<th width="80px"><?php echo vmText::_('COM_VIRTUEMART_USER_IS_VENDOR'); ?></th>
			<?php } ?>
			<th><?php echo $this->sort('ju.id', 'COM_VIRTUEMART_ID') ?></th>
		</tr>
		</thead>
		<?php
		$k = 0;
		for ($i = 0, $n = count($this->userList); $i < $n; $i++) {
			$row = $this->userList[$i];
			$checked = JHtml::_('grid.id', $i, $row->id);
			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=user&task=edit&virtuemart_user_id[]=' . $row->id);
			$is_vendor = $this->toggle($row->is_vendor, $i, 'toggle.user_is_vendor');
		?>
			<tr class="row<?php echo $k ; ?>">
				<td class="admin-checkbox">
					<?php echo $checked; ?>
				</td>

				<td align="left">
					<a href="<?php echo $editlink; ?>"><?php echo $row->username; ?></a>
				</td>
				<td align="left">
					<?php echo $row->name; ?>
				</td>
				<td align="left">
					<?php echo $row->email; ?>
				</td>
				<td align="left">
					<?php
					if(empty($row->shopper_group_name)) $row->shopper_group_name = $this->defaultShopperGroup;
					echo vmText::_($row->shopper_group_name);
					?>
				</td>
				<?php if(Vmconfig::get('multix','none')!=='none'){ ?>
				<td align="center">
					<?php echo $is_vendor; ?>
				</td>
				<?php } ?>
				<td align="right">
					<?php echo $row->id; ?>
				</td>
			</tr>
			<?php
			$k = 1 - $k;
		}
		?>
		<tfoot>
			<tr>
				<td colspan="11">
					<?php echo $this->pagination->getListFooter(); ?>
				</td>
			</tr>
		</tfoot>
	</table>
</div>

	<?php echo $this->addStandardHiddenToForm(); ?>
</form>

<?php AdminUIHelper::endAdminArea(); ?>
