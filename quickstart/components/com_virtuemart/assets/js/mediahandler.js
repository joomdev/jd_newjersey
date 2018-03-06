/**
 * mediahandler.js: for VirtueMart Mediahandler
 *
 * @package    VirtueMart
 * @subpackage Javascript Library
 * @authors    Patrick Kohl, Max Milbers
 * @copyright  Copyright (c) 2011-2016 VirtueMart Team. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

jQuery(document).ready(function($){

	var media = $('#searchMedia').data();
	var searchMedia = $('input#searchMedia');
	searchMedia.click(function () {
		if (media.start>0) media.start=0;
	});
	searchMedia.autocomplete({
		source: Virtuemart.medialink,
		select: function(event, ui){
			$('#ImagesContainer').append(ui.item.label);
			//$(this).autocomplete( 'option' , 'source' , '". JURI::root(false) ."administrator/index.php?option=com_virtuemart&view=product&task=getData&format=json&type=relatedcategories&row='+nextCustom )
		},
		minLength:1,
		html: true
	});
	$('.js-pages').click(function (e) {
		e.preventDefault();
		if (searchMedia.val() =='') {
			searchMedia.val(' ');
			media.start = 0;
		} else if ($(this).hasClass('js-next')) media.start = media.start+16 ;
		else if (media.start > 0) media.start = media.start-16 ;
		searchMedia.autocomplete( 'option' , 'source' , Virtuemart.medialink+'&start='+media.start );
		searchMedia.autocomplete( 'search');
	});
	$('[name="upload"]').on ('change', function (){
		var rr = $(this).parent().find("[name='media[media_action]']:checked");
		if (typeof $(rr[0]).val() != 'undefined' && $(rr[0]).val() == 0) {
			var rs = $(this).parent().find("[id='media[media_action]upload']").attr('checked', true);
		}
	});
	$('#ImagesContainer').sortable({
		update: function(event, ui) {
			$(this).find('.ordering').each(function(index,element) {
				$(element).val(index);
			});
		}
	});
});

(function ($) {

	var methods = {
	media:function (mediatype, total) {
		var page = 0,
			max = 24,
			container = $(this);
		var pagetotal = Math.ceil(total / max);
		var cache = new Array();

		var formatTitle = function (title, currentArray, currentIndex, currentOpts) {
			var pagination = '' , pagetotal = total / max;
			if (pagetotal > 0) {
				pagination = '<span><<</span><span><</span>';
				for (i = 0; i < pagetotal; i++) {
					pagination += '<span>' + (i + 1) + '</span>';
				}
				pagination += '<span>></span><span>>></span>';
			}
			return '<div class="media-pagination">' + (title && title.length ? '<b>' + title + '</b>' : '' ) + ' ' + pagination + '</div>';
		}

		$("#fancybox-title").delegate(".media-pagination span", "click", function (event) {
			var newPage = $(this).text();
			display(newPage);
			event.preventDefault();
		});
		container.delegate("a.vm_thumb", "click", function (event) {
			$.fancybox({
				"type":"image",
				"titlePosition":"inside",
				"title":this.title,
				"href":this.href
			});
			event.preventDefault();
		});
		$("#media-dialog").delegate(".vm_thumb_image", "click", function (event) {
			event.preventDefault();
			var id = $(this).find('input').val(), ok = 0;
			var inputArray = new Array();
			$('#ImagesContainer input:hidden').each(
				function () {
					inputArray.push($(this).val())
				}
			);
			if ($.inArray(id, inputArray) == -1) {
				that = $(this);
				$(this).clone().appendTo(container).unbind("click").append('<div class="vmicon vmicon-16-remove 4remove" title="remove"></div><div class="edit-24-grey" title="' + vm2string.editImage + '"><div>');
				that.hide().fadeIn();
			}

		});

		$("#admin-ui-tabs").delegate("div.4remove", "click", function () {
			$(this).closest(".vm_thumb_image").fadeOut("500", function () {
				$(this).remove();
			});
		});
		$("#admin-ui-tabs").delegate("span.4remove", "click", function () {
			$(this).closest(".removable").fadeOut("500", function () {
				$(this).remove()
			});
		});

		$("#addnewselectimage2").fancybox({
			"hideOnContentClick":false,
			"autoDimensions":true,
			"titlePosition":"inside",
			"title":"Media list",
			"titleFormat":formatTitle,
			"onComplete":function () {
				$('.media-pagination').children().eq(page + 3).addClass('media-page-selected');
			}
		});

		container.delegate(".edit-24-grey", "click", function () {
			var data = $(this).parent().find("input").val();
			$.getJSON("index.php?option=com_virtuemart&view=media&task=viewJson&format=json&virtuemart_media_id=" + data,
				function (datas, textStatus) {
					if (datas.msg == "OK") {
						$("#vm_display_image").attr("src", datas.file_root + datas.file_url);
						$("#vm_display_image").attr("alt", datas.file_title);
						$("#file_title").html(datas.file_title);
						if (datas.published == 1) $("#adminForm [name='media[media_published]']").attr('checked', true);
						else $("#adminForm [name=media_published]").attr('checked', false);
						if (datas.file_is_downloadable == 0) {
							$("#media_rolesfile_is_displayable").attr('checked', true);
							//$("#adminForm [name=media_roles]").filter("value='file_is_downloadable'").attr('checked', false);
						}
						else {
							//$("#adminForm [name=media_roles]").filter("value='file_is_displayable'").attr('checked', false);
							$("#media_rolesfile_is_downloadable").attr('checked', true);
						}
						$("#adminForm [name='media[file_title]']").val(datas.file_title);
						$("#adminForm [name='media[file_description]']").val(datas.file_description);
						$("#adminForm [name='media[file_meta]']").val(datas.file_meta);
						$("#adminForm [name='media[file_class]']").val(datas.file_class);
						$("#adminForm [name='media[file_url]']").val(datas.file_url);
						$("#adminForm [name='media[file_url_thumb]']").val(datas.file_url_thumb);
						var lang = datas.file_lang.split(',');
						$("#adminForm [name='media[active_languages][]']").val(lang).trigger("liszt:updated");
						$("[name='media[active_media_id]']").val(datas.virtuemart_media_id);
						if (typeof datas.file_url_thumb !== "undefined") {
							$(".vm_thumb_image").attr("src", datas.file_root + datas.file_url_thumb_dyn);
						}
						else {
							$(".vm_thumb_image").attr("src", "");
						}
					} else $("#file_title").html(datas.msg);
				});
		});

		var display = function (num) {
			if (typeof this.page == "undefined") {
				this.oldPage = this.page = 0;

			}
			if (typeof display.cache == "undefined") {
				display.cache = new Array();
			}
			switch (num) {
				case '<':
					if (this.page > 0) --this.page;
					else return;
					break;
				case '>':
					if (this.page < pagetotal - 1) ++this.page;
					else return;
					break;
				case '<<':
					this.page = 0;
					break;
				case '>>':
					this.page = pagetotal - 1;
					break;
				default :
					this.page = num - 1;
					break;
			}
			if (this.oldPage != this.page) {
				//var cache = this.cache ;
				var start = this.page;
				if (typeof display.cache[start] == "undefined") {
					$.getJSON("index.php?option=com_virtuemart&view=media&task=viewJson&format=json&mediatype=" + mediatype + "&start=" + start,
						function (data) {
							if (data.imageList != "ERROR") {
								display.cache[start] = data.imageList;
								$("#media-dialog").html(display.cache[start]);
								$(".page").text("Page(s) " + (start + 1));
							} else {
								$(".page").text("No  more results : Page(s) " + (start + 1));
							}
						}
					);
				} else $("#media-dialog").html(display.cache[start]);
				page = this.oldPage = this.page;
				$('.media-pagination').children().removeClass('media-page-selected');
				$('.media-pagination').children().eq(start + 3).addClass('media-page-selected');
			}
		}
	}
};
$.fn.vmmedia = function (method) {

	if (methods[method]) {
		return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
	} else if (typeof method === 'object' || !method) {
		return methods.init.apply(this, arguments);
	} else {
		$.error('Method ' + method + ' does not exist on Vm2 admin jQuery library');
	}

};
})(jQuery);
	//$('#ImagesContainer').media()

