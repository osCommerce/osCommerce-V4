<div class="import_log">
    {if $message_string}
        {$message_string}
    {else}
        {foreach from=$messages item=message}
            <div>{$message.message_text}</div>
        {/foreach}
    {/if}
</div>
