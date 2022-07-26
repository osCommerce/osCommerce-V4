{if $breadcrumb}
<div class="catalog-breadcrumb">
  {if isset($settings[0].show_text) && $settings[0].show_text}<div class="breadcrumbs-text">{$smarty.const.TEXT_BEFORE_BREADCRUMBS}</div>{/if}
  <ul class="breadcrumb-ul" {*itemscope itemtype="http://schema.org/BreadcrumbList"*}>
  {foreach $breadcrumb as $item}
    <li class="breadcrumb-li" {*itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"*}>
      {if isset($item.link) && $item.link != ''}
      <a class="breadcrumb-link" {*itemtype="https://schema.org/Thing" itemprop="item"*} href="{$item.link}">
        <span class="breadcrumb-link-name" {*itemprop="name"*}>{$item.name}</span>
      </a>
      {else}
        <span class="breadcrumb-name-item" {*itemprop="item" itemtype="https://schema.org/Thing"*}>
          <span class="breadcrumb-name" {*itemprop="name"*}>{$item.name}</span>
        </span>
      {/if}
      {*<meta itemprop="position" content="{$item@iteration}" />*}
    </li>
  {/foreach}
  </ul>
</div>
{/if}