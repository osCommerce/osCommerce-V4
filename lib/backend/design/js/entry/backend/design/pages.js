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

import style from "./style.scss";
import draggablePopup from "src/draggablePopup";
import treeJs from "src/tree";
import Sortable, { AutoScroll } from 'sortablejs';
import interact from 'interactjs';
import editTexts from './edit-texts';
import exportBlock from './exportBlock';
import popupSettings from "src/popupSettings";


export function init(){
    let $themesMenu = $('.themes-menu');

    $themesMenu.css('left', $('#sidebar').width());

    $('.right-area', $themesMenu)
        .append(`<span class="btn btn-elements btn-edit-widgets">Edit Widgets</span>`)
        .append(`<span class="btn btn-elements btn-edit-texts">Edit Texts</span>`)
        .append(`<span class="btn btn-elements btn-open-pages">${entryData.tr.TEXT_PAGES}</span>`)
        .append(`<span class="btn btn-elements btn-open-widgets">${entryData.tr.TEXT_WIDGETS}</span>`);

    openPages();
    editTexts();

    $('.top_bead h1').html(entryData.theme_title + ' / ' + localStorage.getItem('page-breadcrumbs'));

    $(window).on('reloaded-frame', sortWidgets);

    addingWidgets();

    $('html, body').css('overflow', 'hidden');

    interact('.info-view-wrap')
        .resizable({
            edges: {
                right: '.info-view-right-resize'
            },
        })
        .on('resizemove', event => {
            Object.assign(event.target.style, {
                width: `${event.rect.width}px`,
            })
        })
};

function addingWidgets(){
    $(window).on('reloaded-frame', function(){
        let frame = $('#info-view').contents();
        let boxType = '';
        $('.block[data-type]', frame).each(function(){
            let type = $(this).data('type');
            if (type != 'header' && type != 'footer'){
                boxType = type
            }
        });
        $.get('design/widgets-list', {type: boxType}, function(data) {
            entryData.widgetList = data
        }, 'json');
    });

    const widgetsPopUp = openWidgets({data: {adding: moveWidget, settings: {close: 'hide'}}})
    widgetsPopUp.trigger('close');
    $('.btn-open-widgets').on('click', function(){
        if (widgetsPopUp.is(":visible")) {
            widgetsPopUp.trigger('close');
        } else {
            widgetsPopUp.trigger('open');
        }
    });

    $(window).on('reloaded-frame', function(){
        let frame = $('#info-view').contents();
        $('.block .add-box', frame).on('click', {adding: addWidgets}, openWidgets);
        $('.menu-widget .export', frame).on('click', exportBlock)
    });
}

function addWidgets($html, $target, popup){
    $('.widget-item', $html).on('click', function(){
        var data = {
            'theme_name': entryData.theme_name,
            'block': $target.data('name'),
            'box': $(this).data('name'),
            'order': $('> div', $target).length + 1
        };
        popup.remove();
        $.post('design/box-add', data, function(){
            $(window).trigger('reload-frame')
        }, 'json');
    })
}

function sortWidgets(){
    let frame = $('#info-view').contents();
    $('div.box, div.box-block', frame).addClass('dragable');
    $('.block' , frame).each(function () {
        Sortable.create(this, {
            group: 'block',
            draggable: '> .dragable',
            handle: '.handle',
            animation: 300,
            swapThreshold: 0.5,
            invertSwap: true,
            onEnd: function(event) {
                var blocks = {};
                blocks['name'] =  $(event.to).data('name');
                blocks['theme_name'] =  entryData.theme_name;
                blocks['id'] = {};
                $('> div', event.to).each(function(i){
                    blocks.id[i] = $(this).attr('id');
                });
                $.post('design/blocks-move', blocks, function(){
                    //$(window).trigger('reload-frame')
                }, 'json');
            }
        });
    })
}

