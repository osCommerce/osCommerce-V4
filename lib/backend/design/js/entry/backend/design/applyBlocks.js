import editCss from './editCss';
import editBox from './editBox';
import exportBlock from './exportBlock';
import changePage from './changePage';

export default function(){

    const frame = $('#info-view').contents();

    $('a', frame).removeAttr('href');
    $('form', frame).removeAttr('action').on('submit', () => false);

    $('.block[data-type]', frame).append(`
        <span class="menu-widget root-block-menu">
            <span class="add-box add-box-single" title="${entryData.tr.ADD_WIDGET}"></span>
            ${entryData.designer_mode ? `<span class="export root-export" title="${entryData.tr.EXPORT_BLOCK}"></span>` : ''}
        </span>`);

    changePage(frame);

    $('.box-block', frame).append(`
        <span class="menu-widget">
            <span class="add-box" title="${entryData.tr.ADD_WIDGET}"></span>
            <span class="edit-box" title="${entryData.tr.EDIT_BLOCK}"></span>
            <span class="handle" title="${entryData.tr.MOVE_BLOCK}"></span>
            ${entryData.designer_mode ? `<span class="export" title="${entryData.tr.EXPORT_BLOCK}"></span>` : ''}
            <span class="remove-box" title="${entryData.tr.REMOVE_WIDGET}"></span>
        </span>`);
    $('.box-block > .menu-widget', frame).each(function(){
        const box = $(this).parent();
        $(this).css({
            'margin-left': box.css('padding-left'),
            'bottom': $(this).css('bottom').replace('px', '') * 1 + box.css('padding-bottom').replace('px', '')*1
        });
    });
    $('.box-block.type-1 > .menu-widget', frame).each(function(){
        const box = $(this).parent();
        $(this).css({
            'margin-left': 0,
            'left': (box.width() - $('> .block', box).width())/2 - 12
        });
    });

    $('.box, .box-block', frame).on('mouseleave mouseenter', function(e){
        $('.box-active', frame).removeClass('box-active');
        if (e.type == 'mouseleave') {
            $(this).parent().closest('.box, .box-block').addClass('box-active');
        } else if (e.type == 'mouseenter') {
            $(this).addClass('box-active');
        }
    });

    $('.table-adding-column .update-cols', frame).on('click', function(e){
        $('.info-view').addClass('hided-box').append('<div class="hided-box-holder"><div class="preloader"></div></div>');
        $.post('design/set-theme-setting', { theme_name: entryData.theme_name, setting_group: 'tableRow', setting_name: $(this).data('name'), setting_value: $(this).data('cols')}, function () {
            $(window).trigger('reload-frame');
        });
    });

    $('.box', frame).append(`
        <span class="menu-widget">
            <span class="edit-box" title="${entryData.tr.EDIT_WIDGET}"></span>
            ${entryData.designer_mode && false ? `<span class="edit-css" title="${entryData.tr.EDIT_WIDGET_STYLES}"></span>` : ''}
            <span class="handle" title="${entryData.tr.MOVE_BLOCK}"></span>
            ${entryData.designer_mode ? `<span class="export" title="${entryData.tr.EXPORT_BLOCK}"></span>` : ''}
            <span class="remove-box" title="${entryData.tr.REMOVE_WIDGET}"></span>
        </span>`);

    $('.menu-widget', frame).each(function(){
        if ($(this).parent('.box').css('float') == 'right') {
            $(this).css({
                left: 'auto',
                right: 0
            });
        }
    });

    $('.block .remove-box', frame).on('click',
        function(){
        const blocks = {};
        const $parent = $(this).closest('div[id]');
        blocks.name =  $parent.data('name');
        blocks.theme_name =  entryData.theme_name;
        blocks.id = $parent.attr('id');
        $.post('design/box-delete', blocks, function(){
            $parent.remove();
            //if (newWin && typeof newWin.location.reload == 'function') newWin.location.reload(localStorage.getItem('page-url')+'&is_admin=1');
        }, 'json');
    });

    $('.menu-widget .edit-css', frame).off('click').on('click', {frame: frame}, function () {
        editCss(frame, this);
    });

    $('.menu-widget .edit-box', frame).off('click').on('click', editBox);

    $('.import-box', frame).each(function(){
        var block_name = $(this).closest('div[data-name]').parent().closest('div[data-name]').data('name');
        var box_id = $(this).parent().attr('id');
        $(this).dropzone({
            url: 'design/import-block?theme_name=' + entryData.theme_name + '&block_name=' + block_name + '&box_id=' + box_id,
            success: function(){
                $(window).trigger('reload-frame');
            },
            acceptedFiles: '.zip'
        });
    });

    $('.menu-widget .export', frame).on('click', exportBlock);

    var type_box = '';
    $('.block[data-type]', frame).each(function(){
        var type = $(this).data('type');
        if (type != 'header' && type != 'footer'){
            type_box = type;
        }
    });
    $('body', frame).prepend('<div class="widgets-list"></div>');



    $(window).trigger('frame-ready');






}