;
(function ($, scope) {

    function NextendVisualManagerCore(parameters) {
        this.loadDefaults();

        this.$ = $(this);

        window.nextend[this.type + 'Manager'] = this;

        this.modals = this.initModals();

        this.lightbox = $('#n2-lightbox-' + this.type);

        this.notificationStack = new NextendNotificationCenterStack(this.lightbox.find('.n2-top-bar'));

        this.visualListContainer = this.lightbox.find('.n2-lightbox-sidebar-list');

        this.parameters = parameters;

        this.visuals = {};

        this.controller = this.initController();
        if (this.controller) {
            this.renderer = this.controller.renderer;
        }

        this.firstLoadVisuals(parameters.visuals);

        $('.n2-' + this.type + '-save-as-new')
            .on('click', $.proxy(this.saveAsNew, this));

        this.cancelButton = $('#n2-' + this.type + '-editor-cancel')
            .on('click', $.proxy(this.hide, this));

        this.saveButton = $('#n2-' + this.type + '-editor-save')
            .off('click')
            .on('click', $.proxy(this.setVisual, this));
    };

    NextendVisualManagerCore.prototype.setTitle = function (title) {
        this.lightbox.find('.n2-logo').html(title);
    };

    NextendVisualManagerCore.prototype.loadDefaults = function () {
        this.mode = 'linked';
        this.labels = {
            visual: n2_('visual'),
            visuals: n2_('visuals')
        };
        this.visualLoadDeferreds = {};
        this.showParameters = false;
    }


    NextendVisualManagerCore.prototype.initModals = function () {
        return new NextendVisualManagerModals(this);
    };

    NextendVisualManagerCore.prototype.firstLoadVisuals = function (visuals) {

        for (var k in visuals) {
            this.sets[k].loadVisuals(visuals[k]);
        }
    };

    NextendVisualManagerCore.prototype.initController = function () {

    };

    NextendVisualManagerCore.prototype.getVisual = function (id) {
        if (parseInt(id) > 0) {
            if (typeof this.visuals[id] !== 'undefined') {
                return this.visuals[id];
            } else if (typeof this.visualLoadDeferreds[id] !== 'undefined') {
                return this.visualLoadDeferreds[id];
            } else {
                var deferred = $.Deferred();
                this.visualLoadDeferreds[id] = deferred;
                this._loadVisualFromServer(id)
                    .done($.proxy(function () {
                        deferred.resolve(this.visuals[id]);
                        delete this.visualLoadDeferreds[id];
                    }, this))
                    .fail($.proxy(function () {
                        // This visual is Empty!!!
                        deferred.resolve({
                            id: -1,
                            name: n2_('Empty')
                        });
                        delete this.visualLoadDeferreds[id];
                    }, this));
                return deferred;
            }
        } else {
            try {
                JSON.parse(Base64.decode(id));
                return {
                    id: 0,
                    name: n2_('Static')
                };
            } catch (e) {
                // This visual is Empty!!!
                return {
                    id: -1,
                    name: n2_('Empty')
                };
            }
        }
    };

    NextendVisualManagerCore.prototype._loadVisualFromServer = function (visualId) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'loadVisual'
            }),
            data: {
                visualId: visualId
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                n2c.error('@todo: load the visual data!');
            }, this));
    };

    NextendVisualManagerCore.prototype.show = function (data, saveCallback, showParameters) {

        NextendEsc.add($.proxy(function () {
            this.hide();
            return true;
        }, this));

        this.notificationStack.enableStack();

        this.showParameters = $.extend({
            previewMode: false,
            previewHTML: false
        }, showParameters);

        $('body').css('overflow', 'hidden');
        this.lightbox.css('display', 'block');
        $(window)
            .on('resize.' + this.type + 'Manager', $.proxy(this.resize, this));
        this.resize();

        this.loadDataToController(data);
        this.controller.show();

        this.$.on('save', saveCallback);
    };

    NextendVisualManagerCore.prototype.setAndClose = function (data) {
        this.$.trigger('save', [data]);
    };

    NextendVisualManagerCore.prototype.hide = function (e) {
        this.controller.pause();
        this.notificationStack.popStack();
        if (typeof e !== 'undefined') {
            e.preventDefault();
            NextendEsc.pop();
        }
        this.controller.close();
        this.$.off('save');
        $(window).off('resize.' + this.type + 'Manager');
        $('body').css('overflow', '');
        this.lightbox.css('display', 'none');
    };

    NextendVisualManagerCore.prototype.resize = function () {
        var h = this.lightbox.height();
        var sidebar = this.lightbox.find('.n2-sidebar');
        sidebar.find('.n2-lightbox-sidebar-list').height(h - 1 - sidebar.find('.n2-logo').outerHeight() - sidebar.find('.n2-sidebar-row').outerHeight() - sidebar.find('.n2-save-as-new-container').parent().height());

        var contentArea = this.lightbox.find('.n2-content-area');
        contentArea.height(h - 1 - contentArea.siblings('.n2-top-bar, .n2-table').outerHeight());
    };

    NextendVisualManagerCore.prototype.loadDataToController = function (data) {
        if (this.isVisualData(data)) {
            $.when(this.getVisual(data)).done($.proxy(function (visual) {
                if (visual.id > 0) {
                    visual.activate();
                } else {
                    console.error(data + ' visual is not found linked');
                }
            }, this));
        } else {
            console.error(data + ' visual not found');
        }
    };

    NextendVisualManagerCore.prototype.isVisualData = function (data) {
        return parseInt(data) > 0;
    };

    NextendVisualManagerCore.prototype.setVisual = function (e) {
        e.preventDefault();
        switch (this.mode) {
            case 0:
                break;
            case 'static':
                this.modals.getLinkedOverwriteOrSaveAs()
                    .show('saveAsNew');
                break;
            case 'linked':
            default:
                if (this.activeVisual) {
                    if (this.activeVisual.compare(this.controller.get('set'))) {
                        //if (this.getBase64(this.activeVisual.name) == this.activeVisual.base64) {
                        this.setAndClose(this.activeVisual.id);
                        this.hide(e);
                    } else {

                        if (this.activeVisual && !this.activeVisual.isEditable()) {
                            this.modals.getLinkedOverwriteOrSaveAs()
                                .show('saveAsNew');
                        } else {
                            this.modals.getLinkedOverwriteOrSaveAs()
                                .show();
                        }
                    }
                } else {
                    this.modals.getLinkedOverwriteOrSaveAs()
                        .show('saveAsNew');
                }
                break;
        }
    };

    NextendVisualManagerCore.prototype.saveAsNew = function (e) {
        e.preventDefault();

        this.modals.getSaveAs()
            .show();
    };

    NextendVisualManagerCore.prototype._saveAsNew = function (name) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'addVisual'
            }),
            data: {
                setId: this.setsSelector.val(),
                value: Base64.encode(JSON.stringify({
                    name: name,
                    data: this.controller.get('saveAsNew')
                }))
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                var visual = response.data.visual;
                this.changeActiveVisual(this.sets[visual.referencekey].addVisual(visual));
            }, this));
    };

    NextendVisualManagerCore.prototype.saveActiveVisual = function (name) {

        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'changeVisual'
            }),
            data: {
                visualId: this.activeVisual.id,
                value: this.getBase64(name)
            },
            dataType: 'json'
        }).done($.proxy(function (response) {
            this.activeVisual.setValue(response.data.visual.value, true);
        }, this));
    };

    NextendVisualManagerCore.prototype.changeActiveVisual = function (visual) {
        if (this.activeVisual) {
            this.activeVisual.notActive();
            this.activeVisual = false;
        }
        if (visual /*&& (this.mode == 0 || this.mode == 'linked')*/) {
            if (this.mode == 'static') {
                this.setMode('linked');
            }
            visual.active();
            this.activeVisual = visual;
        }
    };

    NextendVisualManagerCore.prototype.getBase64 = function (name) {

        return Base64.encode(JSON.stringify({
            name: name,
            data: this.controller.get('set')
        }));
    };

    NextendVisualManagerCore.prototype.removeRules = function (mode, visual) {
        this.renderer.deleteRules(mode, this.parameters.renderer.pre, '.' + this.getClass(visual.id, mode));
    };

    scope.NextendVisualManagerCore = NextendVisualManagerCore;

    /**
     * Sets are visible
     */
    function NextendVisualManagerVisibleSets() {
        NextendVisualManagerCore.prototype.constructor.apply(this, arguments);
    }

    NextendVisualManagerVisibleSets.prototype = Object.create(NextendVisualManagerCore.prototype);
    NextendVisualManagerVisibleSets.prototype.constructor = NextendVisualManagerVisibleSets;

    NextendVisualManagerVisibleSets.prototype.firstLoadVisuals = function (visuals) {
        this.sets = {};
        this.setsByReference = {};

        this.setsSelector = $('#' + this.parameters.setsIdentifier + 'sets_select');
        for (var i = 0; i < this.parameters.sets.length; i++) {
            this.newVisualSet(this.parameters.sets[i]);
        }
        this.initSetsManager();

        for (var k in visuals) {
            this.sets[k].loadVisuals(visuals[k])
        }

        this.activeSet = this.sets[this.setsSelector.val()];
        this.activeSet.active();

        this.setsSelector.on('change', $.proxy(function () {
            this.activeSet.notActive();
            this.activeSet = this.sets[this.setsSelector.val()];
            this.activeSet.active();
        }, this));
    };


    NextendVisualManagerVisibleSets.prototype.initSetsManager = function () {
        new NextendVisualSetsManager(this);
    };

    NextendVisualManagerVisibleSets.prototype._loadVisualFromServer = function (visualId) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'loadSetByVisualId'
            }),
            data: {
                visualId: visualId
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                this.sets[response.data.set.setId].loadVisuals(response.data.set.visuals);

            }, this));
    };

    NextendVisualManagerVisibleSets.prototype.changeSet = function (setId) {
        if (this.setsSelector.val() != setId) {
            this.setsSelector.val(setId)
                .trigger('change');
        }
    };

    NextendVisualManagerVisibleSets.prototype.changeSetById = function (id) {
        if (typeof this.sets[id] !== 'undefined') {
            this.changeSet(id);
        }
    };

    NextendVisualManagerVisibleSets.prototype.newVisualSet = function (set) {
        return new NextendVisualSet(set, this);
    };

    scope.NextendVisualManagerVisibleSets = NextendVisualManagerVisibleSets;

    /**
     * Sets are editable
     * Ex.: Layout
     */
    function NextendVisualManagerEditableSets() {
        NextendVisualManagerVisibleSets.prototype.constructor.apply(this, arguments);
    }

    NextendVisualManagerEditableSets.prototype = Object.create(NextendVisualManagerVisibleSets.prototype);
    NextendVisualManagerEditableSets.prototype.constructor = NextendVisualManagerEditableSets;

    NextendVisualManagerEditableSets.prototype.initSetsManager = function () {
        new NextendVisualSetsManagerEditable(this);
    };

    scope.NextendVisualManagerEditableSets = NextendVisualManagerEditableSets;

    /**
     * Static and linked mode
     * Ex.: Style, Fonts, Animation
     */

    function NextendVisualManagerSetsAndMore() {
        NextendVisualManagerEditableSets.prototype.constructor.apply(this, arguments);

        this.linkedButton = $('#n2-' + this.type + '-editor-set-as-linked');
        this.setMode(0);
    }

    NextendVisualManagerSetsAndMore.prototype = Object.create(NextendVisualManagerEditableSets.prototype);
    NextendVisualManagerSetsAndMore.prototype.constructor = NextendVisualManagerSetsAndMore;


    NextendVisualManagerSetsAndMore.prototype.setMode = function (newMode) {
        if (newMode == 'static') {
            this.changeActiveVisual(null);
        }
        if (this.mode != newMode) {
            switch (newMode) {
                case 0:
                    //this.modeRadio.parent.css('display', 'none');
                    this.cancelButton.css('display', 'none');
                    this.saveButton
                        .off('click');
                    break;

                case 'static':
                default:
                    this.cancelButton.css('display', 'inline-block');
                    this.saveButton
                        .off('click')
                        .on('click', $.proxy(this.setVisualAsStatic, this));
                    this.linkedButton
                        .off('click')
                        .on('click', $.proxy(this.setVisualAsLinked, this));
                    break;
            }
            this.mode = newMode;
        }
    };

    NextendVisualManagerSetsAndMore.prototype.loadDataToController = function (data) {
        if (parseInt(data) > 0) {
            $.when(this.getVisual(data)).done($.proxy(function (visual) {
                if (visual.id > 0) {
                    this.setMode('linked');
                    visual.activate();
                } else {
                    this.setMode('static');
                    this.controller.load('', false, this.showParameters);
                }
            }, this));
        } else {
            var visualData = '';
            this.setMode('static');
            try {
                visualData = this.getStaticData(data);
            } catch (e) {
                // This visual is Empty!!!
            }
            this.controller.load(visualData, false, this.showParameters);
        }
    };

    NextendVisualManagerSetsAndMore.prototype.getStaticData = function (data) {
        var d = JSON.parse(Base64.decode(data)).data;
        if (typeof d === 'undefined') {
            return '';
        }
        return d;
    };

    NextendVisualManagerSetsAndMore.prototype.setVisualAsLinked = function (e) {
        this.setVisual(e);
    };

    NextendVisualManagerSetsAndMore.prototype.setVisualAsStatic = function (e) {
        e.preventDefault();
        this.setAndClose(this.getBase64(n2_('Static')));
        this.hide(e);
    };

    scope.NextendVisualManagerSetsAndMore = NextendVisualManagerSetsAndMore;


    /**
     * Multiple selection
     * Ex.: Background animation, Post background animation
     */

    function NextendVisualManagerMultipleSelection(parameters) {

        window.nextend[this.type + 'Manager'] = this;

        // Push the constructor to the first show as an optimization.
        this._lateInit = $.proxy(function (parameters) {
            NextendVisualManagerVisibleSets.prototype.constructor.call(this, parameters);
        }, this, parameters);

    }

    NextendVisualManagerMultipleSelection.prototype = Object.create(NextendVisualManagerVisibleSets.prototype);
    NextendVisualManagerMultipleSelection.prototype.constructor = NextendVisualManagerMultipleSelection;


    NextendVisualManagerMultipleSelection.prototype.lateInit = function () {
        if (!this.inited) {
            this.inited = true;

            this._lateInit();
        }
    };

    NextendVisualManagerMultipleSelection.prototype.show = function (data, saveCallback, controllerParameters) {

        this.lateInit();

        this.notificationStack.enableStack();

        NextendEsc.add($.proxy(function () {
            this.hide();
            return true;
        }, this));

        $('body').css('overflow', 'hidden');
        this.lightbox.css('display', 'block');
        $(window)
            .on('resize.' + this.type + 'Manager', $.proxy(this.resize, this));
        this.resize();

        var i = 0;
        if (data != '') {
            var selected = data.split('||'),
                hasSelected = false;
            for (; i < selected.length; i++) {
                $.when(this.getVisual(selected[i])).done(function (visual) {
                    if (visual && visual.check) {
                        visual.check();
                        if (!hasSelected) {
                            hasSelected = true;
                            visual.activate();
                        }
                    }
                });
            }
        }

        this.$.on('save', saveCallback);

        this.controller.start(controllerParameters);

        if (i == 0) {
            $.when(this.activeSet._loadVisuals())
                .done($.proxy(function () {
                    for (var k in this.activeSet.visuals) {
                        this.activeSet.visuals[k].activate();
                        break;
                    }
                }, this));
        }
    };

    NextendVisualManagerMultipleSelection.prototype.setVisual = function (e) {
        e.preventDefault();
        this.setAndClose(this.getAsString());
        this.hide(e);
    };

    NextendVisualManagerMultipleSelection.prototype.getAsString = function () {
        var selected = [];
        for (var k in this.sets) {
            var set = this.sets[k];
            for (var i in set.visuals) {
                if (set.visuals[i].checked) {
                    selected.push(set.visuals[i].id);
                }
            }
        }
        if (selected.length == 0 && this.activeVisual) {
            selected.push(this.activeVisual.id);
        }
        return selected.join('||');
    };

    NextendVisualManagerMultipleSelection.prototype.hide = function (e) {
        NextendVisualManagerVisibleSets.prototype.hide.apply(this, arguments);

        for (var k in this.sets) {
            var set = this.sets[k];
            for (var i in set.visuals) {
                set.visuals[i].unCheck();
            }
        }
    };

    scope.NextendVisualManagerMultipleSelection = NextendVisualManagerMultipleSelection;


    function NextendVisualCore(visual, visualManager) {
        this.id = visual.id;
        this.visualManager = visualManager;
        this.setValue(visual.value, false);
        this.visual = visual;
        this.visualManager.visuals[this.id] = this;
    };

    NextendVisualCore.prototype.compare = function (value) {

        var length = Math.max(this.value.length, value.length);
        for (var i = 0; i < length; i++) {
            if (!this._compareTab(typeof this.value[i] === 'undefined' ? {} : this.value[i], typeof value[i] === 'undefined' ? {} : value[i])) {
                return false;
            }
        }
        return true;
    };

    NextendVisualCore.prototype._compareTab = function (a, b) {
        var aProps = Object.getOwnPropertyNames(a);
        var bProps = Object.getOwnPropertyNames(b);
        if (a.length === 0 && bProps.length === 0) {
            return true;
        }

        if (aProps.length != bProps.length) {
            return false;
        }

        for (var i = 0; i < aProps.length; i++) {
            var propName = aProps[i];

            // If values of same property are not equal,
            // objects are not equivalent
            if (a[propName] !== b[propName]) {
                return false;
            }
        }

        return true;
    };

    NextendVisualCore.prototype.setValue = function (value, render) {
        var data = null;
        if (typeof value == 'string') {
            this.base64 = value;
            data = JSON.parse(Base64.decode(value));
        } else {
            data = value;
        }
        this.name = data.name;
        this.value = data.data;

        if (render) {
            this.render();
        }
    };

    NextendVisualCore.prototype.isSystem = function () {
        return (this.visual.system == 1);
    };

    NextendVisualCore.prototype.isEditable = function () {
        return (this.visual.editable == 1);
    };

    NextendVisualCore.prototype.activate = function (e) {
        if (typeof e !== 'undefined') {
            e.preventDefault();
        }
        this.visualManager.changeActiveVisual(this);
        this.visualManager.controller.load(this.value, false, this.visualManager.showParameters);
    };

    NextendVisualCore.prototype.active = function () {
    };

    NextendVisualCore.prototype.notActive = function () {
    };

    NextendVisualCore.prototype.delete = function (e) {
        if (e) {
            e.preventDefault();
        }
        NextendDeleteModal('n2-visual', this.name, $.proxy(function () {
            this._delete();
        }, this));
    };
    NextendVisualCore.prototype._delete = function () {

        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                nextendaction: 'deleteVisual'
            }),
            data: {
                visualId: this.id
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                var visual = response.data.visual;

                if (this.visualManager.activeVisual && this.id == this.visualManager.activeVisual.id) {
                    this.visualManager.changeActiveVisual(null);
                }
                this.removeRules();
                delete this.visualManager.visuals[this.id];
                delete this.set.visuals[this.id];
                this.row.remove();
                this.visualManager.$.trigger('visualDelete', [this.id]);
            }, this));
    };

    NextendVisualCore.prototype.removeRules = function () {

    };

    NextendVisualCore.prototype.render = function () {

    };

    NextendVisualCore.prototype.isUsed = function () {
        return false;
    };

    scope.NextendVisualCore = NextendVisualCore;

    function NextendVisualWithSet(visual, set, visualManager) {
        this.set = set;
        NextendVisualCore.prototype.constructor.call(this, visual, visualManager);
    };

    NextendVisualWithSet.prototype = Object.create(NextendVisualCore.prototype);
    NextendVisualWithSet.prototype.constructor = NextendVisualWithSet;

    NextendVisualWithSet.prototype.active = function () {
        var setId = this.set.set.id;
        this.visualManager.changeSet(setId);

        NextendVisualCore.prototype.active.call(this);
    };

    scope.NextendVisualWithSet = NextendVisualWithSet;


    function NextendVisualWithSetRow() {
        NextendVisualWithSet.prototype.constructor.apply(this, arguments);
    };

    NextendVisualWithSetRow.prototype = Object.create(NextendVisualWithSet.prototype);
    NextendVisualWithSetRow.prototype.constructor = NextendVisualWithSetRow;


    NextendVisualWithSetRow.prototype.createRow = function () {
        this.row = $('<li></li>')
            .append($('<a href="#">' + this.name + '</a>')
                .on('click', $.proxy(this.activate, this)));
        if (!this.isSystem()) {
            this.row.append($('<span class="n2-actions"></span>')
                .append($('<a href="#"><i class="n2-i n2-i-delete n2-i-grey-opacity"></i></a>')
                    .on('click', $.proxy(this.delete, this))));
        }
        return this.row;
    };

    NextendVisualWithSetRow.prototype.setValue = function (value, render) {
        NextendVisualWithSet.prototype.setValue.call(this, value, render);

        if (this.row) {
            this.row.find('> a').html(this.name);
        }
    };

    NextendVisualWithSetRow.prototype.active = function () {
        this.row.addClass('n2-active');
        NextendVisualWithSet.prototype.active.call(this);
    };

    NextendVisualWithSetRow.prototype.notActive = function () {
        this.row.removeClass('n2-active');
        NextendVisualWithSet.prototype.notActive.call(this);
    };

    scope.NextendVisualWithSetRow = NextendVisualWithSetRow;


    function NextendVisualWithSetRowMultipleSelection(visual, set, visualManager) {
        this.checked = false;
        visual.system = 1;
        visual.editable = 0;
        NextendVisualWithSetRow.prototype.constructor.apply(this, arguments);
    };

    NextendVisualWithSetRowMultipleSelection.prototype = Object.create(NextendVisualWithSetRow.prototype);
    NextendVisualWithSetRowMultipleSelection.prototype.constructor = NextendVisualWithSetRowMultipleSelection;


    NextendVisualWithSetRowMultipleSelection.prototype.createRow = function () {
        var row = NextendVisualWithSetRow.prototype.createRow.call(this);
        this.checkbox = $('<div class="n2-list-checkbox"><i class="n2-i n2-i-tick"></i></div>')
            .on('click', $.proxy(this.checkOrUnCheck, this))
            .prependTo(row.find('a'));

        return row;
    };

    NextendVisualWithSetRowMultipleSelection.prototype.setValue = function (data, render) {
        this.name = data.name;
        this.value = data.data;
        if (this.row) {
            this.row.find('> a').html(this.name);
        }

        if (render) {
            this.render();
        }
    };

    NextendVisualWithSetRowMultipleSelection.prototype.activate = function (e) {
        if (typeof e !== 'undefined') {
            e.preventDefault();
        }
        this.visualManager.changeActiveVisual(this);
        this.visualManager.controller.setAnimationProperties(this.value);
    };

    NextendVisualWithSetRowMultipleSelection.prototype.checkOrUnCheck = function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (this.checked) {
            this.unCheck();
        } else {
            this.check();
        }
    };

    NextendVisualWithSetRowMultipleSelection.prototype.check = function () {
        this.checked = true;
        this.checkbox.addClass('n2-active');
        this.activate();
    };

    NextendVisualWithSetRowMultipleSelection.prototype.unCheck = function () {
        this.checked = false;
        this.checkbox.removeClass('n2-active');
        this.activate();
    };

    scope.NextendVisualWithSetRowMultipleSelection = NextendVisualWithSetRowMultipleSelection;

})(n2, window);

