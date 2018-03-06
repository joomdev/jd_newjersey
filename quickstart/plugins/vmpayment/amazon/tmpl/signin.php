<?php
defined('_JEXEC') or die();

/**
 * @author Valérie Isaksen
 * @version $Id: signin.php 8431 2014-10-14 14:11:46Z alatak $
 * @package VirtueMart
 * @subpackage vmpayment
 * @copyright Copyright (C) 2004-Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.   - All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */
?>
<?php

JHtml::_('behavior.tooltip');
vmJsApi::jPrice();
static $jsSILoaded = false;
if (!$jsSILoaded) {
	$doc = JFactory::getDocument();

	$signInButton = '<div class=\"amazonSignInButton\"><div id=\"payWithAmazonDiv\" >'
// <img src=\"' . $viewData['buttonWidgetImageURL'] . '\" style=\"cursor: pointer;\"/></div>

.'<div id=\"amazonSignInErrorMsg\"></div></div>';

	vmJsApi::addJScript('/plugins/vmpayment/amazon/assets/js/amazon.js');
	if ($viewData['include_amazon_css']) {
		$doc->addStyleSheet(JURI::root(true) . '/plugins/vmpayment/amazon/assets/css/amazon.css');
	}
	$renderAmazonAddressBook = $viewData['renderAmazonAddressBook'] ? 'true' : 'false';

	$js = "
jQuery(document).ready(function($) {

		jQuery( '.amazonSignIn' ).remove();
		jQuery( '" . $viewData['sign_in_css'] . "' ).append('<div class=\"amazonSignIn\" ></div>');
		jQuery( '.amazonSignIn' ).append('<div class=\"amazonSignTip\">" . vmText::_('VMPAYMENT_AMAZON_SIGNIN_TIP', true) . "</div>');
		jQuery( '.amazonSignIn' ).append('" . $signInButton . "');
		jQuery( '.amazonSignIn' ).append('<div class=\"amazonSignTip\" id=\"amazonSignOr\"><span>" . vmText::_('VMPAYMENT_AMAZON_SIGNIN_OR', true) . "</span></div>');
		amazonPayment.showAmazonButton('" . $viewData['sellerId'] . "', '" . $viewData['redirect_page'] . "', " . $renderAmazonAddressBook . ");

});
";


	vmJsApi::addJScript('vm.amazonSignIn', $js);


	if ($viewData['layout'] == 'cart') {

		$js = "
jQuery(document).ready( function($) {
	jQuery('#leaveAmazonCheckout').click(function(){
		amazonPayment.leaveAmazonCheckout();
	});
});
";
		vmJsApi::addJScript('vm.amazonLeavecheckout', $js);

		if (vRequest::getWord('view') == 'cart') {
			$js = "
	jQuery(document).ready(function($) {
	jQuery('#checkoutFormSubmit').attr('disabled', 'true');
	jQuery('#checkoutFormSubmit').removeClass( 'vm-button-correct' );
	jQuery('#checkoutFormSubmit').addClass( 'vm-button' );
	jQuery('#checkoutFormSubmit').text( '" . vmText::_('VMPAYMENT_AMAZON_CLICK_PAY_AMAZON', true) . "' );
	});
";
			vmJsApi::addJScript('vm.amazonSubmitform', $js);
		}


	}
}
?>
