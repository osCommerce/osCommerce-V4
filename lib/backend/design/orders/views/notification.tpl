{if $messages}
    {foreach $messages as $message}
        <div class="alert alert-{$message['type']} fade in"><i data-dismiss="alert" class="icon-remove close"></i>{$message['message']}</div>
    {/foreach}
{/if}