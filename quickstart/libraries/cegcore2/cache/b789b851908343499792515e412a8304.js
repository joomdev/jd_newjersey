/* form20887-1520382473-g2.boot8896-1520382473-g2.forms19375-1520382473-g21105-1520382473-transition13315-1520382473 */
/*!
 * # Semantic UI 2.2.11 - Transition
 * http://github.com/semantic-org/semantic-ui/
 *
 *
 * Released under the MIT license
 * http://opensource.org/licenses/MIT
 *
 */
!function(n,i,e,t){"use strict";i=void 0!==i&&i.Math==Math?i:"undefined"!=typeof self&&self.Math==Math?self:Function("return this")(),n.fn.transition=function(){var t,a=n(this),o=a.selector||"",r=(new Date).getTime(),s=[],l=arguments,d=l[0],u=[].slice.call(arguments,1),c="string"==typeof d;i.requestAnimationFrame||i.mozRequestAnimationFrame||i.webkitRequestAnimationFrame||i.msRequestAnimationFrame;return a.each(function(i){var m,f,p,g,v,b,y,h,w,C=n(this),A=this;w={initialize:function(){m=w.get.settings.apply(A,l),g=m.className,p=m.error,v=m.metadata,h="."+m.namespace,y="module-"+m.namespace,f=C.data(y)||w,b=w.get.animationEndEvent(),c&&(c=w.invoke(d)),!1===c&&(w.verbose("Converted arguments into settings object",m),m.interval?w.delay(m.animate):w.animate(),w.instantiate())},instantiate:function(){w.verbose("Storing instance of module",w),f=w,C.data(y,f)},destroy:function(){w.verbose("Destroying previous module for",A),C.removeData(y)},refresh:function(){w.verbose("Refreshing display type on next animation"),delete w.displayType},forceRepaint:function(){w.verbose("Forcing element repaint");var n=C.parent(),i=C.next();0===i.length?C.detach().appendTo(n):C.detach().insertBefore(i)},repaint:function(){w.verbose("Repainting element");A.offsetWidth},delay:function(n){var e,t,o=w.get.animationDirection();o||(o=w.can.transition()?w.get.direction():"static"),n=void 0!==n?n:m.interval,e="auto"==m.reverse&&o==g.outward,t=e||1==m.reverse?(a.length-i)*m.interval:i*m.interval,w.debug("Delaying animation by",t),setTimeout(w.animate,t)},animate:function(n){if(m=n||m,!w.is.supported())return w.error(p.support),!1;if(w.debug("Preparing animation",m.animation),w.is.animating()){if(m.queue)return!m.allowRepeats&&w.has.direction()&&w.is.occurring()&&!0!==w.queuing?w.debug("Animation is currently occurring, preventing queueing same animation",m.animation):w.queue(m.animation),!1;if(!m.allowRepeats&&w.is.occurring())return w.debug("Animation is already occurring, will not execute repeated animation",m.animation),!1;w.debug("New animation started, completing previous early",m.animation),f.complete()}w.can.animate()?w.set.animating(m.animation):w.error(p.noAnimation,m.animation,A)},reset:function(){w.debug("Resetting animation to beginning conditions"),w.remove.animationCallbacks(),w.restore.conditions(),w.remove.animating()},queue:function(n){w.debug("Queueing animation of",n),w.queuing=!0,C.one(b+".queue"+h,function(){w.queuing=!1,w.repaint(),w.animate.apply(this,m)})},complete:function(n){w.debug("Animation complete",m.animation),w.remove.completeCallback(),w.remove.failSafe(),w.is.looping()||(w.is.outward()?(w.verbose("Animation is outward, hiding element"),w.restore.conditions(),w.hide()):w.is.inward()?(w.verbose("Animation is outward, showing element"),w.restore.conditions(),w.show()):(w.verbose("Static animation completed"),w.restore.conditions(),m.onComplete.call(A)))},force:{visible:function(){var n=C.attr("style"),i=w.get.userStyle(),e=w.get.displayType(),t=i+"display: "+e+" !important;",a=C.css("display"),o=void 0===n||""===n;a!==e?(w.verbose("Overriding default display to show element",e),C.attr("style",t)):o&&C.removeAttr("style")},hidden:function(){var n=C.attr("style"),i=C.css("display"),e=void 0===n||""===n;"none"===i||w.is.hidden()?e&&C.removeAttr("style"):(w.verbose("Overriding default display to hide element"),C.css("display","none"))}},has:{direction:function(i){var e=!1;return i=i||m.animation,"string"==typeof i&&(i=i.split(" "),n.each(i,function(n,i){i!==g.inward&&i!==g.outward||(e=!0)})),e},inlineDisplay:function(){var i=C.attr("style")||"";return n.isArray(i.match(/display.*?;/,""))}},set:{animating:function(n){var i;w.remove.completeCallback(),n=n||m.animation,i=w.get.animationClass(n),w.save.animation(i),w.force.visible(),w.remove.hidden(),w.remove.direction(),w.start.animation(i)},duration:function(n,i){i=i||m.duration,((i="number"==typeof i?i+"ms":i)||0===i)&&(w.verbose("Setting animation duration",i),C.css({"animation-duration":i}))},direction:function(n){n=n||w.get.direction(),n==g.inward?w.set.inward():w.set.outward()},looping:function(){w.debug("Transition set to loop"),C.addClass(g.looping)},hidden:function(){C.addClass(g.transition).addClass(g.hidden)},inward:function(){w.debug("Setting direction to inward"),C.removeClass(g.outward).addClass(g.inward)},outward:function(){w.debug("Setting direction to outward"),C.removeClass(g.inward).addClass(g.outward)},visible:function(){C.addClass(g.transition).addClass(g.visible)}},start:{animation:function(n){n=n||w.get.animationClass(),w.debug("Starting tween",n),C.addClass(n).one(b+".complete"+h,w.complete),m.useFailSafe&&w.add.failSafe(),w.set.duration(m.duration),m.onStart.call(A)}},save:{animation:function(n){w.cache||(w.cache={}),w.cache.animation=n},displayType:function(n){"none"!==n&&C.data(v.displayType,n)},transitionExists:function(i,e){n.fn.transition.exists[i]=e,w.verbose("Saving existence of transition",i,e)}},restore:{conditions:function(){var n=w.get.currentAnimation();n&&(C.removeClass(n),w.verbose("Removing animation class",w.cache)),w.remove.duration()}},add:{failSafe:function(){var n=w.get.duration();w.timer=setTimeout(function(){C.triggerHandler(b)},n+m.failSafeDelay),w.verbose("Adding fail safe timer",w.timer)}},remove:{animating:function(){C.removeClass(g.animating)},animationCallbacks:function(){w.remove.queueCallback(),w.remove.completeCallback()},queueCallback:function(){C.off(".queue"+h)},completeCallback:function(){C.off(".complete"+h)},display:function(){C.css("display","")},direction:function(){C.removeClass(g.inward).removeClass(g.outward)},duration:function(){C.css("animation-duration","")},failSafe:function(){w.verbose("Removing fail safe timer",w.timer),w.timer&&clearTimeout(w.timer)},hidden:function(){C.removeClass(g.hidden)},visible:function(){C.removeClass(g.visible)},looping:function(){w.debug("Transitions are no longer looping"),w.is.looping()&&(w.reset(),C.removeClass(g.looping))},transition:function(){C.removeClass(g.visible).removeClass(g.hidden)}},get:{settings:function(i,e,t){return"object"==typeof i?n.extend(!0,{},n.fn.transition.settings,i):"function"==typeof t?n.extend({},n.fn.transition.settings,{animation:i,onComplete:t,duration:e}):"string"==typeof e||"number"==typeof e?n.extend({},n.fn.transition.settings,{animation:i,duration:e}):"object"==typeof e?n.extend({},n.fn.transition.settings,e,{animation:i}):"function"==typeof e?n.extend({},n.fn.transition.settings,{animation:i,onComplete:e}):n.extend({},n.fn.transition.settings,{animation:i})},animationClass:function(n){var i=n||m.animation,e=w.can.transition()&&!w.has.direction()?w.get.direction()+" ":"";return g.animating+" "+g.transition+" "+e+i},currentAnimation:function(){return!(!w.cache||void 0===w.cache.animation)&&w.cache.animation},currentDirection:function(){return w.is.inward()?g.inward:g.outward},direction:function(){return w.is.hidden()||!w.is.visible()?g.inward:g.outward},animationDirection:function(i){var e;return i=i||m.animation,"string"==typeof i&&(i=i.split(" "),n.each(i,function(n,i){i===g.inward?e=g.inward:i===g.outward&&(e=g.outward)})),e||!1},duration:function(n){return n=n||m.duration,!1===n&&(n=C.css("animation-duration")||0),"string"==typeof n?n.indexOf("ms")>-1?parseFloat(n):1e3*parseFloat(n):n},displayType:function(n){return n=void 0===n||n,m.displayType?m.displayType:(n&&void 0===C.data(v.displayType)&&w.can.transition(!0),C.data(v.displayType))},userStyle:function(n){return n=n||C.attr("style")||"",n.replace(/display.*?;/,"")},transitionExists:function(i){return n.fn.transition.exists[i]},animationStartEvent:function(){var n,i=e.createElement("div"),t={animation:"animationstart",OAnimation:"oAnimationStart",MozAnimation:"mozAnimationStart",WebkitAnimation:"webkitAnimationStart"};for(n in t)if(void 0!==i.style[n])return t[n];return!1},animationEndEvent:function(){var n,i=e.createElement("div"),t={animation:"animationend",OAnimation:"oAnimationEnd",MozAnimation:"mozAnimationEnd",WebkitAnimation:"webkitAnimationEnd"};for(n in t)if(void 0!==i.style[n])return t[n];return!1}},can:{transition:function(i){var e,t,a,o,r,s,l=m.animation,d=w.get.transitionExists(l),u=w.get.displayType(!1);if(void 0===d||i){if(w.verbose("Determining whether animation exists"),e=C.attr("class"),t=C.prop("tagName"),a=n("<"+t+" />").addClass(e).insertAfter(C),o=a.addClass(l).removeClass(g.inward).removeClass(g.outward).addClass(g.animating).addClass(g.transition).css("animationName"),r=a.addClass(g.inward).css("animationName"),u||(u=a.attr("class",e).removeAttr("style").removeClass(g.hidden).removeClass(g.visible).show().css("display"),w.verbose("Determining final display state",u),w.save.displayType(u)),a.remove(),o!=r)w.debug("Direction exists for animation",l),s=!0;else{if("none"==o||!o)return void w.debug("No animation defined in css",l);w.debug("Static animation found",l,u),s=!1}w.save.transitionExists(l,s)}return void 0!==d?d:s},animate:function(){return void 0!==w.can.transition()}},is:{animating:function(){return C.hasClass(g.animating)},inward:function(){return C.hasClass(g.inward)},outward:function(){return C.hasClass(g.outward)},looping:function(){return C.hasClass(g.looping)},occurring:function(n){return n=n||m.animation,n="."+n.replace(" ","."),C.filter(n).length>0},visible:function(){return C.is(":visible")},hidden:function(){return"hidden"===C.css("visibility")},supported:function(){return!1!==b}},hide:function(){w.verbose("Hiding element"),w.is.animating()&&w.reset(),A.blur(),w.remove.display(),w.remove.visible(),w.set.hidden(),w.force.hidden(),m.onHide.call(A),m.onComplete.call(A)},show:function(n){w.verbose("Showing element",n),w.remove.hidden(),w.set.visible(),w.force.visible(),m.onShow.call(A),m.onComplete.call(A)},toggle:function(){w.is.visible()?w.hide():w.show()},stop:function(){w.debug("Stopping current animation"),C.triggerHandler(b)},stopAll:function(){w.debug("Stopping all animation"),w.remove.queueCallback(),C.triggerHandler(b)},clear:{queue:function(){w.debug("Clearing animation queue"),w.remove.queueCallback()}},enable:function(){w.verbose("Starting animation"),C.removeClass(g.disabled)},disable:function(){w.debug("Stopping animation"),C.addClass(g.disabled)},setting:function(i,e){if(w.debug("Changing setting",i,e),n.isPlainObject(i))n.extend(!0,m,i);else{if(void 0===e)return m[i];n.isPlainObject(m[i])?n.extend(!0,m[i],e):m[i]=e}},internal:function(i,e){if(n.isPlainObject(i))n.extend(!0,w,i);else{if(void 0===e)return w[i];w[i]=e}},debug:function(){!m.silent&&m.debug&&(m.performance?w.performance.log(arguments):(w.debug=Function.prototype.bind.call(console.info,console,m.name+":"),w.debug.apply(console,arguments)))},verbose:function(){!m.silent&&m.verbose&&m.debug&&(m.performance?w.performance.log(arguments):(w.verbose=Function.prototype.bind.call(console.info,console,m.name+":"),w.verbose.apply(console,arguments)))},error:function(){m.silent||(w.error=Function.prototype.bind.call(console.error,console,m.name+":"),w.error.apply(console,arguments))},performance:{log:function(n){var i,e,t;m.performance&&(i=(new Date).getTime(),t=r||i,e=i-t,r=i,s.push({Name:n[0],Arguments:[].slice.call(n,1)||"",Element:A,"Execution Time":e})),clearTimeout(w.performance.timer),w.performance.timer=setTimeout(w.performance.display,500)},display:function(){var i=m.name+":",e=0;r=!1,clearTimeout(w.performance.timer),n.each(s,function(n,i){e+=i["Execution Time"]}),i+=" "+e+"ms",o&&(i+=" '"+o+"'"),a.length>1&&(i+=" ("+a.length+")"),(void 0!==console.group||void 0!==console.table)&&s.length>0&&(console.groupCollapsed(i),console.table?console.table(s):n.each(s,function(n,i){console.log(i.Name+": "+i["Execution Time"]+"ms")}),console.groupEnd()),s=[]}},invoke:function(i,e,a){var o,r,s,l=f;return e=e||u,a=A||a,"string"==typeof i&&void 0!==l&&(i=i.split(/[\. ]/),o=i.length-1,n.each(i,function(e,t){var a=e!=o?t+i[e+1].charAt(0).toUpperCase()+i[e+1].slice(1):i;if(n.isPlainObject(l[a])&&e!=o)l=l[a];else{if(void 0!==l[a])return r=l[a],!1;if(!n.isPlainObject(l[t])||e==o)return void 0!==l[t]&&(r=l[t],!1);l=l[t]}})),n.isFunction(r)?s=r.apply(a,e):void 0!==r&&(s=r),n.isArray(t)?t.push(s):void 0!==t?t=[t,s]:void 0!==s&&(t=s),void 0!==r&&r}},w.initialize()}),void 0!==t?t:this},n.fn.transition.exists={},n.fn.transition.settings={name:"Transition",silent:!1,debug:!1,verbose:!1,performance:!0,namespace:"transition",interval:0,reverse:"auto",onStart:function(){},onComplete:function(){},onShow:function(){},onHide:function(){},useFailSafe:!0,failSafeDelay:100,allowRepeats:!1,displayType:!1,animation:"fade",duration:!1,queue:!0,metadata:{displayType:"display"},className:{animating:"animating",disabled:"disabled",hidden:"hidden",inward:"in",loading:"loading",looping:"looping",outward:"out",transition:"transition",visible:"visible"},error:{noAnimation:"Element is no longer attached to DOM. Unable to animate.  Use silent setting to surpress this warning in production.",repeated:"That animation is already occurring, cancelling repeated animation",method:"The method you called is not defined",support:"This browser does not support CSS animations"}}}(jQuery,window,document);
/*!
 * # Semantic UI 2.2.11 - Form Validation
 * http://github.com/semantic-org/semantic-ui/
 *
 *
 * Released under the MIT license
 * http://opensource.org/licenses/MIT
 *
 */
