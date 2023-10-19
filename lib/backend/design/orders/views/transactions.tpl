{*use class="\backend\design\editor\Transactions"*}
<style>
.transactions-box .actions-box { height: 50px; margin: 0 0 3px; padding: 10px; border: 1px solid #ccc; }
table.table-transactions td:last-child { text-align:center; }
</style>
<div class="widget box after">
    <div class="widget-header">{$smarty.const.TEXT_TRANSACTIONS}</div>
    <div class="popupCategory">
        <div class="tabbable tabbable-custom">
            {assign var=activeTab value="tab_assigned"}
            {if $manager->getOrderSplitter()->hasUnclosedRma($orders_id)}
                {$activeTab="tab_rma"}
            {/if}
            <ul class="nav nav-tabs">
               <li {if $activeTab == 'tab_assigned'}class="active"{/if} data-bs-toggle="tab" data-bs-target="#tab_assigned"><a><span>{$smarty.const.TEXT_TRANSACTIONS}</span></a></li>
               {if $activeTab == 'tab_rma'}
               <li class="active" data-bs-toggle="tab" data-bs-target="#tab_rma"><a><span>{$smarty.const.TEXT_CREDITNOTE}</span></a></li>
               {/if}
               <li data-bs-toggle="tab" data-bs-target="#tab_assign"><a><span>{$smarty.const.TEXT_ASSIGN_TRANSACTIONS}</span></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane {if $activeTab == 'tab_assigned'}active{/if} tabbable-custom" id="tab_assigned">
                    <div class="widget-content transactions-box">
                        <table class="table table-striped table-selectable table-checkable table-hover table-responsive table-bordered datatable tabl-res table-transactions table-colored">
                            <thead>
                                <tr>
                                    <th>{$smarty.const.TEXT_TRANSACTION_ID}</th>
                                    <th>{$smarty.const.BOX_MODULES_PAYMENT}</th>
                                    <th>{$smarty.const.TEXT_TRANSACTION_AMOUNT}</th>
                                    <th>{$smarty.const.ENTRY_STATUS} / {$smarty.const.TEXT_DATE}</th>
                                    <th>{$smarty.const.TABLE_HEADING_COMMENTS}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                            {if $transactions}
                                {foreach $transactions as $transaction}
                                    <tr class="parent-tr-row" data-id="{$transaction->orders_transactions_id}">
                                        <td>{$transaction->transaction_id}<input type="hidden" class="oid" value="{$transaction->orders_transactions_id}"></td>
                                        <td>{$manager->getPaymentCollection()->get($transaction->payment_class)->title}</td>
                                        <td>{$currencies->format($transaction->transaction_amount, false, $transaction->transaction_currency)}</td>
                                        <td class="tr-status">{\yii\helpers\Inflector::humanize($transaction->transaction_status)}<br>{\common\helpers\Date::formatDateTime($transaction->date_created)}</td>
                                        <td>{$transaction->comments}</td>
                                        <td class="tr-actions"></td>
                                    </tr>
                                {/foreach}
                            {else}
                                <tr>
                                    <td colspan="6">{$smarty.const.TEXT_NO_TRANSACTIONS}</td>
                                </tr>
                            {/if}
                            </tbody>
                        </table>
                    </div>
                </div>
                {if $activeTab == 'tab_rma'}
                <div class="tab-pane active tabbable-custom" id="tab_rma">
                    <div class="widget-content transactions-rma-box">
                        {$manager->render('CreditNotes', ['manager' => $manager, 'orders_id' => $orders_id])}
                    </div>
                </div>
                {/if}
                <div class="tab-pane tabbable-custom" id="tab_assign">
                    <div class="widget-content transactions-assign-box">
                        {$manager->render('AssignTransactions', ['manager' => $manager, 'orders_id' => $orders_id])}
                    </div>
                </div>
            </div>
        </div>
        <div class="preloader" style="display:none;text-align:center;padding:0;"></div>
    </div>
</div>
<script>
    var trTable;    
    var transPo = function(){
        return {
            transactions: [],
            payment_transactions: [],
            creditNote :{
                amount:0
            },
            getVoidBtn: function(type, id, full, holder){
                if (typeof holder == 'undefined' || holder.length == 0){
                    holder = '';
                }
                return '<button class="btn btn-default btn-make-'+type+'" data-id="'+id+'" data-full="'+full+'" data-holder="'+holder+'">{$smarty.const.IMAGE_REFUND|escape:javascript}</button>';
            },
            getCNurl: function (id){
                return '<a href="{$cnurl}?orders_id='+{$orders_id}+'&cnId='+id+'" class="" target="_blank">{$smarty.const.TEXT_CREDITNOTE|escape:javascript}</a>';
            },
            getCNAmount(){
                if ($('#total_due').is('span') && !isNaN(parseFloat($('#total_due').data('value'))) ){
                    this.creditNote.amount = parseFloat($('#total_due').data('value'));
                }
            },
            getTransactionAmount:function(id){
                var _amount = 0;
                if (this.payment_transactions.length>0){
                    $.each(this.payment_transactions, function (i, tr){
                        if (tr.parent == id){
                            _amount = tr.amount;
                        }
                    })
                }
                return _amount;
            },
            readyToReturn: function(){
                if (Array.isArray(this.creditNote.to_return)){
                    $.each(this.creditNote.to_return, function(i, e){
                        if ($('input[name="transaction['+e.transaction_orders_id+']"].trans-amount').is('input')){
                            var val = parseFloat(unformatMaskField('input[name="transaction['+e.transaction_orders_id+']"].trans-amount'));
                            if ( val > 0 ){
                                e.returning_amount = val;
                                e.invalid = false;
                            } else {
                                e.invalid = true;
                            }
                        }
                    })
                    var validAmount = 0;
                    var allowedAmount = 0;
                    $.each(this.creditNote.to_return, function(i, e){
                        if (!e.invalid){
                            validAmount += e.returning_amount;
                            allowedAmount += e.full_amount;
                        }
                    })
                    
                    if (allowedAmount >= validAmount && validAmount > 0 && allowedAmount > 0){
                        this.creditNote.amount = validAmount;
                        return true;
                    }
                }
                return false;
            },
            processRefund:function(){
                if (this.readyToReturn()){
                    //$('.transactions-list').hide();
                    var T = this;
                    $('.popupCategory .preloader').show();
                    $.post('{$url}',{
                        'action': 'return_by_credit',
                        'transaction_data': this.creditNote,
                    }, function(data){
                        $('.popupCategory .preloader').hide();
                        if(data.hasOwnProperty('log')){
                            $('.transactions-log').html('<div><b>Log:</b></div>');
                            if (Array.isArray(data.log)){
                                $.each(data.log, function(i, e){
                                    $('.transactions-log').append('<div>'+e+'</div>');
                                })
                            } else {
                                $('.transactions-log').html(data.log);
                            }
                        }
                        if (data.hasOwnProperty('returned_amount')){
                            $('#total_refunded').html(data.returned_amount);
                        }
                        if (data.hasOwnProperty('hide')){
                            $.each(data.hide, function(i, e){
                                $('.transactions-list div[data-class="parent-'+e+'"]').remove();
                            })
                            $('.btn-go-return').hide();
                        }
                        if (data.hasOwnProperty('cn_id')){
                            if (data.cn_id){
                                $('.transactions-log').append('<div><a href="{Yii::$app->urlManager->createUrl(['orders/credit-notes', 'orders_id' => $orders_id])}'+'&cnId='+data.cn_id+'" target="_blank">{$smarty.const.TEXT_CREDITNOTE|escape:javascript} ('+data.cn_id+')</a></div>');
                            }
                        }
                        T.reloadOProcess();
                        T.checkTransactions();
                    }, 'json');
                } else{
                    bootbox.alert("{$smarty.const.TEXT_REFUND_AMOUNT_ENOUGH|escape:javascript}");
                }
            },
            reloadOProcess: function(){
                $.get('{$orderprocessUrl}',{}, function(data){ $('#order_management_data').html(data.content); },'json');
            },
            checkTransactions: function (){
                if (this.transactions.length ){
                    $('.popupCategory .preloader').show();
                    $.post('{$url}',{
                        'action': 'check_server_refunds',
                        'orders_transactions': this.transactions,
                        'return':1,
                    }, function(data){
                        $.when(redrawTransactions(data)).done(checkCreditNotes()).then(function(){ setEvents(); });
                        $('.checking-refunds').html('');
                        $('.popupCategory .preloader').hide();
                    }, 'json');
                }
            },
            refundOrVoid: function(id, amount, action){
                var T = this;
                $('.popupCategory .preloader').show();
                $.post('{$url}', {
                    'amount': amount,
                    'action': 'make_'+action,
                    'transaction_orders_id' : id
                }, function(data){
                    $('.popupCategory .preloader').hide();
                    T.reloadOProcess();
                    T.checkTransactions();
                    //redrawTransactions(data);
                }, 'json');
            },
            unlinkTransaction: function(toid){
                if (toid){
                    var T = this;
                    $('.popupCategory .preloader').show();
                    $.post('{$url}', {
                        'action': 'unlink_transaction',
                        'transaction_orders_id' : toid
                    }, function(data){
                        $('.popupCategory .preloader').hide();
                        T.transactions.splice(T.transactions.indexOf(toid),1);
                        var input = $('input.oid[value='+toid+']:hidden');
                        if ($(input).is('input')){
                            T.payment_transactions.forEach(function(item){ if (item.parent == toid ){ if (Array.isArray(item.children)){ item.children.forEach(function(child){ if (child.orders_transactions_child_id){ $('input.child-trid[value='+child.orders_transactions_child_id+']').parents('tr').remove(); } }) } } }, T.payment_transactions);
                            $(input).parents('tr').remove();
                        }
                        T.payment_transactions = [];
                    }, 'json');
                }
            }
        }
    }
    
    var trans = new transPo();
        
    function setEvents(){
        $('.btn-make-refund').click(function(){
            var id = $(this).data('id');
            if (id){
                var trAmount = trans.getTransactionAmount(id);
                if (!$(this).data('full') == 'full'){
                    var holder = $(this).data('holder');
                    if (holder.length>0){
                        trAmount = $(holder).val();
                    }
                }
                bootbox.prompt({
                    title : '{$smarty.const.TEXT_REFUND_PROMPT|escape:javascript}',
                    value : trAmount,
                    callback: function(result){
                        if (result !== null){
                            if (parseFloat(result) > trAmount){
                                bootbox.alert('{$smarty.const.TEXT_REFUND_AMOUNT_ERROR|escape:javascript}');
                            } else {
                                trans.refundOrVoid(id, result, 'refund');
                            }
                        }
                        
                    }
                });
            } else {
                console.error('{$smarty.const.TEXT_REFUND_UNDEFINED_TRANSACTION|escape:javascript}');
            }
        })
        
        $('.btn-make-void').click(function(){
            var id = $(this).data('id');
            if (id){
                bootbox.confirm('{$smarty.const.TEXT_REFUND_PROMPT|escape:javascript}', function(result){
                    if (result){
                        trans.refundOrVoid(id, 0, 'void');
                    }
                })
            } else {
                console.error('{$smarty.const.TEXT_REFUND_UNDEFINED_TRANSACTION|escape:javascript}');
            }
        })
        $('.btn-go-return').click(function(){
            trans.processRefund();
        })
        
        $('.transactions-list').find('input.trans-amount').setMaskMoney();
        
        $('.unlink-transaction').click(function(){
            var owner = $(this).parents('tr').find('input.oid:hidden');
            if ($(owner).is('input')){
                bootbox.confirm('{$smarty.const.TEXT_UNLINK_PROMPT|escape:javascript}', function(result){
                    if (result){
                        var id = $(owner).val();
                        trans.unlinkTransaction(id);
                    }
                })
            }
        })
    }
    
    function clearActions(rows){
        if (Array.isArray(rows)){
            $.each(rows, function (i, row){
                trTable.fnUpdate('', i , 5 );
            })
        }
    }
    
    function redrawTransactions(data){
        if (data.hasOwnProperty('statuses')){
            $.each(trTable.fnGetNodes(), function(i, e){
               if (!$(e).hasClass('parent-tr-row')){
                trTable.fnDeleteRow(i);
               }
            });
            _rows = trTable.fnGetData();
            clearActions(_rows);
            var shift = 0, unshift = 0;
            $.each(data.statuses, function (i, dta){
                trans.payment_transactions.push(dta);
                unshift = 0;
                if (dta.hasOwnProperty('children') && Array.isArray(dta.children) && dta.children.length > 0){
                    var rIndex = trTable.fnGetPosition($('.parent-tr-row[data-id='+dta.parent+']').get(0)) + shift;
                    $.each(dta.children, function(iter, child){
                        var cChild = getRow(child);
                        if (child.hasOwnProperty('splinters_suborder_id') && parseInt(child.splinters_suborder_id) > 0){
                            cChild[5] = trans.getCNurl(child.splinters_suborder_id);
                        }
                        _rows.splice(rIndex+1, 0, cChild);
                        shift++;
                    })
                    unshift = dta.children.length;
                }
                
                if (Array.isArray(_rows[i+shift-unshift])){
                    if ( dta.hasOwnProperty('can_void') && dta.can_void ){
                        _rows[i+shift-unshift][5] = trans.getVoidBtn('void', dta.parent, 'full');
                    }
                    if ( dta.hasOwnProperty('can_refund') && dta.can_refund ){
                        _rows[i+shift-unshift][5] = trans.getVoidBtn('refund', dta.parent, 'full');
                    }
                    
                    _rows[i+shift][3] = dta.status + '<br>'+dta.date_created;
                } else {
                    _rows.push(_empty);
                }
            })
            
            if (_rows.length > 0){
                trTable.fnClearTable();
                $.each(_rows, function(i, e){
                    trTable.fnAddData(_empty);
                    trTable.fnUpdate(e, i);
                })
                $.each(trTable.find('input.oid:hidden'), function(i,e){ 
                    $(e).closest('tr').addClass('parent-tr-row').css('background-color', '#f7f4f4').attr('data-id', $(e).val());
                    let context = $(e).closest('tr.parent-tr-row').find('td:last').html();
                    context = context + '<br>' + '<span><i class="icon-trash unlink-transaction"></i></span>';
                    $(e).closest('tr.parent-tr-row').find('td:last').html(context);
                })
            }
        }
    }
    
    function getRow(child){
        return [
            '&nbsp;&nbsp;&nbsp;'+child.transaction_id+"<input type='hidden' class='child-trid' value='"+child.orders_transactions_child_id+"'>",
            '',
            child.transaction_amount,
            child.transaction_status+'<br>'+child.date_created,
            child.comments,
            ''
        ];
    }
    
    var _rows =[];
    var _empty = ['','','','','',''];
    $(document).ready(function(){
        {if $manager->getOrderInstance()->hasTransactions()}
        trTable = $('.table-transactions').dataTable({
             "order": [[ 0, "desc" ]],
             "ordering": false
        });
        
        $.each(trTable.fnGetNodes(), function (i, e){
            trans.transactions.push($(e).find('.oid:hidden').val());
        })
        
        trans.checkTransactions();
        trans.getCNAmount();
        {/if}
    })
    
    function checkCreditNotes(){
        if (trans.payment_transactions.length > 0 && $('#tab_rma').is('div')){
            var allowedAmount = 0;
            var toRet = {};
            $.each(trans.payment_transactions, function (i, paytrs){
                if (paytrs.hasOwnProperty('can_refund') && paytrs.hasOwnProperty('can_void')){
                    if (paytrs.can_refund || paytrs.can_void){
                        var dti = $('input[name="transaction['+paytrs.parent+']"].trans-amount');
                        if (dti.is('input')){
                            if (paytrs.can_void){
                                dti.attr('readonly', 'readonly').attr('title', '{$smarty.const.TEXT_CAN_VOID_ONLY|escape:javascript}');
                            }
                            paytrs.amount = parseFloat(paytrs.amount);
                            if (paytrs.hasOwnProperty('children') && Array.isArray(paytrs.children)){
                                $.each(paytrs.children, function( ci, ce){
                                    paytrs.amount -= parseFloat(ce.transaction_amount_clear);
                                })
                            }
                            allowedAmount = paytrs.amount + allowedAmount;
                            if (!Array.isArray(trans.creditNote.to_return)) trans.creditNote.to_return = [];
                            toRet = { 'transaction_orders_id': paytrs.parent, 'returning_amount': parseFloat(dti.val()), 'full_amount': paytrs.amount };
                            trans.creditNote.to_return.push(toRet);
                        }
                    }
                    if ( !(paytrs.can_refund || paytrs.can_void )){
                        $('div[data-class="parent-'+paytrs.parent+'"]').remove();
                    }
                }
            })
            
            if (trans.creditNote.amount <= allowedAmount && allowedAmount > 0){
                $('.btn-box').html('<button class="btn btn-confirm btn-go-return">{$smarty.const.IMAGE_REFUND|escape:javascript}</button>');
            } else {
                console.error('leak of amount: '+allowedAmount+' for '+trans.creditNote.amount);
            }
            
            if ( !$('.transactions-list > div').length ){
                $('.transactions-list').html('{$smarty.const.TEXT_NO_TRANSACTIONS|escape:javascript}');
            }
        }
        
    }
</script>
