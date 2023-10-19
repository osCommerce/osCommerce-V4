$.fn.inRow = function(options, col){
    return this.each(function() {
        var heightItem = 0;
        var _this = $(this);
        $.each(options, function(i, option){

            var heightTmp = 0;
            heightItem = 0;
            var row = [];
            var n = 0;
            var j = 0;
            var len = _this.find(option).length;
            _this.find(option).each(function(i){
                row[n] = $(this);
                var col_tmp = col;
                if (i % col == col - 1 && i != 0 || i == len-1){
                    if (i == len-1 && len % col != 0){
                        col_tmp = len % col
                    }
                    heightItem = 0;
                    for(j = 0; j < col_tmp; j++){
                        if(row[j]) {
                            row[j].css('min-height', '0');
                            if (row[j].css('box-sizing') == 'border-box') {
                                heightTmp = row[j].height();
                                heightTmp += row[j].css('padding-top').replace(/[^0-9]+/, '')*1;
                                heightTmp += row[j].css('padding-bottom').replace(/[^0-9]+/, '')*1;
                            } else {
                                heightTmp = row[j].height();
                            }
                            if (heightItem < heightTmp) {
                                heightItem = heightTmp;
                            }
                        }
                    }
                    for(j = 0; j < col_tmp; j++){
                        if (row[j]) {
                            row[j].css('min-height', heightItem);
                        }
                    }
                    n = -1;
                }
                n++
            })

        });
    })
};

$.fn.closeable = function(options){
    options = $.extend({
        eventClass: '.closeable-event',
        contentClass: '.closeable-content',
        headingClass: '.closeable-heading',
        closedClass: 'closed',
    },options);

    return this.each(function() {
        var box = $(this);
        var id = box.attr('id');
        var content = $(options.contentClass + ':first', box);
        var heading = $(options.headingClass + ':first', box);

        var state = boxState(box);
        if (state === 'closed' || (!state && heading.hasClass(options.closedClass))) {
            heading.addClass(options.closedClass);
            content.hide()
        } else {
            heading.removeClass(options.closedClass);
            content.show()
        }

        $(options.eventClass + ':first', box).off('click', openCloseBox).on('click', openCloseBox)

        function openCloseBox(){
            var state = boxState(box);
            if (state === 'closed' || (!state && heading.hasClass(options.closedClass))) {
                heading.removeClass(options.closedClass);
                content.slideDown();
                boxState(box, 'opened')
            } else {
                heading.addClass(options.closedClass);
                content.slideUp();
                boxState(box, 'closed')
            }
        }
    });


    function boxState(box, state){
        var states = JSON.parse(localStorage.getItem("boxStates"));
        var id = box.attr('id');
        if (!state) {
            if (id && states && states[id]) {
                return states[id];
            } else {
                return false
            }
        } else if (id){
            if (!states) {
                states = {};
            }
            states[id] = state;
            localStorage.setItem("boxStates", JSON.stringify(states))
        }
    }
};

$.fn.backCounter = function(options){
    options = $.extend({
        daysBox: '.box-days',// html box which contained days count and attendant content
        hoursBox: '.box-hours',// html box which contained hours count and attendant content
        minutesBox: '.box-minutes',// html box which contained minutes count and attendant content
        secondsBox: '.box-seconds',// html box which contained seconds count and attendant content
        daysElement: '.left-count-days',// html element which contained only days digits
        hoursElement: '.left-count-hours',// html element which contained only hours digits
        minutesElement: '.left-count-minutes',// html element which contained only minutes digits
        secondsElement: '.left-count-seconds'// html element which contained only seconds digits
    },options);

    return this.each(function() {

        var daysBox = $(options.daysBox, this);
        var hoursBox = $(options.hoursBox, this);
        var minutesBox = $(options.minutesBox, this);
        var secondsBox = $(options.secondsBox, this);
        var daysElement = $(options.daysElement, this);
        var hoursElement = $(options.hoursElement, this);
        var minutesElement = $(options.minutesElement, this);
        var secondsElement = $(options.secondsElement, this);
        var days = daysElement.text()*1;
        var hours = hoursElement.text()*1;
        var minutes = minutesElement.text()*1;
        var seconds = secondsElement.text()*1;
        var daysTmp = days;
        var hoursTmp = hours;
        var minutesTmp = minutes;
        var secondsTmp = seconds;

        var updateTime = function(){
            if (seconds <= 0 && minutes <= 0 && hours <= 0 && days <=0) {
                location.reload();
            }

            if (!days && (daysBox.is(':visible') || minutesBox.is(':hidden'))) {
                daysBox.hide();
                minutesBox.show();
            }
            if (!days && !hours && (hoursBox.is(':visible') || secondsBox.is(':hidden'))) {
                hoursBox.hide();
                secondsBox.show();
            }

            seconds = seconds - 1;
            if (seconds < 0) {
                seconds = 59;
                minutes = minutes - 1;
                if (minutes < 0) {
                    minutes = 59;
                    hours = hours - 1;
                    if (hours < 0) {
                        hours = 23;
                        days = days - 1;
                        if (days < 0) {
                            days = 0;
                        }
                    }
                }
            }

            if (daysTmp !== days) {
                daysElement.text(days + '');
            }
            if (hoursTmp !== hours) {
                hoursElement.text(hours + '');
            }
            if (minutesTmp !== minutes) {
                minutesElement.text(minutes + '');
            }
            if (secondsTmp !== seconds) {
                secondsElement.text(seconds + '');
            }

            daysTmp = days;
            hoursTmp = hours;
            minutesTmp = minutes;
            secondsTmp = seconds;
        };

        setInterval(updateTime, 1000);
    })
};


