{use class="\yii\helpers\Html"}
<div class="gift-certificate">
    {Html::beginForm('account/apply-certificate', 'post', ['name' => 'apply-cert'])}
    <div class="alert fade in" style="display:none;">
        <i data-dismiss="alert" class="icon-remove close"></i>
        <span id="message-place"></span>
    </div>

    <div class="gift-certificate-form">
        <div class="gift-certificate-input">
            {Html::activeInput('text', $model, 'gv_redeem_code', ['class' => 'form-control input-gv_redeem_code', 'data-required' => $smarty.const.ENTRY_GIFT_CERTIFICATE])}
        </div>
        <div class="gift-certificate-button">
            {Html::submitButton(TEXT_APPLY, ['class' => 'btn'])}
        </div>
    </div>
    {Html::endForm()}
</div>
<script>
    tl(function(){
        $('form[name=apply-cert]').submit(function(){
            var form = this;
            var alertFade = $('.alert.fade');
            $.post($(form).attr('action'), $(form).serialize(), function(data, status){
                if (data.hasOwnProperty('message')){
                    $('#message-place').html(data.message);
                    alertFade.removeClass('alert-success').removeClass('alert-danger')
                    if (data.error){
                        alertFade.addClass('alert-danger');
                    } else {
                        alertFade.addClass('alert-success');
                        setTimeout(function(){ location.reload() }, 2000);
                    }
                    alertFade.show();
                }
            }, 'json');

            return false;
        })
    })
</script>