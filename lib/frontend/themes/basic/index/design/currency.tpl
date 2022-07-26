{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="currencies">
      <div class="current">
        <span>GBP</span>
      </div>
      <div class="select">
        <a>BGN</a>
        <a>USD</a>
        <a>EUR</a>
      </div>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-currencies edit-element-2"{Info::dataClass('.currencies')}>
      <div class="current edit-element-1"{Info::dataClass('.currencies .current')}>
        <div class="edit-element-1"{Info::dataClass('.currencies .current span')}>GBP</div>
      </div>
      <div class="select edit-element-2"{Info::dataClass('.currencies .select')}>
        <div class="edit-element-1"{Info::dataClass('.currencies .select a')}>BGN</div>
        <div class="edit-element-1"{Info::dataClass('.currencies .select a')}>USD</div>
        <div class="edit-element-1"{Info::dataClass('.currencies .select a')}>EUR</div>
      </div>
    </div>

  </div>

</div>