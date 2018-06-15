/**
 * orders.js: functions for Order administration
 *
 * @package	VirtueMart
 * @subpackage Javascript Library
 * @author Max Milbers
 * @copyright Copyright (c) 2016 VirtueMart Team. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
if (typeof Virtuemart === "undefined")
	var Virtuemart = {};

(function($) {
	Virtuemart.onReadyOrderStatus = function () {
		$(".orderstatus_select").change(function () {

			var name = $(this).attr("name");
			var brindex = name.indexOf("orders[");
			if (brindex >= 0) {
				//yeh, yeh, maybe not the most elegant way, but it does, what it should
				var s = name.indexOf("[") + 1;
				var e = name.indexOf("]");
				var id = name.substring(s, e);

				var selected = $(this).val();
				var selStr = "[name=\"orders[" + id + "][customer_notified]\"]";
				var elem = $(selStr);

				if ($.inArray(selected, Virtuemart.orderstatus) != -1) {
					elem.attr("checked", true);
					// for the checkbox
					$(this).parent().parent().find("input[name=\"cid[]\"]").attr("checked", true);
				} else {
					elem.attr("checked", false);
				}
			}
		});

		$('.show_comment').click(function () {
			$(this).prev('.element-hidden').show();
			return false
		});
		$('.element-hidden').mouseleave(function () {
			$(this).hide();
		});
		$('.element-hidden').mouseout(function () {
			$(this).hide();
		});
	}
	Virtuemart.set2status = function () {

		var newStatus = $("#order_status_code_bulk").val();

		var customer_notified = $("input[name=\'customer_notified\']").is(":checked");
		var customer_send_comment = $("input[name=\'customer_send_comment\']").is(":checked");
		var update_lines = $("input[name=\'update_lines\']").is(":checked");
		var comments = $("textarea[name=\'comments\']").val();

		field = document.getElementsByName("cid[]");
		var fname = "";
		var sel = 0;
		for (i = 0; i < field.length; i++) {
			if (field[i].checked) {
				fname = "orders[" + field[i].value + "]";
				$("input[name=\'" + fname + "[customer_notified]\']").prop("checked", customer_notified);
				$("input[name=\'" + fname + "[customer_send_comment]\']").prop("checked", customer_send_comment);
				$("input[name=\'" + fname + "[update_lines]\']").prop("checked", update_lines);
				$("textarea[name=\'" + fname + "[comments]\']").text(comments);
				$("#order_status" + i).val(newStatus).trigger("chosen:updated").trigger("liszt:updated");
			}
		}
	};

	Virtuemart.onReadyOrderItems = function () {
		$(document).ready(function () {

			$('.show_element').click(function () {
				$('.element-hidden').toggle();
				$('select').trigger('chosen:updated');
				return false;
			});
            $('.selectItemStatusCode').change(function () {
            	if ($('#updateOrderItemStatus').hasClass('viewMode')){
                    document.orderItemForm.task.value = 'updateOrderItemStatus';
                    document.orderItemForm.submit();
                    return false;
				}
            });
			$('.updateOrderItemStatus').click(function () {
				document.orderItemForm.task.value = 'updateOrderItemStatus';
				document.orderItemForm.submit();
				return false;
			});
			$('.updateOrder').click(function () {
				document.orderForm.submit();
				return false;
			});
			$('.createOrder').click(function () {
				document.orderForm.task.value = 'CreateOrderHead';
				document.orderForm.submit();
				return false;
			});
			$('.newOrderItem').click(function (event) {
				document.orderItemForm.task.value = 'newOrderItem';
				document.orderItemForm.submit();
				return false;
			});
			$('.orderStatFormSubmit').click(function () {
				//document.orderStatForm.task.value = 'updateOrderItemStatus';
				document.orderStatForm.submit();
				return false;
			});
			$('.cancelEdit').click(function (event) {
				Virtuemart.cancelEdit(event);
			});
			$('.enableEdit').click(function (event) {
				Virtuemart.enableEdit(event);
			});
		})
	}


	Virtuemart.removeItem = function (e,id){
		var answer = confirm(Virtuemart.confirmDelete);
		if (answer) {
			document.orderItemForm.task.value = 'removeOrderItem';
			$(document.orderItemForm).append('<input type="hidden" name="orderLineId" value="'+id+'" /> ');
			document.orderItemForm.submit();

		}
		e.preventDefault();
	}

	jQuery(function ($) {

		$('.orderEdit').hide();
		$('.orderView').show();

		/*$('.updateOrderItemStatus').click(function () {
			document.orderItemForm.task.value = 'updateOrderItemStatus';
			document.orderItemForm.submit();
			return false
		});*/

		$('select#virtuemart_paymentmethod_id').change(function () {
			$('span#delete_old_payment').show();
			$('input#delete_old_payment').attr('checked', 'checked');
		});

	});

	Virtuemart.enableEdit = function (e) {
		$('.orderEdit').each(function () {
			var d = $(this).css('visibility') == 'visible';
			$(this).toggle();
			$('.orderEdit').addClass('orderEdit');
		});
		$('.orderView').each(function () {
			$(this).toggle();
		});
        $('.enableEdit').hide();
        $("#updateOrderItemStatus").removeClass('viewMode');
		e.preventDefault();
	};

	Virtuemart.addNewLine = function (e, i) {

		var row = $('#itemTable').find('#lItemRow').html();
		var needle = 'item_id[' + i + ']';

		//var needle = new RegExp('item_id['+i+']','igm');
		while (row.indexOf(needle) !== -1) {
			row = row.replace(needle, 'item_id[0]');
		}

		$('#itemTable').find('#lItemRow').after('<tr>' + row + '</tr>');
		e.preventDefault();
	}

	Virtuemart.cancelEdit = function (e) {
		$('#orderItemForm').each(function () {
			this.reset();
		});
		$('.selectItemStatusCode')
			.find('option:selected').prop('selected', true)
			.end().trigger('liszt:updated');
		$('.orderEdit').hide();
		$('.enableEdit').show();
		$('.orderView').show();
		$("#updateOrderItemStatus").addClass('viewMode');
		e.preventDefault();
	}

	Virtuemart.resetOrderHead = function (e) {
		$('#orderForm').each(function () {
			this.reset();
		});
		$('select#virtuemart_paymentmethod_id')
			.find('option:selected').prop('selected', true)
			.end().trigger('liszt:updated');
		$('select#virtuemart_shipmentmethod_id')
			.find('option:selected').prop('selected', true)
			.end().trigger('liszt:updated');
		e.preventDefault();
	}
})(jQuery)



