(function(){
  var frame_height = 1000;
  var history = [];
  var history_i = 0;
  var newWin = false;

  var popUpPosition = function(){
    var d = ($(window).height() - $('.popup-box').height()) / 2;
    if (d < 50) d = 50;
    $('.popup-box-wrap').css('top', $(window).scrollTop() + d)
  };

  var saveSettings = function() {

    var boxSave = $('#box-save');


    window.boxInputChanges = {};

    boxSave.on('change', 'input, select, textarea', function(){
      if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
        window.boxInputChanges[$(this).attr('name')] = '';
      } else if ($(this).attr('type') == 'checkbox' && $(this).is(':checked')) {
        window.boxInputChanges[$(this).attr('name')] = 1;
      }else {
        window.boxInputChanges[$(this).attr('name')] = $(this).val();
      }
    });



    boxSave.on('submit', function(){

      window.boxInputChanges['id'] = $('input[name="id"]', this).val();
      var params = $('input[name="params"], select[name="params"]', this).val();
      if (params) {
        window.boxInputChanges['params'] = params;
      }

      var values = [];
      $.each( window.boxInputChanges, function(name, value) {
        values = values.concat({ "name": name, "value": value});
      });

      values = values.concat(
        $('.visibility input[disabled]', this).map(function() {
          return { "name": this.name, "value": 1}
        }).get()
      );

      $('.check_on_off').each(function(){
          values = values.concat({ "name": $(this).attr('name'), "value": $(this).prop( "checked" )});
      });

      var data = values.reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
      }, {});

      $.post('design/box-save', {'values': JSON.stringify(data)}, function(){ });
      setTimeout(function(){
        $(window).trigger('reload-frame')
      }, 300);
      return false
    });

  };

  $.fn.infoView = function(options){
    var op = jQuery.extend({
      page_url: '',
      page_id: '288',
      na: '',
      remove_class: '',
      theme_name: 'theme-1',
      clear_url: false
    },options);

    var applyBlocks = function(){

      var _frame = $('#info-view');
      var frame = _frame.contents();

      $('a', frame).removeAttr('href');
      $('form', frame).removeAttr('action').on('submit', function(){return false});

      if (op.remove_class.length > 0) {
        $('.' + op.remove_class, frame).each(function () {
          $(this).removeClass(op.remove_class)
        });
      }


      $('*[data-block]', frame).each(function(){
        if ($(this).html() === '') {
          $(this).html('<span class="iv-editing">empty field</span>')
        }
      });

      $('.block', frame).append('<span class="add-box add-box-single">Add Widget</span>');
      $('.block .block > .add-box', frame).remove();

      //$('.block[data-cols]', frame).append('<span class="add-box add-box-single">Add Widget</span>');

      $('.box-block', frame).append('<span class="menu-widget">' +
        '  <span class="add-box">Add Widget</span>' +
        '  <span class="edit-box" title="Edit Block"></span>' +
        '  <span class="handle" title="Move block"></span>' +
        '  <span class="export" title="Export Block"></span>' +
        '  <span class="remove-box" title="Remove Widget"></span>' +
        '</span>');
      $('.box-block > .menu-widget', frame).each(function(){
        var box = $(this).parent();
        $(this).css({
          'margin-left': box.css('padding-left'),
          'bottom': $(this).css('bottom').replace("px", "") * 1 + box.css('padding-bottom').replace("px", "")*1
        })
      });
      $('.box-block.type-1 > .menu-widget', frame).each(function(){
        var box = $(this).parent();
        $(this).css({
          'margin-left': 0,
          'left': (box.width() - $('> .block', box).width())/2 - 12
        })
      });

      $('.box, .box-block', frame).on('mouseleave mouseenter', function(e){
        $('.box-active', frame).removeClass('box-active');
        if (e.type == 'mouseleave') {
          $(this).parent().closest('.box, .box-block').addClass('box-active')
        } else if (e.type == 'mouseenter') {
          $(this).addClass('box-active')
        }
      });

      $('.box', frame).append('<span class="menu-widget">' +
        '<span class="edit-box" title="Edit Widget"></span>' +
        '<span class="edit-css" title="Edit widget styles (global for all same widgets)"></span>' +
        '<span class="handle" title="Move block"></span>' +
        '<span class="export" title="Export Block"></span>' +
        '<span class="remove-box" title="Remove Widget"></span>' +
        '</span>');

      $('.menu-widget', frame).each(function(){
        if ($(this).parent('.box').css('float') == 'right') {
          $(this).css({
              left: 'auto',
              right: 0
          })
        }
      });

      $('.block .remove-box', frame).on('click', function(){
        var blocks = {};
        var _this = $(this).closest('div[id]');
        blocks['name'] =  _this.data('name');
        blocks['theme_name'] =  op.theme_name;
        blocks['id'] = _this.attr('id');
        $.post('design/box-delete', blocks, function(){
          _this.remove();
          localStorage.getItem('page-url')
          if (newWin && typeof newWin.location.reload == 'function') newWin.location.reload(localStorage.getItem('page-url')+'&is_admin=1');
        }, 'json');
      });

      $('.menu-widget .edit-css', frame).off('click').on('click', {frame: frame}, editCss);

      $('.menu-widget .edit-box', frame).off('click').on('click', function(){
        var this_block = $(this).closest('div[id]');
        var block_id = this_block.attr('id');
        var block_name = this_block.data('name');
        var block_type = '';
        this_block.closest('div[data-type]').each(function(){
          block_type = $(this).data('type')
        });
          let widgetData = entryData.widgetList.find(i => i.name == $(this).closest('div[data-name]').data('name'));
          let widgetTitle = 'Widget settings';
          if (widgetData && widgetData.title && typeof widgetData.title === "string") {
            widgetTitle = widgetData.title;
          }


        $('body').append('<div class="popup-box-wrap"><div class="around-pop-up around-widget-settings"></div><div class="popup-box widget-settings"><div class="pop-up-close"></div><div class="pop-up-content"><div class="preloader"></div></div></div></div>');
        $('.around-pop-up, .pop-up-close').on('click', function(){
          $('.popup-box-wrap').trigger('remove').remove()
        });

        $('.popup-box:last').draggable({ handle: ".popup-heading" });

        $.get('design/box-edit', {id: block_id, name: block_name, block_type: block_type}, function(data){
          $('.pop-up-content').html(data);
          var boxSave = $('#box-save');
          /*boxSave.on('submit', saveSettings);*/
          saveSettings();
          $('.popup-buttons .btn-cancel').on('click', function(){
            $('.popup-box-wrap').trigger('remove').remove()
          });

          $('.widget-settings .popup-heading').text(widgetTitle);

          var showChanges = function(){
            var style = $('#style .style-tabs-content > .active');
            $('.changed', style).removeClass('changed');
            $('input:not([type="radio"]), select', style).each(function(){
              if (
                ($(this).val() !== '' && $(this).attr('type') != 'checkbox') ||
                ($(this).attr('type') == 'checkbox' && $(this).prop( "checked" ))
              ) {
                $(this).closest('.setting-row').find('label').addClass('changed');
                $(this).closest('label').addClass('changed');
                var id = $(this).closest('.tab-pane').attr('id');
                $('.nav a[href="#'+id+'"]').addClass('changed');
                id = $(this).closest('.tab-pane').parents('.tab-pane').attr('id');
                $('.nav a[href="#'+id+'"]').addClass('changed');
              }
            })
          };
          showChanges();
          boxSave.on('change', showChanges);

          popUpPosition();
        });

        popUpPosition();
      });

      $('.import-box', frame).each(function(){
        var block_name = $(this).closest('div[data-name]').parent().closest('div[data-name]').data('name');
        var box_id = $(this).parent().attr('id');
        $(this).dropzone({
          url: 'design/import-block?theme_name=' + op.theme_name + '&block_name=' + block_name + '&box_id=' + box_id,
          success: function(){
            $(window).trigger('reload-frame')
          },
            acceptedFiles: '.zip'
        })
      });


      var type_box = '';
      $('.block[data-type]', frame).each(function(){
        var type = $(this).data('type');
        if (type != 'header' && type != 'footer'){
          type_box = type
        }
      });
      $('body', frame).prepend('<div class="widgets-list"></div>');

      instruments(function(instruments){
        widgetsResizing(instruments, frame);
      });


      $(window).trigger('frame-ready')
    };

    var instruments = function(created){
      $('.instruments').remove();
      $('body').append('<div class="instruments"><div class="ins-heading"><div class="ins-hide"></div></div><div class="ins-ico"></div><div class="ins-content"></div></div>');

      var instruments = $('.instruments');
      instruments.draggable({ handle: ".ins-heading" });


      created($('.ins-content'));
    };

    var widgetsResizing = function(instruments, frame){
      instruments.prepend('<div class="widgets-resizing"><div class="smaller" title="Alt+\'-\'"></div><div class="scale"><span>100</span>%</div><div class="bigger disable" title="Alt+\'+\'"></div></div>');

      var bigger = $('.widgets-resizing .bigger');
      var smaller = $('.widgets-resizing .smaller');
      var scale = $('.widgets-resizing .scale span');
      var categories = $('.categories', frame);
      var scaleText = scale.text() * 1;

      smaller.off('click').on('click', function(){
        bigger.removeClass('disable');
        smaller.removeClass('disable');
        categories.css('width', categories.width())
        if (scaleText === 100) {
          scaleText = 75;
        } else if (scaleText === 75) {
          scaleText = 66;
        } else if (scaleText === 66) {
          scaleText = 50;
        } else if (scaleText <= 10) {
          smaller.addClass('disable');
          scaleText = 10;
        } else {
          scaleText = scaleText - 10;
          if (scaleText === 10) {
            smaller.addClass('disable');
          }
        }
        $('.box > *, .tab-navigation', frame).css({'zoom': scaleText+'%'});
        scale.text(scaleText);
        $('body', frame).addClass('sizing')
      });

      bigger.off('click').on('click', function(){
        bigger.removeClass('disable');
        smaller.removeClass('disable');
        if (scaleText >= 100) {
          scaleText = 100;
          bigger.addClass('disable');
          categories.css('width', '')
        } else if (scaleText === 75) {
          scaleText = 100;
          bigger.addClass('disable');
          $('body', frame).removeClass('sizing')
        } else if (scaleText === 66) {
          scaleText = 75;
        } else if (scaleText === 50) {
          scaleText = 66;
        } else {
          scaleText = scaleText + 10;
        }
        $('.box > *, .tab-navigation', frame).css({'zoom': scaleText+'%'});
        scale.text(scaleText);
      });

      var smallerWidgets = function(){
        smaller.trigger('click')
      };
      $(document).bind('keydown', 'Alt+-', smallerWidgets);
      $(frame).bind('keydown', 'Alt+-', smallerWidgets);

      var biggerWidgets = function(){
        bigger.trigger('click')
      };
      $(document).bind('keydown', 'Alt+=', biggerWidgets);
      $(frame).bind('keydown', 'Alt+=', biggerWidgets);
    };



    var editCss = function(event){
      var frame = event.data.frame;

      var thisBox = $(this).closest('div[id]');
      var boxId = thisBox.attr('id');
      var boxName = thisBox.data('name');
      var boxType = '';
      thisBox.closest('div[data-type]').each(function(){
        boxType = $(this).data('type')
      });

      var windowWidth = $('body', frame).width();
      var widgetWidth = thisBox.width();

      var k = 1.2;
      if (widgetWidth < 300) k = 1.7;
      var popupWidth = widgetWidth * k;
      if (popupWidth > windowWidth - 20) {
        popupWidth = windowWidth - 20;
      }
      var popupLeft = (windowWidth - popupWidth) / 2;
      var popupTop = thisBox.offset().top;

      $('body', frame).append('' +
        '<div class="popup-widget-style">' +
        '  <div class="pop-up-close"></div>' +
        '  <div class="popup-heading">Edit "' + boxName + '" widget styles</div>' +
        '  <div class="popup-content"><div class="' + thisBox.attr('class') + '"></div></div>' +
        '</div>');
      var popupWidgetStyle = $('.popup-widget-style:last', frame);
      popupWidgetStyle.css({
        left: popupLeft,
        width: popupWidth,
        top: popupTop
      });
      $('.pop-up-close', popupWidgetStyle).on('click', function(){
        popupWidgetStyle.remove()
      });
      popupWidgetStyle.draggable({ handle: ".popup-heading" });

      var box = $('.popup-content > div', popupWidgetStyle);
      box.removeClass('box');
      box.removeClass('box-active');
      var thisBoxHtml = $(thisBox.html());
      thisBoxHtml = thisBoxHtml.not('script');
      thisBoxHtml = thisBoxHtml.not('.menu-widget');
      thisBoxHtml = thisBoxHtml.not('.move-block');
      $('script', thisBoxHtml).remove();
      box.append(thisBoxHtml);

      $('input, select, textarea, img', popupWidgetStyle).each(function(){
        $(this).wrap('<div class="input-helper"></div>')
      });

      var widgetClass = box.attr('class');
      $('*:hidden', popupWidgetStyle).show();
      $('.popup-content > div *[class]:not(input, select, textarea, img, .products-listing, products-listing *)', popupWidgetStyle).each(function(){
        var elementClass = $(this).attr('class');
        if ($(this).hasClass('input-helper')) {
          elementClass = $('input, select, textarea, img', this).attr('class');
        }
        if (widgetClass && elementClass) {
          widgetClass = widgetClass.replace(/\s+/g, ".");
          elementClass = elementClass.replace(/\s+/g, ".");
          $(this)
            .addClass('edit-class')
            .attr('data-class', '.' + widgetClass + ' .' + elementClass);
        }

        if ($(this).css('display') == 'inline') {
          $(this).css({display: 'inline-block', 'vertical-align': 'top'})
        }
      });
      $('*[data-class]', popupWidgetStyle)
        .append('<span class="menu-widget"><span class="edit-box" title="Edit Block"></span></span>')
        .hover(function(){
          $(this).addClass('active')
        }, function(){
          $(this).removeClass('active')
        })
        .each(function(){
          $('.edit-box', this).attr('title', $(this).data('class'))
        });


      $('.edit-box', popupWidgetStyle).on('click', function(e){
        $('.popup-draggable').remove();

        $('body').append('<div class="popup-draggable" style="left:'+(e.pageX*1+200)+'px; top: '+(e.pageY*1+200)+'px"><div class="pop-up-close"></div><div class="preloader"></div></div>');
        var popup_draggable = $('.popup-draggable');
        popup_draggable.css({
          left: ($(window).width() - popup_draggable.width())/2,
          top: $(window).scrollTop() + 200
        });
        $('.pop-up-close').on('click', function(){
          popup_draggable.remove()
        });
        var selector = $(this).parent().parent().data('class');

        $.get('design/style-edit', {data_class: selector, theme_name: op.theme_name}, function(data){
          popup_draggable.html(data);
            saveStyles()
          $('.popup-content').prepend('<span class="popup-heading-small-text">'+selector+'</span>');
          $('.pop-up-close').on('click', function(){
            popup_draggable.remove();
            $('#dynamic-style', frame).remove()
          });
          $( ".popup-draggable" ).draggable({ handle: ".popup-heading" });

          $('#dynamic-style', frame).remove();
          $('head', frame).append('<style id="dynamic-style"></style>');
          var boxSave = $('#box-save');
          boxSave.on('change', function(){
            $.post('design/demo-styles', $(this).serializeArray(), function(data){
              $('#dynamic-style', frame).html(data);
            })
          });

          var showChanges = function(){
            $('.changed', boxSave).removeClass('changed');
            $('input, select', boxSave).each(function(){
              if ($(this).val() !== '') {
                $(this).closest('.setting-row').find('label').addClass('changed');
                var id = $(this).closest('.tab-pane').attr('id');
                $('.nav a[href="#'+id+'"]').addClass('changed');
                id = $(this).closest('.tab-pane').parents('.tab-pane').attr('id');
                $('.nav a[href="#'+id+'"]').addClass('changed');
              }
            })
          };
          showChanges();
          boxSave.on('change', showChanges);

        });


        popup_draggable.draggable(/*{ handle: "p" }*/);
      })

    };


    var main = function() {

      var body = $('body');

      if (op.clear_url) localStorage.setItem('page-url', '');
      if (localStorage.getItem('page-url') == undefined || localStorage.getItem('page-url') == 'undefined' || localStorage.getItem('page-url') == ''){
        var url = op.page_url;
        localStorage.setItem('page-url', url);
        history[history_i] = url;
      } else {
        url = localStorage.getItem('page-url');
      }
      op.page_url = url;

      var generalPage = false;

      if (!generalPage) {
          $('.nav-tabs a[href="#any"]').trigger('click')
          $('.any-page').val(op.page_url)
      }

      $(this).html('<iframe src="' + op.page_url + '" width="100%" frameborder="no" id="info-view"></iframe>');
      var _frame = $('#info-view');
      _frame.on('load', function(){

        var frame = _frame.contents();
        $('body', frame).addClass('edit-blocks');

        applyBlocks();


        $('.btn-preview').on('click', function(){
          $('.btn-edit').show();
          $('.btn-preview').hide();
          $('body', frame).removeClass('edit-blocks');
          $('body', frame).addClass('view-blocks');
        });
        $('.btn-edit').on('click', function(){
          $('.btn-preview').show();
          $('.btn-edit').hide();
          $('body', frame).addClass('edit-blocks');
          $('body', frame).removeClass('view-blocks');
        });

        var clickPreview = function(){
          if ($('body', frame).hasClass('edit-blocks')){
            $('.btn-edit').show();
            $('.btn-preview').hide();
            $('body', frame).removeClass('edit-blocks');
            $('body', frame).addClass('view-blocks');
          } else {
            $('.btn-preview').show();
            $('.btn-edit').hide();
            $('body', frame).addClass('edit-blocks');
            $('body', frame).removeClass('view-blocks');
          }
        };
        $(document).bind('keydown', 'Alt+p', clickPreview);
        $(frame).bind('keydown', 'Alt+p', clickPreview);

        $('.btn-preview-2').on('click', function(){
          newWin = window.open(localStorage.getItem('page-url')+'&is_admin=1', "Preview", "left=0,top=0,width=1200,height=900,location=no");
        });

          $(window).trigger('reloaded-frame')
      });

      $(window).off('reload-frame').on('reload-frame', reloadFrame);

      function reloadFrame(){
          if (newWin && typeof newWin.location.reload == 'function') {
              newWin.location.reload(localStorage.getItem('page-url')+'&is_admin=1');
          }
          $('.popup-box-wrap').trigger('remove').remove();

          $('.info-view').addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>')

          var _frame = $('#info-view');
          _frame.parent().css('position', 'relative');
          _frame.attr('id', 'info-view-1');
          _frame.css({
              'position': 'relative',
              'z-index': 2
          });
          _frame.after('<iframe src="' + localStorage.getItem('page-url') + '" width="100%" frameborder="no" id="info-view"></iframe>');
          var _frame_new = $('#info-view');
          _frame_new.css({
              'position': 'absolute',
              'left': '0',
              'top': '0'
          });
          _frame_new.on('load', function(){
              var frame = _frame.contents();
              var frame_new = _frame_new.contents();
            $('.info-view').removeClass('hided-box')
            $('.info-view > .hided-box-holder').remove();
            frame_new.scrollTop(frame.scrollTop())
              $('body', frame_new).addClass('edit-blocks');
              applyBlocks();
              setTimeout(function(){
                  _frame.remove();
                  _frame_new.css({
                      'position': 'relative'
                  });
                  $(window).trigger('reloaded-frame')
              }, 100);
          });
      }
    };

    return this.each(main)
  };

})(jQuery);

