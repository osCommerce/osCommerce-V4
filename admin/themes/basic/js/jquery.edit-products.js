(function(){

  $.fn.editProduct = function(options){
    var op = jQuery.extend({
      page_url: ''
    },options);

    var scroll_to;
    var history = [];
    var history_i = 0;

    var main = function() {

      var body = $('body');
      var main_box = $(this);

      $(this).html('<iframe src="' + op.page_url + '" width="100%" style="height: 1000px" frameborder="no" id="info-view"></iframe>');
      var _frame = $('#info-view');
      _frame.height($(window).height() - 150);
      _frame.on('load', function(){


        var frame = _frame.contents();
        var update_height = function(){
          _frame.height($('body', frame).height() + 150);
          $(window).scrollTop(scroll_to);
        };
        update_height();
        setTimeout(update_height, 1000);
        setTimeout(update_height, 2000);
        setTimeout(update_height, 3000);
        setTimeout(function(){
          _frame.height($('body', frame).height() + 150);
        }, 5000);

        $('a', frame).removeAttr('href');
        $('a', frame).on('click', function(){
          return false
        });
        $('form', frame).removeAttr('action');
        $('form', frame).off().on('submit', function(){
          return false
        })


      });

      $(window).on('reload-frame', function(){
        $('.popup-box-wrap').remove();
        scroll_to = $(window).scrollTop();
        _frame.remove();
        main_box.each(main);
      })
    };

    return this.each(main)
  };

})(jQuery);