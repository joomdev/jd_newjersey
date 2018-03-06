<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage   ratings
* @author
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: ratings.php 2233 2010-01-21 21:21:29Z SimonHodgkiss $
*/

// @todo a link or tooltip to show the details of shop user who posted comment
// @todo more flexible templating, theming, etc..

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
AdminUIHelper::startAdminArea($this);
/* Get the component name */
$option = vRequest::getCmd('option');
?>
<form action="index.php?option=com_virtuemart&view=ratings" method="post" name="adminForm" id="adminForm">
<div id="header">
	<div id="filterbox">
	<table>
	  <tr>
		 <td align="left" width="100%">
			<?php echo vmText::_('COM_VIRTUEMART_FILTER'); ?>:
			<input type="text" name="filter_ratings" value="<?php echo vRequest::getVar('filter_ratings', ''); ?>" />
			<button class="btn btn-small" onclick="this.form.submit();"><?php echo vmText::_('COM_VIRTUEMART_GO'); ?></button>
			<button class="btn btn-small" onclick="document.adminForm.filter_ratings.value='';"><?php echo vmText::_('COM_VIRTUEMART_RESET'); ?></button>
			<?php if($this->showVendors()){
				echo Shopfunctions::renderVendorList(vmAccess::getVendorId());
			} ?>
		 </td>
	  </tr>
	</table>
	</div>
	<div id="resultscounter" ><?php echo $this->pagination->getResultsCounter();?></div>
</div>

<div style="text-align: left;">
	<table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
	<thead>
	<tr>
		<th class="admin-checkbox"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>
		<th width="40%"><?php echo $this->sort('created_on', 'COM_VIRTUEMART_DATE') ; ?></th>
		<th width="40%"><?php echo $this->sort('product_name') ; ?></th>
		<th width="10%"><?php echo $this->sort('rating', 'COM_VIRTUEMART_RATE_NOM') ; ?></th>
		<th width="20"><?php echo $this->sort('published') ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	if (count($this->ratingslist) > 0) {
		$i = 0;
		$k = 0;
		$keyword = vRequest::getCmd('keyword');
		foreach ($this->ratingslist as $key => $review) {
			$checked = JHtml::_('grid.id', $i , $review->virtuemart_rating_id);
			$published = $this->gridPublished( $review, $i );

			?>
			<tr class="row<?php echo $k ; ?>">
				<!-- Checkbox -->
				<td class="admin-checkbox"><?php echo $checked; ?></td>
				<!-- Username + time -->
				<?php $link = 'index.php?option='.$option.'&view=ratings&task=listreviews&virtuemart_product_id='.$review->virtuemart_product_id; ?>
				<td><?php echo JHtml::_('link', $link,vmJsApi::date($review->created_on,'LC2',true) , array("title" => vmText::_('COM_VIRTUEMART_RATING_EDIT_TITLE'))); ?></td>
				<!-- Product name -->
				<?php $link = 'index.php?option='.$option.'&view=product&task=edit&virtuemart_product_id='.$review->virtuemart_product_id ; ?>
				<td><?php echo JHtml::_('link', JRoute::_($link), $review->product_name, array('title' => vmText::_('COM_VIRTUEMART_EDIT').' '.htmlentities($review->product_name))); ?></td>
				<!-- Stars rating -->
				<td align="center">
					
					<?php // Rating Stars output
					$maxrating = VmConfig::get('vm_maximum_rating_scale', 5);
				    $ratingwidth = round($review->rating) * 24;
				    ?>
	
				    <span title="<?php echo (vmText::_("COM_VIRTUEMART_RATING_TITLE").' '. round($review->rating) . '/' . $maxrating) ?>" class="ratingbox" style="display:inline-block;">
						<span class="stars-orange" style="width:<?php echo $ratingwidth.'px'; ?>">
						</span>
				    </span>

				</td>
				<!-- published -->
				<td><?php echo $published; ?></td>
			</tr>
		<?php
			$k = 1 - $k;
			$i++;
		}
	}
	?>
	</tbody>
	<tfoot>
		<tr>
		<td colspan="16">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
		</tr>
	</tfoot>
	</table>
</div>
<!-- Hidden Fields -->
	<?php echo $this->addStandardHiddenToForm(); ?>
</form>
<?php AdminUIHelper::endAdminArea(); ?>