function sortWidgets(widgetA, widgetB) {
      if (widgetA.title.toLowerCase() > widgetB.title.toLowerCase()) {
          return 1
      } else {
          return -1
      }
}



function saveStyles () {

    var boxSave = $('#box-save');


    window.boxInputChanges = {};

    boxSave.on('change blur click keyup', 'input, select, textarea', function(){
        if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
            window.boxInputChanges[$(this).attr('name')] = '';
        } else if ($(this).attr('type') == 'checkbox' && $(this).is(':checked')) {
            window.boxInputChanges[$(this).attr('name')] = 1;
        }else {
            window.boxInputChanges[$(this).attr('name')] = $(this).val();
        }
    });



    boxSave.on('submit', function(){

        window.boxInputChanges['id'] = $('input[name="id"]', this).val();
        var params = $('input[name="params"], select[name="params"]', this).val();
        if (params) {
            window.boxInputChanges['params'] = params;
        }

        var values = [];
        $.each( window.boxInputChanges, function(name, value) {
            values = values.concat({ "name": name, "value": value});
        });

        values = values.concat(
            $('.visibility input[disabled]', this).map(function() {
                return { "name": this.name, "value": 1}
            }).get()
        );

        $('.check_on_off').each(function(){
            values = values.concat({ "name": $(this).attr('name'), "value": $(this).prop( "checked" )});
        });

        var data = values.reduce(function(obj, item) {
            obj[item.name] = item.value;
            return obj;
        }, {});

        $.post('design/style-save', {
            'values': JSON.stringify(data),
            'theme_name': $('input[name="theme_name"]', this).val(),
            'data_class': $('input[name="data_class"]', this).val()
        }, function(){
            $(window).trigger('reload-frame')
        });
        $('.popup-draggable').remove();
        return false
    });

};