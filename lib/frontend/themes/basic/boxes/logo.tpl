<div class="logo">
        {$style = ''}
    {if $width}
        {$style = 'width:'|cat:$width|cat:'px;height:auto'}
    {elseif $height}
        {$style = 'height:'|cat:$height|cat:'px;width:auto'}
    {/if}
    {if $url}
        <a href="{$url}"><img src="{$image}" alt="{STORE_NAME}" style="border: none;{$style}"></a>
    {else}
        <img src="{$image}" alt="{STORE_NAME}" style="border: none;{$style}">
    {/if}
</div>