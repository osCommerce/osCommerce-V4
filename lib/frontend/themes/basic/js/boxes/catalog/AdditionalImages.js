tl([
    createJsUrl('slick.min.js'),
    createJsUrl('jquery.fancybox.pack.js'),
], function(){

    $('.w-catalog-additional-images').each(function(){

        var $widget = $(this);
        var widgetId = $widget.attr('id').substring(4);
        var settings = entryData.widgets[widgetId];

        if (settings.fancybox) {
            $('a', $widget).fancybox({
                nextEffect: 'fade',
                prevEffect: 'fade',
                padding: 10
            });
        } else {
            $('a', $widget).on('click', function(e){
                e.preventDefault()
            })
        }

        if (settings.carousel) {
            var responsive = [];

            for (var width in settings.colInRowCarousel) {
                responsive.push({
                    breakpoint: width,
                    settings: {
                        slidesToShow: +settings.colInRowCarousel[width],
                        slidesToScroll: +settings.colInRowCarousel[width]
                    }
                })
            }

            $('ul', $widget).slick({
                slidesToShow: +settings.col_in_row,
                slidesToScroll: +settings.col_in_row,
                responsive: responsive
            });
        }

    })
})

