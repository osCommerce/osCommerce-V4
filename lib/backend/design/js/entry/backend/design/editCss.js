
export default function(frame, elem){

        var thisBox = $(elem).closest('div[id]');
        var boxId = thisBox.attr('id');
        var boxName = thisBox.data('name');
        var boxType = '';
        thisBox.closest('div[data-type]').each(function(){
            boxType = $(elem).data('type')
        });

        var windowWidth = $('body', frame).width();
        var widgetWidth = thisBox.width();

        var k = 1.2;
        if (widgetWidth < 300) k = 1.7;
        var popupWidth = widgetWidth * k;
        if (popupWidth > windowWidth - 20) {
            popupWidth = windowWidth - 20;
        }
        var popupLeft = (windowWidth - popupWidth) / 2;
        var popupTop = thisBox.offset().top;

        $('body', frame).append('' +
            '<div class="popup-widget-style">' +
            '  <div class="pop-up-close"></div>' +
            '  <div class="popup-heading">Edit "' + boxName + '" widget styles</div>' +
            '  <div class="popup-content"><div class="' + thisBox.attr('class') + '"></div></div>' +
            '</div>');
        var popupWidgetStyle = $('.popup-widget-style:last', frame);
        popupWidgetStyle.css({
            left: popupLeft,
            width: popupWidth,
            top: popupTop
        });
        $('.pop-up-close', popupWidgetStyle).on('click', function(){
            popupWidgetStyle.remove()
        });
        popupWidgetStyle.draggable({ handle: ".popup-heading" });

        var box = $('.popup-content > div', popupWidgetStyle);
        box.removeClass('box');
        box.removeClass('box-active');
        var thisBoxHtml = $(thisBox.html());
        thisBoxHtml = thisBoxHtml.not('script');
        thisBoxHtml = thisBoxHtml.not('.menu-widget');
        thisBoxHtml = thisBoxHtml.not('.move-block');
        $('script', thisBoxHtml).remove();
        box.append(thisBoxHtml);

        $('input, select, textarea, img', popupWidgetStyle).each(function(){
            $(this).wrap('<div class="input-helper"></div>')
        });

        var widgetClass = box.attr('class');
        $('*:hidden', popupWidgetStyle).show();
        $('.popup-content > div *[class]:not(input, select, textarea, img, .products-listing, products-listing *)', popupWidgetStyle).each(function(){
            var elementClass = $(this).attr('class');
            if ($(this).hasClass('input-helper')) {
                elementClass = $('input, select, textarea, img', this).attr('class');
            }
            if (widgetClass && elementClass) {
                widgetClass = widgetClass.replace(/\s+/g, ".");
                elementClass = elementClass.replace(/\s+/g, ".");
                $(this)
                    .addClass('edit-class')
                    .attr('data-class', '.' + widgetClass + ' .' + elementClass);
            }

            if ($(this).css('display') == 'inline') {
                $(this).css({display: 'inline-block', 'vertical-align': 'top'})
            }
        });
        $('*[data-class]', popupWidgetStyle)
            .append('<span class="menu-widget"><span class="edit-box" title="Edit Block"></span></span>')
            .hover(function(){
                $(this).addClass('active')
            }, function(){
                $(this).removeClass('active')
            })
            .each(function(){
                $('.edit-box', this).attr('title', $(this).data('class'))
            });


        $('.edit-box', popupWidgetStyle).on('click', function(e){
            $('.popup-draggable').remove();

            $('body').append('<div class="popup-draggable" style="left:'+(e.pageX*1+200)+'px; top: '+(e.pageY*1+200)+'px"><div class="pop-up-close"></div><div class="preloader"></div></div>');
            var popup_draggable = $('.popup-draggable');
            popup_draggable.css({
                left: ($(window).width() - popup_draggable.width())/2,
                top: $(window).scrollTop() + 200
            });
            $('.pop-up-close').on('click', function(){
                popup_draggable.remove()
            });
            var selector = $(this).parent().parent().data('class');

            $.get('design/style-edit', {data_class: selector, theme_name: entryData.theme_name}, function(data){
                popup_draggable.html(data);
                saveStyles()
                $('.popup-content').prepend('<span class="popup-heading-small-text">'+selector+'</span>');
                $('.pop-up-close').on('click', function(){
                    popup_draggable.remove();
                    $('#dynamic-style', frame).remove()
                });
                $( ".popup-draggable" ).draggable({ handle: ".popup-heading" });

                $('#dynamic-style', frame).remove();
                $('head', frame).append('<style id="dynamic-style"></style>');
                var boxSave = $('#box-save');
                boxSave.on('change', function(){
                    $.post('design/demo-styles', $(this).serializeArray(), function(data){
                        $('#dynamic-style', frame).html(data);
                    })
                });

                var showChanges = function(){
                    $('.changed', boxSave).removeClass('changed');
                    $('input, select', boxSave).each(function(){
                        if ($(this).val() !== '') {
                            $(this).closest('.setting-row').find('label').addClass('changed');
                            var id = $(this).closest('.tab-pane').attr('id');
                            $('.nav a[href="#'+id+'"]').addClass('changed');
                            id = $(this).closest('.tab-pane').parents('.tab-pane').attr('id');
                            $('.nav a[href="#'+id+'"]').addClass('changed');
                        }
                    })
                };
                showChanges();
                boxSave.on('change', showChanges);

            });


            popup_draggable.draggable(/*{ handle: "p" }*/);
        })

    function saveStyles () {

        var boxSave = $('#box-save');


        window.boxInputChanges = {};

        boxSave.on('change blur click keyup', 'input, select, textarea', function(){
            if ($(this).attr('type') == 'checkbox' && !$(this).is(':checked')) {
                window.boxInputChanges[$(this).attr('name')] = '';
            } else if ($(this).attr('type') == 'checkbox' && $(this).is(':checked')) {
                window.boxInputChanges[$(this).attr('name')] = 1;
            }else {
                window.boxInputChanges[$(this).attr('name')] = $(this).val();
            }
        });



        boxSave.on('submit', function(){

            window.boxInputChanges['id'] = $('input[name="id"]', this).val();
            var params = $('input[name="params"], select[name="params"]', this).val();
            if (params) {
                window.boxInputChanges['params'] = params;
            }

            var values = [];
            $.each( window.boxInputChanges, function(name, value) {
                values = values.concat({ "name": name, "value": value});
            });

            values = values.concat(
                $('.visibility input[disabled]', this).map(function() {
                    return { "name": this.name, "value": 1}
                }).get()
            );

            $('.check_on_off').each(function(){
                values = values.concat({ "name": $(this).attr('name'), "value": $(this).prop( "checked" )});
            });

            var data = values.reduce(function(obj, item) {
                obj[item.name] = item.value;
                return obj;
            }, {});

            $.post('design/style-save', {
                'values': JSON.stringify(data),
                'theme_name': $('input[name="theme_name"]', this).val(),
                'data_class': $('input[name="data_class"]', this).val()
            }, function(){
                $(window).trigger('reload-frame')
            });
            $('.popup-draggable').remove();
            return false
        });

    };
}