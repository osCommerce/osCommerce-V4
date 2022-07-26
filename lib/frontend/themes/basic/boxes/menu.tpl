<table class="wrapper"><tr><td>
<div class="menu {$settings[0].class} w-{$settings[0].class}"{if $settings[0].params != 'Account box'} style="display: none"{/if}>
  <span class="menu-ico"></span>

  {if $is_menu == false}
    <div class="no-menu"></div>
    <script type="text/javascript">
      tl(function(){
        $('.no-menu').closest('.box').find('.edit-box').trigger('click')
      })
    </script>
  {/if}

  {$menu_htm}

</div>
    </td></tr></table>
<script type="text/javascript">
  function remove_px(str){
    if (typeof(str) != 'undefined') {
    str = str.replace('px', '');
    return Number(str);
    } else {
      return 0;
    }
  }
  function big_height(tag){
    return tag.height() + remove_px(tag.css('padding-top')) + remove_px(tag.css('padding-bottom'))
  }
  function big_width(tag){
    return tag.width() + remove_px(tag.css('padding-left')) + remove_px(tag.css('padding-right'))
  }

  tl(function(){

    var menu = $('#box-'+{$id});

    {if $settings[0].class == 'menu-slider'}

      $('.menu-slider > ul > li > a', menu).on('click', function(){
        var menu = $(this).closest('.menu-slider');
        var open_ul = $('> ul > li > ul', menu);
        open_ul.css('top', $('> ul', menu).height());
        open_ul.slideUp(300);

        if (!$(this).parent().hasClass('opened')) {
          $('> ul > li', menu).removeClass('opened');
          $(this).parent().addClass('opened');
          $('+ ul', this).slideDown(300)

          $('.close', open_ul).remove();
          open_ul.prepend('<span class="close"></span>');
          $('.close', open_ul).on('click', function(){
            $(this).closest('li').find('> a').trigger('click')
          })

        } else {
          $('> ul > li', menu).removeClass('opened');
        }

        if ($('+ ul', this).length > 0) {
          return false
        }
      });

    {/if}


    {if $settings[0].class == 'menu-style-1' || $settings[0].class == 'menu-horizontal'}
      var menu_styles = function(){
        var ul_2 = $('.menu-style-1 > ul > li > ul, .menu-horizontal > ul > li > ul', menu);
        var ul = $('.menu-style-1 > ul > li > ul ul, .menu-horizontal > ul > li > ul ul', menu);
        ul_2.css('display', 'block');
        ul.css('display', 'block').css({ left: '', right: '' });
        var li1 = $('.menu-style-1 > ul > li, .menu-horizontal > ul > li', menu);

        ul.each(function(){
          var li = $(this).parent('li');
            if (li.length) {
                var li_width = big_width(li);
                var right_edge = li.offset().left + li_width + big_width($(this));
                if (right_edge > $(window).width()) {
                    $(this).css({ left: '', right: '100%'});
                } else {
                    $(this).css({ left: '100%', right: ''});
                }
            }
        });

        ul.css('display', '');
        ul_2.css('display', '');
      };

      setTimeout(menu_styles, 100);

      $(window).on('resize', menu_styles);
      var set_menu_style = function(){
        $(window).on('resize', menu_styles)
      };

      menu.on('show', set_menu_style);
      menu.on('hide', function(){
        $('.menu-style-1 > ul > li > ul ul, .menu-horizontal > ul > li > ul ul', menu);
        $(window).off('resize', menu_styles);
      });
    {/if}



    {if $settings[0].class == 'menu-big'}
      var menu_styles2 = function(){
        $('.menu-big > ul > li > ul', menu).css('top', $('.menu-big > ul > li', menu).height());
      };
      menu_styles2();
      setTimeout(menu_styles2, 100);

      var menu_trigger = function(){
        $('.menu-big > ul > li > ul > li > ul', menu).each(function(){
          $('> li:eq(2)', this).addClass('hide-more');
          if ($('> li', this).length > 2){
            $(this).append('<li class="show-all">{$smarty.const.TEXT_SHOW_ALL|escape:javascript}</li>');
          }
        });
        $('.menu-big li.show-all', menu).on('click', function(){
          if ($(this).hasClass('show-all-hide')){
            $(this).removeClass('show-all-hide');
            $(this).html('{$smarty.const.TEXT_SHOW_ALL|escape:javascript}');
            $(this).parent().find('.hide-more').removeClass('hide-more-disable')
          } else {
            $(this).addClass('show-all-hide');
            $(this).html('{$smarty.const.TEXT_HIDE|escape:javascript}');
            $(this).parent().find('.hide-more').addClass('hide-more-disable')
          }
        });
      };
      menu_trigger();
      menu.on('show', menu_trigger);
      menu.on('hide', function(){
        $('.menu-big > ul > li > ul > li > ul > li', menu).removeClass('hide-more').removeClass('hide-more-disable');
        $('.menu-big li.show-all', menu).remove();
      });
    {/if}



    var hide_size = [];
    {foreach $hide_size as $item}
    hide_size[{$item@index}] = [{$item[0]}, {$item[1]}];
    {/foreach}

    var hide_menu = false;
    var hm = false;
    var width = $(window).width();

    var resize = function(){
      width = $(window).width();
      hm = false;
      $.each(hide_size, function(k, item){
        if (!item[0]) item[0] = 0;
        if (!item[1]) item[1] = 10000;
        if (item[0] < width && width < item[1]){
          hm = true;
        }
      });

      if (hide_menu == false && hm == true) menu.trigger('hide');
      if (hide_menu == true && hm == false) menu.trigger('show');
      hide_menu = hm;
    };
    setTimeout(resize, 100);
    $(window).on('resize', resize);

    setTimeout(function(){
      $('.menu', menu).show()
    }, 50);


    var close_menu = true;
    var body_click = function(){
      setTimeout(function(){
        if (close_menu) {
          if ($('.menu-ico', menu).hasClass('active')) {
            $('.menu-ico', menu).removeClass('active')
          }
        }
      }, 100)
    };
    var menu_click = function(){
      close_menu = false;
      setTimeout(function(){
        close_menu = true;
      }, 200)
    };

    var open_close = function(){
      if ($(this).hasClass('opened')){
        $(this).removeClass('opened')
      } else {
        $(this).addClass('opened')
      }
      return false;
    };

    var sizes = function(){
      var left = 0 - ($('.menu', menu).offset().left);
      var top = $('.menu', menu).height();
      $('.menu-ico', menu).next('ul').css({ left:left, top:top, width:$(window).width()});
    };

    menu.on('hide', function(){
      $('.menu', menu).addClass('hided');

      $('.menu-ico', menu).off('click').on('click' ,function(){
        var _this = $(this);
        if (!_this.hasClass('active')){
          _this.addClass('active')
        } else {
          _this.removeClass('active')
        }
        $('body').off('click', body_click).on('click', body_click)
        menu.off('click', menu_click).on('click', menu_click)
      });

      $('ul', menu).prev('a, .no-link').off('click', open_close).on('click', open_close);

      sizes();
      $(window).on('resize', sizes)
    });
    menu.on('show', function(){
      $('.menu', menu).removeClass('hided');
      $('.menu-ico', menu).off('click').removeClass('active');
      $('body').off('click', body_click);
      menu.off('click', menu_click);
      $('ul', menu).prev('a, .no-link').off('click', open_close).removeClass('opened');
      $('.menu-ico', menu).next('ul').css({ left:'', top:'', width:''});
      $(window).off('resize', sizes)
    });

    $('ul', menu).prev('a, .no-link').addClass('parent')


  })
</script>