$.fn.quantity = function(options){
    options = $.extend({
        min: 1,
        max: false,
        step: 1,
        virtual_item_qty: 1,
        virtual_item_step: [1],
        event: function(){}
    },options);

    return this.each(function() {
        var _this = $(this);
        if ( _this.val()==='' ) _this.val('0');
        if (!_this.parent().hasClass('qty-box')) {
            var delay = (function(){
                var timer = 0;
                return function(callback, ms){
                    clearTimeout (timer);
                    timer = setTimeout(callback, ms || 300);
                };
            })();

            var fast_step = { '+':1, '-':-1, 'PageUp':10, 'PageDown':-10 };

            var min = 0;
            var max = 0;
            var step = 0;
            var virtual_item_qty = 1;
            var virtual_item_step = [1];

            _this.wrap('<span class="qty-box"></span>');
            var qtyBox = _this.closest('.qty-box');
            qtyBox.prepend('<span class="smaller"></span>');
            var smaller = $('.smaller', qtyBox)
            qtyBox.append('<span class="bigger"></span>');
            var bigger = $('.bigger', qtyBox);
            var qty = _this.val();

            _this.on('changeSettings', function(event, skip_check){
                var differ_settings = false;
                var new_min = _this.attr('data-min')?parseInt(_this.attr('data-min'),10):options.min;
                var new_max = _this.attr('data-max')?parseInt(_this.attr('data-max'),10):options.max;
                var new_step = _this.attr('data-step')?parseInt(_this.attr('data-step'),10):options.step;
                var new_virtual_item_qty = _this.attr('data-virtual-item-qty')?parseInt(_this.attr('data-virtual-item-qty'),10):options.virtual_item_qty;
                var new_virtual_item_step = _this.attr('data-virtual-item-step')?_this.data('virtual-item-step'):options.virtual_item_step;
                if (new_max !== false && new_min > new_max){
                    new_max = false;
                    _this.attr('data-error', 'min > max');
                }
                if (min !== new_min) differ_settings = true;
                if (new_max!==false && max !== new_max) differ_settings = true;
                if (step !== new_step) differ_settings = true;
                if (virtual_item_qty !== new_virtual_item_qty) differ_settings = true;
                if (virtual_item_step !== new_virtual_item_step) differ_settings = true;
                min = new_min;
                max = new_max;
                step = new_step;
                virtual_item_qty = new_virtual_item_qty;
                if (virtual_item_qty < 1) {
                    virtual_item_qty = 1;
                }
                virtual_item_step = new_virtual_item_step;
                if (!$.isArray(virtual_item_step)) {
                    virtual_item_step = [1];
                }
                $.each(virtual_item_step, function(index) {
                    virtual_item_step[index] = parseInt(this, 10);
                });
                if ( differ_settings && !skip_check ) {
                    _this.trigger('check_quantity');
                }
            });
            _this.trigger('changeSettings', true);

            _this.on('focus',function(){
                this.select();
            });

            bigger.on('click', function(){
                if (!$(this).hasClass('disabled')) {
                    qty = _this.data('value-real');
                    let qty_original = parseInt(qty, 10);
                    qty = parseInt(qty, 10) + step;
                    if ((virtual_item_qty > 1) && (virtual_item_step.length > 0)) {
                        let qty_step = (qty % virtual_item_qty);
                        let qty_index = $.inArray(qty_step, virtual_item_step);
                        if ((qty_step != 0) && (qty_index < 0)) {
                            qty_step = (qty_original % virtual_item_qty);
                            qty_index = $.inArray(qty_step, virtual_item_step);
                            if (qty_step == 0) {
                                qty = (qty_original + virtual_item_qty - virtual_item_step[virtual_item_step.length - 1]);
                            } else {
                                if (typeof(virtual_item_step[qty_index + 1]) != 'undefined') {
                                    qty = (qty_original + virtual_item_step[qty_index + 1] - virtual_item_step[qty_index]);
                                } else {
                                    qty = (virtual_item_qty * (Math.ceil(qty / virtual_item_qty)));
                                }
                            }
                        }
                    }
                    _this.trigger('check_quantity', [qty]);
                }
            });

            smaller.on('click', function(){
                if (!$(this).hasClass('disabled')) {
                    qty = _this.data('value-real');
                    if ( qty===(''+min) && (_this.attr('data-zero-init') || (_this.attr('data-min') && _this.attr('data-min')==='0')) ) {
                        qty = 0;
                    }else {
                        let qty_original = parseInt(qty, 10);
                        qty = parseInt(qty, 10) - step;
                        if ((qty > min) && (virtual_item_qty > 1) && (virtual_item_step.length > 0)) {
                            let qty_step = (qty % virtual_item_qty);
                            let qty_index = $.inArray(qty_step, virtual_item_step);
                            if ((qty_step != 0) && (qty_index < 0)) {
                                qty_step = (qty_original % virtual_item_qty);
                                qty_index = $.inArray(qty_step, virtual_item_step);
                                if (qty_step == 0) {
                                    qty = (qty_original - virtual_item_qty + virtual_item_step[virtual_item_step.length - 1]);
                                } else {
                                    if (typeof(virtual_item_step[qty_index - 1]) != 'undefined') {
                                        qty = (qty_original + virtual_item_step[qty_index - 1] - virtual_item_step[qty_index]);
                                    } else {
                                        qty = virtual_item_qty * (Math.floor(qty / virtual_item_qty));
                                    }
                                }
                            }
                        }
                        if (qty < min) qty = min;
                    }
                    _this.trigger('check_quantity',[qty]);
                }
            });

            _this.on('check_quantity',function(event, new_value, direct_change){
                var old_value = parseInt((_this.data('value-real') || _this.val()),10);
                var qty = ((new_value===0)?new_value:parseInt(new_value || old_value,10)),
                    base_qty = 0,
                    zero_allow = !!_this.attr('data-zero-init') || (_this.attr('data-min') && _this.attr('data-min')==='0');
                if ( isNaN(qty) ) return;
                if ( zero_allow && qty===0 ) {

                }else{
                    var result_quantity = Math.max(min, qty, 1);
                    if (min > step) {
                        base_qty = min;
                    }
                    if (result_quantity > min && ((result_quantity - base_qty) % step) !== 0) {
                        result_quantity = base_qty + ((Math.floor((result_quantity - base_qty) / step) + 1) * step);
                    }
                    qty = result_quantity;

                    if ((virtual_item_qty > 1) && (virtual_item_step.length > 0)) {
                        let qty_step = (qty % virtual_item_qty);
                        let qty_index = $.inArray(qty_step, virtual_item_step);
                        if ((qty_step != 0) && (qty_index < 0)) {
                            $.each(virtual_item_step, function() {
                                if (this > qty_step) {
                                    qty_index = this;
                                    return false;
                                }
                            });
                            if (qty_index >= 0) {
                                qty = (qty + qty_index - qty_step);
                            } else {
                                qty = (virtual_item_qty * Math.ceil(qty / virtual_item_qty));
                            }
                        }
                    }
                }

                if (max !== false ) {
                    if ( qty >= max ) {
                        qty = max;
                        _this.trigger('qty_max', [qty, max]);
                        bigger.addClass('disabled');
                    }else{
                        bigger.removeClass('disabled')
                    }
                }
                if (min !== false) {
                    if (zero_allow) {
                        if (qty>0) {
                            smaller.removeClass('disabled');
                        }else{
                            qty = 0;
                            smaller.addClass('disabled');
                        }
                    }else {
                        if ( qty > min ) {
                            smaller.removeClass('disabled');
                        }else{
                            if ( _this.val()!=='' ) qty = min;
                            smaller.addClass('disabled');
                        }
                    }
                }

                let value_virtual_item_qty = qty;
                if (virtual_item_qty > 1) {
                    value_virtual_item_qty = (value_virtual_item_qty / virtual_item_qty).toFixed(2);
                }

                _this.val( value_virtual_item_qty );
                _this.data('value-real', qty);
                _this.data('last-value', qty);
                if ( old_value!==qty || direct_change ) {
                    delay(function() {
                        _this.trigger('change');
                        options.event();
                    });
                }
            });
            _this.on('reset_good_quantity', function(){
                _this.trigger('check_quantity',[_this.data('last-value') || 0]);
            });

            _this.on('blur', function(){
                if ( _this.val()==='' || isNaN(parseInt(_this.val(),10)) ) {
                    _this.trigger('reset_good_quantity');
                }
            });
            _this.on('keydown', function(e){
                if (e.keyCode === 27) {
                    _this.trigger('reset_good_quantity');
                    var new_value = _this.val().replace(((virtual_item_qty > 1) ?/[^0-9\.]/g : /[^0-9]/g), '');
                    _this.trigger('check_quantity', [new_value * virtual_item_qty, true]);
                    return false;
                }
                if (fast_step[e.key]) {
                    if ( !isNaN(parseInt(_this.val(),10)) ) {
                        var setVal = parseInt(_this.val(),10)+fast_step[e.key];
                        _this.val(setVal<0?0:setVal);
                        var new_value = _this.val().replace(((virtual_item_qty > 1) ?/[^0-9\.]/g : /[^0-9]/g), '');
                        _this.trigger('check_quantity', [new_value * virtual_item_qty, true]);
                    }
                    return false;
                }
                return true;
            });
            _this.on('keyup', function(e){
                var new_value = _this.val().replace(((virtual_item_qty > 1) ?/[^0-9\.]/g : /[^0-9]/g), '');

                let delayTime = 500;
                if (new_value === '') {
                    delayTime = 3000;
                }
                delay(function(){
                    _this.trigger('check_quantity', [new_value * virtual_item_qty, true]);
                }, delayTime);
            });

            _this.trigger('check_quantity');

        }
    })
};


