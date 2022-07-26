/* ========================================================================
 * @author Edouard Tack <edouard@tackacoder.fr>
 * Candlestick main version 1.3.1
 * Licensed under MIT (https://github.com/EdouardTack/candlestick/blob/master/LICENSE)
 * ======================================================================== */

"use strict";
(function($) {

    /** Storage of the candlestick's settings */
    var candlestickOptions = {};

    /**
     * jQuery extended plugin
     *
     * @property {string} Mode value
     * @value options|contents
     * @property {object} Contents value
     * @value left|middle|right
     * @property {string} On value
     * @value '1'
     * @property {string} Off value
     * @value '0'
     * @property {string} Default/None value
     * @value ''
     * @property {string} Size
     * @value 'md'
     * @property {bool/object} Swipe
     * @value true
     * @property {bool} debug mode
     * @value false
     * @property {bool} allowManualDefault
     * @value true
     * @property {function}
     * @property {function}
     * @property {function}
     * @property {function}
     * @property {function}
     */
    $.fn.candlestick = function(options) {
        if (options == 'val') {
            if ($(this).find('input[type="hidden"]')) {
                return $(this).find('input[type="hidden"]').val();
            }
            else {
                return $(this).val();
            }
        }

        var swipe = true;
        if ((typeof options) != 'undefined') {
            if ((typeof options.swipe) == 'boolean') {
                swipe = options.swipe;
            }
            else if ((typeof options.swipe) == 'object') {
                if ((typeof options.swipe.enabled) != 'undefined' && (typeof options.swipe.enabled) == 'boolean') {
                    var defaultSwipeOptions = {
                        enabled: true,
                        mobile: true,
                        desktop: true,
                        transition: false
                    };
                    swipe = $.extend(defaultSwipeOptions, options.swipe);
                }
            }
        }

        var defaults = {
            'mode': 'options',
            'contents': {
                'left': 'Left',
                'middle': 'Middle',
                'right': 'Right',
                'swipe': false
            },
            'on': '1',
            'off': '0',
            'nc': '',
            'size': 'md',
            'swipe': swipe,
            'debug': false,
            'allowManualDefault': true,
            afterAction: function() {},
            afterRendering: function() {},
            afterOrganization: function() {},
            afterSetting: function() {},
            customVars: function(settings) {return settings;}
        };

        if (candlestickOptions[$(this).attr('id')]) {
            defaults = candlestickOptions[$(this).attr('id')];
        }

        // Do options like reset or change value
        if ((typeof options) == 'string' && $(this).attr('id')) {
            // Condition about the choose option
            switch (options) {
                case 'on':
                    return this.each(function() {
                        var candlestick = candlestickLightInitialize($(this), defaults);
                        candlestick.defaultOrganization();
                        candlestick.setBackground('on');
                        candlestick.setHandle(1);
                    });
                break;
                case 'off':
                    return this.each(function() {
                        var candlestick = candlestickLightInitialize($(this), defaults);
                        candlestick.defaultOrganization();
                        candlestick.setBackground('off');
                        candlestick.setHandle(-1);
                    });
                break;
                case 'reset':
                case 'default':
                    return this.each(function() {
                        var candlestick = candlestickLightInitialize($(this), defaults);
                        if (defaults.allowManualDefault) {
                            candlestick.defaultOrganization();
                            candlestick.setValue(0);
                        }
                    });
                break;
                case 'enable':
                    return this.each(function() {
                        var candlestick = candlestickLightInitialize($(this), defaults);
                        candlestick.parent.removeClass('candlestick-disabled');
                    });
                break;
                case 'disable':
                    return this.each(function() {
                        var candlestick = candlestickLightInitialize($(this), defaults);
                        candlestick.parent.addClass('candlestick-disabled');
                    });
                break;
            }
        }

        var settings = $.extend(defaults, options);

        return this.each(function() {
            if ($(this).attr('type') == 'checkbox') {
                if ($(this).attr('id')) {
                    var candlestick = new Candlestick($(this), settings);
                    candlestickOptions[$(this).attr('id')] = settings;
                    candlestick.initialize();
                }
                else {
                    $.error('Candlestick needs an unique id attribute to store settings !!!');
                }
            }
            else {
                $.error('Candlestick only used on checkbox input fields !!!');
            }
        });
    }

    /**
     * Instance of Candlestick object
     *
     * @param element jQuery element
     * @param settings Settings for Candlestick Object
     * @return void
     */
    var Candlestick = function (element, settings) {
        this.element = element;
        this.settings = settings.customVars(settings);
        this.default = {};

        if (this.getMode('contents') && this.settings.contents.swipe)
            this.settings.swipe = false;
    };

    /**
     * Initializing the awesome
     *
     * @return void
     */
    Candlestick.prototype.initialize = function () {
        this.log('initialize');
        // Initialize default values
        this.default.id = this.element.attr('id');
        this.default.value = this.element.val();
        this.default.name = this.element.attr('name');
        this.default.data = this.element.data();

        // Create the HTML code
        this.wrapElement();

        // Delete Input element
        this.delete();

        // Do afterRendering callback
        if (this.settings.afterRendering())
            this.settings.afterRendering(this.element, this.parent);

        // Organize elements inside the plugin
        this.defaultOrganization();

        // Do afterOrganization callback
        if (this.settings.afterOrganization())
            this.settings.afterOrganization(this.element, this.parent);

        // set the value
        this.assignValue();

        // initialize possible actions
        this.actions();
    };

    /**
     * Create the new element
     * Do the html architecture
     *
     * @return void
     */
    Candlestick.prototype.wrapElement = function () {
        this.log('Create HTMLCandlestick element');
        var $classes = '';

        var $id = '';
        if (this.default.id)
            $id = this.default.id;
        else
            $id = this.default.name.replace(/[\[\]]/gi, "");

        this.log('ID : ' + $id);
        this.default.id = $id;

        var datas = '';
        if (this.default.data) {
            var $data = this.default.data;
            for (var d in $data) {
                datas += ' data-' + d + '="' + $data[d] + '"';
            }
        }

        var $inputDisabled = '';
        if (this.element.attr('disabled')) {
            $classes = ' candlestick-disabled';
            $inputDisabled = ' disabled';
        }

        var $inputReadonly = '';
        if (this.element.attr('readonly')) {
            $classes = ' candlestick-disabled readonly';
            $inputReadonly = ' readonly';
        }

        if (this.isSwipeEnable()) {
            $classes += ' grab';
        }

        if (!this.settings.allowManualDefault) {
            $classes += ' candlestick-default-disabled';
        }

        switch (this.settings.mode) {
            case 'contents':
                var html = '<div class="candlestick-wrapper candlestick-contents"><div data-candlestick-id="' + $id + '" class="candlestick-bg' + $classes + '"><div class="candlestick-toggle"></div><div class="candlestick-off" data-content="' + this.settings.contents.left + '"></div>';

                if (this.settings.contents.middle) {
                    html += '<div class="candlestick-nc" data-content="' + this.settings.contents.middle + '"></div>';
                }

                html += '<div class="candlestick-on" data-content="' + this.settings.contents.right + '"></div><input type="hidden" class="' + this.element.attr('class') + '" value="' + this.default.value + '" name="' + this.default.name + '" id="' + $id + '"' + datas + $inputDisabled + $inputReadonly + '></div></div>';
            break;
            default:
                var html = '<div class="candlestick-wrapper candlestick-size-' + this.settings.size + '"><div data-candlestick-id="' + $id + '" class="candlestick-bg' + $classes + '"><div class="candlestick-toggle"></div><div class="candlestick-off"><i class="fa fa-times"></i></div><div class="candlestick-nc">&nbsp;</div><div class="candlestick-on"><i class="fa fa-check"></i></div><input type="hidden" class="' + this.element.attr('class') + '" value="' + this.default.value + '" name="' + this.default.name + '" id="' + $id + '"' + datas + $inputDisabled + $inputReadonly + '></div></div>';
            break;
        }

        this.element.wrap(html);
    };

    /**
     * Set the value compare with settings values
     *
     * @return void
     */
    Candlestick.prototype.assignValue = function () {
        var value = this.default.value;

        if (
            (
                (typeof value) == (typeof this.settings.on) &&
                value == this.settings.on
            ) ||
            (
                (typeof value) == (typeof this.settings.off) &&
                value == this.settings.off
            )
        )
        {
            this.setPositionByValue(value);
        }
    };

    /**
     * Set the value compared the cursor moving
     *
     * @param cursor identity of cursor moving
     * @return void
     */
    Candlestick.prototype.setValue = function (cursor) {
        var value = '';
        if (cursor == 0) {
            value = this.settings.nc;
        }
        else if (cursor > 0) {
            value = this.settings.on;
        }
        else if (cursor < 0) {
            value = this.settings.off;
        }

        this.element.val(value);

        // Do afterSetting callback
        if (this.settings.afterSetting)
            this.settings.afterSetting(this.element, this.parent, value);
    };

    /**
     * Move the toggle compared the value
     *
     * @param cursor identity of cursor moving
     * @return void
     */
    Candlestick.prototype.setHandle = function (cursor) {
        var action = '';
        var left = 0;

        if (cursor == 0) {
            this.defaultOrganization();
            // Do afterAction callback
            this.settings.afterAction(this.element, this.parent, 'default');

            this.setValue(cursor);
            return;
        }
        else if (cursor > 0) {
            left = this.parent.outerWidth() - this.parent.find('.candlestick-toggle').outerWidth();
            action = 'on';
        }
        else if (cursor < 0) {
            action = 'off';
        }

        // Do afterAction callback
        this.settings.afterAction(this.element, this.parent, action);

        this.handle.css('left', left);
        this.setValue(cursor);
    };

    /**
     * Move the toggle in initialization with the value setting
     *
     * @param value
     * @return void
     */
    Candlestick.prototype.setPositionByValue = function(value) {
        if (value == this.settings.on) {
            this.setBackground('on');
            this.setHandle(1);
        }
        else if (value == this.settings.off) {
            this.setBackground('off');
            this.setHandle(-1);
        }
    };

    /**
     * Set the class for the background element
     *
     * @param className class name
     * @return void
     */
    Candlestick.prototype.setBackground = function (className) {
        this.parent.removeClass('on').removeClass('default').removeClass('off');
        this.parent.addClass(className);
    };

    /**
     * Organization when no value set
     *
     * @return void
     */
    Candlestick.prototype.defaultOrganization = function () {
        var candlestickBgHeight = this.parent.outerHeight();
        var candlestickBgWidth = this.parent.outerWidth();
        var candlestickFalseWidth = this.handle.outerWidth();
        var candlestickToggleWidth = this.handle.outerWidth();
        var candlestickToggleHeight = this.handle.outerHeight();

        this.parent.addClass('default');
        this.parent.removeClass('on').removeClass('off');
        this.handle.css({
            top: (candlestickBgHeight - (candlestickToggleHeight)) / 2,
            left: (candlestickBgWidth / 2) - (candlestickToggleWidth / 2)
        });
    };

    /**
     *
     *
     * @return void
     */
    Candlestick.prototype.mouseContents = function () {
        var that = this;
        $('.candlestick-bg').on('mousedown', function(e) {
            e.preventDefault();
            e.stopPropagation();

            that.mouseEvent = {
                leftElement: 100,
                offsetX: 0
            };

            $(this).addClass('move');
            that.mouseEvent.leftElement = parseInt($(this).find('.candlestick-toggle').css('left'));
            that.mouseEvent.offsetX = e.pageX;
        })
        .on('mousemove', function(e) {
            var move;

            e.preventDefault();
            e.stopPropagation();

            if ($(this).hasClass('move')) {
                // MOVE LEFT
                if (e.pageX < that.mouseEvent.offsetX) {
                    move = e.pageX - that.mouseEvent.offsetX;
                }
                // MOVE RIGHT
                else {
                    move = e.pageX - that.mouseEvent.offsetX;
                }

                var leftPosition = that.slot($(this), move);
                $(this).find('.candlestick-toggle').css('left', leftPosition);
            }
        })
        .on('mouseup', function(e) {
            e.preventDefault();
            e.stopPropagation();

            $(this).removeClass('move');
            that.placeToggle($(this));
        });
    };

    /**
     * Place the toggle button by his left position
     *
     * @param element jQuery element $('.candlestick-bg')
     * @return void
     */
    Candlestick.prototype.placeToggle = function (element) {
        var widthElement = parseInt(element.find('.candlestick-toggle').innerWidth());
        var leftPositionElement = parseInt(element.find('.candlestick-toggle').css('left'));

        // Off position
        if (leftPositionElement <= widthElement) {
            this.log('Off position');
            if (leftPositionElement < (widthElement / 2)) {
                this.log('putOff event');
                this.putOff();
            }
            else {
                this.log('putDefault event');
                this.putDefault();
            }
        }
        // Default / On position
        else {
            this.log('Other position (except Off)');
            // On position
            if (leftPositionElement >= (widthElement + (widthElement / 2))) {
                this.log('On position');
                this.putOn();
            }
            else { // Default position
                this.log('default position');
                this.putDefault('default');
            }
        }
    };

    /**
     * The toggle button can not exit his parent slot
     *
     * @param element jQuery element $('.candlestick-bg')
     * @param move int Move integer
     * @return int The new left position
     */
    Candlestick.prototype.slot = function (element, move) {
        var leftPosition = this.mouseEvent.leftElement + move;
        var widthElement = parseInt(element.find('.candlestick-toggle').innerWidth());
        var limitationSlot = widthElement * 2;

        // Right limitation
        if (leftPosition > limitationSlot)
            leftPosition = limitationSlot;

        // Left limitation
        if (leftPosition < 0)
            leftPosition = 0;

        return leftPosition;
    };

    /**
     * List of possible actions
     *
     * @return void
     */
    Candlestick.prototype.actions = function () {
        this.actionOn();
        this.actionOff();
        this.actionDefault();

        if (this.isSwipeEnable()) {
            this.log('Swipe is enable. Swipe event is On.');
            this.actionSwipe();
        }
        else {
            if (this.getMode('contents')) {
                this.log('"contents" mode. Mouse event is enable.');
                this.mouseContents();
            }
        }
    };

    /**
     * On action
     *
     * @return void
     */
    Candlestick.prototype.actionOn = function () {
        var that = this;
        this.parent.find('.candlestick-on').on('click', function(e) {
            e.preventDefault();
            that.putOn();
        });
    };

    /**
     * Do the On animations
     *
     * @return void
     */
    Candlestick.prototype.putOn = function () {
        if (!this.parent.hasClass('candlestick-disabled')) {
            this.setBackground('on');
            this.setHandle(1);
        }
    };

    /**
     * Off action
     *
     * @return void
     */
    Candlestick.prototype.actionOff = function () {
        var that = this;
        this.parent.find('.candlestick-off').on('click', function(e) {
            e.preventDefault();
            that.putOff();
        });
    };

    /**
     * Do the Off animations
     *
     * @return void
     */
    Candlestick.prototype.putOff = function () {
        if (!this.parent.hasClass('candlestick-disabled')) {
            this.setBackground('off');
            this.setHandle(-1);
        }
    };

    /**
     * Put the toggle to his position by swipe direction
     *
     * @param direction bool
     * @return void
     */
    Candlestick.prototype.putToggle = function (direction) {
        if (!this.parent.hasClass('candlestick-disabled')) {
            switch (direction) {
                case 'left':
                    if (this.element.val() == this.settings.on) {
                        this.setBackground('');
                        this.setHandle(0);
                    }
                    else if (this.element.val() == this.settings.nc) {
                        this.putOff();
                    }
                break;
                case 'right':
                    if (this.element.val() == this.settings.off) {
                        this.setBackground('');
                        this.setHandle(0);
                    }
                    else if (this.element.val() == this.settings.nc) {
                        this.putOn();
                    }
                    return false;
                break;
            }
        }
    };

    /**
     * Default action
     *
     * @return void
     */
    Candlestick.prototype.actionDefault = function () {
        var that = this;
        this.parent.find('.candlestick-nc').on('click', function(e) {
            e.preventDefault();
            if (!that.parent.hasClass('candlestick-disabled') && that.settings.allowManualDefault) {
                that.putDefault('default');
            }
        });
    };

    /**
     * Do the Default animations
     *
     * @return void
     */
    Candlestick.prototype.putDefault = function (classname) {
        this.setBackground(classname);
        this.setHandle(0);
    };

    /**
     * Swipe action
     *
     * @return void
     */
    Candlestick.prototype.actionSwipe = function () {
        var that = this;

        if (that.isSwipeEnable()) {
            if (typeof $.fn.hammer != 'undefined') {
                that.log('Hammerjs is enable');

                var element = that.parent.find('.candlestick-toggle');
                if (that.getMode('contents'))
                    element = that.parent;

                element.hammer({
                    threshold: 0,
                    velocity: 0.1
                })
                .bind("panleft", function(e) {
                    if (!that.getMode('contents')) {
                        e.preventDefault();
                        e.stopPropagation();

                        if (that.settings.swipe.transition) {
                            that.putToggle('left');
                        }
                        else {
                            that.putOff();
                        }
                    }

                    return false;
                })
                .bind("panright", function(e) {
                    if (!that.getMode('contents')) {
                        e.preventDefault();
                        e.stopPropagation();

                        if (that.settings.swipe.transition) {
                            that.putToggle('right');
                        }
                        else {
                            that.putOn();
                        }
                    }

                    return false;
                })
                .bind("swipeleft", function(e) {
                    if (that.getMode('contents')) {
                        that.swipeElementContents($(this), -1);
                    }
                })
                .bind("swiperight", function(e) {
                    if (that.getMode('contents')) {
                        that.swipeElementContents($(this), 1);
                    }
                });
            }
            else {
                $.error('You have to load hammerjs && jquery-hammerjs lib to use swipe option');
            }
        }
    };

    /**
     * Put the toggle to the right position after swipe event
     *
     * @param element jQuery element
     * @param direction integer direction (-1 => left,1 => right)
     *
     * @return void
     */
    Candlestick.prototype.swipeElementContents = function (element, direction) {
        var widthElement = parseInt(this.handle.innerWidth());
        var leftPosition = parseInt(this.handle.css('left'));

        if (direction > 0) { // Right swipe
            if (leftPosition == 0) {
                this.log('Right swipe to default position');
                if (this.settings.nc)
                    this.putDefault();
                else
                    this.putOff();
            }
            else {
                this.log('Right swipe to on position');
                this.putOn();
            }
        }
        else if (direction < 0) { // Left swipe
            if (leftPosition > 0 && leftPosition > (widthElement * 2)) {
                this.log('Left swipe to default position');
                if (this.settings.nc)
                    this.putDefault();
                else
                    this.putOn();
            }
            else {
                this.log('Left swipe to off position');
                this.putOff();
            }
        }
    };

    /**
     * Check is Swipe option is enable
     *
     * @return bool
     */
    Candlestick.prototype.isSwipeEnable = function () {
        var swipe = true;
        if ((typeof this.settings.swipe) == 'boolean') {
            swipe = this.settings.swipe;
        }
        else if ((typeof this.settings.swipe) == 'object') {
            swipe = this.settings.swipe.enabled;
        }

        return swipe;
    };

    /**
     * Check the mode
     *
     * @param mode string attending mode
     * @return bool
     */
    Candlestick.prototype.getMode = function (mode) {
        return this.settings.mode == mode;
    };

    /**
     * Delete the default element and instance new jquery elements
     *
     * @return void
     */
    Candlestick.prototype.delete = function () {
        this.element.remove();
        this.parent = $('[data-candlestick-id="' + this.default.id + '"]');
        this.handle = this.parent.find('.candlestick-toggle');
        this.element = $('#' + this.default.id);
    };

    /**
     * Display log message
     * Enable the debug option
     *
     * @param string Message to display
     * @return void
     */
    Candlestick.prototype.log = function (string) {
        if (this.settings.debug) {
            console.log(string);
        }
    };

    /**
     * Use it to declare a light Candlestick Object without standard initialization
     *
     * @param element
     * @param settings
     *
     * @return Object
     */
    function candlestickLightInitialize(element, settings) {
        var candle = $('#' + element.attr('id'));
        var candlestick = new Candlestick(candle, settings);
        candlestick.parent = candle.parent();
        candlestick.handle = candlestick.parent.find('.candlestick-toggle');

        candlestick.settings = settings.customVars(settings);
        candlestick.default = {};
        candlestick.default.id = candlestick.element.attr('id');
        candlestick.default.value = candlestick.element.val();
        candlestick.default.name = candlestick.element.attr('name');
        candlestick.default.data = candlestick.element.data();

        return candlestick;
    }

})(jQuery);
