//main.js

var delayKeyUp = (function(){
/// Important - do not use this in the callback function (undefined)
    var timer = 0;
    return function(callback, ms){
        clearTimeout (timer);
        timer = setTimeout(callback, ms);
    };
})();

$.fn.popUp = function(options){
    var op = jQuery.extend({
        overflow: false,
        box_class: false,
        one_popup: true,
        data: [],
        event: false,
        action: false,
        type: false,
        only_show:false,
        box: '<div class="popup-box-wrap"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>',
        dataType: 'html',
        loaded: function(){},
        success: function(data, $popupBox){
            const scrollTop = $(window).scrollTop();
            $('.pop-up-content', $popupBox).html(data);
            $(window).scrollTop(scrollTop);
            op.position($popupBox);
            op.loaded();
            const inp = $popupBox.find('input:visible:first').focus().get(0);
            if (inp) {
                const val = inp.value;
                inp.value = '';
                inp.value = val;
            }
        },
        close:  function($box){
            $('.pop-up-close', $box).click(function(){
                $('.popup-box', $box).trigger('popup.close');
                $box.remove();
            });
            $box.on('click', '.btn-cancel', function(e){
                e.preventDefault();
                $('.popup-box', $box).trigger('popup.close');
                $box.remove();
            });
        },
        position: function(popupBox){
            if (options && options.name) {
                const popupSettingsVal = popupSettings(options.name);
                if (popupSettingsVal) {
                    let translateX = '0px';
                    let translateY = '0px';
                    if (popupSettingsVal.x) {
                        popupBox.attr('data-x', popupSettingsVal.x);
                        translateX = popupSettingsVal.x + 'px';
                    }
                    if (popupSettingsVal.y) {
                        if (popupSettingsVal.y + options.top < 0) {
                            popupSettingsVal.y = -options.top;
                        }
                        popupBox.attr('data-y', popupSettingsVal.y);
                        translateY = popupSettingsVal.y + 'px';
                    }

                    popupBox.css('transform', `translate(${translateX}, ${translateY})`);
                    if (popupSettingsVal.width) {
                        popupBox.css('width', popupSettingsVal.width + 'px');
                    }
                    if (popupSettingsVal.height) {
                        popupBox.css('height', popupSettingsVal.height + 'px');
                    }
                }
            }

            let d = ($(window).height() - popupBox.height()) / 2;
            if (d < 0) {
                d = 30;
            }
            $('.popup-box-wrap').css('top', $(window).scrollTop() + d);
        },
        opened: function(){},
        draggable: {
            allowFrom: '.popup-heading',
            listeners: {
                move: function (event) {
                    console.log(1111);
                    var target = event.target;
                    // keep the dragged position in the data-x/data-y attributes
                    var x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                    var y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;

                    // translate the element
                    target.style.transform = 'translate(' + x + 'px, ' + y + 'px)';

                    // update the posiion attributes
                    target.setAttribute('data-x', x);
                    target.setAttribute('data-y', y);

                    if (options && options.name) {
                        popupSettings(options.name, {x, y});
                    }
                },
            }
        },
        resizable: {
            // resize from all edges and corners
            edges: { left: true, right: true, bottom: true, top: true },

            listeners: {
                move (event) {
                    var target = event.target;
                    var x = (parseFloat(target.getAttribute('data-x')) || 0);
                    var y = (parseFloat(target.getAttribute('data-y')) || 0);

                    // update the element's style
                    target.style.width = event.rect.width + 'px';
                    target.style.height = event.rect.height + 'px';

                    // translate when resizing from top or left edges
                    x += event.deltaRect.left;
                    y += event.deltaRect.top;

                    target.style.transform = 'translate(' + x + 'px,' + y + 'px)';

                    target.setAttribute('data-x', x);
                    target.setAttribute('data-y', y);

                    if (options.name) {
                        popupSettings(options.name, {x, y, width: event.rect.width, height: event.rect.height})
                    }
                }
            },
        },
        className: '',
        top: 200,
        width: false,
        height: false,
        name: false,
        interact: false
    },options);

    var body = $('body');

    return this.each(function() {
        var _action = '';
        var _event = '';
        if ($(this)[0].localName == 'a'){
            if (!op.event) {
                _event = 'click';
            }
            if (!op.action) {
                _action = 'href';
            }
        } else if ($(this)[0].localName == 'form') {
            if (!op.event) {
                _event = 'submit';
            }
            if (!op.action) {
                _action = 'action';
            }
        }
        if (op.event == 'show') {
            _event = 'load';
        }

        jQuery(this).off(_event).on(_event, function(e){
            e.preventDefault();
            if(op.one_popup){
                $('.popup-box:last').trigger('popup.close');
                $('.popup-box-wrap').remove();
            }
            let url = '';
            if (op.action) {
                url = op.action;
            } else {
                url = $(this).attr(_action);
            }

            const $box = $(op.box);
            const $popupBox = $('.popup-box', $box);
            body.append($box);
            if($(this).attr('data-class')) {
                $popupBox.addClass($(this).attr('data-class'));
            }
            if (op.box_class) {
                $popupBox.addClass(op.box_class);
            }
            if (op.className) {
                $popupBox.addClass(op.className);
            }

            if (op.interact && interact ) {
                $popupBox.addClass('interact');
                const interactBox = interact($popupBox[0]);
                interactBox.draggable(op.draggable);
                interactBox.resizable(op.resizable);
            }

            op.position($popupBox);
            $(window).on('window.resize', () => op.position($popupBox));
            $popupBox.on('popup.close', function(){
                $(window).off('window.resize', () => op.position($popupBox));
            });
            if (op.event === 'show' && !op.only_show) {
                op.success($(this).html(), $popupBox);
            } else if (op.event === 'show' && op.only_show) {
                op.success(op.data, $popupBox);
            } else {
                let _data = [];
                if ($(this)[0].localName === 'form'){
                    _data = $(this).serializeArray();
                    _data.push(op.data);
                    _data.push({name: 'popup', value: 'true'});
                } else {
                    _data = op.data;
                }
                var _type = '';
                if (!op.type && $(this)[0].localName === 'form') {
                    _type = $(this).attr('method');
                } else {
                    _type = 'GET';
                }
                if (op.dataType === 'jsonp') {
                    _data = 'encode_date='+base64_encode($.param(_data));
                }

                const _this = $(this);
                if (url.search('#') !== -1) {
                    op.success($(url).html(), $popupBox);
                    op.opened(_this);
                }else{
                    $.ajax({
                        url: url,
                        data: _data,
                        dataType: op.dataType,
                        type: _type,
                        crossDomain: false,
                        success: function(data){
                            op.success(data, $popupBox);
                            op.opened(_this);
                        }
                    });
                }
            }

            op.close($box);
            return false;
        });
        $(this).trigger('load');

    });

    function popupSettings(popupName, val = null){
        const popupSettingsString = localStorage.getItem('popupSettings');

        let popupSettings = {};
        if (popupSettingsString) {
            popupSettings = JSON.parse(popupSettingsString);
            if (!popupSettings) {
                popupSettings = {};
            }
        }

        if (val === null) {
            if (popupSettings[popupName]) {
                return popupSettings[popupName];
            } else {
                return null;
            }
        }

        if (!popupSettings[popupName]) {
            popupSettings[popupName] = {};
        }

        popupSettings[popupName] = $.extend(true, popupSettings[popupName], val);

        localStorage.setItem('popupSettings', JSON.stringify(popupSettings));
    }
};


