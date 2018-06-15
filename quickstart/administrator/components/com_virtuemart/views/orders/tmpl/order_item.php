<?php
/**
 * Display form details
 *
 * @package	VirtueMart
 * @subpackage Orders
 * @author Oscar van Eijk, Max Milbers, Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: order.php 9741 2018-01-26 11:13:21Z alatak $
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
$lId=0;
$item=$this->item;
$i=0;
?>


<td>
	<?php // TODO : commenting dynoTable way at the moment ?>
	<!--span class="vmicon vmicon-16-remove order-item-remove"></span-->
	<?php // TODO COM_VIRTUEMART_ORDER_DELETE_ITEM_JS : _JS Why ? ?>
	<?php if(vmAccess::manager('orders.edit')) { ?>
		<?php if (!VmConfig::get('ordersAddOnly',false)) { ?>
		<a href="#" title="<?php echo vmText::_('COM_VIRTUEMART_ORDER_DELETE_ITEM_JS'). ' ' . $item->order_item_name ; ?>"
		   onClick="javascript:Virtuemart.removeItem(event,<?php echo $item->virtuemart_order_item_id; ?>);">
			<span class="vmicon vmicon-16-remove 4remove orderEdit"></span></a>
		<?php } ?>
		<?php //TODO: change vmicon-16-move and create vmicon-16-clone class ?>
			<span class="icon-copy order-item-clone orderEdit hasTip" title="<?php echo vmText::_('COM_VIRTUEMART_ORDER_ITEM_CLONE'). ' ' . $item->order_item_name ; ?>"></span>
	<?php } ?>
</td>
<td>
		<span class='orderView'>
			<?php echo $item->product_quantity; ?>
		</span>
	<input class='input-mini orderEdit' type="text" size="3" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][product_quantity]" value="<?php echo $item->product_quantity; ?>"/>
	<?php //if(empty($item->virtuemart_product_id)) { ?>

	<?php //} ?>
</td>
<td>
	<span class='orderView'><?php echo $item->order_item_name; ?></span>

	<input class='orderEdit' type="text"  name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][order_item_name]" value="<?php echo $item->order_item_name; ?>" style="width:90%;min-width:100px" />
	<?php if ($item->virtuemart_order_item_id > 0 ) { ?>
		<div class="goto-product">
			<a href="<?php echo $item->linkedit ?>" target="_blank"
			   title="<?php echo vmText::_('COM_VM_GOTO_PRODUCT') . ' ' . $item->order_item_name ?>">
				<span class="vm2-modallink"></span>
			</a>
		</div>

		<?php
		if (!class_exists('VirtueMartModelCustomfields')) {
			require(VMPATH_ADMIN . DS . 'models' . DS . 'customfields.php');
		}
		$product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item, 'BE');
		if ($product_attribute) {
			echo '<div>' . $product_attribute . '</div>';
		}

		$_dispatcher = JDispatcher::getInstance();
		$_returnValues = $_dispatcher->trigger('plgVmOnShowOrderLineBEShipment', array($this->orderID, $item->virtuemart_order_item_id));
		$_plg = '';
		foreach ($_returnValues as $_returnValue) {
			if ($_returnValue !== null) {
				$_plg .= $_returnValue;
			}
		}
		if ($_plg !== '') {
			echo '<table border="0" celspacing="0" celpadding="0">'
				. '<tr>'
				. '<td width="8px"></td>' // Indent
				. '<td>' . $_plg . '</td>'
				. '</tr>'
				. '</table>';
		}
	}
	?>
</td>
<td>
	<span class='orderView'><?php echo $item->order_item_sku; ?></span>
    <span class='orderEdit'><?php echo vmText::_('COM_VIRTUEMART_ORDER_ITEM_ENTER_SKU_PRODUCT_ID') ?></span>
	<input class='orderEdit' type="text" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][order_item_sku]" value="<?php echo $item->order_item_sku; ?>" placeholder="<?php echo vmText::_('COM_VIRTUEMART_ORDER_ITEM_ENTER_SKU') ?>"/><br>

	<input class='orderEdit' type="text" size="10" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][virtuemart_product_id]" value="<?php echo $item->virtuemart_product_id; ?>" placeholder="<?php echo vmText::_('COM_VIRTUEMART_ORDER_ITEM_ENTER_PRODUCT_ID') ?>"/>

</td>
<td align="center">
	<!--<?php echo $this->orderstatuslist[$item->order_status]; ?><br />-->
	<?php echo $this->itemstatusupdatefields[$item->virtuemart_order_item_id]; ?>

</td>
<td align="right" style="padding-right: 5px;">
	<?php
	$item->product_discountedPriceWithoutTax = (float) $item->product_discountedPriceWithoutTax;
	if (!empty($item->product_discountedPriceWithoutTax) && $item->product_discountedPriceWithoutTax != $item->product_priceWithoutTax) {
		echo '<span style="text-decoration:line-through">'.$this->currency->priceDisplay($item->product_item_price) .'</span><br />';
		echo '<span >'.$this->currency->priceDisplay($item->product_discountedPriceWithoutTax) .'</span>';
	} else {
		echo '<span >'.$this->currency->priceDisplay($item->product_item_price) .'</span>';
	}
	?><br />
	<input class='orderEdit' type="text" size="8" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][product_item_price]" value="<?php echo $item->product_item_price; ?>"/>
</td>
<td align="right" style="padding-right: 5px;">
	<?php echo $this->currency->priceDisplay($item->product_basePriceWithTax); ?><br />
	<input class='orderEdit' type="text" size="8" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][product_basePriceWithTax]" value="<?php echo $item->product_basePriceWithTax; ?>"/>
</td>
<td align="right" style="padding-right: 5px;">
	<?php echo $this->currency->priceDisplay($item->product_final_price); ?><br />
	<input class='orderEdit' type="text" size="8" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][product_final_price]" value="<?php echo $item->product_final_price; ?>"/>
</td>
<td align="right" style="padding-right: 5px;">
	<?php echo $this->currency->priceDisplay( $item->product_tax); ?><br />
	<input class='orderEdit' type="text" size="12" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][product_tax]" value="<?php echo $item->product_tax; ?>"/>
	<span style="display: block; font-size: 80%;" title="<?php echo vmText::_('COM_VIRTUEMART_ORDER_EDIT_CALCULATE_DESC'); ?>">
			<input class='orderEdit' type="checkbox" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][calculate_product_tax]" value="1" /> <label class='orderEdit' for="calculate_product_tax"><?php echo vmText::_('COM_VIRTUEMART_ORDER_EDIT_CALCULATE'); ?></label>
		</span>
</td>
<td align="right" style="padding-right: 5px;">
	<?php echo $this->currency->priceDisplay( $item->product_subtotal_discount); ?><br />
	<input class='orderEdit' type="text" size="8" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][product_subtotal_discount]" value="<?php echo $item->product_subtotal_discount; ?>"/>
</td>
<td align="right" style="padding-right: 5px;">
	<?php
	$item->product_basePriceWithTax = (float) $item->product_basePriceWithTax;
	if(!empty($item->product_basePriceWithTax) && $item->product_basePriceWithTax != $item->product_final_price ) {
		echo '<span style="text-decoration:line-through" >'.$this->currency->priceDisplay($item->product_basePriceWithTax,$this->currency,$item->product_quantity) .'</span><br />' ;
	}
	elseif (empty($item->product_basePriceWithTax) && $item->product_item_price != $item->product_final_price) {
		echo '<span style="text-decoration:line-through">' . $this->currency->priceDisplay($item->product_item_price,$this->currency,$item->product_quantity) . '</span><br />';
	}
	echo $this->currency->priceDisplay($item->product_subtotal_with_tax);
	?>
	<input class='orderEdit' type="hidden" size="8" name="item_id[<?php echo $item->virtuemart_order_item_id; ?>][product_subtotal_with_tax]" value="<?php echo $item->product_subtotal_with_tax; ?>"/>
</td>
