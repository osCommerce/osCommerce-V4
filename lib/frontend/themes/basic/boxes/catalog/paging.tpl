{use class="Yii"}
{use class="frontend\design\Info"}

<div class="paging">
    {if isset($links.prev_page.link)}
      <a href="{$links.prev_page.link}" class="prev"></a>
    {else}
      <span class="prev"></span>
    {/if}
    {if isset($links.prev_pages.link)}
      <a class="prev-pages" href="{$links.prev_pages.link}" title="{$links.prev_pages.title}">...</a>
    {/if}

    {foreach $links.page_number as $page}
      {if isset($page.link)}
        <a class="page-number" href="{$page.link}">{$page.title}</a>
      {else}
        <span class="active">{$page.title}</span>
      {/if}
    {/foreach}

    {if isset($links.next_pages.link)}
      <a class="next-pages" href="{$links.next_pages.link}" title="{$links.next_pages.title}">...</a>
    {/if}
    {if isset($links.next_page.link)}
      <a href="{$links.next_page.link}" class="next"></a>
    {else}
      <span class="next"></span>
    {/if}
</div>

{if \frontend\design\Info::themeSetting('old_listing')}
<script type="text/javascript">
  tl('{Info::themeFile('/js/main.js')}', function(){
    var boxID = $('#box-{$box_id}');

    $('a', boxID).off('click', getProductsList).on('click', getProductsList);
  })
</script>
{/if}