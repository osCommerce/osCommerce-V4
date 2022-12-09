tl([createJsUrl('jquery.lazy.min.js'), createJsUrl('slick.min.js')], function(){
    $('.w-banner').each(function(){
        const $box = $(this);
        const boxId = $box.attr('id');
        if (!boxId) {
            return ''
        }
        const widgetId = boxId.substring(4);
        const state = tl.store.getState();

        if (isElementExist(['widgets', widgetId, 'lazyLoad'], state)) {
            $('picture', this).each(function(){
                var item = this;
                $('img', item).lazy({
                    bind: 'event',
                    beforeLoad: function(){
                        $('source', item).each(function(){
                            let srcset = $(this).data('srcset');
                            $(this).attr('srcset', srcset).removeAttr('data-srcset')
                        })
                    },
                    afterLoad: function(){
                        $('img', item).removeClass('na-banner');
                        $('source', item).each(function(){
                            $(this).removeClass('na-banner')
                        })
                    }
                })
            })
        }

        if (isElementExist(['widgets', widgetId, 'settings', 'banners_type'], state) &&
            state.widgets[widgetId].settings.banners_type == 'carousel') {

            var responsive = [];
            if (isElementExist(['widgets', widgetId, 'colInRowCarousel'], state)){
                for (var size in state['widgets'][widgetId]['colInRowCarousel']) {
                    responsive.push({
                        breakpoint: size,
                        settings: {
                            slidesToShow: +state['widgets'][widgetId]['colInRowCarousel'][size],
                            slidesToScroll: +state['widgets'][widgetId]['colInRowCarousel'][size]
                        }
                    })
                }
            }

            let colInRow = 1;
            if (isElementExist(['widgets', widgetId, 'settings', 'col_in_row'], state)){
                colInRow = +state.widgets[widgetId].settings.col_in_row;
            }

            const slick = {
                slidesToShow: colInRow,
                slidesToScroll: colInRow,
                infinite: true,
                responsive: responsive,
            }

            if (isElementExist(['widgets', widgetId, 'settings', 'dots'], state)){
                slick.dots = true
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'centerMode'], state)){
                slick.centerMode = true
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'adaptiveHeight'], state)){
                slick.adaptiveHeight = true
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'autoplay'], state)){
                slick.autoplay = true
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'autoplaySpeed'], state)){
                slick.autoplaySpeed = state.widgets[widgetId].settings.autoplaySpeed
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'speed'], state)){
                slick.speed = state.widgets[widgetId].settings.speed
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'fade'], state)){
                slick.fade = true
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'cssEase'], state)){
                slick.cssEase = state.widgets[widgetId].settings.cssEase
            }

            $('.banner-holder', $box).slick(slick);
        }
    })
});
