import fileHolder from './file-holder';

export default function ($box, options) {
    const $fileManager = $('.file-manager', $box)
    let keyShow = 0;
    let keyHide = 0;

    options.uploadprogress = function(file, progress, bytesSent) {
        if (file.previewElement) {
            var byteInfo = '';
            if (bytesSent > 1000000) {
                byteInfo = Math.round(bytesSent/100000)/10 + 'MB';
            } else if (bytesSent > 1000) {
                byteInfo = Math.round(bytesSent/100)/10 + 'KB';
            } else {
                byteInfo = Math.round(bytesSent) + 'B';
            }
            var percent = Math.round(progress) + '%';

            $box.find('.upload-progress').css('display', 'flex');
            $box.find('.upload-progress-bar-content').width(progress + '%');
            $box.find('.upload-progress-val').html(byteInfo);
            $box.find('.upload-progress-percent').html(percent);
        }
    };

    options.clickable = $('.btn-from-computer', $box).get(0);
    options.previewTemplate = '<div class="upload-tmp">';

    options.success = function (e, data) {
        fileHolder($box, options, entryData.baseUrl + 'uploads/' + e.name, e.name, e.type.split('/')[0]);
    };

    $fileManager.dropzone(options);

    $fileManager.on('dragenter', function(e){
        keyShow++;
        if (keyShow === 2) {
            $fileManager.append(`<div class="over-message">${entryData?.tr?.TEXT_DROP_FILES}</div>`);
        }
        keyHide = 0;
    });
    $fileManager.on('dragleave', function(e){
        keyHide++;
        if (keyHide === 2) {
            $('.over-message', $fileManager).remove();
        }
        keyShow = 0;
    });
    $fileManager.on('drop', function(e){
        $('.over-message', $fileManager).remove();
        keyShow = 0;
    });
}