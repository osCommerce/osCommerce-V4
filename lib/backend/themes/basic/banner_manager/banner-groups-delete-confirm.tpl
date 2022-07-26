<form action="" class="delete-type-form">
    <div class="popup-heading">{$smarty.const.IMAGE_DELETE} {$banners_group}</div>
    <div class="popup-content">
        {$smarty.const.DO_YOU_REALLY_WANT_DELETE_GROUP} {sprintf($smarty.const.TYPE_DELETE_TO_CONFIRM, $smarty.const.TYPE_DELETE)}
        <div style="padding-top: 10px"><input type="text" class="form-control confirm-input"/></div>
    </div>
    <div class="popup-buttons">
        <button type="submit" class="btn btn-delete btn-no-margin delete-product">{IMAGE_DELETE}</button>
        <span class="btn btn-edit btn-primary cancel-delete">{IMAGE_CANCEL}</span>
    </div>
</form>
<script type="text/javascript">
    $(function(){

        $('.popup-buttons .cancel-delete').on('click', function(){
            $.get("banner_manager/banner-groups-bar", {
                'banners_group' : '{$banners_group}'
            }, function(data){
                $('.right_column .scroll_col').html(data);
            });
        });

        $('.delete-type-form').on('submit', function(e){
            e.preventDefault();
            var form = $(this);
            if ($('.confirm-input', form).val().toLowerCase() === '{$smarty.const.TYPE_DELETE}'.toLowerCase()) {

                $.get('{Yii::$app->urlManager->createUrl(['banner_manager/banner-groups-delete'])}', {
                    'banners_group' : '{$banners_group}',
                }, function(data){
                    alertMessage('<div class="popup-content">'+data.text+'</div>');
                    if (data.status === 'ok') {
                        setTimeout("location.reload()", 1000)
                    }
                }, 'json');


            } else {
                alertMessage('<div class="popup-content">{sprintf(TYPE_DELETE_TO_CONFIRM, TYPE_DELETE)}</div>')
            }
            return false
        })
    })
</script>