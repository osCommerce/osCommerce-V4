{use class="frontend\design\IncludeTpl"}
{use class="frontend\design\Info"}
<div class="w-image-maps">
    <div class="svg-wrap">
        <img src="{$image}" alt="">
        {$svg}
    </div>

    <div class="products-items">
        {if in_array($settings[0]['listing_type'], ['type-1', 'type-1_2', 'type-1_3', 'type-1_4', 'type-2', 'type-2_2'])}
            {IncludeTpl::widget(['file' => 'boxes/products-listing.tpl', 'params' => ['only_column'=>true, 'products' => $products, 'settings' => $settings, 'languages_id' => $languages_id]])}
        {else}
            {\frontend\design\boxes\ProductListing::widget(['products' => $products, 'settings' => $settings, 'id' => $id])}
        {/if}
    </div>

    <div class="default-links float-items">
        {foreach $defaultLinks as $link}
            <div class="item" data-id="{$link.id}">
                <a href="{$link.href}">
                    {if $link.title}<span class="title">{$link.title}</span>{/if}
                    {if $link.description}<span class="description">{$link.description}</span>{/if}
                </a>
            </div>
        {/foreach}
    </div>

    <div class="common-links float-items">
        {foreach $commonLinks as $link}
            <div class="item" data-id="{$link.id}">
                <a href="{$link.common_name}">
                    {if $link.title_c}<span class="title">{$link.title_c}</span>{/if}
                    {if $link.description_c}<span class="description">{$link.description_c}</span>{/if}
                </a>
            </div>
        {/foreach}
    </div>

    <div class="categories-items float-items">
        {foreach $categories as $link}
            <div class="item" data-id="{$link.id}">
                <a href="{$link.href}">
                    {if $link.img}<span class="image"><img src="{$link.img}" alt="{$link.title}"></span>{/if}
                    {if $link.title}<span class="title">{$link.title}</span>{/if}
                    {if $link.description}<span class="description">{$link.description}</span>{/if}
                </a>
            </div>
        {/foreach}
    </div>

    <div class="info-items float-items">
        {foreach $info as $link}
            <div class="item" data-id="{$link.id}">
                <a href="{$link.href}">
                    {if $link.title}<span class="title">{$link.title}</span>{/if}
                    {if $link.description}<span class="description">{$link.description}</span>{/if}
                </a>
            </div>
        {/foreach}
    </div>

    <div class="brands-items float-items">
        {foreach $brands as $link}
            <div class="item" data-id="{$link.id}">
                <a href="{$link.href}">
                    {if $link.image}<span class="image"><img src="{$link.img}" alt="{$link.title}"></span>{/if}
                    {if $link.title}<span class="title">{$link.title}</span>{/if}
                </a>
            </div>
        {/foreach}
    </div>

    <div class="locations-items float-items">
        {foreach $locations as $link}
            <div class="item" data-id="{$link.id}">
                <a href="{$link.href}">
                    {if $link.image}<span class="image"><img src="{$link.img}" alt="{$link.title}"></span>{/if}
                    {if $link.title}<span class="title">{$link.title}</span>{/if}
                    {if $link.description}<span class="description">{$link.description}</span>{/if}
                </a>
            </div>
        {/foreach}
    </div>
</div>

<script>
    tl('{Info::themeFile('/js/image-map.js')}', function(){
        imageMap.imageMap('{$allItemsJson}')
    })
</script>