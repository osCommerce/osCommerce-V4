{use class="frontend\design\Info"}



<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="bestsellers">
      <div class="heading-3">{$smarty.const.BEST_SELLERS}</div>

      <div class="bestsellers-list">
        <div class="item"><span>1</span><a>{$smarty.const.TEXT_PRODUCT} 1</a></div>
        <div class="item"><span>2</span><a>{$smarty.const.TEXT_PRODUCT} 2</a></div>
        <div class="item"><span>3</span><a>{$smarty.const.TEXT_PRODUCT} 3</a></div>
        <div class="item"><span>4</span><a>{$smarty.const.TEXT_PRODUCT} 4</a></div>
        <div class="item"><span>5</span><a>{$smarty.const.TEXT_PRODUCT} 5</a></div>
      </div>
    </div>

  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-bestsellers edit-element-2"{Info::dataClass('.bestsellers')}>
      <div class="edit-element-2 heading-3"{Info::dataClass('.bestsellers .heading-3')}>{$smarty.const.BEST_SELLERS}</div>
      <div class="edit-bestsellers-list edit-element-2"{Info::dataClass('.bestsellers-list')}>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item:first-child')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item:first-child span')}>1</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item:first-child a')}>{$smarty.const.TEXT_PRODUCT} 1</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>2</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>{$smarty.const.TEXT_PRODUCT} 2</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>3</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>{$smarty.const.TEXT_PRODUCT} 3</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>4</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>{$smarty.const.TEXT_PRODUCT} 4</a></div>
        <div class="item edit-element-1"{Info::dataClass('.bestsellers-list .item')}><div class="edit-element-1"{Info::dataClass('.bestsellers-list .item span')}>5</div><a  class="edit-element-1"{Info::dataClass('.bestsellers-list .item a')}>{$smarty.const.TEXT_PRODUCT} 5</a></div>
      </div>
    </div>

  </div>

</div>

