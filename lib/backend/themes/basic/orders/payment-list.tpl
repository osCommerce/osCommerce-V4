{use class="common\helpers\Html"}
{if !$listOnly }
<div class="popup-heading">{$smarty.const.POPUP_HEADING_ORDER_PAYMENT}</div>
{/if}
<div class="creditHistoryPopup">
    <table class="table table-striped table-bordered table-hover table-responsive table-ordering order-payment-datatable double-grid">
        <thead>
            <tr>
                <th>&nbsp;</th>
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_PAYMENT_METHOD}</th>
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_PAYMENT_STATUS}</th>
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_PAYMENT_AMOUNT}</th>
                <th class="col-md-2" data-orderable="false">{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_DATE} / {$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_ID}</th>
                <th class="col-md-2" data-orderable="false">{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_STATUS}</th>
                <th class="col-md-4" data-orderable="false">{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_COMMENTARY}</th>
                {*<th data-orderable="false">{$smarty.const.TABLE_HEADING_PAYMENT_TRANSACTION_DATE}</th>
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_PROCESSED_BY}</th>
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_DATE_ADDED}</th>
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_UPDATE_BY}</th>
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_UPDATE_DATE}</th>*}
                <th data-orderable="false">{$smarty.const.TABLE_HEADING_ACTION}</th>
            </tr>
        </thead>
        <tbody>
            {foreach $paymentArray as $paymentRecord}
                <tr>
                    <td>{$paymentRecord['orders_payment_id']}</td>
                    <td>{$paymentRecord['orders_payment_module_name']}</td>
                    <td>{$paymentRecord['orders_payment_status']}</td>
                    <td><span style="color: {$paymentRecord['orders_payment_amount_colour']};">{$paymentRecord['orders_payment_amount']}</span></td>
                    <td>
                      <span class="transaction-date">{$paymentRecord['orders_payment_transaction_date']}</span>
                      <br />
                      <span class="transaction-id">{$paymentRecord['orders_payment_transaction_id']}</span>


                      <br />
                      <br />
                      <label>{$smarty.const.TABLE_HEADING_DATE_ADDED}</label>
                      <br />
                      <span class="transaction-date-create">{$paymentRecord['orders_payment_date_create']}</span>

                      {if $paymentRecord['orders_payment_admin_create']}
                        <br />
                        <label>{$smarty.const.TABLE_HEADING_PROCESSED_BY}</label>
                        <br />
                        <span class="transaction-create-admin">{$paymentRecord['orders_payment_admin_create']}</span>
                      {/if}

                      {if $paymentRecord['orders_payment_date_update']}
                        <br />
                        <br />
                        <label>{$smarty.const.TABLE_HEADING_UPDATE_DATE}</label>
                        <br />
                        <span class="transaction-date-update">{$paymentRecord['orders_payment_date_update']}</span>
                      {/if}

                      {if $paymentRecord['orders_payment_admin_update']}
                        <br />
                        <label>{$smarty.const.TABLE_HEADING_UPDATE_BY}</label>
                        <br />
                        <span class="transaction-update-admin">{$paymentRecord['orders_payment_admin_update']}</span>
                      {/if}
                    </td>
                    <td>{$paymentRecord['orders_payment_transaction_status']}</td>
                    <td>{$paymentRecord['orders_payment_transaction_commentary']}
                    </td>
                    {*<td>{$paymentRecord['orders_payment_transaction_date']}</td>
                    <td>{$paymentRecord['orders_payment_admin_create']}</td>
                    <td>{$paymentRecord['orders_payment_date_create']}</td>
                    <td>{$paymentRecord['orders_payment_admin_update']}</td>
                    <td>{$paymentRecord['orders_payment_date_update']}</td>*}
                    <td class="actions">
                        <a href="{Yii::$app->urlManager->createUrl(['orders/payment-edit', 'opyID' => $paymentRecord['orders_payment_id']])}" class="popup-opye" data-class="popupCredithistory">{$smarty.const.IMAGE_EDIT}</a><br>
                        {if $paymentRecord['transactional']}
                          <a href="javascript:void(0);" class="update-status-link" data-id="{$paymentRecord['orders_payment_id']}">{$smarty.const.IMAGE_UPDATE}</a><br>
                        {/if}
                        {foreach ['refund', 'capture', 'reauthorize'] as $taction}
                          {if $paymentRecord['can_'|cat:$taction]}
                            <a class="btn-transactional-action btn-action-{$taction}" data-action="{$taction}" data-id="{$paymentRecord['orders_payment_id']}" data-amount="{$paymentRecord['payment_amount']}" href="#" onclick="return transactionalClick(this);">{$smarty.const.{'IMAGE_'|cat:strtoupper($taction)} }</a><br>
                          {/if}
                        {/foreach}
                        {foreach ['delete', 'void'] as $taction}
                          {if $paymentRecord['can_'|cat:$taction]}
                            <a class="btn-confirm-action btn-action-{$taction}" data-action="{$taction}" data-id="{$paymentRecord['orders_payment_id']}" data-prompt="{$smarty.const.{'TEXT_PROMPT_'|cat:strtoupper($taction)}|escape}" href="#" onclick="return confirmClick(this);">{$smarty.const.{'IMAGE_'|cat:strtoupper($taction)} }</a><br>
                          {/if}
                        {/foreach}

                        {if $paymentRecord['orders_payment_is_refund'] > 0}
                        <a href="{Yii::$app->urlManager->createUrl(['orders/payment-refund', 'opyID' => $paymentRecord['orders_payment_id']])}" class="popup-opye" data-class="popupCredithistory">{$smarty.const.SAVE_REFUND_INFO}</a><br>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </tbody>
    </table>