$.fn.validate = function(options){
    op = $.extend({
        onlyCheck: false
    },options);
    $(this).closest('form').removeClass('not-valid');

    return this.each(function() {
        var _this = $(this);
        var message = _this.data('required');

        if (!message) return true;

        _this.on('check', {validateElement: _this}, validateFormElement);

        if (op.onlyCheck) return true;

        _this.addClass('validate-element');
        _this.on('change', {validateElement: _this}, validateFormElement);
        _this.on('keyup', {validateElement: _this}, validateFormElement);
        if (op.onlyCheck) {
            _this.on('change', {validateElement: _this}, validateFormElement);
        }

        _this.on('remove-validate', function(){
            _this.removeClass('validate-element');
            _this.off('change', validateFormElement);
            _this.removeClass('required-error');
            _this.next('.required-message-wrap').remove();
            _this.off('keyup', validateFormElement);
            if (op.onlyCheck) {
                _this.off('change', validateFormElement);
            }
        });

        _this.closest('form')
            .off('submit', validateForm)
            .on('submit', validateForm);
        _this.closest('form')
            .off('submit', validateScrollToError)
            .on('submit', validateScrollToError);
        _this.closest('form')
            .off('submit', validateFormSubmit)
            .on('submit', validateFormSubmit);

    })
};
function validateFormSubmit(event){
    if ($('.required-error').length > 0) {
        event.preventDefault()
    }
}
function validateForm(){
    $('input.validate-element, select.validate-element, textarea.validate-element', this).each(function(){
        validateFormElement({data: {validateElement: $(this)}})
    })
}
function validateFormElement(event){
    var _this = event.data.validateElement;
    var message = _this.data('required');
    var pattern = _this.data('pattern');
    var confirmation = _this.data('confirmation');
    var error = false;
    if (_this.hasClass('skip-validation')) return true;
    if (pattern){
        if (pattern === 'email'){
            pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        }
        if (_this.val().search(pattern) === -1){
            error = true;
        }
    } else if (confirmation){
        if (_this.closest('form').find(confirmation).val() !== _this.val()) {
            error = true;
        }
    } else if (_this.attr('type') === 'checkbox'){
        if (_this.prop("checked") === false) {
            error = true;
        }
    } else {
        if (!_this.val()) {
            error = true;
        }
    }
    if (error){
        const $requiredMessage = $(`<div class="required-message-wrap"><div class="required-message">${message}</div></div>`);
        $requiredMessage.on('mouseenter', function () {
            if ($(this).hasClass('top-error-mes')) {
                $(this).animate({'top': ''}, 100);
                $(this).removeClass('top-error-mes');
            } else {
                $(this).animate({'top': - _this[0].offsetHeight - $('> div', this).height()}, 100);
                $(this).addClass('top-error-mes');
            }
        });
        if (_this.attr('type') === 'checkbox'){
            var box = '';
            if (_this.parent().hasClass('bootstrap-switch-container')) {
                box = _this.closest('.bootstrap-switch').parent()
            } else {
                box = _this.parent()
            }
            box.addClass('required-error');
            box.after($requiredMessage);
            box.next().find('.required-message').hide().slideDown(300);

        } else if (!_this.hasClass('required-error')) {

            _this.addClass('required-error');
            _this.after($requiredMessage);
            _this.next().find('.required-message').hide().slideDown(300);
        }

        _this.closest('form').addClass('not-valid');
    } else {
        if (_this.attr('type') === 'checkbox'){
            var box = '';
            if (_this.parent().hasClass('bootstrap-switch-container')) {
                box = _this.closest('.bootstrap-switch').parent()
            } else {
                box = _this.parent()
            }
            box.removeClass('required-error');
            var this_next = box.next('.required-message-wrap');
            this_next.find('.required-message').slideUp(300, function(){
                this_next.remove()
            });

        } else {
            _this.removeClass('required-error');
            var this_next = _this.next('.required-message-wrap');
            this_next.find('.required-message').slideUp(300, function(){
                this_next.remove()
            });
        }
    }
}
function validateScrollToError(){
    if ($('.required-error', this).length > 0) {
        $('body, html').animate({'scrollTop': $('.required-error:first', this).offset().top - 100}, 500)
    }
}