function openWidgets(event){
    $('.widgets-page-popup').remove();

    let $target = {};
    if ($(this).hasClass('add-box-single')) {
        $target = $(this).parent('.block');
    } else {
        $target = $(this).closest('.box-block').find('> .block:first');
    }

    let $html = $('<div><div class="preloader"></div></div>');

    let height = $(window).height() - 140;
    let popupSettingsVal = popupSettings('widgets');
    if (popupSettingsVal && popupSettingsVal.height) {
        height = popupSettingsVal.height;
    }
    $html.css({
        maxHeight: height - 75,
        overflow: 'auto'
    })

    let popup = draggablePopup($html, {
        heading: 'Widgets',
        name: 'widgets',
        top: 100,
        height: height,
        className: 'widgets-page-popup',
        zooming:true,
        resizable: {
            edges: {
                top: false,
                left: false,
                right: true,
                bottom: true,
            },
            listeners: {
                move (event) {
                    Object.assign(event.target.style, {
                        width: `${event.rect.width}px`,
                        height: `${event.rect.height}px`,
                    });
                    $html.css({
                        maxHeight: parseInt($html.css('max-height')) + event.deltaRect.height + 'px'
                    })
                    popupSettings('widgets', {width: event.rect.width, height: event.rect.height})
                }
            },
        },
        ...event.data.settings
    });


    reloadFrame();
    $(window).off('reloaded-frame', reloadFrame).on('reloaded-frame', reloadFrame);

    function reloadFrame(){

        let frame = $('#info-view').contents();
        let boxType = '';
        $('.block[data-type]', frame).each(function(){
            let type = $(this).data('type');
            if (type != 'header' && type != 'footer'){
                boxType = type
            }
        });
        $.get('design/widgets-list', {type: boxType}, function(data) {

            data.sort((a, b) => a.title.toLowerCase() < b.title.toLowerCase() ? -1 : 1)

            $html.html('');
            $.each(data, function (i, item) {
                if (!$('.box-group-'+item.type, $html).length){
                    $html.append('<div class="box-group box-group-'+item.type+'"></div>')
                }
                if (item.name == 'title') {
                    $('.box-group-' + item.type, $html).prepend('<div class="title">' + item.title + '</div>');
                } else {
                    $('.box-group-' + item.type, $html).append('<div class="widget-item ico-' + item.class + '" data-name="' + item.name + '" title="' + item.title + '">' + item.title + ' <span style="display: none">1</span></div>');
                }

            });
            searchWidget($html);
            event.data.adding($html, $target, popup);
        }, 'json');

        setTimeout(function(){
            moveWidget($html);
        }, 0)
    }

    return popup;
}

function searchWidget($html){
    let $sarchInput = $('<input type="text" class="form-control search-widget" placeholder="Search">');
    $html.prepend($sarchInput);

    $sarchInput.on('keyup', function(){
        let val = $sarchInput.val();

        $('.widget-item', $html).each(function(){
            if (val === '' || $(this).text().toLowerCase().search(val.toLowerCase()) !== -1) {
                $(this).show()
            } else {
                $(this).hide()
            }
        })
    })
}

function moveWidget($html){
    let frame = $('#info-view').contents();

    $('.box-group', $html).each(function(){
        Sortable.create(this, {
            group: {
                name: 'block',
                pull: 'clone',
                put: false
            },
            draggable: '.widget-item',
            animation: 300,
            swapThreshold: 0.5,
            invertSwap: true,
            onEnd: function(event){
                var data = {
                    'theme_name': entryData.theme_name,
                    'box': $(event.item).data('name'),
                    'block': $(event.to).data('name'),
                    'order': event.newIndex
                };
                data['id'] = {};
                $('> div', event.to).each(function(i){
                    if ($(this).hasClass('widget-item')){
                        data.id[i] = 'new';
                    } else {
                        data.id[i] = $(this).attr('id');
                    }
                });
                $.post('design/box-add-sort', data, function(){
                    $(window).trigger('reload-frame')
                }, 'json');
            }
        });
    })
}