;
(function ($, scope) {

    function NextendVisualManagerModals(visualManager) {
        this.visualManager = visualManager;
        this.linkedOverwriteOrSaveAs = null;
        this.saveAs = null;
    };

    NextendVisualManagerModals.prototype.getLinkedOverwriteOrSaveAs = function () {
        if (this.linkedOverwriteOrSaveAs == null) {
            var context = this;
            this.linkedOverwriteOrSaveAs = new NextendModal({
                zero: {
                    size: [
                        500,
                        140
                    ],
                    title: '',
                    back: false,
                    close: true,
                    content: '',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-grey n2-uc n2-h4">' + n2_('Save as new') + '</a>', '<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Overwrite current') + '</a>'],
                    fn: {
                        show: function () {
                            this.title.html(n2_printf(n2_('%s changed - %s'), context.visualManager.labels.visual, context.visualManager.activeVisual.name));
                            if (context.visualManager.activeVisual && !context.visualManager.activeVisual.isEditable()) {
                                this.loadPane('saveAsNew');
                            } else {
                                this.controls.find('.n2-button-green')
                                    .on('click', $.proxy(function (e) {
                                        e.preventDefault();
                                        context.visualManager.saveActiveVisual(context.visualManager.activeVisual.name)
                                            .done($.proxy(function () {
                                                this.hide(e);
                                                context.visualManager.setAndClose(context.visualManager.activeVisual.id);
                                                context.visualManager.hide();
                                            }, this));
                                    }, this));

                                this.controls.find('.n2-button-grey')
                                    .on('click', $.proxy(function (e) {
                                        e.preventDefault();
                                        this.loadPane('saveAsNew');
                                    }, this));
                            }
                        }
                    }
                },
                saveAsNew: {
                    size: [
                        500,
                        220
                    ],
                    title: n2_('Save as'),
                    back: 'zero',
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Save as new') + '</a>'],
                    fn: {
                        show: function () {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name').focus();

                            if (context.visualManager.activeVisual) {
                                nameField.val(context.visualManager.activeVisual.name);
                            }

                            button.on('click', $.proxy(function (e) {
                                e.preventDefault();
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    context.visualManager._saveAsNew(name)
                                        .done($.proxy(function () {
                                            this.hide(e);
                                            context.visualManager.setAndClose(context.visualManager.activeVisual.id);
                                            context.visualManager.hide();
                                        }, this));
                                }
                            }, this));
                        }
                    }
                }
            }, false);
        }
        return this.linkedOverwriteOrSaveAs;
    };

    NextendVisualManagerModals.prototype.getSaveAs = function () {
        if (this.saveAs === null) {
            var context = this;
            this.saveAs = new NextendModal({
                zero: {
                    size: [
                        500,
                        220
                    ],
                    title: n2_('Save as'),
                    back: false,
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Save as new') + '</a>'],
                    fn: {
                        show: function () {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name').focus();

                            if (context.visualManager.activeVisual) {
                                nameField.val(context.visualManager.activeVisual.name);
                            }

                            button.on('click', $.proxy(function (e) {
                                e.preventDefault();
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    context.visualManager._saveAsNew(name)
                                        .done($.proxy(this.hide, this, e));
                                }
                            }, this));
                        }
                    }
                }
            }, false);
        }
        return this.saveAs;
    };

    scope.NextendVisualManagerModals = NextendVisualManagerModals;
})(n2, window);
;
(function ($, scope) {

    function NextendVisualSetsManager(visualManager) {
        this.visualManager = visualManager;
        this.$ = $(this);
    }

    scope.NextendVisualSetsManager = NextendVisualSetsManager;

    function NextendVisualSetsManagerEditable(visualManager) {
        this.modal = null;
        NextendVisualSetsManager.prototype.constructor.apply(this, arguments);

        this.$.on({
            setAdded: function (e, set) {
                new NextendVisualSet(set, visualManager);
            },
            setChanged: function (e, set) {
                visualManager.sets[set.id].rename(set.value);
            },
            setDeleted: function (e, set) {
                visualManager.sets[set.id].delete();
                visualManager.setsSelector.trigger('change');
            }
        });

        this.manageButton = $('#' + visualManager.parameters.setsIdentifier + '-manage')
            .on('click', $.proxy(this.showManageSets, this));

    };

    NextendVisualSetsManagerEditable.prototype = Object.create(NextendVisualSetsManager.prototype);
    NextendVisualSetsManagerEditable.prototype.constructor = NextendVisualSetsManagerEditable;

    NextendVisualSetsManagerEditable.prototype.isSetAllowedToEdit = function (id) {
        if (id == -1 || typeof this.visualManager.sets[id] == 'undefined' || this.visualManager.sets[id].set.editable == 0) {
            return false;
        }
        return true;
    };


    NextendVisualSetsManagerEditable.prototype.createVisualSet = function (name) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                nextendaction: 'createSet'
            }),
            data: {
                name: name
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                this.$.trigger('setAdded', response.data.set)
            }, this));
    };

    NextendVisualSetsManagerEditable.prototype.renameVisualSet = function (id, name) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                nextendaction: 'renameSet'
            }),
            data: {
                setId: id,
                name: name
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                this.$.trigger('setChanged', response.data.set);
                nextend.notificationCenter.success(n2_('Set renamed'));
            }, this));
    };

    NextendVisualSetsManagerEditable.prototype.deleteVisualSet = function (id) {

        var d = $.Deferred(),
            set = this.visualManager.sets[id],
            deferreds = [];

        $.when(set._loadVisuals())
            .done($.proxy(function () {
                for (var k in set.visuals) {
                    deferreds.push(set.visuals[k]._delete());
                }

                $.when.apply($, deferreds).then($.proxy(function () {
                    NextendAjaxHelper.ajax({
                        type: "POST",
                        url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                            nextendaction: 'deleteSet'
                        }),
                        data: {
                            setId: id
                        },
                        dataType: 'json'
                    })
                        .done($.proxy(function (response) {
                            d.resolve();
                            this.$.trigger('setDeleted', response.data.set);
                        }, this));
                }, this));
            }, this))
            .fail(function () {
                d.reject();
            });
        return d
            .fail(function () {
                nextend.notificationCenter.error(n2_('Unable to delete the set'));
            });
    };

    NextendVisualSetsManagerEditable.prototype.showManageSets = function () {
        var visualManager = this.visualManager,
            setsManager = this;
        if (this.modal === null) {
            this.modal = new NextendModal({
                zero: {
                    size: [
                        500,
                        390
                    ],
                    title: n2_('Sets'),
                    back: false,
                    close: true,
                    content: '',
                    controls: ['<a href="#" class="n2-add-new n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Add new') + '</a>'],
                    fn: {
                        show: function () {
                            this.title.html(n2_printf(n2_('%s sets'), visualManager.labels.visual));

                            this.createHeading(n2_('Sets')).appendTo(this.content);
                            var data = [];
                            for (var k in visualManager.sets) {
                                var id = visualManager.sets[k].set.id;
                                if (setsManager.isSetAllowedToEdit(id)) {
                                    data.push([visualManager.sets[k].set.value, $('<div class="n2-button n2-button-grey n2-button-x-small n2-uc n2-h5">' + n2_('Rename') + '</div>')
                                        .on('click', {id: id}, $.proxy(function (e) {
                                            this.loadPane('rename', false, false, [e.data.id]);
                                        }, this)), $('<div class="n2-button n2-button-red n2-button-x-small n2-uc n2-h5">' + n2_('Delete') + '</div>')
                                        .on('click', {id: id}, $.proxy(function (e) {
                                            this.loadPane('delete', false, false, [e.data.id]);
                                        }, this))]);
                                } else {
                                    data.push([visualManager.sets[k].set.value, '', '']);
                                }
                            }
                            this.createTable(data, ['width:100%;', '', '']).appendTo(this.createTableWrap().appendTo(this.content));

                            this.controls.find('.n2-add-new')
                                .on('click', $.proxy(function (e) {
                                    e.preventDefault();
                                    this.loadPane('addNew');
                                }, this));
                        }
                    }
                },
                addNew: {
                    title: n2_('Create set'),
                    size: [
                        500,
                        220
                    ],
                    back: 'zero',
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Add') + '</a>'],
                    fn: {
                        show: function () {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name').focus();

                            button.on('click', $.proxy(function (e) {
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    setsManager.createVisualSet(name)
                                        .done($.proxy(function (response) {
                                            this.hide(e);
                                            nextend.notificationCenter.success(n2_('Set added'));
                                            visualManager.setsSelector.val(response.data.set.id).trigger('change')
                                        }, this));
                                }
                            }, this));
                        }
                    }
                },
                rename: {
                    title: n2_('Rename set'),
                    size: [
                        500,
                        220
                    ],
                    back: 'zero',
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Rename') + '</a>'],
                    fn: {
                        show: function (id) {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name')
                                    .val(visualManager.sets[id].set.value).focus();

                            button.on('click', $.proxy(function () {
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    setsManager.renameVisualSet(id, name)
                                        .done($.proxy(this.goBack, this));
                                }
                            }, this));
                        }
                    }
                },
                'delete': {
                    title: n2_('Delete set'),
                    size: [
                        500,
                        190
                    ],
                    back: 'zero',
                    close: true,
                    content: '',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-grey n2-uc n2-h4">' + n2_('Cancel') + '</a>', '<a href="#" class="n2-button n2-button-big n2-button-red n2-uc n2-h4">' + n2_('Yes') + '</a>'],
                    fn: {
                        show: function (id) {

                            this.createCenteredSubHeading(n2_printf(n2_('Do you really want to delete the set and all associated %s?'), visualManager.labels.visuals)).appendTo(this.content);

                            this.controls.find('.n2-button-grey')
                                .on('click', $.proxy(function (e) {
                                    e.preventDefault();
                                    this.goBack();
                                }, this));

                            this.controls.find('.n2-button-red')
                                .html('Yes, delete "' + visualManager.sets[id].set.value + '"')
                                .on('click', $.proxy(function (e) {
                                    e.preventDefault();
                                    setsManager.deleteVisualSet(id)
                                        .done($.proxy(this.goBack, this));
                                }, this));
                        }
                    }
                }
            }, false);
        }
        this.modal.show(false, [this.visualManager.setsSelector.val()]);
    };

    scope.NextendVisualSetsManagerEditable = NextendVisualSetsManagerEditable;


    function NextendVisualSet(set, visualManager) {
        this.set = set;
        this.visualManager = visualManager;

        this.visualList = $('<ul class="n2-list n2-h4"></ul>');


        this.visualManager.sets[set.id] = this;
        if (set.referencekey != '') {
            this.visualManager.setsByReference[set.referencekey] = set;
        }

        this.option = $('<option value="' + set.id + '">' + set.value + '</option>')
            .appendTo(this.visualManager.setsSelector);
    };


    NextendVisualSet.prototype.active = function () {
        $.when(this._loadVisuals())
            .done($.proxy(function () {
                this.visualList.appendTo(this.visualManager.visualListContainer);
            }, this));
    };

    NextendVisualSet.prototype.notActive = function () {
        this.visualList.detach();
    };

    NextendVisualSet.prototype.loadVisuals = function (visuals) {
        if (typeof this.visuals === 'undefined') {
            this.visuals = {};
            for (var i = 0; i < visuals.length; i++) {
                this.addVisual(visuals[i]);
            }
        }
    };

    NextendVisualSet.prototype._loadVisuals = function () {
        if (this.visuals == null) {
            return NextendAjaxHelper.ajax({
                type: "POST",
                url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                    nextendaction: 'loadVisualsForSet'
                }),
                data: {
                    setId: this.set.id
                },
                dataType: 'json'
            })
                .done($.proxy(function (response) {
                    this.loadVisuals(response.data.visuals);
                }, this));
        }
        return true;
    };

    NextendVisualSet.prototype.addVisual = function (visual) {
        if (typeof this.visuals[visual.id] === 'undefined') {
            this.visuals[visual.id] = this.visualManager.createVisual(visual, this);
            this.visualList.append(this.visuals[visual.id].createRow());
        }
        return this.visuals[visual.id];
    };

    NextendVisualSet.prototype.rename = function (name) {
        this.set.value = name;
        this.option.html(name);
    };

    NextendVisualSet.prototype.delete = function () {
        this.option.remove();
        delete this.visualManager.sets[this.set.id];
    };

    scope.NextendVisualSet = NextendVisualSet;

})(n2, window);
(function ($, scope) {
    "use strict";
    function NextendVisualEditorControllerBase() {
        this.loadDefaults();
        this.lightbox = $('#n2-lightbox-' + this.type);
    }

    NextendVisualEditorControllerBase.prototype.loadDefaults = function () {
        this.type = '';
        this._style = false;
        this.isChanged = false;
        this.visible = false;
    };

    NextendVisualEditorControllerBase.prototype.init = function () {
        this.lightbox = $('#n2-lightbox-' + this.type);
    };


    NextendVisualEditorControllerBase.prototype.pause = function () {

    };

    NextendVisualEditorControllerBase.prototype.getEmptyVisual = function () {
        return [];
    };

    NextendVisualEditorControllerBase.prototype.get = function () {
        return this.currentVisual;
    };

    NextendVisualEditorControllerBase.prototype.load = function (visual, tabs, parameters) {
        this.isChanged = false;
        this.lightbox.addClass('n2-editor-loaded');
        if (visual == '') {
            visual = this.getEmptyVisual();
        }
        this._load(visual, tabs, parameters);
    };

    NextendVisualEditorControllerBase.prototype._load = function (visual, tabs, parameters) {
        this.currentVisual = $.extend(true, {}, visual);
    };

    NextendVisualEditorControllerBase.prototype.addStyle = function (style) {
        if (this._style) {
            this._style.remove();
        }
        this._style = $("<style>" + style + "</style>").appendTo("head");
    };

    NextendVisualEditorControllerBase.prototype.show = function () {
        this.visible = true;
    };

    NextendVisualEditorControllerBase.prototype.close = function () {
        this.visible = false;
    };
    scope.NextendVisualEditorControllerBase = NextendVisualEditorControllerBase;

    function NextendVisualEditorControllerWithEditor() {

        NextendVisualEditorControllerBase.prototype.constructor.apply(this, arguments);

        this.editor = this.initEditor();
        this.editor.$.on('change', $.proxy(this.propertyChanged, this));
    };


    NextendVisualEditorControllerWithEditor.prototype = Object.create(NextendVisualEditorControllerBase.prototype);
    NextendVisualEditorControllerWithEditor.prototype.constructor = NextendVisualEditorControllerWithEditor;


    NextendVisualEditorControllerWithEditor.prototype.initEditor = function () {
        return new NextendVisualEditor();
    };

    NextendVisualEditorControllerWithEditor.prototype.propertyChanged = function (e, property, value) {
        this.isChanged = true;
        this.currentVisual[property] = value;
    };

    NextendVisualEditorControllerWithEditor.prototype._load = function (visual, tabs, parameters) {
        NextendVisualEditorControllerBase.prototype._load.apply(this, arguments);
        this.loadToEditor();
    };

    NextendVisualEditorControllerWithEditor.prototype.loadToEditor = function () {
        this.editor.load(this.currentVisual);
    };

    scope.NextendVisualEditorControllerWithEditor = NextendVisualEditorControllerWithEditor;


    function NextendVisualEditorController(previewModesList) {
        NextendVisualEditorControllerWithEditor.prototype.constructor.apply(this, arguments);

        this.previewModesList = previewModesList;

        this.initPreviewModes();
        if (previewModesList) {

            this.renderer = this.initRenderer();

            this.clearTabButton = this.lightbox.find('.n2-editor-clear-tab')
                .on('click', $.proxy(this.clearCurrentTab, this));


            this.tabField = new NextendElementRadio('n2-' + this.type + '-editor-tabs', ['0']);
            this.tabField.element.on('nextendChange.n2-editor', $.proxy(this.tabChanged, this));

            this.previewModeField = new NextendElementRadio('n2-' + this.type + '-editor-preview-mode', ['0']);
            this.previewModeField.element.on('nextendChange.n2-editor', $.proxy(this.previewModeChanged, this));

            this.previewModeField.options.eq(0).html(this.previewModesList[0].label);
        }
    }

    NextendVisualEditorController.prototype = Object.create(NextendVisualEditorControllerWithEditor.prototype);
    NextendVisualEditorController.prototype.constructor = NextendVisualEditorController;

    NextendVisualEditorController.prototype.loadDefaults = function () {
        NextendVisualEditorControllerWithEditor.prototype.loadDefaults.call(this);

        this.currentPreviewMode = '0';
        this.currentTabIndex = 0;
        this._renderTimeout = 0;
        this._delayStart = 0;
    };

    NextendVisualEditorController.prototype.initPreviewModes = function () {
    };

    NextendVisualEditorController.prototype.initRenderer = function () {
    };

    NextendVisualEditorController.prototype._load = function (visual, tabs, parameters) {

        this.currentVisual = [];
        for (var i = 0; i < visual.length; i++) {
            this.currentVisual[i] = $.extend(true, this.getCleanVisual(), visual[i]);
        }

        this.localModePreview = {};
        if (parameters.previewMode === false) {
            this.availablePreviewMode = false;
        } else {
            this.availablePreviewMode = parameters.previewMode;
            if (tabs === false) {
                tabs = this.getTabs();
            }
            for (var i = this.currentVisual.length; i < tabs.length; i++) {
                this.currentVisual[i] = this.getCleanVisual();
            }
            if (parameters.previewHTML !== false && parameters.previewHTML != '') {
                this.localModePreview[parameters.previewMode] = parameters.previewHTML;
            }
        }

        this.currentTabs = tabs;

        if (tabs === false) {
            tabs = [];
            for (var i = 0; i < this.currentVisual.length; i++) {
                tabs.push('#' + i);
            }
        }

        this.setTabs(tabs);
    };

    NextendVisualEditorController.prototype.getCleanVisual = function () {
        return {};
    };

    NextendVisualEditorController.prototype.getTabs = function () {
        return this.previewModesList[this.availablePreviewMode].tabs;
    };

    NextendVisualEditorController.prototype.setTabs = function (labels) {
        this.tabField.insideChange('0');
        for (var i = this.tabField.values.length - 1; i > 0; i--) {
            this.tabField.removeTabOption(this.tabField.values[i]);
        }
        this.tabField.options.eq(0).html(labels[0]);
        for (var i = 1; i < labels.length; i++) {
            this.tabField.addTabOption(i + '', labels[i]);
        }

        this.makePreviewModes();
    };

    NextendVisualEditorController.prototype.tabChanged = function () {
        if (document.activeElement) {
            document.activeElement.blur();
        }

        var tab = this.tabField.element.val();

        this.currentTabIndex = tab;
        if (typeof this.currentVisual[tab] === 'undefined') {
            this.currentVisual[tab] = {};
        }
        var values = $.extend({}, this.currentVisual[0]);
        if (tab != 0) {
            $.extend(values, this.currentVisual[tab]);
            this.clearTabButton.css('display', '');
        } else {
            this.clearTabButton.css('display', 'none');
        }

        this.editor.load(values);
        this._tabChanged();
    };

    NextendVisualEditorController.prototype._tabChanged = function () {
        this._renderPreview();
    };

    NextendVisualEditorController.prototype.clearCurrentTab = function (e) {
        if (e) {
            e.preventDefault();
        }
        this.currentVisual[this.currentTabIndex] = {};
        this.tabChanged();
        this._renderPreview();
    };

    NextendVisualEditorController.prototype.makePreviewModes = function () {
        var modes = [];
        // Show all preview mode for the tab count
        if (this.availablePreviewMode === false) {
            var tabCount = this.tabField.options.length;
            if (typeof this.previewModes[tabCount] !== "undefined") {
                modes = this.previewModes[tabCount];
            }
            this.setPreviewModes(modes);
        } else {
            modes = [this.previewModesList[this.availablePreviewMode]];
            this.setPreviewModes(modes, this.availablePreviewMode);
        }
    };

    NextendVisualEditorController.prototype.setPreviewModes = function (modes, defaultMode) {
        for (var i = this.previewModeField.values.length - 1; i > 0; i--) {
            this.previewModeField.removeTabOption(this.previewModeField.values[i]);
        }
        for (var i = 0; i < modes.length; i++) {
            this.previewModeField.addTabOption(modes[i].id, modes[i].label);
        }
        if (typeof defaultMode === 'undefined') {
            defaultMode = '0';
        }
        this.previewModeField.insideChange(defaultMode);
    };

    NextendVisualEditorController.prototype.previewModeChanged = function () {
        var mode = this.previewModeField.element.val();

        if (this.currentTabs === false) {
            if (mode == 0) {
                for (var i = 0; i < this.currentVisual.length; i++) {
                    this.tabField.options.eq(i).html('#' + i);
                }
            } else {
                var tabs = this.previewModesList[mode].tabs;
                if (tabs) {
                    for (var i = 0; i < this.currentVisual.length; i++) {
                        this.tabField.options.eq(i).html(tabs[i]);
                    }
                }
            }
        }
        this.currentPreviewMode = mode;
        this._renderPreview();

        this.setPreview(mode);
    };

    NextendVisualEditorController.prototype.setPreview = function (mode) {
    };

    NextendVisualEditorController.prototype.propertyChanged = function (e, property, value) {
        this.isChanged = true;
        this.currentVisual[this.currentTabIndex][property] = value;
        this.renderPreview();
    };

    NextendVisualEditorController.prototype.renderPreview = function () {
        var now = $.now();
        if (this._renderTimeout) {
            clearTimeout(this._renderTimeout);
            if (now - this._delayStart > 100) {
                this._renderPreview();
                this._delayStart = now;
            }
        } else {
            this._delayStart = now;
        }
        this._renderTimeout = setTimeout($.proxy(this._renderPreview, this), 33);
    };

    NextendVisualEditorController.prototype._renderPreview = function () {
        this._renderTimeout = false;
    };

    scope.NextendVisualEditorController = NextendVisualEditorController;

    function NextendVisualEditor() {
        this.fields = {};
        this.$ = $(this);
    };

    NextendVisualEditor.prototype.load = function (values) {
        this._off();
        this._on();
    };

    NextendVisualEditor.prototype._on = function () {
        for (var id in this.fields) {
            this.fields[id].element.on(this.fields[id].events);
        }
    };

    NextendVisualEditor.prototype._off = function () {
        for (var id in this.fields) {
            this.fields[id].element.off('.n2-editor');
        }
    };

    NextendVisualEditor.prototype.trigger = function (property, value) {
        this.$.trigger('change', [property, value]);
    };

    scope.NextendVisualEditor = NextendVisualEditor;

    function NextendVisualRenderer(editorController) {
        this.editorController = editorController;
    }

    NextendVisualRenderer.prototype.deleteRules = function (modeKey, pre, selector) {
        var mode = this.editorController.previewModesList[modeKey],
            rePre = new RegExp('@pre', "g"),
            reSelector = new RegExp('@selector', "g");
        for (var k in mode.selectors) {
            var rule = k
                .replace(rePre, pre)
                .replace(reSelector, selector);
            nextend.css.deleteRule(rule);
        }
    };

    NextendVisualRenderer.prototype.getCSS = function (modeKey, pre, selector, visualTabs, parameters) {
        var css = '',
            mode = this.editorController.previewModesList[modeKey],
            rePre = new RegExp('@pre', "g"),
            reSelector = new RegExp('@selector', "g");

        for (var k in mode.selectors) {
            var rule = k
                .replace(rePre, pre)
                .replace(reSelector, selector);

            css += rule + "{\n" + mode.selectors[k] + "}\n";
            if (typeof parameters.deleteRule !== 'undefined') {
                nextend.css.deleteRule(rule);
            }
        }


        if (modeKey == 0) {
            var visualTab = visualTabs[parameters.activeTab];
            if (parameters.activeTab != 0) {
                visualTab = $.extend({}, visualTabs[0], visualTab);
            }
            css = css.replace(new RegExp('@tab[0-9]*', "g"), this.render(visualTab));
        } else if (mode.renderOptions.combined) {
            for (var i = 0; i < visualTabs.length; i++) {
                css = css.replace(new RegExp('@tab' + i, "g"), this.render(visualTabs[i]));
            }
        } else {
            for (var i = 0; i < visualTabs.length; i++) {
                visualTabs[i] = $.extend({}, visualTabs[i])
                css = css.replace(new RegExp('@tab' + i, "g"), this.render(visualTabs[i]));
            }
        }
        return css;
    };

    NextendVisualRenderer.prototype.render = function (visualData) {
        var visual = this.makeVisualData(visualData);
        var css = '',
            raw = '';
        if (typeof visual.raw !== "undefined") {
            raw = visual.raw;
            delete visual.raw;
        }
        for (var k in visual) {

            css += this.deCase(k) + ": " + visual[k] + ";\n";
        }
        css += raw;
        return css;
    };

    NextendVisualRenderer.prototype.makeVisualData = function (visualData) {
        var visual = {};
        for (var property in visualData) {
            if (visualData.hasOwnProperty(property) && typeof visualData[property] !== 'function') {
                this['makeStyle' + property](visualData[property], visual);
            }
        }
        return visual;
    };

    NextendVisualRenderer.prototype.deCase = function (s) {
        return s.replace(/[A-Z]/g, function (a) {
            return '-' + a.toLowerCase()
        });
    };

    scope.NextendVisualRenderer = NextendVisualRenderer;

})(n2, window);

