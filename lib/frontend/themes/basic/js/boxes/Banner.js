window.tl([createJsUrl('jquery.lazy.min.js'), createJsUrl('slick.min.js')], function(){
    $('.w-banner:not(.applied)').each(function(){
        const $box = $(this);
        const boxId = $box.attr('id');
        if (!boxId) {
            return '';
        }
        const widgetId = boxId.substring(4);
        const state = window.tl.store.getState();
        $box.addClass('applied');

        if (isElementExist(['widgets', widgetId, 'lazyLoad'], state)) {
            $('picture', this).each(function(){
                const item = this;
                $('img', item).lazy({
                    bind: 'event',
                    beforeLoad: function(){
                        $('source', item).each(function(){
                            let srcset = $(this).data('srcset');
                            $(this).attr('srcset', srcset).removeAttr('data-srcset');
                        });
                    },
                    afterLoad: function(){
                        $('img', item).removeClass('na-banner');
                        $('source', item).each(function(){
                            $(this).removeClass('na-banner');
                        });
                    }
                });
            });
        }

        if (isElementExist(['widgets', widgetId, 'settings', 'banners_type'], state) &&
            state.widgets[widgetId].settings.banners_type == 'carousel') {

            const responsive = [];
            if (isElementExist(['widgets', widgetId, 'colInRowCarousel'], state)){
                for (let size in state.widgets[widgetId].colInRowCarousel) {
                    responsive.push({
                        breakpoint: size,
                        settings: {
                            slidesToShow: +state.widgets[widgetId].colInRowCarousel[size],
                            slidesToScroll: +state.widgets[widgetId].colInRowCarousel[size]
                        }
                    });
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
            };

            if (isElementExist(['widgets', widgetId, 'settings', 'dots'], state)){
                slick.dots = true;
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'centerMode'], state)){
                slick.centerMode = true;
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'adaptiveHeight'], state)){
                slick.adaptiveHeight = true;
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'autoplay'], state)){
                slick.autoplay = true;
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'autoplaySpeed'], state)){
                slick.autoplaySpeed = state.widgets[widgetId].settings.autoplaySpeed;
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'speed'], state)){
                slick.speed = state.widgets[widgetId].settings.speed;
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'fade'], state)){
                slick.fade = true;
            }
            if (isElementExist(['widgets', widgetId, 'settings', 'cssEase'], state)){
                slick.cssEase = state.widgets[widgetId].settings.cssEase;
            }

            $('.banner-holder', $box).slick(slick);
        }

        $('video', $box).each(function(){

            const $video = $(this);
            const $sources = [];
            let $main = $('');
            let $videoBox = $video.clone();
            const $bannerBox = $(this).parent();
            const videoId = $video.attr('id');

            $videoBox.html('');

            $('source', this).each(function(){
                if ($(this).hasClass('main')) {
                    $main = $(this).clone();
                } else {
                    $sources.push($(this).attr('src', $(this).attr('srcset')).clone());
                }
            });
            $video.remove();

            rebuildVideo();
            $(window).on('resize', rebuildVideoDelay);


            let delay = false;
            function rebuildVideoDelay(){
                if (!delay) {
                    delay = true;
                    setTimeout(function () {
                        rebuildVideo();
                        delay = false;
                    }, 1000);
                }
            }

            function rebuildVideo(){
                const width = window.innerWidth;
                $bannerBox.html('');
                $sources.forEach(function($source){
                    if ($source.data('min') < width && $source.data('max') > width) {
                        $bannerBox.html('');
                        let $video = $videoBox.clone();
                        if ($source.data('type') === 'image') {
                            $video = $(`<picture id="${videoId}"></picture>`);
                            $video.append(`<img src="${$source.attr('src')}" alt="">`);
                        }
                        $video.append($source.clone());
                        $bannerBox.append($video);
                    }
                });
                if (!$('video, picture', $bannerBox).length) {
                    let $video = $videoBox.clone();
                    if ($main.data('type') === 'image') {
                        $video = $(`<picture id="${videoId}"></picture>`);
                        $video.append(`<img src="${$main.attr('src')}" alt="">`);
                    }
                    $video.append($main.clone());
                    $bannerBox.append($video);
                }
            }
        });

    });
});
