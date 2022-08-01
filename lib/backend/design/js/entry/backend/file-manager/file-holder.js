import crop from "./crop";
import draggablePopup from "src/draggablePopup";


export default function ($box, options, src, value = '') {

    const $holderImage = $(`<div class="uploaded-image"></div>`)

    let $file = '';
    const $name = $(`<input type="hidden" name="${options.name}" class="file-name" value="${value ? '' : options.value}">`)
    const $uploaded = $(`<input type="hidden" name="${options.upload}" class="file-uploaded" value="${value}">`)
    const $delete = $(`<input type="hidden" name="${options.delete}" value="0">`)
    const $fileButtonHolder = $(`<span class="file-button-holder"></span>`)
    const $unlink = $(`<span class="file-unlink" title="Unlink"></span>`)
    const $remove = $(`<span class="file-remove" title="Remove"></span>`)

    if (options.type == 'image') {
        $file = $(`<img src="${src}">`)
    } else if (options.type == 'video') {
        $file = $(`<video class="video-js" width="200px" height="150px" controls>
                      <source src="${src}" class="show-image">
                   </video>`)
    } else {
        $file = $('<div class="file-uploaded-item "></div>')
    }

    $holderImage.append($file)
    $holderImage.append($name)
    $holderImage.append($uploaded)
    $holderImage.append($delete)
    $holderImage.append($fileButtonHolder)
    $fileButtonHolder.append($unlink)
    $fileButtonHolder.append($remove)

    $unlink.on('click', function(){
        $name.val('')
        $uploaded.val('')
        $file.remove()
        $fileButtonHolder.remove()
    })

    $remove.on('click', function(){
        $name.val('')
        $uploaded.val('')
        $delete.val('1')
        $file.remove()
        $fileButtonHolder.remove()
    })

    $('.uploaded-wrap', $box).html($holderImage)

    if (options.type != 'image') {
        return null
    }

    const $resize = $(`<span class="file-resize" title="Resize"></span>`)
    $fileButtonHolder.append($resize)
    $resize.on('click', function () {
        let width = 1200
        if ($(window).width() < 1200) {
            width = $(window).width() - 20
        }
        let height = 800
        if ($(window).height() < 800) {
            height = $(window).height() - 20
        }
        let top = ($(window).height() - height) / 2

        const $html = $(`<div style="position: relative; height: ${height - 140}px"></div>`)
        $html.append($file.clone())
        let $popup = draggablePopup($html, {
            name: 'edit-image',
            className: 'edit-image-popup',
            heading: entryData.tr.TEXT_EDIT_IMAGE,
            buttons: [''],
            resizable: false,
            width,
            height,
            top
        });
        crop($box, options, $popup)
    })
}