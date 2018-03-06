<?php
defined('_JEXEC') or die();

/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: render_pluginname.php 8200 2014-08-14 11:09:44Z alatak $
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
vmJsApi::jQuery();
vmJsApi::chosenDropDowns();
?>
<span class="vmpayment">
	<?php
	if (!empty($viewData['logo'])) {
		?>
		<span class="vmCartPaymentLogo">
			<?php echo $viewData['logo'] ?>
        </span>
	<?php
	}
	?>
	<span class="vmpayment_name"><?php echo $viewData['payment_name'] ?> </span>
	<?php
	if ($viewData['shop_mode'] == 'sandbox') {
		?>
		<span style="color:red;font-weight:bold">Sandbox (<?php echo $viewData['virtuemart_paymentmethod_id'] ?>)</span>
	<?php
	}
	?>
	<?php
	if (!empty($viewData['payment_description'])) {
		?>
		<span class="vmpayment_description"><?php echo $viewData['payment_description'] ?> </span>
	<?php
	}
	?>
	<?php if (isset($viewData['extraInfo']) AND isset($viewData['extraInfo']['cc_number']) AND $viewData['extraInfo']['cc_number']) { ?>
		<div class="vmpayment_selected_cc">
			<?php
			if ($viewData['extraInfo']['cc_type']) {
				echo "(" . vmText::_('VMPAYMENT_REALEX_HPP_API_CC_' . $viewData['extraInfo']['cc_type']) . ' ';
				if ($viewData['where'] != 'order') {
					echo $viewData['extraInfo']['cc_number'];
				} else {
					echo $viewData['extraInfo']['cc_number'];
				}
				if ($viewData['extraInfo']['cc_expire_month']) {
					echo " " . $viewData['extraInfo']['cc_expire_month'] . '/' . $viewData['extraInfo']['cc_expire_year'];
				}
				if ($viewData['extraInfo']['cc_name']) {
					echo ' (' . $viewData['extraInfo']['cc_name'] . ')';
				}
				?>
				)
			<?php
			}
			?>

		</div>
	<?php
	}
	?>
	<?php if (isset($viewData['remote_save_card']) AND isset($viewData['extraInfo']['remote_save_card']) AND $viewData['extraInfo']['remote_save_card']) {
		?>
		<div class="vmpayment_description">
			<?php
			echo vmText::_('VMPAYMENT_REALEX_HPP_API_SAVE_CARD_DETAILS_YES');
			?>

		</div>
	<?php
	}
	?>
</span>



