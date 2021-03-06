/**
 * @package         Modals
 * @version         9.7.1
 * 
 * @author          Peter van Westen <info@regularlabs.com>
 * @link            http://www.regularlabs.com
 * @copyright       Copyright © 2017 Regular Labs All Rights Reserved
 * @license         http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

var RegularLabsModals = null;
var RLModals          = null;

(function($) {
	"use strict";

	RegularLabsModals = {
		active_modal: null,
		resize_timer: null,
		modal_delay : null,
		window_width: $(window).width(),

		init: function(options) {
			options = options ? options : this.getOptions();

			if (!options) {
				return;
			}

			// Resize Colorbox when resizing window or changing mobile device orientation
			$(window).resize(RegularLabsModals.resizeOnBrowserResize);
			window.addEventListener("orientationchange", RegularLabsModals.resizeOnBrowserResize, false);

			$.each($('.' + options.class), function(i, el) {
				RegularLabsModals.initModal(el);
			});


			$(document).bind('cbox_open', function() {
				RegularLabsModals.window_width = $(window).width();

				$("#colorbox").swipe({
					swipeLeft : function(event, direction, distance, duration, fingerCount) {
						$.colorbox.next();
					},
					swipeRight: function(event, direction, distance, duration, fingerCount) {
						$.colorbox.prev();
					},

					fallbackToMouseEvents: false
				});
			});
		},

		getOptions: function() {
			if (typeof rl_modals_options !== 'undefined') {
				return rl_modals_options;
			}

			if (typeof Joomla === 'undefined' || typeof Joomla.getOptions === 'undefined') {
				console.error('Joomla.getOptions not found!\nThe Joomla core.js file is not being loaded.');
				return false;
			}

			return Joomla.getOptions('rl_modals');
		},

		initModal: function(el) {
			var $el = $(el);

			// Prevent modals from being initialised multiple times when initModals is triggered again
			if ($el.attr('data-modal-done')) {
				return;
			}

			var options = this.getOptions();

			var defaults = $.extend({}, options.defaults);

			// Get data from tag
			$.each(el.attributes, function(index, attr) {
				if (attr.name.indexOf("data-modal-") === 0) {
					var key       = $.camelCase(attr.name.substring(11));
					defaults[key] = attr.value;
				}
			});

			$el.attr('data-modal-done', '1');

			// remove width/height if inner is already set
			if (defaults['innerWidth'] != undefined) {
				delete defaults['width'];
			}
			if (defaults['innerHeight'] != undefined) {
				delete defaults['height'];
			}


			// set true/false values to booleans
			for (var key in defaults) {
				if (defaults[key] == 'true') {
					defaults[key] = true;
				} else if (defaults[key] == 'false') {
					defaults[key] = false;
				} else if (!isNaN(defaults[key])) {
					defaults[key] = parseFloat(defaults[key]);
				}
			}


			
			defaults['onComplete'] = function() {

				RegularLabsModals.active_modal = $el;
				$('.modal_active').removeClass('modal_active');
				$el.addClass('modal_active');

				$('.cboxIframe').attr({
					webkitAllowFullScreen: true,
					mozallowfullscreen   : true,
					allowFullScreen      : true
				});

				RegularLabsModals.resize();
				RegularLabsModals.checkScrollBar();


				$('#colorbox').addClass('complete');
			};

			defaults['onClosed'] = function() {
				$('#colorbox').removeClass('complete');
				$('.modal_active').removeClass('modal_active');
			};


			$el.data('modals_settings', defaults);

			if (!isNaN(defaults.maxWidth) && defaults.maxWidth > ($(window).width() * .95)) {
				defaults.maxWidth = '95%';
			}

			// Bind the modal script to the element
			$el.colorbox(defaults);

		},

		resizeOnBrowserResize: function() {
			if (RegularLabsModals.resize_timer) {
				clearTimeout(RegularLabsModals.resize_timer);
			}

			RegularLabsModals.resize_timer = setTimeout(function() {
				var $modal_wrapper = $("#cboxWrapper");

				if (!$modal_wrapper.is(':visible')) {
					return;
				}

				var $modal = RegularLabsModals.active_modal;

				RegularLabsModals.resize();
				RegularLabsModals.checkScrollBar();
			}, 100);
		},

		checkScrollBar: function() {
			var $colorbox = $('#colorbox');
			var $content  = $('#cboxLoadedContent');

			$colorbox.removeClass('has_scrollbar');

			if ($content.prop('scrollHeight') > $content.innerHeight()) {
				$colorbox.addClass('has_scrollbar');
			}
		},

		resize: function() {
			var self    = this;
			var options = this.getOptions();

			if (!options.auto_correct_size) {
				return;
			}

			setTimeout(function() {
				self.resizeContent();
				self.resizeTitle();
			}, options.auto_correct_size_delay);
		},

		resizeContent: function() {
			var $modal = RegularLabsModals.active_modal;

			if (!$modal) {
				return;
			}

			// Don't resize videos or images
			if ($modal.attr('data-modal-video') || $modal.attr('data-modal-image')) {
				return;
			}

			var modals_settings = $modal.data('modals_settings');

			var $content   = $('#cboxLoadedContent');
			var $container = $('#cboxContent');

			var original_container_width = this.getWidth($container);
			var original_width           = this.getWidth($content);
			var original_height          = this.getHeight($content);

			var container_extra_width = original_container_width - original_width;

			var max_width  = this.getMaxWidth();
			var min_width  = this.getMinWidth();
			var max_height = this.getMaxHeight();
			var min_height = this.getMinHeight();

			var new_width = typeof modals_settings.width !== 'undefined'
				? this.convertWidth(modals_settings.width)
				: original_width;

			var new_height = typeof modals_settings.height !== 'undefined'
				? this.convertHeight(modals_settings.height)
				: original_height;

			new_width  = this.limitValue(new_width, min_width, max_width);
			new_height = this.limitValue(new_height, min_height, max_height);

			var new_container_width = new_width + container_extra_width;

			if (modals_settings.iframe) {
				$container.width(new_container_width);
				this.resizeModal(new_width, new_height);

				return;
			}

			new_container_width = new_width + container_extra_width;

			var $content_copy = $content.clone();
			$content.after($content_copy);

			$content.hide();

			$container.width(new_container_width);
			$content_copy.css({
				'width'  : 'auto',
				'height' : 'auto',
				'display': modals_settings.iframe ? 'block' : 'inline-block'
			});

			var resized_width  = this.getWidth($content_copy);
			var resized_height = this.getHeight($content_copy);

			$container.width(original_container_width);
			$content.show();
			$content_copy.remove();

			new_width  = this.limitValue(resized_width, min_width, max_width);
			new_height = this.limitValue(resized_height, min_height, max_height);

			new_container_width = new_width + container_extra_width;
			$container.width(new_container_width);

			this.resizeModal(new_width, new_height);
		},

		resizeTitle: function() {
			var $title = $('#cboxTitle');

			if (!this.getHeight($title)) {
				return;
			}

			var $content        = $('#cboxLoadedContent');
			var original_height = this.getHeight($content);

			var title_attr     = this.getTitleHeightAndPos();
			var title_height   = title_attr.height;
			var title_position = title_attr.position;

			var margin = parseInt($content.css('margin-' + title_position));

			var diff_height = title_height - margin;

			if (diff_height < 1) {
				return;
			}

			var new_height = original_height + diff_height;
			var max_height = RegularLabsModals.getMaxHeight();

			new_height = Math.min(new_height, max_height);

			var new_content_height = new_height - diff_height;

			$content.css('margin-' + title_position, title_height);

			this.resizeModal(0, new_height);

			$content.height(new_content_height);
		},

		getTitleHeightAndPos: function() {
			var $title = $('#cboxTitle');

			var height_inner = this.getHeight($title);

			if (!height_inner) {
				return {'height': 0, 'position': 'top'};
			}

			var height_outer = $title.outerHeight() + 1;

			var top      = parseInt($title.css('top'));
			var bottom   = parseInt($title.css('bottom'));
			var position = top ? 'bottom' : 'top';

			$title.height(height_inner * 2);

			if (top === parseInt($title.css('top'))) {
				position = 'top';
				height_outer += top;
			} else if (bottom === parseInt($title.css('bottom'))) {
				position = 'bottom';
				height_outer += bottom;
			}

			$title.height('auto');

			return {'height': height_outer, 'position': position};
		},

		resizeModal: function(width, height) {
			var $modal          = RegularLabsModals.active_modal;
			var modals_settings = $modal.data('modals_settings');

			if (width) {
				modals_settings.innerWidth = width;
			}

			if (height) {
				modals_settings.innerHeight = height;
			}

			$modal.colorbox.resize(modals_settings);
		},

		reload: function() {
			var $colorbox       = $('#colorbox');
			var $content        = $('#cboxLoadedContent');
			var $modal          = RegularLabsModals.active_modal;
			var modals_settings = $modal.data('modals_settings');

			var original_fadeOut       = $modal.colorbox.settings.fadeOut;
			var original_initialWidth  = $modal.colorbox.settings.initialWidth;
			var original_initialHeight = $modal.colorbox.settings.initialHeight;
			var original_scrollHeight  = $content.scrollTop();

			$modal.colorbox(modals_settings);

			$modal.colorbox.settings.fadeOut       = 0;
			$modal.colorbox.settings.initialWidth  = this.getWidth($colorbox);
			$modal.colorbox.settings.initialHeight = this.getHeight($colorbox);

			$modal.click();

			$modal.colorbox.settings.fadeOut       = original_fadeOut;
			$modal.colorbox.settings.initialWidth  = original_initialWidth;
			$modal.colorbox.settings.initialHeight = original_initialHeight;

			$content.scrollTop(original_scrollHeight);
		},

		limitValue: function(value, min, max) {
			min = min ? min : 0;
			max = max ? max : 100000;

			value = Math.max(value, min);
			value = Math.min(value, max);

			return value;
		},

		getWidth: function($el) {
			return Math.ceil($el[0].getBoundingClientRect().width)
				- parseInt($el.css('padding-left')) - parseInt($el.css('padding-right'));
		},

		getHeight: function($el) {
			return Math.ceil($el[0].getBoundingClientRect().height)
				- parseInt($el.css('padding-top')) - parseInt($el.css('padding-bottom'));
		},

		getMaxWidth: function() {
			var $modal          = RegularLabsModals.active_modal;
			var modals_settings = $modal.data('modals_settings');

			if ($modal.attr('data-modal-inner-width')) {
				return this.convertWidth($modal.attr('data-modal-inner-width'), true);
			}

			return this.convertWidth(modals_settings.maxWidth);
		},

		getMaxHeight: function() {
			var $modal          = RegularLabsModals.active_modal;
			var modals_settings = $modal.data('modals_settings');

			if ($modal.attr('data-modal-inner-height')) {
				return this.convertHeight($modal.attr('data-modal-inner-height'), true);
			}

			return this.convertHeight(modals_settings.maxHeight);
		},

		getMinWidth: function() {
			var $modal          = RegularLabsModals.active_modal;
			var modals_settings = $modal.data('modals_settings');

			if ($modal.attr('data-modal-inner-width')) {
				return this.convertWidth($modal.attr('data-modal-inner-width'), true);
			}

			return typeof modals_settings.minWidth !== 'undefined'
				? this.convertWidth(modals_settings.minWidth)
				: 0;
		},

		getMinHeight: function() {
			var $modal          = RegularLabsModals.active_modal;
			var modals_settings = $modal.data('modals_settings');

			if ($modal.attr('data-modal-inner-height')) {
				return this.convertHeight($modal.attr('data-modal-inner-height'), true);
			}

			return typeof modals_settings.minHeight !== 'undefined'
				? this.convertHeight(modals_settings.minHeight)
				: 0;
		},

		convertWidth: function(width, inner) {
			var inner = inner ? true : false;

			if (!width) {
				return this.getWidthByPercentage(95);
			}

			if (isNaN(width) && width.indexOf('%') > -1) {
				return this.getWidthByPercentage(width);
			}

			width = parseInt(width);

			var inner_width = inner ? width : this.getInnerWidth(width);
			var outer_width = inner ? this.getOuterWidth(width) : width;

			if (outer_width > ($(window).width() * .95)) {
				return this.getWidthByPercentage(95);
			}

			return inner_width;
		},

		convertHeight: function(height, inner) {
			var inner = inner ? true : false;

			if (!height) {
				return this.getHeigthByPercentage(95);
			}

			if (isNaN(height) && height.indexOf('%') > -1) {
				return this.getHeigthByPercentage(height);
			}

			height = parseInt(height);

			var inner_height = inner ? height : this.getInnerHeight(height);
			var outer_height = inner ? this.getOuterHeight(height) : height;

			if (outer_height > ($(window).height() * .95)) {
				return this.getHeigthByPercentage(95);
			}

			return inner_height;
		},

		getWidthByPercentage: function(percentage) {
			var width = parseInt(percentage) * $(window).width() / 100;

			return this.getInnerWidth(width);
		},

		getHeigthByPercentage: function(percentage) {
			var height = parseInt(percentage) * $(window).height() / 100;

			return this.getInnerHeight(height);
		},

		getInnerWidth: function(width) {
			return parseInt(width - this.getOuterWidthPadding());
		},

		getOuterWidth: function(width) {
			return parseInt(width + this.getOuterWidthPadding());
		},

		getInnerHeight: function(height) {
			return parseInt(height - this.getOuterHeightPadding());
		},

		getOuterHeight: function(height) {
			return parseInt(height + this.getOuterHeightPadding());
		},

		getOuterWidthPadding: function() {
			return parseInt(
				$('#cboxMiddleLeft').outerWidth() + $('#cboxMiddleRight').outerWidth()
				+ parseInt($('#cboxLoadedContent').css('margin-left')) + parseInt($('#cboxLoadedContent').css('margin-right'))
				+ parseInt($('#cboxLoadedContent').css('padding-left')) + parseInt($('#cboxLoadedContent').css('padding-right'))
			);
		},

		getOuterHeightPadding: function() {
			return parseInt(
				$('#cboxTopCenter').outerHeight() + $('#cboxBottomCenter').outerHeight()
				+ parseInt($('#cboxLoadedContent').css('margin-top')) + parseInt($('#cboxLoadedContent').css('margin-bottom'))
				+ parseInt($('#cboxLoadedContent').css('padding-top')) + parseInt($('#cboxLoadedContent').css('padding-bottom'))
			);
		}
	};

	$(document).ready(function() {
		RegularLabsModals.init();

		RLModals = RegularLabsModals;
	});
})(jQuery);
