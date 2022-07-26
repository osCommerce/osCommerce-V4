{if $app->controller->view->notificationCount gt 0}
    <li class="notification-block">
        <div class="notification-box" title="{$smarty.const.TEXT_ADMIN_NOTIFICATIONS}">{$app->controller->view->notificationCount}</div>
    </li>
    <script>
        $('.notification-box').click(function(){
            $.get('', { 'action': 'show-notifier-list' }, function(data){
                alertMessage(data);
                $('.notification-block').hide();
            })
        })
    </script>
{/if}