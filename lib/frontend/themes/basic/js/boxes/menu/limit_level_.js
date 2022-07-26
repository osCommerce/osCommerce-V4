tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const $body = $('body');

        for (let level = 1; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'limit_level_'+level], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $ul = $('.level-' + level, $menu);

            $('> li.limited-item', $ul).removeClass('limited-item');
            $('> li.show-more-button', $ul).remove();
            $('> li.show-less-button', $ul).remove();

            const state = tl.store.getState();
            if (isElementExist(['widgets', widgetId, 'params', 'limit_level_'+level], state) &&
                state.widgets[widgetId].params['limit_level_'+level]) {

                const limitItems = +state.widgets[widgetId].params['limit_level_'+level];

                $ul.each(function () {
                    $('> li', this).each(function(i){
                        if (i+1 > limitItems) {
                            $(this).addClass('limited-item')
                        }
                    })
                })

                if (isElementExist(['widgets', widgetId, 'params', 'show_more_button'], state)) {
                    const $showMore = $('<li class="show-more-button">' + entryData.tr.TEXT_SHOW_MORE + '</li>');
                    const $showLess = $('<li class="show-less-button" style="display: none">' + entryData.tr.TEXT_SHOW_LESS + '</li>');
                    const $limitedItems = $('> li.limited-item', $ul);

                    if ($('> li', $ul).length > +limitItems){
                        $ul.append($showMore);
                        $ul.append($showLess);
                    }

                    $showMore.on('click', function () {
                        $limitedItems.addClass('limited-item-more').removeClass('limited-item');
                        $showMore.hide();
                        $showLess.show();
                    })
                    $showLess.on('click', function () {
                        $limitedItems.addClass('limited-item').removeClass('limited-item-more');
                        $showMore.show();
                        $showLess.hide();
                    })
                }
            }
        }
    })
})