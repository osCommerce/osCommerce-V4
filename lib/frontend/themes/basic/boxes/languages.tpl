<div class="languages" onclick="void(0)">
    <div class="current">
      {foreach $languages as $language}
        {if $language.id == $languages_id}
          {$language.image}
        {/if}
      {/foreach}
    </div>
    <div class="select">
      {foreach $languages as $language}
        {if $language.id != $languages_id}
          <a class="lang-link" href="{$language.link}">{$language.image}</a>
        {/if}
      {/foreach}
    </div>
</div>