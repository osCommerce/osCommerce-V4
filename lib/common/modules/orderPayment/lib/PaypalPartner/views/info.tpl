{if $errors}
    {foreach $errors as $error}
        <div class="alert fade in alert-danger">
            <i data-dismiss="alert" class="icon-remove close"></i>
            <span id="message_plce">{$error}</span>
        </div>
    {/foreach}
{/if}
{if $messages}
    {foreach $messages as $message}
        <div class="alert fade in alert-info">
            <i data-dismiss="alert" class="icon-remove close"></i>
            <span id="message_plce">{$message}</span>
        </div>
    {/foreach}
{/if}
