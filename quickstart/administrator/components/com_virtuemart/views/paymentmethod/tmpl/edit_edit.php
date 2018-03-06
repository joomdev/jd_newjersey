<?php
/**
*
* Description
*
* @package	VirtueMart
* @subpackage Paymentmethod
* @author Max Milbers
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: edit_edit.php 9413 2017-01-04 17:20:58Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
?>

<div class="col50">
    <fieldset>
        <legend><?php echo vmText::_('COM_VIRTUEMART_PAYMENTMETHOD'); ?></legend>
        <table class="admintable">
		<?php echo VmHTML::row('input','COM_VIRTUEMART_PAYMENTMETHOD_FORM_NAME','payment_name',$this->payment->payment_name,'class="required"'); ?>
		<?php echo VmHTML::row('input','COM_VIRTUEMART_SLUG','slug',$this->payment->slug); ?>
     	<?php echo VmHTML::row('booleanlist','COM_VIRTUEMART_PUBLISHED','published',$this->payment->published); ?>
		<?php echo VmHTML::row('textarea','COM_VIRTUEMART_PAYMENT_FORM_DESCRIPTION','payment_desc',$this->payment->payment_desc); ?>
		<?php echo VmHTML::row('raw','COM_VIRTUEMART_PAYMENT_CLASS_NAME', $this->vmPPaymentList ); ?>
		<?php echo VmHTML::row('raw','COM_VIRTUEMART_PAYMENTMETHOD_FORM_SHOPPER_GROUP', $this->shopperGroupList ); ?>
		<?php echo VmHTML::row('input','COM_VIRTUEMART_LIST_ORDER','ordering',$this->payment->ordering,'class="inputbox"','',4,4); ?>
		<?php echo VmHTML::row('raw', 'COM_VIRTUEMART_CURRENCY', $this->currencyList); ?>
	    <?php
	    if ($this->showVendors()) {
			echo VmHTML::row('raw', 'COM_VIRTUEMART_VENDOR', $this->vendorList);
	    }
		if($this->showVendors ){
			echo VmHTML::row('checkbox','COM_VIRTUEMART_SHARED', 'shared', $this->payment->shared );
		}
	    ?>
          </table>
    </fieldset>
</div>

