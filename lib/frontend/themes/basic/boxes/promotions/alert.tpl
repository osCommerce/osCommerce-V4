{use class="frontend\design\Info"}

 <div class="promo-alert-content {if $popup}popup{/if}" style="display:none;">
  {if is_array($message)}
   <div class="promo-alert-widget">
    {if isset($message['message'])}
    <div class="promo-message"><b>{$message['message']}</b></div>
    {/if}
    {if isset($message['award'])}
    <div class="points-added">{$message['award']} {$smarty.const.TEXT_POINTS_ADDED}</div>
    {/if}
    {if isset($message['type'])}
    <div class="promo-type">{$smarty.const.TEXT_POINTS_FOR}: <b>{$message['type']}</b></div>
    {/if}
    {if isset($message['total'])}
    <div class="promo-balance">{$smarty.const.YOUR_BONUS_POINTS_BALANCE}: <b>{$message['total']}</b></div>
    {/if}
   </div>
   {/if}
</div>
{if $popup}
<script>
    tl('{Info::themeFile('/js/main.js')}', function(){        
        alertMessage($('.promo-alert-content').html());
    })
</script>
{/if}
