<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Currency
* @author RickG
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

<form action="index.php?option=com_virtuemart&view=currency" method="post" name="adminForm" id="adminForm">
    <table>
	<tr>
	    <td width="100%">
			<?php echo $this->displayDefaultViewSearch ('COM_VIRTUEMART_CURRENCY','search') ; ?>
	    </td>
	</tr>
    </table>
    <div id="editcell">
	    <table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
	    <thead>
		<tr>
		    <th class="admin-checkbox">
			<input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" />
		    </th>
		    <th >
				<?php  echo $this->sort('currency_name','COM_VIRTUEMART_CURRENCY') ; ?>
		    </th>
		    <th width="80">
			<?php echo $this->sort('currency_exchange_rate') ?>
		    </th>
		    <th width="20">
			<?php echo vmText::_('COM_VIRTUEMART_CURRENCY_SYMBOL'); ?>
		    </th>
<?php /*		    <th width="10">
			<?php echo vmText::_('COM_VIRTUEMART_CURRENCY_LIST_CODE_2'); ?>
		    </th> */?>
		    <th width="20">
			<?php  echo $this->sort('currency_code_3') ?>
		    </th>
             <th width="20">
			<?php echo vmText::_('COM_VIRTUEMART_CURRENCY_NUMERIC_CODE'); ?>
		    </th>
<?php /*		    <th >
				<?php echo vmText::_('COM_VIRTUEMART_CURRENCY_START_DATE'); ?>
			</th>
			<th >
				<?php echo vmText::_('COM_VIRTUEMART_CURRENCY_END_DATE'); ?>
			</th> */?>
			<th width="10">
				<?php echo vmText::_('COM_VIRTUEMART_PUBLISHED'); ?>
			</th>
		<?php /*	<th width="10">
				<?php echo vmText::_('COM_VIRTUEMART_SHARED'); ?>
			</th> */ ?>
		</tr>
	    </thead>
	    <?php
	    $k = 0;
	    for ($i=0, $n=count( $this->currencies ); $i < $n; $i++) {
		$row = $this->currencies[$i];

		$checked = JHtml::_('grid.id', $i, $row->virtuemart_currency_id);
			$published = $this->gridPublished( $row, $i );

			$editlink = JROUTE::_('index.php?option=com_virtuemart&view=currency&task=edit&cid[]=' . $row->virtuemart_currency_id);
		?>
	    <tr class="row<?php echo $k ; ?>">
		<td class="admin-checkbox">
			<?php echo $checked; ?>
		</td>
		<td align="left">
		    <a href="<?php echo $editlink; ?>"><?php echo $row->currency_name; ?></a>
		</td>
		<td align="left">
			<?php echo $row->currency_exchange_rate; ?>
		</td>
		<td align="left">
			<?php echo $row->currency_symbol; ?>
		</td>
<?php /*<td align="left">
			<?php echo $row->currency_code_2; ?>
		</td>  */ ?>
		<td align="left">
			<?php echo $row->currency_code_3; ?>
		</td>
        <td align="left">
			<?php echo $row->currency_numeric_code; ?>
		</td>
		<td align="center">
			<?php echo $published; ?>
		</td>
		<?php /*
		<td align="center">
			<?php echo $row->shared; ?>
		</td>	*/?>
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

    <input type="hidden" name="option" value="com_virtuemart" />
    <input type="hidden" name="controller" value="currency" />
    <input type="hidden" name="view" value="currency" />
    <input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->lists['filter_order']; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->lists['filter_order_Dir']; ?>" />
    <?php echo JHtml::_( 'form.token' ); ?>
</form>



<?php AdminUIHelper::endAdminArea(); ?>