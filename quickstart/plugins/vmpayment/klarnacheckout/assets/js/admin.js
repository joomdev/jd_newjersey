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

jQuery().ready(function ($) {



    /************/
    /* Handlers */
    /************/
    handleLogo = function () {
        var payment_logos = $("input[name='params[payment_logos]']:checked").val();

        $('.show_payment_logo').parents('.control-group').hide();

        if (payment_logos == '1') {
            $('.show_payment_logo').parents('.control-group').show();
        }
    }
    /**********/
    /* Events */
    /**********/
    $("input[name='params[payment_logos]']").change(function () {
        handleLogo();
    });
    /*****************/
    /* Initial calls */
    /*****************/
    handleLogo();


});
