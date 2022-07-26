<div class="or_box_head">{$data.image_types_name}</div>
<div class="row_or_wrapp">
    {*<div class="row_or">
        <div>{$smarty.const.TEXT_DATE_ADDED}</div>
        <div>{\common\helpers\Date::date_short($cInfo->date_added)}</div>
    </div>*}
    {*<div class="row_or">
        <div>{$smarty.const.FIELDSET_ASSIGNED_PRODUCTS}:</div>
        <div>{$params['total_products']}</div>
    </div>*}
</div>
<div class="btn-toolbar btn-toolbar-order">
    <a class="btn btn-primary btn-edit btn-no-margin btn-process-order" href="{Yii::$app->urlManager->createUrl(['image-settings/edit', 'image_types_id' => $data.image_types_id])}">{IMAGE_EDIT}</a>
    {*<span class="btn btn-delete delete-item btn-process-order">{IMAGE_DELETE}</span>*}
</div>

<script type="text/javascript">
    $(function(){

        $('.delete-item').on('click', function(){
            $.get('{Yii::$app->urlManager->createUrl(['image-settings/delete-confirm'])}', {
                'image_types_id': '{$data.image_types_id}',
            }, function(data){
                $('.right_column .scroll_col').html(data);
            })
        })
    })
</script>