function openPages(){
    if (!entryData || !entryData.groups || !entryData.pages) {
        return false;
    }

    let $html = $('<div><div class="sales-channels"></div><ul class="tree"></ul></div>');

    for (let groupName in entryData.groups) {
        if (!entryData.groups.hasOwnProperty(groupName)) {
            continue;
        }

        let group = entryData.groups[groupName];

        $('.tree', $html).append(`
            <li data-group="${group.name}">
                <div class="item-holder">
                    <div class="text close-holder">${group.title}</div>
                    <div class="close-sub-items closed"></div>
                </div>
                <ul></ul>
            </li>
        `)
    }

    for (let pageKey in entryData.pages) {
        if (!entryData.pages.hasOwnProperty(pageKey)) {
            continue;
        }

        let page = entryData.pages[pageKey];

        $(`li[data-group="${page.group}"] > ul`, $html).append(`
            <li data-page="${pageKey}">
                <div class="item-holder">
                    <div class="text">${page.title}${page.added ? `<small>(${page.type})</small>` : ''}</div>
                    ${page.added ? `<div class="item-button page-remove" data-title="${page.title}" title="${entryData.tr.TEXT_REMOVE}"></div>` : ''}
                    ${page.settings ? `<div class="item-button page-settings" title="${entryData.tr.TEXT_EDIT_SETTINGS}"></div>` : ''}
                    ${unitedPages(page.type).length > 1 ? `<div class="item-button page-copy" title="${entryData.tr.TEXT_COPY_PAGE}"></div>` : ''}
                </div>
            </li>
        `)
    }

    for (let groupName in entryData.groups) {
        if (!entryData.groups.hasOwnProperty(groupName)) {
            continue;
        }

        let group = entryData.groups[groupName];

        $(`li[data-group="${group.name}"] > ul`, $html).append(`
            <li data-add-page="${group.name}">
                <div class="item-holder">
                    <div class="text">+ ${entryData.tr.TEXT_ADD_PAGE}</div>
                </div>
            </li>
        `)
    }

    treeJs($html);
    treeSearch($html);
    salesChannels($html);
    openPage($html);
    addPage($html);
    removePage($html);
    pageSettings($html);
    copyPage($html);


    let height = $(window).height() - 180 > 700 ? 700 : $(window).height() - 180;
    let popupSettingsVal = popupSettings('pages');
    if (popupSettingsVal && popupSettingsVal.height) {
        height = popupSettingsVal.height;
    }
    $html.css({
        height: height - 75,
        overflow: 'auto'
    })

    let popup = draggablePopup($html, {
        heading: entryData.tr.TEXT_CHOOSE_PAGE,
        name: 'pages',
        top: 100,
        height,
        className: 'choose-page-popup',
        zooming:true,
        close: 'hide',
        resizable: {
            edges: {
                top: false,
                left: false,
                right: true,
                bottom: true,
            },
            listeners: {
                move (event) {
                    Object.assign(event.target.style, {
                        width: `${event.rect.width}px`,
                        height: `${event.rect.height}px`,
                    });
                    $html.css({
                        height: $html.height() + event.deltaRect.height + 'px'
                    })
                    popupSettings('pages', {width: event.rect.width, height: event.rect.height})
                }
            },
        },
    });

    /*$html.resizable();
    popup.resizable({
        alsoResize: $html
    });*/

    /*interact(popup.get(0))
        .resizable({
            edges: {
                right: true,
                bottom: true,
            },
        })
        .on('resizemove', event => {
            Object.assign(event.target.style, {
                width: `${event.rect.width}px`,
                height: `${event.rect.height}px`,
            });
            $html.css({
                height: $html.height() + event.deltaRect.height + 'px'
            })
        });*/

    popup.trigger('close');
    $('.btn-open-pages').on('click', function(){
        if (popup.is(":visible")) {
            popup.trigger('close');
        } else {
            popup.trigger('open');
        }
    });
}

function copyPage($html){
    $('li[data-page] .page-copy', $html).on('click', function(){
        let pageName = $(this).closest('li').data('page');

        let $form = $(`
                <div class="setting-content">
                    <p>${entryData.tr.COPY_PAGE_CONTENT_FROM} </p>
                    <p>
                        <select name="page_type" class="form-control">
                            <option value=""></option>
                        </select>
                    </p>
                    <p>to <strong>"${entryData.pages[pageName].title}"</strong></p>
                </div>`);

        unitedPages(entryData.pages[pageName].type).forEach(
            up => $('select', $form).append(`
                    <option value="${entryData.pages[up].page_name}">${entryData.pages[up].title}</option>`)
        )

        let $btnSave = $(`<span class="btn btn-save">${entryData.tr.IMAGE_SAVE}</span>`);
        let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);

        let popup = draggablePopup($form, {
            heading: entryData.tr.COPY_PAGE_CONTENT,
            top: 100,
            buttons: [$btnSave, $btnCancel],
            resizable: false
        });

        $btnSave.on('click', function(){
            let sendData = {
                theme_name: entryData.theme_name,
                page_to: pageName,
                page_from: $('select', $form).val(),
            };

            $.post(entryData.mainUrl + '/design/copy-page', sendData, function () {
                document.location.reload();
                popup.remove()
            })
        });

        $btnCancel.on('click', function(){
            popup.remove()
        });
    })
}

