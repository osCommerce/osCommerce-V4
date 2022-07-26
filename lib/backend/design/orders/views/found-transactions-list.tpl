{use class="common\helpers\Html"}
<div class="found-transactions-list">
<style>
table tr.selected-row{ background-color:#dfe0e4; }
</style>
{Html::beginForm($url, 'post', ['id' => 'frmAgn'])}
    {Html::hiddenInput('payment_class', $payment)}
    {if is_array($transactions) && count($transactions)>0}
        <label>{$smarty.const.TEXT_FOUND_TRANSACTIONS}</label>
        <table class="table datatable hover table-found-list table-responsive table-bordered table-colored table-striped table-selectable">
            <thead>
                <tr>
                    <th class="col-md-3">{$smarty.const.TEXT_TRANSACTION_ID}</th>
                    <th class="col-md-2">{$smarty.const.TEXT_DATE}</th>
                    <th class="col-md-2">{$smarty.const.TEXT_TRANSACTION_AMOUNT}</th>
                    <th class="col-md-2">{$smarty.const.ENTRY_STATUS}</th>
                    <th class="col-md-3">{$smarty.const.TABLE_TEXT_NAME}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $transactions as $transaction}
                <tr class="{if count($transactions) == 1}selected-row{/if} {if $transaction['negative']}negative{/if}">
                    <td data-id="{$transaction['id']}">{$transaction['id']}</td>
                    <td data-sort="{strftime('%Y%m%d%H%M%s')}">{$transaction['date']}</td>
                    <td>{$transaction['amount']}</td>
                    <td>{$transaction['status']}</td>
                    <td>{$transaction['name']}
                      {*<pre>{print_r($transaction, 1)}</pre>*}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <br/>
        </div>
        {Html::submitButton(TEXT_ASSIGN, ['class' => 'btn btn-assign-it'])}
    {else}
        <label><b>{$smarty.const.TEXT_NO_TRANSACTIONS}</b></label>
        {if is_string($transactions)}
          <br><span class="message">{$transactions}</span>
        {/if}
    {/if}
{Html::endForm()}
<script>
    $(document).ready(function(){
        function checkNegative(objTr){
            if ($(objTr).hasClass('negative')) {
                bootbox.alert('{$smarty.const.TEXT_NEGATIVE_TRANSACTION_UNALLOWED|escape:"javascript"}');
                return true;
            }
            return false;
        }
        
        var table = $('.table-found-list').dataTable({
            iLength:10,
            pageLength:10,
            "order": [[ 1, "desc" ]],
        });
        
        $('tr', table).click(function(){
            $('tr', table).removeClass('selected-row');
            $(this).addClass('selected-row');
        }).dblclick(function(){
            $('tr', table).removeClass('selected-row');
            $(this).addClass('selected-row');
            if (!checkNegative(this)){
                assign($('td', this).data('id'), $('#frmAgn input[name=payment_class]').val());
            }
        })
        
        function assign($id, $class){
            bootbox.confirm('{$smarty.const.TEXT_CONFIRM_ASSIGN_TRANSACTION|escape:"javascript"}', function(result){
                if (result){
                    var form = [];
                    form.push({ 'name': 'transaction_id', 'value':$id });
                    form.push({ 'name': 'payment_class', 'value': $class });
                    form.push({ 'name': 'action', 'value': 'assign_transaction' });
                    $.post('{$url}', form, function(data){
                        let list, type;
                        if (data.hasOwnProperty('errors')){
                            list = data.errors;
                            type = 'alert-warning';
                        }
                        if (data.hasOwnProperty('message')){
                            list = data.message;
                            type = 'alert-success';
                        }
                        if (Array.isArray(list)){
                            $('.atb #message_plce:last').html(list.join("<br>"));
                            $('.atb .alert.fade').removeClass('alert-warning alert-success').addClass(type).show();
                        }
                        if (data.hasOwnProperty('done')){
                            if(data.done){
                                $.get('{$url}',{}, function(data){ 
                                    $('.pop-up-content').html(data);
                                });
                            }
                        }
                    }, 'json');
                }
            });
        }
        
        $('.btn-assign-it').click(function(e){
            e.preventDefault();
            if ($('tr.selected-row', table).is('tr')){
                if (!checkNegative($('tr.selected-row', table))){
                    assign($('tr.selected-row td', table).data('id'), $('#frmAgn input[name=payment_class]').val());
                }
            } else {
                bootbox.alert('{$smarty.const.TEXT_SELECT_TRANSACTION|escape:"javascript"}');
            }
            return false;
        })
    })
</script>
</div>