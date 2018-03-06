/**
 *
 * Paypal payment plugin
 *
 * @author Jeremy Magne
 * @version $Id: paypal.php 7217 2013-09-18 13:42:54Z alatak $
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

jQuery().ready(function($) {

	$('.cc_type_sandbox').change(function() {
		var pmid = $(this).attr('rel');
		var cc_type = $('#cc_type_'+pmid).val();
		switch (cc_type) {
			case 'Visa':
				$('#cc_number_'+pmid).val('4007000000027');
				$('#cc_cvv_'+pmid).val('123');
				break;
			case 'Mastercard':
				$('#cc_number_'+pmid).val('6011000000000012');
				$('#cc_cvv_'+pmid).val('123');
				break;
			case 'Amex':
				$('#cc_number_'+pmid).val('370000000000002');
				$('#cc_cvv_'+pmid).val('1234');
				break;
			case 'Discover':
				$('#cc_number_'+pmid).val('5424000000000015');
				$('#cc_cvv_'+pmid).val('123');
                break;
            case 'Maestro':
                $('#cc_number_'+pmid).val('6763318282526706');
                $('#cc_cvv_'+pmid).val('123');
				break;
			default:
				$('#cc_number_'+pmid).val('');
				$('#cc_cvv_'+pmid).val('');
		}
	});
	
	$('.cc_type_sandbox').trigger('change');

	$('input[name=virtuemart_paymentmethod_id]').change(function() {
		var selectedMethod = $('input[name=virtuemart_paymentmethod_id]:checked').val();
		//$('.paymentMethodOptions').hide();
		$('#paymentMethodOptions_'+selectedMethod).show();
	});

	$('input[name=virtuemart_paymentmethod_id]').trigger('change');
	
});
