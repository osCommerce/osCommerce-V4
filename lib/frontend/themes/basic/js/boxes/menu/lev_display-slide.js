tl(createJsUrl('main.js'), function(){
    $('.w-menu').each(function(){
        const $menu = $(this);
        const widgetId = $menu.attr('id').substring(4);
        const $body = $('body');

        for (let level = 2; level < 7; level++) {
            tl.subscribe(['widgets', widgetId, 'params', 'lev'+level+'_display'], (() => apply(level)));
            apply(level);
        }

        function apply(level){
            const $ul = $('.level-' + level, $menu);

            $('.level-' + level + ' > .back-button', $menu).remove();
            $('.level-' + (level - 1) + ' .slide-parent', $menu).removeClass('slide-parent');
            $ul.each(function(){
                const $li = $(this).closest('li');
                $('> a', $li).off('click', parentClass)
                $li.closest('.slide-parent').removeClass('slide-parent')
            });
            $body.off('click', closeItem);

            const state = tl.store.getState();
            if (isElementExist(['widgets', widgetId, 'params', 'lev'+level+'_display'], state) &&
                state.widgets[widgetId].params['lev'+level+'_display'] == 'slide') {
                $body.on('click', closeItem )

                $ul.each(function(){
                    const $ul = $(this);
                    const $li = $(this).closest('li');
                    const $a = $li.find('> a, > .no-link');
                    let text = $a.text();

                    const $backButton = $(`<li class="back-button">${text}</li>`)
                    $ul.prepend($backButton)

                    $a.on('click', parentClass)
                    $backButton.on('click', backButton);
                })
            }
        }

        function parentClass(e){
            $(this).closest('ul').toggleClass('slide-parent')
        }

        function backButton(){
            const $li = $(this).parent('ul').parent('li');
            $li.parent('ul').removeClass('slide-parent')
            $li.removeClass('vis-show')
        }

        function closeItem(e){
            if (!$(e.target).closest('.slide-parent').length) {
                $('.slide-parent', $menu).removeClass('slide-parent')
            }
        }
    })
})