{use class="frontend\design\boxes\NewProducts"}
{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{Info::addBlockToWidgetsList('menu-simple')}
<div style="margin: 30px 0 30px;">

  <div class="styles-row">
    <div class="styles-col-2">

      <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>
      <div class="" style="min-height: 60px" id="demo-menu-simple">
        <div class="menu menu-simple w-menu-simple">
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

    </div>
    <div class="styles-col-2">

      <div class="demo-heading-3">{$smarty.const.EDIT}</div>
      <div class="demo-edit-menu-vertical" style="min-height: 300px">
        <span class="menu-ico"></span>
        <ul{Info::dataClass('.w-menu-simple > ul')}>
          <li{Info::dataClass('.w-menu-simple > ul > li')}>
            <a{Info::dataClass('.w-menu-simple > ul > li > a, .w-menu-simple > ul > li > .no-link')}>Item 1 (level 1)</a>
            <ul{Info::dataClass('.w-menu-simple > ul > li > ul')}>
              <li{Info::dataClass('.w-menu-simple > ul > li > ul > li')}>
                <a{Info::dataClass('.w-menu-simple > ul > li > ul > li > a, .w-menu-simple > ul > li > ul > li > .no-link')}>Item 4 (level 2)</a>
                <ul{Info::dataClass('.w-menu-simple > ul > li > ul > li > ul')}>
                  <li{Info::dataClass('.w-menu-simple > ul > li > ul > li > ul li')}>
                    <a{Info::dataClass('.w-menu-simple > ul > li > ul > li > ul li a, .w-menu-simple > ul > li > ul > li > ul li .no-link')}>Item 6 (level 3)</a>
                  </li>
                  <li{Info::dataClass('.w-menu-simple > ul > li > ul > li > ul li')}>
                    <a{Info::dataClass('.w-menu-simple > ul > li > ul > li > ul a, .w-menu-simple > ul > li > ul > li > ul .no-link')}>Item 7 (level 3)</a>
                  </li>
                  <li{Info::dataClass('.w-menu-simple > ul > li > ul > li > ul li')}>
                    <a{Info::dataClass('.w-menu-simple > ul > li > ul > li > ul a, .w-menu-simple > ul > li > ul > li > ul .no-link')}>Item 8 (level 3)</a>
                  </li>
                </ul>
              </li>
              <li{Info::dataClass('.w-menu-simple > ul > li > ul > li')}>
                <a{Info::dataClass('.w-menu-simple > ul > li > ul > li > a, .w-menu-simple > ul > li > ul > li > .no-link')}>Item 5 (level 2)</a>
              </li>
            </ul>
          </li>
          <li{Info::dataClass('.w-menu-simple > ul > li')}>
            <a{Info::dataClass('.w-menu-simple > ul > li > a, .w-menu-simple > ul > li > .no-link')}>Item 2 (level 1)</a>
          </li>
          <li{Info::dataClass('.w-menu-simple > ul > li')}>
            <a{Info::dataClass('.w-menu-simple > ul > li > a, .w-menu-simple > ul > li > .no-link')}>Item 3 (level 1)</a>
          </li>
        </ul>
      </div>

    </div>
  </div>





</div>