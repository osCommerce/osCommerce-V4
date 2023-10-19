import Sortable from 'sortablejs';
import draggablePopup from 'src/draggablePopup';

export default function(){

    $(window).on('reloaded-frame', function(){
        const frame = $('#info-view').contents();
        let boxType = '';
        $('.block[data-type]', frame).each(function(){
            let type = $(this).data('type');
            if (type != 'header' && type != 'footer'){
                boxType = type;
            }
        });
        $.get('design/widgets-list', {type: boxType}, function(data) {
            entryData.widgetList = data;
        }, 'json');

        $('.block .add-box', frame).on('click', {adding: addWidgets}, openWidgets);

        sortWidgets();
    });

    $('.btn-open-widgets').on('click', {adding: moveWidget}, openWidgets);
}


function addWidgets($html, $target, popup){
    $('.widget-item', $html).on('click', function(){
        const data = {
            'theme_name': entryData.theme_name,
            'block': $target.data('name'),
            'box': $(this).data('name'),
            'order': $('> div', $target).length + 1
        };
        popup.remove();
        $.post('design/box-add', data, function(){
            $(window).trigger('reload-frame');
        }, 'json');
    });
}

function openWidgets(event){
    $('.widgets-page-popup').remove();

    let $target = {};
    if ($(this).hasClass('add-box-single')) {
        $target = $(this).closest('.block');
    } else {
        $target = $(this).closest('.box-block').find('> .block:first');
    }

    let $html = $('<div><div class="preloader"></div></div>');

    let popup = draggablePopup($html, {
        heading: 'Widgets',
        name: 'widgets',
        top: 100,
        className: 'widgets-page-popup',
        zooming:true,
        height: 400,
        resizable: {
            edges: {
                top: false,
                left: false,
                right: true,
                bottom: true,
            },
        },
        ...event.data.settings
    });

    entryData.widgetList.sort(function(a, b){
        if (a.name == 'title' || b.name == 'title') {
            return 0;
        }
        return a.title.toLowerCase() < b.title.toLowerCase() ? -1 : 1
    });

    $html.html('');
    $.each(entryData.widgetList, function (i, item) {
        if (!item.type) {
            return null;
        }
        if (!$('.box-group-'+item.type, $html).length){
            $html.append(`
                    <div class="widget box" id="${item.type}">
                        <div class="widget-header">
                            <h4></h4>
                            <div class="toolbar no-padding">
                                <div class="btn-group">
                                    <span class="btn btn-xs widget-collapse"><i class="icon-angle-down"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content box-group box-group-${item.type}"></div>
                    </div>
            `);
        }
        if (item.name == 'title') {
            $(`#${item.type} h4`, $html).html(item.title);
        } else {
            //if (item.type == 'groups') {

            //} else {
                $(`#${item.type} .widget-content`, $html).append('<div class="widget-item ico-' + item.class + '" data-name="' + item.name + '" title="' + item.title + '">' + item.title + ' <span style="display: none">1</span></div>');
            //}
        }
    });

    $('.widget', $html).each(function () {
        if (!$('h4', this).html()) {
            $('h4', this).html($(this).attr('id'));
        }
    });

    $('.widget .toolbar .widget-collapse', $html).openCloseWidget();

    searchWidget($html, popup);
    event.data.adding($html, $target, popup);

    return popup;
}

function searchWidget($html, $popup){
    let $sarchInput = $('<input type="text" class="form-control search-widget-inp" placeholder="Search">');
    $('.popup-heading', $popup).append($sarchInput);

    $sarchInput.on('keyup', function(){
        let val = $sarchInput.val();

        $('.widget-item', $html).each(function(){
            if (val === '' || $(this).text().toLowerCase().search(val.toLowerCase()) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });

        $('.widget-content', $html).show();

        $('.widget', $html).each(function(){
            $(this).show();
            if (!$('.widget-item:visible', this).length) {
                $(this).hide();
            }
        });
    });
}

function moveWidget($html){
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
                data.id = {};
                $('> div', event.to).each(function(i){
                    if ($(this).hasClass('widget-item')){
                        data.id[i] = 'new';
                    } else {
                        data.id[i] = $(this).attr('id');
                    }
                });
                $.post('design/box-add-sort', data, function(){
                    $(window).trigger('reload-frame');
                }, 'json');
            }
        });
    });
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
                blocks.name =  $(event.to).data('name');
                blocks.theme_name =  entryData.theme_name;
                blocks.id = {};
                $('> div', event.to).each(function(i){
                    blocks.id[i] = $(this).attr('id');
                });
                $.post('design/blocks-move', blocks, function(){
                    //$(window).trigger('reload-frame')
                }, 'json');
            }
        });
    });
}