import Cropper from 'cropperjs';
import CropperCss from 'cropperjs/dist/cropper.css';

export default function ($box, options, $popup) {

    let aspectRatio = false;
    if (options.width && options.height) {
        aspectRatio = options.width / options.height;
    }

    const $popupButtons = $('.popup-buttons', $popup);

    const $leftButtons = $(`<div class="left-buttons"></div>`).appendTo($popupButtons);
    const $centerButtons = $(`<div class="center-buttons"></div>`).appendTo($popupButtons);
    const $rightButtons = $(`<div class="right-buttons"></div>`).appendTo($popupButtons);

    const $sideSpaceColor = $(`<label class="sile-space-color" title="${entryData.tr.TEXT_AFTER_SAVING}">
                                    <span class="title">${entryData.tr.TEXT_CHOOSE_SIDE_COLOR}:</span>
                                    <input type="color" name="" value="#ffffff" class="form-control" />
                                </label>`).appendTo($centerButtons);

    const $cancelBtn = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`).appendTo($leftButtons);
    const $saveBtn = $(`<span class="btn btn-confirm btn-save">${entryData.tr.IMAGE_SAVE}</span>`).appendTo($rightButtons);
    const $zoomInfo = $(`<span class="zoom-info">+</span>`).appendTo($centerButtons);
    const $zoomIn = $(`<span class="btn btn-zoom-in">+</span>`).appendTo($centerButtons);
    const $zoomOut = $(`<span class="btn btn-zoom-out">-</span>`).appendTo($centerButtons);
    const $alignBorders = $(`<span class="btn btn-align-borders">${entryData.tr.TEXT_ALIGN_BORDERS}</span>`).appendTo($centerButtons);
    const $poorQuality = $(`<label class="poor-quality">
                                <input type="checkbox">
                                <span>${entryData.tr.TEXT_POOR_QUALITY}</span>
                            </label>`).appendTo($centerButtons);

    let _init = true;
    const $image = $('img', $popup);
    const cropper = new Cropper($image.get(0), {
        aspectRatio,
        dragMode: 'move',
        zoom(event) {
            $zoomInfo.html('Zoom: ' + Math.round(event.detail.ratio * 100) + '%');
        },
        crop(event) {
            if (_init) {
                _init = false;
                cropper.zoom(-0.1);
            }

            const canvasData = cropper.getCanvasData();
            const cropBoxData = cropper.getCropBoxData();
            const scale = canvasData.naturalWidth / canvasData.width;

            //console.log(cropBoxData.width * scale, options.width);
            if (cropBoxData.width * scale < options.width && !$('input', $poorQuality).prop('checked')) {
                cropper.setCropBoxData({width: Math.ceil(options.width*100 / scale)/100});
                $poorQuality.css('color', '#f00');
                setTimeout(() => $poorQuality.css('color', ''), 500);
            }

            if (
                cropBoxData.top < canvasData.top ||
                cropBoxData.left < canvasData.left ||
                cropBoxData.top + cropBoxData.height > canvasData.top + canvasData.height ||
                cropBoxData.left + cropBoxData.width > canvasData.left + canvasData.width
            ) {
                $sideSpaceColor.css('visibility', 'visible');
            } else {
                $sideSpaceColor.css('visibility', 'hidden');
            }

            if (
                Math.abs(cropBoxData.top - canvasData.top) < 10 ||
                Math.abs(cropBoxData.top + cropBoxData.height - canvasData.top - canvasData.height) < 10 ||
                Math.abs(cropBoxData.left - canvasData.left) < 10 ||
                Math.abs(cropBoxData.left + cropBoxData.width - canvasData.left - canvasData.width) < 10
            ) {
                $alignBorders.css('visibility', 'visible');
            } else {
                $alignBorders.css('visibility', 'hidden');
            }
        },
    });

    $zoomIn.on('click', () => cropper.zoom(0.1));
    $zoomOut.on('click', () => cropper.zoom(-0.1));
    $cancelBtn.on('click', () => $popup.trigger('close'));

    $alignBorders.on('click', async function () {
        let canvasData = cropper.getCanvasData();
        let cropBoxData = cropper.getCropBoxData();
        if (Math.abs(cropBoxData.top - canvasData.top) < 10) {
            cropper.setCropBoxData({top: canvasData.top});
            canvasData = cropper.getCanvasData();
            cropBoxData = cropper.getCropBoxData();
        }

        if (Math.abs(cropBoxData.top + cropBoxData.height - canvasData.top - canvasData.height) < 10) {
            cropper.setCropBoxData({height: canvasData.top + canvasData.height - cropBoxData.top});
            canvasData = cropper.getCanvasData();
            cropBoxData = cropper.getCropBoxData();
        }

        if (Math.abs(cropBoxData.left - canvasData.left) < 10) {
            cropper.setCropBoxData({left: canvasData.left});
            canvasData = cropper.getCanvasData();
            cropBoxData = cropper.getCropBoxData();
        }

        if (Math.abs(cropBoxData.left + cropBoxData.width - canvasData.left - canvasData.width) < 10) {
            cropper.setCropBoxData({width: canvasData.left + canvasData.width - cropBoxData.left});
        }
    });

    $saveBtn.on('click', function () {
        const canvasData = cropper.getCanvasData();
        const cropBoxData = cropper.getCropBoxData();
        const scale = canvasData.naturalWidth / canvasData.width;
        const top = (cropBoxData.top - canvasData.top) * scale;
        const left = (cropBoxData.left - canvasData.left) * scale;
        const width = cropBoxData.width * scale;
        const height = cropBoxData.height * scale;
        const src = $image.attr('src');
        const imgWidth = options.width || '';
        const imgHeight = options.height || '';
        const color = $('input', $sideSpaceColor).val();

        const $preloader = $('<div class="hided-box-holder"><div class="preloader"></div></div>');
        $box.addClass('hided-box').append($preloader);
        $popup.trigger('close');
        $.post('upload/crop-image', {top, left, width, height, src, imgWidth, imgHeight, color}, function (data, status) {
            $preloader.remove();
            $box.removeClass('hided-box');
            if (status != 'success') {
                alertMessage('Request error.', 'alert-message');
                return null;
            }
            if (data.error) {
                alertMessage(data.error, 'alert-message');
            }
            if (data.src) {
                $('.uploaded-image img', $box).attr('src', data.src);
                const from = data.src.lastIndexOf('/') + 1;
                const imgName = data.src.slice(from);
                $('input.file-uploaded', $box).val(imgName).trigger('change');
                if (!options.unlink) {
                    $('input.file-delete', $box).val(1).trigger('change');
                }
            }
        }, 'json').fail(function() {
            $preloader.remove();
            $box.removeClass('hided-box');
            alertMessage('Request error.', 'alert-message');
        });
    });

}