$.fn.popUp = function(options){
    var op = jQuery.extend({
        beforeSend: function(){},
        overflow: false,
        box_class: false,
        one_popup: true,
        data: [],
        event: false,
        action: false,
        type: false,
        box: '<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>',
        dataType: 'html',
        success: function(data, popup_box){
            var n = $(window).scrollTop();
            $('.pop-up-content:last').html(data);
            $(window).scrollTop(n);
            op.position(popup_box)
        },
        close:  function(){
            $('.pop-up-close, .around-pop-up').click(function(){
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove();
                return false
            });
            $('.popup-box').on('click', '.btn-cancel', function(){
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap:last').remove();
                return false
            });
        },
        position: function(popup_box){
            var d = ($(window).height() - $('.popup-box').height()) / 2;
            if (d < 0) d = 0;
            $('.popup-box-wrap').css('top', $(window).scrollTop() + d);
        },
        opened: function(){}
    },options);

    var body = $('body');
    var html = $('html');


    return this.each(function() {
        if ($(this).hasClass('set-popup')){
            return false
        }
        $(this).addClass('set-popup');
        var _action = '';
        var _event = '';
        if ($(this).get(0).localName == 'a'){
            if (!op.event) _event = 'click';
            if (!op.action) _action = 'href'
        } else if ($(this).get(0).localName == 'form') {
            if (!op.event) _event = 'submit';
            if (!op.action) _action = 'action'
        }
        if (op.event == 'show') _event = 'load';

        jQuery(this).on(_event, function(event){
            event.preventDefault();
            if(op.one_popup){
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap').remove();
            }
            var url = '';
            if (op.action) {
                url = op.action;
            } else {
                url = $(this).attr(_action);
            }

            body.append(op.box);
            var popup_box = $('.popup-box:last');
            if($(this).attr('data-class'))popup_box.addClass($(this).attr('data-class'));
            if (op.box_class) popup_box.addClass(op.box_class);

            op.position(popup_box);
            var position_pp = function(){
                op.position(popup_box)
            };
            $(window).on('window.resize', position_pp);
            popup_box.on('popup.close', function(){
                $(window).off('window.resize', position_pp);
            });

            if (op.event == 'show'){
                op.success($(this).html(), popup_box);
                op.opened(_this);
            } else {
                var _data = {};
                if ($(this).get(0).localName == 'form'){
                    _data = $(this).serializeArray();
                    _data.push(op.data);
                    _data.push({name: 'popup', value: 'true'});
                } else {
                    //_data = $.extend({'ajax': 'true'}, op.data)
                    _data = $.extend($.extend(_data, op.data), op.beforeSend());
                }
                var _type = '';
                if (!op.type && $(this).get(0).localName == 'form') {
                    _type = $(this).attr('method')
                } else {
                    if (op.type != 'POST') {
                        _type = 'GET';
                    } else {
                        _type = 'POST';
                    }
                }
                if (op.dataType == 'jsonp') {
                    _data = 'encode_date='+base64_encode($.param(_data))
                }
                var _this = $(this);
                if (url.search('#') != -1){
                    op.success($(url).html(), popup_box);
                    op.opened(_this);
                }else{
                    $.ajax({
                        url: url,
                        data: _data,
                        dataType: op.dataType,
                        type: _type,
                        crossDomain: false,
                        success: function(data){
                            op.success(data, popup_box);
                            op.opened(_this);
                        }
                    });
                }

            }

            op.close();
            return false
        });
        $(this).trigger('load')

    })
};



