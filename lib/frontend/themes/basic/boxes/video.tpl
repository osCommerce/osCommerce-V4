<video
        class="video-js"
        width="{if $settings[0].width_v}{$settings[0].width_v}{else}400{/if}px"
        height="{if $settings[0].height_v}{$settings[0].height_v}{else}300{/if}px"
        {if $settings[$languages_id].poster}poster="{$settings[$languages_id].poster}"{/if}
        {if $settings[0].autoplay}autoplay{/if}
        {if $settings[0].controls}controls{/if}
        {if $settings[0].loop}loop{/if}
        {if $settings[0].muted}muted{/if}
>
  <source src="{$settings[$languages_id].video}">
</video>