<?php
/**
 * @version $Id: getklarna.php 8498 2014-10-21 13:29:03Z alatak $
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


class JFormFieldGetKlarna extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'getKlarna';

	function getInput() {

		$jlang = JFactory::getLanguage();
		$lang = $jlang->getTag();
		$langArray = explode("-", $lang);
		$lang = strtolower($langArray[1]);
		$countriesData = KlarnaHandler::countriesData();
		$signLang = "en";
		foreach ($countriesData as $countryData) {
			if ($countryData['country_code'] == $lang) {
				$signLang = $lang;
				break;
			}
		}

		$url = "https://merchants.klarna.com/signup/?locale=" . $signLang . "&partner_id=7829355537eae268a17667c199e7c7662d3391f7&utm_campaign=Platform&utm_medium=Partners&utm_source=Virtuemart";

		$logo = '<img src="' . JURI::root() . VMKLARNAPLUGINWEBROOT . '/klarna/assets/images/logo/get_klarna_now.jpg" />';
		$html = '<div><a target="_blank" href="' . $url . '" id="klarna_getklarna_link" ">' . $logo . '</a> ';
		$html .= ' <a target="_blank" href="https://static.klarna.com/integration_sales/partner/VirtueMart/v1.0/Klarna_documentation.pdf" class="signin-button-link">' . vmText::_('VMPAYMENT_KLARNA_DOCUMENTATION') . '</a></div>';


		return $html;
	}

}