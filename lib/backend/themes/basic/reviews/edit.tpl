{use class="yii\helpers\Html"}
<!--=== Page Content ===-->
<div id="rewiews_management_data">
<!--===Customers List ===-->
<form name="save_item_form" id="save_item_form" onSubmit="return saveItem();">
<div class="box-wrap">
    <div class="cedit-top redit-top after">
        <div class="cedit-block cedit-block-1">
            <div class="status-left" style="float: none;">
                <span>{$smarty.const.TABLE_HEADING_STATUS}</span>
                <input type="checkbox" name="status" value="on" class="check_bot_switch_on_off" {if $rInfo->status == 1}checked{/if} />
            </div>
        </div>
        <div class="cedit-block cedit-block-2">
            <div class="cr-ord-cust">
                <span>{$smarty.const.ENTRY_REVIEW}</span>
                <div>{$status}</div>
            </div>
        </div>
        <div class="cedit-block cedit-block-3">
            <div class="cr-ord-cust">
                <span>{$smarty.const.ENTRY_FROM}</span>
                <div><a href="{$app->urlManager->createUrl(['customers/customeredit', 'customers_id' => $rInfo->customers_id])}">{$rInfo->customers_name}</a></div>
            </div>
        </div>
        <div class="cedit-block cedit-block-4">
            <div class="cr-ord-cust">
                <span>{$smarty.const.ENTRY_DATE}</span>
                <div>{\common\helpers\Date::date_short($rInfo->date_added)}</div>
            </div>
        </div>
        <div class="cedit-block cedit-block-5">
            <div class="re-pr-img">
                <span>{$smarty.const.ENTRY_PRODUCT_IMAGE}</span><a href="{$app->urlManager->createUrl(['categories/productedit', 'pID' => $rInfo->products_id])}">{$image}</a>
            </div>
        </div>
    </div>    
    <div class="create-or-wrap after create-cus-wrap">
        <div class="widget box box-no-shadow" style="margin-bottom: 0;">
            <div class="widget-header widget-header-review">
                <h4>{$smarty.const.ENTRY_REVIEW_RATING}</h4>
            </div>
            <div class="widget-content">
                <div class="wedit-rev after">
                    <label>{$smarty.const.ENTRY_REVIEW}</label>
                    {Html::textarea('reviews_text', $rInfo->reviews_text, ['class' => 'form-control', 'rows' => '10'])}
                    <div class="no-translate">{$smarty.const.ENTRY_REVIEW_TEXT}</div>
                    <label>{$smarty.const.ENTRY_RATING}</label>
                    <div class="rating-holder">
                        {Html::radio('reviews_rating', $rInfo->reviews_rating == '1', ['class' => 'star', 'value' => '1', 'title' => "Rate this 1 star out of 5"])}
                        {Html::radio('reviews_rating', $rInfo->reviews_rating == '2', ['class' => 'star', 'value' => '2', 'title' => "Rate this 2 star out of 5"])}
                        {Html::radio('reviews_rating', $rInfo->reviews_rating == '3', ['class' => 'star', 'value' => '3', 'title' => "Rate this 3 star out of 5"])}
                        {Html::radio('reviews_rating', $rInfo->reviews_rating == '4', ['class' => 'star', 'value' => '4', 'title' => "Rate this 4 star out of 5"])}
                        {Html::radio('reviews_rating', $rInfo->reviews_rating == '5', ['class' => 'star', 'value' => '5', 'title' => "Rate this 5 star out of 5"])}
                    </div>
                </div>
            </div>
        </div>        
    </div>
</div>
<div class="btn-bar">
    <div class="btn-left"><a href="javascript:void(0)" onclick="return backStatement();" class="btn btn-cancel-foot">{$smarty.const.IMAGE_CANCEL}</a></div>
    <div class="btn-right"><button class="btn btn-confirm">{$smarty.const.IMAGE_SAVE}</button></div>
</div>
{Html::input('hidden', 'reviews_id', $rInfo->reviews_id)}
</form>
<script>
function saveItem() {
    $.post("{$app->urlManager->createUrl('reviews/submit')}", $('#save_item_form').serialize(), function (data, status) {
        if (status == "success") {
            $('#rewiews_management_data').html(data);
        } else {
            alert("Request error.");
        }
    }, "html");

    return false;
}
function backStatement() {
    window.history.back();
    return false;
}
$(document).ready(function(){ 
    $(".check_bot_switch_on_off").bootstrapSwitch(
        {
			onText: "{$smarty.const.SW_ON}",
			offText: "{$smarty.const.SW_OFF}",
            handleWidth: '20px',
            labelWidth: '24px'
        }
    );
    $('input.star').rating(); 
});
</script>

</div>
<!-- /Page Content -->
