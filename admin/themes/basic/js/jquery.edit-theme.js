(function(){

  $.fn.editTheme = function(options){
    var op = jQuery.extend({
      page_url: '',
      theme_name: 'theme-1'
    },options);

    var scroll_to;

    var main = function() {

      var body = $('body');
      var main_box = $(this);

      var page_url = main_box.data('url');
      if (!page_url) {
        page_url = op.page_ur;
      }

      $('.tab-links div[data-href="'+localStorage.getItem('page-url')+'"]').each(function(){
        $('.tab-links div').removeClass('active');
        $(this).addClass('active')
      });

      $(this).html('<iframe src="' + page_url + '" width="100%" height="1000" frameborder="no" scrolling="no"></iframe>');
      var _frame = $('iframe', this);
      _frame.on('load', function(){

        var frame = _frame.contents();
        var update_height = function(){
          var h = $('html', frame).height();
          _frame.height(h);
        };
        for (var i = 500; i < 3500; i += 500) {
          setTimeout(update_height, i);
        }

        $('a', frame).removeAttr('href');

        $('body', frame).addClass('edit-theme');
        $('.btn-preview').on('click', function(){
          $('.btn-edit').show();
          $('.btn-preview').hide();
          $('body', frame).removeClass('edit-theme');
          $('body', frame).addClass('view-blocks');
        });
        $('.btn-edit').on('click', function(){
          $('.btn-preview').show();
          $('.btn-edit').hide();
          $('body', frame).addClass('edit-theme');
          $('body', frame).removeClass('view-blocks');
        });

        $('form', frame).removeAttr('action').on('submit', function(){return false});


        $('*[data-class]', frame)
          .append('<span class="menu-widget"><span class="edit-box" title="Edit Block"></span></span>')
          .hover(function(){
            //$('*[data-class]', frame).removeClass('active');
            $(this).addClass('active')
          }, function(){
            $(this).removeClass('active')
          })
          .each(function(){
            $('.edit-box', this).attr('title', $(this).data('class'))
          });

        $('.edit-box', frame).on('click', function(e){
          $('.popup-draggable').remove();
          _frame.parent().addClass('active');
          
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
              saveSettings();
            $('.popup-content').prepend('<span class="popup-heading-small-text">'+selector+'</span>');

            $('.pop-up-close').on('click', function(){
              popup_draggable.trigger('remove').remove();
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


      });

      $(window).off('reload-frame').on('reload-frame', function(){
        $('.popup-box-wrap').remove();
        scroll_to = $(window).scrollTop();
        _frame.remove();
        main_box.each(main);
      })
    };

    return this.each(main)
  };

})(jQuery);



function saveSettings () {

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
        setTimeout(function(){
        }, 300);
        return false
    });

};