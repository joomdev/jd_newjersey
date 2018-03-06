var NextendSmartSliderAdminStorage = function () {
    /** @type {NextendSmartSliderAdminTimelineManager} */
    this.timelineManager = null;
    /** @type {NextendSmartSliderAdminTimelineControl} */
    this.timelineControl = null;
    /** @type {SmartSliderAdminSlide} */
    this.slide = null;
    /** @type {NextendSmartSliderAbstract} */
    this.frontend = null;
    /** @type {SmartSliderAdminGenerator} */
    this.generator = null;
    /** @type {NextendSmartSliderAdminSlideLayerManager} */
    this.layerManager = null;
    /** @type {NextendSmartSliderAdminLayoutHistory} */
    this.history = null;


    this.oneSecWidth = 200;
    this.oneSecMs = 1000;
    this.fps = 20;
    this.pxToFrame = this.oneSecWidth / this.fps;

    this.$currentSlideElement = null;
};

NextendSmartSliderAdminStorage.prototype.durationToOffsetX = function (sec) {
    return sec * this.oneSecWidth;
};

NextendSmartSliderAdminStorage.prototype.offsetXToDuration = function (px) {
    return px / this.oneSecWidth;
};

NextendSmartSliderAdminStorage.prototype.normalizeOffsetX = function (offsetX) {
    return Math.round(offsetX / this.pxToFrame) * this.pxToFrame;
};


NextendSmartSliderAdminStorage.prototype.startEditor = function (sliderElementID, slideContentElementID, isUploadDisabled, uploadUrl, uploadDir) {
    if (this.slide === null) {
        new SmartSliderAdminSlide(sliderElementID, slideContentElementID, isUploadDisabled, uploadUrl, uploadDir);
    }
    return this.slide;
};

window.nextend.pre = 'div#n2-ss-0 ';
window.nextend.smartSlider = new NextendSmartSliderAdminStorage();
;
(function (smartSlider, $, scope) {

    function NextendBackgroundAnimationManager() {
        this.type = 'backgroundanimation';
        NextendVisualManagerMultipleSelection.prototype.constructor.apply(this, arguments);
    };

    NextendBackgroundAnimationManager.prototype = Object.create(NextendVisualManagerMultipleSelection.prototype);
    NextendBackgroundAnimationManager.prototype.constructor = NextendBackgroundAnimationManager;

    NextendBackgroundAnimationManager.prototype.loadDefaults = function () {
        NextendVisualManagerMultipleSelection.prototype.loadDefaults.apply(this, arguments);
        this.type = 'backgroundanimation';
        this.labels = {
            visual: 'Background animation',
            visuals: 'Background animations'
        };
    };

    NextendBackgroundAnimationManager.prototype.initController = function () {
        return new NextendBackgroundAnimationEditorController();
    };

    NextendBackgroundAnimationManager.prototype.createVisual = function (visual, set) {
        return new NextendVisualWithSetRowMultipleSelection(visual, set, this);
    };

    scope.NextendBackgroundAnimationManager = NextendBackgroundAnimationManager;

})(nextend.smartSlider, n2, window);

;
(function ($, scope) {

    function NextendBackgroundAnimationEditorController() {
        this.parameters = {
            shiftedBackgroundAnimation: 0
        };
        NextendVisualEditorController.prototype.constructor.call(this, false);

        this.bgAnimationElement = $('.n2-bg-animation');
        this.slides = $('.n2-bg-animation-slide');
        this.bgImages = $('.n2-bg-animation-slide-bg');
        NextendTween.set(this.bgImages, {
            rotationZ: 0.0001
        });

        this.directionTab = new NextendElementRadio('n2-background-animation-preview-tabs', ['0', '1']);
        this.directionTab.element.on('nextendChange.n2-editor', $.proxy(this.directionTabChanged, this));

        if (!nModernizr.csstransforms3d || !nModernizr.csstransformspreserve3d) {
            nextend.notificationCenter.error('Background animations are not available in your browser. It works if the <i>transform-style: preserve-3d</i> feature available. ')
        }
    };

    NextendBackgroundAnimationEditorController.prototype = Object.create(NextendVisualEditorController.prototype);
    NextendBackgroundAnimationEditorController.prototype.constructor = NextendBackgroundAnimationEditorController;

    NextendBackgroundAnimationEditorController.prototype.loadDefaults = function () {
        NextendVisualEditorController.prototype.loadDefaults.call(this);
        this.type = 'backgroundanimation';
        this.current = 0;
        this.animationProperties = false;
        this.direction = 0;
    };

    NextendBackgroundAnimationEditorController.prototype.get = function () {
        return null;
    };

    NextendBackgroundAnimationEditorController.prototype.load = function (visual, tabs, mode, preview) {
        this.lightbox.addClass('n2-editor-loaded');
    };

    NextendBackgroundAnimationEditorController.prototype.setTabs = function (labels) {

    };

    NextendBackgroundAnimationEditorController.prototype.directionTabChanged = function () {
        this.direction = parseInt(this.directionTab.element.val());
    };

    NextendBackgroundAnimationEditorController.prototype.start = function () {
        if (this.animationProperties) {
            if (!this.timeline) {
                this.next();
            } else {
                this.timeline.play();
            }
        }
    };

    NextendBackgroundAnimationEditorController.prototype.pause = function () {
        if (this.timeline) {
            this.timeline.pause();
        }
    };

    NextendBackgroundAnimationEditorController.prototype.next = function () {
        this.timeline = new NextendTimeline({
            paused: true,
            onComplete: $.proxy(this.ended, this)
        });
        var current = this.bgImages.eq(this.current),
            next = this.bgImages.eq(1 - this.current);

        if (nModernizr.csstransforms3d && nModernizr.csstransformspreserve3d) {
            this.currentAnimation = new window['NextendSmartSliderBackgroundAnimation' + this.animationProperties.type](this, current, next, this.animationProperties, 1, this.direction);

            this.slides.eq(this.current).css('zIndex', 2);
            this.slides.eq(1 - this.current).css('zIndex', 3);

            this.timeline.to(this.slides.eq(this.current), 0.5, {
                opacity: 0
            }, this.currentAnimation.getExtraDelay());

            this.timeline.to(this.slides.eq(1 - this.current), 0.5, {
                opacity: 1
            }, this.currentAnimation.getExtraDelay());


            this.currentAnimation.postSetup();

        } else {

            this.timeline.to(this.slides.eq(this.current), 1.5, {
                opacity: 0
            }, 0);

            this.timeline.to(this.slides.eq(1 - this.current), 1.5, {
                opacity: 1
            }, 0);
        }
        this.current = 1 - this.current;
        this.timeline.play();
    };

    NextendBackgroundAnimationEditorController.prototype.ended = function () {
        if (this.currentAnimation) {
            this.currentAnimation.ended();
        }
        this.next();
    };

    NextendBackgroundAnimationEditorController.prototype.setAnimationProperties = function (animationProperties) {
        var lastAnimationProperties = this.animationProperties;
        this.animationProperties = animationProperties;
        if (!lastAnimationProperties) {
            this.next();
        }
    };

    scope.NextendBackgroundAnimationEditorController = NextendBackgroundAnimationEditorController;

})
(n2, window);

(function ($, scope) {

    function NextendSmartSliderCreateSlider(ajaxUrl) {
        this.createSliderModal = null;
        this.ajaxUrl = ajaxUrl;
        $('.n2-ss-create-slider').click($.proxy(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.showModal();
        }, this));

        this.notificationStack = new NextendNotificationCenterStackModal($('body'));
        $('.n2-ss-demo-slider').click($.proxy(function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            this.showDemoSliders();
        

        }, this));
    }

    NextendSmartSliderCreateSlider.prototype.showModal = function () {
        if (!this.createSliderModal) {
            var ajaxUrl = this.ajaxUrl;
            var presets = [];

            presets.push({
                key: 'default',
                name: n2_('Default'),
                image: '$ss$/admin/images/sliderpresets/default.png'
            });
            presets.push({
                key: 'thumbnailhorizontal',
                name: n2_('Thumbnail - horizontal'),
                image: '$ss$/admin/images/sliderpresets/thumbnailhorizontal.png'
            });
            presets.push({
                key: 'caption',
                name: n2_('Caption'),
                image: '$ss$/admin/images/sliderpresets/caption.png'
            });
            this.createSliderModal = new NextendModal({
                zero: {
                    size: [
                        N2SSPRO ? 750 : 580,
                        N2SSPRO ? 630 : 390
                    ],
                    title: n2_('Create Slider'),
                    back: false,
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: [
                        '<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Create') + '</a>'
                    ],
                    fn: {
                        show: function () {

                            var button = this.controls.find('.n2-button-green'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                });

                            form.append(this.createInput(n2_('Slider name'), 'slidertitle', 'width: 240px;'));
                            form.append(this.createInputUnit(n2_('Width'), 'sliderwidth', 'px', 'width: 30px;'));
                            form.append(this.createInputUnit(n2_('Height'), 'sliderheight', 'px', 'width: 30px;'));

                            new NextendElementAutocompleteSimple("sliderwidth", ["1920", "1400", "1000", "800", "600", "400"]);
                            new NextendElementAutocompleteSimple("sliderheight", ["800", "600", "500", "400", "300", "200"]);

                            var sliderTitle = $('#slidertitle').val(n2_('Slider')).focus(),
                                sliderWidth = $('#sliderwidth').val(900),
                                sliderHeight = $('#sliderheight').val(500);

                            sliderWidth.parent().addClass('n2-form-element-autocomplete ui-front');
                            sliderHeight.parent().addClass('n2-form-element-autocomplete ui-front');

                            this.createHeading(n2_('Preset')).appendTo(this.content);

                            var imageRadio = this.createImageRadio(presets)
                                    .css('height', N2SSPRO ? 360 : 240)
                                    .appendTo(this.content),
                                sliderPreset = imageRadio.find('input');

                            button.on('click', $.proxy(function () {

                                NextendAjaxHelper.ajax({
                                    type: "POST",
                                    url: NextendAjaxHelper.makeAjaxUrl(ajaxUrl, {
                                        nextendaction: 'create'
                                    }),
                                    data: {
                                        sliderTitle: sliderTitle.val(),
                                        sliderSizeWidth: sliderWidth.val(),
                                        sliderSizeHeight: sliderHeight.val(),
                                        preset: sliderPreset.val()
                                    },
                                    dataType: 'json'
                                }).done($.proxy(function (response) {
                                    NextendAjaxHelper.startLoading();
                                }, this));

                            }, this));
                        }
                    }
                }
            });
        }
        this.createSliderModal.show();
    };

    NextendSmartSliderCreateSlider.prototype.showDemoSliders = function () {
        var that = this;
        $('body').css('overflow', 'hidden');
        var frame = $('<iframe src="//smartslider3.com/demo-import/?pro=' + (N2SSPRO ? '1' : '0') + '" frameborder="0"></iframe>').css({
                position: 'fixed',
                zIndex: 100000,
                left: 0,
                top: 0,
                width: '100%',
                height: '100%'
            }).appendTo('body'),
            closeFrame = function () {
                $('body').css('overflow', '');
                frame.remove();
                window.removeEventListener("message", listener, false);
                that.notificationStack.popStack();
            },
            listener = function (e) {
                if (e.origin !== "http://smartslider3.com" && e.origin !== "https://smartslider3.com")
                    return;
                var msg = e.data;
                switch (msg.key) {
                    case 'importSlider':
                        NextendAjaxHelper.ajax({
                            type: "POST",
                            url: NextendAjaxHelper.makeAjaxUrl(that.ajaxUrl, {
                                nextendaction: 'importDemo'
                            }),
                            data: {
                                key: Base64.encode(msg.data.href.replace(/^(http(s)?:)?\/\//, '//'))
                            },
                            dataType: 'json'
                        }).fail(function () {
                            //closeFrame();
                        });
                        break;
                    case 'closeWindow':
                        closeFrame();
                }
            };

        this.notificationStack.enableStack();
        NextendEsc.add($.proxy(function () {
            closeFrame();
            return true;
        }, this));

        window.addEventListener("message", listener, false);
    };

    scope.NextendSmartSliderCreateSlider = NextendSmartSliderCreateSlider;

})(n2, window);
function strip_tags(input, allowed) {
    allowed = (((allowed || '') + '')
        .toLowerCase()
        .match(/<[a-z][a-z0-9]*>/g) || [])
        .join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '')
        .replace(tags, function ($0, $1) {
            return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
        });
}

if (!Array.prototype.filter) {
    Array.prototype.filter = function (fun /*, thisp */) {
        "use strict";

        if (this === void 0 || this === null)
            throw new TypeError();

        var t = Object(this);
        var len = t.length >>> 0;
        if (typeof fun !== "function")
            throw new TypeError();

        var res = [];
        var thisp = arguments[1];
        for (var i = 0; i < len; i++) {
            if (i in t) {
                var val = t[i]; // in case fun mutates this
                if (fun.call(thisp, val, i, t))
                    res.push(val);
            }
        }

        return res;
    };
}
(function ($, scope, undefined) {

    function NextendSmartSliderAdminInlineField() {

        this.$input = $('<input type="text" name="name" />')
            .on({
                mouseup: function (e) {
                    e.stopPropagation();
                },
                keyup: $.proxy(function (e) {
                    if (e.keyCode == 27) {
                        this.cancel();
                    }
                }, this),
                blur: $.proxy(this.save, this)
            });

        this.$form = $('<form class="n2-inline-form"></form>')
            .append(this.$input)
            .on('submit', $.proxy(this.save, this));
    }

    NextendSmartSliderAdminInlineField.prototype.injectNode = function ($targetNode, value) {
        this.$input.val(value);
        $targetNode.append(this.$form);
        this.$input.focus();
    };

    NextendSmartSliderAdminInlineField.prototype.save = function (e) {
        e.preventDefault();
        this.$input.trigger('valueChanged', [this.$input.val()]);
        this.$input.off('blur');
        this.destroy();
    };

    NextendSmartSliderAdminInlineField.prototype.cancel = function () {
        this.$input.trigger('cancel');
        this.destroy();
    };

    NextendSmartSliderAdminInlineField.prototype.destroy = function () {
        this.$input.off('blur')
        this.$form.remove();
    };

    scope.NextendSmartSliderAdminInlineField = NextendSmartSliderAdminInlineField;

})(n2, window);




