{use class="yii\helpers\Html"}
<form action="" id="add-locations">
    <input type="hidden" name="platform_id" value="{$platform_id}"/>
    <input type="hidden" name="parent_id" value="{$parent_id}"/>
    <div class="popup-heading">{$page_name}</div>
    <div class="popup-content pop-mess-cont">
        <p>
            <label>{$smarty.const.TEXT_DELIVERY_LOCATION_BATCH_LOCATION_NAMES}</label>
            {Html::textarea('location_string','',['class'=>'form-control'])}
        </p>
    </div>
    <div class="noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><button type="submit" class="btn btn-primary btn-save">{$smarty.const.TEXT_BTN_OK}</button></div>
    </div>
</form>
<script type="text/javascript">
    (function($){
        $(function(){
            $('#add-locations').on('submit', function(){
                var values = $(this).serializeArray();
                $.post('{$action}', values, function(){
                    $('.popup-box-wrap:last').remove();
                    $(window).trigger('reload-frame');
                }, 'json');

                return false
            })
        })
    })(jQuery)
</script>