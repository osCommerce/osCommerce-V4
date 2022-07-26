<style type="text/css">
    .widget-quantity-discount .quantity-discount-row {
        display: flex;
        align-items: flex-end;
        gap: 5px;
        margin-bottom: 5px;
    }
    .widget-quantity-discount .qty-qty {
        flex-grow: 2;
    }
    .widget-quantity-discount .qty-price {
        flex-grow: 4;
    }
    .widget-quantity-discount .qty-price-gross {
        flex-grow: 4;
    }
    .widget-quantity-discount .btn-remove {
        flex-grow: 1;
    }
    .widget-quantity-discount .btn-remove:before {
        content: '\f1f8';
        font-family: FontAwesome;
        font-weight: 400;
        color: #f52f1b;
        cursor: pointer;
        font-size: 19px;
    }
    .widget-quantity-discount .discount-button-add {
        text-align: right;
    }
    .widget-quantity-discount .btn-add {
        padding: 3px 15px;
    }
    .widget-quantity-discount .btn-add:before {
        font-size: 14px
    }
    .widget-quantity-discount label {
        margin: 0;
    }
    .widget-quantity-discount .switcher {
        margin-bottom: 10px;
    }
    .widget-quantity-discount .switcher label {
        margin: 0 20px 0 0;
        display: inline-block;
    }
</style>
<div class="widget-quantity-discount" data-name="{$name}">

    <div class="switcher">
        <label>{$smarty.const.TEXT_QUANTITY_DISCOUNT}</label>
        <input type="checkbox" value="1" name="{$name}[status]" class="check_qty_discount_prod" />
    </div>

    <div class="quantity-discount-cont" style="display:none;">

        <div class="wrap-discount"></div>
        <div class="discount-button-add">
            <span class="btn btn-add">{$smarty.const.TEXT_AND_MORE}</span>
        </div>

    </div>
</div>
<script type="text/javascript">
    $(function(){
        const $box = $('.widget-quantity-discount[data-name="{$name}"]');
        const values = JSON.parse('{$value}');
        const $wrapDiscount = $('.wrap-discount', $box);
        const $switcher = $('.check_qty_discount_prod', $box);
        const $cont = $('.quantity-discount-cont', $box);
        const idSuffix = '{$idSuffix}';
        let count = 0;

        if (values.length > 0) {
            $switcher.prop('checked', true);
            $cont.show()
        }

        $wrapDiscount.append(values.map(item => row(item)))

        $('.btn-add', $box).on('click', () => $wrapDiscount.append(row()))


        $switcher.bootstrapSwitch({
            onSwitchChange: function (element, argument) {
                if (argument) {
                    $cont.show();
                    if (!$('.quantity-discount-row', $wrapDiscount).length) {
                        $wrapDiscount.append(row())
                    }
                } else {
                    $cont.hide();
                }
                return true;
            },
            onText: "{$smarty.const.SW_ON}",
            offText: "{$smarty.const.SW_OFF}",
        });


        function row(values = { }){
            count++;
            const $row = $(`
<div class="quantity-discount-row">
    <div class="qty-qty">
        <label>{$smarty.const.TABLE_HEADING_QUANTITY}</label>
        <input name="{$name}[qty][]" value="${ values.qty || ''}" id="${ idSuffix + count}_qty" class="form-control">
    </div>
    <div class="qty-price">
        <label>{$smarty.const.TEXT_PRICE}</label>
        <input name="{$name}[price][]" value="${ values.price || ''}" id="${ idSuffix + count}_price" class="form-control inp-price">
    </div>
    {if $gross}
    <div class="qty-price-gross">
        <label>{$smarty.const.TEXT_GROSS}</label>
        <input name="{$name}[price_gross][]" value="${ values.price_gross || ''}"
               id="${ idSuffix + count}_price_gross" class="form-control inp-price-gross">
    </div>
    {/if}
    <span class="btn-remove"></span>
</div>
`);
            $('.btn-remove', $row).on('click', () => $row.remove())
            $('.inp-price', $row).on('keyup', function(){ updateGrossPrice(this) })
            $('.inp-price-gross', $row).on('keyup', function(){ updateNetPrice(this) })
            setTimeout(function(){
                if (values.price && !values.price_gross) {
                    updateGrossPrice($('.inp-price', $row).get(0))
                }
                if (!values.price && values.price_gross) {
                    updateNetPrice($('.inp-price-gross', $row).get(0))
                }
            }, 0)
            return $row
        }

    })
</script>