$.fn.radioHolder = function(options){
    options = $.extend({
        holder: 'label'
    },options);

    var _this = this;

    return this.each(function() {
        var item = $(this).closest(options.holder);
        if($(this).is(':checked')){
            item.addClass('active');
        }else{
            item.removeClass('active');
        }
        $(this).on('change', function(){
            _this.each(function(){
                var item = $(this).closest(options.holder);
                if($(this).is(':checked')){
                    item.addClass('active');
                }else{
                    item.removeClass('active');
                }
            })
        })
    })
};


$.fn.rating = function( method, options ) {
    method = method || 'create';
    // This is the easiest way to have default options.
    var settings = $.extend({
        // These are the defaults.
        limit: 5,
        value: 0,
        glyph: "glyphicon-star",
        coloroff: "#ccc",
        coloron: "#183d78",
        size: "2.0em",
        cursor: "default",
        onClick: function () {},
        endofarray: "idontmatter"
    }, options );
    var style = "";
    style = style + "font-size:" + settings.size + "; ";
    style = style + "cursor:" + settings.cursor + "; ";

    if (method == 'create') {
        //this.html('');	//junk whatever was there

        //initialize the data-rating property
        this.each(function(){
            attr = $(this).attr('data-rating');
            if (attr === undefined || attr === false) { $(this).attr('data-rating',settings.value); }
        })
        //bolt in the glyphs
        for (var i = 0; i < settings.limit; i++){
            var rating_text = this.attr('data-text' + (i+1));
            if (typeof(rating_text) == "undefined" ) {
                rating_text = '';
            }

            this.append('<span title="' + rating_text + '" data-value="' + (i+1) + '" class="ratingicon glyphicon ' + settings.glyph + '" style="' + style + '" aria-hidden="true"></span>');
        }

        //paint
        this.each(function() { paint($(this)); });

    }
    if (method == 'set') {
        this.attr('data-rating',options);
        this.each(function() { paint($(this)); });
    }
    if (method == 'get') {
        return this.attr('data-rating');
    }
    //register the click events
    this.find("span.ratingicon").click(function() {
        rating = $(this).attr('data-value')
        $(this).parent().attr('data-rating',rating);
        paint($(this).parent());
        settings.onClick.call( $(this).parent() );
    })
    function paint(div) {
        rating = parseInt(div.attr('data-rating'));
        div.find("input").val(rating);	//if there is an input in the div lets set it's value
        var rating_text = div.attr('data-text' + rating);
        div.find('span.rating-description').remove();
        if (typeof(rating_text) != "undefined" ) {
            div.append(' <span class="rating-description">' + rating_text + '</span>');
        }

        div.find("span.ratingicon").each(function(){	//now paint the stars

            var rating = parseInt($(this).parent().attr('data-rating'));
            var value = parseInt($(this).attr('data-value'));
            if (value > rating) {
                $(this).removeClass('coloron')
            } else {
                $(this).addClass('coloron')
            }
        })
    }

};



