<?php
/**
 *
 * Realex User Field plugin
 *
 * @author Valerie Isaksen
 * @version $Id: creditcardlist.php 9294 2016-09-21 09:56:21Z Milbo $
 * @package VirtueMart
 * @subpackage userfield
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
defined('_JEXEC') or die();
$doc = JFactory::getDocument();
//$doc->addScript(JURI::root(true) . '/plugins/vmuserfield/realex_hpp_api/realex_hpp_api/assets/js/site.js');
$doc->addStyleSheet(JURI::root(true) . '/plugins/vmuserfield/realex_hpp_api/realex_hpp_api/assets/css/realex.css');
$storedCreditCards = $viewData['storedCreditCards'];
?>
<div class="vmuserfield_cardinfo realex_cardinfo">

	<?php
	$i = 1;
	foreach ($storedCreditCards as $storedCreditCard) {
		?>
		<div class="releax_show_cc">

			<div class="vmpayment_cc_info vmpayment_creditcardtype">
				<span class="vmpayment_label"><label
						for="creditcardtype"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CCTYPE'); ?></label></span>
			<span class="vmpayment_creditcardtype_<?php echo $storedCreditCard->realex_hpp_api_saved_pmt_type ?>">
			<?php
			echo $storedCreditCard->realex_hpp_api_saved_pmt_type;
			?>
			</span>
			</div>
			<div class="vmpayment_cc_info vmpayment_cc_type">
				<span class="vmpayment_label"><label
						for="cc_type"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CCNUM'); ?></label></span>
				<?php
				echo $storedCreditCard->realex_hpp_api_saved_pmt_digits;
				?>

			</div>

			<div class="vmpayment_cc_info vmpayment_cc_date">
				<span class="vmpayment_label"><label
						for="creditcardtype"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_EXPDATE'); ?></label></span>
				<?php
				$exp_date = $this->explodeExpDate($storedCreditCard->realex_hpp_api_saved_pmt_expdate);
				if (count($exp_date) == 2) {
					echo shopfunctions::listMonths('cc_expire_month_' . $storedCreditCard->id, $exp_date['mm'], "class=\"inputbox vm-chzn-select\" style=\"width: 100px;\"", 'm');
					echo shopfunctions::listYears('cc_expire_year_' . $storedCreditCard->id, $exp_date['yy'], null, null, "class=\"inputbox vm-chzn-select\" style=\"width: 100px;\"  onchange=\"var month = document.getElementById('cc_expire_month_'" . $storedCreditCard->virtuemart_paymentmethod_id . "); if(!CreditCardisExpiryDate(month.value,this.value, '" . $storedCreditCard->virtuemart_paymentmethod_id . "')){this.value='';month.value='';}\" ",'y');
				}
				?>
				<div id="cc_expiredate_errormsg"></div>

			</div>
			<div class="vmpayment_cc_info vmpayment_cc_name">

				<span class="vmpayment_label"><label
						for="cc_name"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CCNAME'); ?></label></span>

				<input type="text" size="30" class="inputbox" id="cc_name"
				       name="cc_name_<?php echo $storedCreditCard->id; ?>"
				       value="<?php echo $storedCreditCard->realex_hpp_api_saved_pmt_name; ?>"
				       autocomplete="off"
				       onchange="ccError=razCCerror(<?php echo $storedCreditCard->virtuemart_paymentmethod_id; ?>);
					       CheckCreditCardNumber(this . value, <?php echo $storedCreditCard->virtuemart_paymentmethod_id; ?>);
					       if (!ccError) {
					       this.value='';}"/>

				<div id="cc_cardname_errormsg"></div>

			</div>
			<?php if ($viewData['deleteUpdateAuthorized']) { ?>
				<div class="releax_delete_cc">
					<?php
					$checked_deleted = VmHtml::checkbox ('realex_card_delete_ids', 0, $storedCreditCard->id, 0, 'aria-invalid="false"',false);
					echo $checked_deleted ?><?php echo vmText::_('VMUSERFIELD_REALEX_HPP_API_DELETE_CARD') ?>
				</div>
				<div class="releax_update_cc">
					<?php
					$checked_updated = VmHtml::checkbox ('realex_card_update_ids', 0, $storedCreditCard->id, 0, 'aria-invalid="false"',false);
					echo $checked_updated ?><?php echo vmText::_('VMUSERFIELD_REALEX_HPP_API_UPDATE_CARD') ?>
				</div>
			<?php
			}

			$i++;

			?>
		</div>
	<?php

	}
	?>
</div>


