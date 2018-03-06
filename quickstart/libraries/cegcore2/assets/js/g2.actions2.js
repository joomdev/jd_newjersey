(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	
	$.G2.actions2 = {};
	
	$.G2.actions2.ready = function(Elem){
		if(typeof Elem == 'undefined'){
			Elem = $('body');
		}
		
		Elem.find('.G2-dynamic2').each(function(k, element){
			$.G2.actions2.dynamics(element);
		});
	};
	
	$.G2.actions2.get = function(selector, element){
		if(typeof selector === 'object'){
			if(selector['fn'] == 'closest'){
				return $(element).closest(selector['id']);
			}
		}else{
			return $(selector);
		}
	};
	
	$.G2.actions2.fns = function(element, actData){
		var act = actData['act'];
		if(act == 'modal'){
			var $modal = $.G2.actions2.get(actData['id'], element);//.last();
			if(actData['fn'] == undefined){
				if($modal.hasClass('dynamic')){
					$modal.children('.content').last().html('<div class="ui active inline centered loader"></div>');
				}
				$modal.modal({
					//'detachable' : false, 
					'inverted' : true,
					'onShow' : function(){
						if($(this).hasClass('source')){
							//$(this).children('.content').first().html($(this).children('.source').first().html());
						}
					}
				}).modal('show');
			}else if(actData['fn'] == 'hide'){
				$modal.modal().modal('hide');
			}
			return true;
		}
		
		if(act == 'validate'){
			if($.G2.actions2.get(actData['id'], element).form('is valid')){
				return true;
			}
		}
		
		if(act == 'remove'){
			$.G2.actions2.get(actData['id'], element).transition({
				'animation' : 'fly right', 
				'onComplete' : function(){
					$.G2.actions2.get(actData['id'], element).remove();
				}
			});
			return true;
		}
		
		if(act == 'reload'){
			if($(actData['id']).find('.ui.modal.visible').length > 0){
				//return false;
			}
			
			var counter = $(element).data('counter') ? parseInt($(element).data('counter')) : 0;
			counter = counter + 1;
			$(element).data('counter', counter);
			
			return $.G2.actions2.fns(element, {'act':'ajax', 'url':$.G2.actions2.get(actData['id'], element).data('url') + '&_counter=' + counter, 'result':actData['id'], 'fn':'replace'});
		}
		
		if(act == 'ajax'){
			$.ajax({
				url: actData['url'],
				data: (actData['form'] != undefined) ? $.G2.actions2.get(actData['form'], element).find(':input').serializeArray() : {},
				beforeSend: function(){
					$(element).addClass('loading');
					if(actData['form'] != undefined){
						$.G2.actions2.get(actData['form'], element).addClass('loading');
					}
				},
				error: function(xhr, textStatus, message){
					$(element).removeClass('loading');
					if(actData['form'] != undefined){
						$.G2.actions2.get(actData['form'], element).removeClass('loading');
					}
					if(actData['result'] != undefined){
						$.G2.actions2.get(actData['result'], element).html('<div class="ui message red">'+textStatus+':'+message+'</div>');
					}
				},
				success: function(result){
					$(element).removeClass('loading');
					if(actData['form'] != undefined){
						$.G2.actions2.get(actData['form'], element).removeClass('loading');
					}
					if(result.substring(0, 1) == '{' && result.slice(-1) == '}'){
						var json = JSON.parse(result);
						
						if(actData['result'] != undefined){
							$.each(json, function(type, info){
								if(type == 'error'){
									$.G2.actions2.get(actData['result'], element).html('<div class="ui message red">'+info+'</div>');
								}
								if(type == 'success'){
									$.G2.actions2.get(actData['result'], element).html('<div class="ui center aligned icon header green"><i class="circular checkmark icon green"></i>'+info+'</div>');
								}
							});
						}
					}else{
						var newContent = $(result);
						if(actData['result'] != undefined){
							if(actData['fn'] == undefined){
								$.G2.actions2.get(actData['result'], element).html(newContent);
							}else if(actData['fn'] == 'replace'){
								$.G2.actions2.get(actData['result'], element).replaceWith(newContent);
							}
						}
						newContent.trigger('contentChange');
					}
					
					if(actData['success'] != undefined){
						$.G2.actions2.run(element, actData['success']);
					}
					
					return true;
				}
			});
		}
	};
	
	$.G2.actions2.run = function(element, actions){
		$.each(actions, function(index, actData){
			var reply = $.G2.actions2.fns(element, actData);
			//console.log(reply);
			if(reply != true){
				return false;
			}
		});
	};
	
	$.G2.actions2.dynamics = function(element){
		
		if($(element).data('actions')){
			var actions = $(element).data('actions');
			$.each(actions, function(event, eventActions){
				$(element).on(event, function(e){
					$.G2.actions2.run(element, eventActions);
				});
			});
		}
	};
	
}(jQuery));