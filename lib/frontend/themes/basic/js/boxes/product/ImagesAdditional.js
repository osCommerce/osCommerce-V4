tl([
    createJsUrl('slick.min.js')
], function(){
    var productId = $('input[name="products_id"]').val();

    $('.additional-images').each(function(){
        var $box = $(this).closest('.box');
        var widgetId = $box.attr('id').substring(4);
        var settings = entryData.widgets[widgetId];
        var $additionalImages = $('.additional-images', $box);

        var slick = { }
        if (!settings.alignPosition) {
            slick.vertical = true;
            slick.rows = 3;
        } else {
            slick.slidesToShow = 3
        }
        slick.infinite = false;

        applyAdditionalImages();
        tl.subscribe(['products', productId, 'images'], applyAdditionalImages);

        function applyAdditionalImages (){
            if ($additionalImages.hasClass('slick-initialized')) {
                $additionalImages.slick('unslick');
            }
            $additionalImages.html('');

            var state = tl.store.getState();
            var images = state.products[productId].images;
            var $item;

            for (var id in images) {
                $item = Product.mediaItem(images[id], 'Small', id);

                if (state.products[productId].defaultImage === id) {
                    $item.addClass('active')
                }

                $item.on('click', function() {
                    $('.active', $additionalImages).removeClass('active');
                    $(this).addClass('active');

                    tl.store.dispatch({
                        type: 'CHANGE_PRODUCT_IMAGE',
                        value: {
                            id: productId,
                            defaultImage: $(this).data('id'),
                        },
                        file: 'boxes/product/ImagesAdditional'
                    });
                });
                $additionalImages.append($item);
            }

            $additionalImages.slick(slick);
        }
    })
})