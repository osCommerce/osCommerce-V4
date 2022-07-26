<a href="{$link}" class="btn-2 btn-to-checkout"><span class="btn-title">{$smarty.const.PROCEED_TO_CHECKOUT}</span></a>
{if is_array($inline)}
  {foreach $inline as $link}
      <div class="or-text">{$smarty.const.TEXT_OR}</div>
      <div class="add-buttons">
          {$link}
      </div>
  {/foreach}
{/if}