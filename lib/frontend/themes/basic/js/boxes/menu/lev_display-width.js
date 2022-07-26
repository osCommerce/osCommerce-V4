tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const $liLevels = [0, 0,
            $('.menu-content > ul > li', $menu),
            $('.menu-content > ul > li > ul > li', $menu),
            $('.menu-content > ul > li > ul > li > ul > li', $menu),
            $('.menu-content > ul > li > ul > li > ul > li > ul > li', $menu),
            $('.menu-content > ul > li > ul > li > ul > li > ul > li > ul > li', $menu),
        ];

        for (let level = 2; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_display'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $liLevels[level];

            $('> ul', $li).css({'width': '', 'max-height': '', 'left': '', 'position': ''});
            $li.off('mouseenter', setStyles);

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_display'], state) &&
                state.widgets[widgetId].params['lev'+level+'_display'] == 'width'
            ) {
                $li.on('mouseenter', setStyles)
            }
        }

        function setStyles(){
            $('> ul', this).css({'width': '1px', 'height': '1px', 'left': '1px'});
            let left = - $(this).offset().left;
            let height = $(window).height() - ($(this).offset().top - $(window).scrollTop() + $(this).height());
            $('> ul', this).css({
                'width': $(window).width(),
                'height': height,
                'left': left,
                'position': 'absolute'
            })
        }
    })
})