<?php
/**
 * @version $Id: getklikandpay.php 6369 2012-08-22 14:33:46Z alatak $
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

/**
 * Renders a label element
 */

jimport('joomla.form.formfield');
class JFormFieldGetKlikandpay extends JFormField {

var $type = 'getKlikandpay';

	function getInput() {
		vmJsApi::addJScript( '/plugins/vmpayment/klikandpay/klikandpay/assets/js/admin.js');

		$lang = $this->getLang();
		if ($lang == 'fr') {
			$url = "https://www.klikandpay.com/cgi-bin/connexion.pl?FROM=869B834067";
		} else {
			$url = "https://www.klikandpay.com/cgi-bin/connexion.pl?FROM=869B834067&L=en";
		}
		$logo = '<img src="https://www.klikandpay.com/images/logo_en.png" style="width: 150px;">';
		$html = '<p><a target="_blank" href="' . $url . '"  >' . $logo . '</a></p>';

		$html .= '<p><a class="signin-button-link" href="' . $url . '" target="_blank">' . vmText::_('VMPAYMENT_KLIKANDPAY_GET') . '</a>';
		$html .= ' <a target="_blank" href="https://www.youtube.com/watch?v=DVcUU3FiuMM" class="signin-button-link">' . vmText::_('VMPAYMENT_KLIKANDPAY_DOCUMENTATION') . '</a></p>';

		return $html;
	}

	protected function getLang() {


		$language = JFactory::getLanguage();
		$tag = strtolower(substr($language->get('tag'), 0, 2));
		return $tag;
	}


}