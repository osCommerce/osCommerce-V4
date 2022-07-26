{use class="frontend\design\Info"}


<div class="frame-content-wrap">

  <div class="col-left">
    <div class="demo-heading-3">{$smarty.const.IMAGE_PREVIEW}</div>

    <div class="paging">
      <a class="prev"></a>

      <a>1</a>
      <a>2</a>
      <span class="active">3</span>
      <a>4</a>
      <a>5</a>
      <a>6</a>

      <a class="next"></a>
    </div>


  </div>
  <div class="col-right">
    <div class="demo-heading-3">{$smarty.const.EDIT}</div>


    <div class="edit-paging edit-element-2"{Info::dataClass('.paging')}>
      <div class="prev edit-element-1"{Info::dataClass('.paging a.prev, .paging span.prev')}>&#8249;</div>

      <div class="edit-element-1"{Info::dataClass('.paging a, .paging span')}>1</div>
      <div class="edit-element-1"{Info::dataClass('.paging a, .paging span')}>2</div>
      <div class="edit-element-1"{Info::dataClass('.paging a, .paging span')}>3</div>
      <div class="edit-element-1"{Info::dataClass('.paging a, .paging span')}>4</div>
      <div class="edit-element-1"{Info::dataClass('.paging a, .paging span')}>5</div>
      <div class="edit-element-1"{Info::dataClass('.paging a, .paging span')}>6</div>

      <div class="next edit-element-1"{Info::dataClass('.paging a.next, .paging span.next')}>&#8250;</div>
    </div>

  </div>

</div>