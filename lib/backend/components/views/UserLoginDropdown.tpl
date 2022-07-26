<li class="dropdown user">
    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
        {$context->adminAvatar}
        <!--<i class="icon-male"></i>-->
        <span class="username">{$context->adminFullname}</span>
        <i class="icon-caret-down small"></i>
    </a>
    <ul class="dropdown-menu">
        <li><a href="{$app->urlManager->createUrl("adminaccount")}"><i class="icon-user"></i> {$smarty.const.TEXT_MY_ACCOUNT}</a></li>
        <!--<li><a href="pages_calendar.html"><i class="icon-calendar"></i> My Calendar</a></li>-->
        <!--<li><a href="#"><i class="icon-tasks"></i> My Tasks</a></li>-->
        <!--<li class="divider"></li>-->
        <li><a href="{$app->urlManager->createUrl("logout")}"><i class="icon-key"></i> {$smarty.const.TEXT_HEADER_LOGOUT}</a></li>
    </ul>
</li>
