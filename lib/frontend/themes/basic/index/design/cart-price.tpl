{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

  <div class="price-box">
    <div class="price-row">
      <div class="title">{$smarty.const.GIFT_WRAP_OPTION}:</div>
      <div class="price">£0.00</div>
    </div>
    <div class="price-row total">
      <div class="title">{$smarty.const.SUB_TOTAL}:</div>
      <div class="price">£240.00</div>
    </div>
  </div>

  </div>
  <div class="col-right">

    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-price-box edit-element-2"{Info::dataClass('.price-box')}>
      <div class="edit-price-row edit-element-1"{Info::dataClass('.price-box .price-row')}>
        <div class="edit-title edit-element-1"{Info::dataClass('.price-box .title')}>{$smarty.const.GIFT_WRAP_OPTION}:</div>
        <div class="edit-price edit-element-1"{Info::dataClass('.price-box .price')}>£0.00</div>
      </div>
      <div class="edit-price-row total edit-element-1"{Info::dataClass('.price-box .price-row.total')}>
        <div class="edit-title edit-element-1"{Info::dataClass('.price-box .total .title')}>{$smarty.const.SUB_TOTAL}:</div>
        <div class="edit-price edit-element-1"{Info::dataClass('.price-box .total .price')}>£240.00</div>
      </div>
    </div>
  </div>
</div>