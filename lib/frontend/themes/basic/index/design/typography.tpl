{use class="frontend\design\Info"}

<div class="frame-content-wrap">

  <div class="edit-row-element"{Info::dataClass('h1')}>
    <h1>{$smarty.const.TXT_PAGE_HEADING}</h1>
  </div>

  <div class="edit-row-element"{Info::dataClass('.heading-2, h2')}>
    <div class="heading-2">{$smarty.const.TXT_HEADING_LEVEL} 2</div>
  </div>

  <div class="edit-row-element"{Info::dataClass('.heading-3, h3')}>
    <div class="heading-3">{$smarty.const.TXT_HEADING_LEVEL} 3 <a class="right-text edit-small-line-element" {Info::dataClass('.heading-3 .right-text, h3 .right-text')}>{$smarty.const.ENTRY_LINK}</a> <a class="edit edit-small-line-element" {Info::dataClass('.heading-3 .edit, h3 .edit')}>{$smarty.const.IMAGE_EDIT}</a></div>
  </div>

  <div class="edit-row-element"{Info::dataClass('.heading-4, h4')}>
    <div class="heading-4">{$smarty.const.TXT_HEADING_LEVEL} 4 <a class="right-text edit-small-line-element" {Info::dataClass('.heading-4 .right-text, h4 .right-text')}>{$smarty.const.ENTRY_LINK}</a> <a class="edit edit-small-line-element" {Info::dataClass('.heading-4 .edit, h4 .edit')}>{$smarty.const.IMAGE_EDIT}</a></div>
  </div>

  <div class="edit-row-element"{Info::dataClass('p')}>
    <p>{$smarty.const.TXT_BODY_TEXT_EXAMPLE}. You hate me; you want to kill me! Well, go on! Kill me! KILL ME! I'm the Doctor. Well, they call me the Doctor. I don't know why. I call me the Doctor too. I still don't know why. The way I see it, every life is a pile of good things and bad things.…hey.…the good things don't always soften the bad things; but vice-versa the bad things don't necessarily spoil the good things and make them unimportant.</p>
  </div>

  <div class="edit-row-element"{Info::dataClass('a')}>
    <p><a href="">{$smarty.const.TXT_LINKS_STYLE}</a></p>
  </div>

  <div class="">
    <div class="edit-line-element"{Info::dataClass('.in-stock')}><span class="in-stock"><span class="in-stock-icon edit-small-line-element"{Info::dataClass('.in-stock-icon')}></span> {$smarty.const.TEXT_IN_STOCK}</span></div>
    <div class="edit-line-element"{Info::dataClass('.transit')}><span class="transit"><span class="transit-icon edit-small-line-element"{Info::dataClass('.transit-icon')}></span> Transit</span></div>
    <div class="edit-line-element"{Info::dataClass('.pre-order')}><span class="pre-order"><span class="pre-order-icon edit-small-line-element"{Info::dataClass('.pre-order-icon')}></span> Pre Order</span></div>
    <div class="edit-line-element"{Info::dataClass('.out-stock')}><span class="out-stock"><span class="out-stock-icon edit-small-line-element"{Info::dataClass('.out-stock-icon')}></span> {$smarty.const.TEXT_OUT_STOCK}</span></div>
  </div>


</div>