{use class="Yii"}
{use class="frontend\design\Info"}
<div class="page-style">
  <span class="listing-look-title">{$smarty.const.VIEW_AS}</span>
  {if $grid_link}<a href="{$grid_link|escape:'html'}" class="grid{if $gl == 'grid'} active{/if}" title="{$smarty.const.TEXT_GRID_VIEW}" rel="nofollow" data-type="listingTypeCol" data-gl="grid"></a>{/if}
  {if $list_link}<a href="{$list_link|escape:'html'}" class="list{if $gl == 'list'} active{/if}" title="{$smarty.const.TEXT_LIST_VIEW}" rel="nofollow" data-type="listingTypeRow" data-gl="list"></a>{/if}
  {if $b2b_link}<a href="{$b2b_link|escape:'html'}" class="b2b{if $gl == 'b2b'} active{/if}" title="{$smarty.const.TEXT_B2B_VIEW}" rel="nofollow" data-type="listingTypeB2b" data-gl="b2b"></a>{/if}
</div>
{if \frontend\design\Info::themeSetting('old_listing')}
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    var boxID = $('#box-{$box_id}');

    $('a', boxID).off('click', getProductsList).on('click', getProductsList);
  })
</script>
{/if}