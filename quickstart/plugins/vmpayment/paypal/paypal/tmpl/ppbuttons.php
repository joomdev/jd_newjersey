<?php

defined('_JEXEC') or die('Restricted access');

/**
 * Class vmPPButton
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

/**
 * https://www.paypal.com/us/webapps/mpp/logo-center
 * https://www.paypal.com/uk/webapps/mpp/logo-center
 * https://www.paypal.com/de/webapps/mpp/logo-center
 * https://www.paypal.com/cn/webapps/mpp/logos-buttons
 *
 */
class vmPPButton {


	/**
	 * was getExpressProduct
	 * @param $method
	 * @param bool $credit
	 * @return string
	 */

    static function renderCheckoutButton($method){

		$link = JURI::root() . 'index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=' . $method->payment_element . '&action=SetExpressCheckout&pm=' . $method->virtuemart_paymentmethod_id;

		if($method->offer_credit){
			$img = self::getCreditLogo();
			$class = 'pp-img-credit';
		} else {
			$img = self::getExpressLogo();
			$class = 'pp-img-express';
		}
		//$img = self::getCheckoutLogo($credit);

        if($method->offer_credit){
			$text = vmText::_('VMPAYMENT_PAYPAL_CREDITCHECKOUT_BUTTON');
        } else {
			$text = vmText::_('VMPAYMENT_PAYPAL_EXPCHECKOUT_BUTTON');
        }

		$html = '<a href="'.$link.'" title="'.$text.'" >
    <img class="'.$class.'" src="'.$img.'" align="left" alt="'.$text.'" title="'.$text.'" >
</a>';
		return $html;
    }

	static function getCreditLogo(){
		$lang = JFactory::getLanguage();

		if($lang->hasKey('VMPAYMENT_PAYPAL_CHECKOUT_CREDIT_IMG')){
			return vmText::_('VMPAYMENT_PAYPAL_CHECKOUT_CREDIT_IMG');
		} else {
			$tag = $lang->getTag();

			$v = array(
			'en-US' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppcredit-logo-small.png',
			'en-UK' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/ppcredit-logo-small.png',
/*			'de-DE' => '',
			'es-ES' => '',
			'pl-PL' => '',
			'nl-NL' => '',
			'fr-FR' => '',
			'it-IT' => '',
			'zn-CN' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png'*/
			);

			if (!isset($v[$tag])) {
				$tag = 'en-US';
			}
			return $v[$tag];
		}
	}

	static function getExpressLogo(){
		$lang = JFactory::getLanguage();

		if($lang->hasKey('VMPAYMENT_PAYPAL_CHECKOUT_EXP_IMG')){
			return vmText::_('VMPAYMENT_PAYPAL_CHECKOUT_EXP_IMG');
		} else {
			$tag = $lang->getTag();

			$v = array(
			'en-US' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'en-UK' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'de-DE' => 'https://www.paypalobjects.com/webstatic/de_DE/i/de-btn-expresscheckout.gif',
			'es-ES' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'pl-PL' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'nl-NL' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'fr-FR' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'it-IT' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'zn-CN' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png'
			);

			if (!isset($v[$tag])) {
				$tag = 'en-US';
			}
			return $v[$tag];
		}

	}

	static function getPayPalInfoLink(){
		$lang = JFactory::getLanguage();
		$tag = $lang->getTag();
		if(strlen($tag)==5){
			$tag2 = strtolower(substr($tag,3));
			return 'https://www.paypal.com/'.$tag2.'/webapps/mpp/paypal-popup';
		} else {
			return 'https://www.paypal.com/en/webapps/mpp/paypal-popup';
		}

	}

	static function getMarkCreditLogo(){
		$lang = JFactory::getLanguage();

		if($lang->hasKey('VMPAYMENT_PAYPAL_CREDIT_MARK_IMG')){
			return vmText::_('VMPAYMENT_PAYPAL_CREDIT_MARK_IMG');
		} else {
			$tag = $lang->getTag();

			$v = array(
			'en-US' => 'https://www.paypalobjects.com/webstatic/en_US/btn/btn_bml_text.png',
			/*'en-UK' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'de-DE' => 'https://www.paypalobjects.com/webstatic/de_DE/i/de-btn-expresscheckout.gif',
			'es-ES' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'pl-PL' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'nl-NL' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'fr-FR' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'it-IT' => 'https://www.paypalobjects.com/webstatic/en_US/i/btn/png/gold-rect-paypalcheckout-34px.png',
			'zn-CN' => 'https://www.paypalobjects.com/webstatic/en_US/i/buttons/checkout-logo-large.png'*/
			);

			if (!isset($v[$tag])) {
				$tag = 'en-US';
			}
			return $v[$tag];
		}

	}

