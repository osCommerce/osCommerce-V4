{use class="yii\helpers\Html"}
<div class="widget box">
    <div id="CleanImportedData" class="widget-content">
        <ul>
            {Html::radioList("CleanFully", 'fully', ['fully' => $smarty.const.EXTENSION_OSCLINK_TEXT_CLEAN_RADIO_ALL, 'selected' => $smarty.const.EXTENSION_OSCLINK_TEXT_CLEAN_RADIO_SELECTED], ['separator' => '<br />'])}
            <div id="CleanSelectedItems" class="widget" style="display: none; margin: 10px 0 0 30px ">
                {foreach $app->controller->view->cleaningArray as $groupItem}
                    <li>{$groupItem['group_name']}</li>
                        <ul>
                        {foreach $groupItem['feeds'] as $feedItem}
                            <label><input type="checkbox" name="{$feedItem['feed']}" checked></input>{$feedItem['feed_name']}</label><br />
                        {/foreach}
                    </ul>
                {/foreach}
            </div>
        </ul>
        <p class="btn-wr">
            <a class="btn btn-primary btn-execute">{$smarty.const.EXTENSION_OSCLINK_TEXT_CLEAN_BUTTON}</a>
        </p>
    </div>
</div>
<script type="text/javascript">
</script>
