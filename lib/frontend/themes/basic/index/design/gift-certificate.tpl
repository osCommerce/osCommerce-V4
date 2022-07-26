{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="gift-code gift-certificate">
      <div>
        <div class="heading-4">{$smarty.const.GIFT_CERTIFICATE}</div>
        <p>{$smarty.const.GIFT_CERTIFICATE_TEXT}</p>
        <div class="input-apple">
          <button type="submit" class="btn">{$smarty.const.APPLY}</button>
          <div><input type="text" name="credit_apply[gv][gv_redeem_code]" autocomplete="off"></div>
        </div>

        <div class="credit-amount">
          <strong>{$smarty.const.CURRENT_CREDIT_AMOUNT}</strong> £100.00
        </div>
        <div class="credit-amount">
          <strong>{$smarty.const.TEXT_CREDIT_AMOUNT_ASK_USE}</strong>
          <div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-animate" style="width: 62px;"><div class="bootstrap-switch-container" style="width: 94px; margin-left: -32px;"><span class="bootstrap-switch-handle-on bootstrap-switch-primary" style="width: 32px;">Yes</span><span class="bootstrap-switch-label" style="width: 30px;">&nbsp;</span><span class="bootstrap-switch-handle-off bootstrap-switch-default" style="width: 32px;">No</span><input name="credit_apply[gv][cot_gv]" type="checkbox" class="check-on-off"></div></div>
        </div>

      </div>
    </div>

  </div>
  <div class="col-right">

    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-gift-code edit-gift-certificate edit-element-2"{Info::dataClass('.gift-code')}>
      <div class="edit-element-2"{Info::dataClass('.gift-code > div')}>
        <div class="heading-4 edit-element-1"{Info::dataClass('.gift-certificate .heading-4')}>{$smarty.const.GIFT_CERTIFICATE}</div>
        <p class="edit-element-1"{Info::dataClass('.gift-code p')}>{$smarty.const.GIFT_CERTIFICATE_TEXT}</p>
        <div class="edit-input-apple edit-element-1"{Info::dataClass('.gift-code .input-apple')}>
          <div class="edit-element-1" style="display: inline-block"{Info::dataClass('.gift-code button')}>
            <button type="submit" class="btn">{$smarty.const.APPLY}</button>
          </div>
          <div class="edit-element-1"{Info::dataClass('.gift-code .input-apple > div')}>
            <div class="edit-element-1" style="display: inline-block"{Info::dataClass('.gift-code input')}>
              <input type="text" name="credit_apply[gv][gv_redeem_code]" autocomplete="off">
            </div>
          </div>
        </div>

        <div class="edit-credit-amount edit-element-1"{Info::dataClass('.credit-amount')}>
          <div class="edit-element-1" style="display: inline-block"{Info::dataClass('.credit-amount strong')}>{$smarty.const.CURRENT_CREDIT_AMOUNT}</div>
          £100.00
        </div>
        <div class="edit-credit-amount"{Info::dataClass('.credit-amount')}>
          <div class="edit-element-1" style="display: inline-block"{Info::dataClass('.credit-amount strong')}>{$smarty.const.TEXT_CREDIT_AMOUNT_ASK_USE}</div>
          <div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-animate" style="width: 62px;"><div class="bootstrap-switch-container" style="width: 94px; margin-left: -32px;"><span class="bootstrap-switch-handle-on bootstrap-switch-primary" style="width: 32px;">Yes</span><span class="bootstrap-switch-label" style="width: 30px;">&nbsp;</span><span class="bootstrap-switch-handle-off bootstrap-switch-default" style="width: 32px;">No</span><input name="credit_apply[gv][cot_gv]" type="checkbox" class="check-on-off"></div></div>
        </div>

      </div>
    </div>


  </div>
</div>
<div class="frame-content-wrap">
  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="gift-code discount-coupon">
      <div>
        <div class="heading-4">{$smarty.const.DISCOUNT_COUPON}</div>
        <p>{$smarty.const.DISCOUNT_COUPON_TEXT}</p>
        <div class="input-apple">
          <button type="submit" class="btn">{$smarty.const.APPLY}</button>
          <div><input type="text" name="credit_apply[coupon][gv_redeem_code]" value="" autocomplete="off"></div>
        </div>
      </div>
    </div>

  </div>
  <div class="col-right">

    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-gift-code edit-discount-coupon edit-element-2"{Info::dataClass('.gift-code')}>
      <div class="edit-element-2"{Info::dataClass('.gift-code > div')}>
        <div class="heading-4 edit-element-1"{Info::dataClass('.discount-coupon .heading-4')}>{$smarty.const.DISCOUNT_COUPON}</div>
        <p class="edit-element-1"{Info::dataClass('.gift-code p')}>{$smarty.const.DISCOUNT_COUPON_TEXT}</p>
        <div class="edit-input-apple edit-element-1"{Info::dataClass('.gift-code .input-apple')}>
          <div class="edit-element-1" style="display: inline-block"{Info::dataClass('.gift-code button')}>
            <button type="submit" class="btn">{$smarty.const.APPLY}</button>
          </div>
          <div class="edit-element-1"{Info::dataClass('.gift-code .input-apple > div')}>
            <div class="edit-element-1" style="display: inline-block"{Info::dataClass('.gift-code input')}>
              <input type="text" name="credit_apply[gv][gv_redeem_code]" autocomplete="off">
            </div>
          </div>
        </div>
      </div>
    </div>


  </div>

</div>