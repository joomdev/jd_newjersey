(function ($) {
    var currentLoads = parseInt(n2.jStorage.get('SS3FREELOADS', 0)),
        currentStatus = parseInt(n2.jStorage.get('SS3FREE', 0)),
        counter = 0,
        setStatus = function (status) {
            if ((currentStatus & status) == 0) {
                currentStatus = currentStatus | status;
                n2.jStorage.set('SS3FREE', currentStatus);
            }
        },
        STATUS = {
            CREATE_SLIDER: 2,
            ADD_SAMPLE_SLIDER: 4,
            EDIT_SLIDER: 8,
            EDIT_SLIDE: 16,
            ADD_LAYER: 32,
            ADD_SLIDES: 64
        }, CB = {
            CREATE_SLIDER: function () {
                $('.n2-box.n2-ss-create-slider').on('mousedown', function () {
                    setStatus(STATUS.CREATE_SLIDER);
                });
            },
            ADD_SAMPLE_SLIDER: function () {
                $('.n2-ss-demo-slider').on('mousedown', function () {
                    setStatus(STATUS.ADD_SAMPLE_SLIDER);
                });
            },
            EDIT_SLIDER: function () {
                if ($('#slidertitle').length) {
                    $('.n2-main-top-bar .n2-button-green').on('mousedown', function () {
                        setStatus(STATUS.EDIT_SLIDER);
                    });
                }
            },
            ADD_SLIDES: function () {
                $('.n2-slides-add').on('mousedown', function () {
                    setStatus(STATUS.ADD_SLIDES);
                });
            },
            EDIT_SLIDE: function () {
                if ($('#slidetitle').length) {
                    setStatus(STATUS.EDIT_SLIDE);
                }
            },
            ADD_LAYER: function () {
                $('#n2-ss-item-container .n2-ss-core-item').on('mousedown', function () {
                    setStatus(STATUS.ADD_LAYER);
                });
            }
        };
    $(window).ready(function () {
        for (var k in CB) {
            if ((currentStatus & STATUS[k]) == 0) {
                CB[k]();
            } else {
                counter++;
            }
        }

        n2.jStorage.set('SS3FREELOADS', currentLoads + 1);

        if (currentLoads > 30 && counter > 4) {
            $.ajax({
                type: 'GET',
                url: NextendAjaxHelper.makeAjaxUrl(window.location.href, {
                    nextendcontroller: 'settings',
                    nextendaction: 'rated'
                }),
                dataType: 'json'
            }).done(function () {
                n2.jStorage.set('SS3FREELOADS', 0)
                n2.jStorage.set('SS3FREE', 0);
            });

            var modal = new NextendModal({
                zero: {
                    size: [
                        600,
                        440
                    ],
                    title: n2_('Hurray! You\'re almost an expert!'),
                    back: false,
                    close: true,
                    content: '<img src="' + nextend.imageHelper.fixed('$ss$/admin/images/free/getpro1.jpg') + '" />' +
                    '<div class="n2-h2">Are you satisfied with Smart Slider 3?</div>',
                    controls: [
                        '<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Yes') + '</a>',
                        '<a href="http://smartslider3.com/suggestion/" target="_blank" class="n2-button n2-button-big n2-button-red n2-uc n2-h4">' + n2_('No') + '</a>'
                    ],
                    fn: {
                        show: function () {
                            this.controls.eq(0).on('click', $.proxy(function (e) {
                                e.preventDefault();
                                this.loadPane('pro');
                            }, this));
                            this.controls.eq(1).find('.n2-button-red').on('click', $.proxy(function () {
                                this.hide();
                            }, this));
                        }
                    }
                },
                pro: {
                    size: [
                        600,
                        N2PLATFORM == 'wordpress' ? 522 : 442
                    ],
                    title: n2_('Be a professional!'),
                    back: false,
                    close: true,
                    content: '<img src="' + nextend.imageHelper.fixed('$ss$/admin/images/free/getpro2.jpg') + '" />' +
                    '<div class="n2-h3">Take your slider to the next level with Smart Slider 3 PRO!</div>' +
                    '<a href="' + window.N2SSWHYPRO + '" target="_blank" style="margin-top: 20px;" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('See all features') + '</a>',
                    fn: {
                        show: function () {
                            if (N2PLATFORM == 'wordpress') {
                                this.content.append('<div class="n2-ss-rate"><div class="n2-h3">If you have a minute share your experience!</div><a href="https://wordpress.org/support/view/plugin-reviews/smart-slider-3" target="_blank" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Add your own review') + '</a></div>')
                            }
                        }
                    }
                }
            });
            modal.setCustomClass('n2-ss-go-pro');
            modal.show();
        }
    });
})(n2);
