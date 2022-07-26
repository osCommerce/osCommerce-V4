import productTemplate from "./productTemplate";
import dragCopySortElements from "./dragCopySortElements";
import draggablePopup from "src/draggablePopup";

export default function(){

    let searchInput = $('.product-name');
    let platformSelect = $('select.platform');
    let suggest = $('.suggest-products');

    return {
        init: function(){

            $('.suggest-products, .edit-field').on('click', 'a', function(e){
                e.preventDefault();
                return false;
            });

            searchInput.on('keyup', function(e){
                $.get('index/search-suggest', {
                    platform_id: platformSelect.val(),
                    keywords: searchInput.val(),
                    no_click: true,
                    json: true
                }, function(data){
                    suggest.html('');
                    data.forEach(function(product){
                        suggest.append(productTemplate(product))
                    })
                }, 'json')
            });

            dragCopySortElements();

            editWidget();

            addProductPopup();
        },
    }

}


function editWidget(){
    let tr = emailEditor.data.tr;
    $('.edit-field').on('click', '.block_product_row .email-widget-edit-box', function(){
        let productsRow = $(this).closest('.block_product_row');

        let html = $('<div></div>');

        html.append(productsInRow(productsRow));
        //html.append(functionForNewSettingRow2(productsRow));
        //html.append(functionForNewSettingRow3(productsRow));
        //html.append(functionForNewSettingRow4(productsRow));

        let btnSave = $(`<span class="btn btn-save">${tr.IMAGE_SAVE}</span>`);
        btnSave.on('click', function(){
            productsRow.trigger('save')// pass event to all setting functions
        });

        let btnCancel = $(`<span class="btn btn-cancel">${tr.IMAGE_CANCEL}</span>`);
        btnCancel.on('click', function(){
            productsRow.trigger('cancel')// pass event to all setting functions
        });

        let popup = draggablePopup(html, {
            heading: tr.EDIT_PRODUCTS_ROW,
            buttons: [btnSave, btnCancel],
            beforeRemove: function(){
                productsRow.trigger('cancel')// pass event to all setting functions
            }
        });

        /* It is closing popup bu click on buttons*/
        btnSave.on('click', function(){
            popup.remove()
        });
        btnCancel.on('click', function(){
            popup.remove()
        });
    })
}

function productsInRow(productsRow){
    let tr = emailEditor.data.tr;
    let cols = productsRow.data('cols');
    let trRow = $('tr', productsRow);

    let tdBackup = $('td', productsRow).clone();
    let colsBackup = cols;

    let td = [];
    $('td', productsRow).each(function(i){
        if (i % 2 === 0){
            td.push($(this).clone());
        }
    });

    let row = $(`
      <div class="setting-row">
        <label for="">${tr.PRODUCTS_IN_ROW}</label>
        <select name="" class="form-control">
          <option value="1">1</option>
          <option value="2">2</option>
          <option value="3">3</option>
          <option value="4">4</option>
          <option value="5">5</option>
        </select>
      </div>
        `);

    let separator = $('<td style="width:2.5%">&nbsp;</td>');
    let emptyField = $(`<td class="product-content block"><div class="product-area-holder">${tr.DROP_PRODUCT_HERE}</div></td>`);

    $('select', row).val(cols).on('change', function(){

        cols = $(this).val();
        productsRow.attr('data-cols', cols);
        productsRow.data('cols', cols);

        let width = Math.floor((100 - ((cols - 1) * 2.5)) / cols);

        trRow.html('');
        for (let i = 0; i < cols; i++){
            if (i !== 0){
                trRow.append(separator.clone())
            }
            let tdTmp;
            if (td[i] && td[i] instanceof $){
                tdTmp = td[i];
            } else {
                tdTmp = emptyField;
            }
            tdTmp.css('width', width + '%');
            trRow.append(tdTmp.clone())
        }
        dragCopySortElements();
        $('body').trigger('changedEmail');
    });

    productsRow.on('save', function(){
    });

    productsRow.on('cancel', function(){
        trRow.html('');
        trRow.append(tdBackup);
        productsRow.attr('data-cols', colsBackup);
        productsRow.data('cols', colsBackup);

        dragCopySortElements();
        $('body').trigger('changedEmail');
    });

    return row;
}

function addProductPopup(){
    let editField = $('.edit-field');

    editField
        .off('click', '.product-area-holder', clickAreaHolder)
        .on('click', '.product-area-holder', clickAreaHolder)

}

function clickAreaHolder(){
    let editField = $('.edit-field');
    let data = emailEditor.data;
    let tr = emailEditor.data.tr;

    let productContent = $(this).closest('.product-content');

    let suggestProductsPopup = $('<div class="suggest-products-popup"></div>');
    let searchInput = $(`<input type="" class="product-name form-control" placeholder="${tr.START_TYPING_PRODUCT_NAME}">`);
    let suggestProducts = $('<div class="suggest-products"></div>');
    let platformSelect = $('select.platform').clone();

    suggestProductsPopup.append(platformSelect);
    suggestProductsPopup.append(searchInput);
    suggestProductsPopup.append(suggestProducts);

    let btnCancel = $(`<span class="btn btn-cancel">${tr.IMAGE_CANCEL}</span>`);

    let popup = draggablePopup(suggestProductsPopup, {
        heading: tr.CHOOSE_PRODUCT,
        buttons: ['<span></span>', btnCancel],
    });

    btnCancel.on('click', function(){
        popup.remove()
    });


    searchInput.on('keyup', function(e){
        $.get('index/search-suggest', {
            platform_id: platformSelect.val(),
            keywords: searchInput.val(),
            no_click: true,
            json: true
        }, function(data){
            suggestProducts.html('');
            data.forEach(function(product){
                suggestProducts.append(productTemplate(product))
            })
        }, 'json')
    });

    suggestProducts.on('click', 'a', function(e){
        e.preventDefault();
    });
    suggestProducts.on('click', '.product-item', function(e){
        let newProduct = $(this).clone();
        productContent.html('').append(newProduct);

        if (data.styles[data.theme_name]) {
            $.each(data.styles[data.theme_name], function (index, value) {
                $(index, editField).css(value)
            });
        }

        popup.remove();
        $('body').trigger('changedEmail');
        dragCopySortElements();
    });
}
