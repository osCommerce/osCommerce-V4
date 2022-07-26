tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);

        for (let level = 2; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_display'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $('.level-' + (level - 1) + ' > li', $menu)

            $('> ul', $li).css({'width': '', 'height': '', 'left': '', 'position': ''});
            $li.off('mouseenter', setStyles);

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_display'], state) &&
                state.widgets[widgetId].params['lev'+level+'_display'] == 'width_cont'
            ) {
                $li.on('mouseenter', setStyles)
                $li.on('click', setStyles)
            }
        }

        function setStyles(){
            $('> ul', this).css({'width': '1px', 'height': '1px', 'left': '1px'});
            const $container = $('.main-width, .type-1 > .block');
            const left = - $(this).offset().left + $container.offset().left;
            const height = $(window).height() - ($(this).offset().top - $(window).scrollTop() + $(this).height());
            const width = $container.width()
            $('> ul', this).css({
                'width': width,
                'height': height,
                'left': left,
                'position': 'absolute'
            })
        }
    })
})