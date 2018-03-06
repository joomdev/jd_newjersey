<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Manufacturer
* @author Patrick Kohl
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

<form action="index.php?option=com_virtuemart&view=manufacturer" method="post" name="adminForm" id="adminForm">
<div id="header">
<div id="filterbox">
	<table class="">
		<tr>
			<td align="left">
			<?php echo $this->displayDefaultViewSearch() ?>
			</td>

		</tr>
	</table>
	</div>
	<div id="resultscounter"><?php echo $this->pagination->getResultsCounter(); ?></div>

</div>
    <div id="editcell">
	    <table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
	    <thead>
		<tr>
		    <th class="admin-checkbox">
			<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
		    </th>
		    <th width="25%">
				<?php echo $this->sort('mf_name', 'COM_VIRTUEMART_MANUFACTURER_NAME') ; ?>
		    </th>
		    <th width="20%">
				<?php echo $this->sort('mf_email', 'COM_VIRTUEMART_MANUFACTURER_EMAIL') ; ?>
		    </th>
		    <th width="30%">
				<?php echo $this->sort('mf_desc', 'COM_VIRTUEMART_MANUFACTURER_DESCRIPTION'); ?>
		    </th>
		    <th width="15%">
				<?php echo $this->sort('mf_category_name', 'COM_VIRTUEMART_MANUFACTURER_CATEGORY'); ?>
		    </th>
		    <th width="15%">
				<?php echo $this->sort('mf_url', 'COM_VIRTUEMART_MANUFACTURER_URL'); ?>
		    </th>
		    <th width="20px">
				<?php echo vmText::_('COM_VIRTUEMART_PUBLISHED'); ?>
		    </th>
		      <th><?php echo $this->sort('m.virtuemart_manufacturer_id', 'COM_VIRTUEMART_ID')  ?></th>
		</tr>
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count( $this->manufacturers ); $i < $n; $i++) {
		$row = $this->manufacturers[$i];

		$checked = JHtml::_('grid.id', $i, $row->virtuemart_manufacturer_id,null,'virtuemart_manufacturer_id');
		$published = $this->gridPublished( $row, $i );
		$editlink = JROUTE::_('index.php?option=com_virtuemart&view=manufacturer&task=edit&virtuemart_manufacturer_id=' . $row->virtuemart_manufacturer_id);
		?>
	    <tr class="row<?php echo $k ; ?>">
		<td class="admin-checkbox">
			<?php echo $checked; ?>
		</td>
		<td align="left">
			<?php
			if(empty($row->mf_name)){
				$row->mf_name = vmText::sprintf('COM_VM_TRANSLATION_MISSING','virtuemart_manufacturer_id',$row->virtuemart_manufacturer_id);
			}
			?>
		    <a href="<?php echo $editlink; ?>"><?php echo $row->mf_name; ?></a>

		</td>
		<td align="left">
			<?php if (!empty($row->mf_email)) echo  '<a href="mailto:'.$row->mf_name.'<'.$row->mf_email.'>">'.$row->mf_email ; ?>
		</td>
		<td>
			<?php if (!empty($row->mf_desc)) echo $row->mf_desc; ?>
		</td>
		<td>
			<?php if (!empty($row->mf_category_name)) echo $row->mf_category_name; ?>
		</td>
		<td>
			<?php if (!empty($row->mf_url)) echo '<a href="'. $row->mf_url.'">'. $row->mf_url ; ?>
		</td>
		<td align="center">
			<?php echo $published; ?>
		</td>
		<td align="right">
		    <?php echo $row->virtuemart_manufacturer_id; ?>
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