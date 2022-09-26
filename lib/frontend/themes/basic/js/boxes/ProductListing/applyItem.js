if (!ProductListing) var ProductListing = {};
ProductListing.applyItem = function($item, widgetId) {
    var productId = $item.data('id');
    var state = tl.store.getState();

    ProductListing.applyItemData($item, widgetId);

    ProductListing.applyItemImage($item, widgetId);
    ProductListing.applyItemPrice($item, widgetId);
    ProductListing.applyItemStock($item, widgetId);
    ProductListing.applyItemQtyInput($item, widgetId);
    ProductListing.applyItemBuyButton($item, widgetId);
    ProductListing.applyItemAttributes($item, widgetId);
    ProductListing.applyItemCompare($item, widgetId);
    if (ProductListing.applyItemPersonalCatalog) {
        ProductListing.applyItemPersonalCatalog($item, widgetId);
    }
    ProductListing.applyItemBatchSelect($item, widgetId);
    ProductListing.applyItemBatchRemove($item, widgetId);
    ProductListing.applyItemProductGroup($item, widgetId);

    if (isElementExist(['products', productId, 'show_attributes_quantity'], state)) {
        ProductListing.updateAttributes(widgetId, productId, {
            products_id: productId,
            type: 'productListing'
        }, $item);
    }

    if (isElementExist(['extensions', 'productListing', 'applyItem'], tl)){
        for(var extension in tl.extensions.productListing.applyItem) {
            tl.extensions.productListing.applyItem[extension]($item, widgetId)
        }
    }

    if (window.pCarousel && window.pCarousel.addItem) {
        var product = state['products'][productId];
        var productImage = '<img\
                  src="' + product.image + '"\
                  alt="' + product.image_alt + '"\
                  title="' + product.image_title + '"'
            + (product.srcset ? 'srcset="' + product.srcset + '"' : '')
            + (product.sizes ? 'srcset="' + product.sizes + '"' : '') + '\>';

        var productPrice = '<div class="price">'
            + (product.price.special ? '<span class="old">' + product.price.old + '</span>' : '') +
            +(product.price.special ? '<span class="specials">' + product.price.special + '</span>' : '') +
            +(!product.price.special ? '<span class="current">' + product.price.current + '</span>' : '') +
            '</div>'

        window.pCarousel.addItem(productId, product.link, product.products_name, productImage, productPrice);
    }
}