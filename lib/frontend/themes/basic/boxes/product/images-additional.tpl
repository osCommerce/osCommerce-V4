{use class="frontend\design\Info"}
{\frontend\design\Info::addBoxToCss('product-images')}
{\frontend\design\Info::addBoxToCss('fancybox')}
{Info::addBoxToCss('slick')}
{if !$settings[0].no_wrapper}
<div class="js-product-image-set additional-images-box">
    <div class="images{if $settings[0].align_position == 'horizontal'} additional-horizontal{/if}">
{/if}

        <div class="additional-images"{if $images_count < 2} style="visibility: hidden; width: 0; height: 0" {/if}>

{function imageItem}
<div class="item" data-id="{$image_id}">
    {if $item.type == 'image'}
            <a href="{$item.image.Large.url}"
               title="{$item.title|escape:'html'}"
               class=" {if $item.default} active{/if}"
               data-fancybox-group="fancybox">
                <img
                        src="{$item.image.Small.url}"
                        data-med="{$item.image.Medium.url}"
                        data-lrg="{$item.image.Large.url}"
                        alt="{$item.alt|escape:'html'}"
                        title="{$item.title|escape:'html'}"
                        class="default item-img"
                        srcset="{$item.srcset}"
                        sizes="{$item.sizes}"
                >
            </a>
    {else}
            {if $item.video_type == 0}
                <img src="{$item.video_preview}" alt="" data-type="0" data-code="{$item.code}" class="add-video">
            {else}
                <video width="150px" height="100px" data-type="1" class="add-video">
                    <source src="{$item.src}">
                </video>
            {/if}
    {/if}
    </div>
{/function}

            {foreach $images as $image_id=>$item}
                {if $item.default == 1}
                    {imageItem item=$item image_id = $image_id}
                {/if}
            {/foreach}
            {foreach $images as $image_id=>$item}
                {if $item.default == 0}
                    {imageItem item=$item image_id = $image_id}
                {/if}
            {/foreach}
        </div>


{if !$settings[0].no_wrapper}
    </div>
</div>
{/if}