/**
 * Created by Milbo on 18.11.2016.
 */

if (typeof Virtuemart === "undefined")
	var Virtuemart = {};



Virtuemart.customfields = jQuery(function($) {

	$(document).ready(function(){
		$('#custom_field').sortable({cursorAt: { top: 0, left: 0 },handle: '.vmicon-16-move'});
		// Need to declare the update routine outside the sortable() function so
		// that it can be called when adding new customfields
		$('#custom_field').bind('sortupdate', function(event, ui) {
			$(this).find('.ordering').each(function(index,element) {
				$(element).val(index);
			});
		});
		$('#custom_categories').sortable({cursorAt: { top: 0, left: 0 },handle: '.vmicon-16-move'});
		$('#custom_categories').bind('sortupdate', function(event, ui) {
			$(this).find('.ordering').each(function(index,element) {
				$(element).val(index);
			});
		});
		$('#custom_products').sortable({cursorAt: { top: 0, left: 0 },handle: '.vmicon-16-move'});
		$('#custom_products').bind('sortupdate', function(event, ui) {
			$(this).find('.ordering').each(function(index,element) {
				$(element).val(index);
			});
		});
	});
	$('select#customlist').chosen().change(function() {
		selected = $(this).find( 'option:selected').val() ;
		$.getJSON(Virtuemart.jsonLink+'&type=fields&id='+selected+'&row='+Virtuemart.nextCustom,
			function(data) {
				$.each(data.value, function(index, value){
					$('#custom_field').append(value);
					$('#custom_field').trigger('sortupdate');
				});
			});
		Virtuemart.nextCustom++;
	});

	$.each($('.cvard'), function(i,val){
		$(val).chosen().change(function() {
			quantity = $(this).parent().find('input[type=\"hidden\"]');
			quantity.val($(this).val());
		});
	});

	$('input#relatedproductsSearch').autocomplete({
		source: Virtuemart.jsonLink+'&type=relatedproducts&row='+Virtuemart.nextCustom,
		select: function(event, ui){
			$('#custom_products').append(ui.item.label);
			$('#custom_products').trigger('sortupdate');
			Virtuemart.nextCustom++;
			$(this).autocomplete( 'option' , 'source' , Virtuemart.jsonLink+'&type=relatedproducts&row='+Virtuemart.nextCustom )
		},
		minLength:1,
		html: true
	});
	$('input#relatedcategoriesSearch').autocomplete({

		source: Virtuemart.jsonLink+'&type=relatedcategories&row='+Virtuemart.nextCustom,
		select: function(event, ui){
			$('#custom_categories').append(ui.item.label);
			$('#custom_categories').trigger('sortupdate');
			Virtuemart.nextCustom++;
			$(this).autocomplete( 'option' , 'source' , Virtuemart.jsonLink+'&type=relatedcategories&row='+Virtuemart.nextCustom )
		},
		minLength:1,
		html: true
	});


	eventNames = 'click.remove keydown.remove change.remove focus.remove'; // all events you wish to bind to

	function removeParent() {$('#customfieldsParent').remove(); }

	$('#customfieldsTable').find('input').each(function(i){
		current = $(this);
		current.click(function(){
			$('#customfieldsParent').remove();
		});
	});

});

Virtuemart.edit_status = jQuery (function($) {

	$(document).ready(function ($) {
		jQuery('#image').change( function() {
			var $newimage = jQuery(this).val();
			jQuery('#product_availability').val($newimage);
			jQuery('#imagelib').attr({ src:Virtuemart.imagePath+$newimage, alt:$newimage });
		});
		jQuery('.js-change-stock').change( function() {

			var in_stock = jQuery('.js-change-stock[name="product_in_stock"]');
			var ordered = jQuery('.js-change-stock[name="product_ordered"]');
			var product_in_stock= parseInt(in_stock.val());
			if ( oldstock == "undefined") var oldstock = product_in_stock ;
			var product_ordered=parseInt(ordered.val());
			if (product_in_stock>product_ordered && product_in_stock!=oldstock )
				jQuery('#notify_users').attr('checked','checked');
			else jQuery('#notify_users').attr('checked',false);
		});
	});


});