function alertMessage(data, className){
    $('body').append('<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content ' + (className ? className : 'alert-message') + '"></div></div></div>');

    $('.pop-up-content:last').append(data)

    var d = ($(window).height() - $('.popup-box').height()) / 2;
    if (d < 0) d = 0;
    $('.popup-box-wrap').css('top', $(window).scrollTop() + d);

    $('.pop-up-close, .around-pop-up').click(function(){
        $('.popup-box-wrap:last').remove();
        return false
    });
    $('.popup-box').on('click', '.btn-cancel', function(){
        $('.popup-box-wrap:last').remove();
        return false
    });
}


function confirmMessage(message, func, okButton, cancelButton){
    if (!okButton) okButton = 'Ok';
    if (!cancelButton) cancelButton = 'Cancel';
    $('body').append('<div class="popup-box-wrap confirm-popup"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content">' +
        '<div class="confirm-text">'+message+'</div>' +
        '<div class="buttons"><span class="btn btn-cancel">'+cancelButton+'</span><span class="btn btn-default btn-success">'+okButton+'</span></div>' +
        '</div></div></div>');

    var popup_box = $('.popup-box:last');

    var d = ($(window).height() - popup_box.height()) / 2;
    if (d < 0) d = 0;
    $('.popup-box-wrap').css('top', $(window).scrollTop() + d);

    $('.btn-cancel, .pop-up-close', popup_box).on('click', function(){
        $('.popup-box-wrap:last').remove();
    });
    $('.btn-success', popup_box).on('click', function(){
        func();
        $('.popup-box-wrap:last').remove();
    })
}


