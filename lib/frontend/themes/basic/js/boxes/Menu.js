tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const $menuStyle = $('.menu-style', $menu);
        const widgetId = $menu.attr('id').substring(4);
        const state = tl.store.getState();

        if (!isElementExist(['widgets', widgetId, 'settings'], state)) {
            return
        }

        const settings = state.widgets[widgetId].settings

        tl.store.dispatch({
            type: 'WIDGET_CHANGE_SETTINGS',
            value: {
                widgetId: widgetId,
                settingName: 'params',
                settingValue: settings[0],
            },
            file: 'boxes/Menu 1'
        });

        layoutChange('', document.body.classList.value.split(' ').filter(cl => cl.match(/^[0-9w]+$/)))
        $(window).on('layoutChange', layoutChange)


        function layoutChange(e, d){
            const current = d.current || d;
            for (let setting in settings.visibility) {
                const state = tl.store.getState();
                const params = state.widgets[widgetId].params ? JSON.parse(JSON.stringify(state.widgets[widgetId].params)) : {};
                let value = settings[0] ? settings[0][setting] : false;

                for (let limits in settings.visibility[setting]) {
                    if (current.includes(limits) && settings.visibility[setting][limits]) {
                        value = settings.visibility[setting][limits];
                    }
                }

                if (value != params[setting]) {
                    params[setting] = value ?? false;

                    if (params[setting]) {
                        $menuStyle.attr('data-' + setting, params[setting])
                    } else {
                        $menuStyle.removeAttr('data-' + setting)
                    }
                    tl.store.dispatch({
                        type: 'WIDGET_CHANGE_SETTINGS',
                        value: {
                            widgetId: widgetId,
                            settingName: 'params',
                            settingValue: params,
                        },
                        file: 'boxes/Menu 2'
                    });
                }

            }
        }
    })
})