;
(function ($, scope) {

    function NextendAnimationManager() {
        NextendVisualManagerSetsAndMore.prototype.constructor.apply(this, arguments);
    };

    NextendAnimationManager.prototype = Object.create(NextendVisualManagerSetsAndMore.prototype);
    NextendAnimationManager.prototype.constructor = NextendAnimationManager;

    NextendAnimationManager.prototype.loadDefaults = function () {
        NextendVisualManagerSetsAndMore.prototype.loadDefaults.apply(this, arguments);
        this.type = 'animation';
        this.labels = {
            visual: n2_('animation'),
            visuals: n2_('animations')
        };
        this.availableFeatures = {
            repeatable: 0,
            specialZero: 0,
            repeat: 0,
            playEvent: 0,
            pauseEvent: 0,
            stopEvent: 0,
            instantOut: 0
        };
    };

    NextendAnimationManager.prototype.initController = function () {
        return new NextendAnimationEditorController(this.parameters.renderer.modes);
    };

    NextendAnimationManager.prototype.show = function (features, data, saveCallback, showParameters) {
        this.currentFeatures = $.extend({}, this.availableFeatures, features);

        NextendVisualManagerSetsAndMore.prototype.show.call(this, data.animations, saveCallback, showParameters);

        this.controller.loadTransformOrigin(data.transformOrigin);

        if (this.currentFeatures.repeatable) {
            this.controller.featureRepeatable(1);
            if (!data.repeatable) {
                data.repeatable = 0;
            }
            this.controller.loadRepeatable(data.repeatable);
        } else {
            this.controller.featureRepeatable(0);
        }

        if (this.currentFeatures.specialZero) {
            this.controller.featureSpecialZero(1);
            if (!data.specialZero) {
                data.specialZero = 0;
            }
            this.controller.loadSpecialZero(data.specialZero);
        } else {
            this.controller.featureSpecialZero(0);
        }

        if (this.currentFeatures.instantOut) {
            this.controller.featureInstantOut(1);
            if (!data.instantOut) {
                data.instantOut = 0;
            }
            this.controller.loadInstantOut(data.instantOut);
        } else {
            this.controller.featureInstantOut(0);
        }

        if (this.currentFeatures.repeat) {
            this.controller.featureRepeat(1);
            if (!data.repeatCount) {
                data.repeatCount = 0;
            }
            this.controller.loadRepeatCount(data.repeatCount);

            if (!data.repeatStartDelay) {
                data.repeatStartDelay = 0;
            }
            this.controller.loadRepeatStartDelay(data.repeatStartDelay);
        } else {
            this.controller.featureRepeat(0);
        }

        if (this.currentFeatures.playEvent) {
            this.controller.featurePlayEvent(1);
            if (!data.playEvent) {
                data.playEvent = '';
            }
            this.controller.loadPlayEvent(data.playEvent);
        } else {
            this.controller.featurePlayEvent(0);
        }

        if (this.currentFeatures.pauseEvent) {
            this.controller.featurePauseEvent(1);
            if (!data.pauseEvent) {
                data.pauseEvent = '';
            }
            this.controller.loadPauseEvent(data.pauseEvent);
        } else {
            this.controller.featurePauseEvent(0);
        }

        if (this.currentFeatures.stopEvent) {
            this.controller.featureStopEvent(1);
            if (!data.stopEvent) {
                data.stopEvent = '';
            }
            this.controller.loadStopEvent(data.stopEvent);
        } else {
            this.controller.featureStopEvent(0);
        }

        if (data.animations.length == 0) {
            $.when(this.activeSet._loadVisuals())
                .done($.proxy(function () {
                    for (var k in this.activeSet.visuals) {
                        this.activeSet.visuals[k].activate();
                        break;
                    }
                }, this));
        }
    };

    NextendAnimationManager.prototype.setAndClose = function (data) {
        if (data.length == 1 && this.isEquivalent(data[0], this.controller.getEmptyAnimation())) {
            data = [];
        }
        var animationData = {
            transformOrigin: this.controller.transformOrigin,
            animations: data
        };

        if (this.currentFeatures.repeatable) {
            animationData.repeatable = this.controller.repeatable;
        }

        if (this.currentFeatures.specialZero) {
            animationData.specialZero = this.controller.specialZero;
        }

        if (this.currentFeatures.instantOut) {
            animationData.instantOut = this.controller.instantOut;
        }

        if (this.currentFeatures.repeat) {
            animationData.repeatCount = this.controller.repeatCount;
            animationData.repeatStartDelay = this.controller.repeatStartDelay;
        }

        if (this.currentFeatures.playEvent) {
            animationData.playEvent = this.controller.playEvent;
        }

        if (this.currentFeatures.pauseEvent) {
            animationData.pauseEvent = this.controller.pauseEvent;
        }

        if (this.currentFeatures.stopEvent) {
            animationData.stopEvent = this.controller.stopEvent;
        }

        this.$.trigger('save', animationData);
    };

    NextendAnimationManager.prototype.createVisual = function (visual, set) {
        return new NextendAnimation(visual, set, this);
    };

    NextendAnimationManager.prototype.setVisualAsStatic = function (e) {
        e.preventDefault();
        this.setAndClose(this.controller.get('save'));
        this.hide(e);
    };

    NextendAnimationManager.prototype.setMode = function (newMode) {
        NextendVisualManagerSetsAndMore.prototype.setMode.call(this, 'static');
    };

    NextendAnimationManager.prototype.getStaticData = function (data) {
        return data;
    };

    NextendAnimationManager.prototype.isEquivalent = function (a, b) {
        // Create arrays of property names
        var aProps = Object.getOwnPropertyNames(a);
        var bProps = Object.getOwnPropertyNames(b);

        // If number of properties is different,
        // objects are not equivalent
        if (aProps.length != bProps.length) {
            return false;
        }

        for (var i = 0; i < aProps.length; i++) {
            var propName = aProps[i];

            // If values of same property are not equal,
            // objects are not equivalent
            if (a[propName] !== b[propName]) {
                return false;
            }
        }

        // If we made it this far, objects
        // are considered equivalent
        return true;
    };

    scope.NextendAnimationManager = NextendAnimationManager;


    function NextendAnimation() {
        NextendVisualWithSetRow.prototype.constructor.apply(this, arguments);
    };

    NextendAnimation.prototype = Object.create(NextendVisualWithSetRow.prototype);
    NextendAnimation.prototype.constructor = NextendAnimation;

    NextendAnimation.prototype.activate = function (e) {
        if (typeof e !== 'undefined') {
            e.preventDefault();
        }
        this.visualManager.changeActiveVisual(this);
        if (typeof this.value.specialZero !== 'undefined') {
            this.visualManager.controller.loadSpecialZero(this.value.specialZero);
        }
        if (typeof this.value.transformOrigin !== 'undefined') {
            this.visualManager.controller.loadTransformOrigin(this.value.transformOrigin);
        }
        this.visualManager.controller.load(this.value.animations, false, this.visualManager.showParameters);
    };

    scope.NextendAnimation = NextendAnimation;

})(n2, window);

