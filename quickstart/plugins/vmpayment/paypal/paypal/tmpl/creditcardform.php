<?php
/**
 *
 * Paypal  payment plugin
 *
 * @author Jeremy Magne
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
defined('_JEXEC') or die();

$customerData = $viewData['customerData'];

JHTML::_('behavior.tooltip');
JHTML::script('vmcreditcard.js', 'components/com_virtuemart/assets/js/', false);
vmLanguage::loadJLang('com_virtuemart', true);
vmJsApi::jCreditCard();

vmJsApi::addJScript('/plugins/vmpayment/paypal/paypal/assets/js/site.js');

?>
<div id="paymentMethodOptions_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>" class="paymentMethodOptions" >
    <br />
    <span class="vmpayment_cardinfo">
        <?php 
       echo vmText::_('VMPAYMENT_PAYPAL_CC_COMPLETE_FORM');
        if ($viewData['sandbox'] ) {
            echo '<br />' . vmText::_('VMPAYMENT_PAYPAL_CC_SANDBOX_INFO');
        }
        ?>
        <table border="0" cellspacing="0" cellpadding="2" width="100%">
            <tr valign="top">
                <td nowrap width="10%" align="right">
                    <label for="creditcardtype"><?php echo vmText::_('VMPAYMENT_PAYPAL_CC_CCTYPE'); ?></label>
                </td>
                <td>

                	<ul class="cards">
						<?php
						foreach ( $viewData['creditcards'] as $creditCard) {
							echo '<li class="'.$creditCard.'">'.vmText::_('VMPAYMENT_PAYPAL_CC_' . strtoupper($creditCard)).'</li>';
							//$options[] = JHTML::_('select.option', $creditCard, vmText::_('VMPAYMENT_PAYPAL_CC_' . strtoupper($creditCard)));
						}
						?>                    	
                    </ul>
                    <?php
                    foreach ($viewData['creditcards'] as $creditCard) {
                        $options[] = JHTML::_('select.option', $creditCard, vmText::_('VMPAYMENT_PAYPAL_CC_' . strtoupper($creditCard)));
                    }
					if ($viewData['method']->sandbox ) {
						$attribs = 'class="cc_type_sandbox" rel="'.$viewData['virtuemart_paymentmethod_id'].'"';
					} else {
						$attribs = 'class="cc_type" rel="'.$viewData['virtuemart_paymentmethod_id'].'"';
					}
                   echo JHTML::_('select.genericlist', $options, 'cc_type_'.$viewData['virtuemart_paymentmethod_id'], $attribs, 'value', 'text', $customerData->getVar('cc_type'));
                    ?>
                </td>
            </tr>
            <tr valign="top">
                <td nowrap width="10%" align="right">
                    <label for="cc_type"><?php echo vmText::_('VMPAYMENT_PAYPAL_CC_CCNUM'); ?></label>
                </td>
                <td>
                    <input type="text" size="30" class="inputbox" id="cc_number_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>"
                        name="cc_number_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>" value="<?php echo $customerData->getVar('cc_number'); ?>"
                        autocomplete="off" onchange="ccError=razCCerror(<?php echo $viewData['virtuemart_paymentmethod_id']; ?>);
                            CheckCreditCardNumber(this . value, <?php echo $viewData['virtuemart_paymentmethod_id']; ?>);
                        if (!ccError) {
                        this.value='';}" />
                    <div id="cc_cardnumber_errormsg_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>"></div>
                </td>
            </tr>
            <tr valign="top">
                <td nowrap width="10%" align="right">
                    <label for="cc_cvv"><?php echo vmText::_('VMPAYMENT_PAYPAL_CC_CVV2') ?></label>
                </td>
                <td>
                    <input type="text" class="inputbox" id="cc_cvv_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>" name="cc_cvv_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>" maxlength="4" size="5" value="<?php echo $customerData->getVar('cc_cvv'); ?>" autocomplete="off" />
                    <span class="hasTip" title="<?php echo vmText::_('VMPAYMENT_PAYPAL_CC_WHATISCVV') ?>::<?php echo vmText::sprintf("VMPAYMENT_PAYPAL_CC_WHATISCVV_TOOLTIP", $this->_displayCVVImages($viewData['method'])) ?> ">
                        <?php echo vmText::_('VMPAYMENT_PAYPAL_CC_WHATISCVV'); ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td nowrap width="10%" align="right"><?php echo vmText::_('VMPAYMENT_PAYPAL_CC_EXDATE'); ?></td>
                <td>
                    <?php 
                    echo shopfunctions::listMonths('cc_expire_month_' . $viewData['virtuemart_paymentmethod_id'], $customerData->getVar('cc_expire_month'));
                    echo " / ";
                    echo shopfunctions::listYears('cc_expire_year_' . $viewData['virtuemart_paymentmethod_id'], $customerData->getVar('cc_expire_year'), null, null, "onchange=\"var month = document.getElementById('cc_expire_month_'".$viewData['virtuemart_paymentmethod_id']."); if(!CreditCardisExpiryDate(month.value,this.value, '".$viewData['virtuemart_paymentmethod_id']."')){this.value='';month.value='';}\" ");
                    ?>
                    <div id="cc_expiredate_errormsg_<?php echo $viewData['virtuemart_paymentmethod_id']; ?>"></div>
                </td>
            </tr>
        </table>
    </span>
</div>
