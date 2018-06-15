/**
 * Created by Milbo on 18.11.2016.
 */

if (typeof Virtuemart === "undefined")
	var Virtuemart = {};

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
				insertRowPlace:"last",
				hideTableOnEmpty:true,
				onRowRemove:function () {
				},
                onBeforeRowInsert:function (newTr) {
                },

				onRowClone:function () {
				},
				onRowAdd:function (newTr) {
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
                $(clonedRow).find(".chzn-container").remove();
                $(clonedRow).find('select').each(function () {
                    $(this).removeClass("chzn-done").addClass("vm-chzn-add").show();
                });

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
					var randomNumber = Math.floor(Math.random() * 100);
                    this.id += '__c'+randomNumber;
				});
                options.onBeforeRowInsert(clonedRow);

				if (options.insertRowPlace=="last") {
					//finally append new row to end of table
					$(tbod).append(clonedRow);
				} else {
                    $(tbod).find(options.insertRowPlace +':last').after(clonedRow);
				}


                bindActions(clonedRow);
                console.log('executed insertRow');
				jQuery("select.vm-chzn-add").chosen({enable_select_all: true,select_all_text : vm2string.select_all_text,select_some_options_text:vm2string.select_some_options_text,disable_search_threshold: 5});
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
							options.onRowAdd(newTr);
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

})(jQuery);