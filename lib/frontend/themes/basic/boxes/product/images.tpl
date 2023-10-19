{use class="frontend\design\Info"}
{\frontend\design\Info::addBoxToCss('product-images')}
{\frontend\design\Info::addBoxToCss('fancybox')}

<div class="js-product-image-set main-image-box">
    <div class="images{if $settings[0].align_position === 'horizontal'} additional-horizontal{else} additional-vertical{/if}">
        <div class="product-image">
        <div class="img-holder">
            <img
                    src="{$img}"
                    alt="{$main_image_alt|escape:'html'}"
                    itemprop="image"
                    title="{$main_image_title|escape:'html'}"
                    class="main-image"
                    {if $srcset}srcset="{$srcset}"{/if}
                    {if $sizes}sizes="{$sizes}"{/if}
            >
            {if isset($product.promo_class)}
                <span class="{$product.promo_class}"></span>
            {else if isset($product.promo_icon)}
                <span class="promo"><img src="{$product.promo_icon}"></span>
            {/if}
        </div>
        </div>

        {if !$settings[0].hide_additional}
            {\frontend\design\boxes\product\ImagesAdditional::widget(['settings' => [['no_wrapper' => '1', 'align_position' => $settings[0].align_position]], 'params' => $params])}
        {/if}

    </div>

</div>