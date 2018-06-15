/**
 * dynupdate.js: Dynamic update of product content for VirtueMart
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author Max Galt
 * @copyright Copyright (c) 2014 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
jQuery(function($) {

    // Add to cart and other scripts may check this variable and return while
    // the content is being updated.
    Virtuemart.isUpdatingContent = false;
	Virtuemart.recalculate = false;
	Virtuemart.recalculate = false;
	Virtuemart.setBrowserState = true;

    Virtuemart.updateContent = function(url, callback) {

        if(Virtuemart.isUpdatingContent) return false;
        Virtuemart.isUpdatingContent = true;
        urlSuf='tmpl=component&format=html&dynamic=1';
        var glue = '&';
        if(url.indexOf('&') == -1 && url.indexOf('?') == -1){
			glue = '?';
        }
        url += glue+urlSuf;
		$(this).vm2front("startVmLoading");
		$.ajax({
            url: url,
            dataType: 'html',
            success: function(data) {
				var title = $(data).filter('title').text();
				$('title').text(title);
				var el = $(data).find(Virtuemart.containerSelector);
				if (! el.length) el = $(data).filter(Virtuemart.containerSelector);
				if (el.length) {
					Virtuemart.container.html(el.html());

					Virtuemart.updateCartListener();
					Virtuemart.updateDynamicUpdateListeners();

					if (Virtuemart.updateImageEventListeners) Virtuemart.updateImageEventListeners();
					if (Virtuemart.updateChosenDropdownLayout) Virtuemart.updateChosenDropdownLayout();
					//Virtuemart.product($("form.product"));

					if(Virtuemart.recalculate) {
						$("form.js-recalculate").each(function(){
							if ($(this).find(".product-fields").length && !$(this).find(".no-vm-bind").length) {
								var id= $(this).find('input[name="virtuemart_product_id[]"]').val();
								Virtuemart.setproducttype($(this),id);
							}
						});
					}
				}
				Virtuemart.isUpdatingContent = false;
				if (callback && typeof(callback) === "function") {
					callback();
				}
				$(this).vm2front("stopVmLoading");
            },
			error: function(datas) {
				alert('Error updating page');
				Virtuemart.isUpdatingContent = false;
				$(this).vm2front("stopVmLoading");
			},
			statusCode: {
				404: function() {
					Virtuemart.isUpdatingContent = false;
					$(this).vm2front("stopVmLoading");
					alert( "page not found" );
				}
			}
        });
        Virtuemart.isUpdatingContent = false;
    }

    // GALT: this method could be renamed into more general "updateEventListeners"
    // and all other VM init scripts placed in here.
    Virtuemart.updateCartListener = function() {
        // init VM's "Add to Cart" scripts should be in a function registered for the trigger, so long, just quickn dirty
		if (typeof Virtuemart.product !== "undefined"){
			Virtuemart.product($(".product"));
		}
		$('body').trigger('updateVirtueMartProductDetail');
    }

    Virtuemart.updL = function (event) {
        event.preventDefault();
        var url = $(this).attr('href');
        Virtuemart.setBrowserNewState(url);
        Virtuemart.updateContent(url);
    }

    Virtuemart.upd = function(event) {
        event.preventDefault();
        var url = $(this).attr('url');
        if (typeof url === typeof undefined || url === false) {
            url = $(this).val();
        }
        if(url!=null){
			url = url.replace(/amp;/g, '');
            Virtuemart.setBrowserNewState(url);
            Virtuemart.updateContent(url);
        }
    };

	Virtuemart.updForm = function(event) {

		cartform = $("#checkoutForm");
		carturl = cartform.attr('action');
		if (typeof carturl === typeof undefined || carturl === false) {
			carturl = $(this).attr('url');
			console.log('my form no action url, try attr url ',cartform);
			if (typeof carturl === typeof undefined || carturl === false) {
				carturl = 'index.php?option=com_virtuemart&view=cart'; console.log('my form no action url, try attr url ',carturl);
			}
		}
		urlSuf='tmpl=component';
		carturlcmp = carturl;
		if(carturlcmp.indexOf(urlSuf) == -1){
			var glue = '&';
			if(carturlcmp.indexOf('&') == -1 && carturlcmp.indexOf('?') == -1){
				glue = '?';
			}
			carturlcmp += glue+urlSuf;
		}

		cartform.submit(function() {
			if(Virtuemart.isUpdatingContent) return false;
			Virtuemart.isUpdatingContent = true;
			$(this).vm2front("startVmLoading");

			$.ajax({
				type: "POST",
				url: carturlcmp,
				dataType: "html",
				data: cartform.serialize(), // serializes the form's elements.
				success: function(datas) {

					if (typeof window._klarnaCheckout !== "undefined"){
						window._klarnaCheckout(function (api) {
							//console.log(' updateSnippet suspend');
							api.suspend();
						});
					}


					var el = $(datas).find(Virtuemart.containerSelector);
					if (! el.length) el = $(datas).filter(Virtuemart.containerSelector);
					if (el.length) {
						Virtuemart.container.html(el.html());
						//Virtuemart.updateCartListener();
						//Virtuemart.updDynFormListeners();
						//Virtuemart.updateCartListener();

						if (Virtuemart.updateImageEventListeners) Virtuemart.updateImageEventListeners();
						if (Virtuemart.updateChosenDropdownLayout) Virtuemart.updateChosenDropdownLayout();
					}
					jQuery('body').trigger('updateVirtueMartCartModule');
					Virtuemart.setBrowserNewState(carturl);
					Virtuemart.isUpdatingContent = false;
					$(this).vm2front("stopVmLoading");
					if (typeof window._klarnaCheckout !== "undefined"){
						window._klarnaCheckout(function (api) {
							//console.log(' updateSnippet suspend');
							api.resume();
						});
					}
				},
				error: function(datas) {
					alert('Error updating cart');
					Virtuemart.isUpdatingContent = false;
					$(this).vm2front("stopVmLoading");
				},
				statusCode: {
					404: function() {
						Virtuemart.isUpdatingContent = false;
						$(this).vm2front("stopVmLoading");
						alert( "page not found" );
					}
				}
			});
			return false; // avoid to execute the actual submit of the form.
		});
	};

	Virtuemart.updFormS = function(event) {
		Virtuemart.updForm();
		$("#checkoutForm").submit();
	}

	Virtuemart.updDynFormListeners = function() {

		$('#checkoutForm').find('*[data-dynamic-update=1]').each(function(i, el) {
			var nodeName = el.nodeName;
			el = $(el);
			//console.log('updDynFormListeners ' + nodeName, el);
			switch (nodeName) {
				case 'BUTTON':
					el[0].onchange = null;
					el.off('click',Virtuemart.updForm);
					el.on('click',Virtuemart.updForm);
				default:
					el[0].onchange = null;
					el.off('click',Virtuemart.updFormS);
					el.on('click',Virtuemart.updFormS);
					break;
			}
		});
	}

    Virtuemart.updateDynamicUpdateListeners = function() {
        $('*[data-dynamic-update=1]').each(function(i, el) {
            var nodeName = el.nodeName;
            el = $(el);
            //console.log('updateDynamicUpdateListeners '+nodeName, el);
            switch (nodeName) {
                case 'A':
					el[0].onclick = null;
                    el.off('click',Virtuemart.updL);
                    el.on('click',Virtuemart.updL);
                    break;
                default:
					el[0].onchange = null;
                    el.off('change',Virtuemart.upd);
                    el.on('change',Virtuemart.upd);
            }
        });
    }

    var everPushedHistory = false;
    var everFiredPopstate = false;
    Virtuemart.setBrowserNewState = function (url) {
    	if(!Virtuemart.setBrowserState) return false;

        if (typeof window.onpopstate == "undefined")
            return;
        var stateObj = {
            url: url
        }
        everPushedHistory = true;
        try {
            history.pushState(stateObj, "", url);
        }
        catch(err) {
            // Fallback for IE
            window.location.href = url;
            return false;
        }
    }

    Virtuemart.browserStateChangeEvent = function(event) {
        // Fix. Chrome and Safari fires onpopstate event onload.
        // Also fix browsing through history when mixed with Ajax updates and
        // full updates.
        if (!everPushedHistory && event.state == null && !everFiredPopstate)
            return;

        everFiredPopstate = true;
        var url;
        if (event.state == null) {
            url = window.location.href;
        } else {
            url = event.state.url;
        }
        Virtuemart.updateContent(url);
    }
    window.onpopstate = Virtuemart.browserStateChangeEvent;

});
