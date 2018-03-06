<?php defined('_JEXEC') or die('Restricted access');
/**
 *
 * Layout for the shopping cart for Amazon
 *
 * @package    VirtueMart
 * @subpackage Cart
 * @author Max Milbers, Valérie Isaksen
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */
?>


<fieldset class="vm-fieldset-pricelist">
<table
	class="cart-summary"
	cellspacing="0"
	cellpadding="0"
	border="0"
	width="100%">
<tr>
	<th align="left"><?php echo vmText::_ ('COM_VIRTUEMART_CART_NAME') ?></th>
	<th align="left"><?php echo vmText::_ ('COM_VIRTUEMART_CART_SKU') ?></th>
	<th
		align="center"
		width="60px"><?php echo vmText::_ ('COM_VIRTUEMART_CART_PRICE') ?></th>
	<th
		align="right"
		width="140px"><?php echo vmText::_ ('COM_VIRTUEMART_CART_QUANTITY') ?>
		<?php if (!$this->readonly_cart) { ?>
			/ <?php echo vmText::_('COM_VIRTUEMART_CART_ACTION') ?>
		<?php } ?>
	</th>


	<?php if (VmConfig::get ('show_tax')) {
		$tax = vmText::_ ('COM_VIRTUEMART_CART_SUBTOTAL_TAX_AMOUNT');
		if(!empty($this->cart->cartData['VatTax'])){
			reset($this->cart->cartData['VatTax']);
			$taxd = current($this->cart->cartData['VatTax']);
			$tax = $taxd['calc_name'] .' '. rtrim(trim($taxd['calc_value'],'0'),'.').'%';
		}
		?>
		<th align="right" width="60px"><?php  echo "<span  class='priceColor2'>" . $tax . '</span>' ?></th>
	<?php } ?>
	<th align="right" width="60px"><?php echo "<span  class='priceColor2'>" . vmText::_ ('COM_VIRTUEMART_CART_SUBTOTAL_DISCOUNT_AMOUNT') . '</span>' ?></th>
	<th align="right" width="70px"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL') ?></th>
</tr>

<?php
$i = 1;

foreach ($this->cart->products as $pkey => $prow) { ?>

	<tr valign="top" class="sectiontableentry<?php echo $i ?>">
		<input type="hidden" name="cartpos[]" value="<?php echo $pkey ?>">
		<td align="left">
			<?php if ($prow->virtuemart_media_id) { ?>
				<span class="cart-images">
						 <?php
						 if (!empty($prow->images[0])) {
							 echo $prow->images[0]->displayMediaThumb ('', FALSE);
						 }
						 ?>
						</span>
			<?php } ?>
			<?php echo JHtml::link ($prow->url, $prow->product_name);
			echo $this->customfieldsModel->CustomsFieldCartDisplay ($prow);
			?>

		</td>
		<td align="left"><?php  echo $prow->product_sku ?></td>
		<td align="center">
			<?php
			if (VmConfig::get ('checkout_show_origprice', 1) && $prow->prices['discountedPriceWithoutTax'] != $prow->prices['priceWithoutTax']) {
				echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE) . '</span><br />';
			}

			if ($prow->prices['discountedPriceWithoutTax']) {
				echo $this->currencyDisplay->createPriceDiv ('discountedPriceWithoutTax', '', $prow->prices, FALSE, FALSE);
			} else {
				echo $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, FALSE, FALSE);
			}
			?>
		</td>
		<td align="right"><?php
			 if (!$this->readonly_cart) {

			if ($prow->step_order_level)
				$step=$prow->step_order_level;
			else
				$step=1;
			if($step==0)
				$step=1;
			?>
			<input type="text"
			       onblur="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
			       onclick="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
			       onchange="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
			       onsubmit="Virtuemart.checkQuantity(this,<?php echo $step?>,'<?php echo vmText::_ ('COM_VIRTUEMART_WRONG_AMOUNT_ADDED')?>');"
			       title="<?php echo  vmText::_('COM_VIRTUEMART_CART_UPDATE') ?>" class="quantity-input js-recalculate" size="3" maxlength="4" name="quantity[<?php echo $pkey; ?>]" value="<?php echo $prow->quantity ?>" />

			<button type="submit" class="vmicon vm2-add_quantity_cart" name="updatecart.<?php echo $pkey ?>" title="<?php echo  vmText::_ ('COM_VIRTUEMART_CART_UPDATE') ?>" />

			<button type="submit" class="vmicon vm2-remove_from_cart" name="delete.<?php echo $pkey ?>" title="<?php echo vmText::_ ('COM_VIRTUEMART_CART_DELETE') ?>" />
			 <?php } else {
				 echo $prow->quantity  ;
			 }?>

		</td>

		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"><?php echo "<span class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('taxAmount', '', $prow->prices, FALSE, FALSE, $prow->quantity) . "</span>" ?></td>
		<?php } ?>
		<td align="right"><?php echo "<span class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('discountAmount', '', $prow->prices, FALSE, FALSE, $prow->quantity) . "</span>" ?></td>
		<td colspan="1" align="right">
			<?php
			if (VmConfig::get ('checkout_show_origprice', 1) && !empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceWithTax'] != $prow->prices['salesPrice']) {
				echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceWithTax', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span><br />';
			}
			elseif (VmConfig::get ('checkout_show_origprice', 1) && empty($prow->prices['basePriceWithTax']) && $prow->prices['basePriceVariant'] != $prow->prices['salesPrice']) {
				echo '<span class="line-through">' . $this->currencyDisplay->createPriceDiv ('basePriceVariant', '', $prow->prices, TRUE, FALSE, $prow->quantity) . '</span><br />';
			}
			echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $prow->prices, FALSE, FALSE, $prow->quantity) ?></td>
	</tr>
	<?php
	$i = ($i==1) ? 2 : 1;
} ?>
<!--Begin of SubTotal, Tax, Shipment, Coupon Discount and Total listing -->
<?php if (VmConfig::get ('show_tax')) {
	$colspan = 3;
} else {
	$colspan = 2;
} ?>
<tr>
	<td colspan="4">&nbsp;</td>

	<td colspan="<?php echo $colspan ?>">
		<hr/>
	</td>
