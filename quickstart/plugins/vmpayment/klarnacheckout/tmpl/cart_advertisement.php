<?php

defined('_JEXEC') or die('Restricted access');

/**
 * @author Valérie Isaksen
 * @version $Id: cart_advertisement.php 7862 2014-04-25 09:26:53Z alatak $
 * @package VirtueMart
 * @subpackage payment
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

$css =".totalInPaymentCurrency {display:none;}\n";
if ($viewData ['payment_form_position']=='right' or $viewData ['payment_form_position'] =='specific') {
	vmJsApi::css('klarnacheckout', 'plugins/vmpayment/klarnacheckout/assets/css');
}

?>

<?php
$class='';
if ($viewData ['message'] ) {
	$class='disableSnippet';
}
?>

<div id="kco-payment-method" >
	<div id="kco-shipment-method"><?php echo $viewData ['message']; ?>  </div>
<?php if ($viewData ['snippet'] )  { ?>
<div class="<?php echo $class ?>"><?php echo $viewData ['snippet']; ?>  </div>
<?php }


?>

</div>

<input type="hidden" name="SnippetDisplayed" value="1">