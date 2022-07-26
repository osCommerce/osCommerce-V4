tl(createJsUrl('main.js'), function(){

    var $productReviews = $('.product-reviews');
    var $productReviewsFirst = $('.product-reviews:first');


    if (window.location.hash === '#reviews'){
        var tabId = $productReviews.closest('.block[id]').attr('id');
        setTimeout(function(){
            $('a.tab-a[data-href="#' + tabId + '"]').trigger('click');
            var top = Math.ceil($productReviewsFirst.offset().top - 100);
            window.scrollTo(0, top)
        }, 100)
    }

    $.get(entryData.reviewsLink, function(d){
        $productReviews.html(d)
    });
    $productReviews.on('click', 'a:not(.no-ajax)', function(){
        if ($(this).parents('.password-forgotten-link').length>0) return true;
        $.get($(this).attr('href'), function(d){
            $productReviews.html(d)
        });
        return false
    })

})