<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage
* @author
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id$
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
AdminUIHelper::startAdminArea($this);

/* Load some variables */


// OSP in view.html.php $virtuemart_category_id = vRequest::getInt('virtuemart_category_id', false);
if ($product_parent_id=vRequest::getInt('product_parent_id', false))   $col_product_name='COM_VIRTUEMART_PRODUCT_CHILDREN_LIST'; else $col_product_name='COM_VIRTUEMART_PRODUCT_NAME';

?>
<form action="index.php?option=com_virtuemart&view=product" method="post" name="adminForm" id="adminForm">
<div id="header">
<span id="filterbox">
	<span>
			<?php echo vmText::_('COM_VIRTUEMART_FILTER') ?>:
				<select class="inputbox" id="virtuemart_category_id" name="virtuemart_category_id" onchange="this.form.submit(); return false;">
					<option value=""><?php echo vmText::sprintf( 'COM_VIRTUEMART_SELECT' ,  vmText::_('COM_VIRTUEMART_CATEGORY')) ; ?></option>
				</select>
					 <?php echo JHtml::_('select.genericlist', $this->manufacturers, 'virtuemart_manufacturer_id', 'class="inputbox" onchange="document.adminForm.submit(); return false;"', 'value', 'text',
					 	$this->model->virtuemart_manufacturer_id );
					?>

				<?php echo vmText::_('COM_VIRTUEMART_PRODUCT_LIST_SEARCH_BY_DATE') ?>&nbsp;
					<input type="text" value="<?php echo $this->filter_product ?>" name="filter_product" size="25" />
				<?php
					echo $this->lists['search_type'];
					echo $this->lists['search_order'];
					echo vmJsApi::jDate($this->search_date, 'search_date');
					echo $this->lists['vendors'];
				?>
				<button  class="btn btn-small" onclick="this.form.submit();"><?php echo vmText::_('COM_VIRTUEMART_GO'); ?></button>
				<button  class="btn btn-small" onclick="document.adminForm.filter_product.value=''; document.adminForm.search_type.options[0].selected = true;"><?php echo vmText::_('COM_VIRTUEMART_RESET'); ?></button>

				<?php echo $this->pagination->getLimitBox(); ?>
	</span>
	<div id="resultscounter"><?php echo $this->pagination->getResultsCounter(); ?></div>

</div>

<div style="text-align: left;">
<?php
// $this->productlist
$mediaLimit = (int)VmConfig::get('mediaLimit',20);
$totalList = count($this->productlist);
if($this->pagination->limit<=$mediaLimit or $totalList<=$mediaLimit){
	$imgWidth = 90;
} else {
	$imgWidth = 30;
}

?>
	<table class="adminlist table table-striped" cellspacing="0" cellpadding="0">
	<thead>
	<tr>
		<th class="admin-checkbox"><input type="checkbox" name="toggle" value="" onclick="Joomla.checkAll(this)" /></th>

		<th width="20%"><?php echo $this->sort('product_name',$col_product_name) ?> </th>
		<?php if (!$product_parent_id ) { ?>
                <th width="10%"><?php echo $this->sort('product_parent_id','COM_VIRTUEMART_PRODUCT_CHILDREN_OF'); ?></th>
                <?php } ?>
                <th width="80px" ><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_PARENT_LIST_CHILDREN'); ?></th>
                <th style="min-width:<?php echo $imgWidth ?>px;width:5%;"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_MEDIA'); ?></th>
		<th><?php echo $this->sort('product_sku') ?></th>
		<th width="90px" ><?php echo $this->sort('product_price', 'COM_VIRTUEMART_PRODUCT_PRICE_TITLE') ; ?></th>
