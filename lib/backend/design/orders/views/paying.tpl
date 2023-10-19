{use class="yii\helpers\Html"}
{use class="common\helpers\OrderPayment"}
<style>
    .f_tr {
        padding:5px
    }
</style>
<div class="update_paid_on_process_holder">
    <div class="update-paid">
        <label style="vertical-align: top">
            {Html::checkbox('use_update_amount', false, [ 'class' => 'upade_paid_on_process', 'id' => 'use_update_amount_checkbox'  ])}
            {$smarty.const.TEXT_UPDATE_PAID_AMOUNT}
        </label>
        <div style='display: inline-block' class="">
            <div style='display: none' class='payingPayDetails'>
                <div class="f_tr after">
                    {Html::dropDownList('paid_prefix', '+', ['+' => '+', '-' => '-'], ['class' => 'form-select', 'style' => 'margin-left:5px; width: 45px; display: inline-block;', 'id' => 'use_update_amount_paid_prefix'])}
                    {Html::input('text', 'update_paid_amount', {$amt}, ['class' => 'form-control', 'style' => 'margin-left:5px; width: 100px; display: inline-block;', 'id' => 'update_paid_amount_input'])}<br />
                </div>
                <div class="f_tr after">
                    <div class="f_tr after">
                        {$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_STATUS}<span class="colon">:</span>
                        {Html::dropDownList('t_status', OrderPayment::OPYS_SUCCESSFUL, OrderPayment::getStatusList(OrderPayment::OPYS_SUCCESSFUL), ['class' => 'form-select', 'style' => 'margin-left:5px; width: 125px;  display: inline-block;', 'id' => 'update_paid_status'])}<br />
                    </div>
                    <div class="f_tr after">
                        {$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_ID}<span class="colon">:</span>
                        {Html::input('text', 'transaction_id', $trId, ['class' => 'form-control', 'style' => 'margin-left:5px; width: 150px; display: inline-block;', 'id' => 'update_paid_transaction'])}
                    </div>
                </div>
            </div>
        </div>
        <script>
            (function ($) {
                $('.upade_paid_on_process').change(function (e) {
                    e.preventDefault();
                    let parent = $(this).closest('div.update_paid_on_process_holder');
                    if ($(this).prop('checked')) {
                        $('.payingPayDetails', parent).show();
                        //$(parent).find('input[name=update_paid_amount]').attr('type', 'input');
                        //$(parent).find('select[name=paid_prefix]').css('display', 'inline-block');
                    } else {

                        $('.payingPayDetails', parent).hide();
                        //$(parent).find('input[name=update_paid_amount]').attr('type', 'hidden').val('0');
                        //$(parent).find('select[name=paid_prefix]').css('display', 'none');
                    }
                });
            }(jQuery));
        </script>
    </div>
</div>