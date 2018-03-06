/**
 * vmsite.js: General Javascript Library for VirtueMart Frontpage
 *
 *
 * @package    VirtueMart
 * @subpackage Javascript Library
 * @authors    Patrick Kohl, Max Milbers, Abhishek Das
 * @copyright  Copyright (c) 2014-2016 VirtueMart Team. All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */
if (typeof Virtuemart === "undefined")
	var Virtuemart = {};
//var Virtuemart = window.Virtuemart || {};

(function($) {
	var methods = {
			_cache: {},
			list: function(options) {
				if (typeof Virtuemart.vmSiteurl === 'undefined') Virtuemart.vmSiteurl = '';

				return this.each(function() {
					this.self = $(this);
					this.opt = this.opt || {};
					this.opt = $.extend(true, {}, $.fn.vm2front.defaults, this.opt, methods._processOpt(options));
					this.form = $(this).closest('form');
					this.state_fields = $(this.form).find(this.opt.state_field_selector);

					methods._update.call(this, false);

					$(this).on('change', function() {
						methods._update.call(this, this.opt.show_list_loader);
					});
				});
			},
			setOpt: function(options) {
				return this.each(function() {
					this.opt = $.extend(true, {}, $.fn.vm2front.defaults, this.opt, methods._processOpt(options));
				});
			},
			_processOpt: function(options) {
				if (options.hasOwnProperty('dest')) {
					options['state_field_selector'] = options.dest;
					delete options.dest;
				}
				if (options.hasOwnProperty('ids')) {
					options['selected_state_ids'] = options.ids;
					delete options.ids;
				}
				if (options.hasOwnProperty('prefiks')) {
					options['field_prefix'] = options.prefiks;
					delete options.prefiks;
				}
				return options;
			},
			_update: function(showLoader) {
				var that = this,
					selected_country_ids = $(this).val() || [];

				if (!$.isArray(selected_country_ids)) {
					selected_country_ids = $.makeArray(selected_country_ids);
				}

				if (selected_country_ids.length) {
					selected_country_ids = selected_country_ids.join(',');

					if (methods._cache.hasOwnProperty(selected_country_ids)) {
						methods._addToList.call(that, methods._cache[selected_country_ids]);
					} else {
						$.ajax({
							dataType: 'JSON',
							url: Virtuemart.vmSiteurl + 'index.php?option=com_virtuemart&view=state&format=json&virtuemart_country_id=' + selected_country_ids,
							beforeSend: function() {
								if (showLoader) {
									methods.startVmLoading();
								}
							},
							success: function(data) {
								if (data) {
									methods._cache[selected_country_ids] = data;
									methods._addToList.call(that, methods._cache[selected_country_ids]);
								}
								if (showLoader) {
									methods.stopVmLoading();
								}
							},
							error: function(e, t, n) {
								console.log(e);
								console.log(t);
								console.log(n);
								if (showLoader) {
									methods.stopVmLoading();
								}
							}
						})
					}
				}
			},
			_addToList: function(data) {
				var that = this,
					dataType = $.type(data),
					i = 0;
				selected_state_ids = [];

				if (that.opt.selected_state_ids && that.opt.selected_state_ids.length) {
					if (that.opt.selected_state_ids) {
						if ($.type(that.opt.selected_state_ids) === 'string') {
							selected_state_ids = that.opt.selected_state_ids.split(',');
						}
						if ($.type(that.opt.selected_state_ids) === 'number') {
							selected_state_ids.push(that.opt.selected_state_ids);
						}
					}
				}

				$(that.state_fields).each(function() {
					var state_field = this,
						id = $(this).attr('id'),
						form = $(that.form),
						label = id && form.length ? form.find('label[for="' + id + '"]') : null,
						required = $(state_field).data('required'),
						hasData = false;

					$(state_field).data('label', label);

					if ((required !== true && required !== false) || ($(state_field).attr('required') || $(state_field).hasClass('required'))) {
						if ($(state_field).attr('required') || $(state_field).hasClass('required')) {
							$(state_field).data('required', true).removeAttr('required').removeAttr('aria-required').removeClass('required');
							if (label && label.length && that.opt.asterisk_class) {
								label.find('.' + that.opt.asterisk_class).hide();
							}
						} else {
							$(state_field).data('required', false);
						}
					}

					$('optgroup', state_field).each(function() {
						if ($(this).data('ajaxloaded')) $(this).remove();
					});

					if (dataType === 'object' || dataType === 'array') {
						hasData = false;

						$.each(data, function(country_id, states) {
							var country_name = $(that).find('option[value="' + country_id + '"]').text(),
								prefix = that.opt.field_prefix ? that.opt.field_prefix + '-' : that.opt.field_prefix,
								optgroup_id = prefix + 'group-' + i + '-' + country_id,
								optgroup, option;

							if (!$('#' + optgroup_id, this).length) {
								optgroup = $('<optgroup />', {
									id: optgroup_id,
									label: country_name
								}).data('ajaxloaded', true);

								$.each(states, function(index, state) {
									option = $('<option />', {
										value: state.virtuemart_state_id
									}).text(state.state_name);

									if ($.inArray(state.virtuemart_state_id, selected_state_ids) >= 0) {
										option.attr('selected', true);
									}

									optgroup.append(option);
									hasData = true;
								});
							}

							if (optgroup && hasData) {
								$(state_field).append(optgroup);
							}
						});
					}

					if (hasData && $(state_field).data('required')) {
						$(state_field).attr('required', true).attr('aria-required', true);
						label = $(state_field).data('label');
						if (label && $(label).length && that.opt.asterisk_class) {
							$(label).find('.' + that.opt.asterisk_class).show();
						}
					}

					if ($(state_field).hasClass('invalid') || $(state_field).attr('aria-invalid') == 'true') {
						$(state_field).trigger('blur');
					}

					if (that.opt.field_update_trigger && $.type(that.opt.field_update_trigger) === 'string') {
						$(state_field).trigger(that.opt.field_update_trigger);
					}

					i++;
				});
			},
			startVmLoading: function(message) {
				var object = {
					data: {
						msg: (!message ? '' : message)
					}
				};
				Virtuemart.startVmLoading(object);
			},
			stopVmLoading: function() {
				Virtuemart.stopVmLoading();
			}
		};


	$.fn.vm2front = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist in Vm2front plugin.');
		}
	};

	$.fn.vm2front.defaults = {
		state_field_selector : '#virtuemart_state_id_field',
		selected_state_ids   : '',
		field_prefix         : '',
		field_update_trigger : 'liszt:updated',
		show_list_loader     : true,
		asterisk_class       : 'asterisk'
	};

	/* You can override default options like below.
	jQuery(function($) {
	    $('#virtuemart_country_id_field').vm2front('setOpt', {show_list_loader : true});
	});
	*/

	Virtuemart.startVmLoading = function(a) {
		var msg = '';
		if (typeof a.data.msg !== 'undefined') {
			msg = a.data.msg;
		}
		$('body').addClass('vmLoading');
		if (!$('div.vmLoadingDiv').length) {
			$('body').append('<div class="vmLoadingDiv"><div class="vmLoadingDivMsg">' + msg + '</div></div>');
		}
	};

	Virtuemart.stopVmLoading = function() {
		if ($('body').hasClass('vmLoading')) {
			$('body').removeClass('vmLoading');
			$('div.vmLoadingDiv').remove();
		}
	};

	Virtuemart.sendCurrForm = function(event){
		event.preventDefault();
		if(event.currentTarget.length > 0){
			$(event.currentTarget[0].form.submit());
		} else {
			var f = jQuery(event.currentTarget).closest('form');
			f.submit();
		}
		/*var acti = jQuery(f).attr(\'action\');
		jQuery(f).attr(\'action\', acti+"&tmpl=component");*/
	}
})(jQuery)