import addBlocks from "./addBlocks";


export default dragCopySortElements;

function dragCopySortElements(){
    let data = emailEditor.data;
    let tr = data.tr;

    let options = {
        sourceArea: '.suggest-products, .widgets-list, .suggest-images',
        sourceItems: '.box',
        sourceHandle: '',
        destinationArea: '.w-email-content, .w-email-content .block',
        destinationItems: '> .box',
        destinationHandle: '',
        receive: function(event, ui){},
    };


    let copyHelper;
    let destinationArea = $(options.destinationArea);
    let sourceArea = $(options.sourceArea);

    destinationArea.sortable({
        connectWith: destinationArea,
        items: options.destinationItems,
        cursor: 'move',
        handle: options.destinationHandle,
        update: function( event, ui ) {
            $('.original-placeholder').remove();
        },
        revert: true,
        tolerance: "pointer",
        scroll: false,
        sort: function(){
        },
        start: function (e, ui) {
            let clone = ui.item.clone();
            clone.addClass('original-placeholder')
            ui.item.parent().append(clone);
        },
        stop: function() {
            $('.original-placeholder').remove();
            $('body').trigger('changedEmail');
        },
        over: function(e,ui){

            let item = $(ui.item[0]);
            let place = $(ui.placeholder).parent();

            if (isAllowedPlace(item, place)){
                $(ui.placeholder).show().css('width', '');
            } else {
                $(ui.placeholder).hide();
            }

        },
        receive: function(e,ui) {
            copyHelper= null;

            let item = $(ui.item[0]);
            let place = $(this);

            if (!isAllowedPlace(item, place)){
                $(ui.item).remove();
            }

            let editField = $('.edit-field');

            if ($(ui.item[0]).hasClass('widget-item')){
                applyStyles();

                addBlocks().resizeCell(ui.item[0]);
                dragCopySortElements();
            }
            if ($(ui.item[0]).hasClass('image-item')){
                let src = $('img', ui.item).attr('src');
                src = src.replace('thumbnails/', '');
                $('img', ui.item).attr('src', src);
                $('img', ui.item).closest('.image-content').find('.image-area-holder').remove();

                applyStyles();
            }
            if ($(ui.item[0]).hasClass('product-item')){
                $('img', ui.item).closest('.product-content').find('.product-area-holder').remove();
                $('.product-content').each(function(){
                    if ($('div', this).length === 0) {
                        $(this).append(`<div class="product-area-holder">${tr.DROP_PRODUCT_HERE}</div>`)
                    }
                });

                applyStyles();
            }


            $('body').trigger('changedEmail');

            options.receive(e,ui)
        }
    });

    sourceArea.sortable({
        handle: options.sourceHandle,
        connectWith: destinationArea,
        items: options.sourceItems,
        forcePlaceholderSize: false,
        helper: function(e,li) {
            copyHelper = li.clone().insertAfter(li);
            return li.clone();
        },
        stop: function() {
            copyHelper && copyHelper.remove();
        },
        update: function( event, ui){
            if (ui.item.parent().hasClass('box-group')){
                return false;
            }
        }
    });
}

function isAllowedPlace(item, place){

    if (item.hasClass('widget-item')){
        if (place.hasClass('image-content') || place.hasClass('product-content')){
            return false;
        }
    }
    if (item.hasClass('image-item')) {
        if (place.hasClass('w-email-content') || place.hasClass('product-content')){
            return false;
        }
    }
    if (item.hasClass('product-item')){
        if (place.hasClass('w-email-content') || place.hasClass('image-content')){
            return false;
        }
    }

    return true;
}

function applyStyles(){
    let editField = $('.edit-field');
    let data = emailEditor.data;
    if (data.styles[data.theme_name]) {
        $.each(data.styles[data.theme_name], function (index, value) {
            $(index, editField).css(value)
        });
    }
}