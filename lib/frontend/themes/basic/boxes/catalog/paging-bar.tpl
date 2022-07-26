{use class="Yii"}
{frontend\design\Info::addBoxToCss('pagination')}
<div class="pagination">

  <div class="left-area">
    {$counts}
  </div>

  <div class="right-area">
    {if isset($links.prev_page.link)}
      <a href="{$links.prev_page.link}" class="prev"></a>
    {else}
      <span class="prev"></span>
    {/if}
    {if isset($links.prev_pages.link)}
      <a href="{$links.prev_pages.link}" title="{$links.prev_pages.title}">...</a>
    {/if}

    {foreach $links.page_number as $page}
      {if isset($page.link)}
        <a href="{$page.link}">{$page.title}</a>
      {else}
        <span class="active">{$page.title}</span>
      {/if}
    {/foreach}

    {if isset($links.next_pages.link)}
      <a href="{$links.next_page.link}" title="{$links.next_page.title}">...</a>
    {/if}
    {if isset($links.next_page.link)}
      <a href="{$links.next_page.link}" class="next"></a>
    {else}
      <span class="next"></span>
    {/if}
  </div>


</div>
