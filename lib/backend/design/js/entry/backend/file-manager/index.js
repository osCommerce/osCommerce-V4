
import style from "./style.scss"
import template from "./template"
import gallery from "./gallery"
import upload from "./upload"
import fileHolder from "./file-holder"

$.fn.fileManager = function(op){
    const _op = $.extend({
        name: 'file',
        value: '',
        upload: 'upload',
        delete: 'delete',
        url: 'upload/index',
        type: 'image',
        template: template(),
    }, op)

    return this.each(function() {
        const $box = $(this)

        if ($box.hasClass('applied')) {
            return false
        }

        const options = $.extend(_op, $box.data());

        $box.addClass('applied')
        $box.html(options.template)

        if (options.value) {
            fileHolder($box, options, entryData.frontendUrl + 'images/' + options.value)
        }

        gallery($box, options)
        upload($box, options)

    })
}