<?php
/**
*
* Order items view
*
* @package	VirtueMart
* @subpackage Orders
* @author Max Milbers, Valerie Isaksen, Spirous Petrakis
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2018 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: details_items.php 5432 2012-02-14 02:20:35Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

$colspan=8;

if ($this->doctype != 'invoice') {
    $colspan -= 4;
} elseif ( ! VmConfig::get('show_tax')) {
    $colspan -= 1;
}

$handled = array();
$discountsBill = false;
$taxBill = false;
$vats = 0;
foreach($this->orderDetails['calc_rules'] as $rule){
	if(isset($sumRules[$rule->virtuemart_calc_id])){	// or $rule->calc_kind=='payment' or $rule->calc_kind=='shipment'){
		continue;
	}
	$handled[$rule->virtuemart_calc_id] = true;
	$r = new stdClass();
	$r->calc_result = $rule->calc_result;
	$r->calc_amount = $rule->calc_amount;
	$r->calc_rule_name = $rule->calc_rule_name;
	$r->calc_kind = $rule->calc_kind;
	$r->calc_value = $rule->calc_value;

	if($rule->calc_kind == 'DBTaxRulesBill' or $rule->calc_kind == 'DATaxRulesBill'){
		$discountsBill[$rule->virtuemart_calc_id] = $r;
	}
	if($rule->calc_kind == 'taxRulesBill' or $rule->calc_kind == 'VatTax' or $rule->calc_kind=='payment' or $rule->calc_kind=='shipment'){
		//vmdebug('method rule',$rule);
		$r->label = shopFunctionsF::getTaxNameWithValue($rule->calc_rule_name,$rule->calc_value);
		if(isset($taxBill[$rule->virtuemart_calc_id])){
			$taxBill[$rule->virtuemart_calc_id]->calc_amount += $r->calc_amount;
		} else {
			$taxBill[$rule->virtuemart_calc_id] = $r;
		}
	}
} ?>

<table class="html-email" width="100%" cellspacing="0" cellpadding="5" border="0" style="border-collapse: collapse; margin: 0 auto;<?php echo $this->isMail ? ' font-family: Arial, Helvetica, sans-serif; font-size: 12px;' : ''; ?>">
	<tr style="text-align: left;" class="sectiontableheader">
		<th align="left" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_SKU') ?></th>
		<th align="center" bgcolor="#EEEEEE" colspan="2" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_PRODUCT_NAME_TITLE') ?></th>
		<th align="center" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_STATUS') ?></th>
		<?php if ($this->doctype == 'invoice') { ?>
		<th align="center" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRICE') ?></th>
		<?php } ?>
		<th align="center" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_QTY') ?></th>
		<?php if ($this->doctype == 'invoice') { ?>
			<?php if ( VmConfig::get('show_tax')) { ?>
		<th align="center" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;"><?php
				if(is_array($taxBill) and count($taxBill)==1){
					reset($taxBill);
					$t = current($taxBill);
					echo shopFunctionsF::getTaxNameWithValue($t->calc_rule_name,$t->calc_value);
				} else {
					echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_TAX');
				}
		?></th>
			<?php } ?>
		<th align="center" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_SUBTOTAL_DISCOUNT_AMOUNT') ?></th>
		<th align="right" bgcolor="#EEEEEE" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></th>
		<?php } ?>
	</tr>

<?php
$menuItemID = shopFunctionsF::getMenuItemId($this->orderDetails['details']['BT']->order_language);
if(!class_exists('VirtueMartModelCustomfields'))require(VMPATH_ADMIN.DS.'models'.DS.'customfields.php');
VirtueMartModelCustomfields::$useAbsUrls = ($this->isMail or $this->isPdf);
foreach($this->orderDetails['items'] as $item) {
	$qtt = $item->product_quantity ;

    if ($this->print and !$this->isPdf) {
		$product_name = $item->order_item_name;;
	} else {
		$product_name = '<a href="'.JURI::root().'index.php?option=com_virtuemart&view=productdetails&virtuemart_category_id=' . $item->virtuemart_category_id .
		'&virtuemart_product_id=' . $item->virtuemart_product_id . '&Itemid=' . $menuItemID.'">'.$item->order_item_name.'</a>';
	}

	?>
	<tr style="vertical-align: top;">
		<td align="left" style="border: 1px solid #CCCCCC;">
			<?php echo $item->order_item_sku; ?>
		</td>
		<td align="left" style="border: 1px solid #CCCCCC;" colspan="2">
			<div><?php echo $product_name; ?></div>
			<?php
				$product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item,'FE');
				echo $product_attribute;
			?>
		</td>
		<td align="center" style="border: 1px solid #CCCCCC;">
			<?php echo $this->orderstatuses[$item->order_status]; ?>
		</td>
	<?php if ($this->doctype == 'invoice') { ?>
		<td align="right" style="border: 1px solid #CCCCCC;" class="priceCol">
			<?php
			$item->product_discountedPriceWithoutTax = (float) $item->product_discountedPriceWithoutTax;
			if (!empty($item->product_priceWithoutTax) && $item->product_discountedPriceWithoutTax != $item->product_priceWithoutTax) {
				echo '<span style="text-decoration: line-through;">'.$this->currency->priceDisplay($item->product_item_price, $this->user_currency_id) .'</span><br />';
				echo '<span >'.$this->currency->priceDisplay($item->product_discountedPriceWithoutTax, $this->user_currency_id) .'</span><br />';
			} else {
				echo '<span >'.$this->currency->priceDisplay($item->product_item_price, $this->user_currency_id) .'</span><br />';
			}
			?>
		</td>
	<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;">
			<?php echo $qtt; ?>
		</td>
	<?php if ($this->doctype == 'invoice') { ?>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<td align="right" style="border: 1px solid #CCCCCC;" class="priceCol"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($item->product_tax ,$this->user_currency_id, $qtt)."</span>" ?></td>
		<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;" class="priceCol" >
			<?php echo  $this->currency->priceDisplay( $item->product_subtotal_discount, $this->user_currency_id );  //No quantity is already stored with it ?>
		</td>
		<td align="right" style="border: 1px solid #CCCCCC;" class="priceCol">
			<?php
			$item->product_basePriceWithTax = (float) $item->product_basePriceWithTax;
			$class = '';
			if(!empty($item->product_basePriceWithTax) && $item->product_basePriceWithTax != $item->product_final_price ) {
				echo '<span style="text-decoration: line-through;">'.$this->currency->priceDisplay($item->product_basePriceWithTax,$this->user_currency_id,$qtt) .'</span><br />' ;
			}
			elseif (empty($item->product_basePriceWithTax) && $item->product_item_price != $item->product_final_price) {
				echo '<span style="text-decoration: line-through;">' . $this->currency->priceDisplay($item->product_item_price,$this->user_currency_id,$qtt) . '</span><br />';
			}

			echo $this->currency->priceDisplay(  $item->product_subtotal_with_tax ,$this->user_currency_id); //No quantity or you must use product_final_price ?>
		</td>
	<?php } ?>
	</tr>
<?php
} ?>

<?php if ($this->doctype == 'invoice') { ?>
	<tr class="sectiontableentry1">
		<td colspan="6" align="right" style="border: 1px solid #CCCCCC;"><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_PRICES_TOTAL'); ?></td>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($this->orderDetails['details']['BT']->order_tax, $this->user_currency_id)."</span>" ?></td>
		<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo "<span  class='priceColor2'>".$this->currency->priceDisplay($this->orderDetails['details']['BT']->order_discountAmount, $this->user_currency_id)."</span>" ?></td>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_salesPrice, $this->user_currency_id) ?></td>
	</tr>

	<?php
	if ($this->orderDetails['details']['BT']->coupon_discount <> 0.00) {
		$coupon_code=$this->orderDetails['details']['BT']->coupon_code?' ('.$this->orderDetails['details']['BT']->coupon_code.')':'';
	?>
	<tr>
		<td align="right" style="border: 1px solid #CCCCCC;" class="pricePad" colspan="6"><?php echo vmText::_('COM_VIRTUEMART_COUPON_DISCOUNT').$coupon_code ?></td>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<td align="right" style="border: 1px solid #CCCCCC;">&nbsp;</td>
		<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;">&nbsp;</td>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->coupon_discount, $this->user_currency_id); ?></td>
	</tr>
	<?php } ?>

	<?php
	if($discountsBill){
		foreach($discountsBill as $rule){ ?>
	<tr >
		<td colspan="6" align="right" style="border: 1px solid #CCCCCC;" class="pricePad"><?php echo $rule->calc_rule_name ?> </td>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<td align="right" style="border: 1px solid #CCCCCC;">&nbsp;</td>
		<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo $this->currency->priceDisplay($rule->calc_amount, $this->user_currency_id); ?></td>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo $this->currency->priceDisplay($rule->calc_amount, $this->user_currency_id); ?></td>
	</tr>
			<?php
		}
	} ?>

	<tr>
		<td align="right" style="border: 1px solid #CCCCCC;" class="pricePad" colspan="6"><?php echo $this->orderDetails['shipmentName'] ?></td>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<td align="right" style="border: 1px solid #CCCCCC;"><span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_shipment_tax, $this->user_currency_id) ?></span> </td>
		<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;">&nbsp;</td>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_shipment + $this->orderDetails['details']['BT']->order_shipment_tax, $this->user_currency_id); ?></td>
	</tr>

	<tr>
		<td align="right" style="border: 1px solid #CCCCCC;" class="pricePad" colspan="6"><?php echo $this->orderDetails['paymentName'] ?></td>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<td align="right" style="border: 1px solid #CCCCCC;"><span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_payment_tax, $this->user_currency_id) ?></span> </td>
		<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;">&nbsp;</td>
		<td align="right" style="border: 1px solid #CCCCCC;"><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_payment + $this->orderDetails['details']['BT']->order_payment_tax, $this->user_currency_id); ?></td>
	</tr>

	<tr>
		<td align="right" style="border: 1px solid #CCCCCC;" class="pricePad" colspan="6"><strong><?php echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') ?></strong></td>
		<?php if ( VmConfig::get('show_tax')) { ?>
		<td align="right" style="border: 1px solid #CCCCCC;"><span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_billTaxAmount, $this->user_currency_id); ?></span></td>
		<?php } ?>
		<td align="right" style="border: 1px solid #CCCCCC;"><span class='priceColor2'><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_billDiscountAmount, $this->user_currency_id); ?></span></td>
		<td align="right" style="border: 1px solid #CCCCCC;"><strong><?php echo $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_total, $this->user_currency_id); ?></strong></td>
	</tr>

	<?php
	$colspan_pcr = 3;
	if (!VmConfig::get('show_tax')) {
		$colspan_pcr -= 1;
	}
	if($this->doVendor){
		$comp = $this->orderDetails['details']['BT']->order_currency;
	} else {
		$comp = $this->user_currency_id;
	}
	if(!empty($this->orderDetails['details']['BT']->payment_currency_rate)
		and $this->orderDetails['details']['BT']->payment_currency_id!=$comp and $this->orderDetails['details']['BT']->payment_currency_rate!=1.0){
	?>
	<tr>
		<td align="right" style="border: 1px solid #CCCCCC;" class="pricePad" colspan="6"><strong><?php echo vmText::_('COM_VM_TOTAL_IN_PAYMENT_CURRENCY') ?></strong></td>
		<td align="right" style="border: 1px solid #CCCCCC;" class="pricePad" colspan="<?php echo $colspan_pcr ?>"><?php
			if($this->orderDetails['details']['BT']->order_currency==$this->orderDetails['details']['BT']->user_currency_id and $this->orderDetails['details']['BT']->user_currency_id!=$this->orderDetails['details']['BT']->payment_currency_id){
				echo $this->orderDetails['details']['BT']->payment_currency_rate;
			} else if ($this->orderDetails['details']['BT']->order_currency==$this->orderDetails['details']['BT']->payment_currency_id and $this->orderDetails['details']['BT']->payment_currency_id!=$this->orderDetails['details']['BT']->user_currency_id){
				echo $this->orderDetails['details']['BT']->user_currency_rate;
			}
			echo ' <strong>';
			echo $this->currencyP->priceDisplay($this->orderDetails['details']['BT']->order_total, $this->orderDetails['details']['BT']->payment_currency_id); ?>
			</strong></td>
	</tr>
	<?php
	}

	if($taxBill){
		?>
		<tr>
			<td colspan="7" align="right" style="border: 1px solid #CCCCCC;" class="pricePad"><?php echo vmText::_('COM_VIRTUEMART_TOTAL_INCL_TAX') ?> </td>
			<?php if ( VmConfig::get('show_tax')) {  ?>
			<td align="left" style="border: 1px solid #CCCCCC;">&nbsp;</td>
			<?php } ?>
			<td align="left" style="border: 1px solid #CCCCCC;">&nbsp;</td>
		</tr><?php
		foreach($taxBill as $rule){
			if ($rule->calc_kind == 'taxRulesBill' or $rule->calc_kind == 'VatTax' ) { ?>
				<tr >
					<td colspan="6" align="right" style="border: 1px solid #CCCCCC;" class="pricePad"><?php echo $rule->label ?> </td>
					<?php if ( VmConfig::get('show_tax')) {  ?>
					<td align="right" style="border: 1px solid #CCCCCC;"><?php echo $this->currency->priceDisplay($rule->calc_result, $this->user_currency_id); ?></td>
					<?php } ?>
					<td align="right" style="border: 1px solid #CCCCCC;">&nbsp;</td>
					<td align="right" style="border: 1px solid #CCCCCC;">&nbsp;</td>
				</tr>
				<?php
			}
		}
	}
} ?>
</table>
