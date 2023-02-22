{use class="frontend\design\Info"}

<div class="banner-holder">

    {foreach $banners as $bKey => $banner}

        <div class="banner-item{if $banner.text_position} text-{$banner.text_position}{/if}">

            {if $banner.image}
                <div class="banner-image">
                    {if $banner.banners_url}
                        <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{/if}
                            class="banner-box banner-box-{$banner.banners_id}"
                            data-id="{$banner.banners_id}"
                            id="banner-box-{$banner.banners_id}"
                            {if $banner.nofollow == 1} rel="nofollow"{/if}>
                            {$banner.image}
                        </a>
                    {else}
                        <span class="banner-box banner-box-{$banner.banners_id}" id="banner-box-{$banner.banners_id}">{$banner.image}</span>
                    {/if}
                </div>
            {/if}

            {if $banner.banners_html_text}
                <div class="banner-text"><div class="banner-text-holder">{$banner.banners_html_text}</div></div>
            {/if}

        </div>

    {/foreach}

</div>
{\common\components\google\widgets\GoogleTagmanger::getJsEvents([[ 'class' => '.banner-item a', 'action' => 'click' , 'php_action' => 'promotionClick', 'page' => 'current', 'immidiately' => 'true']])}