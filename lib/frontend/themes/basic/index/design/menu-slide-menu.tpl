{use class="frontend\design\Info"}
<div style="margin: 30px 0 30px;">
  {Info::addBlockToWidgetsList('menu-slider')}

  <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>
  <div class="" style="min-height: 60px" id="demo-menu-slider">
    <div class="menu menu-slider w-menu-slider">
      <span class="menu-ico"></span>
      <ul>
        <li class="active">
          <a>Item 1 (level 1)</a>
          <ul>
            <li class="active">
              <a>Item 4 (level 2)</a>
              <ul>
                <li class="active"><a>Item 6 (level 3)</a></li>
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
  <div class="demo-edit-menu-slider" style="min-height: 300px">
    <span class="menu-ico"{Info::dataClass('.w-menu-slider .menu-ico')}></span>
    <ul{Info::dataClass('.w-menu-slider > ul')}>
      <li{Info::dataClass('.w-menu-slider > ul > li')}>
        <a{Info::dataClass('.w-menu-slider > ul > li > a')}>Item 1 (level 1)</a>
      </li>
      <li{Info::dataClass('.w-menu-slider > ul > li')}>
        <a{Info::dataClass('.w-menu-slider > ul > li > a')}>Item 2 (level 1)</a>
      </li>
      <li{Info::dataClass('.w-menu-slider > ul > li')}>
        <a{Info::dataClass('.w-menu-slider > ul > li > a')}>Item 3 (level 1)</a>
      </li>
    </ul>
    <ul>
        <ul{Info::dataClass('.w-menu-slider > ul > li > ul')}>
          <span class="close"{Info::dataClass('.w-menu-slider .close')} style="position: absolute"></span>
          <li{Info::dataClass('.w-menu-slider > ul > li > ul > li')}>
            <a{Info::dataClass('.w-menu-slider > ul > li > ul > li > a')}>Item 4 (level 2)</a>
            <ul{Info::dataClass('.w-menu-slider > ul > li > ul > li > ul')}>
              <li{Info::dataClass('.w-menu-slider > ul > li > ul > li > ul li')}><a{Info::dataClass('.w-menu-slider > ul > li > ul > li > ul li a')}>Item 6 (level 3)</a></li>
              <li{Info::dataClass('.w-menu-slider > ul > li > ul > li > ul li')}><a{Info::dataClass('.w-menu-slider > ul > li > ul > li > ul a')}>Item 7 (level 3)</a></li>
              <li{Info::dataClass('.w-menu-slider > ul > li > ul > li > ul li')}><a{Info::dataClass('.w-menu-slider > ul > li > ul > li > ul a')}>Item 8 (level 3)</a></li>
            </ul>
          </li>
          <li{Info::dataClass('.w-menu-slider > ul > li > ul > li')}>
            <a{Info::dataClass('.w-menu-slider > ul > li > ul > li > a')}>Item 5 (level 2)</a>
          </li>
        </ul>
    </ul>
  </div>


  <script type="text/javascript">

    tl(function(){
      var menu = $('#demo-menu-slider');

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
    })
  </script>

</div>