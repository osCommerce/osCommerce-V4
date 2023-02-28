{use class="frontend\design\Info"}
        <script>
            var banners = [];
            var collectBanners = function(banner){
                var _b = { };
                if (banner.hasOwnProperty('banners_id')){ _b.id = banner.banners_id; }
                if (banner.hasOwnProperty('banners_title')){ _b.name = banner.banners_title; }
                if (banner.hasOwnProperty('text_position')){ _b.position = banner.text_position; }
                if (banner.hasOwnProperty('banners_group')){ _b.creative = banner.banners_group; }
                banners.push(_b);
            }
        </script>
<div class="banner">
    {if $banner_type == 'banner' || $banner_type == ''}
      
      {foreach $banners as $bKey => $banner}
        {$banner.banner_display = '2'}
        {if $banner.banners_html_text && $banner.banner_display == '1'}
          <div class="single_banner">{$banner.banners_html_text}</div>
        {elseif $banner.banners_image && (!$banner.banner_display || $banner.banner_display == 3)}
            {if $banner.banners_url}
              <script>collectBanners({json_encode($banner)});</script>
              <div class="single_banner"><a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if} class="imgBann" data-id="{$banner.banners_id}"{if $banner.nofollow == 1} rel="nofollow"{/if}>{$banner.image}</a></div>
            {else}
              <div class="single_banner"><span>{$banner.image}</span></div>
            {/if}
        {elseif $banner.banners_image && $banner.banner_display == '2'}
          <div class="image-text-banner {$banner.text_position}">
              {if $banner.banners_url}
                <div class="single_banner"><a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if} class="imgBann" data-id="{$banner.banners_id}"{if $banner.nofollow == 1} rel="nofollow"{/if}>{$banner.image}</a></div>
              {else}
                <div class="single_banner"><span>{$banner.image}</span></div>
              {/if}
              <div class="text-banner"><div class="text-banner-1"><div class="text-banner-2">{$banner.banners_html_text}</div></div></div>
          </div>
        {elseif $banner.banner_display == '4'}
            {if $banner.banners_url}
                <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if} class="imgBann" data-id="{$banner.banners_id}"{if $banner.nofollow == 1} rel="nofollow"{/if}>
                    <video id="video_background" preload="auto" autoplay="true" loop="true" muted="muted">
                        <source src="{$banner.banners_image_url}" type="video/mp4">
                    </video>
                    <script>collectBanners({json_encode($banner)});</script>
                </a>
            {else}
                <video id="video_background" preload="auto" autoplay="true" loop="true" muted="muted">
                    <source src="{$banner.banners_image_url}" type="video/mp4">
                </video>
            {/if}
        {/if}
      {/foreach}

    {elseif $banner_type == 'carousel'}
        <div class="carousel">
            {foreach $banners as $banner}
              {if $banner.is_banners_image_valid || $banner.banner_display == '1'}
                <div class="item">
                  {if $banner.banner_display == '0' ||  $banner.banner_display == '2' ||  $banner.banner_display == '3'}
                    {if $banner.banners_url}
                      <script>collectBanners({json_encode($banner)});</script>
                      <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if} class="imgBann" data-id="{$banner.banners_id}"{if $banner.nofollow == 1} rel="nofollow"{/if}>
                          <span class="carousel_img">{$banner.image}</span>
                          {if $banner.banner_display == '2'}
                              <span class="carousel-text {$banner.text_position}"><span>{$banner.banners_html_text}</span></span>
                          {/if}
                      </a>
                    {else}
                      <span class="carousel_img">{$banner.image}</span>
                      {if $banner.banner_display == '2'}
                          <span class="carousel-text {$banner.text_position}"><span>{$banner.banners_html_text}</span></span>
                      {/if}
                    {/if}
                  {elseif $banner.banner_display == '4'}
                      {if $banner.banners_url}
                      <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if} class="imgBann" data-id="{$banner.banners_id}"{if $banner.nofollow == 1} rel="nofollow"{/if}>
                          <video id="video_background" preload="auto" autoplay="true" loop="true" muted="muted">
                              <source src="{$banner.banners_image_url}" type="video/mp4">
                          </video>
                          <script>collectBanners({json_encode($banner)});</script>
                      </a>
                  {else}
                      <video id="video_background" preload="auto" autoplay="true" loop="true" muted="muted">
                          <source src="{$banner.banners_image_url}" type="video/mp4">
                      </video>
                  {/if}
                  {else}
                    <span class="carousel_text">{$banner.banners_html_text}</span>
                  {/if}

                </div>
              {/if}
            {/foreach}
        </div>

        <script>
            tl('{Info::themeFile('/js/slick.min.js')}', function(){

                var box = $('#box-{$id}');

                var carousel = $('.carousel', box);
                var tabs = carousel.parents('.tabs');
                tabs.find('> .block').show();

                {Info::addBoxToCss('slick')}
                carousel.slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: true,
                    autoplaySpeed: {if $settings.pauseTime}{$settings.pauseTime}{else}3000{/if}
                });
                setTimeout(function(){ tabs.trigger('tabHide') }, 100)

            })
        </script>


    {elseif $banner_type == 'slider'}


      <div class="slider-wrapper"><div id="slider" class="sliderItems">
          {foreach $banners as $banner}
              {if $banner.banner_display == '0' || $banner.banner_display == '2'}
                {if $banner.banners_url}
                    <a href="{$banner.banners_url}"{if $banner.target == 1} target="_blank"{else}{/if} class="imgBann" data-id="{$banner.banners_id}"{if $banner.nofollow == 1} rel="nofollow"{/if}>{$banner.image}<script>collectBanners({json_encode($banner)});</script></a>
                {else}
                    {$banner.image}
                {/if}
              {else}
                <div class="htmlBanText {$banner.text_position}">{$banner.banners_html_text}</div>
              {/if}
          {/foreach}
        </div></div>


    {if !Info::isAdmin()}
      <script type="text/javascript">
        tl('{Info::themeFile('/js/jquery.nivo.slider.pack.js')}', function(){
          $('head').append('<link rel="stylesheet" href="{Info::themeFile('/css/nivo-slider.min.css')}"/>');
          $('.sliderItems').nivoSlider({
            effect: '{$settings.effect}',
            slices: {$settings.slices},
            boxCols: {$settings.boxCols},
            boxRows: {$settings.boxRows},
            animSpeed: {$settings.animSpeed},
            pauseTime: {$settings.pauseTime},
            directionNav: {$settings.directionNav},
            controlNav: {$settings.controlNav},
            controlNavThumbs: {$settings.controlNavThumbs},
            pauseOnHover: {$settings.pauseOnHover},
            manualAdvance: {$settings.manualAdvance}
          });
        })
      </script>
    {/if}
    {/if}
    {\common\components\google\widgets\GoogleTagmanger::getJsEvents([[ 'class' => '.banner a.imgBann', 'action' => 'click' , 'php_action' => 'promotionClick', 'page' => 'current', 'immidiately' => 'true']])}
</div>