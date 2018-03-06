<?php

defined('_JEXEC') or die('Restricted access');
/**
 *
 * Layout for the shopping cart
 *
 * @package	VirtueMart
 * @subpackage Cart
 * @author Max Milbers, Valerie Isaksen
 *
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 *
 */
// Plain text formating
// echo sprintf("[%s]\n",      $s); // affichage d'une chaîne standard
// echo sprintf("[%10s]\n",    $s); // justification à droite avec des espaces
// echo sprintf("[%-10s]\n",   $s); // justification à gauche avec des espaces
// echo sprintf("[%010s]\n",   $s); // l'espacement nul fonctionne aussi sur les cha�nes
// echo sprintf("[%'#10s]\n",  $s); // utilisation du caractère personnalis� de s�paration '#'
// echo sprintf("[%10.10s]\n", $t); // justification � gauche mais avec une coupure � 10 caract�res
// $s = 'monkey';
// [monkey]
// [    monkey]
// [monkey    ]
// [0000monkey]
// [####monkey]
// [many monke]
// Check to ensure this file is included in Joomla!
// jimport( 'joomla.application.component.view');
// $viewEscape = new JView();
// $viewEscape->setEscape('htmlspecialchars');
// TODO Temp fix !!!!! *********************************>>>
//$skuPrint = echo sprintf( "%64.64s",strtoupper (vmText::_('COM_VIRTUEMART_SKU') ) ) ;
// Head of table
echo strip_tags(vmText::sprintf('COM_VIRTUEMART_ORDER_PRINT_TOTAL', $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_total,$this->user_currency_id))) . "\n";
echo sprintf("%'-64.64s", '') . "\n";
echo vmText::_('COM_VIRTUEMART_ORDER_ITEM') . "\n";
foreach ($this->orderDetails['items'] as $item) {
    echo "\n";
    echo $item->product_quantity . ' X ' . $item->order_item_name . ' (' . strtoupper(vmText::_('COM_VIRTUEMART_SKU')) . $item->order_item_sku . ')' . "\n";
    //if (!empty($item->product_attribute)) {
	if (!class_exists('VirtueMartModelCustomfields'))
	    require(VMPATH_ADMIN . DS . 'models' . DS . 'customfields.php');
	$product_attribute = VirtueMartModelCustomfields::CustomsFieldOrderDisplay($item, 'FE');
	echo "\n" . strip_tags($product_attribute) . "\n";
    //}
    if (!empty($item->product_basePriceWithTax) && $item->product_basePriceWithTax != $item->product_final_price) {
	echo $item->product_basePriceWithTax . "\n";
    }

    echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') . $item->product_final_price;
    if (VmConfig::get('show_tax')) {
	echo ' (' . vmText::_('COM_VIRTUEMART_ORDER_PRINT_PRODUCT_TAX') . ':' . $this->currency->priceDisplay($item->product_tax,$this->user_currency_id) . ')' . "\n";
    }
    echo "\n";
}
echo sprintf("%'-64.64s", '');
echo "\n";

// Coupon
if (!empty($this->orderDetails['details']['BT']->coupon_code)) {
    echo vmText::_('COM_VIRTUEMART_COUPON_DISCOUNT') . ':' . $this->orderDetails['details']['BT']->coupon_code . ' ' . vmText::_('COM_VIRTUEMART_PRICE') . ':' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->coupon_discount,$this->user_currency_id);
    echo "\n";
}



foreach ($this->orderDetails['calc_rules'] as $rule) {
    if ($rule->calc_kind == 'DBTaxRulesBill') {
	echo $rule->calc_rule_name . $this->currency->priceDisplay($rule->calc_amount, $this->user_currency_id) . "\n";
    } elseif ($rule->calc_kind == 'taxRulesBill') {
	echo $rule->calc_rule_name . ' ' . $this->currency->priceDisplay($rule->calc_amount,$this->user_currency_id) . "\n";
    } elseif ($rule->calc_kind == 'DATaxRulesBill') {
	echo $rule->calc_rule_name . ' ' . $this->currency->priceDisplay($rule->calc_amount,$this->user_currency_id) . "\n";
    }
}


echo strtoupper(vmText::_('COM_VIRTUEMART_ORDER_PRINT_SHIPPING')) . ' (' . strip_tags(str_replace("<br />", "\n", $this->orderDetails['shipmentName'])) . ' ) ' . "\n";
echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') . ' : ' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_shipment,$this->user_currency_id);
if (VmConfig::get('show_tax')) {
    echo ' (' . vmText::_('COM_VIRTUEMART_ORDER_PRINT_TAX') . ' : ' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_shipment_tax,$this->user_currency_id) . ')';
}
echo "\n";
echo strtoupper(vmText::_('COM_VIRTUEMART_ORDER_PRINT_PAYMENT')) . ' (' . strip_tags(str_replace("<br />", "\n", $this->orderDetails['paymentName'])) . ' ) ' . "\n";
echo vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL') . ':' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_payment,$this->user_currency_id);
if (VmConfig::get('show_tax')) {
    echo ' (' . vmText::_('COM_VIRTUEMART_ORDER_PRINT_TAX') . ' : ' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_payment_tax,$this->user_currency_id) . ')';
}
echo "\n";

echo sprintf("%'-64.64s", '') . "\n";
// total order
echo vmText::_('COM_VIRTUEMART_MAIL_SUBTOTAL_DISCOUNT_AMOUNT') . ' : ' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_billDiscountAmount,$this->user_currency_id) . "\n";

echo strtoupper(vmText::_('COM_VIRTUEMART_ORDER_PRINT_TOTAL')) . ' : ' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_total,$this->user_currency_id) . "\n";
if (VmConfig::get('show_tax')) {
    echo ' (' . vmText::_('COM_VIRTUEMART_ORDER_PRINT_TAX') . ' : ' . $this->currency->priceDisplay($this->orderDetails['details']['BT']->order_billTaxAmount,$this->user_currency_id) . ')' . "\n";
}
echo "\n";

