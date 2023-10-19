{use class="common\helpers\Html"}
{include file="../assets/tabs.tpl"}

<div class="popup-heading after">{$smarty.const.POPUP_HEADING_ORDER_PAYMENT_ADD}</div>
<br />
<div class="col-md-12 popup-content">
  
{*{$tbs = [['title'=>$smarty.const.IMAGE_ADD, 'callback'=>'newOrderPayment', 'tabs_type' => 'hTab', 'cssClass' => 'tabs-add-payment'],
         ['title'=>$smarty.const.IMAGE_SEARCH, 'callback' => 'searchPayment', 'tabs_type' => 'hTab', 'cssClass' => 'tabs-search-payment']]}
{call hTab data=$tbs id_prefix="payment_search_add"}*}
    <div class="tabbable tabbable-custom">
        <ul class="nav nav-tabs tabs-h" id="payment_search_add_ul" role="tablist">
            <li{if !$search} class="active"{/if} data-bs-toggle="tab" data-bs-target="#payment_search_add_1">
                <a>{$smarty.const.IMAGE_ADD}</a>
            </li>
            <li{if $search} class="active"{/if} data-bs-toggle="tab" data-bs-target="#payment_search_add_2">
                <a>{$smarty.const.IMAGE_SEARCH}</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane{if !$search} active{/if}" id="payment_search_add_1">
                {call newOrderPayment}
            </div>
            <div class="tab-pane{if $search} active{/if}" id="payment_search_add_2">
                {call searchPayment}
            </div>
        </div>
    </div>

</div>

{function searchPayment}
  <div class="col-md-12 widget box">
    <div class="widget-content">
    {include file="payment-search.tpl"}
    </div>
  </div>
{/function}


{function newOrderPayment}
  <div class="widget-content">
    <form id="order_payment_edit" action="{Yii::$app->urlManager->createUrl('orders/payment-save')}" method="post">
    {tep_draw_hidden_field('oID', $oID)}
    {tep_draw_hidden_field('orders_payment_id', '0')}
    <div class="creditHistoryPopup">
        <div class="widget box box-wrapp-blue filter-wrapp">
            <div class="widget-content usc-box usc-box2">
                <div class="f_tab">
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_METHOD}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {tep_draw_pull_down_menu('orders_payment_module', $paymentArray, '', 'class="form-select"')}
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
                            {Html::textInput('orders_payment_amount', $paymentRecord['orders_payment_amount'])}
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_CURRENCY}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {tep_draw_pull_down_menu('orders_payment_currency', $currencyArray, $currencyDefaultCode, 'class="form-select"')}
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_ID}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {Html::textInput('orders_payment_transaction_id', $paymentRecord['orders_payment_transaction_id'])}
                        </div>
                    </div>
                    <div class="f_row">
                        <div class="f_td">
                            <label>{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_DATE}:&nbsp;</label>
                        </div>
                        <div class="f_td">
                            {Html::textInput('orders_payment_transaction_date', \common\helpers\Date::formatCalendarDate($paymentRecord['orders_payment_transaction_date']), ['class'=>"datepicker"])}
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
    <div class="btn-bar">
        <div class="btn-left"><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
        <div class="btn-right"><input type="submit" class="btn btn-confirm" value="{$smarty.const.IMAGE_UPDATE}"></div>
    </div>
    </form>
  </div>
{/function}


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
        {if $mode }
            $('#payment_search_add_ul .tabs-search-payment a:first').trigger('click');
        {else}
            if ($('#payment_search_add_ul .tabs-search-payment.active').length) {
                $('#payment_search_add_ul .tabs-add-payment a:first').trigger('click');
            }
        {/if}
    });
</script>