;
(function ($, scope) {

    var zero = {
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
    };

    function NextendAnimationEditorController() {

        this.timeline = new NextendTimeline();

        this.initTabOrdering();

        NextendVisualEditorController.prototype.constructor.apply(this, arguments);

        $('#n2-animation-editor-tab-add').on('click', $.proxy(this.addTab, this));
        $('#n2-animation-editor-tab-delete').on('click', $.proxy(this.deleteCurrentTab, this));
        this.preview = $('<div class="n2-animation-preview-box" style="background-image: url(' + nextend.imageHelper.getRepeatedPlaceholder() + ')"></div>').appendTo($('#n2-animation-editor-preview'));
        this.setPreviewSize(400, 100);

        this.initBackgroundColor();
    }

    NextendAnimationEditorController.prototype = Object.create(NextendVisualEditorController.prototype);
    NextendAnimationEditorController.prototype.constructor = NextendAnimationEditorController;

    NextendAnimationEditorController.prototype.loadDefaults = function () {
        NextendVisualEditorController.prototype.loadDefaults.call(this);
        this.type = 'animation';
        this.group = 'in';
        this.mode = 0;
        this.playing = false;
        this.specialZero = 0;
        this.transformOrigin = '0|*|0|*|0';
        this.playEvent = '';
        this.pauseEvent = '';
        this.stopEvent = '';
        this.repeatable = 0;
        this.instantOut = 0;
    };


    NextendAnimationEditorController.prototype.setPreviewSize = function (w, h) {
        this.preview.css({
            width: w,
            height: h,
            marginLeft: -w / 2,
            marginTop: -h / 2
        });
        return this;
    };

    NextendAnimationEditorController.prototype.setGroup = function (group) {
        this.group = group;
        return this;
    };

    NextendAnimationEditorController.prototype.initPreviewModes = function () {

        this.previewModes = [this.previewModesList['solo']];
    };

    NextendAnimationEditorController.prototype.makePreviewModes = function () {
        if (this.tabField.options.length > 0) {
            this.setPreviewModes(this.previewModes);
        } else {
            this.setPreviewModes([]);
        }
    };

    NextendAnimationEditorController.prototype.initRenderer = function () {
        return new NextendVisualRenderer(this);
    };

    NextendAnimationEditorController.prototype.initEditor = function () {
        var editor = new NextendAnimationEditor();
        editor.$.on('nameChanged', $.proxy(this.animationNameChanged, this));
        return editor;
    };


    NextendAnimationEditorController.prototype.pause = function () {
        this.clearTimeline();
    };

    NextendAnimationEditorController.prototype.get = function (type) {
        if (type == 'saveAsNew') {
            return {
                specialZero: this.specialZero,
                transformOrigin: this.transformOrigin,
                animations: this.currentVisual
            };
        }
        return this.currentVisual;
    };

    NextendAnimationEditorController.prototype.getEmptyAnimation = function () {
        return {
            name: 'Animation',
            duration: 0.8,
            delay: 0,
            ease: 'easeOutCubic',
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
        };
    };

    NextendAnimationEditorController.prototype.getEmptyVisual = function () {
        return [this.getEmptyAnimation()];
    };

    NextendAnimationEditorController.prototype.initBackgroundColor = function () {

        new NextendElementText("n2-animation-editor-background-color");
        new NextendElementColor("n2-animation-editor-background-color", 0);

        var box = this.lightbox.find('.n2-editor-preview-box');
        $('#n2-animation-editor-background-color').on('nextendChange', function () {
            box.css('background', '#' + $(this).val());
        });
    };

    NextendAnimationEditorController.prototype._renderPreview = function () {
        NextendVisualEditorController.prototype._renderPreview.call(this);

        if (this.visible) {
            this.refreshTimeline();
        }
    };

    NextendAnimationEditorController.prototype.setPreview = function (mode) {
        if (this.visible) {
            this.refreshTimeline();
        }
    };

    NextendAnimationEditorController.prototype.getPreviewCssClass = function () {
        return 'n2-' + this.type + '-editor-preview';
    };

    NextendAnimationEditorController.prototype._load = function (visual, tabs, parameters) {

        parameters.previewMode = true;

        for (var i = 0; i < visual.length; i++) {
            visual[i] = $.extend({}, this.getEmptyAnimation(), visual[i]);
        }

        NextendVisualEditorController.prototype._load.call(this, visual, tabs, parameters);
    };

    NextendAnimationEditorController.prototype.initTabOrdering = function () {
        var originalIndex = -1,
            parent = $('#n2-animation-editor-tabs').parent();
        parent.sortable({
            items: '.n2-radio-option',
            start: $.proxy(function (e, ui) {
                originalIndex = this.tabField.options.index(ui.item);
            }, this),
            update: $.proxy(function (e, ui) {
                var targetIndex = ui.item.index();
                parent.sortable('cancel');

                var visualTab = this.currentVisual[originalIndex];
                this.currentVisual.splice(originalIndex, 1);
                this.currentVisual.splice(targetIndex, 0, visualTab);
                for (var i = 0; i < this.currentVisual.length; i++) {
                    this.tabField.options.eq(i).html(this.currentVisual[i].name);
                }
                this.tabField.options.eq(targetIndex).trigger('click');
                originalIndex = -1;

                if (this.currentPreviewMode != 'solo') {
                    this.refreshTimeline();
                } else {
                    this._tabChanged();
                }
            }, this)
        });
    };

    NextendAnimationEditorController.prototype.getTabs = function () {
        var tabs = [];
        for (var i = 0; i < this.currentVisual.length; i++) {
            tabs.push(this.currentVisual[i].name);
        }
        return tabs;
    };

    NextendAnimationEditorController.prototype._tabChanged = function () {
        if (this.currentPreviewMode == 'solo') {
            this.refreshTimeline();
        }
    };

    NextendAnimationEditorController.prototype.clearCurrentTab = function (e) {
        if (e) {
            e.preventDefault();
        }
    };

    NextendAnimationEditorController.prototype.addTab = function (e, force) {
        if (e) {
            e.preventDefault();
        }
        var i = this.tabField.values.length;
        this.currentVisual[i] = this.getEmptyAnimation();
        this.tabField.addTabOption(i + '', this.currentVisual[i].name);

        this.tabField.options.eq(i).trigger('click');

        if (this.currentPreviewMode != 'solo') {
            this.refreshTimeline();
        }
    };

    NextendAnimationEditorController.prototype.deleteCurrentTab = function (e) {
        if (e) {
            e.preventDefault();
        }
        this.deleteTab(this.currentTabIndex);

        this.currentTabIndex = Math.min(this.currentTabIndex, this.currentVisual.length - 1);
        this.tabField.options.eq(this.currentTabIndex).trigger('click');
    };

    NextendAnimationEditorController.prototype.deleteTab = function (index) {
        if (this.currentVisual.length > 1) {
            this.tabField.removeTabOption(this.tabField.values[index]);
            this.tabField.values = [];
            for (var i = 0; i < this.tabField.options.length; i++) {
                this.tabField.values.push(i + '');
            }
            this.currentVisual.splice(index, 1);
        } else {
            this.addTab(null, true);
            this.deleteTab(0);
        }

        if (this.currentPreviewMode != 'solo') {
            this.refreshTimeline();
        }
    };

    NextendAnimationEditorController.prototype.animationNameChanged = function (e, name) {
        this.tabField.options.eq(this.currentTabIndex).html(name);
    };

    NextendAnimationEditorController.prototype.show = function () {
        NextendVisualEditorController.prototype.show.call(this);
        this.createTimeline();
    };

    NextendAnimationEditorController.prototype.hide = function () {
        this.clearTimeline();
        NextendVisualEditorController.prototype.hide.call(this);
    };

    NextendAnimationEditorController.prototype.createTimeline = function () {
        if (this.timeline) {
            this.timeline.pause(0);
        }
        this.timeline = new NextendTimeline({
            paused: 1
        });

        this.timeline.eventCallback("onComplete", $.proxy(function () {
            this.timeline.play(0, false);
        }, this));

        this.timeline.set(this.preview.get(0), {
            transformOrigin: this.transformOrigin.split('|*|').join('% ') + 'px'
        }, 0);

        var animations = [];

        switch (this.currentPreviewMode) {
            case 'solo':
                animations.push($.extend({}, this.currentVisual[this.currentTabIndex]));
                break;
            default:
                $.extend(true, animations, this.currentVisual);
        }

        for (var i = 0; i < animations.length; i++) {
            if (animations[i].delay > 0.5) {
                animations[i].delay = 0.5;
            }
        }

        switch (this.group) {

            case 'in':
                this.buildTimelineIn(this.timeline, this.preview.get(0), animations, 1);
                break;
            case 'loop':
                if (this.currentPreviewMode == 'solo') {
                    this.buildTimelineOut(this.timeline, this.preview.get(0), animations, 1);
                } else {
                    this.buildTimelineLoop(this.timeline, this.preview.get(0), animations, 1);
                }
                break;
            case 'out':
                this.buildTimelineOut(this.timeline, this.preview.get(0), animations, 1);
                break;
            default:
                console.log(this.group + ' animation is not supported!');
        }
        if (this.timeline.totalDuration() > 0) {
            this.timeline.play();
        }
    };

    NextendAnimationEditorController.prototype.refreshTimeline = function () {
        this.clearTimeline();
        this.createTimeline();
    };

    NextendAnimationEditorController.prototype.clearTimeline = function () {
        if (this.timeline) {
            this.timeline.pause();
            if (this.repeatTimeout) {
                clearTimeout(this.repeatTimeout);
            }
            this.timeline.progress(1, true);
        }
    };

    NextendAnimationEditorController.prototype.setCurrentZero = function (element) {
        NextendTween.set(element, $.extend({}, this.currentZero));
    };

    NextendAnimationEditorController.prototype.buildTimelineIn = function (timeline, element, animations, ratio) {

        this.currentZero = zero;
        if (this.group == 'in' && (this.currentPreviewMode != 'solo' || this.currentTabIndex == this.currentVisual.length - 1) && this.specialZero && animations.length > 0) {
            this.currentZero = animations.pop();
            delete this.currentZero.name;
            this.currentZero.x = this.currentZero.x * ratio;
            this.currentZero.y = this.currentZero.y * ratio;
            this.currentZero.z = this.currentZero.z * ratio;
            this.currentZero.rotationX = -this.currentZero.rotationX;
            this.currentZero.rotationY = -this.currentZero.rotationY;
            this.currentZero.rotationZ = -this.currentZero.rotationZ;
            this.setCurrentZero(element);
        }

        var duration = 0,
            chain = this._buildAnimationChainIn(animations, ratio, this.currentZero);
        var i = 0;
        if (chain.length > 0) {
            timeline.fromTo(element, chain[i].duration, chain[i].from, chain[i].to, duration);
            duration += chain[i].duration + chain[i].to.delay;
            i++;

            for (; i < chain.length; i++) {
                timeline.to(element, chain[i].duration, chain[i].to, duration);
                duration += chain[i].duration + chain[i].to.delay;
            }
        }
    };

    NextendAnimationEditorController.prototype._buildAnimationChainIn = function (animations, ratio, currentZero) {
        var preparedAnimations = [
            {
                from: currentZero
            }
        ];
        for (var i = animations.length - 1; i >= 0; i--) {
            var animation = animations[i],
                delay = animation.delay,
                duration = animation.duration,
                ease = animation.ease;
            delete animation.delay;
            delete animation.duration;
            delete animation.ease;
            delete animation.name;

            var previousAnimation = preparedAnimations[0].from;

            //animation.x = parseFloat(previousAnimation.x) + animation.x * ratio;
            //animation.y = parseFloat(previousAnimation.y) + animation.y * ratio;
            //animation.z = parseFloat(previousAnimation.z) + animation.z * ratio;
            animation.x = -animation.x * ratio;
            animation.y = -animation.y * ratio;
            animation.z = -animation.z * ratio;
            animation.rotationX = -animation.rotationX;
            animation.rotationY = -animation.rotationY;
            animation.rotationZ = -animation.rotationZ;

            preparedAnimations.unshift({
                duration: duration,
                from: animation,
                to: $.extend({}, previousAnimation, {
                    ease: ease,
                    delay: delay
                })
            });
        }
        preparedAnimations.pop();
        return preparedAnimations;
    };

    NextendAnimationEditorController.prototype.buildTimelineLoop = function (timeline, element, animations, ratio) {

        var chain = this._buildAnimationChainLoop(animations, ratio);
        var i = 0;
        if (chain.length > 0) {
            timeline.fromTo(element, chain[i].duration, chain[i].from, chain[i].to);
            i++;
            for (; i < chain.length; i++) {
                timeline.to(element, chain[i].duration, chain[i].to);
            }
        }
    };

    NextendAnimationEditorController.prototype._buildAnimationChainLoop = function (animations, ratio) {

        delete animations[0].name;

        if (animations.length == 1) {
            var singleAnimation = animations[0],
                animation = $.extend({}, zero);
            animation.duration = singleAnimation.duration;
            animation.ease = singleAnimation.ease;
            if ((singleAnimation.rotationX == 360 || singleAnimation.rotationY == 360 || singleAnimation.rotationZ == 360) && singleAnimation.opacity == 1 && singleAnimation.x == 0 && singleAnimation.y == 0 && singleAnimation.z == 0 && singleAnimation.scaleX == 1 && singleAnimation.scaleY == 1 && singleAnimation.scaleZ == 1 && singleAnimation.skewX == 0) {
                return [
                    {
                        duration: animations[0].duration,
                        from: $.extend({}, zero),
                        to: animations[0]
                    }
                ];
            } else {
                animations.unshift(animation);
            }
        }
        var i = 0;
        var preparedAnimations = [
            {
                duration: animations[i].duration,
                to: animations[i]
            }
        ];
        i++;
        for (; i < animations.length; i++) {
            var animation = animations[i],
                duration = animation.duration;
            delete animation.duration;
            delete animation.name;

            var previousAnimation = $.extend({}, preparedAnimations[preparedAnimations.length - 1].to);
            delete previousAnimation.delay;
            delete previousAnimation.ease;

            //animation.x = parseFloat(previousAnimation.x) + animation.x * ratio;
            //animation.y = parseFloat(previousAnimation.y) + animation.y * ratio;
            //animation.z = parseFloat(previousAnimation.z) + animation.z * ratio;
            animation.x = animation.x * ratio;
            animation.y = animation.y * ratio;
            animation.z = animation.z * ratio;

            preparedAnimations.push({
                duration: duration,
                from: previousAnimation,
                to: animation
            });
        }

        preparedAnimations.push({
            duration: preparedAnimations[0].duration,
            from: $.extend({}, preparedAnimations[preparedAnimations.length - 1].to),
            to: $.extend({}, preparedAnimations[0].to)
        });
        preparedAnimations.shift();

        return preparedAnimations;
    };

    NextendAnimationEditorController.prototype.buildTimelineOut = function (timeline, element, animations, ratio) {

        var duration = 0,
            chain = this._buildAnimationChainOut(animations, ratio);

        var i = 0;
        if (chain.length > 0) {
            timeline.fromTo(element, chain[i].duration, chain[i].from, chain[i].to, duration);
            duration += chain[i].duration + chain[i].to.delay;
            i++;

            for (; i < chain.length; i++) {
                timeline.to(element, chain[i].duration, chain[i].to, duration);
                duration += chain[i].duration + chain[i].to.delay;
            }
        }

    };

    NextendAnimationEditorController.prototype._buildAnimationChainOut = function (animations, ratio) {
        var preparedAnimations = [
            {
                to: zero
            }
        ];
        for (var i = 0; i < animations.length; i++) {
            var animation = animations[i],
                duration = animation.duration;
            delete animation.duration;
            delete animation.name;

            var previousAnimation = $.extend({}, preparedAnimations[preparedAnimations.length - 1].to);
            delete previousAnimation.delay;
            delete previousAnimation.ease;

            //animation.x = parseFloat(previousAnimation.x) + animation.x * ratio;
            //animation.y = parseFloat(previousAnimation.y) + animation.y * ratio;
            //animation.z = parseFloat(previousAnimation.z) + animation.z * ratio;
            animation.x = animation.x * ratio;
            animation.y = animation.y * ratio;
            animation.z = animation.z * ratio;

            preparedAnimations.push({
                duration: duration,
                from: previousAnimation,
                to: animation
            });
        }
        preparedAnimations.shift();
        return preparedAnimations;
    };

    NextendAnimationEditorController.prototype.loadSpecialZero = function (specialZero) {
        this.editor.fields.specialZero.element.data('field').insideChange(specialZero);
        this.refreshSpecialZero(specialZero);
    };

    NextendAnimationEditorController.prototype.refreshSpecialZero = function (specialZero) {
        this.specialZero = parseInt(specialZero) ? 1 : 0;
        this.refreshTimeline();
    };

    NextendAnimationEditorController.prototype.loadRepeatCount = function (repeatCount) {
        this.editor.fields.repeatCount.element.data('field').insideChange(repeatCount);
        this.refreshRepeatCount(repeatCount);
    };

    NextendAnimationEditorController.prototype.refreshRepeatCount = function (repeatCount) {
        this.repeatCount = Math.max(0, parseInt(repeatCount));
    };

    NextendAnimationEditorController.prototype.loadRepeatStartDelay = function (repeatStartDelay) {
        this.editor.fields.repeatStartDelay.element.data('field').insideChange(repeatStartDelay * 1000);
        this.refreshRepeatStartDelay(repeatStartDelay);
    };

    NextendAnimationEditorController.prototype.refreshRepeatStartDelay = function (repeatStartDelay) {
        this.repeatStartDelay = Math.max(0, parseFloat(repeatStartDelay));
    };

    NextendAnimationEditorController.prototype.loadTransformOrigin = function (transformOrigin) {
        this.editor.fields.transformOrigin.element.data('field').insideChange(transformOrigin);
        this.refreshTransformOrigin(transformOrigin);
    };

    NextendAnimationEditorController.prototype.refreshTransformOrigin = function (transformOrigin) {

        this.transformOrigin = transformOrigin;

        NextendTween.set(this.preview.parent().get(0), {
            perspective: '1000px'
        });
        this.refreshTimeline();
    };

    NextendAnimationEditorController.prototype.loadPlayEvent = function (event) {
        this.editor.fields.playEvent.element.data('field').insideChange(event);
        this.refreshPlayEvent(event);
    };

    NextendAnimationEditorController.prototype.refreshPlayEvent = function (event) {

        this.playEvent = event;
    };

    NextendAnimationEditorController.prototype.loadPauseEvent = function (event) {
        this.editor.fields.pauseEvent.element.data('field').insideChange(event);
        this.refreshPauseEvent(event);
    };

    NextendAnimationEditorController.prototype.refreshPauseEvent = function (event) {
        this.pauseEvent = event;
    };

    NextendAnimationEditorController.prototype.loadStopEvent = function (event) {
        this.editor.fields.stopEvent.element.data('field').insideChange(event);
        this.refreshStopEvent(event);
    };

    NextendAnimationEditorController.prototype.refreshStopEvent = function (event) {
        this.stopEvent = event;
    };

    NextendAnimationEditorController.prototype.loadRepeatable = function (repeatable) {
        this.editor.fields.repeatable.element.data('field').insideChange(repeatable);
        this.refreshRepeatable(repeatable);
    };

    NextendAnimationEditorController.prototype.refreshRepeatable = function (repeatable) {
        this.repeatable = repeatable;
    };

    NextendAnimationEditorController.prototype.loadInstantOut = function (instantOut) {
        this.editor.fields.instantOut.element.data('field').insideChange(instantOut);
        this.refreshInstantOut(instantOut);
    };

    NextendAnimationEditorController.prototype.refreshInstantOut = function (instantOut) {
        this.instantOut = instantOut;
    };

    NextendAnimationEditorController.prototype.featureSpecialZero = function (enabled) {
        var row = this.editor.fields.specialZero.element.closest('tr');
        if (enabled) {
            row.removeClass('n2-hidden');
        } else {
            row.addClass('n2-hidden');
        }
    };

    NextendAnimationEditorController.prototype.featureRepeat = function (enabled) {
        var rows = this.editor.fields.repeatCount.element.closest('tr')
            .add(this.editor.fields.repeatStartDelay.element.closest('tr'));
        if (enabled) {
            rows.removeClass('n2-hidden');
        } else {
            rows.addClass('n2-hidden');
        }
    };

    NextendAnimationEditorController.prototype.featurePlayEvent = function (enabled) {
        var row = this.editor.fields.playEvent.element.closest('.n2-mixed-group');
        if (enabled) {
            row.removeClass('n2-hidden');
        } else {
            row.addClass('n2-hidden');
        }
    };

    NextendAnimationEditorController.prototype.featurePauseEvent = function (enabled) {
        var row = this.editor.fields.pauseEvent.element.closest('.n2-mixed-group');
        if (enabled) {
            row.removeClass('n2-hidden');
        } else {
            row.addClass('n2-hidden');
        }
    };

    NextendAnimationEditorController.prototype.featureStopEvent = function (enabled) {
        var row = this.editor.fields.stopEvent.element.closest('.n2-mixed-group');
        if (enabled) {
            row.removeClass('n2-hidden');
        } else {
            row.addClass('n2-hidden');
        }
    };

    NextendAnimationEditorController.prototype.featureRepeatable = function (enabled) {
        var row = this.editor.fields.repeatable.element.closest('.n2-mixed-group');
        if (enabled) {
            row.removeClass('n2-hidden');
        } else {
            row.addClass('n2-hidden');
        }
    };

    NextendAnimationEditorController.prototype.featureInstantOut = function (enabled) {
        var row = this.editor.fields.instantOut.element.closest('.n2-mixed-group');
        if (enabled) {
            row.removeClass('n2-hidden');
        } else {
            row.addClass('n2-hidden');
        }
    };

    scope.NextendAnimationEditorController = NextendAnimationEditorController;

    function NextendAnimationEditor() {
        NextendVisualEditor.prototype.constructor.apply(this, arguments);

        this.fields = {
            name: {
                element: $('#n2-animation-editorname'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeName, this)
                }
            },
            duration: {
                element: $('#n2-animation-editorduration'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeDuration, this)
                }
            },
            delay: {
                element: $('#n2-animation-editordelay'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeDelay, this)
                }
            },
            easing: {
                element: $('#n2-animation-editoreasing'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeEasing, this)
                }
            },
            opacity: {
                element: $('#n2-animation-editoropacity'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeOpacity, this)
                }
            },
            offset: {
                element: $('#n2-animation-editoroffset'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeOffset, this)
                }
            },
            rotate: {
                element: $('#n2-animation-editorrotate'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeRotate, this)
                }
            },
            scale: {
                element: $('#n2-animation-editorscale'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeScale, this)
                }
            },
            skew: {
                element: $('#n2-animation-editorskew'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeSkew, this)
                }
            },
            specialZero: {
                element: $('#n2-animation-editorspecial-zero'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeSpecialZero, this)
                }
            },
            repeatCount: {
                element: $('#n2-animation-editorrepeat-count'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeRepeatCount, this)
                }
            },
            repeatStartDelay: {
                element: $('#n2-animation-editorrepeat-start-delay'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeRepeatStartDelay, this)
                }
            },
            transformOrigin: {
                element: $('#n2-animation-editortransformorigin'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeTransformOrigin, this)
                }
            },
            playEvent: {
                element: $('#n2-animation-editorplay'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changePlayEvent, this)
                }
            },
            pauseEvent: {
                element: $('#n2-animation-editorpause'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changePauseEvent, this)
                }
            },
            stopEvent: {
                element: $('#n2-animation-editorstop'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeStopEvent, this)
                }
            },
            repeatable: {
                element: $('#n2-animation-editorrepeatable'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeRepeatable, this)
                }
            },
            instantOut: {
                element: $('#n2-animation-editorinstant-out'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeInstantOut, this)
                }
            }
        }
    }

    NextendAnimationEditor.prototype = Object.create(NextendVisualEditor.prototype);
    NextendAnimationEditor.prototype.constructor = NextendAnimationEditor;

    NextendAnimationEditor.prototype.load = function (values) {
        this._off();
        this.fields.name.element.data('field').insideChange(values.name);
        this.fields.duration.element.data('field').insideChange(values.duration * 1000);
        this.fields.delay.element.data('field').insideChange(values.delay * 1000);
        this.fields.easing.element.data('field').insideChange(values.ease);
        this.fields.opacity.element.data('field').insideChange(values.opacity * 100);

        this.fields.offset.element.data('field').insideChange(values.x + '|*|' + values.y + '|*|' + values.z);
        this.fields.rotate.element.data('field').insideChange(values.rotationX + '|*|' + values.rotationY + '|*|' + values.rotationZ);
        this.fields.scale.element.data('field').insideChange(values.scaleX * 100 + '|*|' + values.scaleY * 100 + '|*|' + values.scaleZ * 100);
        this.fields.skew.element.data('field').insideChange(values.skewX);
        this.fields.specialZero.element.data('field').insideChange(nextend.animationManager.controller.specialZero);
        this.fields.repeatCount.element.data('field').insideChange(nextend.animationManager.controller.repeatCount);
        this.fields.repeatStartDelay.element.data('field').insideChange(nextend.animationManager.controller.repeatStartDelay * 1000);
        this.fields.transformOrigin.element.data('field').insideChange(nextend.animationManager.controller.transformOrigin);


        this.fields.playEvent.element.data('field').insideChange(nextend.animationManager.controller.playEvent);
        this.fields.pauseEvent.element.data('field').insideChange(nextend.animationManager.controller.pauseEvent);
        this.fields.stopEvent.element.data('field').insideChange(nextend.animationManager.controller.stopEvent);
        this.fields.repeatable.element.data('field').insideChange(nextend.animationManager.controller.repeatable);
        this.fields.instantOut.element.data('field').insideChange(nextend.animationManager.controller.instantOut);

        this._on();
    };

    NextendAnimationEditor.prototype.changeName = function () {
        this.trigger('name', this.fields.name.element.val());
        this.$.trigger('nameChanged', this.fields.name.element.val());
    };

    NextendAnimationEditor.prototype.changeDuration = function () {
        this.trigger('duration', this.fields.duration.element.val() / 1000);
    };

    NextendAnimationEditor.prototype.changeDelay = function () {
        this.trigger('delay', this.fields.delay.element.val() / 1000);
    };

    NextendAnimationEditor.prototype.changeEasing = function () {
        this.trigger('ease', this.fields.easing.element.val());
    };

    NextendAnimationEditor.prototype.changeOpacity = function () {
        this.trigger('opacity', this.fields.opacity.element.val() / 100);
    };

    NextendAnimationEditor.prototype.changeOffset = function () {
        var offset = this.fields.offset.element.val().split('|*|');
        this.trigger('x', offset[0]);
        this.trigger('y', offset[1]);
        this.trigger('z', offset[2]);
    };

    NextendAnimationEditor.prototype.changeRotate = function () {
        var rotate = this.fields.rotate.element.val().split('|*|');
        this.trigger('rotationX', rotate[0]);
        this.trigger('rotationY', rotate[1]);
        this.trigger('rotationZ', rotate[2]);
    };

    NextendAnimationEditor.prototype.changeScale = function () {
        var scale = this.fields.scale.element.val().split('|*|');
        this.trigger('scaleX', scale[0] / 100);
        this.trigger('scaleY', scale[1] / 100);
        this.trigger('scaleZ', scale[2] / 100);
    };

    NextendAnimationEditor.prototype.changeSkew = function () {
        this.trigger('skewX', this.fields.skew.element.val());
    };

    NextendAnimationEditor.prototype.changeTransformOrigin = function () {
        nextend.animationManager.controller.refreshTransformOrigin(this.fields.transformOrigin.element.val());
    };

    NextendAnimationEditor.prototype.changeSpecialZero = function () {
        nextend.animationManager.controller.refreshSpecialZero(this.fields.specialZero.element.val());
    };

    NextendAnimationEditor.prototype.changeRepeatCount = function () {
        nextend.animationManager.controller.refreshRepeatCount(this.fields.repeatCount.element.val());
    };

    NextendAnimationEditor.prototype.changeRepeatStartDelay = function () {
        nextend.animationManager.controller.refreshRepeatStartDelay(this.fields.repeatStartDelay.element.val() / 1000);
    };

    NextendAnimationEditor.prototype.changePlayEvent = function () {
        nextend.animationManager.controller.refreshPlayEvent(this.fields.playEvent.element.val());
    };

    NextendAnimationEditor.prototype.changePauseEvent = function () {
        nextend.animationManager.controller.refreshPauseEvent(this.fields.pauseEvent.element.val());
    };

    NextendAnimationEditor.prototype.changeStopEvent = function () {
        nextend.animationManager.controller.refreshStopEvent(this.fields.stopEvent.element.val());
    };

    NextendAnimationEditor.prototype.changeRepeatable = function () {
        nextend.animationManager.controller.refreshRepeatable(this.fields.repeatable.element.val());
    };

    NextendAnimationEditor.prototype.changeInstantOut = function () {
        nextend.animationManager.controller.refreshInstantOut(this.fields.instantOut.element.val());
    };

    scope.NextendAnimationEditor = NextendAnimationEditor;

})
(n2, window);

(function ($, scope, undefined) {

    var cache = {};

    function NextendBrowse(url, uploadAllowed) {
        this.url = url;
        this.uploadAllowed = parseInt(uploadAllowed);
        this.currentPath = $.jStorage.get('browsePath', '');
        var timeout = null;
        this.node = $('<div class="n2-browse-container"/>').on('dragover', function (e) {
            if (timeout !== null) {
                clearTimeout(timeout);
                timeout = null;
            } else {
                $(e.currentTarget).addClass('n2-drag-over');
            }
            timeout = setTimeout(function () {
                $(e.currentTarget).removeClass('n2-drag-over');
                timeout = null;
            }, 400);

        });
        nextend.browse = this;
    };

    NextendBrowse.prototype.clear = function () {
        if (this.uploadAllowed) {
            this.node.find('#n2-browse-upload').fileupload('destroy');
        }
        this.node.empty();
    };

    NextendBrowse.prototype.getNode = function (mode, callback) {
        this.clear();
        this.mode = mode;
        if (mode == 'multiple') {
            this.selected = [];
        }
        this.callback = callback;
        this._loadPath(this.getCurrentFolder(), $.proxy(this._renderBoxes, this));
        return this.node;
    };

    NextendBrowse.prototype._renderBoxes = function (data) {
        this.clear();

        if (this.uploadAllowed) {
            this.node.append($('<div class="n2-browse-box n2-browse-upload"><div class="n2-h4">' + n2_('Drop files anywhere to upload or') + ' <a class="n2-button n2-button-medium n2-button-grey n2-uc n2-h4" href="#">' + n2_('Select files') + '</a></div><input id="n2-browse-upload" type="file" name="image" multiple></div>'));

            this.node.find('#n2-browse-upload').fileupload({
                url: NextendAjaxHelper.makeAjaxUrl(this.url, {
                    nextendaction: 'upload'
                }),
                sequentialUploads: true,
                dropZone: this.node,
                pasteZone: false,
                dataType: 'json',
                paramName: 'image',
                add: $.proxy(function (e, data) {

                    var box = $('<div class="n2-browse-box n2-browse-image"><div class="n2-button n2-button-small n2-button-blue"><i class="n2-i n2-it n2-i-tick"></i></div><div class="n2-browse-title">0%</div></div>');

                    var images = this.node.find('.n2-browse-image');
                    if (images.length > 0) {
                        box.insertBefore(images.eq(0));
                    } else {
                        box.appendTo(this.node);
                    }
                    data.box = box;


                    data.formData = {path: this.currentPath};
                    data.submit();
                }, this),
                progress: function (e, data) {
                    var progress = parseInt(data.loaded / data.total * 100, 10);
                    data.box.find('.n2-browse-title').html(progress + '%');
                },
                done: $.proxy(function (e, data) {
                    var response = data.result;

                    if (response.data && response.data.name) {
                        cache[response.data.path].data.files[response.data.name] = response.data.url;

                        data.box.css('background-image', 'url(' + encodeURI(nextend.imageHelper.fixed(response.data.url)) + ')')
                            .on('click', $.proxy(this.clickImage, this, response.data.url))
                            .find('.n2-browse-title').html(response.data.name);
                        if (this.mode == 'multiple') {
                            this.selected.push(response.data.url);
                            data.box.addClass('n2-active');
                        }
                    } else {
                        data.box.destroy();
                    }

                    NextendAjaxHelper.notification(response);

                }, this),
                fail: $.proxy(function (e, data) {
                    data.box.remove();
                    NextendAjaxHelper.notification(data.jqXHR.responseJSON);
                }, this)
            });

            $.jStorage.set('browsePath', this.getCurrentFolder());
        }

        if (data.path != '') {
            this.node.append($('<div class="n2-browse-box n2-browse-directory"><i class="n2-i n2-it n2-i-up"></i></div>').on('click', $.proxy(function (directory) {
                this._loadPath(directory, $.proxy(this._renderBoxes, this))
            }, this, data.path + '/..')));
        }
        for (var k in data.directories) {
            if (data.directories.hasOwnProperty(k)) {
                this.node.append($('<div class="n2-browse-box n2-browse-directory"><i class="n2-i n2-it n2-i-folder"></i><div class="n2-browse-title">' + k + '</div></div>').on('click', $.proxy(function (directory) {
                    this._loadPath(directory, $.proxy(this._renderBoxes, this))
                }, this, data.directories[k])));
            }
        }
        for (var k in data.files) {
            if (data.files.hasOwnProperty(k)) {
                var box = $('<div class="n2-browse-box n2-browse-image"><div class="n2-button n2-button-small n2-button-blue"><i class="n2-i n2-it n2-i-tick"></i></div><div class="n2-browse-title">' + k + '</div></div>')
                    .css('background-image', 'url(' + encodeURI(nextend.imageHelper.fixed(data.files[k])) + ')')
                    .on('click', $.proxy(this.clickImage, this, data.files[k]));
                this.node.append(box);

                if (this.mode == 'multiple') {
                    if ($.inArray(data.files[k], this.selected) != -1) {
                        box.addClass('n2-active');
                    }
                }
            }
        }
    };


    NextendBrowse.prototype._loadPath = function (path, callback) {
        if (typeof cache[path] === 'undefined') {
            cache[path] = NextendAjaxHelper.ajax({
                type: "POST",
                url: NextendAjaxHelper.makeAjaxUrl(this.url),
                data: {
                    path: path
                },
                dataType: 'json'
            });
        }
        $.when(cache[path]).done($.proxy(function (response) {
            this.currentPath = response.data.path;
            cache[response.data.path] = response;
            cache[path] = response;
            callback(response.data);
        }, this));

    };

    NextendBrowse.prototype.clickImage = function (image, e) {
        if (this.mode == 'single') {
            this.callback(image);
        } else if (this.mode == 'multiple') {
            var i = $.inArray(image, this.selected);
            if (i == -1) {
                $(e.currentTarget).addClass('n2-active');
                this.selected.push(image);
            } else {
                $(e.currentTarget).removeClass('n2-active');
                this.selected.splice(i, 1);
            }
        }
    };

    NextendBrowse.prototype.getSelected = function () {
        return this.selected;
    };

    NextendBrowse.prototype.getCurrentFolder = function () {
        return this.currentPath;
    };


    scope.NextendBrowse = NextendBrowse;

})(n2, window);
;
(function ($, scope) {

    function NextendFontManager() {
        NextendVisualManagerSetsAndMore.prototype.constructor.apply(this, arguments);
        this.setFontSize(16);
    };

    NextendFontManager.prototype = Object.create(NextendVisualManagerSetsAndMore.prototype);
    NextendFontManager.prototype.constructor = NextendFontManager;

    NextendFontManager.prototype.loadDefaults = function () {
        NextendVisualManagerSetsAndMore.prototype.loadDefaults.apply(this, arguments);
        this.type = 'font';
        this.labels = {
            visual: n2_('font'),
            visuals: n2_('fonts')
        };

        this.styleClassName = '';
        this.styleClassName2 = '';
    };

    NextendFontManager.prototype.initController = function () {
        return new NextendFontEditorController(this.parameters.renderer.modes, this.parameters.defaultFamily);
    };

    NextendFontManager.prototype.addVisualUsage = function (mode, fontValue, pre) {
        /**
         * if fontValue is numeric, then it is a linked font!
         */
        if (parseInt(fontValue) > 0) {
            return this._addLinkedFont(mode, fontValue, pre);
        } else {
            try {
                this._renderStaticFont(mode, fontValue, pre);
                return true;
            } catch (e) {
                // Empty font
                return false;
            }
        }
    };

    NextendFontManager.prototype._addLinkedFont = function (mode, fontId, pre) {
        var used = this.parameters.renderer.usedFonts,
            d = $.Deferred();
        $.when(this.getVisual(fontId))
            .done($.proxy(function (font) {
                if (font.id > 0) {
                    if (typeof pre === 'undefined') {
                        if (typeof used[font.id] === 'undefined') {
                            used[font.id] = [mode];
                            this.renderLinkedFont(mode, font, pre);
                        } else if ($.inArray(mode, used[font.id]) == -1) {
                            used[font.id].push(mode);
                            this.renderLinkedFont(mode, font, pre);
                        }
                    } else {
                        this.renderLinkedFont(mode, font, pre);
                    }
                    d.resolve(true);
                } else {
                    d.resolve(false);
                }
            }, this))
            .fail(function () {
                d.resolve(false);
            });
        return d;
    };

    NextendFontManager.prototype.renderLinkedFont = function (mode, font, pre) {
        if (typeof pre === 'undefined') {
            pre = this.parameters.renderer.pre;
        }
        nextend.css.add(this.renderer.getCSS(mode, pre, '.' + this.getClass(font.id, mode), font.value, {
            deleteRule: true
        }));

    };

    NextendFontManager.prototype._renderStaticFont = function (mode, font, pre) {
        if (typeof pre === 'undefined') {
            pre = this.parameters.renderer.pre;
        }
        nextend.css.add(this.renderer.getCSS(mode, pre, '.' + this.getClass(font, mode), JSON.parse(Base64.decode(font)).data, {}));
    };

    /**
     * We should never use this method as we do not track if a font used with the same mode multiple times.
     * So there is no sync and if we delete a used font, other usages might fail to update correctly in
     * special circumstances.
     * @param mode
     * @param fontId
     */
    NextendFontManager.prototype.removeUsedFont = function (mode, fontId) {
        var used = this.parameters.renderer.usedFonts;
        if (typeof used[fontId] !== 'undefined') {
            var index = $.inArray(mode, used[fontId]);
            if (index > -1) {
                used[fontId].splice(index, 1);
            }
        }
    };

    NextendFontManager.prototype.getClass = function (font, mode) {
        if (parseInt(font) > 0) {
            return 'n2-font-' + font + '-' + mode;
        } else if (font == '') {
            // Empty font
            return '';
        }
        // Font might by empty with this class too, but we do not care as nothing wrong if it has an extra class
        // We could do try catch to JSON.parse(Base64.decode(font)), but it is wasting resource
        return 'n2-font-' + md5(font) + '-' + mode;
    };

    NextendFontManager.prototype.createVisual = function (visual, set) {
        return new NextendFont(visual, set, this);
    };

    NextendFontManager.prototype.setConnectedStyle = function (styleId) {
        this.styleClassName = $('#' + styleId).data('field').renderStyle();
    };

    NextendFontManager.prototype.setConnectedStyle2 = function (styleId) {
        this.styleClassName2 = $('#' + styleId).data('field').renderStyle();
    };

    NextendFontManager.prototype.setFontSize = function (fontSize) {
        this.controller.setFontSize(fontSize)
    };

    scope.NextendFontManager = NextendFontManager;


    function NextendFont() {
        NextendVisualWithSetRow.prototype.constructor.apply(this, arguments);
    };

    NextendFont.prototype = Object.create(NextendVisualWithSetRow.prototype);
    NextendFont.prototype.constructor = NextendFont;

    NextendFont.prototype.removeRules = function () {
        var used = this.isUsed();
        if (used) {
            for (var i = 0; i < used.length; i++) {
                this.visualManager.removeRules(used[i], this);
            }
        }
    };

    NextendFont.prototype.render = function () {
        var used = this.isUsed();
        if (used) {
            for (var i = 0; i < used.length; i++) {
                this.visualManager.renderLinkedFont(used[i], this);
            }
        }
    };

    NextendFont.prototype.isUsed = function () {
        if (typeof this.visualManager.parameters.renderer.usedFonts[this.id] !== 'undefined') {
            return this.visualManager.parameters.renderer.usedFonts[this.id];
        }
        return false;
    };

    scope.NextendFont = NextendFont;

})(n2, window);

