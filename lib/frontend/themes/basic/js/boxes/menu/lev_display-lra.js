tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);

        for (let level = 2; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_display'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $('.level-' + (level - 1) + ' > li.parent', $menu)

            $li.removeClass('lra-left');
            $li.off('mouseenter', setStyles);
            $li.off('click', setStyles);

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_display'], state) &&
                state.widgets[widgetId].params['lev'+level+'_display'] == 'lra'
            ) {
                $li.on('mouseenter', setStyles);
                $li.on('click', setStyles)
            }
        }

        function setStyles(){
            const $li = $(this);
            const leftWidth = $li.offset().left;
            const rightWidth = $(window).width() - $li.offset().left + $li.width();
            if (leftWidth > rightWidth/2) {
                $li.addClass('lra-left')
            }
        }
    })
})