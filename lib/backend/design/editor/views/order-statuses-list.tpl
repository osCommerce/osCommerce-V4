{use class="\yii\helpers\Html"}
{function table}
    {$manager->render('StatusTable', ['manager' => $manager, 'enquire' => $enquire])}
{/function}

{function form}
{*Html::beginForm('', 'post')*}
        <div class="">
            <center>
            <div class="f_row">
                <div class="f_td">
                    <label>{$smarty.const.ENTRY_STATUS}</label>
                </div>
                <div class="f_td">
                    {Html::dropDownList('status', $status, $orders_statuses, ['class' => 'form-select'])}
                </div>
            </div>
            <div class="f_row">
                <div class="f_td">
                    <label>{$smarty.const.ENTRY_NOTIFY_COMMENTS}</label>
                </div>
                <div class="f_td">
                    {Html::textArea('comment', '', ['class' => 'form-control status-comment'])}
                </div>
            </div>
            </center>
        </div>
        {if !$hide}
        <div class="noti-btn">
          <div class="btn-left"><button class="btn btn-cancel" onclick="return closePopup()">{$smarty.const.IMAGE_CANCEL}</button></div>
          <div class="btn-right"><button class="btn btn-confirm" onclick="return updatePayOrder()">{$smarty.const.IMAGE_SAVE}</button></div>
        </div>
        {/if}
    {*Html::endForm()*}
{/function}

{if $hide}
    <div class="order-status-table-box">
        <div style="padding:10px;">
        {call table}
        </div>
        {call form hade=true}
    </div>
{else}
<div class="widget box box-no-shadow ">
        <div class="widget-header widget-header-order-status">
            <h4>{$smarty.const.TEXT_ORDER_STATUS}</h4>
        </div>
        <div class="widget-content usc-box">
            {call table}
        </div>
        {call form}
<script type="text/javascript">
function updatePayOrder() {
    var paid = 0;
    if ($('input[name="update_totals[ot_paid]"]:last').is('input')){
        paid = parseFloat($('input[name="update_totals[ot_paid]"]:last').val());
    }
    var total = parseFloat($('input[name="update_totals[ot_total]"]:last').val());
    var extra = [];
    extra.push({ 'name':'status','value':$('select[name=status]').val() });
    extra.push({ 'name':'comments','value':$('textarea.status-comment').val() });
    console.log(extra);
    order.saveOrder($('#checkoutForm'), extra,  'just_save', (total - paid), function(data){
        if (data.hasOwnProperty('prompt')) {
            history.replaceState({}, '', data.redirect);
            window.location.reload();
        }
    });
    return false;
}
</script>
</div>
{/if}
