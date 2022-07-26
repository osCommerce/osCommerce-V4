
{\frontend\design\Info::addBoxToCss('price-box')}
<div class="price-box">
  {if $gift_wrap}
    <div class="price-row">
      <div class="title">{output_label const="GIFT_WRAP"}</div>
      <div class="price">{$gift_wrap}</div>
    </div>
  {/if}
  <div class="price-row total">
    <div class="title">{output_label const="SUB_TOTAL"}</div>
    <div class="price">{$sub_total}</div>
  </div>
</div>