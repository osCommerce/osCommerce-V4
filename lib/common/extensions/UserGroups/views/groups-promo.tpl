{if $allPromos && $groups_id}
{use class="yii\helpers\Html"}
<div class="row_fields promo-box">
    <div class="widget box box-no-shadow">
        <div class="widget-header widget-header-discount">
            <h4>{$smarty.const.TEXT_PERSONAL_PROMOTIONS}</h4></a>
        </div>
        <div class="widget-content">
        {if $myPromos}
            {foreach $myPromos as $myPromo}
                {if $myPromo->hasProperty('promotion')}
                <div class="w-line-row w-line-row-1">
                    <div class="wl-td1">
                        <label data-id="{$myPromo->promo_id}">{Html::a($myPromo->promotion->promo_label, Yii::$app->urlManager->createUrl(['promotions/edit', 'promo_id' => $myPromo->promo_id]), ['target' => '_blank'])} &nbsp;<span class="prmo del-pt"></span></label>
                    </div>
                </div>
                {/if}
            {/foreach}
        {/if}
        <div class="buttons_hours">
            <a href="{$app->urlManager->createUrl(['groups/show-promo', 'groups_id' => $groups_id ])}" class="btn" id="more-promo">{$smarty.const.TEXT_ADD_MORE}</a>
        </div>
        </div>
    </div>
    <script>
    (function($){
        $('#more-promo').popUp();
        
        $('.prmo.del-pt').click(function(){
            var label = $(this).closest('label');
            $.post('groups/drop-promo', {
                'groups_id': '{$groups_id}',
                'promo_id': label.data('id'),
            }, function(data, status){
                if (status =='success'){
                    label.remove();
                }
            })
        })
    })(jQuery)
    
    </script>
</div>
{/if}