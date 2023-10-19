{use class="common\helpers\Html"}
<div class="atb">
{Html::beginForm($url, 'post', ['id'=> 'searchTransactions'])}

{Html::dropDownList('payment_class', '', $list, ['class' => 'form-select', 'id' => 'search_payment_class', 'prompt' => $smarty.const.TEXT_SELECT_PAYMENT_METHOD])}
<div class="alert fade in" style="display:none;">
  <i data-dismiss="alert" class="icon-remove close"></i>
  <span id="message_plce"></span>
</div>
<div class="required_fields"></div>
<br/>
{Html::button($smarty.const.IMAGE_SEARCH, ['id' => 'search-transactions', 'class' => 'btn btn-primary'])}

{Html::endForm()}
</div>
<br/>
<div class="transactions-search"></div>
<script>
    $('document').ready(function(){
        $('#search_payment_class').change(function(){
            if ($(this).val()){
                $('.atb .alert.fade').hide();
                var form = $('#searchTransactions').serializeArray();
                form.push({ 'name': 'action', 'value': 'get_fields' });
                $('.required_fields').addClass('preloader');
                $.post('{$url}', form, function(data){
                    if (data.hasOwnProperty('required')){
                        $('.required_fields').removeClass('preloader');
                        $('.required_fields').html(data.required);
                    }
                }, 'json');
            }
        })
        $('#search-transactions').click(function(){
            if ($('#search_payment_class').val()){
                var form = $('#searchTransactions').serializeArray();
                $('.popupCategory .preloader').show();
                form.push({ 'name': 'action', 'value': 'search_transactions' });
                $.post('{$url}', form, function(data){
                    $('.popupCategory .preloader').hide();
                    if (data.hasOwnProperty('transactions')){
                        $('.transactions-search').html(data.transactions);
                    }
                    if (data.hasOwnProperty('errors')){
                        var message = '';
                        $.each(data.errors, function(i, e){
                            message = message + '<br>' + e[0];
                        });
                        $('.atb #message_plce:last').html(message);
                        $('.atb .alert.fade').show();
                    }
                }, 'json');
            }
        })
    })
</script>
