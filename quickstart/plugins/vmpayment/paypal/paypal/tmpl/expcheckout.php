<?php
/**
 *
 * Paypal payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: paypal.php 7217 2013-09-18 13:42:54Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004 - 2017 Virtuemart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See /administrator/components/com_virtuemart/COPYRIGHT.php for copyright notices and details.
 *
 * http://virtuemart.net
 */

if(!class_exists('vmPPButton')) require(VMPATH_PLUGINS .'/vmpayment/paypal/paypal/tmpl/ppbuttons.php');

$paypalInterface = $viewData['paypalInterface'];

vmJsApi::css(  'paypal','plugins/vmpayment/paypal/paypal/assets/css/');

?>

<div class="pp-wrap">
		<?php
		if ($viewData['sandbox'] ) { ?>
	<span style="color:red;font-weight:bold">Sandbox (<?php echo $viewData['virtuemart_paymentmethod_id'] ?>)</span>
		<?php
		}
		if(empty($viewData['offer_credit'])){ ?>
	<div class="pp-express">
		<?php echo vmPPButton::renderCheckoutButton($viewData['method']); ?>
	</div>
	<div class="clear"></div>
		<?php
		} else { ?>
	<div class="pp-credit">
		<?php
		echo vmPPButton::renderCheckoutButton($viewData['method']);
		static $frame= false;
		?>
		<button class="pp-mark-credit-modal" >
			<img src="https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png" />
		</button>
			<?php if($frame){ ?>
		<div id="paypal_offer_frame"></div>
			<?php
			$frame = false;
			}?>
	</div>
		<?php
		} ?>
</div>
<div class="clear"></div>
<?php
$fcredit = 'var ppframeCredit = jQuery("<div></div>")';
$fcredit .= ".html('<iframe id=\"paypal_offer_frame_credit\" style=\"border: 0px;\" src=\"' + ppurlcredit + '\" width=\"100%\" height=\"100%\"></iframe>')";
$fcredit .= '.dialog({
	   autoOpen: false,
	   closeOnEscape: true,
	   modal: true,
	   height: heightsiz,
	   width: widthsiz,
	   title: "Paypal Credit offer"
   });';
$j = '
jQuery(document).ready( function() {
	var page = Virtuemart.vmSiteurl + "index.php?option=com_virtuemart&view=plugin&vmtype=vmpayment&name=paypal&tmpl=component";
	var heightsiz = jQuery(window).height() * 0.9;
	var widthsiz = jQuery(window).width() * 0.8;

	var ppurlcredit = page+"&action=getPayPalCreditOffer";

	var bindClose = function(){
		ppiframe = jQuery("#paypal_offer_frame_credit");
		closElem = ppiframe.contents().find("a").filter(\':contains("Close")\');;
		closElem.on("click", function() {
			jQuery(".ui-dialog-titlebar-close").click();
		});
	};

	jQuery(".pp-mark-credit-modal").on("click", function(){
	'.$fcredit.'
		ppframeCredit.dialog("open");
		setTimeout(bindClose,2000);
	});

	return false;
});
';

vmJsApi::addJScript('paypal_offer',$j);