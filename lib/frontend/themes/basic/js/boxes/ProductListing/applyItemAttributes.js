if (!ProductListing) var ProductListing = {};
ProductListing.applyItemAttributes = function($item, widgetId) {
    var productId = $item.data('id');
    var $box = $('.attributes', $item);
    var $inputs = $('input[type="radio"], select', $box);
    var $qty = $('.qty-inp', $box);
    var attributes = {};
    var state = tl.store.getState();
    if (isElementExist(['widgets', widgetId, 'batchSelectedWidget'], state)) {
        $box.find('input[type="radio"], select').each(function(){
            $(this).attr('name', (this.name.indexOf('list')===0?this.name:('list'+this.name))/*.replace(/\[/,'['+parseInt(productId)+'][')*/);
        });
    }
    $inputs.serializeArray().forEach(function(element){
        attributes[element.name] = element.value
    });

    $qty.quantity();

    tl.store.dispatch({
        type: 'WIDGET_CLEAR_PRODUCT_MIX_ATTRIBUTE',
        value: {
            widgetId: widgetId,
            productId: productId,
        },
        file: 'boxes/ProductListing/applyItemAttributes',
    })

    $qty.on('change', function(){
        var attributeId = $(this).closest('.mix-attributes').data('id');
        var optionId = $(this).closest('.attribute-qty-block').data('id');
        var qty = $(this).val();
        tl.store.dispatch({
            type: 'WIDGET_CHANGE_PRODUCT_MIX_ATTRIBUTE',
            value: {
                widgetId: widgetId,
                productId: productId,
                attributeId: attributeId,
                optionId: optionId,
                qty: qty,
            },
            file: 'boxes/ProductListing/applyItemAttributes',
        })
    });

    $inputs.on('change', function(){
        $box.addClass('loader');
        var data = {}
        $inputs.serializeArray().forEach(function(element){
            attributes[element.name] = element.value
            data[element.name] = element.value
        })

        var state = tl.store.getState();
        data.products_id = productId;
        data.qty = state.widgets[widgetId]['products'][productId]['qty'];
        data.type = 'productListing';

        ProductListing.updateAttributes(widgetId, productId, data, $item);
    });

    tl.store.dispatch({
        type: 'WIDGET_CHANGE_PRODUCT_ATTRIBUTE',
        value: {
            widgetId: widgetId,
            productId: productId,
            attributes: attributes,
        },
        file: 'boxes/ProductListing/applyItemAttributes',
    })
}