;
(function ($, scope) {

    function NextendFontEditorController(previewModesList, defaultFamily) {
        this.defaultFamily = defaultFamily;
        NextendVisualEditorController.prototype.constructor.apply(this, arguments);

        this.preview = $('#n2-font-editor-preview');

        this.initBackgroundColor();
    }

    NextendFontEditorController.prototype = Object.create(NextendVisualEditorController.prototype);
    NextendFontEditorController.prototype.constructor = NextendFontEditorController;

    NextendFontEditorController.prototype.loadDefaults = function () {
        NextendVisualEditorController.prototype.loadDefaults.call(this);
        this.type = 'font';
        this.preview = null;
        this.fontSize = 14;
    };

    NextendFontEditorController.prototype.initPreviewModes = function () {

        this.previewModes = {
            1: [this.previewModesList['simple']],
            2: [this.previewModesList['link'], this.previewModesList['hover'], this.previewModesList['accordionslidetitle']],
            3: [this.previewModesList['paragraph']]
        };
    };

    NextendFontEditorController.prototype.initRenderer = function () {
        return new NextendFontRenderer(this);
    };

    NextendFontEditorController.prototype.initEditor = function () {
        return new NextendFontEditor();
    };

    NextendFontEditorController.prototype._load = function (visual, tabs, parameters) {
        if (visual.length) {
            visual[0] = $.extend({}, this.getEmptyFont(), visual[0]);
        }

        NextendVisualEditorController.prototype._load.call(this, visual, tabs, parameters);
    };

    NextendFontEditorController.prototype.getEmptyFont = function () {
        return {
            color: "000000ff",
            size: "14||px",
            tshadow: "0|*|0|*|0|*|000000ff",
            afont: this.defaultFamily,
            lineheight: "1.5",
            bold: 0,
            italic: 0,
            underline: 0,
            align: "left",
            letterspacing: "normal",
            wordspacing: "normal",
            texttransform: "none",
            extra: ""
        };
    };

    NextendFontEditorController.prototype.getCleanVisual = function () {
        return {
            extra: ''
        };
    };

    NextendFontEditorController.prototype.getEmptyVisual = function () {
        return [this.getEmptyFont()];
    };

    NextendFontEditorController.prototype.setFontSize = function (fontSize) {
        this.fontSize = fontSize;
        this.preview.css('fontSize', fontSize);
    };

    NextendFontEditorController.prototype.initBackgroundColor = function () {

        new NextendElementText("n2-font-editor-background-color");
        new NextendElementColor("n2-font-editor-background-color", 0);

        var box = this.lightbox.find('.n2-editor-preview-box');
        $('#n2-font-editor-background-color').on('nextendChange', function () {
            box.css('background', '#' + $(this).val());
        });
    };

    NextendFontEditorController.prototype._renderPreview = function () {
        NextendVisualEditorController.prototype._renderPreview.call(this);
        this.addStyle(this.renderer.getCSS(this.currentPreviewMode, '', '.' + this.getPreviewCssClass(), this.currentVisual, {
            activeTab: this.currentTabIndex
        }));
    };

    NextendFontEditorController.prototype.setPreview = function (mode) {

        var html = '';
        if (typeof this.localModePreview[mode] !== 'undefined') {
            html = this.localModePreview[mode];
        } else {
            html = this.previewModesList[mode].preview;
        }

        var fontClassName = this.getPreviewCssClass(),
            styleClassName = nextend.fontManager.styleClassName,
            styleClassName2 = nextend.fontManager.styleClassName2;

        html = html.replace(/\{([^]*?)\}/g, function (match, script) {
            return eval(script);
        });

        this.preview.html(html);
    };

    NextendFontEditorController.prototype.getPreviewCssClass = function () {
        return 'n2-' + this.type + '-editor-preview';
    };

    scope.NextendFontEditorController = NextendFontEditorController;

    function NextendFontEditor() {

        NextendVisualEditor.prototype.constructor.apply(this, arguments);

        this.fields = {
            family: {
                element: $('#n2-font-editorfamily'),
                events: {
                    'nextendChange.n2-editor': $.proxy(this.changeFamily, this)
                }
            },
            color: {
                element: $('#n2-font-editorcolor'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeColor, this)
                }
            },
            size: {
                element: $('#n2-font-editorsize'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeSize, this)
                }
            },
            lineHeight: {
                element: $('#n2-font-editorlineheight'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeLineHeight, this)
                }
            },
            decoration: {
                element: $('#n2-font-editordecoration'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeDecoration, this)
                }
            },
            align: {
                element: $('#n2-font-editortextalign'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeAlign, this)
                }
            },
            shadow: {
                element: $('#n2-font-editortshadow'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeShadow, this)
                }
            },
            letterSpacing: {
                element: $('#n2-font-editorletterspacing'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeLetterSpacing, this)
                }
            },
            wordSpacing: {
                element: $('#n2-font-editorwordspacing'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeWordSpacing, this)
                }
            },
            textTransform: {
                element: $('#n2-font-editortexttransform'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeTextTransform, this)
                }
            },
            css: {
                element: $('#n2-font-editorextracss'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeCSS, this)
                }
            }
        }
    };

    NextendFontEditor.prototype = Object.create(NextendVisualEditor.prototype);
    NextendFontEditor.prototype.constructor = NextendFontEditor;

    NextendFontEditor.prototype.load = function (values) {
        this._off();
        var family = values.afont.split('||'); // split for a while for compatibility
        this.fields.family.element.data('field').insideChange(family[0]);

        this.fields.color.element.data('field').insideChange(values.color);
        this.fields.size.element.data('field').insideChange(values.size
                .split('||')
                .join('|*|')
        );

        this.fields.lineHeight.element.data('field').insideChange(values.lineheight);
        this.fields.decoration.element.data('field').insideChange([
            values.bold == 1 ? 'bold' : '',
            values.italic == 1 ? 'italic' : '',
            values.underline == 1 ? 'underline' : ''
        ].join('||'));

        this.fields.align.element.data('field').insideChange(values.align);
        this.fields.shadow.element.data('field').insideChange(values.tshadow.replace(/\|\|px/g, ''));
        this.fields.letterSpacing.element.data('field').insideChange(values.letterspacing);
        this.fields.wordSpacing.element.data('field').insideChange(values.wordspacing);
        this.fields.textTransform.element.data('field').insideChange(values.texttransform);
        this.fields.css.element.data('field').insideChange(values.extra);

        this._on();
    };

    NextendFontEditor.prototype.changeFamily = function () {
        this.trigger('afont', this.fields.family.element.val());
    };

    NextendFontEditor.prototype.changeColor = function () {
        this.trigger('color', this.fields.color.element.val());
    };

    NextendFontEditor.prototype.changeSize = function () {
        this.trigger('size', this.fields.size.element.val().replace('|*|', '||'));
    };

    NextendFontEditor.prototype.changeLineHeight = function () {
        this.trigger('lineheight', this.fields.lineHeight.element.val());
    };

    NextendFontEditor.prototype.changeDecoration = function () {
        var value = this.fields.decoration.element.val();

        var bold = 0;
        if (value.indexOf('bold') != -1) {
            bold = 1;
        }
        this.trigger('bold', bold);

        var italic = 0;
        if (value.indexOf('italic') != -1) {
            italic = 1;
        }
        this.trigger('italic', italic);

        var underline = 0;
        if (value.indexOf('underline') != -1) {
            underline = 1;
        }
        this.trigger('underline', underline);
    };

    NextendFontEditor.prototype.changeAlign = function () {
        this.trigger('align', this.fields.align.element.val());
    };

    NextendFontEditor.prototype.changeShadow = function () {
        this.trigger('tshadow', this.fields.shadow.element.val());
    };

    NextendFontEditor.prototype.changeLetterSpacing = function () {
        this.trigger('letterspacing', this.fields.letterSpacing.element.val());
    };

    NextendFontEditor.prototype.changeWordSpacing = function () {
        this.trigger('wordspacing', this.fields.wordSpacing.element.val());
    };

    NextendFontEditor.prototype.changeTextTransform = function () {
        this.trigger('texttransform', this.fields.textTransform.element.val());
    };

    NextendFontEditor.prototype.changeCSS = function () {
        this.trigger('extra', this.fields.css.element.val());
    };

    scope.NextendFontEditor = NextendFontEditor;


    function NextendFontRenderer() {
        NextendVisualRenderer.prototype.constructor.apply(this, arguments);
    }

    NextendFontRenderer.prototype = Object.create(NextendVisualRenderer.prototype);
    NextendFontRenderer.prototype.constructor = NextendFontRenderer;


    NextendFontRenderer.prototype.getCSS = function (modeKey, pre, selector, visualTabs, parameters) {
        visualTabs = $.extend([], visualTabs);
        visualTabs[0] = $.extend(this.editorController.getEmptyFont(), visualTabs[0]);
        if (this.editorController.previewModesList[modeKey].renderOptions.combined) {
            for (var i = 1; i < visualTabs.length; i++) {
                visualTabs[i] = $.extend({}, visualTabs[i - 1], visualTabs[i]);
                if (visualTabs[i].size == visualTabs[0].size) {
                    visualTabs[i].size = '100||%';
                }
            }
        }
        return NextendVisualRenderer.prototype.getCSS.call(this, modeKey, pre, selector, visualTabs, parameters);
    };

    NextendFontRenderer.prototype.makeStylecolor = function (value, target) {
        target.color = '#' + value.substr(0, 6) + ";\ncolor: " + N2Color.hex2rgbaCSS(value);
    };

    NextendFontRenderer.prototype.makeStylesize = function (value, target) {
        var fontSize = value.split('||');
        if (fontSize[1] == 'px') {
            target.fontSize = (fontSize[0] / this.editorController.fontSize * 100) + '%';
        } else {
            target.fontSize = value.replace('||', '');
        }
    };

    NextendFontRenderer.prototype.makeStyletshadow = function (value, target) {
        var ts = value.split('|*|');
        if (ts[0] == '0' && ts[1] == '0' && ts[2] == '0') {
            target.textShadow = 'none';
        } else {
            target.textShadow = ts[0] + 'px ' + ts[1] + 'px ' + ts[2] + 'px ' + N2Color.hex2rgbaCSS(ts[3]);
        }
    };

    NextendFontRenderer.prototype.makeStyleafont = function (value, target) {
        var families = value.split(',');
        for (var i = 0; i < families.length; i++) {
            families[i] = this.getFamily(families[i]
                .replace(/^\s+|\s+$/gm, '')
                .replace(/"|'/gm, ''));
        }
        target.fontFamily = families.join(',');
    };
    NextendFontRenderer.prototype.getFamily = function (family) {
        $(window).trigger('n2Family', [family]);
        return "'" + family + "'";
    };

    NextendFontRenderer.prototype.makeStylelineheight = function (value, target) {

        target.lineHeight = value;
    };

    NextendFontRenderer.prototype.makeStylebold = function (value, target) {
        if (value == 1) {
            target.fontWeight = 'bold';
        } else {
            target.fontWeight = 'normal';
        }
    };

    NextendFontRenderer.prototype.makeStyleitalic = function (value, target) {
        if (value == 1) {
            target.fontStyle = 'italic';
        } else {
            target.fontStyle = 'normal';
        }
    };

    NextendFontRenderer.prototype.makeStyleunderline = function (value, target) {
        if (value == 1) {
            target.textDecoration = 'underline';
        } else {
            target.textDecoration = 'none';
        }
    };

    NextendFontRenderer.prototype.makeStylealign = function (value, target) {

        target.textAlign = value;
    };

    NextendFontRenderer.prototype.makeStyleletterspacing = function (value, target) {
        target.letterSpacing = value;
    };

    NextendFontRenderer.prototype.makeStylewordspacing = function (value, target) {
        target.wordSpacing = value;
    };

    NextendFontRenderer.prototype.makeStyletexttransform = function (value, target) {
        target.textTransform = value;
    };

    NextendFontRenderer.prototype.makeStyleextra = function (value, target) {

        target.raw = value;
    };

    scope.NextendFontRenderer = NextendFontRenderer;

})
(n2, window);

;
(function ($, scope) {

    function NextendImageManager() {
        this.referenceKeys = {};
        NextendVisualManagerCore.prototype.constructor.apply(this, arguments);
    };

    NextendImageManager.prototype = Object.create(NextendVisualManagerCore.prototype);
    NextendImageManager.prototype.constructor = NextendImageManager;

    NextendImageManager.prototype.loadDefaults = function () {
        NextendVisualManagerCore.prototype.loadDefaults.apply(this, arguments);
        this.type = 'image';
        this.labels = {
            visual: n2_('image'),
            visuals: n2_('images')
        };

        this.fontClassName = '';
    };


    NextendImageManager.prototype.initController = function () {
        return new NextendImageEditorController();
    };

    NextendImageManager.prototype.createVisual = function (visual) {
        return new NextendImage(visual, this);
    };

    NextendImageManager.prototype.firstLoadVisuals = function (visuals) {
        for (var i = 0; i < visuals.length; i++) {
            this.referenceKeys[visuals[i].hash] = this.visuals[visuals[i].id] = this.createVisual(visuals[i]);
        }
    };

    NextendImageManager.prototype.getVisual = function (image) {
        if (image == '') {
            nextend.notificationCenter.error('The image is empty', {
                timeout: 3
            });
        } else {
            var referenceKey = md5(image);
            if (typeof this.referenceKeys[referenceKey] !== 'undefined') {
                return this.referenceKeys[referenceKey];
            } else if (typeof this.visualLoadDeferreds[referenceKey] !== 'undefined') {
                return this.visualLoadDeferreds[referenceKey];
            } else {
                var deferred = $.Deferred();
                this.visualLoadDeferreds[referenceKey] = deferred;
                this._loadVisualFromServer(image)
                    .done($.proxy(function () {
                        deferred.resolve(this.referenceKeys[referenceKey]);
                        delete this.visualLoadDeferreds[referenceKey];
                    }, this))
                    .fail($.proxy(function () {
                        // This visual is Empty!!!
                        deferred.resolve({
                            id: -1,
                            name: n2_('Empty')
                        });
                        delete this.visualLoadDeferreds[referenceKey];
                    }, this));
                return deferred;
            }
        }
    };

    NextendImageManager.prototype._loadVisualFromServer = function (image) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'loadVisualForImage'
            }),
            data: {
                image: image
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                var visual = response.data.visual;
                this.referenceKeys[visual.hash] = this.visuals[visual.id] = this.createVisual(visual);
            }, this));
    };

    NextendImageManager.prototype.isVisualData = function (data) {
        return data != '';
    };

    NextendImageManager.prototype.setVisual = function (e) {
        e.preventDefault();
        if (this.controller.isChanged) {
            this.saveActiveVisual(this.activeVisual.name)
                .done($.proxy(function (response) {
                    $(window).trigger(response.data.visual.hash, this.activeVisual.value);
                    this.hide(e);
                }, this));
        } else {
            this.hide(e);
        }
    };

    NextendImageManager.prototype.getBase64 = function () {

        return Base64.encode(JSON.stringify(this.controller.get('set')));
    };

    scope.NextendImageManager = NextendImageManager;

    function NextendImage() {
        NextendVisualCore.prototype.constructor.apply(this, arguments);
    };

    NextendImage.prototype = Object.create(NextendVisualCore.prototype);
    NextendImage.prototype.constructor = NextendImage;

    NextendImage.prototype.setValue = function (value, render) {
        this.base64 = value;
        this.value = JSON.parse(Base64.decode(value));
    };

    NextendImage.prototype.activate = function (e) {
        if (typeof e !== 'undefined') {
            e.preventDefault();
        }
        this.visualManager.changeActiveVisual(this);
        this.visualManager.controller.load(this, false, this.visualManager.showParameters);
    };

    scope.NextendImage = NextendImage;

})(n2, window);

