{use class="Yii"}
{use class="frontend\design\Info"}
<div class="functionality-bar">
  <form action="{$sorting_link}" method="get" class="sort-form">
    {$hidden_fields}

    <div class="view">
      {$smarty.const.VIEW_AS} &nbsp;
      {if $grid_link}<a href="{$grid_link}" class="grid{if $gl == 'grid'} active{/if}" title="{$smarty.const.TEXT_GRID_VIEW}" rel="nofollow"></a>{/if}
      {if $list_link}<a href="{$list_link}" class="list{if $gl == 'list'} active{/if}" title="{$smarty.const.TEXT_LIST_VIEW}" rel="nofollow"></a>{/if}
      {if $b2b_link}<a href="{$b2b_link}" class="b2b{if $gl == 'b2b'} active{/if}" title="{$smarty.const.TEXT_B2B_VIEW}" rel="nofollow"></a>{/if}
    </div>

    {if !$compare_button}
    <div class="compare-box-btn">
      <a class="compare_button btn" href="{Yii::$app->urlManager->createUrl('catalog/compare')}">{$smarty.const.BOX_HEADING_COMPARE_LIST}</a>
    </div>
    {/if}

    <div class="sort">
      {$smarty.const.SORT_BY}
      <select name="sort" onchange="this.form.submit()">
        {foreach $sorting as $item}
          <option value="{$item.id}"{if $item.id === $sorting_id} selected{/if}>{$item.title}</option>
        {/foreach}
      </select>
    </div>

    {if !$fbl}
    <div class="show">
      {$smarty.const.SHOW}
      <select name="max_items" onchange="this.form.submit()">
        {foreach $view as $item}
          <option value="{$item}"{if $view_id == $item} selected{/if}>{$item}</option>
        {/foreach}
      </select>
      {$smarty.const.ITEMS}
    </div>
    {/if}

    <button type="submit" class="btn no-js">{$smarty.const.TEXT_APPLY}</button>
  </form>
</div>
{if !$compare_button}

<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    $('.no-js').hide();


    if (!window.compare_key) {
      window.compare_key = 1;
      var params = { compare: []};
      $('.compare_button').popUp({
        box: "<div class='popup-box-wrap'><div class='around-pop-up'></div><div class='popup-box popupCompare'><div class='pop-up-close'></div><div class='popup-heading compare-head'>{$smarty.const.BOX_HEADING_COMPARE_LIST}</div><div class='pop-up-content'><div class='preloader'></div></div></div></div>",
        data: params,
        beforeSend: function () {
          params.compare.splice(0, params.compare.length);
          $('input[name="compare[]"]').each(function (i, e) {
            if (e.checked) {
              params.compare.push(e.value);
            }
          })
        }
      })
    }
  });
</script>
{/if}