</div>
{if !$listOnly }
<div class="mail-sending noti-btn">
    <div><span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span></div>
    <div>
    {if $onBehalfUrl}
    <div><a id="popup-pay-now" class="btn" href="#">{$smarty.const.TEXT_ORDER_PAY}</a></div>
    {/if}<a id="popup-opye-search" href="{Yii::$app->urlManager->createUrl(['orders/payment-edit', 'oID' => $oID, 'search'=>1])}" class="popup btn" data-class="popupCredithistory">{$smarty.const.IMAGE_SEARCH}</a>&nbsp;<a id="popup-opye-add" href="{Yii::$app->urlManager->createUrl(['orders/payment-edit', 'oID' => $oID])}" class="popup btn" data-class="popupCredithistory">{$smarty.const.IMAGE_ADD}</a></div>
</div>
<script>
    var table;
    var transPo = function(){
        return {
            transactions: [],
            payment_transactions: [],
            creditNote :{
                amount:0
            },
            /*
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
            },*/
            readyToReturn: function(){
              
              return true; //always reload all list
/*
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
 */
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
            transactionalAction: function(id, amount, action){
                var T = this;
                //$('.popupCategory .preloader').show();
                $('.pop-up-content').css('cursor', 'wait');
                $.post('{$url}', {
                    'amount': amount,
                    'action': 'make_'+action,
                    'op_id' : id
                }, function(data){
                  $('.pop-up-content').css('cursor', '');
                  if (data.status == 'OK') {
                    //$('.popupCategory .preloader').hide();
                    reloadOProcess();
                    reloadList();
                    //redrawTransactions(data);
                  } else if ( data.message ) {
                    alert(data.message);
                  }
                }, 'json');
            },
            confirmAction: function(id, action){
              var T = this;
              //$('.popupCategory .preloader').show();
              $('.pop-up-content').css('cursor', 'wait');
              $.post('{$url}', {
                'action': 'make_'+action,
                'op_id' : id
              }, function(data){
                $('.pop-up-content').css('cursor', '');
                if (data.status == 'OK') {
                  //$('.popupCategory .preloader').hide();
                  reloadOProcess();
                  reloadList();
                  //redrawTransactions(data);
                } else if ( data.message ) {
                  alert(data.message);
                }
              }, 'json');
            }
        }
    }

    var trans = new transPo();


    function initTable() {
      table = $('.order-payment-datatable').dataTable({
            'pageLength': 5,
            'order': [[0, 'desc']],
            'columnDefs': [{ 'visible': false, 'targets': 0 }]
        });
      var oSettings = table.fnSettings();
      oSettings._iDisplayStart = 0;
      $('.order-payment-datatable').on( 'draw.dt', function () {
        bindLinks();
      } );
      table.fnDraw();
      //bindLinks();

    }

    function bindLinks() {
      $('.update-status-link').not('.inited').on('click', function (e) {
        e.preventDefault();
        $.post(
          "{Yii::$app->urlManager->createUrl(['orders/payment-update-status', 'oID' => $oID, 'platform_id' => $platform_id])}",
          [ { name:'opyID', value: $(this).data('id') } ],
          function(data, status){
            if ( data.status == 'OK' ) {
              reloadList();
            } else if ( data.message ) {
              alert(data.message);
            }
          },
          'json'
        );

      });
      $('.update-status-link').addClass('inited');

      $('.popup-opye').popUp({ 'one_popup': false });
      $('#popup-opye-add, #popup-opye-search').popUp({ 'one_popup': false });
    }

      function transactionalClick( el) {
        var id = $(el).data('id'),
            action = $(el).data('action'),
            amount = $(el).data('amount');
        if (id && action) {
          bootbox.prompt({
              title : '{$smarty.const.TEXT_REFUND_PROMPT|escape:javascript}',
              value : amount,
              callback: function(result){
                  if (result !== null){
                      if (parseFloat(result) > amount) {
                          bootbox.alert('{$smarty.const.TEXT_REFUND_AMOUNT_ERROR|escape:javascript}');
                      } else {
                          trans.transactionalAction(id, result, action);
                      }
                  }

              }
          });
        } else {
            console.error('{$smarty.const.TEXT_REFUND_UNDEFINED_TRANSACTION|escape:javascript}');
        }
        return false;
      }

      function confirmClick( el) {
        var id = $(el).data('id'),
            prompt = $(el).data('prompt'),
            action = $(el).data('action');
        if (id && action) {

            bootbox.confirm(prompt, function(result){
                if (result){
                  trans.confirmAction(id, action);
                }
            });



        } else {
            console.error('{$smarty.const.TEXT_REFUND_UNDEFINED_TRANSACTION|escape:javascript}');
        }
        return false;
      }


    (function($) {

      initTable();



      $('#popup-pay-now').on('click', function (event) {
        event.preventDefault();
        $('.mail-sending.noti-btn').hide();

        var paymentPopup = $('.popupEditCat'); //popup-box

        $('.pop-up-close', paymentPopup).hide();
        var w = Math.max(300, Math.round(screen.width/2));
        var h = Math.max(300, Math.round(screen.height*0.65));

        paymentPopup.css("width", w +'px').css("height", h +'px');
        var d = ($(window).height() - $('.popup-box').height()) / 2;
        if (d < 0) d = 0;
        $('.popup-box-wrap').css('top', $(window).scrollTop() + d);
        $(".pop-up-content:last").html('<iframe src="{$onBehalfUrl}" frameborder="0" style="width:' + (w-15) +'px;height:' + (h-15) +'px"></iframe><div class="noti-btn"><div />{$smarty.const.TEXT_SCROLL_TO_CONFIRM}<div class="btn-right"><button class="btn btn-confirm" id="paymentCloseBtn">{$smarty.const.IMAGE_CLOSE}</button></div></div>');
        $("#paymentCloseBtn").on('click', function() {
          $.ajax({
              url: "{tep_catalog_href_link('account/logoff')}",
              complete: function(data, status, xhr) {
                  window.location.reload();
              }
          });

        });

        return false;
      });

    })(jQuery);

    function reloadList() {
        $.get(
          "{Yii::$app->urlManager->createUrl(['orders/payment-list', 'oID' => $oID, 'list_only' => 1])}",
          '',
          function(data, status){
            $( ".creditHistoryPopup" ).html( data );
            initTable();
          },
          'html'
        );
    }

    function reloadOProcess(){
      $.get('{$orderprocessUrl}', '', function(data){ $('#order_management_data').html(data.content); },'json');
    }

</script>
{/if}