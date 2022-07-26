tl(createJsUrl('jquery.lazy.min.js'), function(){
    $('.w-banner').each(function(){
        var widgetId = $(this).attr('id');
        if (!widgetId) {
            return ''
        }
        widgetId = widgetId.substring(4);
        if (!isElementExist(['widgets', widgetId, 'lazyLoad'], entryData)) {
            return ''
        }
        $('picture', this).each(function(){
            var item = this;
            $('img', item).lazy({
                bind: 'event',
                beforeLoad: function(){
                    $('source', item).each(function(){
                        let srcset = $(this).data('srcset');
                        $(this).attr('srcset', srcset).removeAttr('data-srcset')
                    })
                },
                afterLoad: function(){
                    $('img', item).removeClass('na-banner');
                    $('source', item).each(function(){
                        $(this).removeClass('na-banner')
                    })
                }
            })
        })
    })
});
