{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="qty-input">
      <label for="qty">{$smarty.const.UNIT_QTY}:</label>
      <div class="input">
        <span class="qty-box">
          <span class="smaller"></span>
          <input type="text" id="qty" name="qty" value="1" class="qty-inp">
          <span class="bigger"></span>
        </span>
      </div>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-qty-input edit-element-2"{Info::dataClass('.qty-input')}>
      <label for="qty" class="edit-element-2"{Info::dataClass('.qty-input label')}>{$smarty.const.UNIT_QTY}:</label>
      <div class="edit-input edit-element-2"{Info::dataClass('.qty-input .input')}>
        <div class="edit-qty-box edit-element-1"{Info::dataClass('.qty-box')}>
          <div class="edit-smaller edit-element-1"{Info::dataClass('.qty-box .smaller')}>-</div>
          <div class="edit-element-1"{Info::dataClass('.qty-box input')}>
            <input type="text" id="qty" name="qty" value="1" class="qty-inp">
          </div>
          <div class="edit-bigger edit-element-1"{Info::dataClass('.qty-box .bigger')}>+</div>
        </div>
      </div>
    </div>


  </div>

</div>