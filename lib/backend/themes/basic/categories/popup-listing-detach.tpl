<div>
    <form action="{Yii::$app->urlManager->createUrl(['categories/listing-detach', 'product_id' => $product_id])}" method="post" onsubmit="return confirmDetach(this)">
    <div class="pop-up-content">
        <div class="popup-heading">{$smarty.const.BUTTON_DETACH_LISTING_PRODUCT}</div>
        <div class="popup-content">
            {sprintf(TEXT_CONFIRM_DETACH_PRODUCT_S, $product_name, $parent_product_name)}
        </div>
    </div>
    <div class="noti-btn">
        <div class="btn-left">
            <a href="javascript:void(0)" class="btn btn-cancel-foot" onclick="return closePopup();">{$smarty.const.IMAGE_CANCEL}</a>
        </div>
        <div class="btn-right">
            <button class="btn btn-primary" type="submit">{$smarty.const.IMAGE_CONFIRM}</button>
        </div>
    </div>
    </form>
</div>
<script type="text/javascript">
    function confirmDetach(form) {
        $.post($(form).attr('action'), $(form).serializeArray(), function(){
            closePopup();
            if ( typeof resetStatement === 'function') resetStatement();
        });
        return false;
    }
</script>