function treeSearch($html){
    let $searchInput = $(`<input type="text" class="form-control" placeholder="${entryData.tr.TEXT_SEARCH_PAGE}"/>`);
    $('.tree', $html).before($searchInput);

    let $btn = $('.close-sub-items', $html);
    let $li = $btn.closest('li');
    let $ul = $('> ul', $li);
    let $addPage = $('li[data-add-page]', $html);
    let $titles = $('li[data-page] .text', $html);

    $searchInput.on('keyup', function(){
        let val = $searchInput.val();

        $titles.each(function(){
            if ($(this).text().toLowerCase().search(val.toLowerCase()) !== -1) {
                $(this).closest('li').show()
            } else {
                $(this).closest('li').hide()
            }
        })

        $('li[data-group]', $html).show().each(function(){
            if ($('li:visible', this).length === 0){
                $(this).hide()
            }
        })

        if (val.length > 0) {
            $btn.removeClass('closed');
            $li.removeClass('closed');
            $ul.show(100);
            $addPage.hide()
        } else {
            $btn.addClass('closed');
            $li.addClass('closed');
            $li.show();
            $ul.hide(100);
            $addPage.show()
        }
    })
}

function pageSettings($html){
    $('li[data-page] .page-settings', $html).on('click', function(){
        let pageName = $(this).closest('li').data('page');

        let $form = $(`<div class="setting-content"></div>`);


        let $btnSave = $(`<span class="btn btn-save">${entryData.tr.IMAGE_SAVE}</span>`);
        let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);

        let popup = draggablePopup($form, {
            heading: entryData.tr.TEXT_PAGE_SETTINGS,
            top: 100,
            buttons: [$btnSave, $btnCancel],
            resizable: false,
        });

        let pageSend = entryData.pages[pageName].page_name;
        if (entryData.pages[pageName].added) {
            pageSend = entryData.pages[pageName].title;
        }

        $.get(entryData.mainUrl + '/design/add-page-settings', {
            heading: entryData.pages[pageName].title,
            theme_name: entryData.theme_name,
            page_name: pageSend,
            page_type: entryData.pages[pageName].type,
        }, function(response){
            $form.append(response)
        });

        $btnSave.on('click', function(){

            let values = $('input, select', $form).serializeArray();
            values = values.concat(
                $('input[type=checkbox]:not(:checked)', $form).map(function() {
                    return { "name": this.name, "value": 0}
                }).get()
            );
            $.post(entryData.mainUrl + '/design/add-page-settings-action', values, function(){
                popup.remove()
                $(window).trigger('reload-frame');
            }, 'json');
        });

        $btnCancel.on('click', function(){
            popup.remove()
        });
    })
}

function removePage($html){
    $('li[data-page] .page-remove', $html).on('click', function(){
        let pageName = $(this).data('title');

        let $form = $(`<div class="confirm-text">${entryData.tr.TEXT_REMOVE_THIS_PAGE}</div>`);

        let $btnSave = $(`<span class="btn btn-save">${entryData.tr.TEXT_REMOVE}</span>`);
        let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);

        let popup = draggablePopup($form, {
            top: 100,
            buttons: [$btnSave, $btnCancel],
            resizable: {}
        });

        $btnSave.on('click', function(){
            let sendData = {
                theme_name: entryData.theme_name,
                page_name: pageName
            };
            $.get(entryData.mainUrl + '/design/remove-page-template', sendData, function(d){
                if (d.code == 2) {
                    location.reload();
                } else {
                    alertMessage('Error')
                }
            }, 'json')
        });

        $btnCancel.on('click', function(){
            popup.remove()
        });
    })
}

