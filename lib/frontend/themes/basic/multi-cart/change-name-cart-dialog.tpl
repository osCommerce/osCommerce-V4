{use class="yii\helpers\Html"}
{use class="frontend\design\Info"}
{Html::beginForm($link, 'post', ['id' => 'save-cart-form'])}
<div class="multicart-box">
    <div class="heading-3">
    {if $type == 'change'}
        {$smarty.const.TEXT_CHANGE_CART_NAME}
    {else}
        {$smarty.const.INSERT_NEW_CART_NAME}
    {/if}
    </div>
    <div class="messsage"></div>
    <div class="js-carts">
        {Html::textInput('cart_name', $current_name)}
    </div>
    <div class="buttons">{Html::submitButton(TEXT_SAVE, ['class' => 'submit btn-1 btn-save']) }</div>
</div>
{Html::endForm()}
<script>
    tl('{Info::themeFile('/js/main.js')}', function(){
        var body = $('body');

        body.on('submit', '#save-cart-form', function(e){
            e.preventDefault();

            $.post($(this).attr('action') , $( this ).serialize(), function (data) {
                if (data.hasOwnProperty('success')){
                    $('.multicart-box .messsage').html(data.message);
                    if(data.success){
                        setTimeout(function(){ location.reload(); }, 500);
                    }
                }

            },'json');

            return false;
        });
    })
</script>