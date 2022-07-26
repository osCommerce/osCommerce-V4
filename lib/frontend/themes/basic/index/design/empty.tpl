{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="cart-page">
      <div class="empty">{$smarty.const.CART_EMPTY}</div>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>

    <div class="edit-empty edit-element-2"{Info::dataClass('.cart-page .empty')}>{$smarty.const.CART_EMPTY}</div>


  </div>

</div>