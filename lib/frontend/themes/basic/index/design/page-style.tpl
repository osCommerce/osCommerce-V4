{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="page-style">
      <span>{$smarty.const.VIEW_AS}</span>
      <a class="grid" title=""></a>
      <a class="list" title=""></a>
      <a class="b2b" title=""></a>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-page-style edit-element-2"{Info::dataClass('.page-style')}>
      <div class="edit-element-1"{Info::dataClass('.page-style span')}>{$smarty.const.VIEW_AS}</div>
      <div class="edit-element-1"{Info::dataClass('.page-style a.grid')}>{$smarty.const.TEXT_GRID_VIEW}</div>
      <div class="edit-element-1"{Info::dataClass('.page-style a.list')}>{$smarty.const.TEXT_LIST_VIEW}</div>
      <div class="edit-element-1"{Info::dataClass('.page-style a.b2b')}>{$smarty.const.TEXT_B2B_VIEW}</div>
    </div>

  </div>

</div>