	/**
	 * We use this for the acceptance mark, this can also be just the paypal info logo
	 *
	 * @return string
	 */
	static function renderMarkAcceptance(){
		$lang = JFactory::getLanguage();
		$tag = $lang->getTag();

		$v = array(				 //https://www.paypalobjects.com/webstatic/mktg/logo/AM_SbyPP_mc_vs_dc_ae.jpg
		'en-US' => array('img' => 'https://www.paypalobjects.com/webstatic/mktg/logo/AM_SbyPP_mc_vs_dc_ae.jpg', 'title' => 'How PayPal Works', 'alt'=>'PayPal Acceptance Mark'),

		'en-UK' => array('img' => 'https://www.paypalobjects.com/webstatic/mktg/Logo/AM_SbyPP_mc_vs_ms_ae_UK.png', 'title' => 'How PayPal Works', 'alt'=>'PayPal Acceptance Mark'),

		'de-DE' => array('img' => 'https://www.paypalobjects.com/webstatic/de_DE/i/de-pp-logo-150px.png', 'title' => 'So funktioniert PayPal', 'alt'=>'PayPal Acceptance Mark'),

		'es-ES' => array('img' => 'https://www.paypalobjects.com/webstatic/mktg/logo-center/logotipo_paypal_pagos_tarjetas.jpg', 'title' => 'Cómo funciona PayPal', 'alt'=>'Marcas de aceptación'),

		'pl-PL' => array('img' => 'https://www.paypalobjects.com/webstatic/mktg/logo-center/banner_pl_secured_payments_by_pp_319x110.jpg', 'title' => 'Jak działa PayPal', 'alt'=>'Znak akceptacji PayPal'),

		'nl-NL' => array('img' => 'https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_veilig_betalen_met_paypal_logos-nl.jpg', 'title' => 'Hoe PayPal Werkt', 'alt'=>'PayPal Acceptatie Logo'),

		'fr-FR' => array('img' => 'https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_paiements_securises_fr.jpg', 'title' => 'PayPal Comment Ca Marche', 'alt'=>'PayPal Acceptance Mark'),

		'it-IT' => array('img' => 'https://www.paypalobjects.com/webstatic/mktg/logo-center/logo_paypal_pagamento.jpg', 'title' => '"Come funziona PayPal', 'alt'=>'Marchi di accettazione PayPal'),

		'zn-CN' => array('img' => 'https://www.paypalobjects.com/webstatic/en_AU/i/buttons/btn_paywith_primary_l.png', 'title' => 'How PayPal Works', 'alt'=>'使用PayPal付款')
		);
		if (!isset($v[$tag])) {
			$tag = 'en-US';
		}

		//Enable Overriding of the given image
		if($lang->hasKey('VMPAYMENT_PAYPAL_ACCEPTANCE_MARK_IMG')){
			$v[$tag]['img'] = vmText::_('VMPAYMENT_PAYPAL_ACCEPTANCE_MARK_IMG');
		}

		if($lang->hasKey('VMPAYMENT_PAYPAL_EXPCHECKOUT_AVAILABALE')){
			$v[$tag]['title'] = vmText::_('VMPAYMENT_PAYPAL_EXPCHECKOUT_AVAILABALE');
		}

		if($lang->hasKey('VMPAYMENT_PAYPAL_ACCEPTANCE_MARK_ALT')){
			$v[$tag]['alt'] = vmText::_('VMPAYMENT_PAYPAL_ACCEPTANCE_MARK_ALT');
		}

		$v[$tag]['link'] = self::getPayPalInfoLink();

		$html = '<a href="'.$v[$tag]['link'].'" title="'.$v[$tag]['title'].'" onclick="javascript:window.open(\''.$v[$tag]['link'].'\',\'WIPaypal\',\'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=1060, height=700\'); return false;"><img src="'.$v[$tag]['img'].'" border="0" alt="'.$v[$tag]['alt'].'"></a>';

		return $html;
	}

	static function renderMarkCredit(){
		static $go = true;
		$html = '';
		if($go){
			$img = self::getMarkCreditLogo();
		    $html = '<button class="pp-mark-credit-modal">
        <img src="'.$img.'" /></button>';
			$html .= '<div id="paypal_offer_frame"></div>';

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

		}
		return $html;
	}
}