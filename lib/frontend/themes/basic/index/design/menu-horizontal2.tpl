{use class="frontend\design\boxes\NewProducts"}
{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{Info::addBlockToWidgetsList('menu-horizontal')}

<div style="margin: 30px 0 30px;">

  <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>
  <div class="" style="min-height: 60px" id="demo-menu-horizontal">
    <div class="menu menu-horizontal w-menu-horizontal">
      <span class="menu-ico"></span>
      <ul>
        <li class="active">
          <a>Item 1 (level 1, active)</a>
          <ul>
            <li class="active">
              <a>Item 4 (level 2, active)</a>
              <ul>
                <li class="active"><a>Item 6 (level 3, active)</a></li>
                <li><a>Item 7 (level 3)</a></li>
                <li><a>Item 8 (level 3)</a></li>
              </ul>
            </li>
            <li>
              <a>Item 5 (level 2)</a>
            </li>
          </ul>
        </li>
        <li>
          <a>Item 2 (level 1)</a>
        </li>
        <li>
          <a>Item 3 (level 1)</a>
        </li>
      </ul>
    </div>
  </div>

  <div class="demo-heading-3">{$smarty.const.EDIT}</div>
  <div class="demo-edit-menu" style="min-height: 300px">
    <span class="menu-ico"{Info::dataClass('.w-menu-horizontal .menu-ico')}></span>
    <ul{Info::dataClass('.w-menu-horizontal > ul')}>
      <li{Info::dataClass('.w-menu-horizontal > ul > li')}>
        <a{Info::dataClass('.w-menu-horizontal > ul > li > a, .w-menu-horizontal > ul > li > .no-link')}>Item 1 (level 1)</a>
        <ul{Info::dataClass('.w-menu-horizontal > ul > li > ul')}>
          <li{Info::dataClass('.w-menu-horizontal > ul > li > ul > li')}>
            <a{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > a, .w-menu-horizontal > ul > li > ul > li > .no-link')}>Item 4 (level 2)</a>
            <ul{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > ul')}>
              <li{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > ul li')}>
                <a{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > ul li a, .w-menu-horizontal > ul > li > ul > li > ul li .no-link')}>Item 6 (level 3)</a>
              </li>
              <li{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > ul li')}>
                <a{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > ul a, .w-menu-horizontal > ul > li > ul > li > ul .no-link')}>Item 7 (level 3)</a>
              </li>
              <li{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > ul li')}>
                <a{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > ul a, .w-menu-horizontal > ul > li > ul > li > ul .no-link')}>Item 8 (level 3)</a>
              </li>
            </ul>
          </li>
          <li{Info::dataClass('.w-menu-horizontal > ul > li > ul > li')}>
            <a{Info::dataClass('.w-menu-horizontal > ul > li > ul > li > a, .w-menu-horizontal > ul > li > ul > li > .no-link')}>Item 5 (level 2)</a>
          </li>
        </ul>
      </li>
      <li{Info::dataClass('.w-menu-horizontal > ul > li')}>
        <a{Info::dataClass('.w-menu-horizontal > ul > li > a, .w-menu-horizontal > ul > li > .no-link')}>Item 2 (level 1)</a>
      </li>
      <li{Info::dataClass('.w-menu-horizontal > ul > li')}>
        <a{Info::dataClass('.w-menu-horizontal > ul > li > a, .w-menu-horizontal > ul > li > .no-link')}>Item 3 (level 1)</a>
      </li>
    </ul>
  </div>


  <script type="text/javascript">

    tl(function(){
      var menu = $('#demo-menu-horizontal');
      var menu_styles = function(){
        $('.menu-horizontal > ul > li > ul', menu).css('top', $('.menu-horizontal > ul > li', menu).height());
        var ul_2 = $('.menu-horizontal > ul > li > ul', menu);
        var ul = $('.menu-horizontal > ul > li > ul ul', menu);
        ul_2.css('display', 'block');
        ul.css('display', 'block').css({ left: '', right: '' });
        var li1 = $('.menu-horizontal > ul > li', menu);
        ul_2.css('top', big_height(li1) - 1);

        ul.each(function(){
          var li = $(this).parent('li');
          var li_width = big_width(li);
          var right_edge = li.offset().left + li_width + big_width($(this));
          if (right_edge > $(window).width()){
            $(this).css({ left: '', right: li_width });
          } else {
            $(this).css({ left: li_width, right: '' });
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
        $('.menu-horizontal > ul > li > ul ul', menu);
        $(window).off('resize', menu_styles);
      });
    })
  </script>

</div>