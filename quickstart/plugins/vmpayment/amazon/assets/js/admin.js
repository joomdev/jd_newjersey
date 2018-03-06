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

jQuery().ready(function ($) {

    /************/
    /* Handlers */
    /************/
    handleRegionParameters = function () {
        var region = $('#params_region').val();

        $('.region-other').parents('.control-group').hide();

        if (region === 'OTHER') {
            $('.region-other').parents('.control-group').show();
        }
    }


    handleAuthorizationERPEnabled = function () {
        var authorization = $('#params_authorization_mode_erp_enabled').val();

        if (authorization === 'authorization_done_by_erp') {
            $('.capture_mode').parents('.control-group').hide();
            $('.capture_mode_warning').parents('.control-group').hide();
            $('.capture_mode_warning').hide();
            $('.status_authorization').parents('.control-group').hide();
            $('.status_capture').parents('.control-group').hide();
            $('.status_refunded').parents('.control-group').hide();
            $('.status_refunded').parents('.control-group').hide();
            $('.ipnurl').parents('.control-group').hide();
            $('.ipn_warning').parents('.control-group').hide();
            $('.soft_decline').parents('.control-group').hide();
            $('.sandbox_error_simulation').parents('.control-group').hide();

        } else {
            $('.capture_mode').parents('.control-group').show();
            $('.status_capture').parents('.control-group').show();
            $('.status_authorization').parents('.control-group').show();
            $('.ipnurl').parents('.control-group').show();
            $('.ipn_warning').parents('.control-group').show();
            $('.soft_decline').parents('.control-group').show();
            $('.sandbox_error_simulation').parents('.control-group').show();
            handleCaptureMode();
        }
    }

    handleAuthorizationERPDisabled = function () {
        var authorization = $('#params_authorization_mode_erp_disabled').val();
        $('.capture_mode').parents('.control-group').show();
        handleCaptureMode();
    }

    handleERPMode = function () {
        var erp_mode = $('#params_erp_mode').val();


        $('.authorization_mode_erp_enabled').parents('.control-group').hide();
        $('.authorization_mode_erp_disabled').parents('.control-group').hide();
        $('.erp_mode_enabled_warning').parents('.control-group').hide();

        if (erp_mode === 'erp_mode_disabled') {
            $('.authorization_mode_erp_disabled').parents('.control-group').show();
            handleAuthorizationERPDisabled();
        } else if (erp_mode === 'erp_mode_enabled') {
            $('.erp_mode_enabled_warning').parents('.control-group').show();
            $('.authorization_mode_erp_enabled').parents('.control-group').show();
            handleAuthorizationERPEnabled();
        }
    }

    handleCaptureMode = function () {
        var capture_mode = $('#params_capture_mode').val();
        $('.capture_mode_warning').parents('.control-group').hide();
        $('.capture_mode_warning').hide();

        if (capture_mode === 'capture_immediate') {
            $('.capture_mode_warning').parents('.control-group').show();
            $('.capture_mode_warning').show();
        }
    }


    handleEnvironment = function () {
        var environment = $('#params_environment').val();
        if (environment === 'sandbox') {
            $('.sandbox_error_simulation').parents('.control-group').show();
            $('.ipn-sandbox').show();
            $('.sandbox_warning').show();
        } else {
            $('.sandbox_error_simulation').parents('.control-group').hide();
            $('.ipn-sandbox').hide();
            $('.sandbox_warning').hide();
        }
    }

    handleIPNDisabled = function () {
        var ipn_reception = $('#params_ipn_reception').val();
        $('.ipn_reception_disabled').parents('.control-group').hide();
        $('.ipnurl').parents('.control-group').hide();
        $('.ipn_warning').parents('.control-group').hide();
        if (ipn_reception === 'ipn_reception_disabled') {
            $('.ipn_reception_disabled').parents('.control-group').show();
        } else {
            $('.ipnurl').parents('.control-group').show();
            $('.ipn_warning').parents('.control-group').show();
        }
    }

    /**********/
    /* Events */
    /**********/
    $('#params_region').change(function () {
        handleRegionParameters();
    });
    $('#params_erp_mode').change(function () {
        handleERPMode();
    });
    $('#params_authorization_mode_erp_enabled').change(function () {
        handleAuthorizationERPEnabled();
    });
    $('#authorization_mode_erp_disabled').change(function () {
        handleAuthorizationERPDisabled();
    });
    $('#params_capture_mode').change(function () {
        handleCaptureMode();
    });

    $('#params_environment').change(function () {
        handleEnvironment();
    });
    $('#params_ipn_reception').change(function () {
        handleIPNDisabled();
    });

    /*****************/
    /* Initial calls */
    /*****************/
    handleRegionParameters();
    handleERPMode();
    handleAuthorizationERPEnabled();
    handleAuthorizationERPDisabled();
    handleEnvironment();
    handleCaptureMode();
    handleIPNDisabled();

});