Virtuemart.prdcustomer = jQuery(function($) {

	var $customerMailLink = Virtuemart.urlDomain+'/index.php?option=com_virtuemart&view=productdetails&task=sentproductemailtoshoppers&virtuemart_product_id=' +Virtuemart.virtuemart_product_id;
	var $customerMailNotifyLink = 'index.php?option=com_virtuemart&view=product&task=ajax_notifyUsers&virtuemart_product_id=' +Virtuemart.virtuemart_product_id;
	var $customerListLink = 'index.php?option=com_virtuemart&view=product&format=json&type=userlist&virtuemart_product_id=' +Virtuemart.virtuemart_product_id;
	var $customerListNotifyLink = 'index.php?option=com_virtuemart&view=product&task=ajax_waitinglist&virtuemart_product_id=' +Virtuemart.virtuemart_product_id;
	var $customerListtype = 'reserved';

	$(document).ready(function ($) {

		populate_customer_list(jQuery('select#order_status').val());
		customer_initiliaze_boxes();
		jQuery("input:radio[name=customer_email_type],input:checkbox[name=notification_template]").click(function () {
			customer_initiliaze_boxes();
		});
		jQuery('select#order_status').chosen({enable_select_all:true, select_some_options_text:vm2string.select_some_options_text}).change(function () {
			populate_customer_list(jQuery(this).val());
		})
		jQuery('.mailing .button2-left').click(function () {

			email_type = jQuery("input:radio[name=customer_email_type]:checked").val();
			if (email_type == 'notify') {

				var $body = '';
				var $subject = '';
				if (jQuery('input:checkbox[name=notification_template]').not(':checked')) {
					$subject = jQuery('#mail-subject').val();
					$body = jQuery('#mail-body').val();
				}
				var $max_number = jQuery('input[name=notify_number]').val();

				jQuery.post($customerMailNotifyLink, { subject:$subject, mailbody:$body, max_number:$max_number, token:Virtuemart.token },
					function (data) {
						alert(Virtuemart.msgsent);
						jQuery.getJSON($customerListNotifyLink, {tmpl:'component', no_html:1},
							function (data) {
								//			jQuery("#customers-list").html(data.value);
								$html = '';
								jQuery.each(data, function (key, val) {
									if (val.virtuemart_user_id == 0) {
										$html += '<tr><td></td><td></td><td><a href="mailto:' + val.notify_email + '">' + val.notify_email + '</a></td></tr>';
									}
									else {
										$html += '<tr><td>' + val.name + '</td><td>' + val.username + '</td><td><a href="mailto:' + val.notify_email + '">' + val.notify_email + '</a></td></tr>';
									}
								});
								jQuery("#customers-notify-list").html($html);
							}
						);
					}
				);

			}
			else if (email_type == 'customer') {
				var $subject = jQuery('#mail-subject').val();
				var $body = jQuery('#mail-body').val();
				if ($subject == '') {
					alert(Virtuemart.enterSubj);
				}
				else if ($body == '') {
					alert(Virtuemart.enterBody);
				}
				else {
					var $statut = jQuery('select#order_status').val();
					jQuery.post($customerMailLink, { subject:$subject, mailbody:$body, statut:$statut, token:Virtuemart.token },
						function (data) {
							alert(Virtuemart.msgsent);
							//jQuery("#customers-list-msg").html('<strong><?php echo vmText::_ ('COM_VIRTUEMART_PRODUCT_NOTIFY_MESSAGE_SENT')?></strong>');
							//jQuery("#mail-subject").html('');
							jQuery("#mail-body").html('');
						}
					);
				}

			}

		});

	});

	/* JS for list changes */
	function populate_customer_list($status) {
		if ($status == "undefined" || $status == null) $status = '';
		if($status !=''){
			jQuery.getJSON($customerListLink, { order_status:$status  },
				function (data) {
					jQuery("#customers-list").html(data.value);
				});
		}
	}

	function customer_initiliaze_boxes() {
		email_type = jQuery("input:radio[name=customer_email_type]:checked").val();
		if (email_type == 'notify') {
			jQuery('#notify_particulars').show();
			jQuery('#customer-mail-list').hide();
			jQuery('#customer-mail-notify-list').show();
			jQuery("input:radio[name=customer_email_type]").val()
			if (jQuery('input:checkbox[name=notification_template]').is(':checked')) jQuery('#customer-mail-content').hide();
			else  jQuery('#customer-mail-content').show();

		}
		else if (email_type = 'customer') {
			jQuery('#notify_particulars').hide();
			jQuery('#customer-mail-content').show();
			jQuery('#customer-mail-list').show();
			jQuery('#customer-mail-notify-list').hide();
		}
	}

});

