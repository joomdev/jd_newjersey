<?php
/**
 *
 * Paypal payment plugin
 *
 * @author Max Milbers
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
?>
<div style="margin: 8px;">
    <?php
    if ($viewData['sandbox'] ) {
		?>
        <span style="color:red;font-weight:bold">Sandbox (<?php echo $viewData['virtuemart_paymentmethod_id'] ?>)</span>
		<?php
	}

if(empty($viewData['offer_credit'])) {
	?><div class="pp-logo"><?php
	echo vmPPButton::renderMarkAcceptance();
	?></div><?php
} else {
	?>
    <div class="pp-mark-credit"><?php
	    echo vmPPButton::renderMarkCredit();
	?>
    </div><?php
}
    ?>
</div>