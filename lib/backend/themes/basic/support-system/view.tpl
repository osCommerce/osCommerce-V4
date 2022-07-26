{if $product}
    <div class="or_box_head">{$product->description->products_name}</div>
        <div class="row_or_wrapp">
            <div class="row_or"><div>Topics Amount:</div><div>{$product->getTopicsAmount()}</div></div>
        </div>
        <div class="btn-toolbar">            
            <center><button class="btn btn-delete" onclick="confirmDeleteTopic({$product->products_id})">{$smarty.const.IMAGE_DELETE}</button></center>
        </div>
{else}
     <div class="or_box_head">{$topic->description->info_title}</div>
        <div class="row_or_wrapp">
            <div class=""><div>Faq Topic for {$topic->product->description->products_name}</div><div></div></div>
        </div>        
        <div class="row_or_wrapp">
            <div class="row_or"><div>{$smarty.const.TEXT_INFO_DATE_ADDED}</div><div>{\common\helpers\Date::date_short($topic->date_added)}</div></div>
        </div>
        <div class="row_or_wrapp">
            {if tep_not_null($topic->last_modified)}
                <div class="row_or"><div>{$smarty.const.TEXT_INFO_LAST_MODIFIED}</div><div>{\common\helpers\Date::date_short($topic->last_modified)}</div></div>
            {/if}
        </div>
        <div class="btn-toolbar">
            <a href="{\Yii::$app->urlManager->createUrl(['support-system/edit', 'tID' => $topic->topic_id])}" class="btn btn-edit btn-no-margin">{$smarty.const.IMAGE_EDIT}</a>
            <button class="btn btn-delete" onclick="confirmDeleteTopic({$topic->products_id}, {$topic->topic_id})">{$smarty.const.IMAGE_DELETE}</button>
        </div>
{/if}