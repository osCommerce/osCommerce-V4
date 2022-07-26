{use class="\yii\helpers\Html"}
<div class="widget box gift-certificate">
 {Html::beginForm('account/apply-certificate', 'post', ['name' => 'apply-cert'])}
  <div class="middle-form">
    <div class="heading-4">{$smarty.const.GIFT_CERTIFICATE}</div>
      <div class="widget-content account_block">
        <p>{$smarty.const.ENTRY_GIFT_CERTIFICATE}</p>
        
      <div class="alert fade in" style="display:none;">
          <i data-dismiss="alert" class="icon-remove close"></i>
          <span id="message_plce"></span>
      </div>     
        <div>
            <div class="col-md-6" style="display:inline-block;">
                {Html::activeInput('text', $model, 'gv_redeem_code', ['class' => 'form-control input-gv_redeem_code', 'data-required' => $smarty.const.ENTRY_GIFT_CERTIFICATE])}
            </div>
            <div class="col-md-6"  style="display:inline-block;">
                {Html::submitButton(TEXT_APPLY, ['class' => 'btn'])}
            </div>
        </div>
       </div>
    </div>
  {Html::endForm()}
</div>
<script>
    tl('', function(){    
        $('form[name=apply-cert]').submit(function(){
            var form = this;
            $.post($(form).attr('action'), $(form).serialize(), function(data, status){
                if (data.hasOwnProperty('message')){
                    $('#message_plce').html(data.message);
                    $('.alert.fade').removeClass('alert-success').removeClass('alert-danger')
                    if (data.error){
                        $('.alert.fade').addClass('alert-danger');
                    } else {
                        $('.alert.fade').addClass('alert-success');
                        if (data.hasOwnProperty('new_amount')){
                            $('.credit_amount_ac .date_value').html(data.new_amount);
                        }
                        setTimeout(function(){ $('.pop-up-close').trigger('click'); }, 2000);
                    }
                    $('.alert.fade').show();
                }
            }, 'json');
            
            return false;
        })
    })
</script>