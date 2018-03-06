<?php
/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: getrealex.php 8414 2014-10-12 20:30:38Z alatak $
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
defined('JPATH_BASE') or die();


jimport('joomla.form.formfield');
class JFormFieldGetRealex extends JFormField {
	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	public $type = 'getrealex';

	protected function getInput() {
		vmJsApi::addJScript( '/plugins/vmpayment/realex_hpp_api/realex_hpp_api/assets/js/admin.js');
		vmJsApi::css( 'admin','plugins/vmpayment/realex_hpp_api/realex_hpp_api/assets/css/');

		$url = "http://www.realexpayments.com/partner-referral?id=virtuemart";
		$logo = '<img src="http://www.realexpayments.com/images/logo_realex_large.png" width="150"/>';
		$html = '<p><a target="_blank" href="' . $url . '"  >' . $logo . '</a></p>';
		$html .= '<p><a target="_blank" href="' . $url . '" class="signin-button-link">' . vmText::_('VMPAYMENT_REALEX_HPP_API_REGISTER') . '</a>';
		$html .= ' <a target="_blank" href="http://docs.virtuemart.net/manual/shop-menu/payment-methods/realex-hpp-and-api.html" class="signin-button-link">' . vmText::_('VMPAYMENT_REALEX_HPP_API_DOCUMENTATION') . '</a></p>';


		return $html;
	}
}