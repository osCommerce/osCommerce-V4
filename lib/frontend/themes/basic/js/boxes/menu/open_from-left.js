tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const state = tl.store.getState();
        const $content = $('.menu-content', $menu);

        let startX, startY;
        let breakTouch = false;
        let startMove = false;
        let menuWidth;

        if (!isElementExist(['widgets', widgetId, 'settings'], state)) {
            return
        }

        tl.subscribe(['widgets', widgetId, 'params', 'open_from'], apply);
        tl.subscribe(['widgets', widgetId, 'params', 'ofl_width'], apply);
        apply();

        function apply(){
            $('.close-menu', $menu).remove();
            $(window).off('touchstart', touchstart);
            $(window).off('touchmove', touchmove);
            $(window).off('touchend', touchend);
            breakTouch = false;
            startMove = false;
            $content.css('transition', '');
            $content.css('left', '');
            $content.css('width', '');


            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'open_from'], state) &&
                state.widgets[widgetId].params.open_from == 'left'
            ) {
                const $close = $('<div class="close-menu"><span>' + entryData.tr.TEXT_CLOSE + '</span></div>')
                $content.prepend($close);
                $close.on('click', function(){
                    $menu.removeClass('bi-opened')
                });

                menuWidth = state.widgets[widgetId].params.ofl_width || $(window).width();
                $content.css('width', menuWidth)
                if ((''+menuWidth).match(/[a-zA-Z\%]/)) {
                    menuWidth = $content.width()
                }

                $(window).on('touchstart', touchstart);
                $(window).on('touchmove', touchmove);
                $(window).on('touchend', touchend);
            }

        }

        function touchstart(e){
            if (!$menu.hasClass('bi-opened')) return;
            breakTouch = false;
            startMove = false;
            startX = e.changedTouches[0].pageX;
            startY = e.changedTouches[0].pageY
        }

        function touchmove(e){
            if (!$menu.hasClass('bi-opened')) return;
            let pageX = e.changedTouches[0].pageX;
            let pageY = e.changedTouches[0].pageY;

            if (breakTouch) {
                return
            }
            if (!startMove && (startY - pageY) > 10) {
                breakTouch = true;
                return
            }
            if (!startMove && (startX - pageX) > 10 && (startY - pageY) < 5) {
                startMove = true
            }
            if (!startMove) {
                return;
            }

            $content.css('transition', 'auto');
            let menuLeft = pageX - startX + 10;
            if (menuLeft < -menuWidth) menuLeft = -menuWidth;
            if (menuLeft > 0) menuLeft = 0;

            $content.css('left', menuLeft)
        }

        function touchend(e){
            if (!$menu.hasClass('bi-opened')) return;
            $content.css('transition', '');
            let pageX = e.changedTouches[0].pageX;
            let pageY = e.changedTouches[0].pageY;

            if (startMove && (startX - pageX) > menuWidth / 2) {
                $menu.removeClass('bi-opened')
            }

            breakTouch = false;
            startMove = false;
            $content.css('left', '');
        }
    })
})