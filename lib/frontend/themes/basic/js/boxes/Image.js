tl(createJsUrl('jquery.lazy.min.js'), function(){
    $('.w-image').each(function(){
        var widgetId = $(this).attr('id');
        if (!widgetId) {
            return ''
        }
        widgetId = widgetId.substring(4);
        if (!isElementExist(['widgets', widgetId, 'lazyLoad'], entryData)) {
            return ''
        }
        var e = $('img', this).lazy({
            bind: 'event'
        })
    })
});
