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
    success: function(data, popup_box){
      var n = $(window).scrollTop();
      $('.pop-up-content:last').html(data);
      $(window).scrollTop(n);
      op.position(popup_box);
      op.loaded();
        var inp = $('.pop-up-content:last').find('input:visible:first').focus().get(0);
        if (inp) {
            var val = inp.value;
            inp.value = '';
            inp.value = val;
        }
    },
    close:  function(){
      $('.pop-up-close').click(function(){
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
      if (d < 0) d = 30;
      $('.popup-box-wrap').css('top', $(window).scrollTop() + d);
    },
    opened: function(){}
  },options);

  var body = $('body');
  var html = $('html');



  return this.each(function() {
    var _action = '';
    var _event = '';
    if ($(this).context.localName == 'a'){
      if (!op.event) _event = 'click';
      if (!op.action) _action = 'href'
    } else if ($(this).context.localName == 'form') {
      if (!op.event) _event = 'submit';
      if (!op.action) _action = 'action'
    }
    if (op.event == 'show') _event = 'load';

    jQuery(this).off(_event).on(_event, function(){
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
      if (op.event == 'show' && !op.only_show){
        op.success($(this).html(), popup_box);
      }else if (op.event == 'show' && op.only_show){
        op.success(op.data, popup_box);
      } else {
        if ($(this).context.localName == 'form'){
          var _data = $(this).serializeArray();
          _data.push(op.data);
          _data.push({name: 'popup', value: 'true'});
        } else {
          //var _data = $.extend({'ajax': 'true'}, op.data)
          var _data = op.data
        }
        var _type = '';
        if (!op.type && $(this).context.localName == 'form') {
          _type = $(this).attr('method')
        } else {
          _type = 'GET'
        }
        if (op.dataType == 'jsonp') {
          _data = 'encode_date='+base64_encode($.param(_data))
        }
        var _this = $(this);
        //we need ajax popup with hash in URL!!! but possible with jquery 3+ (1.10 now :()
        //if (url.search('#') == 0){
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

$.fn.validate = function(options){
    op = $.extend({
        onlyCheck: false
    },options);
    $(this).closest('form').removeClass('not-valid');
    return this.each(function() {
        var _this = $(this);
        var message = _this.data('required');
        var pattern = _this.data('pattern');
        var confirmation = _this.data('confirmation');

        var check = function(){
            var error = false;
            $(this).find('.preloader-holder').hide();
            $(this).find('button[type="submit"]').show();
            if (_this.hasClass('skip-validation')) return true;
            if (pattern != undefined){
                if (pattern == 'email'){
                    pattern = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                }
                if (_this.val().search(pattern) == -1){
                    error = true;
                }
            } else if (confirmation != undefined){
                if (_this.parents('form').find(confirmation).val() != _this.val()) {
                    error = true;
                }
            } else {
                if (_this.val() == 0 || _this.val() == '') {
                    error = true;
                }

            }
            if (error){
                if (!_this.hasClass('required-error')) {
                    _this.addClass('required-error');
                    _this.after('<div class="required-message-wrap"><div class="required-message">' + message + '</div></div>');
                    _this.next().find('.required-message').hide().slideDown(300);
                    _this.on('keyup', check);
                    if (op.onlyCheck) {
                        _this.on('change', check);
                    }
                }

                _this.closest('form').addClass('not-valid');
                return false
            } else {
                _this.removeClass('required-error');
                var this_next = _this.next('.required-message-wrap');
                this_next.find('.required-message').slideUp(300, function(){
                    this_next.remove()
                });
                _this.off('keyup', check);
                if (op.onlyCheck) {
                    _this.off('change', check);
                }
                if ($(this).is('form') == true) {
                    $(this).find('button[type="submit"]').hide();
                    $(this).find('.preloader-holder').show();
                }
            }
        };

        if (message != undefined){
            if (op.onlyCheck){
                _this.on('check', check);
            } else {
                _this.on('change', check);
                _this.on('check', check);
                _this.parents('form').off('submit').on('submit', check);
            }
        }
    })
};

function alertMessage(data, className){
  const $pupUp = $('<div class="popup-box-wrap ' + (className ? className + '-wrap' : '') + '"><div class="around-pop-up"></div><div class="popup-box"><div class="pop-up-close"></div><div class="pop-up-content ' + (className ? className : '') + '"></div></div></div>');
  $('body').append($pupUp);

  $('.pop-up-content', $pupUp).append(data);

  const $inp =  $('.popup-box', $pupUp).find('input:visible:first').focus().get(0);
  if ($inp) {
    const val = $inp.value;
    $inp.value = '';
    $inp.value = val;
  }

  let d = ($(window).height() - $('.popup-box', $pupUp).height()) / 2;
  if (d < 0) d = 0;
  $pupUp.css('top', $(window).scrollTop() + d);

  $('.pop-up-close, .around-pop-up', $pupUp).click(function(e){
    e.preventDefault();
    setTimeout(() => $pupUp.remove(), 0);
  });
  $('.popup-box', $pupUp).on('click', '.btn-cancel', function(e){
    e.preventDefault();
    setTimeout(() => $pupUp.remove(), 0);
  });

  return $pupUp
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
    $('.tl-all-pages-block ul li a[data-toggle="tab"]').on('click', function () {
    // click on "all pages" tab - activate tab and scroll
        var listId = $(this).parent().parent().attr('id') ;
        if (typeof listId !== "undefined") {
            listId = listId.replace(/_scr$/, '');
            $('#' + listId + ' li.active').removeClass('active');
            $('#' + listId + ' a[href="' + $(this).attr('href') + '"]').parent().addClass('active');
        } else {
            $('.nav-tabs-scroll li.active').removeClass('active');
            $('.nav-tabs-scroll a[href="' + $(this).attr('href') + '"]').parent().addClass('active');
        }
        $('.nav-tabs-scroll').scrollingTabs('scrollToActiveTab');
    });

    $('.nav-tabs-scroll a[data-toggle="tab"]').on('click', function () {
        var listId = $(this).parent().parent().attr('id') ;
        if (typeof listId !== "undefined") {
            $('#' + listId + '_scr li.active').removeClass('active');
            $('#' + listId + '_scr a[href="' + $(this).attr('href') + '"]').parent().addClass('active');
        } else {
            $('.tl-all-pages-block ul li.active').removeClass('active');
            $('.tl-all-pages-block ul li a[href="' + $(this).attr('href') + '"]').parent().addClass('active');
        }
    });

    $('.nav-tabs-scroll').scrollingTabs().on('ready.scrtabs', function () {
        $('.tab-content').show();
    });
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        $('.nav-tabs-scroll').scrollingTabs('refresh');
    });
    $('.tp-all-pages-btn-wrapp').hover(function() {
        var parentWidth = $(this).parent().parent().css('width');
        $(this).next().css('max-width', parentWidth);
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
          if ($input.val()!=='') $input.val('');
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
            if ($input.val()!=='') $input.val('');
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

    $('[data-toggle=tab]').click(function() {
        let h = $(this).attr('href');
        if (h != undefined) {
          if (h.substr(0,1) == '#') {
            history.replaceState(undefined, undefined, window.location.pathname + window.location.search + $(this).attr('href'));
          }
        }
      })

    //should be done last (when all shown.bs.tab binded)
    if (location.hash.length) {
      setTimeout(function() {
        let list = location.hash.split('_');
        if (Array.isArray(list)) {
          let a = '';
          $.each(list, function(i,e) {
            if(a.length==0) {
              a = e;
            } else {
              a = a + '_' + e;
            }
            $('a[href=' + a + ']').tab('show');
            $('a[href=' + a + ']').trigger('shown.bs.tab');
          })
        }}, 500);
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
                    }
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
  var option = jQuery.extend({
                      showClose: true,
                      useCurrent: 'day',
                      icons: {
                        close: 'bdtp-ok'
                      },
                      format: 'DD MMM YYYY h:mm A'
                      //format: '{common\helpers\Date::DATE_FORMAT_DATEPTIMEICKER_PHP|escape:'html'}'
                  },options);
  try {
    $(this).datetimepicker(option)
      .on("dp.show", function(e) {
        /*if ($(this).val()=='') {
          var d = new Date();
          var yyyy = new Intl.DateTimeFormat('en', { year: 'numeric' }).format(d);
          var mmm = new Intl.DateTimeFormat('en', { month: 'short' }).format(d);
          var dd = new Intl.DateTimeFormat('en', { day: '2-digit' }).format(d);
          $(this).data("DateTimePicker").date(dd + " " + mmm + " " + yyyy + " 00:00:00");
        }*/
        $('.bdtp-ok').html("OK"); //2do translate
      });
  } catch (e) {
    console.log(e);
  }

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