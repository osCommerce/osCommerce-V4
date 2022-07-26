{use class="frontend\design\Info"}
<div style="margin: 30px 0 30px;">

  <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>
  <div class="" style="min-height: 60px" id="demo-menu-big">
    <div class="menu menu-big">
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
    <span class="menu-ico"></span>
    <ul{Info::dataClass('.menu-big > ul')}>
      <li{Info::dataClass('.menu-big > ul > li.active')}>
        <a{Info::dataClass('.menu-big > ul > li.active > a')}>Item 1 (level 1, active)</a>
        <ul{Info::dataClass('.menu-big > ul > li.active > ul')}>
          <li{Info::dataClass('.menu-big > ul > li > ul > li.active')}>
            <a{Info::dataClass('.menu-big > ul > li > ul > li.active > a')}>Item 4 (level 2, active)</a>
            <ul{Info::dataClass('.menu-big > ul > li > ul > li.active > ul')}>
              <li{Info::dataClass('.menu-big > ul > li > ul > li > ul li.active')}><a{Info::dataClass('.menu-big > ul > li > ul > li > ul li.active a')}>Item 6 (level 3, active)</a></li>
              <li{Info::dataClass('.menu-big > ul > li > ul > li > ul li')}><a{Info::dataClass('.menu-big > ul > li > ul > li > ul a')}>Item 7 (level 3)</a></li>
              <li{Info::dataClass('.menu-big > ul > li > ul > li > ul li')}><a{Info::dataClass('.menu-big > ul > li > ul > li > ul a')}>Item 8 (level 3)</a></li>
            </ul>
          </li>
          <li{Info::dataClass('.menu-big > ul > li > ul > li')}>
            <a{Info::dataClass('.menu-big > ul > li > ul > li > a')}>Item 5 (level 2)</a>
          </li>
        </ul>
      </li>
      <li{Info::dataClass('.menu-big > ul > li')}>
        <a{Info::dataClass('.menu-big > ul > li > a')}>Item 2 (level 1)</a>
      </li>
      <li{Info::dataClass('.menu-big > ul > li')}>
        <a{Info::dataClass('.menu-big > ul > li > a')}>Item 3 (level 1)</a>
      </li>
    </ul>
  </div>


  <script type="text/javascript">

    tl(function(){
      var menu = $('#demo-menu-big');

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
    })
  </script>

</div>