</tr>
<tr class="sectiontableentry1">
	<td colspan="4" align="right"><?php echo vmText::_ ('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>

	<?php if (VmConfig::get ('show_tax')) { ?>
		<td align="right"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('taxAmount', '', $this->cart->cartPrices, FALSE) . "</span>" ?></td>
	<?php } ?>
	<td align="right"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('discountAmount', '', $this->cart->cartPrices, FALSE) . "</span>" ?></td>
	<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ('salesPrice', '', $this->cart->cartPrices, FALSE) ?></td>
</tr>

<?php
if (VmConfig::get ('coupons_enable')) {
	?>
	<tr class="sectiontableentry2">
		<td colspan="4" align="left">
			<?php if (!empty($this->layoutName) && $this->layoutName == 'default') {
				echo $this->loadTemplate ('coupon');
			}
			?>

			<?php if (!empty($this->cart->cartData['couponCode'])) { ?>
			<?php
			echo $this->cart->cartData['couponCode'];
			echo $this->cart->cartData['couponDescr'] ? (' (' . $this->cart->cartData['couponDescr'] . ')') : '';
			?>
		</td>

		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ('couponTax', '', $this->cart->cartPrices['couponTax'], FALSE); ?> </td>
		<?php } ?>
		<td align="right"> </td>
		<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceCoupon', '', $this->cart->cartPrices['salesPriceCoupon'], FALSE); ?> </td>
		<?php } else { ?>
			<td colspan="6" align="left">&nbsp;</td>
		<?php
		}

		?>
	</tr>
<?php } ?>
<?php
foreach ($this->cart->cartData['DBTaxRulesBill'] as $rule) {
	?>
	<tr class="sectiontableentry<?php echo $i ?>">
		<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>

		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"></td>
		<?php } ?>
		<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?></td>
		<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
	</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
} ?>

<?php

foreach ($this->cart->cartData['taxRulesBill'] as $rule) {
	?>
	<tr class="sectiontableentry<?php echo $i ?>">
		<td colspan="4" align="right"><?php echo $rule['calc_name'] ?> </td>
		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
		<?php } ?>
		<td align="right"><?php ?> </td>
		<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
	</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
}

