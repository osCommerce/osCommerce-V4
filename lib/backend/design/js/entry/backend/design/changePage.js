import draggablePopup from 'src/draggablePopup';

export default function (frame) {
    const $blocks = $('.block[data-type]', frame);
    const url = new URL(window.location.href);
    const theme_name = url.searchParams.get('theme_name');

    let names = [];
    $blocks.each(function () {
        names.push($(this).data('name'));
    });

    $.get('design-groups/get-groups', {names, theme_name}, function (response) {
        $blocks.each(function () {
            const name = $(this).data('name');
            if (!response[name] || !response[name].list || !response[name].list.length) {
                return null;
            }

            const $changeButton = $(`<span class="change-box" title="Change">${entryData.tr.TEXT_CHANGE}</span>`, );
            $('> .menu-widget', this).prepend($changeButton);

            $changeButton.on('click', function () {

                let $btnSave = $(`<span class="btn btn-primary btn-save">${entryData.tr.IMAGE_SAVE}</span>`);
                let $btnCancel = $(`<span class="btn btn-cancel">${entryData.tr.IMAGE_CANCEL}</span>`);

                const $content = $('<div></div>');

                response[name].list.forEach(function (item) {
                    const checked = response[name].files.includes(item.file) ? ' checked' : '';
                    const $item = $(`
                        <label class="group-item">
                            ${response[name].multiSelect ?
                                `<input type="checkbox" name="group${item.id}" value="${item.id}"${checked}/>` :
                                `<input type="radio" name="group" value="${item.id}"${checked}/>`}
                            <div class="group-item-holder">
                                <div class="radio-box"></div>
                                ${response[name].multiSelect ? `<div class="handle"></div>` : ``}
                                <div class="images">
                                    ${item.images.map(image => `<div class="image"><img src="${image.image}"></div>`)}
                                </div>
                                <div class="">
                                    <div class="title">${item.name}</div>
                                    <div class="description">${item.comment}</div>
                                </div>
                            </div>
                        </label>
                    `);

                    $content.append($item);
                    $('.images', $item).on('click', openImages);
                });

                if (response[name].multiSelect) {
                    $content.sortable({
                        handle: '.handle',
                        axis: 'y'
                    });
                }

                let $popup = draggablePopup($content, {
                    name: 'change-block-popup',
                    heading: `Change "${response[name].title || name}"`,
                    top: 50,
                    buttons: [$btnCancel, $btnSave],
                    className: 'widget-settings'
                });

                $btnSave.on('click', function () {
                    let group_id = [];

                    if (response[name].multiSelect) {
                        $(`input:checked`, $content).each(function () {
                            group_id.push($(this).val());
                        });
                    } else {
                        group_id = [$(`input:checked`, $content).val()];
                    }
                    $.post('design-groups/set-group', { theme_name, group_id, category: name }, function (response) {
                        if (response.error) {
                            console.error(response.error);
                            alertMessage('Errors. See the console for details.', 'alert-message');
                        } else {
                            if (response.widgets && response.widgets.length) {
                                let no = false;
                                let notInstalled = false;
                                const $message = $('<div class="widget-message"></div>');
                                const $no = $(`<div><div class="title">${entryData.tr.EXTENSIONS_YOU_DONT_HAVE}</div></div>`);
                                const $notInstalled = $('<div><div>${entryData.tr.WIDGETS_NOT_INSTALLED_EXTENSIONS}</div></div>');
                                response.widgets.forEach(function (widget) {
                                    if (widget.status == 'no') {
                                        no = true;
                                    }
                                    if (widget.status == 'not-installed') {
                                        notInstalled = true;
                                    }
                                });

                                if (no) {
                                    $message.append($no);
                                }
                                if (notInstalled) {
                                    $message.append($notInstalled);
                                }

                                response.widgets.forEach(function (widget) {
                                    if (widget.status == 'no') {
                                        $no.append(`<div>${ widget.name }</div>`);
                                    }
                                    if (widget.status == 'not-installed') {
                                        $notInstalled.append(`<div>${ widget.name }</div>`);
                                    }
                                });
                                alertMessage($message);
                            }
                            $popup.remove();
                            $(window).trigger('reload-frame');
                        }
                    }, 'json');
                });
            });
        });
    }, 'json');


    function openImages(e) {
        e.preventDefault();

        const $popUp = $(`
            <div class="mp-wrapper">
                <div class="mp-shadow"></div>
                <div class="media-popup">
                    <div class="mp-close"></div>
                    <div class="mp-content"></div>
                 </div>
             </div>`);

        $('.mp-close', $popUp).on('click', function(){
            $popUp.remove();
        });

        const $popUpContent = $('.mp-content', $popUp);

        const $bigImages = $('<div class="mp-big-images"></div>');
        const $smallImages = $('<div class="mp-small-images"></div>');

        $('.image', this).each(function(){
            $bigImages.append($(this).clone());
            $smallImages.append($(this).clone());
        });

        $popUpContent.append($bigImages);
        $popUpContent.append($smallImages);

        $('body').append($popUp);

        $bigImages.slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            fade: true,
            //initialSlide: initialSlide,
            asNavFor: '.mp-small-images'
        });
        $smallImages.slick({
            slidesToShow: 9,
            slidesToScroll: 9,
            //initialSlide: initialSlide,
            asNavFor: '.mp-big-images',
            dots: true,
            centerMode: true,
            focusOnSelect: true,
            responsive: [
                {
                    breakpoint: 1500,
                    settings: {
                        slidesToShow: 7,
                        slidesToScroll: 7
                    }
                },
                {
                    breakpoint: 1100,
                    settings: {
                        slidesToShow: 5,
                        slidesToScroll: 5
                    }
                },
                {
                    breakpoint: 700,
                    settings: {
                        slidesToShow: 3,
                        slidesToScroll: 3
                    }
                },
            ]
        });
    }
}