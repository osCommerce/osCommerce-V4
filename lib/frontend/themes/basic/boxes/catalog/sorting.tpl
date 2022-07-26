{use class="Yii"}
{use class="frontend\design\Info"}

{if \frontend\design\Info::themeSetting('old_listing')}
<div class="sorting">
  <form action="{$sorting_link|escape:'html'}" method="get" class="sort-form">
    {$hidden_fields}

    <span class="before">{$smarty.const.SORT_BY}</span>
    <select class="sort-select" name="sort">
      {foreach $sorting as $item}
        <option value="{$item.id}"{if $item.id === $sorting_id} selected{/if}>{$item.title}</option>
      {/foreach}
    </select>

  </form>
</div>

<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    var boxID = $('#box-{$box_id}');

    $('select', boxID).off('change', getProductsList).on('change', getProductsList);
  })
</script>
{else}

  <div class="sorting">
      <span class="before">{$smarty.const.SORT_BY}</span>
      <select class="sort-select" name="sort">
          {foreach $sorting as $item}
            <option value="{$item.id}"{if $item.id === $sorting_id} selected{/if}>{$item.title}</option>
          {/foreach}
      </select>
  </div>
{/if}