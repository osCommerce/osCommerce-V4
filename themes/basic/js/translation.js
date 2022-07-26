tl(function(){
    let editButtons = $('<div class="translation-edit-buttons"></div>');
    $('body').append(editButtons);

    const observer = new MutationObserver(addEvents);
    observer.observe($('body').get(0), { childList: true, attributes: true, characterData: true });
    addEvents();

    function addEvents (){
        $('.translation-key-option').each(function(){
            let select = $(this).closest('select')
            select.attr('data-translation', '');

            let value = $(this).attr('value');
            let key = $(this).data('translation-key');
            let entity = $(this).data('translation-entity');

            select.attr('data-translation-key-' + value, key);
            select.attr('data-translation-entity-' + value, entity);
        })

        $('*[data-translation], .translation-key').off('mouseenter').on('mouseenter', function(){
            editButtons.html('');
            let element = $(this)
            $.each(this.attributes, function() {
                if (this.name.search('data-translation-key') === 0) {
                    let type = this.name.replace('data-translation-key-', '');
                    let key = this.value;
                    let entity = element.data('translation-entity-' + type);
                    if (!entity) entity = element.data('translation-entity');
                    let editBtn = $(`<div class="translation-edit-button" data-key="${key}" data-entity="${entity}">${key}</div>`);
                    editButtons.append(editBtn);
                    editBtn.on('click', openTranslationWindow)
                }
            });
            let top = element.offset().top;
            let left = element.offset().left;
            let width = element.width();
            let height = element.height();
            let editButtonsHeight = editButtons.height();
            let editButtonsTop;
            if (top < editButtonsHeight) {
                editButtonsTop = top + height + 5;
            } else {
                editButtonsTop = top - editButtonsHeight -5;
            }
            editButtons.css({
                top: editButtonsTop,
                left: left + width - editButtons.width() - 20
            })
        });

    }

    function openTranslationWindow(){
        $('body').append('<div id="translation-popup"><iframe id="translation-frame" width="800" height="700" src="' + DIR_WS_HTTP_ADMIN_CATALOG + 'texts/edit?translation_key=' + $(this).data('key') + '&translation_entity=' + $(this).data('entity') + '&popup=1"></div>');

        $('<a href="#translation-popup"></a>').popUp({box_class: 'translation-popup'}).trigger('click');

        $('#translation-popup').remove();

        editButtons.html('').css('top', -10);

        $('#translation-frame').on('load', function(){
            let frame = $(this).contents();
            $('.btn-confirm', frame).on('click', function(){
                setTimeout(function(){
                    $('.popup-box-wrap').remove();
                    window.location.reload()
                }, 100)
            });
            $('.btn-cancel-foot', frame).on('click', function(){
                setTimeout(function(){
                    $('.popup-box-wrap').remove();
                    $.get(setFrontendTranslationTimeUrl)
                }, 100)
            });
        })
    }

    setInterval(function(){
        if ($('.translation-popup').length > 0) {
            $.get(setFrontendTranslationTimeUrl)
        }
    }, 1000 * 60 * 4)

    $.get(setFrontendTranslationTimeUrl)
})