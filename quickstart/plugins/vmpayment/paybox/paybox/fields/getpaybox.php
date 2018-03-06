<?php
/**
 * @version $Id: getpaybox.php 6369 2012-08-22 14:33:46Z alatak $
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



class JFormFieldGetPaybox extends JFormField {

	/**
	 * Element name
	 *
	 * @access    protected
	 * @var        string
	 */
	var $type = 'getPaybox';

	function getInput() {

		$js = '
//<![CDATA[
		jQuery(document).ready(function( $ ) {

		    jQuery("#paybox_getpaybox_link").click( function() {
				 if ( $("#paybox_getpaybox_show_hide").is(":visible") ) {
				  $("#paybox_getpaybox_show_hide").hide("slow");
			        $("#paybox_getpaybox_link").html("' . addslashes(vmText::_('VMPAYMENT_PAYBOX_ALREADY_ACCOUNT')) . '");
				} else {
				 $("#paybox_getpaybox_show_hide").show("slow");
			       $("#paybox_getpaybox_link").html("' . addslashes(vmText::_('VMPAYMENT_PAYBOX_GET_PAYBOX_HIDE')) . '");
			    }
		    });
		});
//]]>
';

		vmJsApi::addJScript("vm.getPaybox",$js);
		vmJsApi::addJScript(  '/plugins/vmpayment/paybox/paybox/assets/js/admin.js');
		vmJsApi::css(  'admin','plugins/vmpayment/paybox/paybox/assets/css/');
		$cid = vRequest::getvar('cid', NULL, 'array');
		if (is_Array($cid)) {
			$virtuemart_paymentmethod_id = $cid[0];
		} else {
			$virtuemart_paymentmethod_id = $cid;
		}

		$query = "SELECT * FROM `#__virtuemart_paymentmethods` WHERE  virtuemart_paymentmethod_id = '" . $virtuemart_paymentmethod_id . "'";
		$db = JFactory::getDBO();
		$db->setQuery($query);
		$params = $db->loadObject();
		$html = '<img src="http://virtuemart.boutique-paybox.com/PayboxLogo.jpg" width="200px"/><br />';

		if ($params->created_on == $params->modified_on) {
			$id = "paybox_getpaybox_link";
			$html .= '<a href="#" id="' . $id . '">' . vmText::_('VMPAYMENT_PAYBOX_GET_PAYBOX_HIDE') . '</a>';
			$display = '';
			$html .= '<div id="paybox_getpaybox_show_hide" align=""' . $display . ' >';
		} else {
			$id = "paybox_getpaybox_link";
			$html .= '<a href="#" id="' . $id . '">' . vmText::_('VMPAYMENT_PAYBOX_ALREADY_ACCOUNT') . '</a>';
			$display = ' style="display: none;"';
			$html .= '<div id="paybox_getpaybox_show_hide" align=""' . $display . ' >';
		}
		$id = "";


		$lang = $this->getLang();;
		if ($lang == 'fr') {
			$url = "http://virtuemart.boutique-paybox.com/PayboxPres.html";
		} else {
			$url = "http://virtuemart.boutique-paybox.com/PayboxPres.html";
		}
		$html .= '<iframe src="' . $url . '" scrolling="yes" style="x-overflow: none;" frameborder="0" height="1400px" width="800px"></iframe>';
		$html .= "</div>";
		return $html;
	}

	protected function getLang() {


		$language =  JFactory::getLanguage();
		$tag = strtolower(substr($language->get('tag'), 0, 2));
		return $tag;
	}


}