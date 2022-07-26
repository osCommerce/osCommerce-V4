{use class="frontend\design\Info"}




<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="edit-btn">
      <span class="btn">{$smarty.const.TXT_BUTTON_TYPE} 1</span>
    </div>
    <div class="edit-btn">
      <span class="btn-1">{$smarty.const.TXT_BUTTON_TYPE} 2</span>
    </div>
    <div class="edit-btn">
      <span class="btn-3">{$smarty.const.TXT_BUTTON_TYPE} 3</span>
    </div>
    <div class="edit-btn">
      <span class="btn-2">{$smarty.const.TXT_BUTTON_TYPE} 4</span>
    </div>

    <div class="view-all">
      <span class="btn">View All</span>
    </div>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-buttons">
      <div class="edit-element-1"{Info::dataClass('.btn')}>
        {$smarty.const.TXT_BUTTON_TYPE} 1
      </div>
      <div class="edit-element-1"{Info::dataClass('.btn-1')}>
        {$smarty.const.TXT_BUTTON_TYPE} 2
      </div>
      <div class="edit-element-1"{Info::dataClass('.btn-3')}>
        {$smarty.const.TXT_BUTTON_TYPE} 3
      </div>
      <div class="edit-element-1"{Info::dataClass('.btn-2')}>
        {$smarty.const.TXT_BUTTON_TYPE} 4
      </div>
    </div>

    <div class="edit-view-all edit-element-2"{Info::dataClass('.view-all')}>
      <div class="edit-element-1"{Info::dataClass('.view-all .btn')}>
        View All
      </div>
    </div>


  </div>

</div>