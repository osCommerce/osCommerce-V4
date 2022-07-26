{use class="\yii\helpers\Html"}
{use class="frontend\design\Info"}

<div class="holder">
    <div class="promo-text">
        <div class="heading-4">{$smarty.const.PROMOCODE_HEADING}</div>
        <div class="text">{$smarty.const.PROMOCODE_TEXT}</div>
    </div>
    <div class="promo-code-form">
        {Html::beginForm('', 'post', ['id' => 'promo-code-form'])}
            {Html::activeInput('text', $model, 'promo_code', ['data-required' => $smarty.const.PROMO_CODE_REQUIRED, 'autocomplete' => 'off', 'placeholder' => $smarty.const.HAVE_PROMO_CODE])}
            {Html::activeInput('hidden', $model, 'promo_action', ['value' => 'apply-promo-code'])}
            {Html::submitButton('Apply', ['class' => 'btn'])}
        {Html::endForm()}
        <div class="promo-text-2">
            {$smarty.const.ID_LIKE_PROMOCODE}
        </div>
    </div>
</div>
<script>
    tl('{Info::themeFile('/js/main.js')}', function(){
        var box = $('#box-{$id}');

        $('input', box).validate();

        $('form', box).submit(function(){
            var form = this;
            $.post($(form).attr('action'), $(form).serialize(), function(data){
                if (data.hasOwnProperty('message')){
                    alertMessage('<h4>'+data.title+'</h4><div>'+data.message+'</div>');
                }
            }, 'json');
            return false;
        });
    });
</script>