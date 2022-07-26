{use class="frontend\design\Info"}


<div class="frame-content-wrap">

    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="ga_wrapper">
      <div class="ga_row after">

        <div class="ga_column after">
          <div class="ga_img">
            <a><img src="/tl3/themes/theme-1/img/na.png" alt=""></a>
          </div>
          <div class="ga_ovr">
            <div class="ga_name">
              <a>Product 1</a>
            </div>
            <div class="ga_qty">Buy 1 get 1 free</div>
            <div class="ga_price after">
              Buy 1 same items to get it
            </div>
          </div>
        </div>

        <div class="ga_column after">
          <div class="ga_img">
            <img src="/tl3/themes/theme-1/img/na.png" alt="">
          </div>
          <div class="ga_ovr">
            <div class="ga_name">
              <a>Product 2</a>
            </div>
            <div class="ga_qty">Buy 2 get 1 free</div>
            <div class="ga_price after">
              Buy 2 same items to get it
            </div>
          </div>
        </div>

        <div class="ga_column after">
          <div class="ga_img">
            <a><img src="/tl3/themes/theme-1/img/na.png" alt=""></a>
          </div>
          <div class="ga_ovr">
            <div class="ga_name">
              <a>Product 3</a>
              <div class="ga_attributes">
                <div>
                  <select class="js_ga_select">
                    <option value="0">Select</option>
                  </select>
                </div>
                <div>
                  <select class="js_ga_select" name="giveaways[55][id][8]" data-required=" Caseback" data-empty-option=" Caseback">
                    <option value="0">Select</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="ga_qty">Buy 3 get 1 free</div>
            <div class="ga_price after">
              <span class="checkBoxWrap"><div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-animate" style="width: 62px;"><div class="bootstrap-switch-container" style="width: 94px; margin-left: -32px;"><span class="bootstrap-switch-handle-on bootstrap-switch-primary" style="width: 32px;">Yes</span><span class="bootstrap-switch-label" style="width: 30px;">&nbsp;</span><span class="bootstrap-switch-handle-off bootstrap-switch-default" style="width: 32px;">No</span><input type="checkbox" name="giveaway_switch[1]" value="1" onclick="this.form.submit();"></div></div></span>
              Add for free to your basket
            </div>
          </div>
        </div>

        <div class="ga_column after">
          <div class="ga_img">
            <a><img src="/tl3/themes/theme-1/img/na.png" alt=""></a>
          </div>
          <div class="ga_ovr">
            <div class="ga_name">
              <a>Product 4</a>
            </div>
            <div class="ga_qty">Buy 3 get 2 free</div>
            <div class="ga_price after">
              Buy 3 same items to get it
            </div>
          </div>
        </div>

      </div>
    </div>


  </div>
  <div class="frame-content-wrap">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-ga_wrapper edit-element-2"{Info::dataClass('.ga_wrapper')}>
      <div class="edit-ga_row after edit-element-2"{Info::dataClass('.ga_row')}>

        <div class="edit-ga_column edit-element-2"{Info::dataClass('.ga_column')}>
          <div class="edit-ga_img edit-element-1"{Info::dataClass('.ga_img')}>
            <a><img src="/tl3/themes/theme-1/img/na.png" alt=""></a>
          </div>
          <div class="edit-ga_ovr edit-element-1"{Info::dataClass('.ga_ovr')}>
            <div class="edit-ga_name edit-element-1"{Info::dataClass('.ga_name')}>
              <a>Product 1</a>
            </div>
            <div class="edit-ga_qty edit-element-1"{Info::dataClass('.ga_qty')}>Buy 1 get 1 free</div>
            <div class="edit-ga_price  edit-element-1"{Info::dataClass('.ga_price')}>
              Buy 1 same items to get it
            </div>
          </div>
        </div>

        <div class="edit-ga_column edit-element-2"{Info::dataClass('.ga_column')}>
          <div class="edit-ga_img edit-element-1"{Info::dataClass('.ga_img')}>
            <img src="/tl3/themes/theme-1/img/na.png" alt="">
          </div>
          <div class="edit-ga_ovr edit-element-1"{Info::dataClass('.ga_ovr')}>
            <div class="edit-ga_name edit-element-1"{Info::dataClass('.ga_name')}>
              Product 2
            </div>
            <div class="edit-ga_qty edit-element-1"{Info::dataClass('.ga_qty')}>Buy 2 get 1 free</div>
            <div class="edit-ga_price  edit-element-1"{Info::dataClass('.ga_price')}>
              Buy 2 same items to get it
            </div>
          </div>
        </div>

        <div class="edit-ga_column edit-element-2"{Info::dataClass('.ga_column')}>
          <div class="edit-ga_img edit-element-1"{Info::dataClass('.ga_img')}>
            <a><img src="/tl3/themes/theme-1/img/na.png" alt=""></a>
          </div>
          <div class="edit-ga_ovr edit-element-1"{Info::dataClass('.ga_ovr')}>
            <div class="edit-ga_name edit-element-1"{Info::dataClass('.ga_name')}>
              <a>Product 3</a>
              <div class="edit-ga_attributes edit-element-1"{Info::dataClass('.ga_attributes')}>
                <div class="edit-element-1"{Info::dataClass('.ga_attributes > div')}>
                  <div class="edit-element-1"{Info::dataClass('.ga_attributes select')}>
                  <select class="">
                    <option value="0">Select</option>
                  </select>
                  </div>
                </div>
                <div class="edit-element-1"{Info::dataClass('.ga_attributes > div')}>
                  <div class="edit-element-1"{Info::dataClass('.ga_attributes select')}>
                  <select class="">
                    <option value="0">Select</option>
                  </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="edit-ga_qty edit-element-1"{Info::dataClass('.ga_qty')}>Buy 3 get 1 free</div>
            <div class="edit-ga_price  edit-element-1"{Info::dataClass('.ga_price')}>
              Buy 3 same items to get it
            </div>
          </div>
        </div>

        <div class="edit-ga_column edit-element-2"{Info::dataClass('.ga_column')}>
          <div class="edit-ga_img edit-element-1"{Info::dataClass('.ga_img')}>
            <a><img src="/tl3/themes/theme-1/img/na.png" alt=""></a>
          </div>
          <div class="edit-ga_ovr edit-element-1"{Info::dataClass('.ga_ovr')}>
            <div class="edit-ga_name edit-element-1"{Info::dataClass('.ga_name')}>
              <a>Product 4</a>
            </div>
            <div class="edit-ga_qty edit-element-1"{Info::dataClass('.ga_qty')}>Buy 3 get 2 free</div>
            <div class="edit-ga_price  edit-element-1"{Info::dataClass('.ga_price')}>
              <div class="edit-checkBoxWrap edit-element-1"{Info::dataClass('.ga_price .checkBoxWrap')}>
                <div class="bootstrap-switch bootstrap-switch-wrapper bootstrap-switch-off bootstrap-switch-animate" style="width: 62px;"><div class="bootstrap-switch-container" style="width: 94px; margin-left: -32px;"><span class="bootstrap-switch-handle-on bootstrap-switch-primary" style="width: 32px;">Yes</span><span class="bootstrap-switch-label" style="width: 30px;">&nbsp;</span><span class="bootstrap-switch-handle-off bootstrap-switch-default" style="width: 32px;">No</span><input type="checkbox" name="giveaway_switch[1]" value="1" onclick="this.form.submit();"></div></div>
              </div>
              Add for free to your basket
            </div>
          </div>
        </div>

      </div>
    </div>


</div>