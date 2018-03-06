/**
 *
 * KlarnaCheckout payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id:$
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



var klarnaCheckoutPayment = {
    initPayment: function (hideBTST) {

        if (hideBTST == 1) {
            jQuery(".billto-shipto").hide();
        }
        jQuery("#checkoutFormSubmit").hide();
        jQuery(".vm-fieldset-tos").hide();
    },


    updateCart: function (klarnaData, virtuemart_paymentmethod_id) {
        var zip = encodeURIComponent(klarnaData.postal_code);
        var email = encodeURIComponent(klarnaData.email);
        var given_name = encodeURIComponent(klarnaData.given_name);
        var family_name = encodeURIComponent(klarnaData.family_name);
        console.log('updateCart:' + zip + ' '+ email);

        //if (zip === '') return;
        var url = vmSiteurl + 'index.php?option=com_virtuemart&view=plugin&type=vmpayment&nosef=1&name=klarnacheckout&loadJS=1&action=updateCartWithKlarnacheckoutAddress&virtuemart_paymentmethod_id=' + virtuemart_paymentmethod_id + '&zip='+zip + '&email='+email + '&family_name='+family_name+ '&given_name='+given_name + '&lang='+vmLang;

jQuery.ajax({
            type: "POST",
            cache: false,
            dataType: "html",
            url: url

        }).success(
            function (datas) {
				console.log('updateCart: success');

				window._klarnaCheckout(function (api) {
					console.log(' updateSnippet suspend');
					api.suspend();
				});
				Virtuemart.updFormS();
                //document.id('kco-shipment-method').set('html', 'SELECT A SHIPMENT FIRST');
				window._klarnaCheckout(function (api) {
					console.log('updateSnippet resume');
					api.resume();
            });
        });
    },


    updateSnippet: function ( ) {

                    window._klarnaCheckout(function (api) {
                        console.log(' updateSnippet suspend');
                        api.suspend();
                    });
                    Virtuemart.updFormS();

                    window._klarnaCheckout(function (api) {
                        console.log('updateSnippet resume');
                        api.resume();
                    });
    },

    leaveKarnaCheckout: function(virtuemart_paymentmethod_id) {
        var url =  vmSiteurl + 'index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=klarnacheckout&action=leaveKlarnaCheckout&virtuemart_paymentmethod_id='+virtuemart_paymentmethod_id +  '&lang='+vmLang;
        jQuery.getJSON(url, function(data) {
            var reloadurl = vmSiteurl +'index.php?option=com_virtuemart&view=cart' +  '&lang='+vmLang;
            window.location.href = reloadurl;
        });

    },


}


