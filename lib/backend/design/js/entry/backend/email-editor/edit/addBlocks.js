import dragCopySortElements from "./dragCopySortElements";
import draggablePopup from "src/draggablePopup";

export default function(){

    return {
        init: function(){
            let editField = $('.edit-field');

            $('.block_block', editField).each(function(){
                resizeCell(this);
            });

            editWidgetPopup();
            dragCopySortElements();

        },
        resizeCell: resizeCell,
    }
}

function resizeCell(block){
    let cellsWidth = 0;
    $('.ui-resizable-handle', block).remove();
    let cell = $('td:not(:last) > .block-content', block);
    cell.resizable({
        handles: 'e',
        start: (e, ui) => {
            let td1 = $(ui.element[0]).closest('td');
            let td2 = $('+ td', td1);
            cellsWidth = td1.width() + td2.width();
        },
        resize: (e, ui) => {
            let tr = $(ui.element[0]).closest('tr');
            let td1 = $(ui.element[0]).closest('td');
            let td2 = $('+ td', td1);
            let width = tr.width();
            $(ui.element[0]).css({'left': 0});
            $(ui.element[0]).css('width', '');

            if (width) {
                td1.css('width', (ui.size.width / width) * 100 + '%');
            }
            if (cellsWidth) {
                td2.css('width', (cellsWidth / width) * 100 - (ui.size.width / width) * 100 + '%');
            }
            $('body').trigger('changedEmail');
        },
        stop: (e, ui) => {
            $(ui.element[0]).css('width', '')
        },
    });
}

function editWidgetPopup(){
    let tr = emailEditor.data.tr;
    $('.edit-field').on('click', '.block_block > .menu-widget > .email-widget-edit-box', function(){
        let editWidget = $(this).closest('.block_block');

        let html = $('<div></div>');

        html.append(cellsInRow(editWidget));
        //html.append(functionForNewSettingRow2(editWidget));
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
            heading: 'Edit Block',
            buttons: [btnSave, btnCancel],
            beforeRemove: function(){
                editWidget.trigger('cancel')// pass event to all setting functions
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

function cellsInRow(editWidget){
    let tr = emailEditor.data.tr;
    let cols = editWidget.data('cols');
    let trRow = $('tr', editWidget);

    let tdBackup = $('td', editWidget).clone();
    let colsBackup = cols;

    let td = [];
    $('td', editWidget).each(function(i){
        td.push($(this).clone());
    });

    let row = $(`
      <div class="setting-row">
        <label for="">Cells in row</label>
        <input type="number" class="form-control"/>
      </div>
        `);

    let emptyField = $(`<td><div class="block-content widget-box-content block"></div></td>`);

    $('input', row).val(cols).on('change', function(){

        cols = $(this).val();
        editWidget.attr('data-cols', cols);
        editWidget.data('cols', cols);

        let width = Math.floor(100 / cols);

        trRow.html('');
        for (let i = 0; i < cols; i++){
            let tdTmp;
            if (td[i] && td[i] instanceof $){
                tdTmp = td[i];
            } else {
                tdTmp = emptyField;
            }
            tdTmp.css('width', width + '%');
            trRow.append(tdTmp.clone())
        }

        resizeCell(editWidget);

        $('body').trigger('changedEmail');
    });

    editWidget.on('save', function(){
        dragCopySortElements();
        editWidget.off('save');
        editWidget.off('cancel');
    });

    editWidget.on('cancel', function(){
        trRow.html('');
        trRow.append(tdBackup);
        editWidget.attr('data-cols', colsBackup);
        editWidget.data('cols', colsBackup);

        resizeCell(editWidget);
        dragCopySortElements();
        $('body').trigger('changedEmail');
        
        editWidget.off('save');
        editWidget.off('cancel');
    });

    return row;
}
