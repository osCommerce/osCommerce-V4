tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);

        for (let level = 1; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_goto'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $('.level-' + level + ' > li', $menu);

            $('.level-' + (+level + 1) + ' > .goto-button', $menu).remove();

            const state = tl.store.getState();
            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_goto'], state) &&
                    state.widgets[widgetId].params['lev'+level+'_goto']) {

                $('.level-' + level + ' > li', $menu).each(function(){
                    const $ul = $('> ul', this);
                    const $a = $('> a', this);
                    let text = $a.text();
                    const href = $a.attr('href');

                    if (entryData.tr.GOTO_BUTTON_TEXT) {
                        text = entryData.tr.GOTO_BUTTON_TEXT.replace('%s', text)
                    }
                    if (state.widgets[widgetId].params['lev'+level+'_goto'] == 'top' && text && href) {
                        $ul.prepend(`<li class="goto-button"><a href="${href}">${text}</a></li>`)
                    } else if (state.widgets[widgetId].params['lev'+level+'_goto'] == 'bottom' && text && href) {
                        $ul.append(`<li class="goto-button"><a href="${href}">${text}</a></li>`)
                    }
                })
            }
        }

    })
})