
{foreach $socials as $social}
    <a href="{$social.link}" class="{$social.css_class}" target="_blank" rel="noopener">
        <span class="image">
            {if $social.image}
                <img src="{$social.image}" alt="{$social.name}">
            {/if}
        </span>
        <span class="name">{$social.name}</span>
    </a>
{/foreach}