
import style from './style.scss';
import pages from './pages';
import widgets from './widgets';
import applyBlocks from './applyBlocks';
import createPageUrl from './createPageUrl';
import draggablePopup from 'src/draggablePopup';

export function init(){

    let newWin = false;
    const $btnPreview = $('.btn-preview');
    const $btnEdit = $('.btn-edit');
    const theme_name = entryData.theme_name;
    const $infoView = $('.info-view');
    let scroll = 0;

    let pageUrl = localStorage.getItem('page-url');

    if (pageUrl && !pageUrl.toLowerCase().includes('theme_name=' + theme_name.toLowerCase())) {
        pageUrl = false;
    }

    if (!pageUrl) {
        let url = '';
        let breadcrumbs = '';
        if (entryData.groups && entryData.groups.home) {
            url = createPageUrl({
                action: '',
                page_name: 'main',
                platform_id: entryData.platformSelect[0].id
            });
            breadcrumbs = entryData.groups.home.title || '';
            if (entryData.pages && entryData.pages.home && entryData.pages.home.title) {
                breadcrumbs += ' / ';
                breadcrumbs += entryData.pages.home.title;
            }
        } else if (entryData.groups && entryData.pages) {
            const firstGroup = Object.keys(entryData.groups)[0];

            let page = '';
            for (let pageKey in entryData.pages) {
                if (!entryData.pages.hasOwnProperty(pageKey)) {
                    continue;
                }
                if (entryData.pages[pageKey].group === firstGroup) {
                    page = entryData.pages[pageKey];
                    break;
                }
            }

            breadcrumbs = entryData.groups[firstGroup].title || '';
            if (page) {
                url = createPageUrl(page);
                breadcrumbs += ' / ';
                breadcrumbs += page.title;
            }
        }

        localStorage.setItem('page-url', url);
        localStorage.setItem('page-breadcrumbs', breadcrumbs);
    }

    const $iframe = $(`<iframe src="${localStorage.getItem('page-url')}" width="100%" frameborder="no" id="info-view"></iframe>`);
    $infoView.html('').append($iframe);

    $iframe.on('load', function(){

        const $frame = $iframe.contents();
        const $body = $('body', $frame);
        $body.addClass('edit-blocks');

        if (!$body.hasClass('is-admin')) {
            const $warning = $(`<div>${entryData.tr.DATA_FROM_NETWORK_CHANGED}</div>`);

            let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);
            let $btnLogOff = $(`<a href="logout" class="btn btn-primary">${entryData.tr.TEXT_HEADER_LOGOUT}</a>`);

            let popup = draggablePopup($warning, {
                heading: entryData.tr.ICON_WARNING,
                top: 100,
                buttons: [$btnCancel, $btnLogOff],
                resizable: false
            });

            $btnCancel.on('click', function(){
                popup.remove();
            });
        }

        applyBlocks();

        //$(document).bind('keydown', 'Alt+p', clickPreview);
        //$frame.bind('keydown', 'Alt+p', clickPreview);
        $btnPreview.on('click', clickPreview);
        $btnEdit.on('click', clickPreview);

        function clickPreview(){
            if ($body.hasClass('edit-blocks')){
                $btnEdit.show();
                $btnPreview.hide();
                $body.removeClass('edit-blocks');
                $body.addClass('view-blocks');
            } else {
                $btnPreview.show();
                $btnEdit.hide();
                $body.addClass('edit-blocks');
                $body.removeClass('view-blocks');
            }
        }

        $('.btn-preview-2').on('click', function(){
            newWin = window.open(localStorage.getItem('page-url')+'&is_admin=1', 'Preview', 'left=0,top=0,width=1200,height=900,location=no');
        });

        $infoView.removeClass('hided-box');
        $('.hided-box-holder', $infoView).remove();

        $('html, body', $frame).scrollTop(scroll);

        $(window).trigger('reloaded-frame');
    });

    $(window).off('reload-frame').on('reload-frame', reloadFrame);

    function reloadFrame(){
        scroll = $iframe.contents().scrollTop();
        $iframe.attr('src', $iframe.attr('src'));
        if (newWin) {
            newWin.location.reload();
        }
    }


    $('.btn-save-boxes').on('click', function(){
        $infoView.addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>');
        $.get($(this).data('href'), { theme_name}, function(d){
            $(window).trigger('reload-frame');
            const $alert = alertMessage(d);

            const $homePage = $(`<iframe src="${entryData.frontendUrl}?theme_name=${theme_name}" width="100%" height="0" frameborder="no"></iframe>`);

            $('body').append($homePage);

            $homePage.on('load', function(){
                html2canvas($homePage.contents().find('body').get(0))
                    .then(function(canvas) {
                        $.post('upload/screenshot', { theme_name, image: canvas.toDataURL('image/png')});
                        $homePage.remove();
                    });
            });

            setTimeout(() => $alert.remove(), 1000);
        });
    });


    const $redoButtons = $('.redo-buttons');
    $redoButtons.on('click', '.btn-undo', function(){
        const event = $(this).data('event');
        $redoButtons.hide();
        $.get('design/undo', { theme_name }, function(){
            if (event == 'addPage' ){
                location.href = location.href;
            }
            $(window).trigger('reload-frame');

        });
    });
    $redoButtons.on('click', '.btn-redo', function(){
        const event = $(this).data('event');
        $redoButtons.hide();
        $.get('design/redo', { theme_name, 'steps_id': $(this).data('id')}, function(){
            if (event == 'addPage'){
                location.href = location.href;
            }
            $(window).trigger('reload-frame');
        });
    });
    $.get('design/redo-buttons', { theme_name }, function(data){
        $redoButtons.html(data);
    });
    $(window).on('reload-frame', function(){
        $.get('design/redo-buttons', { theme_name }, function(data){
            $redoButtons.html(data);
            $redoButtons.show();
        });
    });
    $('.themes-menu .right-area')
        .append(`<span class="btn btn-elements btn-edit-widgets">${entryData.tr.EDIT_WIDGETS}</span>`)
        .append(`<span class="btn btn-elements btn-edit-texts">${entryData.tr.EDIT_TEXTS}</span>`)
        .append(`<span class="btn btn-elements btn-open-pages">${entryData.tr.TEXT_PAGES}</span>`)
        .append(`<span class="btn btn-elements btn-open-widgets">${entryData.tr.TEXT_WIDGETS}</span>`);

    widgets();
    pages();
}