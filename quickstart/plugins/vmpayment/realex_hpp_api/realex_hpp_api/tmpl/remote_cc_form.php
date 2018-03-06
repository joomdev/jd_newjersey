<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: remote_cc_form.php 9420 2017-01-12 09:35:36Z Milbo $
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
defined('_JEXEC') or die();

$ccData = $viewData['ccData'];

JHTML::_('behavior.tooltip');
JHTML::script('vmcreditcard.js', 'components/com_virtuemart/assets/js/', false);
vmLanguage::loadJLang('com_virtuemart', true);
vmJsApi::jCreditCard();
vmJsApi::jQuery();
vmJsApi::chosenDropDowns();
vmJsApi::addJScript( '/plugins/vmpayment/realex_hpp_api/realex_hpp_api/assets/js/site.js');
vmJsApi::css( 'realex','plugins/vmpayment/realex_hpp_api/realex_hpp_api/assets/css/');

vmJsApi::addJScript ('vmRealexSumit',"

	jQuery(document).ready(function($) {
	jQuery(this).vm2front('stopVmLoading');
	jQuery('#checkoutRealexFormSubmitButton').bind('click dblclick', function(e){
	jQuery(this).vm2front('startVmLoading');
	e.preventDefault();
    jQuery(this).attr('disabled', 'true');
    jQuery(this).removeClass( 'vm-button-correct' );
    jQuery(this).addClass( 'vm-button' );
    jQuery('#checkoutRealexFormSubmit').submit();

});

	});

");
$attribute = '';
if ($viewData['dccinfo']) {
	$attribute = ' readonly ';
}
?>
<div class="realex remote_cc_form" id="remote_cc_form">

<h3 class="order_amount"><?php echo $viewData['order_amount']; ?></h3>

<div class="cc_form_payment_name">
	<?php
	echo $viewData['payment_name'];
	?>
</div>

<form method="post" action="<?php echo $viewData['submit_url'] ?>" id="checkoutRealexFormSubmit">
<?php if (!$viewData['dccinfo']) { ?>
	<?php if (!empty($viewData['creditcardsDropDown'])) { ?>
		<div class="vmpayment_cardinfo" id="vmpayment_cardinfo">
			<div class="vmpayment_cardinfo_text">
				<?php if (!$viewData['dccinfo']) {
					echo vmText::_('VMPAYMENT_REALEX_HPP_API_PLEASE_SELECT');
				} else {
					echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_YOUR_CC');
				}
				?>
			</div>
			<div class="creditcardsDropDown">
				<?php
				echo $viewData['creditcardsDropDown'];
				?>
			</div>
		</div>
		<?php

		if ($viewData['cvn_checking']) {
			?>
			<div class="vmpayment_cc_info vmpayment_cc_cvv_realvault">

				<span class="vmpayment_label"><label
						for="cc_cvv_realvault"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CVV2') ?></label></span>

				<input type="text" class="inputbox cc_cvv" id="cc_cvv" name="cc_cvv_realvault" maxlength="4" size="5"
				       value="<?php echo $ccData['cc_cvv_realvault']; ?>" autocomplete="off"/>
				<span class="hasTip"
				      title="<?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV') ?>::<?php echo vmText::sprintf("VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV_TOOLTIP", $this->_getCVVImages($viewData['cvv_images'])) ?> ">
                        <?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV'); ?>
                    </span>


			</div>
		<?php
		}
	} else {
		?>
		<input type="hidden" name="saved_cc_selected" value="-1"/>

	<?php
	}
}
?>

<div class="vmpayment_cardinfo realexRemoteCCForm">
<?php if ($viewData['integration'] == 'remote') { ?>
	<div class="vmpayment_cardinfo_text">
		<?php if (!$viewData['dccinfo']) {
			if (empty($viewData['creditcardsDropDown'])) {
				echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_COMPLETE_FORM');

			} else {
				echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_ADD_NEW');
			}
		} else {
			echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_YOUR_CC');
		}
		?>
	</div>


	<div class="vmpayment_cc_info vmpayment_creditcardtype">
			<span class="vmpayment_label"><label
					for="creditcardtype"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CCTYPE'); ?></label></span>
		<?php if (!$viewData['dccinfo']) {

			foreach ($viewData['creditcards'] as $creditCard) {
				$options[] = JHTML::_('select.option', $creditCard, vmText::_('VMPAYMENT_REALEX_HPP_API_CC_' . strtoupper($creditCard)));
			}
			$attribs = 'class="inputbox vm-chzn-select" style= "width: 250px;" rel="' . $viewData['virtuemart_paymentmethod_id'] . '"';
			echo JHTML::_('select.genericlist', $options, 'cc_type', $attribs, 'value', 'text', $ccData['cc_type']);
		} else {
			?>
			<span class="vmpayment_creditcardtype_<?php echo $ccData['cc_type'] ?>">
			<?php
			echo $ccData['cc_type'];
			?>
			</span>
			<input type="hidden" name="cc_type" value="<?php echo $ccData['cc_type'] ?>"/>
			<input type="hidden" name="saved_cc_selected" value="<?php echo $ccData['saved_cc_selected'] ?>"/>
		<?php
		}?>
	</div>

	<div class="vmpayment_cc_info vmpayment_cc_type">
			<span class="vmpayment_label"><label
					for="cc_type"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CCNUM'); ?></label></span>
		<?php if (!$viewData['dccinfo']) { ?>

			<input type="text" size="30" class="inputbox" id="cc_number"
			       name="cc_number" value="<?php echo wordwrap($ccData['cc_number'], 4, " "); ?>"
			       autocomplete="off"
			       onchange="ccError=razCCerror(<?php echo $viewData['virtuemart_paymentmethod_id']; ?>);
				       CheckCreditCardNumber(this . value, <?php echo $viewData['virtuemart_paymentmethod_id']; ?>);
				       if (!ccError) {
				       this.value='';}"/>

			<div id="cc_cardnumber_errormsg"></div>
		<?php
		} else {
			echo wordwrap($ccData['cc_number_masked'], 4, " ");
			?>
			<input type="hidden" name="cc_number" value="<?php echo $ccData['cc_number'] ?>"/>
		<?php
		}?>
	</div>
	<?php //if (  ($viewData['cvn_checking'])  and isset($ccData['cc_cvv'])) { ?>
		<?php if (   isset($ccData['cc_cvv'])) { ?>
			<div class="vmpayment_cc_info vmpayment_cc_cvv">

				<span class="vmpayment_label"><label
						for="cc_cvv"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CVV2') ?></label></span>
			<?php if (!$viewData['dccinfo']) { ?>

				<input type="text" class="inputbox cc_cvv" id="cc_cvv" name="cc_cvv" maxlength="4" size="5"
				       value="<?php echo $ccData['cc_cvv']; ?>" autocomplete="off"/>
				<span class="hasTip"
				      title="<?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV') ?>::<?php echo vmText::sprintf("VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV_TOOLTIP", $this->_getCVVImages($viewData['cvv_images'])) ?> ">
                        <?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV'); ?>
                    </span>
			<?php
			} else {
				echo $ccData['cc_cvv_masked'];
				?>
				<input type="hidden" name="cc_cvv" value="<?php echo $ccData['cc_cvv'] ?>"/>
			<?php
			}?>
		</div>
	<?php } ?>

	<?php if (isset($ccData['cc_expire_month']) OR isset($ccData['cc_expire_year'])) { ?>
		<div class="vmpayment_cc_info vmpayment_cc_date">
				<span class="vmpayment_label"><label
						for="creditcardtype"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_EXPDATE'); ?></label></span>
			<?php if (!$viewData['dccinfo']) { ?>
				<?php
				echo shopfunctions::listMonths('cc_expire_month', $ccData['cc_expire_month'], "class=\"inputbox vm-chzn-select\" style=\"width: 100px;\"", 'm');

				echo shopfunctions::listYears('cc_expire_year', $ccData['cc_expire_year'], null, null, "class=\"inputbox vm-chzn-select\" style=\"width: 100px;\"  onchange=\"var month = document.getElementById('cc_expire_month_'" . $viewData['virtuemart_paymentmethod_id'] . "); if(!CreditCardisExpiryDate(month.value,this.value, '" . $viewData['virtuemart_paymentmethod_id'] . "')){this.value='';month.value='';}\" ", "Y");
				?>
				<div id="cc_expiredate_errormsg"></div>
			<?php
			} else {
				echo $ccData['cc_expire_month'] . '/' . $ccData['cc_expire_year'];
				?>
				<input type="hidden" name="cc_expire_month" value="<?php echo $ccData['cc_expire_month'] ?>"/>
				<input type="hidden" name="cc_expire_year" value="<?php echo $ccData['cc_expire_year'] ?>"/>
			<?php
			}?>
		</div>
	<?php } ?>

	<div class="vmpayment_cc_info vmpayment_cc_name">

			<span class="vmpayment_label"><label
					for="cc_name"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CCNAME'); ?></label></span>
		<?php if (!$viewData['dccinfo']) { ?>
			<input type="text" size="30" class="inputbox" id="cc_name"
			       name="cc_name" value="<?php echo $ccData['cc_name']; ?>"
			       autocomplete="off"
			       onchange="ccError=razCCerror(<?php echo $viewData['virtuemart_paymentmethod_id']; ?>);
				       CheckCreditCardNumber(this . value, <?php echo $viewData['virtuemart_paymentmethod_id']; ?>);
				       if (!ccError) {
				       this.value='';}"/>

			<div id="cc_cardname_errormsg"></div>
		<?php
		} else {
			echo $ccData['cc_name'];
			?>
			<input type="hidden" name="cc_name" value="<?php echo $ccData['cc_name'] ?>"/>
		<?php
		}?>
	</div>
<?php } ?>
<?php if (isset($ccData['cc_cvv_realvault'])) { ?>
	<div class="vmpayment_cc_info vmpayment_cc_cvv">

			<span class="vmpayment_label"><label
					for="cc_cvv"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_CVV2') ?></label></span>
		<?php if (!$viewData['dccinfo']) { ?>

			<input type="text" class="inputbox cc_cvv" id="cc_cvv" name="cc_cvv" maxlength="4" size="5"
			       value="<?php echo $ccData['cc_cvv']; ?>" autocomplete="off"/>
			<span class="hasTip"
			      title="<?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV') ?>::<?php echo vmText::sprintf("VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV_TOOLTIP", $this->_getCVVImages($viewData['cvv_images'])) ?> ">
                        <?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_CC_WHATISCVV'); ?>
                    </span>
		<?php
		} else {
			echo $ccData['cc_cvv_masked'];
			?>
			<input type="hidden" name="cc_cvv_realvault" value="<?php echo $ccData['cc_cvv_realvault'] ?>"/>
		<?php
		}?>
	</div>
	<input type="hidden" name="cc_type" value="<?php echo $ccData['cc_type'] ?>"/>
<?php } ?>
<?php if ($viewData['dccinfo']) { ?>
	<div class="dccinfo">
		<div class="dcc_offer_title">
			<?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY'); ?>
		</div>

		<div class="dcc_offer" id="dcc_offer_section">
			<div id="dcc_offer_text">
				<?php echo vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_TIP', $this->getCardHolderAmount($viewData['dccinfo']->merchantamount), $viewData['dccinfo']->merchantcurrency, $this->getCardHolderAmount($viewData['dccinfo']->cardholderamount), $viewData['dccinfo']->cardholdercurrency); ?>
			</div>
			<div class="dcc_offer_exchange_rate">
				<?php echo vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_EXCHANGE_RATE', 1, $viewData['dccinfo']->merchantcurrency, $viewData['dccinfo']->cardholderrate, $viewData['dccinfo']->cardholdercurrency); ?>
			</div>


		</div>
		<div class="dcc_choices">
			<div class="dcc_choice">
				<input class="dcc_offer_btn vm-button" name="dcc_choice" id="dcc_choice_1" type="radio" value="1">
				<label for="dcc_choice_1">
					<?php echo vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_YES', $this->getCardHolderAmount($viewData['dccinfo']->cardholderamount), $viewData['dccinfo']->cardholdercurrency); ?>
				</label>
			</div>
			<div class="dcc_choice">
				<input class="dcc_offer_btn vm-button" name="dcc_choice" id="dcc_choice_0" type="radio" value="0"
				       checked="checked"> <label for="dcc_choice_0">
					<?php echo vmText::sprintf('VMPAYMENT_REALEX_HPP_API_DCC_PAY_OWN_CURRENCY_NO', $this->getCardHolderAmount($viewData['dccinfo']->merchantamount), $viewData['dccinfo']->merchantcurrency); ?>
				</label>
			</div>
		</div>

	</div>
<?php } ?>
<?php if ($viewData['integration'] == 'remote') { ?>
	<?php if (!$viewData['dccinfo']) { ?>
		<?php if ($viewData['offer_save_card']) {
			if ($ccData['save_card']) {
				$checked = 'checked="checked"';
			} else {
				$checked = '';
			}
			?>

			<div class="offer_save_card">
				<div id="save_card_tip"><?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_SAVE_CARD_DETAILS_TIP') ?></div>
				<label for="save_card">
					<input id="save_card" name="save_card" type="checkbox" value="1" <?php echo $checked ?>><span
						class="save_card"> <?php echo vmText::_('VMPAYMENT_REALEX_HPP_API_SAVE_CARD_DETAILS') ?></span> </label>
			</div>
		<?php
		}
	} else {
		?>
		<input type="hidden" name="save_card" value="<?php echo $ccData['save_card'] ?>"/>
<?php
	}
}
?>

</div>
<div class="dcc_card_payment_button details-button">
		<span class="addtocart-button">
		<input type="submit" class="dcc_offer_btn addtocart-button"
		       value="<?php echo $viewData['card_payment_button'] ?>" id="checkoutRealexFormSubmitButton"/>
		<input type="hidden" name="option" value="com_virtuemart"/>
		<input type="hidden" name="view" value="pluginresponse"/>
		<input type="hidden" name="task" value="pluginnotification"/>
		<input type="hidden" name="token" value="<?php echo $viewData['token'] ?>"/>
		<input type="hidden" name="notificationTask" value="<?php echo $viewData['notificationTask']; ?>"/>
		<input type="hidden" name="order_number" value="<?php echo $viewData['order_number']; ?>"/>
		<input type="hidden" name="pm" value="<?php echo $viewData['virtuemart_paymentmethod_id']; ?>"/>
		<input type="hidden" name="virtuemart_paymentmethod_id"
		       value="<?php echo $viewData['virtuemart_paymentmethod_id']; ?>"/>
		</span>

</div>
</form>
</div>
