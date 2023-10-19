{use class="\yii\helpers\Html"}
<div class="popup-heading">{$smarty.const.POPUP_HEADING_ORDER_PAYMENT_EDIT}</div>
<form id="order_payment_edit" action="{Yii::$app->urlManager->createUrl('orders/payment-save')}" method="post">
    {tep_draw_hidden_field('orders_payment_id', $paymentRecord['orders_payment_id'])}
    <div class="creditHistoryPopup">
        <div class="widget box box-wrapp-blue filter-wrapp">
            <div class="widget-content usc-box usc-box2">
                <div class="f_tab">
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_METHOD}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            <span>{$paymentRecord['orders_payment_module_name']}</span>
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_STATUS}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {tep_draw_pull_down_menu('orders_payment_status', $orderPaymentStatusArray, $paymentRecord['orders_payment_status'], 'class="form-select"')}
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_AMOUNT}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {tep_draw_input_field('orders_payment_amount', $paymentRecord['orders_payment_amount'], 'class="form-control"')}
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_CURRENCY}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            <span>{$paymentRecord['orders_payment_currency']}</span>
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_CURRENCY_RATE}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            <span>{$paymentRecord['orders_payment_currency_rate']}</span>
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_ID}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {tep_draw_input_field('orders_payment_transaction_id', $paymentRecord['orders_payment_transaction_id'], 'class="form-control"')}
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_DATE}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {tep_draw_input_field('orders_payment_transaction_date', \common\helpers\Date::formatCalendarDate($paymentRecord['orders_payment_transaction_date']), 'class="datepicker form-control"')}
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_STATUS}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            <span>{$paymentRecord['orders_payment_transaction_status']}</span>
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_COMMENTARY}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {tep_draw_textarea_field('orders_payment_transaction_commentary', 'soft', '40', '5', $paymentRecord['orders_payment_transaction_commentary'], 'class="form-control"', false)}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="mail-sending noti-btn">
        <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div><input type="submit" class="btn btn-confirm" value="{$smarty.const.IMAGE_UPDATE}"></div>
    </div>
</form>
<script>
    $('#order_payment_edit').submit(function(e) {
        e.preventDefault();
        $.post('{Yii::$app->urlManager->createUrl('orders/payment-save')}', $(this).serializeArray(), function(response, status) {
            if (status == 'success') {
                if (response.status == 'ok') {
                    $('#order_payment_edit .btn-cancel').trigger('click');
                }
                if (response.message != '') {
                    alert(response.message);
                }
                if (response.reload == 1) {
                    window.location = window.location;
                }
            }
        }, 'json');
    });

    $(document).ready(function() {
	$('.datepicker').datepicker({
            changeMonth: true,
            changeYear: true,
            showOtherMonths:true,
            autoSize: false,
            dateFormat: 'dd M yy',
            onSelect: function(e) {
                if ($(this).val().length > 0) {
                    $(this).siblings('span').addClass('active_options');
                } else {
                    $(this).siblings('span').removeClass('active_options');
                }
            }
        });
    });
</script>