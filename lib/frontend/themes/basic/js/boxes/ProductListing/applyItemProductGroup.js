if (!ProductListing) var ProductListing = {};
ProductListing.applyItemProductGroup = function($item, widgetId) {
//function reloadGroups($_listing, widgetId){
    var $rootBox = $('#box-'+widgetId);
    var $_listing = $rootBox.find('.product-listing');

    if ( $rootBox.data('group-init') ) return;

    $rootBox.on('click', '.js-list-prod', function(event){
        var $link = $(event.currentTarget);
        var prodUrl = $link.attr('href');
        var prodId = $link.data('productsId');
        var $itemBox = $link.parents('.item');
        var $productsListing = $link.parents('.products-listing');
        var listType = $productsListing.attr('data-listing-type');
        var listParam = $productsListing.attr('data-listing-param');
        var listCallback = $productsListing.attr('data-listing-callback');
        var boxId = $itemBox.parents('.box').attr('id') || '';
        var ajaxParam = {
            'products_id': prodId,
            'onlyFilter':'',
            'productListing': 1,
            'onlyProducts':1,
            'listType':listType,
            'listParam':listParam,
            'boxId': boxId
        };
        var listPreCallback = $productsListing.attr('data-listing-pre-callback');
        if ( listPreCallback && typeof window[listPreCallback] === 'function' ) {
            (window[listPreCallback])(event, ajaxParam);
        }
        $.get(window.productCellUrl?window.productCellUrl:window.location.href, ajaxParam, function(data){
            if ( listCallback && typeof window[listCallback] === 'function' ) {
                 if ( !(window[listCallback])(data) ) { return; }
            }
            var $newItem = $('<div>' + data.html + '</div>');
            var $items = $('.item', $newItem);
            tl.store.dispatch({
                type: 'ADD_PRODUCTS',
                value: {
                    products: data.entryData.products,
                },
                file: 'boxes/ProductListing'
            });
            $items.each(function(){
                ProductListing.applyItem($(this), widgetId);
            });
            if ( $itemBox.hasClass('slick-slide') ){
                $itemBox.attr('data-id',$items.attr('data-id'));
                $itemBox.data('id',$items.attr('data-id'));
                $itemBox.attr('data-name',$items.attr('data-name'));
                $itemBox.data('name',$items.attr('data-name'));
                $itemBox.children().replaceWith($items.children());
            }else{
                $itemBox.replaceWith($items);
            }
            //ProductListing.alignItems($rootBox.find($itemBox));
            ProductListing.alignItems($_listing);
            if($('.new_arrivals').length > 0){
                if($(window).width() > 800){
                    setTimeout(function(){
                        $('.new_arrivals .item:nth-child(2) .image').removeAttr('style');
                        var wrapHeight = $('.new_arrivals .item:nth-child(1)').innerHeight() + $('.new_arrivals .item:nth-child(3)').innerHeight();
                        var secondHeight = $('.new_arrivals .item:nth-child(2)').innerHeight();
                        var secondHeightImg = (wrapHeight - secondHeight + $('.new_arrivals .item:nth-child(2) .image').innerHeight());
                        $('.new_arrivals .item:nth-child(2) .image').css('min-height', secondHeightImg);
                    },1);
                }
            }
        }, 'json').fail(function(){
            window.location.href = prodUrl;
        });

        return false;
    });
    $rootBox.data('group-init', true);
}
