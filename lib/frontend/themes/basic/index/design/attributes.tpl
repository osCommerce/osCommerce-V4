{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="attributes">
      <div>
        <select name="" class="form-control">
          <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
        </select>
      </div>
      <div>
        <select name="" class="form-control">
          <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
        </select>
      </div>
      <div>
        <select name="" class="form-control">
          <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
        </select>
      </div>
      <div>
        <select name="" class="form-control">
          <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
        </select>
      </div>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-attributes edit-element-2"{Info::dataClass('.product .attributes')}>
      <div class="edit-element-1"{Info::dataClass('.product .attributes > div:nth-child(2n + 1)')}>
        <div class="edit-element-1"{Info::dataClass('.product .attributes select')}>
          <select name="" class="form-control">
            <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
          </select>
        </div>
      </div>
      <div class="edit-element-1"{Info::dataClass('.product .attributes > div:nth-child(2n)')}>
        <div class="edit-element-1"{Info::dataClass('.product .attributes select')}>
          <select name="" class="form-control">
            <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
          </select>
        </div>
      </div>
      <div class="edit-element-1"{Info::dataClass('.product .attributes > div:nth-child(2n + 1)')}>
        <div class="edit-element-1"{Info::dataClass('.product .attributes select')}>
          <select name="" class="form-control">
            <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
          </select>
        </div>
      </div>
      <div class="edit-element-1"{Info::dataClass('.product .attributes > div:nth-child(2n)')}>
        <div class="edit-element-1"{Info::dataClass('.product .attributes select')}>
          <select name="" class="form-control">
            <option value="0">{$smarty.const.PULL_DOWN_DEFAULT}</option>
          </select>
        </div>
      </div>
    </div>

  </div>

</div>