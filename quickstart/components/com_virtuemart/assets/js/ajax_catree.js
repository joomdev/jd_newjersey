/**
 * catreeajax.js: load category tree by ajax
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author Max Milbers
 * @copyright Copyright (c) 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
// vmText::sprintf( 'COM_VIRTUEMART_SELECT' ,  vmText::_('COM_VIRTUEMART_CATEGORY'))

//Virtuemart.empty;
//Virtuemart.param;
if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
Virtuemart.startVmLoading = function(a) {
	var msg = '';
	/*if (typeof a.data.msg !== 'undefined') {
	 msg = a.data.msg;
	 }*/
	jQuery('#pro-tech_ajax_load').addClass('vmLoading');
	if (!jQuery('div.vmLoadingDiv').length) {
		jQuery('body').append('<div class=\"vmLoadingDiv\"><div class=\"vmLoadingDivMsg\">' + msg + '</div></div>');
	}
};

Virtuemart.stopVmLoading = function() {
	if (jQuery('#pro-tech_ajax_load').hasClass('vmLoading')) {
		jQuery('body').removeClass('vmLoading');
		jQuery('div.vmLoadingDiv').remove();
	}
};

Virtuemart.loadCategoryTree = function(id){
	jQuery('#'+id+'_chzn').remove();
	jQuery('<div id=\"pro-tech_ajax_load\" style=\"display:inline-block;width:220px;background-color:#ddd;height:25px;line-height:25px;padding:0 10px;box-sizing:border-box;background-size:20px\">Loading</div>').insertAfter('select#'+id);
	Virtuemart.startVmLoading('Loading categories');

	if(Virtuemart.isAdmin=='1'){
		Virtuemart.adminSuffix = 'administrator/';
		Virtuemart.ajaxCategoryUrl = 'option=com_virtuemart&view=product&type=getCategoriesTree'+Virtuemart.param+'&format=json'+Virtuemart.vmLang;
	} else {
		Virtuemart.adminSuffix = '';
		Virtuemart.ajaxCategoryUrl = 'option=com_virtuemart&view=category&type=getCategoriesTree'+Virtuemart.param+'&format=json'+Virtuemart.vmLang;
	}
	jQuery.ajax({
		type: 'GET',
		url: Virtuemart.vmSiteurl+Virtuemart.adminSuffix+'index.php',
		cache: 'false',
		data: Virtuemart.ajaxCategoryUrl,
		success:function(json){
			jQuery('select#'+id).switchClass('chzn-done','chzn-select');
			jQuery('select#'+id).html('<option value=\"\">'+Virtuemart.emptyCatOpt+'</option>'+json.value);
			jQuery('#pro-tech_ajax_load').remove();
			jQuery('select#'+id).chosen();
			Virtuemart.stopVmLoading();
		}
	});
};