{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="demo-heading-4">{$smarty.const.TEXT_CURRENT}</div>
    <div class="price">
      <span class="current">£66.00</span>
    </div>

    <div class="demo-heading-4" style="margin-top: 20px">{$smarty.const.TEXT_SPECIAL}</div>
    <div class="price">
      <span class="old">£78.00</span>
      <span class="special">£66.00</span>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="demo-heading-4">{$smarty.const.TEXT_CURRENT}</div>
    <div class="price edit-element-2"{Info::dataClass('.price')}>
      <div class="current edit-element-1"{Info::dataClass('.price .current')}>£66.00</div>
    </div>

    <div class="demo-heading-4" style="margin-top: 20px">{$smarty.const.TEXT_SPECIAL}</div>
    <div class="price edit-element-2"{Info::dataClass('.price')}>
      <div class="old edit-element-1"{Info::dataClass('.price .old')}>£78.00</div>
      <div class="special edit-element-1"{Info::dataClass('.price .special')}>£66.00</div>
    </div>

  </div>

</div>