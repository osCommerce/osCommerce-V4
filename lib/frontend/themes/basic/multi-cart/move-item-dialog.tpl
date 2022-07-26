{use class="yii\helpers\Html"}
{use class="frontend\design\Info"}
{Html::beginForm($link, 'post', ['id' => 'move-item-form'])}
{Html::hiddenInput('action', $action)}
{Html::hiddenInput('scart', $scart)}
{Html::hiddenInput('pId', $pId)}
{Html::hiddenInput('qty', $qty)}
<div>

    <div class="js-carts">
        <div>Save to cart</div>
        {assign var = 'count' value = 1}
        {foreach $carts as $key => $cart}

                <div>{Html::radio('carts', true, ['label' => 'Cart '|cat:$count++|cat:'&nbsp;-&nbsp;'|cat:$currencies->format( $cart->show_total() ), 'value' => $key, 'disabled' => ($key == $scart)])}</div>

        {/foreach}
        <div>{Html::radio('carts', true, ['label' => 'Current cart', 'value' => 'current', 'disabled' => ('current' == $scart)])}</div>
        <div>Options</div>
        <div>{Html::radio('merge-option', true, ['label' => 'Replace same products', 'value' => 'replace'])}</div>
        <div>{Html::radio('merge-option', false, ['label' => 'Append same products',  'value' => 'append'])}</div>
    </div>

    <div>{Html::submitButton('Save', ['class' => 'submit']) }</div>
</div>
{Html::endForm()}

<script>
    tl('{Info::themeFile('/js/main.js')}', function(){

        $('body').on('submit', '#move-item-form', function(e){
            e.preventDefault();
            $.post($(this).attr('action') , $( this ).serialize(), function (data) {
                if(data.success){
                    location.reload();
                }
            },'json');
            return false;
        });
    })
</script>