;
(function ($, scope) {

    function NextendImageEditorController() {
        NextendVisualEditorControllerWithEditor.prototype.constructor.apply(this, arguments);
    };

    NextendImageEditorController.prototype = Object.create(NextendVisualEditorControllerWithEditor.prototype);
    NextendImageEditorController.prototype.constructor = NextendImageEditorController;

    NextendImageEditorController.prototype.loadDefaults = function () {
        NextendVisualEditorControllerWithEditor.prototype.loadDefaults.call(this);
        this.type = 'image';
        this.currentImage = '';
    };

    NextendImageEditorController.prototype.get = function (type) {
        return this.currentVisual;
    };

    NextendImageEditorController.prototype.getEmptyVisual = function () {
        return {
            desktop: {
                size: '0|*|0'
            },
            tablet: {
                image: '',
                size: '0|*|0'
            },
            mobile: {
                image: '',
                size: '0|*|0'
            }
        };
    };

    NextendImageEditorController.prototype.initEditor = function () {
        return new NextendImageEditor();
    };

    NextendImageEditorController.prototype._load = function (visual, tabs, parameters) {
        this.currentImage = visual.visual.image;
        NextendVisualEditorControllerWithEditor.prototype._load.call(this, visual.value, tabs, parameters);
    };

    NextendImageEditorController.prototype.loadToEditor = function () {
        this.editor.load(this.currentImage, this.currentVisual);
    };

    NextendImageEditorController.prototype.propertyChanged = function (e, device, property, value) {
        this.isChanged = true;
        this.currentVisual[device][property] = value;
    };

    scope.NextendImageEditorController = NextendImageEditorController;

    function NextendImageEditor() {
        this.previews = null;
        this.desktopImage = '';

        NextendVisualEditor.prototype.constructor.apply(this, arguments);

        this.fields = {
            'desktop-size': {
                element: $('#n2-image-editordesktop-size'),
                events: {
                    'nextendChange.n2-editor': $.proxy(this.changeSize, this, 'desktop')
                }
            },
            'tablet-image': {
                element: $('#n2-image-editortablet-image'),
                events: {
                    'nextendChange.n2-editor': $.proxy(this.changeImage, this, 'tablet')
                }
            },
            'tablet-size': {
                element: $('#n2-image-editortablet-size'),
                events: {
                    'nextendChange.n2-editor': $.proxy(this.changeSize, this, 'tablet')
                }
            },
            'mobile-image': {
                element: $('#n2-image-editormobile-image'),
                events: {
                    'nextendChange.n2-editor': $.proxy(this.changeImage, this, 'mobile')
                }
            },
            'mobile-size': {
                element: $('#n2-image-editormobile-size'),
                events: {
                    'nextendChange.n2-editor': $.proxy(this.changeSize, this, 'mobile')
                }
            }
        }

        this.previews = {
            desktop: $('#n2-image-editordesktop-preview'),
            tablet: $('#n2-image-editortablet-preview'),
            mobile: $('#n2-image-editormobile-preview')
        };

        var generateTablet = $(this.buttonGenerate())
            .on('click', $.proxy(this.generateImage, this, 'tablet'))
            .insertAfter(this.fields['tablet-image'].element.parent());

        var generateMobile = $(this.buttonGenerate())
            .on('click', $.proxy(this.generateImage, this, 'mobile'))
            .insertAfter(this.fields['mobile-image'].element.parent());
    };

    NextendImageEditor.prototype = Object.create(NextendVisualEditor.prototype);
    NextendImageEditor.prototype.constructor = NextendImageEditor;

    NextendImageEditor.prototype.load = function (image, values) {
        this._off();
        for (var k in this.fields) {
            var keys = k.split('-');
            this.fields[k].element.data('field').insideChange(values[keys[0]][keys[1]]);
        }
        this.desktopImage = image;
        this.makePreview('desktop', image);

        if (values.desktop.size == '0|*|0') {
            this.getImageSize(image)
                .done($.proxy(function (width, height) {
                    this.fields['desktop-size'].element.data('field').insideChange(width + '|*|' + height);
                }, this));
        }

        for (var k in values) {
            if (typeof values[k].image != 'undefined') {
                this.makePreview(k, values[k].image);
            }
        }
        this._on();
    };

    NextendImageEditor.prototype.changeImage = function (device, e, field) {
        var image = field.element.val();
        if (this.makePreview(device, image)) {
            this.getImageSize(image)
                .done($.proxy(function (width, height) {
                    this.fields[device + '-size'].element.data('field').insideChange(width + '|*|' + height);
                }, this));
        } else {
            this.fields[device + '-size'].element.data('field').insideChange('0|*|0');
        }

        this.trigger(device, 'image', image);
    };

    NextendImageEditor.prototype.changeSize = function (device, e, field) {
        this.trigger(device, 'size', field.element.val());
    };

    NextendImageEditor.prototype.makePreview = function (device, image) {
        if (image) {
            this.previews[device].html('<img style="max-width:100%; max-height: 300px;" src="' + nextend.imageHelper.fixed(image) + '" />');
            return true;
        } else {
            this.previews[device].html('');
            return false;
        }
    };
    NextendImageEditor.prototype.getImageSize = function (image) {
        var deferred = $.Deferred(),
            newImage = new Image();

        newImage.onload = function () {
            deferred.resolve(newImage.width, newImage.height);
        }

        newImage.src = nextend.imageHelper.fixed(image);
        if (newImage.complete || newImage.readyState === 4) {
            newImage.onload();
        }
        return deferred;
    };

    NextendImageEditor.prototype.buttonGenerate = function () {
        return '<a href="#" class="n2-button n2-button-medium n2-button-grey n2-h5 n2-uc">' + n2_('Generate') + '</a>';
    };

    NextendImageEditor.prototype.generateImage = function (device) {
        var image = this.desktopImage;
        if (image == '') {
            nextend.notificationCenter.error(n2_('Desktop image is empty!'), {
                timeout: 3
            });
            return false;
        } else {
            return NextendAjaxHelper.ajax({
                type: "POST",
                url: NextendAjaxHelper.makeAjaxUrl(nextend.imageManager.parameters.ajaxUrl, {
                    nextendaction: 'generateImage'
                }),
                data: {
                    device: device,
                    image: image
                },
                dataType: 'json'
            }).done($.proxy(function (response) {
                var image = response.data.image;
                this.fields[device + '-image'].element.data('field').insideChange(nextend.imageHelper.make(image));
            }, this));
        }
    };

    NextendImageEditor.prototype.trigger = function (device, property, value) {
        this.$.trigger('change', [device, property, value]);
    };

    scope.NextendImageEditor = NextendImageEditor;

})
(n2, window);

;
(function ($, scope) {

    function NextendVisualManagerModals(visualManager) {
        this.visualManager = visualManager;
        this.linkedOverwriteOrSaveAs = null;
        this.saveAs = null;
    };

    NextendVisualManagerModals.prototype.getLinkedOverwriteOrSaveAs = function () {
        if (this.linkedOverwriteOrSaveAs == null) {
            var context = this;
            this.linkedOverwriteOrSaveAs = new NextendModal({
                zero: {
                    size: [
                        500,
                        140
                    ],
                    title: '',
                    back: false,
                    close: true,
                    content: '',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-grey n2-uc n2-h4">' + n2_('Save as new') + '</a>', '<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Overwrite current') + '</a>'],
                    fn: {
                        show: function () {
                            this.title.html(n2_printf(n2_('%s changed - %s'), context.visualManager.labels.visual, context.visualManager.activeVisual.name));
                            if (context.visualManager.activeVisual && !context.visualManager.activeVisual.isEditable()) {
                                this.loadPane('saveAsNew');
                            } else {
                                this.controls.find('.n2-button-green')
                                    .on('click', $.proxy(function (e) {
                                        e.preventDefault();
                                        context.visualManager.saveActiveVisual(context.visualManager.activeVisual.name)
                                            .done($.proxy(function () {
                                                this.hide(e);
                                                context.visualManager.setAndClose(context.visualManager.activeVisual.id);
                                                context.visualManager.hide();
                                            }, this));
                                    }, this));

                                this.controls.find('.n2-button-grey')
                                    .on('click', $.proxy(function (e) {
                                        e.preventDefault();
                                        this.loadPane('saveAsNew');
                                    }, this));
                            }
                        }
                    }
                },
                saveAsNew: {
                    size: [
                        500,
                        220
                    ],
                    title: n2_('Save as'),
                    back: 'zero',
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Save as new') + '</a>'],
                    fn: {
                        show: function () {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name').focus();

                            if (context.visualManager.activeVisual) {
                                nameField.val(context.visualManager.activeVisual.name);
                            }

                            button.on('click', $.proxy(function (e) {
                                e.preventDefault();
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    context.visualManager._saveAsNew(name)
                                        .done($.proxy(function () {
                                            this.hide(e);
                                            context.visualManager.setAndClose(context.visualManager.activeVisual.id);
                                            context.visualManager.hide();
                                        }, this));
                                }
                            }, this));
                        }
                    }
                }
            }, false);
        }
        return this.linkedOverwriteOrSaveAs;
    };

    NextendVisualManagerModals.prototype.getSaveAs = function () {
        if (this.saveAs === null) {
            var context = this;
            this.saveAs = new NextendModal({
                zero: {
                    size: [
                        500,
                        220
                    ],
                    title: n2_('Save as'),
                    back: false,
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Save as new') + '</a>'],
                    fn: {
                        show: function () {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name').focus();

                            if (context.visualManager.activeVisual) {
                                nameField.val(context.visualManager.activeVisual.name);
                            }

                            button.on('click', $.proxy(function (e) {
                                e.preventDefault();
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    context.visualManager._saveAsNew(name)
                                        .done($.proxy(this.hide, this, e));
                                }
                            }, this));
                        }
                    }
                }
            }, false);
        }
        return this.saveAs;
    };

    scope.NextendVisualManagerModals = NextendVisualManagerModals;
})(n2, window);
;
(function ($, scope) {

    function NextendVisualSetsManager(visualManager) {
        this.visualManager = visualManager;
        this.$ = $(this);
    }

    scope.NextendVisualSetsManager = NextendVisualSetsManager;

    function NextendVisualSetsManagerEditable(visualManager) {
        this.modal = null;
        NextendVisualSetsManager.prototype.constructor.apply(this, arguments);

        this.$.on({
            setAdded: function (e, set) {
                new NextendVisualSet(set, visualManager);
            },
            setChanged: function (e, set) {
                visualManager.sets[set.id].rename(set.value);
            },
            setDeleted: function (e, set) {
                visualManager.sets[set.id].delete();
                visualManager.setsSelector.trigger('change');
            }
        });

        this.manageButton = $('#' + visualManager.parameters.setsIdentifier + '-manage')
            .on('click', $.proxy(this.showManageSets, this));

    };

    NextendVisualSetsManagerEditable.prototype = Object.create(NextendVisualSetsManager.prototype);
    NextendVisualSetsManagerEditable.prototype.constructor = NextendVisualSetsManagerEditable;

    NextendVisualSetsManagerEditable.prototype.isSetAllowedToEdit = function (id) {
        if (id == -1 || typeof this.visualManager.sets[id] == 'undefined' || this.visualManager.sets[id].set.editable == 0) {
            return false;
        }
        return true;
    };


    NextendVisualSetsManagerEditable.prototype.createVisualSet = function (name) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                nextendaction: 'createSet'
            }),
            data: {
                name: name
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                this.$.trigger('setAdded', response.data.set)
            }, this));
    };

    NextendVisualSetsManagerEditable.prototype.renameVisualSet = function (id, name) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                nextendaction: 'renameSet'
            }),
            data: {
                setId: id,
                name: name
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                this.$.trigger('setChanged', response.data.set);
                nextend.notificationCenter.success(n2_('Set renamed'));
            }, this));
    };

    NextendVisualSetsManagerEditable.prototype.deleteVisualSet = function (id) {

        var d = $.Deferred(),
            set = this.visualManager.sets[id],
            deferreds = [];

        $.when(set._loadVisuals())
            .done($.proxy(function () {
                for (var k in set.visuals) {
                    deferreds.push(set.visuals[k]._delete());
                }

                $.when.apply($, deferreds).then($.proxy(function () {
                    NextendAjaxHelper.ajax({
                        type: "POST",
                        url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                            nextendaction: 'deleteSet'
                        }),
                        data: {
                            setId: id
                        },
                        dataType: 'json'
                    })
                        .done($.proxy(function (response) {
                            d.resolve();
                            this.$.trigger('setDeleted', response.data.set);
                        }, this));
                }, this));
            }, this))
            .fail(function () {
                d.reject();
            });
        return d
            .fail(function () {
                nextend.notificationCenter.error(n2_('Unable to delete the set'));
            });
    };

    NextendVisualSetsManagerEditable.prototype.showManageSets = function () {
        var visualManager = this.visualManager,
            setsManager = this;
        if (this.modal === null) {
            this.modal = new NextendModal({
                zero: {
                    size: [
                        500,
                        390
                    ],
                    title: n2_('Sets'),
                    back: false,
                    close: true,
                    content: '',
                    controls: ['<a href="#" class="n2-add-new n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Add new') + '</a>'],
                    fn: {
                        show: function () {
                            this.title.html(n2_printf(n2_('%s sets'), visualManager.labels.visual));

                            this.createHeading(n2_('Sets')).appendTo(this.content);
                            var data = [];
                            for (var k in visualManager.sets) {
                                var id = visualManager.sets[k].set.id;
                                if (setsManager.isSetAllowedToEdit(id)) {
                                    data.push([visualManager.sets[k].set.value, $('<div class="n2-button n2-button-grey n2-button-x-small n2-uc n2-h5">' + n2_('Rename') + '</div>')
                                        .on('click', {id: id}, $.proxy(function (e) {
                                            this.loadPane('rename', false, false, [e.data.id]);
                                        }, this)), $('<div class="n2-button n2-button-red n2-button-x-small n2-uc n2-h5">' + n2_('Delete') + '</div>')
                                        .on('click', {id: id}, $.proxy(function (e) {
                                            this.loadPane('delete', false, false, [e.data.id]);
                                        }, this))]);
                                } else {
                                    data.push([visualManager.sets[k].set.value, '', '']);
                                }
                            }
                            this.createTable(data, ['width:100%;', '', '']).appendTo(this.createTableWrap().appendTo(this.content));

                            this.controls.find('.n2-add-new')
                                .on('click', $.proxy(function (e) {
                                    e.preventDefault();
                                    this.loadPane('addNew');
                                }, this));
                        }
                    }
                },
                addNew: {
                    title: n2_('Create set'),
                    size: [
                        500,
                        220
                    ],
                    back: 'zero',
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Add') + '</a>'],
                    fn: {
                        show: function () {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name').focus();

                            button.on('click', $.proxy(function (e) {
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    setsManager.createVisualSet(name)
                                        .done($.proxy(function (response) {
                                            this.hide(e);
                                            nextend.notificationCenter.success(n2_('Set added'));
                                            visualManager.setsSelector.val(response.data.set.id).trigger('change')
                                        }, this));
                                }
                            }, this));
                        }
                    }
                },
                rename: {
                    title: n2_('Rename set'),
                    size: [
                        500,
                        220
                    ],
                    back: 'zero',
                    close: true,
                    content: '<form class="n2-form"></form>',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-green n2-uc n2-h4">' + n2_('Rename') + '</a>'],
                    fn: {
                        show: function (id) {

                            var button = this.controls.find('.n2-button'),
                                form = this.content.find('.n2-form').on('submit', function (e) {
                                    e.preventDefault();
                                    button.trigger('click');
                                }).append(this.createInput(n2_('Name'), 'n2-visual-name', 'width: 446px;')),
                                nameField = this.content.find('#n2-visual-name')
                                    .val(visualManager.sets[id].set.value).focus();

                            button.on('click', $.proxy(function () {
                                var name = nameField.val();
                                if (name == '') {
                                    nextend.notificationCenter.error(n2_('Please fill the name field!'));
                                } else {
                                    setsManager.renameVisualSet(id, name)
                                        .done($.proxy(this.goBack, this));
                                }
                            }, this));
                        }
                    }
                },
                'delete': {
                    title: n2_('Delete set'),
                    size: [
                        500,
                        190
                    ],
                    back: 'zero',
                    close: true,
                    content: '',
                    controls: ['<a href="#" class="n2-button n2-button-big n2-button-grey n2-uc n2-h4">' + n2_('Cancel') + '</a>', '<a href="#" class="n2-button n2-button-big n2-button-red n2-uc n2-h4">' + n2_('Yes') + '</a>'],
                    fn: {
                        show: function (id) {

                            this.createCenteredSubHeading(n2_printf(n2_('Do you really want to delete the set and all associated %s?'), visualManager.labels.visuals)).appendTo(this.content);

                            this.controls.find('.n2-button-grey')
                                .on('click', $.proxy(function (e) {
                                    e.preventDefault();
                                    this.goBack();
                                }, this));

                            this.controls.find('.n2-button-red')
                                .html('Yes, delete "' + visualManager.sets[id].set.value + '"')
                                .on('click', $.proxy(function (e) {
                                    e.preventDefault();
                                    setsManager.deleteVisualSet(id)
                                        .done($.proxy(this.goBack, this));
                                }, this));
                        }
                    }
                }
            }, false);
        }
        this.modal.show(false, [this.visualManager.setsSelector.val()]);
    };

    scope.NextendVisualSetsManagerEditable = NextendVisualSetsManagerEditable;


    function NextendVisualSet(set, visualManager) {
        this.set = set;
        this.visualManager = visualManager;

        this.visualList = $('<ul class="n2-list n2-h4"></ul>');


        this.visualManager.sets[set.id] = this;
        if (set.referencekey != '') {
            this.visualManager.setsByReference[set.referencekey] = set;
        }

        this.option = $('<option value="' + set.id + '">' + set.value + '</option>')
            .appendTo(this.visualManager.setsSelector);
    };


    NextendVisualSet.prototype.active = function () {
        $.when(this._loadVisuals())
            .done($.proxy(function () {
                this.visualList.appendTo(this.visualManager.visualListContainer);
            }, this));
    };

    NextendVisualSet.prototype.notActive = function () {
        this.visualList.detach();
    };

    NextendVisualSet.prototype.loadVisuals = function (visuals) {
        if (typeof this.visuals === 'undefined') {
            this.visuals = {};
            for (var i = 0; i < visuals.length; i++) {
                this.addVisual(visuals[i]);
            }
        }
    };

    NextendVisualSet.prototype._loadVisuals = function () {
        if (this.visuals == null) {
            return NextendAjaxHelper.ajax({
                type: "POST",
                url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                    nextendaction: 'loadVisualsForSet'
                }),
                data: {
                    setId: this.set.id
                },
                dataType: 'json'
            })
                .done($.proxy(function (response) {
                    this.loadVisuals(response.data.visuals);
                }, this));
        }
        return true;
    };

    NextendVisualSet.prototype.addVisual = function (visual) {
        if (typeof this.visuals[visual.id] === 'undefined') {
            this.visuals[visual.id] = this.visualManager.createVisual(visual, this);
            this.visualList.append(this.visuals[visual.id].createRow());
        }
        return this.visuals[visual.id];
    };

    NextendVisualSet.prototype.rename = function (name) {
        this.set.value = name;
        this.option.html(name);
    };

    NextendVisualSet.prototype.delete = function () {
        this.option.remove();
        delete this.visualManager.sets[this.set.id];
    };

    scope.NextendVisualSet = NextendVisualSet;

})(n2, window);
;
(function ($, scope) {

    function NextendStyleManager() {
        NextendVisualManagerSetsAndMore.prototype.constructor.apply(this, arguments);
        this.setFontSize(14);
    };

    NextendStyleManager.prototype = Object.create(NextendVisualManagerSetsAndMore.prototype);
    NextendStyleManager.prototype.constructor = NextendStyleManager;

    NextendStyleManager.prototype.loadDefaults = function () {
        NextendVisualManagerSetsAndMore.prototype.loadDefaults.apply(this, arguments);
        this.type = 'style';
        this.labels = {
            visual: n2_('style'),
            visuals: n2_('styles')
        };

        this.styleClassName2 = '';
        this.fontClassName = '';
        this.fontClassName2 = '';
    };


    NextendStyleManager.prototype.initController = function () {
        return new NextendStyleEditorController(this.parameters.renderer.modes);
    };

    NextendStyleManager.prototype.addVisualUsage = function (mode, styleValue, pre) {
        /**
         * if styleValue is numeric, then it is a linked style!
         */
        if (parseInt(styleValue) > 0) {
            return this._addLinkedStyle(mode, styleValue, pre);
        } else {
            try {
                this._renderStaticStyle(mode, styleValue, pre);
                return true;
            } catch (e) {
                return false;
            }
        }
    };

    NextendStyleManager.prototype._addLinkedStyle = function (mode, styleId, pre) {
        var used = this.parameters.renderer.usedStyles,
            d = $.Deferred();
        $.when(this.getVisual(styleId))
            .done($.proxy(function (style) {
                if (style.id > 0) {
                    if (typeof pre === 'undefined') {
                        if (typeof used[style.id] === 'undefined') {
                            used[style.id] = [mode];
                            this.renderLinkedStyle(mode, style, pre);
                        } else if ($.inArray(mode, used[style.id]) == -1) {
                            used[style.id].push(mode);
                            this.renderLinkedStyle(mode, style, pre);
                        }
                    } else {
                        this.renderLinkedStyle(mode, style, pre);
                    }
                    d.resolve(true);
                } else {
                    d.resolve(false);
                }
            }, this))
            .fail(function () {
                d.resolve(false);
            });
        return d;
    };

    NextendStyleManager.prototype.renderLinkedStyle = function (mode, style, pre) {
        if (typeof pre === 'undefined') {
            pre = this.parameters.renderer.pre;
        }
        nextend.css.add(this.renderer.getCSS(mode, pre, '.' + this.getClass(style.id, mode), style.value, {
            deleteRule: true
        }));

    };

    NextendStyleManager.prototype._renderStaticStyle = function (mode, style, pre) {
        if (typeof pre === 'undefined') {
            pre = this.parameters.renderer.pre;
        }
        nextend.css.add(this.renderer.getCSS(mode, pre, '.' + this.getClass(style, mode), JSON.parse(Base64.decode(style)).data, {}));
    };

    /**
     * We should never use this method as we do not track if a style used with the same mode multiple times.
     * So there is no sync and if we delete a used style, other usages might fail to update correctly in
     * special circumstances.
     * @param mode
     * @param styleId
     */
    NextendStyleManager.prototype.removeUsedStyle = function (mode, styleId) {
        var used = this.parameters.renderer.usedStyles;
        if (typeof used[styleId] !== 'undefined') {
            var index = $.inArray(mode, used[styleId]);
            if (index > -1) {
                used[styleId].splice(index, 1);
            }
        }
    };

    NextendStyleManager.prototype.getClass = function (style, mode) {
        if (parseInt(style) > 0) {
            return 'n2-style-' + style + '-' + mode;
        } else if (style == '') {
            return '';
        }
        // style might by empty with this class too, but we do not care as nothing wrong if it has an extra class
        // We could do try catch to JSON.parse(Base64.decode(style)), but it is wasting resource
        return 'n2-style-' + md5(style) + '-' + mode;
    };

    NextendStyleManager.prototype.createVisual = function (visual, set) {
        return new NextendStyle(visual, set, this);
    };

    NextendStyleManager.prototype.setConnectedStyle = function (styleId) {
        this.styleClassName2 = $('#' + styleId).data('field').renderStyle();
    };

    NextendStyleManager.prototype.setConnectedFont = function (fontId) {
        this.fontClassName = $('#' + fontId).data('field').renderFont();
    };

    NextendStyleManager.prototype.setConnectedFont2 = function (fontId) {
        this.fontClassName2 = $('#' + fontId).data('field').renderFont();
    };

    NextendStyleManager.prototype.setFontSize = function (fontSize) {
        this.controller.setFontSize(fontSize)
    };

    scope.NextendStyleManager = NextendStyleManager;

    function NextendStyle() {
        NextendVisualWithSetRow.prototype.constructor.apply(this, arguments);
    };

    NextendStyle.prototype = Object.create(NextendVisualWithSetRow.prototype);
    NextendStyle.prototype.constructor = NextendStyle;


    NextendStyle.prototype.removeRules = function () {
        var used = this.isUsed();
        if (used) {
            for (var i = 0; i < used.length; i++) {
                this.visualManager.removeRules(used[i], this);
            }
        }
    };

    NextendStyle.prototype.render = function () {
        var used = this.isUsed();
        if (used) {
            for (var i = 0; i < used.length; i++) {
                this.visualManager.renderLinkedStyle(used[i], this);
            }
        }
    };

    NextendStyle.prototype.isUsed = function () {
        if (typeof this.visualManager.parameters.renderer.usedStyles[this.id] !== 'undefined') {
            return this.visualManager.parameters.renderer.usedStyles[this.id];
        }
        return false;
    };

    scope.NextendStyle = NextendStyle;

})(n2, window);

