import crop from './crop';
import draggablePopup from 'src/draggablePopup';
import imageStretch from './image-stretch';
import editor from './editor';


export default async function fileHolder($box, options, src, value = '', type = '') {

    const $holderImage = $(`<div class="uploaded-image"></div>`);

    let $file = '';
    const $name = $(`<input type="hidden" name="${options.name}" class="file-name" value="${value ? '' : options.value || ''}">`);
    const $uploaded = $(`<input type="hidden" name="${options.upload}" class="file-uploaded" value="${value}">`);
    const $delete = $(`<input type="hidden" name="${options.delete}" class="file-delete" value="${!options.unlink && value ? '1' : '0'}">`);
    const $fileButtonHolder = $(`<span class="file-button-holder"></span>`);
    const $unlink = $(`<span class="file-unlink" title="Unlink"></span>`);
    const $remove = $(`<span class="file-remove" title="Remove"></span>`);
    const $createSvg = $(`<span class="btn btn-create-svg">Create SVG</span>`);

    if (!type) {
        await fetch(src).then(function (response){
            const contentType = response.headers.get('content-type');
            type = (contentType ? contentType.split('/')[0] : 'image');
        });
    }

    if (type == 'image') {
        $file = $(`<img src="${src}">`);
    } else if (type == 'video') {
        $file = $(`<video class="video-js" width="200px" height="145px" controls>
                      <source src="${src}" class="show-image">
                   </video>`);
    } else {
        $file = $(`<div class="file-uploaded-item item-type-${type}"></div>`);
    }

    if (src) {
        $holderImage.append($file);
        $holderImage.append($fileButtonHolder);
    }
    $holderImage.append($name);
    $holderImage.append($uploaded);
    $holderImage.append($delete);
    $fileButtonHolder.append($remove);

    if (options.unlink) {
        $fileButtonHolder.append($unlink);
        $unlink.on('click', function(){
            $name.val('');
            $uploaded.val('');
            $file.remove();
            $fileButtonHolder.remove();
            $('.file-manager-buttons', $box).append($createSvg);
        });
    }

    if (options.edit) {
        const $edit = $(`<span class="file-edit" title="Edit SVG"></span>`);
        $fileButtonHolder.append($edit);
        if (!src && !value) {
            $('.file-manager-buttons', $box).append($createSvg);
        } else {
            $createSvg.remove();
        }
        const edit = () => editor($box, options, src, value);
        $createSvg.on('click', edit);
        $edit.on('click', edit);
    }

    imageStretch(options, $file, $fileButtonHolder, $holderImage);

    $remove.on('click', function(){
        $name.val('').trigger('change');
        $uploaded.val('').trigger('change');
        $delete.val('1').trigger('change');
        $file.remove();
        $fileButtonHolder.remove();
        $('.file-manager-buttons', $box).append($createSvg);
    });

    $('.uploaded-wrap', $box).html($holderImage);
    $name.trigger('change');
    $uploaded.trigger('change');
    $delete.trigger('change');

    if (type != 'image' || src.slice(-3) == 'svg') {
        return null;
    }

    const $resize = $(`<span class="file-resize" title="Resize"></span>`);
    $fileButtonHolder.append($resize);
    $resize.on('click', function () {
        let width = 1200;
        if ($(window).width() < 1200) {
            width = $(window).width() - 20;
        }
        let height = 800;
        if ($(window).height() < 800) {
            height = $(window).height() - 20;
        }
        let top = ($(window).height() - height) / 2;

        const $html = $(`<div style="position: relative; height: ${height - 140}px"></div>`);
        $html.append($file.clone());
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
        crop($box, options, $popup);
    });
}