$.fn.hideTab = function(){
    return this.each(function(){
        $(this).css({
            'padding': 0,
            'margin': 0,
            'height':0,
            'min-height':0,
            'overflow': 'hidden',
            'border': 'none'
        })
    })
};
$.fn.showTab = function(){
    return this.each(function(){
        $(this).removeAttr('style')
    })
};


jQuery.cookie = function(name, value, options) {
    if (typeof value != 'undefined') {
        options = options || {};
        if (value === null) {
            value = '';
            options.expires = -1;
        }
        var expires = '';
        if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
            var date;
            if (typeof options.expires == 'number') {
                date = new Date();
                date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
            } else {
                date = options.expires;
            }
            expires = '; expires=' + date.toUTCString();
        }
        var path = options.path ? '; path=' + (options.path) : '';
        var domain = options.domain ? '; domain=' + (options.domain) : '';
        var secure = options.secure ? '; secure' : '';
        document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
    } else { // only name given, get cookie
        var cookieValue = null;
        if (document.cookie && document.cookie != '') {
            var cookies = document.cookie.split(';');
            for (var i = 0; i < cookies.length; i++) {
                var cookie = jQuery.trim(cookies[i]);
                if (cookie.substring(0, name.length + 1) == (name + '=')) {
                    cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
                    break;
                }
            }
        }
        return cookieValue;
    }
};

/*used for products carousel on product info*/
if (typeof useCarousel != 'undefined'){
    if (useCarousel){
        var products_carousel = function(){
            var obj = {
                products_ids: [],
                prevLink: false,
                nextLink: false,
                init: function (){
                    if (!localStorage.hasOwnProperty('idsOnListing')){
                        localStorage.idsOnListing = '';
                    }
                    this.products_ids = new Array();
                    $(window).on('unload', this.saveItems)
                },
                reset:function(){
                    this.products_ids = new Array();
                },
                addItem: function(id, link, name, img, price){
                    var item = {
                        'id': id,
                        'link': link,
                        'name': name,
                        'img': img,
                        'price': price
                    };
                    this.products_ids.push(item);
                },
                saveItems: function(){
                    localStorage.idsOnListing = JSON.stringify(obj.products_ids);
                },
                restoreItems:function(){
                    if (localStorage.hasOwnProperty('idsOnListing') && localStorage.idsOnListing.length){
                        data = JSON.parse(localStorage.idsOnListing);
                        obj.reset();
                        if (Array.isArray(data)){
                            data.map(function(e, i){
                                obj.addItem(e.id, e.link, e.name, e.img, e.price);
                            });
                        }
                    }
                },
                fetchPrevLink: function(pid){
                    var link = false;
                    this.products_ids.map(function(e,i){
                        if (e.id != pid){
                            link = e;
                        } else {
                            obj.prevLink = link;
                        }
                    });
                },
                fetchNextLink: function(pid){
                    var link = false;
                    var ready = false;
                    this.products_ids.map(function(e,i){
                        if (e.id == pid){
                            ready = true;
                        }
                        if (ready){
                            if (e.id != pid){
                                obj.nextLink = e;
                                ready = false;
                            }
                        }
                    });
                },
                buildCursor: function(id){
                    this.fetchPrevLink(id);
                    this.fetchNextLink(id);
                    this.sameCategoryProducts(id);
                    if (this.prevLink){
                        $('body').append('<a href="'+this.prevLink.link+'" class="prev-next-product prev-product">\
                            <span class="pn-direction">' + window.tr.PREVIOUS_PRODUCT + '</span>\
                            <span class="pn-image">' + this.prevLink.img + '</span>\
                            <span class="pn-name">' + this.prevLink.name + '</span>\
                            <span class="pn-price">' + this.prevLink.price + '</span>\
                            </a>');
                    }
                    if (this.nextLink){
                        $('body').append('<a href="'+this.nextLink.link+'" class="prev-next-product next-product">\
                            <span class="pn-direction">' + window.tr.NEXT_PRODUCT + '</span>\
                            <span class="pn-image">' + this.nextLink.img + '</span>\
                            <span class="pn-name">' + this.nextLink.name + '</span>\
                            <span class="pn-price">' + this.nextLink.price + '</span>\
                            </a>');
                    }
                },
                sameCategoryProducts: function(id){
                    if (this.prevLink || this.nextLink) {
                        return '';
                    }

                    $.get('catalog/same-category-products', {id: id}, function(response){
                        response.forEach(function(d){
                            obj.addItem(d.id, d.link, d.name, d.img, d.price)
                        })
                        obj.fetchPrevLink(id);
                        obj.fetchNextLink(id);
                    }, 'json')
                }
            };
            return obj;
        }

        window.pCarousel = new products_carousel();
        pCarousel.init();
    }
}
/*used for products carousel on product info*/

