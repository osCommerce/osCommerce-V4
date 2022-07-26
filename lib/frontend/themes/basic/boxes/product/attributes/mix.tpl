{use class="frontend\design\Info"}
<div class="mix-attributes multiattributes">
    {if !empty($item.image)}<img src="{$app->request->baseUrl}/images/{$item.image}" alt="{$item.title|escape:'html'}" width="48px;">{/if}
    {if !empty($item.color)}<span style="color: {$item.color};">{/if}
    <div>{$item.title}</div>
    {if !empty($item.color)}</span>{/if}
    {foreach $item.options as $option}
        {if $option['mix']}
        <label class="attribute-qty-block">
            <input type="hidden" name="{*$item.name*}mix_attr[{$products_id|escape:'html'}][][{$item.id}]" value="{$option.id}" {if $option.id==$item.selected}checked{/if}{if {strlen($option.params)} > 0} {$option.params}{/if}>
            {if !empty($option.image)}<img src="{$app->request->baseUrl}/images/{$option.image}" alt="{$option.text|escape:'html'}"  width="48px;">{/if}
            <span{if !empty($option.color)} style="color: {$option.color};"{/if}>{$option.text}</span>
            {\frontend\design\boxes\product\MultiQuantity::widget(['option' => $option])}
        </label>
        {/if}
    {/foreach}
    <script type="text/javascript">
      tl('{Info::themeFile('/js/main.js')}', function(){
        {Info::addBoxToCss('quantity')}
        $('input.qty-inp').quantity();
      })
    </script>
</div>