$.fn.validate = function(options){
    var op = $.extend({
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

function alertMessage(data, className){
    const $pupUp = $('<div class="popup-box-wrap ' + (className ? className + '-wrap' : '') + '"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content ' + (className ? className : '') + '"></div></div></div>');
    $('body').append($pupUp);

    $('.pop-up-content', $pupUp).append(data);

    let d = ($(window).height() - $('.popup-box', $pupUp).height()) / 2;
    if (d < 0) d = 0;
    $pupUp.css('top', $(window).scrollTop() + d);

    const $inp =  $('.popup-box', $pupUp).find('input:visible:first').focus().get(0);
    if ($inp) {
        const val = $inp.value;
        $inp.value = '';
        $inp.value = val;
    }

    $('.pop-up-close, .around-pop-up', $pupUp).click(function(e){
        e.preventDefault();
        setTimeout(() => $pupUp.remove(), 0);
    });
    $('.popup-box', $pupUp).on('click', '.btn-cancel', function(e){
        e.preventDefault();
        setTimeout(() => $pupUp.remove(), 0);
    });

    return $pupUp;
}


$.fn.uploads = function(options){
    var option = jQuery.extend({
        overflow: false,
        box_class: false,
        acceptedFiles: ''
    },options);

    var body = $('body');
    var html = $('html');

    return this.each(function() {

        var _this = $(this);

        var preload = _this.data('preload');
        if (preload == undefined) {
            var img = _this.data('img');
            if (img != undefined) {
                preload = img;
            } else {
                preload = '';
            }
        }

        var $TRANSLATE = {'title':'Drop files here', 'button': 'Upload', 'or': 'or'};
        if (typeof $tranlations == 'object'){
            if ($tranlations.hasOwnProperty('FILEUPLOAD_TITLE')) $TRANSLATE.title = $tranlations.FILEUPLOAD_TITLE;
            if ($tranlations.hasOwnProperty('FILEUPLOAD_BUTTON')) $TRANSLATE.button = $tranlations.FILEUPLOAD_BUTTON;
            if ($tranlations.hasOwnProperty('FILEUPLOAD_OR')) $TRANSLATE.or = $tranlations.FILEUPLOAD_OR;
        }

        _this.html('\
    <div class="upload-file-wrap">\
      <div class="upload-file-template external_images-hide">'+$TRANSLATE.title+'<br>'+$TRANSLATE.or+'<br><span class="btn">'+$TRANSLATE.button+'</span></div>\
      <div class="upload-file"></div>\
      <div class="upload-hidden"><input type="hidden" name="'+_this.data('name')+'" value="'+preload+'" /></div>\
    </div>');

        var linked = _this.data('linked');

        var value = _this.data('value');
        if (value != undefined && value !="") {
            $('.upload-file', _this).append('<div class="dz-details external_images-hide"><img src="'+value+'" /><div onclick="uploadRemove(this, \''+show+'\', \''+linked+'\')" class="upload-remove"></div></div>')
        }
        if ( _this.data('external-image') ) {
            $('.upload-file', _this).append('<div class="dz-details external_images-show"><img src="'+_this.data('external-image')+'" /></div>');
        }

        var url = _this.data('url');
        if (url == undefined || url =="") {
            url = "upload/index";
        }

        var show = _this.data('show');
        var myDropzone = new Dropzone($('.upload-file', _this).get(0), {
            url: url,
            maxFiles: 1,
            acceptedFiles: option.acceptedFiles,
            uploadMultiple: false,
            thumbnailWidth: 146,
            thumbnailHeight: 146,
            sending:  function(e, data) {
                $('.upload-hidden input[type="hidden"]', _this).val(e.name).trigger('change');
                $('.upload-remove', _this).on('click', function(){
                    $('.upload-hidden input[type="hidden"]', _this).val('').trigger('change');
                    _this.trigger('upload-remove')
                    $('.dz-details', _this).remove();
                    if (show != undefined) {
                        $('#'+show).text(' ');
                    }
                    if (linked != undefined) {
                        $('#'+linked).hide();
                    }
                })
            },
            dataType: 'json',
            previewTemplate: '<div class="dz-details"><img data-dz-thumbnail /><div class="upload-remove"></div></div>',
            drop: function(){
                $('.upload-file', _this).html('');
            },
            success: function(e, data) {
                if (show != undefined) {
                    $('#'+show).text(e.name)
                }
                if (linked != undefined) {
                    uploadSuccess(linked, e.name);

                }
                _this.trigger('upload');

                if (e.name.slice(-3) == 'zip') {
                    $('.dz-details', _this).addClass('zip-ico')
                } else {
                    $('.dz-details', _this).removeClass('zip-ico')
                }
            },
            uploadprogress: function(file, progress, bytesSent) {
                if (file.previewElement) {
                    var byteInfo = '';
                    if (bytesSent > 1000000) {
                        byteInfo = Math.round(bytesSent/100000)/10 + 'MB';
                    } else if (bytesSent > 1000) {
                        byteInfo = Math.round(bytesSent/100)/10 + 'KB';
                    } else {
                        byteInfo = Math.round(bytesSent) + 'B';
                    }
                    var percent = Math.round(progress) + '%';

                    var $uploadBox = $(file.previewElement).closest('.upload-image');
                    $uploadBox.find('.upload-progress').css('display', 'flex');
                    $uploadBox.find('.upload-progress-bar-content').width(progress + '%');
                    $uploadBox.find('.upload-progress-val').html(byteInfo);
                    $uploadBox.find('.upload-progress-percent').html(percent);
                }
            }
        });

        _this.on('destroy', function(){
            myDropzone.destroy()
        })

    })
};

$.fn.openCloseWidget = function(options){
    var option = jQuery.extend({
        speed: 200,
    },options);

    return this.each(function() {
        const widget         = $(this).closest(".widget");
        const widgetContent = widget.children(".widget-content:first");
        const widgetChart   = widget.children(".widget-chart:first");
        const divider        = widget.children(".divider:first");
        const widgetId       = widget.attr('id');
        const $i       = $(this).children('i');

        const widgetStatuses = JSON.parse(localStorage.getItem('widgetStatuses'));
        if (widgetStatuses && widgetStatuses[widgetId] == 'closed'){
            closeWidget()
        } else if (widgetStatuses && widgetStatuses[widgetId] == 'opened') {
            openWidget()
        }

        $(this).on('click', function () {
            if (widget.hasClass('widget-closed')) {
                openWidget()
            } else {
                closeWidget()
            }
        })

        function openWidget() {
            if (widgetId) {
                let widgetStatuses = {};
                widgetStatuses = JSON.parse(localStorage.getItem('widgetStatuses'))
                if (!widgetStatuses) widgetStatuses = {};
                widgetStatuses[widgetId] = 'opened';
                localStorage.setItem('widgetStatuses', JSON.stringify(widgetStatuses));
            }

            $i.removeClass('icon-angle-up').addClass('icon-angle-down');
            widgetContent.slideDown(option.speed, function() {
                widget.removeClass('widget-closed');
            });
            widgetChart.slideDown(option.speed);
            divider.slideDown(option.speed);
        }
        function closeWidget() {
            if (widgetId) {
                let widgetStatuses = {};
                widgetStatuses = JSON.parse(localStorage.getItem('widgetStatuses'))
                if (!widgetStatuses) widgetStatuses = {};
                widgetStatuses[widgetId] = 'closed';
                localStorage.setItem('widgetStatuses', JSON.stringify(widgetStatuses));
            }

            $i.removeClass('icon-angle-down').addClass('icon-angle-up');
            widgetContent.slideUp(option.speed, function() {
                widget.addClass('widget-closed');
            });
            widgetChart.slideUp(option.speed);
            divider.slideUp(option.speed);
        }
    })
}

$.popUpConfirm = function(message, func){
    $('body').append('<div class="popup-box-wrap confirm-popup"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content">' +
        '<div class="confirm-text">'+message+'</div>' +
        '<div class="buttons"><span class="btn btn-cancel">Cancel</span><span class="btn btn-default btn-success">Ok</span></div>' +
        '</div></div></div>');

    var popup_box = $('.popup-box');

    var d = ($(window).height() - popup_box.height()) / 2;
    if (d < 0) d = 0;
    $('.popup-box-wrap').css('top', $(window).scrollTop() + d);

    $('.btn-cancel').on('click', function(){
        $('.popup-box-wrap:last').remove();
    });
    $('.btn-success').on('click', function(){
        func();
        $('.popup-box-wrap:last').remove();
    });

};


$.fn.galleryImage = function(baseUrl, type, path = ''){
    return this.each(function(){
        $(this).on('click', function(){
            var _this = $(this);
            var $TRANSLATE = {'themes_folder':'Files from themes folder', 'general_folder': 'Files from general folder', 'all_files': 'All files'};
            if (typeof $tranlations == 'object'){
                if ($tranlations.hasOwnProperty('TEXT_THEMES_FOLDER')) $TRANSLATE.themes_folder = $tranlations.TEXT_THEMES_FOLDER;
                if ($tranlations.hasOwnProperty('TEXT_GENERAL_FOLDER')) $TRANSLATE.general_folder = $tranlations.TEXT_GENERAL_FOLDER;
                if ($tranlations.hasOwnProperty('TEXT_ALL_FILES')) $TRANSLATE.all_files = $tranlations.TEXT_ALL_FILES;
            }
            var name = $(this).data('name');
            if (name == undefined) name = 'params';
            var theme_name = $(this).closest('form').find('input[name="theme_name"]').val();
            var filter = '';
            if (!theme_name) {
                theme_name = '';
            } else {
                filter = '<select class="form-control folder-name" name="folder_name"><option value="3">'+$TRANSLATE.themes_folder+'</option><option value="2">Files from general folder</option><option value="1">All files</option></select>';
            }
            $.get(baseUrl + '/design/gallery', { type, theme_name, path}, function(d){
                $('body').append('<div class="images-popup"><div class="close"></div><div class="search"><input type="text" class="form-control">'+filter+'</div><div class="image-content">'+d+'</div></div>');
                $('.images-popup .item-general').hide();
                $('.images-popup .item').on('click', function(){
                    var img = $('.name', this).text();
                    var path = $('.name', this).data('path');
                    if (!path) path = '';
                    $('input[name="'+name+'"]').val(path + img).trigger('change');
                    $('.images-popup').remove();
                    $('input[name="uploads"]').remove();
                    _this.trigger('choose-image');
                    if (name == 'params'){
                        $('.show-image').attr('src', baseUrl+'/../' + path + img)
                    } else {
                        $('.show-image[data-name="'+name+'"]').attr('src', baseUrl+'/../' + path + img).closest('video').trigger('load')
                    }
                });
                $('.images-popup .close').on('click', function(){
                    $('.images-popup').remove()
                });

                if (!theme_name){
                    $('.images-popup .item').show();
                    $('.images-popup .item-themes').hide();
                }

                $('.images-popup .search .folder-name').on('change', function(){
                    if ($(this).val() == 1){
                        $('.images-popup .item').show()
                    }
                    if ($(this).val() == 2){
                        $('.images-popup .item').show();
                        $('.images-popup .item-themes').hide();
                    }
                    if ($(this).val() == 3){
                        $('.images-popup .item').show();
                        $('.images-popup .item-general').hide();
                    }
                })

                $('.images-popup .search input').on('keyup', function(){
                    var val = $(this).val();

                    $('.images-popup .name').each(function(){
                        if ($(this).text().search(val) != -1){
                            $(this).parent().show()
                        } else {
                            $(this).parent().hide()
                        }
                    });

                    if (val == '') $('.images-popup .item').show();

                    if ($('.images-popup .search .folder-name').val() == 2){
                        $('.images-popup .item-themes').hide();
                    }
                    if ($('.images-popup .search .folder-name').val() == 3){
                        $('.images-popup .item-general').hide();
                    }
                })
            })
        })
    })
};

$.fn.quantity = function(options){
    options = $.extend({
        min: 1,
        max: false,
        step: 1,
        event: function(){}
    },options);

    return this.each(function() {
        var _this = $(this);
        if (!_this.parent().hasClass('qty-box')) {
            var min = 0;
            var max = 0;
            var step = 0;
            if (_this.attr('data-min')) min = parseInt(_this.attr('data-min'),10);
            else min = options.min;
            if (_this.attr('data-max')) max = parseInt(_this.attr('data-max'),10);
            else max = options.max;
            if (_this.attr('data-step')) step = parseInt(_this.attr('data-step'),10);
            else step = options.step;

            if (min !== false && max !== false && min > max){
                _this.attr('data-error', 'min > max');
                return false;
            }
            _this.wrap('<span class="qty-box"></span>');
            var qtyBox = _this.closest('.qty-box');
            qtyBox.prepend('<span class="smaller"></span>');
            var smaller = $('.smaller', qtyBox)
            qtyBox.append('<span class="bigger"></span>');
            var bigger = $('.bigger', qtyBox);
            var qty = _this.val();
            if (max !== false && qty*1 >= max*1){
                bigger.addClass('disabled');
            }
            if (min !== false && qty <= min){
                smaller.addClass('disabled');
            }

            _this.on('changeSettings', function(){
                if (_this.attr('data-min')) min = parseInt(_this.attr('data-min'),10);
                else min = options.min;
                if (_this.attr('data-max')) max = parseInt(_this.attr('data-max'),10);
                else max = options.max;
                if (_this.attr('data-step')) step = parseInt(_this.attr('data-step'),10);
                else step = options.step;
            });

            _this.on('focus',function(){
                this.select();
            });

            bigger.on('click', function(){
                qty = parseInt(_this.val(),10);
                if (!$(this).hasClass('disabled')) {
                    qty = qty + step;
                    if (max !== false && qty >= max) {
                        qty = max;
                        bigger.addClass('disabled');
                    }
                    if (min !== false && qty > min) {
                        smaller.removeClass('disabled');
                    }
                    _this.val(qty).trigger('change');
                    options.event();
                }
            });

            smaller.on('click', function(){
                qty = _this.val();
                if (!$(this).hasClass('disabled')) {
                    qty = qty - step;
                    if (min !== false && qty <= min) {
                        qty = min;
                        smaller.addClass('disabled');
                    }
                    if (max !== false && qty < max) {
                        bigger.removeClass('disabled');
                    }
                    _this.val(qty).trigger('change');
                    options.event();
                }
            });

            _this.on('check_quantity',function(){
                var qty = parseInt(_this.val(),10);
                if ((qty % step)!=0){
                    qty = Math.floor(qty / step)*step + step;
                    if (max !== false ) {
                        if ( qty >= max ) {
                            qty = max;
                            bigger.addClass('disabled');
                        }else{
                            bigger.removeClass('disabled')
                        }
                    }
                    if (min !== false) {
                        if ( qty > min ) {
                            smaller.removeClass('disabled');
                        }else{
                            smaller.addClass('disabled');
                        }
                    }
                    _this.val( qty ).trigger('change');
                    options.event();
                }
            });

            _this.on('keyup', function(){
                _this.val(_this.val().replace(/[^0-9]/g, ''));

                delayKeyUp(function(){ ///2test CAN'T test as there isn't the input in admin.
                    _this.trigger('check_quantity');
                }, 2000);
            });

            if ( _this.val()>0 ) {
                _this.trigger('check_quantity');
            }
        }
    })
};

document.addEventListener("keydown", function(e) {
    if($('.content-container form textarea').hasClass('ckeditor')){
        if (e.keyCode == 83 && (navigator.platform.match("Mac") ? e.metaKey : e.ctrlKey)) {
            e.preventDefault();
            $('.content-container form button').click();
        }
    }
}, false);

function filters_height_block() {
    setTimeout(function(){
        var maxHeight = 0;
        $(".item_filter").each(function () {
            if ($(this).height() > maxHeight)
            {
                maxHeight = $(this).height();
            }
        });
        $(".item_filter").css('min-height', maxHeight);
    }, 1000);
}

$.extend({
    getUrlVars: function(){
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for(var i = 0; i < hashes.length; i++)
        {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    },
    getUrlVar: function(name){
        return $.getUrlVars()[name];
    }
});

function choose_platform_filter() {
    setTimeout(function () {
        if ($('.choose_platform span').hasClass('checked')) {
            $('.choose_platform .tl_filters_title').addClass('active_options');
        } else {
            $('.choose_platform .tl_filters_title').removeClass('active_options');
        }
    }, 200);
}

function choose_select_filter(){
    $('.widget-fixed select').each(function(){
        if($(this).val() != ''){
            $(this).siblings('label').addClass('active_options');
        }else{
            $(this).siblings('label').removeClass('active_options');
        }
    });
}

function choose_input_filter(){
    $('.widget-fixed input[type="text"]').each(function(){
        if($(this).val() != ''){
            $(this).siblings('label').addClass('active_options');
        }else{
            $(this).siblings('label').removeClass('active_options');
        }
    });
}

$(document).ready(function(){
    $('body').on('click', 'a[data-toggle="tab"]', e => e.preventDefault());
    $('.tl-all-pages-block ul [data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        // click on "all pages" tab - activate tab and scroll
        var listId = $(this).parent().attr('id') ;
        if (typeof listId !== "undefined") {
            listId = listId.replace(/_scr$/, '');
            $('#' + listId + ' li.active').removeClass('active');
            $('#' + listId + ' [data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
        } else {
            $('.nav-tabs-scroll li.active').removeClass('active');
            $('.nav-tabs-scroll [data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
        }
        $('.nav-tabs-scroll').scrollingTabs('refresh');
    });

    $('.nav-tabs-scroll [data-bs-toggle="tab"]').on('shown.bs.tab', function () {
        var listId = $(this).parent().attr('id') ;
        if (typeof listId !== "undefined") {
            $('#' + listId + '_scr li.active').removeClass('active');
            $('#' + listId + '_scr [data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
        } else {
            $('.tl-all-pages-block ul li.active').removeClass('active');
            $('.tl-all-pages-block ul li[data-bs-target="' + $(this).data('bs-target') + '"]').addClass('active');
        }
    });

    $('.nav-tabs-scroll').scrollingTabs({
        bootstrapVersion: 5,
    }).on('ready.scrtabs', function () {
        $('.tab-content').show();
    });
    $('[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('.nav-tabs-scroll').scrollingTabs('refresh');
    });
    $('.tp-all-pages-btn-wrapp').hover(function() {
        var parentWidth = $(this).parent().parent().css('width');
        $(this).next().css('max-width', parentWidth);
    });

    $('body').on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function () {
        $(this).parent('li').addClass('active')
    });
    $('body').on('hidden.bs.tab', 'a[data-bs-toggle="tab"]', function () {
        $(this).parent('li').removeClass('active')
    });

    if($('.widget').hasClass('widget-fixed')){
        $(window).scroll(function(){
            var height_head = $('.header.navbar-fixed-top').height() + $('.top_header').height();
            if($(window).scrollTop() > height_head){
                $('.widget-fixed .widget-header').addClass('widget-fixed-top');
                $('.widget-fixed .widget-header.widget-fixed-top').css('top', height_head);
                $('.widget-fixed .widget-header.widget-fixed-top').css('width', $('.content-container').width());
                $(window).resize(function() {
                    $('.widget-fixed .widget-header.widget-fixed-top').css('width', $('.content-container').width());
                });
            }else{
                $('.widget-fixed .widget-header').removeClass('widget-fixed-top');
                $('.widget-fixed .widget-header').css('top', 'auto');
                $('.widget-fixed .widget-header').css('width', '100%');
            }
        });
    }

    if($('.widget-content > form > div').hasClass('wrap_filters')){
        filters_height_block();
        $('.toolbar').click(function(){
            filters_height_block();
            var filters_status = $.getUrlVar('fs');
            if(filters_status == 'open'){
                $('input[name="fs"]').val('closed');
            }else{
                $('input[name="fs"]').val('open');
            }
            setFilterState();
        });
    }

    var filters_status = $.getUrlVar('fs');
    if(filters_status == 'open'){
        $('.widget.box').removeClass('widget-closed');
        $('.widget.box .widget-header .toolbar.no-padding .btn i').removeClass('icon-angle-up');
        $('.widget.box .widget-header .toolbar.no-padding .btn i').addClass('icon-angle-down');
        filters_height_block();
    }

    /* Choose options filters */
    choose_select_filter();
    choose_platform_filter();
    choose_input_filter();
    $('.choose_platform input[type="checkbox"]').click(function(){
        choose_platform_filter();
    });
    $('.widget-fixed select').change(function(){
        choose_select_filter();
    });
    $('.widget-fixed input[type="text"]').change(function(){
        choose_input_filter();
    });

    /* End choose options filters */

    /* {{ textInputNullable */
    let inputNullableTmpVal = '';
    $(document).on('click', '.js-main-text-input-nullable .js-input-nullable-edit', function(event){
        const $group = $(event.target).parents('.js-main-text-input-nullable');
        const $input = $group.find('input');
        inputNullableTmpVal = $input.val();
        $input.removeAttr('readonly');
        $input.trigger('focus')
        $('.js-input-nullable-edit', $group).hide();
        $('.js-input-nullable-close', $group).show();
        $('.js-input-nullable-save', $group).show();
        $('.js-input-nullable-undo', $group).show();
        $('.js-input-nullable-default', $group).show();
    });
    $(document).on('click', '.js-main-text-input-nullable .js-input-nullable-close', function(event){
        const $group = $(event.target).parents('.js-main-text-input-nullable');
        const $input = $group.find('input');
        $input.attr('readonly','readonly');
        $input.val(inputNullableTmpVal).change();
        $('.js-input-nullable-edit', $group).show();
        $('.js-input-nullable-close', $group).hide();
        $('.js-input-nullable-save', $group).hide();
        $('.js-input-nullable-undo', $group).hide();
    });
    $(document).on('click', '.js-main-text-input-nullable .js-input-nullable-save', function(event){
        const $group = $(event.target).parents('.js-main-text-input-nullable');
        const $input = $group.find('input');
        $input.attr('readonly','readonly');
        $('.js-input-nullable-edit', $group).show();
        $('.js-input-nullable-close', $group).hide();
        $('.js-input-nullable-save', $group).hide();
        $('.js-input-nullable-undo', $group).hide();
        if ( $input.val()==='' || (''+$input.val())===(''+$input.attr('placeholder')) ) {
            if ($input.val()!=='' && !$input.hasClass('keep-val')) $input.val('');
            $('.js-input-nullable-default', $group).hide();
        }
        $input.change();
        $input.trigger('nullable-save');
    });
    $(document).on('click', '.js-main-text-input-nullable .js-input-nullable-undo', function(event){
        const $group = $(event.target).parents('.js-main-text-input-nullable');
        const $input = $group.find('input');
        $input.val($input.attr('placeholder')).change();
    });
    $(document).on('click', '.js-main-text-input-nullable .js-input-nullable-undo', function(event){
        var $group = $(event.target).parents('.js-main-text-input-nullable');
        var $input = $group.find('input');
    });
    $(document).on('update-state', '.js-main-text-input-nullable input',function(event){
        const $group = $(event.target).parents('.js-main-text-input-nullable');
        var $input = $(event.target);
        $('.js-input-nullable-default-val', $group).html($input.attr('placeholder'))
        setTimeout(function(){
            if (!$('.js-input-nullable-save:visible', $group).length && ($input.val()==='' || (''+$input.val())===(''+$input.attr('placeholder'))) ) {
                if ($input.val()!=='' && !$input.hasClass('keep-val')) $input.val('');
                $('.js-input-nullable-default', $group).hide();
            }else{
                $('.js-input-nullable-default', $group).show();
            }
        }, 0)
    });
    $(document).on('keydown', '.js-main-text-input-nullable input',function(event){
        const $group = $(event.target).parents('.js-main-text-input-nullable');
        if (event.keyCode == 27) {
            event.preventDefault();
            $('.js-input-nullable-close', $group).trigger('click');
            return false
        }
        if (event.keyCode == 13) {
            event.preventDefault();
            $('.js-input-nullable-save', $group).trigger('click');
            return false
        }
    });
    jQuery.fn.textInputNullableValue = function()
    {
        var $input = $(this);
        if ($input.val() === ''){
            return $input.attr('placeholder');
        }
        return $input.val();
    }
    /* }} textInputNullable */


    $('body').on('click', '[data-bs-target]', function() {
        let tabHash = $(this).attr('data-bs-target');
        if (tabHash && tabHash.substr(0,1) != '#') {
            return true;
        }

        tabHash = tabHash.substr(1);
        const url = new URL(window.location.href);
        let urlHashArr = [];
        if (url.hash) {
            urlHashArr = url.hash.substr(1).split('/');
        }

        $(this).parent().find('> [data-bs-target]').each(function () {
            const itemHash = $(this).attr('data-bs-target');
            if (itemHash && itemHash.substr(0,1) == '#') {
                const index = urlHashArr.indexOf(itemHash.substr(1));
                if (index > -1) {
                    urlHashArr.splice(index, 1);
                }
            }
        })

        urlHashArr.push(tabHash);
        url.hash = '#' + urlHashArr.join('/');
        window.history.replaceState({}, '', url.toString());
    });

    if (location.hash.length) {
        let urlHashArr = location.hash.substr(1).split('/');
        urlHashArr.forEach(function(hash){
            const triggerTabList = document.querySelectorAll('[data-bs-target="#' + hash + '"]');
            if (triggerTabList.length){
                const tab = new bootstrap.Tab(triggerTabList[0]);
                tab.show();
                setTimeout(() => $(triggerTabList).trigger('shown.bs.tab'), 100)
            }
        })
    }

    /* {{ sort orders products */
    $('.js-sort-order-products').each(function(){
        var $sort_button = $(this);
        $sort_button.on('click',function(){
            var $target = $($sort_button.data('selector'));
            if ( $target.hasClass('sort-active') ) {
                $target.sortable( "destroy" );
                $target.removeClass('sort-active');
            }else {
                $target.sortable({
                    update: function (event, ui) {
                        $.ajax({
                            type: "POST",
                            url: $sort_button.data('server-action'),
                            data: $(this).sortable('serialize',{key:'sortkey[]', attribute:'data-sortkey', expression: /(.+)/}),
                            success: function(data){
                                if (data && data.status && data.status=="ok") {

                                } else {
                                    return false;
                                }
                            },
                            dataType: 'json'
                        });
                    },
                    scroll: true,
                    scrollSensitivity: 50,
                    scrollSpeed: 50
                }).disableSelection();;
                $target.addClass('sort-active');
            }
        });
        $sort_button.removeClass('hide');
    });
    /* }} sort orders products */
});


$.fn.tlSwitch = function(options, parentBlock = '.tab-pane'){
    const settings = jQuery.extend({
        onText: window.entryData.tr.SW_ON,
        offText: window.entryData.tr.SW_OFF,
        handleWidth: '20px',
        labelWidth: '24px',
    }, options);
    return this.each(function() {
        const input = $(this);
        const block = input.closest(parentBlock);
        const blocks = input.parents(parentBlock);

        const activateSwitcher = function () {
            if (!input.hasClass('activated') && (block.length === 0 || block.is(':visible'))) {
                input.addClass('activated');
                input.bootstrapSwitch(settings);
                observer.disconnect();
            }
        };

        const observer = new MutationObserver(activateSwitcher);
        blocks.each(function(){
            observer.observe(this, { attributes: true });
        });

        activateSwitcher()

    })
};

$.fn.limitValue = function(options){
    if (options === 'title') {
        let max = 60
        if (isElementExist(['config', 'META_TITLE_MAX_TAG_LENGTH'], entryData)) {
            max = entryData.config.META_TITLE_MAX_TAG_LENGTH
        }
        options = {
            max: max,
            limit: false
        }
    }
    if (options === 'description') {
        let max = 160
        if (isElementExist(['config', 'META_DESCRIPTION_TAG_LENGTH'], entryData)) {
            max = entryData.config.META_DESCRIPTION_TAG_LENGTH
        }
        options = {
            max: max,
            limit: false
        }
    }

    if (options && !options.enteredText && isElementExist(['tr', 'TEXT_ENTERED_CHARACTERS'], entryData)) {
        options.enteredText = entryData.tr.TEXT_ENTERED_CHARACTERS
    }
    if (options && !options.leftText && isElementExist(['tr', 'TEXT_LEFT_CHARACTERS'], entryData)) {
        options.leftText = entryData.tr.TEXT_LEFT_CHARACTERS
    }
    if (options && !options.overflowText && isElementExist(['tr', 'TEXT_OVERFLOW_CHARACTERS'], entryData)) {
        options.overflowText = entryData.tr.TEXT_OVERFLOW_CHARACTERS
    }

    let op = jQuery.extend({
        showEnteredCount: true,
        showLeftCount: true,
        max: 60,
        limit: true,
        enteredText: 'You entered %s characters',
        leftText: 'Left %s characters',
        overflowText: 'You overflow %s characters',
    },options);

    op.enteredText = op.enteredText.replace('%s', '<span class="entered-count">0</span>');
    op.leftText = op.leftText.replace('%s', `<span class="left-count">${op.max ? op.max : ''}</span>`);
    op.overflowText = op.overflowText.replace('%s', `<span class="overflow-count"></span>`);

    return this.each(function() {
        let $field = $(this);
        $field.next('.limited-text').remove();
        let $limitedText = $('<div class="limited-text"></div>');
        let $enteredText = $(`<span class="entered-text">${op.enteredText}. </span>`);
        let $leftText = $(`<span class="left-text">${op.leftText}. </span>`);
        let $overflowText = $(`<span class="overflow-text" style="display: none">${op.overflowText}. </span>`);
        let $enteredCount = $('.entered-count', $enteredText);
        let $leftCount = $('.left-count', $leftText);
        let $overflowCount = $('.overflow-count', $overflowText);

        if (op.showEnteredCount) {
            $limitedText.append($enteredText)
        }
        if (op.showLeftCount && op.max) {
            $limitedText.append($leftText)
        }
        if (!op.limit && op.max) {
            $limitedText.append($overflowText)
        }

        $field.after($limitedText);

        $field.off('keyup change').on('keyup change', function(){
            let length = $(this).val().length;
            $enteredCount.text(length);
            if (op.max) {
                if (op.max < length && op.limit) {
                    $(this).val($(this).val().slice(0, op.max));
                    length = op.max;
                    $limitedText
                        .animate({'opacity': 0.3}, 100)
                        .animate({'opacity': 1}, 100)
                        .animate({'opacity': 0.3}, 100)
                        .animate({'opacity': 1}, 100)
                } else if (op.max < length) {
                    $limitedText.addClass('overflowing');
                    $field.addClass('overflowing');
                    $overflowText.show();
                    $leftText.hide();
                    $overflowCount.text(length - op.max)
                } else {
                    $limitedText.removeClass('overflowing');
                    $field.removeClass('overflowing');
                    $overflowText.hide();
                    $leftText.show();
                }
                $leftCount.text(op.max - length);
                $enteredCount.text(length);
            }
        }).trigger('change');
    })
};

function isElementExist(path, obj){
    if (path.length > 1) {
        if (obj && typeof obj[path[0]] === 'object') {

            return isElementExist(path.slice(1), obj[path[0]])

        }
    } else if (obj[path[0]]) {
        return true
    }
    return false;
}

$.fn.showPassword = function(){
    return this.each(function() {
        let $input = $(this);
        if ($input.hasClass('eye-applied')) {
            return '';
        }
        $input.addClass('eye-applied');

        let $eye = $('<span class="eye-password"></span>');
        let $eyeWrap = $('<span class="eye-password-wrap"></span>');
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

$.fn.tlDatetimepicker = function(options){
    if (typeof $.fn.tempusDominus !== 'function') {
        return null;
    }
    const option = jQuery.extend({
        localization: {
            locale: 'en',
            format: 'dd MMM yyyy h:mm T'
        }
    },options);
    return this.each(function() {
        if ($(this).val()) {
            $(this).val(moment(new Date($(this).val())).format('DD MMM YYYY h:mm A'));
        }
        setTimeout(() => $(this).tempusDominus(option), 100);
    })
}

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


$(function(){
    $('.menu-message').each(function(){
        var $box = $(this);
        var t = localStorage.getItem('closed-menu-message');
        if (t && 1*t + 1000*60*60*24 > Date.now()) {
            $box.remove()
        }
        $('.close', $box).on('click', function(){
            localStorage.setItem('closed-menu-message', Date.now())
        })
    })
})

/**
 * @param array list of objects {name, value}, value
 * @param jquery object $destination, add name form list item to input ($destination)
 * @param string defaultValue list item name or call to action text
 * @return jquery object
 */
function htmlDropdown(list, $destination, defaultValue = false) {
    const $dropdown = $('<div class="html-dropdown-dropdown"></div>');
    const $htmlDropdown = $('<div class="html-dropdown"></div>');
    const $selectedItem = $('<div class="selected-item"></div>');
    const value = $destination.val();
    let empty = true;
    $htmlDropdown.append($selectedItem).append($dropdown);
    list.forEach(function(item){
        const $item = $(`<div class="item"></div>`).append(itemValue(item.value));
        $item.on('click', function(){
            $destination.val(item.name).trigger('change');
            $('.active', $dropdown).removeClass('active');
            $(this).addClass('active');
            $selectedItem.html('').append(itemValue(item.value))
        });
        $dropdown.append($item)
        if (value == item.name) {
            $selectedItem.html('').append(itemValue(item.value))
            $item.addClass('active');
            empty = false;
        } else if (!value && defaultValue == item.name) {
            $selectedItem.html('').append(itemValue(item.value))
            $item.addClass('active');
            $destination.val(item.name).trigger('change');
            empty = false;
        }
    });

    function itemValue(value){
        if (typeof value == 'object') {
            return value.clone(true, true)
        } else {
            return value
        }
    }

    if (empty && defaultValue) {
        $destination.val('').trigger('change');
        $selectedItem.html(defaultValue)
    }

    $selectedItem.on('click', function(){
        const top = $selectedItem.offset().top + $selectedItem.outerHeight();
        const left = $selectedItem.offset().left;
        $('body').append($dropdown);
        $dropdown.css({top, left});
        setTimeout(function(){
            $('body').one('click', function(){
                setTimeout(() => $htmlDropdown.append($dropdown), 100)
            })
        }, 0)
    })

    return $htmlDropdown
}


// jquery.inrow.js

jQuery.fn.inrow = function(options){
    var options = jQuery.extend({
        item1: false,
        item2: false,
        item3: false,
        item4: false,
        item5: false,
        item6: false
    },options);
    return this.each(function(j) {
        if (options.item1 != false){
            heightItem = 0;
            jQuery(this).find(options.item1).each(function(){
                if (heightItem < $(this).height()){
                    heightItem = $(this).height();
                }
            });
            jQuery(this).find(options.item1).css('min-height',heightItem+'px');
        }
        if (options.item2 != false){
            heightItem = 0;
            jQuery(this).find(options.item2).each(function(){
                if (heightItem < $(this).height()){
                    heightItem = $(this).height();
                }
            });
            jQuery(this).find(options.item2).css('min-height',heightItem+'px');
        }
        if (options.item3 != false){
            heightItem = 0;
            jQuery(this).find(options.item3).each(function(){
                if (heightItem < $(this).height()){
                    heightItem = $(this).height();
                }
            });
            jQuery(this).find(options.item3).css('min-height',heightItem+'px');
        }
        if (options.item4 != false){
            heightItem = 0;
            jQuery(this).find(options.item4).each(function(){
                if (heightItem < $(this).height()){
                    heightItem = $(this).height();
                }
            });
            jQuery(this).find(options.item4).css('min-height',heightItem+'px');
        }
        if (options.item5 != false){
            heightItem = 0;
            jQuery(this).find(options.item5).each(function(){
                if (heightItem < $(this).height()){
                    heightItem = $(this).height();
                }
            });
            jQuery(this).find(options.item5).css('min-height',heightItem+'px');
        }
        if (options.item6 != false){
            heightItem = 0;
            jQuery(this).find(options.item6).each(function(){
                if (heightItem < $(this).height()){
                    heightItem = $(this).height();
                }
            });
            jQuery(this).find(options.item6).css('min-height',heightItem+'px');
        }

    });
};


// app.js

/**
 Core script to handle the entire layout and base functions
 **/
var App = function() {

    "use strict";

    // IE mode
    var isIE8 = false;
    var isIE9 = false;
    var isIE10 = false;
    var responsiveHandlers = [];
    var layoutColorCodes = {
        'blue':   '#54728c',
        'red':    '#e25856',
        'green':  '#94B86E',
        'purple': '#852b99',
        'grey':   '#555555',
        'yellow': '#ffb848'
    };
    var sidebarWidth = '250px';

    //* BEGIN:CORE HANDLERS *//
    // this function handles responsive layout on screen size resize or mobile device rotate.
    var handleResponsive = function() {
        /**
         * Sidebar-Toggle-Button
         */
        (function(){
            const widthBreakPoint = 1400
            visibilitySideBar();
            $('.toggle-sidebar').on('click', function () {
                $(this).toggleClass('open');
                $('#sidebar').css('width', '');
                $('#sidebar > #divider').css('margin-left', '');
                $('#content').css('margin-left', '');
                let key = 'sidebar';
                if ($(window).width() < widthBreakPoint) {
                    key = 'sidebar-mobile';
                }
                if (localStorage.getItem(key) == 'hide') {
                    localStorage.setItem(key, 'show');
                } else {
                    localStorage.setItem(key, 'hide');
                }
                visibilitySideBar();
                return false;
            })
            function visibilitySideBar(){
                let key = 'sidebar';
                if ($(window).width() < widthBreakPoint) {
                    key = 'sidebar-mobile';
                    if (!localStorage.getItem(key)) {
                        localStorage.setItem(key, 'hide');
                    }
                }
                if (localStorage.getItem(key) == 'hide') {
                    $('.top_header').css('padding-right', '0');
                    $('.contentContainer > .btn-bar-top').css("left", '0');
                    $('.order-helpers .btn-bar-top').css("left", '0');
                    $('#container').addClass('sidebar-closed');

                } else {
                    $('.top_header').css('padding-right', '252px');
                    $('.contentContainer > .btn-bar-top').css("left", '271px');
                    $('.order-helpers .btn-bar-top').css("left", '271px');
                    $('#container').removeClass('sidebar-closed');
                    $('.toggle-sidebar').addClass('open');
                }
            }
            let oldWidth = $(window).width();
            $(window).on('resize', function () {
                if (
                    (oldWidth < widthBreakPoint && $(window).width() >= widthBreakPoint) ||
                    (oldWidth >= widthBreakPoint && $(window).width() < widthBreakPoint)
                ) {
                    visibilitySideBar();
                }
                oldWidth = $(window).width();
            })
        })()
    }

    var calculateHeight = function() {
        $('body').height('100%');

        var $header         = $('.header');
        var header_height   = $header.outerHeight();

        var document_height = $(document).height();
        var window_height   = $(window).height();

        var doc_win_diff    = document_height - window_height;

        if (doc_win_diff <= header_height) {
            var new_height  = document_height - doc_win_diff;
        } else {
            var new_height  = document_height;
        }

        new_height = new_height - header_height;

        var document_height = $(document).height();

        //$('body').height(new_height);
    }

    var handleLayout = function() {
        calculateHeight();

        // For margin to top, if header is fixed
        if ($('.header').hasClass('navbar-fixed-top')) {
            $('#container').addClass('fixed-header');
        }
    }

    var handleResizeEvents = function() {
        var resizeLayout = debounce(_resizeEvents, 30);
        $(window).resize(resizeLayout);
    }

    // Executed only every 30ms
    var _resizeEvents = function() {
        calculateHeight();

        // Realign headers from DataTables (otherwise header will have an offset)
        // Only affects horizontal scrolling DataTables
        if ($.fn.dataTable) {
            var tables = $.fn.dataTable.fnTables(true);
            $(tables).each(function() {
                if (typeof $(this).data('horizontalWidth') != 'undefined') {
                    $(this).dataTable().fnAdjustColumnSizing();
                }
            });
        }
    }

    /**
     * Creates and returns a new debounced version of the passed
     * function which will postpone its execution until after wait
     * milliseconds have elapsed since the last time it was invoked.
     *
     * Source: http://underscorejs.org/
     */
    var debounce = function(func, wait, immediate) {
        var timeout, args, context, timestamp, result;
        return function() {
            context = this;
            args = arguments;
            timestamp = new Date();
            var later = function() {
                var last = (new Date()) - timestamp;
                if (last < wait) {
                    timeout = setTimeout(later, wait - last);
                } else {
                    timeout = null;
                    if (!immediate) result = func.apply(context, args);
                }
            };
            var callNow = immediate && !timeout;
            if (!timeout) {
                timeout = setTimeout(later, wait);
            }
            if (callNow) result = func.apply(context, args);
            return result;
        };
    };

    /**
     * Swipe Events
     */
    var handleSwipeEvents = function() {
        // Enable feature only on small widths
        if ($(window).width() <= 767) {

            $('body').on('movestart', function(e) {
                // If the movestart is heading off in an upwards or downwards
                // direction, prevent it so that the browser scrolls normally.
                if ((e.distX > e.distY && e.distX < -e.distY) || (e.distX < e.distY && e.distX > -e.distY)) {
                    e.preventDefault();
                }

                // Prevents showing sidebar while scrolling through projects
                var $parentClass = $(e.target).parents('#project-switcher');

                if ($parentClass.length) {
                    e.preventDefault();
                }
            }).on('swipeleft', function(e) {
                // Hide sidebar on swipeleft
                $('body').toggleClass('nav-open');
            }).on('swiperight', function(e) {
                // Show sidebar on swiperight
                $('body').toggleClass('nav-open');
            });

        }
    }

    var handleSidebarMenu = function() {
        var arrow_class_open   = 'icon-minus',
            arrow_class_closed = 'icon-plus';

        $('li:has(ul)', '#sidebar-content ul').each(function() {
            if ($(this).hasClass('current') || $(this).hasClass('open-default')) {
                $('>a', this).append("<i class='arrow " + arrow_class_open + "'></i>");
            } else {
                $('>a', this).append("<i class='arrow " + arrow_class_closed + "'></i>");
            }
        });

        if ($('#sidebar').hasClass('sidebar-fixed')) {
            $('#sidebar-content').append('<div class="fill-nav-space"></div>');
        }

        $('#sidebar-content ul > li > a').on('click', function (e) {

            if ($(this).next().hasClass('sub-menu') == false) {
                return;
            }

            // Toggle on small devices instead of accordion
            if ($(window).width() > 767) {
                var parent = $(this).parent().parent();

                /* parent.children('li.open').children('a').children('i.arrow').removeClass(arrow_class_open).addClass(arrow_class_closed); */
                /* parent.children('li.open').children('.sub-menu').slideUp(200); */
                parent.children('li.open-default').children('.sub-menu').slideUp(200);
                /* parent.children('li.open').removeClass('open').removeClass('open-default'); */
            }

            var sub = $(this).next();
            if (sub.is(":visible")) {
                $('i.arrow', $(this)).removeClass(arrow_class_open).addClass(arrow_class_closed);
                $(this).parent().removeClass('open');
                sub.slideUp(200, function() {
                    $(this).parent().removeClass('open-fixed').removeClass('open-default');
                    calculateHeight();
                });
            } else {
                $('i.arrow', $(this)).removeClass(arrow_class_closed).addClass(arrow_class_open);
                $(this).parent().addClass('open');
                sub.slideDown(200, function() {
                    calculateHeight();
                });
            }

            e.preventDefault();
        });

        var _handleResizeable = function() {
            $('#divider.resizeable').mousedown(function(e){
                e.preventDefault();

                var divider_width = $('#divider').width();
                $(document).mousemove(function(e){
                    var sidebar_width = e.pageX+divider_width;
                    if (sidebar_width <= 300 && sidebar_width >= (divider_width * 2 - 3)) {
                        if (sidebar_width >= 240 && sidebar_width <= 260) {
                            $('#sidebar').css("width", 250);
                            $('#sidebar-content').css("width", 250);
                            $('#content').css("margin-left", 250);
                            $('#divider').css("margin-left", 250);
                            $('.contentContainer > .btn-bar-top').css("left", 271);
                            $('.order-helpers .btn-bar-top').css("left", 271);
                        } else {
                            $('#sidebar').css("width",sidebar_width);
                            $('#sidebar-content').css("width", sidebar_width);
                            $('#content').css("margin-left",sidebar_width);
                            $('#divider').css("margin-left",sidebar_width);
                            $('.top_header').css('padding-right', sidebar_width * 1 + 22)
                            $('.contentContainer > .btn-bar-top').css("left", sidebar_width + 21);
                            $('.order-helpers .btn-bar-top').css("left", sidebar_width + 21);
                        }

                    }

                })
            });
            $(document).mouseup(function(e){
                $(document).unbind('mousemove');
            });
        }

        _handleResizeable();
    }

    var handleScrollbars = function() {
        var android_chrome = /android.*chrom(e|ium)/.test(navigator.userAgent.toLowerCase());

        if( /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test(navigator.userAgent) && android_chrome == false) {
            $('#sidebar').css('overflow-y', 'auto');
        } else {
            if ($('#sidebar').hasClass('sidebar-fixed') || $(window).width() <= 767) {
                if (android_chrome) {
                    var wheelStepInt = 7;
                } else {
                    var wheelStepInt = 7;
                }

                $('#sidebar-content').slimscroll({
                    'height': '100%',
                    wheelStep: wheelStepInt,
                    alwaysVisible: true,
                });
            }
        }
    }

    var handleWidgets = function() {
        $('.widget .toolbar .widget-collapse').openCloseWidget();
        $('.widget .toolbar .widget-collapse1').click(function() {
            var widget         = $(this).parents(".widget");
            var widget_content = widget.children(".widget-content1");
            var widget_chart   = widget.children(".widget-chart");
            var divider        = widget.children(".divider");

            if (widget.hasClass('widget-closed')) {
                // Open Widget
                $(this).children('i').removeClass('icon-angle-up').addClass('icon-angle-down');
                widget_content.slideDown(200, function() {
                    widget.removeClass('widget-closed');
                });
                widget_chart.slideDown(200);
                divider.slideDown(200);
            } else {
                // Close Widget
                $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-up');
                widget_content.slideUp(200, function() {
                    widget.addClass('widget-closed');
                });
                widget_chart.slideUp(200);
                divider.slideUp(200);
            }
        });
        $('.widget .toolbar .widget-collapse2').click(function() {
            var widget         = $(this).parents(".widget");
            var widget_content = widget.children(".widget-content2");
            var widget_chart   = widget.children(".widget-chart");
            var divider        = widget.children(".divider");

            if (widget.hasClass('widget-closed')) {
                // Open Widget
                $(this).children('i').removeClass('icon-angle-up').addClass('icon-angle-down');
                widget_content.slideDown(200, function() {
                    widget.removeClass('widget-closed');
                });
                widget_chart.slideDown(200);
                divider.slideDown(200);
            } else {
                // Close Widget
                $(this).children('i').removeClass('icon-angle-down').addClass('icon-angle-up');
                widget_content.slideUp(200, function() {
                    widget.addClass('widget-closed');
                });
                widget_chart.slideUp(200);
                divider.slideUp(200);
            }
        });
    }

    var handleCheckableTables = function() {
        $( 'body').on('change', '.table-checkable thead th.checkbox-column :checkbox, .table-checkable thead th .checkbox-column :checkbox', function() {
            var checked = $( this ).prop( 'checked' );

            var data_horizontalWidth = $(this).parents('table.table-checkable').data('horizontalWidth');
            if (typeof data_horizontalWidth != 'undefined') {
                var $checkable_table_body = $( this ).parents('.dataTables_scroll').find('.dataTables_scrollBody tbody');
            } else {
                var $checkable_table_body = $( this ).parents('table').children('tbody');
            }

            $checkable_table_body.each(function(i, tbody) {
                $(tbody).find('.checkbox-column').each(function(j, cb) {
                    var cb_self = $( '.uniform:checkbox', $(cb) ).prop( "checked", checked ).trigger('change');

                    if (cb_self.hasClass('uniform')) {
                        $.uniform.update(cb_self);
                    }

                    $(cb).closest('tr').toggleClass( 'checked', checked );
                });
            });
        });
        $( '.table-checkable tbody tr td.checkbox-column :checkbox' ).on('change', function() {
            var checked = $( this ).prop( 'checked' );
            $( this ).closest('tr').toggleClass( 'checked', checked );
        });
    }

    var handleScrollers = function() {
        $('.scroller').each(function () {
            $(this).slimScroll({
                size: '7px',
                opacity: '0.2',
                position: 'right',
                height: $(this).attr('data-height'),
                alwaysVisible: ($(this).attr('data-always-visible') == '1' ? true : false),
                railVisible: ($(this).attr('data-rail-visible') == '1' ? true : false),
                disableFadeOut: true
            });
        });
    }

    var handleProjectSwitcher = function() {
        handleProjectSwitcherWidth();

        $('.project-switcher-btn').click(function (e) {
            e.preventDefault();

            _hideVisibleProjectSwitcher(this);

            $(this).parent().toggleClass('open');

            // Define default project switcher
            var data_projectSwitcher = _getProjectSwitcherID(this);

            $(data_projectSwitcher).slideToggle(200, function() {
                $(this).toggleClass('open');
            });
        });

        // Hide project switcher on click elsewhere the element
        $('body').click(function(e) {
            if (typeof e.target.className == "string") {
                var classes = e.target.className.split(' ');

                if ($.inArray('project-switcher', classes) == -1 && $.inArray('project-switcher-btn', classes) == -1
                    && $(e.target).parents().index($('.project-switcher')) == -1 && $(e.target).parents('.project-switcher-btn').length == 0) {

                    _hideVisibleProjectSwitcher();

                }
            }
        });

        /*
         * Horizontal scrollbars
         */

        $('.project-switcher #frame').each(function () {
            $(this).slimScrollHorizontal({
                width: '100%',
                alwaysVisible: true,
                color: '#fff',
                opacity: '0.2',
                size: '5px'
            });
        });

        var _hideVisibleProjectSwitcher = function(el) {
            $('.project-switcher').each(function () {
                var $projectswitcher = $(this);

                // Only slide up visible project switcher
                if ($projectswitcher.is(':visible')) {
                    var data_projectSwitcher = _getProjectSwitcherID(el);

                    if (data_projectSwitcher != ('#' + $projectswitcher.attr('id'))) {
                        $(this).slideUp(200, function() {
                            $(this).toggleClass('open');

                            // Remove all clicked states from toggle buttons
                            $('.project-switcher-btn').each(function () {
                                // Define default project switcher
                                var data_projectSwitcher = _getProjectSwitcherID(this);

                                if (data_projectSwitcher == ('#' + $projectswitcher.attr('id'))) {
                                    $(this).parent().removeClass('open');
                                }
                            });
                        });
                    }
                }
            });
        }

        var _getProjectSwitcherID = function(el) {
            // Define default project switcher
            var data_projectSwitcher = $(el).data('projectSwitcher');
            if (typeof data_projectSwitcher == 'undefined') {
                data_projectSwitcher = '#project-switcher';
            }

            return data_projectSwitcher;
        }
    }

    /**
     * Calculates project switcher width
     */
    var handleProjectSwitcherWidth = function() {
        $('.project-switcher').each(function () {
            // To fix the hidden-width()-bug
            var $projectswitcher = $(this);
            $projectswitcher.css('position', 'absolute').css('margin-top', '-1000px').show();

            // Iterate through each li
            var total_width = 0;
            $('ul li', this).each(function() {
                total_width += $(this).outerWidth(true) + 15;
            });

            // And finally hide it again
            $projectswitcher.css('position', 'relative').css('margin-top', '0').hide();

            $('ul', this).width(total_width);
        });
    }

    //* END:CORE HANDLERS *//

    return {

        //main function to initiate template pages
        init: function(in_container) {
            //core handlers
            handleResponsive(); // Checks for IE-version, click-handler for sidebar-toggle-button, Breakpoints
            handleLayout(); // Calls calculateHeight()
            handleResizeEvents(); // Calls _resizeEvents() every 30ms on resizing
            handleSwipeEvents(); // Enables feature to swipe to the left or right on mobile phones to open the sidebar
            if (typeof in_container == 'undefined'){
                handleSidebarMenu(); // Handles navigation
            }
            handleScrollbars(); // Adds styled scrollbars for sidebar on desktops
            handleWidgets(); // Handle collapse and expand from widgets
            handleCheckableTables(); // Checks all checkboxes in a table if master checkbox was toggled
            handleScrollers(); // Initializes slimscroll for scrollable widgets
            handleProjectSwitcher(); // Adds functionality for project switcher at the header
        },

        getLayoutColorCode: function(name) {
            if (layoutColorCodes[name]) {
                return layoutColorCodes[name];
            } else {
                return '';
            }
        },

        // Wrapper function to block elements (indicate loading)
        blockUI: function (el, centerY) {
            var el = $(el);
            el.block({
                message: '<img src="./assets/img/ajax-loading.gif" alt="">',
                centerY: centerY != undefined ? centerY : true,
                css: {
                    top: '10%',
                    border: 'none',
                    padding: '2px',
                    backgroundColor: 'none'
                },
                overlayCSS: {
                    backgroundColor: '#000',
                    opacity: 0.05,
                    cursor: 'wait'
                }
            });
        },

        // Wrapper function to unblock elements (finish loading)
        unblockUI: function (el) {
            $(el).unblock({
                onUnblock: function () {
                    $(el).removeAttr("style");
                }
            });
        }

    };

}();
jQuery(document).ready(function($){
    if(localStorage.getItem('basicActiveTab')){
        $('.advanced').removeClass('active');
        $('.basic').addClass('active');
        $('#nav > li').eq(3).nextAll().hide();
    }
    if(localStorage.getItem("advancedActiveTab")){
        $('.advanced').addClass('active');
    }
    $('.basic').click(function(){
        localStorage.removeItem("advancedActiveTab");
        localStorage.setItem("basicActiveTab", 'active');
        $('.advanced').removeClass('active');
        if($('#nav > li').eq(3).nextAll().hasClass('current')){
        }else {
            $(this).addClass('active');
            $('#nav > li').eq(3).nextAll().hide();
            return false;
        }

    })
    $('.advanced').click(function(){
        localStorage.removeItem("basicActiveTab");
        localStorage.setItem("advancedActiveTab", 'active');
        $('.basic').removeClass('active');
        $(this).addClass('active');
        $('#nav > li').eq(3).nextAll().show();
        return false;
    })
    $('#nav li a').click(function(){
        var offset = $('#sidebar-content').scrollTop();
        localStorage.removeItem("scrolltop");
        localStorage.setItem("scrolltop", offset);
    })
    $('.summary_arrow').click(function(){
        $(this).toggleClass('closeContent');
        $(this).parent().next().slideToggle(200);
    })
    $('.btn-show-orders, .sb-title').hover(function(){
            $(this).parent().parent().addClass('summary-box-active');
        },function(){
            $('.summary-box').removeClass('summary-box-active');
        }
    )

})
$(window).on('load', function(){
    var cookieValue = localStorage.getItem("scrolltop");
    if(cookieValue != undefined){
        $('#sidebar-content').animate({scrollTop: cookieValue-24}, 500);
        $('.slimScrollBar').css('top',cookieValue-24);
    }
})

var clockData = {};

function updateClock (currentTime, clockSelector, dateSelector )
{
    var currentHours = currentTime.getHours ( );
    var currentMinutes = currentTime.getMinutes ( );
    var currentSeconds = currentTime.getSeconds ( );

    if (!clockData[clockSelector]) {
        clockData[clockSelector] = {}
    }

    // Pad the minutes and seconds with leading zeros, if required
    currentMinutes = ( currentMinutes < 10 ? "0" : "" ) + currentMinutes;
    currentSeconds = ( currentSeconds < 10 ? "0" : "" ) + currentSeconds;

    // Compose the string for display
    var currentTimeString = currentHours + ":" + currentMinutes;
    if (clockData[clockSelector].currentTimeString !== currentTimeString) {
        clockData[clockSelector].currentTimeString = currentTimeString;
        $(clockSelector).html(currentTimeString);
    }

    var currentDay = window.dayOfWeek && window.dayOfWeek[currentTime.getDay()];
    var currentDateW = currentTime.getDate();
    var numberMonth = currentTime.getMonth();
    var currentMonth = window.monthNames && window.monthNames[numberMonth];
    var currentYear = currentTime.getFullYear();

    // Compose the string for display
    var currentDateString = currentDay + "<br>" + currentDateW + " " + currentMonth + ", " + currentYear;
    if (clockData[clockSelector].currentDateString !== currentDateString) {
        clockData[clockSelector].currentDateString = currentDateString;
        $(dateSelector).html(currentDateString);
    }
}

function updateTime(){
    var currentTime = new Date ();
    var serverTime = new Date (currentTime.getTime() - (window.diferentServerTime || 0));
    updateClock(currentTime, "#clock", "#date");
    updateClock(currentTime, "#clock-1", "#date-1");
    updateClock(serverTime, "#clock-2", "#date-2")
}

$(document).ready(function() {

    setInterval(updateTime, 1000);

    var currentTime = new Date ( );
    var d = currentTime.getHours()*60 + currentTime.getMinutes() - $('.united-date').data('time');
    if (d < 0-10 || d > 10){
        $('.united-date').hide();
        $('.current-date').show();
        $('.server-date').show();
    }


    var color = '#ff0000';

    var highlight = function(obj, reg){
        if (reg.length == 0) return;
        $(obj).find('span').html($(obj).find('span').text().replace( new RegExp( "(" +  reg  + ")" , 'gi' ), '<font style="color:'+color+'">$1</font>'));
        return;
    }

    var unhighlight = function(obj){
        $(obj).find('span').html($(obj).find('span').text());
    }

    var search = null;
    var started = false;
    $('#menusearch').on('focus keyup', function(e){

        if ($(this).val().length == 0){
            //restart
            started = false;
        }

        if (!started && e.type == 'focus'){
            $('#nav').find('li').addClass('open').children('ul').show();
            $('#nav').find('.arrow').removeClass('icon-plus').addClass('icon-minus');
        }

        started = true;
        var str = $(this).val();
        search = new RegExp(str, 'i');


        $.each($('#nav').find('a[href!="javascript:void(0);"]'), function(i, e){
            unhighlight(e);
            if (!search.test($(e).text())){
                $(e).parent().hide();
            } else {
                $(e).parents('ul li').show();
                $(e).next().show();
                highlight(e, str);
            }
        });

        $.each($('#nav').find('a[href!="javascript:void(0);"]').parent(), function(i, e){
            if ($(e).is(':visible')){
                $(e).find('ul, li').show();
            }
        });


        $.each($('#nav').find('a[href="javascript:void(0);"]'), function(i, e){
            if ($(e).next().find('li:visible').length == 0){
                $(e).parent().hide();
            } else {
                $(e).parent().show();
            }

        });


    })
    $(window).scroll(function(){
        if($(window).scrollTop() > 0){
            $('.top_header').addClass('scrollHeader');
        }else{
            $('.top_header').removeClass('scrollHeader');
        }
    })
    $(window).scroll(function() {

        if($('.order-wrap').length > 0 && $('.order-wrap .table-responsive').length){
            if(($('.order-wrap .table-responsive').offset().top - $('.order-wrap').offset().top) < 101){
                var extra_pad = 151-($('.order-wrap .table-responsive').offset().top - $('.order-wrap').offset().top);
            }else{
                var extra_pad = 0;
            }
            if($(document).scrollTop() > $('.order-wrap').offset().top-extra_pad && $('.scroll_col').height() < $(window).height()-extra_pad ) {
                $('.scroll_col').css('top', $(document).scrollTop() + extra_pad - $('.order-wrap').offset().top);
                $('.batchCol').css('top', $(document).scrollTop() + extra_pad - $('.order-wrap').offset().top);
            }else{
                $('.scroll_col').css('top', '');
                $('.batchCol').css('top', '');
            }
        }


    });
});
function deleteScroll(){
    $('.scroll_col').addClass('fixcolumn');
}
function heightColumn(){

    setTimeout(function(){
        $('.right_column .widget.box').removeAttr('style');
        var wrap_height = $('.order-wrap').height();
        var scol_height = $('.right_column .widget.box .scroll_col').height();
        if(wrap_height > scol_height){
            $('.right_column .widget.box').css('min-height', wrap_height);
        }else{
            $('.right_column .widget.box').css('min-height', scol_height);
        }
    }, 700);

}

$(window).on('load', function(){
    if($('.order-wrap').length > 0){
        setTimeout(function() {
            heightColumn();
        },500);
        $(document).on("ajaxComplete", function(){
            setTimeout(function(){
                heightColumn();
            }, 500)
        });
        $(window).resize(function(){
            heightColumn();
        })
        $(window).resize();
    }

    var c_data = localStorage.getItem('closed_data');
    if (!c_data) c_data = '';
    var closed_data = c_data.split('|');
    $('.tl-wrap-li-left-cat').each(function(){
        var head = $('.collapse_span', this);
        var content = $('+ ol', this);
        var _id = $('.cat_text', this).attr('id');
        var in_closed_data = closed_data.indexOf(_id);
        if (in_closed_data == -1) {
            in_closed_data = false;
        } else {
            in_closed_data = true;
        }
        if (in_closed_data){
            head.addClass('c_up');
            content.hide()
        }
        head.off('click').on('click', function(){
            if (head.hasClass('c_up')){
                head.removeClass('c_up');
                content.slideDown();
                localStorage.setItem('closed_data', c_data.replace(_id + '|', ''))
            } else {
                head.addClass('c_up');
                content.slideUp();
                localStorage.setItem('closed_data', c_data + _id + '|')
            }
        })
    });

    /*$('.collapse_span').click(function(){
        $(this).toggleClass('c_up');
        $(this).parent().parent().parent().next().slideToggle();
    });*/
    $('.collapse_all').click(function(){
        $('.categories_ul ol').slideUp();
        $('.collapse_span').addClass('c_up');
        $('.expand_all').removeClass('switch_active');
        $(this).addClass('switch_active');
        return false;
    })
    $('.expand_all').click(function(){
        $('.categories_ul ol').slideDown();
        $('.collapse_span').removeClass('c_up');
        $('.collapse_all').removeClass('switch_active');
        $(this).addClass('switch_active');
        return false;
    })
})


// plugins.js

/**
 * Core script to handle plugins
 */

var Plugins = function() {

    "use strict";

    /**
     * $.browser for jQuery 1.9
     */
    var initBrowserDetection = function() {
        $.browser={};(function(){$.browser.msie=false;
            $.browser.version=0;if(navigator.userAgent.match(/MSIE ([0-9]+)\./)){
                $.browser.msie=true;$.browser.version=RegExp.$1;}})();
    }

    /**
     * Daterangepicker
     */
    var initDaterangepicker = function() {
        if ($.fn.daterangepicker) {
            $('.range').daterangepicker({
                    startDate: moment().subtract(29, 'days'),
                    endDate: moment(),
                    minDate: '01/01/2012',
                    maxDate: '12/31/2014',
                    dateLimit: { days: 60 },
                    showDropdowns: true,
                    showWeekNumbers: true,
                    timePicker: false,
                    timePickerIncrement: 1,
                    timePicker12Hour: true,
                    ranges: {
                        'Today': [moment(), moment()],
                        'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                        'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                        'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                        'This Month': [moment().startOf('month'), moment().endOf('month')],
                        'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                    },
                    opens: 'left',
                    buttonClasses: ['btn btn-default'],
                    applyClass: 'btn-sm btn-primary',
                    cancelClass: 'btn-sm',
                    format: 'MM/DD/YYYY',
                    separator: ' to ',
                    locale: {
                        applyLabel: 'Submit',
                        fromLabel: 'From',
                        toLabel: 'To',
                        customRangeLabel: 'Custom Range',
                        daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
                        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                        firstDay: 1
                    }
                },

                function (start, end) {
                    var range_updated = start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY');

                    App.blockUI($("#content"));
                    setTimeout(function () {
                        App.unblockUI($("#content"));
                        //App.scrollTo();
                    }, 1000);

                    $('.range span').html(range_updated);
                });

            $('.range span').html(moment().subtract(29, 'days').format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
        }
    }

    /**************************
     * Tooltips               *
     **************************/
    var initTooltips = function() {
        // Set default options

        // TODO: $.extend does not work since BS3!

        // This fixes issue #5865
        // (When using tooltips and popovers with the Bootstrap input groups,
        // you'll have to set the container option to avoid unwanted side effects.)
        $.extend(true, $.fn.tooltip.defaults, {
            container: 'body'
        });

        $('.bs-tooltip').tooltip({
            container: 'body'
        });
        $('.bs-focus-tooltip').tooltip({
            trigger: 'focus',
            container: 'body'
        });
    }

    /**************************
     * Popovers               *
     **************************/
    var initPopovers = function() {
        $('.bs-popover').popover();
    }

    /**************************
     * Easy Pie Chart         *
     **************************/
    var initCircularCharts = function() {
        if ($.easyPieChart) {
            // Set default options
            $.extend(true, $.easyPieChart.defaultOptions, {
                lineCap: 'butt',
                animate: 500,
                barColor: App.getLayoutColorCode('blue')
            });

            // Initialize defaults
            $('.circular-chart').easyPieChart({
                size: 110,
                lineWidth: 10
            });
        }
    }

    /**************************
     * DataTables             *
     **************************/
    var initDataTables = function() {
        if ($.fn.dataTable) {
            $.fn.dataTableExt.oPagination.listbox = {
                /*
                 * Function: oPagination.listbox.fnInit
                 * Purpose:  Initalise dom elements required for pagination with listbox input
                 * Returns:  -
                 * Inputs:   object:oSettings - dataTables settings object
                 *             node:nPaging - the DIV which contains this pagination control
                 *             function:fnCallbackDraw - draw function which must be called on update
                 */
                "fnInit": function (oSettings, nPaging, fnCallbackDraw) {
                    var nFirst = document.createElement( 'span' );
                    var nPrevious = document.createElement( 'span' );
                    var nNext = document.createElement( 'span' );
                    var nLast = document.createElement( 'span' );

                    var nInput = document.createElement('select');
                    var nPage = document.createElement('span');
                    var nOf = document.createElement('span');
                    nOf.className = "paginate_of";
                    nPage.className = "paginate_page";
                    if (oSettings.sTableId !== '') {
                        nPaging.setAttribute('id', oSettings.sTableId + '_paginate');
                    }

                    nFirst.innerHTML = oSettings.oLanguage.oPaginate.sFirst;
                    nPrevious.innerHTML = oSettings.oLanguage.oPaginate.sPrevious;
                    nNext.innerHTML = oSettings.oLanguage.oPaginate.sNext;
                    nLast.innerHTML = oSettings.oLanguage.oPaginate.sLast;

                    nFirst.className = "paginate_button first";
                    nPrevious.className = "paginate_button previous";
                    nNext.className="paginate_button next";
                    nLast.className = "paginate_button last";

                    if ( oSettings.sTableId !== '' )
                    {
                        nFirst.setAttribute( 'id', oSettings.sTableId+'_first' );
                        nPrevious.setAttribute( 'id', oSettings.sTableId+'_previous' );
                        nNext.setAttribute( 'id', oSettings.sTableId+'_next' );
                        nLast.setAttribute( 'id', oSettings.sTableId+'_last' );
                    }

                    nInput.style.display = "inline";
                    nPage.innerHTML = "";
                    nPaging.appendChild( nFirst );
                    nPaging.appendChild( nPrevious );
                    //nPaging.appendChild(nPage);
                    nPaging.appendChild(nInput);
                    //nPaging.appendChild(nOf);
                    nPaging.appendChild( nNext );
                    nPaging.appendChild( nLast );

                    $(nFirst).click( function () {
                        window.scroll(0,0); //scroll to top of page
                        oSettings.oApi._fnPageChange( oSettings, "first" );
                        fnCallbackDraw( oSettings );
                    } );

                    $(nPrevious).click( function() {
                        window.scroll(0,0); //scroll to top of page
                        oSettings.oApi._fnPageChange( oSettings, "previous" );
                        fnCallbackDraw( oSettings );
                    } );

                    $(nNext).click( function() {
                        window.scroll(0,0); //scroll to top of page
                        oSettings.oApi._fnPageChange( oSettings, "next" );
                        fnCallbackDraw( oSettings );
                    } );

                    $(nLast).click( function() {
                        window.scroll(0,0); //scroll to top of page
                        oSettings.oApi._fnPageChange( oSettings, "last" );
                        fnCallbackDraw( oSettings );
                    } );

                    $(nInput).change(function (e) { // Set DataTables page property and redraw the grid on listbox change event.
                        window.scroll(0,0); //scroll to top of page
                        if (this.value === "" || this.value.match(/[^0-9]/)) { /* Nothing entered or non-numeric character */
                            return;
                        }
                        var iNewStart = oSettings._iDisplayLength * (this.value - 1);
                        if (iNewStart > oSettings.fnRecordsDisplay()) { /* Display overrun */
                            oSettings._iDisplayStart = (Math.ceil((oSettings.fnRecordsDisplay() - 1) / oSettings._iDisplayLength) - 1) * oSettings._iDisplayLength;
                            fnCallbackDraw(oSettings);
                            return;
                        }
                        oSettings._iDisplayStart = iNewStart;
                        fnCallbackDraw(oSettings);
                    }); /* Take the brutal approach to cancelling text selection */
                    $('span', nPaging).bind('mousedown', function () {
                        return false;
                    });
                    $('span', nPaging).bind('selectstart', function () {
                        return false;
                    });
                },

                /*
                 * Function: oPagination.listbox.fnUpdate
                 * Purpose:  Update the listbox element
                 * Returns:  -
                 * Inputs:   object:oSettings - dataTables settings object
                 *             function:fnCallbackDraw - draw function which must be called on update
                 */
                "fnUpdate": function (oSettings, fnCallbackDraw) {
                    if (!oSettings.aanFeatures.p) {
                        return;
                    }
                    var iPages = Math.ceil((oSettings.fnRecordsDisplay()) / oSettings._iDisplayLength);
                    var iCurrentPage = Math.ceil(oSettings._iDisplayStart / oSettings._iDisplayLength) + 1; /* Loop over each instance of the pager */
                    var an = oSettings.aanFeatures.p;
                    for (var i = 0, iLen = an.length; i < iLen; i++) {
                        var spans = an[i].getElementsByTagName('span');
                        var inputs = an[i].getElementsByTagName('select');
                        var elSel = inputs[0];
                        if(elSel.options.length != iPages) {
                            elSel.options.length = 0; //clear the listbox contents
                            for (var j = 0; j < iPages; j++) { //add the pages
                                var oOption = document.createElement('option');
                                oOption.text = j + 1;
                                oOption.value = j + 1;
                                try {
                                    elSel.add(oOption, null); // standards compliant; doesn't work in IE
                                } catch (ex) {
                                    elSel.add(oOption); // IE only
                                }
                            }
                            //spans[1].innerHTML = "";
                        }
                        elSel.value = iCurrentPage;
                        var buttons = an[i].getElementsByTagName('span');
                        if ( oSettings._iDisplayStart === 0 )
                        {
                            buttons[0].className = "paginate_disabled_previous";
                            buttons[1].className = "paginate_disabled_previous";
                        }
                        else
                        {
                            buttons[0].className = "paginate_enabled_previous";
                            buttons[1].className = "paginate_enabled_previous";
                        }

                        if ( oSettings.fnDisplayEnd() == oSettings.fnRecordsDisplay() )
                        {
                            buttons[2].className = "paginate_disabled_next";
                            buttons[3].className = "paginate_disabled_next";
                        }
                        else
                        {
                            buttons[2].className = "paginate_enabled_next";
                            buttons[3].className = "paginate_enabled_next";
                        }
                    }
                }
            };
            // Set default options
            $.extend(true, $.fn.dataTable.defaults, {
                "oLanguage": {
                    "sSearch": "",
                    "sLengthMenu": "_MENU_",
                    "sInfo": "Displaying _START_ to _END_ (of _TOTAL_ records)",
                    "oPaginate": {
                        "sNext": "<i class='fa fa-arrow-right'></i>",
                        "sPrevious": "<i class='fa fa-arrow-left'></i>"
                    }
                },
                "sPaginationType": "listbox",
                "stateSave": true,
                "sDom": "<'row'<'dataTables_header clearfix'<'col-md-6'><'col-md-6 col-md-6-new'f>r>>t<'row'<'dataTables_footer clearfix'<'col-md-6'li><'col-md-6'p>>>",
                // set the initial value
                "iDisplayLength": 25,
                fnDrawCallback: function () {
                    if ($.fn.uniform) {
                        $(':radio.uniform, :checkbox.uniform').uniform();
                    }

                    if ($.fn.select2) {
                        $('.dataTables_length select').select2({
                            minimumResultsForSearch: "-1"
                        });
                    }
                    if ( !$('.table tbody tr').hasClass('selected') ) {
                        if ($("#row_id").val() == undefined) {
                            $('.table tbody tr:eq(0)').click();
                        } else {
                            var sel = $('.table > tbody > tr:eq(' + $("#row_id").val() + ')');
                            if (sel[0] == undefined) {
                                $("#row_id").val(0);
                            }
                            $('.table > tbody > tr:eq(' + $("#row_id").val() + ')').click();
                        }
                    }
                    $('tr td .uniform').click(function() {
                        if(typeof getTableSelectedCount === 'function' && getTableSelectedCount() > 0){
                            $('.order-box-list .btn-wr').removeClass('disable-btn');
                        }else{
                            $('.order-box-list .btn-wr').addClass('disable-btn');
                        }
                        if (typeof afterClickBatchSelection==='function') {
                            afterClickBatchSelection();
                        }
                    });
                    if (typeof onDrawCallbackEvent==='function') {
                        onDrawCallbackEvent();
                    }
                    setTimeout(function(){
                        $('.right_column .widget.box').removeAttr('style');
                        var wrap_height = $('.order-wrap').height();
                        var scol_height = $('.widget.box .scroll_col').height();
                        if(wrap_height > scol_height){
                            $('.row .widget.box').css('min-height', wrap_height);
                        }else{
                            $('.row .widget.box').css('min-height', scol_height);
                        }
                    }, 700);
                    if ($('.wtres table.table.tabl-res').length==0) {
                        $('table.table.tabl-res').wrap('<div class="wtres"></div>');
                        if ($('.wtres .sh-scloll').length==0){
                            $('.wtres').before('<div class="sh-scloll">Table scrolled</div>');
                        }
                    }

                    // SEARCH - Add the placeholder for Search and Turn this into in-line formcontrol
                    var search_input = $(this).closest('.dataTables_wrapper').find('div[id$=_filter] input');

                    // Only apply settings once
                    if (search_input.parent().hasClass('input-group')) return;

                    //search_input.attr('placeholder', 'Search')
                    search_input.addClass('form-control')
                    search_input.wrap('<div class="input-group input-group-order"></div>');
                    search_input.parent().prepend('<span class="input-group-addon dt-ic-search"><i class="icon-search"></i></span>');
                }
            });

//			$.fn.dataTable.defaults.aLengthMenu = [[5, 10, 25, 50, -1], [5, 10, 25, 50]];
            $.fn.dataTable.defaults.aLengthMenu = [[5, 10, 25, 50, 100, 500, 1000, -1], [5, 10, 25, 50, 100, 500, 1000, "All"]];

            // Initialize default datatables
            $('.datatable').each(function () {
                var self = $(this);
                var options = {};

                /*
                 * Options via data-attribute
                 */

                // General Wrapper
                var data_dataTable = self.attr('datatable');//data
                if (typeof data_dataTable != 'undefined') {
                    $.extend(true, options, data_dataTable);
                }

                // Display Length
                var data_displayLength = self.attr('displayLength');//data

                if (typeof data_displayLength != 'undefined') {
                    $.extend(true, options, {
                        "iDisplayLength": data_displayLength
                    });
                }

                // Vertical Scrolling
                var data_verticalHeight = self.attr('verticalHeight');//data
                if (typeof data_verticalHeight != 'undefined') {
                    $.extend(true, options, {
                        "scrollY":        data_verticalHeight,
                        "scrollCollapse": true,
                        "paging":         false
                    });
                }

                // Horizontal Scrolling
                var data_horizontalWidth = self.attr('horizontalWidth');//data
                if (typeof data_horizontalWidth != 'undefined') {
                    $.extend(true, options, {
                        "sScrollX": "100%",
                        "sScrollXInner": data_horizontalWidth,
                        "bScrollCollapse": true
                    });
                }

                /*
                 * Other
                 */
                if ($('.datatable').hasClass('table-texts-table')) {
                    var height_texts_table = $(window).height() - 300;
                    $.extend(true, options, {
                        "sScrollX": true,
                        "sScrollY": height_texts_table,
                        "bScrollCollapse": true
                    });
                }
                // Checkable Tables
                if (self.hasClass('table-selectable')) {

                    $.extend(true, options, {
                        'aoColumnDefs': [
                            {'bSortable': false, 'aTargets': ['_all']}
                        ]
                    });
                    $.extend(true, options, {
                        "bSort": false,
                        //"bInfo": false,
                        //"bFilter": false,
                        "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                            $(nRow).addClass('checkbox-column');
                            return nRow;
                        }
                    });
                } else if (self.hasClass('table-checkable')) {
                    var data_checkable_list = self.attr('checkable_list');//data
                    if (data_checkable_list == '') {
                        $.extend(true, options, {
                            'aoColumnDefs': [
                                {'bSortable': false, 'aTargets': ['_all']}
                            ]
                        });
                        /*$.extend(true, options, {
    "bSort": false,
                            //"bInfo": false,
                            //"bFilter": false,
                            "fnRowCallback": function( nRow, aData, iDisplayIndex ) {
                                $(nRow).addClass('checkbox-column');
                                return nRow;
                            }
                        });*/
                    } else {
                        var column_index_list = data_checkable_list.split(',');
                        var aoColumnDefs = [];
                        for(var column_key in column_index_list) {
                            aoColumnDefs.push({ 'bSortable': true, 'aTargets': [ parseInt(column_index_list[column_key]) ] });
                        }
                        aoColumnDefs.push({ 'bSortable': false, 'aTargets': ['_all'] });
                        $.extend(true, options, {
                            "bSort": true,
                            'aoColumnDefs': aoColumnDefs
                        });
                    }
                }

                //orderSequence
                if (self.hasClass('table-ordering')) {
                    var data_order_list = self.attr('order_list');//data
                    var data_order_by = self.attr('order_by');//data
                    var column_index_list = data_order_list.split(',');
                    var column_index_by = data_order_by.split(',');
                    var aoColumnDefs = [];
                    for(var column_key in column_index_list) {
                        aoColumnDefs.push([parseInt(column_index_list[column_key],10), column_index_by[column_key]]);
                    }
                    $.extend(true, options, {
                        'order': aoColumnDefs
                    });
                } else {
                    /*
                                $.extend(true, options, {
                                        'ordering': false
                                });
                    */
                }

                // TableTools
                if (self.hasClass('table-tabletools')) {
                    $.extend(true, options, {
                        "sDom": "<'row'<'dataTables_header clearfix'<'col-md-4'l><'col-md-8'Tf>r>>t<'row'<'dataTables_footer clearfix'<'col-md-6'i><'col-md-6'p>>>", // T is new
                        "oTableTools": {
                            "aButtons": [
                                "copy",
                                "print",
                                "csv",
                                "xls",
                                "pdf"
                            ],
                            "sSwfPath": "plugins/datatables/tabletools/swf/copy_csv_xls_pdf.swf"
                        }
                    });
                }

                // ColVis
                if (self.hasClass('table-colvis')) {
                    $.extend(true, options, {
                        "sDom": "<'row'<'dataTables_header clearfix'<'col-md-6'l><'col-md-6'Cf>r>>t<'row'<'dataTables_footer clearfix'<'col-md-6'i><'col-md-6'p>>>", // C is new
                        "oColVis": {
                            "buttonText": "Columns <i class='icon-angle-down'></i>",
                            "iOverlayFade": 0
                        }
                    });
                }

                // If ColVis is used with checkable Tables
                if (self.hasClass('table-checkable') && self.hasClass('table-colvis')) {
                    $.extend(true, options, {
                        "oColVis": {
                            "aiExclude": [0]
                        }
                    });
                }

                if (self.hasClass('table-colored')) {
                    $.extend(true, options, {
                        "createdRow": function (row, data, index) {
                            var color = $(row).find('.row_colored').val();
                            if (color != '') {
                                $(row).css('background-color', color);
                            }
                        }
                    });
                }

                if (self.hasClass('table-statuses')) {
                    $.extend(true, options, {
                        "createdRow": function (row, data, index) {
                            var cls = $(row).find('.tr-status-class').val();
                            //console.log('class ' + cls);
                            if (cls != '') {
                                $(row).addClass(cls)
                            }
                        }
                    })
                }


                // Responsive Tables
                if (self.hasClass('table-responsive')) {
                    var responsiveHelper;
                    var breakpointDefinition = {
                        tablet: 1024,
                        phone: 480
                    };

                    // Preserve old function from $.extend above
                    // to extend a function
                    var old_fnDrawCallback = $.fn.dataTable.defaults.fnDrawCallback;

                    $.extend(true, options, {
                        bAutoWidth: false,
                        fnPreDrawCallback: function () {
                        },
                        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                            if (self.hasClass('sortable-grid')) {
                                var cell_identify = $(nRow).find('.cell_identify').val();
                                var cell_type = $(nRow).find('.cell_type').val();
                                $(nRow).attr('id', cell_type + '-' + cell_identify);
                            }
                            if (self.hasClass('table-selectable')) {
                                $(nRow).addClass('checkbox-column');
                            }
                        },
                        fnDrawCallback: function (oSettings) {
                            if (self.hasClass('catelogue-grid')) {
                                $("#list_bread_crumb").html(oSettings.json.breadcrumb || '');
                                $("#categories_counter").text(oSettings.json.categories);
                                $("#products_counter").text(oSettings.json.products);
                                $('.prod_name_double').dblclick(function() {
                                    var url_edit = $(this).data('clickDouble');
                                    $(location).attr('href',url_edit);
                                });
                            }

                            if (self.hasClass('double-grid')) {
                                $('.double-grid > tbody > tr > td').dblclick(function() {
                                    var _tmp = $(this).parent().find('.click_double');

                                    if ($(_tmp).data('clickDouble') != 'undefined' && $(_tmp).data('clickDouble') != null){
                                        var url_edit = $(_tmp).data('clickDouble');
                                        $(location).attr('href',url_edit);
                                    } else if ($(_tmp).data('clickFunction') != 'undefined' && $(_tmp).data('clickFunction') != null){
                                        try {
                                            eval( $(_tmp).data('clickFunction'));
                                        }catch (err){
                                            console.log(err);
                                        }
                                    }
                                });
                            }

                            // Extending function
                            old_fnDrawCallback.apply(this, oSettings);

                            if ( typeof productsGridInit === 'function' ) {
                                productsGridInit();
                            }
                        }
                    });
                }


                if (self.hasClass('table-no-search')) {
                    $.extend(true, options, {
                        searching: false
                    });
                }

                if (self.hasClass('table-no-pagination')) {
                    var height_texts_table = $(window).height() - 300;
                    $.extend(true, options, {
                        "searching": false,
                        "sScrollY": height_texts_table,
                        "scrollCollapse": true,
                        "paging":         false,
                    });
                }


                // Ajax
                var data_charts = self.attr('data_charts');
                var data_ajax = self.attr('data_ajax');
                if (typeof data_ajax != 'undefined') {
                    var data_type = self.attr('data_type');
                    if (typeof data_type == 'undefined') {
                        data_type = "GET";
                    }
                    $.extend(true, options, {
                        "processing": true,
                        "serverSide": true,
                        "ajax": {
                            "url" : data_ajax,
                            "type": data_type,
                            "dataSrc": function ( json ) {
                                if (typeof data_charts != 'undefined' && typeof onDraw == 'function') {
                                    onDraw(json.data);
                                }
                                if (typeof json.head == 'object' && typeof onDraw == 'function' ){
                                    onDraw(json, table);
                                }

                                if (typeof(rData) == 'object' && rData != null) rData = json;

                                return json.data;
                            },
                            "data" : function ( d ) {
                                d.id = $('#global_id').val();
                                d.filter = $('#filterForm, #filterFormHead').serialize();
                                // d.custom = $('#myInput').val();
                                // etc
                            },
                        },
                        "initComplete": function( settings, json ) {
                            if (self.attr('callback') !== 'undefined' && typeof eval(self.attr('callback')) == 'function') {
                                eval(self.attr('callback')).call(this, json);
                            }
                        },
                    });
                }


                if (typeof $tranlations == 'object'){
                    options.language = {
                        "paginate":{},
                    };
                    if ($tranlations.hasOwnProperty('DATATABLE_FIRST')) {options.language.paginate.first = $tranlations.DATATABLE_FIRST;}
                    if ($tranlations.hasOwnProperty('DATATABLE_LAST')) options.language.paginate.last = $tranlations.DATATABLE_LAST;
                    if ($tranlations.hasOwnProperty('DATATABLE_INFO')) options.language.info = $tranlations.DATATABLE_INFO;
                    if ($tranlations.hasOwnProperty('DATATABLE_INFO_EMPTY')) options.language.infoEmpty = $tranlations.DATATABLE_INFO_EMPTY;
                    if ($tranlations.hasOwnProperty('DATATABLE_EMPTY_TABLE')) options.language.emptyTable = $tranlations.DATATABLE_EMPTY_TABLE;
                }

                var multiSelect = self.hasClass('table-multi-select');
                var msLatestSelectedIdx;

                var table = $(this).dataTable(options);

                $(this).find('tbody').on( 'click', 'tr', function (event) {
                    if (multiSelect) {
                        if (event.ctrlKey || event.metaKey) {
                            $(this).toggleClass("selected");
                            if ($(this).hasClass('selected')){
                                msLatestSelectedIdx = $(this).index();
                            }
                        } else {
                            if (event.shiftKey ) {
                                $(this).addClass("selected");
                                var i, n, endIdx = $(this).index();
                                if (msLatestSelectedIdx<endIdx) {
                                    i = msLatestSelectedIdx+1;
                                    n = endIdx-1;
                                } else {
                                    i = endIdx;
                                    n = msLatestSelectedIdx-1;
                                }

                                for (i; i<=n; i++) {
                                    $($(this).siblings()[i]).addClass('selected');
                                }
                            } else {
                                $(this).addClass("selected").siblings().removeClass('selected');
                            }
                            msLatestSelectedIdx = $(this).index();
                        }

                    } else {
                        if ( $(this).hasClass('selected') ) {
                            $(this).removeClass('selected');
                            if (typeof onUnclickEvent==='function') {
                                if (onUnclickEvent(this, table, event) === false){
                                    $(this).addClass('selected');
                                }
                            }
                        } else {
                            if ( typeof onClickEvent === 'function' ) {
                                var $selectedRows = table.find('tr.selected');
                                $selectedRows.removeClass('selected');
                                $(this).addClass('selected');
                                if (onClickEvent(this, table, event) === false) {
                                    table.$('tr.selected').removeClass('selected');
                                    $selectedRows.addClass('selected')
                                }
                            }else{
                                table.$('tr.selected').removeClass('selected');
                                $(this).addClass('selected');
                            }
                        }
                    }
                } );
                $(this).find('tbody').on( 'dblclick', 'tr', function (event) {
                    if (typeof onDblClickEvent==='function') {
                        onDblClickEvent(this, table, event);
                    }
                } );

            });
        }
    }

    /**************************
     * Flot Defaults          *
     **************************/
    var defaultPlotOptions = {
        colors: [App.getLayoutColorCode('blue'), App.getLayoutColorCode('red'), App.getLayoutColorCode('green'), App.getLayoutColorCode('purple'), App.getLayoutColorCode('grey'), App.getLayoutColorCode('yellow')],
        legend: {
            show: true,
            labelBoxBorderColor: "", // border color for the little label boxes
            backgroundOpacity: 0.95 // set to 0 to avoid background
        },
        series: {
            points: {
                show: false,
                radius: 3,
                lineWidth: 2, // in pixels
                fill: true,
                fillColor: "#ffffff",
                symbol: "circle" // or callback
            },
            lines: {
                // we don't put in show: false so we can see
                // whether lines were actively disabled
                show: true,
                lineWidth: 2, // in pixels
                fill: false,
                fillColor: { colors: [ { opacity: 0.4 }, { opacity: 0.1 } ] },
            },
            bars: {
                lineWidth: 1, // in pixels
                barWidth: 1, // in units of the x axis
                fill: true,
                fillColor: { colors: [ { opacity: 0.7 }, { opacity: 1 } ] },
                align: "left", // or "center"
                horizontal: false
            },
            pie: {
                show: false,
                radius: 1,
                label: {
                    show: false,
                    radius: 2/3,
                    formatter: function(label, series){
                        return '<div style="font-size:8pt;text-align:center;padding:2px;color:white;text-shadow: 0 1px 0 rgba(0, 0, 0, 0.6);">'+label+'<br/>'+Math.round(series.percent)+'%</div>';
                    },
                    threshold: 0.1
                }
            },
            shadowSize: 0
        },
        grid: {
            show: true,
            borderColor: "#efefef", // set if different from the grid color
            tickColor: "rgba(0,0,0,0.06)", // color for the ticks, e.g. "rgba(0,0,0,0.15)"
            labelMargin: 10, // in pixels
            axisMargin: 8, // in pixels
            borderWidth: 0, // in pixels
            minBorderMargin: 10, // in pixels, null means taken from points radius
            mouseActiveRadius: 5 // how far the mouse can be away to activate an item
        },
        tooltipOpts: {
            defaultTheme: false
        },
        selection: {
            color: App.getLayoutColorCode('blue')
        }
    };

    var defaultPlotWidgetOptions = {
        colors: ['#ffffff'],
        legend: {
            show: false,
            backgroundOpacity: 0
        },
        series: {
            points: {
            }
        },
        grid: {
            tickColor: 'rgba(255, 255, 255, 0.1)',
            color: '#ffffff',
        },
        shadowSize: 1
    };

    /**************************
     * Circle Dial (Knob)     *
     **************************/
    var initKnob = function() {
        if ($.fn.knob) {
            $(".knob").knob();

            // All elements, which has no color specified, apply default color
            $('.knob').each(function () {
                if (typeof $(this).attr('data-fgColor') == 'undefined') {
                    $(this).trigger('configure', {
                        'fgColor': App.getLayoutColorCode('blue'),
                        'inputColor': App.getLayoutColorCode('blue')
                    });
                }
            });
        }
    }

    /**************************
     * Sparkline Statbox Defaults
     **************************/
    var defaultSparklineStatboxOptions = {
        type: 'bar',
        height: '19px',
        zeroAxis: false,
        barWidth: '4px',
        barSpacing: '1px',
        barColor: '#fff'
    }

    /**************************
     * ColorPicker            *
     **************************/
    var initColorPicker = function() {
        if ($.fn.colorpicker) {
            $('.bs-colorpicker').colorpicker();
        }
    }

    /**************************
     * Template               *
     **************************/
    var initTemplate = function() {
        if ($.fn.template) {
            // Set default options
            $.extend(true, $.fn.template.defaults, {

            });
        }
    }

    return {

        // main function to initiate all plugins
        init: function () {
            initBrowserDetection(); // $.browser for jQuery 1.9
            initDaterangepicker(); // Daterangepicker for dashboard
            initTooltips(); // Bootstrap tooltips
            initPopovers(); // Bootstrap popovers
            initDataTables(); // Managed Tables
            initCircularCharts(); // Easy Pie Chart
            initKnob(); // Circle Dial
            initColorPicker(); // Bootstrap ColorPicker
            //initTemplate(); // Template
        },

        getFlotDefaults: function() {
            return defaultPlotOptions;
        },

        getFlotWidgetDefaults: function() {
            return $.extend(true, {}, Plugins.getFlotDefaults(), defaultPlotWidgetOptions);
        },

        getSparklineStatboxDefaults: function() {
            return defaultSparklineStatboxOptions;
        }

    };

}();


// plugins.form-components.js

/*
 * Core script to handle all form specific plugins
 */

var FormComponents = function() {

    "use strict";

    /**************************
     * Input limiter          *
     **************************/
    var initInputlimiter = function() {
        if ($.fn.inputlimiter) {
            // Set default options
            $.extend(true, $.fn.inputlimiter.defaults, {
                boxAttach: false,
                boxId: 'limit-text',
                remText: '%n character%s remaining.',
                limitText: 'Field limited to %n character%s.',
                zeroPlural: true
            });

            // Initialize limiter
            $('textarea.limited').each(function(index, value) {
                var limitText = $.fn.inputlimiter.defaults.limitText;
                var data_limit = $(this).data('limit');
                limitText = limitText.replace(/\%n/g, data_limit);
                limitText = limitText.replace(/\%s/g, (data_limit <= 1 ? '' : 's'));

                $(this).parent().find('#limit-text').html(limitText);
                $(this).inputlimiter({
                    limit: data_limit
                });
            });
        }
    }

    /**************************
     * Uniform                *
     **************************/
    var initUniform = function() {
        if ($.fn.uniform) {
            $(':radio.uniform, :checkbox.uniform').uniform();
        }
    }

    /**************************
     * Select2                *
     **************************/
    var initSelect2 = function() {
        if ($.fn.select2) {
            // Set default options
            $.extend(true, $.fn.select2.defaults, {
                width: 'resolve'
            });

            // Initialize default select2 boxes
            $('.select2').each(function() {
                var self = $(this);
                $(self).select2(self.data());
            });

            // Initialize DataTables Select2 Boxes
            $('.dataTables_length select').select2({
                minimumResultsForSearch: "-1"
            });
        }
    }

    /**************************
     * Fileinput              *
     **************************/
    var initFileinput = function() {
        if ($.fn.fileInput) {
            // Set default options
            $.extend(true, $.fn.fileInput.defaults, {
                placeholder: 'No file selected...',
                buttontext: 'Browse...'
            });

            $('[data-style="fileinput"]').each(function () {
                var $input = $(this);
                $input.fileInput($input.data());
            });
        }
    }

    /**************************
     * Spinner                *
     **************************/
    var initSpinner = function() {
        if ($.fn.spinner) {
            $('.spinner').each(function() {
                $(this).spinner();
            });
        }
    }

    /**************************
     * Validation             *
     **************************/
    var initValidation = function() {
        if ($.validator) {
            // Set default options
            $.extend( $.validator.defaults, {
                errorClass: "has-error",
                validClass: "has-success",
                highlight: function(element, errorClass, validClass) {
                    if (element.type === 'radio') {
                        this.findByName(element.name).addClass(errorClass).removeClass(validClass);
                    } else {
                        $(element).addClass(errorClass).removeClass(validClass);
                    }
                    $(element).closest(".form-group").addClass(errorClass).removeClass(validClass);
                },
                unhighlight: function(element, errorClass, validClass) {
                    if (element.type === 'radio') {
                        this.findByName(element.name).removeClass(errorClass).addClass(validClass);
                    } else {
                        $(element).removeClass(errorClass).addClass(validClass);
                    }
                    $(element).closest(".form-group").removeClass(errorClass).addClass(validClass);

                    // Fix for not removing label in BS3
                    $(element).closest('.form-group').find('label[generated="true"]').html('');
                }
            });

            var _base_resetForm = $.validator.prototype.resetForm;
            $.extend( $.validator.prototype, {
                resetForm: function() {
                    _base_resetForm.call( this );
                    this.elements().closest('.form-group')
                        .removeClass(this.settings.errorClass + ' ' + this.settings.validClass);
                },
                showLabel: function(element, message) {
                    var label = this.errorsFor( element );
                    if ( label.length ) {
                        // refresh error/success class
                        label.removeClass( this.settings.validClass ).addClass( this.settings.errorClass );

                        // check if we have a generated label, replace the message then
                        if ( label.attr("generated") ) {
                            label.html(message);
                        }
                    } else {
                        // create label
                        label = $("<" + this.settings.errorElement + "/>")
                            .attr({"for":  this.idOrName(element), generated: true})
                            .addClass(this.settings.errorClass)
                            .addClass('help-block')
                            .html(message || "");
                        if ( this.settings.wrapper ) {
                            // make sure the element is visible, even in IE
                            // actually showing the wrapped element is handled elsewhere
                            label = label.hide().show().wrap("<" + this.settings.wrapper + "/>").parent();
                        }
                        if ( !this.labelContainer.append(label).length ) {
                            if ( this.settings.errorPlacement ) {
                                this.settings.errorPlacement(label, $(element) );
                            } else {
                                label.insertAfter(element);
                            }
                        }
                    }
                    if ( !message && this.settings.success ) {
                        label.text("");
                        if ( typeof this.settings.success === "string" ) {
                            label.addClass( this.settings.success );
                        } else {
                            this.settings.success( label, element );
                        }
                    }
                    this.toShow = this.toShow.add(label);
                }
            });
        }
    }

    /**************************
     * Multiselect            *
     **************************/
    var initMultiselect = function() {
        if ($.fn.multiselect) {
            var $TRANSLATE = {checkAllText: "Check all", uncheckAllText: "Uncheck all", noneSelectedText: "Select options", selectedText: "# selected"};
            if (typeof $tranlations == 'object'){
                if ($tranlations.hasOwnProperty('MULTISELECT_CHECK_ALL')) $TRANSLATE.checkAllText = $tranlations.MULTISELECT_CHECK_ALL;
                if ($tranlations.hasOwnProperty('MULTISELECT_UNCHECK_ALL')) $TRANSLATE.uncheckAllText = $tranlations.MULTISELECT_UNCHECK_ALL;
                if ($tranlations.hasOwnProperty('MULTISELECT_NON_SELECTED')) $TRANSLATE.noneSelectedText = $tranlations.MULTISELECT_NON_SELECTED;
                if ($tranlations.hasOwnProperty('MULTISELECT_SELECTED_TEXT')) $TRANSLATE.selectedText = $tranlations.MULTISELECT_SELECTED_TEXT;
            }
            $('.multiselect').each(function () {
                $(this).multiselect($TRANSLATE);
            });
        }
    }

    return {

        // main function to initiate all plugins
        init: function () {
            initInputlimiter(); // Input limiter
            initUniform(); // Uniform (styled radio- and checkboxes)
            initSelect2(); // Custom styled selects e.g. with search
            initFileinput(); // Custom styled file inputs
            initSpinner(); // Spinner
            initValidation(); // Validation
            initMultiselect(); // Multiselect
        }

    };

}();



// /includes/general.js

function SetFocus() {
    if (document.forms.length > 0) {
        var field = document.forms[0];
        for (i=0; i<field.length; i++) {
            if ( (field.elements[i].type != "image") &&
                (field.elements[i].type != "hidden") &&
                (field.elements[i].type != "reset") &&
                (field.elements[i].type != "submit") ) {

                document.forms[0].elements[i].focus();

                if ( (field.elements[i].type == "text") ||
                    (field.elements[i].type == "password") )
                    document.forms[0].elements[i].select();

                break;
            }
        }
    }
}

function rowOverEffect(object) {
    if (object.className == 'dataTableRow') object.className = 'dataTableRowOver';
}

function rowOutEffect(object) {
    if (object.className == 'dataTableRowOver') object.className = 'dataTableRow';
}

var editorFieldName = '';
var editorFormName = '';

function loadedHTMLAREA(form,field){
    var height = 768, width = 1024;
    editorFormName = form;
    editorFieldName = field;
    var top = (screen.height) ? (screen.height-height)/2 : 0;
    var left = (screen.width) ? (screen.width-width)/2 : 0;
    window.open('popup_editor.php','editor','status,scrollbars,resizable,width='+width+',height='+height+',top='+top+',left='+left);
}

function checkbox_addition_image_resize_click(checkbox_checked_status, text_id_image_sm) {
    if (checkbox_checked_status) {
        document.getElementById(text_id_image_sm).disabled = true;
    } else {
        document.getElementById(text_id_image_sm).disabled = false;
    }
}

function ChangeNewImageStyle(checkbox, newImageForm) {
    if(checkbox.checked) {
        document.getElementById(newImageForm).style.display = 'none';
        document.getElementById(newImageForm + '_chooser').style.display = 'none';
    } else {
        document.getElementById(newImageForm).style.display = 'block';
        document.getElementById(newImageForm + '_chooser').style.display = 'block';
    }
}

function cke_preload() {
    if (typeof(CKEDITOR) == 'object'){
        $.each(CKEDITOR.instances, function(i, e){
            if (typeof(e) == 'object'){
                $('textarea[name="'+e.name+'"]').text(e.getData());
            }
        })
    }
}
function ckeplugin(){
    if (typeof(CKEDITOR) == 'object'){
        $.each(CKEDITOR.instances, function(i, e){
            if (typeof(e) == 'object'){
                $('#'+e.name).text(e.getData());
            }
        })
    }
}

window.addEventListener('load', function () {
    const alertList = document.querySelectorAll('.alert[data-name]');
    alertList.forEach(function (alert) {
        const name = alert.attributes['data-name'].value;
        const alertsStr = localStorage.getItem('alerts');
        if (alertsStr) {
            const alerts = JSON.parse(alertsStr);
            if (alerts && alerts[name] && parseInt(alerts[name]) > (Date.now() - (3600 * 24 * 1000))) {
                alert.style.display = 'none';
            }
        }

        alert.querySelector('.btn-close').addEventListener('click', function () {
            let alertsStr = localStorage.getItem('alerts');
            let alerts = {};
            if (alertsStr) {
                alerts = JSON.parse(alertsStr);
            }
            alerts[name] = Date.now();
            alertsStr = JSON.stringify(alerts);
            localStorage.setItem('alerts', alertsStr);
        })
    })
});