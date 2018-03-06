<?php
/**
 * @version $Id: getsofort.php 8602 2014-12-02 10:20:51Z alatak $
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

jimport('joomla.form.formfield');

class JFormFieldGetSofort extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'getSofort';

	function getInput() {

		$jlang = JFactory::getLanguage();
		$lang = $jlang->getTag();
		$langArray = explode("-", $lang);
		$lang = strtolower($langArray[1]);
		$getSofortLang = 'eng-DE';
		if ($lang == 'de') {
			$getSofortLang = "ger-DE";
		}

		// MOre information
		$getSofortLInk = "https://www.sofort.com/" . $getSofortLang . "/merchant/products/";
		//$getSofortLInk="https://www.sofort.com/payment/users/register/688";
		$html = '<a href="#" id="sofortmoreinfo_link" ">' . vmText::_('VMPAYMENT_SOFORT_READMORE') . '</a>';
		$html .= '<div id="sofortmoreinfo_show_hide" >';

		$js = '
		jQuery(document).ready(function( $ ) {
			$("#sofortmoreinfo_show_hide").hide();
			jQuery("#sofortmoreinfo_link").click( function() {
				 if ( $("#sofortmoreinfo_show_hide").is(":visible") ) {
				  $("#sofortmoreinfo_show_hide").hide("slow");
			        $("#sofortmoreinfo_link").html("' . addslashes(vmText::_('VMPAYMENT_SOFORT_READMORE')) . '");
				} else {
				 $("#sofortmoreinfo_show_hide").show("slow");
			       $("#sofortmoreinfo_link").html("' . addslashes(vmText::_('VMPAYMENT_SOFORT_HIDE')) . '");
			    }
		    });
		});
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);

		$html .= '<iframe src="' . $getSofortLInk . '" scrolling="yes" style="x-overflow: none;" frameborder="0" height="500px" width="850px"></iframe>';
		$html .= '</div>';
// Get Sofort

		// MOre information
		$getSofortLInk = "https://www.sofort.com/payment/users/register/688";
		$html .= '<p style="margin-top: 20px;"><a target="_blank" href="' . $getSofortLInk . '" id="getsogort_link" class="signin-button-link">' . vmText::_('VMPAYMENT_SOFORT_REGISTERNOW') . '</a>';
		if ($lang == 'de') {
			$manualLink = "https://www.sofort.com/integrationCenter-ger-DE/content/view/full/4945";
		} else {
			$manualLink = "https://www.sofort.com/integrationCenter-eng-DE/content/view/full/4945";
		}
		$html .= '<a target="_blank" href="' . $manualLink . '" id="getsogort_link" class="signin-button-link">' . vmText::_('VMPAYMENT_SOFORT_DOCUMENTATION') . '</a>';
		$html .= '</p>';
		return $html;
	}


}