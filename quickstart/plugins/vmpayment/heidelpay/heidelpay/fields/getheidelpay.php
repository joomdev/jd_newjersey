<?php
/**
 * @version $Id: getheidelpay.php 6369 2012-08-22 14:33:46Z alatak $
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


class JFormFieldGetHeidelpay extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'getHeidelpay';

	function getInput() {

		$js = '
//<![CDATA[
		jQuery(document).ready(function( $ ) {

		    jQuery("#heidelpay_getheidelpay_link").click( function() {
				 if ( $("#heidelpay_getheidelpay_show_hide").is(":visible") ) {
				  $("#heidelpay_getheidelpay_show_hide").hide("slow");
			        $("#heidelpay_getheidelpay_link").html("' . addslashes(vmText::_('VMPAYMENT_HEIDELPAY_CREATE_ACCOUNT')) . '");
				} else {
				 $("#heidelpay_getheidelpay_show_hide").show("slow");
			       $("#heidelpay_getheidelpay_link").html("' . addslashes(vmText::_('VMPAYMENT_HEIDELPAY_GET_HEIDELPAY_HIDE')) . '");
			    }
		    });
		});
//]]>
';

		$doc = JFactory::getDocument();
		$doc->addScriptDeclaration($js);
		$cid = vRequest::getvar('cid', NULL, 'array');
		if (is_Array($cid)) {
			$virtuemart_paymentmethod_id = $cid[0];
		} else {
			$virtuemart_paymentmethod_id = $cid;
		}

		$query = "SELECT payment_params FROM `#__virtuemart_paymentmethods` WHERE  virtuemart_paymentmethod_id = '" . $virtuemart_paymentmethod_id . "'";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$params = $db->loadResult();

		$payment_params = explode("|", $params);
		foreach ($payment_params as $payment_param) {
			if (empty($payment_param)) {
				continue;
			}
			$param = explode('=', $payment_param);
			$payment_params[$param[0]] = substr($param[1], 1, -1);
		}
		$id = "";

		if (isset($payment_params['HEIDELPAY_SECURITY_SENDER']) AND ($payment_params['HEIDELPAY_SECURITY_SENDER'] == '31HA07BC8124AD82A9E96D9A35FAFD2A' or $payment_params['HEIDELPAY_SECURITY_SENDER'] == '')) {
			$id = "heidelpay_getheidelpay_link";
			$display = '';
			$html = '<a href="#" id="' . $id . '">' . vmText::_('VMPAYMENT_HEIDELPAY_ALREADY_ACCOUNT') . '</a>';
		} else {
			$id = "heidelpay_getheidelpay_link";
			$display = ' style="display: none;"';
			$html = '<a href="#" id="' . $id . '">' . vmText::_('VMPAYMENT_HEIDELPAY_CREATE_ACCOUNT') . '</a>';
		}
		$lang = $this->getLang();

		$html .= '<div id="heidelpay_getheidelpay_show_hide" align=""' . $display . ' >';
		$url = "http://demoshops.heidelpay.de/contactform/?campaign=vituemart&shop=vituemart&lang=" . $lang;
		$html .= '<iframe src="' . $url . '" scrolling="yes" style="x-overflow: none;" frameborder="0" height="1400px" width="300px"></iframe>';
		$html .= "</div>";
		return $html;
	}

	protected function getLang() {

		$language = JFactory::getLanguage();
		$tag = strtolower(substr($language->get('tag'), 0, 2));
		return $tag;
	}

}