{foreach $list as $item}
    <a href="javascript:void(0)" onclick="return searchSuggestSelected({$item.id});" class="item">
      <span class="image">{if isset($item.image)}<img src="{$item.image}" alt="">{/if}</span>
      <span class="name">{$item.title}</span>
    </a>
{/foreach}