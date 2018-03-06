/**
 *
 * Paypal payment plugin
 *
 * @author Jeremy Magne
 * @author Valérie Isaksen
 * @version $Id: paypal.php 7217 2013-09-18 13:42:54Z alatak $
 * @package VirtueMart
 * @subpackage payment
 * Copyright (C) 2004-2014 Virtuemart Team. All rights reserved.
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
    handleCredentials = function () {

        var sandbox = $('#params_sandbox').val();

        if (sandbox == 1) {
            var sandboxmode = 'sandbox';
        } else {
            var sandboxmode = 'live';
        }


        $('.live,.sandbox').closest('.control-group').hide();
        $('.get_sandbox_credentials').hide();
        $('.get_live_credentials').hide();

        if (sandboxmode == 'live') {
            $('.live').closest('.control-group').show();
            $('.get_live_credentials').show();

        } else {
            $('.sandbox').closest('.control-group').show();
            $('.get_sandbox_credentials').show();
        }
    }


    /**********/
    /* Events */
    /**********/
    $('#params_sandbox').change(function () {
        handleCredentials();
        handleAuthentication();
    });


    handleCredentials();


});
