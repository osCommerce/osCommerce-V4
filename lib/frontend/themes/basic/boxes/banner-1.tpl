{use class="frontend\design\Info"}

<div class="banner-holder">

    {foreach $banners as $bKey => $banner}

        <div class="banner-item{if $banner.text_position} text-{$banner.text_position}{/if}">

            {if $banner.banners_image && in_array($banner.banner_display, ['0', '2', '3'])}
                <div class="banner-image">
                    {if $banner.banners_url}
                        <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{/if}
                            data-id="{$banner.banners_id}"
                            {if $banner.nofollow == 1} rel="nofollow"{/if}>
                            {$banner.image}
                        </a>
                    {else}
                        <span>{$banner.image}</span>
                    {/if}
                </div>
            {/if}

            {if $banner.banners_html_text && in_array($banner.banner_display, ['1', '2'])}
                <div class="banner-text"><div class="banner-text-holder">{$banner.banners_html_text}</div></div>
            {/if}

            {if $banner.banners_image_url && in_array($banner.banner_display, ['4'])}
                {capture name="video"}
                    <video preload="auto" autoplay="true" loop="true" muted="muted">
                        <source src="{$banner.banners_image_url}" type="video/mp4">
                    </video>
                {/capture}
                {if $banner.banners_url}
                    <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{/if}
                       data-id="{$banner.banners_id}"
                            {if $banner.nofollow == 1} rel="nofollow"{/if}>
                        {$smarty.capture.video}
                    </a>
                {else}
                    {$smarty.capture.video}
                {/if}
            {/if}

        </div>

    {/foreach}

</div>
{\common\components\google\widgets\GoogleTagmanger::getJsEvents([[ 'class' => '.banner-item a', 'action' => 'click' , 'php_action' => 'promotionClick', 'page' => 'current', 'immidiately' => 'true']])}