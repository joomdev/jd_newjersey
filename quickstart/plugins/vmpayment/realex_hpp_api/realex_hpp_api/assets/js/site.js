/**
 *
 * Realex payment plugin
 *
 * @author Valerie Isaksen
 * @version $Id: site.js 8200 2014-08-14 11:09:44Z alatak $
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
jQuery().ready(function ($) {

    /************/
    /* Handlers */
    /************/
    handleRealexRemoteCCForm = function () {
        var hasCreditcardsDropDownClass = $("#vmpayment_cardinfo > *").hasClass("creditcardsDropDown");
        if (hasCreditcardsDropDownClass) {
            var CCselected = $(".creditcardsDropDown input[type='radio']:checked").val();
            $('.realexRemoteCCForm').hide();
            $('.vmpayment_cc_cvv_realvault').show();
            if (CCselected == -1) {
                $('.realexRemoteCCForm').show();
                $('.vmpayment_cc_cvv_realvault').hide();
            }
        }

    }


    /**********/
    /* Events */
    /**********/
    $('.realexListCC').change(function () {
        handleRealexRemoteCCForm();

    });

    /*****************/
    /* Initial calls */
    /*****************/
    handleRealexRemoteCCForm();

});

