<?php  defined('_JEXEC') or die();

/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: redirect_form.php 8200 2014-08-14 11:09:44Z alatak $
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
?>

<input type="radio" name="virtuemart_paymentmethod_id"
       id="payment_id_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>"
       value="<?php echo $viewData['virtuemart_paymentmethod_id']; ?>" <?php echo $viewData ['checked']; ?>>
<label for="payment_id_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>">

    <span class="vmpayment">
        <?php if (!empty($viewData['payment_logo'])) { ?>
	        <span class="vmpayment_logo"><?php echo $viewData ['payment_logo']; ?> </span>
        <?php } ?>
	    <span class="vmpayment_name"><?php echo $viewData['payment_name']; ?></span>


	    <?php if (!empty($viewData['payment_cost'])) { ?>
		    <span
			    class="vmpayment_cost"><?php echo vmText::_('COM_VIRTUEMART_PLUGIN_COST_DISPLAY') . $viewData['payment_cost'] ?></span>
	    <?php } ?>
    </span>

	<?php
	/*
	if (!empty($viewData['creditcardsDropDown'])) { ?>
		<div class="creditcardsDropDown">
			<?php
			echo $viewData['creditcardsDropDown'];
			?>
		</div>
	<?php
	}
	*/
	if (!empty($viewData['offerSaveCard'])) {
		?>
		<div class="realex_offerSaveCard">
			<?php
			echo $viewData['offerSaveCard'];
			?>
		</div>
	<?php
	}
	?>
</label>
