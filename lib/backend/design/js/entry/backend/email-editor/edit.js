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

import style from "./edit/style.scss";
import save from "./edit/save";
import addProducts from "./edit/addProducts";
import addImages from "./edit/addImages";
import addBlocks from "./edit/addBlocks";
import dragCopySortElements from "./edit/dragCopySortElements";
import widgetMenuTemplate from "./edit/widgetMenuTemplate";
import clearHtml from "./edit/clearHtml";
import draggablePopup from "src/draggablePopup";


export function init(data){

    emailEditor.data = data;

    let btnSave = $('.btn-save-boxes');
    let form = $('.email-editor');
    let editField = $('.edit-field');
    let subjectInput = $('input[name="subject"]');

    save({
        button: btnSave,
        form: form,
        saveUrl: 'email-editor/save'
    });

    chooseTheme();

    editTextImage();

    popUpPreview();

    rightColPosition();

    widgetContainer();

    exportHtml();

    editField.html();

    editField.on('click', '.email-widget-remove-box', removeBox);

    $.get(data.baseUrl + `/../email-template?theme_name=${data.theme_name}&page_name=${data.template}`, applyEmailFunctions)


};

function widgetContainer(){
    const
        classUp = 'icon-angle-up',
        classDown = 'icon-angle-down',
        classClosed = 'widget-closed';

    $('.widget.box').each(function(){
        const
            widget = $(this),
            header = $('.widget-header', widget),
            icon = $('.icon-angle-up, .icon-angle-down', widget),
            content = $('.widget-content', widget);

        header.each(function(){
            const state = widgetState(widget);
            if (state === 'closed' || (!state && widget.hasClass(classClosed))) {
                widget.addClass(classClosed);
                icon.addClass(classUp).removeClass(classDown);
                content.hide()
            } else {
                widget.removeClass(classClosed);
                icon.addClass(classDown).removeClass(classUp);
                content.show()
            }
        });

        header.on('click', function toggleContainer(){
            if (widget.hasClass(classClosed)) {
                widget.removeClass(classClosed);
                icon.addClass(classDown).removeClass(classUp);
                content.slideDown();
                widgetState(widget, 'opened')
            } else {
                widget.addClass(classClosed);
                icon.addClass(classUp).removeClass(classDown);
                content.slideUp();
                widgetState(widget, 'closed')
            }
        });


    });

    function widgetState(widget, state){
        let widgetStates = JSON.parse(localStorage.getItem("widgetStates"));
        let id = widget.attr('id');
        if (!state) {
            if (id && widgetStates && widgetStates[id]) {
                return widgetStates[id];
            } else {
                return false
            }
        } else if (id){
            if (!widgetStates) {
                widgetStates = {};
            }
            widgetStates[id] = state;
            localStorage.setItem("widgetStates", JSON.stringify(widgetStates))
        }
    }
}

function rightColPosition(){
    let rightCol = $('.editor-right-col');

    let top = rightCol.offset().top;
    let height = $(window).height() - top - 100;

    rightCol.css({
        'height': height,
        'width': width
    });

    let width = rightCol.parent().width() - 30;
    $('> *', rightCol).css({
        'width': width
    });

    $(window).on('scroll', function(){
        rightCol.css({
            'top': $(window).scrollTop()
        })
    });
    $(window).on('resize', function(){
        let width = rightCol.parent().width() - 30;
        $('> *', rightCol).css({
            'width': width
        })
    });
}

function applyEmailFunctions(html) {
    let data = emailEditor.data;

    let editField = $('.edit-field');
    let subjectInput = $('input[name="subject"]');
    let subject = subjectInput.val();

    subjectInput.on('change', function(){
        subject = subjectInput.val()
    });

    editField.html(html);

    let emailTitle = $('.w-email-title', editField);
    emailTitle.html(subject);
    subjectInput.on('change keyup', function(){
        emailTitle.html($(this).val());
        $('body').trigger('changedEmail');
    });

    let emailContent = $('.w-email-content', editField);

    emailContent.html('');

    if (data.data && data.data != 'dW5kZWZpbmVk') {
        emailContent.html(atob(data.data));
    }

    emailContent.addClass('holder');

    $('.box').each(function(){
        if (!$('> .menu-widget', this).length){
            $(this).append(widgetMenuTemplate())
        }
    });

    $('.block_image_text, .block_text_image', editField).each(function(){
        resizeImageTextBlock($(this))
    });


    dragCopySortElements();


    addProducts().init();
    addImages(data.absUrl).init();
    addBlocks().init();
    $('body').trigger('changedEmail');

    if (data.styles[data.theme_name]) {
        $.each(data.styles[data.theme_name], function (index, value) {
            $(index, editField).css(value)
        })
    }
}

