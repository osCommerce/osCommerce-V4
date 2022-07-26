tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);

        for (let level = 1; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_vis'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $li = $('.level-' + level, $menu).parent('li.parent');
            const $a = $(' > .open-close-ico', $li);
            const $body = $('body');

            $a.off('click', clickItem);
            $li.removeClass('vis-show');
            $body.off('click', closeItem);

            const state = tl.store.getState();
            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_vis'], state) &&
                state.widgets[widgetId].params['lev'+level+'_vis'] == 'click_icon') {

                $a.on('click', clickItem);
                $body.on('click', closeItem )
            }

            function closeItem(e){
                const $parents = $(e.target).parents('.vis-show');
                if ($parents.length == 0) {
                    $('.vis-show').removeClass('vis-show');
                    return
                }
                const $parent = $parents.get($parents.length - 1);
                $('.vis-show').each(function(){
                    if (!$.contains( $parent, this ) && this != $parent) {
                        $(this).removeClass('vis-show')
                    }
                })
            }
        }

        function clickItem(e){
            e.preventDefault()

            $(this).parent().toggleClass('vis-show');

        }

    })
})