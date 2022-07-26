tl(createJsUrl('jquery.lazy.min.js'), function(){
    $('.w-categories, .w-top-categories').each(function(){
        var widgetId = $(this).closest('.box').attr('id').substring(4);
        if (!isElementExist(['widgets', widgetId, 'lazyLoad'], entryData)) {
            return ''
        }
        $('.item').each(function(){
            var item = this;
            $('img', item).lazy({
                bind: 'event',
                beforeLoad: function(){
                    $('source', item).each(function(){
                        let srcset = $(this).data('srcset');
                        $(this).attr('srcset', srcset).removeAttr('data-srcset')
                    })
                }
            })
        })
    })
});