// based on http://www.seomoves.org/blog/web-design-development/dynotable-a-jquery-plugin-by-bob-tantlinger-2683/
(function ($) {
	$.fn.extend({
		dynoTable:function (options) {

			var defaults = {
				removeClass: '.price-remove',	//'.row-remover',
				cloneClass: '.price-clone',	//'.row-cloner',
				addRowTemplateId: '#productPriceRowTmpl',	//'#add-template',
				addRowButtonId: '#add_new_price', 	//'#add-row',
				lastRowRemovable:true,
				orderable:true,
				dragHandleClass: ".price_ordering",	//".drag-handle",
				insertFadeSpeed:"slow",
				removeFadeSpeed:"fast",
				hideTableOnEmpty:true,
				onRowRemove:function () {
				},
				onRowClone:function () {
				},
				onRowAdd:function () {
				},
				onTableEmpty:function () {
				},
				onRowReorder:function () {
				}
			};

			options = $.extend(defaults, options);

			var cloneRow = function (btn) {
				var clonedRow = $(btn).closest('tr').clone();
				var tbod = $(btn).closest('tbody');
				insertRow(clonedRow, tbod);
				options.onRowClone();
			}

			var insertRow = function (clonedRow, tbod) {
				var numRows = $(tbod).children("tr").length;
				if (options.hideTableOnEmpty && numRows == 0) {
					$(tbod).parents("table").first().show();
				}

				$(clonedRow).find('*').andSelf().filter('[id]').each(function () {
					//change to something else so we don't have ids with the same name
					// this.id += "_" + numRows;
				});

				//finally append new row to end of table
				$(tbod).append(clonedRow);
				bindActions(clonedRow);
				$(tbod).children("tr:last").hide().fadeIn(options.insertFadeSpeed);
			}

			var removeRow = function (btn) {
				var tbod = $(btn).parents("tbody:first");
				var numRows = $(tbod).children("tr").length;

				if (numRows > 1 || options.lastRowRemovable === true) {
					var trToRemove = $(btn).parents("tr:first");
					$(trToRemove).fadeOut(options.removeFadeSpeed, function () {
						$(trToRemove).remove();
						options.onRowRemove();
						if (numRows == 1) {
							if (options.hideTableOnEmpty) {
								$(tbod).parents('table').first().hide();
							}
							// we want to remove the class remove
							$().removeClass("vmicon-16-remove");
							options.onTableEmpty();
						}
					});
				}
			}

			var bindClick = function (elem, fn) {
				$(elem).click(fn);
			}

			var bindCloneLink = function (lnk) {
				bindClick(lnk, function () {
					var btn = $(this);
					cloneRow(btn);
					return false;
				});
			}

			var bindRemoveLink = function (lnk) {
				bindClick(lnk, function () {
					var btn = $(this);
					removeRow(btn);
					return false;
				});
			}

			var bindActions = function (obj) {
				obj.find(options.removeClass).each(function () {
					bindRemoveLink($(this));
				});

				obj.find(options.cloneClass).each(function () {
					bindCloneLink($(this));
				});
			}

			return this.each(function () {
				//Sanity check to make sure we are dealing with a single case
				if (this.nodeName.toLowerCase() == 'table') {
					var table = $(this);
					var tbody = $(table).children("tbody").first();

					if (options.orderable && $().sortable) {
						$(tbody).sortable({
							handle:options.dragHandleClass,
							helper:function (e, ui) {
								ui.children().each(function () {
									$(this).width($(this).width());
								});
								return ui;
							},
							items:"tr",
							update:function (event, ui) {
								options.onRowReorder();
							}
						});
					}

					$(table).find(options.addRowTemplateId).each(function () {
						$(this).removeAttr("id");
						var tmpl = $(this);
						tmpl.remove();
						bindClick($(options.addRowButtonId), function () {
							//options.onBeforeRowAdd();
							var newTr = tmpl.clone();
							insertRow(newTr, tbody);
							options.onRowAdd();
							return false;
						});
					});
					bindActions(table);

					var numRows = $(tbody).children("tr").length;
					if (options.hideTableOnEmpty && numRows == 0) {
						$(table).hide();
					}
				}
			});
		}
	});

	$.fn.products = function (method) {

		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on Vm2 admin jQuery library');
		}

	};

})(jQuery);