function removeBox(){
    let tr = emailEditor.data.tr;
    let box = $(this).closest('.box');
    let block = box.closest('.block');

    if (block.find('.box').length === 1) {
        if (block.hasClass('image-content')) {
            block.append(`<div class="image-area-holder">${tr.DROP_IMAGE_HERE}</div>`)
        }
        if (block.hasClass('product-content')) {
            block.append(`<div class="product-area-holder">${tr.DROP_PRODUCT_HERE}</div>`)
        }
    }
    box.remove();
    $('body').trigger('changedEmail');
}

function resizeImageTextBlock(block){
    let widgetItem = $(block).closest('.widget-item');
    let handle = 'e';
    if (block.hasClass('block_text_image')) {
        handle = 'w';
    }
    $('.image-content', block).resizable({
        handles: handle,
        resize: (e, ui) => {
            let tr = $(ui.element[0]).closest('tr');
            let width = tr.width();
            let spacer = (20/width) * 100;
            $(ui.element[0]).css({'left': 0});
            $('.image-cell', tr).css('width', (ui.size.width/width)*100 + '%');
            $('.text-cell', tr).css('width', 100 - (ui.size.width/width)*100 - spacer + '%');
            $('body').trigger('changedEmail');
        }
    });
}

function editTextImage(){
    let editField = $('.edit-field');
    let tr = emailEditor.data.tr;

    editField.on('click', '.text-cell', function(){
        let textCell = $(this).closest('.box').find('.text-cell');
        if (textCell && textCell.length === 0) {
            return true
        }
        let text = textCell.html();

        let html = `
<div>
    <textarea name="ckeditor2" id="" cols="30" rows="10" class="ckeditor2">${text}</textarea>
</div>
        `;

        let btnSave = $(`<span class="btn btn-save">${tr.IMAGE_SAVE}</span>`);

        let btnCancel = $(`<span class="btn btn-cancel">${tr.IMAGE_CANCEL}</span>`);

        let popup = draggablePopup(html, {
            heading: tr.EDIT_TEXT,
            buttons: [btnSave, btnCancel],
            beforeRemove: function(){
                textCell.html(text);
            }
        });
        btnSave.on('click', function(){
            popup.remove()
        });
        btnCancel.on('click', function(){
            textCell.html(text);
            popup.remove();
            $('body').trigger('changedEmail');
        });


        CKEDITOR.replace( 'ckeditor2', {
            toolbar: [
                { name: 'basicstyles',items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'TextColor' ] },
                { name: 'paragraph', items: [  'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
                { name: 'links', items: [ 'Link', 'Unlink' ] },
                { name: 'font', items: [ 'Font', 'FontSize' ] },
            ],
            on: {
                change: function( evt ) {
                    for (let instance in CKEDITOR.instances ) {
                        CKEDITOR.instances[instance].updateElement();

                        textCell.html($('.ckeditor2').val());
                        $('body').trigger('changedEmail');
                    }
                }
            }
        });
    })
}


function chooseTheme() {
    let data = emailEditor.data;

    $(`.themes .item[data-theme_name="${data.theme_name}"][data-template="${data.template}"]`).addClass('active');

    $('.themes .item').on('click', applyTheme);

    if (!data.theme_name) {
        $('.nav-tabs a[href="#themes"]').trigger('click');
        $('.themes .item:first').each(applyTheme)
    }

    function applyTheme(){
        data.theme_name = $(this).data('theme_name');
        data.template = $(this).data('template');

        $('.themes .item').removeClass('active');
        $(this).addClass('active');

        let saveData = $('.w-email-content').clone();
        $('.ui-resizable-handle', saveData).remove();
        data.data = btoa(saveData.html());

        $.get(data.baseUrl + `/../email-template?theme_name=${data.theme_name}&page_name=${data.template}`, applyEmailFunctions)
    }
}

function popUpPreview(){
    let data = emailEditor.data;
    $('.btn-preview').on('click', function(){
        let win = window.open('', "win", "width=900,height=800");

        let html = clearHtml($('.block.email').clone());
        win.document.body.innerHTML = '';
        win.document.write(html);

        $('body').on('changedEmail', function(){
            let html = clearHtml($('.block.email').clone());
            win.document.body.innerHTML = '';
            win.document.write(html);
        })
    })
}

function exportHtml(){
    $('.btn-export').on('click', function(){

        let html = clearHtml($('.block.email').clone());

        let element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(html));
        element.setAttribute('download', 'email.html');

        element.style.display = 'none';
        document.body.appendChild(element);

        element.click();

        document.body.removeChild(element);
    })
}





