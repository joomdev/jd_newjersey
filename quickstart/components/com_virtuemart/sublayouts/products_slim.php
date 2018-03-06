<?php
/**
 * sublayout products
 *
 * @package	VirtueMart
 * @author Max Milbers
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL2, see LICENSE.php
 * @version $Id: cart.php 7682 2017-02-26 17:07:20Z Milbo $
 */
 
defined('_JEXEC') or die('Restricted access');
$products_per_row = empty($viewData['products_per_row'])? 1:$viewData['products_per_row'] ;
$currency = $viewData['currency'];
$showRating = $viewData['showRating'];
$verticalseparator = " vertical-separator";
echo shopFunctionsF::renderVmSubLayout('askrecomjs');

$ItemidStr = '';
$Itemid = shopFunctionsF::getLastVisitedItemId();
if(!empty($Itemid)){
	$ItemidStr = '&Itemid='.$Itemid;
}

$dynamic = false;

if (vRequest::getInt('dynamic',false) and vRequest::getInt('virtuemart_product_id',false)) {
	$dynamic = true;
}

foreach ($viewData['products'] as $type => $products ) {

	$col = 1;
	$nb = 1;
	$row = 1;

	if($dynamic){
		$rowsHeight[$row]['product_s_desc'] = 1;
		$rowsHeight[$row]['price'] = 1;
		$rowsHeight[$row]['customfields'] = 1;
		$col = 2;
		$nb = 2;
	} else {
		$rowsHeight = shopFunctionsF::calculateProductRowsHeights($products,$currency,$products_per_row);

		if( (!empty($type) and count($products)>0) or (count($viewData['products'])>1 and count($products)>0)){
			$productTitle = vmText::_('COM_VIRTUEMART_'.strtoupper($type).'_PRODUCT'); ?>
	<div class="<?php echo $type ?>-view">
	  <h4><?php echo $productTitle ?></h4>
			<?php // Start the Output
		}
	}

	// Calculating Products Per Row
	$cellwidth = ' width'.floor ( 100 / $products_per_row );

	$BrowseTotalProducts = count($products);

	$col = 1;
	$nb = 1;
	$row = 1;

	foreach ( $products as $product ) {
		if(!is_object($product) or empty($product->link)) {
			vmdebug('$product is not object or link empty',$product);
			continue;
		}

		// Show the horizontal seperator
		if ($col == 1 && $nb > $products_per_row) { ?>
	<div class="horizontal-separator"></div>
		<?php }

		// this is an indicator wether a row needs to be opened or not
		if ($col == 1) { ?>
	<div class="row">
		<?php }

		// Show the vertical seperator
		if ($nb == $products_per_row or $nb % $products_per_row == 0) {
			$show_vertical_separator = ' ';
		} else {
			$show_vertical_separator = $verticalseparator;
		}

			// Show Products
			?>
            <div class="product floatleft<?php echo $cellwidth.$show_vertical_separator ?>">

                    <div class="width30 floatleft center">
                        <a title="<?php echo $product->product_name ?>" rel="vm-additional-images" href="<?php echo $product->link; ?>">
							<?php
							echo $product->images[0]->displayMediaThumb( 'class="browseProductImage"', false );
							?>
                        </a>

						<?php
						if(VmConfig::get( 'display_stock', 1 )) { ?>
                            <!-- 						if (!VmConfig::get('use_as_catalog') and !(VmConfig::get('stockhandle','none')=='none')){?> -->
                            <div class="paddingtop8">
                                <span class="vmicon vm2-<?php echo $product->stock->stock_level ?>"
                                      title="<?php echo $product->stock->stock_tip ?>"></span>
                                <span class="stock-level"><?php echo JText::_( 'COM_VIRTUEMART_STOCK_LEVEL_DISPLAY_TITLE_TIP' ) ?></span>
                            </div>
						<?php } ?>
                    </div>

                    <div class="width70 floatright">

                        <h2><?php echo JHTML::link( $product->link, $product->product_name ); ?></h2>

                        <?php //echo $rowsHeight[$row]['price'] ?>
                        <div class="vm3pr-<?php echo $rowsHeight[$row]['price'] ?>"> <?php
                            echo shopFunctionsF::renderVmSubLayout('prices',array('product'=>$product,'currency'=>$currency)); ?>
                            <div class="clear"></div>
                        </div>
                        <?php //echo $rowsHeight[$row]['customs'] ?>
                        <div class="vm3pr-<?php echo $rowsHeight[$row]['customfields'] ?>"> <?php
                            echo shopFunctionsF::renderVmSubLayout('addtocart',array('product'=>$product,'rowHeights'=>$rowsHeight[$row], 'position' => array('ontop', 'addtocart'))); ?>
                        </div>

                        <div class="vm-details-button">
                            <?php // Product Details Button
                            $link = empty($product->link)? $product->canonical:$product->link;
                            echo JHtml::link($link.$ItemidStr,vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), array ('title' => $product->product_name, 'class' => 'product-details' ) );
                            //echo JHtml::link ( JRoute::_ ( 'index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id , FALSE), vmText::_ ( 'COM_VIRTUEMART_PRODUCT_DETAILS' ), array ('title' => $product->product_name, 'class' => 'product-details' ) );
                            ?>
                        </div>
                    <div class="clear"></div>
					<?php if($dynamic){
						echo vmJsApi::writeJS();
					} ?>
                </div>
                <!-- end of spacer -->
            </div> <!-- end of product -->

		<?php
        $nb ++;
      // Do we need to close the current row now?
      if ($col == $products_per_row || $nb>$BrowseTotalProducts) { ?>
    <div class="clear"></div>
  </div>
      <?php
      	$col = 1;
		$row++;
    } else {
      $col ++;
    }
  }

      if( (!empty($type) and count($products)>0) or (count($viewData['products'])>1 and count($products)>0) ){
        // Do we need a final closing row tag?
        //if ($col != 1) {
      ?>
    <div class="clear"></div>
  </div>
    <?php
    // }
    }
  }
