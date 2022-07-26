/**
 * This file is part of osCommerce ecommerce platform.
 * osCommerce the ecommerce
 *
 * @link https://www.oscommerce.com
 * @copyright Copyright (c) 2000-2022 osCommerce LTD
 *
 * Released under the GNU General Public License
 * For the full copyright and license information, please view the LICENSE.TXT file that was distributed with this source code.
 */

import createUrl from "src/createUrl";
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

export default function () {

    $(window).on('frame-ready', function(){
        switchView()
    })

    $('.btn-edit-texts').on('click', editTexts)
    $('.btn-edit-widgets').on('click', editWidgets)

    window.addEventListener('message', function (e) {
        store.data = e.data;

        const $editButtons = $('<div class="translation-edit-buttons"></div>');
        store.update({'$editButtons': $editButtons});

        let frame = $('#info-view').contents();
        const $body = $('body', frame);
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
    });
}

function editTexts(){

    const $frame = $('#info-view');

    let url = createUrl($frame.attr('src'), {'texts': 1})
    localStorage.setItem('page-url', url);
    localStorage.setItem('mode', 'texts');
    $frame.attr('src', url);
}

function editWidgets(){

    const $frame = $('#info-view');

    let url = createUrl($frame.attr('src'), {'texts': ''})
    localStorage.setItem('page-url', url);
    localStorage.setItem('mode', 'widgets');
    $frame.attr('src', url);
}

function switchView() {
    let $infoView = $('#info-view');
    let frame = $infoView.contents();
    const url = new URL($infoView.attr('src'))
    if (localStorage.getItem('mode') == 'texts') {
        $('.btn-edit-texts').hide();
        $('.btn-open-widgets').hide();
        $('.btn-edit-widgets').show();
        $('body', frame).removeClass('edit-blocks');
        $('body', frame).addClass('view-blocks');

        if (!url.searchParams.get('texts')) {
            url.searchParams.set('texts', '1');
            $infoView.attr('src', url.href)
        }
    } else {
        $('.btn-edit-texts').show();
        $('.btn-open-widgets').show();
        $('.btn-edit-widgets').hide();
        $('body', frame).removeClass('view-blocks');
        $('body', frame).addClass('edit-blocks');

        if (url.searchParams.get('texts')) {
            url.searchParams.delete('texts');
            $infoView.attr('src', url.href)
        }
    }
}