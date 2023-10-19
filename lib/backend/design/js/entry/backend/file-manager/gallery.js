import fileHolder from "./file-holder";
import draggablePopup from "src/draggablePopup";

export default function ($box, options) {
    $('.btn-from-gallery', $box).on('click', function(){
        const directory = [{title: 'Gallery', name: 'main'}];
        const fileTypes = options.acceptedFiles || '';

        let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);
        let $content = $(`<div class="gallery-content"><div class="preloader"></div></div>`);
        let $search = $(`<input type="text" class="form-control search" placeholder="Search">`);
        let $breadcrumbs = $(`<div class="g-breadcrumbs"></div>`);

        let popup = draggablePopup($content, {
            className: 'gallery-popup',
            name: 'gallery',
            heading: $breadcrumbs,
            buttons: [$search, $btnCancel],
        });

        $btnCancel.on('click', () => popup.trigger('close'));

        getContent(directory);
        breadcrumbsAction();

        function getContent(directory) {
            $.get(entryData.baseUrl + 'design/gallery', { fileTypes, directory }, function(response){
                $search.off('keyup');
                $content.html('');
                if (directory.length == 1) {
                    $content.append(`
                    <div class="upload-info">Drop files here or upload from computer to add files in gallery</div>`);
                    if ($content[0].dropzone) {
                        $content[0].dropzone.destroy();
                        //$content.removeData('dropzone');
                    }
                    $content.dropzone({
                        url: options.url + '?folder=images',
                        clickable: '.upload-info',
                        acceptedFiles: options.acceptedFiles,
                        previewsContainer: '',
                        success: function(e) {
                            const typeSplit = e.type.split('/');
                            let type = '';
                            if (typeSplit[1] == 'svg') {
                                type = 'svg';
                            } else if (typeSplit[0] == 'image' || typeSplit[0] == 'video') {
                                type = typeSplit[0];
                            }
                            $('.item-directory:last', $content).after(fileItem({
                                fileName: e.name,
                                file: e.name,
                                upload: true,
                                type
                            }));
                        },
                        sending: function() {
                        },
                        error: function() {
                            alertMessage('<div class="alert-message">Error</div>');
                        },
                    });
                }
                if (response.directories) {
                    response.directories.forEach(function (item) {
                        $content.append(directoryItem(item));
                    });
                }
                if (response.files) {
                    response.files.forEach(function (item) {
                        $content.append(fileItem(item));
                    });
                }
            }, 'json');
        }

        function fileItem(data) {
            const $item = $(`<div class="item item-file type-${data.type}" data-name="${data.file}">
                                ${directory.length == 1 ? '<div class="remove-holder"><div class="btn-remove" title="Remove"></div></div>' : ''}
                                <div class="image"></div>
                                <div class="title">${data.fileName}</div>
                             </div>`);
            let $image = '';
            switch (data.type) {
                case 'image':
                    if (directory[1] && directory[1].name == 'themes' || data.upload) {
                        $image = $(`<img src="${entryData.baseUrl}../images/${data.file}">`);
                    } else {
                        $image = $(`<img src="${entryData.baseUrl}../images/thumbnails/${data.file}">`);
                        let fuse = true;
                        $image.on('error', function () {
                            if (fuse) {
                                fuse = false;
                                $.get(entryData.baseUrl + 'design/gallery-thumbnail', { file: data.file }, function(response){
                                    $image.attr('src', `${entryData.baseUrl}../images/${response.thumbnail}`);
                                }, 'json');
                            }
                        });
                    }
                    break;
                case 'svg': case 'svg+xml':
                    $image = $(`<img src="${entryData.baseUrl}../images/${data.file}">`);
                    break;
                case 'video':
                    $image = $(`<video class="video-js" width="150px" height="110px" controls="">
                                   <source src="${entryData.baseUrl}../images/${data.file}" class="show-image">
                               </video>`);
                    break;
                default:
                    $image = $(`<div class="image-icon"></div>`);
            }
            $('.image', $item).append($image);
            $('.image, .title', $item).on('click', function(){
                popup.trigger('close');
                const filePath = (data.fileHash ? data.fileHash : data.file);
                fileHolder($box, options, entryData.baseUrl+'../images/' + filePath, 'images/' + filePath);
            });

            $search.on('keyup', function () {
                if ($(this).val() && data.fileName.toLowerCase().search($(this).val().toLowerCase()) === -1) {
                    $item.hide();
                } else {
                    $item.show();
                }
            });

            $('.btn-remove', $item).on('click', function () {
                bootbox.confirm(`Are you sure you want to remove "${data.fileName}" image`, function(result) {
                    if (result) {
                        $.post('upload/remove', {file: data.file}, () => $item.remove());
                    }
                });
            });

            return $item;
        }

        function directoryItem(data) {
            const splitName = data.name.toString().split('-');
            const $item = $(`<div class="item item-directory${splitName[0] == 'product' ? ' item-product' : ''}" data-name="${data.name}">
                                <div class="image"><div class="image-icon"></div></div>
                                <div class="title">${data.title}</div>
                            </div>`);
            $item.on('click', function(){
                breadcrumbsAction(data);
                getContent(directory);
            });

            $search.on('keyup', function () {
                if ($(this).val() && data.title.toLowerCase().search($(this).val().toLowerCase()) === -1) {
                    $item.hide();
                } else {
                    $item.show();
                }
            });

            return $item;
        }

        function breadcrumbsAction(item){
            $breadcrumbs.html('');
            if (item) {
                directory.push(item);
            }
            directory.forEach(function (item, index) {
                const $item = $(`<div class="item">${item.title}</div>`);
                $breadcrumbs.append($item);
                $item.on('click', function(){
                    directory.splice(index+1);
                    getContent(directory);
                    breadcrumbsAction();
                });
            });
        }

    });
}