foreach ($this->cart->cartData['DATaxRulesBill'] as $rule) {
	?>
	<tr class="sectiontableentry<?php echo $i ?>">
		<td colspan="4" align="right"><?php echo   $rule['calc_name'] ?> </td>

		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"></td>

		<?php } ?>
		<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?>  </td>
		<td align="right"><?php echo $this->currencyDisplay->createPriceDiv ($rule['virtuemart_calc_id'] . 'Diff', '', $this->cart->cartPrices[$rule['virtuemart_calc_id'] . 'Diff'], FALSE); ?> </td>
	</tr>
	<?php
	if ($i) {
		$i = 1;
	} else {
		$i = 0;
	}
} ?>

<?php if (!empty($this->cart->virtuemart_shipmentmethod_id) ) { ?>
	<tr class="sectiontableentry1" style="vertical-align:top;">
		<?php if (!$this->cart->automaticSelectedShipment) { ?>
			<td colspan="4" align="left">
			<?php echo $this->cart->cartData['shipmentName']; ?>
			</td>
		<?php
		} else {
			?>
			<td colspan="4" align="left">
				<?php echo $this->cart->cartData['shipmentName']; ?>
			</td>
		<?php } ?>

		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('shipmentTax', '', $this->cart->cartPrices['shipmentTax'], FALSE) . "</span>"; ?> </td>
		<?php } ?>
		<td align="right"><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?></td>
		<td style="align:right;vertical-align:middle;"><?php echo $this->currencyDisplay->createPriceDiv ('salesPriceShipment', '', $this->cart->cartPrices['salesPriceShipment'], FALSE); ?> </td>
	</tr>
<?php } ?>
<?php if ($this->cart->pricesUnformatted['salesPrice']>0.0 and !empty($this->cart->virtuemart_paymentmethod_id)) { ?>
	<tr class="sectiontableentry1" style="vertical-align:top;">
		<?php if (!$this->cart->automaticSelectedPayment) { ?>
			<td colspan="4" style="align:left;vertical-align:top;">
				<?php echo $this->cart->cartData['paymentName'];  ?> </td>

			</td>
		<?php } else { ?>
			<td colspan="4" style="align:left;vertical-align:top;" >
				<?php echo '<h4>'.vmText::_ ('COM_VIRTUEMART_CART_SELECTED_PAYMENT').'</h4>'; ?>
				<?php echo $this->cart->cartData['paymentName']; ?> </td>
		<?php } ?>
		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"><?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('paymentTax', '', $this->cart->cartPrices['paymentTax'], FALSE) . "</span>"; ?> </td>
		<?php } ?>
		<td align="right"><?php if($this->cart->cartPrices['salesPriceShipment'] < 0) echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?></td>
		<td style="align:right;vertical-align:middle;" ><?php  echo $this->currencyDisplay->createPriceDiv ('salesPricePayment', '', $this->cart->cartPrices['salesPricePayment'], FALSE); ?> </td>
	</tr>
<?php  } ?>
<tr>
	<td colspan="4">&nbsp;</td>
	<td colspan="<?php echo $colspan ?>">
		<hr/>
	</td>
</tr>
<tr class="sectiontableentry2">
	<td colspan="4" align="right"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL') ?>:</td>

	<?php if (VmConfig::get ('show_tax')) { ?>
		<td align="right"> <?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('billTaxAmount', '', $this->cart->cartPrices['billTaxAmount'], FALSE) . "</span>" ?> </td>
	<?php } ?>
	<td align="right"> <?php echo "<span  class='priceColor2'>" . $this->currencyDisplay->createPriceDiv ('billDiscountAmount', '', $this->cart->cartPrices['billDiscountAmount'], FALSE) . "</span>" ?> </td>
	<td align="right"><strong><?php echo $this->currencyDisplay->createPriceDiv ('billTotal', '', $this->cart->cartPrices['billTotal'], FALSE); ?></strong></td>
</tr>
<?php
if ($this->totalInPaymentCurrency) {
	?>

	<tr class="sectiontableentry2">
		<td colspan="4" align="right"><?php echo vmText::_ ('COM_VIRTUEMART_CART_TOTAL_PAYMENT') ?>:</td>

		<?php if (VmConfig::get ('show_tax')) { ?>
			<td align="right"></td>
		<?php } ?>
		<td align="right"></td>
		<td align="right"><strong><?php echo $this->totalInPaymentCurrency;   ?></strong></td>
	</tr>
<?php
}
?>

</table>
</fieldset>
