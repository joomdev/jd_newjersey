<?php
/**
*
* Order detail view
*
* @package	VirtueMart
* @subpackage Orders
* @author Oscar van Eijk, Valerie Isaksen
* @link https://virtuemart.net
* @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* VirtueMart is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* @version $Id: details.php 9523 2017-05-04 10:23:55Z Milbo $
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');
vmJsApi::css('vmpanels');
if($this->print){
	?>

		<body onload="javascript:print();">
		<div class="vm-orders-vendor-image"><img src="<?php  echo JURI::root() . $this-> vendor->images[0]->file_url ?>"></div>
		<h2><?php  echo $this->vendor->vendor_store_name; ?></h2>
		<?php  echo $this->vendor->vendor_name .' - '.$this->vendor->vendor_phone ?>
		<h1><?php echo vmText::_('COM_VIRTUEMART_ACC_ORDER_INFO'); ?></h1>
		<div class="spaceStyle vm-orders-order print">
		<?php
		echo $this->loadTemplate('order');
		?>
		</div>

		<div class="spaceStyle vm-orders-items print">
		<?php
		echo $this->loadTemplate('items');
		?>
		</div>
		<?php if(!class_exists('VirtuemartViewInvoice')) require_once(VMPATH_SITE .DS. 'views'.DS.'invoice'.DS.'view.html.php');
		echo VirtuemartViewInvoice::replaceVendorFields($this->vendor->vendor_letter_footer_html, $this->vendor); ?>
		</body>
		<?php
} else {

	?>
<div class="vm-wrap">
	<div class="vm-orders-information">
	<h1><?php echo vmText::_('COM_VIRTUEMART_ACC_ORDER_INFO'); ?>

	<?php

	/* Print view URL */
	$details_link = "<a href=\"javascript:void window.open('$this->details_url', 'win2', 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no');\"  >";
	//$details_link .= '<span class="hasTip print_32" title="' . vmText::_('COM_VIRTUEMART_PRINT') . '">&nbsp;</span></a>';
	$button = 'system/printButton.png';
	$details_link .= JHtml::_('image',$button, vmText::_('COM_VIRTUEMART_PRINT'), NULL, true);
	$details_link  .=  '</a>';
	echo $details_link;
	$this->orderdetails['details']['BT']->invoiceNumber = VmModel::getModel('orders')->getInvoiceNumber($this->orderdetails['details']['BT']->virtuemart_order_id);
	echo shopFunctionsF::getInvoiceDownloadButton($this->orderdetails['details']['BT']) ?>
	</h1>
<?php if($this->order_list_link){ ?>
	<div class='spaceStyle'>
	    <div class="floatright">
		<a href="<?php echo $this->order_list_link ?>" rel="nofollow"><?php echo vmText::_('COM_VIRTUEMART_ORDERS_VIEW_DEFAULT_TITLE'); ?></a>
	    </div>
	    <div class="clear"></div>
	</div>
<?php }?>
	<div class="spaceStyle vm-orders-order">
	<?php
	echo $this->loadTemplate('order');
	?>
	</div>

	<div class="spaceStyle vm-orders-items">
	<?php

	$tabarray = array();

	$tabarray['items'] = 'COM_VIRTUEMART_ORDER_ITEM';
	$tabarray['history'] = 'COM_VIRTUEMART_ORDER_HISTORY';

	shopFunctionsF::buildTabs ( $this, $tabarray); ?>
	</div>
	<br clear="all"/><br/>
	</div>
</div>	
	<?php
}

?>






