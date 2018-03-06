<?php
/**
 *
 * Amazon payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: ipnurl.php 9185 2016-02-25 13:51:01Z Milbo $
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

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

jimport('joomla.form.formfield');

class JFormFieldIpnURL extends JFormField {

	var $type = 'ipnurl';
	var $class = 'ipnurl level3';

	protected function getInput() {
		$cid = vRequest::getvar('cid', NULL, 'array');
		if (is_array($cid)) {
			$virtuemart_paymentmethod_id = $cid[0];
		} else {
			$virtuemart_paymentmethod_id = $cid;
		}

		$http = JURI::root() . 'index.php?option=com_virtuemart&view=vmplg&task=notify&nt=ipn&tmpl=component&pm=' . $virtuemart_paymentmethod_id;
		$https = str_replace('http://', 'https://', $http);

		$string = '<div class="' . $this->class . '">';
		$string .= '<div class="">' . $https . ' <br /></div>';
		if (strcmp($https,$http) !==0){
			$string .= '<div class="ipn-sandbox">' . vmText::_('VMPAYMENT_AMAZON_OR') . '<br />';
			$string .= $http. '</div>';
		}
		$string .= "</div>";
			return $string;
	}
}