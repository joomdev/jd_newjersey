/**
 *
 * Realex payment plugin
 *
 * @author Valérie Isaksen
 * @version $Id: admin.js 8466 2014-10-16 16:06:11Z alatak $
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
    handleIntegrationParameters = function () {
        var integration = $('#params_integration').val();

        $('.redirect, .remote').parents('.control-group').hide();

        if (integration == 'redirect') {
            $('.redirect').parents('.control-group').show();
        } else if (integration == 'remote') {
            $('.remote').parents('.control-group').show();
        }

    }

    handleRealvault = function () {
        var realvault = $('#params_realvault').val();
        var integration = $('#params_integration').val();
        $('.redirect-realvault').parents('.control-group').hide();
        $('.redirect-norealvault').parents('.control-group').hide();

        $('.realvault-param').parents('.control-group').hide();
        $('.realvault').parents('.control-group').hide();
        $('.norealvault').parents('.control-group').hide();

        if (integration == 'redirect') {
            if (realvault == 1) {
                $('.redirect-realvault').parents('.control-group').show();
                $('.realvault').parents('.control-group').show();
                $('.realvault-param').parents('.control-group').show();

            } else {
                $('.redirect-norealvault').parents('.control-group').show();
                $('#params_threedsecure option').eq(0).attr('selected', 'selected');
                // depends on the chosen version
                $('#params_threedsecure').trigger("chosen:updated"); //newer
                $("#params_threedsecure").trigger("liszt:updated"); // our
            }
        } else {
            if (realvault == 1) {
                $('.realvault-param').parents('.control-group').show();
            }
                $('.realvault').parents('.control-group').show();


        }

    }

    handleSettlement = function () {
        var settlement = $('#params_settlement').val();

        $('.settlement').parents('.control-group').hide();

        if (settlement == 'delayed') {
            $('.settlement').parents('.control-group').show();
        }
    }

    handlethreedsecure = function () {
        var threedsecure = $('#params_threedsecure').val();
        var realvault = $('#params_realvault').val();
        var integration = $('#params_integration').val();

        $('.threedsecure').parents('.control-group').hide();

        if ((threedsecure == 1 && integration == 'redirect' && realvault == 1) ||  (threedsecure == 1 && integration == 'remote')) {
            $('.threedsecure').parents('.control-group').show();
        }
    }

    handleDcc = function () {
        var dcc = $('#params_dcc').val();

        $('.dcc').parents('.control-group').hide();
        $('.nodcc').parents('.control-group').hide();

        if (dcc == 1) {
            $('.dcc').parents('.control-group').show();
        }
        if (dcc == 0) {
            $('.nodcc').parents('.control-group').show();
        }
    }

    handleAutoComplete = function () {
        $('#params_merchant_id').attr('autocomplete', 'off');
    }
    /**********/
    /* Events */
    /**********/
    $('#params_integration').change(function () {
        handleIntegrationParameters();
        handleRealvault();

    });
    $('#params_realvault').change(function () {
        handleRealvault();
    });
    $('#params_settlement').change(function () {
        handleSettlement();
    });
    $('#params_dcc').change(function () {
        handleDcc();
    });
    $('#params_threedsecure').change(function () {
        handlethreedsecure();
    });

    /*****************/
    /* Initial calls */
    /*****************/

    handleIntegrationParameters();
    handleRealvault();
    handleSettlement();
    handleDcc();
    handlethreedsecure();
    handleAutoComplete();
});
