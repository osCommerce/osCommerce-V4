if (!ProductListing) var ProductListing = {};
ProductListing.applyItemImage = function($item, widgetId) {
    var productId = $item.data('id');
    var $box = $('.image', $item);
    var state = tl.store.getState();
    tl(createJsUrl('jquery.lazy.min.js'), function(){
        $('.image img', $item).lazy({
            bind: 'event',
            beforeLoad: function(){
                $('source', $item).each(function(){
                    let srcset = $(this).data('srcset');
                    $(this).attr('srcset', srcset).removeAttr('data-srcset')
                })
            }
        });
    });

    if (!isElementExist(['widgets', widgetId, 'listingName'], state) ||
        !isElementExist(['widgets', widgetId, 'products'], state)) {
        return;
    }
    const listingName = state.widgets[widgetId].listingName;
    if (!isElementExist(['productListings', listingName, 'itemElementSettings', 'image', 'add_images'], state)) {
        return;
    }

    const $holder = $('<div class="image-holder"></div>');

    const p1 = new Promise((resolve, reject) => {
        $.get('catalog/product-images', { id: productId }, function(responce){
            for (let imageKey in responce) {
                if (isElementExist([imageKey, 'image', 'Medium', 'url'], responce)) {
                    $holder.append(`<div class="item-image"><div><img src="${responce[imageKey].image.Medium.url}"></div></div>`)
                }
            }
            $box.html('').append($holder)
            resolve()
        }, 'json')
    });

    const p2 = new Promise((resolve, reject) => {
        tl(createJsUrl('slick.min.js'), function(){
            resolve()
        })
    })

    Promise.all([p1, p2]).then(values => {
        $holder.slick({dots: true})
    });
};