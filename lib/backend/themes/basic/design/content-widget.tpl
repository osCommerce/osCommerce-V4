<div class="content-widget-wrap">
    <div class="popup-heading">{$widgetName}</div>

    <div class="popup-content">

        {$content}

    </div>

    <div class="buttons"></div>

    <div class="popup-buttons">
        <span class="btn btn-primary btn-insert">{$smarty.const.IMAGE_INSERT}</span>
        <span class="btn btn-cancel">{$smarty.const.IMAGE_CANCEL}</span>
    </div>
</div>

<script type="text/javascript">
    $(function(){
        const oEditor = CKEDITOR.instances.{$smarty.get.editor_id};
        const widgetName = `{$widgetName}`;

        if(oEditor.mode === 'wysiwyg') {
            $('.content-widget-wrap .btn-insert').click(function(){
                const settings = $('.content-widget-wrap select, .content-widget-wrap input')
                    .serializeArray()
                    .filter(i => i.value)
                    .map(function(item) {
                        const name = item.name.match(/setting\[0\]\[([^\]]+)\]/)[1];
                        return `${ name}="${ item.value}"`
                    })
                    .join(' ');

                let html = `<widget name="${ widgetName}" ${ settings}></widget>`
                let newElement = CKEDITOR.dom.element.createFromHtml( html, oEditor.document );
                oEditor.insertElement( newElement );
                $(this).parents('.popup-box-wrap').remove();
            })
        } else {
            $('.content-widget-wrap .popup-buttons .btn-cancel').trigger('click')
            alertMessage(`{$smarty.const.TEXT_PLEASE_TURN}`, 'alert-message')
        }


        if (widgetName == 'Banner' && !$('#banners_type').val()) {
            $('#banners_type').val('single').trigger('change')
        }
        if (widgetName == 'Banner' && !$('.template-row select').val()) {
            $('.template-row select').val('1');
            $('.template-row').hide()
        }
    })
</script>