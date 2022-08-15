{use class="frontend\design\Info"}{if !isset($settings[0].lazy_load)}{$settings[0].lazy_load = false}{/if}
{if \common\helpers\Acl::checkExtensionAllowed('Promotions')}
{\common\extensions\Promotions\widgets\PromotionIcons\PromotionIcons::widget(['params' => ['product' => $product]])}
{/if}

<a href="{$product.link}">
    <picture>
        {foreach $product.sources as $source}

            <source
                {if $settings[0].lazy_load}srcset="{Info::themeSetting('na_product', 'hide')}"
                data-{/if}srcset="{$source.srcset}"
                media="{$source.media}"
            >
        {/foreach}
        <img
                {if $settings[0].lazy_load}src="{Info::themeSetting('na_product', 'hide')}"
                data-{/if}src="{$product.image}"
                alt="{str_replace('"', '″', strip_tags($product.image_alt))}"
                title="{str_replace('"', '″', strip_tags($product.image_title))}"
        >
    </picture>
</a>
