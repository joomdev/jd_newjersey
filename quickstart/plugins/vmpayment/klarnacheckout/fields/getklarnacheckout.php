<?php
/**
 * @version $Id: getklarnacheckout.php 7301 2013-10-29 17:45:07Z alatak $
 *
 * @author Valérie Isaksen
 * @package VirtueMart
 * @copyright Copyright (c) 2004 - 2012 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * VirtueMart is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('JPATH_BASE') or die();


require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'define.php');
if (!class_exists('KlarnaHandler')) {
	require(VMPATH_ROOT . DS . 'plugins' . DS . 'vmpayment' . DS . 'klarna' . DS . 'klarna' . DS . 'helpers' . DS . 'klarnahandler.php');
}

jimport('joomla.form.formfield');

class JFormFieldGetKlarnacheckout extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'getKlarnacheckout';

	function getInput() {
		vmJsApi::addJScript( '/plugins/vmpayment/klarnacheckout/assets/js/admin.js');
		vmJsApi::css('admin', 'plugins/vmpayment/klarnacheckout/assets/css');
		JHtml::_('behavior.colorpicker');

		$jlang = JFactory::getLanguage ();
		$lang = $jlang->getTag ();
		$langArray = explode ("-", $lang);
		$lang = strtolower ($langArray[1]);
		$countriesData = KlarnaHandler::countriesData ();
		$signLang = "en";
		foreach ($countriesData as $countryData) {
			if ($countryData['country_code'] == $lang) {
				$signLang = $lang;
				break;
			}
		}

		$logo = '<img src="' . JURI::root () . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo/get_klarna_now.jpg" style="margin-bottom: 10px"/>';
		$html = '<p><a href="#" id="klarna_getklarna_link" ">' . $logo . '</a></p>';
// https://merchants.klarna.com/signup?locale=en&partner_id=7829355537eae268a17667c199e7c7662d3391f7&utm_campaign=Platform&utm_medium=Partners&utm_source=Virtuemart
		$html .= '<div id="klarna_getklarna_show_hide" >';
		$url = "https://merchants.klarna.com/signup/?locale=" . $signLang . "&partner_id=7829355537eae268a17667c199e7c7662d3391f7&utm_campaign=Platform&utm_medium=Partners&utm_source=Virtuemart";

		$js = '
		jQuery(document).ready(function( $ ) {
			$("#klarna_getklarna_show_hide").hide();
			jQuery("#klarna_getklarna_link").click( function() {
				 if ( $("#klarna_getklarna_show_hide").is(":visible") ) {
				  $("#klarna_getklarna_show_hide").hide("slow");
			        $("#klarna_getklarna_link").html("' . addslashes ($logo) . '");
				} else {
				 $("#klarna_getklarna_show_hide").show("slow");
			       $("#klarna_getklarna_link").html("' . addslashes (vmText::_ ('VMPAYMENT_KLARNA_GET_KLARNA_HIDE')) . '");
			    }
		    });
		});
';

		vmJsApi::addJScript('vm.getKlarna', $js);


		$html .= '<iframe src="' . $url . '" scrolling="yes" style="x-overflow: none;" frameborder="0" height="600px" width="850px"></iframe>';
		$html .= '</div>';
		$html .= '<p><a target="_blank" href="http://cdn.klarna.com/1.0/shared/content/integration/guide/virtuemart.pdf" class="signin-button-link">' . vmText::_('VMPAYMENT_KLARNACHECKOUT_DOCUMENTATION') . '</a></p>';

		return $html;
	}

}