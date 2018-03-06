<?php
/**
 * Admin form for the checkout configuration settings
 *
 * @package	VirtueMart
 * @subpackage Config
 * @author Oscar van Eijk
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2011 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * @version $Id: default_checkout.php 9653 2017-10-18 12:59:33Z Milbo $
 */
defined('_JEXEC') or die('Restricted access');

?>
<fieldset>
	<legend><?php echo vmText::_('COM_VIRTUEMART_ADMIN_CFG_CHECKOUT_SETTINGS'); ?></legend>
	<table class="admintable">
		<?php
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_ADDTOCART_POPUP','addtocart_popup',VmConfig::get('addtocart_popup',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_CFG_POPUP_REL','popup_rel',VmConfig::get('popup_rel',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CHECKOUT_OPC','oncheckout_opc',VmConfig::get('oncheckout_opc',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_CFG_OPC_AJAX','oncheckout_ajax',VmConfig::get('oncheckout_ajax',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_STEPS','oncheckout_show_steps',VmConfig::get('oncheckout_show_steps',1));
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_SHIPMENT',$this->listShipment,'set_automatic_shipment','','virtuemart_shipmentmethod_id','shipment_name',VmConfig::get('set_automatic_shipment',0));
		echo VmHTML::row('genericlist','COM_VIRTUEMART_ADMIN_CFG_AUTOMATIC_PAYMENT',$this->listPayment,'set_automatic_payment','','virtuemart_paymentmethod_id','payment_name',VmConfig::get('set_automatic_payment',0));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_CFG_AGREE_TERMS_ONORDER','agree_to_tos_onorder',VmConfig::get('agree_to_tos_onorder',1));

		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_PRODUCTIMAGES','oncheckout_show_images',VmConfig::get('oncheckout_show_images',1));

		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_ONCHECKOUT_CHANGE_SHOPPER','oncheckout_change_shopper',VmConfig::get('oncheckout_change_shopper',1));

		echo VmHTML::row('genericlist','COM_VIRTUEMART_CFG_DELDATE_INV',$this->osDel_Options,'del_date_type','class="inputbox"', 'order_status_code', 'order_status_name', VmConfig::get('del_date_type',array('m')), 'del_date_type',true);

		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_ONCHECKOUT_SHOW_REGISTER','oncheckout_show_register',VmConfig::get('oncheckout_show_register',1));
		echo VmHTML::row('checkbox','COM_VIRTUEMART_ADMIN_ONCHECKOUT_ONLY_REGISTERED','oncheckout_only_registered',VmConfig::get('oncheckout_only_registered',0));
		//echo VmHTML::row('checkbox','COM_VM_CFG_PROVIDE_ORDER_GUEST_LINK','orderGuestLink',VmConfig::get('orderGuestLink',0));

		$opt = array(
		'none' => vmText::_('COM_VIRTUEMART_NONE'),
		'registered_only' => vmText::_('COM_VM_CFG_ORDERTRACKING_REGISTERED'),
		'guests' => vmText::_('COM_VM_CFG_ORDERTRACKING_GUESTS'),
		'guestlink' => vmText::_('COM_VM_CFG_ORDERTRACKING_GUESTLINK')
		);
		echo VmHTML::row('genericlist','COM_VM_CFG_ORDERTRACKING',$opt, 'ordertracking', '', 'value', 'text', VmConfig::get('ordertracking','guests'));
		?>

	</table>
</fieldset>