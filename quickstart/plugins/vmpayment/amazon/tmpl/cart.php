<?php
/**
 *
 * Layout for the AMAZON cart
 * @version $Id$
 * @package    VirtueMart
 * @subpackage Cart
 * @author Valerie Isaksen
 *
 * @link https://virtuemart.net
 * @copyright Copyright (c) 2004 - 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidation');

?>
	<div id="amazonShipmentNotFoundDiv">

			<div id="system-message-container">
				<dl id="system-message">
					<?php if (isset($this->found_shipment_method) and !$this->found_shipment_method) { ?>
					<dt class="info">info</dt>
					<dd class="info message">
						<ul>
							<li><?php echo JText::_('VMPAYMENT_AMAZON_UPDATECART_SHIPMENT_NOT_FOUND'); ?></li>
						</ul>
					</dd>
						<?php
					}
					?>
				</dl>
			</div>

	</div>
	<div id="amazonErrorDiv">
	</div>

	<div id="amazonLoading"></div>

	<div class="cart-view" id="cart-view">
		<div id="amazonHeader">
			<div class="width50 floatleft">
				<h1><?php echo vmText::_('VMPAYMENT_AMAZON_PAY_WITH_AMAZON'); ?></h1>
				<div class="payments-signin-button"></div>
			</div>
			<div class="width50 floatleft right">
				<?php // Continue Shopping Button
				if (!empty($this->continue_link_html)) {
					echo $this->continue_link_html;
				}
				?>
				<div>
					<a href="#" id="leaveAmazonCheckout"><?php echo vmText::_('VMPAYMENT_AMAZON_LEAVE_PAY_WITH_AMAZON') ?></a>
				</div>
			</div>
			<div class="clear"></div>
		</div>
		<div id="amazonAddressBookWalletWidgetDiv">
			<div id="amazonAddressBookWidgetDiv" class="width50 floatleft"></div>

			<div id="amazonWalletWidgetDiv" class="width50 floatleft"></div>
		</div>
		<div class="clear"></div>
		<div id="amazonChargeNowWarning"></div>

		<div class="clear"></div>
		<div id="amazonCartDiv">
			<div id="signInButton"></div>

			<?php

			if ($this->checkout_task) {
				$taskRoute = '&task=' . $this->checkout_task;
			} else {
				$taskRoute = '';
			}

			if ($this->cart->getDataValidated()) {
				$this->readonly_cart = true;
			} else {
				$this->readonly_cart = false;
			}

			?>
			<form method="post" id="checkoutForm" name="checkoutForm"
			      action="<?php echo JRoute::_('index.php?option=com_virtuemart&view=cart' . $taskRoute, $this->useXHTML, $this->useSSL); ?>">

				<div id="amazonShipmentsDiv"><?php
					//if (!$this->readonly_cart) {
					if (!$this->cart->automaticSelectedShipment or !$this->readonly_cart) {
						?>
						<?php echo $this->loadTemplate('shipment'); ?>
					<?php

					}
					?>
				</div>
				<?php
				//}
				// This displays the pricelist MUST be done with tables, because it is also used for the emails
				echo $this->loadTemplate('pricelist');

				/*if (!empty($this->checkoutAdvertise)) {
					?>
					<div id="checkout-advertise-box"> <?php
					foreach ($this->checkoutAdvertise as $checkoutAdvertise) {
						?>
						<div class="checkout-advertise">
							<?php echo $checkoutAdvertise; ?>
						</div>
					<?php
					}
					?></div><?php
				}*/

				echo $this->loadTemplate('cartfields');

				?>
					<div id="amazon_checkout">

						<?php
						echo $this->checkout_link_html;
						?>
					</div>

				<input type='hidden' id='STsameAsBT' name='STsameAsBT' value='<?php echo $this->cart->STsameAsBT; ?>'/>
				<input type='hidden' name='virtuemart_paymentmethod_id' value='<?php echo $this->cart->virtuemart_paymentmethod_id; ?>'/>
				<input type='hidden' name='order_language' value='<?php echo $this->order_language; ?>'/>
				<input type='hidden' name='task' value='updatecart'/>
				<input type='hidden' name='option' value='com_virtuemart'/>
				<input type='hidden' name='view' value='cart'/>
			</form>
		</div>

		<?php
		vmJsApi::addJScript('updDynamicListeners',"
if (typeof Virtuemart.containerSelector === 'undefined') Virtuemart.containerSelector = '#cart-view';
if (typeof Virtuemart.container === 'undefined') Virtuemart.container = jQuery(Virtuemart.containerSelector);

jQuery(document).ready(function() {
	if (Virtuemart.container)
		Virtuemart.updDynFormListeners();
}); ");

		vmJsApi::addJScript('vm.checkoutFormSubmit',"
Virtuemart.bCheckoutButton = function(e) {
	e.preventDefault();
	jQuery(this).vm2front('startVmLoading');
	jQuery(this).attr('disabled', 'true');
	jQuery(this).removeClass( 'vm-button-correct' );
	jQuery(this).addClass( 'vm-button' );
	jQuery(this).fadeIn( 400 );
	var name = jQuery(this).attr('name');
	var div = '<input name=\"'+name+'\" value=\"1\" type=\"hidden\">';

	jQuery('#checkoutForm').append(div);
	//Virtuemart.updForm();
	jQuery('#checkoutForm').submit();
}

jQuery(document).ready(function($) {
	jQuery(this).vm2front('stopVmLoading');
	var el = jQuery('#checkoutFormSubmit');
	el.unbind('click dblclick');
	el.on('click dblclick',Virtuemart.bCheckoutButton);
});
	");


		$this->addCheckRequiredJs();
		?>
		<div style="display:none;" id="cart-js">
			<?php echo vmJsApi::writeJS(); ?>
		</div>
	</div>

<?php vmTime('Cart view Finished task ', 'Start'); ?>