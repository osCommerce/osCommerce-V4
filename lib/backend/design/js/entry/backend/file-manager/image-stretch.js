import draggablePopup from 'src/draggablePopup';

export default function (options, $file, $fileButtonHolder, $holderImage) {
    if (!options.width || !options.height || !options.positionName || !options.fitName) {
        return null;
    }
    const $positionBtn = $(`<span class="image-position" title="Image stretch/position in banner box"></span>`);
    const $position = $(`<input type="hidden" name="${options.positionName}" class="img-position" value="${options.positionValue}">`);
    const $fit = $(`<input type="hidden" name="${options.fitName}" class="img-fit" value="${options.fitValue}">`);

    $holderImage.append($position);
    $holderImage.append($fit);
    $fileButtonHolder.append($positionBtn);

    let timeout = false;
    const resizeObserver = new ResizeObserver((entries) => {
        if (timeout) {
            return null;
        }
        timeout = true;
        setTimeout(() => timeout = false, 500);

        const $uploadedWrap = $file.closest('.uploaded-wrap');
        const maxWidth = $uploadedWrap.width();
        const maxHeight = $uploadedWrap.height();

        if (maxWidth && maxHeight) {
            const p = options.width / options.height;
            let width = maxWidth;
            let height = maxHeight;
            if (maxWidth / p > maxHeight) {
                width = maxHeight * p;
            } else {
                height = maxWidth / p;
            }
            $file.css({width, height});
        }
    });
    resizeObserver.observe($file[0]);

    const $html = $(`
        <div class="fit-position-popup" style="display: none">
            <div class="row m-b-2 align-items-center">
                <label class="col-5 align-right">${entryData.tr.IMAGE_FIT}:</label>
                <div class="col-7">
                    <select name="_fit" class="form-control">
                        <option value="cover">${entryData.tr.IMAGE_FIT_COVER}</option>
                        <option value="fill">${entryData.tr.IMAGE_FIT_FILL}</option>
                        <option value="contain">${entryData.tr.IMAGE_FIT_CONTAIN}</option>
                        <option value="none">${entryData.tr.IMAGE_FIT_NONE}</option>
                        <option value="scale-down">${entryData.tr.IMAGE_FIT_SCALE_DOWN}</option>
                    </select>
                </div>
            </div>
            <div class="row align-items-center">
                <label class="col-5 align-right">${entryData.tr.IMAGE_POSITION}:</label>
                <div class="col-7">
                    <select name="_position" class="form-control">
                        <option value="">${entryData.tr.TEXT_MIDDLE_CENTER}</option>
                        <option value="left top">${entryData.tr.TEXT_TOP_LEFT}</option>
                        <option value="center top">${entryData.tr.TEXT_TOP_CENTER}</option>
                        <option value="right top">${entryData.tr.TEXT_TOP_RIGHT}</option>
                        <option value="left center">${entryData.tr.TEXT_MIDDLE_LEFT}</option>
                        <option value="right center">${entryData.tr.TEXT_MIDDLE_RIGHT}</option>
                        <option value="left bottom">${entryData.tr.TEXT_BOTTOM_LEFT}</option>
                        <option value="center bottom">${entryData.tr.TEXT_BOTTOM_CENTER}</option>
                        <option value="right bottom">${entryData.tr.TEXT_BOTTOM_RIGHT}</option>
                    </select>
                </div>
            </div>
        </div>
    `);

    const $selectPosition = $('select[name="_position"]', $html);
    const $selectFit = $('select[name="_fit"]', $html);
    $holderImage.append($html);

    if (options.positionValue) {
        $selectPosition.val(options.positionValue);
    }
    if (options.fitValue) {
        $selectFit.val(options.fitValue);
    }
    $file.css({objectFit:options.fitValue || 'cover'});

    $selectPosition.on('change', function(){
        $position.val($(this).val());
        $file.css({objectPosition:$(this).val()});
    }).trigger('change');
    $selectFit.on('change', function(){
        $fit.val($(this).val());
        $file.css({objectFit:$(this).val()});
    }).trigger('change');

    $positionBtn.on('click', function() {
        $html.show();
        setTimeout(() => $('body').on('click', closePopUp), 0);
    });

    function closePopUp(e) {
        if (!$(e.target).closest('.fit-position-popup').length) {
            $html.hide();
            $('body').off('click', closePopUp);
        }
    }
}