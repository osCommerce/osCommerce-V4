import draggablePopup from "src/draggablePopup";

export default function exportBlock(){
    const id = $(this).closest('div[id]').attr('id');
    const url = new URL(window.entryData.mainUrl + '/design/export-block');
    url.searchParams.set('id', id);
    url.searchParams.set('theme_name', window.entryData.theme_name);

    const $html = $(`<div><input type="text" name="block_name" class="form-control"/></div>`);
    const $cancel = $(`<span class="btn">${window.entryData.tr.IMAGE_CANCEL}</span>`);
    const $export = $(`<span class="btn btn-primary">${window.entryData.tr.TEXT_EXPORT}</span>`);

    let popup = draggablePopup($html, {
        heading: window.entryData.tr.TEXT_NAME_THIS_BLOCK,
        name: 'export',
        resizable: {
            edges: {
                top: false,
                left: false,
                right: false,
                bottom: false,
            }
        },
        buttons: [$export, $cancel]
    });

    $cancel.on('click', function(){
        popup.trigger('close');
    });
    $export.on('click', function(){
        url.searchParams.set('block_name', $('input', $html).val());
        popup.trigger('close');
        window.location = url.toString();
    });
}