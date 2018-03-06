/*
	# mod_sp_tweet - Twitter Module by JoomShaper.com
	# -----------------------------------------------
	# Author    JoomShaper http://www.joomshaper.com
	# Copyright (C) 2010 - 2014 JoomShaper.com. All Rights Reserved.
	# license - GNU/GPL V2 or Later
	# Websites: http://www.joomshaper.com
	*/
var sptweetSlide = new Class({
    Implements: [Options, Events],
    options: {
        'morphDuration': 400,
        'animationPeriodicalTime': 8000
    },
    initialize: function (selector, options) {
        this.setOptions(options);
        this.selector = $$(selector);
        this.lis = $$(selector + ' .sp-tweet-item');
        this.current = 0;
        if (this.lis.length > 0) {
            this.lis[0].setStyle('visibility', 'visible');
            this.lis[0].getParent().setStyle('height', this.lis[0].getHeight())
        }
        var Class = this;
        this.lis.each(function (lis, inc) {
            Class.item = lis;
            if (inc > 0) {
                lis.set('opacity', 0);
                lis.setStyle('z-index', 0)
            }
        });
		this.animation.periodical(this.options.animationPeriodicalTime, this);
    },
    'onAnimationEnd': function (div) {
        this.fireEvent('end', div);
        return div
    },
    'onAnimationStart': function (div) {
        this.fireEvent('start', div);
        return div
    },
    'goNext': function ($items) {
        this.current = (this.current < $items.length - 1) ? this.current + 1 : 0
    },
    'currentItem': function ($items) {
        return $items[this.current]
    },
    'fade': function ($items, Class) {
        var c = this.currentItem($items);
        new Fx.Morph(c, {
            duration: this.options.morphDuration,
            transition: Fx.Transitions.linear,
            'onComplete': function () {
                c.setStyle('height', null);
                c.setStyle('z-index', 0);
                c.fade('out');
                Class.onAnimationEnd(c)
            }
        }).start({
            'height': 0,
            'opacity': 0,
            'z-index': 0,
        });
        this.current = (this.current < $items.length - 1) ? this.current + 1 : 0;
        $items[this.current].getParent().set('tween', {}).tween('height', $items[this.current].getParent().getHeight(), $items[this.current].getHeight());
        this.onAnimationStart($items[this.current]).setStyle('z-index', 1).fade('in')
    },
    'animation': function () {
        this.fade(this.lis, this)
    }
});