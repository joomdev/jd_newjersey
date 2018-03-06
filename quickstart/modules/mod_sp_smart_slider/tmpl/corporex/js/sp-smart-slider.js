/**
* @author    JoomShaper http://www.joomshaper.com
* @copyright Copyright (C) 2010 - 2013 JoomShaper
* @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2
*/;
(function ($) {
    var methods = {
        currentIndex: 0,
        prevIndex: 0,
        nextIndex: 0,
        totalItems: 0,
        settings: {},
        canvasWidth: 0,
        canvasHeight: 0,
        elements: '',
        prevIndex: function () {
            var c = this.currentIndex;
            c--;
            if (c < 0)
                c = this.totalItems - 1;
            return c;
        },
        nextIndex: function () {
            var c = this.currentIndex;
            c++;
            if (c >= (this.totalItems))
                c = 0;
            return c;
        },

        currentIndex: function () {
            return this.currentIndex;
        },

        prev: function () {
            clearTimeout(this.autoplay);
            this.currentIndex--;
            if (this.currentIndex < 0) {
                this.currentIndex = this.totalItems - 1;
                this.prevIndex = methods['prevIndex'].call(this);
            }
            var $this = this;
            methods['run'].call(this);
        },

        next: function () {
            clearTimeout(this.autoplay);
            this.currentIndex++;
            if (this.currentIndex >=this.totalItems){
                this.currentIndex = 0;
                this.nextIndex = methods['nextIndex'].call(this);
            }
            methods['run'].call(this);
        },

        play: function () {
            var $this = this;
            this.autoplay = setTimeout(function () {
                methods['next'].call($this);
            }, 1000);
        },

        preloader: function () {
            var loadedImage = new Image();
            var preloadImages = this.settings.preloadImages;
            var $this = this;

            if (this.settings.showPreloader != true || preloadImages.length < 1) {
                $(this.settings.preloader).remove();
                methods['start'].call(this);
                this.elements.trigger('onSlide');
            } else {
                for (i = 0; i < preloadImages.length; i++) loadedImage.src = preloadImages[i];
                    $(loadedImage).load(function () {
                        $($this.settings.preloader).fadeOut('fast', function () {
                            $(this).remove();
                        });
                        methods['start'].call($this);
                        $this.elements.trigger('onSlide');
                    });
            }
        },

        autoplay: function () {
            var $this = this;
            if (this.settings.autoplay == true) {
                this.autoplay = setTimeout(function () {
                    methods['next'].call($this);
                }, this.settings.interval);
            }
        },

        pause: function () {
            clearTimeout(this.autoplay);
        },

        goTo: function (index) {
            clearTimeout(this.autoplay);
            if( this.currentIndex==index ){
                return false;
            }
            this.currentIndex = index;
            methods['run'].call(this);
        },

        run: function () {
            clearTimeout(this.delay);
            clearTimeout(this.autoplay);
            var $this = this;
            var $item = this.items;

            $item.each(function(){
                if( $(this).hasClass($this.settings.animateInClass) ){
                    $(this).removeClass($this.settings.animateInClass).addClass($this.settings.animateOutClass);
                }
            });

            this.delay = setTimeout(function () {
                $item.removeClass($this.settings.animateOutClass);
                $item.eq($this.currentIndex).removeClass($this.settings.animateOutClass).addClass($this.settings.animateInClass);

                $this.elements.trigger('onSlide');
            }, this.settings.delay);

            methods['autoplay'].call(this);
        },

        start: function () {
            clearTimeout(this.delay);
            clearTimeout(this.autoplay);
            var $this = this;
            var $item = this.items;

            this.delay = setTimeout(function () {

                $item.eq($this.currentIndex)
                .removeClass($this.settings.animateOutClass)
                .addClass($this.settings.animateInClass);

                $this.elements.trigger('onSlide');
            }, this.settings.delay);

            methods['autoplay'].call(this);
        },

        resize: function (fn) {
            if (this.settings.fullWidth == true) {
                this.elements.height($(window).width() * this.ratioHeight);
            }
        },

        onSlide: function (fn) {
            var $this = this;
            this.elements.bind('onSlide', function (event) {
                fn($this.currentIndex, $this.items, event);
            });
        },

        init: function (elements, settings) {
            this.currentIndex = 0;
            this.elements = elements;
            this.items = $(elements).find('>*');

            this.totalItems = this.items.length;
            this.settings = settings;

            var $this = this;
            this.items.each(function (i) {
                $(this).addClass($this.settings.itemClassPrefix + (i + 1));
            });
        },

    };

    $.fn.spSmartslider = function (options, param) {

        var settings = $.extend({
            preloadImages: [],
            autoplay: true,
            preloader: '.sp-preloader',
            showPreloader: true,
            interval: 5000,
            delay: 500,
            itemClassPrefix: 'item-',
            rWidth: 0,
            rHeight: 0,
            fullWidth: false,
            animateInClass: 'animate-in',
            animateOutClass: 'animate-out',
        }, options);

        return this.each(function (index, element) {

            if (typeof (options) === 'string') {
                methods[options].call(this, param);
            } else {
                methods['init'].call(this, $(this), settings);
                methods['preloader'].call(this);
                methods['autoplay'].call(this);
                methods['resize'].call(this);
            }
        });
    }
})(jQuery);