(function ($, scope, undefined) {

    function NextendSmartSliderAdminSidebarSlides(ajaxUrl, contentAjaxUrl, parameters, isUploadDisabled, uploadUrl, uploadDir) {
        this.quickPostModal = null;
        this.quickVideoModal = null;
        this.parameters = parameters;
        this.slides = [];
        this.ajaxUrl = ajaxUrl;
        this.contentAjaxUrl = contentAjaxUrl;
        this.slidesPanel = $('#n2-ss-slides');
        this.slidesContainer = this.slidesPanel.find('.n2-ss-slides-container');

        this.initSlidesOrderable();

        var slides = this.slidesContainer.find('.n2-box-slide');
        for (var i = 0; i < slides.length; i++) {
            this.slides.push(new NextendSmartSliderAdminSlide(this, slides.eq(i)));
        }

        if (this.slides.length > 0) {
            this.slidesPanel.addClass('n2-ss-has-slides');
        }

        $('.n2-add-quick-image').on('click', $.proxy(this.addQuickImage, this));
        $('.n2-box-slide-add').on('click', $.proxy(this.addQuickImage, this));
        $('.n2-add-quick-video').on('click', $.proxy(this.addQuickVideo, this));
        $('.n2-add-quick-post').on('click', $.proxy(this.addQuickPost, this));

        this.initBulk();

        if ($('#n2-ss-slide-editor-main-tab').length == 0) {
            new NextendSmartSliderSidebarSlides();
        }


        if (!isUploadDisabled) {
            var images = [];
            this.slidesContainer.fileupload({
                url: uploadUrl,
                pasteZone: false,
                dropZone: this.slidesContainer,
                dataType: 'json',
                paramName: 'image',

                add: $.proxy(function (e, data) {
                    data.formData = {path: '/' + uploadDir};
                    data.submit();
                }, this),

                done: $.proxy(function (e, data) {
                    var response = data.result;
                    if (response.data && response.data.name) {
                        images.push({
                            title: response.data.name,
                            description: '',
                            image: response.data.url
                        });
                    } else {
                        NextendAjaxHelper.notification(response);
                    }

                }, this),

                fail: $.proxy(function (e, data) {
                    NextendAjaxHelper.notification(data.jqXHR.responseJSON);
                }, this),

                start: function () {
                    NextendAjaxHelper.startLoading();
                },

                stop: $.proxy(function () {
                    if (images.length) {
                        this._addQuickImages(images);
                    } else {
                        setTimeout(function () {
                            NextendAjaxHelper.stopLoading();
                        }, 100);
                    }
                    images = [];
                }, this)
            });

            var timeout = null;
            this.slidesContainer.on('dragover', $.proxy(function (e) {
                if (timeout !== null) {
                    clearTimeout(timeout);
                    timeout = null;
                } else {
                    this.slidesContainer.addClass('n2-drag-over');
                }
                timeout = setTimeout($.proxy(function () {
                    this.slidesContainer.removeClass('n2-drag-over');
                    timeout = null;
                }, this), 400);

            }, this));
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.changed = function () {
        if (this.slides.length > 0) {
            this.slidesPanel.addClass('n2-ss-has-slides');
        } else {
            this.slidesPanel.removeClass('n2-ss-has-slides');
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.initSlidesOrderable = function () {
        this.slidesContainer.sortable({
            items: ".n2-box-slide",
            tolerance: 'pointer',
            stop: $.proxy(this.saveSlideOrder, this),
            helper: 'clone',
            placeholder: 'n2-box-placeholder n2-box'
        });
    };

    NextendSmartSliderAdminSidebarSlides.prototype.saveSlideOrder = function (e) {
        var slideNodes = this.slidesContainer.find('.n2-box-slide'),
            slides = [],
            ids = [],
            originalIds = [];
        for (var i = 0; i < slideNodes.length; i++) {
            var slide = slideNodes.eq(i).data('slide');
            slides.push(slide);
            ids.push(slide.getId());
        }
        for (var i = 0; i < this.slides.length; i++) {
            originalIds.push(this.slides[i].getId());
        }

        if (JSON.stringify(originalIds) != JSON.stringify(ids)) {
            $(window).triggerHandler('SmartSliderSidebarSlidesOrderChanged');
            var queries = {
                nextendcontroller: 'slides',
                nextendaction: 'order'
            };
            NextendAjaxHelper.ajax({
                type: 'POST',
                url: NextendAjaxHelper.makeAjaxUrl(this.ajaxUrl, queries),
                data: {
                    slideorder: ids
                }
            });
            this.slides = slides;
            this.changed();
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.initSlides = function () {
        var previousLength = this.slides.length;
        var slideNodes = this.slidesContainer.find('.n2-box-slide'),
            slides = [];
        for (var i = 0; i < slideNodes.length; i++) {
            var slide = slideNodes.eq(i).data('slide');
            slides.push(slide);
        }
        this.slides = slides;
        this.changed();
        $(window).triggerHandler('SmartSliderSidebarSlidesChanged');
    };

    NextendSmartSliderAdminSidebarSlides.prototype.unsetFirst = function () {
        for (var i = 0; i < this.slides.length; i++) {
            this.slides[i].unsetFirst();
        }
        this.changed();
    };

    NextendSmartSliderAdminSidebarSlides.prototype.addQuickImage = function (e) {
        e.preventDefault();
        nextend.imageHelper.openMultipleLightbox($.proxy(this._addQuickImages, this));
    };

    NextendSmartSliderAdminSidebarSlides.prototype._addQuickImages = function (images) {
        NextendAjaxHelper.ajax({
            type: 'POST',
            url: NextendAjaxHelper.makeAjaxUrl(this.ajaxUrl, {
                nextendaction: 'quickImages'
            }),
            data: {
                images: Base64.encode(JSON.stringify(images))
            }
        }).done($.proxy(function (response) {
            var boxes = $(response.data).insertBefore(this.slidesContainer.find('.n2-clear'));
            boxes.each($.proxy(function (i, el) {
                new NextendSmartSliderAdminSlide(this, $(el));
            }, this));
            this.initSlides();
        }, this));
    };

    NextendSmartSliderAdminSidebarSlides.prototype.addQuickVideo = function (e) {
        e.preventDefault();
        var manager = this;
        if (!this.quickVideoModal) {
            this.quickVideoModal = new NextendModal({
                zero: {
                    size: [
                        500,
                        350
                    ],
                    title: n2_('Add video'),
                    back: false,
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Add video') + '</a>'],
                    fn: {
                        show: function () {
                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Video url'), 'n2-slide-video-url', 'width: 446px;')),
                                videoUrlField = this.content.find('#n2-slide-video-url').focus();

                            this.content.append(this.createHeading(n2_('Examples')));
                            this.content.append(this.createTable([['YouTube', 'https://www.youtube.com/watch?v=MKmIwHAFjSU'], ['Vimeo', 'https://vimeo.com/144598279']], ['', '']));

                            button.on('click', $.proxy($.proxy(function (e) {
                                e.preventDefault();
                                var video = videoUrlField.val(),
                                    youtubeRegexp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/,
                                    youtubeMatch = video.match(youtubeRegexp),
                                    vimeoRegexp = /https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/,
                                    vimeoMatch = video.match(vimeoRegexp);

                                if (youtubeMatch) {
                                    NextendAjaxHelper.getJSON('https://www.googleapis.com/youtube/v3/videos?id=' + encodeURI(youtubeMatch[2]) + '&part=snippet&key=AIzaSyC3AolfvPAPlJs-2FgyPJdEEKS6nbPHdSM').done($.proxy(function (data) {
                                        if (data.items.length) {
                                            var snippet = data.items[0].snippet;

                                            var thumbnails = data.items[0].snippet.thumbnails,
                                                thumbnail = thumbnails.maxres || thumbnails.standard || thumbnails.high || thumbnails.medium || thumbnails.default;

                                            manager._addQuickVideo(this, {
                                                type: 'youtube',
                                                title: snippet.title,
                                                description: snippet.description,
                                                image: thumbnail.url,
                                                video: youtubeMatch[2]
                                            });
                                        }
                                    }, this)).fail(function (data) {
                                        nextend.notificationCenter.error(data.error.errors[0].message);
                                    });
                                } else if (vimeoMatch) {
                                    NextendAjaxHelper.getJSON('https://vimeo.com/api/v2/video/' + vimeoMatch[3] + '.json').done($.proxy(function (data) {
                                        manager._addQuickVideo(this, {
                                            type: 'vimeo',
                                            title: data[0].title,
                                            description: data[0].description,
                                            video: vimeoMatch[3],
                                            image: data[0].thumbnail_large
                                        });
                                    }, this)).fail(function (data) {
                                        nextend.notificationCenter.error(data.responseText);
                                    });

                                } else {
                                    nextend.notificationCenter.error('This video url is not supported!');
                                }
                            }, this)));
                        }
                    }
                }
            });
        }
        this.quickVideoModal.show();
    };

    NextendSmartSliderAdminSidebarSlides.prototype._addQuickVideo = function (modal, video) {
        NextendAjaxHelper.ajax({
            type: 'POST',
            url: NextendAjaxHelper.makeAjaxUrl(this.ajaxUrl, {
                nextendaction: 'quickVideo'
            }),
            data: {
                video: Base64.encode(JSON.stringify(video))
            }
        }).done($.proxy(function (response) {
            var box = $(response.data).insertBefore(this.slidesContainer.find('.n2-clear'));
            new NextendSmartSliderAdminSlide(this, box);

            this.initSlides();
        }, this));
        modal.hide();
    };

    NextendSmartSliderAdminSidebarSlides.prototype.addQuickPost = function (e) {
        e.preventDefault();
        if (!this.quickPostModal) {
            var manager = this,
                cache = {},
                getContent = $.proxy(function (search) {
                    if (typeof cache[search] == 'undefined') {
                        cache[search] = NextendAjaxHelper.ajax({
                            type: "POST",
                            url: NextendAjaxHelper.makeAjaxUrl(this.contentAjaxUrl),
                            data: {
                                keyword: search
                            },
                            dataType: 'json'
                        });
                    }
                    return cache[search];
                }, this);

            this.quickPostModal = new NextendModal({
                zero: {
                    size: [
                        600,
                        430
                    ],
                    title: n2_('Add post'),
                    back: false,
                    close: true,
                    content: '<div class="n2-form"></div>',
                    fn: {
                        show: function () {

                            this.content.find('.n2-form').append(this.createInput(n2_('Keyword'), 'n2-ss-keyword', 'width:546px;'));
                            var search = $('#n2-ss-keyword'),
                                heading = this.createHeading('').appendTo(this.content),
                                result = this.createResult().appendTo(this.content),
                                searchString = '';

                            search.on('keyup', $.proxy(function () {
                                searchString = search.val();
                                getContent(searchString).done($.proxy(function (r) {
                                    if (search.val() == searchString) {
                                        if (searchString == '') {
                                            heading.html(n2_('No search term specified. Showing recent items.'));
                                        } else {
                                            heading.html(n2_printf(n2_('Showing items match for "%s"'), searchString));
                                        }

                                        var rows = r.data,
                                            data = [],
                                            modal = this;
                                        for (var i = 0; i < rows.length; i++) {
                                            data.push([rows[i].title, rows[i].info, $('<div class="n2-button n2-button-green n2-button-x-small n2-uc n2-h5">' + n2_('Select') + '</div>')
                                                .on('click', {post: rows[i]}, function (e) {
                                                    manager._addQuickPost(modal, e.data.post);
                                                })]);
                                        }
                                        result.html('');
                                        this.createTable(data, ['width:100%;', '', '']).appendTo(this.createTableWrap().appendTo(result));
                                    }
                                }, this));
                            }, this))
                                .trigger('keyup').focus();
                        }
                    }
                }
            });
        }
        this.quickPostModal.show();
    };

    NextendSmartSliderAdminSidebarSlides.prototype._addQuickPost = function (modal, post) {
        if (!post.image) {
            post.image = '';
        }
        NextendAjaxHelper.ajax({
            type: 'POST',
            url: NextendAjaxHelper.makeAjaxUrl(this.ajaxUrl, {
                nextendaction: 'quickPost'
            }),
            data: {
                post: post
            }
        }).done($.proxy(function (response) {
            var box = $(response.data).insertBefore(this.slidesContainer.find('.n2-clear'));
            new NextendSmartSliderAdminSlide(this, box);

            this.initSlides();
        }, this));
        modal.hide();
    };

    NextendSmartSliderAdminSidebarSlides.prototype.initBulk = function () {
        $('.n2-slides-bulk').on('click', $.proxy(this.enterBulk, this));
        $('.n2-bulk-cancel').on('click', $.proxy(this.leaveBulk, this));

        var selects = $('.n2-bulk-select').find('a');

        // Invert
        selects.eq(0).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkSelect(function (slide) {
                slide.invertSelection();
            });
        }, this));

        //Select all
        selects.eq(1).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkSelect(function (slide) {
                slide.select();
            });
        }, this));

        //Select none
        selects.eq(2).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkSelect(function (slide) {
                slide.deSelect();
            });
        }, this));

        //Select published
        selects.eq(3).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkSelect(function (slide) {
                if (slide.publishElement.hasClass('n2-active')) {
                    slide.select();
                } else {
                    slide.deSelect();
                }
            });
        }, this));

        //Select unpublished
        selects.eq(4).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkSelect(function (slide) {
                if (slide.publishElement.hasClass('n2-active')) {
                    slide.deSelect();
                } else {
                    slide.select();
                }
            });
        }, this));

        var actions = $('.n2-bulk-action').find('a');

        //Delete
        actions.eq(0).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkAction('deleteSlides');
        }, this));

        //Duplicate
        actions.eq(1).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkAction('duplicateSlides');
        }, this));

        //Publish
        actions.eq(2).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkAction('publishSlides');
        }, this));

        //Unpublish
        actions.eq(3).on('click', $.proxy(function (e) {
            e.preventDefault();
            this.bulkAction('unPublishSlides');
        }, this));
    };

    NextendSmartSliderAdminSidebarSlides.prototype.bulkSelect = function (cb) {
        for (var i = 0; i < this.slides.length; i++) {
            cb(this.slides[i]);
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.bulkAction = function (action) {
        var slides = [],
            ids = [];
        this.bulkSelect(function (slide) {
            if (slide.selected) {
                slides.push(slide);
                ids.push(slide.getId());
            }
        });
        if (ids.length) {
            this[action](ids, slides);
        } else {
            nextend.notificationCenter.notice('Please select one or more slides for the action!');
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.enterBulk = function () {
        this.slidesContainer.sortable('option', 'disabled', true);
        $('#n2-admin').addClass('n2-slide-bulk-mode');

        for (var i = 0; i < this.slides.length; i++) {
            this.slides[i].selectMode();
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.leaveBulk = function () {
        this.slidesContainer.sortable('option', 'disabled', false);
        $('#n2-admin').removeClass('n2-slide-bulk-mode');

        for (var i = 0; i < this.slides.length; i++) {
            this.slides[i].normalMode();
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.deleteSlides = function (ids, slides) {
        var title = slides[0].box.find('.n2-box-button a').text();
        if (slides.length > 1) {
            title += ' and ' + (slides.length - 1) + ' more';
        }
        NextendDeleteModal('slide-delete', title, $.proxy(function () {
            NextendAjaxHelper.ajax({
                url: NextendAjaxHelper.makeAjaxUrl(this.ajaxUrl, {
                    nextendaction: 'delete'
                }),
                type: 'POST',
                data: {
                    slides: ids
                }
            }).done($.proxy(function () {
                for (var i = 0; i < slides.length; i++) {
                    slides[i].deleted();
                }
                this.initSlides();
            }, this));
        }, this));
    };

    NextendSmartSliderAdminSidebarSlides.prototype.duplicateSlides = function (ids, slides) {
        for (var i = 0; i < this.slides.length; i++) {
            if (this.slides[i].selected) {
                this.slides[i].duplicate($.Event("click", {
                    currentTarget: this.slides[i].box.find('.n2-slide-duplicate')
                })).done(function (slide) {
                    slide.selectMode();
                });
            }
        }
    };

    NextendSmartSliderAdminSidebarSlides.prototype.publishSlides = function (ids, slides) {
        NextendAjaxHelper.ajax({
            url: NextendAjaxHelper.makeAjaxUrl(this.ajaxUrl, {
                nextendaction: 'publish'
            }),
            type: 'POST',
            data: {
                slides: ids
            }
        }).done($.proxy(function () {
            for (var i = 0; i < slides.length; i++) {
                slides[i].published();
            }
            this.changed();
        }, this));
    };

    NextendSmartSliderAdminSidebarSlides.prototype.unPublishSlides = function (ids, slides) {
        NextendAjaxHelper.ajax({
            url: NextendAjaxHelper.makeAjaxUrl(this.ajaxUrl, {
                nextendaction: 'unpublish'
            }),
            type: 'POST',
            data: {
                slides: ids
            }
        }).done($.proxy(function () {
            for (var i = 0; i < slides.length; i++) {
                slides[i].unPublished();
            }
            this.changed();
        }, this));
    };
    scope.NextendSmartSliderAdminSidebarSlides = NextendSmartSliderAdminSidebarSlides;

    function NextendSmartSliderAdminSlide(manager, box) {
        this.selected = false;
        this.manager = manager;

        this.box = box.data('slide', this)
            .addClass('n2-clickable');
        this.normalMode();
        this.box.find('.n2-slide-first')
            .on('click', $.proxy(this.setFirst, this));
        this.publishElement = this.box.find('.n2-slide-published')
            .on('click', $.proxy(this.switchPublished, this));
        this.box.find('.n2-slide-duplicate')
            .on('click', $.proxy(this.duplicate, this));
        this.box.find('.n2-slide-delete')
            .on('click', $.proxy(this.delete, this));
    };

    NextendSmartSliderAdminSlide.prototype.getId = function () {
        return this.box.data('slideid');
    };
    NextendSmartSliderAdminSlide.prototype.setFirst = function (e) {
        e.stopPropagation();
        e.preventDefault();
        NextendAjaxHelper.ajax({
            url: NextendAjaxHelper.makeAjaxUrl(this.manager.ajaxUrl, {
                nextendaction: 'first'
            }),
            type: 'POST',
            data: {
                id: this.getId()
            }
        }).done($.proxy(function () {
            this.manager.unsetFirst();
            this.box.addClass('n2-first-slide');
        }, this));
    };
    NextendSmartSliderAdminSlide.prototype.unsetFirst = function () {
        this.box.removeClass('n2-first-slide');
    };

    NextendSmartSliderAdminSlide.prototype.switchPublished = function (e) {
        e.stopPropagation();
        e.preventDefault();
        if (this.isPublished()) {
            this.manager.unPublishSlides([this.getId()], [this]);
        } else {
            this.manager.publishSlides([this.getId()], [this]);
        }
    };

    NextendSmartSliderAdminSlide.prototype.isPublished = function () {
        return this.publishElement.hasClass('n2-active');
    };

    NextendSmartSliderAdminSlide.prototype.published = function () {
        this.publishElement.addClass('n2-active');
    };

    NextendSmartSliderAdminSlide.prototype.unPublished = function () {
        this.publishElement.removeClass('n2-active');
    };

    NextendSmartSliderAdminSlide.prototype.goToEdit = function (e) {
        window.location = this.box.data('editurl');
    };

    NextendSmartSliderAdminSlide.prototype.duplicate = function (e) {
        e.stopPropagation();
        e.preventDefault();
        var deferred = $.Deferred();
        NextendAjaxHelper.ajax({
            url: NextendAjaxHelper.makeAjaxUrl($(e.currentTarget).attr('href'), {
                nextendaction: 'duplicate'
            })
        }).done($.proxy(function (response) {
            var box = $(response.data).insertAfter(this.box);
            var newSlide = new NextendSmartSliderAdminSlide(this.manager, box);
            this.manager.initSlides();
            deferred.resolve(newSlide);
        }, this));
        return deferred;
    };

    NextendSmartSliderAdminSlide.prototype.delete = function (e) {
        e.stopPropagation();
        e.preventDefault();
        this.manager.deleteSlides([this.getId()], [this]);
    };
    NextendSmartSliderAdminSlide.prototype.deleted = function () {
        this.box.remove();
    };

    NextendSmartSliderAdminSlide.prototype.selectMode = function () {
        this.box.off('.n2-slide');
        this.box.on('click.n2-slide', $.proxy(this.invertSelection, this));
    };

    NextendSmartSliderAdminSlide.prototype.normalMode = function () {
        this.box.off('.n2-slide');
        this.box.on('click.n2-slide', $.proxy(this.goToEdit, this));
        this.deSelect();
    };

    NextendSmartSliderAdminSlide.prototype.invertSelection = function (e) {
        if (e) {
            e.preventDefault();
        }

        if (!this.selected) {
            this.select();
        } else {
            this.deSelect();
        }
    };

    NextendSmartSliderAdminSlide.prototype.select = function () {
        this.selected = true;
        this.box.addClass('n2-active');
    };

    NextendSmartSliderAdminSlide.prototype.deSelect = function () {
        this.selected = false;
        this.box.removeClass('n2-active');
    };

    scope.NextendSmartSliderAdminSlide = NextendSmartSliderAdminSlide;
})(n2, window);
(function (smartSlider, $, scope, undefined) {

    function NextendSmartSliderSidebar() {
        NextendAdminVerticalPane.prototype.constructor.call(this, $('.n2-layers-tab'), $('#n2-ss-layers-items-list').css('overflow', 'auto'), $('#n2-tabbed-layer-item-animation-tabs > .n2-tabs').css('overflow', 'auto'));

        smartSlider.sidebarManager = this;

        this.panelHeading = $('#layeritemeditorpanel').find('.n2-sidebar-tab-switcher .n2-td');


        var sidebar = $('#n2-ss-slide-sidebar');

        var contentTop = sidebar.parent().siblings('.n2-td').offset().top - $('#wpadminbar, .navbar').height();

        var onScrollCB = $.proxy(function () {
            if ($(window).scrollTop() > contentTop) {
                sidebar.addClass("n2-sidebar-fixed");
            } else {
                sidebar.removeClass("n2-sidebar-fixed");
            }
        }, this);

        sidebar.css({
            width: sidebar.width()
        });

        this.lateInit();
        $(window).scroll(onScrollCB);
        onScrollCB();

        new NextendSmartSliderEditorSidebarSlides();
        new NextendSmartSliderSidebarLayout();
    };

    NextendSmartSliderSidebar.prototype = Object.create(NextendAdminVerticalPane.prototype);
    NextendSmartSliderSidebar.prototype.constructor = NextendSmartSliderSidebar;

    NextendSmartSliderSidebar.prototype.loadDefaults = function () {

        NextendAdminVerticalPane.prototype.loadDefaults.apply(this, arguments);

        this.key = 'smartsliderSlideSidebarRatio';
    };

    NextendSmartSliderSidebar.prototype.switchTab = function (tab) {
        this.panelHeading.eq(tab).trigger('click');
    };

    NextendSmartSliderSidebar.prototype.getExcludedHeight = function () {
        var h = 0;
        h += $('#n2-ss-slide-editor-main-tab').outerHeight();
        h += $('#n2-ss-item-container').outerHeight();
        h += $('#n2-tabbed-layer-item-animation-tabs > .n2-labels').outerHeight();
        h += this.tab.find('.n2-sidebar-pane-sizer').outerHeight();
        h += 1; // border
        return h;
    };
    scope.NextendSmartSliderSidebar = NextendSmartSliderSidebar;

    function NextendSmartSliderEditorSidebarSlides() {

        var tab = $('#n2-ss-slides');

        NextendAdminSinglePane.prototype.constructor.call(this, tab, tab.find('.n2-ss-slides-container').css('overflow', 'auto'));

        $('.n2-slides-tab-label').on('click.n2-slides-init', $.proxy(function (e) {
            this.lateInit();
            $(e.target).off('click.n2-slides-init');
        }, this));
    }

    NextendSmartSliderEditorSidebarSlides.prototype = Object.create(NextendAdminSinglePane.prototype);
    NextendSmartSliderEditorSidebarSlides.prototype.constructor = NextendSmartSliderEditorSidebarSlides;

    NextendSmartSliderEditorSidebarSlides.prototype.getExcludedHeight = function () {
        var h = 0;
        h += $('#n2-ss-slide-editor-main-tab').outerHeight();
        h += $('.n2-slides-tab .n2-definition-list').outerHeight(true);
        h += 2; // border
        return h;
    };

    scope.NextendSmartSliderEditorSidebarSlides = NextendSmartSliderEditorSidebarSlides;


    function NextendSmartSliderSidebarSlides() {

        var tab = $('#n2-ss-slides');

        var sidebar = tab.parents('.n2-sidebar-inner');
        var contentTop = sidebar.parent().siblings('.n2-td').offset().top - $('#wpadminbar, .navbar').height();

        sidebar.css({
            width: sidebar.width()
        });

        $(window).scroll($.proxy(function () {
            if ($(window).scrollTop() > contentTop) {
                sidebar.addClass("n2-sidebar-fixed");
            } else {
                sidebar.removeClass("n2-sidebar-fixed");
            }
        }, this)).trigger('scroll');

        NextendAdminSinglePane.prototype.constructor.call(this, tab, tab.find('.n2-ss-slides-container').css('overflow', 'auto'));

        this.lateInit();
    }

    NextendSmartSliderSidebarSlides.prototype = Object.create(NextendAdminSinglePane.prototype);
    NextendSmartSliderSidebarSlides.prototype.constructor = NextendSmartSliderSidebarSlides;

    NextendSmartSliderSidebarSlides.prototype.getExcludedHeight = function () {
        var h = 0;
        h += $('#n2-ss-slide-editor-main-tab').outerHeight();
        h += $('.n2-sidebar .n2-definition-list').outerHeight(true);
        h += 2; // border
        return h;
    };

    scope.NextendSmartSliderSidebarSlides = NextendSmartSliderSidebarSlides;


    function NextendSmartSliderSidebarLayout() {

        var tab = $('.n2-layouts-tab');

        NextendAdminVerticalPane.prototype.constructor.call(this, tab, tab.find('.n2-lightbox-sidebar-list'), tab.find('.n2-ss-history-list'));

        $('.n2-layouts-tab-label').on('click.n2-layout-init', $.proxy(function (e) {
            this.lateInit();
            $(e.target).off('click.n2-layout-init');
        }, this));
    }

    NextendSmartSliderSidebarLayout.prototype = Object.create(NextendAdminVerticalPane.prototype);
    NextendSmartSliderSidebarLayout.prototype.constructor = NextendSmartSliderSidebarLayout;

    NextendSmartSliderSidebarLayout.prototype.loadDefaults = function () {

        NextendAdminVerticalPane.prototype.loadDefaults.apply(this, arguments);
        this.key = 'smartsliderLayoutSidebarRatio';
    };

    NextendSmartSliderSidebarLayout.prototype.getExcludedHeight = function () {
        var h = 0;
        h += $('#n2-ss-slide-editor-main-tab').outerHeight();
        h += this.tab.find('.n2-sidebar-row').outerHeight() * 2;
        h += this.tab.find(' > div > ul').outerHeight();
        h += this.tab.find('.n2-sidebar-pane-sizer').outerHeight();
        h += 1; // border
        return h;
    };

    scope.NextendSmartSliderSidebarLayout = NextendSmartSliderSidebarLayout;

})(nextend.smartSlider, n2, window);
;
(function (smartSlider, $, scope, undefined) {


    function SmartSliderAdminSlide(sliderElementID, slideContentElementID, isUploadDisabled, uploadUrl, uploadDir) {
        this.readyDeferred = $.Deferred();
        smartSlider.slide = this;

        this._warnInternetExplorerUsers();

        this.$slideContentElement = $('#' + slideContentElementID);
        this.slideStartValue = this.$slideContentElement.val();
        this.$sliderElement = $('#' + sliderElementID);


        smartSlider.frontend = window["n2-ss-0"];

        var fontSize = this.$sliderElement.data('fontsize');

        nextend.fontManager.setFontSize(fontSize);
        nextend.styleManager.setFontSize(fontSize);


        smartSlider.$currentSlideElement = smartSlider.frontend.adminGetCurrentSlideElement();

        new SmartSliderAdminGenerator();

        smartSlider.$currentSlideElement.addClass('n2-ss-currently-edited-slide');
        var staticSlide = smartSlider.frontend.parameters.isStaticEdited;
        new NextendSmartSliderAdminSlideLayerManager(smartSlider.$currentSlideElement.data('slide'), staticSlide, isUploadDisabled, uploadUrl, uploadDir);

        if (!staticSlide) {
            this._initializeBackgroundChanger();
        }

        this.readyDeferred.resolve();

        $('#smartslider-form').on({
            checkChanged: $.proxy(this.prepareFormForCheck, this),
            submit: $.proxy(this.onSlideSubmit, this)
        });
    };

    SmartSliderAdminSlide.prototype.ready = function (fn) {
        this.readyDeferred.done(fn);
    };

    SmartSliderAdminSlide.prototype.prepareFormForCheck = function () {
        var data = JSON.stringify(smartSlider.layerManager.getData()),
            startData = JSON.stringify(JSON.parse(Base64.decode(this.slideStartValue)));

        this.$slideContentElement.val(startData == data ? this.slideStartValue : Base64.encode(data));
    };

    SmartSliderAdminSlide.prototype.onSlideSubmit = function (e) {
        if (!nextend.isPreview) {
            this.prepareForm();
            e.preventDefault();

            nextend.askToSave = false;
            NextendAjaxHelper.ajax({
                type: 'POST',
                url: NextendAjaxHelper.makeAjaxUrl(window.location.href),
                data: $('#smartslider-form').serialize(),
                dataType: 'json'
            }).done(function () {
                nextend.askToSave = true;
                $('#smartslider-form').trigger('saved');
            });
        }
    };

    SmartSliderAdminSlide.prototype.prepareForm = function () {
        this.$slideContentElement.val(Base64.encode(JSON.stringify(smartSlider.layerManager.getData())));
    };

    SmartSliderAdminSlide.prototype._initializeBackgroundChanger = function () {
        this.background = {
            slideBackgroundColorField: $('#slidebackgroundColor'),
            slideBackgroundImageField: $('#slidebackgroundImage'),
            slideBackgroundImageOpacity: $('#slidebackgroundImageOpacity'),
            slideBackgroundModeField: $('#slidebackgroundMode'),
            backgroundImageElement: smartSlider.$currentSlideElement.find('.nextend-slide-bg'),
            canvas: smartSlider.$currentSlideElement.find('.n2-ss-slide-background')
        };

        this.background.slideBackgroundColorField.on('nextendChange', $.proxy(this.__onAfterBackgroundColorChange, this));
        this.background.slideBackgroundImageField.on('nextendChange', $.proxy(this.__onAfterBackgroundImageChange, this));
        this.background.slideBackgroundImageOpacity.on('nextendChange', $.proxy(this.__onAfterBackgroundImageOpacityChange, this));
        this.background.slideBackgroundModeField.on('nextendChange', $.proxy(this.__onAfterBackgroundImageChange, this));

        // Auto fill thumbnail if empty
        var thumbnail = $('#slidethumbnail');
        if (thumbnail.val() == '') {
            var itemImage = $('#item_imageimage'),
                cb = $.proxy(function (image) {
                    if (image != '' && image != '$system$/images/placeholder/image.png') {
                        thumbnail.val(image).trigger('change');
                        this.background.slideBackgroundImageField.off('.slidethumbnail');
                        itemImage.off('.slidethumbnail');
                    }
                }, this);
            this.background.slideBackgroundImageField.on('nextendChange.slidethumbnail', $.proxy(function () {
                cb(this.background.slideBackgroundImageField.val());
            }, this));
            itemImage.on('nextendChange.slidethumbnail', $.proxy(function () {
                cb(itemImage.val());
            }, this));
        }
    };

    SmartSliderAdminSlide.prototype.__onAfterBackgroundColorChange = function () {
        var backgroundColor = this.background.slideBackgroundColorField.val();
        if (backgroundColor.substr(6, 8) == '00') {
            this.background.canvas.css('background', '');
        } else {
            this.background.canvas.css('background', '#' + backgroundColor.substr(0, 6))
                .css('background', N2Color.hex2rgbaCSS(backgroundColor));
        }
    };

    SmartSliderAdminSlide.prototype.__onAfterBackgroundImageOpacityChange = function () {
        smartSlider.$currentSlideElement.data('slideBackground').setOpacity(this.background.slideBackgroundImageOpacity.val() / 100);
    };

    /**
     * This event callback is responsible for the slide editor to show the apropiate background color and image.
     * @private
     */
    SmartSliderAdminSlide.prototype.__onAfterBackgroundImageChange = function () {
        smartSlider.$currentSlideElement.data('slideBackground').changeDesktop(smartSlider.generator.fill(this.background.slideBackgroundImageField.val()), '', this.background.slideBackgroundModeField.val());
        this.__onAfterBackgroundImageOpacityChange();
    };

    /**
     * Warn old version IE users that the editor may fail to wrok in their browser.
     * @private
     */
    SmartSliderAdminSlide.prototype._warnInternetExplorerUsers = function () {
        var ie = this.__isInternetExplorer();
        if (ie && ie < 10) {
            alert(window.ss2lang.The_editor_was_tested_under_Internet_Explorer_10_Firefox_and_Chrome_Please_use_one_of_the_tested_browser);
        }
    };

    /**
     * @returns Internet Explorer version number or false
     * @private
     */
    SmartSliderAdminSlide.prototype.__isInternetExplorer = function () {
        var myNav = navigator.userAgent.toLowerCase();
        return (myNav.indexOf('msie') != -1) ? parseInt(myNav.split('msie')[1]) : false;
    };

    SmartSliderAdminSlide.prototype.getLayout = function () {
        var propertiesRaw = $('#smartslider-form').serializeArray(),
            properties = {};

        for (var i = 0; i < propertiesRaw.length; i++) {
            var m = propertiesRaw[i].name.match(/slide\[(.*?)\]/);
            if (m) {
                properties[m[1]] = propertiesRaw[i].value;
            }
        }
        delete properties['generator'];
        delete properties['published'];
        delete properties['publishdates'];
        delete properties['record-start'];
        delete properties['record-slides'];
        delete properties['slide'];

        properties['slide'] = smartSlider.layerManager.getData();
        return properties;
    };

    SmartSliderAdminSlide.prototype.loadLayout = function (properties, slideDataOverwrite, layerOverwrite) {
        // we are working on references!
        var slide = properties['slide'];
        delete properties['slide'];
        if (layerOverwrite) {
            smartSlider.layerManager.loadData(slide, true);
        } else {
            smartSlider.layerManager.loadData(slide, false);
        }
        if (slideDataOverwrite) {
            for (var k in properties) {
                $('#slide' + k).val(properties[k]).trigger('change');
            }
        }
        properties['slide'] = slide;
    };

    scope.SmartSliderAdminSlide = SmartSliderAdminSlide;

})(nextend.smartSlider, n2, window);
(function (smartSlider, $, scope, undefined) {
    nextend['ssBeforeResponsive'] = function () {
        new NextendSmartSliderAdminZoom(this);
    };

    function NextendSmartSliderAdminZoom(responsive) {
        this.key = 'n2-ss-editor-device-lock-mode';
        this.devices = {
            unknownUnknown: $('<div />')
        };
        this.responsive = responsive;
        this.responsive.setOrientation('portrait');
        this.responsive.parameters.onResizeEnabled = 0;
        this.responsive.parameters.forceFull = 0; // We should disable force full feature on admin dashboard as it won't render before the sidebar
        this.responsive._getDevice = this.responsive._getDeviceZoom;

        this.lock = $('#n2-ss-lock').on('click', $.proxy(this.switchLock, this));

        var desktopWidth = responsive.parameters.sliderWidthToDevice['desktopPortrait'];

        this.container = this.responsive.containerElement.closest('.n2-ss-container-device').addBack();
        this.container.width(desktopWidth);
        this.containerWidth = desktopWidth;

        this.initZoom();

        var tr = $('#n2-ss-devices .n2-tr'),
            modes = responsive.parameters.deviceModes;

        this.devices.desktopPortrait = $('<div class="n2-td n2-panel-option" data-device="desktop" data-orientation="portrait"><i class="n2-i n2-it n2-i-v-desktop"></i></div>').appendTo(tr);
        if (modes.desktopLandscape) {
            this.devices.desktopLandscape = $('<div class="n2-td n2-panel-option" data-device="desktop" data-orientation="landscape"><i class="n2-i n2-it n2-i-v-desktop-landscape"></i></div>').appendTo(tr);
        } else {
            this.devices.desktopLandscape = this.devices.desktopPortrait;
        }

        if (modes.tabletPortrait) {
            this.devices.tabletPortrait = $('<div class="n2-td n2-panel-option" data-device="tablet" data-orientation="portrait"><i class="n2-i n2-it n2-i-v-tablet"></i></div>').appendTo(tr);
        } else {
            this.devices.tabletPortrait = this.devices.desktopPortrait;
        }
        if (modes.tabletLandscape) {
            this.devices.tabletLandscape = $('<div class="n2-td n2-panel-option" data-device="tablet" data-orientation="landscape"><i class="n2-i n2-it n2-i-v-tablet-landscape"></i></div>').appendTo(tr);
        } else {
            this.devices.tabletLandscape = this.devices.desktopLandscape;
        }

        if (modes.mobilePortrait) {
            this.devices.mobilePortrait = $('<div class="n2-td n2-panel-option" data-device="mobile" data-orientation="portrait"><i class="n2-i n2-it n2-i-v-mobile"></i></div>').appendTo(tr);
        } else {
            this.devices.mobilePortrait = this.devices.tabletPortrait;
        }
        if (modes.mobileLandscape) {
            this.devices.mobileLandscape = $('<div class="n2-td n2-panel-option" data-device="mobile" data-orientation="landscape"><i class="n2-i n2-it n2-i-v-mobile-landscape"></i></div>').appendTo(tr);
        } else {
            this.devices.mobileLandscape = this.devices.tabletLandscape;
        }

        this.deviceOptions = $('#n2-ss-devices .n2-panel-option');

        $('#n2-ss-devices').css('width', (this.deviceOptions.length * 62) + 'px');

        this.deviceOptions.each($.proxy(function (i, el) {
            $(el).on('click', $.proxy(this.setDeviceMode, this));
        }, this));

        responsive.sliderElement.on('SliderDeviceOrientation', $.proxy(this.onDeviceOrientationChange, this));
    };

    NextendSmartSliderAdminZoom.prototype.onDeviceOrientationChange = function (e, modes) {
        $('#n2-admin').removeClass('n2-ss-mode-' + modes.lastDevice)
            .addClass('n2-ss-mode-' + modes.device);
        this.devices[modes.lastDevice + modes.lastOrientation].removeClass('n2-active');
        this.devices[modes.device + modes.orientation].addClass('n2-active');
    };

    NextendSmartSliderAdminZoom.prototype.setDeviceMode = function (e) {
        var el = $(e.currentTarget);
        if ((e.ctrlKey || e.metaKey) && smartSlider.layerManager) {
            var orientation = el.data('orientation');
            smartSlider.layerManager.copyOrResetMode(el.data('device') + orientation[0].toUpperCase() + orientation.substr(1));
        } else {
            this.responsive.setOrientation(el.data('orientation'));
            this.responsive.setMode(el.data('device'));
        }
    };

    NextendSmartSliderAdminZoom.prototype.switchLock = function (e) {
        e.preventDefault();
        this.lock.toggleClass('n2-active');
        if (this.lock.hasClass('n2-active')) {
            this.setZoomSyncMode();
            this.zoomChange(this.zoom.slider("value"), 'sync', false);

            $.jStorage.set(this.key, 'sync');
        } else {
            this.setZoomFixMode();
            $.jStorage.set(this.key, 'fix');
        }
    };

    NextendSmartSliderAdminZoom.prototype.initZoom = function () {
        var zoom = $("#n2-ss-slider-zoom");
        if (zoom.length > 0) {

            if (typeof zoom[0].slide !== 'undefined') {
                zoom[0].slide = null;
            }

            this.zoom =
                zoom.slider({
                    range: "min",
                    step: 1,
                    value: 1,
                    min: 0,
                    max: 102
                });

            this.responsive.sliderElement.on('SliderResize', $.proxy(this.sliderResize, this));

            if ($.jStorage.get(this.key, 'sync') == 'fix') {
                this.setZoomFixMode();
            } else {
                this.setZoomSyncMode();
                this.lock.addClass('n2-active');
            }

            var parent = zoom.parent(),
                change = $.proxy(function (value) {
                    var oldValue = this.zoom.slider('value');
                    this.zoom.slider('value', oldValue + value);
                }, this),
                interval = null,
                mouseDown = $.proxy(function (value) {
                    change(value);
                    interval = setInterval($.proxy(change, this, value), 1000 / 25);
                    $(window).one('mouseup', function () {
                        if (interval) {
                            clearInterval(interval);
                        }
                    });
                }, this);
            parent.find('.n2-i-minus').on({
                mousedown: $.proxy(mouseDown, this, -1)
            });
            parent.find('.n2-i-plus').on({
                mousedown: $.proxy(mouseDown, this, 1)
            });
        }
    };

    NextendSmartSliderAdminZoom.prototype.sliderResize = function (e, ratios) {
        this.setZoom();
    };

    NextendSmartSliderAdminZoom.prototype.setZoomFixMode = function () {
        this.zoom.off('.n2-ss-zoom')
            .on({
                'slide.n2-ss-zoom': $.proxy(this.zoomChangeFixMode, this),
                'slidechange.n2-ss-zoom': $.proxy(this.zoomChangeFixMode, this)
            });
    };

    NextendSmartSliderAdminZoom.prototype.setZoomSyncMode = function () {

        this.zoom.off('.n2-ss-zoom')
            .on({
                'slide.n2-ss-zoom': $.proxy(this.zoomChangeSyncMode, this),
                'slidechange.n2-ss-zoom': $.proxy(this.zoomChangeSyncMode, this)
            });
    };

    NextendSmartSliderAdminZoom.prototype.zoomChangeFixMode = function (event, ui) {
        this.zoomChange(ui.value, 'fix', ui);
    };

    NextendSmartSliderAdminZoom.prototype.zoomChangeSyncMode = function (event, ui) {
        this.zoomChange(ui.value, 'sync', ui);
    };

    NextendSmartSliderAdminZoom.prototype.zoomChange = function (value, mode, ui) {
        var ratio = 1;
        if (value < 50) {
            ratio = nextend.smallestZoom / this.containerWidth + Math.max(value / 50, 0) * (1 - nextend.smallestZoom / this.containerWidth);
        } else if (value > 52) {
            ratio = 1 + (value - 52) / 50;
        }
        var width = parseInt(ratio * this.containerWidth);
        this.container.width(width);

        switch (mode) {
            case 'sync':
                this.responsive.doResize();
                break;
            default:
                this.responsive.doResize(true);
                break;
        }
        if (ui) {
            ui.handle.innerHTML = width + 'px';
        }
    };

    NextendSmartSliderAdminZoom.prototype.setZoom = function () {
        var ratio = this.responsive.containerElement.width() / this.containerWidth;
        var v = 50;
        if (ratio < 1) {
            v = (ratio - nextend.smallestZoom / this.containerWidth) / (1 - nextend.smallestZoom / this.containerWidth) * 50;
        } else if (ratio > 1) {
            v = (ratio - 1) * 50 + 52;
        }
        var oldValue = this.zoom.slider('value');
        this.zoom.slider('value', v);
    };
})
(nextend.smartSlider, n2, window);
;
(function ($, scope) {

    function NextendElementAnimationManager(id, managerIdentifier) {
        this.element = $('#' + id);
        this.managerIdentifier = managerIdentifier;

        this.element.parent()
            .on('click', $.proxy(this.show, this));

        this.element.siblings('.n2-form-element-clear')
            .on('click', $.proxy(this.clear, this));

        this.name = this.element.siblings('input');

        this.updateName(this.element.val());

        NextendElement.prototype.constructor.apply(this, arguments);
    };


    NextendElementAnimationManager.prototype = Object.create(NextendElement.prototype);
    NextendElementAnimationManager.prototype.constructor = NextendElementAnimationManager;


    NextendElementAnimationManager.prototype.show = function (e) {
        e.preventDefault();
        nextend[this.managerIdentifier].show(this.element.val(), $.proxy(this.save, this));
    };

    NextendElementAnimationManager.prototype.clear = function (e) {
        e.preventDefault();
        e.stopPropagation();
        this.val('');
    };

    NextendElementAnimationManager.prototype.save = function (e, value) {
        this.val(value);
    };

    NextendElementAnimationManager.prototype.val = function (value) {
        this.element.val(value);
        this.updateName(value);
        this.triggerOutsideChange();
    };

    NextendElementAnimationManager.prototype.insideChange = function (value) {
        this.element.val(value);

        this.updateName(value);

        this.triggerInsideChange();
    };

    NextendElementAnimationManager.prototype.updateName = function (value) {
        if (value == '') {
            value = n2_('Disabled');
        } else if (value.split('||').length > 1) {
            value = n2_('Multiple animations')
        } else {
            value = n2_('Single animation');
        }
        this.name.val(value);
    };

    scope.NextendElementAnimationManager = NextendElementAnimationManager;

    function NextendElementPostAnimationManager() {
        NextendElementAnimationManager.prototype.constructor.apply(this, arguments);
    };


    NextendElementPostAnimationManager.prototype = Object.create(NextendElementAnimationManager.prototype);
    NextendElementPostAnimationManager.prototype.constructor = NextendElementPostAnimationManager;

    NextendElementPostAnimationManager.prototype.clear = function (e) {
        e.preventDefault();
        e.stopPropagation();
        var data = this.element.val().split('|*|');
        data[2] = '';
        this.val(data.join('|*|'));
    };
    NextendElementPostAnimationManager.prototype.updateName = function (value) {
        var data = value.split('|*|');
        value = data[2];
        if (value == '') {
            value = n2_('Disabled');
        } else if (value.split('||').length > 1) {
            value = n2_('Multiple animations');
        } else {
            value = n2_('Single animation');
        }
        this.name.val(value);
    };

    scope.NextendElementPostAnimationManager = NextendElementPostAnimationManager;

})(n2, window);
(function ($, scope) {

    var STATUS = {
            INITIALIZED: 0,
            UNDER_PICK_PARENT: 1,
            UNDER_PICK_CHILD: 2
        },
        OVERLAYS = '<div class="n2-ss-picker-overlay" data-align="left" data-valign="top" />' +
            '<div class="n2-ss-picker-overlay" data-align="center" data-valign="top" style="left:33%;top:0;" />' +
            '<div class="n2-ss-picker-overlay" data-align="right" data-valign="top" style="left:66%;top:0;width:34%;" />' +
            '<div class="n2-ss-picker-overlay" data-align="left" data-valign="middle" style="left:0;top:33%;" />' +
            '<div class="n2-ss-picker-overlay" data-align="center" data-valign="middle" style="left:33%;top:33%; " />' +
            '<div class="n2-ss-picker-overlay" data-align="right" data-valign="middle" style="left:66%;top:33%;width:34%;" />' +
            '<div class="n2-ss-picker-overlay" data-align="left" data-valign="bottom" style="left:0;top:66%;height:34%;" />' +
            '<div class="n2-ss-picker-overlay" data-align="center" data-valign="bottom" style="left:33%;top:66%;height:34%;" />' +
            '<div class="n2-ss-picker-overlay" data-align="right" data-valign="bottom" style="left:66%;top:66%;width:34%;height:34%;" />';

    function NextendElementLayerPicker(id) {
        this.status = 0;
        this.element = $('#' + id);
        this.overlays = null;

        this.aligns = this.element.parent().parent().siblings();

        this.globalPicker = $('#n2-ss-parent-linker');
        this.picker = this.element.siblings('.n2-ss-layer-picker')
            .on({
                click: $.proxy(this.click, this),
                mouseenter: $.proxy(function () {
                    var value = this.element.val();
                    if (value != '') {
                        $('#' + value).addClass('n2-highlight');
                    }
                }, this),
                mouseleave: $.proxy(function () {
                    var value = this.element.val();
                    if (value != '') {
                        $('#' + value).removeClass('n2-highlight');
                    }
                }, this)
            });


        NextendElement.prototype.constructor.apply(this, arguments);
    };


    NextendElementLayerPicker.prototype = Object.create(NextendElement.prototype);
    NextendElementLayerPicker.prototype.constructor = NextendElementLayerPicker;

    NextendElementLayerPicker.prototype.click = function (e) {
        if (this.status == STATUS.INITIALIZED) {
            $('body').on('mousedown.n2-ss-parent-linker', $.proxy(function (e) {
                var el = $(e.target),
                    parent = el.parent();
                if (!el.hasClass('n2-ss-picker-overlay') && !parent.hasClass('n2-under-pick')) {
                    this.endSelection();
                }
            }, this));
            var layers = nextend.activeLayer.parent().find('.n2-ss-layer').not(nextend.activeLayer),
                cb = function (id) {
                    layers.each(function () {
                        var layer = $(this),
                            layerObject = layer.data('layerObject');
                        if (layerObject.getProperty(false, 'parentid') == id) {
                            layers = layers.not(layer);
                            var id2 = layerObject.getProperty(false, 'id');
                            if (id2 && id2 != '') {
                                cb(id2);
                            }
                        }
                    });
                };
            var cID = nextend.activeLayer.data('layerObject').getProperty(false, 'id');
            if (cID && cID != '') {
                cb(cID);
            }

            if (layers.length > 0) {
                this.globalPicker.addClass('n2-under-pick');
                this.picker.addClass('n2-under-pick');

                layers.addClass('n2-ss-picking-on-layer');
                this.overlays = $(OVERLAYS).appendTo(layers);
                this.overlays.on('mousedown', $.proxy(function (e) {
                    var selectedOverlay = $(e.currentTarget),
                        parentAlign = selectedOverlay.data('align'),
                        parentValign = selectedOverlay.data('valign'),
                        parentObject = selectedOverlay.parent().data('layerObject');
                    this.status = STATUS.UNDER_PICK_CHILD;
                    this.overlays.remove();

                    layers.removeClass('n2-ss-picking-on-layer');
                    nextend.activeLayer.addClass('n2-ss-picking-on-layer');
                    this.overlays = $(OVERLAYS).appendTo(nextend.activeLayer);
                    this.overlays.on('mousedown', $.proxy(function (e) {
                        var selectedChildOverlay = $(e.currentTarget),
                            align = selectedChildOverlay.data('align'),
                            valign = selectedChildOverlay.data('valign');

                        nextend.activeLayer.removeClass('n2-ss-picking-on-layer');
                        nextend.activeLayer.data('layerObject').parentPicked(parentObject, parentAlign, parentValign, align, valign);

                        //this.change(parentObject.requestID());

                        e.preventDefault();
                        e.stopPropagation();
                        this.endSelection();
                    }, this));
                    e.preventDefault();
                    e.stopPropagation();
                }, this));

                NextendEsc.add($.proxy(function () {
                    this.endSelection();
                    return false;
                }, this));

                this.status = STATUS.UNDER_PICK_PARENT;
            }
        } else if (this.status == STATUS.UNDER_PICK_PARENT) {
            this.change('');
            this.endSelection();
        } else if (this.status == STATUS.UNDER_PICK_CHILD) {
            this.change('');
            this.endSelection();
        }
    };

    NextendElementLayerPicker.prototype.endSelection = function () {
        $('body').off('mousedown.n2-ss-parent-linker');
        nextend.activeLayer.parent().find('.n2-ss-layer').removeClass('n2-ss-picking-on-layer');
        this.globalPicker.removeClass('n2-under-pick');
        this.picker.removeClass('n2-under-pick');
        if (this.overlays) {
            this.overlays.remove();
        }
        this.overlays = null;
        this.status = STATUS.INITIALIZED;
        NextendEsc.pop();
    };

    NextendElementLayerPicker.prototype.change = function (value) {
        this.picker.trigger('mouseleave');
        this.element.val(value).trigger('change');
        this._setValue(value);
        this.triggerOutsideChange();
    };

    NextendElementLayerPicker.prototype.insideChange = function (value) {
        this.element.val(value);
        this._setValue(value);

        this.triggerInsideChange();
    };

    NextendElementLayerPicker.prototype._setValue = function (value) {
        if (value && value != '') {
            this.picker.addClass('n2-active');
            this.aligns.css('display', '');
        } else {
            this.picker.removeClass('n2-active');
            this.aligns.css('display', 'none');
        }
    };

    scope.NextendElementLayerPicker = NextendElementLayerPicker;

})(n2, window);
;
(function ($, scope) {

    function NextendElementSliderType(id) {
        this.element = $('#' + id);

        this.setAttribute();

        this.element.on('nextendChange', $.proxy(this.setAttribute, this));
    };

    NextendElementSliderType.prototype.setAttribute = function () {

        $('#n2-admin').attr('data-slider-type', this.element.val());
    };

    scope.NextendElementSliderType = NextendElementSliderType;

})(n2, window);

;
(function ($, scope) {

    function NextendElementSliderWidgetArea(id) {
        this.element = $('#' + id);

        this.area = $('#' + id + '_area');

        this.areas = this.area.find('.n2-area');

        this.areas.on('click', $.proxy(this.chooseArea, this));

        NextendElement.prototype.constructor.apply(this, arguments);
    };


    NextendElementSliderWidgetArea.prototype = Object.create(NextendElement.prototype);
    NextendElementSliderWidgetArea.prototype.constructor = NextendElementSliderWidgetArea;


    NextendElementSliderWidgetArea.prototype.chooseArea = function (e) {
        var value = parseInt($(e.target).data('area'));

        this.element.val(value);
        this.setSelected(value);

        this.triggerOutsideChange();
    };

    NextendElementSliderWidgetArea.prototype.insideChange = function (value) {
        value = parseInt(value);
        this.element.val(value);
        this.setSelected(value);

        this.triggerInsideChange();
    };

    NextendElementSliderWidgetArea.prototype.setSelected = function (index) {
        this.areas.removeClass('n2-active');
        this.areas.eq(index - 1).addClass('n2-active');
    };

    scope.NextendElementSliderWidgetArea = NextendElementSliderWidgetArea;

})(n2, window);

"use strict";
(function ($, scope) {
    function NextendElementWidgetPosition(id) {

        this.element = $('#' + id + '-mode');
        this.container = this.element.closest('.n2-form-element-mixed');

        this.tabs = this.container.find('> .n2-mixed-group');

        this.element.on('nextendChange', $.proxy(this.onChange, this));

        this.onChange();
    };

    NextendElementWidgetPosition.prototype.onChange = function () {
        var value = this.element.val();

        if (value == 'advanced') {
            this.tabs.eq(2).css('display', '');
            this.tabs.eq(1).css('display', 'none');
        } else {
            this.tabs.eq(1).css('display', '');
            this.tabs.eq(2).css('display', 'none');
        }
    };

    scope.NextendElementWidgetPosition = NextendElementWidgetPosition;

})(n2, window);

(function (smartSlider, $, scope, undefined) {
    "use strict";
    function Generator() {
        this._refreshTimeout = null;
        this.modal = false;
        this.group = 0;
        smartSlider.generator = this;
        var variables = smartSlider.$currentSlideElement.data('variables');
        if (variables) {
            this.variables = variables;

            for (var i in this.variables) {
                if (!isNaN(parseFloat(i)) && isFinite(i)) {
                    this.group = Math.max(this.group, parseInt(i) + 1);
                }
            }

            this.fill = this.generatorFill;
            if (this.group > 0) {
                this.registerField = this.generatorRegisterField;

                this.button = $('<a href="#" class="n2-form-element-button n2-form-element-button-inverted n2-h5 n2-uc" style="position:absolute; left: -26px; top:50%;margin-top: -14px;font-size: 14px; padding:0; width: 28px;text-align: center;">$</a>')
                    .on('click', $.proxy(function (e) {
                        e.preventDefault();
                        this.showModal();
                    }, this));
                this.registerField($('#slidetitle'));
                this.registerField($('#slidedescription'));
                this.registerField($('#slidethumbnail'));
                this.registerField($('#slidebackgroundImage'));
                this.registerField($('#slidebackgroundAlt'));
                this.registerField($('#slidebackgroundVideoMp4'));
                this.registerField($('#slidebackgroundVideoWebm'));
                this.registerField($('#slidebackgroundVideoOgg'));
                this.registerField($('#linkslidelink_0'));

                //this.showModal();
            }

            this.initSlideDataRefresh();
        } else {
            this.variables = null;
        }
    };

    Generator.prototype.fill = function (value) {
        return value;
    };

    Generator.prototype.generatorFill = function (value) {
        return value.replace(/{((([a-z]+)\(([0-9a-zA-Z_,\/\(\)]+)\))|([a-zA-Z0-9_\/]+))}/g, $.proxy(this.parseFunction, this));
    };

    Generator.prototype.parseFunction = function (s, s2, s3, functionName, argumentString, variable) {
        if (typeof variable == 'undefined') {
            var args = argumentString.split(/,(?!.*\))/);
            for (var i = 0; i < args.length; i++) {
                args[i] = this.parseVariable(args[i]);
            }
            return this[functionName].apply(this, args);
        } else {
            return this.parseVariable(variable);
        }
    };

    Generator.prototype.parseVariable = function (variable) {

        var functionMatch = variable.match(/((([a-z]+)\(([0-9a-zA-Z_,\/\(\)]+)\)))/);
        if (functionMatch) {
            return this.parseFunction.apply(this, functionMatch);
        } else {
            var variableMatch = variable.match(/([a-zA-Z][0-9a-zA-Z_]*)(\/([0-9a-z]+))?/);
            if (variableMatch) {
                var index = variableMatch[3];
                if (typeof index == 'undefined') {
                    index = 0;
                } else {
                    var i = parseInt(index);
                    if (!isNaN(i)) {
                        index = Math.max(index, 1) - 1;
                    }
                }
                if (typeof this.variables[index] != 'undefined' && typeof this.variables[index][variableMatch[1]] != 'undefined') {
                    return this.variables[index][variableMatch[1]];
                }
                return '';
            }
            return variable;
        }
    };

    Generator.prototype.cleanhtml = function (variable) {
        return strip_tags(variable, '<p><a><b><br /><br/><i>');
    };

    Generator.prototype.removehtml = function (variable) {
        return $('<div>' + variable + '</div>').text();
    };

    Generator.prototype.splitbychars = function (s, start, length) {
        return s.substr(start, length);
    };

    Generator.prototype.splitbywords = function (variable, start, length) {
        var s = variable,
            len = s.length,
            posStart = Math.max(0, start == 0 ? 0 : s.indexOf(' ', start)),
            posEnd = Math.max(0, length > len ? len : s.indexOf(' ', length));
        return s.substr(posStart, posEnd);
    };

    Generator.prototype.findimage = function (variable, index) {
        var s = variable,
            re = /(<img.*?src=[\'"](.*?)[\'"][^>]*>)|(background(-image)??\s*?:.*?url\((["|\']?)?(.+?)(["|\']?)?\))/gi,
            r = [],
            tmp = null;

        index = typeof index != 'undefined' ? parseInt(index) - 1 : 0;

        while (tmp = re.exec(s)) {        
            if (typeof tmp[2] != 'undefined') {
                r.push(tmp[2]);
            } else if (typeof tmp[6] != 'undefined') {
                r.push(tmp[6]);
            }
        }

        if (r.length) {
            if (r.length > index) {
                return r[index];
            } else {
                return r[r.length - 1];
            }
        } else {
            return '';
        }
    };
    
    Generator.prototype.findlink = function (variable, index) {
        var s = variable,
            re = /href=["\']?([^"\'>]+)["\']?/gi,
            r = [],
            tmp = null;

        index = typeof index != 'undefined' ? parseInt(index) - 1 : 0;
        
        while (tmp = re.exec(s)) {
            if (typeof tmp[1] != 'undefined') {
                r.push(tmp[1]);
            }
        }

        if (r.length) {
            if (r.length > index) {
                return r[index];
            } else {
                return r[r.length - 1];
            }
        } else {
            return '';
        }
    };

    Generator.prototype.registerField = function (field) {
    };

    Generator.prototype.generatorRegisterField = function (field) {
        var parent = field.parent();
        parent.on({
            mouseenter: $.proxy(function () {
                this.activeField = field;
                this.button.prependTo(parent);
            }, this)
        });
    };

    Generator.prototype.getModal = function () {
        var that = this;
        if (!this.modal) {
            var active = {
                    key: '',
                    group: 1,
                    filter: 'no',
                    split: 'no',
                    splitStart: 0,
                    splitLength: 300,
                    findImage: 0,
                    findImageIndex: 1,
                    findLink: 0,
                    findLinkIndex: 1
                },
                getVariableString = function () {
                    var variable = active.key + '/' + active.group;
                    if (active.findImage) {
                        variable = 'findimage(' + variable + ',' + Math.max(1, active.findImageIndex) + ')';
                    }
                    if (active.findLink) {
                        variable = 'findlink(' + variable + ',' + Math.max(1, active.findLinkIndex) + ')';
                    }
                    if (active.filter != 'no') {
                        variable = active.filter + '(' + variable + ')';
                    }
                    if (active.split != 'no' && active.splitStart >= 0 && active.splitLength > 0) {
                        variable = active.split + '(' + variable + ',' + active.splitStart + ',' + active.splitLength + ')';
                    }
                    return '{' + variable + '}';
                },
                resultContainer = $('<div class="n2-generator-result-container" />'),
                updateResult = function () {
                    resultContainer.html($('<div/>').text(that.fill(getVariableString())).html());
                };

            var group = that.group,
                variables = null,
                groups = null,
                content = $('<div class="n2-generator-insert-variable"/>');


            var groupHeader = NextendModal.prototype.createHeading(n2_('Choose the group')).appendTo(content);
            var groupContainer = $('<div class="n2-group-container" />').appendTo(content);


            content.append(NextendModal.prototype.createHeading(n2_('Choose the variable')));
            var variableContainer = $('<div class="n2-variable-container" />').appendTo(content);

            //content.append(NextendModal.prototype.createHeading('Functions'));
            var functionsContainer = $('<div class="n2-generator-functions-container n2-form-element-mixed" />')
                .appendTo($('<div class="n2-form" />').appendTo(content));

            content.append(NextendModal.prototype.createHeading(n2_('Result')));
            resultContainer.appendTo(content);


            $('<div class="n2-mixed-group"><div class="n2-mixed-label"><label>' + n2_('Filter') + '</label></div><div class="n2-mixed-element"><div class="n2-form-element-list"><select autocomplete="off" name="filter" id="n2-generator-function-filter"><option selected="selected" value="no">' + n2_('No') + '</option><option value="cleanhtml">' + n2_('Clean HTML') + '</option><option value="removehtml">' + n2_('Remove HTML') + '</option></select></div></div></div>')
                .appendTo(functionsContainer);
            var filter = functionsContainer.find('#n2-generator-function-filter');
            filter.on('change', $.proxy(function () {
                active.filter = filter.val();
                updateResult();
            }, this));


            $('<div class="n2-mixed-group"><div class="n2-mixed-label"><label>' + n2_('Split by chars') + '</label></div><div class="n2-mixed-element"><div class="n2-form-element-list"><select autocomplete="off" name="split" id="n2-generator-function-split"><option selected="selected" value="no">' + n2_('No') + '</option><option value="splitbychars">' + n2_('Strict') + '</option><option value="splitbywords">' + n2_('Respect words') + '</option></select></div><div class="n2-form-element-text n2-text-has-unit n2-border-radius"><div class="n2-text-sub-label n2-h5 n2-uc">' + n2_('Start') + '</div><input type="text" autocomplete="off" style="width: 22px;" class="n2-h5" value="0" id="n2-generator-function-split-start"></div><div class="n2-form-element-text n2-text-has-unit n2-border-radius"><div class="n2-text-sub-label n2-h5 n2-uc">' + n2_('Length') + '</div><input type="text" autocomplete="off" style="width: 22px;" class="n2-h5" value="300" id="n2-generator-function-split-length"></div></div></div>')
                .appendTo(functionsContainer);
            var split = functionsContainer.find('#n2-generator-function-split');
            split.on('change', $.proxy(function () {
                active.split = split.val();
                updateResult();
            }, this));
            var splitStart = functionsContainer.find('#n2-generator-function-split-start');
            splitStart.on('change', $.proxy(function () {
                active.splitStart = parseInt(splitStart.val());
                updateResult();
            }, this));
            var splitLength = functionsContainer.find('#n2-generator-function-split-length');
            splitLength.on('change', $.proxy(function () {
                active.splitLength = parseInt(splitLength.val());
                updateResult();
            }, this));


            $('<div class="n2-mixed-group"><div class="n2-mixed-label"><label>' + n2_('Find image') + '</label></div><div class="n2-mixed-element"><div class="n2-form-element-onoff"><div class="n2-onoff-slider"><div class="n2-onoff-no"><i class="n2-i n2-i-close"></i></div><div class="n2-onoff-round"></div><div class="n2-onoff-yes"><i class="n2-i n2-i-tick"></i></div></div><input type="hidden" autocomplete="off" value="0" id="n2-generator-function-findimage"></div><div class="n2-form-element-text n2-text-has-unit n2-border-radius"><div class="n2-text-sub-label n2-h5 n2-uc">' + n2_('Index') + '</div><input type="text" autocomplete="off" style="width: 22px;" class="n2-h5" value="1" id="n2-generator-function-findimage-index"></div></div></div>')
                .appendTo(functionsContainer);

            var findImage = functionsContainer.find('#n2-generator-function-findimage');
            findImage.on('nextendChange', $.proxy(function () {
                active.findImage = parseInt(findImage.val());
                updateResult();
            }, this));
            var findImageIndex = functionsContainer.find('#n2-generator-function-findimage-index');
            findImageIndex.on('change', $.proxy(function () {
                active.findImageIndex = parseInt(findImageIndex.val());
                updateResult();
            }, this));


            $('<div class="n2-mixed-group"><div class="n2-mixed-label"><label>' + n2_('Find link') + '</label></div><div class="n2-mixed-element"><div class="n2-form-element-onoff"><div class="n2-onoff-slider"><div class="n2-onoff-no"><i class="n2-i n2-i-close"></i></div><div class="n2-onoff-round"></div><div class="n2-onoff-yes"><i class="n2-i n2-i-tick"></i></div></div><input type="hidden" autocomplete="off" value="0" id="n2-generator-function-findlink"></div><div class="n2-form-element-text n2-text-has-unit n2-border-radius"><div class="n2-text-sub-label n2-h5 n2-uc">' + n2_('Index') + '</div><input type="text" autocomplete="off" style="width: 22px;" class="n2-h5" value="1" id="n2-generator-function-findlink-index"></div></div></div>')
                .appendTo(functionsContainer);

            var findLink = functionsContainer.find('#n2-generator-function-findlink');
            findLink.on('nextendChange', $.proxy(function () {
                active.findLink = parseInt(findLink.val());
                updateResult();
            }, this));
            var findLinkIndex = functionsContainer.find('#n2-generator-function-findlink-index');
            findLinkIndex.on('change', $.proxy(function () {
                active.findLinkIndex = parseInt(findLinkIndex.val());
                updateResult();
            }, this));

            for (var k in this.variables[0]) {
                $('<a href="#" class="n2-button n2-button-small n2-button-grey">' + k + '</a>')
                    .on('click', $.proxy(function (key, e) {
                        e.preventDefault();
                        variables.removeClass('n2-active');
                        $(e.currentTarget).addClass('n2-active');
                        active.key = key;
                        updateResult();
                    }, this, k))
                    .appendTo(variableContainer);
            }

            variables = variableContainer.find('a');
            variables.eq(0).trigger('click');

            if (group == 1) {
                groupHeader.css('display', 'none');
                groupContainer.css('display', 'none');
            }
            for (var i = 0; i < group; i++) {
                $('<a href="#" class="n2-button n2-button-small n2-button-grey">' + (i + 1) + '</a>')
                    .on('click', $.proxy(function (groupIndex, e) {
                        e.preventDefault();
                        groups.removeClass('n2-active');
                        $(e.currentTarget).addClass('n2-active');
                        active.group = groupIndex + 1;
                        updateResult();
                    }, this, i))
                    .appendTo(groupContainer);
            }
            groups = groupContainer.find('a');
            groups.eq(0).trigger('click');

            var inited = false;

            this.modal = new NextendModal({
                zero: {
                    size: [
                        1000,
                        group > 1 ? 560 : 490
                    ],
                    title: n2_('Insert variable'),
                    back: false,
                    close: true,
                    content: content,
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green">' + n2_('Insert') + '</a>'],
                    fn: {
                        show: function () {
                            if (!inited) {
                                new NextendElementOnoff("n2-generator-function-findimage");
                                new NextendElementOnoff("n2-generator-function-findlink");
                                inited = true;
                            }
                            this.controls.find('.n2-button').on('click', $.proxy(function (e) {
                                e.preventDefault();
                                that.insert(getVariableString());
                                this.hide(e);
                            }, this));
                        }
                    }
                }
            }, false);

            this.modal.setCustomClass('n2-ss-generator-modal');
        }
        return this.modal;
    };

    Generator.prototype.showModal = function () {

        this.getModal().show();
    };

    Generator.prototype.insert = function (value) {
        this.activeField.val(value).trigger('change');
    };

    Generator.prototype.initSlideDataRefresh = function () {

        var name = $('#slidetitle').on('nextendChange', $.proxy(function () {
            this.variables.slide.name = name.val();
            this.refresh();
        }, this));

        var description = $('#slidedescription').on('nextendChange', $.proxy(function () {
            this.variables.slide.description = description.val();
            this.refresh();
        }, this));

    };


    Generator.prototype.refresh = function () {
        if (this._refreshTimeout) {
            clearTimeout(this._refreshTimeout);
            this._refreshTimeout = null;
        }
        this._refreshTimeout = setTimeout($.proxy(this._refresh, this), 100);
    };

    Generator.prototype._refresh = function () {
        var layers = smartSlider.layerManager.layerList;
        for (var j = 0; j < layers.length; j++) {
            var items = layers[j].items;
            for (var i = 0; i < items.length; i++) {
                items[i].reRender();
            }
        }
    };


    scope.SmartSliderAdminGenerator = Generator;

})(nextend.smartSlider, n2, window);
(function ($, scope, undefined) {

    function GeneratorRecords(ajaxUrl) {
        this.ajaxUrl = ajaxUrl;

        $("#generatorrecord-viewer").on("click", $.proxy(this.showRecords, this));
    };

    GeneratorRecords.prototype.showRecords = function (e) {
        e.preventDefault();
        NextendAjaxHelper.ajax({
            type: "POST",
            url: this.ajaxUrl,
            data: $("#smartslider-form").serialize(),
            dataType: "json"
        }).done(function (response) {
            var modal = new NextendModal({
                zero: {
                    size: [
                        1300,
                        700
                    ],
                    title: "Records",
                    content: response.data.html
                }
            }, true);
            modal.content.css('overflow', 'auto');
        }).error(function (response) {
            if (response.status == 200) {
                var modal = new NextendModal({
                    zero: {
                        size: [
                            1300,
                            700
                        ],
                        title: "Response",
                        content: response.responseText
                    }
                }, true);
                modal.content.css('overflow', 'auto');
            }
        });
    };

    scope.NextendSmartSliderGeneratorRecords = GeneratorRecords;
})(n2, window);
(function (smartSlider, $, scope, undefined) {

    function Item(item, layer, itemEditor, createPosition) {
        this.item = item;
        this.layer = layer;
        this.itemEditor = itemEditor;

        this.type = this.item.data('item');
        this.values = this.item.data('itemvalues');

        if (typeof this.values !== 'object') {
            this.values = $.parseJSON(this.values);
        }

        if (scope['NextendSmartSliderItemParser_' + this.type] !== undefined) {
            this.parser = new scope['NextendSmartSliderItemParser_' + this.type](this);
        } else {
            this.parser = new scope['NextendSmartSliderItemParser'](this);
        }
        this.item.data('item', this);

        if (typeof createPosition !== 'undefined') {
            if (this.layer.items.length == 0 || this.layer.items.length <= createPosition) {
                this.item.appendTo(this.layer.layer);
            } else {
                this.layer.items[createPosition].item.before(this.item);
            }
        }

        if (typeof createPosition === 'undefined' || this.layer.items.length == 0 || this.layer.items.length <= createPosition) {
            this.layer.items.push(this);
        } else {
            this.layer.items.splice(createPosition, 0, this);
        }

        if (this.item.children().length === 0) {
            this.reRender();
        }


        $('<div/>')
            .addClass('ui-helper ui-item-overlay')
            .css('zIndex', 89)
            .appendTo(this.item);

        $(window).trigger('ItemCreated');
    };

    Item.prototype.changeValue = function (property, value) {
        if (this == this.itemEditor.activeItem) {
            $('#item_' + this.type + property).data('field')
                .insideChange(value);
        } else {
            this.values[property] = value;
        }
    };

    Item.prototype.activate = function (e, force) {
        this.itemEditor.setActiveItem(this, force);
    };

    Item.prototype.deActivate = function () {
    };

    Item.prototype.render = function (html, data, originalData) {
        this.layer.layer.triggerHandler('itemRender');
        this.item.html(this.parser.render(html, data));

        // These will be available on the backend render
        this.values = originalData;

        $('<div/>')
            .addClass('ui-helper ui-item-overlay')
            .css('zIndex', 89)
            .appendTo(this.item);

        var layerName = this.parser.getName(data);
        if (layerName === false) {
            layerName = this.type;
        } else {
            layerName = layerName.replace(/[<> ]/gi, '');
        }
        this.layer.rename(layerName, false);

        this.layer.update();
    };

    Item.prototype.reRender = function (newData) {

        var data = {},
            itemEditor = this.itemEditor,
            form = itemEditor.getItemType(this.type),
            html = form.template;

        for (var name in this.values) {
            data[name] = this.values[name];
            //$.extend(data, this.parser.parse(name, data[name]));
        }

        data = $.extend({}, this.parser.getDefault(), data, newData);

        var originalData = $.extend({}, data);

        this.parser.parseAll(data, this);
        this.values = originalData;

        for (var k in data) {
            var reg = new RegExp('\\{' + k + '\\}', 'g');
            html = html.replace(reg, data[k]);
        }

        this.render($(html), data, this.values);
    };

    Item.prototype.duplicate = function () {
        this.layer.addItem(this.getHTML(), true);
    };

    Item.prototype.delete = function () {
        this.item.trigger('mouseleave');
        this.item.remove();

        if (this.itemEditor.activeItem == this) {
            this.itemEditor.activeItem = null;
        }

        delete this.itemEditor;
        delete this.layer;
    };

    Item.prototype.getHTML = function (base64) {
        var item = '';
        if (base64) {

            item = '[' + this.type + ' values="' + Base64.encode(JSON.stringify(this.values)) + '"]';
        } else {
            item = $('<div class="n2-ss-item n2-ss-item-' + this.type + '"></div>')
                .attr('data-item', this.type)
                .attr('data-itemvalues', JSON.stringify(this.values));
        }
        return item;
    };

    Item.prototype.getData = function () {
        return {
            type: this.type,
            values: this.values
        };
    };

    scope.NextendSmartSliderItem = Item;
})(nextend.smartSlider, n2, window);
(function (smartSlider, $, scope, undefined) {

    function ItemManager(layerEditor) {
        this.suppressChange = false;

        this.layerEditor = layerEditor;

        this._initInstalledItems();

        this.form = {};
        this.activeForm = {
            form: $('<div></div>')
        };
    }

    ItemManager.prototype.setActiveItem = function (item, force) {
        if (item != this.activeItem || force) {
            var type = item.type,
                values = item.values;

            this.activeForm.form.css('display', 'none');

            this.activeForm = this.getItemType(type);

            if (this.activeItem) {
                this.activeItem.deActivate();
            }

            this.activeItem = item;

            this.suppressChange = true;

            for (var key in values) {
                var field = $('#item_' + type + key).data('field');
                if (field) {
                    field.insideChange(values[key]);
                }
            }

            this.suppressChange = false;

            this.activeForm.form.css('display', 'block');
        }
    };

    ItemManager.prototype._initInstalledItems = function () {

        $('#n2-ss-item-container .n2-ss-core-item')
            .on('click', $.proxy(function (e) {
                this.createLayerItem($(e.currentTarget).data('item'));
            }, this));
    };

    ItemManager.prototype.createLayerItem = function (type) {
        var itemData = this.getItemType(type),
            layer = this.layerEditor.createLayer($('.n2-ss-core-item-' + type).data('layerproperties'));

        var itemNode = $('<div></div>').data('item', type).data('itemvalues', $.extend(true, {}, itemData.values))
            .addClass('n2-ss-item n2-ss-item-' + type);

        var item = new scope.NextendSmartSliderItem(itemNode, layer, this, 0);
        layer.activate();

        smartSlider.sidebarManager.switchTab(0);

        return item;
    };

    /**
     * Initialize an item type and subscribe the field changes on that type.
     * We use event normalization to stop not necessary rendering.
     * @param type
     * @private
     */
    ItemManager.prototype.getItemType = function (type) {
        if (this.form[type] === undefined) {
            var form = $('#smartslider-slide-toolbox-item-type-' + type),
                formData = {
                    form: form,
                    template: form.data('itemtemplate'),
                    values: form.data('itemvalues'),
                    fields: form.find('[name^="item_' + type + '"]'),
                    fieldNameRegexp: new RegExp('item_' + type + "\\[(.*?)\\]", "")
                };
            formData.fields.on({
                nextendChange: $.proxy(this.updateCurrentItem, this),
                keydown: $.proxy(this.updateCurrentItemDeBounced, this)
            });

            this.form[type] = formData;
        }
        return this.form[type];
    };

    /**
     * This function renders the current item with the current values of the related form field.
     */
    ItemManager.prototype.updateCurrentItem = function (e) {
        if (!this.suppressChange) {
            var data = {},
                originalData = {},
                form = this.form[this.activeItem.type],
                html = form.template,
                parser = this.activeItem.parser;

            // Get the current values of the fields
            // Run through the related item filter
            // Replace the variables in the template of the item type
            form.fields.each($.proxy(function (i, field) {
                var field = $(field),
                    name = field.attr('name').match(form.fieldNameRegexp)[1];

                originalData[name] = data[name] = field.val();

            }, this));

            data = $.extend({}, parser.getDefault(), data);

            parser.parseAll(data, this.activeItem);
            for (var k in data) {
                var reg = new RegExp('\\{' + k + '\\}', 'g');
                html = html.replace(reg, data[k]);
            }

            this.activeItem.render($(html), data, originalData);
        }
    };

    ItemManager.prototype.updateCurrentItemDeBounced = NextendDeBounce(function (e) {
        this.updateCurrentItem(e);
    }, 100);

    scope.NextendSmartSliderItemManager = ItemManager;

})(nextend.smartSlider, n2, window);
(function ($, scope, undefined) {

    function ItemParser(item) {
        this.pre = 'div#' + nextend.smartSlider.frontend.sliderElement.attr('id') + ' ';

        this.item = item;

        this.fonts = [];
        this.styles = [];

        this.needFill = [];
        this.added();
    }

    ItemParser.prototype.getDefault = function () {
        return {};
    };

    ItemParser.prototype.added = function () {

    };

    ItemParser.prototype.addedFont = function (mode, name) {
        this.fonts.push({
            mode: mode,
            name: name
        });
        $.when(nextend.fontManager.addVisualUsage(mode, this.item.values[name], this.pre))
            .done($.proxy(function (existsFont) {
                if (!existsFont) {
                    this.item.changeValue(name, '');
                }
            }, this));
    };

    ItemParser.prototype.addedStyle = function (mode, name) {
        this.styles.push({
            mode: mode,
            name: name
        });
        $.when(nextend.styleManager.addVisualUsage(mode, this.item.values[name], this.pre))
            .done($.proxy(function (existsStyle) {
                if (!existsStyle) {
                    this.item.changeValue(name, '');
                }
            }, this));

    };

    ItemParser.prototype.parseAll = function (data, item) {

        for (var i = 0; i < this.fonts.length; i++) {
            data[this.fonts[i].name + 'class'] = nextend.fontManager.getClass(data[this.fonts[i].name], this.fonts[i].mode) + ' ';
        }

        for (var i = 0; i < this.styles.length; i++) {
            data[this.styles[i].name + 'class'] = nextend.styleManager.getClass(data[this.styles[i].name], this.styles[i].mode) + ' ';
        }
        for (var i = 0; i < this.needFill.length; i++) {
            data[this.needFill[i]] = nextend.smartSlider.generator.fill(data[this.needFill[i]]);
        }
    };

    ItemParser.prototype.render = function (node, data) {
        return node;
    };

    ItemParser.prototype.getName = function (data) {
        return false;
    };

    ItemParser.prototype.resizeLayerToImage = function (item, image) {
        $("<img/>")
            .attr("src", image)
            .load(function () {
                var slideSize = item.layer.layerEditor.slideSize;
                var maxWidth = slideSize.width,
                    maxHeight = slideSize.height;

                if (this.width > 0 && this.height > 0) {
                    maxWidth = parseInt(Math.min(this.width, maxWidth));
                    maxHeight = parseInt(Math.min(this.height, maxHeight));
                    if (slideSize.width / slideSize.height <= maxWidth / maxHeight) {
                        item.layer.setProperty('width', maxWidth);
                        item.layer.setProperty('height', this.height * maxWidth / this.width);
                    } else {
                        var width = Math.min(this.width * slideSize.height / this.height, maxWidth);
                        item.layer.setProperty('width', width);
                        item.layer.setProperty('height', this.height * width / this.width);
                    }
                }
            });
    };

    ItemParser.prototype.fitLayer = function (item) {
        return false;
    };

    scope.NextendSmartSliderItemParser = ItemParser;

})(n2, window);
(function ($, scope, undefined) {

    function ItemParserButton() {
        NextendSmartSliderItemParser.apply(this, arguments);
    };

    ItemParserButton.prototype = Object.create(NextendSmartSliderItemParser.prototype);
    ItemParserButton.prototype.constructor = ItemParserButton;

    ItemParserButton.prototype.added = function () {
        this.needFill = ['content', 'url'];
        this.addedFont('link', 'font');
        this.addedStyle('button', 'style');

        nextend.smartSlider.generator.registerField($('#item_buttoncontent'));
        nextend.smartSlider.generator.registerField($('#linkitem_buttonlink_0'));
    };

    ItemParserButton.prototype.getName = function (data) {
        return data.content;
    };

    ItemParserButton.prototype.parseAll = function (data) {
        var link = data.link.split('|*|');
        data.url = link[0];
        data.target = link[1];
        delete data.link;

        if (data.fullwidth | 0) {
            data.display = 'block;';
        } else {
            data.display = 'inline-block;';
        }

        data.extrastyle = data.nowrap | 0 ? 'white-space: nowrap;' : '';

        NextendSmartSliderItemParser.prototype.parseAll.apply(this, arguments);
    };

    scope.NextendSmartSliderItemParser_button = ItemParserButton;
})(n2, window);
(function ($, scope, undefined) {

    function ItemParserHeading() {
        NextendSmartSliderItemParser.apply(this, arguments);
    };

    ItemParserHeading.prototype = Object.create(NextendSmartSliderItemParser.prototype);
    ItemParserHeading.prototype.constructor = ItemParserHeading;

    ItemParserHeading.prototype.getDefault = function () {
        return {
            link: '#|*|_self',
            font: '',
            style: ''
        }
    };

    ItemParserHeading.prototype.added = function () {
        this.needFill = ['heading', 'url'];

        this.addedFont('hover', 'font');
        this.addedStyle('heading', 'style');

        nextend.smartSlider.generator.registerField($('#item_headingheading'));
        nextend.smartSlider.generator.registerField($('#linkitem_headinglink_0'));

    };

    ItemParserHeading.prototype.getName = function (data) {
        return data.heading;
    };

    ItemParserHeading.prototype.parseAll = function (data) {

        data.uid = $.fn.uid();

        var link = data.link.split('|*|');
        data.url = link[0];
        data.target = link[1];
        delete data.link;


        if (data.fullwidth | 0) {
            data.display = 'block;';
        } else {
            data.display = 'inline-block;';
        }

        data.extrastyle = data.nowrap | 0 ? 'white-space: nowrap;' : '';

        data.heading = $('<div>' + data.heading + '</div>').text().replace(/\n/g, '<br />');
        data.priority = 2;
        data.class = '';
    

        NextendSmartSliderItemParser.prototype.parseAll.apply(this, arguments);

        if (data['url'] == '#') {
            data['afontclass'] = '';
        } else {
            data['afontclass'] = data['fontclass'];
            data['fontclass'] = '';
        }
    };

    ItemParserHeading.prototype.render = function (node, data) {
        if (data['url'] == '#') {
            var a = node.find('a');
            a.parent().html(a.html());
        }
        return node;
    }

    scope.NextendSmartSliderItemParser_heading = ItemParserHeading;
})(n2, window);
(function ($, scope, undefined) {

    function ItemParserImage() {
        NextendSmartSliderItemParser.apply(this, arguments);
    };

    ItemParserImage.prototype = Object.create(NextendSmartSliderItemParser.prototype);
    ItemParserImage.prototype.constructor = ItemParserImage;

    ItemParserImage.prototype.getDefault = function () {
        return {
            size: '100%|*|auto',
            link: '#|*|_self',
            style: ''
        }
    };

    ItemParserImage.prototype.added = function () {
        this.needFill = ['image', 'url'];

        this.addedStyle('box', 'style');

        nextend.smartSlider.generator.registerField($('#item_imageimage'));
        nextend.smartSlider.generator.registerField($('#item_imagealt'));
        nextend.smartSlider.generator.registerField($('#item_imagetitle'));
        nextend.smartSlider.generator.registerField($('#linkitem_imagelink_0'));
    };

    ItemParserImage.prototype.getName = function (data) {
        return data.image.split('/').pop();
    };

    ItemParserImage.prototype.parseAll = function (data, item) {
        var size = data.size.split('|*|');
        data.width = size[0];
        data.height = size[1];
        delete data.size;

        var link = data.link.split('|*|');
        data.url = link[0];
        data.target = link[1];
        delete data.link;

        NextendSmartSliderItemParser.prototype.parseAll.apply(this, arguments);

        if (item && item.values.image == '$system$/images/placeholder/image.png' && data.image != item.values.image) {
            data.image = nextend.imageHelper.fixed(data.image);
            this.resizeLayerToImage(item, data.image);
        } else {
            data.image = nextend.imageHelper.fixed(data.image);
        }

    };

    ItemParserImage.prototype.fitLayer = function (item) {
        this.resizeLayerToImage(item, nextend.imageHelper.fixed(item.values.image));
        return true;
    };

    ItemParserImage.prototype.render = function (node, data) {
        if (data['url'] == '#') {
            node.html(node.children('a').html());
        }
        return node;
    };

    scope.NextendSmartSliderItemParser_image = ItemParserImage;
})(n2, window);

(function ($, scope, undefined) {

    function ItemParserText() {
        NextendSmartSliderItemParser.apply(this, arguments);
    };

    ItemParserText.prototype = Object.create(NextendSmartSliderItemParser.prototype);
    ItemParserText.prototype.constructor = ItemParserText;

    ItemParserText.prototype.getDefault = function () {
        return {
            contentmobile: '',
            contenttablet: '',
            font: '',
            style: ''
        }
    };

    ItemParserText.prototype.added = function () {
        this.needFill = ['content', 'contenttablet', 'contentmobile'];

        this.addedFont('paragraph', 'font');
        this.addedStyle('heading', 'style');

        nextend.smartSlider.generator.registerField($('#item_textcontent'));
        nextend.smartSlider.generator.registerField($('#item_textcontenttablet'));
        nextend.smartSlider.generator.registerField($('#item_textcontentmobile'));
    };

    ItemParserText.prototype.getName = function (data) {
        return data.content;
    };

    ItemParserText.prototype.parseAll = function (data) {
        NextendSmartSliderItemParser.prototype.parseAll.apply(this, arguments);

        data['p'] = _wp_Autop(data['content']);
        data['ptablet'] = _wp_Autop(data['contenttablet']);
        data['pmobile'] = _wp_Autop(data['contentmobile']);
    };
    ItemParserText.prototype.render = function (node, data) {
        if (data['contenttablet'] == '') {
            node = node.filter(':not(.n2-ss-tablet)');
            node.filter('.n2-ss-desktop').addClass('n2-ss-tablet');
        }
        if (data['contentmobile'] == '') {
            node = node.filter(':not(.n2-ss-mobile)');
            node.filter('.n2-ss-tablet, .n2-ss-desktop').last().addClass('n2-ss-mobile');
        }

        node.find('p').addClass(data['fontclass'] + ' ' + data['styleclass']);
        node.find('a').on('click', function (e) {
            e.preventDefault();
        });
        return node;
    };

    scope.NextendSmartSliderItemParser_text = ItemParserText;

    function _wp_Autop(pee) {
        var preserve_linebreaks = false,
            preserve_br = false,
            blocklist = 'table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre' +
                '|form|map|area|blockquote|address|math|style|p|h[1-6]|hr|fieldset|noscript|legend|section' +
                '|article|aside|hgroup|header|footer|nav|figure|details|menu|summary';

        if (pee.indexOf('<object') !== -1) {
            pee = pee.replace(/<object[\s\S]+?<\/object>/g, function (a) {
                return a.replace(/[\r\n]+/g, '');
            });
        }

        pee = pee.replace(/<[^<>]+>/g, function (a) {
            return a.replace(/[\r\n]+/g, ' ');
        });

        // Protect pre|script tags
        if (pee.indexOf('<pre') !== -1 || pee.indexOf('<script') !== -1) {
            preserve_linebreaks = true;
            pee = pee.replace(/<(pre|script)[^>]*>[\s\S]+?<\/\1>/g, function (a) {
                return a.replace(/(\r\n|\n)/g, '<wp-line-break>');
            });
        }

        // keep <br> tags inside captions and convert line breaks
        if (pee.indexOf('[caption') !== -1) {
            preserve_br = true;
            pee = pee.replace(/\[caption[\s\S]+?\[\/caption\]/g, function (a) {
                // keep existing <br>
                a = a.replace(/<br([^>]*)>/g, '<wp-temp-br$1>');
                // no line breaks inside HTML tags
                a = a.replace(/<[a-zA-Z0-9]+( [^<>]+)?>/g, function (b) {
                    return b.replace(/[\r\n\t]+/, ' ');
                });
                // convert remaining line breaks to <br>
                return a.replace(/\s*\n\s*/g, '<wp-temp-br />');
            });
        }

        pee = pee + '\n\n';
        pee = pee.replace(/<br \/>\s*<br \/>/gi, '\n\n');
        pee = pee.replace(new RegExp('(<(?:' + blocklist + ')(?: [^>]*)?>)', 'gi'), '\n$1');
        pee = pee.replace(new RegExp('(</(?:' + blocklist + ')>)', 'gi'), '$1\n\n');
        pee = pee.replace(/<hr( [^>]*)?>/gi, '<hr$1>\n\n'); // hr is self closing block element
        pee = pee.replace(/\r\n|\r/g, '\n');
        pee = pee.replace(/\n\s*\n+/g, '\n\n');
        pee = pee.replace(/([\s\S]+?)\n\n/g, '<p>$1</p>\n');
        pee = pee.replace(/<p>\s*?<\/p>/gi, '');
        pee = pee.replace(new RegExp('<p>\\s*(</?(?:' + blocklist + ')(?: [^>]*)?>)\\s*</p>', 'gi'), '$1');
        pee = pee.replace(/<p>(<li.+?)<\/p>/gi, '$1');
        pee = pee.replace(/<p>\s*<blockquote([^>]*)>/gi, '<blockquote$1><p>');
        pee = pee.replace(/<\/blockquote>\s*<\/p>/gi, '</p></blockquote>');
        pee = pee.replace(new RegExp('<p>\\s*(</?(?:' + blocklist + ')(?: [^>]*)?>)', 'gi'), '$1');
        pee = pee.replace(new RegExp('(</?(?:' + blocklist + ')(?: [^>]*)?>)\\s*</p>', 'gi'), '$1');
        pee = pee.replace(/\s*\n/gi, '<br />\n');
        pee = pee.replace(new RegExp('(</?(?:' + blocklist + ')[^>]*>)\\s*<br />', 'gi'), '$1');
        pee = pee.replace(/<br \/>(\s*<\/?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)>)/gi, '$1');
        pee = pee.replace(/(?:<p>|<br ?\/?>)*\s*\[caption([^\[]+)\[\/caption\]\s*(?:<\/p>|<br ?\/?>)*/gi, '[caption$1[/caption]');

        pee = pee.replace(/(<(?:div|th|td|form|fieldset|dd)[^>]*>)(.*?)<\/p>/g, function (a, b, c) {
            if (c.match(/<p( [^>]*)?>/)) {
                return a;
            }

            return b + '<p>' + c + '</p>';
        });

        // put back the line breaks in pre|script
        if (preserve_linebreaks) {
            pee = pee.replace(/<wp-line-break>/g, '\n');
        }

        if (preserve_br) {
            pee = pee.replace(/<wp-temp-br([^>]*)>/g, '<br$1>');
        }

        return pee;
    };
})(n2, window);
(function ($, scope, undefined) {

    function ItemParserVimeo() {
        NextendSmartSliderItemParser.apply(this, arguments);
    };

    ItemParserVimeo.prototype = Object.create(NextendSmartSliderItemParser.prototype);
    ItemParserVimeo.prototype.constructor = ItemParserVimeo;

    ItemParserVimeo.prototype.added = function () {
        this.needFill = ['vimeourl'];

        nextend.smartSlider.generator.registerField($('#item_vimeovimeourl'));
    };

    ItemParserVimeo.prototype.getName = function (data) {
        return data.vimeourl;
    };

    ItemParserVimeo.prototype.parseAll = function (data, item) {
        var vimeoChanged = item.values.vimeourl != data.vimeourl;

        NextendSmartSliderItemParser.prototype.parseAll.apply(this, arguments);

        if (data.image == '') {
            data.image = '$system$/images/placeholder/video.png';
        }

        data.image = nextend.imageHelper.fixed(data.image);

        if (vimeoChanged && data.vimeourl != '') {
            var vimeoRegexp = /https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)/,
                vimeoMatch = data.vimeourl.match(vimeoRegexp);

            var videoCode = false;
            if (vimeoMatch) {
                videoCode = vimeoMatch[3];
            } else if (data.vimeourl.match(/^[0-9]+$/)) {
                videoCode = data.vimeourl;
            }

            if (videoCode) {
                NextendAjaxHelper.getJSON('https://vimeo.com/api/v2/video/' + encodeURI(videoCode) + '.json').done($.proxy(function (data) {
                    $('#item_vimeoimage').val(data[0].thumbnail_large).trigger('change');
                }, this)).fail(function (data) {
                    nextend.notificationCenter.error(data.responseText);
                });
            } else {
                nextend.notificationCenter.error('The provided URL does not match any known Vimeo url or code!');
            }
        }
    };

    ItemParserVimeo.prototype.fitLayer = function (item) {
        return true;
    };

    scope.NextendSmartSliderItemParser_vimeo = ItemParserVimeo;
})(n2, window);
(function ($, scope, undefined) {

    function ItemParserYouTube() {
        NextendSmartSliderItemParser.apply(this, arguments);
    };

    ItemParserYouTube.prototype = Object.create(NextendSmartSliderItemParser.prototype);
    ItemParserYouTube.prototype.constructor = ItemParserYouTube;

    ItemParserYouTube.prototype.added = function () {
        this.needFill = ['youtubeurl'];

        nextend.smartSlider.generator.registerField($('#item_youtubeyoutubeurl'));
    };

    ItemParserYouTube.prototype.getName = function (data) {
        return data.youtubeurl;
    };

    ItemParserYouTube.prototype.parseAll = function (data, item) {

        var youTubeChanged = item.values.youtubeurl != data.youtubeurl;

        NextendSmartSliderItemParser.prototype.parseAll.apply(this, arguments);

        if (data.image == '') {
            data.image = '$system$/images/placeholder/video.png';
        }

        data.image = nextend.imageHelper.fixed(data.image);

        if (youTubeChanged) {
            var youtubeRegexp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/,
                youtubeMatch = data.youtubeurl.match(youtubeRegexp);

            if (youtubeMatch) {
                NextendAjaxHelper.getJSON('https://www.googleapis.com/youtube/v3/videos?id=' + encodeURI(youtubeMatch[2]) + '&part=snippet&key=AIzaSyC3AolfvPAPlJs-2FgyPJdEEKS6nbPHdSM').done($.proxy(function (data) {
                    if (data.items.length) {

                        var thumbnails = data.items[0].snippet.thumbnails,
                            thumbnail = thumbnails.maxres || thumbnails.standard || thumbnails.high || thumbnails.medium || thumbnails.default;

                        $('#item_youtubeimage').val(thumbnail.url).trigger('change');
                    }
                }, this)).fail(function (data) {
                    nextend.notificationCenter.error(data.error.errors[0].message);
                });
            } else {
                nextend.notificationCenter.error('The provided URL does not match any known YouTube url or code!');
            }
        }
    };

    ItemParserYouTube.prototype.fitLayer = function (item) {
        return true;
    };

    scope.NextendSmartSliderItemParser_youtube = ItemParserYouTube;
})(n2, window);
(function (smartSlider, $, scope, undefined) {
    "use strict";
    function LayerAnimation(animations, group, data) {
        this.$ = $(this);
        this.animations = animations;
        this.group = group;
        this.data = data;

        this.row = $('<li class="n2-ss-animation-row"></li>')
            .data('animation', this);

        var handle = $('<div class="n2-ss-animation-title"></div>')
            .appendTo(this.row);

        this.label = $('<span>' + this.data.name + '</span>')
            .appendTo(handle);

        var actions = $('<div class="n2-actions"></div>')
            .append($('<a onclick="return false;" href="#"><i class="n2-i n2-i-delete n2-i-grey-opacity"></i></a>')
                .on('click', $.proxy(this.delete, this)))
            .appendTo(handle);

    };

    LayerAnimation.prototype.getRow = function () {
        return this.row;
    };

    LayerAnimation.prototype.edit = function () {
        this.animations.edit(this.group, this.animations[this.group + 'Rows'].index(this.row));
    };

    LayerAnimation.prototype.save = function (data) {
        if (data !== false) {
            this.data = data;
            this.label.html(data.name);

            this.$.trigger('animationChanged');
        }
    };

    LayerAnimation.prototype.delete = function (e) {
        if (e) {
            e.stopPropagation();
        }
        this.row.remove();
        this.animations.removeAnimation(this);
        smartSlider.layerAnimationManager.update(this.group);

        this.$.trigger('animationDeleted');
    };

    LayerAnimation.prototype.setDelay = function (newDelay) {
        this.data.delay = newDelay;
    };

    LayerAnimation.prototype.setDuration = function (newDuration) {
        this.data.duration = newDuration;
    };

    scope.NextendSmartSliderLayerAnimation = LayerAnimation;

})(nextend.smartSlider, n2, window);
(function (smartSlider, $, scope, undefined) {
    function LayerAnimationManager(layerEditor) {

        this.layerEditor = layerEditor;

        this.createGroup('in', n2_('in'), '#layer-animation-chain-in');
        this.lists = this.in.list;

        this.createGroup('loop', n2_('loop'), '#layer-animation-chain-loop');
        this.createGroup('out', n2_('out'), '#layer-animation-chain-out');
        this.lists = this.lists.add(this.loop.list).add(this.out.list);


        smartSlider.layerAnimationManager = this;
    };

    LayerAnimationManager.prototype.createGroup = function (identifier, label, container) {
        container = $(container);
        var header = $('<div class="n2-sidebar-row n2-sidebar-header-bg n2-form-dark n2-sets-header"><div class="n2-table"><div class="n2-tr"><div class="n2-td"><div class="n2-h3 n2-uc">' + label + '</div></div><div style="text-align: ' + (nextend.isRTL() ? 'left' : 'right') + ';" class="n2-td"></div></div></div></div>').appendTo(container),
            buttonPlaceholder = header.find('.n2-td').eq(1);

        this[identifier] = {
            container: container,
            header: header,
            list: $('<ul class="n2-list n2-h4 n2-list-orderable n2-ss-animation-list"></ul>')
                .on('click', $.proxy(this.editGroup, this, identifier))
                .data('group', identifier)
                .appendTo(container),
            add: this.getAddButton(identifier, n2_('Add')).appendTo(buttonPlaceholder),
            clear: this.getClearButton(identifier).appendTo(buttonPlaceholder)
        };
    };

    LayerAnimationManager.prototype.getAddButton = function (identifier, label) {
        var button = $('<a href="#" class="n2-button n2-button-medium n2-button-green n2-h5 n2-uc">' + label + '</a>')
            .on('click', $.proxy(this.createAnimation, this, identifier));
        return button;
    };

    LayerAnimationManager.prototype.getClearButton = function (identifier) {
        var button = $('<a href="#" class="n2-button n2-button-medium n2-button-grey n2-h5 n2-uc">' + n2_('Clear') + '</a>')
            .on('click', $.proxy(this.clear, this, identifier));
        return button;
    };

    LayerAnimationManager.prototype.getActiveLayer = function () {
        return this.layerEditor.layerList[this.layerEditor.activeLayerIndex];
    };

    LayerAnimationManager.prototype.editGroup = function (identifier, e) {
        var index = 0;
        if (e) {
            e.preventDefault();
            index = $(e.target).closest('.n2-ss-animation-row').index();
        }
        if (index != -1) {
            var layerAnimations = this.getActiveLayer().animation;
            layerAnimations.edit(identifier, index);
        }
    };

    LayerAnimationManager.prototype.clear = function (group, e) {
        if (e) {
            e.preventDefault();
        }

        this.getActiveLayer().animation.clear(group);
    };

    LayerAnimationManager.prototype.createAnimation = function (group, e) {
        if (e) {
            e.preventDefault();
        }
        var activeLayer = this.getActiveLayer(),
            $layer = activeLayer.layer,
            animationManager = nextend.animationManager;

        animationManager.controller
            .setPreviewSize($layer.width(), $layer.height())
            .setGroup(group);

        var features = {
            repeatable: 1
        };

        if (group == 'in') {
            features.specialZero = 1;
            features.playEvent = 1;
            animationManager.changeSetById(1000);
            animationManager.setTitle(n2_('In animation'));
        } else if (group == 'loop') {
            features.repeat = 1;

            features.playEvent = 1;
            features.pauseEvent = 1;
            features.stopEvent = 1;
            animationManager.changeSetById(1200);
            animationManager.setTitle(n2_('Loop animation'));
        } else if (group == 'out') {
            features.playEvent = 1;
            features.instantOut = 1;
            animationManager.changeSetById(1000);
            animationManager.setTitle(n2_('Out animation'));
        }

        animationManager.show(features, {
            animations: [],
            transformOrigin: '50|*|50|*|0',
            specialZero: activeLayer.animation.data.specialZero,
            repeatCount: activeLayer.animation.data.repeatCount,
            repeatDelay: activeLayer.animation.data.repeatDelay,
            playEvent: '',
            pauseEvent: '',
            stopEvent: '',
            repeatable: activeLayer.animation.data.repeatable,
            instantOut: activeLayer.animation.data.instantOut
        }, $.proxy(this.storeNewAnimation, this, group), {
            previewMode: false,
            previewHTML: false
        });
    };

    LayerAnimationManager.prototype.storeNewAnimation = function (group, e, animationStack) {
        if (animationStack.animations.length > 0) {
            var layerAnimations = this.getActiveLayer().animation;
            layerAnimations.setTransformOrigin(group, animationStack.transformOrigin);
            layerAnimations.setRepeatable(animationStack.repeatable);

            if (group == 'in') {
                layerAnimations.setSpecialZero(group, animationStack.specialZero);
                layerAnimations.setEvent(group, 'PlayEvent', animationStack.playEvent);
            } else if (group == 'loop') {
                layerAnimations.setRepeatCount(group, animationStack.repeatCount);
                layerAnimations.setRepeatStartDelay(group, animationStack.repeatStartDelay);

                layerAnimations.setEvent(group, 'PlayEvent', animationStack.playEvent);
                layerAnimations.setEvent(group, 'PauseEvent', animationStack.pauseEvent);
                layerAnimations.setEvent(group, 'StopEvent', animationStack.stopEvent);
            } else if (group == 'out') {
                layerAnimations.setEvent(group, 'PlayEvent', animationStack.playEvent);
                layerAnimations.setInstantOut(animationStack.instantOut);
            }

            for (var i = 0; i < animationStack.animations.length; i++) {
                layerAnimations.addAnimation(group, animationStack.animations[i]);
            }

            this.update(group);
            $(window).triggerHandler('AnimationAdded');
        }

        this.update(group);
    };

    /**
     * @param animations {NextendSmartSliderLayerAnimations}
     */
    LayerAnimationManager.prototype.activateAnimations = function (animations) {
        animations.inRows.prependTo(this.in.list);

        animations.loopRows.prependTo(this.loop.list);
        animations.outRows.prependTo(this.out.list);


        this.update('in');
        this.update('loop');
        this.update('out');

    };

    LayerAnimationManager.prototype.update = function (group) {
        if (this[group].list.children().length) {
            this[group].add.css('display', 'none');
            this[group].clear.css('display', '');
        } else {
            this[group].add.css('display', '');
            this[group].clear.css('display', 'none');
        }
    };

    scope.NextendSmartSliderLayerAnimationManager = LayerAnimationManager;

})(nextend.smartSlider, n2, window);
(function (smartSlider, $, scope, undefined) {

    var defaults = {
        repeatable: 0,

        in: [],
        specialZeroIn: 0,
        transformOriginIn: '50|*|50|*|0',
        inPlayEvent: '',

        loop: [],
        repeatCount: 0,
        repeatStartDelay: 0,
        transformOriginLoop: '50|*|50|*|0',
        loopPlayEvent: '',
        loopPauseEvent: '',
        loopStopEvent: '',

        out: [],
        transformOriginOut: '50|*|50|*|0',
        outPlayEvent: '',
        instantOut: 1

    };

    function LayerAnimations(layer) {
        this._loaded = false;
        this.active = false;
        this.layer = layer;

        this.data = null;

        layer.layer.data('adminLayerAnimations', this);

        this.inRows = $();
        this.loopRows = $();
        this.outRows = $();

        //this.load();
    };

    /**
     * Here should we remove the nodes what we have added previously
     */
    LayerAnimations.prototype.deActivate = function () {

        this.active = false;
        this.inRows.detach();
        this.loopRows.detach();
        this.outRows.detach();
    };

    /**
     * Add nodes to the layer animation panel when it is activated
     */
    LayerAnimations.prototype.activate = function () {

        // Lazy load...
        //this.load();

        this.active = true;

        smartSlider.layerAnimationManager.activateAnimations(this);
    };

    LayerAnimations.prototype.addAnimation = function (group, data) {
        var animation = new NextendSmartSliderLayerAnimation(this, group, data),
            row = animation.getRow();

        this[group + 'Rows'] = this[group + 'Rows']
            .add(row);

        if (this.active) {
            row.appendTo(smartSlider.layerAnimationManager[group].list);
        }

        this.layer.$.trigger('layerAnimationAdded', [group, animation]);
    };

    /**
     * @param {NextendSmartSliderLayerAnimation} animationObject
     */
    LayerAnimations.prototype.removeAnimation = function (animationObject) {
        var group = animationObject.group;
        this[group + 'Rows'] = this[group + 'Rows'].not(animationObject.row);
    };

    LayerAnimations.prototype.clear = function (group) {
        var rows = this[group + 'Rows'];
        for (var i = 0; i < rows.length; i++) {
            rows.eq(i).data('animation').delete();
        }
    };

    LayerAnimations.prototype.edit = function (group, index) {
        var animations = [];
        for (var i = 0; i < this[group + 'Rows'].length; i++) {
            animations.push(this[group + 'Rows'].eq(i).data('animation').data);
        }

        var animationManager = nextend.animationManager;
        animationManager.controller
            .setPreviewSize(this.layer.layer.width(), this.layer.layer.height())
            .setGroup(group);

        var features = {
                repeatable: 1
            },
            data = {
                animations: animations,
                transformOrigin: this.data['transformOrigin' + this.ucfirst(group)],
                repeatable: this.data.repeatable
            };

        if (group == 'in') {
            features.specialZero = 1;
            data.specialZero = this.data.specialZeroIn;

            features.playEvent = 1;
            data.playEvent = this.data.inPlayEvent;
            animationManager.changeSetById(1000);
            animationManager.setTitle(n2_('In animation'));
        } else if (group == 'loop') {
            features.repeat = 1;
            data.repeatCount = this.data.repeatCount;
            data.repeatStartDelay = this.data.repeatStartDelay;

            features.playEvent = 1;
            data.playEvent = this.data.loopPlayEvent;
            features.pauseEvent = 1;
            data.pauseEvent = this.data.loopPauseEvent;
            features.stopEvent = 1;
            data.stopEvent = this.data.loopStopEvent;
            animationManager.changeSetById(1200);
            animationManager.setTitle(n2_('Loop animation'));
        } else if (group == 'out') {
            features.playEvent = 1;
            features.instantOut = 1;
            data.playEvent = this.data.outPlayEvent;
            data.instantOut = this.data.instantOut;
            animationManager.changeSetById(1000);
            animationManager.setTitle(n2_('Out animation'));
        }

        animationManager.show(features, data, $.proxy(this.storeAnimations, this, group), {
            previewMode: false,
            previewHTML: false
        });
        if (index > 0) {
            animationManager.controller.tabField.options.eq(index).trigger('click');
        }
    };

    LayerAnimations.prototype.storeAnimations = function (group, e, animationStack) {
        var i = 0,
            rows = this[group + 'Rows'];

        this.setTransformOrigin(group, animationStack.transformOrigin);
        this.setRepeatable(animationStack.repeatable);

        if (group == 'in') {
            this.setSpecialZero(group, animationStack.specialZero);
            this.setEvent(group, 'PlayEvent', animationStack.playEvent);
        } else if (group == 'loop') {
            this.setRepeatCount(group, animationStack.repeatCount);
            this.setRepeatStartDelay(group, animationStack.repeatStartDelay);
            this.setEvent(group, 'PlayEvent', animationStack.playEvent);
            this.setEvent(group, 'PauseEvent', animationStack.pauseEvent);
            this.setEvent(group, 'StopEvent', animationStack.stopEvent);
        } else if (group == 'out') {
            this.setEvent(group, 'PlayEvent', animationStack.playEvent);
            this.setInstantOut(animationStack.instantOut);
        }

        for (; i < animationStack.animations.length && i < rows.length; i++) {
            rows.eq(i).data('animation').save(animationStack.animations[i]);
        }
        for (; i < animationStack.animations.length; i++) {
            this.addAnimation(group, animationStack.animations[i]);
        }
        for (; i < rows.length; i++) {
            rows.eq(i).data('animation').delete();
        }

        smartSlider.layerAnimationManager.update(group);
    };

    LayerAnimations.prototype.load = function () {
        if (this._loaded === false) {
            var animationsRaw = this.layer.layer.data('animations');

            this.data = {};

            $.extend(this.data, defaults);

            if (typeof animationsRaw !== 'undefined') {
                $.extend(this.data, $.parseJSON(Base64.decode(animationsRaw)));
            }

            this._load('in');
            this._load('loop');
            this._load('out');


            this._loaded = true;
        }
    };

    LayerAnimations.prototype._load = function (group) {

        if (typeof this.data[group] !== 'undefined') {
            for (var i = 0; i < this.data[group].length; i++) {
                this.addAnimation(group, this.data[group][i]);
            }
            delete this.data[group];
        }
    };

    LayerAnimations.prototype.getAnimationsCode = function () {
        if (this._loaded === false) {
            return this.layer.layer.data('animations');
        } else {
            var animations = $.extend({}, this.data, {
                in: [],
                loop: [],
                out: []
            });

            for (var i = 0; i < this.inRows.length; i++) {
                var animation = this.inRows.eq(i).data('animation');
                animations.in.push(animation.data);
            }

            for (var i = 0; i < this.loopRows.length; i++) {
                var animation = this.loopRows.eq(i).data('animation');
                animations.loop.push(animation.data)
            }

            for (var i = 0; i < this.outRows.length; i++) {
                var animation = this.outRows.eq(i).data('animation');
                animations.out.push(animation.data)
            }

            return Base64.encode(JSON.stringify(animations));
        }
    };

    LayerAnimations.prototype.loadData = function (data) {
        this.clear('in');
        this.clear('loop');
        this.clear('out');

        this.data = {};
        $.extend(this.data, defaults);
        $.extend(this.data, data);


        this._load('in');
        this._load('loop');
        this._load('out');
    };

    LayerAnimations.prototype.getData = function () {
        var animations = $.extend({}, this.data, {
            in: [],
            loop: [],
            out: []
        });

        for (var i = 0; i < this.inRows.length; i++) {
            var animation = this.inRows.eq(i).data('animation');
            animations.in.push($.extend(true, {}, animation.data));
        }

        for (var i = 0; i < this.loopRows.length; i++) {
            var animation = this.loopRows.eq(i).data('animation');
            animations.loop.push($.extend(true, {}, animation.data))
        }

        for (var i = 0; i < this.outRows.length; i++) {
            var animation = this.outRows.eq(i).data('animation');
            animations.out.push($.extend(true, {}, animation.data))
        }
        return animations;
    };

    LayerAnimations.prototype.setSpecialZero = function (group, value) {
        value = parseInt(value) ? 1 : 0;
        if (value != this.data['transformOrigin' + this.ucfirst(group)]) {
            this.data.specialZeroIn = value;
            this.layer.$.trigger('layerAnimationSpecialZeroInChanged');
        }
    };

    LayerAnimations.prototype.setRepeatCount = function (group, value) {
        this.data.repeatCount = value;
    };

    LayerAnimations.prototype.setRepeatStartDelay = function (group, value) {
        this.data.repeatStartDelay = value;
    };

    LayerAnimations.prototype.setEvent = function (group, event, value) {
        this.data[group + event] = value;
    };

    LayerAnimations.prototype.setTransformOrigin = function (group, value) {
        this.data['transformOrigin' + this.ucfirst(group)] = value;
    };

    LayerAnimations.prototype.setRepeatable = function (value) {
        this.data.repeatable = parseInt(value) ? 1 : 0;
    };

    LayerAnimations.prototype.setInstantOut = function (value) {
        this.data.instantOut = parseInt(value) ? 1 : 0;
    };

    LayerAnimations.prototype.ucfirst = function (string) {
        return string.charAt(0).toUpperCase() + string.slice(1);
    };

    scope.NextendSmartSliderLayerAnimations = LayerAnimations;

})(nextend.smartSlider, n2, window);
(function (smartSlider, $, scope, undefined) {

    var highlighted = false,
        timeout = null;
    window.nextendPreventClick = false;

    var UNDEFINED,
        rAFShim = (function () {
            var timeLast = 0;

            return window.webkitRequestAnimationFrame || window.mozRequestAnimationFrame || function (callback) {
                    var timeCurrent = (new Date()).getTime(),
                        timeDelta;

                    /* Dynamically set delay on a per-tick basis to match 60fps. */
                    /* Technique by Erik Moller. MIT license: https://gist.github.com/paulirish/1579671 */
                    timeDelta = Math.max(0, 16 - (timeCurrent - timeLast));
                    timeLast = timeCurrent + timeDelta;

                    return setTimeout(function () {
                        callback(timeCurrent + timeDelta);
                    }, timeDelta);
                };
        })(),
        resizeCollection = {
            raf: false,
            ratios: null,
            isThrottled: false,
            layers: []
        },
        requestRender = function () {
            if (resizeCollection.raf === false) {
                resizeCollection.raf = true;
                rAFShim(function () {
                    for (var i = 0; i < resizeCollection.layers.length; i++) {
                        resizeCollection.layers[i].doTheResize(resizeCollection.ratios, true, resizeCollection.isThrottled);
                    }
                    resizeCollection = {
                        raf: false,
                        ratios: null,
                        isThrottled: false,
                        layers: []
                    };
                });
            }
        };

    function Layer(layerEditor, layer, itemEditor, properties) {
        //this.resize = NextendDeBounce(this.resize, 200);
        //this.triggerLayerResized = NextendThrottle(this.triggerLayerResized, 30);
        this._triggerLayerResizedThrottled = NextendThrottle(this._triggerLayerResized, 30);
        //this.doThrottledTheResize = NextendThrottle(this.doTheResize, 16.6666);
        this.markSmallLayer = NextendDeBounce(this.markSmallLayer, 500);
        this.doThrottledTheResize = this.doTheResize;
        this.eye = false;
        this.lock = false;
        this.parent = false;
        this.parentIsVisible = true;
        this.$ = $(this);

        this.layerEditor = layerEditor;

        if (!layer) {
            layer = $('<div class="n2-ss-layer" style="z-index: ' + layerEditor.zIndexList.length + ';"></div>')
                .appendTo(layerEditor.layerContainerElement);
            this.property = $.extend({
                id: null,
                parentid: null,
                parentalign: 'center',
                parentvalign: 'middle',
                name: 'New layer',
                nameSynced: 1,
                crop: 'visible',
                inneralign: 'left',
                parallax: 0,
                align: 'center',
                valign: 'middle',
                fontsize: 100,
                adaptivefont: 0,
                desktopPortrait: 1,
                desktopLandscape: 1,
                tabletPortrait: 1,
                tabletLandscape: 1,
                mobilePortrait: 1,
                mobileLandscape: 1,
                left: 0,
                top: 0,
                responsiveposition: 1,
                width: 'auto',
                height: 'auto',
                responsivesize: 1,
                mouseenter: UNDEFINED,
                click: UNDEFINED,
                mouseleave: UNDEFINED,
                play: UNDEFINED,
                pause: UNDEFINED,
                stop: UNDEFINED
            }, properties);
        } else {
            this.property = {
                id: layer.attr('id'),
                parentid: layer.data('parentid'),
                parentalign: layer.data('desktopportraitparentalign'),
                parentvalign: layer.data('desktopportraitparentvalign'),
                name: layer.data('name') + '',
                nameSynced: layer.data('namesynced'),
                crop: layer.data('crop'),
                inneralign: layer.data('inneralign'),
                parallax: layer.data('parallax'),
                align: layer.data('desktopportraitalign'),
                valign: layer.data('desktopportraitvalign'),
                fontsize: layer.data('desktopportraitfontsize'),
                adaptivefont: layer.data('adaptivefont'),
                desktopPortrait: parseFloat(layer.data('desktopportrait')),
                desktopLandscape: parseFloat(layer.data('desktoplandscape')),
                tabletPortrait: parseFloat(layer.data('tabletportrait')),
                tabletLandscape: parseFloat(layer.data('tabletlandscape')),
                mobilePortrait: parseFloat(layer.data('mobileportrait')),
                mobileLandscape: parseFloat(layer.data('mobilelandscape')),
                left: parseInt(layer.data('desktopportraitleft')),
                top: parseInt(layer.data('desktopportraittop')),
                responsiveposition: parseInt(layer.data('responsiveposition')),
                responsivesize: parseInt(layer.data('responsivesize')),
                mouseenter: layer.data('mouseenter'),
                click: layer.data('click'),
                mouseleave: layer.data('mouseleave'),
                play: layer.data('play'),
                pause: layer.data('pause'),
                stop: layer.data('stop')
            };

            var width = layer.data('desktopportraitwidth');
            if (this.isDimensionPropertyAccepted(width)) {
                this.property.width = width;
            } else {
                this.property.width = parseInt(width);
            }

            var height = layer.data('desktopportraitheight');
            if (this.isDimensionPropertyAccepted(height)) {
                this.property.height = height;
            } else {
                this.property.height = parseInt(height);
            }
        }

        if (!this.property.id) {
            this.property.id = null;
        }

        this.subscribeParentCallbacks = {};
        if (this.property.parentid) {
            this.subscribeParent();
        } else {
            this.property.parentid = null;
        }

        if (!this.property.parentalign) {
            this.property.parentalign = 'center';
        }

        if (!this.property.parentvalign) {
            this.property.parentvalign = 'middle';
        }

        if (typeof this.property.nameSynced === 'undefined') {
            this.property.nameSynced = 1;
        }

        if (typeof this.property.responsiveposition === 'undefined') {
            this.property.responsiveposition = 1;
        }

        if (typeof this.property.responsivesize === 'undefined') {
            this.property.responsivesize = 1;
        }

        if (!this.property.inneralign) {
            this.property.inneralign = 'left';
        }

        if (!this.property.crop) {
            this.property.crop = 'visible';
        }

        if (!this.property.parallax) {
            this.property.parallax = 0;
        }

        if (typeof this.property.fontsize == 'undefined') {
            this.property.fontsize = 100;
        }

        if (typeof this.property.adaptivefont == 'undefined') {
            this.property.adaptivefont = 0;
        }

        if (!this.property.align) {
            this.property.align = 'left';
        }

        if (!this.property.valign) {
            this.property.valign = 'top';
        }
        layer.attr('data-align', this.property.align);
        layer.attr('data-valign', this.property.valign);

        this.layer = layer.data('layerObject', this);
        this.layer.css('visibility', 'hidden');

        this.zIndex = parseInt(this.layer.css('zIndex'));
        if (isNaN(this.zIndex)) {
            this.zIndex = 0;
        }

        var eye = layer.data('eye'),
            lock = layer.data('lock');
        if (eye !== null && typeof eye != 'undefined') {
            this.eye = !!eye;
        }
        if (lock !== null && typeof lock != 'undefined') {
            this.lock = !!lock;
        }
        this.deviceProperty = {
            desktopPortrait: {
                left: this.property.left,
                top: this.property.top,
                width: this.property.width,
                height: this.property.height,
                align: this.property.align,
                valign: this.property.valign,
                parentalign: this.property.parentalign,
                parentvalign: this.property.parentvalign,
                fontsize: this.property.fontsize
            },
            desktopLandscape: {
                left: layer.data('desktoplandscapeleft'),
                top: layer.data('desktoplandscapetop'),
                width: layer.data('desktoplandscapewidth'),
                height: layer.data('desktoplandscapeheight'),
                align: layer.data('desktoplandscapealign'),
                valign: layer.data('desktoplandscapevalign'),
                parentalign: layer.data('desktoplandscapeparentalign'),
                parentvalign: layer.data('desktoplandscapeparentvalign'),
                fontsize: layer.data('desktoplandscapefontsize')
            },
            tabletPortrait: {
                left: layer.data('tabletportraitleft'),
                top: layer.data('tabletportraittop'),
                width: layer.data('tabletportraitwidth'),
                height: layer.data('tabletportraitheight'),
                align: layer.data('tabletportraitalign'),
                valign: layer.data('tabletportraitvalign'),
                parentalign: layer.data('tabletportraitparentalign'),
                parentvalign: layer.data('tabletportraitparentvalign'),
                fontsize: layer.data('tabletportraitfontsize')
            },
            tabletLandscape: {
                left: layer.data('tabletlandscapeleft'),
                top: layer.data('tabletlandscapetop'),
                width: layer.data('tabletlandscapewidth'),
                height: layer.data('tabletlandscapeheight'),
                align: layer.data('tabletlandscapealign'),
                valign: layer.data('tabletlandscapevalign'),
                parentalign: layer.data('tabletlandscapeparentalign'),
                parentvalign: layer.data('tabletlandscapeparentvalign'),
                fontsize: layer.data('tabletlandscapefontsize')
            },
            mobilePortrait: {
                left: layer.data('mobileportraitleft'),
                top: layer.data('mobileportraittop'),
                width: layer.data('mobileportraitwidth'),
                height: layer.data('mobileportraitheight'),
                align: layer.data('mobileportraitalign'),
                valign: layer.data('mobileportraitvalign'),
                parentalign: layer.data('mobileportraitparentalign'),
                parentvalign: layer.data('mobileportraitparentvalign'),
                fontsize: layer.data('mobileportraitfontsize')
            },
            mobileLandscape: {
                left: layer.data('mobilelandscapeleft'),
                top: layer.data('mobilelandscapetop'),
                width: layer.data('mobilelandscapewidth'),
                height: layer.data('mobilelandscapeheight'),
                align: layer.data('mobilelandscapealign'),
                valign: layer.data('mobilelandscapevalign'),
                parentalign: layer.data('mobilelandscapeparentalign'),
                parentvalign: layer.data('mobilelandscapeparentvalign'),
                fontsize: layer.data('mobilelandscapefontsize')
            }
        };


        this.layersItemsElement = layerEditor.layersItemsElement;
        this.layersItemsUlElement = this.layersItemsElement.find('> ul');

        this.createRow();

        this.itemEditor = itemEditor;

        this.initItems();

        this.___makeLayerAlign();
        this.___makeLayerResizeable();
        this.___makeLayerDraggable();
        this.___makeLayerQuickHandle();

        layerEditor.layerList.push(this);
        //this.index = layerEditor.layerList.push(this) - 1;

        /**
         * This is a fix for the editor load. The layers might not in the z-index order on the load,
         * so we have to "mess up" the array and let the algorithm to fix it.
         */
        if (typeof layerEditor.zIndexList[this.zIndex] === 'undefined') {
            layerEditor.zIndexList[this.zIndex] = this;
        } else {
            layerEditor.zIndexList.splice(this.zIndex, 0, this);
        }

        this._lock();

        this.animation = new NextendSmartSliderLayerAnimations(this);


        this.layerEditor.$.trigger('layerCreated', this);
        $(window).triggerHandler('layerCreated');

        this.animation.load();

        this.layer.on({
            mousedown: $.proxy(this.activate, this),
            dblclick: $.proxy(this.fit, this)
        });

        this.markSmallLayer();

        setTimeout($.proxy(function () {
            this._resize(true);
            this._eye();
        }, this), 300);
    };

    Layer.prototype.getIndex = function () {
        return this.layerEditor.layerList.indexOf(this);
    };

    Layer.prototype.getParent = function () {
        return $('#' + this.getProperty(false, 'parentid')).data('layerObject');
    };

    Layer.prototype.requestID = function () {
        var id = this.getProperty(false, 'id');
        if (!id) {
            id = $.fn.uid();
            this.setProperty('id', id, 'layer');
        }
        return id;
    };

    Layer.prototype.createRow = function () {
        var dblClickInterval = 300,
            timeout = null,
            unlink = $('<a class="n2-ss-parent-unlink" href="#" onclick="return false;"><i class="n2-i n2-i-layerunlink n2-i-grey-opacity"></i></a>').on('click', $.proxy(this.unlink, this)),
            remove = $('<a href="#" onclick="return false;"><i class="n2-i n2-i-delete n2-i-grey-opacity"></i></a>').on('click', $.proxy(this.delete, this)),
            duplicate = $('<a href="#" onclick="return false;"><i class="n2-i n2-i-duplicate n2-i-grey-opacity"></i></a>').on('click', $.proxy(this.duplicate, this, true, false));

        this.soloElement = $('<a href="#" onclick="return false;"><i class="n2-i n2-i-bulb n2-i-grey-opacity"></i></a>').css('opacity', 0.3).on('click', $.proxy(this.switchSolo, this));
        this.eyeElement = $('<a href="#" onclick="return false;"><i class="n2-i n2-i-eye n2-i-grey-opacity"></i></a>').on('click', $.proxy(this.switchEye, this));
        this.lockElement = $('<a href="#" onclick="return false;"><i class="n2-i n2-i-lock n2-i-grey-opacity"></i></a>').on('click', $.proxy(this.switchLock, this));

        this.layerRow = $('<li class="n2-ss-layer-row"></li>')
            .on({
                mouseenter: $.proxy(function () {
                    this.layer.addClass('n2-highlight');
                }, this),
                mouseleave: $.proxy(function (e) {
                    this.layer.removeClass('n2-highlight');
                }, this)
            })
            .appendTo(this.layersItemsUlElement);
        this.layerTitleSpan = $('<span class="n2-ucf">' + this.property.name + '</span>')
            .on({
                mouseup: $.proxy(function (e) {
                    if (timeout) {
                        clearTimeout(timeout);
                        timeout = null;
                        this.editName();
                    } else {
                        timeout = setTimeout($.proxy(function () {
                            this.activate();
                            timeout = null;
                        }, this), dblClickInterval);
                    }
                }, this)
            });

        this.layerTitle = $('<div class="n2-ss-layer-title"></div>')
            .append(this.layerTitleSpan)
            .append($('<div class="n2-actions"></div>').append(unlink).append(duplicate).append(remove))
            .append($('<div class="n2-actions-left"></div>').append(this.eyeElement).append(this.soloElement).append(this.lockElement))
            .appendTo(this.layerRow)
            .on({
                mouseup: $.proxy(function (e) {
                    if (e.target.tagName === 'DIV') {
                        this.activate();
                    }
                }, this)
            });

        this.editorVisibilityChange();
    };

    Layer.prototype.editorVisibilityChange = function () {
        switch (this.layersItemsUlElement.children().length) {
            case 0:
                $('body').removeClass('n2-has-layers');
                break;
            case 1:
                $('body').addClass('n2-has-layers');
                break;
        }
    };

    Layer.prototype.setZIndex = function (targetIndex) {
        this.zIndex = targetIndex;
        this.layer.css('zIndex', targetIndex);
        this.layersItemsUlElement.append(this.layerRow);
        this.$.trigger('layerIndexed', targetIndex);
    };

    /**
     *
     * @param item {optional}
     */
    Layer.prototype.activate = function (e) {
        if (document.activeElement) {
            document.activeElement.blur();
        }
        if (this.items.length == 0) {
            console.error('The layer do not have item on it!');
        } else {
            this.items[0].activate();
        }

        // Set the layer active if it is not active currently
        var currentIndex = this.getIndex();
        if (this.layerEditor.activeLayerIndex !== currentIndex) {
            this.layerRow.addClass('n2-active');
            this.layer.triggerHandler('n2-ss-activate');
            this.layerEditor.changeActiveLayer(currentIndex);
            nextend.activeLayer = this.layer;

            var scroll = this.layersItemsUlElement.parent(),
                scrollTop = scroll.scrollTop(),
                top = this.layerRow.get(0).offsetTop;
            if (top < scrollTop || top > scrollTop + scroll.height() - this.layerRow.height()) {
                scroll.scrollTop(top);
            }

            if (timeout) {
                highlighted.removeClass('n2-highlight2');
                clearTimeout(timeout);
                timeout = null;
            }
            highlighted = this.layer.addClass('n2-highlight2');
            timeout = setTimeout(function () {
                highlighted.removeClass('n2-highlight2');
                highlighted = null;
                timeout = null;
            }, 500);
        }
    };

    Layer.prototype.deActivate = function () {
        this.animation.deActivate();
        this.layerRow.removeClass('n2-active');
        this.layer.triggerHandler('n2-ss-deactivate');
    };

    Layer.prototype.fit = function () {
        var layer = this.layer.get(0);

        var slideSize = this.layerEditor.slideSize,
            position = this.layer.position();

        if (layer.scrollWidth > 0 && layer.scrollHeight > 0) {
            var resized = false;
            for (var i = 0; i < this.items.length; i++) {
                resized = this.items[i].parser.fitLayer(this.items[i]);
                if (resized) {
                    break;
                }
            }
            if (!resized) {
                this.setProperty('width', 'auto', 'layer');
                this.setProperty('height', 'auto', 'layer');

                var layerWidth = this.layer.width();
                if (Math.abs(this.layerEditor.layerContainerElement.width() - this.layer.position().left - layerWidth) < 2) {
                    this.setProperty('width', layerWidth, 'layer');
                }
            }
        }
    };

    Layer.prototype.switchToAnimation = function () {
        smartSlider.sidebarManager.switchTab(1);
    };

    Layer.prototype.hide = function (targetMode) {
        this.store(false, (targetMode ? targetMode : this.getMode()), 0, true);
    };

    Layer.prototype.show = function (targetMode) {
        this.store(false, (targetMode ? targetMode : this.getMode()), 1, true);
    };

    Layer.prototype.switchSolo = function () {
        this.layerEditor.setSolo(this);
    };

    Layer.prototype.markSolo = function () {
        this.soloElement.css('opacity', 1);
        this.layer.addClass('n2-ss-layer-solo');
    };

    Layer.prototype.unmarkSolo = function () {
        this.soloElement.css('opacity', 0.3);
        this.layer.removeClass('n2-ss-layer-solo');
    };

    Layer.prototype.switchEye = function () {
        this.eye = !this.eye;
        this._eye();
    };

    Layer.prototype._eye = function () {
        if (this.eye) {
            this.eyeElement.css('opacity', 0.3);
            this.layer.css('visibility', 'hidden');
        } else {
            this.eyeElement.css('opacity', 1);
            this.layer.css('visibility', '');
        }
    };

    Layer.prototype._hide = function () {
        this.layer.css('display', 'none');
    };

    Layer.prototype._show = function () {
        if (parseInt(this.property[this.layerEditor.getMode()])) {
            this.layer.css('display', 'block');
        }
    };

    Layer.prototype.switchLock = function () {
        this.lock = !this.lock;
        this._lock();
    };

    Layer.prototype._lock = function () {
        if (this.lock) {
            this.lockElement.css('opacity', 1);
            this.layer.nextenddraggable("disable");
            this.layer.nextendResizable("disable");
            this.layer.addClass('n2-ss-layer-locked');
        } else {
            this.lockElement.css('opacity', 0.3);
            this.layer.nextenddraggable("enable");
            this.layer.nextendResizable("enable");
            this.layer.removeClass('n2-ss-layer-locked');

        }
    };

    Layer.prototype.duplicate = function (needActivate, newParentId) {
        var layer = this.getHTML(true, false);

        var id = layer.attr('id');
        if (id) {
            id = $.fn.uid();
            layer.attr('id', id);
        }

        if (newParentId) {
            layer.attr('data-parentid', newParentId);
        }

        var newLayer = this.layerEditor.addLayer(layer, true);

        this.layer.triggerHandler('LayerDuplicated', id);

        this.layerRow.trigger('mouseleave');

        if (needActivate) {
            newLayer.activate();
        }
    };

    Layer.prototype.delete = function () {

        this.deActivate();

        for (var i = 0; i < this.items.length; i++) {
            this.items[i].delete();
        }

        this.layerEditor.zIndexList.splice(this.zIndex, 1);

        var parentId = this.getProperty(false, 'parentid');
        if (parentId) {
            this.unSubscribeParent(true);
        }
        // If delete happen meanwhile layer dragged or resized, we have to cancel that.
        this.layer.trigger('mouseup');
        this.layer.triggerHandler('LayerDeleted');
        this.layer.remove();
        this.layerRow.remove();
        this.layerEditor.layerDeleted(this.getIndex());

        this.editorVisibilityChange();

        this.$.trigger('layerDeleted');

        delete this.layerEditor;
        delete this.layer;
        delete this.itemEditor;
        delete this.animation;
        delete this.items;
    };

    Layer.prototype.getHTML = function (itemsIncluded, base64) {
        var layer = $('<div class="n2-ss-layer"></div>')
            .attr('style', this.getStyleText());

        for (var k in this.property) {
            if (k != 'width' && k != 'height' && k != 'left' && k != 'top') {
                layer.attr('data-' + k.toLowerCase(), this.property[k]);
            }
        }

        for (var k in this.deviceProperty) {
            for (var k2 in this.deviceProperty[k]) {
                layer.attr('data-' + k.toLowerCase() + k2, this.deviceProperty[k][k2]);
            }
        }

        layer.css({
            position: 'absolute',
            zIndex: this.zIndex + 1
        });

        for (var k in this.deviceProperty['desktop']) {
            layer.css(k, this.deviceProperty['desktop'][k] + 'px');
        }

        if (itemsIncluded) {
            for (var i = 0; i < this.items.length; i++) {
                layer.append(this.items[i].getHTML(base64));
            }
        }
        var id = this.getProperty(false, 'id');
        if (id && id != '') {
            layer.attr('id', id);
        }

        layer.attr('data-eye', this.eye);
        layer.attr('data-lock', this.lock);


        layer.attr('data-animations', this.animation.getAnimationsCode());

        return layer;
    };

    Layer.prototype.getData = function (itemsIncluded) {
        var layer = {
            zIndex: (this.zIndex + 1),
            eye: this.eye,
            lock: this.lock,
            animations: this.animation.getData()
        };
        for (var k in this.property) {
            switch (k) {
                case 'width':
                case 'height':
                case 'left':
                case 'top':
                case 'align':
                case 'valign':
                case 'parentalign':
                case 'parentvalign':
                case 'fontsize':
                    break;
                default:
                    layer[k.toLowerCase()] = this.property[k];
            }
        }

        // store the device based properties
        for (var device in this.deviceProperty) {
            for (var property in this.deviceProperty[device]) {
                var value = this.deviceProperty[device][property];
                if (typeof value === 'undefined') {
                    continue;
                }
                if (!(property == 'width' && this.isDimensionPropertyAccepted(value)) && !(property == 'height' && this.isDimensionPropertyAccepted(value)) && property != 'align' && property != 'valign' && property != 'parentalign' && property != 'parentvalign') {
                    value = parseFloat(value);
                }
                layer[device.toLowerCase() + property] = value;
            }
        }

        // Set the default styles for the layer
        /*var defaultProperties = this.deviceProperty['desktopPortrait'];
         layer.style += 'left:' + parseFloat(defaultProperties.left) + 'px;';
         layer.style += 'top:' + parseFloat(defaultProperties.top) + 'px;';
         if (this.isDimensionPropertyAccepted(defaultProperties.width)) {
         layer.style += 'width:' + defaultProperties.width + ';';
         } else {
         layer.style += 'width:' + parseFloat(defaultProperties.width) + 'px;';
         }
         if (this.isDimensionPropertyAccepted(defaultProperties.height)) {
         layer.style += 'height:' + defaultProperties.height + ';';
         } else {
         layer.style += 'height:' + parseFloat(defaultProperties.height) + 'px;';
         }*/

        if (itemsIncluded) {
            layer.items = [];
            for (var i = 0; i < this.items.length; i++) {
                layer.items.push(this.items[i].getData());
            }
        }
        return layer;
    };

    Layer.prototype.initItems = function () {
        this.items = [];
        var items = this.layer.find('.n2-ss-item');
        for (var i = 0; i < items.length; i++) {
            this.addItem(items.eq(i), false);
        }
    };

    Layer.prototype.addItem = function (item, place) {
        if (place) {
            item.appendTo(this.layer);
        }
        new NextendSmartSliderItem(item, this, this.itemEditor);
    };

    Layer.prototype.editName = function () {
        var input = new NextendSmartSliderAdminInlineField();

        input.$input.on({
            valueChanged: $.proxy(function (e, newName) {
                this.rename(newName, true);
                this.layerTitleSpan.css('display', 'inline');
            }, this),
            cancel: $.proxy(function () {
                this.layerTitleSpan.css('display', 'inline');
            }, this)
        });

        this.layerTitleSpan.css('display', 'none');
        input.injectNode(this.layerTitle, this.property.name);

    };

    Layer.prototype.rename = function (newName, force) {

        if (this.property.nameSynced || force) {

            if (force) {
                this.property.nameSynced = 0;
            }

            if (newName == '') {
                if (force) {
                    this.property.nameSynced = 1;
                    if (this.items.length) {
                        this.items[0].reRender();
                        return false;
                    }
                }
                newName = 'Layer #' + (this.layerEditor.layerList.length + 1);
            }
            newName = newName.substr(0, 35);
            if (this.property.name != newName) {
                this.property.name = newName;
                this.layerTitleSpan.html(newName);

                this.$.trigger('layerRenamed', newName);
            }
        }
    };

    Layer.prototype.markSmallLayer = function () {
        if (this.layer) {
            var w = this.layer.width(),
                h = this.layer.height();
            if (w < 50 || h < 50) {
                this.layer.addClass('n2-ss-layer-small');
            } else {
                this.layer.removeClass('n2-ss-layer-small');
            }
        }
    };

    // from: manager or other
    Layer.prototype.setProperty = function (name, value, from) {
        switch (name) {
            case 'responsiveposition':
            case 'responsivesize':
                value = parseInt(value);
            case 'id':
            case 'parentid':
            case 'inneralign':
            case 'crop':
            case 'parallax':
            case 'adaptivefont':
            case 'mouseenter':
            case 'click':
            case 'mouseleave':
            case 'play':
            case 'pause':
            case 'stop':
                this.store(false, name, value, true);
                break;
            case 'parentalign':
            case 'parentvalign':
            case 'align':
            case 'valign':
            case 'fontsize':
                this.store(true, name, value, true);
                break;
            case 'width':
                var ratioSizeH = this.layerEditor.getResponsiveRatio('h')
                if (!parseInt(this.getProperty(false, 'responsivesize'))) {
                    ratioSizeH = 1;
                }

                var v = value;
                if (!this.isDimensionPropertyAccepted(value)) {
                    v = ~~value;
                    if (v != value) {
                        this.$.trigger('propertyChanged', [name, v]);
                    }
                }
                this.storeWithModifier(name, v, ratioSizeH, true);
                this._resize(false);
                break;
            case 'height':
                var ratioSizeV = this.layerEditor.getResponsiveRatio('v')
                if (!parseInt(this.getProperty(false, 'responsivesize'))) {
                    ratioSizeV = 1;
                }

                var v = value;
                if (!this.isDimensionPropertyAccepted(value)) {
                    v = ~~value;
                    if (v != value) {
                        this.$.trigger('propertyChanged', [name, v]);
                    }
                }

                this.storeWithModifier(name, v, ratioSizeV, true);
                this._resize(false);
                break;
            case 'left':
                var ratioPositionH = this.layerEditor.getResponsiveRatio('h')
                if (!parseInt(this.getProperty(false, 'responsiveposition'))) {
                    ratioPositionH = 1;
                }

                var v = ~~value;
                if (v != value) {
                    this.$.trigger('propertyChanged', [name, v]);
                }

                this.storeWithModifier(name, v, ratioPositionH, true);
                break;
            case 'top':
                var ratioPositionV = this.layerEditor.getResponsiveRatio('v')
                if (!parseInt(this.getProperty(false, 'responsiveposition'))) {
                    ratioPositionV = 1;
                }

                var v = ~~value;
                if (v != value) {
                    this.$.trigger('propertyChanged', [name, v]);
                }

                this.storeWithModifier(name, v, ratioPositionV, true);
                break;
            case 'showFieldDesktopPortrait':
                this.store(false, 'desktopPortrait', parseInt(value), true);
                break;
            case 'showFieldDesktopLandscape':
                this.store(false, 'desktopLandscape', parseInt(value), true);
                break;
            case 'showFieldTabletPortrait':
                this.store(false, 'tabletPortrait', parseInt(value), true);
                break;
            case 'showFieldTabletLandscape':
                this.store(false, 'tabletLandscape', parseInt(value), true);
                break;
            case 'showFieldMobilePortrait':
                this.store(false, 'mobilePortrait', parseInt(value), true);
                break;
            case 'showFieldMobileLandscape':
                this.store(false, 'mobileLandscape', parseInt(value), true);
                break;
        }

        if (from != 'manager') {
            // jelezzuk a sidebarnak, hogy valamely property megvaltozott
            this.$.trigger('propertyChanged', [name, value]);
        }
    };

    Layer.prototype.getProperty = function (deviceBased, name) {

        if (deviceBased) {
            var properties = this.deviceProperty[this.getMode()],
                fallbackProperties = this.deviceProperty['desktopPortrait'];
            if (typeof properties[name] !== 'undefined') {
                return properties[name];
            } else if (typeof fallbackProperties[name] !== 'undefined') {
                return fallbackProperties[name];
            }
        }
        return this.property[name];
    };

    Layer.prototype.store = function (deviceBased, name, value, needRender) {
        this.property[name] = value;
        if (deviceBased) {
            var mode = this.getMode();
            this.deviceProperty[mode][name] = value;
        }

        if (needRender) {
            this.render(name, value);
        }

        if (name == 'width' || name == 'height') {
            this.markSmallLayer();
        }
        return;

        var lastLocalValue = this.property[name],
            lastValue = lastLocalValue;

        if (!isReset && this.property[name] != value) {
            this.property[name] = value;
            if (deviceBased) {
                lastValue = this.getProperty(deviceBased, name);
                this.deviceProperty[this.getMode()][name] = value;
            }
        } else if (deviceBased) {
            lastValue = this.getProperty(deviceBased, name);
            //this.property[name] = value;
        }
        /*if (lastLocalValue != value) {
         this.$.trigger('propertyChanged', [name, value]);
         }*/
        // The resize usually sets px for left/top/width/height values for the original percents. So we have to force those values back.
        if (needRender) {
            this.render(name, value);
        }

        if (name == 'width' || name == 'height') {
            this.markSmallLayer();
        }
    };

    Layer.prototype.storeWithModifier = function (name, value, modifier, needRender) {
        this.property[name] = value;
        var mode = this.getMode();
        this.deviceProperty[mode][name] = value;

        if (needRender) {
            this.renderWithModifier(name, value, modifier);
        }

        if (name == 'width' || name == 'height') {
            this.markSmallLayer();
        }
        return;


        var lastLocalValue = this.property[name];

        if (!isReset && this.property[name] != value) {
            this.property[name] = value;

            //this.$.trigger('propertyChanged', [name, value]);

            this.deviceProperty[this.getMode()][name] = value;
        }
        /*
         if (lastLocalValue != value) {
         this.$.trigger('propertyChanged', [name, value]);
         }
         */
        // The resize usually sets px for left/top/width/height values for the original percents. So we have to force those values back.
        if (needRender) {
            this.renderWithModifier(name, value, modifier);
        }

        this.markSmallLayer();
    };

    Layer.prototype.render = function (name, value) {
        this['_sync' + name](value);
    };

    Layer.prototype.renderWithModifier = function (name, value, modifier) {
        if ((name == 'width' || name == 'height') && this.isDimensionPropertyAccepted(value)) {
            this['_sync' + name](value);
        } else {
            this['_sync' + name](Math.round(value * modifier));
        }
    };

    Layer.prototype._syncid = function (value) {
        if (!value || value == '') {
            this.layer.removeAttr('id');
        } else {
            this.layer.attr('id', value);
        }
    };

    Layer.prototype.subscribeParent = function () {
        var that = this;
        this.subscribeParentCallbacks = {
            LayerResized: function () {
                that.resizeParent.apply(that, arguments);
            },
            LayerParent: function () {
                that.layer.addClass('n2-ss-layer-parent');
                that.layer.triggerHandler('LayerParent');
            },
            LayerUnParent: function () {
                that.layer.removeClass('n2-ss-layer-parent');
                that.layer.triggerHandler('LayerUnParent');
            },
            LayerDeleted: function () {
                that.setProperty('parentid', '', 'layer');
            },
            LayerDuplicated: function (e, newParentId) {
                that.duplicate(false, newParentId);
            },
            LayerShowChange: function (e, mode, value) {
                if (that.getMode() == mode) {
                    that.parentIsVisible = value;
                }
            },
            'n2-ss-activate': function () {
                that.layerRow.addClass('n2-parent-active');
            },
            'n2-ss-deactivate': function () {
                that.layerRow.removeClass('n2-parent-active');
            }
        };
        this.parent = n2('#' + this.property.parentid).on(this.subscribeParentCallbacks);
    };

    Layer.prototype.unSubscribeParent = function (isDelete) {
        this.layerRow.removeClass('n2-parent-active');
        if (this.parent) {
            this.parent.off(this.subscribeParentCallbacks);
        }
        this.parent = false;
        this.subscribeParentCallbacks = {};
        if (!isDelete) {
            var position = this.layer.position();
            this.setPosition(position.left, position.top);
        }
    };

    Layer.prototype.unlink = function (e) {
        e.preventDefault();
        this.setProperty('parentid', '', 'layer');
    };

    Layer.prototype.parentPicked = function (parentObject, parentAlign, parentValign, align, valign) {
        this.setProperty('parentid', '', 'layer');

        this.setProperty('align', align, 'layer');
        this.setProperty('valign', valign, 'layer');
        this.setProperty('parentalign', parentAlign, 'layer');
        this.setProperty('parentvalign', parentValign, 'layer');

        this.setProperty('parentid', parentObject.requestID(), 'layer');
    };

    Layer.prototype._syncparentid = function (value) {
        if (!value || value == '') {
            this.layer.removeAttr('data-parentid');
            this.unSubscribeParent(false);
        } else {
            if ($('#' + value).length == 0) {
                this.setProperty('parentid', '', 'layer');
            } else {
                this.layer.attr('data-parentid', value);
                this.subscribeParent();
                this.setPosition(this.layer.position().left, this.layer.position().top);
            }
        }
    };

    Layer.prototype._syncparentalign = function (value) {
        this.layer.data('parentalign', value);
        var parent = this.getParent();
        if (parent) {
            parent._resize(false);
        }
    };

    Layer.prototype._syncparentvalign = function (value) {
        this.layer.data('parentvalign', value);
        var parent = this.getParent();
        if (parent) {
            parent._resize(false);
        }
    };

    Layer.prototype._syncinneralign = function (value) {
        this.layer.css('text-align', value);
    };

    Layer.prototype._synccrop = function (value) {
        if (value == 'auto') {
            value = 'hidden';
        }

        var mask = this.layer.find('> .n2-ss-layer-mask');
        if (value == 'mask') {
            value = 'hidden';
            if (!mask.length) {
                mask = $("<div class='n2-ss-layer-mask'></div>").appendTo(this.layer);
                for (var i = 0; i < this.items.length; i++) {
                    mask.append(this.items[i].item);
                }
            }
        } else {
            if (mask.length) {
                for (var i = 0; i < this.items.length; i++) {
                    this.layer.append(this.items[i].item);
                    mask.remove();
                }
            }
        }
        this.layer.css('overflow', value);
    };

    Layer.prototype._syncparallax = function (value) {

    };

    Layer.prototype._syncalign = function (value, lastValue) {
        if (lastValue !== 'undefined' && value != lastValue) {
            this.setPosition(this.layer.position().left, this.layer.position().top);
        }
        this.layer.attr('data-align', value);
    };

    Layer.prototype._syncvalign = function (value, lastValue) {
        if (lastValue !== 'undefined' && value != lastValue) {
            this.setPosition(this.layer.position().left, this.layer.position().top);
        }
        this.layer.attr('data-valign', value);
    };

    Layer.prototype._syncfontsize = function (value) {
        this.adjustFontSize(this.getProperty(false, 'adaptivefont'), value, true);
    };

    Layer.prototype._syncadaptivefont = function (value) {
        this.adjustFontSize(value, this.getProperty(true, 'fontsize'), true);
    };

    Layer.prototype.adjustFontSize = function (isAdaptive, fontSize, shouldUpdatePosition) {
        fontSize = parseInt(fontSize);
        if (parseInt(isAdaptive)) {
            this.layer.css('font-size', (nextend.smartSlider.frontend.sliderElement.data('fontsize') * fontSize / 100) + 'px');
        } else if (fontSize != 100) {
            this.layer.css('font-size', fontSize + '%');
        } else {
            this.layer.css('font-size', '');
        }
        if (shouldUpdatePosition) {
            this.update();
        }
    };

    Layer.prototype._syncleft = function (value) {
        if (!this.parent || !this.parentIsVisible) {
            switch (this.getProperty(true, 'align')) {
                case 'right':
                    this.layer.css({
                        left: 'auto',
                        right: -value + 'px'
                    });
                    break;
                case 'center':
                    this.layer.css({
                        left: (this.layer.parent().width() / 2 + value - this.layer.width() / 2) + 'px',
                        right: 'auto'
                    });
                    break;
                default:
                    this.layer.css({
                        left: value + 'px',
                        right: 'auto'
                    });
            }
        } else {
            var position = this.parent.position(),
                align = this.getProperty(true, 'align'),
                parentAlign = this.getProperty(true, 'parentalign'),
                left = 0;
            switch (parentAlign) {
                case 'right':
                    left = position.left + this.parent.width();
                    break;
                case 'center':
                    left = position.left + this.parent.width() / 2;
                    break;
                default:
                    left = position.left;
            }

            switch (align) {
                case 'right':
                    this.layer.css({
                        left: 'auto',
                        right: (this.layer.parent().width() - left - value) + 'px'
                    });
                    break;
                case 'center':
                    this.layer.css({
                        left: (left + value - this.layer.width() / 2) + 'px',
                        right: 'auto'
                    });
                    break;
                default:
                    this.layer.css({
                        left: (left + value) + 'px',
                        right: 'auto'
                    });
            }

        }

        this.triggerLayerResized();
    };

    Layer.prototype._synctop = function (value) {
        if (!this.parent || !this.parentIsVisible) {
            switch (this.getProperty(true, 'valign')) {
                case 'bottom':
                    this.layer.css({
                        top: 'auto',
                        bottom: -value + 'px'
                    });
                    break;
                case 'middle':
                    this.layer.css({
                        top: (this.layer.parent().height() / 2 + value - this.layer.height() / 2) + 'px',
                        bottom: 'auto'
                    });
                    break;
                default:
                    this.layer.css({
                        top: value + 'px',
                        bottom: 'auto'
                    });
            }
        } else {
            var position = this.parent.position(),
                valign = this.getProperty(true, 'valign'),
                parentVAlign = this.getProperty(true, 'parentvalign'),
                top = 0;
            switch (parentVAlign) {
                case 'bottom':
                    top = position.top + this.parent.height();
                    break;
                case 'middle':
                    top = position.top + this.parent.height() / 2;
                    break;
                default:
                    top = position.top;
            }

            switch (valign) {
                case 'bottom':
                    this.layer.css({
                        top: 'auto',
                        bottom: (this.layer.parent().height() - top - value) + 'px'
                    });
                    break;
                case 'middle':
                    this.layer.css({
                        top: (top + value - this.layer.height() / 2) + 'px',
                        bottom: 'auto'
                    });
                    break;
                default:
                    this.layer.css({
                        top: (top + value) + 'px',
                        bottom: 'auto'
                    });
            }
        }

        this.triggerLayerResized();
    };

    Layer.prototype._syncresponsiveposition = function (value) {
        this._resize(false);
    };

    Layer.prototype._syncwidth = function (value) {
        this.layer.css('width', value + (this.isDimensionPropertyAccepted(value) ? '' : 'px'));
    };

    Layer.prototype._syncheight = function (value) {
        this.layer.css('height', value + (this.isDimensionPropertyAccepted(value) ? '' : 'px'));
    };

    Layer.prototype._syncresponsivesize = function (value) {
        this._resize(false);
    };

    Layer.prototype._syncdesktopPortrait = function (value) {
        this.__syncShowOnDevice('desktopPortrait', value);
    };

    Layer.prototype._syncdesktopLandscape = function (value) {
        this.__syncShowOnDevice('desktopLandscape', value);
    };

    Layer.prototype._synctabletPortrait = function (value) {
        this.__syncShowOnDevice('tabletPortrait', value);
    };

    Layer.prototype._synctabletLandscape = function (value) {
        this.__syncShowOnDevice('tabletLandscape', value);
    };

    Layer.prototype._syncmobilePortrait = function (value) {
        this.__syncShowOnDevice('mobilePortrait', value);
    };

    Layer.prototype._syncmobileLandscape = function (value) {
        this.__syncShowOnDevice('mobileLandscape', value);
    };

    Layer.prototype.__syncShowOnDevice = function (mode, value) {
        if (this.getMode() == mode) {
            var value = parseInt(value);
            if (value) {
                this._show();
            } else {
                this._hide();
            }
            this.layer.triggerHandler('LayerShowChange', [mode, value]);
            this.triggerLayerResized();
        }
    };

    Layer.prototype._syncmouseenter =
        Layer.prototype._syncclick =
            Layer.prototype._syncmouseleave =
                Layer.prototype._syncplay =
                    Layer.prototype._syncpause =
                        Layer.prototype._syncstop = function () {
                        };

    Layer.prototype.___makeLayerAlign = function () {
        this.alignMarker = $('<div class="n2-ss-layer-align-marker" />').appendTo(this.layer);
    };

    //<editor-fold desc="Makes layer resizable">

    /**
     * Add resize handles to the specified layer
     * @param {jQuery} layer
     * @private
     */
    Layer.prototype.___makeLayerResizeable = function () {
        this.layer.nextendResizable({
            handles: 'n, e, s, w, ne, se, sw, nw',
            _containment: this.layerEditor.layerContainerElement,
            start: $.proxy(this.____makeLayerResizeableStart, this),
            resize: $.proxy(this.____makeLayerResizeableResize, this),
            stop: $.proxy(this.____makeLayerResizeableStop, this),
            smartguides: $.proxy(function () {
                this.layer.triggerHandler('LayerParent');
                return this.layerEditor.getSnap();
            }, this),
            tolerance: 5
        })
            .on({
                mousedown: $.proxy(function (e) {
                    if (!this.lock) {
                        this.layerEditor.positionDisplay
                            .css({
                                left: e.pageX + 10,
                                top: e.pageY + 10
                            })
                            .html('W: ' + parseInt(this.layer.width()) + 'px<br />H: ' + parseInt(this.layer.height()) + 'px')
                            .addClass('n2-active');
                    }
                    if (document.activeElement) {
                        document.activeElement.blur();
                    }
                }, this),
                mouseup: $.proxy(function (e) {
                    this.layerEditor.positionDisplay.removeClass('n2-active');
                }, this)
            });
    };

    Layer.prototype.____makeLayerResizeableStart = function (event, ui) {
        $('#n2-admin').addClass('n2-ss-resize-layer');
        this.____makeLayerResizeableResize(event, ui);
        this.layerEditor.positionDisplay.addClass('n2-active');
    };

    Layer.prototype.____makeLayerResizeableResize = function (e, ui) {
        this.layerEditor.positionDisplay
            .css({
                left: e.pageX + 10,
                top: e.pageY + 10
            })
            .html('W: ' + ui.size.width + 'px<br />H: ' + ui.size.height + 'px');
        this.triggerLayerResized();
    };

    Layer.prototype.____makeLayerResizeableStop = function (event, ui) {
        window.nextendPreventClick = true;
        setTimeout(function () {
            window.nextendPreventClick = false;
        }, 50);
        $('#n2-admin').removeClass('n2-ss-resize-layer');

        var isAutoWidth = false;
        if (ui.originalSize.width == ui.size.width) {
            var currentValue = this.getProperty(true, 'width');
            if (this.isDimensionPropertyAccepted(currentValue)) {
                isAutoWidth = true;
                this['_syncwidth'](currentValue);
            }
        }

        var isAutoHeight = false;
        if (ui.originalSize.height == ui.size.height) {
            var currentValue = this.getProperty(true, 'height');
            if (this.isDimensionPropertyAccepted(currentValue)) {
                isAutoHeight = true;
                this['_syncheight'](currentValue);
            }
        }
        this.setPosition(ui.position.left, ui.position.top);


        var ratioSizeH = this.layerEditor.getResponsiveRatio('h'),
            ratioSizeV = this.layerEditor.getResponsiveRatio('v');

        if (!parseInt(this.getProperty(false, 'responsivesize'))) {
            ratioSizeH = ratioSizeV = 1;
        }

        if (!isAutoWidth) {
            var value = Math.round(ui.size.width * (1 / ratioSizeH));
            this.storeWithModifier('width', value, ratioSizeH, false);
            this.$.trigger('propertyChanged', ['width', value]);
        }
        if (!isAutoHeight) {
            var value = Math.round(ui.size.height * (1 / ratioSizeV));
            this.storeWithModifier('height', value, ratioSizeV, false);
            this.$.trigger('propertyChanged', ['height', value]);
        }
        this.triggerLayerResized();

        this.layer.triggerHandler('LayerUnParent');

        this.layerEditor.positionDisplay.removeClass('n2-active');
    };
    //</editor-fold>

    //<editor-fold desc="Makes layer draggable">

    /**
     * Add draggable handles to the specified layer
     * @param layer
     * @private
     */
    Layer.prototype.___makeLayerDraggable = function () {

        this.layer.nextenddraggable({
            _containment: this.layerEditor.layerContainerElement,
            start: $.proxy(this.____makeLayerDraggableStart, this),
            drag: $.proxy(this.____makeLayerDraggableDrag, this),
            stop: $.proxy(this.____makeLayerDraggableStop, this),
            smartguides: $.proxy(function () {
                this.layer.triggerHandler('LayerParent');
                return this.layerEditor.getSnap();
            }, this),
            tolerance: 5
        });
    };

    Layer.prototype.____makeLayerDraggableStart = function (event, ui) {
        $('#n2-admin').addClass('n2-ss-move-layer');
        this.____makeLayerDraggableDrag(event, ui);
        this.layerEditor.positionDisplay.addClass('n2-active');

        var currentValue = this.getProperty(true, 'width');
        if (this.isDimensionPropertyAccepted(currentValue)) {
            this.layer.width(this.layer.width() + 0.5); // Center positioned element can wrap the last word to a new line if this fix not added
        }

        var currentValue = this.getProperty(true, 'height');
        if (this.isDimensionPropertyAccepted(currentValue)) {
            this['_syncheight'](currentValue);
        }
    };

    Layer.prototype.____makeLayerDraggableDrag = function (e, ui) {
        this.layerEditor.positionDisplay
            .css({
                left: e.pageX + 10,
                top: e.pageY + 10
            })
            .html('L: ' + parseInt(ui.position.left | 0) + 'px<br />T: ' + parseInt(ui.position.top | 0) + 'px');
        this.triggerLayerResized();
    };

    Layer.prototype.____makeLayerDraggableStop = function (event, ui) {
        window.nextendPreventClick = true;
        setTimeout(function () {
            window.nextendPreventClick = false;
        }, 50);
        $('#n2-admin').removeClass('n2-ss-move-layer');

        this.setPosition(ui.position.left, ui.position.top);

        var currentValue = this.getProperty(true, 'width');
        if (this.isDimensionPropertyAccepted(currentValue)) {
            this['_syncwidth'](currentValue);
        }

        var currentValue = this.getProperty(true, 'height');
        if (this.isDimensionPropertyAccepted(currentValue)) {
            this['_syncheight'](currentValue);
        }

        this.triggerLayerResized();

        this.layer.triggerHandler('LayerUnParent');
        this.layerEditor.positionDisplay.removeClass('n2-active');
    };

    Layer.prototype.moveX = function (x) {
        this.setDeviceBasedAlign();
        this.setProperty('left', this.getProperty(true, 'left') + x, 'layer');
        this.triggerLayerResized();
    };

    Layer.prototype.moveY = function (y) {
        this.setDeviceBasedAlign();
        this.setProperty('top', this.getProperty(true, 'top') + y, 'layer');
        this.triggerLayerResized();
    };

    Layer.prototype.setPosition = function (left, top) {

        var ratioH = this.layerEditor.getResponsiveRatio('h'),
            ratioV = this.layerEditor.getResponsiveRatio('v');

        if (!parseInt(this.getProperty(false, 'responsiveposition'))) {
            ratioH = ratioV = 1;
        }

        this.setDeviceBasedAlign();

        var parent = this.parent,
            p = {
                left: 0,
                leftMultiplier: 1,
                top: 0,
                topMultiplier: 1
            };
        if (!parent || !parent.is(':visible')) {
            parent = this.layer.parent();


            switch (this.getProperty(true, 'align')) {
                case 'center':
                    p.left += parent.width() / 2;
                    break;
                case 'right':
                    p.left += parent.width();
                    break;
            }

            switch (this.getProperty(true, 'valign')) {
                case 'middle':
                    p.top += parent.height() / 2;
                    break;
                case 'bottom':
                    p.top += parent.height();
                    break;
            }
        } else {
            var position = parent.position();
            switch (this.getProperty(true, 'parentalign')) {
                case 'right':
                    p.left = position.left + parent.width();
                    break;
                case 'center':
                    p.left = position.left + parent.width() / 2;
                    break;
                default:
                    p.left = position.left;
            }
            switch (this.getProperty(true, 'parentvalign')) {
                case 'bottom':
                    p.top = position.top + parent.height();
                    break;
                case 'middle':
                    p.top = position.top + parent.height() / 2;
                    break;
                default:
                    p.top = position.top;
            }
        }


        var left, needRender = false;
        switch (this.getProperty(true, 'align')) {
            case 'left':
                left = -Math.round((p.left - left) * (1 / ratioH));
                break;
            case 'center':
                left = -Math.round((p.left - left - this.layer.width() / 2) * (1 / ratioH))
                break;
            case 'right':
                left = -Math.round((p.left - left - this.layer.width()) * (1 / ratioH));
                needRender = true;
                break;
        }
        this.storeWithModifier('left', left, ratioH, needRender);
        this.$.trigger('propertyChanged', ['left', left]);

        var top, needRender = false;
        switch (this.getProperty(true, 'valign')) {
            case 'top':
                top = -Math.round((p.top - top) * (1 / ratioV));
                break;
            case 'middle':
                top = -Math.round((p.top - top - this.layer.height() / 2) * (1 / ratioV));
                break;
            case 'bottom':
                top = -Math.round((p.top - top - this.layer.height()) * (1 / ratioV));
                needRender = true;
                break;
        }
        this.storeWithModifier('top', top, ratioV, needRender);
        this.$.trigger('propertyChanged', ['top', top]);
    }

    Layer.prototype.setDeviceBasedAlign = function () {
        var mode = this.getMode();
        if (typeof this.deviceProperty[mode]['align'] == 'undefined') {
            this.setProperty('align', this.getProperty(true, 'align'), 'layer');
        }
        if (typeof this.deviceProperty[mode]['valign'] == 'undefined') {
            this.setProperty('valign', this.getProperty(true, 'valign'), 'layer');
        }
    };
    //</editor-fold

    Layer.prototype.snap = function () {
        this.layer.nextendResizable("option", "smartguides", $.proxy(function () {
            this.layer.triggerHandler('LayerParent');
            return this.layerEditor.getSnap();
        }, this));
        this.layer.nextenddraggable("option", "smartguides", $.proxy(function () {
            this.layer.triggerHandler('LayerParent');
            return this.layerEditor.getSnap();
        }, this));
    };

    //<editor-fold desc="Makes a layer deletable">

    Layer.prototype.___makeLayerQuickHandle = function () {
        var quick = $('<div class="n2-ss-layer-quick-handle" style="z-index: 92;"><i class="n2-i n2-it n2-i-more"></i></div>')
            .on('mousedown', $.proxy(function (e) {
                e.stopPropagation();
                this.activate();
                var handleOffset = $(e.currentTarget).offset();

                var container = $('<div class="n2-ss-layer-quick-panel"></div>').css(handleOffset)
                    .on('click mouseleave', function () {
                        container.remove();
                    })
                    .appendTo('body');
                $('<div class="n2-ss-layer-quick-panel-option"><i class="n2-i n2-it n2-i-duplicate"></i></div>')
                    .on('click', $.proxy(this.duplicate, this, true, false))
                    .appendTo(container);
                $('<div class="n2-ss-layer-quick-panel-option n2-ss-layer-quick-panel-option-center"><i class="n2-i n2-it n2-i-more"></i></div>').appendTo(container);
                $('<div class="n2-ss-layer-quick-panel-option"><i class="n2-i n2-it n2-i-delete"></i></div>')
                    .on('click', $.proxy(this.delete, this))
                    .appendTo(container);
            }, this))
            .appendTo(this.layer);
    };
    //</editor-fold>

    Layer.prototype.changeEditorMode = function (mode) {
        var value = parseInt(this.property[mode]);
        if (value) {
            this._show();
        } else {
            this._hide();
        }

        this.layer.triggerHandler('LayerShowChange', [mode, value]);

        this._renderModeProperties(false);
    };

    Layer.prototype.resetMode = function (mode, currentMode) {
        if (mode != 'desktopPortrait') {
            var undefined;
            for (var k in this.property) {
                this.deviceProperty[mode][k] = undefined;
            }
            if (mode == currentMode) {
                this._renderModeProperties(true);
            }
        }
    };

    Layer.prototype._renderModeProperties = function (isReset) {

        for (var k in this.property) {
            this.property[k] = this.getProperty(true, k);
            this.$.trigger('propertyChanged', [k, this.property[k]]);
        }

        var fontSize = this.getProperty(true, 'fontsize');
        this.adjustFontSize(this.getProperty(false, 'adaptivefont'), fontSize, false);

        this.layer.attr('data-align', this.property.align);
        this.layer.attr('data-valign', this.property.valign);
        if (isReset) {
            this._resize(true);
        }

    };

    Layer.prototype.copyMode = function (from, to) {
        if (from != to) {
            this.deviceProperty[to] = $.extend({}, this.deviceProperty[to], this.deviceProperty[from]);
        }
    };

    Layer.prototype.getMode = function () {
        return this.layerEditor.getMode();
    };

    Layer.prototype._resize = function (isForced) {
        this.resize({
            slideW: this.layerEditor.getResponsiveRatio('h'),
            slideH: this.layerEditor.getResponsiveRatio('v')
        }, isForced);
    };

    Layer.prototype.doLinearResize = function (ratios) {
        this.doThrottledTheResize(ratios, true);
    };

    Layer.prototype.resize = function (ratios, isForced) {

        if (!this.parent || isForced) {
            //this.doThrottledTheResize(ratios, false);
            this.addToResizeCollection(this, ratios, false);
        }
    };

    Layer.prototype.doTheResize = function (ratios, isLinear, isThrottled) {
        var ratioPositionH = ratios.slideW,
            ratioSizeH = ratioPositionH,
            ratioPositionV = ratios.slideH,
            ratioSizeV = ratioPositionV;

        if (!parseInt(this.getProperty(false, 'responsivesize'))) {
            ratioSizeH = ratioSizeV = 1;
        }

        //var width = this.getProperty(true, 'width');
        //this.storeWithModifier('width', this.isDimensionPropertyAccepted(width) ? width : Math.round(width), ratioSizeH, true);
        //var height = this.getProperty(true, 'height');
        //this.storeWithModifier('height', this.isDimensionPropertyAccepted(height) ? height : Math.round(height), ratioSizeV, true);
        this.renderWithModifier('width', this.getProperty(true, 'width'), ratioSizeH);
        this.renderWithModifier('height', this.getProperty(true, 'height'), ratioSizeV);

        if (!parseInt(this.getProperty(false, 'responsiveposition'))) {
            ratioPositionH = ratioPositionV = 1;
        }
        //this.storeWithModifier('left', Math.round(this.getProperty(true, 'left')), ratioPositionH, true);
        //this.storeWithModifier('top', Math.round(this.getProperty(true, 'top')), ratioPositionV, true);
        this.renderWithModifier('left', this.getProperty(true, 'left'), ratioPositionH);
        this.renderWithModifier('top', this.getProperty(true, 'top'), ratioPositionV);
        if (!isLinear) {
            this.triggerLayerResized(isThrottled, ratios);
        }
    };

    Layer.prototype.resizeParent = function (e, ratios, isThrottled) {
        //this.doThrottledTheResize(ratios, false, isThrottled);
        this.addToResizeCollection(this, ratios, isThrottled);
    };

    Layer.prototype.addToResizeCollection = function (layer, ratios, isThrottled) {
        resizeCollection.ratios = ratios;
        resizeCollection.isThrottled = isThrottled;
        for (var i = 0; i < resizeCollection.layers.length; i++) {
            if (resizeCollection.layers[i] == this) {
                resizeCollection.layers.splice(i, 1);
                break;
            }
        }
        resizeCollection.layers.push(layer);

        requestRender();
        this.triggerLayerResized(isThrottled, ratios);
    };

    Layer.prototype.update = function () {
        var parent = this.parent;

        if (this.getProperty(true, 'align') == 'center') {
            var left = 0;
            if (parent) {
                left = parent.position().left + parent.width() / 2;
            } else {
                left = this.layer.parent().width() / 2;
            }
            var ratio = this.layerEditor.getResponsiveRatio('h');
            if (!parseInt(this.getProperty(false, 'responsiveposition'))) {
                ratio = 1;
            }
            this.layer.css('left', (left - this.layer.width() / 2 + this.getProperty(true, 'left') * ratio));
        }

        if (this.getProperty(true, 'valign') == 'middle') {
            var top = 0;
            if (parent) {
                top = parent.position().top + parent.height() / 2;
            } else {
                top = this.layer.parent().height() / 2;
            }
            var ratio = this.layerEditor.getResponsiveRatio('v');
            if (!parseInt(this.getProperty(false, 'responsiveposition'))) {
                ratio = 1;
            }
            this.layer.css('top', (top - this.layer.height() / 2 + this.getProperty(true, 'top') * ratio));
        }
        this.triggerLayerResized();
    };

    Layer.prototype.triggerLayerResized = function (isThrottled, ratios) {
        if (isThrottled) {
            this._triggerLayerResized(isThrottled, ratios);
        } else {
            this._triggerLayerResizedThrottled(true, ratios);
        }
    };

    Layer.prototype._triggerLayerResized = function (isThrottled, ratios) {

        this.layer.triggerHandler('LayerResized', [ratios || {
            slideW: this.layerEditor.getResponsiveRatio('h'),
            slideH: this.layerEditor.getResponsiveRatio('v')
        }, isThrottled || false]);
    };

    Layer.prototype.getStyleText = function () {
        var style = '';
        var crop = this.property.crop;
        if (crop == 'auto') {
            crop = 'hidden';
        }
        style += 'overflow:' + crop + ';';
        style += 'text-align:' + this.property.inneralign + ';';
        return style;
    };

    Layer.prototype.isDimensionPropertyAccepted = function (value) {
        if ((value + '').match(/[0-9]+%/) || value == 'auto') {
            return true;
        }
        return false;
    };

    scope.NextendSmartSliderLayer = Layer;


})(nextend.smartSlider, n2, window);
(function (smartSlider, $, scope, undefined) {
    var layerClass = '.n2-ss-layer',
        keys = {
            16: 0,
            38: 0,
            40: 0,
            37: 0,
            39: 0
        },
        nameToIndex = {
            left: 0,
            center: 1,
            right: 2,
            top: 0,
            middle: 1,
            bottom: 2
        },
        horizontalAlign = {
            97: 'left',
            98: 'center',
            99: 'right',
            100: 'left',
            101: 'center',
            102: 'right',
            103: 'left',
            104: 'center',
            105: 'right'
        },
        verticalAlign = {
            97: 'bottom',
            98: 'bottom',
            99: 'bottom',
            100: 'middle',
            101: 'middle',
            102: 'middle',
            103: 'top',
            104: 'top',
            105: 'top'
        };

    function AdminSlideLayerManager(layerManager, staticSlide, isUploadDisabled, uploadUrl, uploadDir) {
        this.activeLayerIndex = -1;
        this.snapToEnabled = true;
        this.staticSlide = staticSlide;

        this.layerDefault = {
            align: null,
            valign: null
        };

        this.solo = false;

        this.$ = $(this);
        smartSlider.layerManager = this;

        this.responsive = smartSlider.frontend.responsive;

        new NextendSmartSliderSidebar();

        this.layerList = [];

        this.layersItemsElement = $('#n2-ss-layers-items-list');

        this.frontendSlideLayers = layerManager;

        this.frontendSlideLayers.setZero();


        this.layerContainerElement = smartSlider.$currentSlideElement.find('.n2-ss-layers-container');
        if (!this.layerContainerElement.length) {
            this.layerContainerElement = smartSlider.$currentSlideElement;
        }

        this.layerContainerElement.parent().prepend('<div class="n2-ss-slide-border n2-ss-slide-border-left" /><div class="n2-ss-slide-border n2-ss-slide-border-top" /><div class="n2-ss-slide-border n2-ss-slide-border-right" /><div class="n2-ss-slide-border n2-ss-slide-border-bottom" />');


        this.slideSize = {
            width: this.layerContainerElement.width(),
            height: this.layerContainerElement.height()
        };

        smartSlider.frontend.sliderElement.on('SliderResize', $.proxy(this.refreshSlideSize, this));

        this.initToolbox();

        new NextendSmartSliderLayerAnimationManager(this);

        this.refreshLayers();

        smartSlider.itemEditor = this.itemEditor = new NextendSmartSliderItemManager(this);

        this.positionDisplay = $('<div class="n2 n2-ss-position-display"/>')
            .appendTo('body');

        this.zIndexList = [];

        this.layers.each($.proxy(function (i, layer) {
            new NextendSmartSliderLayer(this, $(layer), this.itemEditor);
        }, this));

        this.reIndexLayers();

        this._makeLayersOrderable();

        $('#smartslider-slide-toolbox-layer').on('mouseenter', function () {
            $('#n2-admin').addClass('smartslider-layer-highlight-active');
        }).on('mouseleave', function () {
            $('#n2-admin').removeClass('smartslider-layer-highlight-active');
        });

        this._initDeviceModeChange();

        //this.initBatch();
        this.initSnapTo();
        this.initEditorTheme();
        this.initAlign();
        this.initParentLinker();
        this.initEvents();

        var globalAdaptiveFont = $('#n2-ss-adaptive-font').on('click', $.proxy(function () {
            this.toolboxForm.adaptivefont.data('field').onoff.trigger('click');
        }, this));

        this.toolboxForm.adaptivefont.on('nextendChange', $.proxy(function () {
            if (this.toolboxForm.adaptivefont.val() == 1) {
                globalAdaptiveFont.addClass('n2-active');
            } else {
                globalAdaptiveFont.removeClass('n2-active');
            }
        }, this));


        new NextendElementNumber("n2-ss-font-size", -Number.MAX_VALUE, Number.MAX_VALUE);
        new NextendElementAutocompleteSimple("n2-ss-font-size", ["60", "80", "100", "120", "140", "160", "180"]);

        var globalFontSize = $('#n2-ss-font-size').on('outsideChange', $.proxy(function () {
            var value = parseInt(globalFontSize.val());
            this.toolboxForm.fontsize.val(value).trigger('change');
        }, this));

        this.toolboxForm.fontsize.on('nextendChange', $.proxy(function () {
            globalFontSize.data('field').insideChange(this.toolboxForm.fontsize.val());
        }, this));

        if (this.zIndexList.length > 0) {
            this.zIndexList[this.zIndexList.length - 1].activate();
        }


        $(window).on({
            keydown: $.proxy(function (e) {
                if (e.target.tagName != 'TEXTAREA' && e.target.tagName != 'INPUT' && (!smartSlider.timelineControl || !smartSlider.timelineControl.isActivated())) {
                    if (this.activeLayerIndex != -1) {
                        if (e.keyCode == 46) {
                            this.layerList[this.activeLayerIndex].delete();
                        } else if (e.keyCode == 35) {
                            this.layerList[this.activeLayerIndex].duplicate(true, false);
                            e.preventDefault();
                        } else if (e.keyCode == 16) {
                            keys[e.keyCode] = 1;
                        } else if (e.keyCode == 38) {
                            if (!keys[e.keyCode]) {
                                var fn = $.proxy(function () {
                                    this.layerList[this.activeLayerIndex].moveY(-1 * (keys[16] ? 10 : 1))
                                }, this);
                                fn();
                                keys[e.keyCode] = setInterval(fn, 100);
                            }
                            e.preventDefault();
                        } else if (e.keyCode == 40) {
                            if (!keys[e.keyCode]) {
                                var fn = $.proxy(function () {
                                    this.layerList[this.activeLayerIndex].moveY((keys[16] ? 10 : 1))
                                }, this);
                                fn();
                                keys[e.keyCode] = setInterval(fn, 100);
                            }
                            e.preventDefault();
                        } else if (e.keyCode == 37) {
                            if (!keys[e.keyCode]) {
                                var fn = $.proxy(function () {
                                    this.layerList[this.activeLayerIndex].moveX(-1 * (keys[16] ? 10 : 1))
                                }, this);
                                fn();
                                keys[e.keyCode] = setInterval(fn, 100);
                            }
                            e.preventDefault();
                        } else if (e.keyCode == 39) {
                            if (!keys[e.keyCode]) {
                                var fn = $.proxy(function () {
                                    this.layerList[this.activeLayerIndex].moveX((keys[16] ? 10 : 1))
                                }, this);
                                fn();
                                keys[e.keyCode] = setInterval(fn, 100);
                            }
                            e.preventDefault();
                        } else if (e.keyCode >= 97 && e.keyCode <= 105) {

                            var hAlign = horizontalAlign[e.keyCode],
                                vAlign = verticalAlign[e.keyCode],
                                toZero = false;
                            if (this.toolboxForm.align.val() == hAlign && this.toolboxForm.valign.val() == vAlign) {
                                toZero = true;
                            }
                            // numeric pad
                            this.horizontalAlign(hAlign, toZero);
                            this.verticalAlign(vAlign, toZero);

                        } else if (e.keyCode == 34) {
                            e.preventDefault();
                            var targetIndex = this.layerList[this.activeLayerIndex].zIndex - 1;
                            if (targetIndex < 0) {
                                targetIndex = this.zIndexList.length - 1;
                            }
                            this.zIndexList[targetIndex].activate();

                        } else if (e.keyCode == 33) {
                            e.preventDefault();
                            var targetIndex = this.layerList[this.activeLayerIndex].zIndex + 1;
                            if (targetIndex > this.zIndexList.length - 1) {
                                targetIndex = 0;
                            }
                            this.zIndexList[targetIndex].activate();

                        }
                    }
                }
            }, this),
            keyup: $.proxy(function (e) {
                if (typeof keys[e.keyCode] !== 'undefined' && keys[e.keyCode]) {
                    clearInterval(keys[e.keyCode]);
                    keys[e.keyCode] = 0;
                }
            }, this)
        });

        if (!isUploadDisabled) {
            smartSlider.frontend.sliderElement.fileupload({
                url: uploadUrl,
                pasteZone: false,
                dropZone: smartSlider.frontend.sliderElement,
                dataType: 'json',
                paramName: 'image',
                add: $.proxy(function (e, data) {
                    data.formData = {path: '/' + uploadDir};
                    data.submit();
                }, this),
                done: $.proxy(function (e, data) {
                    var response = data.result;
                    if (response.data && response.data.name) {
                        var item = this.itemEditor.createLayerItem('image');
                        item.reRender({
                            image: response.data.url
                        });
                        item.activate(null, true);
                    } else {
                        NextendAjaxHelper.notification(response);
                    }

                }, this),
                fail: $.proxy(function (e, data) {
                    NextendAjaxHelper.notification(data.jqXHR.responseJSON);
                }, this),

                start: function () {
                    NextendAjaxHelper.startLoading();
                },

                stop: function () {
                    setTimeout(function () {
                        NextendAjaxHelper.stopLoading();
                    }, 100);
                }
            });
        }
    };

    AdminSlideLayerManager.prototype.getMode = function () {
        return this.mode;
    };

    AdminSlideLayerManager.prototype._getMode = function () {
        return this.responsive.getNormalizedModeString();
    };

    AdminSlideLayerManager.prototype.getResponsiveRatio = function (axis) {
        if (axis == 'h') {
            return this.responsive.lastRatios.slideW;
        } else if (axis == 'v') {
            return this.responsive.lastRatios.slideH;
        }
        return 0;
    };

    AdminSlideLayerManager.prototype.createLayer = function (properties) {
        for (var k in this.layerDefault) {
            if (this.layerDefault[k] !== null) {
                properties[k] = this.layerDefault[k];
            }
        }
        var newLayer = new NextendSmartSliderLayer(this, false, this.itemEditor, properties);

        this.reIndexLayers();

        this._makeLayersOrderable();

        return newLayer;
    };

    AdminSlideLayerManager.prototype.addLayer = function (html, refresh) {
        var newLayer = $(html);
        this.layerContainerElement.append(newLayer);
        var layerObj = new NextendSmartSliderLayer(this, newLayer, this.itemEditor);

        if (refresh) {
            this.reIndexLayers();
            this.refreshMode();
        }
        return layerObj;
    };

    AdminSlideLayerManager.prototype.setSolo = function (layer) {
        if (this.solo) {
            this.solo.unmarkSolo();
            if (this.solo === layer) {
                this.solo = false;
                smartSlider.$currentSlideElement.removeClass('n2-ss-layer-solo-mode');
                return;
            } else {
                this.solo = false;
            }
        }

        this.solo = layer;
        layer.markSolo();
        smartSlider.$currentSlideElement.addClass('n2-ss-layer-solo-mode');
    };

    /**
     * Force the view to change to the second mode (layer)
     */
    AdminSlideLayerManager.prototype.switchToLayerTab = function () {
        smartSlider.slide._changeView(1);
    };

    //<editor-fold desc="Initialize the device mode changer">


    AdminSlideLayerManager.prototype._initDeviceModeChange = function () {
        var resetButton = $('#layerresettodesktop').on('click', $.proxy(this.__onResetToDesktopClick, this));
        this.resetToDesktopTRElement = resetButton.closest('tr');
        this.resetToDesktopGlobalElement = $('#n2-ss-reset-to-desktop').on('click', $.proxy(function () {
            if (this.resetToDesktopTRElement.css('display') == 'table-row') {
                resetButton.trigger('click');
            }
        }, this));


        var globalShowOnDevice = $('#n2-ss-show-on-device').on('click', $.proxy(function () {
            this.toolboxForm['showField' + this.mode.charAt(0).toUpperCase() + this.mode.substr(1)].data('field').onoff.trigger('click');
        }, this));

        this.globalShowOnDeviceCB = function (mode) {
            if (this.mode == mode) {
                if (this.toolboxForm['showField' + this.mode.charAt(0).toUpperCase() + this.mode.substr(1)].val() == 1) {
                    globalShowOnDevice.addClass('n2-active');
                } else {
                    globalShowOnDevice.removeClass('n2-active');
                }
            }
        };

        this.toolboxForm.showFieldDesktopPortrait.on('nextendChange', $.proxy(this.globalShowOnDeviceCB, this, 'desktopPortrait'));
        this.toolboxForm.showFieldDesktopLandscape.on('nextendChange', $.proxy(this.globalShowOnDeviceCB, this, 'desktopLandscape'));

        this.toolboxForm.showFieldTabletPortrait.on('nextendChange', $.proxy(this.globalShowOnDeviceCB, this, 'tabletPortrait'));
        this.toolboxForm.showFieldTabletLandscape.on('nextendChange', $.proxy(this.globalShowOnDeviceCB, this, 'tabletLandscape'));

        this.toolboxForm.showFieldMobilePortrait.on('nextendChange', $.proxy(this.globalShowOnDeviceCB, this, 'mobilePortrait'));
        this.toolboxForm.showFieldMobileLandscape.on('nextendChange', $.proxy(this.globalShowOnDeviceCB, this, 'mobileLandscape'));

        this.__onChangeDeviceOrientation();
        smartSlider.frontend.sliderElement.on('SliderDeviceOrientation', $.proxy(this.__onChangeDeviceOrientation, this));


        //this.__onResize();
        smartSlider.frontend.sliderElement.on('SliderResize', $.proxy(this.__onResize, this));
    };

    /**
     * Refresh the current responsive mode. Example: you are in tablet view and unpublish a layer for tablet, then you should need a refresh on the mode.
     */
    AdminSlideLayerManager.prototype.refreshMode = function () {

        this.__onChangeDeviceOrientation();

        smartSlider.frontend.responsive.reTriggerSliderDeviceOrientation();
    };

    /**
     * When the device mode changed we have to change the slider
     * @param mode
     * @private
     */
    AdminSlideLayerManager.prototype.__onChangeDeviceOrientation = function () {

        this.mode = this._getMode();
        this.globalShowOnDeviceCB(this.mode);

        this.resetToDesktopTRElement.css('display', (this.mode == 'desktopPortrait' ? 'none' : 'table-row'));
        this.resetToDesktopGlobalElement.css('display', (this.mode == 'desktopPortrait' ? 'none' : ''));
        for (var i = 0; i < this.layerList.length; i++) {
            this.layerList[i].changeEditorMode(this.mode);
        }
    };

    AdminSlideLayerManager.prototype.__onResize = function (e, ratios) {

        var sortedLayerList = this.getSortedLayers();

        for (var i = 0; i < sortedLayerList.length; i++) {
            sortedLayerList[i].doLinearResize(ratios);
        }
    };

    /**
     * Reset the custom values of the current mode on the current layer to the desktop values.
     * @private
     */
    AdminSlideLayerManager.prototype.__onResetToDesktopClick = function () {
        if (this.activeLayerIndex != -1) {
            var mode = this.getMode();
            this.layerList[this.activeLayerIndex].resetMode(mode, mode);
        }
    };

    AdminSlideLayerManager.prototype.copyOrResetMode = function (mode) {

        var currentMode = this.getMode();
        if (mode != 'desktopPortrait' && mode == currentMode) {
            for (var i = 0; i < this.layerList.length; i++) {
                this.layerList[i].resetMode(mode, currentMode);
            }
        } else if (mode != 'desktopPortrait' && currentMode == 'desktopPortrait') {
            for (var i = 0; i < this.layerList.length; i++) {
                this.layerList[i].resetMode(mode, currentMode);
            }
        } else if (mode != currentMode) {
            for (var i = 0; i < this.layerList.length; i++) {
                this.layerList[i].copyMode(currentMode, mode);
            }
        }

    };

    AdminSlideLayerManager.prototype.refreshSlideSize = function () {
        this.slideSize.width = smartSlider.frontend.dimensions.slide.width;
        this.slideSize.height = smartSlider.frontend.dimensions.slide.height;
    };

//</editor-fold>

    AdminSlideLayerManager.prototype._makeLayersOrderable = function () {
        this.layersOrderableElement = this.layersItemsElement.find(' > ul');
        this.layersOrderableElement
            .sortable({
                axis: "y",
                helper: 'clone',
                placeholder: "sortable-placeholder",
                forcePlaceholderSize: true,
                tolerance: "pointer",
                items: '.n2-ss-layer-row',
                //handle: '.n2-i-order',
                start: function (event, ui) {
                    $(ui.item).data("startindex", ui.item.index());
                },
                stop: $.proxy(function (event, ui) {
                    var startIndex = this.zIndexList.length - $(ui.item).data("startindex") - 1,
                        newIndex = this.zIndexList.length - $(ui.item).index() - 1;
                    this.zIndexList.splice(newIndex, 0, this.zIndexList.splice(startIndex, 1)[0]);
                    this.reIndexLayers();
                }, this)
            });
    };

    AdminSlideLayerManager.prototype.reIndexLayers = function () {
        this.zIndexList = this.zIndexList.filter(function (item) {
            return item != undefined
        });

        for (var i = this.zIndexList.length - 1; i >= 0; i--) {
            this.zIndexList[i].setZIndex(i);
        }
    };

    AdminSlideLayerManager.prototype.initEvents = function () {
        var parent = $('#n2-tab-events'),
            content = parent.find('> table').css('display', 'none'),
            heading = parent.find('.n2-h3'),
            headingLabel = heading.html(),
            row = $('<div class="n2-sidebar-row n2-sidebar-header-bg n2-form-dark n2-sets-header"><div class="n2-table"><div class="n2-tr"><div class="n2-td"><div class="n2-h3 n2-uc">' + headingLabel + '</div></div><div style="text-align: ' + (nextend.isRTL() ? 'left' : 'right') + ';" class="n2-td"></div></div></div></div>'),
            button = $('<a href="#" class="n2-button n2-button-medium n2-button-green n2-h5 n2-uc">' + n2_('Show') + '</a>').on('click', function (e) {
                e.preventDefault();
                if (button.hasClass('n2-button-green')) {
                    content.css('display', '');
                    button.html(n2_('Hide'));
                    button.addClass('n2-button-grey');
                    button.removeClass('n2-button-green');
                    $.jStorage.set("n2-ss-events", 1);
                } else {
                    content.css('display', 'none');
                    button.html(n2_('Show'));
                    button.addClass('n2-button-green');
                    button.removeClass('n2-button-grey');
                    $.jStorage.set("n2-ss-events", 0);
                }
            });
        if ($.jStorage.get("n2-ss-events", 0)) {
            content.css('display', '');
            button.html(n2_('Hide'));
            button.addClass('n2-button-grey');
            button.removeClass('n2-button-green');
        }
        heading.replaceWith(row);
        button.appendTo(row.find('.n2-td').eq(1));
    }

    AdminSlideLayerManager.prototype.initSnapTo = function () {

        var field = new NextendElementOnoff("n2-ss-snap");

        if (!$.jStorage.get("n2-ss-snap-to-enabled", 1)) {
            field.insideChange(0);
            this.snapToDisable();
        }

        field.element.on('outsideChange', $.proxy(this.switchSnapTo, this));
    };

    AdminSlideLayerManager.prototype.switchSnapTo = function (e) {
        e.preventDefault();
        if (this.snapToEnabled) {
            this.snapToDisable();
        } else {
            this.snapToEnable();
        }
    };

    AdminSlideLayerManager.prototype.snapToDisable = function () {
        this.snapToEnabled = false;
        this.snapToChanged(0);
    };

    AdminSlideLayerManager.prototype.snapToEnable = function () {
        this.snapToEnabled = true;
        this.snapToChanged(1);
    };
    AdminSlideLayerManager.prototype.snapToChanged = function () {
        for (var i = 0; i < this.layerList.length; i++) {
            this.layerList[i].snap();
        }
        $.jStorage.set("n2-ss-snap-to-enabled", this.snapToEnabled);
    };

    AdminSlideLayerManager.prototype.getSnap = function () {
        if (!this.snapToEnabled) {
            return false;
        }

        if (this.staticSlide) {
            return $('.n2-ss-static-slide .n2-ss-layer:not(.n2-ss-layer-locked):not(.n2-ss-layer-parent):visible');
        }
        return $('.n2-ss-slide.n2-ss-slide-active .n2-ss-layer:not(.n2-ss-layer-locked):not(.n2-ss-layer-parent):visible');
    };

    AdminSlideLayerManager.prototype.initEditorTheme = function () {
        this.themeElement = $('#n2-tab-smartslider-editor');
        this.themeButton = $('#n2-ss-theme').on('click', $.proxy(this.switchEditorTheme, this));
        if ($.jStorage.get("n2-ss-theme-dark", 0)) {
            this.themeButton.addClass('n2-active');
            this.themeElement.addClass('n2-ss-theme-dark');
        }
    };

    AdminSlideLayerManager.prototype.switchEditorTheme = function () {
        $.jStorage.set("n2-ss-theme-dark", !this.themeButton.hasClass('n2-active'));
        this.themeButton.toggleClass('n2-active');
        this.themeElement.toggleClass('n2-ss-theme-dark');
    };

    AdminSlideLayerManager.prototype.initAlign = function () {
        var hAlignButton = $('#n2-ss-horizontal-align .n2-radio-option'),
            vAlignButton = $('#n2-ss-vertical-align .n2-radio-option');

        hAlignButton.add(vAlignButton).on('click', $.proxy(function (e) {
            if (e.ctrlKey || e.metaKey) {
                var $el = $(e.currentTarget),
                    isActive = $el.hasClass('n2-sub-active'),
                    align = $el.data('align');
                switch (align) {
                    case 'left':
                    case 'center':
                    case 'right':
                        hAlignButton.removeClass('n2-sub-active');
                        if (isActive) {
                            $.jStorage.set('ss-item-horizontal-align', null);
                            this.layerDefault.align = null;
                        } else {
                            $.jStorage.set('ss-item-horizontal-align', align);
                            this.layerDefault.align = align;
                            $el.addClass('n2-sub-active');
                        }
                        break;
                    case 'top':
                    case 'middle':
                    case 'bottom':
                        vAlignButton.removeClass('n2-sub-active');
                        if (isActive) {
                            $.jStorage.set('ss-item-vertical-align', null);
                            this.layerDefault.valign = null;
                        } else {
                            $.jStorage.set('ss-item-vertical-align', align);
                            this.layerDefault.valign = align;
                            $el.addClass('n2-sub-active');
                        }
                        break;
                }
            } else if (this.activeLayerIndex != -1) {
                var align = $(e.currentTarget).data('align');
                switch (align) {
                    case 'left':
                    case 'center':
                    case 'right':
                        this.horizontalAlign(align, true);
                        break;
                    case 'top':
                    case 'middle':
                    case 'bottom':
                        this.verticalAlign(align, true);
                        break;
                }
            }
        }, this));

        this.toolboxForm.align.on('nextendChange', $.proxy(function () {
            hAlignButton.removeClass('n2-active');
            switch (this.toolboxForm.align.val()) {
                case 'left':
                    hAlignButton.eq(0).addClass('n2-active');
                    break;
                case 'center':
                    hAlignButton.eq(1).addClass('n2-active');
                    break;
                case 'right':
                    hAlignButton.eq(2).addClass('n2-active');
                    break;
            }
        }, this));
        this.toolboxForm.valign.on('nextendChange', $.proxy(function () {
            vAlignButton.removeClass('n2-active');
            switch (this.toolboxForm.valign.val()) {
                case 'top':
                    vAlignButton.eq(0).addClass('n2-active');
                    break;
                case 'middle':
                    vAlignButton.eq(1).addClass('n2-active');
                    break;
                case 'bottom':
                    vAlignButton.eq(2).addClass('n2-active');
                    break;
            }
        }, this));


        var hAlign = $.jStorage.get('ss-item-horizontal-align', null),
            vAlign = $.jStorage.get('ss-item-vertical-align', null);
        if (hAlign != null) {
            hAlignButton.eq(nameToIndex[hAlign]).addClass('n2-sub-active');
            this.layerDefault.align = hAlign;
        }
        if (vAlign != null) {
            vAlignButton.eq(nameToIndex[vAlign]).addClass('n2-sub-active');
            this.layerDefault.valign = vAlign;
        }
    };

    AdminSlideLayerManager.prototype.horizontalAlign = function (align, toZero) {
        if (this.toolboxForm.align.val() != align) {
            this.toolboxForm.align.data('field').options.eq(nameToIndex[align]).trigger('click');
        } else if (toZero) {
            this.toolboxForm.left.val(0).trigger('change');
        }
    };

    AdminSlideLayerManager.prototype.verticalAlign = function (align, toZero) {
        if (this.toolboxForm.valign.val() != align) {
            this.toolboxForm.valign.data('field').options.eq(nameToIndex[align]).trigger('click');
        } else if (toZero) {
            this.toolboxForm.top.val(0).trigger('change');
        }
    };

    AdminSlideLayerManager.prototype.initParentLinker = function () {
        var field = this.toolboxForm.parentid.data('field'),
            parentLinker = $('#n2-ss-parent-linker').on({
                click: function (e) {
                    field.click(e);
                },
                mouseenter: function (e) {
                    field.picker.trigger(e);
                },
                mouseleave: function (e) {
                    field.picker.trigger(e);
                }
            });
    };

    /**
     * Delete all layers on the slide
     */
    AdminSlideLayerManager.prototype.deleteLayers = function () {
        for (var i = this.layerList.length - 1; i >= 0; i--) {
            this.layerList[i].delete();
        }
    };

    AdminSlideLayerManager.prototype.layerDeleted = function (index) {

        this.reIndexLayers();

        var activeLayer = this.getSelectedLayer();

        this.layerList.splice(index, 1);

        if (index === this.activeLayerIndex) {
            this.activeLayerIndex = -1;
            if (this.zIndexList.length > 0) {
                this.zIndexList[this.zIndexList.length - 1].activate();
            } else {
                this.changeActiveLayer(-1);
            }
        } else if (activeLayer) {
            this.activeLayerIndex = activeLayer.getIndex();
        }
    };

    AdminSlideLayerManager.prototype.getSortedLayers = function () {
        var list = this.layerList.slice(),
            children = {};
        for (var i = list.length - 1; i >= 0; i--) {
            if (typeof list[i].property.parentid !== 'undefined' && list[i].property.parentid) {
                if (typeof children[list[i].property.parentid] == 'undefined') {
                    children[list[i].property.parentid] = [];
                }
                children[list[i].property.parentid].push(list[i]);
                list.splice(i, 1);
            }
        }
        for (var i = 0; i < list.length; i++) {
            if (typeof list[i].property.id !== 'undefined' && list[i].property.id && typeof children[list[i].property.id] !== 'undefined') {
                children[list[i].property.id].unshift(0);
                children[list[i].property.id].unshift(i + 1);
                list.splice.apply(list, children[list[i].property.id]);
                delete children[list[i].property.id];
            }
        }
        return list;
    };

    /**
     * Get the HTML code of the whole slide
     * @returns {string} HTML
     */
    AdminSlideLayerManager.prototype.getHTML = function () {
        var node = $('<div></div>');

        var list = this.layerList;
        for (var i = 0; i < list.length; i++) {
            node.append(list[i].getHTML(true, true));
        }

        return node.html();
    };


    AdminSlideLayerManager.prototype.getData = function () {
        var layers = [];

        var list = this.layerList;
        for (var i = 0; i < list.length; i++) {
            layers.push(list[i].getData(true));
        }

        return layers;
    };

    AdminSlideLayerManager.prototype.loadData = function (data, overwrite) {
        var layers = $.extend(true, [], data);
        if (overwrite) {
            this.deleteLayers();
        }
        var zIndexOffset = this.zIndexList.length;
        var idTranslation = {};
        for (var i = 0; i < layers.length; i++) {

            var layerData = layers[i],
                layer = $('<div class="n2-ss-layer"></div>')
                    .attr('style', layerData.style);

            var storedZIndex = layer.css('zIndex');
            if (storedZIndex == 'auto') {
                if (layerData.zIndex) {
                    storedZIndex = layerData.zIndex;
                } else {
                    storedZIndex = 1;
                }
            }
            layer.css('zIndex', storedZIndex + zIndexOffset);
            if (layerData.id) {
                var id = $.fn.uid();
                idTranslation[layerData.id] = id;
                layer.attr('id', id);
            }
            if (layerData.parentid) {
                if (typeof idTranslation[layerData.parentid] != 'undefined') {
                    layerData.parentid = idTranslation[layerData.parentid];
                } else {
                    layerData.parentid = '';
                }
            }

            for (var j = 0; j < layerData.items.length; j++) {
                $('<div class="n2-ss-item n2-ss-item-' + layerData.items[j].type + '"></div>')
                    .data('item', layerData.items[j].type)
                    .data('itemvalues', layerData.items[j].values)
                    .appendTo(layer);
            }

            delete layerData.style;
            delete layerData.items;
            layerData.animations = Base64.encode(JSON.stringify(layerData.animations));
            for (var k in layerData) {
                layer.data(k, layerData[k]);
            }
            this.addLayer(layer, false);
        }
        this.reIndexLayers();
        this.refreshMode();

        if (this.activeLayerIndex == -1 && this.layerList.length > 0) {
            this.layerList[0].activate();
        }
    };

    /**
     * Reloads the layers by the class name
     */
    AdminSlideLayerManager.prototype.refreshLayers = function () {
        this.layers = this.layerContainerElement.find(layerClass);
    };

//<editor-fold desc="Toolbox fields and related stuffs">

    /**
     * Initialize the sidebar Layer toolbox
     */
    AdminSlideLayerManager.prototype.initToolbox = function () {

        this.toolboxElement = $('#smartslider-slide-toolbox-layer');

        this.toolboxForm = {
            id: $('#layerid'),
            parentid: $('#layerparentid'),
            parentalign: $('#layerparentalign'),
            parentvalign: $('#layerparentvalign'),
            left: $('#layerleft'),
            top: $('#layertop'),
            responsiveposition: $('#layerresponsive-position'),
            width: $('#layerwidth'),
            height: $('#layerheight'),
            responsivesize: $('#layerresponsive-size'),
            showFieldDesktopPortrait: $('#layershow-desktop-portrait'),
            showFieldDesktopLandscape: $('#layershow-desktop-landscape'),
            showFieldTabletPortrait: $('#layershow-tablet-portrait'),
            showFieldTabletLandscape: $('#layershow-tablet-landscape'),
            showFieldMobilePortrait: $('#layershow-mobile-portrait'),
            showFieldMobileLandscape: $('#layershow-mobile-landscape'),
            crop: $('#layercrop'),
            inneralign: $('#layerinneralign'),
            parallax: $('#layerparallax'),
            align: $('#layeralign'),
            valign: $('#layervalign'),
            fontsize: $('#layerfont-size'),
            adaptivefont: $('#layeradaptive-font'),
            mouseenter: $('#layeronmouseenter'),
            click: $('#layeronclick'),
            mouseleave: $('#layeronmouseleave'),
            play: $('#layeronplay'),
            pause: $('#layeronpause'),
            stop: $('#layeronstop')
        };

        for (var k in this.toolboxForm) {
            this.toolboxForm[k].on('outsideChange', $.proxy(this.activateLayerPropertyChanged, this, k));
        }

        if (!this.responsive.isEnabled('desktop', 'Landscape')) {
            this.toolboxForm.showFieldDesktopLandscape.closest('.n2-mixed-group').css('display', 'none');
        }
        if (!this.responsive.isEnabled('tablet', 'Portrait')) {
            this.toolboxForm.showFieldTabletPortrait.closest('.n2-mixed-group').css('display', 'none');
        }
        if (!this.responsive.isEnabled('tablet', 'Landscape')) {
            this.toolboxForm.showFieldTabletLandscape.closest('.n2-mixed-group').css('display', 'none');
        }
        if (!this.responsive.isEnabled('mobile', 'Portrait')) {
            this.toolboxForm.showFieldMobilePortrait.closest('.n2-mixed-group').css('display', 'none');
        }
        if (!this.responsive.isEnabled('mobile', 'Landscape')) {
            this.toolboxForm.showFieldMobileLandscape.closest('.n2-mixed-group').css('display', 'none');
        }
    };

    AdminSlideLayerManager.prototype.activateLayerPropertyChanged = function (name, e) {
        if (this.activeLayerIndex != -1) {
            //@todo  batch? throttle
            var value = this.toolboxForm[name].val();
            this.layerList[this.activeLayerIndex].setProperty(name, value, 'manager');
        } else {
            var field = this.toolboxForm[name].data('field');
            if (typeof field !== 'undefined') {
                field.insideChange('');
            }
        }
    };

    /**
     * getter for the currently selected layer
     * @returns {jQuery|boolean} layer element in jQuery representation or false
     * @private
     */
    AdminSlideLayerManager.prototype.getSelectedLayer = function () {
        if (this.activeLayerIndex == -1) {
            return false;
        }
        return this.layerList[this.activeLayerIndex];
    };

//</editor-fold>

    AdminSlideLayerManager.prototype.changeActiveLayer = function (index) {
        var lastActive = this.activeLayerIndex;
        if (lastActive != -1) {
            var $layer = this.layerList[lastActive];
            // There is a chance that the layer already deleted
            if ($layer) {
                $layer.$.off('propertyChanged.layerEditor');

                $layer.deActivate();
            }
        }
        this.activeLayerIndex = index;

        if (index != -1) {
            var $layer = this.layerList[index];
            $layer.$.on('propertyChanged.layerEditor', $.proxy(this.activeLayerPropertyChanged, this));

            $layer.animation.activate();

            var properties = $layer.property;
            for (var name in properties) {
                this.activeLayerPropertyChanged({
                    target: $layer
                }, name, properties[name]);
            }
        }
    };

    AdminSlideLayerManager.prototype.activeLayerPropertyChanged = function (e, name, value) {
        if (typeof this['_formSet' + name] === 'function') {
            this['_formSet' + name](value, e.target);
        } else {
            var field = this.toolboxForm[name].data('field');
            if (typeof field !== 'undefined') {
                field.insideChange(value);
            }
        }
    };

    AdminSlideLayerManager.prototype._formSetname = function (value) {

    };

    AdminSlideLayerManager.prototype._formSetnameSynced = function (value) {

    };

    AdminSlideLayerManager.prototype._formSetdesktopPortrait = function (value, layer) {
        this.toolboxForm.showFieldDesktopPortrait.data('field').insideChange(value);
    };

    AdminSlideLayerManager.prototype._formSetdesktopLandscape = function (value, layer) {
        this.toolboxForm.showFieldDesktopLandscape.data('field').insideChange(value);
    };

    AdminSlideLayerManager.prototype._formSettabletPortrait = function (value, layer) {
        this.toolboxForm.showFieldTabletPortrait.data('field').insideChange(value);
    };

    AdminSlideLayerManager.prototype._formSettabletLandscape = function (value, layer) {
        this.toolboxForm.showFieldTabletLandscape.data('field').insideChange(value);
    };

    AdminSlideLayerManager.prototype._formSetmobilePortrait = function (value, layer) {
        this.toolboxForm.showFieldMobilePortrait.data('field').insideChange(value);
    };

    AdminSlideLayerManager.prototype._formSetmobileLandscape = function (value, layer) {
        this.toolboxForm.showFieldMobileLandscape.data('field').insideChange(value);
    };

    scope.NextendSmartSliderAdminSlideLayerManager = AdminSlideLayerManager;

})(nextend.smartSlider, n2, window);