!function(e,t,n,i){"use strict";t=void 0!==t&&t.Math==Math?t:"undefined"!=typeof self&&self.Math==Math?self:Function("return this")(),e.fn.form=function(t){var i,r=e(this),a=r.selector||"",o=(new Date).getTime(),s=[],l=arguments[0],u=arguments[1],c="string"==typeof l,d=[].slice.call(arguments,1);return r.each(function(){var f,p,m,g,h,v,b,y,x,k,E,w,C,V,R,S,F,A,T,D=e(this),O=this,j=[],$=!1;T={initialize:function(){T.get.settings(),c?(void 0===A&&T.instantiate(),T.invoke(l)):(void 0!==A&&A.invoke("destroy"),T.verbose("Initializing form validation",D,y),T.bindEvents(),T.set.defaults(),T.instantiate())},instantiate:function(){T.verbose("Storing instance of module",T),A=T,D.data(S,T)},destroy:function(){T.verbose("Destroying previous module",A),T.removeEvents(),D.removeData(S)},refresh:function(){T.verbose("Refreshing selector cache"),f=D.find(E.field),p=D.find(E.group),m=D.find(E.message),g=D.find(E.prompt),h=D.find(E.submit),v=D.find(E.clear),b=D.find(E.reset)},submit:function(){T.verbose("Submitting form",D),D.submit()},attachEvents:function(t,n){n=n||"submit",e(t).on("click"+F,function(e){T[n](),e.preventDefault()})},bindEvents:function(){T.verbose("Attaching form events"),D.on("submit"+F,T.validate.form).on("blur"+F,E.field,T.event.field.blur).on("click"+F,E.submit,T.submit).on("click"+F,E.reset,T.reset).on("click"+F,E.clear,T.clear),y.keyboardShortcuts&&D.on("keydown"+F,E.field,T.event.field.keydown),f.each(function(){var t=e(this),n=t.prop("type"),i=T.get.changeEvent(n,t);e(this).on(i+F,T.event.field.change)})},clear:function(){f.each(function(){var t=e(this),n=t.parent(),i=t.closest(p),r=i.find(E.prompt),a=t.data(k.defaultValue)||"",o=n.is(E.uiCheckbox),s=n.is(E.uiDropdown);i.hasClass(w.error)&&(T.verbose("Resetting error on field",i),i.removeClass(w.error),r.remove()),s?(T.verbose("Resetting dropdown value",n,a),n.dropdown("clear")):o?t.prop("checked",!1):(T.verbose("Resetting field value",t,a),t.val(""))})},reset:function(){f.each(function(){var t=e(this),n=t.parent(),i=t.closest(p),r=i.find(E.prompt),a=t.data(k.defaultValue),o=n.is(E.uiCheckbox),s=n.is(E.uiDropdown),l=i.hasClass(w.error);void 0!==a&&(l&&(T.verbose("Resetting error on field",i),i.removeClass(w.error),r.remove()),s?(T.verbose("Resetting dropdown value",n,a),n.dropdown("restore defaults")):o?(T.verbose("Resetting checkbox value",n,a),t.prop("checked",a)):(T.verbose("Resetting field value",t,a),t.val(a)))})},determine:{isValid:function(){var t=!0;return e.each(x,function(e,n){T.validate.field(n,e,!0)||(t=!1)}),t}},is:{bracketedRule:function(e){return e.type&&e.type.match(y.regExp.bracket)},shorthandFields:function(e){var t=Object.keys(e),n=e[t[0]];return T.is.shorthandRules(n)},shorthandRules:function(t){return"string"==typeof t||e.isArray(t)},empty:function(e){return!e||0===e.length||(e.is('input[type="checkbox"]')?!e.is(":checked"):T.is.blank(e))},blank:function(t){return""===e.trim(t.val())},valid:function(t){var n=!0;return t?(T.verbose("Checking if field is valid",t),T.validate.field(x[t],t,!1)):(T.verbose("Checking if form is valid"),e.each(x,function(e,t){T.is.valid(e)||(n=!1)}),n)}},removeEvents:function(){D.off(F),f.off(F),h.off(F),f.off(F)},event:{field:{keydown:function(t){var n=e(this),i=t.which,r=n.is(E.input),a=n.is(E.checkbox),o=n.closest(E.uiDropdown).length>0,s={enter:13,escape:27};i==s.escape&&(T.verbose("Escape key pressed blurring field"),n.blur()),t.ctrlKey||i!=s.enter||!r||o||a||($||(n.one("keyup"+F,T.event.field.keyup),T.submit(),T.debug("Enter pressed on input submitting form")),$=!0)},keyup:function(){$=!1},blur:function(t){var n=e(this),i=n.closest(p),r=T.get.validation(n);i.hasClass(w.error)?(T.debug("Revalidating field",n,r),r&&T.validate.field(r)):"blur"!=y.on&&"change"!=y.on||r&&T.validate.field(r)},change:function(t){var n=e(this),i=n.closest(p),r=T.get.validation(n);r&&("change"==y.on||i.hasClass(w.error)&&y.revalidate)&&(clearTimeout(T.timer),T.timer=setTimeout(function(){T.debug("Revalidating field",n,T.get.validation(n)),T.validate.field(r)},y.delay))}}},get:{ancillaryValue:function(e){return!(!e.type||!e.value&&!T.is.bracketedRule(e))&&(void 0!==e.value?e.value:e.type.match(y.regExp.bracket)[1]+"")},ruleName:function(e){return T.is.bracketedRule(e)?e.type.replace(e.type.match(y.regExp.bracket)[0],""):e.type},changeEvent:function(e,t){return"checkbox"==e||"radio"==e||"hidden"==e||t.is("select")?"change":T.get.inputEvent()},inputEvent:function(){return void 0!==n.createElement("input").oninput?"input":void 0!==n.createElement("input").onpropertychange?"propertychange":"keyup"},fieldsFromShorthand:function(t){var n={};return e.each(t,function(t,i){"string"==typeof i&&(i=[i]),n[t]={rules:[]},e.each(i,function(e,i){n[t].rules.push({type:i})})}),n},prompt:function(e,t){var n,i,r,a=T.get.ruleName(e),o=T.get.ancillaryValue(e),s=e.prompt||y.prompt[a]||y.text.unspecifiedRule,l=-1!==s.search("{value}"),u=-1!==s.search("{name}");return(u||l)&&(i=T.get.field(t.identifier)),l&&(s=s.replace("{value}",i.val())),u&&(n=i.closest(E.group).find("label").eq(0),r=1==n.length?n.text():i.prop("placeholder")||y.text.unspecifiedField,s=s.replace("{name}",r)),s=s.replace("{identifier}",t.identifier),s=s.replace("{ruleValue}",o),e.prompt||T.verbose("Using default validation prompt for type",s,a),s},settings:function(){if(e.isPlainObject(t)){var n=Object.keys(t),i=n.length>0&&(void 0!==t[n[0]].identifier&&void 0!==t[n[0]].rules);i?(y=e.extend(!0,{},e.fn.form.settings,u),x=e.extend({},e.fn.form.settings.defaults,t),T.error(y.error.oldSyntax,O),T.verbose("Extending settings from legacy parameters",x,y)):(t.fields&&T.is.shorthandFields(t.fields)&&(t.fields=T.get.fieldsFromShorthand(t.fields)),y=e.extend(!0,{},e.fn.form.settings,t),x=e.extend({},e.fn.form.settings.defaults,y.fields),T.verbose("Extending settings",x,y))}else y=e.fn.form.settings,x=e.fn.form.settings.defaults,T.verbose("Using default form validation",x,y);R=y.namespace,k=y.metadata,E=y.selector,w=y.className,C=y.regExp,V=y.error,S="module-"+R,F="."+R,A=D.data(S),T.refresh()},field:function(t){return T.verbose("Finding field with identifier",t),t=T.escape.string(t),f.filter("#"+t).length>0?f.filter("#"+t):f.filter('[name="'+t+'"]').length>0?f.filter('[name="'+t+'"]'):f.filter('[name="'+t+'[]"]').length>0?f.filter('[name="'+t+'[]"]'):f.filter("[data-"+k.validate+'="'+t+'"]').length>0?f.filter("[data-"+k.validate+'="'+t+'"]'):e("<input/>")},fields:function(t){var n=e();return e.each(t,function(e,t){n=n.add(T.get.field(t))}),n},validation:function(t){var n,i;return!!x&&(e.each(x,function(e,r){i=r.identifier||e,T.get.field(i)[0]==t[0]&&(r.identifier=i,n=r)}),n||!1)},value:function(e){var t,n=[];return n.push(e),t=T.get.values.call(O,n),t[e]},values:function(t){var n=e.isArray(t)?T.get.fields(t):f,i={};return n.each(function(t,n){var r=e(n),a=(r.prop("type"),r.prop("name")),o=r.val(),s=r.is(E.checkbox),l=r.is(E.radio),u=-1!==a.indexOf("[]"),c=!!s&&r.is(":checked");a&&(u?(a=a.replace("[]",""),i[a]||(i[a]=[]),s?c?i[a].push(o||!0):i[a].push(!1):i[a].push(o)):l?void 0===i[a]&&(i[a]=!!c):i[a]=s?!!c&&(o||!0):o)}),i}},has:{field:function(e){return T.verbose("Checking for existence of a field with identifier",e),e=T.escape.string(e),"string"!=typeof e&&T.error(V.identifier,e),f.filter("#"+e).length>0||(f.filter('[name="'+e+'"]').length>0||f.filter("[data-"+k.validate+'="'+e+'"]').length>0)}},escape:{string:function(e){return e=String(e),e.replace(C.escape,"\\$&")}},add:{rule:function(e,t){T.add.field(e,t)},field:function(t,n){var i={};T.is.shorthandRules(n)?(n=e.isArray(n)?n:[n],i[t]={rules:[]},e.each(n,function(e,n){i[t].rules.push({type:n})})):i[t]=n,x=e.extend({},x,i),T.debug("Adding rules",i,x)},fields:function(t){var n;n=t&&T.is.shorthandFields(t)?T.get.fieldsFromShorthand(t):t,x=e.extend({},x,n)},prompt:function(t,n){var i=T.get.field(t),r=i.closest(p),a=r.children(E.prompt),o=0!==a.length;n="string"==typeof n?[n]:n,T.verbose("Adding field error state",t),r.addClass(w.error),y.inline&&(o||(a=y.templates.prompt(n),a.appendTo(r)),a.html(n[0]),o?T.verbose("Inline errors are disabled, no inline error added",t):y.transition&&void 0!==e.fn.transition&&D.transition("is supported")?(T.verbose("Displaying error with css transition",y.transition),a.transition(y.transition+" in",y.duration)):(T.verbose("Displaying error with fallback javascript animation"),a.fadeIn(y.duration)))},errors:function(e){T.debug("Adding form error messages",e),T.set.error(),m.html(y.templates.error(e))}},remove:{rule:function(t,n){var i=e.isArray(n)?n:[n];if(void 0==n)return T.debug("Removed all rules"),void(x[t].rules=[]);void 0!=x[t]&&e.isArray(x[t].rules)&&e.each(x[t].rules,function(e,n){-1!==i.indexOf(n.type)&&(T.debug("Removed rule",n.type),x[t].rules.splice(e,1))})},field:function(t){var n=e.isArray(t)?t:[t];e.each(n,function(e,t){T.remove.rule(t)})},rules:function(t,n){e.isArray(t)?e.each(fields,function(e,t){T.remove.rule(t,n)}):T.remove.rule(t,n)},fields:function(e){T.remove.field(e)},prompt:function(t){var n=T.get.field(t),i=n.closest(p),r=i.children(E.prompt);i.removeClass(w.error),y.inline&&r.is(":visible")&&(T.verbose("Removing prompt for field",t),y.transition&&void 0!==e.fn.transition&&D.transition("is supported")?r.transition(y.transition+" out",y.duration,function(){r.remove()}):r.fadeOut(y.duration,function(){r.remove()}))}},set:{success:function(){D.removeClass(w.error).addClass(w.success)},defaults:function(){f.each(function(){var t=e(this),n=t.filter(E.checkbox).length>0,i=n?t.is(":checked"):t.val();t.data(k.defaultValue,i)})},error:function(){D.removeClass(w.success).addClass(w.error)},value:function(e,t){var n={};return n[e]=t,T.set.values.call(O,n)},values:function(t){e.isEmptyObject(t)||e.each(t,function(t,n){var i,r=T.get.field(t),a=r.parent(),o=e.isArray(n),s=a.is(E.uiCheckbox),l=a.is(E.uiDropdown),u=r.is(E.radio)&&s,c=r.length>0;c&&(o&&s?(T.verbose("Selecting multiple",n,r),a.checkbox("uncheck"),e.each(n,function(e,t){i=r.filter('[value="'+t+'"]'),a=i.parent(),i.length>0&&a.checkbox("check")})):u?(T.verbose("Selecting radio value",n,r),r.filter('[value="'+n+'"]').parent(E.uiCheckbox).checkbox("check")):s?(T.verbose("Setting checkbox value",n,a),!0===n?a.checkbox("check"):a.checkbox("uncheck")):l?(T.verbose("Setting dropdown value",n,a),a.dropdown("set selected",n)):(T.verbose("Setting field value",n,r),r.val(n)))})}},validate:{form:function(e,t){var n=T.get.values();if($)return!1;if(j=[],T.determine.isValid()){if(T.debug("Form has no validation errors, submitting"),T.set.success(),!0!==t)return y.onSuccess.call(O,e,n)}else if(T.debug("Form has errors"),T.set.error(),y.inline||T.add.errors(j),void 0!==D.data("moduleApi")&&e.stopImmediatePropagation(),!0!==t)return y.onFailure.call(O,j,n)},field:function(t,n,i){i=void 0===i||i,"string"==typeof t&&(T.verbose("Validating field",t),n=t,t=x[t]);var r=t.identifier||n,a=T.get.field(r),o=!!t.depends&&T.get.field(t.depends),s=!0,l=[];return t.identifier||(T.debug("Using field name as identifier",r),t.identifier=r),a.prop("disabled")?(T.debug("Field is disabled. Skipping",r),s=!0):t.optional&&T.is.blank(a)?(T.debug("Field is optional and blank. Skipping",r),s=!0):t.depends&&T.is.empty(o)?(T.debug("Field depends on another value that is not present or empty. Skipping",o),s=!0):void 0!==t.rules&&e.each(t.rules,function(e,n){T.has.field(r)&&!T.validate.rule(t,n)&&(T.debug("Field is invalid",r,n.type),l.push(T.get.prompt(n,t)),s=!1)}),s?(i&&(T.remove.prompt(r,l),y.onValid.call(a)),!0):(i&&(j=j.concat(l),T.add.prompt(r,l),y.onInvalid.call(a,l)),!1)},rule:function(t,n){var i=T.get.field(t.identifier),r=(n.type,i.val()),a=T.get.ancillaryValue(n),o=T.get.ruleName(n),s=y.rules[o];return e.isFunction(s)?(r=void 0===r||""===r||null===r?"":e.trim(r+""),s.call(i,r,a)):void T.error(V.noRule,o)}},setting:function(t,n){if(e.isPlainObject(t))e.extend(!0,y,t);else{if(void 0===n)return y[t];y[t]=n}},internal:function(t,n){if(e.isPlainObject(t))e.extend(!0,T,t);else{if(void 0===n)return T[t];T[t]=n}},debug:function(){!y.silent&&y.debug&&(y.performance?T.performance.log(arguments):(T.debug=Function.prototype.bind.call(console.info,console,y.name+":"),T.debug.apply(console,arguments)))},verbose:function(){!y.silent&&y.verbose&&y.debug&&(y.performance?T.performance.log(arguments):(T.verbose=Function.prototype.bind.call(console.info,console,y.name+":"),T.verbose.apply(console,arguments)))},error:function(){y.silent||(T.error=Function.prototype.bind.call(console.error,console,y.name+":"),T.error.apply(console,arguments))},performance:{log:function(e){var t,n,i;y.performance&&(t=(new Date).getTime(),i=o||t,n=t-i,o=t,s.push({Name:e[0],Arguments:[].slice.call(e,1)||"",Element:O,"Execution Time":n})),clearTimeout(T.performance.timer),T.performance.timer=setTimeout(T.performance.display,500)},display:function(){var t=y.name+":",n=0;o=!1,clearTimeout(T.performance.timer),e.each(s,function(e,t){n+=t["Execution Time"]}),t+=" "+n+"ms",a&&(t+=" '"+a+"'"),r.length>1&&(t+=" ("+r.length+")"),(void 0!==console.group||void 0!==console.table)&&s.length>0&&(console.groupCollapsed(t),console.table?console.table(s):e.each(s,function(e,t){console.log(t.Name+": "+t["Execution Time"]+"ms")}),console.groupEnd()),s=[]}},invoke:function(t,n,r){var a,o,s,l=A;return n=n||d,r=O||r,"string"==typeof t&&void 0!==l&&(t=t.split(/[\. ]/),a=t.length-1,e.each(t,function(n,i){var r=n!=a?i+t[n+1].charAt(0).toUpperCase()+t[n+1].slice(1):t;if(e.isPlainObject(l[r])&&n!=a)l=l[r];else{if(void 0!==l[r])return o=l[r],!1;if(!e.isPlainObject(l[i])||n==a)return void 0!==l[i]&&(o=l[i],!1);l=l[i]}})),e.isFunction(o)?s=o.apply(r,n):void 0!==o&&(s=o),e.isArray(i)?i.push(s):void 0!==i?i=[i,s]:void 0!==s&&(i=s),o}},T.initialize()}),void 0!==i?i:this},e.fn.form.settings={name:"Form",namespace:"form",debug:!1,verbose:!1,performance:!0,fields:!1,keyboardShortcuts:!0,on:"submit",inline:!1,delay:200,revalidate:!0,transition:"scale",duration:200,onValid:function(){},onInvalid:function(){},onSuccess:function(){return!0},onFailure:function(){return!1},metadata:{defaultValue:"default",validate:"validate"},regExp:{htmlID:/^[a-zA-Z][\w:.-]*$/g,bracket:/\[(.*)\]/i,decimal:/^\d+\.?\d*$/,email:/^[a-z0-9!#$%&'*+\/=?^_`{|}~.-]+@[a-z0-9]([a-z0-9-]*[a-z0-9])?(\.[a-z0-9]([a-z0-9-]*[a-z0-9])?)*$/i,escape:/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g,flags:/^\/(.*)\/(.*)?/,integer:/^\-?\d+$/,number:/^\-?\d*(\.\d+)?$/,url:/(https?:\/\/(?:www\.|(?!www))[^\s\.]+\.[^\s]{2,}|www\.[^\s]+\.[^\s]{2,})/i},text:{unspecifiedRule:"Please enter a valid value",unspecifiedField:"This field"},prompt:{empty:"{name} must have a value",checked:"{name} must be checked",email:"{name} must be a valid e-mail",url:"{name} must be a valid url",regExp:"{name} is not formatted correctly",integer:"{name} must be an integer",decimal:"{name} must be a decimal number",number:"{name} must be set to a number",is:'{name} must be "{ruleValue}"',isExactly:'{name} must be exactly "{ruleValue}"',not:'{name} cannot be set to "{ruleValue}"',notExactly:'{name} cannot be set to exactly "{ruleValue}"',contain:'{name} cannot contain "{ruleValue}"',containExactly:'{name} cannot contain exactly "{ruleValue}"',doesntContain:'{name} must contain  "{ruleValue}"',doesntContainExactly:'{name} must contain exactly "{ruleValue}"',minLength:"{name} must be at least {ruleValue} characters",length:"{name} must be at least {ruleValue} characters",exactLength:"{name} must be exactly {ruleValue} characters",maxLength:"{name} cannot be longer than {ruleValue} characters",match:"{name} must match {ruleValue} field",different:"{name} must have a different value than {ruleValue} field",creditCard:"{name} must be a valid credit card number",minCount:"{name} must have at least {ruleValue} choices",exactCount:"{name} must have exactly {ruleValue} choices",maxCount:"{name} must have {ruleValue} or less choices"},selector:{checkbox:'input[type="checkbox"], input[type="radio"]',clear:".clear",field:"input, textarea, select",group:".field",input:"input",message:".error.message",prompt:".prompt.label",radio:'input[type="radio"]',reset:'.reset:not([type="reset"])',submit:'.submit:not([type="submit"])',uiCheckbox:".ui.checkbox",uiDropdown:".ui.dropdown"},className:{error:"error",label:"ui prompt label",pressed:"down",success:"success"},error:{identifier:"You must specify a string identifier for each field",method:"The method you called is not defined.",noRule:"There is no rule matching the one you specified",oldSyntax:"Starting in 2.0 forms now only take a single settings object. Validation settings converted to new syntax automatically."},templates:{error:function(t){var n='<ul class="list">';return e.each(t,function(e,t){n+="<li>"+t+"</li>"}),n+="</ul>",e(n)},prompt:function(t){return e("<div/>").addClass("ui basic red pointing prompt label").html(t[0])}},rules:{empty:function(t){return!(void 0===t||""===t||e.isArray(t)&&0===t.length)},checked:function(){return e(this).filter(":checked").length>0},email:function(t){return e.fn.form.settings.regExp.email.test(t)},url:function(t){return e.fn.form.settings.regExp.url.test(t)},regExp:function(t,n){if(n instanceof RegExp)return t.match(n);var i,r=n.match(e.fn.form.settings.regExp.flags);return r&&(n=r.length>=2?r[1]:n,i=r.length>=3?r[2]:""),t.match(new RegExp(n,i))},integer:function(t,n){var i,r,a,o=e.fn.form.settings.regExp.integer;return n&&-1===["",".."].indexOf(n)&&(-1==n.indexOf("..")?o.test(n)&&(i=r=n-0):(a=n.split("..",2),o.test(a[0])&&(i=a[0]-0),o.test(a[1])&&(r=a[1]-0))),o.test(t)&&(void 0===i||t>=i)&&(void 0===r||t<=r)},decimal:function(t){return e.fn.form.settings.regExp.decimal.test(t)},number:function(t){return e.fn.form.settings.regExp.number.test(t)},is:function(e,t){return t="string"==typeof t?t.toLowerCase():t,(e="string"==typeof e?e.toLowerCase():e)==t},isExactly:function(e,t){return e==t},not:function(e,t){return e="string"==typeof e?e.toLowerCase():e,t="string"==typeof t?t.toLowerCase():t,e!=t},notExactly:function(e,t){return e!=t},contains:function(t,n){return n=n.replace(e.fn.form.settings.regExp.escape,"\\$&"),-1!==t.search(new RegExp(n,"i"))},containsExactly:function(t,n){return n=n.replace(e.fn.form.settings.regExp.escape,"\\$&"),-1!==t.search(new RegExp(n))},doesntContain:function(t,n){return n=n.replace(e.fn.form.settings.regExp.escape,"\\$&"),-1===t.search(new RegExp(n,"i"))},doesntContainExactly:function(t,n){return n=n.replace(e.fn.form.settings.regExp.escape,"\\$&"),-1===t.search(new RegExp(n))},minLength:function(e,t){return void 0!==e&&e.length>=t},length:function(e,t){return void 0!==e&&e.length>=t},exactLength:function(e,t){return void 0!==e&&e.length==t},maxLength:function(e,t){return void 0!==e&&e.length<=t},match:function(t,n){var i;e(this);return e('[data-validate="'+n+'"]').length>0?i=e('[data-validate="'+n+'"]').val():e("#"+n).length>0?i=e("#"+n).val():e('[name="'+n+'"]').length>0?i=e('[name="'+n+'"]').val():e('[name="'+n+'[]"]').length>0&&(i=e('[name="'+n+'[]"]')),void 0!==i&&t.toString()==i.toString()},different:function(t,n){var i;e(this);return e('[data-validate="'+n+'"]').length>0?i=e('[data-validate="'+n+'"]').val():e("#"+n).length>0?i=e("#"+n).val():e('[name="'+n+'"]').length>0?i=e('[name="'+n+'"]').val():e('[name="'+n+'[]"]').length>0&&(i=e('[name="'+n+'[]"]')),void 0!==i&&t.toString()!==i.toString()},creditCard:function(t,n){var i,r,a={visa:{pattern:/^4/,length:[16]},amex:{pattern:/^3[47]/,length:[15]},mastercard:{pattern:/^5[1-5]/,length:[16]},discover:{pattern:/^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/,length:[16]},unionPay:{pattern:/^(62|88)/,length:[16,17,18,19]},jcb:{pattern:/^35(2[89]|[3-8][0-9])/,length:[16]},maestro:{pattern:/^(5018|5020|5038|6304|6759|676[1-3])/,length:[12,13,14,15,16,17,18,19]},dinersClub:{pattern:/^(30[0-5]|^36)/,length:[14]},laser:{pattern:/^(6304|670[69]|6771)/,length:[16,17,18,19]},visaElectron:{pattern:/^(4026|417500|4508|4844|491(3|7))/,length:[16]}},o={},s=!1,l="string"==typeof n&&n.split(",");if("string"==typeof t&&0!==t.length){if(t=t.replace(/[\-]/g,""),l&&(e.each(l,function(n,i){(r=a[i])&&(o={length:-1!==e.inArray(t.length,r.length),pattern:-1!==t.search(r.pattern)},o.length&&o.pattern&&(s=!0))}),!s))return!1;if(i={number:-1!==e.inArray(t.length,a.unionPay.length),pattern:-1!==t.search(a.unionPay.pattern)},i.number&&i.pattern)return!0;for(var u=t.length,c=0,d=[[0,1,2,3,4,5,6,7,8,9],[0,2,4,6,8,1,3,5,7,9]],f=0;u--;)f+=d[c][parseInt(t.charAt(u),10)],c^=1;return f%10==0&&f>0}},minCount:function(e,t){return 0==t||(1==t?""!==e:e.split(",").length>=t)},exactCount:function(e,t){return 0==t?""===e:1==t?""!==e&&-1===e.search(","):e.split(",").length==t},maxCount:function(e,t){return 0!=t&&(1==t?-1===e.search(","):e.split(",").length<=t)}}}}(jQuery,window,document);
(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	$.G2.composer = {};
	
	$.G2.scrollTo = function(Elem){
		if(Elem.length > 0){
			$('html, body').animate({
				scrollTop: Elem.offset().top - 50
			}, 'slow');
		}
	};
	
	$.G2.centerOn = function(Elem){
		if(Elem.length > 0){
			$('html, body').animate({
				scrollTop: Elem.offset().top - $(window).height()/2
			}, 'slow');
		}
	};
	
	$.G2.split = function(inputs, maxcount){
		var data = {};
		if(inputs.length > maxcount){
			for(i = 0; i <= inputs.length; i = i + maxcount){
				data[i] = inputs.slice(i, i + maxcount).serialize();
			}
		}else{
			data[0] = inputs.serialize();
		}
		
		return data;
	};
	
	$.G2.composer.init = function(){
		var section = arguments[0];
		var args = arguments[1];
		
		$.G2.composer[section] = {};
		$.G2.composer[section].params = args;
	};
	
	$.G2.composer.ready = function(){
		var section = arguments[0];
		var args = arguments[1];
		
		$.extend($.G2.composer[section].params, args);
		
		$.each($.G2.composer[section].params, function(i, arr){
			$.G2[i]['ready'].apply($.G2[i], arr);
		});
	};
}(jQuery));
(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	$.G2.boot = {};
	
	$.G2.boot.autocompleter = function(Container){
		Container.find('[data-autocomplete]').each(function(i, dropfield){
			$(dropfield).closest('.ui.search.dropdown').dropdown({
				apiSettings : {
					url: $(dropfield).data('url') + '&' + $(dropfield).attr('name') + '={query}',
					cache : false,
					onResponse : function(Response){
						if(!Response.hasOwnProperty('results')){
							var results = [];
							results['success'] = true;
							results['results'] = [];
							
							var count = 0;
							$.each(Response, function(key, obj){
								results['results'][count] = {};
								results['results'][count]['value'] = key;
								results['results'][count]['name'] = obj;
								count = count + 1;
							});
							
							return results;
						}
					}
				},
				minCharacters: $(dropfield).data('mincharacters') ? $(dropfield).data('mincharacters') : 0,
				message : {noResults : $(dropfield).data('noresults') ? $(dropfield).data('noresults') : 'No results found'},
				//saveRemoteData:false
			});
		});
	};
	
	$.G2.boot.calendar = function(Container){
		//calendar
		Container.find('[data-calendar]').each(function(i, calfield){
			if($(calfield).data('calendarready') === true){
				return true;
			}
			$(calfield).data('calendarready', true);
			
			var mindate = null;
			if($(calfield).data('mindate')){
				var parts = $(calfield).data('mindate').split('-');
				var mindate = new Date(parts[0], parts[1]-1, parts[2]); 
			}
			var maxdate = null;
			if($(calfield).data('maxdate')){
				var parts = $(calfield).data('maxdate').split('-');
				var maxdate = new Date(parts[0], parts[1]-1, parts[2]); 
			}
			if(jQuery.fn.calendar != undefined){
				
				var $realDate = $('<input type="hidden" name="'+$(calfield).attr('name')+'">');
				if($('[type="hidden"][name="'+$(calfield).attr('name')+'"]').length == 0){
					$(calfield).closest('.field').after($realDate);
				}else{
					$realDate = $('[type="hidden"][name="'+$(calfield).attr('name')+'"]').first();
				}
				
				var dformat = $(calfield).data('dformat') ? $(calfield).data('dformat') : 'YYYY-MM-DD';
				var sformat = $(calfield).data('sformat') ? $(calfield).data('sformat') : 'YYYY-MM-DD';
				
				if($(calfield).val().length > 0){
					var calval = $(calfield).val();
					$realDate.val(calval);
					$(calfield).val(moment(calval, sformat).format(dformat));
				}
				
				var opendays = [1,2,3,4,5,6,7];//1 for monday
				if($(calfield).data('opendays')){
					opendays = $(calfield).data('opendays').split(',').map(Number);
				}
				
				var openhours = [1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24];//1 for monday
				if($(calfield).data('openhours')){
					openhours = $(calfield).data('openhours').split(',').map(Number);
				}
				
				$(calfield).closest('.field').calendar({
					startMode : $(calfield).data('startmode'),
					type : $(calfield).data('type'),
					minDate : mindate,
					maxDate : maxdate,
					startCalendar: $(calfield).data('startcalendar') ? $($(calfield).data('startcalendar')).closest('.field') : null,
					endCalendar: $(calfield).data('endcalendar') ? $($(calfield).data('endcalendar')).closest('.field') : null,
					firstDayOfWeek: $(calfield).data('firstday') ? $(calfield).data('firstday') : 0,
					ampm: ($(calfield).data('ampm') != undefined) ? $(calfield).data('ampm') : true,
					monthFirst: $(calfield).data('monthfirst') ? $(calfield).data('monthfirst') : true,
					
					formatter:{
						datetime: function (date, settings) {
							if (!date) return '';
							return moment(date).format(dformat);
						},
						cell: function(cell, date, cellOptions){
							if(cellOptions.mode == 'day' && (opendays.indexOf(parseInt(moment(date).format('E'))) == -1)){
								$(cell).addClass('disabled');
							}
							
							if(cellOptions.mode == 'hour' && (openhours.indexOf(parseInt(moment(date).format('k'))) == -1)){
								$(cell).addClass('disabled');
							}
						}
					},
					parser:{
						date: function (text, settings) {
							if (!text) return '';
							return moment(text, dformat).toDate();
						}
					},
					onChange: function (date, text, mode){
						$realDate.val(moment(date).format(sformat));
					},
					popupOptions:{
						position: $(calfield).data('popuppos') ? $(calfield).data('popuppos') : 'top center'
					},

					text:{
						days: $(calfield).data('days') ? $(calfield).data('days').split(',') : ['S', 'M', 'T', 'W', 'T', 'F', 'S'],
						months: $(calfield).data('months') ? $(calfield).data('months').split(',') : ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
						monthsShort: $(calfield).data('monthsshort') ? $(calfield).data('monthsshort').split(',') : ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
						today: $(calfield).data('today') ? $(calfield).data('today').split(',') : 'Today',
						now: $(calfield).data('now') ? $(calfield).data('now').split(',') : 'Now',
						am: $(calfield).data('am') ? $(calfield).data('am').split(',') : 'AM',
						pm: $(calfield).data('pm') ? $(calfield).data('pm').split(',') : 'PM'
					}
				});
				
			}
		});
	};
	
	$.G2.boot.ready = function(){
		$('body').on('contentChange.basics', '*', function(e){
			e.stopPropagation();
			
			if($(this).prop('tagName') != 'DIV' && $(this).prop('tagName') != 'FORM'){
				//return false;
			}
			
			if(jQuery.fn.tab != undefined){
				$(this).find('.ui.menu.G2-tabs .item, .ui.steps.G2-tabs .step').tab();
			}
			if(jQuery.fn.dropdown != undefined){
				$(this).find('.ui.dropdown').dropdown({'forceSelection' : false, 'placeholder' : ''});
				$.G2.boot.autocompleter($(this));
			}
			if(jQuery.fn.checkbox != undefined){
				$(this).find('.ui.checkbox').checkbox('refresh');
			}
			
			if(jQuery.fn.embed != undefined){
				$(this).find('.ui.embed').embed();
			}
			
			if(jQuery.fn.accordion != undefined){
				$(this).find('.ui.accordion').accordion();
				$(this).find('.ui.accordion').accordion('refresh');
			}
			
			if(jQuery.fn.tooltipster != undefined){
				$(this).find('[data-hint]').addBack().each(function(i, element){
					$(element).tooltipster({
						content: $(element).data('hint'),
						maxWidth: 300,
						delay: 50,
						debug: false,
						contentAsHTML: true
					});
				});
			}
			
			//G2 actions
			if($.G2.actions != undefined){
				$.G2.actions.ready();
			}
			
			if($.G2.actions2 != undefined){
				$.G2.actions2.ready($(this));
			}
			
			if($.G2.forms2 != undefined){
				$.G2.forms2.ready($(this));
			}
			
			$.G2.boot.calendar($(this));
			
			//wysiwyg editor
			if($.G2.tinymce != undefined){
				$.G2.tinymce.init();
			}
			//textareas expand
			$(this).on('keyup.resize', 'textarea[data-autoresize="1"]', function(e){
				$(this).css('overflow', 'hidden');
				if($(this).val().split("\n").length > $(this).attr('rows')){
					$(this).attr('rows', $(this).val().split("\n").length);
				}else{
					if($(this).data('rows') == undefined){
						$(this).data('rows', $(this).attr('rows'));
					}
					if($(this).data('rows') <= $(this).val().split("\n").length){
						$(this).attr('rows', $(this).val().split("\n").length);
					}
				}
			});
			$(this).find('textarea[data-autoresize="1"]').trigger('keyup.resize');
			
		});
		
		//new forms
		//if($('form.ui.form.ce_form').length > 0){
		if($.G2.validation != undefined){
			$('body').on('contentChange.form', 'form', function(e){
				e.stopPropagation();
				$.G2.validation.ready($(this));
			});
			
			$('form.ui.form.ce_form').trigger('contentChange');
		}
		
		//toolbar
		$('.ui.toolbar-button[data-url]').on('click', function(e){
			if($(this).attr('data-form')){
				var toolbar_form = $($(this).attr('data-form'));
			}else{
				var toolbar_form = $(this).closest('form');
			}
			
			toolbar_form.attr('action', $(this).data('url'));
			
			if($(this).attr('name')){
				toolbar_form.append($('<input />').attr('type', 'hidden').attr('name', $(this).attr('name')).val(1));
			}
			
			if($(this).data('selections') == '1' && toolbar_form.find('.ui.selector.checkbox.checked').length == 0){
				alert($(this).data('message'));
				return false;
			}
			
			if($(this).attr('data-fn')){
				var fn = $(this).attr('data-fn');
				window[$(this).attr('data-fn')]($(this));
			}else{
				toolbar_form.submit();
			}
		});
		
		//list selectors
		if(jQuery.fn.checkbox != undefined){
			$('.ui.selector.checkbox').checkbox({
				onChecked: function(){
					$(this).closest('tr').addClass('warning');
				},
				onUnchecked: function(){
					$(this).closest('tr').removeClass('warning');
				}
			});
			$('.ui.selector.checkbox').checkbox('attach events', '.ui.select_all.checkbox');
		}
		
		//errors
		$(':input[data-error]').closest('.field').addClass('error');
		
	};
	
}(jQuery));
(function($){
	if($.G2 == undefined){
		$.G2 = {};
	}
	$.G2.forms = {};
	
	$.G2.forms.initializeForm = function (Form){
		var validationRules = {};
		
		jQuery.fn.form.settings.rules.required = function(value){
			if(value){
				return true;
			}else{
				return false;
			}
		};
		
		jQuery.fn.form.settings.rules.email = function(value){
			if(value.match(/^([a-zA-Z0-9_\.\-\+%])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{1,11})+$/)){
				return true;
			}else{
				return false;
			}
		};
		
		jQuery.fn.form.settings.rules.minChecked = function(value, minChecked){
			jQuery(this).closest('.fields').off('change.validation').on('change.validation', ':input', function(){
				Form.form('validate form');
			});
			
			if(jQuery(this).closest('.fields').find(':input:checked').length >= minChecked){
				jQuery(this).closest('.fields').removeClass('error');
				return true;
			}else{
				jQuery(this).closest('.fields').addClass('error');
				return false;
			}
		};
		
		jQuery.fn.form.settings.rules.maxChecked = function(value, maxChecked){
			jQuery(this).closest('.fields').off('change.validation').on('change.validation', ':input', function(){
				Form.form('validate form');
			});
			
			if(jQuery(this).closest('.fields').find(':input:checked').length > maxChecked){
				jQuery(this).closest('.fields').addClass('error');
				return false;
			}else{
				jQuery(this).closest('.fields').removeClass('error');
				return true;
			}
		};
		
		jQuery.fn.form.settings.rules.exactChecked = function(value, exactChecked){
			jQuery(this).closest('.fields').off('change.validation').on('change.validation', ':input', function(){
				Form.form('validate form');
			});
			
			if(jQuery(this).closest('.fields').find(':input:checked').length != exactChecked){
				jQuery(this).closest('.fields').addClass('error');
				return false;
			}else{
				jQuery(this).closest('.fields').removeClass('error');
				return true;
			}
		};
		
		Form.find('[data-validationrules]').each(function(i, inp){
			if(jQuery(inp).data('validationrules').disabled == undefined || jQuery(inp).data('validationrules').disabled == 0){
				validationRules['field'+i] = jQuery(inp).data('validationrules');
				
				//jQuery.each(['empty', 'required', 'checked', 'minChecked', 'maxChecked', 'exactChecked'], function(i, r){
				jQuery.each(jQuery(inp).data('validationrules')['rules'], function(i, r){
					//if(jQuery(inp).data('validationrules')['rules'][0]['type'].indexOf(r) >= 0){
					jQuery.each(['empty', 'required', 'checked', 'minChecked', 'maxChecked', 'exactChecked'], function(ir, vr){
						if(r['type'].indexOf(vr) > -1){
							if(jQuery(inp).parent().hasClass('checkbox')){
								if(jQuery(inp).closest('.fields').length > 0){
									jQuery(inp).closest('.fields').addClass('required');
								}else{
									jQuery(inp).closest('.field').addClass('required');
								}
							}else{
								jQuery(inp).closest('.field').addClass('required');
							}
						}
					});
				});
			}
		});
		
		Form.form({
			//inline : true,
			inline : Form.data('valloc') ? ((Form.data('valloc') == 'inline' || Form.data('valloc') == 'inlinetext') ? true : false) : true,
			on : 'blur',
			fields: validationRules,
			onInvalid: function(){
				if(Form.data('valloc') == 'inlinetext'){
					var erlabel = $(this).closest('.field').find('.ui.label.prompt.pointing').first();
					erlabel.css('display', 'none !important');
					var field = $(this).closest('.multifield.fields.grouped, .multifield.fields.inline').length > 0 ? $(this).closest('.multifield.fields.grouped, .multifield.fields.inline') : $(this).closest('.field');
					if(field.find('small.custom-error').length > 0){
						field.find('small.custom-error').show();
					}else{
						field.append($('<small class="custom-error">').css('color', 'red').css('display', 'block').text(erlabel.text()));
					}
					erlabel.remove();
				}
			},
			onValid: function(){
				var field = $(this).closest('.multifield.fields.grouped, .multifield.fields.inline').length > 0 ? $(this).closest('.multifield.fields.grouped, .multifield.fields.inline') : $(this).closest('.field');
				field.find('small.custom-error').hide();
			}
		});
	}
	
	$.G2.forms.initializeEvents = function (Form){
		//Form.find('[data-events]').each(function(i, inp){
		Form.off('change.events click.events ready.events', '[data-events]');
		Form.on('change.events click.events ready.events', '[data-events]', function(e){
			e.stopPropagation();
			//var events = jQuery(inp).data('events');
			var inp = this;
			var events = JSON.parse(jQuery(inp).attr('data-events'));
			
			//jQuery(inp).off('change.events click.events ready.events');
			jQuery.each(events, function(ei, event){
				//jQuery(inp).off('change click ready');
				//jQuery(inp).on('change.events click.events ready.events', function(e){
					
					if(event.hasOwnProperty('identifier') != true || event['identifier'] == '' || event.hasOwnProperty('action') != true || event.action.length == 0){
						return;
					}
					
					//get current input value
					var inp_value = jQuery(inp).data('value') ? jQuery(inp).data('value') : jQuery(inp).val();
					
					if(jQuery(inp).attr('type') == 'checkbox'){
						inp_value = (jQuery(inp).is(':checked') ? inp_value : '');
					}
					if(jQuery(inp).prop('tagName') == 'SELECT'){
						inp_value = jQuery(inp).find(':selected').data('value') ? jQuery(inp).find(':selected').data('value') : jQuery(inp).val();
					}
					if(event.hasOwnProperty('value') != true){
						event['value'] = [jQuery(inp).val()];
					}
					if(event.hasOwnProperty('group') && event.group == 1){
						inp_value = [];
						jQuery.each(jQuery(inp).closest('.fields').find(':input:checked'), function(kk, checked){
							if(jQuery(checked).data('value')){
								inp_value.push(jQuery(checked).data('value'));
							}else{
								inp_value.push(jQuery(checked).val());
							}
						});
					}
					
					//evaluate condition
					var event_condition = false;
					if(jQuery.isArray(inp_value)){
						if(event.sign == '='){
							//event_condition = (jQuery.inArray(event['value'], inp_value) > -1);
							event_condition = (jQuery(inp_value).filter(event['value']).length > 0);
						}else if(event.sign == '!='){
							//event_condition = (jQuery.inArray(event['value'], inp_value) == -1);
							event_condition = (jQuery(inp_value).filter(event['value']).length == 0);
						}else if(event.sign == 'change'){
							if(e.type != 'ready'){
								event_condition = true;
							}
						}
					}else{
						if(event.sign == '='){
							//event_condition = (inp_value == event['value']);
							event_condition = (jQuery([inp_value]).filter(event['value']).length > 0);
						}else if(event.sign == '!='){
							//event_condition = (inp_value != event['value']);
							event_condition = (jQuery([inp_value]).filter(event['value']).length == 0);
						}else if(event.sign == 'change'){
							if(e.type != 'ready'){
								event_condition = true;
							}
						}else if(event.sign == 'click' && e.type == 'click'){
							event_condition = true;
						}
					}
					
					var event_targets = [];
					jQuery.each(event['identifier'], function(idi, ident){
						if(ident.substring(0, 1) == '#' || ident.substring(0, 1) == '.' || ident.substring(0, 1) == '['){
							event_targets = jQuery.merge(event_targets, jQuery(ident));
						}else{
							event_targets = jQuery.merge(event_targets, jQuery(':input[name="' + ident + '"]'));
							if(jQuery.inArray('function', event.action) > -1){
								event_targets = [ident];
							}
						}
					});
					
					jQuery.each(event_targets, function(ix, event_target){
						event_target = jQuery(event_target);
						var event_target_one = event_target;
						
						var target_element = event_target.closest('.field');
						if(jQuery.inArray(event_target.prop('tagName'), ['BUTTON', 'DIV']) > -1){
							target_element = event_target;
						}
						if(jQuery.inArray(event_target.prop('type'), ['checkbox', 'radio']) > -1){
							target_element = event_target.closest('.multifield.fields').length > 0 ? event_target.closest('.multifield.fields') : event_target.closest('.field');
						}
						
						if(event_target.data('ghost')){
							if(event_target.closest('.multifield.fields').length > 0){
								var real_event_target = event_target.closest('.multifield.fields').find(':checkbox, :radio');
								target_element = event_target.closest('.multifield.fields');
								if(real_event_target.length > 0){
									event_target = real_event_target;
									event_target_one = real_event_target.first();
								}
							}else{
								
							}
						}
						
						if(jQuery.isArray(event.action) == false){
							event.action = [event.action];
						}
						if(event_condition){
							if(jQuery.inArray('hide', event.action) > -1){
								target_element.hide();
							}
							if(jQuery.inArray('show', event.action) > -1){
								//target_element.show();
								target_element.css('display', '');
								target_element.removeClass('hidden');
							}
							if(jQuery.inArray('disable', event.action) > -1){
								target_element.addClass('disabled');
								event_target.prop('disabled', true);
							}
							if(jQuery.inArray('enable', event.action) > -1){
								target_element.removeClass('disabled');
								event_target.prop('disabled', false);
								if(event_target.prop('tagName') == 'SELECT'){
									event_target.parent('.ui.dropdown').removeClass('disabled');
								}
							}
							if(jQuery.inArray('disable_validation', event.action) > -1){
								if(event_target_one.data('validationrules')){
									var vrules = event_target_one.data('validationrules');
									vrules['disabled'] = 1;
									event_target_one.data('validationrules', vrules);
									
									$.G2.forms.initializeForm(Form);
									target_element.removeClass('required error');
									target_element.find('.ui.label.red.pointing.prompt').remove();
								}
							}
							if(jQuery.inArray('enable_validation', event.action) > -1){
								if(event_target_one.data('validationrules')){
									var vrules = event_target_one.data('validationrules');
									vrules['disabled'] = 0;
									event_target_one.data('validationrules', vrules);
									
									$.G2.forms.initializeForm(Form);
								}
							}
							if(jQuery.inArray('reload', event.action) > -1){
								if(e.type != 'ready' && event_target.length > 0){
									target_element.addClass('ui form loading');
									
									$.ajax({
										url: event_target.data('reloadurl'),
										data: jQuery(inp).closest('.form').serialize(),
										success: function(result){
											var newContent = $(result);
											
											target_element.replaceWith(newContent);
											
											newContent.trigger('contentChange');
											jQuery.G2.forms.initializeForm(Form);
											//Form.trigger('contentChange');
										}
									});
								}
							}
							if(jQuery.inArray('function', event.action) > -1){
								jQuery.each(event['identifier'], function(idi, ident){
									if(e.type != 'ready' && window[ident] != undefined){
										window[ident](jQuery(inp));
									}
								});
							}
							//if(jQuery.inArray(event.action, ['add', 'sub', 'multiply', 'set']) > -1){
							if(jQuery(event.action).filter(['add', 'sub', 'multiply', 'set']).length){
								target_element = event_target;
								
								var current_value = parseFloat(target_element.val());
								if(isNaN(current_value)){
									current_value = 0;
								}
								
								if(jQuery.isArray(inp_value)){
									var inp_value_float = 0;
									jQuery.each(inp_value, function(iv, inp_value_v){
										if(!isNaN(parseFloat(inp_value_v))){
											inp_value_float = inp_value_float + parseFloat(inp_value_v);
										}
									});
								}else{
									var inp_value_float = parseFloat(inp_value);
									if(isNaN(inp_value_float)){
										inp_value_float = 0;
										if(event.action == 'multiply'){
											inp_value_float = 1;
										}
									}
								}
								
								var calcList = {};
								var inp_name = jQuery(inp).attr('name');
								
								if(target_element.data('calclist')){
									calcList = target_element.data('calclist');
								}
								
								var prev_inp_value = 0;
								if(calcList.hasOwnProperty(inp_name)){
									prev_inp_value = calcList[inp_name];
								}
								
								calcList[inp_name] = inp_value_float;
								target_element.data('calclist', calcList);
								var change_value = 0;
								
								if(jQuery.inArray('add', event.action) > -1){
									var total = current_value + inp_value_float - prev_inp_value;
									change_value = inp_value_float;
								}else if(jQuery.inArray('sub', event.action) > -1){
									var total = current_value - inp_value_float - prev_inp_value;
									change_value = - inp_value_float;
								}else if(jQuery.inArray('multiply', event.action) > -1){
									if(prev_inp_value == 0){
										prev_inp_value = 1;
									}
									var total = (current_value/prev_inp_value) * inp_value_float;
								}else if(jQuery.inArray('set', event.action) > -1){
									var total = inp_value_float;
								}
								
								if(change_value != 0){
									calcList[inp_name] = change_value;
									target_element.data('calclist', calcList);
								}
								
								target_element.val(total);
								
								if(target_element.data('display')){
									jQuery('#'+target_element.data('display')).text(total);
								}
							}
						}
					});
				//});
				
				//jQuery(inp).trigger('ready.events');
			});
			
			//jQuery(inp).trigger('ready.events');
		});
		
		Form.find('[data-events]').trigger('ready.events');
	}
	
	$.G2.forms.initializeFeatures = function (Form){
		Form.on('click', '.partitioned .ui.button.next, .partitioned .ui.button.forward', function(e){
			e.preventDefault();
			var activeTab = jQuery(this).closest('.partitioned').find('.ui.segment.tab.active').first();
			activeTab.find(':input').trigger('blur');
			
			if(activeTab.next('.ui.segment.tab').length > 0 && activeTab.find('.field.error').length == 0){
				activeTab.removeClass('active');
				jQuery('[data-tab="'+activeTab.data('tab')+'"]').removeClass('active');
				activeTab.next('.ui.segment.tab').addClass('active');
				jQuery('[data-tab="'+activeTab.next('.ui.segment.tab').data('tab')+'"]').addClass('active').removeClass('disabled');
			}else{
				
			}
		});
		
		Form.on('click', '.partitioned .ui.button.prev, .partitioned .ui.button.backward', function(e){
			e.preventDefault();
			var activeTab = jQuery(this).closest('.partitioned').find('.ui.segment.tab.active').first();
			activeTab.find(':input').trigger('blur');
			
			if(activeTab.prev('.ui.segment.tab').length > 0 && activeTab.find('.field.error').length == 0){
				activeTab.removeClass('active');
				jQuery('[data-tab="'+activeTab.data('tab')+'"]').removeClass('active');
				activeTab.prev('.ui.segment.tab').addClass('active');
				jQuery('[data-tab="'+activeTab.prev('.ui.segment.tab').data('tab')+'"]').addClass('active').removeClass('disabled');
			}else{
				
			}
		});
		
		//Form.find('.repeater .ui.source-item').hide().find(':input').prop('disabled', true);
		Form.find('.repeater .ui.source-item').hide().find(':input').each(function(i, inp){
			$(inp).attr('ex-name', $(inp).attr('name'));
			$(inp).removeAttr('name');
			if(jQuery(inp).data('validationrules')){
				$(inp).attr('data-exvalidationrules', $(inp).attr('data-validationrules'));
				$(inp).removeAttr('data-validationrules');
			}
		});
		
		Form.on('click.repeater', '.repeater .ui.button.multiply', function(e){
			e.preventDefault();
			
			var cloned = jQuery(this).closest('.repeater').find('.ui.source-item').clone().show();
			cloned.find(':input').each(function(i, inp){
				$(inp).attr('name', $(inp).attr('ex-name'));
				$(inp).removeAttr('ex-name');
				if(jQuery(inp).attr('data-exvalidationrules')){
					$(inp).attr('data-validationrules', $(inp).attr('data-exvalidationrules'));
				}
			});
			
			var newHTML = cloned.html().replace(/-N-/g, jQuery(this).closest('.repeater').data('count'));
			if(cloned.data('name')){
				repeaterRegex = new RegExp('#'+cloned.data('name')+'.count', 'gi');
				newHTML = newHTML.replace(repeaterRegex, jQuery(this).closest('.repeater').data('count'));
			}
			
			cloned.html(newHTML);
			jQuery(this).closest('.repeater').data('count', parseInt(jQuery(this).closest('.repeater').data('count')) + 1);
			
			if(jQuery(this).closest('.repeater').data('limit')){
				if(jQuery(this).closest('.repeater').find('.clone-item').length >= parseInt(jQuery(this).closest('.repeater').data('limit'))){
					return;
				}
			}
			jQuery(this).before(cloned.removeClass('source-item').addClass('clone-item'));
			
			cloned.trigger('contentChange');
			jQuery.G2.forms.initializeForm(Form);
			
			jQuery(this).closest('.repeater').trigger('g2.forms.repeater.add');
		});
		
		Form.on('click.repeater', '.repeater .ui.button.remove', function(e){
			e.preventDefault();
			
			jQuery(this).closest('.ui.clone-item').remove();
			
			jQuery(this).closest('.repeater').trigger('g2.forms.repeater.remove');
			
			jQuery.G2.forms.initializeForm(Form);
		});
		
		Form.on('click', '.modaled > .ui.button.green, .modaled > .ui.button.launch', function(e){
			e.preventDefault();
			var theModal = jQuery(this).closest('.modaled').find('.ui.modal').first();
			theModal.modal({detachable : false, closable : (theModal.data('closable') ? true : false)}).modal('show');
		});
		
		Form.on('submit', function(e){
			if(Form.form('is valid') == false){
				if(Form.find('.field.error').first().is(':visible')){
					jQuery.G2.scrollTo(Form.find('.field.error').first());
				}else{
					if(Form.find('.field.error').first().closest('.partitioned').length > 0){
						var activeTab = Form.find('.field.error').first().closest('.partitioned').find('.ui.segment.tab.active').first();
			
						activeTab.removeClass('active');
						jQuery('[data-tab="'+activeTab.data('tab')+'"]').removeClass('active');
						Form.find('.field.error').first().closest('.ui.segment.tab').addClass('active');
						jQuery('[data-tab="'+Form.find('.field.error').first().closest('.ui.segment.tab').data('tab')+'"]').addClass('active');
						jQuery('[data-tab="'+Form.find('.field.error').first().closest('.ui.segment.tab').data('tab')+'"]').removeClass('disabled');
					}
				}
			}else{
				if(Form.data('subanimation')){
					Form.addClass('loading');
				}
				//Form.form('submit');
			}
		});
	}
	
	$.G2.forms.invisible = function(){
		jQuery('div[data-invisible="1"]').each(function(i, invForm){
			var content = jQuery(invForm).html();
			var newForm = jQuery('<form>').html(content);
			jQuery.each(jQuery(invForm).get(0).attributes, function(i, att){
				newForm.attr(att.name, att.value);
			});
			jQuery(invForm).replaceWith(newForm);
			//jQuery('body').trigger('contentChange');
		});
	}
	
	$.G2.forms.ready = function(Form){
		jQuery.G2.forms.initializeFeatures(Form);
		
		jQuery.G2.forms.initializeEvents(Form);
		
		jQuery.G2.forms.initializeForm(Form);
		
		if(jQuery.fn.inputmask != undefined){
			Form.find('[data-inputmask]').inputmask();
		}
		
		Form.on('g2.actions.dynamic.beforeStart', function(){
			Form.data('beforeStart', Form.form('is valid'));
		});
	}
	
}(jQuery));