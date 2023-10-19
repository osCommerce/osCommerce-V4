{use class="Yii"}
{use class="frontend\design\Info"}
{if !$disapear_quantity_input}
    {$extQuota = \common\helpers\Extensions::isAllowed('Quotations')}
    <div class="qty-input"{if !$show_quantity_input && ($extQuota && !$extQuota::optionIsPriceShow()) || ($product_in_cart && $show_in_cart_button != 'no')} style="display: none"{/if}>
        <label for="qty">{output_label const="QTY"}</label>
        <div class="input">
            <input type="text" id="qty" name="qty" value="{if $qty != ''}{$qty}{else}1{/if}" class="qty-inp"{if $quantity_max>0} data-max="{$quantity_max}"{/if}
                {if $minQ = \common\helpers\Extensions::isAllowed('MinimumOrderQty')}{$minQ::setLimit($order_quantity_data)}{/if}
                {if $oqs = \common\helpers\Extensions::isAllowed('OrderQuantityStep')}{$oqs::setLimit($order_quantity_data)}{/if}
            />
        </div>
    </div>
    <script type="text/javascript">
        tl('{Info::themeFile('/js/main.js')}', function(){
            {\frontend\design\Info::addBoxToCss('quantity')}
            $('input.qty-inp').quantity();
        })
    </script>
{/if}
<script>
    tl(function(){
        $('input.qty-inp').not('inited2').on('check_quantity keyup', function(e, param) {
            var $qtyInp = $(this);
            setTimeout(() => $(window).trigger('changedQty', $qtyInp.val()), 0)
            $qtyInp.addClass('inited2');
        })
        $('input.qty-inp').on('qty_max', function(e, qty, max){
            //see  themes/basic/js/main.full.js (+) - bigger disabled (and should) when qty == max but message shouldn't be shown
            if (qty > max) {
                var contentText = '{$smarty.const.SELECTED_TOO_MANY_ITEMS|escape}';
                var buttonOk = '{$smarty.const.CANCEL|escape}';
                var buttonAdd = '{$smarty.const.ADD_S_TO_CART|escape}';
                var $addButton = $('.w-product-buttons button')

                var content = $(`
    <div class="qty-more-max">
        <div class="qty-more-max-content">${ contentText.replace('%s', max)}</div>
        <div class="buttons">
            <span class="btn btn-close">${ buttonOk}</span>
            ${ $addButton.length ? `<span class="btn btn-add">${ buttonAdd.replace('%s', max)}</span>` : ''}
        </div>
    </div>`);

                alertMessage(content)

                $('.btn-close', content).on('click', function(){
                    $(this).closest('.popup-box-wrap').find('.pop-up-close').trigger('click')
                })
                if ($addButton.length) {
                    $('.btn-add', content).on('click', function(){
                        $(this).closest('.popup-box-wrap').find('.pop-up-close').trigger('click')
                        $addButton.trigger('click')
                    })
                }
            }
        })
    })
</script>