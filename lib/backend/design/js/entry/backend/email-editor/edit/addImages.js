
import dragCopySortElements from "./dragCopySortElements";
import widgetMenuTemplate from "./widgetMenuTemplate";
import draggablePopup from "src/draggablePopup";

export default function(baseUrl){

    let imagesArea = $('.suggest-images');

    return {
        init: function(){
            $(".upload-image .btn:not(.dz-clickable)").dropzone({
                url: 'email-editor/upload',
                previewTemplate: '<span></span>',
                success: function(e, response){
                    response = JSON.parse(response);
                    imagesArea.prepend(`
                <div class="image-item box">
                    <img src="${baseUrl}../images/emails/thumbnails/${response[0].file}" class="email-image-thumbnail" style="max-width: 100%; max-height: 100%; width: auto; height: auto">
                    ${widgetMenuTemplate()}
                </div>`)
                }
            });


            $.get('email-editor/gallery', function(responseData){

                imagesArea.html('');
                responseData.forEach(function(img){
                    imagesArea.append(`
                <div class="image-item box">
                    <img src="${baseUrl}../images/emails/thumbnails/${img}" class="email-image-thumbnail" style="max-width: 100%; max-height: 100%; width: auto; height: auto">
                    ${widgetMenuTemplate()}
                </div>`)
                });

            }, 'json');

            dragCopySortElements();

            addImagePopup();
            editWidgetPopup();
        },
    }

}


function addImagePopup(){
    let editField = $('.edit-field');
    let data = emailEditor.data;
    let tr = emailEditor.data.tr;

    editField.on('click', '.image-area-holder', function(){
        let imageContent = $(this).closest('.image-content');

        let suggestImages = $('<div class="suggest-images suggest-images-popup"></div>');


        let btnCancel = $(`<span class="btn btn-cancel">${tr.IMAGE_CANCEL}</span>`);

        let popup = draggablePopup(suggestImages, {
            heading: tr.CHOOSE_IMAGE,
            buttons: ['<span></span>', btnCancel],
        });

        btnCancel.on('click', function(){
            popup.remove()
        });


        $.get('email-editor/gallery', function(responseData){

            responseData.forEach(function(img){
                suggestImages.append(`
                <div class="image-item box">
                    <img src="${data.baseUrl}/../images/emails/thumbnails/${img}" class="email-image-thumbnail" style="max-width: 100%; max-height: 100%; width: auto; height: auto">
                    ${widgetMenuTemplate()}
                </div>`)
            });

        }, 'json');

        suggestImages.on('click', '.image-item', function(){
            let newImg = $(this).clone();
            imageContent.html('').append(newImg);

            let src = $('img', newImg).attr('src');
            src = src.replace('thumbnails/', '');
            $('img', newImg).attr('src', src);

            if (data.styles[data.theme_name]) {
                $.each(data.styles[data.theme_name], function (index, value) {
                    $(index, editField).css(value)
                });
            }

            suggestImages.remove();
            popup.remove();
            dragCopySortElements();
            $('body').trigger('changedEmail');
        })

    })

}

function editWidgetPopup(){
    let tr = emailEditor.data.tr;
    $('.edit-field').on('click', '.image-item .email-widget-edit-box', function(){
        let editWidget = $(this).closest('.image-item');

        let html = $('<div></div>');

        html.append(imageLink(editWidget));
        html.append(imageAlt(editWidget));
        //html.append(functionForNewSettingRow3(editWidget));
        //html.append(functionForNewSettingRow4(editWidget));

        let btnSave = $(`<span class="btn btn-save">${tr.IMAGE_SAVE}</span>`);
        btnSave.on('click', function(){
            editWidget.trigger('save')// pass event to all setting functions
        });

        let btnCancel = $(`<span class="btn btn-cancel">${tr.IMAGE_CANCEL}</span>`);
        btnCancel.on('click', function(){
            editWidget.trigger('cancel')// pass event to all setting functions
        });

        let popup = draggablePopup(html, {
            heading: 'Edit image',
            buttons: [btnSave, btnCancel],
            beforeRemove: function(){
                editWidget.trigger('cancel')// pass event to all setting functions
            }
        });

        /* It is closing popup bu click on buttons*/
        btnSave.on('click', function(){
            setTimeout(function(){popup.remove()}, 0)
        });
        btnCancel.on('click', function(){
            setTimeout(function(){popup.remove()}, 0)
        });
    })
}

function imageLink(editWidget){
    let tr = emailEditor.data.tr;
    let a = $('a', editWidget);
    let img = $('img', editWidget);
    let href = '';

    if (a.length) {
        href = a.attr('href');
    }

    let row = $(`
      <div class="setting-row">
        <label for="">Link</label>
        <input name="" class="form-control">
      </div>
        `);

    let input = $('input', row);
    input.css({width: '300px'});

    input.val(href);

    let saveRow = function(){
        let value = input.val();

        if (a.length) {
            if (value) {
                a.attr('href', value)
            } else {
                img.unwrap()
            }
        } else if (value) {
            a = $('<a>', {'href': value});
            img.wrap(a)
        }
    };

    editWidget.off('save', saveRow).on('save', saveRow);

    let cancelRow = function(){
    };

    editWidget.off('cancel', cancelRow).on('cancel', cancelRow);

    return row;
}

function imageAlt(editWidget){
    let tr = emailEditor.data.tr;
    let img = $('img', editWidget);
    let alt = img.attr('alt');


    let row = $(`
      <div class="setting-row">
        <label for="">Alt text</label>
        <input name="" class="form-control">
      </div>
        `);

    let input = $('input', row);
    input.css({width: '300px'});

    input.val(alt);

    let saveRow = function(){
        let value = input.val();
        img.attr({
            'alt': value,
            'title': value,
        })
    };

    editWidget.off('save', saveRow).on('save', saveRow);

    let cancelRow = function(){};

    editWidget.off('cancel', cancelRow).on('cancel', cancelRow);

    return row;
}