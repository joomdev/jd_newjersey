/**
 *
 * Amazon payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id$
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



var amazonPayment = {

        showAmazonButton: function (sellerId, redirect_page, useAmazonAddressBook) {
//    window.onError = null;  // some of the amazon scripts can load in error handlers to report back errors to amazon.  helps them, but keeps you in the dark.

            new OffAmazonPayments.Widgets.Button({
                sellerId: sellerId,
                useAmazonAddressBook: useAmazonAddressBook,
                onSignIn: function (orderReference) {
                    var amazonOrderReferenceId = orderReference.getAmazonOrderReferenceId();
                    window.location = redirect_page + '&session=' + amazonOrderReferenceId;

                },
                onError: function (error) {
                    alert('AMAZON onSignIn(): ' + error.getErrorCode() + ": " + error.getErrorMessage());
                }
            }).bind('payWithAmazonDiv');

        },

        initPayment: function (sellerId, amazonOrderReferenceId, width, height, isMobile, virtuemart_paymentmethod_id, displayMode) {
            amazonPayment.sellerId = sellerId;
            amazonPayment.amazonOrderReferenceId = amazonOrderReferenceId;
            amazonPayment.width = width;
            amazonPayment.height = height;
            amazonPayment.isMobile = isMobile;
            amazonPayment.virtuemart_paymentmethod_id = virtuemart_paymentmethod_id;
            amazonPayment.displayMode = displayMode;
        },



        showAmazonWallet: function () {
            window.onError = null;
            var checkoutFormSubmit = document.getElementById("checkoutFormSubmit");
            if (checkoutFormSubmit === null) {
                 checkoutFormSubmit = document.getElementById("updateOrderId");
            }

            if (checkoutFormSubmit === null ) return;
            checkoutFormSubmit.className = 'vm-button-correct';
            checkoutFormSubmit.className = 'vm-button';
            checkoutFormSubmit.setAttribute('disabled', 'true');
            new OffAmazonPayments.Widgets.Wallet({
                sellerId: amazonPayment.sellerId,
                amazonOrderReferenceId: amazonPayment.amazonOrderReferenceId,  // amazonOrderReferenceId obtained from Button widget
                displayMode: amazonPayment.displayMode,
                design: {
                    size: {width: amazonPayment.width, height: amazonPayment.height}
                },
                onPaymentSelect: function (orderReference) {
                    haveWallet = true;
                    checkoutFormSubmit.className = 'vm-button-correct';
                    checkoutFormSubmit.removeAttribute('disabled');
                },
                onError: function (error) {
                    amazonPayment.onErrorAmazon('amazonShowWallet', error);
                }
            }).bind("amazonWalletWidgetDiv");

        },

        showAmazonAddress: function () {
            new OffAmazonPayments.Widgets.AddressBook({
                sellerId: amazonPayment.sellerId,
                amazonOrderReferenceId: amazonPayment.amazonOrderReferenceId,  // amazonOrderReferenceId obtained from Button widget
                onAddressSelect: function (orderReference) {
                    var url = Virtuemart.vmSiteurl + 'index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=amazon&action=updateCartWithAmazonAddress&virtuemart_paymentmethod_id=' + amazonPayment.virtuemart_paymentmethod_id + Virtuemart.vmLang;
                    //document.id('amazonShipmentNotFoundDiv').set('html', '');
                    amazonPayment.startLoading();
                    jQuery.getJSON(url,
                        function (datas, textStatus) {
                            var errormsg = '';
                            var checkoutFormSubmit = document.getElementById("checkoutFormSubmit");

                            if (typeof datas.error_msg != 'undefined' && datas.error_msg != '') {
                                errormsg = datas.error_msg;
                                checkoutFormSubmit.className = 'vm-button';
                                //checkoutFormSubmit.setAttribute('disabled', 'true');
                                document.id('amazonShipmentsDiv').style.display = 'none';
                                amazonPayment.stopLoading();
                            } else {
                                checkoutFormSubmit.className = 'vm-button-correct';
                                //checkoutFormSubmit.removeAttribute('disabled');
                                var url = Virtuemart.vmSiteurl + 'index.php?option=com_virtuemart&nosef=1&view=cart&task=updatecartJS&forceMethods=1&virtuemart_paymentmethod_id=' + amazonPayment.virtuemart_paymentmethod_id + Virtuemart.vmLang;
                                amazonPayment.updateCart(url,false);
                                amazonPayment.showAmazonWallet();
                            }
                            document.id('amazonErrorDiv').set('html', errormsg);
                        }
                    );
                    amazonPayment.stopLoading();

                },
                displayMode: amazonPayment.displayMode,
                design: {
                    size: {width:amazonPayment.width, height: amazonPayment.height}
                },
                onError: function (error) {
                    amazonPayment.onErrorAmazon('amazonShowAddress', error, amazonPayment.virtuemart_paymentmethod_id);
                },
            }).bind("amazonAddressBookWidgetDiv");

        },

        startLoading: function () {
            //document.getElementsByTagName('body')[0].className += " vmLoading";
            //document.body.innerHTML += "<div class=\"vmLoadingDiv\"></div>";
        },

        stopLoading: function () {
            //document.getElementsByTagName('body')[0].className = document.getElementsByTagName('body')[0].className.replace("vmLoading","");
        },

        onAmazonAddressSelect: function () {
            amazonPayment.updateCartWithAmazonAddress();
        },



        updateCart: function (url,ship) {
            cartform = jQuery("#checkoutForm");

            jQuery.getJSON(url,
                function (datas, textStatus) {
                    var cartview = "";
                    var sel = '#amazonCartDiv';
                    var cont = jQuery(sel);
                    if (datas.msg) {
                        datas.msg = datas.msg.replace('amazonHeader', 'amazonHeaderHide');
                        if(ship)datas.msg = datas.msg.replace('amazonShipmentNotFoundDiv', 'amazonShipmentNotFoundDivHide');
                        /*for (var i = 0; i < datas.msg.length; i++) {
                            cartview += datas.msg[i].toString();
                        }*/
                        var el = jQuery(datas.msg).find(sel);
                        if (! el.length) el = jQuery(datas.msg).filter(sel);
                        if (el.length) {
                            cont.html(el.html());
                        }
                        el = jQuery(datas.msg).find('#cart-js');
                        if (! el.length) el = jQuery(datas.msg).filter('#cart-js');
                        if (el.length) {
                            jQuery('#cart-js').html(el.html());
                        }
                        //jQuery('#amazonCartDiv').html(cartview);
                        jQuery('#amazonHeaderHide').html('');
                        jQuery('#amazonShipmentNotFoundDivHide').html('');
                        amazonPayment.stopLoading();
                    }

                }
            );
        },

        onErrorAmazon: function (from, error) {
            var sessionExpired = "BuyerSessionExpired";
            if (error.getErrorCode() == sessionExpired) {
                var url = Virtuemart.vmSiteurl + 'index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=amazon&action=resetAmazonReferenceId&virtuemart_paymentmethod_id=' + amazonPayment.virtuemart_paymentmethod_id;
                jQuery.getJSON(url, function (data) {
                    var reloadurl = 'index.php?option=com_virtuemart&view=cart';
                    window.location.href = reloadurl;
                });

            }
        },

        /**
         * used in cart_shipment tmpl
         */
        setShipmentReloadWallet: function() {
            amazonPayment.startLoading();
            var virtuemart_shipmentmethod_ids = document.getElementsByName('virtuemart_shipmentmethod_id');
            var virtuemart_shipmentmethod_id = '';

            for (var i = 0, length = virtuemart_shipmentmethod_ids.length; i < length; i++) {
                if (virtuemart_shipmentmethod_ids[i].checked) {
                    virtuemart_shipmentmethod_id = virtuemart_shipmentmethod_ids[i].value;
                    break;
                }
            }
            // VM3 is updateJS
            var url = Virtuemart.vmSiteurl + 'index.php?option=com_virtuemart&nosef=1&view=cart&task=updatecartJS&forceMethods=1&virtuemart_shipmentmethod_id=' + virtuemart_shipmentmethod_id + Virtuemart.vmLang;
            amazonPayment.updateCart(url,true);
        },

        /**
         * used in addressbook_wallet tmpl
         * @param warning
         */
        displayCaptureNowWarning: function(warning) {
            if(document.getElementById("amazonChargeNowWarning") !== null) {
                document.id('amazonChargeNowWarning').set('html',warning);
            }
        },

        leaveAmazonCheckout: function() {
            var url =  Virtuemart.vmSiteurl + 'index.php?option=com_virtuemart&view=plugin&type=vmpayment&name=amazon&action=leaveAmazonCheckout&virtuemart_paymentmethod_id=' + amazonPayment.virtuemart_paymentmethod_id + Virtuemart.vmLang ;
            jQuery.getJSON(url, function(data) {
                var reloadurl = Virtuemart.vmSiteurl +'index.php?option=com_virtuemart&view=cart' + Virtuemart.vmLang;
                window.location.href = reloadurl;
            });

        }
    }
    ;