var getProductsList = function() {

    var listing = $('.products-listing');
    if (listing.length > 0) {
        var pos = listing.offset();
        var width = listing.width();
        var height = listing.height();
        $('body')
            .append('<div class="filter-listing-loader"></div>')
            .append('<div class="filter-listing-preloader preloader"></div>');
        var preloader = $('.filter-listing-preloader');
        var loader = $('.filter-listing-loader');
        loader.css({
            left: pos.left,
            top: pos.top,
            width: width,
            height: height
        });
        var preloader_h2 = preloader.height() / 2;
        var preloader_w2 = preloader.width() / 2;
        var top = $(window).scrollTop() + ($(window).height() / 2 - preloader_h2);
        if (top < pos.top + preloader_h2) top = pos.top + preloader_h2;
        preloader.css({
            left: pos.left + (width / 2 - preloader_w2),
            top: top
        });
    }

    var _this = $(this);
    var url = _this.attr('action');
    if (typeof url == 'undefined') {
        url = _this.attr('href')
    }
    $.ajax({
        url: url,
        data: _this.serializeArray(),
        dataType: 'html',
        success: function(d){
            window.compare_key = 0;
            if (useCarousel){ pCarousel.reset(); }
            $('.main-content').html(d);
            var filters_url_full = $('#filters_url_full');
            if (filters_url_full.length>0 && filters_url_full.val().length > 0) {
                window.history.pushState(_this.serializeArray(), '', filters_url_full.val());
            } else {
                var new_url = '';
                if ( _this.attr('action') ) {
                    new_url = _this.attr('action');
                    var form_params = _this.serialize();
                    if ( form_params ) {
                        new_url += (new_url.indexOf('?')===-1?'?':'&')+form_params;
                    }
                }else{
                    new_url = _this.attr('href') || window.location.href;
                }
                window.history.pushState(_this.serializeArray(), '', new_url);
            }
            loader.remove();
            preloader.remove();
        }
    });

    return false
};

function isValidEmailAddress(emailAddress) {
    var pattern = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.) {2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
    return pattern.test(emailAddress);
}


$.fn.scrollBox = function(options){
    options = $.extend({
        marginTop: 20,
        marginBottom: 20,
        windowMaxWidth: 1024,
        differentHeight: 50
    },options);

    return this.each(function() {
        var scrollBox = $(this);
        var parentBox = scrollBox.parent().parent();
        var top;
        var left;
        var width;
        var height;
        var parentHeight;
        var bottom;
        var windowWidth;
        var recalculate = true;

        setTimeout(function(){
            boxPosition();
        }, 100);

        $(window).on('scroll resize', boxPosition);

        function setSizes(){
            if (recalculate) {
                top = scrollBox.parent().offset().top;
                left = scrollBox.offset().left;
                width = scrollBox.width();
                height = scrollBox.height();
                parentHeight = scrollBox.parent().parent().height();
                bottom = parentBox.offset().top + parentBox.height();
                windowWidth = $(window).width();
                recalculate = false;
                setTimeout(() => recalculate = true, 500)
            }
        };

        function boxPosition(){
            setSizes();
            var scroll = $(window).scrollTop();
            height = scrollBox.height();
            if (
                scroll >= top - options.marginTop
                && windowWidth > options.windowMaxWidth
                && height < parentHeight - options.differentHeight
            ) {
                scrollBox.css({
                    'position': 'fixed',
                    'width': width,
                    'left': left,
                    'top': options.marginTop,
                    'margin-left': 0,
                    'box-sizing': 'content-box'
                });
                if (scroll + height + options.marginTop >= bottom - options.marginBottom) {
                    scrollBox.css({
                        'top': bottom - height - scroll - options.marginBottom
                    });
                }
            } else {
                scrollBox.css({
                    'position': '',
                    'width': '',
                    'left': '',
                    'top': '',
                    'margin-left': '',
                    'box-sizing': ''
                })
            }
        };
    })
};

$.fn.showPassword = function(){
    return this.each(function() {
        var $input = $(this)
        if ($input.hasClass('eye-applied')) {
            return '';
        }
        $input.addClass('eye-applied');

        var $eye = $('<span class="eye-password"></span>');
        var $eyeWrap = $('<span class="eye-password-wrap"></span>');
        $eyeWrap.append($eye)
        $input.before($eyeWrap);
        $eye.on('click', function(){
            if ($input.attr('type') === 'password') {
                $eye.addClass('eye-password-showed');
                $input.attr('type', 'text')
            } else {
                $eye.removeClass('eye-password-showed');
                $input.attr('type', 'password')
            }
        })
    })
};