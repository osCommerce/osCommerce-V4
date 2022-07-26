{$type=''}

{foreach $list as $item}
  {if $type==''}
    <span class="type-block {$item.type_class}">
  {/if}

  {if $item.type != $type}
    <strong class="items-title">{$item.type}</strong>
    {if $type!=''}
      </span><span class="type-block {$item.type_class}">
    {/if}
    {$type = $item.type}
  {/if}

  <span class="item">
    <a href="{$item.link}" >
      <span class="image">{if isset($item.image)}<img src="{$item.image}" alt="">{/if}</span>
      <span class="name">{$item.title}</span>
    </a>
    {if $item.extra}
    <span class="extra">{$item.extra}</span>
    {/if}
  </span>

{/foreach}
</span>