tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const state = tl.store.getState();

        if (!isElementExist(['widgets', widgetId, 'settings'], state)) {
            return
        }

        tl.subscribe(['widgets', widgetId, 'params', 'limit_levels'], apply);
        apply();

        function apply(){
            $('.parent-limited-level', $menu).each(function(){
                $(this).addClass('parent').removeClass('parent-limited-level')
            });
            $('.open-close-ico-limited-level', $menu).each(function(){
                $(this).addClass('open-close-ico').removeClass('open-close-ico-limited-level')
            });
            $('.limited-level', $menu).removeClass('limited-level')

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'limit_levels'], state) &&
                state.widgets[widgetId].params.limit_levels
            ) {
                const limitLevels = state.widgets[widgetId].params.limit_levels;

                $('.level-' + limitLevels + ' .parent', $menu).each(function(){
                    $(this).addClass('parent-limited-level').removeClass('parent')
                });
                $('.level-' + limitLevels + ' .open-close-ico', $menu).each(function(){
                    $(this).addClass('open-close-ico-limited-level').removeClass('open-close-ico')
                });
                $('.level-' + limitLevels + ' ul', $menu).addClass('limited-level')
            }
        }
    })
})