;
(function ($, scope) {

    function NextendStyleEditorController() {
        NextendVisualEditorController.prototype.constructor.apply(this, arguments);

        this.preview = $('#n2-style-editor-preview');

        this.initBackgroundColor();
    }

    NextendStyleEditorController.prototype = Object.create(NextendVisualEditorController.prototype);
    NextendStyleEditorController.prototype.constructor = NextendStyleEditorController;

    NextendStyleEditorController.prototype.loadDefaults = function () {
        NextendVisualEditorController.prototype.loadDefaults.call(this);
        this.type = 'style';
        this.preview = null;
    };

    NextendStyleEditorController.prototype.initPreviewModes = function () {

        this.previewModes = {
            2: [this.previewModesList['button'], this.previewModesList['box']],
            3: [this.previewModesList['paragraph']]
        };
    };

    NextendStyleEditorController.prototype.initRenderer = function () {
        return new NextendStyleRenderer(this);
    };

    NextendStyleEditorController.prototype.initEditor = function () {
        return new NextendStyleEditor();
    };

    NextendStyleEditorController.prototype._load = function (visual, tabs, parameters) {
        if (visual.length) {
            visual[0] = $.extend({}, this.getEmptyStyle(), visual[0]);
        }

        NextendVisualEditorController.prototype._load.call(this, visual, tabs, parameters);
    };

    NextendStyleEditorController.prototype.getEmptyStyle = function () {
        return {
            backgroundcolor: 'ffffff00',
            padding: '0|*|0|*|0|*|0|*|px',
            boxshadow: '0|*|0|*|0|*|0|*|000000ff',
            border: '0|*|solid|*|000000ff',
            borderradius: '0',
            extra: ''
        };
    };

    NextendStyleEditorController.prototype.getCleanVisual = function () {
        return {
            extra: ''
        };
    };

    NextendStyleEditorController.prototype.getEmptyVisual = function () {
        return [this.getEmptyStyle()];
    };

    NextendStyleEditorController.prototype.setFontSize = function (fontSize) {
        this.preview.css('fontSize', fontSize);
    };

    NextendStyleEditorController.prototype.initBackgroundColor = function () {

        new NextendElementText("n2-style-editor-background-color");
        new NextendElementColor("n2-style-editor-background-color", 0);

        var box = this.lightbox.find('.n2-editor-preview-box');
        $('#n2-style-editor-background-color').on('nextendChange', function () {
            box.css('background', '#' + $(this).val());
        });
    };

    NextendStyleEditorController.prototype._renderPreview = function () {
        NextendVisualEditorController.prototype._renderPreview.call(this);

        this.addStyle(this.renderer.getCSS(this.currentPreviewMode, '', '.' + this.getPreviewCssClass(), this.currentVisual, {
            activeTab: this.currentTabIndex
        }));
    };

    NextendStyleEditorController.prototype.setPreview = function (mode) {

        var html = '';
        if (typeof this.localModePreview[mode] !== 'undefined' && this.localModePreview[mode] != '') {
            html = this.localModePreview[mode];
        } else {
            html = this.previewModesList[mode].preview;
        }

        var styleClassName = this.getPreviewCssClass(),
            fontClassName = nextend.styleManager.fontClassName,
            fontClassName2 = nextend.styleManager.fontClassName2,
            styleClassName2 = nextend.styleManager.styleClassName2;

        html = html.replace(/\{([^]*?)\}/g, function (match, script) {
            return eval(script);
        });

        this.preview.html(html);
    };

    NextendStyleEditorController.prototype.getPreviewCssClass = function () {
        return 'n2-' + this.type + '-editor-preview';
    };

    scope.NextendStyleEditorController = NextendStyleEditorController;

    function NextendStyleEditor() {

        NextendVisualEditor.prototype.constructor.apply(this, arguments);

        this.fields = {
            backgroundColor: {
                element: $('#n2-style-editorbackgroundcolor'),
                events: {
                    'nextendChange.n2-editor': $.proxy(this.changeBackgroundColor, this)
                }
            },
            padding: {
                element: $('#n2-style-editorpadding'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changePadding, this)
                }
            },
            boxShadow: {
                element: $('#n2-style-editorboxshadow'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeBoxShadow, this)
                }
            },
            border: {
                element: $('#n2-style-editorborder'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeBorder, this)
                }
            },
            borderRadius: {
                element: $('#n2-style-editorborderradius'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeBorderRadius, this)
                }
            },
            extracss: {
                element: $('#n2-style-editorextracss'),
                events: {
                    'outsideChange.n2-editor': $.proxy(this.changeExtraCSS, this)
                }
            }
        };
    };

    NextendStyleEditor.prototype = Object.create(NextendVisualEditor.prototype);
    NextendStyleEditor.prototype.constructor = NextendStyleEditor;

    NextendStyleEditor.prototype.load = function (values) {
        this._off();
        this.fields.backgroundColor.element.data('field').insideChange(values.backgroundcolor);
        this.fields.padding.element.data('field').insideChange(values.padding);
        this.fields.boxShadow.element.data('field').insideChange(values.boxshadow);
        this.fields.border.element.data('field').insideChange(values.border);
        this.fields.borderRadius.element.data('field').insideChange(values.borderradius);
        this.fields.extracss.element.data('field').insideChange(values.extra);
        this._on();
    };

    NextendStyleEditor.prototype.changeBackgroundColor = function () {
        this.trigger('backgroundcolor', this.fields.backgroundColor.element.val());

    };

    NextendStyleEditor.prototype.changePadding = function () {
        this.trigger('padding', this.fields.padding.element.val());
    };

    NextendStyleEditor.prototype.changeBoxShadow = function () {
        this.trigger('boxshadow', this.fields.boxShadow.element.val());
    };

    NextendStyleEditor.prototype.changeBorder = function () {
        this.trigger('border', this.fields.border.element.val());
    };

    NextendStyleEditor.prototype.changeBorderRadius = function () {
        this.trigger('borderradius', this.fields.borderRadius.element.val());
    };

    NextendStyleEditor.prototype.changeExtraCSS = function () {
        this.trigger('extra', this.fields.extracss.element.val());
    };

    scope.NextendStyleEditor = NextendStyleEditor;


    function NextendStyleRenderer() {
        NextendVisualRenderer.prototype.constructor.apply(this, arguments);
    }

    NextendStyleRenderer.prototype = Object.create(NextendVisualRenderer.prototype);
    NextendStyleRenderer.prototype.constructor = NextendStyleRenderer;


    NextendStyleRenderer.prototype.getCSS = function (modeKey, pre, selector, visualTabs, parameters) {
        visualTabs[0] = $.extend(this.editorController.getEmptyStyle(), visualTabs[0]);
        return NextendVisualRenderer.prototype.getCSS.call(this, modeKey, pre, selector, visualTabs, parameters);
    };

    NextendStyleRenderer.prototype.makeStylebackgroundcolor = function (value, target) {
        target.background = '#' + value.substr(0, 6) + ";\n\tbackground: " + N2Color.hex2rgbaCSS(value);
    };

    NextendStyleRenderer.prototype.makeStylepadding = function (value, target) {
        var padding = value.split('|*|'),
            unit = padding.pop();
        for (var i = 0; i < padding.length; i++) {
            padding[i] += unit;
        }
        target.padding = padding.join(' ');
    };

    NextendStyleRenderer.prototype.makeStyleboxshadow = function (value, target) {
        var s = value.split('|*|');
        if (s[0] == '0' && s[1] == '0' && s[2] == '0' && s[3] == '0') {
            target.boxShadow = 'none';
        } else {
            target.boxShadow = s[0] + 'px ' + s[1] + 'px ' + s[2] + 'px ' + s[3] + 'px ' + N2Color.hex2rgbaCSS(s[4]);
        }
    };

    NextendStyleRenderer.prototype.makeStyleborder = function (value, target) {
        var border = value.split('|*|');

        target.borderWidth = border[0] + 'px';
        target.borderStyle = border[1];
        target.borderColor = '#' + border[2].substr(0, 6) + ";\n\tborder-color:" + N2Color.hex2rgbaCSS(border[2]);
    };

    NextendStyleRenderer.prototype.makeStyleborderradius = function (value, target) {
        var radius = value.split('|*|');
        radius.push('');
        target.borderRadius = value + 'px';
    };

    NextendStyleRenderer.prototype.makeStyleextra = function (value, target) {

        target.raw = value;
    }

})
(n2, window);

;
(function ($, scope) {

    function NextendVisualManagerCore(parameters) {
        this.loadDefaults();

        this.$ = $(this);

        window.nextend[this.type + 'Manager'] = this;

        this.modals = this.initModals();

        this.lightbox = $('#n2-lightbox-' + this.type);

        this.notificationStack = new NextendNotificationCenterStack(this.lightbox.find('.n2-top-bar'));

        this.visualListContainer = this.lightbox.find('.n2-lightbox-sidebar-list');

        this.parameters = parameters;

        this.visuals = {};

        this.controller = this.initController();
        if (this.controller) {
            this.renderer = this.controller.renderer;
        }

        this.firstLoadVisuals(parameters.visuals);

        $('.n2-' + this.type + '-save-as-new')
            .on('click', $.proxy(this.saveAsNew, this));

        this.cancelButton = $('#n2-' + this.type + '-editor-cancel')
            .on('click', $.proxy(this.hide, this));

        this.saveButton = $('#n2-' + this.type + '-editor-save')
            .off('click')
            .on('click', $.proxy(this.setVisual, this));
    };

    NextendVisualManagerCore.prototype.setTitle = function (title) {
        this.lightbox.find('.n2-logo').html(title);
    };

    NextendVisualManagerCore.prototype.loadDefaults = function () {
        this.mode = 'linked';
        this.labels = {
            visual: n2_('visual'),
            visuals: n2_('visuals')
        };
        this.visualLoadDeferreds = {};
        this.showParameters = false;
    }


    NextendVisualManagerCore.prototype.initModals = function () {
        return new NextendVisualManagerModals(this);
    };

    NextendVisualManagerCore.prototype.firstLoadVisuals = function (visuals) {

        for (var k in visuals) {
            this.sets[k].loadVisuals(visuals[k]);
        }
    };

    NextendVisualManagerCore.prototype.initController = function () {

    };

    NextendVisualManagerCore.prototype.getVisual = function (id) {
        if (parseInt(id) > 0) {
            if (typeof this.visuals[id] !== 'undefined') {
                return this.visuals[id];
            } else if (typeof this.visualLoadDeferreds[id] !== 'undefined') {
                return this.visualLoadDeferreds[id];
            } else {
                var deferred = $.Deferred();
                this.visualLoadDeferreds[id] = deferred;
                this._loadVisualFromServer(id)
                    .done($.proxy(function () {
                        deferred.resolve(this.visuals[id]);
                        delete this.visualLoadDeferreds[id];
                    }, this))
                    .fail($.proxy(function () {
                        // This visual is Empty!!!
                        deferred.resolve({
                            id: -1,
                            name: n2_('Empty')
                        });
                        delete this.visualLoadDeferreds[id];
                    }, this));
                return deferred;
            }
        } else {
            try {
                JSON.parse(Base64.decode(id));
                return {
                    id: 0,
                    name: n2_('Static')
                };
            } catch (e) {
                // This visual is Empty!!!
                return {
                    id: -1,
                    name: n2_('Empty')
                };
            }
        }
    };

    NextendVisualManagerCore.prototype._loadVisualFromServer = function (visualId) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'loadVisual'
            }),
            data: {
                visualId: visualId
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                n2c.error('@todo: load the visual data!');
            }, this));
    };

    NextendVisualManagerCore.prototype.show = function (data, saveCallback, showParameters) {

        NextendEsc.add($.proxy(function () {
            this.hide();
            return true;
        }, this));

        this.notificationStack.enableStack();

        this.showParameters = $.extend({
            previewMode: false,
            previewHTML: false
        }, showParameters);

        $('body').css('overflow', 'hidden');
        this.lightbox.css('display', 'block');
        $(window)
            .on('resize.' + this.type + 'Manager', $.proxy(this.resize, this));
        this.resize();

        this.loadDataToController(data);
        this.controller.show();

        this.$.on('save', saveCallback);
    };

    NextendVisualManagerCore.prototype.setAndClose = function (data) {
        this.$.trigger('save', [data]);
    };

    NextendVisualManagerCore.prototype.hide = function (e) {
        this.controller.pause();
        this.notificationStack.popStack();
        if (typeof e !== 'undefined') {
            e.preventDefault();
            NextendEsc.pop();
        }
        this.controller.close();
        this.$.off('save');
        $(window).off('resize.' + this.type + 'Manager');
        $('body').css('overflow', '');
        this.lightbox.css('display', 'none');
    };

    NextendVisualManagerCore.prototype.resize = function () {
        var h = this.lightbox.height();
        var sidebar = this.lightbox.find('.n2-sidebar');
        sidebar.find('.n2-lightbox-sidebar-list').height(h - 1 - sidebar.find('.n2-logo').outerHeight() - sidebar.find('.n2-sidebar-row').outerHeight() - sidebar.find('.n2-save-as-new-container').parent().height());

        var contentArea = this.lightbox.find('.n2-content-area');
        contentArea.height(h - 1 - contentArea.siblings('.n2-top-bar, .n2-table').outerHeight());
    };

    NextendVisualManagerCore.prototype.loadDataToController = function (data) {
        if (this.isVisualData(data)) {
            $.when(this.getVisual(data)).done($.proxy(function (visual) {
                if (visual.id > 0) {
                    visual.activate();
                } else {
                    console.error(data + ' visual is not found linked');
                }
            }, this));
        } else {
            console.error(data + ' visual not found');
        }
    };

    NextendVisualManagerCore.prototype.isVisualData = function (data) {
        return parseInt(data) > 0;
    };

    NextendVisualManagerCore.prototype.setVisual = function (e) {
        e.preventDefault();
        switch (this.mode) {
            case 0:
                break;
            case 'static':
                this.modals.getLinkedOverwriteOrSaveAs()
                    .show('saveAsNew');
                break;
            case 'linked':
            default:
                if (this.activeVisual) {
                    if (this.activeVisual.compare(this.controller.get('set'))) {
                        //if (this.getBase64(this.activeVisual.name) == this.activeVisual.base64) {
                        this.setAndClose(this.activeVisual.id);
                        this.hide(e);
                    } else {

                        if (this.activeVisual && !this.activeVisual.isEditable()) {
                            this.modals.getLinkedOverwriteOrSaveAs()
                                .show('saveAsNew');
                        } else {
                            this.modals.getLinkedOverwriteOrSaveAs()
                                .show();
                        }
                    }
                } else {
                    this.modals.getLinkedOverwriteOrSaveAs()
                        .show('saveAsNew');
                }
                break;
        }
    };

    NextendVisualManagerCore.prototype.saveAsNew = function (e) {
        e.preventDefault();

        this.modals.getSaveAs()
            .show();
    };

    NextendVisualManagerCore.prototype._saveAsNew = function (name) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'addVisual'
            }),
            data: {
                setId: this.setsSelector.val(),
                value: Base64.encode(JSON.stringify({
                    name: name,
                    data: this.controller.get('saveAsNew')
                }))
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                var visual = response.data.visual;
                this.changeActiveVisual(this.sets[visual.referencekey].addVisual(visual));
            }, this));
    };

    NextendVisualManagerCore.prototype.saveActiveVisual = function (name) {

        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'changeVisual'
            }),
            data: {
                visualId: this.activeVisual.id,
                value: this.getBase64(name)
            },
            dataType: 'json'
        }).done($.proxy(function (response) {
            this.activeVisual.setValue(response.data.visual.value, true);
        }, this));
    };

    NextendVisualManagerCore.prototype.changeActiveVisual = function (visual) {
        if (this.activeVisual) {
            this.activeVisual.notActive();
            this.activeVisual = false;
        }
        if (visual /*&& (this.mode == 0 || this.mode == 'linked')*/) {
            if (this.mode == 'static') {
                this.setMode('linked');
            }
            visual.active();
            this.activeVisual = visual;
        }
    };

    NextendVisualManagerCore.prototype.getBase64 = function (name) {

        return Base64.encode(JSON.stringify({
            name: name,
            data: this.controller.get('set')
        }));
    };

    NextendVisualManagerCore.prototype.removeRules = function (mode, visual) {
        this.renderer.deleteRules(mode, this.parameters.renderer.pre, '.' + this.getClass(visual.id, mode));
    };

    scope.NextendVisualManagerCore = NextendVisualManagerCore;

    /**
     * Sets are visible
     */
    function NextendVisualManagerVisibleSets() {
        NextendVisualManagerCore.prototype.constructor.apply(this, arguments);
    }

    NextendVisualManagerVisibleSets.prototype = Object.create(NextendVisualManagerCore.prototype);
    NextendVisualManagerVisibleSets.prototype.constructor = NextendVisualManagerVisibleSets;

    NextendVisualManagerVisibleSets.prototype.firstLoadVisuals = function (visuals) {
        this.sets = {};
        this.setsByReference = {};

        this.setsSelector = $('#' + this.parameters.setsIdentifier + 'sets_select');
        for (var i = 0; i < this.parameters.sets.length; i++) {
            this.newVisualSet(this.parameters.sets[i]);
        }
        this.initSetsManager();

        for (var k in visuals) {
            this.sets[k].loadVisuals(visuals[k])
        }

        this.activeSet = this.sets[this.setsSelector.val()];
        this.activeSet.active();

        this.setsSelector.on('change', $.proxy(function () {
            this.activeSet.notActive();
            this.activeSet = this.sets[this.setsSelector.val()];
            this.activeSet.active();
        }, this));
    };


    NextendVisualManagerVisibleSets.prototype.initSetsManager = function () {
        new NextendVisualSetsManager(this);
    };

    NextendVisualManagerVisibleSets.prototype._loadVisualFromServer = function (visualId) {
        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.parameters.ajaxUrl, {
                nextendaction: 'loadSetByVisualId'
            }),
            data: {
                visualId: visualId
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                this.sets[response.data.set.setId].loadVisuals(response.data.set.visuals);

            }, this));
    };

    NextendVisualManagerVisibleSets.prototype.changeSet = function (setId) {
        if (this.setsSelector.val() != setId) {
            this.setsSelector.val(setId)
                .trigger('change');
        }
    };

    NextendVisualManagerVisibleSets.prototype.changeSetById = function (id) {
        if (typeof this.sets[id] !== 'undefined') {
            this.changeSet(id);
        }
    };

    NextendVisualManagerVisibleSets.prototype.newVisualSet = function (set) {
        return new NextendVisualSet(set, this);
    };

    scope.NextendVisualManagerVisibleSets = NextendVisualManagerVisibleSets;

    /**
     * Sets are editable
     * Ex.: Layout
     */
    function NextendVisualManagerEditableSets() {
        NextendVisualManagerVisibleSets.prototype.constructor.apply(this, arguments);
    }

    NextendVisualManagerEditableSets.prototype = Object.create(NextendVisualManagerVisibleSets.prototype);
    NextendVisualManagerEditableSets.prototype.constructor = NextendVisualManagerEditableSets;

    NextendVisualManagerEditableSets.prototype.initSetsManager = function () {
        new NextendVisualSetsManagerEditable(this);
    };

    scope.NextendVisualManagerEditableSets = NextendVisualManagerEditableSets;

    /**
     * Static and linked mode
     * Ex.: Style, Fonts, Animation
     */

    function NextendVisualManagerSetsAndMore() {
        NextendVisualManagerEditableSets.prototype.constructor.apply(this, arguments);

        this.linkedButton = $('#n2-' + this.type + '-editor-set-as-linked');
        this.setMode(0);
    }

    NextendVisualManagerSetsAndMore.prototype = Object.create(NextendVisualManagerEditableSets.prototype);
    NextendVisualManagerSetsAndMore.prototype.constructor = NextendVisualManagerSetsAndMore;


    NextendVisualManagerSetsAndMore.prototype.setMode = function (newMode) {
        if (newMode == 'static') {
            this.changeActiveVisual(null);
        }
        if (this.mode != newMode) {
            switch (newMode) {
                case 0:
                    //this.modeRadio.parent.css('display', 'none');
                    this.cancelButton.css('display', 'none');
                    this.saveButton
                        .off('click');
                    break;

                case 'static':
                default:
                    this.cancelButton.css('display', 'inline-block');
                    this.saveButton
                        .off('click')
                        .on('click', $.proxy(this.setVisualAsStatic, this));
                    this.linkedButton
                        .off('click')
                        .on('click', $.proxy(this.setVisualAsLinked, this));
                    break;
            }
            this.mode = newMode;
        }
    };

    NextendVisualManagerSetsAndMore.prototype.loadDataToController = function (data) {
        if (parseInt(data) > 0) {
            $.when(this.getVisual(data)).done($.proxy(function (visual) {
                if (visual.id > 0) {
                    this.setMode('linked');
                    visual.activate();
                } else {
                    this.setMode('static');
                    this.controller.load('', false, this.showParameters);
                }
            }, this));
        } else {
            var visualData = '';
            this.setMode('static');
            try {
                visualData = this.getStaticData(data);
            } catch (e) {
                // This visual is Empty!!!
            }
            this.controller.load(visualData, false, this.showParameters);
        }
    };

    NextendVisualManagerSetsAndMore.prototype.getStaticData = function (data) {
        var d = JSON.parse(Base64.decode(data)).data;
        if (typeof d === 'undefined') {
            return '';
        }
        return d;
    };

    NextendVisualManagerSetsAndMore.prototype.setVisualAsLinked = function (e) {
        this.setVisual(e);
    };

    NextendVisualManagerSetsAndMore.prototype.setVisualAsStatic = function (e) {
        e.preventDefault();
        this.setAndClose(this.getBase64(n2_('Static')));
        this.hide(e);
    };

    scope.NextendVisualManagerSetsAndMore = NextendVisualManagerSetsAndMore;


    /**
     * Multiple selection
     * Ex.: Background animation, Post background animation
     */

    function NextendVisualManagerMultipleSelection(parameters) {

        window.nextend[this.type + 'Manager'] = this;

        // Push the constructor to the first show as an optimization.
        this._lateInit = $.proxy(function (parameters) {
            NextendVisualManagerVisibleSets.prototype.constructor.call(this, parameters);
        }, this, parameters);

    }

    NextendVisualManagerMultipleSelection.prototype = Object.create(NextendVisualManagerVisibleSets.prototype);
    NextendVisualManagerMultipleSelection.prototype.constructor = NextendVisualManagerMultipleSelection;


    NextendVisualManagerMultipleSelection.prototype.lateInit = function () {
        if (!this.inited) {
            this.inited = true;

            this._lateInit();
        }
    };

    NextendVisualManagerMultipleSelection.prototype.show = function (data, saveCallback, controllerParameters) {

        this.lateInit();

        this.notificationStack.enableStack();

        NextendEsc.add($.proxy(function () {
            this.hide();
            return true;
        }, this));

        $('body').css('overflow', 'hidden');
        this.lightbox.css('display', 'block');
        $(window)
            .on('resize.' + this.type + 'Manager', $.proxy(this.resize, this));
        this.resize();

        var i = 0;
        if (data != '') {
            var selected = data.split('||'),
                hasSelected = false;
            for (; i < selected.length; i++) {
                $.when(this.getVisual(selected[i])).done(function (visual) {
                    if (visual && visual.check) {
                        visual.check();
                        if (!hasSelected) {
                            hasSelected = true;
                            visual.activate();
                        }
                    }
                });
            }
        }

        this.$.on('save', saveCallback);

        this.controller.start(controllerParameters);

        if (i == 0) {
            $.when(this.activeSet._loadVisuals())
                .done($.proxy(function () {
                    for (var k in this.activeSet.visuals) {
                        this.activeSet.visuals[k].activate();
                        break;
                    }
                }, this));
        }
    };

    NextendVisualManagerMultipleSelection.prototype.setVisual = function (e) {
        e.preventDefault();
        this.setAndClose(this.getAsString());
        this.hide(e);
    };

    NextendVisualManagerMultipleSelection.prototype.getAsString = function () {
        var selected = [];
        for (var k in this.sets) {
            var set = this.sets[k];
            for (var i in set.visuals) {
                if (set.visuals[i].checked) {
                    selected.push(set.visuals[i].id);
                }
            }
        }
        if (selected.length == 0 && this.activeVisual) {
            selected.push(this.activeVisual.id);
        }
        return selected.join('||');
    };

    NextendVisualManagerMultipleSelection.prototype.hide = function (e) {
        NextendVisualManagerVisibleSets.prototype.hide.apply(this, arguments);

        for (var k in this.sets) {
            var set = this.sets[k];
            for (var i in set.visuals) {
                set.visuals[i].unCheck();
            }
        }
    };

    scope.NextendVisualManagerMultipleSelection = NextendVisualManagerMultipleSelection;


    function NextendVisualCore(visual, visualManager) {
        this.id = visual.id;
        this.visualManager = visualManager;
        this.setValue(visual.value, false);
        this.visual = visual;
        this.visualManager.visuals[this.id] = this;
    };

    NextendVisualCore.prototype.compare = function (value) {

        var length = Math.max(this.value.length, value.length);
        for (var i = 0; i < length; i++) {
            if (!this._compareTab(typeof this.value[i] === 'undefined' ? {} : this.value[i], typeof value[i] === 'undefined' ? {} : value[i])) {
                return false;
            }
        }
        return true;
    };

    NextendVisualCore.prototype._compareTab = function (a, b) {
        var aProps = Object.getOwnPropertyNames(a);
        var bProps = Object.getOwnPropertyNames(b);
        if (a.length === 0 && bProps.length === 0) {
            return true;
        }

        if (aProps.length != bProps.length) {
            return false;
        }

        for (var i = 0; i < aProps.length; i++) {
            var propName = aProps[i];

            // If values of same property are not equal,
            // objects are not equivalent
            if (a[propName] !== b[propName]) {
                return false;
            }
        }

        return true;
    };

    NextendVisualCore.prototype.setValue = function (value, render) {
        var data = null;
        if (typeof value == 'string') {
            this.base64 = value;
            data = JSON.parse(Base64.decode(value));
        } else {
            data = value;
        }
        this.name = data.name;
        this.value = data.data;

        if (render) {
            this.render();
        }
    };

    NextendVisualCore.prototype.isSystem = function () {
        return (this.visual.system == 1);
    };

    NextendVisualCore.prototype.isEditable = function () {
        return (this.visual.editable == 1);
    };

    NextendVisualCore.prototype.activate = function (e) {
        if (typeof e !== 'undefined') {
            e.preventDefault();
        }
        this.visualManager.changeActiveVisual(this);
        this.visualManager.controller.load(this.value, false, this.visualManager.showParameters);
    };

    NextendVisualCore.prototype.active = function () {
    };

    NextendVisualCore.prototype.notActive = function () {
    };

    NextendVisualCore.prototype.delete = function (e) {
        if (e) {
            e.preventDefault();
        }
        NextendDeleteModal('n2-visual', this.name, $.proxy(function () {
            this._delete();
        }, this));
    };
    NextendVisualCore.prototype._delete = function () {

        return NextendAjaxHelper.ajax({
            type: "POST",
            url: NextendAjaxHelper.makeAjaxUrl(this.visualManager.parameters.ajaxUrl, {
                nextendaction: 'deleteVisual'
            }),
            data: {
                visualId: this.id
            },
            dataType: 'json'
        })
            .done($.proxy(function (response) {
                var visual = response.data.visual;

                if (this.visualManager.activeVisual && this.id == this.visualManager.activeVisual.id) {
                    this.visualManager.changeActiveVisual(null);
                }
                this.removeRules();
                delete this.visualManager.visuals[this.id];
                delete this.set.visuals[this.id];
                this.row.remove();
                this.visualManager.$.trigger('visualDelete', [this.id]);
            }, this));
    };

    NextendVisualCore.prototype.removeRules = function () {

    };

    NextendVisualCore.prototype.render = function () {

    };

    NextendVisualCore.prototype.isUsed = function () {
        return false;
    };

    scope.NextendVisualCore = NextendVisualCore;

    function NextendVisualWithSet(visual, set, visualManager) {
        this.set = set;
        NextendVisualCore.prototype.constructor.call(this, visual, visualManager);
    };

    NextendVisualWithSet.prototype = Object.create(NextendVisualCore.prototype);
    NextendVisualWithSet.prototype.constructor = NextendVisualWithSet;

    NextendVisualWithSet.prototype.active = function () {
        var setId = this.set.set.id;
        this.visualManager.changeSet(setId);

        NextendVisualCore.prototype.active.call(this);
    };

    scope.NextendVisualWithSet = NextendVisualWithSet;


    function NextendVisualWithSetRow() {
        NextendVisualWithSet.prototype.constructor.apply(this, arguments);
    };

    NextendVisualWithSetRow.prototype = Object.create(NextendVisualWithSet.prototype);
    NextendVisualWithSetRow.prototype.constructor = NextendVisualWithSetRow;


    NextendVisualWithSetRow.prototype.createRow = function () {
        this.row = $('<li></li>')
            .append($('<a href="#">' + this.name + '</a>')
                .on('click', $.proxy(this.activate, this)));
        if (!this.isSystem()) {
            this.row.append($('<span class="n2-actions"></span>')
                .append($('<a href="#"><i class="n2-i n2-i-delete n2-i-grey-opacity"></i></a>')
                    .on('click', $.proxy(this.delete, this))));
        }
        return this.row;
    };

    NextendVisualWithSetRow.prototype.setValue = function (value, render) {
        NextendVisualWithSet.prototype.setValue.call(this, value, render);

        if (this.row) {
            this.row.find('> a').html(this.name);
        }
    };

    NextendVisualWithSetRow.prototype.active = function () {
        this.row.addClass('n2-active');
        NextendVisualWithSet.prototype.active.call(this);
    };

    NextendVisualWithSetRow.prototype.notActive = function () {
        this.row.removeClass('n2-active');
        NextendVisualWithSet.prototype.notActive.call(this);
    };

    scope.NextendVisualWithSetRow = NextendVisualWithSetRow;


    function NextendVisualWithSetRowMultipleSelection(visual, set, visualManager) {
        this.checked = false;
        visual.system = 1;
        visual.editable = 0;
        NextendVisualWithSetRow.prototype.constructor.apply(this, arguments);
    };

    NextendVisualWithSetRowMultipleSelection.prototype = Object.create(NextendVisualWithSetRow.prototype);
    NextendVisualWithSetRowMultipleSelection.prototype.constructor = NextendVisualWithSetRowMultipleSelection;


    NextendVisualWithSetRowMultipleSelection.prototype.createRow = function () {
        var row = NextendVisualWithSetRow.prototype.createRow.call(this);
        this.checkbox = $('<div class="n2-list-checkbox"><i class="n2-i n2-i-tick"></i></div>')
            .on('click', $.proxy(this.checkOrUnCheck, this))
            .prependTo(row.find('a'));

        return row;
    };

    NextendVisualWithSetRowMultipleSelection.prototype.setValue = function (data, render) {
        this.name = data.name;
        this.value = data.data;
        if (this.row) {
            this.row.find('> a').html(this.name);
        }

        if (render) {
            this.render();
        }
    };

    NextendVisualWithSetRowMultipleSelection.prototype.activate = function (e) {
        if (typeof e !== 'undefined') {
            e.preventDefault();
        }
        this.visualManager.changeActiveVisual(this);
        this.visualManager.controller.setAnimationProperties(this.value);
    };

    NextendVisualWithSetRowMultipleSelection.prototype.checkOrUnCheck = function (e) {
        e.preventDefault();
        e.stopPropagation();
        if (this.checked) {
            this.unCheck();
        } else {
            this.check();
        }
    };

    NextendVisualWithSetRowMultipleSelection.prototype.check = function () {
        this.checked = true;
        this.checkbox.addClass('n2-active');
        this.activate();
    };

    NextendVisualWithSetRowMultipleSelection.prototype.unCheck = function () {
        this.checked = false;
        this.checkbox.removeClass('n2-active');
        this.activate();
    };

    scope.NextendVisualWithSetRowMultipleSelection = NextendVisualWithSetRowMultipleSelection;

})(n2, window);

