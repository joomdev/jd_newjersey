/*
# mod_sp_tweet - Twitter Module by JoomShaper.com
# -----------------------------------------------
# Author    JoomShaper http://www.joomshaper.com
# Copyright (C) 2010 - 2014 JoomShaper.com. All Rights Reserved.
# license - GNU/GPL V2 or Later
# Websites: http://www.joomshaper.com
*/

if ( typeof Object.create !== 'function' ) {
    Object.create = function( obj ) {
        function F() {};
        F.prototype = obj;
        return new F();
    };
}

!(function($){

    var spTweetSlide = {

        init: function( options, elem ) {
            var self        = this;
            self.elem       = elem;
            self.$elem      = $( elem );
            self.options    = $.extend( {}, $.fn.sptweetSlide.options, options );
            this.items      = self.$elem.find('.sp-tweet-item');
           
            if (this.items.length > 0) {
                this.items.css({
                    'display': 'none'
                });
                this.items.first().css({
                    'display': 'block'
                }).addClass('current');
            }

            setInterval($.proxy(this.next, this), this.options.animationPeriodicalTime);
        },

        currentItem: function(){
            return this.items.filter('.current').index();
        },

        next: function() {
            var index = this.currentItem();
            if(index < this.items.length-1 ) {
                this.go(index+1);
            } else {
                this.go(0);
            }
        },

        go: function(index) {

            this.previousIndex      = this.items.filter('.current').index();
            this.currentIndex       = index;
        
            this.previous           = $(this.items[this.previousIndex]).removeClass('current');
            this.current            = $(this.items[this.currentIndex]).addClass('current');

            $(this.previous).css('display', 'none');
            $(this.current).fadeIn(this.options.morphDuration);

        }
    }

    $.fn.sptweetSlide = function( options ) {
        return this.each(function() {
            var sptweetSlide = Object.create( spTweetSlide )
            sptweetSlide.init( options, this )
        })
    }

    $.fn.sptweetSlide.options = {
        'morphDuration': 400,
        'animationPeriodicalTime': 8000,
        'cssClass': 'sp-tweet-item'
    }

})(jQuery);