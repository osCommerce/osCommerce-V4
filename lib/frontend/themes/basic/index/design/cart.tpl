{use class="frontend\design\Info"}

<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="cart-box">
      <a>
        <span style="overflow: visible">
          <strong>{$smarty.const.TEXT_HEADING_SHOPPING_CART}</strong>
          <span class="items">2  items</span>
          <span>£105.19</span>
        </span>
      </a>
      <div class="cart-content">
        <a class="item">
          <span class="image"><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></span>

          <span class="name"><span class="qty">1</span>{$smarty.const.TEXT_PRODUCT} 1</span>
          <span class="price">£46.98</span>
        </a>
        <a class="item">
          <span class="image"><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></span>
          <span class="name"><span class="qty">1</span>{$smarty.const.TEXT_PRODUCT} 2</span>
          <span class="price">£58.21</span>
        </a>
        <div class="cart-total">{$smarty.const.SUB_TOTAL}: £105.19</div>
        <div class="buttons">
          <div class="left-buttons"><a class="btn">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a></div>
          <div class="right-buttons"><a class="btn">{$smarty.const.HEADER_TITLE_CHECKOUT}</a></div>
        </div>
      </div>
    </div>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-cart-box edit-element-2"{Info::dataClass('.cart-box')}>
      <a{Info::dataClass('.cart-box > a')} class="edit-cart-link edit-element-2">
        <div class="edit-element-1"{Info::dataClass('.cart-box > a > span')}>
          <strong{Info::dataClass('.cart-box > a > span > strong')} class=" edit-element-1">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</strong>
          <div class="items edit-element-1" style="display: inline-block"{Info::dataClass('.cart-box > a > span > span.item')}>2  items</div>
          <div class="edit-element-1" style="display: inline-block"{Info::dataClass('.cart-box > a > span > span')}>£105.19</div>
        </div>
      </a>
      <div class="edit-cart-content edit-element-2"{Info::dataClass('.cart-content')}>
        <a class="item edit-element-1"{Info::dataClass('.cart-content a.item')}>
          <div class="edit-image edit-element-1"{Info::dataClass('.cart-content .image')}><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></div>

          <div class="edit-name edit-element-1"{Info::dataClass('.cart-content .name')}>
            <div class="qty edit-element-1" style="display: inline-block"{Info::dataClass('.cart-content .qty')}>1</div>
            {$smarty.const.TEXT_PRODUCT} 1
          </div>
          <div class="edit-price edit-element-1"{Info::dataClass('.cart-content .price')}>£46.98</div>
        </a>
        <a class="item edit-element-1"{Info::dataClass('.cart-content a.item')}>
          <div class="edit-image edit-element-1"{Info::dataClass('.cart-content .image')}><img src="{$app->request->baseUrl}/themes/theme-1/img/na.png" alt=""></div>
          <div class="edit-name edit-element-1"{Info::dataClass('.cart-content .name')}>
            <div class="qty edit-element-1" style="display: inline-block"{Info::dataClass('.cart-content .qty')}>1</div>
            {$smarty.const.TEXT_PRODUCT} 2
          </div>
          <div class="edit-price edit-element-1"{Info::dataClass('.cart-content .price')}>£58.21</div>
        </a>
        <div class="edit-cart-total edit-element-1"{Info::dataClass('.cart-content .cart-total')}>{$smarty.const.SUB_TOTAL}: £105.19</div>
        <div class="buttons edit-element-1"{Info::dataClass('.cart-content .buttons')}>
          <div class="left-buttons"><a class="btn">{$smarty.const.TEXT_HEADING_SHOPPING_CART}</a></div>
          <div class="right-buttons"><a class="btn">{$smarty.const.HEADER_TITLE_CHECKOUT}</a></div>
        </div>
      </div>
    </div>

  </div>

</div>

