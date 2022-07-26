tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const state = tl.store.getState();
        const $icon = $('.burger-icon', $menu)

        if (!isElementExist(['widgets', widgetId, 'settings'], state)) {
            return
        }

        tl.subscribe(['widgets', widgetId, 'params', 'burger_icon'], apply);
        apply();

        function apply(){
            $icon.off('click');
            $menu.removeClass('bi-opened');

            const state = tl.store.getState();

            if (isElementExist(['widgets', widgetId, 'params', 'burger_icon'], state) &&
                state.widgets[widgetId].params.burger_icon
            ) {
                $icon.on('click', function(){
                    $menu.toggleClass('bi-opened')
                });
            }
        }
    })
})