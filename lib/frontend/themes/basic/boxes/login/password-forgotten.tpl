{use class = "yii\helpers\Html"}{use class="yii\captcha\Captcha"}
{use class="frontend\design\Block"}
{use class="frontend\design\Info"}
{Info::addBoxToCss('info')}
{Info::addBoxToCss('form')}

{Html::beginForm(['account/password-forgotten', 'action'=>'process'], 'post', ['name' => 'password_forgotten', 'id' => 'frmPasswordForgotten'])}
<div class="middle-form">
    <div class="messages"></div>
    <div class="col-full">
        <label for="email">{field_label const="ENTRY_EMAIL_ADDRESS" required_text=""}</label>
        <input type="email" name="email_address" value="" id="email">
    </div>
{if $loginModel->captha_enabled == 'recaptha'}
    {$loginModel->captcha_widget}
{/if}
{if $loginModel->captha_enabled == 'captha'}
    <div class="col-full">
        {Captcha::widget(['model' => $loginModel, 'attribute' => 'captcha'])}
    </div>
{/if}
    <div class="buttons">
        <div class="right-buttons"><button class="btn-1" type="submit">{$smarty.const.IMAGE_BUTTON_CONTINUE}</button></div>
        <div class="left-buttons"><a href="{$loginUrl}" class="btn btn-back">{$smarty.const.IMAGE_BUTTON_BACK}</a></div>
    </div>
</div>
{Html::endForm()}

<script>
    tl([
        '{Info::themeFile('/js/main.js')}'
    ], function(){
        var form = $('#box-{$id} form');
        $('input', form).validate();
        form.on('submit', function(e){
            e.preventDefault();
            if ($('.required-error', form).length === 0){
                $.post(form.attr('action'), form.serialize(), function(data){
                    if (data == 'ok') {
                        console.log(data == 'ok');
                        if (form.closest('.popup-box').length > 0) {
                            $('.pop-up-close').trigger('click')
                        } else {
                            window.location.href = "{$loginUrl}";
                        }
                    } else {
                        var messages = '';
                        $.each(data.messages, function(key, val){
                            messages += '<div class="message '+val['.type']+'">'+val.text+'</div>';
                        });
                        $('.messages', form).html(messages)
                    }
                }, 'json')
            }
            return false;
        });
    });
</script>