(function ($, scope) {
    "use strict";
    function NextendVisualEditorControllerBase() {
        this.loadDefaults();
        this.lightbox = $('#n2-lightbox-' + this.type);
    }

    NextendVisualEditorControllerBase.prototype.loadDefaults = function () {
        this.type = '';
        this._style = false;
        this.isChanged = false;
        this.visible = false;
    };

    NextendVisualEditorControllerBase.prototype.init = function () {
        this.lightbox = $('#n2-lightbox-' + this.type);
    };


    NextendVisualEditorControllerBase.prototype.pause = function () {

    };

    NextendVisualEditorControllerBase.prototype.getEmptyVisual = function () {
        return [];
    };

    NextendVisualEditorControllerBase.prototype.get = function () {
        return this.currentVisual;
    };

    NextendVisualEditorControllerBase.prototype.load = function (visual, tabs, parameters) {
        this.isChanged = false;
        this.lightbox.addClass('n2-editor-loaded');
        if (visual == '') {
            visual = this.getEmptyVisual();
        }
        this._load(visual, tabs, parameters);
    };

    NextendVisualEditorControllerBase.prototype._load = function (visual, tabs, parameters) {
        this.currentVisual = $.extend(true, {}, visual);
    };

    NextendVisualEditorControllerBase.prototype.addStyle = function (style) {
        if (this._style) {
            this._style.remove();
        }
        this._style = $("<style>" + style + "</style>").appendTo("head");
    };

    NextendVisualEditorControllerBase.prototype.show = function () {
        this.visible = true;
    };

    NextendVisualEditorControllerBase.prototype.close = function () {
        this.visible = false;
    };
    scope.NextendVisualEditorControllerBase = NextendVisualEditorControllerBase;

    function NextendVisualEditorControllerWithEditor() {

        NextendVisualEditorControllerBase.prototype.constructor.apply(this, arguments);

        this.editor = this.initEditor();
        this.editor.$.on('change', $.proxy(this.propertyChanged, this));
    };


    NextendVisualEditorControllerWithEditor.prototype = Object.create(NextendVisualEditorControllerBase.prototype);
    NextendVisualEditorControllerWithEditor.prototype.constructor = NextendVisualEditorControllerWithEditor;


    NextendVisualEditorControllerWithEditor.prototype.initEditor = function () {
        return new NextendVisualEditor();
    };

    NextendVisualEditorControllerWithEditor.prototype.propertyChanged = function (e, property, value) {
        this.isChanged = true;
        this.currentVisual[property] = value;
    };

    NextendVisualEditorControllerWithEditor.prototype._load = function (visual, tabs, parameters) {
        NextendVisualEditorControllerBase.prototype._load.apply(this, arguments);
        this.loadToEditor();
    };

    NextendVisualEditorControllerWithEditor.prototype.loadToEditor = function () {
        this.editor.load(this.currentVisual);
    };

    scope.NextendVisualEditorControllerWithEditor = NextendVisualEditorControllerWithEditor;


    function NextendVisualEditorController(previewModesList) {
        NextendVisualEditorControllerWithEditor.prototype.constructor.apply(this, arguments);

        this.previewModesList = previewModesList;

        this.initPreviewModes();
        if (previewModesList) {

            this.renderer = this.initRenderer();

            this.clearTabButton = this.lightbox.find('.n2-editor-clear-tab')
                .on('click', $.proxy(this.clearCurrentTab, this));


            this.tabField = new NextendElementRadio('n2-' + this.type + '-editor-tabs', ['0']);
            this.tabField.element.on('nextendChange.n2-editor', $.proxy(this.tabChanged, this));

            this.previewModeField = new NextendElementRadio('n2-' + this.type + '-editor-preview-mode', ['0']);
            this.previewModeField.element.on('nextendChange.n2-editor', $.proxy(this.previewModeChanged, this));

            this.previewModeField.options.eq(0).html(this.previewModesList[0].label);
        }
    }

    NextendVisualEditorController.prototype = Object.create(NextendVisualEditorControllerWithEditor.prototype);
    NextendVisualEditorController.prototype.constructor = NextendVisualEditorController;

    NextendVisualEditorController.prototype.loadDefaults = function () {
        NextendVisualEditorControllerWithEditor.prototype.loadDefaults.call(this);

        this.currentPreviewMode = '0';
        this.currentTabIndex = 0;
        this._renderTimeout = 0;
        this._delayStart = 0;
    };

    NextendVisualEditorController.prototype.initPreviewModes = function () {
    };

    NextendVisualEditorController.prototype.initRenderer = function () {
    };

    NextendVisualEditorController.prototype._load = function (visual, tabs, parameters) {

        this.currentVisual = [];
        for (var i = 0; i < visual.length; i++) {
            this.currentVisual[i] = $.extend(true, this.getCleanVisual(), visual[i]);
        }

        this.localModePreview = {};
        if (parameters.previewMode === false) {
            this.availablePreviewMode = false;
        } else {
            this.availablePreviewMode = parameters.previewMode;
            if (tabs === false) {
                tabs = this.getTabs();
            }
            for (var i = this.currentVisual.length; i < tabs.length; i++) {
                this.currentVisual[i] = this.getCleanVisual();
            }
            if (parameters.previewHTML !== false && parameters.previewHTML != '') {
                this.localModePreview[parameters.previewMode] = parameters.previewHTML;
            }
        }

        this.currentTabs = tabs;

        if (tabs === false) {
            tabs = [];
            for (var i = 0; i < this.currentVisual.length; i++) {
                tabs.push('#' + i);
            }
        }

        this.setTabs(tabs);
    };

    NextendVisualEditorController.prototype.getCleanVisual = function () {
        return {};
    };

    NextendVisualEditorController.prototype.getTabs = function () {
        return this.previewModesList[this.availablePreviewMode].tabs;
    };

    NextendVisualEditorController.prototype.setTabs = function (labels) {
        this.tabField.insideChange('0');
        for (var i = this.tabField.values.length - 1; i > 0; i--) {
            this.tabField.removeTabOption(this.tabField.values[i]);
        }
        this.tabField.options.eq(0).html(labels[0]);
        for (var i = 1; i < labels.length; i++) {
            this.tabField.addTabOption(i + '', labels[i]);
        }

        this.makePreviewModes();
    };

    NextendVisualEditorController.prototype.tabChanged = function () {
        if (document.activeElement) {
            document.activeElement.blur();
        }

        var tab = this.tabField.element.val();

        this.currentTabIndex = tab;
        if (typeof this.currentVisual[tab] === 'undefined') {
            this.currentVisual[tab] = {};
        }
        var values = $.extend({}, this.currentVisual[0]);
        if (tab != 0) {
            $.extend(values, this.currentVisual[tab]);
            this.clearTabButton.css('display', '');
        } else {
            this.clearTabButton.css('display', 'none');
        }

        this.editor.load(values);
        this._tabChanged();
    };

    NextendVisualEditorController.prototype._tabChanged = function () {
        this._renderPreview();
    };

    NextendVisualEditorController.prototype.clearCurrentTab = function (e) {
        if (e) {
            e.preventDefault();
        }
        this.currentVisual[this.currentTabIndex] = {};
        this.tabChanged();
        this._renderPreview();
    };

    NextendVisualEditorController.prototype.makePreviewModes = function () {
        var modes = [];
        // Show all preview mode for the tab count
        if (this.availablePreviewMode === false) {
            var tabCount = this.tabField.options.length;
            if (typeof this.previewModes[tabCount] !== "undefined") {
                modes = this.previewModes[tabCount];
            }
            this.setPreviewModes(modes);
        } else {
            modes = [this.previewModesList[this.availablePreviewMode]];
            this.setPreviewModes(modes, this.availablePreviewMode);
        }
    };

    NextendVisualEditorController.prototype.setPreviewModes = function (modes, defaultMode) {
        for (var i = this.previewModeField.values.length - 1; i > 0; i--) {
            this.previewModeField.removeTabOption(this.previewModeField.values[i]);
        }
        for (var i = 0; i < modes.length; i++) {
            this.previewModeField.addTabOption(modes[i].id, modes[i].label);
        }
        if (typeof defaultMode === 'undefined') {
            defaultMode = '0';
        }
        this.previewModeField.insideChange(defaultMode);
    };

    NextendVisualEditorController.prototype.previewModeChanged = function () {
        var mode = this.previewModeField.element.val();

        if (this.currentTabs === false) {
            if (mode == 0) {
                for (var i = 0; i < this.currentVisual.length; i++) {
                    this.tabField.options.eq(i).html('#' + i);
                }
            } else {
                var tabs = this.previewModesList[mode].tabs;
                if (tabs) {
                    for (var i = 0; i < this.currentVisual.length; i++) {
                        this.tabField.options.eq(i).html(tabs[i]);
                    }
                }
            }
        }
        this.currentPreviewMode = mode;
        this._renderPreview();

        this.setPreview(mode);
    };

    NextendVisualEditorController.prototype.setPreview = function (mode) {
    };

    NextendVisualEditorController.prototype.propertyChanged = function (e, property, value) {
        this.isChanged = true;
        this.currentVisual[this.currentTabIndex][property] = value;
        this.renderPreview();
    };

    NextendVisualEditorController.prototype.renderPreview = function () {
        var now = $.now();
        if (this._renderTimeout) {
            clearTimeout(this._renderTimeout);
            if (now - this._delayStart > 100) {
                this._renderPreview();
                this._delayStart = now;
            }
        } else {
            this._delayStart = now;
        }
        this._renderTimeout = setTimeout($.proxy(this._renderPreview, this), 33);
    };

    NextendVisualEditorController.prototype._renderPreview = function () {
        this._renderTimeout = false;
    };

    scope.NextendVisualEditorController = NextendVisualEditorController;

    function NextendVisualEditor() {
        this.fields = {};
        this.$ = $(this);
    };

    NextendVisualEditor.prototype.load = function (values) {
        this._off();
        this._on();
    };

    NextendVisualEditor.prototype._on = function () {
        for (var id in this.fields) {
            this.fields[id].element.on(this.fields[id].events);
        }
    };

    NextendVisualEditor.prototype._off = function () {
        for (var id in this.fields) {
            this.fields[id].element.off('.n2-editor');
        }
    };

    NextendVisualEditor.prototype.trigger = function (property, value) {
        this.$.trigger('change', [property, value]);
    };

    scope.NextendVisualEditor = NextendVisualEditor;

    function NextendVisualRenderer(editorController) {
        this.editorController = editorController;
    }

    NextendVisualRenderer.prototype.deleteRules = function (modeKey, pre, selector) {
        var mode = this.editorController.previewModesList[modeKey],
            rePre = new RegExp('@pre', "g"),
            reSelector = new RegExp('@selector', "g");
        for (var k in mode.selectors) {
            var rule = k
                .replace(rePre, pre)
                .replace(reSelector, selector);
            nextend.css.deleteRule(rule);
        }
    };

    NextendVisualRenderer.prototype.getCSS = function (modeKey, pre, selector, visualTabs, parameters) {
        var css = '',
            mode = this.editorController.previewModesList[modeKey],
            rePre = new RegExp('@pre', "g"),
            reSelector = new RegExp('@selector', "g");

        for (var k in mode.selectors) {
            var rule = k
                .replace(rePre, pre)
                .replace(reSelector, selector);

            css += rule + "{\n" + mode.selectors[k] + "}\n";
            if (typeof parameters.deleteRule !== 'undefined') {
                nextend.css.deleteRule(rule);
            }
        }


        if (modeKey == 0) {
            var visualTab = visualTabs[parameters.activeTab];
            if (parameters.activeTab != 0) {
                visualTab = $.extend({}, visualTabs[0], visualTab);
            }
            css = css.replace(new RegExp('@tab[0-9]*', "g"), this.render(visualTab));
        } else if (mode.renderOptions.combined) {
            for (var i = 0; i < visualTabs.length; i++) {
                css = css.replace(new RegExp('@tab' + i, "g"), this.render(visualTabs[i]));
            }
        } else {
            for (var i = 0; i < visualTabs.length; i++) {
                visualTabs[i] = $.extend({}, visualTabs[i])
                css = css.replace(new RegExp('@tab' + i, "g"), this.render(visualTabs[i]));
            }
        }
        return css;
    };

    NextendVisualRenderer.prototype.render = function (visualData) {
        var visual = this.makeVisualData(visualData);
        var css = '',
            raw = '';
        if (typeof visual.raw !== "undefined") {
            raw = visual.raw;
            delete visual.raw;
        }
        for (var k in visual) {

            css += this.deCase(k) + ": " + visual[k] + ";\n";
        }
        css += raw;
        return css;
    };

    NextendVisualRenderer.prototype.makeVisualData = function (visualData) {
        var visual = {};
        for (var property in visualData) {
            if (visualData.hasOwnProperty(property) && typeof visualData[property] !== 'function') {
                this['makeStyle' + property](visualData[property], visual);
            }
        }
        return visual;
    };

    NextendVisualRenderer.prototype.deCase = function (s) {
        return s.replace(/[A-Z]/g, function (a) {
            return '-' + a.toLowerCase()
        });
    };

    scope.NextendVisualRenderer = NextendVisualRenderer;

})(n2, window);

;
(function ($, scope) {

    function NextendFontServiceGoogle(style, subset, fonts) {
        this.style = style;
        this.subset = subset;
        this.fonts = fonts;
        $(window).on('n2Family', $.proxy(this.loadFamily, this));
    }

    NextendFontServiceGoogle.prototype.loadFamily = function (e, family) {

        if ($.inArray(family, this.fonts) != -1) {
            $('<link />').attr({
                rel: 'stylesheet',
                type: 'text/css',
                href: '//fonts.googleapis.com/css?family=' + encodeURIComponent(family + ':' + this.style) + '&subset=' + encodeURIComponent(this.subset)
            }).appendTo($('head'));
        }
    };

    scope.NextendFontServiceGoogle = NextendFontServiceGoogle;
})(n2, window);
