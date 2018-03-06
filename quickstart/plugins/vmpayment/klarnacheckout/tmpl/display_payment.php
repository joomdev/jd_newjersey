<?php  defined ('_JEXEC') or die();
/**
 * @author Valérie Isaksen
 * @version $Id: display_payment.php 7352 2013-11-08 13:06:41Z alatak $
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

?>
<?php
if ( $viewData['payment_tooltip']) {
	?>
	<script async src="https://cdn.klarna.com/1.0/code/client/all.js"></script>
<?php
}
$dynUpdate='';
if( VmConfig::get('oncheckout_ajax',false)) {
	$dynUpdate=' data-dynamic-update="1" ';
	$submitForm = '
	jQuery("#payment_id_'.$viewData['plugin']->virtuemart_paymentmethod_id.'").click(function() {
        jQuery("#checkoutForm").submit();
	});
';
	//vmJsApi::addJScript('vm.kco_submitForm', $submitForm);
}
?>
<input type="radio" <?php echo $dynUpdate; ?> name="virtuemart_paymentmethod_id"
       id="payment_id_<?php echo $viewData['plugin']->virtuemart_paymentmethod_id; ?>"
       value="<?php echo $viewData['plugin']->virtuemart_paymentmethod_id; ?>" <?php echo $viewData ['checked']; ?>>
<label for="payment_id_<?php echo $viewData['plugin']->virtuemart_paymentmethod_id; ?>">

    <span class="vmpayment">
        <?php if (!empty($viewData['payment_logo'] )) { ?>

	        <span class="vmCartPaymentLogo">

		        <?php echo $viewData ['payment_logo']; ?> </span>

        <?php } ?>
	    <span class="vmpayment_name"><?php echo $viewData['plugin']->payment_name; ?></span>
	    <?php if (!empty($viewData['plugin']->payment_desc )) { ?>
		    <span class="vmpayment_description"><?php echo $viewData['plugin']->payment_desc; ?></span>
	    <?php } ?>
	    <?php if (!empty($viewData['payment_cost']  )) { ?>
		    <span class="vmpayment_cost"><?php echo vmText::_ ('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') .  $viewData['payment_cost']  ?></span>
	    <?php } ?>
    </span>

</label>