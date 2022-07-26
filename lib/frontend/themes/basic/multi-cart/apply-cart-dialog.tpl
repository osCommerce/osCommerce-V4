{use class="yii\helpers\Html"}
{use class="frontend\design\Info"}
{Html::beginForm($link, 'post', ['id' => 'apply-cart-form'])}
{Html::hiddenInput('cart', $cart)}
<div>

    <div>
        <div>Options</div>
        <div>{Html::radio('apply-option', true, ['label' => 'Save cart?', 'value' => 'save'])}</div>
        <div>{Html::radio('apply-option', false, ['label' => 'Delete cart?', 'value' => 'delete'])}</div>
        <div>{Html::radio('apply-option', false, ['label' => 'Clear cart?',  'value' => 'clear'])}</div>
    </div>
    <div>
        {Html::checkbox('delete-saved', false, ['label' => 'Delete saved carts?'])}
    </div>
    <div>{Html::submitButton('Apply', ['class' => 'submit']) }</div>
</div>
{Html::endForm()}

<script>
    tl('{Info::themeFile('/js/main.js')}', function(){
        $('body').on('submit', '#apply-cart-form', function(e){
            e.preventDefault();
            $.post($(this).attr('action') , $( this ).serialize(), function (data) {
                if(data.success){
                    location.href = '{$reload_url}';
                }
            },'json');
            return false;
        });
    })
</script>