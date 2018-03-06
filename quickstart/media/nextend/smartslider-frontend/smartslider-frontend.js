(function ($, scope, undefined) {
    function NextendSmartSliderBackgroundImages(slider) {
        this.device = null;

        this.load = $.Deferred();

        this.slider = slider;
        this.slides = this.slider.realSlides;

        this.lazyLoad = slider.parameters.lazyLoad;
        this.lazyLoadNeighbor = slider.parameters.lazyLoadNeighbor;

        this.deviceDeferred = $.Deferred();

        /**
         * @type {NextendSmartSliderBackgroundImage[]}
         */
        this.backgroundImages = [];
        for (var i = 0; i < this.slides.length; i++) {
            var image = this.slides.eq(i).find('.n2-ss-slide-background');
            if (image.length > 0) {
                this.backgroundImages[i] = new NextendSmartSliderBackgroundImage(i, image, this);
            } else {
                this.backgroundImages[i] = false;
            }
            this.slides.eq(i).data('slideBackground', this.backgroundImages[i]);
        }

        this.slider.sliderElement.one('SliderDevice', $.proxy(this.onSlideDeviceChangedFirst, this));

    };

    NextendSmartSliderBackgroundImages.prototype.getBackgroundImages = function () {
        return this.backgroundImages;
    };

    NextendSmartSliderBackgroundImages.prototype.onSlideDeviceChangedFirst = function (e, device) {
        this.onSlideDeviceChanged(e, device);
        this.deviceDeferred.resolve();
        this.slider.sliderElement.on('SliderDevice', $.proxy(this.onSlideDeviceChanged, this));

        if (this.lazyLoad == 1) {
            this.preLoad = this.preLoadLazyNeighbor;

            this.load = $.when(this.preLoad(this.slider.currentSlideIndex));
        } else if (this.lazyLoad == 2) { // delayed
            $(window).load($.proxy(this.preLoadAll, this));

            this.load = $.when(this.preLoad(this.slider.currentSlideIndex));
        } else {
            this.load = $.when.apply($, this.preLoadAll());
        }
    };

    NextendSmartSliderBackgroundImages.prototype.onSlideDeviceChanged = function (e, device) {
        this.device = device;
        for (var i = 0; i < this.backgroundImages.length; i++) {
            if (this.backgroundImages[i]) {
                this.backgroundImages[i].onSlideDeviceChanged(device);
            }
        }
    };

    NextendSmartSliderBackgroundImages.prototype.changed = function (i) {
        if (this.lazyLoad == 1 || this.lazyLoad == 2) {
            if (i == this.slider.currentSlideIndex) {
                this.preLoad(i);
            }
        } else {
            this.preLoad(i);
        }
    };

    NextendSmartSliderBackgroundImages.prototype.preLoadCurrent = function () {
        this.preLoad(this.slider.currentSlideIndex);
    };

    NextendSmartSliderBackgroundImages.prototype.preLoadAll = function () {
        var deferreds = [];
        for (var i = 0; i < this.backgroundImages.length; i++) {
            deferreds.push(this._preLoad(i));
        }
        return deferreds;
    };

    NextendSmartSliderBackgroundImages.prototype.preLoad = function (i) {
        return this._preLoad(i);
    };

    NextendSmartSliderBackgroundImages.prototype.preLoadLazyNeighbor = function (i) {

        var lazyLoadNeighbor = this.lazyLoadNeighbor,
            deferreds = [this._preLoad(i)];

        if (lazyLoadNeighbor) {
            var j = 0,
                k = i;
            while (j < lazyLoadNeighbor) {
                k--;
                if (k < 0) {
                    k = this.backgroundImages.length - 1;
                }
                deferreds.push(this._preLoad(k));
                j++;
            }
            j = 0;
            k = i;
            while (j < lazyLoadNeighbor) {
                k++;
                if (k >= this.backgroundImages.length) {
                    k = 0;
                }
                deferreds.push(this._preLoad(k));
                j++;
            }
        }
        this.slider.load.showSpinner('backgroundImage' + i);
        return $.when.apply($, deferreds).done($.proxy(function () {
            this.slider.load.removeSpinner('backgroundImage' + i);
        }, this));
    };

    NextendSmartSliderBackgroundImages.prototype._preLoad = function (i) {
        if (this.backgroundImages[i]) {
            return this.backgroundImages[i].preLoad();
        } else {
            return true
        }
    };

    NextendSmartSliderBackgroundImages.prototype.hack = function () {
        for (var i = 0; i < this.backgroundImages.length; i++) {
            if (this.backgroundImages[i]) {
                this.backgroundImages[i].hack();
            }
        }
    };

    scope.NextendSmartSliderBackgroundImages = NextendSmartSliderBackgroundImages;

    function NextendSmartSliderBackgroundImage(i, element, manager) {
        this.responsiveElement = false;
        this.loadStarted = false;

        this.i = i;
        this.element = element;
        this.manager = manager;
        this.loadDeferred = $.Deferred();

        var image = element.find('.n2-ss-slide-background-image');
        this.image = image;
        if (image.hasClass('n2-ss-slide-simple')) {
            this.mode = 'simple';
            this.currentSrc = image.attr('src');
        } else if (image.hasClass('n2-ss-slide-fill')) {
            this.mode = 'fill';
            this.currentSrc = image.attr('src');
        } else if (image.hasClass('n2-ss-slide-fit')) {
            this.mode = 'fit';
            this.currentSrc = image.attr('src');
        } else if (image.hasClass('n2-ss-slide-stretch')) {
            this.mode = 'stretch';
            this.currentSrc = image.attr('src');
        } else if (image.hasClass('n2-ss-slide-center')) {
            this.mode = 'center';
            var matches = image.css('backgroundImage').match(/url\(["]*([^)"]+)["]*\)/i);
            if (matches.length > 0) {
                this.currentSrc = matches[1];
            }
        } else if (image.hasClass('n2-ss-slide-tile')) {
            this.mode = 'tile';
            var matches = image.css('backgroundImage').match(/url\(["]*([^)"]+)["]*\)/i);
            if (matches.length > 0) {
                this.currentSrc = matches[1];
            }
        } else {
            this.mode = 'fill';
            this.currentSrc = '';
        }

        this.hash = element.data('hash');
        this.desktopSrc = element.data('desktop');
        this.tabletSrc = element.data('tablet');
        this.mobileSrc = element.data('mobile');
        var opacity = element.data('opacity');
        if (opacity >= 0 && opacity < 1) {
            this.opacity = opacity;
        }

        if (manager.slider.isAdmin) {
            this._change = this.change;
            this.change = this.changeAdmin;
        }

        this.listenImageManager();

    };

    NextendSmartSliderBackgroundImage.prototype.fixNatural = function (DOMelement) {
        var img = new Image();
        img.src = DOMelement.src;
        DOMelement.naturalWidth = img.width;
        DOMelement.naturalHeight = img.height;
    };

    NextendSmartSliderBackgroundImage.prototype.preLoad = function () {
        if (this.loadDeferred.state() == 'pending') {
            this.loadStarted = true;
            this.manager.deviceDeferred.done($.proxy(function () {
                this.onSlideDeviceChanged(this.manager.device);
                this.element.imagesLoaded($.proxy(function () {
                    this.isLoaded = true;
                    var imageNode = this.image[0];
                    if (imageNode.tagName == 'IMG' && typeof imageNode.naturalWidth === 'undefined') {
                        this.fixNatural(imageNode);
                    }
                    this.loadDeferred.resolve(this.element);
                }, this));
            }, this));
        }
        return this.loadDeferred;
    };

    NextendSmartSliderBackgroundImage.prototype.afterLoaded = function () {
        return $.when(this.loadDeferred, this.manager.slider.responsive.ready);
    };

    NextendSmartSliderBackgroundImage.prototype.onSlideDeviceChanged = function (device) {
        var newSrc = this.desktopSrc;
        if (device.device == 'mobile') {
            if (this.mobileSrc) {
                newSrc = this.mobileSrc;
            } else if (this.tabletSrc) {
                newSrc = this.tabletSrc;
            }
        } else if (device.device == 'tablet') {
            if (this.tabletSrc) {
                newSrc = this.tabletSrc;
            }
        }
        this.change(newSrc, '', this.mode);
    };

    /**
     * @param {NextendSmartSliderResponsiveElementBackgroundImage} responsiveElement
     */
    NextendSmartSliderBackgroundImage.prototype.addResponsiveElement = function (responsiveElement) {
        this.responsiveElement = responsiveElement;
    };

    NextendSmartSliderBackgroundImage.prototype.listenImageManager = function () {
        if (this.hash != '') {
            $(window).on(this.hash, $.proxy(this.onImageManagerChanged, this));
        }
    };

    NextendSmartSliderBackgroundImage.prototype.notListenImageManager = function () {
        if (this.hash != '') {
            $(window).off(this.hash, null, $.proxy(this.onImageManagerChanged, this));
        }
    };

    NextendSmartSliderBackgroundImage.prototype.onImageManagerChanged = function (e, imageData) {
        this.tabletSrc = imageData.tablet.image;
        this.mobileSrc = imageData.mobile.image;
        if (this.manager.device.device == 'tablet' || this.manager.device.device == 'mobile') {
            this.onSlideDeviceChanged(this.manager.device);
        }
    };

    NextendSmartSliderBackgroundImage.prototype.changeDesktop = function (src, alt, newMode) {
        this.notListenImageManager();
        this.desktopSrc = src;
        this.hash = md5(src);
        this.change(src, alt, newMode);

        if (src != '') {
            var img = new Image();
            img.addEventListener("load", $.proxy(function () {
                $.when(nextend.imageManager.getVisual(src))
                    .done($.proxy(function (visual) {
                        this.onImageManagerChanged(null, visual.value);
                        this.listenImageManager();
                    }, this));
            }, this), false);
            img.src = nextend.imageHelper.fixed(src);
        } else {
            this.tabletSrc = '';
            this.mobileSrc = '';
        }
    };

    NextendSmartSliderBackgroundImage.prototype.changeAdmin = function (src, alt, newMode) {
        if (this.manager.slider.parameters.dynamicHeight) {
            newMode = 'simple';
        }
        this._change(nextend.imageHelper.fixed(src), alt, newMode);
    };

    NextendSmartSliderBackgroundImage.prototype.change = function (src, alt, newMode) {
        if (this.currentSrc != src || this.mode != newMode) {
            if (this.loadStarted) {
                n2c.log('Slide background changed: ', src);
                var node = null;
                switch (newMode) {
                    case 'simple':
                        node = $('<img src="' + src + '" class="n2-ss-slide-background-image n2-ss-slide-simple" />');
                        break;
                    case 'fill':
                        node = $('<img src="' + src + '" class="n2-ss-slide-background-image n2-ss-slide-fill" />');
                        this.responsiveElement.setCentered();
                        break;
                    case 'fit':
                        node = $('<img src="' + src + '" class="n2-ss-slide-background-image n2-ss-slide-fit" />');
                        this.responsiveElement.setCentered();
                        break;
                    case 'stretch':
                        node = $('<img src="' + src + '" class="n2-ss-slide-background-image n2-ss-slide-stretch" />');
                        this.responsiveElement.unsetCentered();
                        break;
                    case 'center':
                        node = $('<div style="background-image: url(\'' + src + '\');" class="n2-ss-slide-background-image n2-ss-slide-center"></div>');
                        this.responsiveElement.unsetCentered();
                        break;
                    case 'tile':
                        node = $('<div style="background-image: url(\'' + src + '\');" class="n2-ss-slide-background-image n2-ss-slide-tile"></div>');
                        this.responsiveElement.unsetCentered();
                        break;
                }
                if (src == '') {
                    node.css('display', 'none');
                }
                node.css('opacity', this.opacity);
                this.image
                    .replaceWith(node)
                    .remove();
                this.responsiveElement.element = this.image = node;
                this.currentSrc = src;
                this.mode = newMode;

                if (this.loadDeferred.state() == 'pending') {
                    this.loadDeferred.resolve();
                }
                this.loadDeferred = $.Deferred();
                this.manager.changed(this.i);

                switch (newMode) {
                    case 'fill':
                    case 'fit':
                        this.afterLoaded().done($.proxy(function () {
                            this.responsiveElement.afterLoaded();
                            this.responsiveElement.refreshRatio();
                            this.responsiveElement._refreshResize();
                        }, this));
                        break;
                    case 'stretch':
                    case 'center':
                    case 'tile':
                    case 'simple':
                        this.responsiveElement._refreshResize();
                        break;
                }
            }
        }
    };

    NextendSmartSliderBackgroundImage.prototype.setOpacity = function (opacity) {
        this.opacity = opacity;
        this.image.css('opacity', opacity);
    };

    NextendSmartSliderBackgroundImage.prototype.hack = function () {
        NextendTween.set(this.element, {
            rotation: 0.0001
        });
    };

    scope.NextendSmartSliderBackgroundImage = NextendSmartSliderBackgroundImage;

})(n2, window);
(function ($, scope, undefined) {

    function NextendSmartSliderLoad(smartSlider, parameters) {
        this.smartSlider = smartSlider;
        this.spinnerKey = '';

        this.id = smartSlider.sliderElement.attr('id');

        this.parameters = $.extend({
            fade: 1,
            scroll: 0,
            spinner: ''
        }, parameters);

        this.spinner = $(this.parameters.spinner);

        this.deferred = $.Deferred();
    };


    NextendSmartSliderLoad.prototype.start = function () {
        if (this.parameters.scroll) {

            var $window = $(window);
            $window.on('scroll.' + this.id, $.proxy(this.onScroll, this));
            this.onScroll();

        } else if (this.parameters.fade) {
            this.loadingArea = $('#' + this.id + '-placeholder').eq(0);
            this.showSpinner('fadePlaceholder');
            n2c.log('Fade on load - start wait');

            $.when(this.smartSlider.responsive.ready, this.smartSlider.backgroundImages.load).done($.proxy(this.showSlider, this));

        } else {
            this.smartSlider.responsive.ready.done($.proxy(function () {
                this.showSlider();
            }, this));
        }
    };

    NextendSmartSliderLoad.prototype.onScroll = function () {
        var $window = $(window);
        if (($window.scrollTop() + $window.height() > (this.smartSlider.sliderElement.offset().top + 100))) {

            n2c.log('Fade on scroll - reached');

            $.when(this.smartSlider.responsive.ready, this.smartSlider.backgroundImages.load).done($.proxy(this.showSlider, this));
            $window.off('scroll.' + this.id);
        }
    };

    NextendSmartSliderLoad.prototype.showSlider = function () {
        n2c.log('Images loaded');

        $.when.apply($, this.smartSlider.widgetDeferreds).done($.proxy(function () {
            n2c.log('Event: BeforeVisible');
            this.smartSlider.responsive.doResize();
            this.smartSlider.sliderElement.trigger('BeforeVisible');

            n2c.log('Fade start');
            this.smartSlider.sliderElement.addClass('n2-ss-loaded');

            this.removeSpinner('fadePlaceholder');
            $('#' + this.id + '-placeholder').remove();
            this.loadingArea = this.smartSlider.sliderElement;

            this.deferred.resolve();
        }, this));
    };

    NextendSmartSliderLoad.prototype.loaded = function (fn) {
        this.deferred.done(fn);
    },

        NextendSmartSliderLoad.prototype.showSpinner = function (spinnerKey) {
            this.spinnerKey = spinnerKey;
            this.spinner.appendTo(this.loadingArea);
        };

    NextendSmartSliderLoad.prototype.removeSpinner = function (spinnerKey) {
        if (this.spinnerKey == spinnerKey) {
            this.spinner.detach();
            this.spinnerKey = '';
        }
    };

    scope.NextendSmartSliderLoad = NextendSmartSliderLoad;

})(n2, window);
(function ($, scope, undefined) {
    function NextendSmartSlider() {
        this.sliders = {};
        this.readys = {};

        this._resetCounters = [];
    }

    NextendSmartSlider.prototype.makeReady = function (id, slider) {
        this.sliders[id] = slider;
        if (typeof this.readys[id] !== 'undefined') {
            for (var i = 0; i < this.readys[id].length; i++) {
                this.readys[id][i].call(slider, slider, slider.sliderElement);
            }
        }
    };

    NextendSmartSlider.prototype.ready = function (id, callback) {
        if (typeof this.sliders[id] !== 'undefined') {
            callback.call(this.sliders[id], this.sliders[id], this.sliders[id].sliderElement);
        } else {
            if (typeof this.readys[id] == 'undefined') {
                this.readys[id] = [];
            }
            this.readys[id].push(callback);
        }
    };

    NextendSmartSlider.prototype.trigger = function (el, event) {
        var $el = n2(el),
            split = event.split(','),
            slide = $el.closest('.n2-ss-slide,.n2-ss-static-slide');

        if (split.length > 1) {
            if ($.inArray(el, this._resetCounters) == -1) {
                this._resetCounters.push(el);

                slide.on('layerAnimationSetStart.resetCounter', function () {
                    $el.data('eventCounter', 0);
                });
            }
            var counter = $el.data('eventCounter') || 0
            event = split[counter];
            counter++;
            if (counter > split.length - 1) {
                counter = 0;
            }
            $el.data('eventCounter', counter);
        }
        slide.triggerHandler(event);
    };

    NextendSmartSlider.prototype.applyAction = function (el, action) {
        var ss = n2(el).closest('.n2-ss-slider').data('ss');
        ss[action].apply(ss, Array.prototype.slice.call(arguments, 2));
    };

    window.n2ss = new NextendSmartSlider();
})(n2, window);
(function ($, scope, undefined) {

    function NextendSmartSliderAbstract(sliderElement, parameters) {
        this.startedDeferred = $.Deferred();

        var id = sliderElement.attr('id');
        if (window[id] && window[id] instanceof NextendSmartSliderAbstract) {
            return false;
        }
        // Register our object to a global variable
        window[id] = this;

        // Store them as we might need to change them back
        this.nextCarousel = this.next;
        this.previousCarousel = this.previous;

        if (sliderElement.prop('tagName') == 'SCRIPT') {
            var dependency = sliderElement.data('dependency'),
                rocketLoad = $.proxy(function () {
                    var rocketSlider = $(sliderElement.html().replace(/<_s_c_r_i_p_t/g, '<script').replace(/<_\/_s_c_r_i_p_t/g, '</script'));
                    sliderElement.replaceWith(rocketSlider);
                    this.postInit(id, $('#' + id), parameters);
                    $(window).triggerHandler('n2Rocket', [this.sliderElement]);
                }, this);
            if ($('#n2-ss-' + dependency).length) {
                n2ss.ready(dependency, $.proxy(function (slider) {
                    slider.ready(rocketLoad);
                }, this));
            } else {
                rocketLoad();
            }
        } else {
            this.postInit(id, sliderElement, parameters);
        }
    }

    NextendSmartSliderAbstract.prototype.postInit = function (id, sliderElement, parameters) {
        this.killed = false;
        this.isAdmin = false;
        this.currentSlideIndex = 0;
        this.responsive = false;
        this.layerMode = true;
        this._lastChangeTime = 0;
        n2c.log('Slider init: ', id);
        this.id = parseInt(id.replace('n2-ss-', ''));

        this.sliderElement = sliderElement.data('ss', this);
        this.readyDeferred = $.Deferred();

        this.parameters = $.extend({
            admin: false,
            playWhenVisible: 1,
            isStaticEdited: false,
            callbacks: '',
            autoplay: {},
            blockrightclick: false,
            maintainSession: 0,
            controls: {
                drag: false,
                touch: 'horizontal',
                keyboard: false,
                scroll: false,
                tilt: false
            },
            hardwareAcceleration: true,
            layerMode: {
                playOnce: 0,
                playFirstLayer: 1,
                mode: 'skippable',
                inAnimation: 'mainInEnd'
            },
            parallax: {
                enabled: 0,
                mobile: 0,
                horizontal: 'mouse',
                vertical: 'mouse',
                origin: 'enter'
            },
            load: {},
            mainanimation: {},
            randomize: {},
            responsive: {},
            lazyload: {
                enabled: 0
            },
            postBackgroundAnimations: false,
            initCallbacks: [],
            dynamicHeight: 0
        }, parameters);

        try {
            eval(this.parameters.callbacks);
        } catch (e) {
            console.error(e);
        }

        this.startVisibilityCheck();
        n2ss.makeReady(this.id, this);


        this.widgetDeferreds = [];
        this.sliderElement.on('addWidget', $.proxy(this.addWidget, this));

        this.isAdmin = !!this.parameters.admin;
        if (this.isAdmin) {
            this.changeTo = function () {
            };
        }

        this.load = new NextendSmartSliderLoad(this, this.parameters.load);

        this.findSlides();

        this.currentSlideIndex = this.__getActiveSlideIndex();

        var forceActiveSlideIndex = typeof window['ss' + this.id] !== 'undefined' ? parseInt(window['ss' + this.id]) : null;
        if (forceActiveSlideIndex !== null) {
            this.changeActiveBeforeLoad(forceActiveSlideIndex);
        }

        if (!this.isAdmin && this.parameters.maintainSession && typeof sessionStorage !== 'undefined') {
            var sessionIndex = parseInt(sessionStorage.getItem('ss-' + this.id));
            if (forceActiveSlideIndex === null && sessionIndex !== null) {
                this.changeActiveBeforeLoad(sessionIndex);
            }
            this.sliderElement.on('mainAnimationComplete', $.proxy(function (e, animation, previous, next) {
                sessionStorage.setItem('ss-' + this.id, next);
            }, this));
        }

        this.backgroundImages = new NextendSmartSliderBackgroundImages(this);

        n2c.log('First slide index: ', this.currentSlideIndex);

        for (var i = 0; i < this.parameters.initCallbacks.length; i++) {
            (new Function(this.parameters.initCallbacks[i]))(this);
        }

        this.initSlides();

        this.widgets = new NextendSmartSliderWidgets(this);

        this.sliderElement.on('universalenter', $.proxy(function () {
            this.sliderElement.addClass('n2-hover');
        }, this)).on('universalleave', $.proxy(function (e) {
            e.stopPropagation();
            this.sliderElement.removeClass('n2-hover');
        }, this));


        this.controls = {};

        if (this.layerMode) {
            this.initMainAnimationWithLayerAnimation();
        }

        if (this.parameters.blockrightclick) {
            this.sliderElement.bind("contextmenu", function (e) {
                e.preventDefault();
            });
        }

        this.initMainAnimation();
        this.initResponsiveMode();

        if (this.killed) {
            return;
        }

        this.initControls();

        this.startedDeferred.resolve(this);

        if (!this.isAdmin) {
            var event = 'click';
            if (this.parameters.controls.touch != '0' && this.parameters.controls.touch) {
                event = 'n2click';
            }
            this.sliderElement.find('[n2click]').each(function (i, el) {
                var el = $(el);
                el.on(event, function () {
                    eval(el.attr('n2click'));
                });
            });

            this.sliderElement.find('[data-click]').each(function (i, el) {
                var el = $(el).on('click', function () {
                    eval(el.data('click'));
                }).css('cursor', 'pointer');
            });

            this.sliderElement.find('[data-mouseenter]').each(function (i, el) {
                var el = $(el).on('mouseenter', function () {
                    eval(el.data('mouseenter'));
                });
            });

            this.sliderElement.find('[data-mouseleave]').each(function (i, el) {
                var el = $(el).on('mouseleave', function () {
                    eval(el.data('mouseleave'));
                });
            });

            this.sliderElement.find('[data-play]').each(function (i, el) {
                var el = $(el).on('n2play', function () {
                    eval(el.data('play'));
                });
            });

            this.sliderElement.find('[data-pause]').each(function (i, el) {
                var el = $(el).on('n2pause', function () {
                    eval(el.data('pause'));
                });
            });

            this.sliderElement.find('[data-stop]').each(function (i, el) {
                var el = $(el).on('n2stop', function () {
                    eval(el.data('stop'));
                });
            });

            var preventFocus = false;
            this.slides.find('a').on('mousedown', function (e) {
                preventFocus = true;
                setTimeout(function () {
                    preventFocus = false;
                }, 100);
            });

            this.slides.find('a').on('focus', $.proxy(function (e) {
                if (!preventFocus) {
                    var slideIndex = this.findSlideIndexByElement(e.currentTarget);
                    if (slideIndex != -1 && slideIndex != this.currentSlideIndex) {
                        this.changeTo(slideIndex, false, false);
                    }
                }
            }, this));
        }

        this.preReadyResolve();

        this.initCarousel();
    };

    NextendSmartSliderAbstract.prototype.initSlides = function () {
        if (this.layerMode) {
            if (this.isAdmin && this.type != 'showcase') {
                new NextendSmartSliderSlide(this, this.slides.eq(this.currentSlideIndex), 1);
            } else {
                for (var i = 0; i < this.slides.length; i++) {
                    new NextendSmartSliderSlide(this, this.slides.eq(i), this.currentSlideIndex == i);
                }
            }

            var staticSlide = this.findStaticSlide();
            if (staticSlide.length) {
                new NextendSmartSliderSlide(this, staticSlide, true, true);
            }
        }
    };

    NextendSmartSliderAbstract.prototype.getRealIndex = function (index) {
        return index;
    };

    NextendSmartSliderAbstract.prototype.changeActiveBeforeLoad = function (index) {
        if (index > 0 && index < this.slides.length && this.currentSlideIndex != index) {
            this.unsetActiveSlide(this.slides.eq(this.currentSlideIndex));
            this.setActiveSlide(this.slides.eq(index));
            this.currentSlideIndex = index;
            this.ready($.proxy(function () {
                this.sliderElement.trigger('sliderSwitchTo', [index, this.getRealIndex(index)]);
            }, this));
        }
    };

    NextendSmartSliderAbstract.prototype.kill = function () {
        this.killed = true;
        $('#' + this.sliderElement.attr('id') + '-placeholder').remove();
        this.sliderElement.closest('.n2-ss-align').remove();
    };

    NextendSmartSliderAbstract.prototype.findSlides = function () {

        this.realSlides = this.slides = this.sliderElement.find('.n2-ss-slide');
    };

    NextendSmartSliderAbstract.prototype.findStaticSlide = function () {
        return this.sliderElement.find('.n2-ss-static-slide');
    };

    NextendSmartSliderAbstract.prototype.addWidget = function (e, deferred) {
        this.widgetDeferreds.push(deferred);
    };

    NextendSmartSliderAbstract.prototype.started = function (fn) {
        this.startedDeferred.done($.proxy(fn, this));
    };

    NextendSmartSliderAbstract.prototype.preReadyResolve = function () {
        // Hack to allow time to widgets to register
        setTimeout($.proxy(this._preReadyResolve, this), 1);
    };

    NextendSmartSliderAbstract.prototype._preReadyResolve = function () {

        this.load.start();
        this.load.loaded($.proxy(this.readyResolve, this));
    };

    NextendSmartSliderAbstract.prototype.readyResolve = function () {
        n2c.log('Slider ready');
        $(window).scroll(); // To force other sliders to recalculate the scroll position

        this.readyDeferred.resolve();
    };

    NextendSmartSliderAbstract.prototype.ready = function (fn) {
        this.readyDeferred.done($.proxy(fn, this));
    };

    NextendSmartSliderAbstract.prototype.startVisibilityCheck = function () {
        this.visibleDeferred = $.Deferred();
        if (this.parameters.playWhenVisible) {
            this.ready($.proxy(function () {
                $(window).on('scroll.n2-ss-visible' + this.id + ' resize.n2-ss-visible' + this.id, $.proxy(this.checkIfVisible, this));
                this.checkIfVisible();
            }, this));
        } else {
            this.ready($.proxy(function () {
                this.visibleDeferred.resolve();
            }, this));
        }
    };

    NextendSmartSliderAbstract.prototype.checkIfVisible = function () {
        var TopView = $(window).scrollTop(),
            BotView = TopView + $(window).height(),
            middlePoint = this.sliderElement.offset().top + this.sliderElement.height() / 2;
        if (TopView <= middlePoint && BotView >= middlePoint) {
            $(window).off('scroll.n2-ss-visible' + this.id + ' resize.n2-ss-visible' + this.id, $.proxy(this.checkIfVisible, this));
            this.visibleDeferred.resolve();
        }
    };

    NextendSmartSliderAbstract.prototype.visible = function (fn) {
        this.visibleDeferred.done($.proxy(fn, this));
    };

    NextendSmartSliderAbstract.prototype.isPlaying = function () {
        if (this.mainAnimation.getState() != 'ended') {
            return true;
        }
        return false;
    };

    NextendSmartSliderAbstract.prototype.focus = function (isSystem) {
        var deferred = $.Deferred();
        if (typeof isSystem == 'undefined') {
            isSystem = 0;
        }
        if (this.responsive.parameters.focusUser && !isSystem || this.responsive.parameters.focusAutoplay && isSystem) {
            var top = this.sliderElement.offset().top - this.responsive.verticalOffsetSelectors.height();
            if ($(window).scrollTop() != top) {
                $("html, body").animate({scrollTop: top}, 400, $.proxy(function () {
                    deferred.resolve();
                }, this));
            } else {
                deferred.resolve();
            }
        } else {
            deferred.resolve();
        }
        return deferred;
    };

    NextendSmartSliderAbstract.prototype.initCarousel = function () {
        if (!parseInt(this.parameters.carousel)) {
            // Replace the methods
            this.next = this.nextNotCarousel;
            this.previous = this.previousNotCarousel;

            var slides = this.slides.length;
            var previousArrowOpacity = 1,
                previousArrow = this.sliderElement.find('.nextend-arrow-previous'),
                previous = function (opacity) {
                    if (opacity != previousArrowOpacity) {
                        NextendTween.to(previousArrow, 0.4, {opacity: opacity}).play();
                        previousArrowOpacity = opacity;
                    }
                };
            var nextArrowOpacity = 1,
                nextArrow = this.sliderElement.find('.nextend-arrow-next'),
                next = function (opacity) {
                    if (opacity != nextArrowOpacity) {
                        NextendTween.to(nextArrow, 0.4, {opacity: opacity}).play();
                        nextArrowOpacity = opacity;
                    }
                };

            var process = function (i) {
                if (i == 0) {
                    previous(0);
                } else {
                    previous(1);
                }
                if (i == slides - 1) {
                    next(0);
                } else {
                    next(1);
                }
            };

            process(this.__getActiveSlideIndex())

            this.sliderElement.on('sliderSwitchTo', function (e, i) {
                process(i);
            });
        }
    };

    NextendSmartSliderAbstract.prototype.next = function (isSystem, customAnimation) {
        var nextIndex = this.currentSlideIndex + 1;
        if (nextIndex >= this.slides.length) {
            nextIndex = 0;
        }
        return this.changeTo(nextIndex, false, isSystem, customAnimation);
    };

    NextendSmartSliderAbstract.prototype.previous = function (isSystem, customAnimation) {
        var nextIndex = this.currentSlideIndex - 1;
        if (nextIndex < 0) {
            nextIndex = this.slides.length - 1;
        }
        return this.changeTo(nextIndex, true, isSystem, customAnimation);
    };

    NextendSmartSliderAbstract.prototype.nextNotCarousel = function (isSystem, customAnimation) {
        var nextIndex = this.currentSlideIndex + 1;
        if (nextIndex < this.slides.length) {
            return this.changeTo(nextIndex, false, isSystem, customAnimation);
        }
        return false;
    };

    NextendSmartSliderAbstract.prototype.previousNotCarousel = function (isSystem, customAnimation) {
        var nextIndex = this.currentSlideIndex - 1;
        if (nextIndex >= 0) {
            return this.changeTo(nextIndex, true, isSystem, customAnimation);
        }
        return false;
    };

    NextendSmartSliderAbstract.prototype.directionalChangeToReal = function (nextSlideIndex) {
        this.directionalChangeTo(nextSlideIndex);
    };

    NextendSmartSliderAbstract.prototype.directionalChangeTo = function (nextSlideIndex) {
        if (nextSlideIndex > this.currentSlideIndex) {
            this.changeTo(nextSlideIndex, false);
        } else {
            this.changeTo(nextSlideIndex, true);
        }
    };

    NextendSmartSliderAbstract.prototype.changeTo = function (nextSlideIndex, reversed, isSystem, customAnimation) {
        nextSlideIndex = parseInt(nextSlideIndex);

        if (nextSlideIndex != this.currentSlideIndex) {
            n2c.log('Event: sliderSwitchTo: ', 'targetSlideIndex');
            this.sliderElement.trigger('sliderSwitchTo', [nextSlideIndex, this.getRealIndex(nextSlideIndex)]);
            var time = $.now();
            $.when(this.backgroundImages.preLoad(nextSlideIndex), this.focus(isSystem)).done($.proxy(function () {

                if (this._lastChangeTime <= time) {
                    this._lastChangeTime = time;
                    // If the current main animation haven't finished yet or the prefered next slide is the same as our current slide we have nothing to do
                    var state = this.mainAnimation.getState();
                    if (state == 'ended') {

                        if (typeof isSystem === 'undefined') {
                            isSystem = false;
                        }

                        var animation = this.mainAnimation;
                        if (typeof customAnimation !== 'undefined') {
                            animation = customAnimation;
                        }

                        this._changeTo(nextSlideIndex, reversed, isSystem, customAnimation);

                        n2c.log('Change From:', this.currentSlideIndex, ' To: ', nextSlideIndex, ' Reversed: ', reversed, ' System: ', isSystem);
                        animation.changeTo(this.currentSlideIndex, this.slides.eq(this.currentSlideIndex), nextSlideIndex, this.slides.eq(nextSlideIndex), reversed, isSystem);

                        this.currentSlideIndex = nextSlideIndex;

                    } else if (state == 'playing') {
                        this.sliderElement.off('.fastChange').one('mainAnimationComplete.fastChange', $.proxy(function () {
                            this.changeTo.call(this, nextSlideIndex, reversed, isSystem, customAnimation);
                        }, this));
                        this.mainAnimation.timeScale(this.mainAnimation.timeScale() * 2);
                    }
                }
            }, this));
            return true;
        }
        return false;
    };

    NextendSmartSliderAbstract.prototype._changeTo = function (nextSlideIndex, reversed, isSystem, customAnimation) {

    };

    NextendSmartSliderAbstract.prototype.__getActiveSlideIndex = function () {
        var index = this.slides.index(this.slides.filter('.n2-ss-slide-active'));
        if (index === -1) {
            index = 0;
        }
        return index;
    };

    NextendSmartSliderAbstract.prototype.setActiveSlide = function (slide) {
        slide.addClass('n2-ss-slide-active');
    };

    NextendSmartSliderAbstract.prototype.unsetActiveSlide = function (slide) {
        slide.removeClass('n2-ss-slide-active');
    };

    NextendSmartSliderAbstract.prototype.initMainAnimationWithLayerAnimation = function () {

        if (this.parameters.layerMode.mode == 'forced') {
            this.sliderElement.on('preChangeToPlay', $.proxy(function (e, deferred, deferredHandled, currentSlide, nextSlide) {
                deferredHandled.handled = true;
                currentSlide.on('layerAnimationCompleteOut.layers', function () {
                    currentSlide.off('layerAnimationCompleteOut.layers');
                    deferred.resolve();
                });
                this.callOnSlide(currentSlide, 'playOut');
            }, this));
        }
        this.sliderElement.on('mainAnimationStart', $.proxy(this.onMainAnimationStartSyncLayers, this, this.parameters.layerMode));

    };

    NextendSmartSliderAbstract.prototype.onMainAnimationStartSyncLayers = function (layerMode, e, animation, previousSlideIndex, currentSlideIndex) {
        var inSlide = this.slides.eq(currentSlideIndex),
            outSlide = this.slides.eq(previousSlideIndex);
        if (layerMode.inAnimation == 'mainInStart') {
            inSlide.on('mainAnimationStartIn.layers', $.proxy(function () {
                inSlide.off('mainAnimationStartIn.layers');
                this.callOnSlide(inSlide, 'playIn');
            }, this));
        } else if (layerMode.inAnimation == 'mainInEnd') {
            inSlide.on('mainAnimationCompleteIn.layers', $.proxy(function () {
                inSlide.off('mainAnimationCompleteIn.layers');
                this.callOnSlide(inSlide, 'playIn');
            }, this));
        }

        if (layerMode.mode == 'skippable') {
            outSlide.on('mainAnimationCompleteOut.layers', $.proxy(function () {
                outSlide.off('mainAnimationCompleteOut.layers');
                if (layerMode.playOnce) {
                    this.callOnSlide(outSlide, 'pause');
                } else {
                    this.callOnSlide(outSlide, 'reset');
                }
            }, this));
        }
    };

    NextendSmartSliderAbstract.prototype.callOnSlide = function (slide, functionName) {
        slide.data('slide')[functionName]();
    };

    NextendSmartSliderAbstract.prototype.findSlideIndexByElement = function (element) {
        element = $(element);
        for (var i = 0; i < this.slides.length; i++) {
            if (this.slides.eq(i).has(element).length === 1) {
                return i;
            }
        }
        return -1;
    };

    NextendSmartSliderAbstract.prototype.initMainAnimation = function () {
    };

    NextendSmartSliderAbstract.prototype.initResponsiveMode = function () {
        new scope[this.responsiveClass](this, this.parameters.responsive);
        this.dimensions = this.responsive.responsiveDimensions;
    };

    NextendSmartSliderAbstract.prototype.initControls = function () {

        if (!this.parameters.admin) {
            if (this.parameters.controls.touch != '0') {
                new NextendSmartSliderControlTouch(this, this.parameters.controls.touch, {
                    fallbackToMouseEvents: this.parameters.controls.drag
                });
            }

            if (this.parameters.controls.keyboard) {
                new NextendSmartSliderControlKeyboard(this, 'horizontal');
            }

            if (this.parameters.controls.scroll) {
                new NextendSmartSliderControlScroll(this);
            }

            if (this.parameters.controls.tilt) {
                new NextendSmartSliderControlTilt(this);
            }

            new NextendSmartSliderControlAutoplay(this, this.parameters.autoplay);

        }
    };

    NextendSmartSliderAbstract.prototype.slideToID = function (id) {
        var index = this.slides.index(this.slides.filter('[data-id="' + id + '"]'));
        return this.slide(index);
    };

    NextendSmartSliderAbstract.prototype.slide = function (index) {
        if (index >= 0 && index < this.slides.length) {
            return this.changeTo(index);
        }
        return false;
    };

    NextendSmartSliderAbstract.prototype.adminGetCurrentSlideElement = function () {

        if (this.parameters.isStaticEdited) {
            return this.findStaticSlide();
        }
        return this.slides.eq(this.currentSlideIndex);
    };

    scope.NextendSmartSliderAbstract = NextendSmartSliderAbstract;

})(n2, window);
(function ($, scope, undefined) {

    function NextendSmartSliderWidgets(slider) {
        this.slider = slider;
        this.sliderElement = slider.sliderElement.on('BeforeVisible', $.proxy(this.onReady, this));

        this.initExcludeSlides();
    }

    NextendSmartSliderWidgets.prototype.onReady = function () {
        this.dimensions = this.slider.dimensions;

        this.widgets = {
            previous: this.sliderElement.find('.nextend-arrow-previous'),
            next: this.sliderElement.find('.nextend-arrow-next'),
            bullet: this.sliderElement.find('.nextend-bullet-bar'),
            autoplay: this.sliderElement.find('.nextend-autoplay'),
            indicator: this.sliderElement.find('.nextend-indicator'),
            bar: this.sliderElement.find('.nextend-bar'),
            thumbnail: this.sliderElement.find('.nextend-thumbnail'),
            shadow: this.sliderElement.find('.nextend-shadow'),
            fullscreen: this.sliderElement.find('.nextend-fullscreen'),
            html: this.sliderElement.find('.nextend-widget-html')
        };

        this.variableElementsDimension = {
            width: this.sliderElement.find('[data-sswidth]'),
            height: this.sliderElement.find('[data-ssheight]')
        };

        this.variableElements = {
            top: this.sliderElement.find('[data-sstop]'),
            right: this.sliderElement.find('[data-ssright]'),
            bottom: this.sliderElement.find('[data-ssbottom]'),
            left: this.sliderElement.find('[data-ssleft]')
        };

        this.slider.sliderElement.on('SliderAnimatedResize', $.proxy(this.onAnimatedResize, this));
        this.slider.sliderElement.on('SliderResize', $.proxy(this.onResize, this));
        this.slider.sliderElement.one('slideCountChanged', $.proxy(function () {
            this.onResize(this.slider.responsive.lastRatios);
        }, this));

        //this.slider.ready($.proxy(function () {
        this.onResize(this.slider.responsive.lastRatios);
        //}, this));
        this.initHover();
    };

    NextendSmartSliderWidgets.prototype.initHover = function () {
        var timeout = null,
            widgets = this.sliderElement.find('.n2-ss-widget-hover');
        if (widgets.length > 0) {
            this.sliderElement.on('universalenter', function (e) {
                var slider = $(this);
                if (timeout) clearTimeout(timeout);
                widgets.css('visibility', 'visible');
                setTimeout(function () {
                    slider.addClass('n2-ss-widget-hover-show');
                }, 50);
            }).on('universalleave', function () {
                var slide = this;
                if (timeout) clearTimeout(timeout);
                timeout = setTimeout(function () {
                    $(slide).removeClass('n2-ss-widget-hover-show');
                    timeout = setTimeout(function () {
                        widgets.css('visibility', 'hidden');
                    }, 400);
                }, 500);
            });
        }
    };

    NextendSmartSliderWidgets.prototype.initExcludeSlides = function () {
        var widgets = this.sliderElement.find('.n2-ss-widget[data-exclude-slides]'),
            hideOrShow = function (widget, excludedSlides, currentSlideIndex) {
                if ($.inArray((currentSlideIndex + 1) + '', excludedSlides) != -1) {
                    widget.addClass('n2-ss-widget-hidden');
                } else {
                    widget.removeClass('n2-ss-widget-hidden');
                }
            };
        widgets.each($.proxy(function (i, el) {
            var widget = $(el),
                excludedSlides = widget.attr('data-exclude-slides').split(',');
            for (var i = excludedSlides.length - 1; i >= 0; i--) {
                var parts = excludedSlides[i].split('-');
                if (parts.length == 2 && parseInt(parts[0]) <= parseInt(parts[1])) {
                    excludedSlides[i] = parts[0];
                    parts[0] = parseInt(parts[0]);
                    parts[1] = parseInt(parts[1]);
                    for (var j = parts[0] + 1; j <= parts[1]; j++) {
                        excludedSlides.push(j + '');
                    }
                }
            }
            hideOrShow(widget, excludedSlides, this.slider.currentSlideIndex);
            this.slider.sliderElement
                .on('sliderSwitchTo', function (e, targetSlideIndex) {
                    hideOrShow(widget, excludedSlides, targetSlideIndex);
                });
        }, this));
    };

    NextendSmartSliderWidgets.prototype.onAnimatedResize = function (e, ratios, timeline, duration) {
        for (var key in this.widgets) {
            var el = this.widgets[key],
                visible = el.is(":visible");
            this.dimensions[key + 'width'] = visible ? el.outerWidth(false) : 0;
            this.dimensions[key + 'height'] = visible ? el.outerHeight(false) : 0;
        }

        // Compatibility variables for the old version
        this.dimensions['width'] = this.dimensions.slider.width;
        this.dimensions['height'] = this.dimensions.slider.height;
        this.dimensions['outerwidth'] = this.sliderElement.parent().width();
        this.dimensions['outerheight'] = this.sliderElement.parent().height();
        this.dimensions['canvaswidth'] = this.dimensions.slide.width;
        this.dimensions['canvasheight'] = this.dimensions.slide.height;
        this.dimensions['margintop'] = this.dimensions.slider.marginTop;
        this.dimensions['marginright'] = this.dimensions.slider.marginRight;
        this.dimensions['marginbottom'] = this.dimensions.slider.marginBottom;
        this.dimensions['marginleft'] = this.dimensions.slider.marginLeft;

        var variableText = '';
        for (var key in this.dimensions) {
            var value = this.dimensions[key];
            if (typeof value == "object") {
                for (var key2 in value) {
                    variableText += "var " + key + key2 + " = " + value[key2] + ";";
                }
            } else {
                variableText += "var " + key + " = " + value + ";";
            }
        }
        eval(variableText);

        for (var k in this.variableElementsDimension) {
            for (var i = 0; i < this.variableElementsDimension[k].length; i++) {
                var el = this.variableElementsDimension[k].eq(i);
                if (el.is(':visible')) {
                    var to = {};
                    try {
                        to[k] = eval(el.data('ss' + k)) + 'px';
                        for (var widget in this.widgets) {
                            if (this.widgets[widget].filter(el).length) {
                                if (k == 'width') {
                                    this.dimensions[widget + k] = el.outerWidth(false);
                                } else if (k == 'height') {
                                    this.dimensions[widget + k] = el.outerHeight(false);
                                }
                                eval(widget + k + " = " + this.dimensions[widget + k] + ";");
                            }
                        }
                    } catch (e) {
                        console.log(el, ' position variable: ' + e.message + ': ', el.data('ss' + k));
                    }
                    timeline.to(el, duration, to, 0);
                }
            }
        }

        for (var k in this.variableElements) {
            for (var i = 0; i < this.variableElements[k].length; i++) {
                var el = this.variableElements[k].eq(i);
                try {
                    var to = {};
                    to[k] = eval(el.data('ss' + k)) + 'px';
                    timeline.to(el, duration, to, 0);
                } catch (e) {
                    console.log(el, ' position variable: ' + e.message + ': ', el.data('ss' + k));
                }
            }
        }
    };


    NextendSmartSliderWidgets.prototype.onResize = function (e, ratios, responsive, timeline) {
        if (timeline) {
            return;
        }
        for (var key in this.widgets) {
            var el = this.widgets[key],
                visible = el.is(":visible");
            this.dimensions[key + 'width'] = visible ? el.outerWidth(false) : 0;
            this.dimensions[key + 'height'] = visible ? el.outerHeight(false) : 0;
        }

        // Compatibility variables for the old version
        this.dimensions['width'] = this.dimensions.slider.width;
        this.dimensions['height'] = this.dimensions.slider.height;
        this.dimensions['outerwidth'] = this.sliderElement.parent().width();
        this.dimensions['outerheight'] = this.sliderElement.parent().height();
        this.dimensions['canvaswidth'] = this.dimensions.slide.width;
        this.dimensions['canvasheight'] = this.dimensions.slide.height;
        this.dimensions['margintop'] = this.dimensions.slider.marginTop;
        this.dimensions['marginright'] = this.dimensions.slider.marginRight;
        this.dimensions['marginbottom'] = this.dimensions.slider.marginBottom;
        this.dimensions['marginleft'] = this.dimensions.slider.marginLeft;

        var variableText = '';
        for (var key in this.dimensions) {
            var value = this.dimensions[key];
            if (typeof value == "object") {
                for (var key2 in value) {
                    variableText += "var " + key + key2 + " = " + value[key2] + ";";
                }
            } else {
                variableText += "var " + key + " = " + value + ";";
            }
        }
        eval(variableText);

        for (var k in this.variableElementsDimension) {
            for (var i = 0; i < this.variableElementsDimension[k].length; i++) {
                var el = this.variableElementsDimension[k].eq(i);
                if (el.is(':visible')) {
                    try {
                        el.css(k, eval(el.data('ss' + k)) + 'px');
                        for (var widget in this.widgets) {
                            if (this.widgets[widget].filter(el).length) {
                                if (k == 'width') {
                                    this.dimensions[widget + k] = el.outerWidth(false);
                                } else if (k == 'height') {
                                    this.dimensions[widget + k] = el.outerHeight(false);
                                }
                                eval(widget + k + " = " + this.dimensions[widget + k] + ";");
                            }
                        }
                    } catch (e) {
                        console.log(el, ' position variable: ' + e.message + ': ', el.data('ss' + k));
                    }
                }
            }
        }

        for (var k in this.variableElements) {
            for (var i = 0; i < this.variableElements[k].length; i++) {
                var el = this.variableElements[k].eq(i);
                try {
                    el.css(k, eval(el.data('ss' + k)) + 'px');
                } catch (e) {
                    console.log(el, ' position variable: ' + e.message + ': ', el.data('ss' + k));
                }
            }
        }
    };

    scope.NextendSmartSliderWidgets = NextendSmartSliderWidgets;

})(n2, window);
(function ($, scope, undefined) {
    function NextendSmartSliderBackgroundAnimationAbstract(sliderBackgroundAnimation, currentImage, nextImage, animationProperties, durationMultiplier, reversed) {

        this.durationMultiplier = durationMultiplier;

        this.original = {
            currentImage: currentImage,
            nextImage: nextImage
        };

        this.animationProperties = animationProperties;

        this.reversed = reversed;

        this.timeline = sliderBackgroundAnimation.timeline;

        this.containerElement = sliderBackgroundAnimation.bgAnimationElement;

        this.shiftedBackgroundAnimation = sliderBackgroundAnimation.parameters.shiftedBackgroundAnimation;

        this.clonedImages = {};

    };

    NextendSmartSliderBackgroundAnimationAbstract.prototype.postSetup = function () {
    };

    NextendSmartSliderBackgroundAnimationAbstract.prototype.ended = function () {

    };

    NextendSmartSliderBackgroundAnimationAbstract.prototype.placeNextImage = function () {
        this.clonedImages.nextImage = this.original.nextImage.clone().css({
            position: 'absolute',
            top: 0,
            left: 0
        });

        this.containerElement.append(this.clonedImages.nextImage);
    };

    NextendSmartSliderBackgroundAnimationAbstract.prototype.placeCurrentImage = function () {
        this.clonedImages.currentImage = this.original.currentImage.clone().css({
            position: 'absolute',
            top: 0,
            left: 0
        });

        this.containerElement.append(this.clonedImages.currentImage);
    };

    NextendSmartSliderBackgroundAnimationAbstract.prototype.hideOriginals = function () {
        this.original.currentImage.css('opacity', 0);
        this.original.nextImage.css('opacity', 0);
    };

    NextendSmartSliderBackgroundAnimationAbstract.prototype.resetAll = function () {
        this.original.currentImage.css('opacity', 1);
        this.original.nextImage.css('opacity', 1);
        this.containerElement.html('');
    };

    NextendSmartSliderBackgroundAnimationAbstract.prototype.getExtraDelay = function () {
        return 0;
    };

    scope.NextendSmartSliderBackgroundAnimationAbstract = NextendSmartSliderBackgroundAnimationAbstract;
})(n2, window);

(function ($, scope, undefined) {

    function NextendSmartSliderBackgroundAnimationFluxAbstract() {
        this.shiftedPreSetup = false;
        this._clonedCurrent = false;
        this._clonedNext = false;

        NextendSmartSliderBackgroundAnimationAbstract.prototype.constructor.apply(this, arguments);

        this.w = this.original.currentImage.width();
        this.h = this.original.currentImage.height();
    };

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype = Object.create(NextendSmartSliderBackgroundAnimationAbstract.prototype);
    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.constructor = NextendSmartSliderBackgroundAnimationFluxAbstract;

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.clonedCurrent = function () {
        if (!this._clonedCurrent) {
            this._clonedCurrent = this.original.currentImage
                .clone()
                .css({
                    width: this.w,
                    height: this.h
                });
        }
        return this._clonedCurrent;
    };

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.clonedNext = function () {
        if (!this._clonedNext) {
            this._clonedNext = this.original.nextImage
                .clone()
                .css({
                    width: this.w,
                    height: this.h
                });
        }
        return this._clonedNext;
    };

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.preSetup = function () {
        if (this.shiftedBackgroundAnimation != 0) {
            this.shiftedPreSetup = true;
        } else {
            this._preSetup();
        }
    };

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype._preSetup = function (skipFadeOut) {
        this.timeline.to(this.original.currentImage.get(0), this.getExtraDelay(), {
            opacity: 0
        }, 0);

        this.original.nextImage.css('opacity', 0);
    };

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.postSetup = function () {
        this.timeline.to(this.original.nextImage.get(0), this.getExtraDelay(), {
            opacity: 1
        });
    };

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.getExtraDelay = function () {
        return .2;
    };

    NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.ended = function () {
        this.original.currentImage.css('opacity', 1);
        this.containerElement.html('');
    };

    scope.NextendSmartSliderBackgroundAnimationFluxAbstract = NextendSmartSliderBackgroundAnimationFluxAbstract;


    function NextendSmartSliderBackgroundAnimationTiled() {
        NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.constructor.apply(this, arguments);

        this.setup();
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype = Object.create(NextendSmartSliderBackgroundAnimationFluxAbstract.prototype);
    NextendSmartSliderBackgroundAnimationTiled.prototype.constructor = NextendSmartSliderBackgroundAnimationTiled;

    NextendSmartSliderBackgroundAnimationTiled.prototype.setup = function (animation) {

        var container = $('<div></div>').css({
            position: 'absolute',
            left: 0,
            top: 0,
            width: this.w,
            height: this.h/*,
             overflow: 'hidden'*/
        });
        this.container = container;
        NextendTween.set(container.get(0), {
            force3D: true,
            perspective: 1000
        });

        var animatablesMulti = [],
            animatables = [];

        var columns = animation.columns,
            rows = animation.rows,
            colWidth = Math.floor(this.w / columns),
            rowHeight = Math.floor(this.h / rows);

        var colRemainder = this.w - (columns * colWidth),
            colAddPerLoop = Math.ceil(colRemainder / columns),
            rowRemainder = this.h - (rows * rowHeight),
            rowAddPerLoop = Math.ceil(rowRemainder / rows),
            totalLeft = 0;

        for (var col = 0; col < columns; col++) {
            animatablesMulti[col] = [];
            var thisColWidth = colWidth,
                totalTop = 0;

            if (colRemainder > 0) {
                var add = colRemainder >= colAddPerLoop ? colAddPerLoop : colRemainder;
                thisColWidth += add;
                colRemainder -= add;
            }

            var thisRowRemainder = rowRemainder;

            for (var row = 0; row < rows; row++) {
                var thisRowHeight = rowHeight;

                if (thisRowRemainder > 0) {
                    var add = thisRowRemainder >= rowAddPerLoop ? rowAddPerLoop : thisRowRemainder;
                    thisRowHeight += add;
                    thisRowRemainder -= add;
                }
                var tile = $('<div class="tile tile-' + col + '-' + row + '"></div>').css({
                    position: 'absolute',
                    top: totalTop + 'px',
                    left: totalLeft + 'px',
                    width: thisColWidth + 'px',
                    height: thisRowHeight + 'px',
                    zIndex: -Math.abs(col - parseInt(columns / 2)) + columns - Math.abs(row - parseInt(rows / 2))
                }).appendTo(container);

                var animatable = this.renderTile(tile, thisColWidth, thisRowHeight, animation, totalLeft, totalTop);
                animatables.push(animatable);
                animatablesMulti[col][row] = animatable;

                totalTop += thisRowHeight;
            }
            totalLeft += thisColWidth;
        }

        container.appendTo(this.containerElement);

        this.preSetup();

        this.animate(animation, animatables, animatablesMulti);
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.animate = function (animation, animatables, animatablesMulti) {
        this['sequence' + animation.tiles.sequence]($.proxy(this.transform, this, animation), animatables, animatablesMulti, animation.tiles.delay * this.durationMultiplier);
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceParallel = function (transform, cuboids) {
        transform(cuboids, null);
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceRandom = function (transform, cuboids, cuboidsMulti, delay) {
        var total = this.timeline.totalDuration();
        for (var i = 0; i < cuboids.length; i++) {
            transform(cuboids[i], total + Math.random() * delay);
        }
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceForwardCol = function (transform, cuboids, cuboidsMulti, delay) {
        var total = this.timeline.totalDuration();
        for (var i = 0; i < cuboids.length; i++) {
            transform(cuboids[i], total + delay * i);
        }
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceBackwardCol = function (transform, cuboids, cuboidsMulti, delay) {
        var total = this.timeline.totalDuration(),
            length = cuboids.length - 1;
        for (var i = 0; i < cuboids.length; i++) {
            transform(cuboids[i], total + delay * (length - i));
        }
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceForwardRow = function (transform, cuboids, cuboidsMulti, delay) {
        var total = this.timeline.totalDuration(),
            i = 0;
        for (var row = 0; row < cuboidsMulti[0].length; row++) {
            for (var col = 0; col < cuboidsMulti.length; col++) {
                transform(cuboidsMulti[col][row], total + delay * i);
                i++;
            }
        }
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceBackwardRow = function (transform, cuboids, cuboidsMulti, delay) {
        var total = this.timeline.totalDuration(),
            i = cuboids.length - 1;
        for (var row = 0; row < cuboidsMulti[0].length; row++) {
            for (var col = 0; col < cuboidsMulti.length; col++) {
                transform(cuboidsMulti[col][row], total + delay * i);
                i--;
            }
        }
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceForwardDiagonal = function (transform, cuboids, cuboidsMulti, delay) {
        var total = this.timeline.totalDuration();
        for (var row = 0; row < cuboidsMulti[0].length; row++) {
            for (var col = 0; col < cuboidsMulti.length; col++) {
                transform(cuboidsMulti[col][row], total + delay * (col + row));
            }
        }
    };

    NextendSmartSliderBackgroundAnimationTiled.prototype.sequenceBackwardDiagonal = function (transform, cuboids, cuboidsMulti, delay) {
        var total = this.timeline.totalDuration(),
            length = cuboidsMulti[0].length + cuboidsMulti.length - 2;
        for (var row = 0; row < cuboidsMulti[0].length; row++) {
            for (var col = 0; col < cuboidsMulti.length; col++) {
                transform(cuboidsMulti[col][row], total + delay * (length - col - row));
            }
        }
    };

    scope.NextendSmartSliderBackgroundAnimationTiled = NextendSmartSliderBackgroundAnimationTiled;


    function NextendSmartSliderBackgroundAnimationFlat() {
        NextendSmartSliderBackgroundAnimationTiled.prototype.constructor.apply(this, arguments);
    };

    NextendSmartSliderBackgroundAnimationFlat.prototype = Object.create(NextendSmartSliderBackgroundAnimationTiled.prototype);
    NextendSmartSliderBackgroundAnimationFlat.prototype.constructor = NextendSmartSliderBackgroundAnimationFlat;

    NextendSmartSliderBackgroundAnimationFlat.prototype.setup = function () {

        var animation = $.extend(true, {
            columns: 1,
            rows: 1,
            tiles: {
                cropOuter: false,
                crop: true,
                delay: 0, // Delay between the starting of the tiles sequence. Ex.: #1 batch start: 0s, #2: .2s, #3: .4s
                sequence: 'Parallel' // Parallel, Random, ForwardCol, BackwardCol, ForwardRow, BackwardRow, ForwardDiagonal, BackwardDiagonal
            },
            main: {
                type: 'next',  // Enable animation on the specified tile: current, next, both
                duration: 0.5,
                real3D: true, // Enable perspective
                zIndex: 1, // z-index of the current image. Change it to 2 to show it over the second image.
                current: { // Animation of the current tile
                    ease: 'easeInOutCubic'
                },
                next: { // Animation of the next tile
                    ease: 'easeInOutCubic'
                }
            }
        }, this.animationProperties);

        if (this.reversed) {
            if (typeof animation.invert !== 'undefined') {
                $.extend(true, animation.main, animation.invert);
            }

            if (typeof animation.invertTiles !== 'undefined') {
                $.extend(animation.tiles, animation.invertTiles);
            }
        }

        NextendSmartSliderBackgroundAnimationTiled.prototype.setup.call(this, animation);

        if (animation.tiles.cropOuter) {
            this.container.css('overflow', 'hidden');
        }
    };

    NextendSmartSliderBackgroundAnimationFlat.prototype.renderTile = function (tile, w, h, animation, totalLeft, totalTop) {

        if (animation.tiles.crop) {
            tile.css('overflow', 'hidden');
        }

        var current = $('<div></div>')
            .css({
                position: 'absolute',
                left: 0,
                top: 0,
                width: w,
                height: h,
                overflow: 'hidden',
                zIndex: animation.main.zIndex
            })
            .append(this.clonedCurrent().clone().css({
                position: 'absolute',
                top: -totalTop + 'px',
                left: -totalLeft + 'px'
            }))
            .appendTo(tile);
        var next = $('<div></div>')
            .css({
                position: 'absolute',
                left: 0,
                top: 0,
                width: w,
                height: h,
                overflow: 'hidden',
                zIndex: 1
            })
            .append(this.clonedNext().clone().css({
                position: 'absolute',
                top: -totalTop + 'px',
                left: -totalLeft + 'px'
            }))
            .appendTo(tile);

        if (animation.main.real3D) {
            NextendTween.set(tile.get(0), {
                transformStyle: "preserve-3d"
            });
            NextendTween.set(current.get(0), {
                transformStyle: "preserve-3d"
            });
            NextendTween.set(next.get(0), {
                transformStyle: "preserve-3d"
            });
        }

        return {
            current: current,
            next: next
        }
    };

    NextendSmartSliderBackgroundAnimationFlat.prototype.transform = function (animation, animatable, total) {

        var main = animation.main;

        if (main.type == 'current' || main.type == 'both') {
            this.timeline.to(animatable.current, main.duration * this.durationMultiplier, main.current, total);
        }

        if (main.type == 'next' || main.type == 'both') {
            this.timeline.from(animatable.next, main.duration * this.durationMultiplier, main.next, total);
        }
    };
    scope.NextendSmartSliderBackgroundAnimationFlat = NextendSmartSliderBackgroundAnimationFlat;


    function NextendSmartSliderBackgroundAnimationCubic() {
        NextendSmartSliderBackgroundAnimationTiled.prototype.constructor.apply(this, arguments);
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype = Object.create(NextendSmartSliderBackgroundAnimationTiled.prototype);
    NextendSmartSliderBackgroundAnimationCubic.prototype.constructor = NextendSmartSliderBackgroundAnimationCubic;


    NextendSmartSliderBackgroundAnimationCubic.prototype.setup = function () {
        var animation = $.extend(true, {
            columns: 1,
            rows: 1,
            fullCube: true,
            tiles: {
                delay: 0.2,  // Delay between the starting of the tiles sequence. Ex.: #1 batch start: 0s, #2: .2s, #3: .4s
                sequence: 'Parallel' // Parallel, Random, ForwardCol, BackwardCol, ForwardRow, BackwardRow, ForwardDiagonal, BackwardDiagonal
            },
            depth: 50, // Used only when side is "Back"
            main: {
                side: 'Left', // Left, Right, Top, Bottom, Back, BackInvert
                duration: 0.5,
                ease: 'easeInOutCubic',
                direction: 'horizontal', // horizontal, vertical // Used when side points to Back
                real3D: true // Enable perspective
            },
            pre: [], // Animations to play on tiles before main
            post: [] // Animations to play on tiles after main
        }, this.animationProperties);
        animation.fullCube = true;

        if (this.reversed) {
            if (typeof animation.invert !== 'undefined') {
                $.extend(true, animation.main, animation.invert);
            }

            if (typeof animation.invertTiles !== 'undefined') {
                $.extend(animation.tiles, animation.invertTiles);
            }
        }

        NextendSmartSliderBackgroundAnimationTiled.prototype.setup.call(this, animation);
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.renderTile = function (tile, w, h, animation, totalLeft, totalTop) {

        var d = animation.depth;

        switch (d) {
            case 'width':
                d = w;
                break;
            case 'height':
                d = h;
                break;
        }
        switch (animation.main.side) {
            case 'Top':
            case 'Bottom':
                d = h;
                break;
            case 'Left':
            case 'Right':
                d = w;
                break;
        }

        if (animation.main.real3D) {
            NextendTween.set(tile.get(0), {
                transformStyle: "preserve-3d"
            });
        }
        var cuboid = $('<div class="cuboid"></div>').css({
            position: 'absolute',
            left: '0',
            top: '0',
            width: '100%',
            height: '100%'
        }).appendTo(tile);
        NextendTween.set(cuboid.get(0), {
            transformStyle: "preserve-3d",
            z: -d / 2
        });

        var backRotationZ = 0;
        if (animation.main.direction == 'horizontal') {
            backRotationZ = 180;
        }
        var back = this.getSide(cuboid, w, h, 0, 0, -d / 2, 180, 0, backRotationZ),
            sides = {
                Back: back,
                BackInvert: back
            };
        if (animation.fullCube || animation.main.direction == 'vertical') {
            sides.Bottom = this.getSide(cuboid, w, d, 0, h - d / 2, 0, -90, 0, 0);
            sides.Top = this.getSide(cuboid, w, d, 0, -d / 2, 0, 90, 0, 0);
        }

        sides.Front = this.getSide(cuboid, w, h, 0, 0, d / 2, 0, 0, 0);
        if (animation.fullCube || animation.main.direction == 'horizontal') {
            sides.Left = this.getSide(cuboid, d, h, -d / 2, 0, 0, 0, -90, 0);
            sides.Right = this.getSide(cuboid, d, h, w - d / 2, 0, 0, 0, 90, 0);
        }

        sides.Front.append(this.clonedCurrent().clone().css({
            position: 'absolute',
            top: -totalTop + 'px',
            left: -totalLeft + 'px'
        }));

        sides[animation.main.side].append(this.clonedNext().clone().css({
            position: 'absolute',
            top: -totalTop + 'px',
            left: -totalLeft + 'px'
        }));

        return cuboid;
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.getSide = function (cuboid, w, h, x, y, z, rX, rY, rZ) {
        var side = $('<div class="n2-3d-side"></div>')
            .css({
                width: w,
                height: h
            })
            .appendTo(cuboid);
        NextendTween.set(side.get(0), {
            x: x,
            y: y,
            z: z,
            rotationX: rX,
            rotationY: rY,
            rotationZ: rZ,
            backfaceVisibility: "hidden"
        });
        return side;
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.addAnimation = function (animation, cuboids) {
        var duration = animation.duration;
        delete animation.duration;
        this.timeline.to(cuboids, duration * this.durationMultiplier, animation);
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.transform = function (animation, cuboid, position) {

        for (var i = 0; i < animation.pre.length; i++) {
            var _a = animation.pre[i];
            var duration = _a.duration * this.durationMultiplier;
            this.timeline.to(cuboid, duration, _a, position);
            position += duration;
        }

        this['transform' + animation.main.side](animation.main, cuboid, position);
        position += animation.main.duration;

        for (var i = 0; i < animation.post.length; i++) {
            var _a = animation.post[i];
            var duration = _a.duration * this.durationMultiplier;
            this.timeline.to(cuboid, duration, _a, position);
            position += duration;
        }
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.transformLeft = function (main, cuboid, total) {
        this._transform(main, cuboid, total, 0, 90, 0);
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.transformRight = function (main, cuboid, total) {
        this._transform(main, cuboid, total, 0, -90, 0);
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.transformTop = function (main, cuboid, total) {
        this._transform(main, cuboid, total, -90, 0, 0);
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.transformBottom = function (main, cuboid, total) {
        this._transform(main, cuboid, total, 90, 0, 0);
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.transformBack = function (main, cuboid, total) {
        if (main.direction == 'horizontal') {
            this._transform(main, cuboid, total, 0, 180, 0);
        } else {
            this._transform(main, cuboid, total, 180, 0, 0);
        }
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype.transformBackInvert = function (main, cuboid, total) {
        if (main.direction == 'horizontal') {
            this._transform(main, cuboid, total, 0, -180, 0);
        } else {
            this._transform(main, cuboid, total, -180, 0, 0);
        }
    };

    NextendSmartSliderBackgroundAnimationCubic.prototype._transform = function (main, cuboid, total, rX, rY, rZ) {
        this.timeline.to(cuboid, main.duration * this.durationMultiplier, {
            rotationX: rX,
            rotationY: rY,
            rotationZ: rZ,
            ease: main.ease
        }, total);
    };

    scope.NextendSmartSliderBackgroundAnimationCubic = NextendSmartSliderBackgroundAnimationCubic;


    function NextendSmartSliderBackgroundAnimationTurn() {
        NextendSmartSliderBackgroundAnimationFluxAbstract.prototype.constructor.apply(this, arguments);

        var animation = $.extend(true, {
            perspective: this.w * 1.5,
            duration: 0.8,
            direction: 'left'
        }, this.animationProperties);

        if (this.reversed) {
            if (animation.direction == 'left') {
                animation.direction = 'right';
            } else {
                animation.direction = 'left';
            }
        }

        var w2 = parseInt(this.w / 2);

        this.clonedCurrent().css({
            'position': 'absolute',
            'top': 0,
            'left': (animation.direction == 'left' ? -1 * (this.w / 2) : 0)
        });

        this.clonedNext().css({
            'position': 'absolute',
            'top': 0,
            'left': (animation.direction == 'left' ? 0 : -1 * (this.w / 2))
        });

        var tab = $('<div class="tab"></div>').css({
            width: w2,
            height: this.h,
            position: 'absolute',
            top: '0px',
            left: animation.direction == 'left' ? w2 : '0',
            'z-index': 101
        });

        NextendTween.set(tab, {
            transformStyle: 'preserve-3d',
            transformOrigin: animation.direction == 'left' ? '0px 0px' : w2 + 'px 0px'
        });

        var front = $('<div class="n2-ff-3d"></div>').append(this.clonedCurrent())
            .css({
                width: w2,
                height: this.h,
                position: 'absolute',
                top: 0,
                left: 0,
                '-webkit-transform': 'translateZ(0.1px)',
                overflow: 'hidden'
            })
            .appendTo(tab);

        NextendTween.set(front, {
            backfaceVisibility: 'hidden',
            transformStyle: 'preserve-3d'
        });


        var back = $('<div class="n2-ff-3d"></div>')
            .append(this.clonedNext())
            .appendTo(tab)
            .css({
                width: w2,
                height: this.h,
                position: 'absolute',
                top: 0,
                left: 0,
                overflow: 'hidden'
            });

        NextendTween.set(back, {
            backfaceVisibility: 'hidden',
            transformStyle: 'preserve-3d',
            rotationY: 180,
            rotationZ: 0
        });

        var current = $('<div></div>')
                .append(this.clonedCurrent().clone().css('left', (animation.direction == 'left' ? 0 : -w2))).css({
                    position: 'absolute',
                    top: 0,
                    left: animation.direction == 'left' ? '0' : w2,
                    width: w2,
                    height: this.h,
                    zIndex: 100,
                    overflow: 'hidden'
                }),
            overlay = $('<div class="overlay"></div>').css({
                position: 'absolute',
                top: 0,
                left: animation.direction == 'left' ? w2 : 0,
                width: w2,
                height: this.h,
                background: '#000',
                opacity: 1,
                overflow: 'hidden'
            }),

            container = $('<div></div>').css({
                width: this.w,
                height: this.h,
                position: 'absolute',
                top: 0,
                left: 0
            }).append(tab).append(current).append(overlay);


        NextendTween.set(container, {
            perspective: animation.perspective,
            perspectiveOrigin: '50% 50%'
        });

        this.placeNextImage();
        this.clonedImages.nextImage.css({
            overflow: 'hidden',
            width: '100%',
            height: '100%'
        });

        this.containerElement.append(container);

        this.preSetup();

        this.timeline.to(tab.get(0), animation.duration * this.durationMultiplier, {
            rotationY: (animation.direction == 'left' ? -180 : 180)
        }, 0);

        this.timeline.to(overlay.get(0), animation.duration * this.durationMultiplier, {
            opacity: 0
        }, 0);
    };

    NextendSmartSliderBackgroundAnimationTurn.prototype = Object.create(NextendSmartSliderBackgroundAnimationFluxAbstract.prototype);
    NextendSmartSliderBackgroundAnimationTurn.prototype.constructor = NextendSmartSliderBackgroundAnimationTurn;


    NextendSmartSliderBackgroundAnimationTurn.prototype.getExtraDelay = function () {
        return 0;
    };

    scope.NextendSmartSliderBackgroundAnimationTurn = NextendSmartSliderBackgroundAnimationTurn;


    function NextendSmartSliderBackgroundAnimationExplode() {
        NextendSmartSliderBackgroundAnimationTiled.prototype.constructor.apply(this, arguments);
    };

    NextendSmartSliderBackgroundAnimationExplode.prototype = Object.create(NextendSmartSliderBackgroundAnimationTiled.prototype);
    NextendSmartSliderBackgroundAnimationExplode.prototype.constructor = NextendSmartSliderBackgroundAnimationExplode;


    NextendSmartSliderBackgroundAnimationExplode.prototype.setup = function () {

        var animation = $.extend(true, {
            columns: 1,
            rows: 1,
            reverse: false,
            tiles: {
                delay: 0, // Delay between the starting of the tiles sequence. Ex.: #1 batch start: 0s, #2: .2s, #3: .4s
                sequence: 'Parallel' // Parallel, Random, ForwardCol, BackwardCol, ForwardRow, BackwardRow, ForwardDiagonal, BackwardDiagonal
            },
            main: {
                duration: 0.5,
                zIndex: 2, // z-index of the current image. Change it to 2 to show it over the second image.
                current: { // Animation of the current tile
                    ease: 'easeInOutCubic'
                }
            }
        }, this.animationProperties);

        this.placeNextImage();
        this.clonedImages.nextImage.css({
            overflow: 'hidden',
            width: '100%',
            height: '100%'
        });

        NextendSmartSliderBackgroundAnimationTiled.prototype.setup.call(this, animation);
    };

    NextendSmartSliderBackgroundAnimationExplode.prototype.renderTile = function (tile, w, h, animation, totalLeft, totalTop) {

        var current = $('<div></div>')
            .css({
                position: 'absolute',
                left: 0,
                top: 0,
                width: w,
                height: h,
                overflow: 'hidden',
                zIndex: animation.main.zIndex
            })
            .append(this.clonedCurrent().clone().css({
                position: 'absolute',
                top: -totalTop + 'px',
                left: -totalLeft + 'px'
            }))
            .appendTo(tile);

        NextendTween.set(tile.get(0), {
            transformPerspective: 1000,
            transformStyle: "preserve-3d"
        });

        return {
            current: current,
            tile: tile
        }
    };

    NextendSmartSliderBackgroundAnimationExplode.prototype.transform = function (animation, animatable, total) {

        var current = $.extend(true, {}, animation.main.current);

        current.rotationX = (Math.random() * 3 - 1) * 90;
        current.rotationY = (Math.random() * 3 - 1) * 90;
        current.rotationZ = (Math.random() * 3 - 1) * 90;
        this.timeline.to(animatable.tile, animation.main.duration * this.durationMultiplier, current, total);
    };

    scope.NextendSmartSliderBackgroundAnimationExplode = NextendSmartSliderBackgroundAnimationExplode;


    function NextendSmartSliderBackgroundAnimationExplodeReversed() {
        NextendSmartSliderBackgroundAnimationTiled.prototype.constructor.apply(this, arguments);
    };

    NextendSmartSliderBackgroundAnimationExplodeReversed.prototype = Object.create(NextendSmartSliderBackgroundAnimationTiled.prototype);
    NextendSmartSliderBackgroundAnimationExplodeReversed.prototype.constructor = NextendSmartSliderBackgroundAnimationExplodeReversed;


    NextendSmartSliderBackgroundAnimationExplodeReversed.prototype.setup = function () {

        var animation = $.extend(true, {
            columns: 1,
            rows: 1,
            reverse: false,
            tiles: {
                delay: 0, // Delay between the starting of the tiles sequence. Ex.: #1 batch start: 0s, #2: .2s, #3: .4s
                sequence: 'Parallel' // Parallel, Random, ForwardCol, BackwardCol, ForwardRow, BackwardRow, ForwardDiagonal, BackwardDiagonal
            },
            main: {
                duration: 0.5,
                zIndex: 2, // z-index of the current image. Change it to 2 to show it over the second image.
                current: { // Animation of the current tile
                    ease: 'easeInOutCubic'
                }
            }
        }, this.animationProperties);

        this.placeCurrentImage();
        this.clonedImages.currentImage.css({
            overflow: 'hidden',
            width: '100%',
            height: '100%'
        });

        NextendSmartSliderBackgroundAnimationTiled.prototype.setup.call(this, animation);
    };

    NextendSmartSliderBackgroundAnimationExplodeReversed.prototype.renderTile = function (tile, w, h, animation, totalLeft, totalTop) {

        var next = $('<div></div>')
            .css({
                position: 'absolute',
                left: 0,
                top: 0,
                width: w,
                height: h,
                overflow: 'hidden',
                zIndex: animation.main.zIndex
            })
            .append(this.clonedNext().clone().css({
                position: 'absolute',
                top: -totalTop + 'px',
                left: -totalLeft + 'px'
            }))
            .appendTo(tile);

        NextendTween.set(tile.get(0), {
            transformPerspective: 1000,
            transformStyle: "preserve-3d"
        });

        return {
            next: next,
            tile: tile
        }
    };

    NextendSmartSliderBackgroundAnimationExplodeReversed.prototype.transform = function (animation, animatable, total) {

        var current = $.extend(true, {}, animation.main.current);

        current.rotationX = (Math.random() * 3 - 1) * 90;
        current.rotationY = (Math.random() * 3 - 1) * 90;
        current.rotationZ = (Math.random() * 3 - 1) * 30;
        this.timeline.from(animatable.tile, animation.main.duration * this.durationMultiplier, current, total);
    };

    scope.NextendSmartSliderBackgroundAnimationExplodeReversed = NextendSmartSliderBackgroundAnimationExplodeReversed;


    function NextendSmartSliderBackgroundAnimationSlixes() {
        NextendSmartSliderBackgroundAnimationTiled.prototype.constructor.apply(this, arguments);
    };

    NextendSmartSliderBackgroundAnimationSlixes.prototype = Object.create(NextendSmartSliderBackgroundAnimationTiled.prototype);
    NextendSmartSliderBackgroundAnimationSlixes.prototype.constructor = NextendSmartSliderBackgroundAnimationSlixes;


    NextendSmartSliderBackgroundAnimationSlixes.prototype.setup = function () {

        var animation = $.extend(true, {
            columns: 2,
            rows: 2,
            main: {
                duration: 2,
                zIndex: 2 // z-index of the current image. Change it to 2 to show it over the second image.
            }
        }, this.animationProperties);

        this.placeNextImage();
        this.clonedImages.nextImage.css({
            overflow: 'hidden',
            width: '100%',
            height: '100%'
        });

        NextendSmartSliderBackgroundAnimationTiled.prototype.setup.call(this, animation);
    };

    NextendSmartSliderBackgroundAnimationSlixes.prototype.renderTile = function (tile, w, h, animation, totalLeft, totalTop) {
        this.container.css('overflow', 'hidden');

        var current = $('<div></div>')
            .css({
                position: 'absolute',
                left: 0,
                top: 0,
                width: w,
                height: h,
                overflow: 'hidden',
                zIndex: animation.main.zIndex
            })
            .append(this.clonedCurrent().clone().css({
                position: 'absolute',
                top: -totalTop + 'px',
                left: -totalLeft + 'px'
            }))
            .appendTo(tile);

        NextendTween.set(tile.get(0), {
            transformPerspective: 1000,
            transformStyle: "preserve-3d"
        });

        return {
            current: current,
            tile: tile
        }
    };

    NextendSmartSliderBackgroundAnimationSlixes.prototype.animate = function (animation, animatables, animatablesMulti) {

        this.timeline.to(animatablesMulti[0][0].tile, animation.main.duration * this.durationMultiplier, {
            left: '-50%',
            ease: 'easeInOutCubic'
        }, 0);
        this.timeline.to(animatablesMulti[0][1].tile, animation.main.duration * this.durationMultiplier, {
            left: '-50%',
            ease: 'easeInOutCubic'
        }, 0.3);

        this.timeline.to(animatablesMulti[1][0].tile, animation.main.duration * this.durationMultiplier, {
            left: '100%',
            ease: 'easeInOutCubic'
        }, 0.15);
        this.timeline.to(animatablesMulti[1][1].tile, animation.main.duration * this.durationMultiplier, {
            left: '100%',
            ease: 'easeInOutCubic'
        }, 0.45);

        $('<div />').css({
            position: 'absolute',
            left: 0,
            top: 0,
            width: '100%',
            height: '100%',
            overflow: 'hidden'
        }).prependTo(this.clonedImages.nextImage.parent()).append(this.clonedImages.nextImage);

        this.timeline.fromTo(this.clonedImages.nextImage, animation.main.duration * this.durationMultiplier, {
            scale: 1.3
        }, {
            scale: 1
        }, 0.45);
    };
    scope.NextendSmartSliderBackgroundAnimationSlixes = NextendSmartSliderBackgroundAnimationSlixes;

})
(n2, window);
/**
 * Abstract class for all the main animations
 * @type {NextendSmartSliderMainAnimationAbstract}
 * @abstract
 */
(function ($, scope, undefined) {
    function NextendSmartSliderMainAnimationAbstract(slider, parameters) {

        this.state = 'ended';
        this.isTouch = false;

        this.slider = slider;

        this.parameters = $.extend({
            duration: 1500,
            ease: 'easeInOutQuint'
        }, parameters);

        this.parameters.duration /= 1000;

        this.sliderElement = slider.sliderElement;

        this.timeline = new NextendTimeline({
            paused: true
        });
    };

    NextendSmartSliderMainAnimationAbstract.prototype.setTouch = function (direction) {
        this.isTouch = direction;
    };

    NextendSmartSliderMainAnimationAbstract.prototype.getState = function () {
        return this.state;
    };

    NextendSmartSliderMainAnimationAbstract.prototype.timeScale = function () {
        if (arguments.length > 0) {
            this.timeline.timeScale(arguments[0]);
            return this;
        }
        return this.timeline.timeScale();
    };

    NextendSmartSliderMainAnimationAbstract.prototype.preChangeToPlay = function (deferred, currentSlide, nextSlide) {
        var deferredHandled = {
            handled: false
        };

        this.sliderElement.trigger('preChangeToPlay', [deferred, deferredHandled, currentSlide, nextSlide]);

        if (!deferredHandled.handled) {
            deferred.resolve();
        }
    };

    NextendSmartSliderMainAnimationAbstract.prototype.changeTo = function (currentSlideIndex, currentSlide, nextSlideIndex, nextSlide, reversed, isSystem) {

        this._initAnimation(currentSlideIndex, currentSlide, nextSlideIndex, nextSlide, reversed);

        this.state = 'initAnimation';

        this.timeline.paused(true);
        this.timeline.eventCallback('onStart', this.onChangeToStart, [currentSlideIndex, nextSlideIndex, isSystem], this);
        this.timeline.eventCallback('onComplete', this.onChangeToComplete, [currentSlideIndex, nextSlideIndex, isSystem], this);

        if (this.slider.parameters.dynamicHeight) {
            var tl = new NextendTimeline();
            this.slider.responsive.doResize(false, tl, nextSlideIndex, 0.6);
            this.timeline.add(tl);
        }


        // If the animation is in touch mode, we do not need to play the timeline as the touch will set the actual progress and also play later...
        if (!this.isTouch) {
            var deferred = $.Deferred();

            deferred.done($.proxy(function () {
                this.play();
            }, this.timeline));

            this.preChangeToPlay(deferred, currentSlide, nextSlide);
        } else {
            this.slider.callOnSlide(currentSlide, 'onOutAnimationsPlayed');
        }
    };

    /**
     * @abstract
     * @param currentSlideIndex
     * @param currentSlide
     * @param nextSlideIndex
     * @param nextSlide
     * @param reversed
     * @private
     */
    NextendSmartSliderMainAnimationAbstract.prototype._initAnimation = function (currentSlideIndex, currentSlide, nextSlideIndex, nextSlide, reversed) {

    };

    NextendSmartSliderMainAnimationAbstract.prototype.onChangeToStart = function (previousSlideIndex, currentSlideIndex, isSystem) {

        this.state = 'playing';

        var parameters = [this, previousSlideIndex, currentSlideIndex, isSystem];

        n2c.log('Event: mainAnimationStart: ', parameters, '{NextendSmartSliderMainAnimationAbstract}, previousSlideIndex, currentSlideIndex, isSystem');
        this.sliderElement.trigger('mainAnimationStart', parameters);

        this.slider.slides.eq(previousSlideIndex).trigger('mainAnimationStartOut', parameters);
        this.slider.slides.eq(currentSlideIndex).trigger('mainAnimationStartIn', parameters);
    };

    NextendSmartSliderMainAnimationAbstract.prototype.onChangeToComplete = function (previousSlideIndex, currentSlideIndex, isSystem) {
        var parameters = [this, previousSlideIndex, currentSlideIndex, isSystem];

        // When the animation done, clear the timeline
        this.timeline.clear();
        this.timeline.timeScale(1);

        this.slider.slides.eq(previousSlideIndex).trigger('mainAnimationCompleteOut', parameters);
        this.slider.slides.eq(currentSlideIndex).trigger('mainAnimationCompleteIn', parameters);

        this.state = 'ended';

        n2c.log('Event: mainAnimationComplete: ', parameters, '{NextendSmartSliderMainAnimationAbstract}, previousSlideIndex, currentSlideIndex, isSystem');
        this.sliderElement.trigger('mainAnimationComplete', parameters);
    };

    NextendSmartSliderMainAnimationAbstract.prototype.getEase = function () {
        if (this.isTouch) {
            return 'linear';
        }
        return this.parameters.ease;
    };
    scope.NextendSmartSliderMainAnimationAbstract = NextendSmartSliderMainAnimationAbstract;
})(n2, window);
(function ($, scope, undefined) {
    function NextendSmartSliderControlAutoplay(slider, parameters) {
        this._inited = false;
        this._active = false;
        this._disabled = false;
        this._currentCount = 0;
        this._progressEnabled = false;
        this.timeline = null;

        this.deferredsMediaPlaying = null;
        this.deferredMouseLeave = null;
        this.deferredMouseEnter = null;
        this.mainAnimationDeferred = true;
        this.autoplayDeferred = null;

        this.slider = slider;

        this.parameters = $.extend({
            enabled: 0,
            start: 1,
            duration: 8000,
            autoplayToSlide: 0,
            pause: {
                mouse: 'enter',
                click: true,
                mediaStarted: true
            },
            resume: {
                click: 0,
                mouse: 0,
                mediaEnded: true
            }
        }, parameters);

        if (this.parameters.enabled) {

            this.parameters.duration /= 1000;

            slider.controls.autoplay = this;

            this.deferredsExtraPlaying = {};

            this.slider.visible($.proxy(this.onReady, this));

        } else {
            this.disable();
        }

        slider.controls.autoplay = this;
    };

    NextendSmartSliderControlAutoplay.prototype.onReady = function () {
        this.autoplayDeferred = $.Deferred();

        var obj = {
            _progress: 0
        };
        this.timeline = NextendTween.to(obj, this.getSlideDuration(this.slider.currentSlideIndex), {
            _progress: 1,
            paused: true,
            onComplete: $.proxy(this.next, this)
        });

        if (this._progressEnabled) {
            this.enableProgress();
        }


        var sliderElement = this.slider.sliderElement;

        if (this.parameters.start) {
            this.continueAutoplay();
        }

        sliderElement.on('mainAnimationStart.autoplay', $.proxy(this.onMainAnimationStart, this));

        if (this.parameters.pause.mouse != '0') {
            switch (this.parameters.pause.mouse) {
                case 'enter':
                    sliderElement.on('mouseenter.autoplay', $.proxy(this.pauseAutoplayMouseEnter, this));
                    sliderElement.on('mouseleave.autoplay', $.proxy(this.pauseAutoplayMouseEnterEnded, this));
                    break;
                case 'leave':
                    sliderElement.on('mouseleave.autoplay', $.proxy(this.pauseAutoplayMouseLeave, this));
                    sliderElement.on('mouseenter.autoplay', $.proxy(this.pauseAutoplayMouseLeaveEnded, this));
                    break;
            }
        }
        if (this.parameters.pause.click && !this.parameters.resume.click) {
            sliderElement.on('universalclick.autoplay', $.proxy(this.pauseAutoplayUniversal, this));
        } else if (!this.parameters.pause.click && this.parameters.resume.click) {
            sliderElement.on('universalclick.autoplay', $.proxy(this.continueAutoplay, this));
        } else {
            sliderElement.on('universalclick.autoplay', $.proxy(function (e) {
                if (this._active) {
                    this.pauseAutoplayUniversal(e);
                } else {
                    this.continueAutoplay(e);
                }
            }, this));
        }
        if (this.parameters.pause.mediaStarted) {
            this.deferredsMediaPlaying = {};
            sliderElement.on('mediaStarted.autoplay', $.proxy(this.pauseAutoplayMediaPlaying, this));
            sliderElement.on('mediaEnded.autoplay', $.proxy(this.pauseAutoplayMediaPlayingEnded, this));
        }

        if (this.parameters.resume.mouse != '0') {
            switch (this.parameters.resume.mouse) {
                case 'enter':
                    sliderElement.on('mouseenter.autoplay', $.proxy(this.continueAutoplay, this));
                    break;
                case 'leave':
                    sliderElement.on('mouseleave.autoplay', $.proxy(this.continueAutoplay, this));
                    break;
            }
        }

        if (this.parameters.resume.mediaEnded) {
            sliderElement.on('mediaEnded.autoplay', $.proxy(this.continueAutoplay, this));
        }
        sliderElement.on('autoplayExtraWait.autoplay', $.proxy(this.pauseAutoplayExtraPlaying, this));
        sliderElement.on('autoplayExtraContinue.autoplay', $.proxy(this.pauseAutoplayExtraPlayingEnded, this));

    };

    NextendSmartSliderControlAutoplay.prototype.enableProgress = function () {
        if (this.timeline) {
            this.timeline.eventCallback('onUpdate', $.proxy(this.onUpdate, this));
        }
        this._progressEnabled = true;
    };


    NextendSmartSliderControlAutoplay.prototype.onMainAnimationStart = function (e, animation, previousSlideIndex, currentSlideIndex, isSystem) {
        this.mainAnimationDeferred = $.Deferred();
        this.deActivate(0);
    };

    NextendSmartSliderControlAutoplay.prototype.onMainAnimationComplete = function (e, animation, previousSlideIndex, currentSlideIndex) {
        this.timeline.duration(this.getSlideDuration(currentSlideIndex));

        this.mainAnimationDeferred.resolve();

        this.continueAutoplay();
    };

    NextendSmartSliderControlAutoplay.prototype.getSlideDuration = function (index) {
        var slide = this.slider.realSlides.eq(this.slider.getRealIndex(index)).data('slide'),
            duration = slide.minimumSlideDuration;

        if (duration < 0.3 && duration < this.parameters.duration) {
            duration = this.parameters.duration;
        }
        return duration;
    };

    NextendSmartSliderControlAutoplay.prototype.continueAutoplay = function (e) {
        if (this.autoplayDeferred.state() == 'pending') {
            this.autoplayDeferred.reject();
        }
        var deferreds = [];
        for (var k in this.deferredsExtraPlaying) {
            deferreds.push(this.deferredsExtraPlaying[k]);
        }
        for (var k in this.deferredsMediaPlaying) {
            deferreds.push(this.deferredsMediaPlaying[k]);
        }
        deferreds.push(this.deferredMouseEnter);
        deferreds.push(this.mainAnimationDeferred);

        this.autoplayDeferred = $.Deferred();
        this.autoplayDeferred.done($.proxy(this._continueAutoplay, this));

        $.when.apply($, deferreds).done($.proxy(function () {
            if (this.autoplayDeferred.state() == 'pending') {
                this.autoplayDeferred.resolve();
            }
        }, this));
    };

    NextendSmartSliderControlAutoplay.prototype._continueAutoplay = function () {
        if (!this._active && !this._disabled) {
            this._active = true;
            if (!this._inited) {
                this.slider.sliderElement.on('mainAnimationComplete.autoplay', $.proxy(this.onMainAnimationComplete, this));
                this._inited = true;
            }
            n2c.log('Event: autoplayStarted');
            this.slider.sliderElement.triggerHandler('autoplayStarted');

            if (this.timeline.progress() == 1) {
                this.timeline.pause(0, false);
            }

            this.startTimeout(null);
        }
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayUniversal = function () {
        this.autoplayDeferred.reject();
        this.deActivate(null);
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayMouseEnter = function () {
        this.autoplayDeferred.reject();
        this.deferredMouseEnter = $.Deferred();
        this.deActivate(null);
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayMouseEnterEnded = function () {
        if (this.deferredMouseEnter) {
            this.deferredMouseEnter.resolve();
        }
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayMouseLeave = function () {
        this.autoplayDeferred.reject();
        this.deferredMouseLeave = $.Deferred();
        this.deActivate(null);
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayMouseLeaveEnded = function () {
        if (this.deferredMouseLeave) {
            this.deferredMouseLeave.resolve();
        }
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayMediaPlaying = function (e, obj) {
        if (typeof this.deferredsMediaPlaying[obj] !== 'undefined') {
            this.autoplayDeferred.reject();
        }
        this.deferredsMediaPlaying[obj] = $.Deferred();
        this.deActivate(null);
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayMediaPlayingEnded = function (e, obj) {
        if (typeof this.deferredsMediaPlaying[obj] !== 'undefined') {
            this.autoplayDeferred.reject();
            this.deferredsMediaPlaying[obj].resolve();
            delete this.deferredsMediaPlaying[obj];
        }
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayExtraPlaying = function (e, obj) {
        if (typeof this.deferredsExtraPlaying[obj] !== 'undefined') {
            this.autoplayDeferred.reject();
        }
        this.deferredsExtraPlaying[obj] = $.Deferred();
        this.deActivate(null);
    };

    NextendSmartSliderControlAutoplay.prototype.pauseAutoplayExtraPlayingEnded = function (e, obj) {
        if (typeof this.deferredsExtraPlaying[obj] !== 'undefined') {
            this.autoplayDeferred.reject();
            this.deferredsExtraPlaying[obj].resolve();
            delete this.deferredsExtraPlaying[obj];
        }
        this.continueAutoplay();
    };

    NextendSmartSliderControlAutoplay.prototype.deActivate = function (seekTo) {
        if (this._active) {
            this._active = false;
            if (seekTo !== 0) {
                n2c.log('Event: autoplayPaused');
                this.slider.sliderElement.triggerHandler('autoplayPaused');
            }
        }

        if (this.timeline) {
            this.timeline.pause(seekTo, false);
        }
    };

    NextendSmartSliderControlAutoplay.prototype.disable = function () {
        this.deActivate(0);
        this.slider.sliderElement.triggerHandler('autoplayPaused');
        this.slider.sliderElement.triggerHandler('autoplayDisabled');
        this.slider.sliderElement.off('.autoplay');
        n2c.log('Autoplay: disable');
        this._disabled = true;
    };

    NextendSmartSliderControlAutoplay.prototype.startTimeout = function (time) {
        if (this._active && !this._disabled) {
            this.timeline.play(time);
        }
    };

    NextendSmartSliderControlAutoplay.prototype.next = function () {
        this.timeline.pause();
        this._currentCount++;
        /**
         * We have reached the maximum slides in the autoplay so disable it completely
         */
        if (this.parameters.autoplayToSlide > 0 && this._currentCount >= this.parameters.autoplayToSlide) {
            n2c.log('Autoplay: auto play to slide value reached');
            this.disable();
        }

        this.slider.nextCarousel(true);
    };

    NextendSmartSliderControlAutoplay.prototype.onUpdate = function () {
        this.slider.sliderElement.triggerHandler('autoplay', this.timeline.progress());
    };

    scope.NextendSmartSliderControlAutoplay = NextendSmartSliderControlAutoplay;
})(n2, window);
(function ($, scope, undefined) {
    "use strict";
    function NextendSmartSliderControlKeyboard(slider, direction, parameters) {

        this.slider = slider;

        this.parameters = $.extend({}, parameters);

        if (direction == 'vertical') {
            this.parseEvent = NextendSmartSliderControlKeyboard.prototype.parseEventVertical;
        } else {
            this.parseEvent = NextendSmartSliderControlKeyboard.prototype.parseEventHorizontal;
        }

        $(document).on('keydown', $.proxy(this.onKeyDown, this));

        slider.controls.keyboard = this;
    };

    NextendSmartSliderControlKeyboard.prototype.onKeyDown = function (e) {

        if (e.target.tagName.match(/BODY|DIV|IMG/)) {
            e = e || window.event;
            if (this.parseEvent.call(this, e)) {
                e.preventDefault();
            }
        }
    };

    NextendSmartSliderControlKeyboard.prototype.parseEventHorizontal = function (e) {
        switch (e.keyCode) {
            case 39: // right arrow
                this.slider.next();
                return true;
            case 37: // left arrow
                this.slider.previous();
                return true;
            default:
                return false;
        }
    };

    NextendSmartSliderControlKeyboard.prototype.parseEventVertical = function (e) {
        switch (e.keyCode) {
            case 40: // down arrow
                this.slider.next();
                return true;
            case 38: // up arrow
                this.slider.previous();
                return true;
            default:
                return false;
        }
    };
    scope.NextendSmartSliderControlKeyboard = NextendSmartSliderControlKeyboard;
})(n2, window);
(function ($, scope, undefined) {
    "use strict";
    function NextendSmartSliderControlScroll(slider) {

        this.preventScroll = false

        this.slider = slider;

        // handled by jquery.mousewheel.js
        slider.sliderElement.on('mousewheel', $.proxy(this.onMouseWheel, this));

        slider.controls.scroll = this;
    };

    NextendSmartSliderControlScroll.prototype.onMouseWheel = function (e) {
        if (!this.preventScroll) {
            this.preventScroll = true;
            if (e.deltaY > 0) {
                if (this.slider.previous()) {
                    // Stops the browser normal scroll
                    e.preventDefault();
                }
            } else {
                if (this.slider.next()) {
                    // Stops the browser normal scroll
                    e.preventDefault();
                }
            }
            setTimeout($.proxy(function () {
                this.preventScroll = false;
            }, this), 400);
        } else {
            e.preventDefault();
        }
    };
    scope.NextendSmartSliderControlScroll = NextendSmartSliderControlScroll;
})(n2, window);
(function ($, scope, undefined) {
    "use strict";
    function NextendSmartSliderControlTilt(slider, parameters) {

        if (typeof window.DeviceOrientationEvent == 'undefined' || typeof window.orientation == 'undefined') {
            return "Not supported";
        }
        this.timeout = null;

        this.slider = slider;

        this.parameters = $.extend({
            duration: 2000
        }, parameters);

        this.orientationchange();

        window.addEventListener('orientationchange', $.proxy(this.orientationchange, this));

        window.addEventListener("deviceorientation", $.proxy(this.handleOrientation, this), true);

        slider.controls.tilt = this;
    };

    NextendSmartSliderControlTilt.prototype.orientationchange = function () {
        switch (window.orientation) {
            case -90:
            case 90:
                this.parseEvent = NextendSmartSliderControlTilt.prototype.parseEventHorizontalLandscape;
                break;
            default:
                this.parseEvent = NextendSmartSliderControlTilt.prototype.parseEventHorizontal;
                break;
        }
    };

    NextendSmartSliderControlTilt.prototype.clearTimeout = function () {
        this.timeout = null;
    };

    NextendSmartSliderControlTilt.prototype.handleOrientation = function (e) {
        if (this.timeout == null && this.parseEvent.call(this, e)) {
            this.timeout = setTimeout($.proxy(this.clearTimeout, this), this.parameters.duration);

            e.preventDefault();
        }
    };

    NextendSmartSliderControlTilt.prototype.parseEventHorizontal = function (e) {
        if (e.gamma > 10) { // right tilt
            this.slider.next();
            return true;
        } else if (e.gamma < -10) { // left tilt
            this.slider.previous();
            return true;
        }
        return false;
    };

    NextendSmartSliderControlTilt.prototype.parseEventHorizontalLandscape = function (e) {
        if (e.beta < -10) { // right tilt
            this.slider.next();
            return true;
        } else if (e.beta > 10) { // left tilt
            this.slider.previous();
            return true;
        }
        return false;
    };

    scope.NextendSmartSliderControlTilt = NextendSmartSliderControlTilt;

})(n2, window);
(function ($, scope, undefined) {
    "use strict";
    var pointer = window.navigator.pointerEnabled || window.navigator.msPointerEnabled;

    function NextendSmartSliderControlTouch(slider, direction, parameters) {
        this.currentAnimation = null;
        this.slider = slider;

        this._animation = slider.mainAnimation;

        this.parameters = $.extend({
            fallbackToMouseEvents: true
        }, parameters);

        this.swipeElement = this.slider.sliderElement.find('> div').eq(0);

        if (direction == 'vertical') {
            this.setVertical();
        } else if (direction == 'horizontal') {
            this.setHorizontal();
        }

        this.swipeElement.addClass('unselectable').swipe({
            axis: this._direction.axis,
            threshold: 10,
            preventDefaultEvents: false,
            triggerOnTouchLeave: true,
            fallbackToMouseEvents: this.parameters.fallbackToMouseEvents,
            swipeStatus: $.proxy(this.onSwipeStatus, this),
            tap: $.proxy(this.onTap, this)
        }).on('dragstart', function (e) {
            e.preventDefault();
        });

        if (!this.parameters.fallbackToMouseEvents) {
            this.swipeElement.on('click', $.proxy(this.onTap, this));
        }

        if (this.parameters.fallbackToMouseEvents) {
            this.swipeElement.addClass('n2-grab');
        }

        slider.controls.touch = this;
    };

    NextendSmartSliderControlTouch.prototype.setHorizontal = function () {

        this._property = 'width';

        this._direction = {
            left: 'next',
            right: 'previous',
            up: null,
            down: null,
            axis: 'horizontal'
        };

        if (pointer) {
            this.swipeElement.css('-ms-touch-action', 'pan-y');
            this.swipeElement.css('touch-action', 'pan-y');
        }
    };

    NextendSmartSliderControlTouch.prototype.setVertical = function () {

        this._property = 'height';

        this._direction = {
            left: null,
            right: null,
            up: 'next',
            down: 'previous',
            axis: 'vertical'
        };

        if (pointer) {
            this.swipeElement.css('-ms-touch-action', 'pan-x');
            this.swipeElement.css('touch-action', 'pan-x');
        }
    };

    NextendSmartSliderControlTouch.prototype.onSwipeStatus = function (event, phase, direction, distance, duration, fingers) {
        if (distance > 10 && direction != null && this._direction[direction] !== null) {
            event.preventDefault();
            if (this.currentAnimation === null) {
                if (this._animation.state != 'ended') {
                    // skip the event as the current animation is still playing
                    return;
                }
                this.swipeElement.addClass('n2-grabbing');

                // Force the main animation into touch mode horizontal/vertical
                this._animation.setTouch(this._direction.axis);

                this.currentAnimation = {
                    direction: direction,
                    percent: 0
                };
                this.slider[this._direction[direction]](false);

            }
            if (this.currentAnimation.percent < 1 && this.currentAnimation.direction == direction) {
                var percent = distance / this.slider.dimensions.slider[this._property];
                if (percent <= 1) {
                    this.currentAnimation.percent = percent;
                    this._animation.timeline.progress(percent);
                }
            }
        }

        /**
         * The direction can be different for the last "action", so this block can't be in the previous if statement
         */
        if (this.currentAnimation !== null && (phase == "end" || phase == "cancel")) {
            var progress = this._animation.timeline.progress();
            if (progress != 1) {
                var totalDuration = this._animation.timeline.totalDuration(),
                    modifiedDuration = Math.max(totalDuration / 3, Math.min(totalDuration, duration / progress / 1000));
                if (modifiedDuration != totalDuration) {
                    this._animation.timeline.totalDuration(modifiedDuration);
                }
                this._animation.timeline.play();
            }
            this.swipeElement.removeClass('n2-grabbing');

            // Switch back the animation into the original mode when our touch is ended
            this._animation.setTouch(false);
            this.currentAnimation = null;
        }
    };

    NextendSmartSliderControlTouch.prototype.onTap = function (e) {
        if ((e.type != 'mouseup' || e.which == 1) && e.type != 'mouseout') {
            $(e.target).trigger('n2click');
        }
    };

    scope.NextendSmartSliderControlTouch = NextendSmartSliderControlTouch;

})(n2, window);
(function ($, scope, undefined) {

    /**
     * NOT_INITIALIZED -> INITIALIZED -> READY_TO_START -> PLAYING -> ENDED
     *                          <-----------------------------/
     */
    var SlideStatus = {
            NOT_INITIALIZED: -1,
            INITIALIZED: 0,
            READY_TO_START: 1,
            PLAYING: 2,
            ENDED: 3
        },
        TimelineMode = {
            event: 0,
            linear: 1
        },
        LayerStatus = {
            NOT_INITIALIZED: -1,
            INITIALIZED: 1,
            PLAY_IN_DISABLED: 2,
            PLAY_IN_STARTED: 3,
            PLAY_IN_PAUSED: 4,
            PLAY_IN_ENDED: 5,
            PLAY_LOOP_STARTED: 6,
            PLAY_LOOP_PAUSED: 7,
            PLAY_LOOP_ENDED: 8,
            PLAY_OUT_STARTED: 9,
            PLAY_OUT_PAUSED: 10,
            PLAY_OUT_ENDED: 11
        },
        In = {
            NOT_INITIALIZED: -1,
            NO: 0,
            INITIALIZED: 1
        },
        Loop = {
            NOT_INITIALIZED: -1,
            NO: 0,
            INITIALIZED: 1
        },
        Out = {
            NOT_INITIALIZED: -1,
            NO: 0,
            INITIALIZED: 1
        },
        zero = {
            opacity: 1,
            x: 0,
            y: 0,
            z: 0,
            rotationX: 0,
            rotationY: 0,
            rotationZ: 0,
            scaleX: 1,
            scaleY: 1,
            scaleZ: 1,
            skewX: 0
        },
        responsiveProperties = ['left', 'top', 'width', 'height'];


    if (/(MSIE\ [0-7]\.\d+)/.test(navigator.userAgent)) {
        function getPos($element) {
            return $element.position();
        }
    } else {
        function getPos($element) {
            return {
                left: $element.prop('offsetLeft'),
                top: $element.prop('offsetTop')
            }
        }
    }

    function Slide(slider, $slideElement, isFirstSlide, isStaticSlide) {
        if (typeof isStaticSlide === 'undefined') {
            isStaticSlide = false;
        }
        this.isStaticSlide = isStaticSlide;
        this.status = SlideStatus.NOT_INITIALIZED;
        this.slider = slider;
        this.slider.isFirstSlide = true;

        this.$slideElement = $slideElement;

        $slideElement.data('slide', this);

        if (!slider.parameters.admin) {
            this.minimumSlideDuration = $slideElement.data('slide-duration');
            if (!$.isNumeric(this.minimumSlideDuration)) {
                this.minimumSlideDuration = 0;
            }
        } else {
            this.minimumSlideDuration = 0;
        }

        this.findLayers();

        if (!this.slider.parameters.admin || !$slideElement.is(this.slider.adminGetCurrentSlideElement())) {
            this.initResponsiveMode();
        }

        this.status = SlideStatus.INITIALIZED;

        this.playOnce = (!this.slider.isAdmin && this.slider.parameters.layerMode.playOnce);
    };

    Slide.prototype.isActive = function () {
        return this.$slideElement.hasClass('n2-ss-slide-active');
    };

    Slide.prototype.findLayers = function () {
        this.$layers = this.$slideElement.find('.n2-ss-layer')
            .each($.proxy(function (i, el) {
                var $el = $(el);
                for (var j = 0; j < responsiveProperties.length; j++) {
                    var property = responsiveProperties[j];
                    $el.data('desktop' + property, parseFloat(el.style[property]));
                }
                var parent = this.getLayerProperty($el, 'parentid');
                if (typeof parent !== 'undefined' && parent) {
                    parent = $('#' + parent);
                    if (parent.length > 0) {
                        $el.data('parent', parent);
                    }
                } else {
                    $el.data('parent', false);
                }
            }, this));
        this.$parallax = this.$layers.filter('[data-parallax]');
    };

    Slide.prototype.getLayerResponsiveProperty = function (layer, mode, property) {
        var value = layer.data(mode + property);
        if (typeof value != 'undefined') {
            return value;
        }
        if (mode != 'desktopportrait') {
            return layer.data('desktopportrait' + property);
        }
        return 0;
    };

    Slide.prototype.getLayerProperty = function (layer, property) {
        return layer.data(property);
    };

    Slide.prototype.initResponsiveMode = function () {
        this.slider.sliderElement.on('SliderDeviceOrientation', $.proxy(function (e, modes) {
            var mode = modes.device + modes.orientation.toLowerCase();
            this.currentMode = mode;
            this.$layers.each($.proxy(function (i, el) {
                var layer = $(el),
                    show = layer.data(mode),
                    parent = layer.data('parent');
                if ((typeof show == 'undefined' || parseInt(show))) {
                    if (this.getLayerProperty(layer, 'adaptivefont')) {
                        layer.css('font-size', (16 * this.getLayerResponsiveProperty(layer, this.currentMode, 'fontsize') / 100) + 'px');
                    } else {
                        layer.css('font-size', this.getLayerResponsiveProperty(layer, this.currentMode, 'fontsize') + '%');
                    }
                    layer.data('shows', 1);
                    layer.css('display', 'block');
                } else {
                    layer.data('shows', 0);
                    layer.css('display', 'none');
                }
            }, this));
        }, this))
            .on('SliderResize', $.proxy(function (e, ratios, responsive) {

                var dimensions = responsive.responsiveDimensions;

                this.$layers.each($.proxy(function (i, el) {
                    this.repositionLayer($(el), ratios, dimensions);
                }, this));
            }, this));
    };

    Slide.prototype.isDimensionPropertyAccepted = function (value) {
        if ((value + '').match(/[0-9]+%/) || value == 'auto') {
            return true;
        }
        return false;
    };

    Slide.prototype.repositionLayer = function (layer, ratios, dimensions) {
        var ratioPositionH = ratios.slideW,
            ratioSizeH = ratioPositionH,
            ratioPositionV = ratios.slideH,
            ratioSizeV = ratioPositionV;

        if (!parseInt(this.getLayerProperty(layer, 'responsivesize'))) {
            ratioSizeH = ratioSizeV = 1;
        }

        var width = this.getLayerResponsiveProperty(layer, this.currentMode, 'width');
        layer.css('width', this.isDimensionPropertyAccepted(width) ? width : (width * ratioSizeH) + 'px');
        var height = this.getLayerResponsiveProperty(layer, this.currentMode, 'height');
        layer.css('height', this.isDimensionPropertyAccepted(height) ? height : (height * ratioSizeV) + 'px');

        if (!parseInt(this.getLayerProperty(layer, 'responsiveposition'))) {
            ratioPositionH = ratioPositionV = 1;
        }


        var left = this.getLayerResponsiveProperty(layer, this.currentMode, 'left') * ratioPositionH,
            top = this.getLayerResponsiveProperty(layer, this.currentMode, 'top') * ratioPositionV,
            align = this.getLayerResponsiveProperty(layer, this.currentMode, 'align'),
            valign = this.getLayerResponsiveProperty(layer, this.currentMode, 'valign');


        var positionCSS = {
                left: 'auto',
                top: 'auto',
                right: 'auto',
                bottom: 'auto'
            },
            parent = this.getLayerProperty(layer, 'parent');

        if (parent && parent.data('shows')) {
            var position = getPos(parent),
                p = {left: 0, top: 0};

            switch (this.getLayerResponsiveProperty(layer, this.currentMode, 'parentalign')) {
                case 'right':
                    p.left = position.left + parent.width();
                    break;
                case 'center':
                    p.left = position.left + parent.width() / 2;
                    break;
                default:
                    p.left = position.left;
            }

            switch (align) {
                case 'right':
                    positionCSS.right = (layer.parent().width() - p.left - left) + 'px';
                    break;
                case 'center':
                    positionCSS.left = (p.left + left - layer.width() / 2) + 'px';
                    break;
                default:
                    positionCSS.left = (p.left + left) + 'px';
                    break;
            }


            switch (this.getLayerResponsiveProperty(layer, this.currentMode, 'parentvalign')) {
                case 'bottom':
                    p.top = position.top + parent.height();
                    break;
                case 'middle':
                    p.top = position.top + parent.height() / 2;
                    break;
                default:
                    p.top = position.top;
            }

            switch (valign) {
                case 'bottom':
                    positionCSS.bottom = (layer.parent().height() - p.top - top) + 'px';
                    break;
                case 'middle':
                    positionCSS.top = (p.top + top - layer.height() / 2) + 'px';
                    break;
                default:
                    positionCSS.top = (p.top + top) + 'px';
                    break;
            }


        } else {
            switch (align) {
                case 'right':
                    positionCSS.right = -left + 'px';
                    break;
                case 'center':
                    positionCSS.left = ((this.isStaticSlide ? layer.parent().width() : dimensions.slide.width) / 2 + left - layer.width() / 2) + 'px';
                    break;
                default:
                    positionCSS.left = left + 'px';
                    break;
            }

            switch (valign) {
                case 'bottom':
                    positionCSS.bottom = -top + 'px';
                    break;
                case 'middle':
                    positionCSS.top = ((this.isStaticSlide ? layer.parent().height() : dimensions.slide.height) / 2 + top - layer.height() / 2) + 'px';
                    break;
                default:
                    positionCSS.top = top + 'px';
                    break;
            }
        }
        layer.css(positionCSS);
    };

    Slide.prototype.setZero = function () {
        this.$slideElement.trigger('layerSetZero', this);
    };

    Slide.prototype.setZeroAll = function () {
        this.$slideElement.trigger('layerSetZeroAll', this);
    };

    Slide.prototype.setStart = function () {
        if (this.status == SlideStatus.INITIALIZED) {
            this.$slideElement.trigger('layerAnimationSetStart');
            this.status = SlideStatus.READY_TO_START;
        }
    };

    Slide.prototype.playIn = function () {
        if (this.status == SlideStatus.READY_TO_START) {
            this.status = SlideStatus.PLAYING;
            this.$slideElement.trigger('layerAnimationPlayIn');
        }
    };

    Slide.prototype.playOut = function () {
        if (this.status == SlideStatus.PLAYING) {
            var deferreds = [];
            this.$slideElement.triggerHandler('beforeMainSwitch', [deferreds]);

            $.when.apply($, deferreds)
                .done($.proxy(function () {
                    this.onOutAnimationsPlayed();
                }, this));
        } else {
            this.onOutAnimationsPlayed();
        }
    };

    Slide.prototype.onOutAnimationsPlayed = function () {
        if (!this.playOnce) {
            this.status = SlideStatus.INITIALIZED;
        } else {
            this.status = SlideStatus.ENDED;
        }
        this.$slideElement.trigger('layerAnimationCompleteOut');
    };

    Slide.prototype.pause = function () {
        this.$slideElement.triggerHandler('layerPause');
    };

    Slide.prototype.reset = function () {
        this.$slideElement.triggerHandler('layerReset');
        this.status = SlideStatus.INITIALIZED;
    };

    Slide.prototype.getTimeline = function () {
        return this.layers.getTimeline();
    };

    scope.NextendSmartSliderSlide = Slide;

    function SlideLayers(slide, $layers, mode, ratios) {
        this.layerAnimations = [];
        this.slide = slide;
        slide.$slideElement.off(".n2-ss-animations");
        for (var i = 0; i < $layers.length; i++) {
            var $layer = $layers.eq(i);
            this.layerAnimations.push(new SlideLayerAnimations(slide, this, $layer, $layer.find('.n2-ss-layer-mask, .n2-ss-layer-parallax').addBack().last(), mode, ratios));
        }
    };

    SlideLayers.prototype.refresh = function (ratios) {
        for (var i = 0; i < this.layerAnimations.length; i++) {
            this.layerAnimations[i].refresh(ratios);
        }
    };

    SlideLayers.prototype.getTimeline = function () {
        var timeline = new NextendTimeline({
            paused: 1
        });
        for (var i = 0; i < this.layerAnimations.length; i++) {
            var animation = this.layerAnimations[i];
            timeline.add(animation.linearTimeline, 0);
            animation.linearTimeline.paused(false);

        }
        return timeline;
    };
    scope.NextendSmartSliderSlideLayers = SlideLayers;

})(n2, window);
(function ($, scope, undefined) {

    var isTablet = null,
        isMobile = null;

    function NextendSmartSliderResponsive(slider, parameters) {
        if (slider.isAdmin) {
            this.doResize = NextendThrottle(this.doResize, 50);
        }

        if (typeof nextend.fontsDeferred === 'undefined') {
            this.triggerResize = this._triggerResize;
        }


        this.fixedEditRatio = 1;
        this.normalizeTimeout = null;
        this.delayedResizeAdded = false;

        this.deviceMode = NextendSmartSliderResponsive.DeviceMode.UNKNOWN;
        this.orientationMode = NextendSmartSliderResponsive.OrientationMode.SCREEN;
        this.orientation = NextendSmartSliderResponsive.DeviceOrientation.UNKNOWN;
        this.lastRatios = {
            ratio: -1
        };
        this.normalizedMode = 'unknownUnknown';

        slider.responsive = this;

        this.widgetMargins = {
            Top: [],
            Right: [],
            Bottom: [],
            Left: []
        };
        this.staticSizes = {
            paddingTop: 0,
            paddingRight: 0,
            paddingBottom: 0,
            paddingLeft: 0
        };
        this.enabledWidgetMargins = [];

        this.slider = slider;
        this.sliderElement = slider.sliderElement;

        var ready = this.ready = $.Deferred();
        this.sliderElement.one('SliderResize', function () {
            ready.resolve();
        });

        this.containerElementPadding = this.sliderElement.parent();
        this.containerElement = this.containerElementPadding.parent();
        this.parameters = $.extend({
            desktop: 1,
            tablet: 1,
            mobile: 1,

            onResizeEnabled: true,
            type: 'auto',
            downscale: true,
            upscale: false,
            minimumHeight: 0,
            maximumHeight: 0,
            minimumHeightRatio: 0,
            maximumHeightRatio: {
                desktopLandscape: 0,
                desktopPortrait: 0,
                mobileLandscape: 0,
                mobilePortrait: 0,
                tabletLandscape: 0,
                tabletPortrait: 0
            },
            maximumSlideWidth: 0,
            maximumSlideWidthLandscape: 0,
            maximumSlideWidthRatio: -1,
            maximumSlideWidthTablet: 0,
            maximumSlideWidthTabletLandscape: 0,
            maximumSlideWidthMobile: 0,
            maximumSlideWidthMobileLandscape: 0,
            maximumSlideWidthConstrainHeight: 0,
            forceFull: 0,
            verticalOffsetSelectors: '',

            focusUser: 0,
            focusAutoplay: 0,

            deviceModes: {
                desktopLandscape: 1,
                desktopPortrait: 0,
                mobileLandscape: 0,
                mobilePortrait: 0,
                tabletLandscape: 0,
                tabletPortrait: 0
            },
            normalizedDeviceModes: {
                unknownUnknown: ["unknown", "Unknown"],
                desktopPortrait: ["desktop", "Portrait"]
            },
            verticalRatioModifiers: {
                unknownUnknown: 1,
                desktopLandscape: 1,
                desktopPortrait: 1,
                mobileLandscape: 1,
                mobilePortrait: 1,
                tabletLandscape: 1,
                tabletPortrait: 1
            },
            minimumFontSizes: {
                desktopLandscape: 0,
                desktopPortrait: 0,
                mobileLandscape: 0,
                mobilePortrait: 0,
                tabletLandscape: 0,
                tabletPortrait: 0
            },
            ratioToDevice: {
                Portrait: {
                    tablet: 0,
                    mobile: 0
                },
                Landscape: {
                    tablet: 0,
                    mobile: 0
                }
            },
            sliderWidthToDevice: {
                desktopLandscape: 0,
                desktopPortrait: 0,
                mobileLandscape: 0,
                mobilePortrait: 0,
                tabletLandscape: 0,
                tabletPortrait: 0
            },

            basedOn: 'combined',
            desktopPortraitScreenWidth: 1200,
            tabletPortraitScreenWidth: 800,
            mobilePortraitScreenWidth: 440,
            tabletLandscapeScreenWidth: 1024,
            mobileLandscapeScreenWidth: 740,
            orientationMode: 'width_and_height'
        }, parameters);

        if (this.parameters.orientationMode == 'width') {
            this.orientationMode = NextendSmartSliderResponsive.OrientationMode.SCREEN_WIDTH_ONLY;
        }

        nextend.smallestZoom = Math.min(Math.max(parameters.sliderWidthToDevice.mobilePortrait, 120), 380);

        switch (this.parameters.basedOn) {
            case 'screen':
                break;
            default:
                if (isTablet == null) {
                    var md = new MobileDetect(window.navigator.userAgent);
                    isTablet = !!md.tablet();
                    isMobile = !!md.phone();
                }
        }

        if (!this.slider.isAdmin) {
            if (!this.parameters.desktop || !this.parameters.tablet || !this.parameters.mobile) {
                if (isTablet == null) {
                    var md = new MobileDetect(window.navigator.userAgent);
                    isTablet = !!md.tablet();
                    isMobile = !!md.phone();
                }
                if (!this.parameters.mobile && isMobile || !this.parameters.tablet && isTablet || !this.parameters.desktop && !isTablet && !isMobile) {
                    this.slider.kill();
                    return;
                }
            }
        }

        this.verticalOffsetSelectors = $(this.parameters.verticalOffsetSelectors);

        n2c.log('Responsive: Store defaults');
        this.storeDefaults();

        if (this.parameters.minimumHeight > 0) {
            this.parameters.minimumHeightRatio = this.parameters.minimumHeight / this.responsiveDimensions.startHeight;
        }

        if (this.parameters.maximumHeight > 0 && this.parameters.maximumHeight >= this.parameters.minimumHeight) {
            this.parameters.maximumHeightRatio = {
                desktopPortrait: this.parameters.maximumHeight / this.responsiveDimensions.startHeight
            };
            this.parameters.maximumHeightRatio.desktopLandscape = this.parameters.maximumHeightRatio.desktopPortrait;
            this.parameters.maximumHeightRatio.tabletPortrait = this.parameters.maximumHeightRatio.desktopPortrait;
            this.parameters.maximumHeightRatio.tabletLandscape = this.parameters.maximumHeightRatio.desktopPortrait;
            this.parameters.maximumHeightRatio.mobilePortrait = this.parameters.maximumHeightRatio.desktopPortrait;
            this.parameters.maximumHeightRatio.mobileLandscape = this.parameters.maximumHeightRatio.desktopPortrait;
        }

        if (this.parameters.maximumSlideWidth > 0) {
            this.parameters.maximumSlideWidthRatio = {
                desktopPortrait: this.parameters.maximumSlideWidth / this.responsiveDimensions.startSlideWidth,
                desktopLandscape: this.parameters.maximumSlideWidthLandscape / this.responsiveDimensions.startSlideWidth,
                tabletPortrait: this.parameters.maximumSlideWidthTablet / this.responsiveDimensions.startSlideWidth,
                tabletLandscape: this.parameters.maximumSlideWidthTabletLandscape / this.responsiveDimensions.startSlideWidth,
                mobilePortrait: this.parameters.maximumSlideWidthMobile / this.responsiveDimensions.startSlideWidth,
                mobileLandscape: this.parameters.maximumSlideWidthMobileLandscape / this.responsiveDimensions.startSlideWidth
            }

            if (this.parameters.maximumSlideWidthConstrainHeight) {
                this.parameters.maximumHeightRatio = this.parameters.maximumSlideWidthRatio;
            }
        }

        n2c.log('Responsive: First resize');
        if (typeof nextend !== 'undefined' && typeof nextend['ssBeforeResponsive'] !== 'undefined') {
            nextend['ssBeforeResponsive'].call(this);
        }

        this.onResize();
        if (this.parameters.onResizeEnabled || this.parameters.type == 'adaptive') {
            $(window).on('resize', $.proxy(this.onResize, this));
        }
    };

    NextendSmartSliderResponsive.OrientationMode = {
        SCREEN: 0,
        ADMIN_LANDSCAPE: 1,
        ADMIN_PORTRAIT: 2,
        SCREEN_WIDTH_ONLY: 3
    };
    NextendSmartSliderResponsive.DeviceOrientation = {
        UNKNOWN: 0,
        LANDSCAPE: 1,
        PORTRAIT: 2
    };
    NextendSmartSliderResponsive._DeviceOrientation = {
        0: 'Unknown',
        1: 'Landscape',
        2: 'Portrait'
    };
    NextendSmartSliderResponsive.DeviceMode = {
        UNKNOWN: 0,
        DESKTOP: 1,
        TABLET: 2,
        MOBILE: 3
    };
    NextendSmartSliderResponsive._DeviceMode = {
        0: 'unknown',
        1: 'desktop',
        2: 'tablet',
        3: 'mobile'
    };

    NextendSmartSliderResponsive.prototype.getOuterWidth = function () {
        var rd = this.responsiveDimensions;
        return rd.startSliderWidth + rd.startSliderMarginLeft + rd.startSliderMarginRight;
    };

    NextendSmartSliderResponsive.prototype.storeDefaults = function () {

        // We should use outerWidth(true) as we need proper margin calculation for the ratio
        this.responsiveDimensions = {
            startWidth: this.sliderElement.outerWidth(true),
            startHeight: this.sliderElement.outerHeight(true)
        };

        /**
         * @type {NextendSmartSliderResponsiveElement[]}
         */
        this.responsiveElements = [];

        this.helperElements = {};

        this.addResponsiveElements();

        this.margins = {
            top: this.responsiveDimensions.startSliderMarginTop,
            right: this.responsiveDimensions.startSliderMarginRight,
            bottom: this.responsiveDimensions.startSliderMarginBottom,
            left: this.responsiveDimensions.startSliderMarginLeft
        }
    };

    /**
     * @abstract
     */
    NextendSmartSliderResponsive.prototype.addResponsiveElements = function () {
    };

    /**
     * Add an element list as a single element. Other elements in the list will get the same property as the first element.
     * @param element
     * @param cssproperties
     * @param name
     */
    NextendSmartSliderResponsive.prototype.addResponsiveElement = function (element, cssproperties, group, name) {
        if (typeof group === 'undefined' || !group) {
            group = 'ratio';
        }
        var responsiveElement = new NextendSmartSliderResponsiveElement(this, group, element, cssproperties, name);
        this.responsiveElements.push(responsiveElement);
        return responsiveElement;
    };

    NextendSmartSliderResponsive.prototype.addResponsiveElementBackgroundImage = function (element, backgroundImage, cssproperties, group, name) {
        if (typeof group === 'undefined' || !group) {
            group = 'ratio';
        }
        var responsiveElement = new NextendSmartSliderResponsiveElementBackgroundImage(this, backgroundImage, group, element, cssproperties, name);
        this.responsiveElements.push(responsiveElement);
        return responsiveElement;
    };

    /**
     * Add each element from the list as a single element. It is good for image list as every image might have different dimensions
     * @param elements
     * @param cssproperties
     * @param name
     */
    NextendSmartSliderResponsive.prototype.addResponsiveElementAsSingle = function (elements, cssproperties, group, name) {
        var responsiveElements = [];
        for (var i = 0; i < elements.length; i++) {
            responsiveElements.push(this.addResponsiveElement(elements.eq(i), cssproperties.slice(0), group, name));
        }
        return responsiveElements;
    };

    NextendSmartSliderResponsive.prototype.addResponsiveElementBackgroundImageAsSingle = function (elements, backgroundImage, cssproperties, group, name) {
        var responsiveElements = [];
        for (var i = 0; i < elements.length; i++) {
            responsiveElements.push(this.addResponsiveElementBackgroundImage(elements.eq(i), backgroundImage, cssproperties.slice(0), group, name));
        }
        return responsiveElements;
    };

    NextendSmartSliderResponsive.prototype.resizeResponsiveElements = function (ratios, timeline, duration) {
        for (var i = 0; i < this.responsiveElements.length; i++) {
            var responsiveElement = this.responsiveElements[i];
            if (typeof ratios[responsiveElement.group] === 'undefined') {
                console.log('error with ' + responsiveElement.group);
            }
            responsiveElement.resize(this.responsiveDimensions, ratios[responsiveElement.group], timeline, duration);
        }
    };

    NextendSmartSliderResponsive.prototype.getDeviceMode = function () {
        return NextendSmartSliderResponsive._DeviceMode[this.deviceMode];
    };

    NextendSmartSliderResponsive.prototype.getDeviceModeOrientation = function () {
        return NextendSmartSliderResponsive._DeviceMode[this.deviceMode] + NextendSmartSliderResponsive._DeviceOrientation[this.orientation];
    };

    NextendSmartSliderResponsive.prototype.onResize = function () {
        if (this.slider.mainAnimation.getState() == 'ended') {
            this.doResize();
        } else if (!this.delayedResizeAdded) {
            this.delayedResizeAdded = true;
            this.sliderElement.on('mainAnimationComplete.responsive', $.proxy(this._doDelayedResize, this));
        }
    };

    NextendSmartSliderResponsive.prototype._doDelayedResize = function () {
        this.doResize();
        this.delayedResizeAdded = false;
    };


    NextendSmartSliderResponsive.prototype.doNormalizedResize = function () {
        if (this.normalizeTimeout) {
            clearTimeout(this.normalizeTimeout);
        }

        this.normalizeTimeout = setTimeout($.proxy(this.doResize, this), 10);
    };

    NextendSmartSliderResponsive.prototype._getOrientation = function () {
        if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.SCREEN) {
            if (window.innerHeight <= window.innerWidth) {
                return NextendSmartSliderResponsive.DeviceOrientation.LANDSCAPE;
            } else {
                return NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT;
            }
        } else if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.ADMIN_PORTRAIT) {
            return NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT;
        } else if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.ADMIN_LANDSCAPE) {
            return NextendSmartSliderResponsive.DeviceOrientation.LANDSCAPE;
        }
    };

    NextendSmartSliderResponsive.prototype._getDevice = function () {
        switch (this.parameters.basedOn) {
            case 'combined':
                return this._getDeviceDevice(this._getDeviceScreenWidth());
            case 'device':
                return this._getDeviceDevice(NextendSmartSliderResponsive.DeviceMode.DESKTOP);
            case 'screen':
                return this._getDeviceScreenWidth();
        }
    };

    NextendSmartSliderResponsive.prototype._getDeviceScreenWidth = function () {
        var viewportWidth = window.innerWidth;
        if (this.orientation == NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT) {
            if (viewportWidth < this.parameters.mobilePortraitScreenWidth) {
                return NextendSmartSliderResponsive.DeviceMode.MOBILE;
            } else if (viewportWidth < this.parameters.tabletPortraitScreenWidth) {
                return NextendSmartSliderResponsive.DeviceMode.TABLET;
            }
        } else {
            if (viewportWidth < this.parameters.mobileLandscapeScreenWidth) {
                return NextendSmartSliderResponsive.DeviceMode.MOBILE;
            } else if (viewportWidth < this.parameters.tabletLandscapeScreenWidth) {
                return NextendSmartSliderResponsive.DeviceMode.TABLET;
            }
        }
        return NextendSmartSliderResponsive.DeviceMode.DESKTOP;
    };

    NextendSmartSliderResponsive.prototype._getDeviceAndOrientationByScreenWidth = function () {
        var viewportWidth = window.innerWidth;
        if (viewportWidth < this.parameters.mobilePortraitScreenWidth) {
            return [NextendSmartSliderResponsive.DeviceMode.MOBILE, NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT];
        } else if (viewportWidth < this.parameters.mobileLandscapeScreenWidth) {
            return [NextendSmartSliderResponsive.DeviceMode.MOBILE, NextendSmartSliderResponsive.DeviceOrientation.LANDSCAPE];
        } else if (viewportWidth < this.parameters.tabletPortraitScreenWidth) {
            return [NextendSmartSliderResponsive.DeviceMode.TABLET, NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT];
        } else if (viewportWidth < this.parameters.tabletLandscapeScreenWidth) {
            return [NextendSmartSliderResponsive.DeviceMode.TABLET, NextendSmartSliderResponsive.DeviceOrientation.LANDSCAPE];
        } else if (viewportWidth < this.parameters.desktopPortraitScreenWidth) {
            return [NextendSmartSliderResponsive.DeviceMode.DESKTOP, NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT];
        }
        return [NextendSmartSliderResponsive.DeviceMode.DESKTOP, NextendSmartSliderResponsive.DeviceOrientation.LANDSCAPE];
    };

    NextendSmartSliderResponsive.prototype._getDeviceDevice = function (device) {
        if (isMobile === true) {
            return NextendSmartSliderResponsive.DeviceMode.MOBILE;
        } else if (isTablet && device != NextendSmartSliderResponsive.DeviceMode.MOBILE) {
            return NextendSmartSliderResponsive.DeviceMode.TABLET;
        }
        return device;
    };

    NextendSmartSliderResponsive.prototype._getDeviceZoom = function (ratio) {
        var orientation;
        if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.ADMIN_PORTRAIT) {
            orientation = NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT;
        } else if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.ADMIN_LANDSCAPE) {
            orientation = NextendSmartSliderResponsive.DeviceOrientation.LANDSCAPE;
        }
        var targetMode = NextendSmartSliderResponsive.DeviceMode.DESKTOP;
        if (ratio <= this.parameters.ratioToDevice[NextendSmartSliderResponsive._DeviceOrientation[orientation]].mobile) {
            targetMode = NextendSmartSliderResponsive.DeviceMode.MOBILE;
        } else if (ratio <= this.parameters.ratioToDevice[NextendSmartSliderResponsive._DeviceOrientation[orientation]].tablet) {
            targetMode = NextendSmartSliderResponsive.DeviceMode.TABLET;
        }
        return targetMode;
    };

    NextendSmartSliderResponsive.prototype.reTriggerSliderDeviceOrientation = function () {
        var normalized = this._normalizeMode(NextendSmartSliderResponsive._DeviceMode[this.deviceMode], NextendSmartSliderResponsive._DeviceOrientation[this.orientation]);
        this.sliderElement.trigger('SliderDeviceOrientation', {
            lastDevice: normalized[0],
            lastOrientation: normalized[1],
            device: normalized[0],
            orientation: normalized[1]
        });
    };

    NextendSmartSliderResponsive.prototype.doResize = function (fixedMode, timeline, nextSlideIndex, duration) {

        // required to force recalculate if the thumbnails widget get hidden.
        this.refreshMargin();

        if (!this.slider.isAdmin) {
            if (this.parameters.forceFull) {
                $('body').css('overflow-x', 'hidden');
                var outerEl = this.containerElement.parent();
                this.containerElement.css('marginLeft', -outerEl.offset().left - parseInt(outerEl.css('paddingLeft')) - parseInt(outerEl.css('borderLeftWidth'))).width(document.body.clientWidth || document.documentElement.clientWidth);
            }
        }
        var ratio = this.containerElementPadding.width() / this.getOuterWidth();


        var hasOrientationOrDeviceChange = false,
            lastOrientation = this.orientation,
            lastDevice = this.deviceMode,
            targetOrientation = null,
            targetMode = null;

        if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.SCREEN_WIDTH_ONLY) {
            var deviceOrientation = this._getDeviceAndOrientationByScreenWidth();
            targetMode = deviceOrientation[0]
            targetOrientation = deviceOrientation[1];
        } else {
            targetOrientation = this._getOrientation()
        }

        if (this.orientation != targetOrientation) {
            this.orientation = targetOrientation;
            hasOrientationOrDeviceChange = true;
            n2c.log('Event: SliderOrientation', {
                lastOrientation: NextendSmartSliderResponsive._DeviceOrientation[lastOrientation],
                orientation: NextendSmartSliderResponsive._DeviceOrientation[targetOrientation]
            });
            this.sliderElement.trigger('SliderOrientation', {
                lastOrientation: NextendSmartSliderResponsive._DeviceOrientation[lastOrientation],
                orientation: NextendSmartSliderResponsive._DeviceOrientation[targetOrientation]
            });
        }

        if (!fixedMode) {
            if (this.orientationMode != NextendSmartSliderResponsive.OrientationMode.SCREEN_WIDTH_ONLY) {
                targetMode = this._getDevice(ratio);
            }

            if (this.deviceMode != targetMode) {
                this.deviceMode = targetMode;
                this.sliderElement.removeClass('n2-ss-' + NextendSmartSliderResponsive._DeviceMode[lastDevice])
                    .addClass('n2-ss-' + NextendSmartSliderResponsive._DeviceMode[targetMode]);
                n2c.log('Event: SliderDevice', {
                    lastDevice: NextendSmartSliderResponsive._DeviceMode[lastDevice],
                    device: NextendSmartSliderResponsive._DeviceMode[targetMode]
                });
                this.sliderElement.trigger('SliderDevice', {
                    lastDevice: NextendSmartSliderResponsive._DeviceMode[lastDevice],
                    device: NextendSmartSliderResponsive._DeviceMode[targetMode]
                });
                hasOrientationOrDeviceChange = true;
            }
        }

        if (!this.slider.isAdmin) {
            if (this.parameters.type == 'fullpage') {
                this.parameters.maximumHeightRatio[this.getDeviceModeOrientation()] = this.parameters.minimumHeightRatio = ((document.documentElement.clientHeight || document.body.clientHeight) - this.getVerticalOffsetHeight()) / this.responsiveDimensions.startHeight;
            }
        }

        if (hasOrientationOrDeviceChange) {
            var lastNormalized = this._normalizeMode(NextendSmartSliderResponsive._DeviceMode[lastDevice], NextendSmartSliderResponsive._DeviceOrientation[lastOrientation]),
                normalized = this._normalizeMode(NextendSmartSliderResponsive._DeviceMode[this.deviceMode], NextendSmartSliderResponsive._DeviceOrientation[this.orientation]);

            if (lastNormalized[0] != normalized[0] || lastNormalized[1] != normalized[1]) {
                this.normalizedMode = normalized[0] + normalized[1];
                n2c.log('Event: SliderDeviceOrientation', {
                    lastDevice: lastNormalized[0],
                    lastOrientation: lastNormalized[1],
                    device: normalized[0],
                    orientation: normalized[1]
                });
                this.sliderElement.trigger('SliderDeviceOrientation', {
                    lastDevice: lastNormalized[0],
                    lastOrientation: lastNormalized[1],
                    device: normalized[0],
                    orientation: normalized[1]
                });
            }
        }
        /*
         if (this.parameters.type == 'adaptive') {
         this._doResize(this.parameters.sliderWidthToDevice[this.normalizedMode] / this.parameters.sliderWidthToDevice.desktopPortrait);
         } else {
         */
        var zeroRatio = this.parameters.sliderWidthToDevice[this.normalizedMode] / this.parameters.sliderWidthToDevice.desktopPortrait;
        if (!this.parameters.downscale && ratio < zeroRatio) {
            ratio = zeroRatio;
        } else if (!this.parameters.upscale && ratio > zeroRatio) {
            ratio = zeroRatio;
        }
        this._doResize(ratio, timeline, nextSlideIndex, duration);
        //}
    };

    NextendSmartSliderResponsive.prototype._normalizeMode = function (device, orientation) {
        return this.parameters.normalizedDeviceModes[device + orientation];
    };

    NextendSmartSliderResponsive.prototype.getNormalizedModeString = function () {
        var normalized = this._normalizeMode(NextendSmartSliderResponsive._DeviceMode[this.deviceMode], NextendSmartSliderResponsive._DeviceOrientation[this.orientation]);
        return normalized.join('');
    };

    NextendSmartSliderResponsive.prototype.getModeString = function () {
        return NextendSmartSliderResponsive._DeviceMode[this.deviceMode] + NextendSmartSliderResponsive._DeviceOrientation[this.orientation];
    };

    NextendSmartSliderResponsive.prototype.isEnabled = function (device, orientation) {
        return this.parameters.deviceModes[device + orientation];
    };

    NextendSmartSliderResponsive.prototype._doResize = function (ratio, timeline, nextSlideIndex, duration) {
        var ratios = {
            ratio: ratio,
            w: ratio,
            h: ratio,
            slideW: ratio,
            slideH: ratio,
            fontRatio: 1
        };

        this._buildRatios(ratios, this.slider.parameters.dynamicHeight, nextSlideIndex);
        /*
         if (this.fixedEditRatio && this.slider.isAdmin) {
         ratios.w = ratios.slideW;
         ratios.h = ratios.slideH;
         }
         */
        ratios.fontRatio = ratios.slideW;


        var isChanged = false;
        for (var k in ratios) {
            if (ratios[k] != this.lastRatios[k]) {
                isChanged = true;
                break;
            }
        }

        if (isChanged) {
            this.resizeResponsiveElements(ratios, timeline, duration);
            this.lastRatios = ratios;

            if (timeline) {
                this.sliderElement.trigger('SliderAnimatedResize', [ratios, timeline, duration]);
                timeline.eventCallback("onComplete", function () {
                    this.triggerResize(ratios, timeline);
                }, [], this);
            } else {
                this.triggerResize(ratios, timeline);
            }
        }
    };

    NextendSmartSliderResponsive.prototype.triggerResize = function (ratios, timeline) {
        nextend.fontsDeferred.done($.proxy(function () {
            this.triggerResize = this._triggerResize;
            this._triggerResize(ratios, timeline);
        }, this));
    };

    NextendSmartSliderResponsive.prototype._triggerResize = function (ratios, timeline) {
        n2c.log('Event: SliderResize', ratios);
        this.sliderElement.trigger('SliderResize', [ratios, this, timeline]);
    };

    NextendSmartSliderResponsive.prototype._buildRatios = function (ratios, dynamicHeight, nextSlideIndex) {
        if (this.parameters.minimumHeightRatio > 0) {
            ratios.h = Math.max(ratios.h, this.parameters.minimumHeightRatio);
        }

        var deviceModeOrientation = this.getDeviceModeOrientation();

        if (this.parameters.maximumHeightRatio[deviceModeOrientation] > 0) {
            ratios.h = Math.min(ratios.h, this.parameters.maximumHeightRatio[deviceModeOrientation]);
        }

        if (this.parameters.maximumSlideWidthRatio[deviceModeOrientation] > 0 && ratios.slideW > this.parameters.maximumSlideWidthRatio[deviceModeOrientation]) {
            ratios.slideW = this.parameters.maximumSlideWidthRatio[deviceModeOrientation];
        }

        ratios.slideW = ratios.slideH = Math.min(ratios.slideW, ratios.slideH);

        var verticalRatioModifier = this.parameters.verticalRatioModifiers[deviceModeOrientation];
        ratios.slideH *= verticalRatioModifier;
        if (this.parameters.type == 'fullpage') {
            if (ratios.slideH > ratios.h) {
                ratios.slideW /= ratios.slideH / ratios.h;
            }
            ratios.slideH = Math.min(ratios.slideH, ratios.h);

            if (this.slider.isAdmin) {
                ratios.w = ratios.slideW;
                ratios.h = ratios.slideH;
            }
        } else {
            ratios.h *= verticalRatioModifier;
            ratios.slideH = Math.min(ratios.slideH, ratios.h);
            //if (this.parameters.type == 'showcase') {
            // Constrain slide ratio in showcase type
            ratios.slideW = ratios.slideH / verticalRatioModifier;
            //}
        }

        var slideIndex = this.slider.currentSlideIndex;
        if (typeof nextSlideIndex !== 'undefined') {
            slideIndex = nextSlideIndex;
        }

        if (dynamicHeight) {
            var backgroundRatio = this.slider.backgroundImages.backgroundImages[slideIndex].responsiveElement.relativeRatio;
            if (backgroundRatio != -1) {
                ratios.slideH *= backgroundRatio;
                ratios.h *= backgroundRatio;
            }
        }

        this.sliderElement.triggerHandler('responsiveBuildRatios', [ratios]);
    };

    NextendSmartSliderResponsive.prototype.setOrientation = function (newOrientation) {
        if (newOrientation == 'portrait') {
            this.orientationMode = NextendSmartSliderResponsive.OrientationMode.ADMIN_PORTRAIT;
        } else if (newOrientation == 'landscape') {
            this.orientationMode = NextendSmartSliderResponsive.OrientationMode.ADMIN_LANDSCAPE;
        }
    };

    NextendSmartSliderResponsive.prototype.setMode = function (newMode) {
        var orientation;
        if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.ADMIN_PORTRAIT) {
            orientation = NextendSmartSliderResponsive.DeviceOrientation.PORTRAIT;
        } else if (this.orientationMode == NextendSmartSliderResponsive.OrientationMode.ADMIN_LANDSCAPE) {
            orientation = NextendSmartSliderResponsive.DeviceOrientation.LANDSCAPE;
        }
        var width = this.parameters.sliderWidthToDevice[newMode + NextendSmartSliderResponsive._DeviceOrientation[orientation]];
        width = nextend.smallestZoom + (((this.parameters.sliderWidthToDevice['desktopPortrait'] - nextend.smallestZoom)) / 50) * Math.floor((width - nextend.smallestZoom) / (((this.parameters.sliderWidthToDevice['desktopPortrait'] - nextend.smallestZoom)) / 50));
        this.setSize(width);
        if (this.containerElement.width() > width) {
            // We have to find a proper value for the zoom slider - backend only
            width = this.parameters.sliderWidthToDevice[newMode + NextendSmartSliderResponsive._DeviceOrientation[orientation]] - (this.parameters.sliderWidthToDevice['desktopPortrait'] - nextend.smallestZoom) / 50;
            this.setSize(width);
        }
    };

    NextendSmartSliderResponsive.prototype.setSize = function (targetWidth) {
        this.containerElement.width(targetWidth);

        this.doResize();
    };

    /**
     * Required for maximum slide width calculation
     * @returns {null}
     */
    NextendSmartSliderResponsive.prototype.getCanvas = function () {
        return null;
    };

    NextendSmartSliderResponsive.prototype.getVerticalOffsetHeight = function () {
        var h = 0;
        for (var i = 0; i < this.verticalOffsetSelectors.length; i++) {
            h += this.verticalOffsetSelectors.eq(i).outerHeight();
        }
        return h;
    };

    NextendSmartSliderResponsive.prototype.addMargin = function (side, widget) {
        this.widgetMargins[side].push(widget);
        if (widget.isVisible()) {
            this._addMarginSize(side, widget.getSize());
            this.enabledWidgetMargins.push(widget);
        }
        this.doNormalizedResize();
    };

    NextendSmartSliderResponsive.prototype.addStaticMargin = function (side, widget) {
        if (!this.widgetStaticMargins) {
            this.widgetStaticMargins = {
                Top: [],
                Right: [],
                Bottom: [],
                Left: []
            };
        }
        this.widgetStaticMargins[side].push(widget);
        this.doNormalizedResize();
    };

    NextendSmartSliderResponsive.prototype.refreshMargin = function () {
        for (var side in this.widgetMargins) {
            var widgets = this.widgetMargins[side];
            for (var i = widgets.length - 1; i >= 0; i--) {
                var widget = widgets[i];
                if (widget.isVisible()) {
                    if ($.inArray(widget, this.enabledWidgetMargins) == -1) {
                        this._addMarginSize(side, widget.getSize());
                        this.enabledWidgetMargins.push(widget);
                    }
                } else {
                    var index = $.inArray(widget, this.enabledWidgetMargins);
                    if (index != -1) {
                        this._addMarginSize(side, -widget.getSize());
                        this.enabledWidgetMargins.splice(index, 1);
                    }
                }
            }
        }
        if (this.widgetStaticMargins) {
            var staticSizes = {
                paddingTop: 0,
                paddingRight: 0,
                paddingBottom: 0,
                paddingLeft: 0
            };
            for (var side in this.widgetStaticMargins) {
                var widgets = this.widgetStaticMargins[side];
                for (var i = widgets.length - 1; i >= 0; i--) {
                    var widget = widgets[i];
                    if (widget.isVisible()) {
                        staticSizes['padding' + side] += widget.getSize();
                    }
                }
            }
            for (var k in staticSizes) {
                this.containerElementPadding.css(staticSizes);
            }
            this.staticSizes = staticSizes;
        }
    };

    NextendSmartSliderResponsive.prototype._addMarginSize = function (side, size) {
        var axis = null;
        switch (side) {
            case 'Top':
            case 'Bottom':
                axis = this._sliderVertical;
                break;
            default:
                axis = this._sliderHorizontal;
        }
        axis.data['margin' + side] += size;
        this.responsiveDimensions['startSliderMargin' + side] += size;
    };

    scope.NextendSmartSliderResponsive = NextendSmartSliderResponsive;
})(n2, window);
(function ($, scope, undefined) {

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    /**
     * @constructor
     * @param responsive {NextendSmartSliderResponsive} caller object
     * @param group {String}
     * @param element {jQuery}
     * @param cssProperties {Array} Array of properties which will be responsive
     * @param name {String} we will register the changed values for this namespace in the global NextendSmartSliderResponsive objects' responsiveDimensions property
     */
    function NextendSmartSliderResponsiveElement(responsive, group, element, cssProperties, name) {
        this.loadDefaults();
        this._lastRatio = 1;
        this.responsive = responsive;

        this.group = group;

        this.element = element;

        this.lazyload = this.responsive.slider.parameters.lazyload.enabled;

        this._readyDeferred = $.Deferred();

        if (typeof name !== 'undefined') {
            this.name = name;
        } else {
            this.name = null;
        }

        this.tagName = element.prop("tagName");

        this.data = {};

        this.helper = {
            /**
             * Holds the current element's parent element, which is required for the centered mode
             */
            parent: null,
            /**
             * Holds the current element's parent original width and height for images
             */
            parentProps: null,
            /**
             * If font size is enabled for the current element, this will hold the different font sized for the different devices
             */
            fontSize: false,
            /**
             * If this is enabled, the responsive mode will try to position the actual element into the center of the parent element
             */
            centered: false
        };

        if (!this.customLoad) {
            switch (this.tagName) {
                case 'IMG':
                    var parent = element.parent();
                    // The images doesn't have their original(not the real dimension, it is the place
                    // what was taken right after the load) width and height values in the future.
                    // So we will calculate the original size from the parent element size
                    // We will assume that the image was 100% width to its parent
                    this.helper.parentProps = {
                        width: parent.width(),
                        height: parent.height()
                    }
                    // Images might not have proper height and width values when not loaded
                    // Let's wait for them
                    if (this.lazyload) {
                        // Lazy load happens much later than the imagesloaded, but this is why it is lazy :)
                        element.on('lazyloaded', $.proxy(this._lateInitIMG, this, cssProperties));
                    } else {
                        element.imagesLoaded($.proxy(this._lateInitIMG, this, cssProperties));
                    }
                    break;
                // We don't have anything to wait so we can start our later initialization
                default:
                    this._lateInit(cssProperties);
            }
        } else {
            this.customLoad(cssProperties);
        }

    };

    NextendSmartSliderResponsiveElement.prototype.loadDefaults = function () {
        this.customLoad = false;
        this.lazyload = false;
    };

    NextendSmartSliderResponsiveElement.prototype._lateInit = function (cssProperties) {

        this._cssProperties = cssProperties;

        this.reloadDefault();

        /**
         * If font-size is responsive on the element, we init this feature on the element.
         */
        if ($.inArray('fontSize', cssProperties) != -1) {

            this.data['fontSize'] = this.element.data('fontsize');

            this.helper.fontSize = {
                fontSize: this.element.data('fontsize'),
                desktopPortrait: this.element.data('minfontsizedesktopportrait'),
                desktopLandscape: this.element.data('minfontsizedesktoplandscape'),
                tabletPortrait: this.element.data('minfontsizetabletportrait'),
                tabletLandscape: this.element.data('minfontsizetabletlandscape'),
                mobilePortrait: this.element.data('minfontsizemobileportrait'),
                mobileLandscape: this.element.data('minfontsizemobilelandscape')
            };

            // Sets the proper font size for the current mode
            //this.setFontSizeByMode(this.responsive.mode.mode);

            // When the mode changes we have to adjust the original font size value in the data
            this.responsive.sliderElement.on('SliderDeviceOrientation', $.proxy(this.onModeChange, this));
        }

        // Our resource is finished with the loading, so we can enable the normal resize method.
        this.resize = this._resize;

        // We are ready
        this._readyDeferred.resolve();
    };

    NextendSmartSliderResponsiveElement.prototype.reloadDefault = function () {

        for (var i = 0; i < this._cssProperties.length; i++) {
            var propName = this._cssProperties[i];
            this.data[propName] = parseInt(this.element.css(propName));
        }
        if (this.name) {
            var d = this.responsive.responsiveDimensions;
            for (var k in this.data) {
                d['start' + capitalize(this.name) + capitalize(k)] = this.data[k];
            }
        }
    };

    NextendSmartSliderResponsiveElement.prototype._lateInitIMG = function (cssProperties, e) {

        // As our background images has 100% width, we know that the original img size was the same as the parent's width.
        // Then we can calculate the original height of the img as the parent element's ratio might not the same as the background image

        var width = this.element.width(),
            height = this.element.height();

        height = parseInt(this.helper.parentProps.width / width * height);
        width = this.helper.parentProps.width;

        var widthIndex = $.inArray('width', cssProperties);
        if (widthIndex != -1) {
            cssProperties.splice(widthIndex, 1);
            this.data['width'] = width;
        }
        var heightIndex = $.inArray('height', cssProperties);
        if (heightIndex != -1) {
            cssProperties.splice(heightIndex, 1);
            this.data['height'] = height;
        }
        this._lateInit(cssProperties);
    };

    /**
     * You can use it as the normal jQuery ready, except it check for the current element list
     * @param {function} fn
     */
    NextendSmartSliderResponsiveElement.prototype.ready = function (fn) {
        this._readyDeferred.done(fn);
    };

    /**
     * When the element list is not loaded yet, we have to add the current resize call to the ready event.
     * @example You have an image which is not loaded yet, but a resize happens on the browser. We have to make the resize later when the image is ready!
     * @param responsiveDimensions
     * @param ratio
     */
    NextendSmartSliderResponsiveElement.prototype.resize = function (responsiveDimensions, ratio) {
        this.ready($.proxy(this.resize, this, responsiveDimensions, ratio));
        this._lastRatio = ratio;
    };

    NextendSmartSliderResponsiveElement.prototype._resize = function (responsiveDimensions, ratio, timeline, duration) {
        if (this.name && typeof responsiveDimensions[this.name] === 'undefined') {
            responsiveDimensions[this.name] = {};
        }

        var to = {};
        for (var propName in this.data) {
            var value = this.data[propName] * ratio;
            if (typeof this[propName + 'Prepare'] == 'function') {
                value = this[propName + 'Prepare'](value);
            }

            if (this.name) {
                responsiveDimensions[this.name][propName] = value;
            }
            to[propName] = value;
        }
        if (timeline) {
            timeline.to(this.element, duration, to, 0);
        } else {
            this.element.css(to);

            if (this.helper.centered) {
                // when centered feature enabled we have to set the proper margins for the element to make it centered
                if (n2const.isIOS && this.tagName == 'IMG') {
                    // If this fix not applied, IOS might not calculate the correct width and height for the image
                    this.element.css({
                        marginLeft: 1,
                        marginTop: 1
                    });
                }
                this.element.css({
                    marginLeft: parseInt((this.helper.parent.width() - this.element.width()) / 2),
                    marginTop: parseInt((this.helper.parent.height() - this.element.height()) / 2)
                });
            }
        }
        this._lastRatio = ratio;
    };

    NextendSmartSliderResponsiveElement.prototype._refreshResize = function () {
        this.responsive.ready.done($.proxy(function () {
            this._resize(this.responsive.responsiveDimensions, this.responsive.lastRatios[this.group]);
        }, this));
    };

    NextendSmartSliderResponsiveElement.prototype.widthPrepare = function (value) {
        return Math.round(value);
    };

    NextendSmartSliderResponsiveElement.prototype.heightPrepare = function (value) {
        return Math.round(value);
    };

    NextendSmartSliderResponsiveElement.prototype.marginLeftPrepare = function (value) {
        return parseInt(value);
    };

    NextendSmartSliderResponsiveElement.prototype.marginRightPrepare = function (value) {
        return parseInt(value);
    };

    NextendSmartSliderResponsiveElement.prototype.lineHeightPrepare = function (value) {
        return value + 'px';
    };

    NextendSmartSliderResponsiveElement.prototype.fontSizePrepare = function (value) {
        var mode = this.responsive.getNormalizedModeString();
        if (value < this.helper.fontSize[mode]) {
            return this.helper.fontSize[mode];
        }
        return value;
    };

    /**
     * Enables the centered feature on the current element.
     */
    NextendSmartSliderResponsiveElement.prototype.setCentered = function () {
        this.helper.parent = this.element.parent();
        this.helper.centered = true;
    };
    NextendSmartSliderResponsiveElement.prototype.unsetCentered = function () {
        this.helper.centered = false;
    };
    NextendSmartSliderResponsiveElement.prototype.onModeChange = function () {
        this.setFontSizeByMode();
    };

    /**
     * Changes the original font size based on the current mode and also updates the current value on the element.
     * @param mode
     */
    NextendSmartSliderResponsiveElement.prototype.setFontSizeByMode = function () {
        this.element.css('fontSize', this.fontSizePrepare(this.data['fontSize'] * this._lastRatio));
    };
    scope.NextendSmartSliderResponsiveElement = NextendSmartSliderResponsiveElement;


    function NextendSmartSliderResponsiveElementBackgroundImage(responsive, backgroundImage, group, element, cssProperties, name) {

        this.ratio = -1;
        this.relativeRatio = 1;

        this.backgroundImage = backgroundImage;

        NextendSmartSliderResponsiveElement.prototype.constructor.call(this, responsive, group, element, cssProperties, name);

        backgroundImage.addResponsiveElement(this);
    };

    NextendSmartSliderResponsiveElementBackgroundImage.prototype = Object.create(NextendSmartSliderResponsiveElement.prototype);
    NextendSmartSliderResponsiveElementBackgroundImage.prototype.constructor = NextendSmartSliderResponsiveElementBackgroundImage;

    NextendSmartSliderResponsiveElementBackgroundImage.prototype.customLoad = function (cssProperties) {
        var parent = this.element.parent();
        // The images doesn't have their original(not the real dimension, it is the place
        // what was taken right after the load) width and height values in the future.
        // So we will calculate the original size from the parent element size
        // We will assume that the image was 100% width to its parent
        this.helper.parentProps = {
            width: parent.width(),
            height: parent.height()
        }
        this.backgroundImage.afterLoaded().done($.proxy(function () {
            this._lateInitIMG(cssProperties);
        }, this));
    };

    NextendSmartSliderResponsiveElementBackgroundImage.prototype._lateInitIMG = function (cssProperties, e) {
        if (this.backgroundImage.mode == 'fill' || this.backgroundImage.mode == 'fit' || this.backgroundImage.mode == 'simple') {
            this.refreshRatio();
            if (!this.responsive.slider.parameters.dynamicHeight) {
                this.setCentered();
            }
        }

        this._lateInit(cssProperties);
    };

    NextendSmartSliderResponsiveElementBackgroundImage.prototype.afterLoaded = function () {
        if (this.backgroundImage.mode == 'fill' || this.backgroundImage.mode == 'fit' || this.backgroundImage.mode == 'simple') {
            this.refreshRatio();
            if (!this.responsive.slider.parameters.dynamicHeight) {
                this.setCentered();
            }
        }
    };

    NextendSmartSliderResponsiveElementBackgroundImage.prototype._resize = function (responsiveDimensions, ratio, timeline, duration) {
        if (this.responsive.slider.parameters.dynamicHeight) {
            this.element.css({
                width: '100%',
                height: '100%'
            });
        } else {
            var slideOuter = responsiveDimensions.slideouter || responsiveDimensions.slide;

            var slideOuterRatio = slideOuter.width / slideOuter.height;
            if (this.backgroundImage.mode == 'fill') {
                if (slideOuterRatio > this.ratio) {
                    this.element.css({
                        width: '100%',
                        height: 'auto'
                    });
                } else {
                    this.element.css({
                        width: 'auto',
                        height: '100%'
                    });
                }
            } else if (this.backgroundImage.mode == 'fit') {
                if (slideOuterRatio < this.ratio) {
                    this.element.css({
                        width: '100%',
                        height: 'auto'
                    });
                } else {
                    this.element.css({
                        width: 'auto',
                        height: '100%'
                    });
                }
            }
        }

        NextendSmartSliderResponsiveElement.prototype._resize.call(this, responsiveDimensions, ratio, timeline, duration);
    };

    NextendSmartSliderResponsiveElementBackgroundImage.prototype.refreshRatio = function () {
        var w = this.element.prop('naturalWidth'),
            h = this.element.prop('naturalHeight');
        this.ratio = w / h;
        var slideW = this.responsive.responsiveDimensions.startSlideWidth,
            slideH = this.responsive.responsiveDimensions.startSlideHeight;
        this.relativeRatio = (slideW / slideH) / this.ratio;
    };

    scope.NextendSmartSliderResponsiveElementBackgroundImage = NextendSmartSliderResponsiveElementBackgroundImage;

})(n2, window);
(function ($, scope, undefined) {

    function NextendSmartSliderVimeoItem(slider, id, sliderid, parameters, hasImage) {
        this.readyDeferred = $.Deferred();

        this.slider = slider;
        this.playerId = id;

        this.parameters = $.extend({
            vimeourl: "//vimeo.com/144598279",
            center: 0,
            autoplay: "0",
            reset: "0",
            title: "1",
            byline: "1",
            portrait: "0",
            loop: "0",
            color: "00adef",
            volume: "-1"
        }, parameters);

        if (navigator.userAgent.toLowerCase().indexOf("android") > -1) {
            this.parameters.autoplay = 0;
        }

        if (this.parameters.autoplay == 1 || !hasImage) {
            this.ready($.proxy(this.initVimeoPlayer, this));
        } else {
            $("#" + this.playerId).on('click', $.proxy(function () {
                this.ready($.proxy(function () {
                    this.readyDeferred.done($.proxy(function () {
                        this.play();
                    }, this));
                    this.initVimeoPlayer();
                }, this));
            }, this));
        }
    };

    NextendSmartSliderVimeoItem.vimeoDeferred = null;

    NextendSmartSliderVimeoItem.prototype.ready = function (callback) {
        if (NextendSmartSliderVimeoItem.vimeoDeferred === null) {
            NextendSmartSliderVimeoItem.vimeoDeferred = $.getScript((window.location.protocol == "https:" ? 'https://secure-a.vimeocdn.com/js/froogaloop2.min.js' : 'http://a.vimeocdn.com/js/froogaloop2.min.js'));
        }
        NextendSmartSliderVimeoItem.vimeoDeferred.done(callback);
    };

    NextendSmartSliderVimeoItem.prototype.initVimeoPlayer = function () {
        var playerElement = n2('<iframe id="' + this.playerId + '_video" src="//player.vimeo.com/video/' + this.parameters.vimeocode + '?api=1&autoplay=0&player_id=' + this.playerId +
        '_video&title=' + this.parameters.title + '&byline=' + this.parameters.byline + '&portrait=' + this.parameters.portrait + '&color=' + this.parameters.color +
        '&loop=' + this.parameters.loop + '" style="position: absolute; top:0; left: 0; width: 100%; height: 100%;" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>');
        $("#" + this.playerId).append(playerElement);

        this.player = $f(playerElement[0]);
        this.playerElement = $(this.player.element);
        this.player.addEvent('ready', $.proxy(this.onReady, this));
    };

    NextendSmartSliderVimeoItem.prototype.onReady = function () {
        var volume = parseFloat(this.parameters.volume);
        if (volume >= 0) {
            this.setVolume(volume);
        }

        this.slideIndex = this.slider.findSlideIndexByElement(this.playerElement);

        if (this.parameters.center == 1) {
            this.onResize();

            this.slider.sliderElement.on('SliderResize', $.proxy(this.onResize, this))
        }
        var layer = this.playerElement.parent().parent();

        this.player.addEvent('play', $.proxy(function () {
            this.slider.sliderElement.trigger('mediaStarted', this);
            layer.triggerHandler('n2play');
        }, this));

        this.player.addEvent('pause', $.proxy(function () {
            layer.triggerHandler('n2pause');
        }));

        this.player.addEvent('finish', $.proxy(function () {
            this.slider.sliderElement.trigger('mediaEnded', this);
            layer.triggerHandler('n2stop');
        }, this));

        //pause video when slide changed
        this.slider.sliderElement.on("mainAnimationStart", $.proxy(function (e, mainAnimation, previousSlideIndex, currentSlideIndex, isSystem) {
            if (currentSlideIndex != this.slideIndex) {
                if (parseInt(this.parameters.reset)) {
                    this.reset();
                } else {
                    this.pause();
                }
            }
        }, this));

        if (this.parameters.autoplay == 1) {
            this.slider.visible($.proxy(this.initAutoplay, this));
        }
        this.readyDeferred.resolve();
    };

    NextendSmartSliderVimeoItem.prototype.onResize = function () {
        var controls = 52,
            parent = this.playerElement.parent(),
            width = parent.width() + controls,
            height = parent.height() + controls,
            aspectRatio = 16 / 9,
            css = {
                width: width,
                height: height,
                marginLeft: 0,
                marginTop: 0
            };
        if (width / height > aspectRatio) {
            css.height = width * aspectRatio;
            css.marginTop = (height - css.height) / 2;
        } else {
            css.width = height * aspectRatio;
            css.marginLeft = (width - css.width) / 2;
        }
        this.playerElement.css(css);
    };

    NextendSmartSliderVimeoItem.prototype.initAutoplay = function () {

        //change slide
        this.slider.sliderElement.on("mainAnimationComplete", $.proxy(function (e, mainAnimation, previousSlideIndex, currentSlideIndex, isSystem) {
            if (currentSlideIndex == this.slideIndex) {
                this.play();
            }
        }, this));

        if (this.slider.currentSlideIndex == this.slideIndex) {
            this.play();
        }
    };

    NextendSmartSliderVimeoItem.prototype.play = function () {
        this.slider.sliderElement.trigger('mediaStarted', this);
        this.player.api("play");
    };

    NextendSmartSliderVimeoItem.prototype.pause = function () {
        this.player.api("pause");
    };

    NextendSmartSliderVimeoItem.prototype.reset = function () {
        this.player.api("seekTo", 0);
    };

    NextendSmartSliderVimeoItem.prototype.setVolume = function (volume) {
        this.player.api('setVolume', volume);
    };

    scope.NextendSmartSliderVimeoItem = NextendSmartSliderVimeoItem;

})(n2, window);
(function ($, scope, undefined) {

    function NextendSmartSliderYouTubeItem(slider, id, parameters, hasImage) {
        this.readyDeferred = $.Deferred();
        this.slider = slider;
        this.playerId = id;

        this.parameters = $.extend({
            youtubeurl: "//www.youtube.com/watch?v=MKmIwHAFjSU",
            youtubecode: "MKmIwHAFjSU",
            center: 0,
            autoplay: "1",
            theme: "dark",
            related: "1",
            vq: "default",
            volume: "-1",
            loop: 0
        }, parameters);

        if (navigator.userAgent.toLowerCase().indexOf("android") > -1) {
            this.parameters.autoplay = 0;
        }

        if (this.parameters.autoplay == 1 || !hasImage) {
            this.ready($.proxy(this.initYoutubePlayer, this));
        } else {
            $("#" + this.playerId).on('click', $.proxy(function () {
                this.ready($.proxy(function () {
                    this.readyDeferred.done($.proxy(function () {
                        this.play();
                    }, this));
                    this.initYoutubePlayer();
                }, this));
            }, this));
        }
    }

    NextendSmartSliderYouTubeItem.YTDeferred = null;
    NextendSmartSliderYouTubeItem.prototype.ready = function (callback) {
        if (NextendSmartSliderYouTubeItem.YTDeferred === null) {
            NextendSmartSliderYouTubeItem.YTDeferred = $.Deferred();
            window.onYouTubeIframeAPIReady = $.proxy(NextendSmartSliderYouTubeItem.YTDeferred.resolve, NextendSmartSliderYouTubeItem.YTDeferred);
            $.getScript("//www.youtube.com/iframe_api");
        }
        NextendSmartSliderYouTubeItem.YTDeferred.done(callback);
    };


    NextendSmartSliderYouTubeItem.prototype.initYoutubePlayer = function () {
        var player = $("#" + this.playerId);
        var layer = player.closest(".n2-ss-layer");

        var vars = {
            enablejsapi: 1,
            origin: window.location.protocol + "//" + window.location.host,
            theme: this.parameters.theme,
            modestbranding: 1,
            wmode: "opaque",
            rel: this.parameters.related,
            vq: this.parameters.vq,
            start: this.parameters.start
        };

        if (this.parameters.center == 1) {
            vars.controls = 0;
            vars.showinfo = 0;
        }
        if (this.parameters.controls != 1) {
            vars.autohide = 1;
            vars.controls = 0;
            vars.showinfo = 0;
        }

        if (+(navigator.platform.toUpperCase().indexOf('MAC') >= 0 && navigator.userAgent.search("Firefox") > -1))
            vars.html5 = 1;

        this.player = new YT.Player(this.playerId, {
            videoId: this.parameters.youtubecode,
            wmode: 'opaque',
            playerVars: vars,
            events: {
                onReady: $.proxy(this.onReady, this),
                onStateChange: $.proxy(function (state) {
                    switch (state.data) {
                        case YT.PlayerState.PLAYING:
                            this.slider.sliderElement.trigger('mediaStarted', this);
                            layer.triggerHandler('n2play');
                            break;
                        case YT.PlayerState.PAUSED:
                            layer.triggerHandler('n2pause');
                            break;
                        case YT.PlayerState.ENDED:
                            if (this.parameters.loop == 1) {
                                this.player.seekTo(0);
                                this.player.playVideo();
                            } else {
                                this.slider.sliderElement.trigger('mediaEnded', this);
                                layer.triggerHandler('n2stop');
                            }
                            break;

                    }
                }, this)
            }
        });

        this.playerElement = $("#" + this.playerId);

        this.slideIndex = this.slider.findSlideIndexByElement(this.playerElement);
        if (this.parameters.center == 1) {
            this.onResize();

            this.slider.sliderElement.on('SliderResize', $.proxy(this.onResize, this))
        }

    };

    NextendSmartSliderYouTubeItem.prototype.onReady = function (state) {

        var volume = parseFloat(this.parameters.volume);
        if (volume >= 0) {
            this.setVolume(volume);
        }

        if (this.parameters.autoplay == 1) {
            this.slider.visible($.proxy(this.initAutoplay, this));
        }

        //pause video when slide changed
        this.slider.sliderElement.on("mainAnimationStart", $.proxy(function (e, mainAnimation, previousSlideIndex, currentSlideIndex, isSystem) {
            if (currentSlideIndex != this.slideIndex) {
                this.pause();
            }
        }, this));
        this.readyDeferred.resolve();
    };

    NextendSmartSliderYouTubeItem.prototype.onResize = function () {
        var controls = 100,
            parent = this.playerElement.parent(),
            width = parent.width(),
            height = parent.height() + controls,
            aspectRatio = 16 / 9,
            css = {
                width: width,
                height: height,
                marginLeft: 0,
                marginTop: 0
            };
        if (width / height > aspectRatio) {
            css.height = width * aspectRatio;
            css.marginTop = (height - css.height) / 2;
        } else {
            css.width = height * aspectRatio;
            css.marginLeft = (width - css.width) / 2;
        }
        this.playerElement.css(css);
    };

    NextendSmartSliderYouTubeItem.prototype.initAutoplay = function () {

        //change slide
        this.slider.sliderElement.on("mainAnimationComplete", $.proxy(function (e, mainAnimation, previousSlideIndex, currentSlideIndex, isSystem) {
            if (currentSlideIndex == this.slideIndex) {
                this.play();
            }
        }, this));

        if (this.slider.currentSlideIndex == this.slideIndex) {
            this.play();
        }
    };

    NextendSmartSliderYouTubeItem.prototype.play = function () {
        if (this.isStopped()) {
            this.slider.sliderElement.trigger('mediaStarted', this);
            this.player.playVideo();
        }
    };

    NextendSmartSliderYouTubeItem.prototype.pause = function () {
        if (!this.isStopped()) {
            this.player.pauseVideo();
        }
    };

    NextendSmartSliderYouTubeItem.prototype.stop = function () {
        this.player.stopVideo();
    };

    NextendSmartSliderYouTubeItem.prototype.isStopped = function () {
        var state = this.player.getPlayerState();
        switch (state) {
            case -1:
            case 0:
            case 2:
            case 5:
                return true;
                break;
            default:
                return false;
                break;
        }
    };

    NextendSmartSliderYouTubeItem.prototype.setVolume = function (volume) {
        this.player.setVolume(volume * 100);
    };

    scope.NextendSmartSliderYouTubeItem = NextendSmartSliderYouTubeItem;

})(n2, window);
