import style from "./style.scss";
import store from "./store";

import translation from "./translation";
import general from "./general";

let modules = [translation, general];

function hintPosition(element){
    let top = element.offset().top;
    let left = element.offset().left;
    let width = element.width();
    let height = element.height();
    let editButtonsHeight = store.data.$editButtons.height();
    let editButtonsTop;
    if (top < editButtonsHeight) {
        editButtonsTop = top + height + 5;
    } else {
        editButtonsTop = top - editButtonsHeight -5;
    }
    store.data.$editButtons.css({
        top: editButtonsTop,
        left: left + width - store.data.$editButtons.width() - 20
    })
}

function addEvents (){
    modules.forEach((module) => module.hints(hintPosition))
}

export function loader(allItemsJson) {
    store.data = JSON.parse(allItemsJson);

    const $editButtons = $('<div class="translation-edit-buttons"></div>');
    store.update({'$editButtons': $editButtons});

    const $body = $('body');
    $body.after($editButtons);

    $body.on('click', function(){
        store.data.$editButtons.html('').css('top', -10);
    })

    const observer = new MutationObserver(addEvents);
    observer.observe($body.get(0), {childList: true, attributes: true, characterData: true});
    addEvents();

    setInterval(function () {
        if ($('.edit-data-popup').length > 0) {
            $.get(store.data.setFrontendTranslationTimeUrl)
        }
    }, 1000 * 60 * 4)

    $.get(store.data.setFrontendTranslationTimeUrl)
}