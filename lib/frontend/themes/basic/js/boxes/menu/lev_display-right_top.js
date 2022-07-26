tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const state = tl.store.getState();
        const $ulLevels = [0,
            $('.level-1', $menu),
            $('.level-2', $menu),
            $('.level-3', $menu),
            $('.level-4', $menu),
            $('.level-5', $menu),
            $('.level-6', $menu),
        ];

        for (let level = 2; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_display'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $ulLevels[level].parent();

            $ulLevels[level].css({'width': '', 'max-width': '', 'min-height': '', 'left': '', 'top': '', 'position': ''});
            $li.off('mouseenter', setStyles);

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_display'], state) &&
                state.widgets[widgetId].params['lev'+level+'_display'] == 'right_top'
            ) {
                $li.each(setStyles);
                $li.on('mouseenter', setStyles)
            }
        }

        function setStyles(){
            let $li = $(this)
            let $ul = $('> ul', this)
            let $parent = $li.parent()
            let left = $li.width();
            let top = - $li.position().top;
            let height = $parent.height()
                + parseInt($parent.css('padding-top'), 10)
                + parseInt($parent.css('padding-bottom'), 10);
            let maxWidth = $(window).width() - ($li.offset().left + $li.width()
                + parseInt($parent.css('padding-left'), 10)
                + parseInt($parent.css('padding-right'), 10));
            let width = $parent.width() - $li.width() - 30;

            if (width < 300){
                $ul.css({'max-width': maxWidth > 300 ? maxWidth : 300})
            } else {
                $ul.css({'width': width})
                $ul.css({'max-width': ''})
            }

            $ul.css({
                'min-height': height,
                'left': left,
                'top': top,
                'position': 'absolute'
            })
        }
    })
})