function addPage($html){
    $('li[data-add-page] .text', $html).on('click', function(){
        let group = $(this).closest('li').data('add-page');

        let $form = $(`
<div>    
    <div class="setting-row">
        <label for="">${entryData.tr.TEXT_PAGE_NAME}</label>
        <input type="text" name="page_name" value="" class="form-control page-name" style="width: 243px" required="">
    </div>
    
    <div class="setting-row">
        <label for="">${entryData.tr.TEXT_PAGE_TYPE}</label>
        <select name="page_type" id="" class="form-control page-types" required="">
        </select>
    </div>
</div>`);
        let $pageTypes = $('.page-types', $form);
        let $pageName = $('.page-name', $form);
        for (let key in entryData.groups[group].types) {
            if (!entryData.groups[group].types.hasOwnProperty(key)) {
                continue;
            }
            let type = entryData.groups[group].types[key];
            $pageTypes.append(`<option value="${type}">${type}</option>`)
        }


        let $btnSave = $(`<span class="btn btn-save">${entryData.tr.IMAGE_SAVE}</span>`);
        let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);


        let popup = draggablePopup($form, {
            heading: entryData.tr.TEXT_ADD_PAGE,
            top: 100,
            buttons: [$btnSave, $btnCancel],
            resizable: false
        });

        $btnSave.on('click', function(){
            let sendData = {
                page_name: $pageName.val(),
                page_type: $pageTypes.val(),
                theme_name: entryData.theme_name,
            };
            $.get(entryData.mainUrl + '/design/add-page-action', sendData, function(d){
                $('.pop-mess-cont .error').remove();
                if (d.code == 1){
                    $('.pop-mess-cont').prepend('<div class="error">'+d.text+'</div>')
                }
                if (d.code == 2){
                    $('.pop-mess-cont').prepend('<div class="info">'+d.text+'</div>');
                    setTimeout(function(){
                        location.reload();
                    }, 1000)
                }
            }, 'json');
        });

        $btnCancel.on('click', function(){
            popup.remove()
        });
    })
}

function openPage($html){
    $('li[data-page] .text', $html).on('click', function(){
        let pageKey = $(this).closest('li').data('page');
        let page = entryData.pages[pageKey];
        let url = createUrl(page);

        let groupTitle = entryData.groups[page.group].title;
        let pageBreadcrumbs = groupTitle + ' / ' + page.title;
        $('.top_bead h1').html(entryData.theme_title + ' / ' + pageBreadcrumbs);
        localStorage.setItem('page-url', url);
        localStorage.setItem('page-breadcrumbs', pageBreadcrumbs);
        $('#info-view').attr('src', url);
        $html.closest('.popup-draggable').trigger('close')
    });

    let goToPage = $(`<input type="text" class="form-control" placeholder="${entryData.tr.GO_TO_PAGE_BY_URL}">`);

    goToPage.on('change', function () {
        let url = $(this).val();
        $('.top_bead h1').html(entryData.theme_title);
        localStorage.setItem('page-url', url);
        localStorage.setItem('page-breadcrumbs', '');
        $('#info-view').attr('src', url);
    })

    $html.append(goToPage);
}

function salesChannels($html){
    if (entryData.platformSelect) {
        $('.sales-channels', $html).append(`
            <div class="sales-channels-holder">
                <!--<div class="title">${entryData.tr.TEXT_SELECT_PREVIEW_PLATFORM}</div>-->
                <select class="sales-channels-select form-control"></select>
            </div>
        `);

        for (let id in entryData.platformSelect) {
            if (!entryData.platformSelect.hasOwnProperty(id)) {
                continue;
            }

            let option = entryData.platformSelect[id];

            $('.sales-channels-select', $html).append(`
                <option value="${option.id}">${option.text}</option>
            `)
        }
    }
}

function createUrl(data) {
    let base = $('base').attr('href').trim();
    if (base.slice(-1) === '/') {
        base = base.slice(0, -1)
    }
    base = base.slice(0, base.lastIndexOf('/'));

    let url = base + '/' + data.action;
    let platformId = $('.sales-channels-select').val();

    url += '?theme_name=' + entryData.theme_name;
    url += '&platform_id=' + platformId;
    url += '&page_name=' + data.page_name;
    url += '&language=' + entryData.languageCode;

    if (data.get_params && data.get_params[platformId]) {
        for (let param in data.get_params[platformId]) {
            if (!data.get_params[platformId].hasOwnProperty(param)) {
                continue;
            }
            url += '&' + param + '=' + data.get_params[platformId][param]
        }
    }

    return url;
}

function unitedPages(type){

    let united = entryData.unitedTypes.filter( arr => arr.includes(type) );
    if (united[0]) {
        united = united[0];
    } else {
        united = [type]
    }

    let unitedPages = [];
    let count = 0;
    for (let pageKey in entryData.pages) {
        if (!entryData.pages.hasOwnProperty(pageKey)) {
            continue;
        }
        let page = entryData.pages[pageKey];

        if (united.includes(page.type)) {
            unitedPages.push(pageKey)
        }
    }
    return unitedPages;
}