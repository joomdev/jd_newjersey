<?php
/**
 *
 * Layout for the AMAZON display wallet
 * @version $Id$
 * @package    VirtueMart
 * @subpackage Cart
 * @author Valerie Isaksen
 *
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2010 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

if ($viewData['include_amazon_css']) {
	$document = JFactory::getDocument()->addStyleSheet(JURI::root(true) . '/plugins/vmpayment/amazon/assets/css/amazon.css');
}
JHtml::_('behavior.formvalidation');

		$js = "
jQuery(document).ready( function($) {
	amazonPayment.showAmazonWallet();
});
";
		vmJsApi::addJScript('vm.showAmazonWallet', $js);
?>
<h3><?php echo vmText::_('VMPAYMENT_AMAZON_INVALIDPAYMENTMETHOD') ?></h3>
<p><?php echo vmText::_('VMPAYMENT_AMAZON_INVALIDPAYMENTMETHOD_CLICK_DECLINE') ?></p>
	<div id="amazonWalletWidgetDiv" ></div>
<form method="post" id="updateOrderId" name="updateOrderForm" disabled action="<?php echo JRoute::_('index.php?option=com_virtuemart' , $viewData['useXHTML'], $viewData['useSSL']); ?>">
	<button type="submit" id="checkoutFormSubmit"  value="1" class="vm-button"  ><span> <?php echo vmText::_('COM_VIRTUEMART_CHECKOUT_TITLE') ?> </span> </button>
	<input type='hidden' name='type' value='vmpayment'/>
	<input type='hidden' name='view' value='plugin'/>
	<input type='hidden' name='name' value='amazon'/>
	<input type='hidden' name='action' value='onInvalidPaymentNewAuthorization'/>
	<input type='hidden' name='order_number' value='<?php echo $viewData['order_number'] ?>'/>
	<input type='hidden' name='virtuemart_paymentmethod_id' value='<?php echo $viewData['virtuemart_paymentmethod_id'] ?>'/>
	<input type='hidden' name='format' value='html'/>
</form>

