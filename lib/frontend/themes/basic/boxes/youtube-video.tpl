{use class="Yii"}
{use class="frontend\design\Info"}

{if $settings[0].showpopup}
    <a href="#youtube_{$youtube_video}" data-class="yv_popup" class="popup-video-click"><img src="http://i1.ytimg.com/vi/{$youtube_video}/hqdefault.jpg" data-video-url="{$youtube_video}" /></a>
    <div id="youtube_{$youtube_video}" style="display: none;">
        <iframe width="100%" height="{if $settings[0].height_v}{$settings[0].height_v}{else}480{/if}" src="https://www.youtube.com/embed/{$youtube_video}?rel={if $settings[0].rel}0{else}1{/if}&controls={if $settings[0].controls}0{else}1{/if}&showinfo={if $settings[0].showinfo}0{else}1{/if}" frameborder="0" allowfullscreen></iframe></div>
    <script type="text/javascript">
        tl('{Info::themeFile('/js/main.js')}', function () { 
            $('.popup-video-click').popUp();
        })
    </script>
{else}
    <iframe width="{if $settings[0].width_v}{$settings[0].width_v}{else}560{/if}" height="{if $settings[0].height_v}{$settings[0].height_v}{else}315{/if}" src="https://www.youtube.com/embed/{$youtube_video}?rel={if $settings[0].rel}0{else}1{/if}&controls={if $settings[0].controls}0{else}1{/if}&showinfo={if $settings[0].showinfo}0{else}1{/if}" frameborder="0" allowfullscreen></iframe>
{/if}