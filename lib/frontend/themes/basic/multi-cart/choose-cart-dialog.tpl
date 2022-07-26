{use class="yii\helpers\Html"}
{use class="frontend\design\Info"}
{use class="\common\extensions\MultiCart\MultiCart"}
{Html::beginForm($link, 'post', ['id' => 'save-cart-form'])}
<div class="multicart-box">
    <div class="heading-3">
        {$text_action}
    </div>
    <div class="messsage"></div>
    <div class="">
    {if is_array($carts) && count($carts) < MultiCart::DEFAULT_CARTS_AMOUNT}
        {assign var='create' value = true}
        {if $carts|count > 0}{$create = false}{/if}
        <div class="cart-item">
            <label class="radio-button">
                <input type="radio" name="dcart" value="create_new"{if $create} checked{/if}>
                <span><input type="text" name="create_new" placeholder="{$smarty.const.TEXT_CREATE_NEW_CART}"/></span>
            </label>
        </div>
    {/if}


    {foreach $carts as $key => $cart}
        <div class="cart-item">
            <label class="radio-button">
                <input type="radio" name="dcart" value="{$key}"{if $first == $key} checked{/if}>
                <span>{$cart['name']} ({$currencies->format( $cart['data']->show_total() )})</span>
            </label>
        </div>
    {/foreach}
    </div>

    <div class="options">
        <div class="heading-4">{$smarty.const.ADDITIONAL_OPTIONS}</div>
        <div class="">
            <div class="option-item">
                <label class="radio-button">
                    <input type="radio" name="merge_option" value="replace" checked>
                    <span>{$smarty.const.TEXT_REPLACE_SAME_PRODUCTS}</span>
                </label>
            </div>
            <div class="option-item">
                <label class="radio-button">
                    <input type="radio" name="merge_option" value="append">
                    <span>{$smarty.const.TEXT_APPEND_SAME_PRODUCTS}</span>
                </label>
            </div>
        </div>
    </div>



    <div class="buttons">
        {Html::hiddenInput('cart_action', $action)}
        {Html::hiddenInput('products_id', $products_id)}
        {Html::hiddenInput('from_uid', $from_uid)}
        {Html::submitButton(TEXT_SAVE, ['class' => 'submit btn-1 btn-primary']) }
    </div>
</div>
{Html::endForm()}
<script>
    tl('{Info::themeFile('/js/main.js')}', function(){

        var options = $('.options')

        var hideShowOptions = function(){
            if ($('input[name="dcart"]:checked').val() === 'create_new') {
                options.hide()
            } else {
                options.show()
            }
        };

        hideShowOptions();
        $('input[name="dcart"]').on('change', hideShowOptions);

        $('input[name="create_new"]').on('focus', function(){
            $('input[value="create_new"]').trigger('click')
        });

        $('#save-cart-form').on('submit', function(e){
            e.preventDefault();
            $.post($(this).attr('action') , $( this ).serialize(), function (data) {
                if(data.success){
                    location.reload();
                } else {
                    $('.multicart-box .messsage').html(data.message);
                }
            },'json');
            return false;
        });
    })
</script>