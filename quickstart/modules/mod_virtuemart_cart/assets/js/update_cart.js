if (typeof Virtuemart === "undefined")
	var Virtuemart = {};

jQuery(function($) {
	Virtuemart.customUpdateVirtueMartCartModule = function(el, options){
		var base 	= this;
		base.el 	= $(".vmCartModule");
		base.options 	= $.extend({}, Virtuemart.customUpdateVirtueMartCartModule.defaults, options);

		base.init = function(){
			$.ajaxSetup({ cache: false })
			$.getJSON(Virtuemart.vmSiteurl + "index.php?option=com_virtuemart&nosef=1&view=cart&task=viewJS&format=json" + Virtuemart.vmLang,
				function (datas, textStatus) {
					base.el.each(function( index ,  module ) {
						if (datas.totalProduct > 0) {
							$(module).find(".vm_cart_products").html("");
							$.each(datas.products, function (key, val) {
								//$("#hiddencontainer .vmcontainer").clone().appendTo(".vmcontainer .vm_cart_products");
								$(module).find(".hiddencontainer .vmcontainer .product_row").clone().appendTo( $(module).find(".vm_cart_products") );
								$.each(val, function (key, val) {
									$(module).find(".vm_cart_products ." + key).last().html(val);
								});
							});
						}
						$(module).find(".show_cart").html(		datas.cart_show);
						$(module).find(".total_products").html(	datas.totalProductTxt);
						$(module).find(".total").html(		datas.billTotal);
					});
				}
			);
		};
		base.init();
	};
	// Definition Of Defaults
	Virtuemart.customUpdateVirtueMartCartModule.defaults = {
		name1: 'value1'
	};

});

jQuery(document).ready(function( $ ) {
	$(document).off("updateVirtueMartCartModule","body",Virtuemart.customUpdateVirtueMartCartModule);
	$(document).on("updateVirtueMartCartModule","body",Virtuemart.customUpdateVirtueMartCartModule);
});
