{use class="frontend\design\Info"}{if !isset($lazy_load)}{$lazy_load = false}{/if}
<div class="image-box">
  {if $link}<a href="{$link}"{if $target_blank} target="_blank"{/if}{if $no_follow} rel="nofollow" {/if}>{/if}
  <img {if $lazy_load}class="lazy" data-{/if}src="{$image}" alt="{$alt}" {if $title} title="{$title}"{/if} style="border: none">
  {if $link}</a>{/if}
</div>
{if $lazy_load}
  <script>
      tl('{Info::themeFile('/js/jquery.lazy.min.js')}', function(){
          $('.lazy').lazy( { bind: 'event' } );
      })
  </script>
{/if}