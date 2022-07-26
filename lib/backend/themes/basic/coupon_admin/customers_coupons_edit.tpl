{use class="yii\helpers\Url"}
{use class="common\helpers\Html"}
<style>
.white {
 background-color: #fff!important;
}
</style>
<div class="widget box box-no-shadow">
    <div class="widget-header widget-header-invoicenformat">
        <h4>{if $customersCoupontRecord->id == true} {$smarty.const.COUPON_CUSTOMERS_COUPONS_EDIT} {else} {$smarty.const.COUPON_CUSTOMERS_COUPONS_ADD} {/if}</h4></div>
    <div class="widget-content">
        <form name="edit_customercode" method="post" action="{Url::to('coupon_admincustomerscodes/save')}" onsubmit="return false;">
        <div class="w-line-row w-line-row-1">
            <div class="col-md-2">
                <label for="only_for_customer">{$smarty.const.COUPON_CUSTOMERS_COUPONS_ONLY_FOR_CUSTOMER}:</label>*
            </div>
            <div class="col-md-4">
                <input name="only_for_customer" id="only_for_customer" class="form-control only_for_customer_starts-input"  value="{$customersCoupontRecord->only_for_customer}" autocomplete="off" />
            </div>
        </div>             
        <div class="w-line-row w-line-row-1">
            <div class="col-md-2">
                <label>{$smarty.const.COUPON_CUSTOMERS_COUPON_CODE}:</label>
            </div>
            <div class="col-md-4">
                <input name="coupon_code" id="coupon_code" class="form-control coupon_code_starts-input" value="{$customersCoupontRecord->coupon_code}" autocomplete="off" />
            </div>
        </div>        
        {Html::input('hidden', 'cid', $cid)}
        {Html::input('hidden', 'id', $customersCoupontRecord->id)}
        <div class="btn-bar">                    
            <div class="btn-right">
                <input type="submit" class="btn btn-primary" value='{$smarty.const.IMAGE_CANCEL}' onclick="$('.customer-coupon').hide();$('#customerscoupons_management').hide();">
                <input accesskey="S" type="submit" class="btn btn-primary"  value='{$smarty.const.COUPON_CUSTOMERS_IMAGE_CONFIRM}' onclick="saveCustomersCode('{$customersCoupontRecord->id}','{$cid}');">
                {if $customersCoupontRecord->id != false}
                <a href="javascript:void(0)" onclick="deleteCustomersCode('{$customersCoupontRecord->id}');" class="btn btn-del">{$smarty.const.COUPON_CUSTOMERS_IMAGE_DELETE}</a>
                {/if}
            </div>
        </div> 
        </form>        
    </div>    
</div>