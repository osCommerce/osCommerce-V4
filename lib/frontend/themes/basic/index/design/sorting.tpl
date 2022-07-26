{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="sorting">
      <span class="before">{$smarty.const.SORT_BY}</span>
      <select name="sort">
        <option value="pa">&#9650; {$smarty.const.TEXT_BY_PRICE}</option>
        <option value="pd">&#9660; {$smarty.const.TEXT_BY_PRICE}</option>
      </select>
    </div>

    <div class="">&nbsp;</div>

    <div class="items-on-page">
        <span class="before">{$smarty.const.SHOW}</span>
        <select name="max_items">
          <option value="8">8</option>
          <option value="16" selected="">16</option>
          <option value="32">32</option>
          <option value="64">64</option>
        </select>
        <span class="after">{$smarty.const.ITEMS}</span>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>



    <div class="edit-items-on-page edit-element-2"{Info::dataClass('.items-on-page, .sorting')}>
      <div class="before edit-element-1"{Info::dataClass('.items-on-page .before, .sorting .before')}>{$smarty.const.SHOW}</div>
      <div class="edit-element-1"{Info::dataClass('.items-on-page select, .sorting select')}>
        <select name="max_items">
          <option value="8">8</option>
          <option value="16" selected="">16</option>
          <option value="32">32</option>
          <option value="64">64</option>
        </select>
      </div>
      <div class="after edit-element-1"{Info::dataClass('.items-on-page .after, .sorting .after')}>{$smarty.const.ITEMS}</div>
    </div>

  </div>

</div>