<?php /*		<th><?php echo JHtml::_('grid.sort', 'COM_VIRTUEMART_CATEGORY', 'c.category_name', $this->lists['filter_order_Dir'], $this->lists['filter_order'] ); ?></th> */ ?>
<th width="15%"><?php echo vmText::_( 'COM_VIRTUEMART_CATEGORY'); ?></th>
		<!-- Only show reordering fields when a category ID is selected! -->
		<?php
		$num_rows = 0;
		if( $this->categoryId ) { ?>
			<th style="min-width:100px;width:5%;">
				<?php echo $this->sort('pc.ordering', 'COM_VIRTUEMART_FIELDMANAGER_REORDER'); ?>
				<?php echo JHtml::_('grid.order', $this->productlist); //vmCommonHTML::getSaveOrderButton( $num_rows, 'changeordering' ); ?>
			</th>
		<?php } ?>
		<th width="10%"><?php echo $this->sort('mf_name', 'COM_VIRTUEMART_MANUFACTURER_S') ; ?></th>
		<th width="40px" ><?php echo vmText::_('COM_VIRTUEMART_REVIEW_S'); ?></th>
		<th width="40px" ><?php echo $this->sort('product_special', 'COM_VIRTUEMART_PRODUCT_FORM_SPECIAL'); ?> </th>
		<th width="40px" ><?php echo $this->sort('published') ; ?></th>
	    <th><?php echo $this->sort('p.virtuemart_product_id', 'COM_VIRTUEMART_ID')  ?></th>
	</tr>
	</thead>
	<tbody>
	<?php
	$total = $this->pagination->total;

	if ($totalList ) {
		$i = 0;
		$k = 0;
		$keyword = vRequest::getCmd('keyword');
		foreach ($this->productlist as $key => $product) {
			$checked = JHtml::_('grid.id', $i , $product->virtuemart_product_id,null,'virtuemart_product_id');
			$published = JHtml::_('grid.published', $product, $i );
			$published = $this->gridPublished( $product, $i );

			$is_featured = $this->toggle($product->product_special, $i,'toggle.product_special');
			$link = 'index.php?option=com_virtuemart&view=product&task=edit&virtuemart_product_id='.$product->virtuemart_product_id;
			?>
			<tr class="row<?php echo $k ; ?>">
				<!-- Checkbox -->
				<td class="admin-checkbox"><?php echo $checked; ?></td>

				<td align ="left>">
					<!--<span style="float:left; clear:left"> -->
  				<?php
				if(empty($product->product_name)){
					$product->product_name = vmText::sprintf('COM_VM_TRANSLATION_MISSING','virtuemart_product_id',$product->virtuemart_product_id);
				}
				echo JHtml::_('link', JRoute::_($link), $product->product_name, array('title' => vmText::_('COM_VIRTUEMART_EDIT').' '. htmlentities($product->product_name))); ?>
					<!-- </span>  -->
				</td>

                <?php if (!$product_parent_id ) { ?>
				<td><?php
					//if ($product->product_parent_id  ) {
						echo $product->parent_link;
					//}
					?></td>
				<?php } ?>
				<td><?php
						echo $product->childlist_link;
                                                 ?>
                                </td>
				<!-- Media -->
				<?php
					// Create URL
					$link = JRoute::_('index.php?view=media&virtuemart_product_id='.$product->virtuemart_product_id.'&option=com_virtuemart');
				?>
				<td align="center">
					<?php
					// We show the images only when less than 21 products are displayeed -->

					if($this->pagination->limit<=$mediaLimit or $totalList<=$mediaLimit){
						// Product list should be ordered
						$this->model->addImages($product,1);
						$img = '<span >('.$product->mediaitems.')</span>'.$product->images[0]->displayMediaThumb('class="vm_mini_image"',false );
						//echo JHtml::_('link', $link, $img,  array('title' => vmText::_('COM_VIRTUEMART_MEDIA_MANAGER').' '.$product->product_name));
					} else {
						//echo JHtml::_('link', $link, '<span class="icon-nofloat vmicon vmicon-16-media"></span> ('.$product->mediaitems.')', array('title' => vmText::_('COM_VIRTUEMART_MEDIA_MANAGER').' '.$product->product_name) );
						$img = '<span class="icon-nofloat vmicon vmicon-16-media"></span> ('.$product->mediaitems.')';
					}
					echo JHtml::_('link', $link, $img,  array('title' => vmText::_('COM_VIRTUEMART_MEDIA_MANAGER').' '.htmlentities($product->product_name)));
					?>
					</td>
				<!-- Product SKU -->
				<td><?php echo $product->product_sku; ?></td>
				<!-- Product price -->
				<td align="right" ><?php
					if(isset($product->product_price_display)) {
						echo $product->product_price_display;
					}
				?></td>
				<!-- Category name -->
				<td><?php
					echo $product->categoriesList;
				?></td>
				<!-- Reorder only when category ID is present -->
				<?php if ($this->categoryId ) { ?>
					<td class="order" >
						<span class="vmicon vmicon-16-move"></span>
						<span><?php echo $this->pagination->vmOrderUpIcon( $i, $product->ordering, 'orderup', vmText::_('COM_VIRTUEMART_MOVE_UP')  ); ?></span>
						<span><?php echo $this->pagination->vmOrderDownIcon( $i, $product->ordering, $total , true, 'orderdown', vmText::_('COM_VIRTUEMART_MOVE_DOWN') ); ?></span>
						<input class="ordering" type="text" name="order[<?php echo $product->id?>]" id="order[<?php echo $i?>]" size="5" value="<?php echo $product->ordering; ?>" style="text-align: center" />

						<?php // echo vmCommonHTML::getOrderingField( $product->ordering ); ?>
					</td>
				<?php }  ?>
				<!-- Manufacturer name -->
				<td><?php
					echo $product->manuList;

				?></td>

				<!-- Reviews -->
				<?php $link = vRequest::vmSpecialChars('index.php?option=com_virtuemart&view=ratings&task=listreviews&virtuemart_product_id='.$product->virtuemart_product_id); ?>
				<td align="center" ><?php echo JHtml::_('link', $link, $product->reviews); ?></td>
				<td align="center" >
					<?php
						echo $is_featured;
					?>
				 </td>
				<!-- published -->
				<td align="center" ><?php echo $published; ?></td>
                                <!-- Vendor name -->
				<td align="right"><?php echo $product->virtuemart_product_id; // echo $product->vendor_name; ?></td>
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
			<?php echo $this->pagination->getListFooter(false); ?>
		</td>
		</tr>
	</tfoot>
	</table>
</div>
<!-- Hidden Fields -->
<input type="hidden" name="product_parent_id" value="<?php echo vRequest::getInt('product_parent_id', 0); ?>" />
	<?php echo $this->addStandardHiddenToForm(); ?>
</form>

<?php AdminUIHelper::endAdminArea();

// DONE BY stephanbais
/// DRAG AND DROP PRODUCT ORDER HACK
if ($this->categoryId ) {
	vmJsApi::addJScript( '/administrator/components/com_virtuemart/assets/js/products.js', false, false );
	//vmJsApi::addJScript( 'sortableProducts', 'Virtuemart.sortableProducts;' );
	vmJsApi::addJScript('sortable','Virtuemart.sortable;');
}

/// END PRODUCT ORDER HACK
?>