/**
 *
 * Paybox payment plugin
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

jQuery().ready(function ($) {

    /************/
    /* handlers */
    /************/
    handleDebitType = function () {
        var debit_type = $('#params_debit_type').val();

        $('.authorization_only, .authorization_capture').parents('.control-group').hide();

        if (debit_type == 'authorization_only') {
            $('.authorization_only').parents('.control-group').show();
        } else if (debit_type == 'authorization_capture') {
            $('.authorization_capture').parents('.control-group').show();
        }
    }
    handle3Dsecure = function () {
        var activate_3dsecure = $('#params_activate_3dsecure').val();

        $('.activate_3dsecure').parents('.control-group').hide();

        if (activate_3dsecure == 'selective') {
            $('.activate_3dsecure').parents('.control-group').show();
        } else if (activate_3dsecure == 'active') {
            $('.activate_3dsecure.activate_3dsecure_warning').parents('.control-group').show();
        }
    }
    handleIntegration = function () {
        var integration = $('#params_integration').val();

        $('.integration ').parents('.control-group').hide();

        if (integration == 'recurring') {
            $('.recurring').parents('.control-group').show();
        } else if (integration == 'subscribe') {
            $('.subscribe').parents('.control-group').show();
        }
    }
    handleShopMode = function () {
        var shop_mode = $('#params_shop_mode').val();

        $('.shop_mode ').parents('.control-group').hide();

        if (shop_mode == 'test') {
            $('.shop_mode').parents('.control-group').show();
        }
    }
    /**********/
    /* Events */
    /**********/
    $('#params_debit_type').change(function () {
        handleDebitType();

    });
    $('#params_activate_3dsecure').change(function () {
        handle3Dsecure();

    });
    $('#params_activate_recurring').change(function () {
        handlepPaymentplan();

    });
    $('#params_shop_mode').change(function () {
        handleShopMode();

    });
    $('#params_integration').change(function () {
        handleIntegration();

    });
    /*****************/
    /* Initial calls */
    /*****************/
    handleShopMode();
    handleDebitType();
    handle3Dsecure();
    handleIntegration();
});
