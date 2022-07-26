<div class="sale-holder">
  <div class="image"><img src="{$imageUrl}" alt="{$product.products_name}"></div>

  <div class="holder-1">

    <div class="ends">
      <span class="ends-date-text">{$smarty.const.SALE_ENDS_DATE_TEXT}</span>
      <span class="ends-date">{$expiresDate}</span>
    </div>

    <div class="left">
      <span class="left-text-left">{$smarty.const.SALE_LEFT_TEXT_LEFT}</span>

      <span class="left-count box-days">
          <span class="left-count-days">{$days}</span>
          <span class="left-count-days-text">{$smarty.const.SALE_TEXT_DAYS}</span>
      </span>
      <span class="left-count box-hours">
          <span class="left-count-hours">{$hours}</span>
          <span class="left-count-hours-text">{$smarty.const.SALE_TEXT_HOURS}</span>
      </span>
      <span class="left-count box-minutes" style="display: none">
          <span class="left-count-minutes">{$minutes}</span>
          <span class="left-count-minutes-text">{$smarty.const.SALE_TEXT_MINUTES}</span>
      </span>
      <span class="left-count box-seconds" style="display: none">
          <span class="left-count-seconds">{$seconds}</span>
          <span class="left-count-seconds-text">{$smarty.const.SALE_TEXT_SECONDS}</span>
      </span>

      <span class="left-text-right">{$smarty.const.SALE_LEFT_TEXT_RIGHT}</span>
    </div>

    <div class="button"><span class="btn">{$smarty.const.SALE_BUTTON}</span></div>

  </div>

  <div class="holder-2">
    <div class="name">{$product.products_name}</div>
    <div class="description">{$product.products_description_short}</div>
    <div class="special">
      <span class="special-price-text">{$smarty.const.SALE_SPECIAL_PRICE_TEXT}</span>
      <span class="special-price">{$product.price_special}</span>
    </div>
    <div class="old">
      <span class="old-price-text">{$smarty.const.SALE_OLD_PRICE_TEXT}</span>
      <span class="old-price">{$product.price_old}</span>
    </div>
  </div>

    {if $save}
  <div class="badge">
    <span class="text">{$smarty.const.SALE_TEXT_SAVE}</span>
    <span class="percents">{$save}%</span>
  </div>
    {/if}
</div>

<script type="text/javascript">
    tl('{\frontend\design\Info::themeFile('/js/main.js')}', function(){
        $('#box-{$id}').backCounter();

    });

    tl(function(){
        var boxId = $('#box-{$id}');

        $('.sale-holder', boxId).on('click', function(){
            window.location = '{$link}';
        })
    })
</script>

