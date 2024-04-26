{function languageItem}
    {if !$settings[0].hide_image}
        <span class="image">{$language.image}</span>
    {/if}
    {if $settings[0].show_title}
        <span class="name">{$language.name}</span>
    {/if}
    {if $settings[0].show_key}
        <span class="key">{$language.key}</span>
    {/if}
{/function}

<div class="languages" onclick="void(0)">
    <div class="current">
        {foreach $languages as $language}
            {if $language.id == $languages_id}
                {languageItem}
            {/if}
        {/foreach}
    </div>
    {if $languages|count > 1}
    <div class="select">
        {foreach $languages as $language}
            {if $language.id == $languages_id}
                <span class="lang-link current-item" style="display: none">
                    {languageItem}
                </span>
            {else}
                <a class="lang-link" href="{$language.link}">
                    {languageItem}
                </a>
            {/if}
        {/foreach}
    </div>
    {/if}
</div>