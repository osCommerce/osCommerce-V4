{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="info">
      <strong>{$smarty.const.TEXT_TEXT}</strong>
      <ul>
        <li>{$smarty.const.TEXT_LISTING}</li>
        <li>{$smarty.const.TEXT_LISTING}</li>
        <li>{$smarty.const.TEXT_LISTING}</li>
      </ul>
      <a>{$smarty.const.TEXT_LINK}</a>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>

    <div class="edit-info edit-element-2"{Info::dataClass('.info')}>
      <div class="edit-element-1"{Info::dataClass('.info strong')}>{$smarty.const.TEXT_TEXT}</div>
      <ul class="edit-element-1"{Info::dataClass('.info ul')}>
        <li class="edit-element-1"{Info::dataClass('.info li')}>{$smarty.const.TEXT_LISTING}</li>
        <li class="edit-element-1"{Info::dataClass('.info li')}>{$smarty.const.TEXT_LISTING}</li>
        <li class="edit-element-1"{Info::dataClass('.info li')}>{$smarty.const.TEXT_LISTING}</li>
      </ul>
      <div class="edit-element-1"{Info::dataClass('.info a')}>{$smarty.const.TEXT_LINK}</div>
    </div>


  </div>

</div>